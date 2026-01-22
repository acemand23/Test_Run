import { Router } from 'express';
import { auditController } from '../controllers/audit.controller';
import { authenticate, requireRole } from '../middleware/auth';

const router = Router();

// All routes require authentication and admin role
router.use(authenticate);
router.use(requireRole('OWNER', 'ADMIN'));

router.get('/', auditController.query.bind(auditController));
router.get('/stats', auditController.stats.bind(auditController));
router.get('/documents/:documentId', auditController.getDocumentTrail.bind(auditController));

export default router;
