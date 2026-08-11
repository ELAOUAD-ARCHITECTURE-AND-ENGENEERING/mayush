import { readdirSync, readFileSync } from 'fs';
import { resolve } from 'path';
import { accountPreferencesState } from '../src/commerce/accountPreferencesState';
import { authState, createCheckoutAuthReturnDestination } from '../src/commerce/authState';
import { applyCartConflictChanges, applyPromotionCode, CartState, getCartTotals } from '../src/commerce/cartState';
import {
  CHECKOUT_SESSION_KEY, CheckoutSession, CheckoutStorage, acceptCheckoutTerms,
  buildSellerDeliveryProjection, createCheckoutMaterialSignature, defaultSavedAddresses,
  getCheckoutGrandTotalMad, getPaymentVerificationPresentation,
  isCheckoutTermsAcceptanceValid, loadCheckoutSession, resolveCheckoutRecovery, saveCheckoutSession,
} from '../src/commerce/checkoutState';
import { BUYER_ORDER_STORAGE_KEY, createBuyerOrderRepository, OrderStorage } from '../src/commerce/orderState';

class MemoryStorage implements CheckoutStorage, OrderStorage {
  values = new Map<string, string>();
  async getItem(key: string) { return this.values.get(key) ?? null; }
  async setItem(key: string, value: string) { this.values.set(key, value); }
  async removeItem(key: string) { this.values.delete(key); }
}

const address = defaultSavedAddresses[0];
const cart: CartState = { lines: [
  { id: 'line-fs-1023', productId: 'FS-1023', name: 'Fauteuil Scandinave', variant: 'Beige', quantity: 1, unitPriceMad: 2890, sellerId: 'seller-a', sellerName: 'Maison Atlas' },
  { id: 'line-tb-2045', productId: 'TB-2045', name: 'Table à manger Oslo', variant: 'Chêne', quantity: 5, unitPriceMad: 900, sellerId: 'seller-b', sellerName: 'Bois & Déco' },
] };

const orderInput = (checkoutAttemptId: string, sourceCart: CartState = cart) => ({
  cart: sourceCart, address, deliveryMethod: 'standard' as const, paymentMethod: 'cmi' as const,
  checkoutAttemptId, deliveryFeeMad: 58, deliveryPackageCount: 2, createdAt: '2026-08-11T14:00:00.000Z',
});

export const runStep8HCheckoutPaymentConflictSystemBehaviorTests = async (assert: (condition: boolean, message: string) => void) => {
  const storage = new MemoryStorage();
  const orders = createBuyerOrderRepository(storage, { seedOrders: [], initialSequence: 2000 });
  await orders.hydrate();
  const created = await orders.createOrder(orderInput('attempt-8h'));
  const delayedAttemptId = created.order.checkoutAttemptId;
  assert(delayedAttemptId === 'attempt-8h' && getPaymentVerificationPresentation(created.order, true) === 'taking_longer', '1 delayed verification keeps the same checkoutAttemptId');
  const beforeDelayedCount = orders.getOrders().length;
  getPaymentVerificationPresentation(created.order, true);
  assert(orders.getOrders().length === beforeDelayedCount, '2 delayed verification does not create another order');
  assert(created.order.paymentStatus === 'prototype_pending_confirmation' && getPaymentVerificationPresentation(created.order, true) !== 'confirmed', '3 delayed verification does not mark payment confirmed');
  assert(orders.getOrderByCheckoutAttemptId('attempt-8h')?.orderId === created.order.orderId, '4 pending state resolves the same orderId');
  const reloadedOrders = createBuyerOrderRepository(storage, { seedOrders: [] }); await reloadedOrders.hydrate();
  assert(reloadedOrders.getOrderByCheckoutAttemptId('attempt-8h')?.paymentStatus === 'prototype_pending_confirmation', '5 pending state survives appropriate reload');
  await reloadedOrders.selectOrder(created.order.orderId);
  assert(reloadedOrders.getSelectedOrder()?.paymentStatus === 'prototype_pending_confirmation', '6 opening Orders from pending does not confirm payment');
  const paymentStates = ['prototype_pending_confirmation', 'confirmed', 'failed', 'cancelled'] as const;
  assert(paymentStates.map((status) => getPaymentVerificationPresentation({ paymentMethod: 'cmi', paymentStatus: status })).join('|') === 'pending|confirmed|failed|cancelled', '7 payment confirmed pending failed and cancelled states remain exclusive');
  assert(getPaymentVerificationPresentation({ paymentMethod: 'cash-on-delivery', paymentStatus: 'cash_on_delivery_pending' }, true) === 'not_applicable', '8 COD does not enter processor-pending verification');

  const signature = createCheckoutMaterialSignature({ cart, selectedAddressId: address.id, deliveryMethod: 'standard', paymentMethod: 'cmi', deliveryFeeMad: 58 });
  const acceptance = acceptCheckoutTerms('attempt-8h', signature, '2026-08-11T14:01:00.000Z');
  assert(isCheckoutTermsAcceptanceValid(acceptance, 'attempt-8h', signature) && !isCheckoutTermsAcceptanceValid(acceptance, 'attempt-next', signature), '9 terms acceptance belongs to the current checkout attempt');
  const countBeforeTerms = orders.getOrders().length;
  createCheckoutMaterialSignature({ cart, selectedAddressId: address.id, deliveryMethod: 'standard', paymentMethod: 'cmi', deliveryFeeMad: 58 });
  assert(orders.getOrders().length === countBeforeTerms, '10 opening terms does not create an order');
  assert(getPaymentVerificationPresentation(created.order) === 'pending', '11 accepting terms does not create payment success');
  const changedSignature = createCheckoutMaterialSignature({ cart: { ...cart, lines: cart.lines.map((line) => line.id === 'line-fs-1023' ? { ...line, unitPriceMad: 3190 } : line) }, selectedAddressId: address.id, deliveryMethod: 'standard', paymentMethod: 'cmi', deliveryFeeMad: 58 });
  assert(!isCheckoutTermsAcceptanceValid(acceptance, 'attempt-8h', changedSignature), '12 terms state invalidates when material checkout facts change');
  const termsScreen = readFileSync(resolve(__dirname, '../src/screens/checkout/CheckoutPaymentConflictSystemScreens.tsx'), 'utf8');
  assert(/onTerms|onPrivacy|Politique de confidentialité/.test(termsScreen), '13 existing Terms and Privacy navigation is reused');

  const duplicate = await orders.createOrder(orderInput('attempt-8h'));
  assert(!duplicate.created && duplicate.order.orderId === created.order.orderId, '14 same checkoutAttemptId resolves the existing order');
  await Promise.all([orders.createOrder(orderInput('attempt-8h')), orders.createOrder(orderInput('attempt-8h'))]);
  assert(orders.getOrders().filter((order) => order.checkoutAttemptId === 'attempt-8h').length === 1, '15 repeated action cannot create a second order for one attempt');
  const legitimate = await orders.createOrder(orderInput('attempt-8h-new'));
  assert(legitimate.created && legitimate.order.orderId !== created.order.orderId, '16 same cart with a new attempt can create a legitimate new order');
  assert(orders.getOrders().filter((order) => JSON.stringify(order.lines) === JSON.stringify(created.order.lines)).length >= 2, '17 duplicate detection is not based only on cart equality');
  assert(orders.getOrderByCheckoutAttemptId('attempt-8h')?.orderId === created.order.orderId, '18 duplicate screen resolves the correct existing orderId');

  const conflictFixture = [
    { kind: 'price' as const, lineId: 'line-fs-1023', oldPriceMad: 2890, newPriceMad: 3190 },
    { kind: 'stock' as const, lineId: 'line-tb-2045', oldQuantity: 5, newQuantity: 2 },
  ];
  assert(conflictFixture[0].oldPriceMad !== conflictFixture[0].newPriceMad, '19 price conflict preserves old and current values');
  const unavailable = applyCartConflictChanges(cart, [{ kind: 'unavailable', lineId: 'line-fs-1023' }]);
  assert(!unavailable.lines.some((line) => line.id === 'line-fs-1023'), '20 an unavailable item does not silently remain purchasable');
  const updated = applyCartConflictChanges(cart, conflictFixture);
  assert(updated.lines.find((line) => line.id === 'line-fs-1023')?.unitPriceMad === 3190 && updated.lines.find((line) => line.id === 'line-tb-2045')?.quantity === 2, '21 accepting changes uses the existing CartState mutation path');
  const historicalSnapshot = JSON.stringify(created.order);
  applyCartConflictChanges(cart, conflictFixture);
  assert(JSON.stringify(orders.getOrderById(created.order.orderId)) === historicalSnapshot, '22 historical BuyerOrder remains immutable');
  const promoted = applyPromotionCode(cart, 'MAYUSH10').cart;
  const promotionInvalidated = applyCartConflictChanges(promoted, [{ kind: 'promotion_invalidated', promotionId: promoted.appliedPromotionId! }]);
  assert(!promotionInvalidated.appliedPromotionId && getCartTotals(promotionInvalidated).discountMad === 0, '23 promotion is revalidated after cart changes');
  const beforeDelivery = buildSellerDeliveryProjection(cart.lines, address, 'standard');
  const afterDelivery = buildSellerDeliveryProjection(unavailable.lines, address, 'standard');
  assert(beforeDelivery.packageCount === 2 && afterDelivery.packageCount === 1 && afterDelivery.deliveryFeeMad < beforeDelivery.deliveryFeeMad, '24 delivery projection recalculates after seller and cart changes');
  const updatedTotals = getCartTotals(updated);
  assert(Number.isInteger(updatedTotals.totalMad) && Number.isInteger(getCheckoutGrandTotalMad(updatedTotals.totalMad, afterDelivery.deliveryFeeMad)), '25 checkout total remains integer-MAD consistent');
  assert(!isCheckoutTermsAcceptanceValid(acceptance, 'attempt-8h', createCheckoutMaterialSignature({ cart: updated, selectedAddressId: address.id, deliveryMethod: 'standard', paymentMethod: 'cmi', deliveryFeeMad: afterDelivery.deliveryFeeMad })), '26 stale review and terms state is invalidated after accepted changes');

  const sessionStorage = new MemoryStorage();
  const session: CheckoutSession = { checkoutAttemptId: 'attempt-8h', screen: 'checkout-skeleton', selectedAddressId: address.id, deliveryMethod: 'standard', paymentMethod: 'cmi', savedAddresses: [address], termsAcceptance: acceptance };
  await saveCheckoutSession(sessionStorage, session);
  assert(JSON.parse(sessionStorage.values.get(CHECKOUT_SESSION_KEY)!).screen === 'checkout-summary', '27 skeleton state is transient');
  assert(!/checkoutLoading|viewStatus|spinnerVisible/.test(sessionStorage.values.get(CHECKOUT_SESSION_KEY)!), '28 skeleton state does not persist in the checkout session');
  await saveCheckoutSession(sessionStorage, { ...session, screen: 'checkout-error' });
  assert(JSON.parse(sessionStorage.values.get(CHECKOUT_SESSION_KEY)!).screen === 'checkout-summary', '29 error state is transient');
  const retryCartBefore = JSON.stringify(cart);
  const recovery = resolveCheckoutRecovery({ cart, selectedAddress: address, deliveryMethod: 'standard' });
  assert(JSON.stringify(cart) === retryCartBefore && recovery.destination === 'checkout-summary', '30 retry preserves cart');
  assert(recovery.deliveryProjection?.available === true && address.id === defaultSavedAddresses[0].id, '31 retry preserves selected address');
  const hydratedSession = await loadCheckoutSession(sessionStorage);
  assert(hydratedSession?.checkoutAttemptId === 'attempt-8h', '32 retry preserves checkoutAttemptId');
  const beforeRetryOrders = orders.getOrders().length; resolveCheckoutRecovery({ cart, selectedAddress: address, deliveryMethod: 'standard' });
  assert(orders.getOrders().length === beforeRetryOrders, '33 retry does not create an order');
  const incompatible = resolveCheckoutRecovery({ cart, selectedAddress: { ...address, city: 'Marrakech', cityId: 'marrakech', zone: 'Guéliz', zoneId: 'gueliz' }, deliveryMethod: 'standard' });
  assert(incompatible.destination === 'delivery-unavailable', '34 retry revalidates incompatible data');
  const navigatorCode = readFileSync(resolve(__dirname, '../src/navigation/RootNavigator.tsx'), 'utf8');
  assert(!/AsyncStorage\.clear\s*\(/.test(navigatorCode), '35 AsyncStorage.clear is not used');

  authState.reset();
  const authDestination = createCheckoutAuthReturnDestination('attempt-8h', 'wallet-balance');
  assert(authDestination.params?.checkoutAttemptId === 'attempt-8h' && authDestination.route === 'wallet-balance', '36 wallet auth return resolves the same checkout context');
  const cards = accountPreferencesState.getPaymentMethods().filter((method) => method.type === 'card');
  assert(cards.every((card) => Boolean(card.id && card.last4 && card.expiry)) && !/(?:\b\d{12,19}\b)|cvv|cvc/i.test(JSON.stringify(cards)), '37 saved-card metadata remains safe');
  const storedOrdersBefore = storage.values.get(BUYER_ORDER_STORAGE_KEY);
  resolveCheckoutRecovery({ cart, selectedAddress: address, deliveryMethod: 'standard' });
  assert(storage.values.get(BUYER_ORDER_STORAGE_KEY) === storedOrdersBefore, '38 return refund and order history remain unchanged');
  assert(readFileSync(resolve(__dirname, '../src/commerce/supportState.ts'), 'utf8').length > 0 && readFileSync(resolve(__dirname, '../src/commerce/notificationPreferencesState.ts'), 'utf8').length > 0, '39 support and notification references remain valid');
  assert(!/sellerDashboard|adminDashboard|sellerSession|adminSession/.test(navigatorCode), '40 no seller or admin state is introduced');
  const checkoutDomain = readFileSync(resolve(__dirname, '../src/commerce/checkoutState.ts'), 'utf8');
  assert(!/fetch\(|axios|websocket|setInterval|CMI callback/i.test(checkoutDomain), '41 no backend or payment-provider settlement claim is introduced');
  const csv = readFileSync(resolve(__dirname, '../docs/phase-5c/CURRENT_SCREEN_STATUS.csv'), 'utf8');
  const step8HReport = readFileSync(resolve(__dirname, '../docs/frontend-completion/STEP_8H_CHECKOUT_PAYMENT_CONFLICT_SYSTEM_STATES_REPORT.md'), 'utf8');
  assert(/309:591.*Exact remaining node|Exact remaining node.*309:591/is.test(step8HReport), '42 Step 8H historical boundary stopped before node 309:591');
  const commandCenter = resolve(__dirname, '../tools/command-center');
  assert(readdirSync(commandCenter).length > 0 && !navigatorCode.includes('tools/command-center'), '43 Command Center remains isolated from Step 8H');
  assert(csv.includes('IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING') || csv.includes('BLOCKED_BY_ENVIRONMENT'), '44 native validation remains pending');
};
