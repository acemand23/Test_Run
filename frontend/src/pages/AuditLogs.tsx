import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { auditApi } from '../services/api';
import { format } from 'date-fns';
import clsx from 'clsx';

const actionLabels: Record<string, string> = {
  DOCUMENT_UPLOADED: 'Document Uploaded',
  DOCUMENT_DELETED: 'Document Deleted',
  DOCUMENT_VIEWED: 'Document Viewed',
  VALIDATION_STARTED: 'Validation Started',
  VALIDATION_COMPLETED: 'Validation Completed',
  VALIDATION_FAILED: 'Validation Failed',
  PROMPT_CREATED: 'Prompt Created',
  PROMPT_UPDATED: 'Prompt Updated',
  PROMPT_DELETED: 'Prompt Deleted',
  USER_LOGIN: 'User Login',
  USER_LOGOUT: 'User Logout',
  USER_CREATED: 'User Created',
  USER_UPDATED: 'User Updated',
  USER_DELETED: 'User Deleted',
  ORG_SETTINGS_UPDATED: 'Settings Updated',
  LLM_CONFIG_UPDATED: 'LLM Config Updated',
  SUBSCRIPTION_CHANGED: 'Subscription Changed',
};

const actionColors: Record<string, string> = {
  DOCUMENT_UPLOADED: 'bg-green-100 text-green-800',
  DOCUMENT_DELETED: 'bg-red-100 text-red-800',
  VALIDATION_STARTED: 'bg-blue-100 text-blue-800',
  VALIDATION_COMPLETED: 'bg-green-100 text-green-800',
  VALIDATION_FAILED: 'bg-red-100 text-red-800',
  PROMPT_CREATED: 'bg-purple-100 text-purple-800',
  PROMPT_UPDATED: 'bg-purple-100 text-purple-800',
  USER_LOGIN: 'bg-gray-100 text-gray-800',
};

export default function AuditLogs() {
  const [page, setPage] = useState(1);
  const [filters, setFilters] = useState({
    action: '',
    entityType: '',
  });

  const { data, isLoading } = useQuery({
    queryKey: ['audit-logs', page, filters],
    queryFn: () => auditApi.query({ page, limit: 20, ...filters }).then((r) => r.data),
  });

  const { data: stats } = useQuery({
    queryKey: ['audit-stats'],
    queryFn: () => auditApi.stats(30).then((r) => r.data),
  });

  const entityTypes = ['document', 'validation', 'prompt', 'user', 'organization', 'llmConfig'];
  const actions = Object.keys(actionLabels);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Audit Logs</h1>
        <p className="text-gray-500">Track all activities in your organization</p>
      </div>

      {/* Stats */}
      {stats && (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          {Object.entries(stats.actionCounts as Record<string, number>)
            .slice(0, 4)
            .map(([action, count]) => (
              <div key={action} className="card">
                <p className="text-2xl font-bold">{count}</p>
                <p className="text-sm text-gray-500">{actionLabels[action] || action}</p>
              </div>
            ))}
        </div>
      )}

      {/* Filters */}
      <div className="flex gap-4 flex-wrap">
        <select
          value={filters.action}
          onChange={(e) => setFilters({ ...filters, action: e.target.value })}
          className="input w-auto"
        >
          <option value="">All Actions</option>
          {actions.map((action) => (
            <option key={action} value={action}>
              {actionLabels[action]}
            </option>
          ))}
        </select>
        <select
          value={filters.entityType}
          onChange={(e) => setFilters({ ...filters, entityType: e.target.value })}
          className="input w-auto"
        >
          <option value="">All Entity Types</option>
          {entityTypes.map((type) => (
            <option key={type} value={type}>
              {type}
            </option>
          ))}
        </select>
      </div>

      {/* Logs Table */}
      <div className="card overflow-hidden">
        {isLoading ? (
          <div className="space-y-4 p-4">
            {[1, 2, 3, 4, 5].map((i) => (
              <div key={i} className="animate-pulse flex gap-4">
                <div className="h-4 bg-gray-200 rounded w-1/6" />
                <div className="h-4 bg-gray-200 rounded w-1/4" />
                <div className="h-4 bg-gray-200 rounded w-1/3" />
              </div>
            ))}
          </div>
        ) : data?.logs?.length ? (
          <>
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Timestamp
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Action
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Entity
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    User
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Details
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {data.logs.map((log: any) => (
                  <tr key={log.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {format(new Date(log.createdAt), 'MMM d, yyyy HH:mm')}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span
                        className={clsx(
                          'px-2 py-1 text-xs font-medium rounded-full',
                          actionColors[log.action] || 'bg-gray-100 text-gray-800'
                        )}
                      >
                        {actionLabels[log.action] || log.action}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <span className="capitalize">{log.entityType}</span>
                      <span className="text-xs text-gray-400 ml-1">
                        ({log.entityId.slice(0, 8)}...)
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {log.user ? (
                        <span>{log.user.name}</span>
                      ) : (
                        <span className="text-gray-400">System</span>
                      )}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                      {log.details && typeof log.details === 'object' && (
                        <span className="text-xs font-mono">
                          {JSON.stringify(log.details).slice(0, 50)}...
                        </span>
                      )}
                      {log.documentHash && (
                        <span className="text-xs text-gray-400 block">
                          Hash: {log.documentHash.slice(0, 16)}...
                        </span>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>

            {/* Pagination */}
            {data.pagination && data.pagination.totalPages > 1 && (
              <div className="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <p className="text-sm text-gray-500">
                  Page {data.pagination.page} of {data.pagination.totalPages} ({data.pagination.total} logs)
                </p>
                <div className="flex gap-2">
                  <button
                    onClick={() => setPage((p) => Math.max(1, p - 1))}
                    disabled={page === 1}
                    className="btn-secondary"
                  >
                    Previous
                  </button>
                  <button
                    onClick={() => setPage((p) => p + 1)}
                    disabled={page >= data.pagination.totalPages}
                    className="btn-secondary"
                  >
                    Next
                  </button>
                </div>
              </div>
            )}
          </>
        ) : (
          <div className="text-center py-12">
            <p className="text-gray-500">No audit logs found</p>
          </div>
        )}
      </div>
    </div>
  );
}
