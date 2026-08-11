import React from 'react';
import { validateAddressDraft } from '../../commerce/checkoutState';
import { AddAddressFormScreen } from '../../screens/checkout/AddAddressFormScreen';
import { AddressSelectionScreen } from '../../screens/checkout/AddressSelectionScreen';
import { AuthenticationGateScreen } from '../../screens/checkout/AuthenticationGateScreen';
import { CheckoutSummaryScreen } from '../../screens/checkout/CheckoutSummaryScreen';
import { DeliveryMethodScreen } from '../../screens/checkout/DeliveryMethodScreen';
import { OrderProcessingScreen } from '../../screens/checkout/OrderProcessingScreen';
import { OrderReviewScreen } from '../../screens/checkout/OrderReviewScreen';
import {
  CashOnDeliveryConfirmationScreen,
  PaymentCancelledScreen,
  PaymentFailureScreen,
  PaymentVerificationScreen,
  SecurePaymentLoadingScreen,
  SecurePaymentRedirectScreen,
} from '../../screens/checkout/PaymentFlowScreens';
import { PaymentMethodScreen } from '../../screens/checkout/PaymentMethodScreen';
import { PaymentStepIntroScreen } from '../../screens/checkout/PaymentStepIntroScreen';
import { PaymentSuccessScreen } from '../../screens/checkout/PaymentSuccessScreen';
import { visualQaFixtures } from './visualQaFixtures';
import { VisualQaScreenKey } from './visualQaTypes';

export const renderVisualQaScreen = (screenKey: VisualQaScreenKey): React.ReactElement | null => {
  const fixture = visualQaFixtures[screenKey];
  if (!fixture) return null;

  const noop = () => undefined;

  switch (screenKey) {
    case '06-checkout-summary-4step-overview-v2-fr':
      return React.createElement(CheckoutSummaryScreen, {
        cart: fixture.cart,
        address: fixture.savedAddresses[0],
        deliveryMethod: fixture.deliveryMethod,
        paymentMethod: fixture.paymentMethod,
        onBack: noop,
        onChooseAddress: noop,
      });
    case '06-choose-address-saved-list-v2-fr':
      return React.createElement(AddressSelectionScreen, {
        addresses: fixture.savedAddresses,
        selectedAddressId: fixture.selectedAddressId,
        onBack: noop,
        onSelect: noop,
        onContinue: noop,
        onAddAddress: noop,
      });
    case '06-add-new-address-form-v2-fr':
      return React.createElement(AddAddressFormScreen, {
        draft: fixture.addressDraft,
        errors: {},
        onChange: noop,
        onBack: noop,
        onSave: noop,
      });
    case '06-add-address-validation-errors-fr':
      return React.createElement(AddAddressFormScreen, {
        draft: fixture.addressDraft,
        errors: validateAddressDraft(fixture.addressDraft),
        onChange: noop,
        onBack: noop,
        onSave: noop,
      });
    case '06-choose-delivery-standard-express-relay-v2-fr':
      return React.createElement(DeliveryMethodScreen, {
        address: fixture.savedAddresses[0],
        selectedMethod: fixture.deliveryMethod,
        onBack: noop,
        onSelect: noop,
        onContinue: noop,
      });
    case '06-choose-payment-cmi-cod-wallet-v2-fr':
      return React.createElement(PaymentMethodScreen, {
        totalMad: fixture.order.totalMad,
        selectedMethod: fixture.paymentMethod,
        processing: false,
        onBack: noop,
        onSelect: noop,
        onContinue: noop,
      });
    case '04-welcome-sign-in-create-account-guest-fr':
      return React.createElement(AuthenticationGateScreen, {
        onSignIn: noop,
        onCreateAccount: noop,
        onContinueAsGuest: noop,
      });
    case '06-payment-step-intro-step3-v2-fr':
      return React.createElement(PaymentStepIntroScreen, {
        order: fixture.order,
        onBack: noop,
        onContinue: noop,
      });
    case '06-secure-payment-redirect-v2-fr':
      return React.createElement(SecurePaymentRedirectScreen, {
        order: fixture.order,
        onContinue: noop,
        onCancel: noop,
      });
    case '06-secure-payment-redirect-loading-fr':
      return React.createElement(SecurePaymentLoadingScreen, {
        order: fixture.order,
        onContinue: noop,
      });
    case '06-payment-verification-processing-fr':
      return React.createElement(PaymentVerificationScreen, {
        order: fixture.order,
        onContinue: noop,
      });
    case '06-cash-on-delivery-confirmation-fr':
      return React.createElement(CashOnDeliveryConfirmationScreen, {
        order: fixture.order,
        onContinue: noop,
      });
    case '06-payment-confirmed-success-v2-fr':
      return React.createElement(PaymentSuccessScreen, {
        order: fixture.order,
        onNext: noop,
        onContinueShopping: noop,
      });
    case '06-payment-failed-retry-fr':
      return React.createElement(PaymentFailureScreen, {
        order: fixture.order,
        onRetry: noop,
        onChangePayment: noop,
      });
    case '06-payment-cancelled-resume-fr':
      return React.createElement(PaymentCancelledScreen, {
        order: fixture.order,
        onContinue: noop,
      });
    case '06-order-review-confirm-multi-vendor-v2-fr':
      return React.createElement(OrderReviewScreen, {
        cart: fixture.cart,
        address: fixture.savedAddresses[0],
        deliveryMethod: fixture.deliveryMethod,
        paymentMethod: fixture.paymentMethod,
        onBack: noop,
        onConfirm: noop,
      });
    case '06-order-processing-loading-state-fr':
      return React.createElement(OrderProcessingScreen, {
        order: fixture.order,
        onFinish: noop,
      });
    default:
      return null;
  }
};
