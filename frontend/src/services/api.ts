import axios from 'axios';
import { useAuthStore } from '../store/auth';

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
  },
});

api.interceptors.request.use((config) => {
  const token = useAuthStore.getState().token;
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      useAuthStore.getState().logout();
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Auth API
export const authApi = {
  register: (data: {
    organizationName: string;
    email: string;
    password: string;
    firstName: string;
    lastName: string;
  }) => api.post('/auth/register', data),

  login: (data: { email: string; password: string }) =>
    api.post('/auth/login', data),

  me: () => api.get('/auth/me'),
};

// Documents API
export const documentsApi = {
  upload: (file: File, storeHashForAudit: boolean = false) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('storeHashForAudit', String(storeHashForAudit));
    return api.post('/documents/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },

  list: (params?: { page?: number; limit?: number; status?: string; search?: string }) =>
    api.get('/documents', { params }),

  get: (id: string) => api.get(`/documents/${id}`),

  getContent: (id: string) => api.get(`/documents/${id}/content`),

  delete: (id: string) => api.delete(`/documents/${id}`),
};

// Validations API
export const validationsApi = {
  run: (data: { documentId: string; promptId: string; llmConfigId: string }) =>
    api.post('/validations', data),

  list: (params?: {
    page?: number;
    limit?: number;
    documentId?: string;
    status?: string;
    result?: string;
  }) => api.get('/validations', { params }),

  get: (id: string) => api.get(`/validations/${id}`),

  stats: (days?: number) => api.get('/validations/stats', { params: { days } }),
};

// Prompts API
export const promptsApi = {
  create: (data: {
    name: string;
    description?: string;
    promptTemplate: string;
    category?: string;
    isDefault?: boolean;
  }) => api.post('/prompts', data),

  update: (id: string, data: Partial<{
    name: string;
    description: string;
    promptTemplate: string;
    category: string;
    isActive: boolean;
    isDefault: boolean;
  }>) => api.put(`/prompts/${id}`, data),

  delete: (id: string) => api.delete(`/prompts/${id}`),

  list: (params?: { page?: number; limit?: number; category?: string; search?: string }) =>
    api.get('/prompts', { params }),

  get: (id: string) => api.get(`/prompts/${id}`),

  getCategories: () => api.get('/prompts/categories'),
};

// LLM Configs API
export const llmConfigsApi = {
  list: () => api.get('/llm-configs'),

  create: (data: {
    provider: string;
    modelName: string;
    displayName: string;
    apiKey?: string;
    isEnabled?: boolean;
    isDefault?: boolean;
  }) => api.post('/llm-configs', data),

  update: (id: string, data: Partial<{
    displayName: string;
    apiKey: string;
    isEnabled: boolean;
    isDefault: boolean;
  }>) => api.put(`/llm-configs/${id}`, data),

  delete: (id: string) => api.delete(`/llm-configs/${id}`),

  getProviders: () => api.get('/llm-configs/providers'),
};

// Subscription API
export const subscriptionApi = {
  getDetails: () => api.get('/subscription'),

  createCheckout: (tier: string) => api.post('/subscription/checkout', { tier }),

  createPortal: () => api.post('/subscription/portal'),
};

// Audit API
export const auditApi = {
  query: (params?: {
    page?: number;
    limit?: number;
    action?: string;
    entityType?: string;
    startDate?: string;
    endDate?: string;
  }) => api.get('/audit', { params }),

  getDocumentTrail: (documentId: string) => api.get(`/audit/documents/${documentId}`),

  stats: (days?: number) => api.get('/audit/stats', { params: { days } }),
};

export default api;
