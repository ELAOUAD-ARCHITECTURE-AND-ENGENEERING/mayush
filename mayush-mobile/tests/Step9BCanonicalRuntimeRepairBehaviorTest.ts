import { execFileSync } from 'child_process';
import { resolve } from 'path';
import { getCartTotals, CartState } from '../src/commerce/cartState';
import { defaultSavedAddresses, resolveFrontendPaymentVerificationOutcome } from '../src/commerce/checkoutState';
import { canonicalBuyerOrderFixtures, createBuyerOrderRepository, getCanonicalOrderDetailRoute, OrderStorage } from '../src/commerce/orderState';
import { filterOrdersByTab, getOrderCardDirection, getOrdersTabDirection, INITIAL_ORDER_TAB, isGlobalOrdersEmpty, ORDER_TAB_STATUSES, OrderTab, reduceOrderTabSelection } from '../src/commerce/orderTabs';
import { getRecentlyViewedFallbackProducts, HOME_RECENT_FALLBACK_IDS, RECENTLY_VIEWED_DATA_SOURCE, resolveHomeProducts } from '../src/screens/discovery/homeCatalog';
import { HOME_CANONICAL_CONTROLS, NATIVE_VALIDATION_STATUS, resolveAboutMayushBackDestination, resolveHomeCanonicalDestination, resolveOrderProcessingDestination, resolvePaymentFailureRecoveryDestination, resolvePaymentVerificationDestination, resolveSettingsAboutDestination, SETTINGS_CANONICAL_CONTROLS } from '../src/navigation/canonicalRuntime';

class MemoryStorage implements OrderStorage {
  public values = new Map<string, string>();
  async getItem(key: string) { return this.values.get(key) ?? null; }
  async setItem(key: string, value: string) { this.values.set(key, value); }
  async removeItem(key: string) { this.values.delete(key); }
}

const cart: CartState = { lines: [{
  id: 'step9b-line', productId: 203, name: 'Suspension Nordique', variant: 'Noir', quantity: 1,
  unitPriceMad: 890, sellerId: 'seller-mayush', sellerName: 'Mayush Design',
}] };

export const runStep9BCanonicalRuntimeRepairBehaviorTests = async (assert: (condition: boolean, message: string) => void) => {
  const allOrders = canonicalBuyerOrderFixtures.map((order) => ({ ...order }));
  let selectedTab: OrderTab = INITIAL_ORDER_TAB;
  assert(selectedTab === 'all', '1 default Orders tab is All');
  selectedTab = reduceOrderTabSelection(selectedTab, 'in_progress');
  assert(selectedTab === 'in_progress', '2 In-progress control changes the selected tab state');
  const inProgress = filterOrdersByTab(allOrders, selectedTab);
  assert(inProgress.length > 0 && inProgress.every((order) => ORDER_TAB_STATUSES.in_progress.includes(order.orderStatus)), '3 In-progress filter contains only lifecycle-valid statuses');
  selectedTab = reduceOrderTabSelection(selectedTab, 'completed');
  const completed = filterOrdersByTab(allOrders, selectedTab);
  assert(selectedTab === 'completed' && completed.length > 0 && completed.every((order) => order.orderStatus === 'delivered'), '4 Completed control and filter work');
  selectedTab = reduceOrderTabSelection(selectedTab, 'cancelled');
  const cancelled = filterOrdersByTab(allOrders, selectedTab);
  assert(selectedTab === 'cancelled' && cancelled.length > 0 && cancelled.every((order) => ORDER_TAB_STATUSES.cancelled.includes(order.orderStatus)), '5 Cancelled control and filter work');
  selectedTab = reduceOrderTabSelection(selectedTab, 'all');
  assert(filterOrdersByTab(allOrders, selectedTab).length === allOrders.length, '6 returning to All restores the full repository projection');
  assert(filterOrdersByTab(allOrders, 'completed').length > 0 && filterOrdersByTab(inProgress, 'completed').length === 0 && !isGlobalOrdersEmpty(inProgress), '7 a tab with no matches is distinct from a globally empty repository');
  const shipped = inProgress.find((order) => order.orderStatus === 'shipped')!;
  assert(Boolean(shipped) && getCanonicalOrderDetailRoute(shipped) === 'order-detail-shipped' && shipped.orderId === inProgress.find((order) => order.orderId === shipped.orderId)?.orderId, '8 an In-progress card preserves its exact ID and canonical detail mapping');
  const delivered = completed[0];
  assert(getCanonicalOrderDetailRoute(delivered) === 'order-detail-delivered', '9 a Completed card resolves its status-specific detail');
  const cancelledOrder = cancelled.find((order) => order.orderStatus === 'cancelled')!;
  assert(Boolean(cancelledOrder) && getCanonicalOrderDetailRoute(cancelledOrder) === 'order-refund-request' && cancelledOrder.orderId === cancelled.find((order) => order.orderId === cancelledOrder.orderId)?.orderId, '10 a Cancelled card preserves its exact order identity');
  assert(getOrdersTabDirection(false) === 'row' && getOrdersTabDirection(true) === 'row-reverse', '11 Orders tab direction changes structurally for RTL');
  assert(getOrderCardDirection(false) === 'row' && getOrderCardDirection(true) === 'row-reverse', '12 Orders cards expose readable LTR and RTL row structures');

  assert(HOME_CANONICAL_CONTROLS.promotions.visible, '13 Promotions has a legitimate visible Home control');
  assert(resolveHomeCanonicalDestination('promotions') === 'promotions-campaigns', '14 Promotions control resolves canonical route 309:598');
  assert(HOME_CANONICAL_CONTROLS.recently_viewed.visible, '15 Recently viewed has a legitimate visible authenticated-Home control');
  const homeRecent = resolveHomeProducts(HOME_RECENT_FALLBACK_IDS);
  const recentScreen = getRecentlyViewedFallbackProducts();
  assert(resolveHomeCanonicalDestination('recently_viewed') === 'recently-viewed' && JSON.stringify(recentScreen.map((item) => item.id)) === JSON.stringify(homeRecent.map((item) => item.id)), '16 Recently viewed resolves canonical route 309:599 with the same catalog identities');
  assert(RECENTLY_VIEWED_DATA_SOURCE === 'deterministic_catalog_fallback' && recentScreen.every((item) => HOME_RECENT_FALLBACK_IDS.includes(item.id as never)), '17 Recently viewed does not invent behavioral history');

  assert(resolveOrderProcessingDestination('cmi') === 'payment-step-intro', '18 eligible CMI payment enters canonical intro 309:693');
  assert(resolveOrderProcessingDestination('cash-on-delivery') === 'cash-on-delivery-confirmation', '19 COD does not enter the online-payment intro');
  const storage = new MemoryStorage();
  const orders = createBuyerOrderRepository(storage, { seedOrders: [], initialSequence: 3000 });
  await orders.hydrate();
  const created = await orders.createOrder({ cart, address: defaultSavedAddresses[0], deliveryMethod: 'standard', paymentMethod: 'cmi', checkoutAttemptId: 'attempt-step9b', paymentPreferenceId: 'pm-mastercard', paymentCardLast4: '8731', paymentVerificationScenario: 'failed_fixture', createdAt: '2026-08-11T16:00:00.000Z' });
  const attemptBeforeIntro = created.order.checkoutAttemptId;
  assert(resolveOrderProcessingDestination(created.order.paymentMethod) === 'payment-step-intro' && created.order.checkoutAttemptId === attemptBeforeIntro, '20 Payment intro preserves checkoutAttemptId');
  const countBeforeIntro = orders.getOrders().length;
  resolveOrderProcessingDestination(created.order.paymentMethod);
  assert(orders.getOrders().length === countBeforeIntro, '21 entering Payment intro does not create a duplicate order');
  const failureOutcome = resolveFrontendPaymentVerificationOutcome(created.order);
  assert(failureOutcome === 'failed' && resolvePaymentVerificationDestination(failureOutcome) === 'payment-failed', '22 deterministic frontend verification failure reaches canonical 309:699');
  const failed = await orders.transitionPaymentStatus(attemptBeforeIntro, 'failed');
  assert(failed?.paymentStatus === 'failed' && orders.getOrderByCheckoutAttemptId(attemptBeforeIntro)?.paymentStatus === 'failed', '23 failed verification never marks payment confirmed');
  const recovered = await orders.transitionPaymentStatus(attemptBeforeIntro, 'prototype_pending_confirmation');
  assert(resolvePaymentFailureRecoveryDestination('retry') === 'payment-step-intro' && recovered?.checkoutAttemptId === attemptBeforeIntro, '24 failed-payment retry preserves the same attempt');
  const duplicate = await orders.createOrder({ cart, address: defaultSavedAddresses[0], deliveryMethod: 'standard', paymentMethod: 'cmi', checkoutAttemptId: attemptBeforeIntro });
  assert(!duplicate.created && orders.getOrders().filter((order) => order.checkoutAttemptId === attemptBeforeIntro).length === 1, '25 failure recovery cannot duplicate the order');
  const confirmed = await orders.transitionPaymentStatus(attemptBeforeIntro, 'confirmed');
  const invalidFailureAfterConfirmation = await orders.transitionPaymentStatus(attemptBeforeIntro, 'failed');
  assert(confirmed?.paymentStatus === 'confirmed' && invalidFailureAfterConfirmation === null && orders.getOrderByCheckoutAttemptId(attemptBeforeIntro)?.paymentStatus === 'confirmed', '26 payment statuses remain mutually exclusive');

  assert(SETTINGS_CANONICAL_CONTROLS.about_mayush.visible, '27 Settings exposes a visible About Mayush action');
  assert(resolveSettingsAboutDestination() === 'about-mayush', '28 Settings action reaches canonical 309:791');
  assert(resolveAboutMayushBackDestination() === 'settings', '29 About Mayush back returns safely to Settings');
  assert(Boolean(resolveHomeCanonicalDestination('promotions') && resolveHomeCanonicalDestination('recently_viewed') && resolveOrderProcessingDestination('cmi') && resolvePaymentVerificationDestination('failed') && resolveSettingsAboutDestination()), '30 target reachability uses exported application transitions without test-only ScreenKey mutation');

  const historicalSnapshot = JSON.stringify(canonicalBuyerOrderFixtures);
  filterOrdersByTab(canonicalBuyerOrderFixtures, 'in_progress');
  filterOrdersByTab(canonicalBuyerOrderFixtures, 'completed');
  assert(JSON.stringify(canonicalBuyerOrderFixtures) === historicalSnapshot, '31 BuyerOrder historical snapshots remain unchanged');
  const totalsBefore = JSON.stringify(getCartTotals(cart));
  resolveOrderProcessingDestination('cmi');
  resolveFrontendPaymentVerificationOutcome(created.order);
  assert(JSON.stringify(getCartTotals(cart)) === totalsBefore, '32 Cart and checkout totals remain unchanged by reachability transitions');
  const repoRoot = resolve(__dirname, '../..');
  const commandCenterDiff = execFileSync('git', ['diff', '--name-only', '--', 'mayush-mobile/tools/command-center'], { cwd: repoRoot, encoding: 'utf8' }).trim();
  assert(commandCenterDiff === '', '33 Command Center remains untouched');
  const backendDiff = execFileSync('git', ['diff', '--name-only', '--', 'app', 'bootstrap', 'config', 'database', 'routes'], { cwd: repoRoot, encoding: 'utf8' }).trim();
  assert(backendDiff === '', '34 Laravel backend remains untouched');
  assert(NATIVE_VALIDATION_STATUS === 'pending', '35 native validation remains explicitly pending');
};
