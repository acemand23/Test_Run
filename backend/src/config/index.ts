import dotenv from 'dotenv';
import { z } from 'zod';

dotenv.config();

const envSchema = z.object({
  NODE_ENV: z.enum(['development', 'production', 'test']).default('development'),
  PORT: z.string().default('3001'),
  API_URL: z.string().url().default('http://localhost:3001'),
  FRONTEND_URL: z.string().url().default('http://localhost:3000'),

  DATABASE_URL: z.string(),
  REDIS_URL: z.string().default('redis://localhost:6379'),

  JWT_SECRET: z.string().min(32),
  JWT_EXPIRES_IN: z.string().default('7d'),

  OPENAI_API_KEY: z.string().optional(),
  ANTHROPIC_API_KEY: z.string().optional(),
  AZURE_OPENAI_API_KEY: z.string().optional(),
  AZURE_OPENAI_ENDPOINT: z.string().optional(),
  AZURE_OPENAI_DEPLOYMENT: z.string().optional(),

  AWS_REGION: z.string().default('us-east-1'),
  AWS_S3_BUCKET: z.string(),
  AWS_ACCESS_KEY_ID: z.string(),
  AWS_SECRET_ACCESS_KEY: z.string(),

  STRIPE_SECRET_KEY: z.string(),
  STRIPE_WEBHOOK_SECRET: z.string(),
  STRIPE_STARTER_PRICE_ID: z.string(),
  STRIPE_PRO_PRICE_ID: z.string(),
  STRIPE_ENTERPRISE_PRICE_ID: z.string(),

  ENCRYPTION_KEY: z.string().min(32),
});

const parsed = envSchema.safeParse(process.env);

if (!parsed.success) {
  console.error('Invalid environment variables:', parsed.error.flatten().fieldErrors);
  throw new Error('Invalid environment variables');
}

export const config = {
  env: parsed.data.NODE_ENV,
  port: parseInt(parsed.data.PORT, 10),
  apiUrl: parsed.data.API_URL,
  frontendUrl: parsed.data.FRONTEND_URL,

  database: {
    url: parsed.data.DATABASE_URL,
  },

  redis: {
    url: parsed.data.REDIS_URL,
  },

  jwt: {
    secret: parsed.data.JWT_SECRET,
    expiresIn: parsed.data.JWT_EXPIRES_IN,
  },

  llm: {
    openai: {
      apiKey: parsed.data.OPENAI_API_KEY,
    },
    anthropic: {
      apiKey: parsed.data.ANTHROPIC_API_KEY,
    },
    azure: {
      apiKey: parsed.data.AZURE_OPENAI_API_KEY,
      endpoint: parsed.data.AZURE_OPENAI_ENDPOINT,
      deployment: parsed.data.AZURE_OPENAI_DEPLOYMENT,
    },
  },

  aws: {
    region: parsed.data.AWS_REGION,
    s3Bucket: parsed.data.AWS_S3_BUCKET,
    accessKeyId: parsed.data.AWS_ACCESS_KEY_ID,
    secretAccessKey: parsed.data.AWS_SECRET_ACCESS_KEY,
  },

  stripe: {
    secretKey: parsed.data.STRIPE_SECRET_KEY,
    webhookSecret: parsed.data.STRIPE_WEBHOOK_SECRET,
    prices: {
      starter: parsed.data.STRIPE_STARTER_PRICE_ID,
      professional: parsed.data.STRIPE_PRO_PRICE_ID,
      enterprise: parsed.data.STRIPE_ENTERPRISE_PRICE_ID,
    },
  },

  encryption: {
    key: parsed.data.ENCRYPTION_KEY,
  },

  subscriptionLimits: {
    STARTER: 100,
    PROFESSIONAL: 500,
    ENTERPRISE: Infinity,
  },
} as const;

export type Config = typeof config;
