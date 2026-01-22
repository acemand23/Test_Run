import { Response, NextFunction } from 'express';
import { z } from 'zod';
import { promptService } from '../services/prompt.service';
import { AuthenticatedRequest } from '../middleware/auth';
import { NotFoundError } from '../middleware/errorHandler';

const createPromptSchema = z.object({
  name: z.string().min(1).max(100),
  description: z.string().max(500).optional(),
  promptTemplate: z.string().min(10).max(10000),
  category: z.string().max(50).optional(),
  isDefault: z.boolean().optional(),
});

const updatePromptSchema = z.object({
  name: z.string().min(1).max(100).optional(),
  description: z.string().max(500).optional(),
  promptTemplate: z.string().min(10).max(10000).optional(),
  category: z.string().max(50).optional(),
  isActive: z.boolean().optional(),
  isDefault: z.boolean().optional(),
});

const listQuerySchema = z.object({
  page: z.coerce.number().int().positive().default(1),
  limit: z.coerce.number().int().positive().max(100).default(20),
  category: z.string().optional(),
  isActive: z.coerce.boolean().optional(),
  search: z.string().optional(),
});

export class PromptController {
  async create(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const input = createPromptSchema.parse(req.body);

      const result = await promptService.createPrompt({
        ...input,
        organizationId: req.user.organizationId,
        userId: req.user.userId,
      });

      res.status(201).json(result);
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

      const input = updatePromptSchema.parse(req.body);

      const result = await promptService.updatePrompt(
        req.params.id,
        req.user.organizationId,
        req.user.userId,
        input
      );

      res.json(result);
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

      await promptService.deletePrompt(
        req.params.id,
        req.user.organizationId,
        req.user.userId
      );

      res.status(204).send();
    } catch (error) {
      next(error);
    }
  }

  async get(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const prompt = await promptService.getPrompt(
        req.params.id,
        req.user.organizationId
      );

      if (!prompt) {
        throw new NotFoundError('Prompt not found');
      }

      res.json(prompt);
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

      const query = listQuerySchema.parse(req.query);

      const result = await promptService.listPrompts(
        req.user.organizationId,
        query
      );

      res.json(result);
    } catch (error) {
      next(error);
    }
  }

  async getCategories(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const categories = await promptService.getCategories(req.user.organizationId);

      res.json({ categories });
    } catch (error) {
      next(error);
    }
  }
}

export const promptController = new PromptController();
