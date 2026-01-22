import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import {
  DocumentTextIcon,
  CheckCircleIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  ClockIcon,
} from '@heroicons/react/24/outline';
import { validationsApi, subscriptionApi } from '../services/api';
import clsx from 'clsx';

const resultColors = {
  PASS: 'text-green-600 bg-green-100',
  FAIL: 'text-red-600 bg-red-100',
  WARNING: 'text-yellow-600 bg-yellow-100',
  NEEDS_REVIEW: 'text-blue-600 bg-blue-100',
};

const resultIcons = {
  PASS: CheckCircleIcon,
  FAIL: XCircleIcon,
  WARNING: ExclamationTriangleIcon,
  NEEDS_REVIEW: ClockIcon,
};

export default function Dashboard() {
  const { data: stats, isLoading: statsLoading } = useQuery({
    queryKey: ['validation-stats'],
    queryFn: () => validationsApi.stats(30).then((r) => r.data),
  });

  const { data: subscription, isLoading: subLoading } = useQuery({
    queryKey: ['subscription'],
    queryFn: () => subscriptionApi.getDetails().then((r) => r.data),
  });

  const { data: recentValidations, isLoading: validationsLoading } = useQuery({
    queryKey: ['recent-validations'],
    queryFn: () => validationsApi.list({ limit: 5 }).then((r) => r.data),
  });

  const totalValidations = stats
    ? Object.values(stats.resultCounts as Record<string, number>).reduce((a, b) => a + b, 0)
    : 0;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p className="text-gray-500">Overview of your document validation activity</p>
      </div>

      {/* Usage Card */}
      <div className="card">
        <h3 className="text-lg font-medium text-gray-900 mb-4">Monthly Usage</h3>
        {subLoading ? (
          <div className="animate-pulse h-8 bg-gray-200 rounded" />
        ) : subscription ? (
          <div>
            <div className="flex justify-between text-sm mb-2">
              <span className="text-gray-600">Documents processed</span>
              <span className="font-medium">
                {subscription.documentsUsed} / {subscription.documentsLimit === Infinity ? 'Unlimited' : subscription.documentsLimit}
              </span>
            </div>
            {subscription.documentsLimit !== Infinity && (
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div
                  className="bg-primary-600 h-2 rounded-full"
                  style={{
                    width: `${Math.min(
                      (subscription.documentsUsed / subscription.documentsLimit) * 100,
                      100
                    )}%`,
                  }}
                />
              </div>
            )}
            <p className="text-sm text-gray-500 mt-2">
              {subscription.tier} Plan - {subscription.status}
            </p>
          </div>
        ) : null}
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {['PASS', 'FAIL', 'WARNING', 'NEEDS_REVIEW'].map((result) => {
          const Icon = resultIcons[result as keyof typeof resultIcons];
          const count = stats?.resultCounts?.[result] || 0;
          return (
            <div key={result} className="card">
              <div className="flex items-center gap-4">
                <div
                  className={clsx(
                    'p-3 rounded-lg',
                    resultColors[result as keyof typeof resultColors]
                  )}
                >
                  <Icon className="h-6 w-6" />
                </div>
                <div>
                  <p className="text-2xl font-bold">{count}</p>
                  <p className="text-sm text-gray-500 capitalize">
                    {result.replace('_', ' ').toLowerCase()}
                  </p>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Recent Validations */}
      <div className="card">
        <div className="flex justify-between items-center mb-4">
          <h3 className="text-lg font-medium text-gray-900">Recent Validations</h3>
          <Link to="/documents" className="text-sm text-primary-600 hover:text-primary-500">
            View all
          </Link>
        </div>
        {validationsLoading ? (
          <div className="space-y-4">
            {[1, 2, 3].map((i) => (
              <div key={i} className="animate-pulse flex gap-4">
                <div className="h-10 w-10 bg-gray-200 rounded" />
                <div className="flex-1 space-y-2">
                  <div className="h-4 bg-gray-200 rounded w-3/4" />
                  <div className="h-3 bg-gray-200 rounded w-1/2" />
                </div>
              </div>
            ))}
          </div>
        ) : recentValidations?.validations?.length ? (
          <div className="divide-y divide-gray-200">
            {recentValidations.validations.map((validation: any) => {
              const Icon = validation.result
                ? resultIcons[validation.result as keyof typeof resultIcons]
                : ClockIcon;
              return (
                <div key={validation.id} className="py-4 flex items-center gap-4">
                  <div
                    className={clsx(
                      'p-2 rounded-lg',
                      validation.result
                        ? resultColors[validation.result as keyof typeof resultColors]
                        : 'text-gray-400 bg-gray-100'
                    )}
                  >
                    <Icon className="h-5 w-5" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-gray-900 truncate">
                      {validation.document?.originalName}
                    </p>
                    <p className="text-sm text-gray-500">
                      {validation.prompt?.name} - {validation.llmConfig?.displayName}
                    </p>
                  </div>
                  <div className="text-right">
                    {validation.score !== null && (
                      <p className="text-sm font-medium">{validation.score}%</p>
                    )}
                    <p className="text-xs text-gray-500">
                      {new Date(validation.createdAt).toLocaleDateString()}
                    </p>
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          <div className="text-center py-8">
            <DocumentTextIcon className="mx-auto h-12 w-12 text-gray-400" />
            <p className="mt-2 text-sm text-gray-500">No validations yet</p>
            <Link to="/documents" className="mt-4 btn-primary inline-flex">
              Upload your first document
            </Link>
          </div>
        )}
      </div>
    </div>
  );
}
