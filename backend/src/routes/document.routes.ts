import { Router } from 'express';
import multer from 'multer';
import { documentController } from '../controllers/document.controller';
import { authenticate, requireOrganization } from '../middleware/auth';

const router = Router();

// Configure multer for file uploads
const upload = multer({
  storage: multer.memoryStorage(),
  limits: {
    fileSize: 50 * 1024 * 1024, // 50MB
  },
});

// All routes require authentication
router.use(authenticate);
router.use(requireOrganization);

router.post('/upload', upload.single('file'), documentController.upload.bind(documentController));
router.get('/', documentController.list.bind(documentController));
router.get('/:id', documentController.get.bind(documentController));
router.get('/:id/content', documentController.getContent.bind(documentController));
router.delete('/:id', documentController.delete.bind(documentController));

export default router;
