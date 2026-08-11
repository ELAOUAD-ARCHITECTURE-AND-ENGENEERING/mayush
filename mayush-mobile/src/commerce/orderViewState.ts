import { BuyerOrder } from './orderState';

export type OrderListLoadStatus = 'idle' | 'loading' | 'ready' | 'empty' | 'error';
export type OrderDetailLoadStatus = 'idle' | 'loading' | 'ready' | 'not_found' | 'error';

export interface OrderViewSnapshot {
  listStatus: OrderListLoadStatus;
  detailStatus: OrderDetailLoadStatus;
  requestedOrderId: string | null;
}

export const hasOrderTrackingMetadata = (order: BuyerOrder, packageId?: string): boolean => {
  const orderPackage = packageId ? order.packages.find((candidate) => candidate.packageId === packageId) : null;
  return Boolean(orderPackage?.trackingNumber || order.trackingNumber);
};

export class OrderViewStateManager {
  private snapshot: OrderViewSnapshot = { listStatus: 'idle', detailStatus: 'idle', requestedOrderId: null };
  private listeners: Array<() => void> = [];

  subscribe(listener: () => void): () => void {
    this.listeners.push(listener);
    return () => { this.listeners = this.listeners.filter((candidate) => candidate !== listener); };
  }

  getSnapshot(): OrderViewSnapshot { return { ...this.snapshot }; }
  private update(next: Partial<OrderViewSnapshot>): void {
    this.snapshot = { ...this.snapshot, ...next };
    this.listeners.forEach((listener) => listener());
  }

  beginListLoad(): void { this.update({ listStatus: 'loading' }); }
  resolveList(orders: BuyerOrder[]): OrderListLoadStatus {
    const listStatus: OrderListLoadStatus = orders.length ? 'ready' : 'empty';
    this.update({ listStatus });
    return listStatus;
  }
  failListLoad(): void { this.update({ listStatus: 'error' }); }
  retryListLoad(): void { this.update({ listStatus: 'loading' }); }

  beginDetailLoad(orderId: string): void {
    this.update({ detailStatus: 'loading', requestedOrderId: orderId });
  }
  resolveDetail(order: BuyerOrder | null): OrderDetailLoadStatus {
    const detailStatus: OrderDetailLoadStatus = order ? 'ready' : 'not_found';
    this.update({ detailStatus, requestedOrderId: order ? order.orderId : this.snapshot.requestedOrderId });
    return detailStatus;
  }
  failDetailLoad(): void { this.update({ detailStatus: 'error' }); }
  clearDetailLookup(): void { this.update({ detailStatus: 'idle', requestedOrderId: null }); }
}

export const orderViewState = new OrderViewStateManager();
