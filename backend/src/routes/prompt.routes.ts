import { Router } from 'express';
import { promptController } from '../controllers/prompt.controller';
import { authenticate, requireRole } from '../middleware/auth';

const router = Router();

// All routes require authentication
router.use(authenticate);

router.get('/categories', promptController.getCategories.bind(promptController));
router.get('/', promptController.list.bind(promptController));
router.get('/:id', promptController.get.bind(promptController));

// Write operations require admin role
router.post('/', requireRole('OWNER', 'ADMIN'), promptController.create.bind(promptController));
router.put('/:id', requireRole('OWNER', 'ADMIN'), promptController.update.bind(promptController));
router.delete('/:id', requireRole('OWNER', 'ADMIN'), promptController.delete.bind(promptController));

export default router;
