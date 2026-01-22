export interface LLMMessage {
  role: 'system' | 'user' | 'assistant';
  content: string;
}

export interface LLMCompletionRequest {
  messages: LLMMessage[];
  temperature?: number;
  maxTokens?: number;
  model?: string;
}

export interface LLMCompletionResponse {
  content: string;
  tokensUsed: {
    prompt: number;
    completion: number;
    total: number;
  };
  model: string;
  finishReason: string;
}

export interface LLMProviderConfig {
  apiKey?: string;
  endpoint?: string;
  deployment?: string;
  organizationId?: string;
  defaultModel?: string;
  maxRetries?: number;
  timeout?: number;
}

export interface ValidationRequest {
  documentContent: string;
  promptTemplate: string;
  documentMetadata?: {
    filename: string;
    mimeType: string;
    pageCount?: number;
  };
}

export interface ValidationResponse {
  result: 'PASS' | 'FAIL' | 'WARNING' | 'NEEDS_REVIEW';
  score?: number;
  findings: ValidationFinding[];
  summary: string;
  rawResponse: string;
  tokensUsed: number;
  processingTimeMs: number;
}

export interface ValidationFinding {
  severity: 'critical' | 'major' | 'minor' | 'info';
  category: string;
  description: string;
  location?: string;
  suggestion?: string;
}

export abstract class BaseLLMProvider {
  protected config: LLMProviderConfig;
  protected providerName: string;

  constructor(config: LLMProviderConfig, providerName: string) {
    this.config = config;
    this.providerName = providerName;
  }

  abstract getAvailableModels(): string[];
  abstract complete(request: LLMCompletionRequest): Promise<LLMCompletionResponse>;

  async validateDocument(request: ValidationRequest): Promise<ValidationResponse> {
    const startTime = Date.now();

    const systemPrompt = this.buildSystemPrompt();
    const userPrompt = this.buildUserPrompt(request);

    const response = await this.complete({
      messages: [
        { role: 'system', content: systemPrompt },
        { role: 'user', content: userPrompt },
      ],
      temperature: 0.1,
      maxTokens: 4096,
    });

    const processingTimeMs = Date.now() - startTime;

    return this.parseValidationResponse(response, processingTimeMs);
  }

  protected buildSystemPrompt(): string {
    return `You are a document quality control specialist. Your job is to review documents and identify any issues based on the provided validation criteria.

You must respond with a JSON object in the following format:
{
  "result": "PASS" | "FAIL" | "WARNING" | "NEEDS_REVIEW",
  "score": <number 0-100>,
  "summary": "<brief summary of the validation result>",
  "findings": [
    {
      "severity": "critical" | "major" | "minor" | "info",
      "category": "<category of the issue>",
      "description": "<detailed description of the issue>",
      "location": "<where in the document this issue was found, if applicable>",
      "suggestion": "<how to fix this issue, if applicable>"
    }
  ]
}

Scoring guidelines:
- PASS (score >= 90): Document meets all criteria with no significant issues
- WARNING (score 70-89): Document has minor issues that should be reviewed
- NEEDS_REVIEW (score 50-69): Document has issues that require human review
- FAIL (score < 50): Document has critical issues and should not proceed

Be thorough but objective. Only flag actual issues found in the document.`;
  }

  protected buildUserPrompt(request: ValidationRequest): string {
    let prompt = `## Validation Criteria\n\n${request.promptTemplate}\n\n`;

    if (request.documentMetadata) {
      prompt += `## Document Information\n`;
      prompt += `- Filename: ${request.documentMetadata.filename}\n`;
      prompt += `- Type: ${request.documentMetadata.mimeType}\n`;
      if (request.documentMetadata.pageCount) {
        prompt += `- Pages: ${request.documentMetadata.pageCount}\n`;
      }
      prompt += '\n';
    }

    prompt += `## Document Content\n\n${request.documentContent}`;

    return prompt;
  }

  protected parseValidationResponse(
    response: LLMCompletionResponse,
    processingTimeMs: number
  ): ValidationResponse {
    try {
      // Extract JSON from the response
      const jsonMatch = response.content.match(/\{[\s\S]*\}/);
      if (!jsonMatch) {
        throw new Error('No JSON found in response');
      }

      const parsed = JSON.parse(jsonMatch[0]);

      return {
        result: parsed.result || 'NEEDS_REVIEW',
        score: parsed.score,
        findings: parsed.findings || [],
        summary: parsed.summary || 'Validation completed',
        rawResponse: response.content,
        tokensUsed: response.tokensUsed.total,
        processingTimeMs,
      };
    } catch {
      // If parsing fails, return a default response
      return {
        result: 'NEEDS_REVIEW',
        score: undefined,
        findings: [{
          severity: 'info',
          category: 'parsing',
          description: 'Could not parse structured response from LLM',
        }],
        summary: response.content.substring(0, 500),
        rawResponse: response.content,
        tokensUsed: response.tokensUsed.total,
        processingTimeMs,
      };
    }
  }
}
