import { Router } from 'express';
import { llmConfigController } from '../controllers/llmConfig.controller';
import { authenticate, requireRole } from '../middleware/auth';

const router = Router();

// All routes require authentication
router.use(authenticate);

// Read routes available to all authenticated users
router.get('/providers', llmConfigController.getSupportedProviders.bind(llmConfigController));
router.get('/', llmConfigController.list.bind(llmConfigController));

// Write operations require admin role
router.post('/', requireRole('OWNER', 'ADMIN'), llmConfigController.create.bind(llmConfigController));
router.put('/:id', requireRole('OWNER', 'ADMIN'), llmConfigController.update.bind(llmConfigController));
router.delete('/:id', requireRole('OWNER', 'ADMIN'), llmConfigController.delete.bind(llmConfigController));

export default router;
