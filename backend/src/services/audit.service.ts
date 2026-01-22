import { prisma } from '../models/prisma';
import { logger } from '../utils/logger';
import { AuditAction } from '@prisma/client';

export interface AuditLogInput {
  action: AuditAction;
  entityType: string;
  entityId: string;
  organizationId: string;
  userId?: string;
  documentId?: string;
  validationId?: string;
  documentHash?: string;
  details?: Record<string, unknown>;
  ipAddress?: string;
  userAgent?: string;
}

export interface AuditLogQuery {
  organizationId: string;
  action?: AuditAction;
  entityType?: string;
  entityId?: string;
  userId?: string;
  startDate?: Date;
  endDate?: Date;
  page?: number;
  limit?: number;
}

export class AuditService {
  async log(input: AuditLogInput): Promise<void> {
    try {
      await prisma.auditLog.create({
        data: {
          action: input.action,
          entityType: input.entityType,
          entityId: input.entityId,
          organizationId: input.organizationId,
          userId: input.userId,
          documentId: input.documentId,
          validationId: input.validationId,
          documentHash: input.documentHash,
          details: input.details || {},
          ipAddress: input.ipAddress,
          userAgent: input.userAgent,
        },
      });

      logger.debug(`Audit log created: ${input.action} on ${input.entityType}:${input.entityId}`);
    } catch (error) {
      // Don't fail the main operation if audit logging fails
      logger.error('Failed to create audit log:', error);
    }
  }

  async query(query: AuditLogQuery) {
    const {
      organizationId,
      action,
      entityType,
      entityId,
      userId,
      startDate,
      endDate,
      page = 1,
      limit = 50,
    } = query;

    const skip = (page - 1) * limit;

    const where = {
      organizationId,
      ...(action && { action }),
      ...(entityType && { entityType }),
      ...(entityId && { entityId }),
      ...(userId && { userId }),
      ...(startDate || endDate
        ? {
            createdAt: {
              ...(startDate && { gte: startDate }),
              ...(endDate && { lte: endDate }),
            },
          }
        : {}),
    };

    const [logs, total] = await Promise.all([
      prisma.auditLog.findMany({
        where,
        orderBy: { createdAt: 'desc' },
        skip,
        take: limit,
        include: {
          user: {
            select: { firstName: true, lastName: true, email: true },
          },
        },
      }),
      prisma.auditLog.count({ where }),
    ]);

    return {
      logs: logs.map((log) => ({
        id: log.id,
        action: log.action,
        entityType: log.entityType,
        entityId: log.entityId,
        details: log.details,
        documentHash: log.documentHash,
        ipAddress: log.ipAddress,
        createdAt: log.createdAt,
        user: log.user
          ? {
              name: `${log.user.firstName} ${log.user.lastName}`,
              email: log.user.email,
            }
          : null,
      })),
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    };
  }

  async getDocumentAuditTrail(documentId: string, organizationId: string) {
    const logs = await prisma.auditLog.findMany({
      where: {
        documentId,
        organizationId,
      },
      orderBy: { createdAt: 'asc' },
      include: {
        user: {
          select: { firstName: true, lastName: true, email: true },
        },
        validation: {
          select: { status: true, result: true, score: true },
        },
      },
    });

    return logs.map((log) => ({
      id: log.id,
      action: log.action,
      details: log.details,
      documentHash: log.documentHash,
      createdAt: log.createdAt,
      user: log.user
        ? {
            name: `${log.user.firstName} ${log.user.lastName}`,
            email: log.user.email,
          }
        : null,
      validation: log.validation,
    }));
  }

  async getAuditStats(organizationId: string, days: number = 30) {
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - days);

    const stats = await prisma.auditLog.groupBy({
      by: ['action'],
      where: {
        organizationId,
        createdAt: { gte: startDate },
      },
      _count: true,
    });

    const dailyActivity = await prisma.$queryRaw<{ date: Date; count: bigint }[]>`
      SELECT DATE(created_at) as date, COUNT(*) as count
      FROM "AuditLog"
      WHERE organization_id = ${organizationId}
        AND created_at >= ${startDate}
      GROUP BY DATE(created_at)
      ORDER BY date ASC
    `;

    return {
      actionCounts: stats.reduce(
        (acc, s) => ({ ...acc, [s.action]: s._count }),
        {} as Record<string, number>
      ),
      dailyActivity: dailyActivity.map((d) => ({
        date: d.date.toISOString().split('T')[0],
        count: Number(d.count),
      })),
    };
  }
}

export const auditService = new AuditService();
