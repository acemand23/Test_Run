import { Routes, Route, NavLink } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import { subscriptionApi, llmConfigsApi } from '../services/api';
import { useAuthStore } from '../store/auth';
import clsx from 'clsx';

function LLMSettings() {
  const queryClient = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['llm-configs'],
    queryFn: () => llmConfigsApi.list().then((r) => r.data),
  });

  const toggleMutation = useMutation({
    mutationFn: ({ id, isEnabled }: { id: string; isEnabled: boolean }) =>
      llmConfigsApi.update(id, { isEnabled }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['llm-configs'] });
      toast.success('LLM configuration updated');
    },
  });

  const setDefaultMutation = useMutation({
    mutationFn: (id: string) => llmConfigsApi.update(id, { isDefault: true }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['llm-configs'] });
      toast.success('Default LLM updated');
    },
  });

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-lg font-medium text-gray-900">LLM Providers</h2>
        <p className="text-sm text-gray-500">Configure which LLM providers to use for validation</p>
      </div>

      {isLoading ? (
        <div className="space-y-4">
          {[1, 2].map((i) => (
            <div key={i} className="card animate-pulse">
              <div className="h-6 bg-gray-200 rounded w-1/4" />
            </div>
          ))}
        </div>
      ) : (
        <div className="space-y-4">
          {data?.configs?.map((config: any) => (
            <div key={config.id} className="card flex items-center justify-between">
              <div>
                <div className="flex items-center gap-2">
                  <h3 className="font-medium text-gray-900">{config.displayName}</h3>
                  {config.isDefault && (
                    <span className="px-2 py-0.5 text-xs bg-primary-100 text-primary-700 rounded-full">
                      Default
                    </span>
                  )}
                  {config.hasCustomApiKey && (
                    <span className="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full">
                      Custom Key
                    </span>
                  )}
                </div>
                <p className="text-sm text-gray-500">
                  {config.provider} - {config.modelName}
                </p>
              </div>
              <div className="flex items-center gap-4">
                {!config.isDefault && config.isEnabled && (
                  <button
                    onClick={() => setDefaultMutation.mutate(config.id)}
                    className="text-sm text-primary-600 hover:text-primary-700"
                  >
                    Set as Default
                  </button>
                )}
                <label className="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    checked={config.isEnabled}
                    onChange={(e) =>
                      toggleMutation.mutate({ id: config.id, isEnabled: e.target.checked })
                    }
                    className="sr-only peer"
                  />
                  <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                </label>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

function BillingSettings() {
  const { organization } = useAuthStore();

  const { data: subscription, isLoading } = useQuery({
    queryKey: ['subscription'],
    queryFn: () => subscriptionApi.getDetails().then((r) => r.data),
  });

  const checkoutMutation = useMutation({
    mutationFn: (tier: string) => subscriptionApi.createCheckout(tier),
    onSuccess: (response) => {
      window.location.href = response.data.url;
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.error || 'Failed to create checkout');
    },
  });

  const portalMutation = useMutation({
    mutationFn: () => subscriptionApi.createPortal(),
    onSuccess: (response) => {
      window.location.href = response.data.url;
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.error || 'Failed to open billing portal');
    },
  });

  const tiers = [
    {
      name: 'Starter',
      id: 'STARTER',
      price: '$49',
      documents: '100',
      features: ['OpenAI GPT-4', 'Basic prompts', 'Email support'],
    },
    {
      name: 'Professional',
      id: 'PROFESSIONAL',
      price: '$149',
      documents: '500',
      features: ['All LLM providers', 'Custom prompts', 'Audit logs', 'Priority support'],
    },
    {
      name: 'Enterprise',
      id: 'ENTERPRISE',
      price: 'Custom',
      documents: 'Unlimited',
      features: ['Custom LLM integration', 'SSO', 'Dedicated support', 'SLA'],
    },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-lg font-medium text-gray-900">Billing & Subscription</h2>
        <p className="text-sm text-gray-500">Manage your subscription and billing details</p>
      </div>

      {/* Current Plan */}
      {isLoading ? (
        <div className="card animate-pulse">
          <div className="h-6 bg-gray-200 rounded w-1/4 mb-2" />
          <div className="h-4 bg-gray-200 rounded w-1/2" />
        </div>
      ) : subscription ? (
        <div className="card">
          <h3 className="font-medium text-gray-900">Current Plan</h3>
          <p className="text-2xl font-bold text-primary-600 mt-2 capitalize">
            {subscription.tier.toLowerCase()}
          </p>
          <p className="text-sm text-gray-500 mt-1">
            Status: {subscription.status}
          </p>
          <div className="mt-4">
            <div className="flex justify-between text-sm mb-2">
              <span>Documents this month</span>
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
          </div>
          <button
            onClick={() => portalMutation.mutate()}
            className="btn-secondary mt-4"
          >
            Manage Billing
          </button>
        </div>
      ) : null}

      {/* Plans */}
      <div>
        <h3 className="font-medium text-gray-900 mb-4">Available Plans</h3>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {tiers.map((tier) => (
            <div
              key={tier.id}
              className={clsx(
                'card border-2',
                organization?.subscriptionTier === tier.id
                  ? 'border-primary-500'
                  : 'border-transparent'
              )}
            >
              <h4 className="font-medium text-gray-900">{tier.name}</h4>
              <p className="text-2xl font-bold mt-2">{tier.price}<span className="text-sm font-normal text-gray-500">/mo</span></p>
              <p className="text-sm text-gray-500 mt-1">{tier.documents} documents/month</p>
              <ul className="mt-4 space-y-2">
                {tier.features.map((feature) => (
                  <li key={feature} className="text-sm text-gray-600 flex items-center gap-2">
                    <svg className="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                    </svg>
                    {feature}
                  </li>
                ))}
              </ul>
              {organization?.subscriptionTier === tier.id ? (
                <button className="btn-secondary w-full mt-4" disabled>
                  Current Plan
                </button>
              ) : tier.id === 'ENTERPRISE' ? (
                <button className="btn-secondary w-full mt-4">Contact Sales</button>
              ) : (
                <button
                  onClick={() => checkoutMutation.mutate(tier.id)}
                  className="btn-primary w-full mt-4"
                >
                  Upgrade
                </button>
              )}
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

function OrganizationSettings() {
  const { organization, user } = useAuthStore();

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-lg font-medium text-gray-900">Organization</h2>
        <p className="text-sm text-gray-500">Manage your organization settings</p>
      </div>

      <div className="card">
        <div className="space-y-4">
          <div>
            <label className="label">Organization Name</label>
            <input
              type="text"
              value={organization?.name || ''}
              disabled
              className="input bg-gray-50"
            />
          </div>
          <div>
            <label className="label">Organization Slug</label>
            <input
              type="text"
              value={organization?.slug || ''}
              disabled
              className="input bg-gray-50"
            />
          </div>
        </div>
      </div>

      <div className="card">
        <h3 className="font-medium text-gray-900 mb-4">Your Profile</h3>
        <div className="space-y-4">
          <div>
            <label className="label">Email</label>
            <input
              type="email"
              value={user?.email || ''}
              disabled
              className="input bg-gray-50"
            />
          </div>
          <div>
            <label className="label">Role</label>
            <input
              type="text"
              value={user?.role || ''}
              disabled
              className="input bg-gray-50 capitalize"
            />
          </div>
        </div>
      </div>
    </div>
  );
}

export default function Settings() {
  const tabs = [
    { name: 'Organization', href: '/settings' },
    { name: 'LLM Providers', href: '/settings/llm' },
    { name: 'Billing', href: '/settings/billing' },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Settings</h1>
        <p className="text-gray-500">Manage your account and preferences</p>
      </div>

      <div className="border-b border-gray-200">
        <nav className="-mb-px flex space-x-8">
          {tabs.map((tab) => (
            <NavLink
              key={tab.name}
              to={tab.href}
              end={tab.href === '/settings'}
              className={({ isActive }) =>
                clsx(
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                  isActive
                    ? 'border-primary-500 text-primary-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                )
              }
            >
              {tab.name}
            </NavLink>
          ))}
        </nav>
      </div>

      <Routes>
        <Route index element={<OrganizationSettings />} />
        <Route path="llm" element={<LLMSettings />} />
        <Route path="billing" element={<BillingSettings />} />
      </Routes>
    </div>
  );
}
