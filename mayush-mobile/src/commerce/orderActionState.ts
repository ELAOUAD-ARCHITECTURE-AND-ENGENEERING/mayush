import AsyncStorage from '@react-native-async-storage/async-storage';
import { addCartLine, CartState, createSelectedVariantCartLine } from './cartState';
import { BuyerOrder, OrderLine } from './orderState';

export const BUYER_ORDER_ACTION_STORAGE_KEY = 'mayush-mobile:buyer-order-actions:v1';

export type CancellationReasonKey =
  | 'ordered_by_mistake'
  | 'modify_products'
  | 'delivery_too_long'
  | 'payment_problem'
  | 'other';

export interface CancellationDraft {
  orderId: string;
  reasonKey: CancellationReasonKey | null;
  message: string;
}

export interface CancellationRequestRecord {
  cancellationRequestId: string;
  orderId: string;
  reasonKey: CancellationReasonKey;
  message?: string;
  requestedAt: string;
  status: 'frontend_requested';
  refundStatus: 'not_started';
}

export interface ProductReviewDraftEntry {
  orderLineId: string;
  productId: number | string;
  rating: number;
}

export interface ProductReviewRecord extends ProductReviewDraftEntry {
  reviewId: string;
  orderId: string;
  submittedAt: string;
  submissionState: 'frontend_only';
}

export type ReturnReasonKey =
  | 'damaged_on_delivery'
  | 'not_as_described'
  | 'size_or_dimensions'
  | 'changed_mind';

export type ReturnStatus = 'requested' | 'approved' | 'received' | 'inspection' | 'completed' | 'rejected';
export type RefundStatus = 'not_started' | 'pending' | 'processing' | 'completed' | 'failed';

export interface ReturnRequestDraftLine {
  orderLineId: string;
  packageId?: string;
  quantity: number;
}

export interface ReturnRequestDraft {
  orderId: string;
  selectedLines: ReturnRequestDraftLine[];
  reasonKey: ReturnReasonKey | null;
  message: string;
}

export interface ReturnRequestLine extends ReturnRequestDraftLine {
  reasonKey: ReturnReasonKey;
  requestedUnitPriceMad: number;
}

export interface ReturnTrackingEvent {
  returnTrackingEventId: string;
  returnRequestId: string;
  labelKey: 'request_created' | 'approved' | 'parcel_received' | 'inspection' | 'refund_processing' | 'refunded';
  state: 'completed' | 'current' | 'upcoming';
  occurredAt?: string;
}

export interface ReturnRequestRecord {
  returnRequestId: string;
  orderId: string;
  selectedLines: ReturnRequestLine[];
  message?: string;
  requestedAt: string;
  status: ReturnStatus;
  refundStatus: RefundStatus;
  requestedRefundAmountMad: number;
  trackingEvents: ReturnTrackingEvent[];
  recordSource: 'frontend_fixture';
}

export interface CancelledOrderRefundDraft {
  orderId: string;
  cancellationRequestId?: string;
  requestedAmountMad: number;
}

export interface RefundRecord {
  refundId: string;
  orderId: string;
  source: 'return' | 'cancelled_order';
  returnRequestId?: string;
  cancellationRequestId?: string;
  requestedAmountMad: number;
  completedAmountMad?: number;
  requestedAt: string;
  processingAt?: string;
  completedAt?: string;
  status: RefundStatus;
  recordSource: 'frontend_fixture';
}

export type DeliveryIssueType = 'delayed' | 'delivery_failed';
export type DeliveryIssueStatus = 'active' | 'reschedule_requested';

export interface DeliveryIssueRecord {
  deliveryIssueId: string;
  orderId: string;
  packageId?: string;
  type: DeliveryIssueType;
  status: DeliveryIssueStatus;
  carrier: string;
  reasonKey: 'sorting_center_delay' | 'recipient_absent';
  occurredAt: string;
  expectedDeliveryAt?: string;
  revisedDeliveryAt?: string;
  recordSource: 'frontend_fixture';
}

export interface DeliveryRescheduleRequestRecord {
  rescheduleRequestId: string;
  deliveryIssueId: string;
  orderId: string;
  packageId?: string;
  requestedSlot: string;
  requestedAt: string;
  status: 'frontend_requested';
  recordSource: 'frontend_fixture';
}

export interface CurrentCatalogItem {
  productId: number | string;
  available: boolean;
  currentVariantId?: string;
  currentVariantLabel?: string;
  currentUnitPriceMad?: number;
  maxQuantity?: number;
}

export type ReorderLineState = 'available' | 'changed' | 'unavailable';

export interface ReorderPlanLine {
  orderLineId: string;
  productId: number | string;
  name: string;
  sellerId?: string;
  imageUri?: string;
  historicalVariantId?: string;
  historicalVariantLabel: string;
  historicalQuantity: number;
  historicalUnitPriceMad: number;
  currentVariantId?: string;
  currentVariantLabel: string;
  currentQuantity: number;
  currentUnitPriceMad: number;
  state: ReorderLineState;
  changes: Array<'price' | 'variant' | 'quantity'>;
  selected: boolean;
}

export interface ReorderPlan {
  orderId: string;
  lines: ReorderPlanLine[];
  resultVariant: 'ready' | 'changed_unavailable' | 'availability_changes';
}

export interface ReorderCartResult {
  cart: CartState;
  addedLineIds: string[];
  ignoredLineIds: string[];
  addedSubtotalMad: number;
}

export interface BuyerOrderActionStorage {
  getItem(key: string): Promise<string | null>;
  setItem(key: string, value: string): Promise<void>;
  removeItem(key: string): Promise<void>;
}

interface PersistedBuyerOrderActions {
  cancellationRequests: CancellationRequestRecord[];
  productReviews: ProductReviewRecord[];
  returnRequests: ReturnRequestRecord[];
  refundRecords: RefundRecord[];
  deliveryIssues: DeliveryIssueRecord[];
  deliveryRescheduleRequests: DeliveryRescheduleRequestRecord[];
}

const DEFAULT_DELIVERY_ISSUES: DeliveryIssueRecord[] = [
  {
    deliveryIssueId: 'delivery-delay-MAY-2026-001841', orderId: 'MAY-2026-001841', type: 'delayed', status: 'active',
    carrier: 'Mayush Delivery', reasonKey: 'sorting_center_delay', occurredAt: '2026-05-26T14:32:00.000Z',
    expectedDeliveryAt: '2026-05-28T18:00:00.000Z', revisedDeliveryAt: '2026-05-30T18:00:00.000Z', recordSource: 'frontend_fixture',
  },
  {
    deliveryIssueId: 'delivery-failed-MAY-2026-001835-package-1', orderId: 'MAY-2026-001835', packageId: 'MAY-2026-001835-01', type: 'delivery_failed', status: 'active',
    carrier: 'Mayush Delivery', reasonKey: 'recipient_absent', occurredAt: '2026-05-28T15:42:00.000Z',
    revisedDeliveryAt: '2026-05-30T18:00:00.000Z', recordSource: 'frontend_fixture',
  },
];

const DEFAULT_CURRENT_CATALOG: CurrentCatalogItem[] = [
  { productId: 201, available: false },
  {
    productId: 202,
    available: true,
    currentVariantLabel: 'Bois : Chêne massif · Finition : Naturelle',
    currentUnitPriceMad: 5790,
    maxQuantity: 2,
  },
  {
    productId: 203,
    available: true,
    currentVariantId: 'blanc-mat',
    currentVariantLabel: 'Couleur : Blanc mat',
    currentUnitPriceMad: 899,
    maxQuantity: 1,
  },
  { productId: 204, available: true, currentVariantLabel: 'Lin naturel', currentUnitPriceMad: 490, maxQuantity: 4 },
];

const cloneCancellationRequest = (record: CancellationRequestRecord): CancellationRequestRecord => ({ ...record });
const cloneReview = (record: ProductReviewRecord): ProductReviewRecord => ({ ...record });
const cloneReturnRequest = (record: ReturnRequestRecord): ReturnRequestRecord => ({
  ...record,
  selectedLines: record.selectedLines.map((line) => ({ ...line })),
  trackingEvents: record.trackingEvents.map((event) => ({ ...event })),
});
const cloneRefundRecord = (record: RefundRecord): RefundRecord => ({ ...record });
const cloneDeliveryIssue = (record: DeliveryIssueRecord): DeliveryIssueRecord => ({ ...record });
const cloneDeliveryReschedule = (record: DeliveryRescheduleRequestRecord): DeliveryRescheduleRequestRecord => ({ ...record });

export const validateDeliveryIssueOwnership = (order: BuyerOrder, issue: DeliveryIssueRecord): boolean => (
  issue.orderId === order.orderId
  && (!issue.packageId || order.packages.some((orderPackage) => orderPackage.packageId === issue.packageId))
);
const cloneReturnDraft = (draft: ReturnRequestDraft | null): ReturnRequestDraft | null => draft ? ({
  ...draft,
  selectedLines: draft.selectedLines.map((line) => ({ ...line })),
}) : null;
const clonePlan = (plan: ReorderPlan | null): ReorderPlan | null => plan ? {
  ...plan,
  lines: plan.lines.map((line) => ({ ...line, changes: [...line.changes] })),
} : null;

export const canCancelOrder = (order: BuyerOrder): boolean => (
  ['created', 'confirmed', 'preparing'].includes(order.orderStatus)
  && order.deliveryStatus === 'preparing'
);

export const canReviewOrder = (order: BuyerOrder): boolean => (
  order.orderStatus === 'delivered' && order.deliveryStatus === 'delivered'
);

export const canReturnOrder = (order: BuyerOrder): boolean => (
  order.orderStatus === 'delivered' && order.deliveryStatus === 'delivered'
);

export const canReturnLine = (order: BuyerOrder, line: OrderLine): boolean => (
  canReturnOrder(order)
  && line.quantity > 0
  && order.lines.some((candidate) => candidate.orderLineId === line.orderLineId)
);

export const getOrderLinePackageId = (order: BuyerOrder, orderLineId: string): string | undefined => (
  order.packages.find((orderPackage) => orderPackage.orderLineIds.includes(orderLineId))?.packageId
);

export const validateReturnLineOwnership = (
  order: BuyerOrder,
  orderLineId: string,
  packageId?: string,
): boolean => {
  const line = order.lines.find((candidate) => candidate.orderLineId === orderLineId);
  if (!line || !canReturnLine(order, line)) return false;
  const actualPackageId = getOrderLinePackageId(order, orderLineId);
  return packageId === undefined || actualPackageId === packageId;
};

export const canRequestCancelledOrderRefund = (order: BuyerOrder): boolean => (
  order.orderStatus === 'cancelled'
  && order.deliveryStatus === 'cancelled'
  && order.paymentStatus === 'confirmed'
  && order.totalMad > 0
);

const normalizeCatalog = (items: CurrentCatalogItem[]): Map<string, CurrentCatalogItem> => new Map(
  items.map((item) => [String(item.productId), { ...item }]),
);

const buildPlanLine = (line: OrderLine, catalog: Map<string, CurrentCatalogItem>): ReorderPlanLine => {
  const current = catalog.get(String(line.productId));
  if (!current?.available) {
    return {
      orderLineId: line.orderLineId,
      productId: line.productId,
      name: line.name,
      sellerId: line.sellerId,
      imageUri: line.imageUri,
      historicalVariantId: line.variantId,
      historicalVariantLabel: line.variantLabel,
      historicalQuantity: line.quantity,
      historicalUnitPriceMad: line.unitPriceMad,
      currentVariantId: line.variantId,
      currentVariantLabel: line.variantLabel,
      currentQuantity: 0,
      currentUnitPriceMad: line.unitPriceMad,
      state: 'unavailable',
      changes: [],
      selected: false,
    };
  }

  const currentVariantId = current.currentVariantId ?? line.variantId;
  const currentVariantLabel = current.currentVariantLabel ?? line.variantLabel;
  const currentUnitPriceMad = current.currentUnitPriceMad ?? line.unitPriceMad;
  const currentQuantity = Math.min(line.quantity, Math.max(1, current.maxQuantity ?? line.quantity));
  const changes: ReorderPlanLine['changes'] = [];
  if (currentUnitPriceMad !== line.unitPriceMad) changes.push('price');
  if (currentVariantId !== line.variantId || currentVariantLabel !== line.variantLabel) changes.push('variant');
  if (currentQuantity !== line.quantity) changes.push('quantity');
  return {
    orderLineId: line.orderLineId,
    productId: line.productId,
    name: line.name,
    sellerId: line.sellerId,
    imageUri: line.imageUri,
    historicalVariantId: line.variantId,
    historicalVariantLabel: line.variantLabel,
    historicalQuantity: line.quantity,
    historicalUnitPriceMad: line.unitPriceMad,
    currentVariantId,
    currentVariantLabel,
    currentQuantity,
    currentUnitPriceMad,
    state: changes.length ? 'changed' : 'available',
    changes,
    selected: changes.length === 0,
  };
};

export const buildReorderPlan = (
  order: BuyerOrder,
  currentCatalog: CurrentCatalogItem[] = DEFAULT_CURRENT_CATALOG,
): ReorderPlan => {
  const catalog = normalizeCatalog(currentCatalog);
  const lines = order.lines.map((line) => buildPlanLine(line, catalog));
  const resultVariant: ReorderPlan['resultVariant'] = lines.some((line) => line.state === 'unavailable')
    ? 'changed_unavailable'
    : lines.some((line) => line.state === 'changed')
      ? 'availability_changes'
      : 'ready';
  return { orderId: order.orderId, lines, resultVariant };
};

export const addReorderPlanToCart = (plan: ReorderPlan, cart: CartState): ReorderCartResult => {
  let nextCart = { lines: cart.lines.map((line) => ({ ...line })) };
  const addedLineIds: string[] = [];
  const ignoredLineIds: string[] = [];
  let addedSubtotalMad = 0;
  plan.lines.forEach((line) => {
    if (!line.selected || line.state === 'unavailable' || line.currentQuantity <= 0) {
      ignoredLineIds.push(line.orderLineId);
      return;
    }
    nextCart = addCartLine(nextCart, createSelectedVariantCartLine({
      productId: line.productId,
      name: line.name,
      variant: line.currentVariantLabel,
      quantity: line.currentQuantity,
      unitPriceMad: line.currentUnitPriceMad,
      imageUri: line.imageUri,
      sellerId: line.sellerId,
    }));
    addedLineIds.push(line.orderLineId);
    addedSubtotalMad += line.currentUnitPriceMad * line.currentQuantity;
  });
  return { cart: nextCart, addedLineIds, ignoredLineIds, addedSubtotalMad };
};

export class BuyerOrderActionRepository {
  private cancellationDraft: CancellationDraft | null = null;
  private cancellationRequests: CancellationRequestRecord[] = [];
  private reviewDraft: { orderId: string; entries: ProductReviewDraftEntry[] } | null = null;
  private productReviews: ProductReviewRecord[] = [];
  private reorderPlan: ReorderPlan | null = null;
  private returnDraft: ReturnRequestDraft | null = null;
  private returnRequests: ReturnRequestRecord[] = [];
  private selectedReturnRequestId: string | null = null;
  private cancelledOrderRefundDraft: CancelledOrderRefundDraft | null = null;
  private refundRecords: RefundRecord[] = [];
  private selectedRefundId: string | null = null;
  private deliveryIssues: DeliveryIssueRecord[] = DEFAULT_DELIVERY_ISSUES.map(cloneDeliveryIssue);
  private deliveryRescheduleRequests: DeliveryRescheduleRequestRecord[] = [];
  private selectedDeliveryIssueId: string | null = null;
  private hydrated = false;
  private listeners: Array<() => void> = [];

  public constructor(
    private readonly storage: BuyerOrderActionStorage,
    private readonly now: () => string = () => new Date().toISOString(),
  ) {}

  public subscribe(listener: () => void): () => void {
    this.listeners.push(listener);
    return () => { this.listeners = this.listeners.filter((candidate) => candidate !== listener); };
  }

  private notify(): void { this.listeners.forEach((listener) => listener()); }

  private async persist(): Promise<void> {
    const state: PersistedBuyerOrderActions = {
      cancellationRequests: this.cancellationRequests.map(cloneCancellationRequest),
      productReviews: this.productReviews.map(cloneReview),
      returnRequests: this.returnRequests.map(cloneReturnRequest),
      refundRecords: this.refundRecords.map(cloneRefundRecord),
      deliveryIssues: this.deliveryIssues.map(cloneDeliveryIssue),
      deliveryRescheduleRequests: this.deliveryRescheduleRequests.map(cloneDeliveryReschedule),
    };
    await this.storage.setItem(BUYER_ORDER_ACTION_STORAGE_KEY, JSON.stringify(state));
  }

  public async hydrate(): Promise<void> {
    if (this.hydrated) return;
    const stored = await this.storage.getItem(BUYER_ORDER_ACTION_STORAGE_KEY);
    if (stored) {
      try {
        const parsed = JSON.parse(stored) as Partial<PersistedBuyerOrderActions>;
        if (Array.isArray(parsed.cancellationRequests)) this.cancellationRequests = parsed.cancellationRequests.map(cloneCancellationRequest);
        if (Array.isArray(parsed.productReviews)) this.productReviews = parsed.productReviews.map(cloneReview);
        if (Array.isArray(parsed.returnRequests)) this.returnRequests = parsed.returnRequests.map(cloneReturnRequest);
        if (Array.isArray(parsed.refundRecords)) this.refundRecords = parsed.refundRecords.map(cloneRefundRecord);
        if (Array.isArray(parsed.deliveryIssues)) this.deliveryIssues = parsed.deliveryIssues.map(cloneDeliveryIssue);
        if (Array.isArray(parsed.deliveryRescheduleRequests)) this.deliveryRescheduleRequests = parsed.deliveryRescheduleRequests.map(cloneDeliveryReschedule);
      } catch {
        // Keep an empty, deterministic action history when local prototype data is corrupt.
      }
    }
    this.hydrated = true;
    this.notify();
  }

  public beginCancellation(order: BuyerOrder): 'eligible' | 'ineligible' {
    if (!canCancelOrder(order)) {
      this.cancellationDraft = null;
      this.notify();
      return 'ineligible';
    }
    this.cancellationDraft = { orderId: order.orderId, reasonKey: null, message: '' };
    this.notify();
    return 'eligible';
  }

  public getCancellationDraft(): CancellationDraft | null {
    return this.cancellationDraft ? { ...this.cancellationDraft } : null;
  }

  public setCancellationReason(reasonKey: CancellationReasonKey): void {
    if (!this.cancellationDraft) return;
    this.cancellationDraft = { ...this.cancellationDraft, reasonKey };
    this.notify();
  }

  public setCancellationMessage(message: string): void {
    if (!this.cancellationDraft) return;
    this.cancellationDraft = { ...this.cancellationDraft, message: message.slice(0, 250) };
    this.notify();
  }

  public validateCancellationDraft(order: BuyerOrder): { valid: boolean; reasonRequired: boolean; orderMismatch: boolean } {
    const orderMismatch = this.cancellationDraft?.orderId !== order.orderId;
    const reasonRequired = !this.cancellationDraft?.reasonKey;
    return { valid: canCancelOrder(order) && !orderMismatch && !reasonRequired, reasonRequired, orderMismatch };
  }

  public async submitCancellationRequest(order: BuyerOrder): Promise<CancellationRequestRecord | null> {
    const validation = this.validateCancellationDraft(order);
    if (!validation.valid || !this.cancellationDraft?.reasonKey) return null;
    const requestedAt = this.now();
    const record: CancellationRequestRecord = {
      cancellationRequestId: `cancel-${order.orderId}-${requestedAt}`,
      orderId: order.orderId,
      reasonKey: this.cancellationDraft.reasonKey,
      message: this.cancellationDraft.message.trim() || undefined,
      requestedAt,
      status: 'frontend_requested',
      refundStatus: 'not_started',
    };
    this.cancellationRequests = [record, ...this.cancellationRequests.filter((item) => item.orderId !== order.orderId)];
    this.cancellationDraft = null;
    this.notify();
    await this.persist();
    return cloneCancellationRequest(record);
  }

  public getCancellationRequest(orderId: string): CancellationRequestRecord | null {
    const record = this.cancellationRequests.find((item) => item.orderId === orderId);
    return record ? cloneCancellationRequest(record) : null;
  }

  public beginReview(order: BuyerOrder): boolean {
    if (!canReviewOrder(order)) {
      this.reviewDraft = null;
      this.notify();
      return false;
    }
    this.reviewDraft = {
      orderId: order.orderId,
      entries: order.lines.map((line) => ({ orderLineId: line.orderLineId, productId: line.productId, rating: 0 })),
    };
    this.notify();
    return true;
  }

  public getReviewDraft(): { orderId: string; entries: ProductReviewDraftEntry[] } | null {
    return this.reviewDraft ? { orderId: this.reviewDraft.orderId, entries: this.reviewDraft.entries.map((entry) => ({ ...entry })) } : null;
  }

  public setReviewRating(orderLineId: string, rating: number): boolean {
    if (!this.reviewDraft?.entries.some((entry) => entry.orderLineId === orderLineId)) return false;
    const safeRating = Math.max(0, Math.min(5, Math.round(rating)));
    this.reviewDraft = {
      ...this.reviewDraft,
      entries: this.reviewDraft.entries.map((entry) => entry.orderLineId === orderLineId ? { ...entry, rating: safeRating } : entry),
    };
    this.notify();
    return true;
  }

  public async submitProductReviews(order: BuyerOrder): Promise<ProductReviewRecord[] | null> {
    if (!canReviewOrder(order) || this.reviewDraft?.orderId !== order.orderId || this.reviewDraft.entries.some((entry) => entry.rating < 1)) return null;
    const submittedAt = this.now();
    const records = this.reviewDraft.entries.map<ProductReviewRecord>((entry) => ({
      ...entry,
      reviewId: `review-${order.orderId}-${entry.orderLineId}`,
      orderId: order.orderId,
      submittedAt,
      submissionState: 'frontend_only',
    }));
    const reviewedLineIds = new Set(records.map((record) => `${record.orderId}:${record.orderLineId}`));
    this.productReviews = [
      ...records,
      ...this.productReviews.filter((record) => !reviewedLineIds.has(`${record.orderId}:${record.orderLineId}`)),
    ];
    this.reviewDraft = null;
    this.notify();
    await this.persist();
    return records.map(cloneReview);
  }

  public getProductReviews(orderId: string): ProductReviewRecord[] {
    return this.productReviews.filter((record) => record.orderId === orderId).map(cloneReview);
  }

  public beginReorder(order: BuyerOrder, catalog?: CurrentCatalogItem[]): ReorderPlan {
    this.reorderPlan = buildReorderPlan(order, catalog);
    this.notify();
    return clonePlan(this.reorderPlan)!;
  }

  public getReorderPlan(): ReorderPlan | null { return clonePlan(this.reorderPlan); }

  public setReorderLineSelected(orderLineId: string, selected: boolean): boolean {
    const line = this.reorderPlan?.lines.find((candidate) => candidate.orderLineId === orderLineId);
    if (!this.reorderPlan || !line || line.state === 'unavailable') return false;
    this.reorderPlan = {
      ...this.reorderPlan,
      lines: this.reorderPlan.lines.map((candidate) => candidate.orderLineId === orderLineId ? { ...candidate, selected } : candidate),
    };
    this.notify();
    return true;
  }

  public addSelectedReorderItemsToCart(cart: CartState): ReorderCartResult | null {
    if (!this.reorderPlan) return null;
    return addReorderPlanToCart(this.reorderPlan, cart);
  }

  public beginReturn(order: BuyerOrder): boolean {
    if (!canReturnOrder(order)) {
      this.returnDraft = null;
      this.notify();
      return false;
    }
    this.returnDraft = { orderId: order.orderId, selectedLines: [], reasonKey: null, message: '' };
    this.notify();
    return true;
  }

  public getReturnDraft(): ReturnRequestDraft | null { return cloneReturnDraft(this.returnDraft); }

  public setReturnLineSelected(order: BuyerOrder, orderLineId: string, selected: boolean, packageId?: string): boolean {
    if (this.returnDraft?.orderId !== order.orderId || !validateReturnLineOwnership(order, orderLineId, packageId)) return false;
    const sourceLine = order.lines.find((line) => line.orderLineId === orderLineId)!;
    const resolvedPackageId = packageId ?? getOrderLinePackageId(order, orderLineId);
    const existing = this.returnDraft.selectedLines.find((line) => line.orderLineId === orderLineId);
    this.returnDraft = {
      ...this.returnDraft,
      selectedLines: selected
        ? existing
          ? this.returnDraft.selectedLines
          : [...this.returnDraft.selectedLines, { orderLineId, packageId: resolvedPackageId, quantity: Math.min(1, sourceLine.quantity) }]
        : this.returnDraft.selectedLines.filter((line) => line.orderLineId !== orderLineId),
    };
    this.notify();
    return true;
  }

  public setReturnLineQuantity(order: BuyerOrder, orderLineId: string, quantity: number): boolean {
    if (this.returnDraft?.orderId !== order.orderId) return false;
    const sourceLine = order.lines.find((line) => line.orderLineId === orderLineId);
    if (!sourceLine || !this.returnDraft.selectedLines.some((line) => line.orderLineId === orderLineId)) return false;
    const safeQuantity = Math.max(1, Math.min(sourceLine.quantity, Math.round(quantity)));
    this.returnDraft = {
      ...this.returnDraft,
      selectedLines: this.returnDraft.selectedLines.map((line) => line.orderLineId === orderLineId ? { ...line, quantity: safeQuantity } : line),
    };
    this.notify();
    return true;
  }

  public setReturnReason(reasonKey: ReturnReasonKey): void {
    if (!this.returnDraft) return;
    this.returnDraft = { ...this.returnDraft, reasonKey };
    this.notify();
  }

  public setReturnMessage(message: string): void {
    if (!this.returnDraft) return;
    this.returnDraft = { ...this.returnDraft, message: message.slice(0, 300) };
    this.notify();
  }

  public validateReturnDraft(order: BuyerOrder): boolean {
    return Boolean(
      this.returnDraft
      && this.returnDraft.orderId === order.orderId
      && this.returnDraft.reasonKey
      && this.returnDraft.selectedLines.length > 0
      && this.returnDraft.selectedLines.every((line) => (
        validateReturnLineOwnership(order, line.orderLineId, line.packageId)
        && line.quantity > 0
        && line.quantity <= (order.lines.find((source) => source.orderLineId === line.orderLineId)?.quantity ?? 0)
      )),
    );
  }

  public async submitReturnRequest(order: BuyerOrder): Promise<ReturnRequestRecord | null> {
    if (!this.validateReturnDraft(order) || !this.returnDraft?.reasonKey) return null;
    const requestedAt = this.now();
    const returnRequestId = `RET-${order.orderId.replace('MAY-', '')}-${requestedAt}`;
    const selectedLines = this.returnDraft.selectedLines.map<ReturnRequestLine>((draftLine) => {
      const source = order.lines.find((line) => line.orderLineId === draftLine.orderLineId)!;
      return { ...draftLine, reasonKey: this.returnDraft!.reasonKey!, requestedUnitPriceMad: source.unitPriceMad };
    });
    const requestedRefundAmountMad = selectedLines.reduce((sum, line) => sum + line.requestedUnitPriceMad * line.quantity, 0);
    const event = (labelKey: ReturnTrackingEvent['labelKey'], state: ReturnTrackingEvent['state'], occurredAt?: string): ReturnTrackingEvent => ({
      returnTrackingEventId: `${returnRequestId}-${labelKey}`,
      returnRequestId,
      labelKey,
      state,
      occurredAt,
    });
    const record: ReturnRequestRecord = {
      returnRequestId,
      orderId: order.orderId,
      selectedLines,
      message: this.returnDraft.message.trim() || undefined,
      requestedAt,
      status: 'inspection',
      refundStatus: 'processing',
      requestedRefundAmountMad,
      trackingEvents: [
        event('request_created', 'completed', requestedAt),
        event('approved', 'completed', requestedAt),
        event('parcel_received', 'completed', requestedAt),
        event('inspection', 'current'),
        event('refund_processing', 'upcoming'),
        event('refunded', 'upcoming'),
      ],
      recordSource: 'frontend_fixture',
    };
    this.returnRequests = [record, ...this.returnRequests];
    this.returnDraft = null;
    this.selectedReturnRequestId = returnRequestId;
    this.notify();
    await this.persist();
    return cloneReturnRequest(record);
  }

  public getReturnRequests(orderId?: string): ReturnRequestRecord[] {
    return this.returnRequests.filter((record) => !orderId || record.orderId === orderId).map(cloneReturnRequest);
  }

  public getReturnRequest(returnRequestId: string): ReturnRequestRecord | null {
    const record = this.returnRequests.find((candidate) => candidate.returnRequestId === returnRequestId);
    return record ? cloneReturnRequest(record) : null;
  }

  public getSelectedReturnRequest(): ReturnRequestRecord | null {
    return this.selectedReturnRequestId ? this.getReturnRequest(this.selectedReturnRequestId) : null;
  }

  public selectReturnRequest(returnRequestId: string): boolean {
    if (!this.returnRequests.some((record) => record.returnRequestId === returnRequestId)) return false;
    this.selectedReturnRequestId = returnRequestId;
    this.notify();
    return true;
  }

  public getReturnTrackingEvents(returnRequestId: string): ReturnTrackingEvent[] {
    return this.getReturnRequest(returnRequestId)?.trackingEvents.map((event) => ({ ...event })) ?? [];
  }

  public beginCancelledOrderRefund(order: BuyerOrder): boolean {
    if (!canRequestCancelledOrderRefund(order)) {
      this.cancelledOrderRefundDraft = null;
      this.notify();
      return false;
    }
    this.cancelledOrderRefundDraft = {
      orderId: order.orderId,
      cancellationRequestId: this.getCancellationRequest(order.orderId)?.cancellationRequestId,
      requestedAmountMad: order.totalMad,
    };
    this.notify();
    return true;
  }

  public getCancelledOrderRefundDraft(): CancelledOrderRefundDraft | null {
    return this.cancelledOrderRefundDraft ? { ...this.cancelledOrderRefundDraft } : null;
  }

  public async requestCancelledOrderRefund(order: BuyerOrder): Promise<RefundRecord | null> {
    const draft = this.cancelledOrderRefundDraft;
    if (!draft || draft.orderId !== order.orderId || !canRequestCancelledOrderRefund(order) || draft.requestedAmountMad !== order.totalMad) return null;
    const requestedAt = this.now();
    const record: RefundRecord = {
      refundId: `refund-${order.orderId}-${requestedAt}`,
      orderId: order.orderId,
      source: 'cancelled_order',
      cancellationRequestId: draft.cancellationRequestId,
      requestedAmountMad: order.totalMad,
      requestedAt,
      processingAt: requestedAt,
      status: 'processing',
      recordSource: 'frontend_fixture',
    };
    this.refundRecords = [record, ...this.refundRecords.filter((candidate) => !(candidate.orderId === order.orderId && candidate.source === 'cancelled_order'))];
    this.cancelledOrderRefundDraft = null;
    this.selectedRefundId = record.refundId;
    this.notify();
    await this.persist();
    return cloneRefundRecord(record);
  }

  public async completeRefundFixture(refundId: string): Promise<RefundRecord | null> {
    const existing = this.refundRecords.find((record) => record.refundId === refundId);
    if (!existing || existing.status !== 'processing') return null;
    const completedAt = this.now();
    const completed: RefundRecord = {
      ...existing,
      status: 'completed',
      completedAmountMad: existing.requestedAmountMad,
      completedAt,
    };
    this.refundRecords = this.refundRecords.map((record) => record.refundId === refundId ? completed : record);
    this.selectedRefundId = refundId;
    this.notify();
    await this.persist();
    return cloneRefundRecord(completed);
  }

  public getRefundRecords(orderId?: string): RefundRecord[] {
    return this.refundRecords.filter((record) => !orderId || record.orderId === orderId).map(cloneRefundRecord);
  }

  public getRefundRecord(refundId: string): RefundRecord | null {
    const record = this.refundRecords.find((candidate) => candidate.refundId === refundId);
    return record ? cloneRefundRecord(record) : null;
  }

  public getSelectedRefund(): RefundRecord | null {
    return this.selectedRefundId ? this.getRefundRecord(this.selectedRefundId) : null;
  }

  public getDeliveryIssues(orderId?: string): DeliveryIssueRecord[] {
    return this.deliveryIssues.filter((record) => !orderId || record.orderId === orderId).map(cloneDeliveryIssue);
  }

  public getDeliveryIssue(deliveryIssueId: string): DeliveryIssueRecord | null {
    const record = this.deliveryIssues.find((candidate) => candidate.deliveryIssueId === deliveryIssueId);
    return record ? cloneDeliveryIssue(record) : null;
  }

  public selectDeliveryIssueForOrder(order: BuyerOrder, packageId?: string): DeliveryIssueRecord | null {
    const issue = this.deliveryIssues.find((candidate) => candidate.orderId === order.orderId && (packageId ? candidate.packageId === packageId : true));
    if (!issue || !validateDeliveryIssueOwnership(order, issue)) { this.selectedDeliveryIssueId = null; this.notify(); return null; }
    this.selectedDeliveryIssueId = issue.deliveryIssueId;
    this.notify();
    return cloneDeliveryIssue(issue);
  }

  public getSelectedDeliveryIssue(): DeliveryIssueRecord | null {
    return this.selectedDeliveryIssueId ? this.getDeliveryIssue(this.selectedDeliveryIssueId) : null;
  }

  public async requestDeliveryReschedule(order: BuyerOrder, deliveryIssueId: string, requestedSlot: string): Promise<DeliveryRescheduleRequestRecord | null> {
    const issue = this.deliveryIssues.find((candidate) => candidate.deliveryIssueId === deliveryIssueId);
    if (!issue || issue.type !== 'delivery_failed' || !validateDeliveryIssueOwnership(order, issue) || !requestedSlot.trim()) return null;
    const requestedAt = this.now();
    const record: DeliveryRescheduleRequestRecord = {
      rescheduleRequestId: `reschedule-${deliveryIssueId}-${requestedAt}`, deliveryIssueId, orderId: order.orderId,
      packageId: issue.packageId, requestedSlot: requestedSlot.trim(), requestedAt, status: 'frontend_requested', recordSource: 'frontend_fixture',
    };
    this.deliveryRescheduleRequests = [record, ...this.deliveryRescheduleRequests.filter((candidate) => candidate.deliveryIssueId !== deliveryIssueId)];
    this.deliveryIssues = this.deliveryIssues.map((candidate) => candidate.deliveryIssueId === deliveryIssueId ? { ...candidate, status: 'reschedule_requested' } : candidate);
    this.notify();
    await this.persist();
    return cloneDeliveryReschedule(record);
  }

  public getDeliveryRescheduleRequests(orderId?: string): DeliveryRescheduleRequestRecord[] {
    return this.deliveryRescheduleRequests.filter((record) => !orderId || record.orderId === orderId).map(cloneDeliveryReschedule);
  }
}

export const createBuyerOrderActionRepository = (
  storage: BuyerOrderActionStorage,
  now?: () => string,
): BuyerOrderActionRepository => new BuyerOrderActionRepository(storage, now);

export const orderActionState = createBuyerOrderActionRepository(AsyncStorage);
