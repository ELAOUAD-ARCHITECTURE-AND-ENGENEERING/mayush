import { emptyCartState } from '../src/commerce/cartState';
import { defaultSavedAddresses } from '../src/commerce/checkoutState';
import {
  BUYER_ORDER_STORAGE_KEY,
  createBuyerOrderRepository,
  getCanonicalOrderDetailRoute,
  getOrderPackageLines,
  OrderStorage,
  validateOrderPackages,
} from '../src/commerce/orderState';
import { resolveNotificationBuyerOrder } from '../src/commerce/notificationPreferencesState';
import { getSupportSelectableOrderIds } from '../src/commerce/supportState';

class MemoryStorage implements OrderStorage {
  public readonly values = new Map<string, string>();
  async getItem(key: string) { return this.values.get(key) ?? null; }
  async setItem(key: string, value: string) { this.values.set(key, value); }
  async removeItem(key: string) { this.values.delete(key); }
}

export const runStep8BOrderDetailTrackingPackagesBehaviorTests = async (
  assert: (condition: boolean, message: string) => void,
): Promise<void> => {
  const storage = new MemoryStorage();
  const repository = createBuyerOrderRepository(storage);
  await repository.hydrate();
  const preparing = repository.getOrderById('MAY-2026-001842')!;
  const shipped = repository.getOrderById('MAY-2026-001841')!;
  const delivered = repository.getOrderById('MAY-2026-001838')!;
  const multi = repository.getOrderById('MAY-2026-001835')!;

  assert(getCanonicalOrderDetailRoute(preparing) === 'order-detail-preparing',
    'preparing order resolves to the canonical preparation detail route');
  assert(getCanonicalOrderDetailRoute(shipped) === 'order-detail-shipped',
    'shipped order resolves to the canonical shipped detail route');

  await repository.selectOrder(shipped.orderId);
  const selectedBeforeTracking = repository.getSelectedOrder();
  const selectedAfterTrackingRead = repository.getSelectedOrder();
  assert(selectedBeforeTracking?.orderId === shipped.orderId && selectedAfterTrackingRead?.orderId === shipped.orderId,
    'selected order identity persists across detail and tracking reads');
  assert(selectedAfterTrackingRead!.trackingEvents.every((event) => shipped.trackingEvents.some((candidate) => candidate.trackingEventId === event.trackingEventId)),
    'tracking timeline events belong to the selected order');

  assert(getCanonicalOrderDetailRoute(delivered) === 'order-detail-delivered' && delivered.invoice?.previewAvailable === true,
    'delivered order resolves to delivered actions with its invoice preview');
  assert(getCanonicalOrderDetailRoute(multi) === 'order-detail-multi-vendor' && multi.packages.every((pkg) => multi.lines.some((line) => line.sellerId === pkg.sellerId)),
    'multi-vendor packages are grouped inside one buyer order');
  assert(multi.packages.every((pkg) => getOrderPackageLines(multi, pkg.packageId).length === pkg.orderLineIds.length),
    'every package references valid selected-order line IDs');
  const packageValidation = validateOrderPackages(multi);
  assert(packageValidation.valid && packageValidation.duplicateLineIds.length === 0,
    'line IDs are not duplicated across incompatible packages');

  await repository.selectOrder(multi.orderId);
  const foreignPackageId = 'MAY-2026-001835-01';
  const rejectedFromOtherOrder = await repository.selectOrder(shipped.orderId)
    .then(() => repository.selectPackage(foreignPackageId));
  await repository.selectOrder(multi.orderId);
  const acceptedPackage = repository.selectPackage(foreignPackageId);
  assert(!rejectedFromOtherOrder && acceptedPackage,
    'selectPackage validates the package against selectedOrderId');
  const persistedPayload = storage.values.get(BUYER_ORDER_STORAGE_KEY) || '';
  assert(!('selectedPackageId' in multi) && !persistedPayload.includes('selectedPackageId'),
    'selectedPackageId remains transient and is never persisted in BuyerOrder');
  assert(repository.getSelectedPackage()?.packageId === foreignPackageId
    && getOrderPackageLines(repository.getSelectedOrder()!, foreignPackageId).length === 2,
  'package detail resolves selectedPackageId within the selected order');
  assert(repository.getSelectedOrder()?.invoice?.invoiceNumber === 'FAC-2026-001835',
    'invoice detail resolves from the selected buyer order snapshot');

  assert(getSupportSelectableOrderIds(repository).includes(multi.orderId),
    'support order selection still resolves repository orders');
  assert(resolveNotificationBuyerOrder('notif-shipped', repository)?.orderId === shipped.orderId,
    'order notifications still resolve repository orders');

  const checkoutOrder = await repository.createOrder({
    cart: emptyCartState(),
    address: defaultSavedAddresses[0],
    deliveryMethod: 'standard',
    paymentMethod: 'cmi',
    checkoutAttemptId: 'step-8b-persisted-checkout',
    createdAt: '2026-08-11T12:00:00.000Z',
  });
  assert(checkoutOrder.created && getCanonicalOrderDetailRoute(checkoutOrder.order) === 'order-detail-preparing',
    'persisted checkout-created order opens the canonical preparation detail');

  repository.selectPackage(null);
  await repository.selectOrder(multi.orderId);
  repository.selectPackage(foreignPackageId);
  const reloadedRepository = createBuyerOrderRepository(storage, { seedOrders: [] });
  await reloadedRepository.hydrate();
  assert(reloadedRepository.getOrderById(checkoutOrder.order.orderId) !== null
    && reloadedRepository.getSelectedPackageId() === null,
  'reload preserves buyer orders but not selectedPackageId');

  const repositoryShape = Object.keys(repository as unknown as Record<string, unknown>);
  assert(!repositoryShape.some((key) => /seller.*(identity|account|session|state)|admin/i.test(key)),
    'buyer order repository contains no seller or admin identity state');
};
