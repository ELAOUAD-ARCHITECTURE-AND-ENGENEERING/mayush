import { readFileSync } from 'fs';
import { resolve } from 'path';
import { emptyCartState } from '../src/commerce/cartState';
import {
  BUYER_ORDER_ACTION_STORAGE_KEY,
  buildReorderPlan,
  BuyerOrderActionStorage,
  canCancelOrder,
  createBuyerOrderActionRepository,
} from '../src/commerce/orderActionState';
import {
  BUYER_ORDER_STORAGE_KEY,
  createBuyerOrderRepository,
  OrderStorage,
} from '../src/commerce/orderState';
import { resolveNotificationBuyerOrder } from '../src/commerce/notificationPreferencesState';
import { getSupportSelectableOrderIds, supportState } from '../src/commerce/supportState';

class MemoryStorage implements OrderStorage, BuyerOrderActionStorage {
  public readonly values = new Map<string, string>();
  async getItem(key: string) { return this.values.get(key) ?? null; }
  async setItem(key: string, value: string) { this.values.set(key, value); }
  async removeItem(key: string) { this.values.delete(key); }
}

export const runStep8COrderCancellationReviewReorderBehaviorTests = async (
  assert: (condition: boolean, message: string) => void,
): Promise<void> => {
  const storage = new MemoryStorage();
  const orders = createBuyerOrderRepository(storage);
  const actions = createBuyerOrderActionRepository(storage, () => '2026-08-11T12:00:00.000Z');
  await orders.hydrate();
  await actions.hydrate();
  const preparing = orders.getOrderById('MAY-2026-001842')!;
  const shipped = orders.getOrderById('MAY-2026-001841')!;
  const delivered = orders.getOrderById('MAY-2026-001838')!;
  const multi = orders.getOrderById('MAY-2026-001835')!;

  assert(canCancelOrder(preparing) && actions.beginCancellation(preparing) === 'eligible',
    'preparing order can enter the eligible cancellation flow');
  assert(!canCancelOrder(shipped) && !canCancelOrder(delivered),
    'ineligible shipped and delivered statuses cannot enter normal cancellation');

  const preparingSnapshotBeforeConfirmation = JSON.stringify(preparing);
  actions.beginCancellation(preparing);
  assert(JSON.stringify(orders.getOrderById(preparing.orderId)) === preparingSnapshotBeforeConfirmation,
    'opening cancellation confirmation does not mutate the buyer order');

  const rejectedWithoutReason = await actions.submitCancellationRequest(preparing);
  actions.setCancellationReason('ordered_by_mistake');
  const validReason = actions.validateCancellationDraft(preparing);
  assert(rejectedWithoutReason === null && validReason.valid,
    'cancellation reason is required and stable-key validation succeeds');

  const cancellationRequest = await actions.submitCancellationRequest(preparing);
  assert(cancellationRequest?.orderId === preparing.orderId && cancellationRequest.reasonKey === 'ordered_by_mistake',
    'cancellation request references the correct orderId and reason key');
  const preparingAfterRequest = orders.getOrderById(preparing.orderId)!;
  assert(cancellationRequest?.status === 'frontend_requested'
    && cancellationRequest.refundStatus === 'not_started'
    && preparingAfterRequest.orderStatus === 'preparing',
  'cancellation request does not fabricate cancellation or refund completion');

  const cannotCancelResult = actions.beginCancellation(delivered);
  assert(cannotCancelResult === 'ineligible' && actions.getCancellationDraft() === null && !canCancelOrder(delivered),
    'cannot-cancel state derives from the centralized eligibility rule');

  assert(actions.beginReview(delivered),
    'delivered order can enter the buyer product-review flow');
  const preparingReviewRejected = actions.beginReview(preparing);
  const preparingReviewSubmission = await actions.submitProductReviews(preparing);
  assert(!preparingReviewRejected && preparingReviewSubmission === null,
    'preparing order cannot submit a product review');

  actions.beginReview(delivered);
  delivered.lines.forEach((line, index) => actions.setReviewRating(line.orderLineId, index + 3));
  const productReviews = await actions.submitProductReviews(delivered);
  assert(productReviews?.length === delivered.lines.length
    && productReviews.every((review) => review.orderId === delivered.orderId && review.orderLineId && review.productId),
  'product reviews reference orderId, orderLineId, and productId without copying BuyerOrder');

  const supportRatingsBefore = supportState.getSupportRequests().map((request) => request.rating ? { ...request.rating } : null);
  actions.beginReview(delivered);
  delivered.lines.forEach((line) => actions.setReviewRating(line.orderLineId, 5));
  await actions.submitProductReviews(delivered);
  const supportRatingsAfter = supportState.getSupportRequests().map((request) => request.rating ? { ...request.rating } : null);
  assert(JSON.stringify(supportRatingsBefore) === JSON.stringify(supportRatingsAfter),
    'buyer product review remains independent from support-ticket satisfaction rating');

  const historicalSnapshot = JSON.stringify(delivered);
  const plan = actions.beginReorder(delivered);
  assert(JSON.stringify(delivered) === historicalSnapshot && JSON.stringify(orders.getOrderById(delivered.orderId)) === historicalSnapshot,
    'building a reorder plan does not mutate the historical order snapshot');

  const unavailable = plan.lines.find((line) => line.state === 'unavailable')!;
  const rejectedUnavailableSelection = actions.setReorderLineSelected(unavailable.orderLineId, true);
  assert(!rejectedUnavailableSelection && actions.getReorderPlan()!.lines.find((line) => line.orderLineId === unavailable.orderLineId)?.selected === false,
    'unavailable products cannot be selected for the new cart');

  const changedPriceLine = plan.lines.find((line) => line.changes.includes('price'))!;
  assert(changedPriceLine.currentUnitPriceMad !== changedPriceLine.historicalUnitPriceMad
    && orders.getOrderById(delivered.orderId)!.lines.find((line) => line.orderLineId === changedPriceLine.orderLineId)!.unitPriceMad === changedPriceLine.historicalUnitPriceMad,
  'current price changes do not rewrite historical order prices');

  plan.lines.filter((line) => line.state !== 'unavailable').forEach((line) => actions.setReorderLineSelected(line.orderLineId, true));
  const cartResult = actions.addSelectedReorderItemsToCart(emptyCartState())!;
  assert(cartResult.addedLineIds.length === 2 && cartResult.cart.lines.length === 2,
    'available reorder items use the existing cart domain and mutation path');

  const variantChangedPlanLine = actions.getReorderPlan()!.lines.find((line) => line.changes.includes('variant'))!;
  const correspondingCartLine = cartResult.cart.lines.find((line) => String(line.productId) === String(variantChangedPlanLine.productId));
  assert(correspondingCartLine?.variant === variantChangedPlanLine.currentVariantLabel
    && correspondingCartLine.quantity === variantChangedPlanLine.currentQuantity,
  'reorder preserves the selected current variant and constrained quantity');

  const readyCatalog = delivered.lines.map((line) => ({ productId: line.productId, available: true, currentVariantId: line.variantId, currentVariantLabel: line.variantLabel, currentUnitPriceMad: line.unitPriceMad, maxQuantity: line.quantity }));
  const changedCatalog = readyCatalog.map((item, index) => index === 0 ? { ...item, currentUnitPriceMad: item.currentUnitPriceMad + 100 } : item);
  assert(plan.resultVariant === 'changed_unavailable'
    && buildReorderPlan(delivered, readyCatalog).resultVariant === 'ready'
    && buildReorderPlan(delivered, changedCatalog).resultVariant === 'availability_changes',
  'alternative reorder result variants are selected deterministically');

  const unrelatedOrderSnapshot = JSON.stringify(multi);
  assert(JSON.stringify(orders.getOrderById(multi.orderId)) === unrelatedOrderSnapshot,
    'cancellation, review, and reorder leave unrelated orders unchanged');

  await orders.selectOrder(delivered.orderId);
  actions.beginReorder(delivered);
  assert(orders.getSelectedOrderId() === delivered.orderId,
    'selectedOrderId remains correct across buyer-order actions');

  const reloadedOrders = createBuyerOrderRepository(storage);
  const reloadedActions = createBuyerOrderActionRepository(storage, () => '2026-08-11T12:01:00.000Z');
  await reloadedOrders.hydrate();
  await reloadedActions.hydrate();
  assert(storage.values.has(BUYER_ORDER_STORAGE_KEY)
    && storage.values.has(BUYER_ORDER_ACTION_STORAGE_KEY)
    && JSON.stringify(reloadedOrders.getOrderById(delivered.orderId)) === historicalSnapshot
    && reloadedActions.getProductReviews(delivered.orderId).length === delivered.lines.length,
  'reload preserves valid action records while historical BuyerOrder remains unchanged');

  assert(getSupportSelectableOrderIds(reloadedOrders).includes(delivered.orderId)
    && resolveNotificationBuyerOrder('notif-shipped', reloadedOrders)?.orderId === shipped.orderId,
  'support and notification order references still resolve through BuyerOrderRepository');

  const actionPayload = storage.values.get(BUYER_ORDER_ACTION_STORAGE_KEY) || '';
  assert(!/sellerIdentity|adminIdentity|sellerSession|adminSession/i.test(actionPayload),
    'buyer order action state contains no seller or admin identity state');

  const registry = JSON.parse(readFileSync(resolve(__dirname, '../docs/frontend-completion/canonical-figma-screen-registry.json'), 'utf8')) as { nodes: Array<{ figmaNodeId: string; screenStatus: string }> };
  assert(registry.nodes.some((node) => node.figmaNodeId === '309:731' && node.screenStatus === 'IMPLEMENTED'),
    'Step 8C reorder availability remains a canonical implemented endpoint');
};
