import bcrypt from 'bcryptjs';
import { prisma } from '../models/prisma';
import { generateToken, JWTPayload } from '../middleware/auth';
import { AppError, ConflictError, UnauthorizedError } from '../middleware/errorHandler';
import { logger } from '../utils/logger';
import { UserRole, SubscriptionTier } from '@prisma/client';

export interface RegisterOrganizationInput {
  organizationName: string;
  email: string;
  password: string;
  firstName: string;
  lastName: string;
}

export interface LoginInput {
  email: string;
  password: string;
}

export interface AuthResponse {
  token: string;
  user: {
    id: string;
    email: string;
    firstName: string;
    lastName: string;
    role: UserRole;
  };
  organization: {
    id: string;
    name: string;
    slug: string;
    subscriptionTier: SubscriptionTier;
  };
}

export interface InviteUserInput {
  email: string;
  firstName: string;
  lastName: string;
  role: UserRole;
  organizationId: string;
}

export class AuthService {
  private readonly SALT_ROUNDS = 12;

  async registerOrganization(input: RegisterOrganizationInput): Promise<AuthResponse> {
    const existingUser = await prisma.user.findUnique({
      where: { email: input.email },
    });

    if (existingUser) {
      throw new ConflictError('Email already registered');
    }

    const slug = this.generateSlug(input.organizationName);

    const existingOrg = await prisma.organization.findUnique({
      where: { slug },
    });

    if (existingOrg) {
      throw new ConflictError('Organization name already taken');
    }

    const passwordHash = await bcrypt.hash(input.password, this.SALT_ROUNDS);

    const result = await prisma.$transaction(async (tx) => {
      const organization = await tx.organization.create({
        data: {
          name: input.organizationName,
          slug,
          subscriptionTier: 'STARTER',
          subscriptionStatus: 'TRIALING',
        },
      });

      const user = await tx.user.create({
        data: {
          email: input.email,
          passwordHash,
          firstName: input.firstName,
          lastName: input.lastName,
          role: 'OWNER',
          organizationId: organization.id,
        },
      });

      // Create default LLM configs
      await tx.lLMConfig.createMany({
        data: [
          {
            provider: 'OPENAI',
            modelName: 'gpt-4-turbo-preview',
            displayName: 'GPT-4 Turbo',
            isEnabled: true,
            isDefault: true,
            organizationId: organization.id,
          },
          {
            provider: 'ANTHROPIC',
            modelName: 'claude-3-5-sonnet-20241022',
            displayName: 'Claude 3.5 Sonnet',
            isEnabled: true,
            isDefault: false,
            organizationId: organization.id,
          },
        ],
      });

      // Create default prompt
      await tx.prompt.create({
        data: {
          name: 'General Document QC',
          description: 'Standard quality control check for documents',
          promptTemplate: `Review this document for the following quality criteria:

1. **Completeness**: Check if all required sections are present
2. **Accuracy**: Verify information consistency and correctness
3. **Formatting**: Ensure proper formatting and structure
4. **Grammar & Spelling**: Check for language errors
5. **Compliance**: Flag any potential compliance issues

Provide detailed findings for any issues discovered.`,
          category: 'general',
          isActive: true,
          isDefault: true,
          organizationId: organization.id,
        },
      });

      return { organization, user };
    });

    const tokenPayload: JWTPayload = {
      userId: result.user.id,
      organizationId: result.organization.id,
      email: result.user.email,
      role: result.user.role,
    };

    const token = generateToken(tokenPayload);

    logger.info(`New organization registered: ${result.organization.name}`);

    return {
      token,
      user: {
        id: result.user.id,
        email: result.user.email,
        firstName: result.user.firstName,
        lastName: result.user.lastName,
        role: result.user.role,
      },
      organization: {
        id: result.organization.id,
        name: result.organization.name,
        slug: result.organization.slug,
        subscriptionTier: result.organization.subscriptionTier,
      },
    };
  }

  async login(input: LoginInput): Promise<AuthResponse> {
    const user = await prisma.user.findUnique({
      where: { email: input.email },
      include: {
        organization: true,
      },
    });

    if (!user) {
      throw new UnauthorizedError('Invalid email or password');
    }

    if (!user.isActive) {
      throw new UnauthorizedError('Account is disabled');
    }

    const isValidPassword = await bcrypt.compare(input.password, user.passwordHash);

    if (!isValidPassword) {
      throw new UnauthorizedError('Invalid email or password');
    }

    // Update last login
    await prisma.user.update({
      where: { id: user.id },
      data: { lastLoginAt: new Date() },
    });

    const tokenPayload: JWTPayload = {
      userId: user.id,
      organizationId: user.organizationId,
      email: user.email,
      role: user.role,
    };

    const token = generateToken(tokenPayload);

    logger.info(`User logged in: ${user.email}`);

    return {
      token,
      user: {
        id: user.id,
        email: user.email,
        firstName: user.firstName,
        lastName: user.lastName,
        role: user.role,
      },
      organization: {
        id: user.organization.id,
        name: user.organization.name,
        slug: user.organization.slug,
        subscriptionTier: user.organization.subscriptionTier,
      },
    };
  }

  async inviteUser(input: InviteUserInput, invitedById: string): Promise<{ id: string; email: string }> {
    const existingUser = await prisma.user.findFirst({
      where: {
        email: input.email,
        organizationId: input.organizationId,
      },
    });

    if (existingUser) {
      throw new ConflictError('User already exists in this organization');
    }

    // Generate temporary password
    const tempPassword = this.generateTemporaryPassword();
    const passwordHash = await bcrypt.hash(tempPassword, this.SALT_ROUNDS);

    const user = await prisma.user.create({
      data: {
        email: input.email,
        passwordHash,
        firstName: input.firstName,
        lastName: input.lastName,
        role: input.role,
        organizationId: input.organizationId,
      },
    });

    // TODO: Send invitation email with temporary password
    logger.info(`User invited: ${input.email} to org ${input.organizationId} by ${invitedById}`);

    return {
      id: user.id,
      email: user.email,
    };
  }

  async changePassword(userId: string, currentPassword: string, newPassword: string): Promise<void> {
    const user = await prisma.user.findUnique({
      where: { id: userId },
    });

    if (!user) {
      throw new AppError('User not found', 404);
    }

    const isValidPassword = await bcrypt.compare(currentPassword, user.passwordHash);

    if (!isValidPassword) {
      throw new UnauthorizedError('Current password is incorrect');
    }

    const newPasswordHash = await bcrypt.hash(newPassword, this.SALT_ROUNDS);

    await prisma.user.update({
      where: { id: userId },
      data: { passwordHash: newPasswordHash },
    });

    logger.info(`Password changed for user: ${user.email}`);
  }

  private generateSlug(name: string): string {
    return name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-|-$/g, '')
      .substring(0, 50);
  }

  private generateTemporaryPassword(): string {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%';
    let password = '';
    for (let i = 0; i < 12; i++) {
      password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return password;
  }
}

export const authService = new AuthService();
