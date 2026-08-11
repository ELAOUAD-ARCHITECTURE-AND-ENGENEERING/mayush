import { readFileSync } from 'fs';
import { resolve } from 'path';
import { BUYER_ORDER_ACTION_STORAGE_KEY, BuyerOrderActionStorage, createBuyerOrderActionRepository, validateDeliveryIssueOwnership } from '../src/commerce/orderActionState';
import { createBuyerOrderRepository, OrderStorage } from '../src/commerce/orderState';
import { hasOrderTrackingMetadata, OrderViewStateManager } from '../src/commerce/orderViewState';
import { supportState } from '../src/commerce/supportState';

class MemoryStorage implements OrderStorage, BuyerOrderActionStorage {
  readonly values = new Map<string, string>();
  async getItem(key: string) { return this.values.get(key) ?? null; }
  async setItem(key: string, value: string) { this.values.set(key, value); }
  async removeItem(key: string) { this.values.delete(key); }
}

export const runStep8EDeliveryIssuesOrderSystemStatesBehaviorTests = async (assert: (condition: boolean, message: string) => void): Promise<void> => {
  const storage = new MemoryStorage();
  const orders = createBuyerOrderRepository(storage);
  const actions = createBuyerOrderActionRepository(storage, () => '2026-08-11T14:00:00.000Z');
  await orders.hydrate(); await actions.hydrate();
  const shipped = orders.getOrderById('MAY-2026-001841')!;
  const multi = orders.getOrderById('MAY-2026-001835')!;
  const preparing = orders.getOrderById('MAY-2026-001842')!;
  const delayed = actions.getDeliveryIssues().find((issue) => issue.type === 'delayed')!;
  const failed = actions.getDeliveryIssues().find((issue) => issue.type === 'delivery_failed')!;
  const shippedBefore = JSON.stringify(shipped); const multiBefore = JSON.stringify(multi);

  assert(validateDeliveryIssueOwnership(shipped, delayed), '1 delayed issue references a valid repository order');
  assert(validateDeliveryIssueOwnership(multi, failed) && failed.packageId === multi.packages[0].packageId, '2 package issue belongs to the selected order');
  actions.selectDeliveryIssueForOrder(shipped); assert(JSON.stringify(orders.getOrderById(shipped.orderId)) === shippedBefore, '3 opening delay does not mutate purchase snapshot');
  const reschedule = await actions.requestDeliveryReschedule(multi, failed.deliveryIssueId, '2026-05-30T09:00:00.000Z');
  assert(reschedule?.status === 'frontend_requested' && JSON.stringify(orders.getOrderById(multi.orderId)) === multiBefore, '4 failed-delivery reschedule records a frontend request without mutating the order');
  assert(reschedule?.recordSource === 'frontend_fixture' && !/confirmed/i.test(reschedule.status), '5 reschedule does not claim provider confirmation');
  supportState.setContactDraft({ selectedOrderId: multi.orderId });
  assert(supportState.getContactDraft().selectedOrderId === multi.orderId, '6 order support references orderId');
  assert(!('order' in (supportState.getContactDraft() as unknown as Record<string, unknown>)), '7 support does not copy BuyerOrder');
  assert(!hasOrderTrackingMetadata(preparing) && hasOrderTrackingMetadata(shipped), '8 tracking-unavailable derives from order metadata');
  assert(preparing.trackingNumber === undefined && preparing.packages.every((pkg) => !pkg.trackingNumber), '9 unavailable tracking does not fabricate a tracking number');
  assert(orders.getOrderById('UNKNOWN') === null, '10 invalid orderId resolves to not-found');
  const selectedUnknown = await orders.selectOrder('UNKNOWN'); await orders.selectOrder(null);
  assert(!selectedUnknown && orders.getSelectedOrder() === null, '11 invalid lookup does not select an arbitrary order');
  assert(orders.getOrderById(shipped.orderId)?.orderId === shipped.orderId, '12 valid repository orders remain after invalid lookup');

  const views = new OrderViewStateManager(); views.beginListLoad();
  assert(views.resolveList([]) === 'empty', '13 empty requires a successful zero-order resolution');
  assert(orders.getOrders().length > 0, '14 isolated empty presentation does not erase persistent orders');
  assert(new OrderViewStateManager().getSnapshot().listStatus === 'idle', '15 list loading state is transient');
  assert(new OrderViewStateManager().getSnapshot().detailStatus === 'idle', '16 detail loading state is transient');
  assert(!('loadStatus' in preparing) && !('detailStatus' in preparing), '17 loading state is not persisted in BuyerOrder');
  views.failListLoad(); views.retryListLoad();
  assert(orders.getOrders().length > 0, '18 error retry preserves durable orders');
  assert(views.getSnapshot().listStatus === 'loading' && views.resolveList(orders.getOrders()) === 'ready', '19 retry transitions error to loading to ready');

  const systemSource = readFileSync(resolve(__dirname, '../src/screens/orders/OrderSystemStateScreens.tsx'), 'utf8');
  assert(/search.*tabs.*order-card-1.*order-card-2.*order-card-3/s.test(systemSource), '20 list skeleton corresponds to Orders structure');
  assert(/summary.*timeline.*products.*address.*payment.*totals.*actions/s.test(systemSource), '21 detail skeleton corresponds to canonical detail structure');
  assert(supportState.getContactDraft().selectedOrderId === multi.orderId, '22 notification/support identity references remain compatible');
  actions.beginCancellation(preparing); actions.setCancellationReason('ordered_by_mistake'); const cancellation = await actions.submitCancellationRequest(preparing);
  assert(cancellation?.orderId === preparing.orderId && actions.getDeliveryRescheduleRequests(multi.orderId).length === 1, '23 cancellation and delivery action records remain compatible');

  const routeMap = JSON.parse(readFileSync(resolve(__dirname, '../design-reference/mayush-mobile-design/figma-handoff/figma-prototype-route-map.json'), 'utf8')) as { connectionStatusOverrides: Record<string, string> };
  assert(routeMap.connectionStatusOverrides['FIGMA-PROT-118'] === 'MISMATCHED', '24 refund completion does not semantically route to delay');
  assert(reschedule?.recordSource === 'frontend_fixture' && delayed.recordSource === 'frontend_fixture', '25 delivery behavior makes no backend or carrier claim');
  assert(!/sellerIdentity|adminIdentity|sellerSession|adminSession/i.test(storage.values.get(BUYER_ORDER_ACTION_STORAGE_KEY) || ''), '26 no seller or admin state is introduced');
  const csv = readFileSync(resolve(__dirname, '../docs/phase-5c/CURRENT_SCREEN_STATUS.csv'), 'utf8');
  assert((csv.match(/309:73[7-9]|309:74[0-5]/g) || []).length === 9 && (csv.match(/IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING/g) || []).length >= 9, '27 native validation remains explicitly pending');
  const registry = JSON.parse(readFileSync(resolve(__dirname, '../docs/frontend-completion/canonical-figma-screen-registry.json'), 'utf8')) as { nodes: Array<{ figmaNodeId: string; domain: string; screenStatus: string }> };
  assert(registry.nodes.filter((node) => node.domain === 'Buyer Orders & Fulfillment' && node.screenStatus === 'MISSING').length === 0, '28 canonical remaining Buyer Orders missing-node count is zero');
};
