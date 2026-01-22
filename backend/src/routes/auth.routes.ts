import { Router } from 'express';
import { authController } from '../controllers/auth.controller';
import { authenticate, requireRole } from '../middleware/auth';

const router = Router();

// Public routes
router.post('/register', authController.register.bind(authController));
router.post('/login', authController.login.bind(authController));

// Protected routes
router.get('/me', authenticate, authController.me.bind(authController));
router.post('/invite', authenticate, requireRole('OWNER', 'ADMIN'), authController.inviteUser.bind(authController));
router.post('/change-password', authenticate, authController.changePassword.bind(authController));

export default router;
