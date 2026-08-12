import { authState, createCheckoutAuthReturnDestination } from '../src/commerce/authState';
import { addCartLine, createSelectedVariantCartLine, emptyCartState } from '../src/commerce/cartState';
import { createLocalCheckoutAttemptId, defaultSavedAddresses, loadCheckoutSession, saveCheckoutSession } from '../src/commerce/checkoutState';
import { createBuyerOrderRepository, OrderStorage } from '../src/commerce/orderState';
import { getSupportSelectableOrderIds } from '../src/commerce/supportState';
import { resolveNotificationBuyerOrder } from '../src/commerce/notificationPreferencesState';

class MemoryStorage implements OrderStorage {
  public readonly values = new Map<string, string>();
  async getItem(key: string) { return this.values.get(key) ?? null; }
  async setItem(key: string, value: string) { this.values.set(key, value); }
  async removeItem(key: string) { this.values.delete(key); }
}

export const runStep8B0OrderFoundationBehaviorTests = async (
  assert: (condition: boolean, message: string) => void,
): Promise<void> => {
  const variantLine = createSelectedVariantCartLine({
    productId: 101,
    name: 'Fauteuil Luna',
    variant: 'Velours-Vert-L 88 cm',
    quantity: 3,
    unitPriceMad: 2950,
  });
  const cart = addCartLine(emptyCartState(), variantLine);
  assert(cart.lines[0].variant === 'Velours-Vert-L 88 cm' && cart.lines[0].quantity === 3,
    'selected variant and quantity reach the cart model');

  const checkoutStorage = new MemoryStorage();
  const checkoutAttemptId = createLocalCheckoutAttemptId(1_786_406_400_000, 'stable');
  await saveCheckoutSession(checkoutStorage, {
    checkoutAttemptId,
    screen: 'payment-method',
    selectedAddressId: 'address-youssef',
    deliveryMethod: 'express',
    paymentMethod: 'wallet',
    savedAddresses: [{
      id: 'address-youssef', name: 'Youssef', phone: '+212 6 12 34 56 78',
      addressLine: 'Rue 1', city: 'Casablanca', postcode: '20000', zone: 'Centre',
    }],
  });
  const restoredCheckout = await loadCheckoutSession(checkoutStorage);
  assert(restoredCheckout?.checkoutAttemptId === checkoutAttemptId
    && restoredCheckout.deliveryMethod === 'express'
    && restoredCheckout.paymentMethod === 'wallet',
  'checkout persistence restores attempt, delivery, payment, address, and route state');

  authState.reset();
  authState.setReturnDestination(createCheckoutAuthReturnDestination(checkoutAttemptId));
  authState.completeLogin('buyer@example.ma');
  const authReturn = authState.getReturnDestination();
  assert(authState.isAuthenticated() && authReturn?.route === 'payment-method'
    && authReturn.params?.checkoutAttemptId === checkoutAttemptId,
  'wallet authentication preserves the checkout return destination');

  const orderStorage = new MemoryStorage();
  const repository = createBuyerOrderRepository(orderStorage, { seedOrders: [], initialSequence: 2000 });
  await repository.hydrate();
  const input = {
    cart,
    address: (restoredCheckout as any)?.savedAddresses?.[0] || defaultSavedAddresses[0],
    deliveryMethod: restoredCheckout!.deliveryMethod,
    paymentMethod: restoredCheckout!.paymentMethod,
    checkoutAttemptId,
    createdAt: '2026-08-11T12:00:00.000Z',
  } as const;
  const first = await repository.createOrder(input);
  const duplicate = await repository.createOrder(input);
  assert(first.created && !duplicate.created && first.order.orderId === duplicate.order.orderId
    && repository.getOrders().length === 1,
  'one checkout attempt creates exactly one buyer order');
  const nextAttempt = await repository.createOrder({ ...input, checkoutAttemptId: 'checkout-next-same-cart' });
  assert(nextAttempt.created && nextAttempt.order.orderId !== first.order.orderId
    && repository.getOrders().length === 2,
  'a new checkout attempt can purchase the same cart as a new order');
  assert(first.order.lines[0].variantLabel === variantLine.variant
    && first.order.lines[0].quantity === 3
    && first.order.paymentStatus === 'prototype_pending_confirmation',
  'order snapshot preserves cart selection and does not invent payment confirmation');

  const restoredRepository = createBuyerOrderRepository(orderStorage, { seedOrders: [] });
  await restoredRepository.hydrate();
  const restoredOrder = restoredRepository.getOrderById(first.order.orderId);
  assert(restoredOrder?.checkoutAttemptId === checkoutAttemptId
    && restoredRepository.getOrders().some((order) => order.orderId === restoredOrder.orderId),
  'orders list and detail selectors share the same persisted repository');
  await restoredRepository.selectOrder(first.order.orderId);
  assert(restoredRepository.getSelectedOrder()?.orderId === first.order.orderId,
    'selectedOrderId persists as a validated repository reference');
  assert(getSupportSelectableOrderIds(restoredRepository).includes(first.order.orderId),
    'support order selection reads buyer repository order IDs');
  const notificationRepository = createBuyerOrderRepository(new MemoryStorage());
  assert(resolveNotificationBuyerOrder('notif-prep', notificationRepository)?.orderId === 'MAY-2026-001842',
    'order notification references resolve through the buyer repository');

  let authRevisions = 0;
  const unsubscribeAuth = authState.subscribe(() => { authRevisions += 1; });
  authState.logout();
  unsubscribeAuth();
  assert(!authState.isAuthenticated() && authState.getUser() === null && authRevisions > 0,
    'logout clears and notifies the authoritative auth state consumed by navigation');
};
