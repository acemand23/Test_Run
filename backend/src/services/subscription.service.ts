import Stripe from 'stripe';
import { prisma } from '../models/prisma';
import { config } from '../config';
import { auditService } from './audit.service';
import { logger } from '../utils/logger';
import { AppError, NotFoundError } from '../middleware/errorHandler';
import { SubscriptionTier, SubscriptionStatus } from '@prisma/client';

const stripe = new Stripe(config.stripe.secretKey, {
  apiVersion: '2023-10-16',
});

export interface CreateSubscriptionInput {
  organizationId: string;
  tier: SubscriptionTier;
  paymentMethodId?: string;
}

export interface SubscriptionDetails {
  tier: SubscriptionTier;
  status: SubscriptionStatus;
  currentPeriodStart: Date | null;
  currentPeriodEnd: Date | null;
  documentsUsed: number;
  documentsLimit: number;
  cancelAtPeriodEnd: boolean;
}

export class SubscriptionService {
  private tierToPriceId: Record<SubscriptionTier, string> = {
    STARTER: config.stripe.prices.starter,
    PROFESSIONAL: config.stripe.prices.professional,
    ENTERPRISE: config.stripe.prices.enterprise,
  };

  private tierLimits: Record<SubscriptionTier, number> = {
    STARTER: 100,
    PROFESSIONAL: 500,
    ENTERPRISE: Infinity,
  };

  async createOrUpdateCustomer(
    organizationId: string,
    email: string,
    name: string
  ): Promise<string> {
    const org = await prisma.organization.findUnique({
      where: { id: organizationId },
    });

    if (!org) {
      throw new NotFoundError('Organization not found');
    }

    if (org.stripeCustomerId) {
      // Update existing customer
      await stripe.customers.update(org.stripeCustomerId, {
        email,
        name,
        metadata: { organizationId },
      });
      return org.stripeCustomerId;
    }

    // Create new customer
    const customer = await stripe.customers.create({
      email,
      name,
      metadata: { organizationId },
    });

    await prisma.organization.update({
      where: { id: organizationId },
      data: { stripeCustomerId: customer.id },
    });

    logger.info(`Stripe customer created for org ${organizationId}: ${customer.id}`);

    return customer.id;
  }

  async createCheckoutSession(
    organizationId: string,
    tier: SubscriptionTier,
    successUrl: string,
    cancelUrl: string
  ): Promise<string> {
    const org = await prisma.organization.findUnique({
      where: { id: organizationId },
    });

    if (!org || !org.stripeCustomerId) {
      throw new AppError('Organization must have a Stripe customer', 400);
    }

    const session = await stripe.checkout.sessions.create({
      customer: org.stripeCustomerId,
      payment_method_types: ['card'],
      line_items: [
        {
          price: this.tierToPriceId[tier],
          quantity: 1,
        },
      ],
      mode: 'subscription',
      success_url: successUrl,
      cancel_url: cancelUrl,
      metadata: {
        organizationId,
        tier,
      },
    });

    logger.info(`Checkout session created for org ${organizationId}: ${session.id}`);

    return session.url || '';
  }

  async createPortalSession(
    organizationId: string,
    returnUrl: string
  ): Promise<string> {
    const org = await prisma.organization.findUnique({
      where: { id: organizationId },
    });

    if (!org || !org.stripeCustomerId) {
      throw new AppError('Organization must have a Stripe customer', 400);
    }

    const session = await stripe.billingPortal.sessions.create({
      customer: org.stripeCustomerId,
      return_url: returnUrl,
    });

    return session.url;
  }

  async handleWebhook(payload: Buffer, signature: string): Promise<void> {
    let event: Stripe.Event;

    try {
      event = stripe.webhooks.constructEvent(
        payload,
        signature,
        config.stripe.webhookSecret
      );
    } catch (err) {
      throw new AppError('Invalid webhook signature', 400);
    }

    logger.info(`Processing Stripe webhook: ${event.type}`);

    switch (event.type) {
      case 'checkout.session.completed':
        await this.handleCheckoutComplete(event.data.object as Stripe.Checkout.Session);
        break;

      case 'customer.subscription.created':
      case 'customer.subscription.updated':
        await this.handleSubscriptionUpdate(event.data.object as Stripe.Subscription);
        break;

      case 'customer.subscription.deleted':
        await this.handleSubscriptionDeleted(event.data.object as Stripe.Subscription);
        break;

      case 'invoice.payment_failed':
        await this.handlePaymentFailed(event.data.object as Stripe.Invoice);
        break;

      default:
        logger.debug(`Unhandled webhook event: ${event.type}`);
    }
  }

  private async handleCheckoutComplete(session: Stripe.Checkout.Session): Promise<void> {
    const organizationId = session.metadata?.organizationId;
    const tier = session.metadata?.tier as SubscriptionTier;

    if (!organizationId || !tier) {
      logger.error('Missing metadata in checkout session');
      return;
    }

    await prisma.organization.update({
      where: { id: organizationId },
      data: {
        subscriptionTier: tier,
        subscriptionStatus: 'ACTIVE',
        monthlyDocumentLimit: this.tierLimits[tier],
      },
    });

    await auditService.log({
      action: 'SUBSCRIPTION_CHANGED',
      entityType: 'organization',
      entityId: organizationId,
      organizationId,
      details: {
        newTier: tier,
        event: 'checkout_completed',
      },
    });

    logger.info(`Subscription activated for org ${organizationId}: ${tier}`);
  }

  private async handleSubscriptionUpdate(subscription: Stripe.Subscription): Promise<void> {
    const customerId = subscription.customer as string;

    const org = await prisma.organization.findFirst({
      where: { stripeCustomerId: customerId },
    });

    if (!org) {
      logger.error(`Organization not found for customer: ${customerId}`);
      return;
    }

    const priceId = subscription.items.data[0]?.price.id;
    const tier = this.priceIdToTier(priceId);

    let status: SubscriptionStatus = 'ACTIVE';
    if (subscription.status === 'past_due') status = 'PAST_DUE';
    if (subscription.status === 'trialing') status = 'TRIALING';
    if (subscription.status === 'canceled') status = 'CANCELED';

    await prisma.organization.update({
      where: { id: org.id },
      data: {
        subscriptionTier: tier,
        subscriptionStatus: status,
        monthlyDocumentLimit: this.tierLimits[tier],
      },
    });

    await auditService.log({
      action: 'SUBSCRIPTION_CHANGED',
      entityType: 'organization',
      entityId: org.id,
      organizationId: org.id,
      details: {
        newTier: tier,
        newStatus: status,
        event: 'subscription_updated',
      },
    });

    logger.info(`Subscription updated for org ${org.id}: ${tier} (${status})`);
  }

  private async handleSubscriptionDeleted(subscription: Stripe.Subscription): Promise<void> {
    const customerId = subscription.customer as string;

    const org = await prisma.organization.findFirst({
      where: { stripeCustomerId: customerId },
    });

    if (!org) {
      return;
    }

    await prisma.organization.update({
      where: { id: org.id },
      data: {
        subscriptionStatus: 'CANCELED',
      },
    });

    await auditService.log({
      action: 'SUBSCRIPTION_CHANGED',
      entityType: 'organization',
      entityId: org.id,
      organizationId: org.id,
      details: {
        event: 'subscription_deleted',
      },
    });

    logger.info(`Subscription canceled for org ${org.id}`);
  }

  private async handlePaymentFailed(invoice: Stripe.Invoice): Promise<void> {
    const customerId = invoice.customer as string;

    const org = await prisma.organization.findFirst({
      where: { stripeCustomerId: customerId },
    });

    if (!org) {
      return;
    }

    await prisma.organization.update({
      where: { id: org.id },
      data: {
        subscriptionStatus: 'PAST_DUE',
      },
    });

    logger.warn(`Payment failed for org ${org.id}`);
  }

  private priceIdToTier(priceId: string): SubscriptionTier {
    for (const [tier, id] of Object.entries(this.tierToPriceId)) {
      if (id === priceId) {
        return tier as SubscriptionTier;
      }
    }
    return 'STARTER';
  }

  async getSubscriptionDetails(organizationId: string): Promise<SubscriptionDetails> {
    const org = await prisma.organization.findUnique({
      where: { id: organizationId },
    });

    if (!org) {
      throw new NotFoundError('Organization not found');
    }

    let currentPeriodStart: Date | null = null;
    let currentPeriodEnd: Date | null = null;
    let cancelAtPeriodEnd = false;

    if (org.stripeCustomerId) {
      try {
        const subscriptions = await stripe.subscriptions.list({
          customer: org.stripeCustomerId,
          limit: 1,
        });

        if (subscriptions.data.length > 0) {
          const sub = subscriptions.data[0];
          currentPeriodStart = new Date(sub.current_period_start * 1000);
          currentPeriodEnd = new Date(sub.current_period_end * 1000);
          cancelAtPeriodEnd = sub.cancel_at_period_end;
        }
      } catch (error) {
        logger.warn(`Failed to fetch Stripe subscription details: ${error}`);
      }
    }

    return {
      tier: org.subscriptionTier,
      status: org.subscriptionStatus,
      currentPeriodStart,
      currentPeriodEnd,
      documentsUsed: org.documentsUsedThisMonth,
      documentsLimit: org.monthlyDocumentLimit,
      cancelAtPeriodEnd,
    };
  }

  async resetMonthlyUsage(): Promise<void> {
    // This should be run by a cron job at the start of each billing cycle
    const result = await prisma.organization.updateMany({
      data: {
        documentsUsedThisMonth: 0,
        billingCycleStart: new Date(),
      },
    });

    logger.info(`Reset monthly usage for ${result.count} organizations`);
  }
}

export const subscriptionService = new SubscriptionService();
