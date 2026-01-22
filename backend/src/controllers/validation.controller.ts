import { Response, NextFunction } from 'express';
import { z } from 'zod';
import { validationService } from '../services/validation.service';
import { AuthenticatedRequest } from '../middleware/auth';
import { NotFoundError } from '../middleware/errorHandler';

const runValidationSchema = z.object({
  documentId: z.string().uuid(),
  promptId: z.string().uuid(),
  llmConfigId: z.string().uuid(),
});

const listQuerySchema = z.object({
  page: z.coerce.number().int().positive().default(1),
  limit: z.coerce.number().int().positive().max(100).default(20),
  documentId: z.string().uuid().optional(),
  status: z.enum(['PENDING', 'PROCESSING', 'COMPLETED', 'FAILED']).optional(),
  result: z.enum(['PASS', 'FAIL', 'WARNING', 'NEEDS_REVIEW']).optional(),
});

const statsQuerySchema = z.object({
  days: z.coerce.number().int().positive().max(365).default(30),
});

export class ValidationController {
  async run(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const input = runValidationSchema.parse(req.body);

      const validationId = await validationService.runValidation({
        ...input,
        organizationId: req.user.organizationId,
        userId: req.user.userId,
      });

      res.status(202).json({
        validationId,
        message: 'Validation started',
      });
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

      const validation = await validationService.getValidation(
        req.params.id,
        req.user.organizationId
      );

      if (!validation) {
        throw new NotFoundError('Validation not found');
      }

      res.json(validation);
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

      const result = await validationService.listValidations(
        req.user.organizationId,
        query
      );

      res.json(result);
    } catch (error) {
      next(error);
    }
  }

  async stats(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const { days } = statsQuerySchema.parse(req.query);

      const stats = await validationService.getValidationStats(
        req.user.organizationId,
        days
      );

      res.json(stats);
    } catch (error) {
      next(error);
    }
  }
}

export const validationController = new ValidationController();
