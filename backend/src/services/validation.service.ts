import { prisma } from '../models/prisma';
import { documentService } from './document.service';
import { auditService } from './audit.service';
import { createLLMServiceFromConfig } from './llm';
import { logger } from '../utils/logger';
import { NotFoundError, AppError } from '../middleware/errorHandler';
import { ValidationStatus, ValidationResult } from '@prisma/client';

export interface RunValidationInput {
  documentId: string;
  promptId: string;
  llmConfigId: string;
  organizationId: string;
  userId: string;
}

export interface ValidationDetails {
  id: string;
  status: ValidationStatus;
  result: ValidationResult | null;
  score: number | null;
  findings: unknown;
  rawResponse: string | null;
  tokensUsed: number | null;
  processingTimeMs: number | null;
  errorMessage: string | null;
  createdAt: Date;
  completedAt: Date | null;
  document: {
    id: string;
    originalName: string;
  };
  prompt: {
    id: string;
    name: string;
  };
  llmConfig: {
    provider: string;
    modelName: string;
    displayName: string;
  };
}

export class ValidationService {
  async runValidation(input: RunValidationInput): Promise<string> {
    // Verify document exists and belongs to organization
    const document = await prisma.document.findFirst({
      where: {
        id: input.documentId,
        organizationId: input.organizationId,
      },
    });

    if (!document) {
      throw new NotFoundError('Document not found');
    }

    // Verify prompt exists and belongs to organization
    const prompt = await prisma.prompt.findFirst({
      where: {
        id: input.promptId,
        organizationId: input.organizationId,
        isActive: true,
      },
    });

    if (!prompt) {
      throw new NotFoundError('Prompt not found');
    }

    // Verify LLM config exists and is enabled
    const llmConfig = await prisma.lLMConfig.findFirst({
      where: {
        id: input.llmConfigId,
        organizationId: input.organizationId,
        isEnabled: true,
      },
    });

    if (!llmConfig) {
      throw new NotFoundError('LLM configuration not found');
    }

    // Create validation record
    const validation = await prisma.validation.create({
      data: {
        status: 'PENDING',
        documentId: input.documentId,
        promptId: input.promptId,
        llmConfigId: input.llmConfigId,
      },
    });

    // Update document status
    await prisma.document.update({
      where: { id: input.documentId },
      data: { status: 'PROCESSING' },
    });

    // Audit log
    await auditService.log({
      action: 'VALIDATION_STARTED',
      entityType: 'validation',
      entityId: validation.id,
      organizationId: input.organizationId,
      userId: input.userId,
      documentId: input.documentId,
      validationId: validation.id,
      details: {
        promptId: input.promptId,
        llmConfigId: input.llmConfigId,
      },
    });

    // Process validation asynchronously
    this.processValidation(
      validation.id,
      document,
      prompt,
      llmConfig,
      input.organizationId,
      input.userId
    ).catch((error) => {
      logger.error(`Validation ${validation.id} failed:`, error);
    });

    return validation.id;
  }

  private async processValidation(
    validationId: string,
    document: { id: string; storageKey: string; originalName: string; mimeType: string },
    prompt: { promptTemplate: string },
    llmConfig: { provider: string; modelName: string; apiKeyEncrypted: string | null; settings: unknown },
    organizationId: string,
    userId: string
  ): Promise<void> {
    try {
      // Update status to processing
      await prisma.validation.update({
        where: { id: validationId },
        data: { status: 'PROCESSING' },
      });

      // Get document content
      const documentContent = await documentService.getDocumentContent(
        document.id,
        organizationId
      );

      // Create LLM service
      const llmService = createLLMServiceFromConfig(
        llmConfig.provider as 'OPENAI' | 'ANTHROPIC' | 'AZURE_OPENAI' | 'CUSTOM',
        llmConfig.modelName,
        llmConfig.apiKeyEncrypted || undefined,
        llmConfig.settings as Record<string, unknown> | undefined
      );

      // Run validation
      const result = await llmService.validateDocument({
        documentContent,
        promptTemplate: prompt.promptTemplate,
        documentMetadata: {
          filename: document.originalName,
          mimeType: document.mimeType,
        },
      });

      // Update validation record
      await prisma.validation.update({
        where: { id: validationId },
        data: {
          status: 'COMPLETED',
          result: result.result as ValidationResult,
          score: result.score,
          findings: result.findings,
          rawResponse: result.rawResponse,
          tokensUsed: result.tokensUsed,
          processingTimeMs: result.processingTimeMs,
          completedAt: new Date(),
        },
      });

      // Update document status
      await prisma.document.update({
        where: { id: document.id },
        data: { status: 'VALIDATED' },
      });

      // Audit log
      await auditService.log({
        action: 'VALIDATION_COMPLETED',
        entityType: 'validation',
        entityId: validationId,
        organizationId,
        userId,
        documentId: document.id,
        validationId,
        details: {
          result: result.result,
          score: result.score,
          findingsCount: result.findings.length,
          tokensUsed: result.tokensUsed,
          processingTimeMs: result.processingTimeMs,
        },
      });

      logger.info(`Validation ${validationId} completed: ${result.result}`);
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Unknown error';

      // Update validation as failed
      await prisma.validation.update({
        where: { id: validationId },
        data: {
          status: 'FAILED',
          errorMessage,
          completedAt: new Date(),
        },
      });

      // Update document status
      await prisma.document.update({
        where: { id: document.id },
        data: { status: 'FAILED' },
      });

      // Audit log
      await auditService.log({
        action: 'VALIDATION_FAILED',
        entityType: 'validation',
        entityId: validationId,
        organizationId,
        userId,
        documentId: document.id,
        validationId,
        details: { error: errorMessage },
      });

      logger.error(`Validation ${validationId} failed: ${errorMessage}`);
    }
  }

  async getValidation(
    validationId: string,
    organizationId: string
  ): Promise<ValidationDetails | null> {
    const validation = await prisma.validation.findFirst({
      where: {
        id: validationId,
        document: {
          organizationId,
        },
      },
      include: {
        document: {
          select: { id: true, originalName: true },
        },
        prompt: {
          select: { id: true, name: true },
        },
        llmConfig: {
          select: { provider: true, modelName: true, displayName: true },
        },
      },
    });

    if (!validation) {
      return null;
    }

    return {
      id: validation.id,
      status: validation.status,
      result: validation.result,
      score: validation.score,
      findings: validation.findings,
      rawResponse: validation.rawResponse,
      tokensUsed: validation.tokensUsed,
      processingTimeMs: validation.processingTimeMs,
      errorMessage: validation.errorMessage,
      createdAt: validation.createdAt,
      completedAt: validation.completedAt,
      document: validation.document,
      prompt: validation.prompt,
      llmConfig: validation.llmConfig,
    };
  }

  async listValidations(
    organizationId: string,
    options: {
      documentId?: string;
      status?: ValidationStatus;
      result?: ValidationResult;
      page?: number;
      limit?: number;
    } = {}
  ) {
    const { documentId, status, result, page = 1, limit = 20 } = options;
    const skip = (page - 1) * limit;

    const where = {
      document: { organizationId },
      ...(documentId && { documentId }),
      ...(status && { status }),
      ...(result && { result }),
    };

    const [validations, total] = await Promise.all([
      prisma.validation.findMany({
        where,
        orderBy: { createdAt: 'desc' },
        skip,
        take: limit,
        include: {
          document: {
            select: { id: true, originalName: true },
          },
          prompt: {
            select: { id: true, name: true },
          },
          llmConfig: {
            select: { provider: true, modelName: true, displayName: true },
          },
        },
      }),
      prisma.validation.count({ where }),
    ]);

    return {
      validations: validations.map((v) => ({
        id: v.id,
        status: v.status,
        result: v.result,
        score: v.score,
        createdAt: v.createdAt,
        completedAt: v.completedAt,
        document: v.document,
        prompt: v.prompt,
        llmConfig: v.llmConfig,
      })),
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    };
  }

  async getValidationStats(organizationId: string, days: number = 30) {
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - days);

    const [resultCounts, statusCounts, dailyValidations] = await Promise.all([
      prisma.validation.groupBy({
        by: ['result'],
        where: {
          document: { organizationId },
          createdAt: { gte: startDate },
          status: 'COMPLETED',
        },
        _count: true,
      }),
      prisma.validation.groupBy({
        by: ['status'],
        where: {
          document: { organizationId },
          createdAt: { gte: startDate },
        },
        _count: true,
      }),
      prisma.$queryRaw<{ date: Date; count: bigint; avg_score: number }[]>`
        SELECT
          DATE(v.created_at) as date,
          COUNT(*) as count,
          AVG(v.score) as avg_score
        FROM "Validation" v
        JOIN "Document" d ON v.document_id = d.id
        WHERE d.organization_id = ${organizationId}
          AND v.created_at >= ${startDate}
        GROUP BY DATE(v.created_at)
        ORDER BY date ASC
      `,
    ]);

    return {
      resultCounts: resultCounts.reduce(
        (acc, r) => ({ ...acc, [r.result || 'null']: r._count }),
        {} as Record<string, number>
      ),
      statusCounts: statusCounts.reduce(
        (acc, s) => ({ ...acc, [s.status]: s._count }),
        {} as Record<string, number>
      ),
      dailyValidations: dailyValidations.map((d) => ({
        date: d.date.toISOString().split('T')[0],
        count: Number(d.count),
        avgScore: d.avg_score ? Math.round(d.avg_score * 10) / 10 : null,
      })),
    };
  }
}

export const validationService = new ValidationService();
