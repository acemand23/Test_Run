import { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import {
  DocumentTextIcon,
  PlayIcon,
  TrashIcon,
  ArrowLeftIcon,
  CheckCircleIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
} from '@heroicons/react/24/outline';
import { documentsApi, validationsApi, promptsApi, llmConfigsApi } from '../services/api';
import clsx from 'clsx';

const resultColors = {
  PASS: 'text-green-600 bg-green-100',
  FAIL: 'text-red-600 bg-red-100',
  WARNING: 'text-yellow-600 bg-yellow-100',
  NEEDS_REVIEW: 'text-blue-600 bg-blue-100',
};

const severityColors = {
  critical: 'bg-red-100 text-red-800 border-red-200',
  major: 'bg-orange-100 text-orange-800 border-orange-200',
  minor: 'bg-yellow-100 text-yellow-800 border-yellow-200',
  info: 'bg-blue-100 text-blue-800 border-blue-200',
};

export default function DocumentDetail() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const [selectedPrompt, setSelectedPrompt] = useState('');
  const [selectedLLM, setSelectedLLM] = useState('');

  const { data: document, isLoading: docLoading } = useQuery({
    queryKey: ['document', id],
    queryFn: () => documentsApi.get(id!).then((r) => r.data),
    enabled: !!id,
  });

  const { data: prompts } = useQuery({
    queryKey: ['prompts'],
    queryFn: () => promptsApi.list({ isActive: true }).then((r) => r.data),
  });

  const { data: llmConfigs } = useQuery({
    queryKey: ['llm-configs'],
    queryFn: () => llmConfigsApi.list().then((r) => r.data),
  });

  const runValidationMutation = useMutation({
    mutationFn: () =>
      validationsApi.run({
        documentId: id!,
        promptId: selectedPrompt,
        llmConfigId: selectedLLM,
      }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['document', id] });
      toast.success('Validation started');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.error || 'Failed to start validation');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: () => documentsApi.delete(id!),
    onSuccess: () => {
      toast.success('Document deleted');
      navigate('/documents');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.error || 'Delete failed');
    },
  });

  // Set defaults when data loads
  if (prompts?.prompts?.length && !selectedPrompt) {
    const defaultPrompt = prompts.prompts.find((p: any) => p.isDefault) || prompts.prompts[0];
    setSelectedPrompt(defaultPrompt.id);
  }

  if (llmConfigs?.configs?.length && !selectedLLM) {
    const defaultConfig = llmConfigs.configs.find((c: any) => c.isDefault && c.isEnabled) ||
      llmConfigs.configs.find((c: any) => c.isEnabled);
    if (defaultConfig) setSelectedLLM(defaultConfig.id);
  }

  if (docLoading) {
    return (
      <div className="space-y-6">
        <div className="animate-pulse">
          <div className="h-8 bg-gray-200 rounded w-1/3 mb-4" />
          <div className="h-4 bg-gray-200 rounded w-1/4" />
        </div>
      </div>
    );
  }

  if (!document) {
    return (
      <div className="text-center py-12">
        <DocumentTextIcon className="mx-auto h-12 w-12 text-gray-400" />
        <p className="mt-2 text-gray-500">Document not found</p>
        <button onClick={() => navigate('/documents')} className="mt-4 btn-primary">
          Back to Documents
        </button>
      </div>
    );
  }

  const latestValidation = document.validations?.[0];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center gap-4">
        <button
          onClick={() => navigate('/documents')}
          className="text-gray-500 hover:text-gray-700"
        >
          <ArrowLeftIcon className="h-5 w-5" />
        </button>
        <div className="flex-1">
          <h1 className="text-2xl font-bold text-gray-900">{document.originalName}</h1>
          <p className="text-gray-500">
            Uploaded {new Date(document.createdAt).toLocaleString()}
          </p>
        </div>
        <button
          onClick={() => deleteMutation.mutate()}
          className="btn-danger"
        >
          <TrashIcon className="h-4 w-4 mr-2" />
          Delete
        </button>
      </div>

      {/* Info Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="card">
          <p className="text-sm text-gray-500">Status</p>
          <p className="text-lg font-medium capitalize">{document.status.toLowerCase()}</p>
        </div>
        <div className="card">
          <p className="text-sm text-gray-500">File Size</p>
          <p className="text-lg font-medium">{(document.fileSize / 1024).toFixed(1)} KB</p>
        </div>
        <div className="card">
          <p className="text-sm text-gray-500">Content Hash</p>
          <p className="text-sm font-mono truncate">{document.contentHash || 'Not stored'}</p>
        </div>
      </div>

      {/* Run Validation */}
      <div className="card">
        <h2 className="text-lg font-medium text-gray-900 mb-4">Run Validation</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label className="label">Prompt Template</label>
            <select
              value={selectedPrompt}
              onChange={(e) => setSelectedPrompt(e.target.value)}
              className="input"
            >
              {prompts?.prompts?.map((prompt: any) => (
                <option key={prompt.id} value={prompt.id}>
                  {prompt.name} {prompt.isDefault && '(Default)'}
                </option>
              ))}
            </select>
          </div>
          <div>
            <label className="label">LLM Provider</label>
            <select
              value={selectedLLM}
              onChange={(e) => setSelectedLLM(e.target.value)}
              className="input"
            >
              {llmConfigs?.configs
                ?.filter((c: any) => c.isEnabled)
                .map((config: any) => (
                  <option key={config.id} value={config.id}>
                    {config.displayName} {config.isDefault && '(Default)'}
                  </option>
                ))}
            </select>
          </div>
          <div className="flex items-end">
            <button
              onClick={() => runValidationMutation.mutate()}
              disabled={!selectedPrompt || !selectedLLM || runValidationMutation.isPending}
              className="btn-primary w-full"
            >
              <PlayIcon className="h-4 w-4 mr-2" />
              {runValidationMutation.isPending ? 'Starting...' : 'Run Validation'}
            </button>
          </div>
        </div>
      </div>

      {/* Latest Validation Result */}
      {latestValidation && (
        <div className="card">
          <h2 className="text-lg font-medium text-gray-900 mb-4">Latest Validation Result</h2>

          {latestValidation.status === 'COMPLETED' && latestValidation.result ? (
            <div className="space-y-4">
              {/* Result Summary */}
              <div className="flex items-center gap-4">
                <div
                  className={clsx(
                    'p-4 rounded-lg',
                    resultColors[latestValidation.result as keyof typeof resultColors]
                  )}
                >
                  {latestValidation.result === 'PASS' && <CheckCircleIcon className="h-8 w-8" />}
                  {latestValidation.result === 'FAIL' && <XCircleIcon className="h-8 w-8" />}
                  {(latestValidation.result === 'WARNING' ||
                    latestValidation.result === 'NEEDS_REVIEW') && (
                    <ExclamationTriangleIcon className="h-8 w-8" />
                  )}
                </div>
                <div>
                  <p className="text-2xl font-bold">{latestValidation.result}</p>
                  {latestValidation.score !== null && (
                    <p className="text-gray-500">Score: {latestValidation.score}%</p>
                  )}
                </div>
                <div className="ml-auto text-right text-sm text-gray-500">
                  <p>{latestValidation.llmConfig?.displayName}</p>
                  <p>{latestValidation.processingTimeMs}ms</p>
                </div>
              </div>

              {/* Findings */}
              {latestValidation.findings && Array.isArray(latestValidation.findings) && (
                <div>
                  <h3 className="text-sm font-medium text-gray-900 mb-2">Findings</h3>
                  <div className="space-y-2">
                    {latestValidation.findings.map((finding: any, index: number) => (
                      <div
                        key={index}
                        className={clsx(
                          'p-3 rounded-lg border',
                          severityColors[finding.severity as keyof typeof severityColors] ||
                            'bg-gray-100 text-gray-800 border-gray-200'
                        )}
                      >
                        <div className="flex items-center gap-2 mb-1">
                          <span className="text-xs font-medium uppercase">
                            {finding.severity}
                          </span>
                          <span className="text-xs">- {finding.category}</span>
                        </div>
                        <p className="text-sm">{finding.description}</p>
                        {finding.suggestion && (
                          <p className="text-sm mt-1 opacity-75">
                            Suggestion: {finding.suggestion}
                          </p>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          ) : (
            <div className="text-center py-8">
              <p className="text-gray-500">
                Status: {latestValidation.status}
                {latestValidation.errorMessage && (
                  <span className="text-red-500 block mt-2">
                    Error: {latestValidation.errorMessage}
                  </span>
                )}
              </p>
            </div>
          )}
        </div>
      )}

      {/* Validation History */}
      {document.validations && document.validations.length > 1 && (
        <div className="card">
          <h2 className="text-lg font-medium text-gray-900 mb-4">Validation History</h2>
          <div className="space-y-2">
            {document.validations.slice(1).map((v: any) => (
              <div
                key={v.id}
                className="flex items-center justify-between py-2 border-b border-gray-100 last:border-0"
              >
                <div className="flex items-center gap-3">
                  <span
                    className={clsx(
                      'px-2 py-1 text-xs font-medium rounded-full',
                      v.result
                        ? resultColors[v.result as keyof typeof resultColors]
                        : 'bg-gray-100 text-gray-800'
                    )}
                  >
                    {v.result || v.status}
                  </span>
                  {v.score !== null && <span className="text-sm text-gray-500">{v.score}%</span>}
                </div>
                <span className="text-sm text-gray-500">
                  {new Date(v.createdAt).toLocaleString()}
                </span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
