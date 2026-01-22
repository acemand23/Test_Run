import OpenAI from 'openai';
import {
  BaseLLMProvider,
  LLMCompletionRequest,
  LLMCompletionResponse,
  LLMProviderConfig,
} from './types';
import { logger } from '../../utils/logger';

export class OpenAIProvider extends BaseLLMProvider {
  private client: OpenAI;
  private defaultModel = 'gpt-4-turbo-preview';

  constructor(config: LLMProviderConfig) {
    super(config, 'openai');

    if (!config.apiKey) {
      throw new Error('OpenAI API key is required');
    }

    this.client = new OpenAI({
      apiKey: config.apiKey,
      organization: config.organizationId,
      maxRetries: config.maxRetries || 3,
      timeout: config.timeout || 60000,
    });

    if (config.defaultModel) {
      this.defaultModel = config.defaultModel;
    }
  }

  getAvailableModels(): string[] {
    return [
      'gpt-4-turbo-preview',
      'gpt-4-turbo',
      'gpt-4',
      'gpt-4-32k',
      'gpt-3.5-turbo',
      'gpt-3.5-turbo-16k',
    ];
  }

  async complete(request: LLMCompletionRequest): Promise<LLMCompletionResponse> {
    const model = request.model || this.defaultModel;

    logger.debug(`OpenAI completion request: model=${model}`);

    try {
      const response = await this.client.chat.completions.create({
        model,
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
        model: response.model,
        finishReason: choice.finish_reason || 'unknown',
      };
    } catch (error) {
      logger.error('OpenAI completion error:', error);
      throw error;
    }
  }
}
