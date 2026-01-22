import { Router } from 'express';
import authRoutes from './auth.routes';
import documentRoutes from './document.routes';
import validationRoutes from './validation.routes';
import promptRoutes from './prompt.routes';
import subscriptionRoutes from './subscription.routes';
import auditRoutes from './audit.routes';
import llmConfigRoutes from './llmConfig.routes';

const router = Router();

// Health check
router.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// API routes
router.use('/auth', authRoutes);
router.use('/documents', documentRoutes);
router.use('/validations', validationRoutes);
router.use('/prompts', promptRoutes);
router.use('/subscription', subscriptionRoutes);
router.use('/audit', auditRoutes);
router.use('/llm-configs', llmConfigRoutes);

export default router;
