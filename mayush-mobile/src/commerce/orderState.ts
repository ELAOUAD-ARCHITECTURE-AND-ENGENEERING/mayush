import AsyncStorage from '@react-native-async-storage/async-storage';
import { CartState, getCartTotals } from './cartState';
import { DeliveryMethod, PaymentMethod, SavedAddress } from './checkoutState';

export const BUYER_ORDER_STORAGE_KEY = 'mayush-mobile:buyer-orders:v1';

export type OrderStatus =
  | 'created'
  | 'confirmed'
  | 'preparing'
  | 'shipped'
  | 'in_transit'
  | 'delivered'
  | 'cancelled'
  | 'return_requested'
  | 'returned'
  | 'refund_pending'
  | 'refunded';

export type PaymentStatus =
  | 'prototype_pending_confirmation'
  | 'cash_on_delivery_pending'
  | 'confirmed'
  | 'failed'
  | 'cancelled';

export type DeliveryStatus =
  | 'preparing'
  | 'shipped'
  | 'in_transit'
  | 'delivered'
  | 'cancelled'
  | 'returning'
  | 'returned';

export interface OrderLine {
  orderLineId: string;
  productId: number | string;
  name: string;
  variantId?: string;
  variantLabel: string;
  quantity: number;
  unitPriceMad: number;
  imageUri?: string;
  sellerId?: string;
  /** @deprecated Pre-Step-8B presentation alias; derived from orderLineId. */
  id: string;
  /** @deprecated Pre-Step-8B presentation alias; derived from variantLabel. */
  variant: string;
}

export interface OrderAddressSnapshot {
  name: string;
  phone: string;
  addressLine: string;
  city: string;
  postcode: string;
  zone: string;
}

export interface OrderTrackingEvent {
  trackingEventId: string;
  status: DeliveryStatus;
  occurredAt: string;
  labelKey: string;
  location?: string;
}

export interface OrderPackage {
  packageId: string;
  sellerId?: string;
  status: DeliveryStatus;
  orderLineIds: string[];
  trackingNumber?: string;
}

export interface OrderInvoiceMetadata {
  invoiceId: string;
  invoiceNumber: string;
  issuedAt: string;
  downloadState: 'not_requested' | 'frontend_fixture' | 'backend_pending';
}

export interface BuyerOrder {
  orderId: string;
  checkoutAttemptId: string;
  createdAt: string;
  paymentReference: string;
  paymentMethod: PaymentMethod;
  paymentStatus: PaymentStatus;
  deliveryMethod: DeliveryMethod;
  orderStatus: OrderStatus;
  deliveryStatus: DeliveryStatus;
  address: OrderAddressSnapshot;
  lines: OrderLine[];
  totalMad: number;
  trackingEvents: OrderTrackingEvent[];
  packages: OrderPackage[];
  invoice: OrderInvoiceMetadata | null;
  /** @deprecated Pre-Step-8B presentation alias; derived from orderId. */
  id: string;
  /** @deprecated Pre-Step-8B presentation alias; derived from checkoutAttemptId. */
  idempotencyKey: string;
  /** @deprecated Pre-Step-8B presentation alias; derived from createdAt. */
  createdAtLabel: string;
}

/** Temporary compatibility name for pre-Step-8B screens. */
export type PrototypeOrder = BuyerOrder;

export interface CreateBuyerOrderInput {
  cart: CartState;
  address: SavedAddress;
  deliveryMethod: DeliveryMethod;
  paymentMethod: PaymentMethod;
  checkoutAttemptId: string;
  createdAt?: string;
}

export interface CreateBuyerOrderResult {
  order: BuyerOrder;
  created: boolean;
}

export interface OrderStorage {
  getItem(key: string): Promise<string | null>;
  setItem(key: string, value: string): Promise<void>;
  removeItem(key: string): Promise<void>;
}

interface PersistedBuyerOrderState {
  orders: BuyerOrder[];
  selectedOrderId: string | null;
  nextOrderSequence: number;
}

interface BuyerOrderRepositoryOptions {
  seedOrders?: BuyerOrder[];
  initialSequence?: number;
}

const seedOrder: BuyerOrder = {
  orderId: 'MAY-2026-001842',
  checkoutAttemptId: 'seed-order-001842',
  createdAt: '2026-05-28T10:24:00.000Z',
  paymentReference: 'PROTOTYPE-PAY-001842',
  paymentMethod: 'cmi',
  paymentStatus: 'prototype_pending_confirmation',
  deliveryMethod: 'standard',
  orderStatus: 'preparing',
  deliveryStatus: 'preparing',
  address: {
    name: 'Karim Benjelloun',
    phone: '+212 6 61 99 88 77',
    addressLine: '12 rue des Orangers',
    city: 'Casablanca',
    postcode: '20000',
    zone: 'Centre',
  },
  lines: [{
    orderLineId: 'seed-line-luna',
    productId: 101,
    name: 'Fauteuil Lounge Luna',
    variantId: 'boucle-beige',
    variantLabel: 'Tissu bouclé • Beige',
    quantity: 1,
    unitPriceMad: 2950,
    id: 'seed-line-luna',
    variant: 'Tissu bouclé • Beige',
  }],
  totalMad: 2950,
  trackingEvents: [],
  packages: [],
  invoice: null,
  id: 'MAY-2026-001842',
  idempotencyKey: 'seed-order-001842',
  createdAtLabel: '28 mai 2026 Ã  10:24',
};

const cloneOrder = (order: BuyerOrder): BuyerOrder => {
  const { selectedPackageId: _legacyUiSelection, ...orderData } = order as BuyerOrder & {
    selectedPackageId?: string | null;
  };
  return {
    ...orderData,
    address: { ...order.address },
    lines: order.lines.map((line) => ({
      ...line,
      id: line.orderLineId,
      variant: line.variantLabel,
    })),
    trackingEvents: order.trackingEvents.map((event) => ({ ...event })),
    packages: order.packages.map((pkg) => ({ ...pkg, orderLineIds: [...pkg.orderLineIds] })),
    invoice: order.invoice ? { ...order.invoice } : null,
    id: order.orderId,
    idempotencyKey: order.checkoutAttemptId,
    createdAtLabel: new Date(order.createdAt).toLocaleString('fr-MA'),
  };
};

const isBuyerOrder = (value: unknown): value is BuyerOrder => {
  if (!value || typeof value !== 'object') return false;
  const order = value as Partial<BuyerOrder>;
  return Boolean(
    typeof order.orderId === 'string'
    && typeof order.checkoutAttemptId === 'string'
    && typeof order.createdAt === 'string'
    && Array.isArray(order.lines)
    && Array.isArray(order.trackingEvents)
    && Array.isArray(order.packages)
    && order.address
  );
};

export class BuyerOrderRepository {
  private orders: BuyerOrder[];
  private selectedOrderId: string | null;
  private nextOrderSequence: number;
  private selectedPackageId: string | null = null;
  private hydrated = false;
  private listeners: Array<() => void> = [];

  public constructor(
    private readonly storage: OrderStorage,
    options: BuyerOrderRepositoryOptions = {},
  ) {
    this.orders = (options.seedOrders ?? [seedOrder]).map(cloneOrder);
    this.selectedOrderId = this.orders[0]?.orderId || null;
    this.nextOrderSequence = options.initialSequence ?? 1843;
  }

  public subscribe(listener: () => void): () => void {
    this.listeners.push(listener);
    return () => {
      this.listeners = this.listeners.filter((candidate) => candidate !== listener);
    };
  }

  private notify() {
    this.listeners.forEach((listener) => listener());
  }

  public async hydrate(): Promise<void> {
    if (this.hydrated) return;
    const stored = await this.storage.getItem(BUYER_ORDER_STORAGE_KEY);
    if (stored) {
      try {
        const parsed = JSON.parse(stored) as Partial<PersistedBuyerOrderState>;
        if (Array.isArray(parsed.orders) && parsed.orders.every(isBuyerOrder)) {
          this.orders = parsed.orders.map(cloneOrder);
          this.selectedOrderId = typeof parsed.selectedOrderId === 'string'
            && this.orders.some((order) => order.orderId === parsed.selectedOrderId)
            ? parsed.selectedOrderId
            : this.orders[0]?.orderId || null;
          this.nextOrderSequence = typeof parsed.nextOrderSequence === 'number'
            ? parsed.nextOrderSequence
            : Math.max(1843, 1842 + this.orders.length);
        }
      } catch {
        // Keep deterministic seed state when persisted prototype data is corrupt.
      }
    }
    this.hydrated = true;
    this.notify();
  }

  public isHydrated(): boolean {
    return this.hydrated;
  }

  public getOrders(): BuyerOrder[] {
    return this.orders.map(cloneOrder);
  }

  public getOrderById(orderId: string): BuyerOrder | null {
    const order = this.orders.find((candidate) => candidate.orderId === orderId);
    return order ? cloneOrder(order) : null;
  }

  public getSelectedOrderId(): string | null {
    return this.selectedOrderId;
  }

  public getSelectedOrder(): BuyerOrder | null {
    return this.selectedOrderId ? this.getOrderById(this.selectedOrderId) : null;
  }

  public getSelectedPackageId(): string | null {
    return this.selectedPackageId;
  }

  public selectPackage(packageId: string | null): boolean {
    if (packageId !== null) {
      const selectedOrder = this.getSelectedOrder();
      if (!selectedOrder?.packages.some((pkg) => pkg.packageId === packageId)) return false;
    }
    this.selectedPackageId = packageId;
    this.notify();
    return true;
  }

  public async selectOrder(orderId: string | null): Promise<boolean> {
    if (orderId !== null && !this.orders.some((order) => order.orderId === orderId)) return false;
    this.selectedOrderId = orderId;
    this.selectedPackageId = null;
    this.notify();
    await this.persist();
    return true;
  }

  public async createOrder(input: CreateBuyerOrderInput): Promise<CreateBuyerOrderResult> {
    const existing = this.orders.find((order) => order.checkoutAttemptId === input.checkoutAttemptId);
    if (existing) {
      this.selectedOrderId = existing.orderId;
      this.selectedPackageId = null;
      this.notify();
      await this.persist();
      return { order: cloneOrder(existing), created: false };
    }

    const orderId = `MAY-${new Date(input.createdAt || Date.now()).getUTCFullYear()}-${String(this.nextOrderSequence).padStart(6, '0')}`;
    this.nextOrderSequence += 1;
    const order: BuyerOrder = {
      orderId,
      checkoutAttemptId: input.checkoutAttemptId,
      createdAt: input.createdAt || new Date().toISOString(),
      paymentReference: input.paymentMethod === 'cash-on-delivery'
        ? `PROTOTYPE-COD-${orderId}`
        : `PROTOTYPE-PENDING-${orderId}`,
      paymentMethod: input.paymentMethod,
      paymentStatus: input.paymentMethod === 'cash-on-delivery'
        ? 'cash_on_delivery_pending'
        : 'prototype_pending_confirmation',
      deliveryMethod: input.deliveryMethod,
      orderStatus: 'preparing',
      deliveryStatus: 'preparing',
      address: {
        name: input.address.name,
        phone: input.address.phone,
        addressLine: input.address.addressLine,
        city: input.address.city,
        postcode: input.address.postcode,
        zone: input.address.zone,
      },
      lines: input.cart.lines.map((line) => ({
        orderLineId: line.id,
        productId: line.productId,
        name: line.name,
        variantId: line.variantId,
        variantLabel: line.selectedVariantText || line.variant,
        quantity: line.quantity,
        unitPriceMad: line.unitPriceMad,
        imageUri: line.imageUri,
        sellerId: line.sellerId,
        id: line.id,
        variant: line.selectedVariantText || line.variant,
      })),
      totalMad: getCartTotals(input.cart).subtotalMad,
      trackingEvents: [],
      packages: [],
      invoice: null,
      id: orderId,
      idempotencyKey: input.checkoutAttemptId,
      createdAtLabel: new Date(input.createdAt || Date.now()).toLocaleString('fr-MA'),
    };

    this.orders = [order, ...this.orders];
    this.selectedOrderId = order.orderId;
    this.selectedPackageId = null;
    this.notify();
    await this.persist();
    return { order: cloneOrder(order), created: true };
  }

  public async clearPrototypeOrders(): Promise<void> {
    this.orders = [];
    this.selectedOrderId = null;
    this.selectedPackageId = null;
    this.nextOrderSequence = 1843;
    this.hydrated = true;
    this.notify();
    await this.storage.removeItem(BUYER_ORDER_STORAGE_KEY);
  }

  private async persist(): Promise<void> {
    const state: PersistedBuyerOrderState = {
      orders: this.orders.map(cloneOrder),
      selectedOrderId: this.selectedOrderId,
      nextOrderSequence: this.nextOrderSequence,
    };
    await this.storage.setItem(BUYER_ORDER_STORAGE_KEY, JSON.stringify(state));
  }
}

export const createBuyerOrderRepository = (
  storage: OrderStorage,
  options: BuyerOrderRepositoryOptions = {},
): BuyerOrderRepository => new BuyerOrderRepository(storage, options);

export const orderState = createBuyerOrderRepository(AsyncStorage);

export const getOrderCreatedAtLabel = (order: BuyerOrder, locale: 'fr' | 'ar' = 'fr'): string => {
  try {
    return new Intl.DateTimeFormat(locale === 'ar' ? 'ar-MA' : 'fr-MA', {
      dateStyle: 'medium',
      timeStyle: 'short',
    }).format(new Date(order.createdAt));
  } catch {
    return order.createdAt;
  }
};

export const getPaymentStatusLabel = (status: PaymentStatus, language: 'fr' | 'ar' = 'fr'): string => {
  const labels = language === 'ar'
    ? {
      prototype_pending_confirmation: 'تأكيد الدفع عبر الخادم معلق',
      cash_on_delivery_pending: 'الدفع عند التسليم',
      confirmed: 'تم تأكيد الدفع',
      failed: 'فشل الدفع',
      cancelled: 'تم إلغاء الدفع',
    }
    : {
      prototype_pending_confirmation: 'Confirmation backend en attente',
      cash_on_delivery_pending: 'À payer à la livraison',
      confirmed: 'Paiement confirmé',
      failed: 'Paiement échoué',
      cancelled: 'Paiement annulé',
    };
  return labels[status];
};

export const getDeliveryStatusLabel = (status: DeliveryStatus, language: 'fr' | 'ar' = 'fr'): string => {
  const labels = language === 'ar'
    ? {
      preparing: 'قيد التحضير', shipped: 'تم الشحن', in_transit: 'في الطريق', delivered: 'تم التوصيل', cancelled: 'ملغى', returning: 'قيد الإرجاع', returned: 'تم الإرجاع',
    }
    : {
      preparing: 'En préparation', shipped: 'Expédiée', in_transit: 'En transit', delivered: 'Livrée', cancelled: 'Annulée', returning: 'Retour en cours', returned: 'Retournée',
    };
  return labels[status];
};
