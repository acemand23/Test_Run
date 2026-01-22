import { Response, NextFunction } from 'express';
import { z } from 'zod';
import { documentService } from '../services/document.service';
import { AuthenticatedRequest } from '../middleware/auth';
import { NotFoundError } from '../middleware/errorHandler';

const listQuerySchema = z.object({
  page: z.coerce.number().int().positive().default(1),
  limit: z.coerce.number().int().positive().max(100).default(20),
  status: z.enum(['PENDING', 'PROCESSING', 'VALIDATED', 'FAILED']).optional(),
  search: z.string().optional(),
});

export class DocumentController {
  async upload(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      if (!req.file) {
        res.status(400).json({ error: 'No file uploaded' });
        return;
      }

      const storeHashForAudit = req.body.storeHashForAudit === 'true';

      const result = await documentService.uploadDocument({
        file: req.file.buffer,
        originalName: req.file.originalname,
        mimeType: req.file.mimetype,
        organizationId: req.user.organizationId,
        uploadedById: req.user.userId,
        storeHashForAudit,
      });

      res.status(201).json(result);
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

      const document = await documentService.getDocument(
        req.params.id,
        req.user.organizationId
      );

      if (!document) {
        throw new NotFoundError('Document not found');
      }

      res.json(document);
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

      const result = await documentService.listDocuments(
        req.user.organizationId,
        query
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

      await documentService.deleteDocument(
        req.params.id,
        req.user.organizationId,
        req.user.userId
      );

      res.status(204).send();
    } catch (error) {
      next(error);
    }
  }

  async getContent(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const content = await documentService.getDocumentContent(
        req.params.id,
        req.user.organizationId
      );

      res.json({ content });
    } catch (error) {
      next(error);
    }
  }
}

export const documentController = new DocumentController();
