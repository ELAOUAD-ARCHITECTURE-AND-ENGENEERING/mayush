import { readFileSync } from 'fs';
import { resolve } from 'path';
import {
  BUYER_ORDER_ACTION_STORAGE_KEY,
  BuyerOrderActionStorage,
  canReturnLine,
  canReturnOrder,
  createBuyerOrderActionRepository,
  validateReturnLineOwnership,
} from '../src/commerce/orderActionState';
import { createBuyerOrderRepository, OrderStorage } from '../src/commerce/orderState';
import { supportState } from '../src/commerce/supportState';

class MemoryStorage implements OrderStorage, BuyerOrderActionStorage {
  public readonly values = new Map<string, string>();
  async getItem(key: string) { return this.values.get(key) ?? null; }
  async setItem(key: string, value: string) { this.values.set(key, value); }
  async removeItem(key: string) { this.values.delete(key); }
}

export const runStep8DReturnsRefundsTrackingBehaviorTests = async (
  assert: (condition: boolean, message: string) => void,
): Promise<void> => {
  const storage = new MemoryStorage();
  const orders = createBuyerOrderRepository(storage);
  const actions = createBuyerOrderActionRepository(storage, () => '2026-08-11T13:00:00.000Z');
  await orders.hydrate();
  await actions.hydrate();
  const delivered = orders.getOrderById('MAY-2026-001838')!;
  const preparing = orders.getOrderById('MAY-2026-001842')!;
  const shipped = orders.getOrderById('MAY-2026-001841')!;
  const multi = orders.getOrderById('MAY-2026-001835')!;
  const cancelledFixture = orders.getOrderById('MAY-2026-001257')!;
  const deliveredSnapshot = JSON.stringify(delivered);
  const unrelatedSnapshot = JSON.stringify(shipped);

  assert(canReturnOrder(delivered) && actions.beginReturn(delivered),
    'delivered eligible order can start return');
  assert(!canReturnOrder(preparing) && !canReturnOrder(shipped) && !actions.beginReturn(preparing),
    'preparing and shipped orders cannot start return');

  actions.beginReturn(delivered);
  assert(!actions.setReturnLineSelected(delivered, preparing.lines[0].orderLineId, true),
    'return item must belong to selected order');
  assert(!canReturnLine(preparing, preparing.lines[0]) && !actions.setReturnLineSelected(preparing, preparing.lines[0].orderLineId, true),
    'ineligible line cannot be selected');
  actions.setReturnLineSelected(delivered, delivered.lines[0].orderLineId, true);
  assert(JSON.stringify(delivered) === deliveredSnapshot && JSON.stringify(orders.getOrderById(delivered.orderId)) === deliveredSnapshot,
    'return selection does not mutate order');

  const partialOrder = {
    ...delivered,
    lines: delivered.lines.map((line, index) => index === 0 ? { ...line, quantity: 3 } : { ...line }),
  };
  actions.beginReturn(partialOrder);
  actions.setReturnLineSelected(partialOrder, partialOrder.lines[0].orderLineId, true);
  actions.setReturnLineQuantity(partialOrder, partialOrder.lines[0].orderLineId, 1);
  actions.setReturnReason('damaged_on_delivery');
  assert(partialOrder.lines[0].quantity === 3 && actions.getReturnDraft()?.selectedLines[0].quantity === 1,
    'partial return quantity does not alter historical quantity');

  const returnRecord = await actions.submitReturnRequest(partialOrder);
  assert(returnRecord?.orderId === partialOrder.orderId
    && returnRecord.selectedLines.length === 1
    && returnRecord.selectedLines[0].orderLineId === partialOrder.lines[0].orderLineId,
  'return request stores orderId and stable orderLineId references');

  const rehydratedActions = createBuyerOrderActionRepository(storage, () => '2026-08-11T13:01:00.000Z');
  await rehydratedActions.hydrate();
  assert(rehydratedActions.getReturnRequests(partialOrder.orderId).length === 1 && rehydratedActions.getReturnDraft() === null,
    'durable return request persists while transient selection does not rehydrate');
  assert(rehydratedActions.getReturnRequest(returnRecord!.returnRequestId)?.orderId === delivered.orderId,
    'return detail resolves the correct request');
  const tracking = rehydratedActions.getReturnTrackingEvents(returnRecord!.returnRequestId);
  assert(tracking.length === 6 && tracking.every((event) => event.returnRequestId === returnRecord!.returnRequestId),
    'tracking events belong to returnRequestId');
  const trackingSnapshot = JSON.stringify(rehydratedActions.getReturnRequest(returnRecord!.returnRequestId));
  rehydratedActions.getReturnTrackingEvents(returnRecord!.returnRequestId);
  assert(JSON.stringify(rehydratedActions.getReturnRequest(returnRecord!.returnRequestId)) === trackingSnapshot,
    'opening return tracking does not mutate status');
  assert(returnRecord?.status === 'inspection' && returnRecord.refundStatus === 'processing',
    'refund status remains distinct from return status');

  actions.beginCancellation(preparing);
  actions.setCancellationReason('ordered_by_mistake');
  const cancellation = await actions.submitCancellationRequest(preparing);
  const cancelledAfterLocalScenario = { ...preparing, orderStatus: 'cancelled' as const, deliveryStatus: 'cancelled' as const };
  actions.beginCancelledOrderRefund(cancelledAfterLocalScenario);
  const refundDraft = actions.getCancelledOrderRefundDraft();
  assert(refundDraft?.orderId === preparing.orderId && refundDraft.cancellationRequestId === cancellation?.cancellationRequestId,
    'cancelled-order refund references the compatible cancellation and order records');
  assert(actions.beginCancelledOrderRefund(cancelledFixture) && actions.getCancelledOrderRefundDraft()?.requestedAmountMad === cancelledFixture.totalMad,
    'refund amount derives deterministically from the BuyerOrder snapshot');
  const processingRefund = await actions.requestCancelledOrderRefund(cancelledFixture);
  const completedRefund = await actions.completeRefundFixture(processingRefund!.refundId);
  assert(processingRefund?.status === 'processing' && processingRefund.completedAmountMad === undefined
    && completedRefund?.status === 'completed' && completedRefund.completedAmountMad === cancelledFixture.totalMad,
  'refund completion requires and reaches a distinct explicit local transition');
  assert(returnRecord?.recordSource === 'frontend_fixture' && processingRefund?.recordSource === 'frontend_fixture'
    && cancellation?.status === 'frontend_requested',
  'action records do not fabricate backend acceptance');
  assert(JSON.stringify(orders.getOrderById(delivered.orderId)) === deliveredSnapshot,
    'historical order lines, prices, address, payment, seller, and quantities remain immutable');

  const deliveredMulti = { ...multi, orderStatus: 'delivered' as const, deliveryStatus: 'delivered' as const };
  const packageId = multi.packages[0].packageId;
  const packageLineId = multi.packages[0].orderLineIds[0];
  assert(validateReturnLineOwnership(deliveredMulti, packageLineId, packageId)
    && !validateReturnLineOwnership(deliveredMulti, packageLineId, multi.packages[1].packageId),
  'multi-package return validates package and line ownership');

  supportState.setContactDraft({ selectedOrderId: delivered.orderId, returnRequestId: returnRecord!.returnRequestId, refundId: completedRefund!.refundId });
  const supportDraft = supportState.getContactDraft() as unknown as Record<string, unknown>;
  assert(supportDraft.selectedOrderId === delivered.orderId && supportDraft.returnRequestId === returnRecord!.returnRequestId
    && supportDraft.refundId === completedRefund!.refundId && !('order' in supportDraft) && !('returnRequest' in supportDraft),
  'support integration stores identity references without copied order data');
  supportState.clearContactDraft();
  assert(JSON.stringify(orders.getOrderById(shipped.orderId)) === unrelatedSnapshot,
    'return and refund actions leave unrelated orders unchanged');

  const reload = createBuyerOrderActionRepository(storage, () => '2026-08-11T13:02:00.000Z');
  await reload.hydrate();
  assert(reload.getCancellationRequest(preparing.orderId)?.cancellationRequestId === cancellation?.cancellationRequestId,
    'Step 8C cancellation records remain compatible after Step 8D persistence');

  const routeMap = JSON.parse(readFileSync(resolve(__dirname, '../design-reference/mayush-mobile-design/figma-handoff/figma-prototype-route-map.json'), 'utf8')) as { connectionStatusOverrides: Record<string, string> };
  assert(routeMap.connectionStatusOverrides['FIGMA-PROT-113'] === 'MISMATCHED',
    'reorder availability does not automatically route to return selection');
  const registry = JSON.parse(readFileSync(resolve(__dirname, '../docs/frontend-completion/canonical-figma-screen-registry.json'), 'utf8')) as { nodes: Array<{ figmaNodeId: string; screenStatus: string }> };
  assert(registry.nodes.some((node) => node.figmaNodeId === '309:737' && node.screenStatus === 'IMPLEMENTED'),
    '309:737 is implemented by Step 8E without changing Step 8D refund semantics');
  const persisted = storage.values.get(BUYER_ORDER_ACTION_STORAGE_KEY) ?? '';
  assert(!/sellerIdentity|adminIdentity|sellerSession|adminSession/i.test(persisted),
    'buyer action persistence introduces no seller or admin state');
};
