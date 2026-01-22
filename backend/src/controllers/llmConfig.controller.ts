import { Response, NextFunction } from 'express';
import { z } from 'zod';
import { prisma } from '../models/prisma';
import { AuthenticatedRequest } from '../middleware/auth';
import { NotFoundError, ConflictError } from '../middleware/errorHandler';
import { encrypt } from '../utils/hash';
import { config } from '../config';
import { auditService } from '../services/audit.service';
import { LLMService } from '../services/llm';

const createConfigSchema = z.object({
  provider: z.enum(['OPENAI', 'ANTHROPIC', 'AZURE_OPENAI', 'CUSTOM']),
  modelName: z.string().min(1).max(100),
  displayName: z.string().min(1).max(100),
  apiKey: z.string().optional(), // User's own API key (will be encrypted)
  isEnabled: z.boolean().default(true),
  isDefault: z.boolean().default(false),
  settings: z.record(z.unknown()).optional(),
});

const updateConfigSchema = z.object({
  displayName: z.string().min(1).max(100).optional(),
  apiKey: z.string().optional(),
  isEnabled: z.boolean().optional(),
  isDefault: z.boolean().optional(),
  settings: z.record(z.unknown()).optional(),
});

export class LLMConfigController {
  async create(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const input = createConfigSchema.parse(req.body);

      // Check for duplicate
      const existing = await prisma.lLMConfig.findFirst({
        where: {
          organizationId: req.user.organizationId,
          provider: input.provider,
          modelName: input.modelName,
        },
      });

      if (existing) {
        throw new ConflictError('LLM configuration already exists for this provider and model');
      }

      // Encrypt API key if provided
      let apiKeyEncrypted: string | undefined;
      if (input.apiKey) {
        apiKeyEncrypted = encrypt(input.apiKey, config.encryption.key);
      }

      // If setting as default, unset other defaults
      if (input.isDefault) {
        await prisma.lLMConfig.updateMany({
          where: {
            organizationId: req.user.organizationId,
            isDefault: true,
          },
          data: { isDefault: false },
        });
      }

      const llmConfig = await prisma.lLMConfig.create({
        data: {
          provider: input.provider,
          modelName: input.modelName,
          displayName: input.displayName,
          isEnabled: input.isEnabled,
          isDefault: input.isDefault,
          apiKeyEncrypted,
          settings: input.settings || {},
          organizationId: req.user.organizationId,
        },
      });

      await auditService.log({
        action: 'LLM_CONFIG_UPDATED',
        entityType: 'llmConfig',
        entityId: llmConfig.id,
        organizationId: req.user.organizationId,
        userId: req.user.userId,
        details: {
          action: 'created',
          provider: input.provider,
          modelName: input.modelName,
        },
      });

      res.status(201).json({
        id: llmConfig.id,
        provider: llmConfig.provider,
        modelName: llmConfig.modelName,
        displayName: llmConfig.displayName,
        isEnabled: llmConfig.isEnabled,
        isDefault: llmConfig.isDefault,
        hasCustomApiKey: !!llmConfig.apiKeyEncrypted,
        settings: llmConfig.settings,
      });
    } catch (error) {
      next(error);
    }
  }

  async update(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const input = updateConfigSchema.parse(req.body);

      const existing = await prisma.lLMConfig.findFirst({
        where: {
          id: req.params.id,
          organizationId: req.user.organizationId,
        },
      });

      if (!existing) {
        throw new NotFoundError('LLM configuration not found');
      }

      // Encrypt API key if provided
      let apiKeyEncrypted: string | undefined;
      if (input.apiKey) {
        apiKeyEncrypted = encrypt(input.apiKey, config.encryption.key);
      }

      // If setting as default, unset other defaults
      if (input.isDefault) {
        await prisma.lLMConfig.updateMany({
          where: {
            organizationId: req.user.organizationId,
            isDefault: true,
            id: { not: req.params.id },
          },
          data: { isDefault: false },
        });
      }

      const llmConfig = await prisma.lLMConfig.update({
        where: { id: req.params.id },
        data: {
          ...(input.displayName && { displayName: input.displayName }),
          ...(input.isEnabled !== undefined && { isEnabled: input.isEnabled }),
          ...(input.isDefault !== undefined && { isDefault: input.isDefault }),
          ...(apiKeyEncrypted && { apiKeyEncrypted }),
          ...(input.settings && { settings: input.settings }),
        },
      });

      await auditService.log({
        action: 'LLM_CONFIG_UPDATED',
        entityType: 'llmConfig',
        entityId: llmConfig.id,
        organizationId: req.user.organizationId,
        userId: req.user.userId,
        details: {
          action: 'updated',
          changes: Object.keys(input),
        },
      });

      res.json({
        id: llmConfig.id,
        provider: llmConfig.provider,
        modelName: llmConfig.modelName,
        displayName: llmConfig.displayName,
        isEnabled: llmConfig.isEnabled,
        isDefault: llmConfig.isDefault,
        hasCustomApiKey: !!llmConfig.apiKeyEncrypted,
        settings: llmConfig.settings,
      });
    } catch (error) {
      next(error);
    }
  }

  async delete(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const existing = await prisma.lLMConfig.findFirst({
        where: {
          id: req.params.id,
          organizationId: req.user.organizationId,
        },
      });

      if (!existing) {
        throw new NotFoundError('LLM configuration not found');
      }

      await prisma.lLMConfig.delete({
        where: { id: req.params.id },
      });

      await auditService.log({
        action: 'LLM_CONFIG_UPDATED',
        entityType: 'llmConfig',
        entityId: req.params.id,
        organizationId: req.user.organizationId,
        userId: req.user.userId,
        details: {
          action: 'deleted',
          provider: existing.provider,
          modelName: existing.modelName,
        },
      });

      res.status(204).send();
    } catch (error) {
      next(error);
    }
  }

  async list(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const configs = await prisma.lLMConfig.findMany({
        where: { organizationId: req.user.organizationId },
        orderBy: [{ isDefault: 'desc' }, { displayName: 'asc' }],
      });

      res.json({
        configs: configs.map((c) => ({
          id: c.id,
          provider: c.provider,
          modelName: c.modelName,
          displayName: c.displayName,
          isEnabled: c.isEnabled,
          isDefault: c.isDefault,
          hasCustomApiKey: !!c.apiKeyEncrypted,
          settings: c.settings,
        })),
      });
    } catch (error) {
      next(error);
    }
  }

  async getSupportedProviders(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      const providers = LLMService.getSupportedProviders();
      const defaultModels = LLMService.getDefaultModels();

      res.json({
        providers: providers.map((p) => ({
          id: p,
          name: p.replace('_', ' '),
          defaultModel: defaultModels[p],
        })),
      });
    } catch (error) {
      next(error);
    }
  }
}

export const llmConfigController = new LLMConfigController();
