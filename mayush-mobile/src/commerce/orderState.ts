import AsyncStorage from '@react-native-async-storage/async-storage';
import { CartState, getCartTotals, parseMadPrice } from './cartState';
import { DeliveryMethod, FrontendPaymentVerificationScenario, PaymentMethod, SavedAddress } from './checkoutState';
import { orderService } from '../services/api/orderService';
import { authState } from './authState';

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
  occurredAt?: string;
  labelKey: string;
  location?: string;
  state: 'completed' | 'current' | 'upcoming';
}

export interface OrderPackage {
  packageId: string;
  sellerId?: string;
  sellerName: string;
  status: DeliveryStatus;
  orderLineIds: string[];
  carrier?: string;
  trackingNumber?: string;
  shippedAt?: string;
  estimatedDeliveryAt?: string;
  trackingEvents: OrderTrackingEvent[];
}

export interface OrderInvoiceMetadata {
  invoiceId: string;
  invoiceNumber: string;
  issuedAt: string;
  downloadState: 'not_requested' | 'frontend_fixture' | 'backend_pending';
  previewAvailable: boolean;
  billingName: string;
  billingEmail?: string;
  billingPhone?: string;
  billingAddress: OrderAddressSnapshot;
}

export interface BuyerOrder {
  orderId: string;
  checkoutAttemptId: string;
  createdAt: string;
  paymentReference: string;
  paymentMethod: PaymentMethod;
  paymentPreferenceId?: string;
  paymentCardLast4?: string;
  paymentVerificationScenario?: FrontendPaymentVerificationScenario;
  paymentStatus: PaymentStatus;
  deliveryMethod: DeliveryMethod;
  orderStatus: OrderStatus;
  deliveryStatus: DeliveryStatus;
  address: OrderAddressSnapshot;
  lines: OrderLine[];
  deliveryFeeMad: number;
  deliveryPackageCount?: number;
  discountMad: number;
  totalMad: number;
  promotionId?: string;
  promotionCode?: string;
  carrier?: string;
  trackingNumber?: string;
  shippedAt?: string;
  estimatedDeliveryAt?: string;
  deliveredAt?: string;
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
  deliveryFeeMad?: number;
  deliveryPackageCount?: number;
  paymentPreferenceId?: string;
  paymentCardLast4?: string;
  paymentVerificationScenario?: FrontendPaymentVerificationScenario;
  createdAt?: string;
}

export interface RetryBuyerOrderPaymentInput {
  paymentMethod: PaymentMethod;
  paymentPreferenceId?: string;
  paymentCardLast4?: string;
  paymentVerificationScenario?: FrontendPaymentVerificationScenario;
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

const legacySeedOrder: BuyerOrder = {
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
  deliveryFeeMad: 0,
  discountMad: 0,
  totalMad: 2950,
  trackingEvents: [],
  packages: [],
  invoice: null,
  id: 'MAY-2026-001842',
  idempotencyKey: 'seed-order-001842',
  createdAtLabel: '28 mai 2026 Ã  10:24',
};

const standardAddress: OrderAddressSnapshot = {
  name: 'Ahmed Mansouri',
  phone: '+212 6 12 34 56 78',
  addressLine: '12 Rue des Oiseaux, Appt 5',
  city: 'Casablanca',
  postcode: '20000',
  zone: 'Maarif',
};

const billingAddress: OrderAddressSnapshot = {
  name: 'Youssef El Amrani',
  phone: '+212 6 12 34 56 78',
  addressLine: '25, Rue des Alizés, Quartier Oasis',
  city: 'Casablanca',
  postcode: '20250',
  zone: 'Oasis',
};

const makeLine = (
  orderLineId: string,
  productId: number,
  name: string,
  variantLabel: string,
  unitPriceMad: number,
  sellerId: string,
  quantity = 1,
): OrderLine => ({
  orderLineId,
  productId,
  name,
  variantLabel,
  quantity,
  unitPriceMad,
  sellerId,
  id: orderLineId,
  variant: variantLabel,
});

const preparationOrder: BuyerOrder = {
  ...legacySeedOrder,
  paymentStatus: 'confirmed',
  address: standardAddress,
  lines: [
    makeLine('line-1842-dune', 201, 'Canapé Dune 3 places', 'Couleur : Beige · Tissu : Lin naturel', 7890, 'seller-atlas'),
    makeLine('line-1842-noya', 202, 'Table à manger Noya', 'Bois : Chêne massif · Finition : Naturelle', 5490, 'seller-atlas'),
  ],
  deliveryFeeMad: 0,
  discountMad: 850,
  totalMad: 12530,
  estimatedDeliveryAt: '2026-06-02T18:00:00.000Z',
  trackingEvents: [
    { trackingEventId: 'track-1842-confirmed', status: 'preparing', occurredAt: '2026-05-28T10:24:00.000Z', labelKey: 'confirmed', state: 'completed' },
    { trackingEventId: 'track-1842-preparing', status: 'preparing', occurredAt: '2026-05-28T11:15:00.000Z', labelKey: 'preparing', state: 'current' },
    { trackingEventId: 'track-1842-shipped', status: 'shipped', labelKey: 'shipped', state: 'upcoming' },
    { trackingEventId: 'track-1842-transit', status: 'in_transit', labelKey: 'in_transit', state: 'upcoming' },
    { trackingEventId: 'track-1842-delivered', status: 'delivered', labelKey: 'delivered', state: 'upcoming' },
  ],
  createdAtLabel: '28 mai 2026 à 10:24',
};

const shippedOrder: BuyerOrder = {
  ...preparationOrder,
  orderId: 'MAY-2026-001841',
  checkoutAttemptId: 'seed-order-001841',
  paymentReference: 'PROTOTYPE-PAY-001841',
  orderStatus: 'shipped',
  deliveryStatus: 'shipped',
  deliveryFeeMad: 30,
  discountMad: 0,
  totalMad: 13410,
  carrier: 'Mayush Delivery',
  trackingNumber: 'TRK-798654321MA',
  shippedAt: '2026-05-28T14:32:00.000Z',
  trackingEvents: [
    { trackingEventId: 'track-1841-confirmed', status: 'preparing', occurredAt: '2026-05-28T10:24:00.000Z', labelKey: 'confirmed', state: 'completed' },
    { trackingEventId: 'track-1841-preparing', status: 'preparing', occurredAt: '2026-05-28T11:15:00.000Z', labelKey: 'preparing', state: 'completed' },
    { trackingEventId: 'track-1841-carrier', status: 'shipped', occurredAt: '2026-05-28T14:32:00.000Z', labelKey: 'handed_to_carrier', state: 'current' },
    { trackingEventId: 'track-1841-transit', status: 'in_transit', labelKey: 'in_transit', state: 'upcoming' },
    { trackingEventId: 'track-1841-out', status: 'in_transit', labelKey: 'out_for_delivery', state: 'upcoming' },
    { trackingEventId: 'track-1841-delivered', status: 'delivered', labelKey: 'delivered', state: 'upcoming' },
  ],
  id: 'MAY-2026-001841',
  idempotencyKey: 'seed-order-001841',
};

const deliveredOrder: BuyerOrder = {
  ...preparationOrder,
  orderId: 'MAY-2026-001838',
  checkoutAttemptId: 'seed-order-001838',
  paymentReference: 'PROTOTYPE-PAY-001838',
  orderStatus: 'delivered',
  deliveryStatus: 'delivered',
  lines: [
    makeLine('line-1838-dune', 201, 'CanapÃ© Dune 3 places', 'Couleur : Beige Â· Tissu : Lin naturel', 7890, 'seller-atlas'),
    makeLine('line-1838-noya', 202, 'Table Ã  manger Noya', 'Bois : ChÃªne massif Â· Finition : Naturelle', 5490, 'seller-atlas'),
    makeLine('line-1838-lune', 203, 'Suspension Lune Noire', 'Couleur : Noir mat', 890, 'seller-noya'),
  ],
  deliveryFeeMad: 30,
  discountMad: 0,
  totalMad: 14300,
  carrier: 'Mayush Delivery',
  trackingNumber: 'MYD587392641',
  shippedAt: '2026-05-28T14:32:00.000Z',
  estimatedDeliveryAt: '2026-05-30T18:00:00.000Z',
  deliveredAt: '2026-05-30T12:15:00.000Z',
  trackingEvents: [
    { trackingEventId: 'track-1838-confirmed', status: 'preparing', occurredAt: '2026-05-28T10:24:00.000Z', labelKey: 'confirmed', state: 'completed' },
    { trackingEventId: 'track-1838-preparing', status: 'preparing', occurredAt: '2026-05-28T11:15:00.000Z', labelKey: 'preparing', state: 'completed' },
    { trackingEventId: 'track-1838-shipped', status: 'shipped', occurredAt: '2026-05-28T14:32:00.000Z', labelKey: 'shipped', state: 'completed' },
    { trackingEventId: 'track-1838-transit', status: 'in_transit', occurredAt: '2026-05-29T08:45:00.000Z', labelKey: 'in_transit', state: 'completed' },
    { trackingEventId: 'track-1838-delivered', status: 'delivered', occurredAt: '2026-05-30T12:15:00.000Z', labelKey: 'delivered', state: 'completed' },
  ],
  invoice: {
    invoiceId: 'invoice-1838', invoiceNumber: 'FAC-2026-001838', issuedAt: '2026-05-30T12:15:00.000Z',
    downloadState: 'frontend_fixture', previewAvailable: true, billingName: billingAddress.name,
    billingEmail: 'youssef.elamrani@email.com', billingPhone: billingAddress.phone, billingAddress,
  },
  id: 'MAY-2026-001838',
  idempotencyKey: 'seed-order-001838',
};

const multiPackageOrder: BuyerOrder = {
  ...deliveredOrder,
  orderId: 'MAY-2026-001835',
  checkoutAttemptId: 'seed-order-001835',
  paymentReference: 'PROTOTYPE-PAY-001835',
  orderStatus: 'in_transit',
  deliveryStatus: 'in_transit',
  deliveredAt: undefined,
  lines: [
    makeLine('line-1835-dune', 201, 'Canapé Dune 3 places', 'Couleur : Beige', 7890, 'seller-atlas'),
    makeLine('line-1835-noya', 202, 'Table à manger Noya', 'Couleur : Chêne naturel', 5490, 'seller-home'),
    makeLine('line-1835-lune', 203, 'Suspension Lune Noire', 'Couleur : Noir mat', 890, 'seller-atlas'),
    makeLine('line-1835-cushion', 204, 'Coussin Lin Beige', 'Lin naturel', 500, 'seller-deco', 2),
  ],
  deliveryFeeMad: 0,
  totalMad: 15270,
  carrier: undefined,
  trackingNumber: undefined,
  invoice: {
    invoiceId: 'invoice-1835', invoiceNumber: 'FAC-2026-001835', issuedAt: '2026-05-28T10:24:00.000Z',
    downloadState: 'frontend_fixture', previewAvailable: true, billingName: billingAddress.name,
    billingEmail: 'youssef.elamrani@email.com', billingPhone: billingAddress.phone, billingAddress,
  },
  packages: [
    {
      packageId: 'MAY-2026-001835-01', sellerId: 'seller-atlas', sellerName: 'Maison Atlas', status: 'in_transit',
      orderLineIds: ['line-1835-dune', 'line-1835-lune'], carrier: 'Mayush Delivery', trackingNumber: 'MDL826479312FR',
      shippedAt: '2026-05-19T11:30:00.000Z', estimatedDeliveryAt: '2026-05-28T18:00:00.000Z',
      trackingEvents: [
        { trackingEventId: 'pkg-01-confirmed', status: 'preparing', occurredAt: '2026-05-18T16:40:00.000Z', labelKey: 'confirmed', state: 'completed' },
        { trackingEventId: 'pkg-01-ready', status: 'preparing', occurredAt: '2026-05-19T09:15:00.000Z', labelKey: 'preparation_complete', state: 'completed' },
        { trackingEventId: 'pkg-01-shipped', status: 'shipped', occurredAt: '2026-05-19T11:30:00.000Z', labelKey: 'package_shipped', state: 'current' },
      ],
    },
    {
      packageId: 'MAY-2026-001835-02', sellerId: 'seller-home', sellerName: 'Home & Living', status: 'shipped',
      orderLineIds: ['line-1835-noya'], carrier: 'Aramex', trackingNumber: 'ARX00183502',
      shippedAt: '2026-05-27T08:30:00.000Z', estimatedDeliveryAt: '2026-05-30T18:00:00.000Z', trackingEvents: [],
    },
    {
      packageId: 'MAY-2026-001835-03', sellerId: 'seller-deco', sellerName: 'Deco Casa', status: 'shipped',
      orderLineIds: ['line-1835-cushion'], carrier: 'Chronopost', trackingNumber: 'CHR00183503',
      shippedAt: '2026-05-27T12:00:00.000Z', estimatedDeliveryAt: '2026-06-02T18:00:00.000Z', trackingEvents: [],
    },
  ],
  id: 'MAY-2026-001835',
  idempotencyKey: 'seed-order-001835',
};

const cancelledRefundEligibleOrder: BuyerOrder = {
  ...preparationOrder,
  orderId: 'MAY-2026-001257',
  checkoutAttemptId: 'seed-order-001257',
  createdAt: '2026-05-10T09:12:00.000Z',
  paymentReference: 'PROTOTYPE-PAY-001257',
  paymentMethod: 'cmi',
  paymentStatus: 'confirmed',
  orderStatus: 'cancelled',
  deliveryStatus: 'cancelled',
  lines: [makeLine('line-1257-cushion', 204, 'Coussin DÃ©co Lin Beige', 'Lin naturel', 450, 'seller-deco')],
  deliveryFeeMad: 0,
  discountMad: 0,
  totalMad: 450,
  carrier: undefined,
  trackingNumber: undefined,
  shippedAt: undefined,
  estimatedDeliveryAt: undefined,
  deliveredAt: undefined,
  trackingEvents: [],
  packages: [],
  invoice: null,
  id: 'MAY-2026-001257',
  idempotencyKey: 'seed-order-001257',
  createdAtLabel: '10 mai 2026 Ã  09:12',
};

export const canonicalBuyerOrderFixtures: BuyerOrder[] = [
  preparationOrder,
  shippedOrder,
  deliveredOrder,
  multiPackageOrder,
  cancelledRefundEligibleOrder,
];

const cloneOrder = (order: BuyerOrder): BuyerOrder => {
  const { selectedPackageId: _legacyUiSelection, ...orderData } = order as BuyerOrder & {
    selectedPackageId?: string | null;
  };
  return {
    ...orderData,
    address: { ...order.address },
    deliveryFeeMad: order.deliveryFeeMad ?? 0,
    discountMad: order.discountMad ?? 0,
    lines: order.lines.map((line) => ({
      ...line,
      id: line.orderLineId,
      variant: line.variantLabel,
    })),
    trackingEvents: order.trackingEvents.map((event) => ({
      ...event,
      state: event.state ?? (event.occurredAt ? 'completed' : 'upcoming'),
    })),
    packages: order.packages.map((pkg) => ({
      ...pkg,
      sellerName: pkg.sellerName || pkg.sellerId || 'Mayush Design',
      orderLineIds: [...pkg.orderLineIds],
      trackingEvents: (pkg.trackingEvents || []).map((event) => ({ ...event })),
    })),
    invoice: order.invoice ? {
      ...order.invoice,
      previewAvailable: order.invoice.previewAvailable ?? false,
      billingName: order.invoice.billingName || order.address.name,
      billingAddress: { ...(order.invoice.billingAddress || order.address) },
    } : null,
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
  private readonly seedOrders?: BuyerOrder[];

  public constructor(
    private readonly storage: OrderStorage,
    options: BuyerOrderRepositoryOptions = {},
  ) {
    this.seedOrders = options.seedOrders;
    this.orders = (options.seedOrders ?? canonicalBuyerOrderFixtures).map(cloneOrder);
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
    let serverOrdersLoaded = false;
    if (authState.isAuthenticated()) {
      try {
        const history = await orderService.getPurchaseHistory();
        if (history?.data && Array.isArray(history.data) && history.data.length > 0) {
          const serverOrders: BuyerOrder[] = history.data.map((item) => ({
            orderId: String(item.id),
            checkoutAttemptId: `attempt-server-${item.id}`,
            createdAt: item.date || new Date().toISOString(),
            orderStatus: (item.delivery_status === 'delivered' ? 'delivered' : item.delivery_status === 'cancelled' ? 'cancelled' : 'confirmed') as OrderStatus,
            deliveryStatus: (item.delivery_status === 'delivered' ? 'delivered' : item.delivery_status === 'cancelled' ? 'cancelled' : 'preparing') as DeliveryStatus,
            paymentStatus: (item.payment_status === 'paid' ? 'confirmed' : 'cash_on_delivery_pending') as PaymentStatus,
            paymentMethod: (item.payment_type === 'cmi' ? 'cmi' : 'cash-on-delivery') as PaymentMethod,
            deliveryMethod: 'standard',
            deliveryFeeMad: 0,
            deliveryPackageCount: 1,
            totalMad: parseMadPrice(item.grand_total || '0 MAD'),
            discountMad: 0,
            paymentReference: `SERVER-${item.id}`,
            invoice: null,
            lines: [],
            packages: [],
            trackingEvents: [],
            address: {
              name: 'Client Mayush',
              phone: '+212 6 00 00 00 00',
              addressLine: 'Adresse de livraison',
              city: 'Casablanca',
              postcode: '20000',
              zone: 'Grand Casablanca',
            },
            id: String(item.id),
            idempotencyKey: `attempt-server-${item.id}`,
            createdAtLabel: item.date || new Date().toLocaleDateString('fr-MA'),
          }));
          // Authenticated + server returned orders: use only server orders (no fixtures).
          // Locally-created orders (checkoutAttemptId does not start with 'attempt-server-') are preserved.
          const localOnlyOrders = this.orders.filter(
            (o) => !o.checkoutAttemptId.startsWith('attempt-server-')
              && !canonicalBuyerOrderFixtures.some((f) => f.orderId === o.orderId),
          );
          const serverIds = new Set(serverOrders.map((o) => o.orderId));
          const localNotOnServer = localOnlyOrders.filter((o) => !serverIds.has(o.orderId));
          this.orders = [...serverOrders, ...localNotOnServer];
          serverOrdersLoaded = true;
        }
      } catch {
        // Keep offline cached orders
      }
    }
    // If no server data was loaded and no persisted data existed, seed fixtures as demo/fallback.
    if (!serverOrdersLoaded && !stored) {
      this.orders = (this.seedOrders ?? canonicalBuyerOrderFixtures).map(cloneOrder);
      this.selectedOrderId = this.orders[0]?.orderId || null;
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

  public getOrderByCheckoutAttemptId(checkoutAttemptId: string): BuyerOrder | null {
    const order = this.orders.find((candidate) => candidate.checkoutAttemptId === checkoutAttemptId);
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

  public getSelectedPackage(): OrderPackage | null {
    const order = this.getSelectedOrder();
    if (!order || !this.selectedPackageId) return null;
    const selectedPackage = order.packages.find((pkg) => pkg.packageId === this.selectedPackageId);
    return selectedPackage ? {
      ...selectedPackage,
      orderLineIds: [...selectedPackage.orderLineIds],
      trackingEvents: selectedPackage.trackingEvents.map((event) => ({ ...event })),
    } : null;
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
    const cartTotals = getCartTotals(input.cart);
    const order: BuyerOrder = {
      orderId,
      checkoutAttemptId: input.checkoutAttemptId,
      createdAt: input.createdAt || new Date().toISOString(),
      paymentReference: input.paymentMethod === 'cash-on-delivery'
        ? `PROTOTYPE-COD-${orderId}`
        : input.paymentMethod === 'wallet'
          ? `PROTOTYPE-WALLET-${orderId}`
          : `PROTOTYPE-CARD-${input.paymentCardLast4 || 'SAFE'}-${orderId}`,
      paymentMethod: input.paymentMethod,
      paymentPreferenceId: input.paymentPreferenceId,
      paymentCardLast4: input.paymentCardLast4,
      paymentVerificationScenario: input.paymentVerificationScenario,
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
      deliveryFeeMad: Math.max(0, Math.round(input.deliveryFeeMad || 0)),
      deliveryPackageCount: input.deliveryPackageCount,
      discountMad: cartTotals.discountMad,
      totalMad: cartTotals.totalMad + Math.max(0, Math.round(input.deliveryFeeMad || 0)),
      promotionId: cartTotals.appliedPromotionId,
      promotionCode: cartTotals.promotionCode,
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

  public async transitionPaymentStatus(checkoutAttemptId: string, nextStatus: PaymentStatus): Promise<BuyerOrder | null> {
    const index = this.orders.findIndex((order) => order.checkoutAttemptId === checkoutAttemptId);
    if (index < 0) return null;
    const current = this.orders[index];
    const allowed: Record<PaymentStatus, readonly PaymentStatus[]> = {
      prototype_pending_confirmation: ['confirmed', 'failed', 'cancelled'],
      cash_on_delivery_pending: ['confirmed', 'cancelled'],
      confirmed: [],
      failed: ['prototype_pending_confirmation'],
      cancelled: ['prototype_pending_confirmation'],
    };
    if (current.paymentStatus !== nextStatus && !allowed[current.paymentStatus].includes(nextStatus)) return null;
    this.orders[index] = { ...current, paymentStatus: nextStatus };
    this.selectedOrderId = current.orderId;
    this.notify();
    await this.persist();
    return cloneOrder(this.orders[index]);
  }

  public async preparePaymentRetry(checkoutAttemptId: string, input: RetryBuyerOrderPaymentInput): Promise<BuyerOrder | null> {
    const index = this.orders.findIndex((order) => order.checkoutAttemptId === checkoutAttemptId);
    if (index < 0 || this.orders[index].paymentStatus === 'confirmed') return null;
    const current = this.orders[index];
    const paymentStatus: PaymentStatus = input.paymentMethod === 'cash-on-delivery'
      ? 'cash_on_delivery_pending'
      : 'prototype_pending_confirmation';
    const paymentReference = input.paymentMethod === 'cash-on-delivery'
      ? `PROTOTYPE-COD-${current.orderId}`
      : input.paymentMethod === 'wallet'
        ? `PROTOTYPE-WALLET-${current.orderId}`
        : `PROTOTYPE-CARD-${input.paymentCardLast4 || 'SAFE'}-${current.orderId}`;
    this.orders[index] = {
      ...current,
      paymentMethod: input.paymentMethod,
      paymentPreferenceId: input.paymentPreferenceId,
      paymentCardLast4: input.paymentCardLast4,
      paymentVerificationScenario: input.paymentVerificationScenario,
      paymentReference,
      paymentStatus,
    };
    this.selectedOrderId = current.orderId;
    this.selectedPackageId = null;
    this.notify();
    await this.persist();
    return cloneOrder(this.orders[index]);
  }

  public async cancelOrder(orderId: string): Promise<boolean> {
    const user = authState.getUser();
    if (user) {
      try {
        const response = await orderService.cancelOrder(Number(orderId));
        if (response.result) {
          const idx = this.orders.findIndex((o) => o.orderId === orderId);
          if (idx >= 0) {
            this.orders[idx] = { ...this.orders[idx], orderStatus: 'cancelled', deliveryStatus: 'cancelled' };
            this.notify();
            await this.persist();
          }
          return true;
        }
      } catch { /* silent */ }
    }
    return false;
  }

  public async submitRefundRequest(orderId: string, reason: string): Promise<boolean> {
    const user = authState.getUser();
    if (!user) return false;
    try {
      const response = await orderService.submitRefundRequest({
        orderDetailId: Number(orderId),
        reason,
      });
      if (response.success) {
        const idx = this.orders.findIndex((o) => o.orderId === orderId);
        if (idx >= 0) {
          this.orders[idx] = { ...this.orders[idx], orderStatus: 'return_requested' };
          this.notify();
          await this.persist();
        }
        return true;
      }
    } catch { /* silent */ }
    return false;
  }

  public async reOrder(orderId: string): Promise<{ successCount: number; failedCount: number }> {
    const user = authState.getUser();
    if (!user) return { successCount: 0, failedCount: 0 };
    try {
      const response = await orderService.reOrder(Number(orderId));
      return {
        successCount: response.success_msgs?.length ?? 0,
        failedCount: response.failed_msgs?.length ?? 0,
      };
    } catch {
      return { successCount: 0, failedCount: 0 };
    }
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

export type CanonicalOrderDetailRoute =
  | 'order-detail-preparing'
  | 'order-detail-shipped'
  | 'order-detail-delivered'
  | 'order-detail-multi-vendor'
  | 'order-refund-request';

export const getCanonicalOrderDetailRoute = (order: BuyerOrder): CanonicalOrderDetailRoute => {
  if (order.orderStatus === 'cancelled' && order.deliveryStatus === 'cancelled') return 'order-refund-request';
  if (order.packages.length > 1) return 'order-detail-multi-vendor';
  if (order.orderStatus === 'delivered') return 'order-detail-delivered';
  if (order.orderStatus === 'shipped' || order.orderStatus === 'in_transit') return 'order-detail-shipped';
  return 'order-detail-preparing';
};

export const getOrderPackageLines = (order: BuyerOrder, packageId: string): OrderLine[] => {
  const pkg = order.packages.find((candidate) => candidate.packageId === packageId);
  if (!pkg) return [];
  const linesById = new Map(order.lines.map((line) => [line.orderLineId, line]));
  if (pkg.orderLineIds.some((lineId) => !linesById.has(lineId))) return [];
  return pkg.orderLineIds.map((lineId) => ({ ...linesById.get(lineId)! }));
};

export interface OrderPackageValidationResult {
  valid: boolean;
  missingLineIds: string[];
  duplicateLineIds: string[];
}

export const validateOrderPackages = (order: BuyerOrder): OrderPackageValidationResult => {
  const validLineIds = new Set(order.lines.map((line) => line.orderLineId));
  const seenLineIds = new Set<string>();
  const missingLineIds = new Set<string>();
  const duplicateLineIds = new Set<string>();
  order.packages.forEach((pkg) => {
    pkg.orderLineIds.forEach((lineId) => {
      if (!validLineIds.has(lineId)) missingLineIds.add(lineId);
      if (seenLineIds.has(lineId)) duplicateLineIds.add(lineId);
      seenLineIds.add(lineId);
    });
  });
  return {
    valid: missingLineIds.size === 0 && duplicateLineIds.size === 0,
    missingLineIds: [...missingLineIds],
    duplicateLineIds: [...duplicateLineIds],
  };
};

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
