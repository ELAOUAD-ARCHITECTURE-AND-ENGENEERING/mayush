import { ScreenKey } from './screenKeys';
import { BuyerOrder } from '../commerce/orderState';
import { ProductMiniDto } from '../contracts/api/dto';

export interface DomainNavigationState {
  currentScreen: ScreenKey;
  selectedOrder?: BuyerOrder | null;
  selectedProduct?: ProductMiniDto | null;
  previousScreen?: ScreenKey | null;
}

export interface NavigationResult {
  nextScreen: ScreenKey;
  selectedOrder?: BuyerOrder | null;
  selectedProduct?: ProductMiniDto | null;
  previousScreen?: ScreenKey | null;
}

/**
  Order Domain Navigation Handler
 */
export const resolveOrderNavigation = (
  target: 'list' | 'detail' | 'cancel' | 'return' | 'tracking',
  order?: BuyerOrder | null
): NavigationResult => {
  if (target === 'list') {
    return { nextScreen: 'orders-list', selectedOrder: null };
  }
  if (!order) {
    return { nextScreen: 'orders-empty', selectedOrder: null };
  }
  if (target === 'cancel') {
    const isEligible = order.orderStatus === 'created' || order.orderStatus === 'confirmed' || order.orderStatus === 'preparing';
    return {
      nextScreen: isEligible ? 'order-cancel-reason' : 'order-cannot-cancel',
      selectedOrder: order,
    };
  }
  if (target === 'return') {
    return {
      nextScreen: order.orderStatus === 'delivered' ? 'order-return-selection' : 'legal-center',
      selectedOrder: order,
    };
  }
  if (target === 'tracking') {
    return {
      nextScreen: 'order-tracking',
      selectedOrder: order,
    };
  }

  const detailScreen: ScreenKey = order.orderStatus === 'delivered'
    ? 'order-detail-delivered'
    : (order.orderStatus === 'shipped' || order.deliveryStatus === 'shipped'
      ? 'order-detail-shipped'
      : 'order-detail-preparing');

  return {
    nextScreen: detailScreen,
    selectedOrder: order,
  };
};

/**
  Account Domain Navigation Handler
 */
export const resolveAccountNavigation = (
  action: 'profile' | 'addresses' | 'add-address' | 'settings' | 'security' | 'help' | 'about'
): NavigationResult => {
  switch (action) {
    case 'profile':
      return { nextScreen: 'edit-profile' };
    case 'addresses':
      return { nextScreen: 'my-addresses-v2' };
    case 'add-address':
      return { nextScreen: 'account-add-address-simple' };
    case 'settings':
      return { nextScreen: 'settings' };
    case 'security':
      return { nextScreen: 'account-security' };
    case 'help':
      return { nextScreen: 'help-center-home' };
    case 'about':
      return { nextScreen: 'about-mayush' };
    default:
      return { nextScreen: 'account' };
  }
};

/**
  Checkout Domain Navigation Handler
 */
export const resolveCheckoutStep = (
  step: 'summary' | 'address' | 'delivery' | 'payment' | 'review',
  hasAddresses: boolean
): NavigationResult => {
  if (step === 'summary') {
    return { nextScreen: hasAddresses ? 'checkout-summary' : 'no-saved-address' };
  }
  if (step === 'address') {
    return { nextScreen: hasAddresses ? 'address-selection' : 'no-saved-address' };
  }
  if (step === 'delivery') {
    return { nextScreen: 'delivery-method' };
  }
  if (step === 'payment') {
    return { nextScreen: 'payment-method' };
  }
  return { nextScreen: 'order-review' };
};
