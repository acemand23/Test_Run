import { Router } from 'express';
import { validationController } from '../controllers/validation.controller';
import { authenticate, requireOrganization } from '../middleware/auth';

const router = Router();

// All routes require authentication
router.use(authenticate);
router.use(requireOrganization);

router.post('/', validationController.run.bind(validationController));
router.get('/stats', validationController.stats.bind(validationController));
router.get('/', validationController.list.bind(validationController));
router.get('/:id', validationController.get.bind(validationController));

export default router;
