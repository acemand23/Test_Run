import Anthropic from '@anthropic-ai/sdk';
import {
  BaseLLMProvider,
  LLMCompletionRequest,
  LLMCompletionResponse,
  LLMProviderConfig,
} from './types';
import { logger } from '../../utils/logger';

export class AnthropicProvider extends BaseLLMProvider {
  private client: Anthropic;
  private defaultModel = 'claude-3-5-sonnet-20241022';

  constructor(config: LLMProviderConfig) {
    super(config, 'anthropic');

    if (!config.apiKey) {
      throw new Error('Anthropic API key is required');
    }

    this.client = new Anthropic({
      apiKey: config.apiKey,
      maxRetries: config.maxRetries || 3,
      timeout: config.timeout || 60000,
    });

    if (config.defaultModel) {
      this.defaultModel = config.defaultModel;
    }
  }

  getAvailableModels(): string[] {
    return [
      'claude-3-5-sonnet-20241022',
      'claude-3-5-haiku-20241022',
      'claude-3-opus-20240229',
      'claude-3-sonnet-20240229',
      'claude-3-haiku-20240307',
    ];
  }

  async complete(request: LLMCompletionRequest): Promise<LLMCompletionResponse> {
    const model = request.model || this.defaultModel;

    logger.debug(`Anthropic completion request: model=${model}`);

    try {
      // Extract system message if present
      const systemMessage = request.messages.find((m) => m.role === 'system');
      const otherMessages = request.messages.filter((m) => m.role !== 'system');

      const response = await this.client.messages.create({
        model,
        max_tokens: request.maxTokens ?? 4096,
        system: systemMessage?.content,
        messages: otherMessages.map((m) => ({
          role: m.role as 'user' | 'assistant',
          content: m.content,
        })),
      });

      const textContent = response.content.find((c) => c.type === 'text');

      return {
        content: textContent?.type === 'text' ? textContent.text : '',
        tokensUsed: {
          prompt: response.usage.input_tokens,
          completion: response.usage.output_tokens,
          total: response.usage.input_tokens + response.usage.output_tokens,
        },
        model: response.model,
        finishReason: response.stop_reason || 'unknown',
      };
    } catch (error) {
      logger.error('Anthropic completion error:', error);
      throw error;
    }
  }
}
