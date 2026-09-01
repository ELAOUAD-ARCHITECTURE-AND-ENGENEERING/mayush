import { BuyerOrder, OrderStatus } from './orderState';

export type OrderTab = 'all' | 'in_progress' | 'completed' | 'cancelled';
export const INITIAL_ORDER_TAB: OrderTab = 'all';

export const ORDER_TAB_STATUSES: Readonly<Record<Exclude<OrderTab, 'all'>, readonly OrderStatus[]>> = {
  in_progress: ['created', 'confirmed', 'preparing', 'shipped', 'in_transit', 'return_requested', 'refund_pending'],
  completed: ['delivered'],
  cancelled: ['cancelled', 'returned', 'refunded'],
};

export const filterOrdersByTab = (orders: readonly BuyerOrder[], tab: OrderTab): BuyerOrder[] =>
  tab === 'all'
    ? orders.map((order) => ({ ...order }))
    : orders.filter((order) => ORDER_TAB_STATUSES[tab].includes(order.orderStatus)).map((order) => ({ ...order }));

export const getOrdersTabDirection = (isRTL: boolean): 'row' | 'row-reverse' => isRTL ? 'row-reverse' : 'row';
export const getOrderCardDirection = (isRTL: boolean): 'row' | 'row-reverse' => isRTL ? 'row-reverse' : 'row';
export const reduceOrderTabSelection = (_current: OrderTab, selected: OrderTab): OrderTab => selected;
export const isGlobalOrdersEmpty = (orders: readonly BuyerOrder[]): boolean => orders.length === 0;
