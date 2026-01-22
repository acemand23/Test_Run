import OpenAI, { AzureOpenAI } from 'openai';
import {
  BaseLLMProvider,
  LLMCompletionRequest,
  LLMCompletionResponse,
  LLMProviderConfig,
} from './types';
import { logger } from '../../utils/logger';

export class AzureOpenAIProvider extends BaseLLMProvider {
  private client: OpenAI;
  private deployment: string;

  constructor(config: LLMProviderConfig) {
    super(config, 'azure_openai');

    if (!config.apiKey || !config.endpoint || !config.deployment) {
      throw new Error('Azure OpenAI requires apiKey, endpoint, and deployment');
    }

    this.deployment = config.deployment;

    this.client = new AzureOpenAI({
      apiKey: config.apiKey,
      endpoint: config.endpoint,
      deployment: config.deployment,
      apiVersion: '2024-02-01',
      maxRetries: config.maxRetries || 3,
      timeout: config.timeout || 60000,
    });
  }

  getAvailableModels(): string[] {
    // Azure uses deployments, not model names directly
    return [this.deployment];
  }

  async complete(request: LLMCompletionRequest): Promise<LLMCompletionResponse> {
    logger.debug(`Azure OpenAI completion request: deployment=${this.deployment}`);

    try {
      const response = await this.client.chat.completions.create({
        model: this.deployment,
        messages: request.messages.map((m) => ({
          role: m.role,
          content: m.content,
        })),
        temperature: request.temperature ?? 0.7,
        max_tokens: request.maxTokens ?? 4096,
      });

      const choice = response.choices[0];
      const usage = response.usage;

      return {
        content: choice.message.content || '',
        tokensUsed: {
          prompt: usage?.prompt_tokens || 0,
          completion: usage?.completion_tokens || 0,
          total: usage?.total_tokens || 0,
        },
        model: this.deployment,
        finishReason: choice.finish_reason || 'unknown',
      };
    } catch (error) {
      logger.error('Azure OpenAI completion error:', error);
      throw error;
    }
  }
}
