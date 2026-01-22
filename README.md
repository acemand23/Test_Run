# DocQC - Document Quality Control Validation Service

A cloud-based SaaS platform that enables companies to validate documents through LLM-powered quality control before sending to external entities.

## Features

- **Document Upload & Processing**: Secure document upload with support for multiple formats (PDF, DOCX, TXT, etc.)
- **LLM-Powered Validation**: Customizable QC prompts reviewed by AI models
- **Multi-LLM Support**: Plugin architecture supporting OpenAI, Anthropic, Azure OpenAI, and custom providers
- **Audit Logging**: Comprehensive logs with optional document hash storage for compliance
- **Results Dashboard**: Real-time validation results and analytics
- **Prompt Management**: Standard interface for creating and managing validation prompts
- **Secure Access**: Role-based authentication with SSO support
- **Subscription Billing**: Monthly subscription tiers with usage tracking

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        Frontend (React)                          │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────────┐   │
│  │ Dashboard │ │ Upload   │ │ Prompts  │ │ Settings/Billing │   │
│  └──────────┘ └──────────┘ └──────────┘ └──────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     API Gateway (Express)                        │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────────┐   │
│  │ Auth     │ │ Documents│ │ Prompts  │ │ Subscriptions    │   │
│  └──────────┘ └──────────┘ └──────────┘ └──────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        ▼                     ▼                     ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────────────┐
│ PostgreSQL   │    │ Redis Cache  │    │ S3/Object Storage    │
│ (Primary DB) │    │ (Sessions)   │    │ (Documents)          │
└──────────────┘    └──────────────┘    └──────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    LLM Provider Layer                            │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────────┐   │
│  │ OpenAI   │ │ Anthropic│ │ Azure    │ │ Custom Provider  │   │
│  └──────────┘ └──────────┘ └──────────┘ └──────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

## Project Structure

```
/
├── backend/                 # Node.js/Express API
│   ├── src/
│   │   ├── config/         # Configuration management
│   │   ├── controllers/    # Route controllers
│   │   ├── middleware/     # Express middleware
│   │   ├── models/         # Database models
│   │   ├── routes/         # API routes
│   │   ├── services/       # Business logic
│   │   │   └── llm/        # LLM provider integrations
│   │   ├── utils/          # Utility functions
│   │   └── index.ts        # Entry point
│   └── package.json
├── frontend/               # React dashboard
│   ├── src/
│   │   ├── components/     # React components
│   │   ├── pages/          # Page components
│   │   ├── hooks/          # Custom hooks
│   │   ├── services/       # API services
│   │   ├── store/          # State management
│   │   └── App.tsx
│   └── package.json
├── shared/                 # Shared types and utilities
│   └── types/
├── docker-compose.yml      # Local development setup
└── README.md
```

## Getting Started

### Prerequisites

- Node.js 18+
- PostgreSQL 14+
- Redis 7+

### Installation

```bash
# Install dependencies
npm install

# Set up environment variables
cp .env.example .env

# Run database migrations
npm run db:migrate

# Start development servers
npm run dev
```

## Environment Variables

```
# Database
DATABASE_URL=postgresql://user:password@localhost:5432/docqc

# Redis
REDIS_URL=redis://localhost:6379

# JWT Secret
JWT_SECRET=your-secret-key

# LLM Providers
OPENAI_API_KEY=your-openai-key
ANTHROPIC_API_KEY=your-anthropic-key

# Storage
AWS_S3_BUCKET=your-bucket
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key

# Stripe (Billing)
STRIPE_SECRET_KEY=your-stripe-key
STRIPE_WEBHOOK_SECRET=your-webhook-secret
```

## Subscription Tiers

| Tier | Documents/Month | LLM Providers | Price |
|------|-----------------|---------------|-------|
| Starter | 100 | OpenAI | $49/mo |
| Professional | 500 | All Standard | $149/mo |
| Enterprise | Unlimited | All + Custom | Custom |

## License

Proprietary - All rights reserved
