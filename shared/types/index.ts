// Shared types between frontend and backend

export interface User {
  id: string;
  email: string;
  firstName: string;
  lastName: string;
  role: UserRole;
}

export type UserRole = 'OWNER' | 'ADMIN' | 'MEMBER' | 'VIEWER';

export interface Organization {
  id: string;
  name: string;
  slug: string;
  subscriptionTier: SubscriptionTier;
  subscriptionStatus: SubscriptionStatus;
}

export type SubscriptionTier = 'STARTER' | 'PROFESSIONAL' | 'ENTERPRISE';
export type SubscriptionStatus = 'ACTIVE' | 'PAST_DUE' | 'CANCELED' | 'TRIALING';

export interface Document {
  id: string;
  filename: string;
  originalName: string;
  mimeType: string;
  fileSize: number;
  status: DocumentStatus;
  contentHash: string | null;
  createdAt: string;
}

export type DocumentStatus = 'PENDING' | 'PROCESSING' | 'VALIDATED' | 'FAILED';

export interface Validation {
  id: string;
  status: ValidationStatus;
  result: ValidationResult | null;
  score: number | null;
  findings: ValidationFinding[];
  rawResponse: string | null;
  tokensUsed: number | null;
  processingTimeMs: number | null;
  errorMessage: string | null;
  createdAt: string;
  completedAt: string | null;
}

export type ValidationStatus = 'PENDING' | 'PROCESSING' | 'COMPLETED' | 'FAILED';
export type ValidationResult = 'PASS' | 'FAIL' | 'WARNING' | 'NEEDS_REVIEW';

export interface ValidationFinding {
  severity: 'critical' | 'major' | 'minor' | 'info';
  category: string;
  description: string;
  location?: string;
  suggestion?: string;
}

export interface Prompt {
  id: string;
  name: string;
  description: string | null;
  promptTemplate: string;
  category: string | null;
  isActive: boolean;
  isDefault: boolean;
  version: number;
  createdAt: string;
  updatedAt: string;
}

export interface LLMConfig {
  id: string;
  provider: LLMProvider;
  modelName: string;
  displayName: string;
  isEnabled: boolean;
  isDefault: boolean;
  hasCustomApiKey: boolean;
}

export type LLMProvider = 'OPENAI' | 'ANTHROPIC' | 'AZURE_OPENAI' | 'CUSTOM';

export interface AuditLog {
  id: string;
  action: AuditAction;
  entityType: string;
  entityId: string;
  details: Record<string, unknown> | null;
  documentHash: string | null;
  ipAddress: string | null;
  createdAt: string;
  user: {
    name: string;
    email: string;
  } | null;
}

export type AuditAction =
  | 'DOCUMENT_UPLOADED'
  | 'DOCUMENT_DELETED'
  | 'DOCUMENT_VIEWED'
  | 'VALIDATION_STARTED'
  | 'VALIDATION_COMPLETED'
  | 'VALIDATION_FAILED'
  | 'PROMPT_CREATED'
  | 'PROMPT_UPDATED'
  | 'PROMPT_DELETED'
  | 'USER_LOGIN'
  | 'USER_LOGOUT'
  | 'USER_CREATED'
  | 'USER_UPDATED'
  | 'USER_DELETED'
  | 'ORG_SETTINGS_UPDATED'
  | 'LLM_CONFIG_UPDATED'
  | 'SUBSCRIPTION_CHANGED';

// API Response types
export interface PaginatedResponse<T> {
  data: T[];
  pagination: {
    page: number;
    limit: number;
    total: number;
    totalPages: number;
  };
}

export interface ApiError {
  error: string;
  details?: Array<{
    field: string;
    message: string;
  }>;
}
