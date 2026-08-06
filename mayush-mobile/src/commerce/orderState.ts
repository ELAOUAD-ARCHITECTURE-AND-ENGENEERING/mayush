import { CartLine, CartState, getCartTotals } from './cartState';
import { DeliveryMethod, PaymentMethod, SavedAddress } from './checkoutState';

export interface PrototypeOrder {
  id: string;
  idempotencyKey: string;
  createdAtLabel: string;
  paymentReference: string;
  paymentMethod: PaymentMethod;
  paymentStatus: 'Payé' | 'À payer à la livraison';
  deliveryMethod: DeliveryMethod;
  deliveryStatus: 'En préparation';
  address: SavedAddress;
  lines: CartLine[];
  totalMad: number;
}

export interface CreateOrderInput {
  cart: CartState;
  address: SavedAddress;
  deliveryMethod: DeliveryMethod;
  paymentMethod: PaymentMethod;
  idempotencyKey: string;
}

export const createPrototypeOrder = (orders: PrototypeOrder[], input: CreateOrderInput): { order: PrototypeOrder; orders: PrototypeOrder[]; created: boolean } => {
  const existing = orders.find((order) => order.idempotencyKey === input.idempotencyKey);
  if (existing) return { order: existing, orders, created: false };

  const sequence = 1842 + orders.length;
  const order: PrototypeOrder = {
    id: `MAY-2026-${String(sequence).padStart(5, '0')}`,
    idempotencyKey: input.idempotencyKey,
    createdAtLabel: '28 mai 2026 à 10:24',
    paymentReference: input.paymentMethod === 'cash-on-delivery' ? 'COD-À-LA-LIVRAISON' : `PAY-${String(1746825127 + orders.length)}`,
    paymentMethod: input.paymentMethod,
    paymentStatus: input.paymentMethod === 'cash-on-delivery' ? 'À payer à la livraison' : 'Payé',
    deliveryMethod: input.deliveryMethod,
    deliveryStatus: 'En préparation',
    address: input.address,
    lines: input.cart.lines,
    totalMad: getCartTotals(input.cart).subtotalMad,
  };
  return { order, orders: [order, ...orders], created: true };
};
