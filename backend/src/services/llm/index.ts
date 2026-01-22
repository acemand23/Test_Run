import { LLMProvider } from '@prisma/client';
import { BaseLLMProvider, LLMProviderConfig, ValidationRequest, ValidationResponse } from './types';
import { OpenAIProvider } from './openai';
import { AnthropicProvider } from './anthropic';
import { AzureOpenAIProvider } from './azure';
import { config } from '../../config';
import { logger } from '../../utils/logger';
import { decrypt } from '../../utils/hash';

export * from './types';
export { OpenAIProvider } from './openai';
export { AnthropicProvider } from './anthropic';
export { AzureOpenAIProvider } from './azure';

type ProviderFactory = (config: LLMProviderConfig) => BaseLLMProvider;

const providerFactories: Record<LLMProvider, ProviderFactory> = {
  OPENAI: (cfg) => new OpenAIProvider(cfg),
  ANTHROPIC: (cfg) => new AnthropicProvider(cfg),
  AZURE_OPENAI: (cfg) => new AzureOpenAIProvider(cfg),
  CUSTOM: () => {
    throw new Error('Custom provider not implemented');
  },
};

export interface LLMServiceConfig {
  provider: LLMProvider;
  modelName: string;
  encryptedApiKey?: string;
  settings?: Record<string, unknown>;
}

export class LLMService {
  private provider: BaseLLMProvider;
  private modelName: string;

  constructor(serviceConfig: LLMServiceConfig) {
    const providerConfig = this.buildProviderConfig(serviceConfig);
    const factory = providerFactories[serviceConfig.provider];

    if (!factory) {
      throw new Error(`Unknown LLM provider: ${serviceConfig.provider}`);
    }

    this.provider = factory(providerConfig);
    this.modelName = serviceConfig.modelName;
  }

  private buildProviderConfig(serviceConfig: LLMServiceConfig): LLMProviderConfig {
    // Start with default API keys from environment
    let apiKey: string | undefined;
    let endpoint: string | undefined;
    let deployment: string | undefined;

    switch (serviceConfig.provider) {
      case 'OPENAI':
        apiKey = config.llm.openai.apiKey;
        break;
      case 'ANTHROPIC':
        apiKey = config.llm.anthropic.apiKey;
        break;
      case 'AZURE_OPENAI':
        apiKey = config.llm.azure.apiKey;
        endpoint = config.llm.azure.endpoint;
        deployment = config.llm.azure.deployment;
        break;
    }

    // If organization provided their own key, use it (decrypt first)
    if (serviceConfig.encryptedApiKey) {
      try {
        apiKey = decrypt(serviceConfig.encryptedApiKey, config.encryption.key);
      } catch (error) {
        logger.error('Failed to decrypt API key:', error);
        throw new Error('Invalid API key configuration');
      }
    }

    return {
      apiKey,
      endpoint,
      deployment,
      defaultModel: serviceConfig.modelName,
      ...(serviceConfig.settings || {}),
    };
  }

  async validateDocument(request: ValidationRequest): Promise<ValidationResponse> {
    logger.info(`Starting document validation with model: ${this.modelName}`);
    return this.provider.validateDocument(request);
  }

  getAvailableModels(): string[] {
    return this.provider.getAvailableModels();
  }

  static getSupportedProviders(): LLMProvider[] {
    return ['OPENAI', 'ANTHROPIC', 'AZURE_OPENAI'];
  }

  static getDefaultModels(): Record<LLMProvider, string> {
    return {
      OPENAI: 'gpt-4-turbo-preview',
      ANTHROPIC: 'claude-3-5-sonnet-20241022',
      AZURE_OPENAI: 'gpt-4',
      CUSTOM: '',
    };
  }
}

// Factory function for creating LLM service from database config
export function createLLMServiceFromConfig(
  provider: LLMProvider,
  modelName: string,
  encryptedApiKey?: string,
  settings?: Record<string, unknown>
): LLMService {
  return new LLMService({
    provider,
    modelName,
    encryptedApiKey,
    settings,
  });
}
