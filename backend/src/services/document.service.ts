import { prisma } from '../models/prisma';
import { storageService } from './storage.service';
import { auditService } from './audit.service';
import { generateDocumentHash } from '../utils/hash';
import { logger } from '../utils/logger';
import { AppError, NotFoundError, ForbiddenError } from '../middleware/errorHandler';
import { DocumentStatus } from '@prisma/client';
import pdfParse from 'pdf-parse';

export interface UploadDocumentInput {
  file: Buffer;
  originalName: string;
  mimeType: string;
  organizationId: string;
  uploadedById: string;
  storeHashForAudit?: boolean;
}

export interface DocumentResult {
  id: string;
  filename: string;
  originalName: string;
  mimeType: string;
  fileSize: number;
  status: DocumentStatus;
  contentHash: string | null;
  createdAt: Date;
  validations?: {
    id: string;
    status: string;
    result: string | null;
    score: number | null;
    createdAt: Date;
  }[];
}

export class DocumentService {
  private readonly SUPPORTED_MIME_TYPES = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'text/plain',
    'text/csv',
    'application/json',
  ];

  private readonly MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB

  async uploadDocument(input: UploadDocumentInput): Promise<DocumentResult> {
    // Validate file
    if (!this.SUPPORTED_MIME_TYPES.includes(input.mimeType)) {
      throw new AppError(`Unsupported file type: ${input.mimeType}`, 400);
    }

    if (input.file.length > this.MAX_FILE_SIZE) {
      throw new AppError('File size exceeds maximum allowed (50MB)', 400);
    }

    // Check organization document limit
    const org = await prisma.organization.findUnique({
      where: { id: input.organizationId },
    });

    if (!org) {
      throw new NotFoundError('Organization not found');
    }

    if (org.documentsUsedThisMonth >= org.monthlyDocumentLimit) {
      throw new ForbiddenError('Monthly document limit reached. Please upgrade your subscription.');
    }

    // Generate content hash
    const contentHash = generateDocumentHash(input.file);

    // Upload to storage
    const uploadResult = await storageService.uploadDocument(
      input.file,
      input.originalName,
      input.mimeType,
      input.organizationId
    );

    // Extract text content
    let extractedText: string | null = null;
    try {
      extractedText = await this.extractTextContent(input.file, input.mimeType);
    } catch (error) {
      logger.warn(`Failed to extract text from document: ${error}`);
    }

    // Create document record
    const document = await prisma.document.create({
      data: {
        filename: uploadResult.key.split('/').pop() || input.originalName,
        originalName: input.originalName,
        mimeType: input.mimeType,
        fileSize: input.file.length,
        storageKey: uploadResult.key,
        contentHash: input.storeHashForAudit ? contentHash : null,
        hashStoredForAudit: input.storeHashForAudit || false,
        status: 'PENDING',
        extractedText,
        organizationId: input.organizationId,
        uploadedById: input.uploadedById,
      },
    });

    // Increment document usage
    await prisma.organization.update({
      where: { id: input.organizationId },
      data: {
        documentsUsedThisMonth: { increment: 1 },
      },
    });

    // Create audit log
    await auditService.log({
      action: 'DOCUMENT_UPLOADED',
      entityType: 'document',
      entityId: document.id,
      organizationId: input.organizationId,
      userId: input.uploadedById,
      documentId: document.id,
      documentHash: input.storeHashForAudit ? contentHash : undefined,
      details: {
        originalName: input.originalName,
        mimeType: input.mimeType,
        fileSize: input.file.length,
      },
    });

    logger.info(`Document uploaded: ${document.id} by user ${input.uploadedById}`);

    return {
      id: document.id,
      filename: document.filename,
      originalName: document.originalName,
      mimeType: document.mimeType,
      fileSize: document.fileSize,
      status: document.status,
      contentHash: document.contentHash,
      createdAt: document.createdAt,
    };
  }

  async getDocument(
    documentId: string,
    organizationId: string
  ): Promise<DocumentResult | null> {
    const document = await prisma.document.findFirst({
      where: {
        id: documentId,
        organizationId,
      },
      include: {
        validations: {
          orderBy: { createdAt: 'desc' },
          take: 10,
          select: {
            id: true,
            status: true,
            result: true,
            score: true,
            createdAt: true,
          },
        },
      },
    });

    if (!document) {
      return null;
    }

    return {
      id: document.id,
      filename: document.filename,
      originalName: document.originalName,
      mimeType: document.mimeType,
      fileSize: document.fileSize,
      status: document.status,
      contentHash: document.contentHash,
      createdAt: document.createdAt,
      validations: document.validations,
    };
  }

  async listDocuments(
    organizationId: string,
    options: {
      page?: number;
      limit?: number;
      status?: DocumentStatus;
      search?: string;
    } = {}
  ) {
    const { page = 1, limit = 20, status, search } = options;
    const skip = (page - 1) * limit;

    const where = {
      organizationId,
      ...(status && { status }),
      ...(search && {
        OR: [
          { originalName: { contains: search, mode: 'insensitive' as const } },
          { filename: { contains: search, mode: 'insensitive' as const } },
        ],
      }),
    };

    const [documents, total] = await Promise.all([
      prisma.document.findMany({
        where,
        orderBy: { createdAt: 'desc' },
        skip,
        take: limit,
        include: {
          uploadedBy: {
            select: { firstName: true, lastName: true, email: true },
          },
          validations: {
            orderBy: { createdAt: 'desc' },
            take: 1,
            select: {
              status: true,
              result: true,
              score: true,
            },
          },
        },
      }),
      prisma.document.count({ where }),
    ]);

    return {
      documents: documents.map((doc) => ({
        id: doc.id,
        filename: doc.filename,
        originalName: doc.originalName,
        mimeType: doc.mimeType,
        fileSize: doc.fileSize,
        status: doc.status,
        createdAt: doc.createdAt,
        uploadedBy: {
          name: `${doc.uploadedBy.firstName} ${doc.uploadedBy.lastName}`,
          email: doc.uploadedBy.email,
        },
        lastValidation: doc.validations[0] || null,
      })),
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    };
  }

  async deleteDocument(
    documentId: string,
    organizationId: string,
    userId: string
  ): Promise<void> {
    const document = await prisma.document.findFirst({
      where: {
        id: documentId,
        organizationId,
      },
    });

    if (!document) {
      throw new NotFoundError('Document not found');
    }

    // Delete from storage
    await storageService.deleteDocument(document.storageKey);

    // Delete from database (cascade will handle validations and audit logs)
    await prisma.document.delete({
      where: { id: documentId },
    });

    // Create audit log
    await auditService.log({
      action: 'DOCUMENT_DELETED',
      entityType: 'document',
      entityId: documentId,
      organizationId,
      userId,
      details: {
        originalName: document.originalName,
        deletedAt: new Date().toISOString(),
      },
    });

    logger.info(`Document deleted: ${documentId} by user ${userId}`);
  }

  async getDocumentContent(documentId: string, organizationId: string): Promise<string> {
    const document = await prisma.document.findFirst({
      where: {
        id: documentId,
        organizationId,
      },
    });

    if (!document) {
      throw new NotFoundError('Document not found');
    }

    if (document.extractedText) {
      return document.extractedText;
    }

    // Fetch and extract if not cached
    const fileBuffer = await storageService.getDocument(document.storageKey);
    const text = await this.extractTextContent(fileBuffer, document.mimeType);

    // Cache the extracted text
    await prisma.document.update({
      where: { id: documentId },
      data: { extractedText: text },
    });

    return text;
  }

  private async extractTextContent(file: Buffer, mimeType: string): Promise<string> {
    switch (mimeType) {
      case 'application/pdf':
        return this.extractPdfText(file);

      case 'text/plain':
      case 'text/csv':
      case 'application/json':
        return file.toString('utf-8');

      case 'application/msword':
      case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
        // For Word docs, we'd need a library like mammoth
        // For now, return a placeholder
        return '[Word document content - extraction requires additional processing]';

      default:
        throw new Error(`Unsupported mime type for text extraction: ${mimeType}`);
    }
  }

  private async extractPdfText(file: Buffer): Promise<string> {
    const data = await pdfParse(file);
    return data.text;
  }
}

export const documentService = new DocumentService();
