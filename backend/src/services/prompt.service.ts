import { prisma } from '../models/prisma';
import { auditService } from './audit.service';
import { logger } from '../utils/logger';
import { NotFoundError, ConflictError } from '../middleware/errorHandler';

export interface CreatePromptInput {
  name: string;
  description?: string;
  promptTemplate: string;
  category?: string;
  isDefault?: boolean;
  organizationId: string;
  userId: string;
}

export interface UpdatePromptInput {
  name?: string;
  description?: string;
  promptTemplate?: string;
  category?: string;
  isActive?: boolean;
  isDefault?: boolean;
}

export interface PromptDetails {
  id: string;
  name: string;
  description: string | null;
  promptTemplate: string;
  category: string | null;
  isActive: boolean;
  isDefault: boolean;
  version: number;
  createdAt: Date;
  updatedAt: Date;
  validationCount?: number;
}

export class PromptService {
  async createPrompt(input: CreatePromptInput): Promise<PromptDetails> {
    // Check for duplicate name
    const existing = await prisma.prompt.findFirst({
      where: {
        name: input.name,
        organizationId: input.organizationId,
      },
    });

    if (existing) {
      throw new ConflictError(`Prompt with name "${input.name}" already exists`);
    }

    // If setting as default, unset other defaults
    if (input.isDefault) {
      await prisma.prompt.updateMany({
        where: {
          organizationId: input.organizationId,
          isDefault: true,
        },
        data: { isDefault: false },
      });
    }

    const prompt = await prisma.prompt.create({
      data: {
        name: input.name,
        description: input.description,
        promptTemplate: input.promptTemplate,
        category: input.category,
        isDefault: input.isDefault || false,
        organizationId: input.organizationId,
      },
    });

    // Audit log
    await auditService.log({
      action: 'PROMPT_CREATED',
      entityType: 'prompt',
      entityId: prompt.id,
      organizationId: input.organizationId,
      userId: input.userId,
      details: {
        name: input.name,
        category: input.category,
      },
    });

    logger.info(`Prompt created: ${prompt.id} by user ${input.userId}`);

    return {
      id: prompt.id,
      name: prompt.name,
      description: prompt.description,
      promptTemplate: prompt.promptTemplate,
      category: prompt.category,
      isActive: prompt.isActive,
      isDefault: prompt.isDefault,
      version: prompt.version,
      createdAt: prompt.createdAt,
      updatedAt: prompt.updatedAt,
    };
  }

  async updatePrompt(
    promptId: string,
    organizationId: string,
    userId: string,
    input: UpdatePromptInput
  ): Promise<PromptDetails> {
    const existing = await prisma.prompt.findFirst({
      where: {
        id: promptId,
        organizationId,
      },
    });

    if (!existing) {
      throw new NotFoundError('Prompt not found');
    }

    // Check for name conflict if changing name
    if (input.name && input.name !== existing.name) {
      const nameConflict = await prisma.prompt.findFirst({
        where: {
          name: input.name,
          organizationId,
          id: { not: promptId },
        },
      });

      if (nameConflict) {
        throw new ConflictError(`Prompt with name "${input.name}" already exists`);
      }
    }

    // If setting as default, unset other defaults
    if (input.isDefault) {
      await prisma.prompt.updateMany({
        where: {
          organizationId,
          isDefault: true,
          id: { not: promptId },
        },
        data: { isDefault: false },
      });
    }

    const prompt = await prisma.prompt.update({
      where: { id: promptId },
      data: {
        ...input,
        version: { increment: 1 },
      },
    });

    // Audit log
    await auditService.log({
      action: 'PROMPT_UPDATED',
      entityType: 'prompt',
      entityId: prompt.id,
      organizationId,
      userId,
      details: {
        changes: Object.keys(input),
        newVersion: prompt.version,
      },
    });

    logger.info(`Prompt updated: ${prompt.id} by user ${userId}`);

    return {
      id: prompt.id,
      name: prompt.name,
      description: prompt.description,
      promptTemplate: prompt.promptTemplate,
      category: prompt.category,
      isActive: prompt.isActive,
      isDefault: prompt.isDefault,
      version: prompt.version,
      createdAt: prompt.createdAt,
      updatedAt: prompt.updatedAt,
    };
  }

  async deletePrompt(
    promptId: string,
    organizationId: string,
    userId: string
  ): Promise<void> {
    const prompt = await prisma.prompt.findFirst({
      where: {
        id: promptId,
        organizationId,
      },
      include: {
        _count: { select: { validations: true } },
      },
    });

    if (!prompt) {
      throw new NotFoundError('Prompt not found');
    }

    // Soft delete if prompt has been used
    if (prompt._count.validations > 0) {
      await prisma.prompt.update({
        where: { id: promptId },
        data: { isActive: false },
      });
    } else {
      await prisma.prompt.delete({
        where: { id: promptId },
      });
    }

    // Audit log
    await auditService.log({
      action: 'PROMPT_DELETED',
      entityType: 'prompt',
      entityId: promptId,
      organizationId,
      userId,
      details: {
        name: prompt.name,
        softDeleted: prompt._count.validations > 0,
      },
    });

    logger.info(`Prompt deleted: ${promptId} by user ${userId}`);
  }

  async getPrompt(
    promptId: string,
    organizationId: string
  ): Promise<PromptDetails | null> {
    const prompt = await prisma.prompt.findFirst({
      where: {
        id: promptId,
        organizationId,
      },
      include: {
        _count: { select: { validations: true } },
      },
    });

    if (!prompt) {
      return null;
    }

    return {
      id: prompt.id,
      name: prompt.name,
      description: prompt.description,
      promptTemplate: prompt.promptTemplate,
      category: prompt.category,
      isActive: prompt.isActive,
      isDefault: prompt.isDefault,
      version: prompt.version,
      createdAt: prompt.createdAt,
      updatedAt: prompt.updatedAt,
      validationCount: prompt._count.validations,
    };
  }

  async listPrompts(
    organizationId: string,
    options: {
      category?: string;
      isActive?: boolean;
      search?: string;
      page?: number;
      limit?: number;
    } = {}
  ) {
    const { category, isActive, search, page = 1, limit = 20 } = options;
    const skip = (page - 1) * limit;

    const where = {
      organizationId,
      ...(category && { category }),
      ...(isActive !== undefined && { isActive }),
      ...(search && {
        OR: [
          { name: { contains: search, mode: 'insensitive' as const } },
          { description: { contains: search, mode: 'insensitive' as const } },
        ],
      }),
    };

    const [prompts, total] = await Promise.all([
      prisma.prompt.findMany({
        where,
        orderBy: [{ isDefault: 'desc' }, { updatedAt: 'desc' }],
        skip,
        take: limit,
        include: {
          _count: { select: { validations: true } },
        },
      }),
      prisma.prompt.count({ where }),
    ]);

    return {
      prompts: prompts.map((p) => ({
        id: p.id,
        name: p.name,
        description: p.description,
        category: p.category,
        isActive: p.isActive,
        isDefault: p.isDefault,
        version: p.version,
        createdAt: p.createdAt,
        updatedAt: p.updatedAt,
        validationCount: p._count.validations,
      })),
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    };
  }

  async getCategories(organizationId: string): Promise<string[]> {
    const categories = await prisma.prompt.findMany({
      where: {
        organizationId,
        category: { not: null },
      },
      select: { category: true },
      distinct: ['category'],
    });

    return categories
      .map((c) => c.category)
      .filter((c): c is string => c !== null);
  }
}

export const promptService = new PromptService();
