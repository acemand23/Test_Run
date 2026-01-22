import { Request, Response, NextFunction } from 'express';
import { z } from 'zod';
import { subscriptionService } from '../services/subscription.service';
import { AuthenticatedRequest } from '../middleware/auth';
import { config } from '../config';

const createCheckoutSchema = z.object({
  tier: z.enum(['STARTER', 'PROFESSIONAL', 'ENTERPRISE']),
});

export class SubscriptionController {
  async getDetails(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const details = await subscriptionService.getSubscriptionDetails(
        req.user.organizationId
      );

      res.json(details);
    } catch (error) {
      next(error);
    }
  }

  async createCheckout(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const { tier } = createCheckoutSchema.parse(req.body);

      const checkoutUrl = await subscriptionService.createCheckoutSession(
        req.user.organizationId,
        tier,
        `${config.frontendUrl}/settings/billing?success=true`,
        `${config.frontendUrl}/settings/billing?canceled=true`
      );

      res.json({ url: checkoutUrl });
    } catch (error) {
      next(error);
    }
  }

  async createPortal(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.user) {
        res.status(401).json({ error: 'Not authenticated' });
        return;
      }

      const portalUrl = await subscriptionService.createPortalSession(
        req.user.organizationId,
        `${config.frontendUrl}/settings/billing`
      );

      res.json({ url: portalUrl });
    } catch (error) {
      next(error);
    }
  }

  async handleWebhook(req: Request, res: Response, next: NextFunction): Promise<void> {
    try {
      const signature = req.headers['stripe-signature'] as string;

      if (!signature) {
        res.status(400).json({ error: 'Missing stripe-signature header' });
        return;
      }

      await subscriptionService.handleWebhook(req.body, signature);

      res.json({ received: true });
    } catch (error) {
      next(error);
    }
  }
}

export const subscriptionController = new SubscriptionController();
