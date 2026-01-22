import { Router } from 'express';
import express from 'express';
import { subscriptionController } from '../controllers/subscription.controller';
import { authenticate, requireRole } from '../middleware/auth';

const router = Router();

// Stripe webhook needs raw body
router.post(
  '/webhook',
  express.raw({ type: 'application/json' }),
  subscriptionController.handleWebhook.bind(subscriptionController)
);

// Protected routes
router.use(authenticate);

router.get('/', subscriptionController.getDetails.bind(subscriptionController));
router.post('/checkout', requireRole('OWNER', 'ADMIN'), subscriptionController.createCheckout.bind(subscriptionController));
router.post('/portal', requireRole('OWNER', 'ADMIN'), subscriptionController.createPortal.bind(subscriptionController));

export default router;
