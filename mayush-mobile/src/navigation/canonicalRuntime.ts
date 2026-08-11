import { PaymentMethod } from '../commerce/checkoutState';

export type HomeCanonicalAction = 'promotions' | 'recently_viewed';
export type PaymentRecoveryAction = 'retry' | 'change_method';
export const NATIVE_VALIDATION_STATUS = 'pending' as const;

export const HOME_CANONICAL_CONTROLS = {
  promotions: { visible: true, destination: 'promotions-campaigns' },
  recently_viewed: { visible: true, destination: 'recently-viewed' },
} as const;

export const SETTINGS_CANONICAL_CONTROLS = {
  about_mayush: { visible: true, destination: 'about-mayush' },
} as const;

export const resolveHomeCanonicalDestination = (action: HomeCanonicalAction) =>
  HOME_CANONICAL_CONTROLS[action].destination;

export const resolveOrderProcessingDestination = (paymentMethod: PaymentMethod) => {
  if (paymentMethod === 'cmi') return 'payment-step-intro' as const;
  if (paymentMethod === 'cash-on-delivery') return 'cash-on-delivery-confirmation' as const;
  return 'payment-success' as const;
};

export const resolvePaymentVerificationDestination = (outcome: 'confirmed' | 'failed' | 'cancelled' | 'pending') => {
  if (outcome === 'confirmed') return 'payment-success' as const;
  if (outcome === 'failed') return 'payment-failed' as const;
  if (outcome === 'cancelled') return 'payment-cancelled' as const;
  return 'payment-confirmation-delayed' as const;
};

export const resolvePaymentFailureRecoveryDestination = (action: PaymentRecoveryAction) =>
  action === 'retry' ? 'payment-step-intro' as const : 'payment-method' as const;

export const resolveSettingsAboutDestination = () => SETTINGS_CANONICAL_CONTROLS.about_mayush.destination;
export const resolveAboutMayushBackDestination = () => 'settings' as const;
