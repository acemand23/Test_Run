import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import { promptsApi } from '../services/api';
import clsx from 'clsx';

interface PromptForm {
  name: string;
  description: string;
  promptTemplate: string;
  category: string;
  isDefault: boolean;
}

export default function Prompts() {
  const [showModal, setShowModal] = useState(false);
  const [editingPrompt, setEditingPrompt] = useState<any>(null);
  const queryClient = useQueryClient();

  const { register, handleSubmit, reset, setValue, formState: { errors } } = useForm<PromptForm>();

  const { data, isLoading } = useQuery({
    queryKey: ['prompts'],
    queryFn: () => promptsApi.list().then((r) => r.data),
  });

  const createMutation = useMutation({
    mutationFn: (data: PromptForm) => promptsApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['prompts'] });
      toast.success('Prompt created');
      closeModal();
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.error || 'Failed to create prompt');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: string; data: Partial<PromptForm> }) =>
      promptsApi.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['prompts'] });
      toast.success('Prompt updated');
      closeModal();
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.error || 'Failed to update prompt');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: string) => promptsApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['prompts'] });
      toast.success('Prompt deleted');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.error || 'Failed to delete prompt');
    },
  });

  const openModal = (prompt?: any) => {
    if (prompt) {
      setEditingPrompt(prompt);
      setValue('name', prompt.name);
      setValue('description', prompt.description || '');
      setValue('promptTemplate', prompt.promptTemplate);
      setValue('category', prompt.category || '');
      setValue('isDefault', prompt.isDefault);
    } else {
      setEditingPrompt(null);
      reset();
    }
    setShowModal(true);
  };

  const closeModal = () => {
    setShowModal(false);
    setEditingPrompt(null);
    reset();
  };

  const onSubmit = (data: PromptForm) => {
    if (editingPrompt) {
      updateMutation.mutate({ id: editingPrompt.id, data });
    } else {
      createMutation.mutate(data);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Prompt Templates</h1>
          <p className="text-gray-500">Manage your QC validation prompts</p>
        </div>
        <button onClick={() => openModal()} className="btn-primary">
          <PlusIcon className="h-4 w-4 mr-2" />
          New Prompt
        </button>
      </div>

      {/* Prompts List */}
      <div className="grid gap-4">
        {isLoading ? (
          [1, 2, 3].map((i) => (
            <div key={i} className="card animate-pulse">
              <div className="h-6 bg-gray-200 rounded w-1/4 mb-2" />
              <div className="h-4 bg-gray-200 rounded w-1/2" />
            </div>
          ))
        ) : data?.prompts?.length ? (
          data.prompts.map((prompt: any) => (
            <div
              key={prompt.id}
              className={clsx(
                'card',
                !prompt.isActive && 'opacity-60'
              )}
            >
              <div className="flex items-start justify-between">
                <div className="flex-1">
                  <div className="flex items-center gap-2">
                    <h3 className="text-lg font-medium text-gray-900">{prompt.name}</h3>
                    {prompt.isDefault && (
                      <span className="px-2 py-0.5 text-xs font-medium bg-primary-100 text-primary-700 rounded-full">
                        Default
                      </span>
                    )}
                    {!prompt.isActive && (
                      <span className="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">
                        Inactive
                      </span>
                    )}
                  </div>
                  {prompt.description && (
                    <p className="text-sm text-gray-500 mt-1">{prompt.description}</p>
                  )}
                  {prompt.category && (
                    <span className="inline-block mt-2 px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">
                      {prompt.category}
                    </span>
                  )}
                </div>
                <div className="flex items-center gap-2">
                  <button
                    onClick={() => openModal(prompt)}
                    className="p-2 text-gray-400 hover:text-gray-600"
                  >
                    <PencilIcon className="h-5 w-5" />
                  </button>
                  <button
                    onClick={() => deleteMutation.mutate(prompt.id)}
                    className="p-2 text-gray-400 hover:text-red-600"
                  >
                    <TrashIcon className="h-5 w-5" />
                  </button>
                </div>
              </div>
              <div className="mt-4">
                <p className="text-xs text-gray-500 mb-1">Prompt Template:</p>
                <pre className="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg overflow-auto max-h-40">
                  {prompt.promptTemplate}
                </pre>
              </div>
              <div className="mt-3 text-xs text-gray-500">
                Used in {prompt.validationCount || 0} validations - v{prompt.version}
              </div>
            </div>
          ))
        ) : (
          <div className="card text-center py-12">
            <p className="text-gray-500">No prompts created yet</p>
            <button onClick={() => openModal()} className="mt-4 btn-primary">
              Create your first prompt
            </button>
          </div>
        )}
      </div>

      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 overflow-y-auto">
          <div className="flex min-h-screen items-center justify-center p-4">
            <div
              className="fixed inset-0 bg-gray-500 bg-opacity-75"
              onClick={closeModal}
            />
            <div className="relative bg-white rounded-lg shadow-xl max-w-2xl w-full">
              <div className="flex items-center justify-between p-4 border-b">
                <h3 className="text-lg font-medium">
                  {editingPrompt ? 'Edit Prompt' : 'New Prompt'}
                </h3>
                <button onClick={closeModal}>
                  <XMarkIcon className="h-5 w-5 text-gray-400" />
                </button>
              </div>
              <form onSubmit={handleSubmit(onSubmit)} className="p-4 space-y-4">
                <div>
                  <label className="label">Name</label>
                  <input
                    {...register('name', { required: 'Name is required' })}
                    className="input"
                    placeholder="General Document QC"
                  />
                  {errors.name && (
                    <p className="mt-1 text-sm text-red-600">{errors.name.message}</p>
                  )}
                </div>
                <div>
                  <label className="label">Description (optional)</label>
                  <input
                    {...register('description')}
                    className="input"
                    placeholder="Brief description of this prompt"
                  />
                </div>
                <div>
                  <label className="label">Category (optional)</label>
                  <input
                    {...register('category')}
                    className="input"
                    placeholder="e.g., legal, finance, general"
                  />
                </div>
                <div>
                  <label className="label">Prompt Template</label>
                  <textarea
                    {...register('promptTemplate', {
                      required: 'Prompt template is required',
                      minLength: { value: 10, message: 'Must be at least 10 characters' },
                    })}
                    rows={10}
                    className="input font-mono text-sm"
                    placeholder="Enter your validation criteria and instructions..."
                  />
                  {errors.promptTemplate && (
                    <p className="mt-1 text-sm text-red-600">{errors.promptTemplate.message}</p>
                  )}
                </div>
                <div className="flex items-center gap-2">
                  <input
                    {...register('isDefault')}
                    type="checkbox"
                    id="isDefault"
                    className="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                  />
                  <label htmlFor="isDefault" className="text-sm text-gray-700">
                    Set as default prompt
                  </label>
                </div>
                <div className="flex justify-end gap-3 pt-4 border-t">
                  <button type="button" onClick={closeModal} className="btn-secondary">
                    Cancel
                  </button>
                  <button
                    type="submit"
                    disabled={createMutation.isPending || updateMutation.isPending}
                    className="btn-primary"
                  >
                    {editingPrompt ? 'Update' : 'Create'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
