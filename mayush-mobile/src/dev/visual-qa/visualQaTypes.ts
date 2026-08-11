import { CartState } from '../../commerce/cartState';
import { AddressDraft, DeliveryMethod, PaymentMethod, SavedAddress } from '../../commerce/checkoutState';
import { PrototypeOrder } from '../../commerce/orderState';

export type VisualQaScreenKey =
  | '06-checkout-summary-4step-overview-v2-fr'
  | '06-choose-address-saved-list-v2-fr'
  | '06-add-new-address-form-v2-fr'
  | '06-add-address-validation-errors-fr'
  | '06-choose-delivery-standard-express-relay-v2-fr'
  | '06-choose-payment-cmi-cod-wallet-v2-fr'
  | '04-welcome-sign-in-create-account-guest-fr'
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
