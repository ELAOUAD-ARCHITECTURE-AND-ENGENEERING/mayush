import { CartState } from '../../commerce/cartState';
import { AddressDraft, DeliveryMethod, PaymentMethod, SavedAddress } from '../../commerce/checkoutState';
import { PrototypeOrder } from '../../commerce/orderState';
import type { SystemStateId } from '../../components/system/systemStateCatalog';

export const SYSTEM_STATE_VISUAL_QA_KEYS = [
  '10-access-denied-403-fr',
  '10-account-other-device-fr',
  '10-account-sync-fr',
  '10-account-temporarily-blocked-fr',
  '10-app-initialization-fr',
  '10-app-up-to-date-fr',
  '10-background-sync-fr',
  '10-biometric-unavailable-fr',
  '10-camera-access-denied-fr',
  '10-connection-restored-fr',
  '10-connection-timeout-fr',
  '10-content-loading-ar',
  '10-content-loading-skeleton-fr',
  '10-data-restoration-progress-fr',
  '10-generic-error-fr',
  '10-installation-progress-fr',
  '10-local-data-update-progress-fr',
  '10-location-access-denied-fr',
  '10-maintenance-completed-ar',
  '10-maintenance-completed-fr',
  '10-notifications-disabled-fr',
  '10-offline-ar',
  '10-offline-cached-content-fr',
  '10-offline-fr',
  '10-page-not-found-fr',
  '10-password-changed-fr',
  '10-photos-access-denied-fr',
  '10-reconnection-progress-fr',
  '10-refresh-progress-fr',
  '10-scheduled-maintenance-fr',
  '10-server-error-500-fr',
  '10-server-unavailable-ar',
  '10-server-unavailable-detailed-fr',
  '10-session-expired-ar',
  '10-session-expired-fr',
  '10-session-restoration-fr',
  '10-session-restored-fr',
  '10-slow-connection-fr',
  '10-too-many-attempts-fr',
  '10-unusual-activity-verification-fr',
  '10-update-available-fr',
  '10-update-download-progress-fr',
  '10-update-failed-fr',
  '10-update-required-ar',
  '10-update-required-fr',
] as const;

export type SystemStateVisualQaKey = typeof SYSTEM_STATE_VISUAL_QA_KEYS[number];

export interface SystemStateVisualFixture {
  screenKey: SystemStateVisualQaKey;
  stateId: SystemStateId;
  language: 'fr' | 'ar';
  viewport: { width: 393; height: 852 };
}

export type VisualQaScreenKey = SystemStateVisualQaKey
  | '06-checkout-summary-4step-overview-v2-fr'
  | '06-choose-address-saved-list-v2-fr'
  | '06-add-new-address-form-v2-fr'
  | '06-add-address-validation-errors-fr'
  | '06-choose-delivery-standard-express-relay-v2-fr'
  | '06-choose-payment-cmi-cod-wallet-v2-fr'
  | '04-welcome-sign-in-create-account-guest-fr'
  | '04-otp-phone-verification-fr'
  | '04-account-created-success-fr'
  | '06-payment-step-intro-step3-v2-fr'
  | '06-secure-payment-redirect-v2-fr'
  | '06-secure-payment-redirect-loading-fr'
  | '06-payment-verification-processing-fr'
  | '06-cash-on-delivery-confirmation-fr'
  | '06-payment-confirmed-success-v2-fr'
  | '06-payment-failed-retry-fr'
  | '06-payment-cancelled-resume-fr'
  | '06-order-review-confirm-multi-vendor-v2-fr'
  | '06-order-processing-loading-state-fr';

export interface VisualQaFixtureData {
  screenKey: VisualQaScreenKey;
  figmaNodeId: string;
  cart: CartState;
  savedAddresses: SavedAddress[];
  selectedAddressId: string;
  addressDraft: AddressDraft;
  deliveryMethod: DeliveryMethod;
  paymentMethod: PaymentMethod;
  order: PrototypeOrder;
}
