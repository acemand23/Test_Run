import { Response, NextFunction } from 'express';
import { z } from 'zod';
import { auditService } from '../services/audit.service';
import { AuthenticatedRequest } from '../middleware/auth';

const querySchema = z.object({
  page: z.coerce.number().int().positive().default(1),
  limit: z.coerce.number().int().positive().max(100).default(50),
  action: z.string().optional(),
  entityType: z.string().optional(),
  entityId: z.string().optional(),
  userId: z.string().uuid().optional(),
  startDate: z.coerce.date().optional(),
  endDate: z.coerce.date().optional(),
});

const statsQuerySchema = z.object({
  days: z.coerce.number().int().positive().max(365).default(30),
});

export class AuditController {
  async query(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const query = querySchema.parse(req.query);

      const result = await auditService.query({
        organizationId: req.user.organizationId,
        ...query,
        action: query.action as any,
      });

      res.json(result);
    } catch (error) {
      next(error);
    }
  }

  async getDocumentTrail(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const trail = await auditService.getDocumentAuditTrail(
        req.params.documentId,
        req.user.organizationId
      );

      res.json({ trail });
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

      const stats = await auditService.getAuditStats(
        req.user.organizationId,
        days
      );

      res.json(stats);
    } catch (error) {
      next(error);
    }
  }
}

export const auditController = new AuditController();
