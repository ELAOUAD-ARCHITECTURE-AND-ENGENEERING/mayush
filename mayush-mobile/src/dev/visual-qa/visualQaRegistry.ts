import React from 'react';
import { validateAddressDraft } from '../../commerce/checkoutState';
import { AddAddressFormScreen } from '../../screens/checkout/AddAddressFormScreen';
import { AddressSelectionScreen } from '../../screens/checkout/AddressSelectionScreen';
import { AuthenticationGateScreen } from '../../screens/checkout/AuthenticationGateScreen';
import { PhoneOtpVerificationScreen } from '../../screens/auth/PhoneOtpVerificationScreen';
import { AccountCreatedSuccessScreen } from '../../screens/auth/AccountCreatedSuccessScreen';
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
import { EditProfileScreen } from '../../screens/account/EditProfileScreen';
import { MyInformationScreen } from '../../screens/account/MyInformationScreen';
import { visualQaFixtures } from './visualQaFixtures';
import { VisualQaScreenKey } from './visualQaTypes';
import type { SystemStateVisualQaKey } from './visualQaTypes';
import { SystemStateScreen } from '../../components/system/SystemStateScreen';
import type { SystemStateId } from '../../components/system/systemStateCatalog';

export const systemStateVisualFixtures: Record<SystemStateVisualQaKey, { stateId: SystemStateId; language: 'fr' | 'ar'; progress?: number }> = {
  '10-access-denied-403-fr': { stateId: 'access-denied', language: 'fr' },
  '10-account-other-device-fr': { stateId: 'account-other-device', language: 'fr' },
  '10-account-sync-fr': { stateId: 'account-sync', language: 'fr' },
  '10-account-temporarily-blocked-fr': { stateId: 'account-temporarily-blocked', language: 'fr' },
  '10-app-initialization-fr': { stateId: 'app-initialization', language: 'fr', progress: 0.42 },
  '10-app-up-to-date-fr': { stateId: 'app-up-to-date', language: 'fr' },
  '10-background-sync-fr': { stateId: 'background-sync', language: 'fr', progress: 0.61 },
  '10-biometric-unavailable-fr': { stateId: 'biometric-unavailable', language: 'fr' },
  '10-camera-access-denied-fr': { stateId: 'camera-access-denied', language: 'fr' },
  '10-connection-restored-fr': { stateId: 'connection-restored', language: 'fr' },
  '10-connection-timeout-fr': { stateId: 'connection-timeout', language: 'fr' },
  '10-content-loading-ar': { stateId: 'content-loading', language: 'ar', progress: 0.56 },
  '10-content-loading-skeleton-fr': { stateId: 'content-loading-skeleton', language: 'fr' },
  '10-data-restoration-progress-fr': { stateId: 'data-restoration', language: 'fr', progress: 0.72 },
  '10-generic-error-fr': { stateId: 'generic-error', language: 'fr' },
  '10-installation-progress-fr': { stateId: 'installation-progress', language: 'fr', progress: 0.7 },
  '10-local-data-update-progress-fr': { stateId: 'local-data-update', language: 'fr', progress: 0.45 },
  '10-location-access-denied-fr': { stateId: 'location-access-denied', language: 'fr' },
  '10-maintenance-completed-ar': { stateId: 'maintenance-completed', language: 'ar' },
  '10-maintenance-completed-fr': { stateId: 'maintenance-completed', language: 'fr' },
  '10-notifications-disabled-fr': { stateId: 'notifications-disabled', language: 'fr' },
  '10-offline-ar': { stateId: 'offline', language: 'ar' },
  '10-offline-cached-content-fr': { stateId: 'offline-cached-content', language: 'fr' },
  '10-offline-fr': { stateId: 'offline', language: 'fr' },
  '10-page-not-found-fr': { stateId: 'page-not-found', language: 'fr' },
  '10-password-changed-fr': { stateId: 'password-changed', language: 'fr' },
  '10-photos-access-denied-fr': { stateId: 'photos-access-denied', language: 'fr' },
  '10-reconnection-progress-fr': { stateId: 'reconnection-progress', language: 'fr' },
  '10-refresh-progress-fr': { stateId: 'refresh-progress', language: 'fr', progress: 0.64 },
  '10-scheduled-maintenance-fr': { stateId: 'scheduled-maintenance', language: 'fr' },
  '10-server-error-500-fr': { stateId: 'server-error-500', language: 'fr' },
  '10-server-unavailable-ar': { stateId: 'server-unavailable', language: 'ar' },
  '10-server-unavailable-detailed-fr': { stateId: 'server-unavailable', language: 'fr' },
  '10-session-expired-ar': { stateId: 'session-expired', language: 'ar' },
  '10-session-expired-fr': { stateId: 'session-expired', language: 'fr' },
  '10-session-restoration-fr': { stateId: 'session-restoration', language: 'fr', progress: 0.62 },
  '10-session-restored-fr': { stateId: 'session-restored', language: 'fr' },
  '10-slow-connection-fr': { stateId: 'slow-connection', language: 'fr' },
  '10-too-many-attempts-fr': { stateId: 'too-many-attempts', language: 'fr' },
  '10-unusual-activity-verification-fr': { stateId: 'unusual-activity-verification', language: 'fr' },
  '10-update-available-fr': { stateId: 'update-available', language: 'fr' },
  '10-update-download-progress-fr': { stateId: 'update-download-progress', language: 'fr', progress: 0.55 },
  '10-update-failed-fr': { stateId: 'update-failed', language: 'fr' },
  '10-update-required-ar': { stateId: 'update-required', language: 'ar' },
  '10-update-required-fr': { stateId: 'update-required', language: 'fr' },
};

export const renderVisualQaScreen = (screenKey: VisualQaScreenKey): React.ReactElement | null => {
  const systemFixture = systemStateVisualFixtures[screenKey as SystemStateVisualQaKey];
  if (systemFixture) {
    const noop = () => undefined;
    return React.createElement(SystemStateScreen, {
      stateId: systemFixture.stateId,
      language: systemFixture.language,
      progress: systemFixture.progress,
      onPrimary: noop,
      onSecondary: noop,
    });
  }

  if ((screenKey as string) === 'edit-profile') {
    const noop = () => undefined;
    return React.createElement(EditProfileScreen, { onBack: noop, onSaveSuccess: noop });
  }

  if ((screenKey as string) === 'my-information') {
    const noop = () => undefined;
    return React.createElement(MyInformationScreen, { onBack: noop, onEditProfile: noop, onChangeEmail: noop, onChangePhone: noop });
  }

  if (
    (screenKey as string) === 'home-logged-in' ||
    (screenKey as string) === '02-home-logged-in-personalized-recommendations' ||
    (screenKey as string) === 'home-personalized'
  ) {
    const { HomeScreen } = require('../../screens/discovery/HomeScreen');
    return React.createElement(HomeScreen, {
      isAuthenticated: true,
      authenticatedUser: {
        id: '403',
        fullName: 'Mohamed Benali',
        emailOrPhone: 'mohamed@example.com',
      },
      orders: [
        {
          orderId: 'CMD-2024-00123',
          createdAt: '24 mai 2024',
          status: 'processing',
          lines: [],
          grandTotal: 249000,
          buyerId: '403',
          deliveryMethod: 'standard',
          paymentMethod: 'cod',
        },
      ],
      cartBadgeCount: 2,
    });
  }

  if (
    (screenKey as string) === 'home-guest' ||
    (screenKey as string) === '02-home-hero-new-arrivals-best-sellers-fr' ||
    (screenKey as string) === 'home'
  ) {
    const { HomeScreen } = require('../../screens/discovery/HomeScreen');
    return React.createElement(HomeScreen, {
      isAuthenticated: false,
    });
  }

  const fixture = visualQaFixtures[screenKey as keyof typeof visualQaFixtures];
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
    case '04-otp-phone-verification-fr':
      return React.createElement(PhoneOtpVerificationScreen, {
        targetAddressOverride: '+212 6 12 34 56 42',
        onBack: noop,
        onHome: noop,
        onSuccess: noop,
      });
    case '04-account-created-success-fr':
      return React.createElement(AccountCreatedSuccessScreen, {
        onContinue: noop,
        onCompleteProfile: noop,
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


