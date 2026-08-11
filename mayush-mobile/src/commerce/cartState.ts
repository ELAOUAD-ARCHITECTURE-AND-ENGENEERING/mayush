export const CART_STORAGE_KEY = 'mayush-mobile:cart-state';

export interface CartLine {
  id: string;
  productId: number | string;
  name: string;
  /** Alias for display name in cart cards */
  productName?: string;
  variant: string;
  /** Stable variant identity supplied by the selector/API when available. */
  variantId?: string;
  /** Alias for variant label in cart cards */
  selectedVariantText?: string;
  quantity: number;
  unitPriceMad: number;
  imageUri?: string;
  /** Alias for product image asset source */
  imageAsset?: string;
  sellerId?: string;
}

export interface CartState {
  lines: CartLine[];
}

export interface CartTotals {
  itemCount: number;
  subtotalMad: number;
}

export const emptyCartState = (): CartState => ({ lines: [] });

const normalizedQuantity = (quantity: number) => Math.max(1, Math.floor(quantity));

export interface SelectedVariantCartInput {
  productId: number | string;
  name: string;
  variant: string;
  quantity: number;
  unitPriceMad: number;
  imageUri?: string;
  sellerId?: string;
}

export const createSelectedVariantCartLine = (input: SelectedVariantCartInput): CartLine => {
  const variant = input.variant.trim() || 'Standard';
  return {
    id: `${input.productId}:${variant}`,
    productId: input.productId,
    name: input.name,
    productName: input.name,
    variant,
    variantId: variant,
    selectedVariantText: variant,
    quantity: normalizedQuantity(input.quantity),
    unitPriceMad: input.unitPriceMad,
    imageUri: input.imageUri,
    imageAsset: input.imageUri,
    sellerId: input.sellerId,
  };
};

export const addCartLine = (state: CartState, line: CartLine): CartState => {
  const quantity = normalizedQuantity(line.quantity);
  const existingIndex = state.lines.findIndex((item) => item.id === line.id);
  if (existingIndex === -1) {
    return { lines: [...state.lines, { ...line, quantity }] };
  }

  return {
    lines: state.lines.map((item, index) => index === existingIndex
      ? { ...item, quantity: item.quantity + quantity, unitPriceMad: line.unitPriceMad, imageUri: line.imageUri || item.imageUri }
      : item),
  };
};

export const updateCartLineQuantity = (state: CartState, lineId: string, quantity: number): CartState => {
  if (quantity <= 0) {
    return { lines: state.lines.filter((item) => item.id !== lineId) };
  }

  return {
    lines: state.lines.map((item) => item.id === lineId ? { ...item, quantity: normalizedQuantity(quantity) } : item),
  };
};

export const getCartTotals = (state: CartState): CartTotals => state.lines.reduce<CartTotals>((totals, line) => ({
  itemCount: totals.itemCount + line.quantity,
  subtotalMad: totals.subtotalMad + (line.unitPriceMad * line.quantity),
}), { itemCount: 0, subtotalMad: 0 });

export const parseMadPrice = (value: string): number => {
  const normalized = value.replace(/[^0-9,.-]/g, '').replace(',', '.');
  const parsed = Number.parseFloat(normalized);
  return Number.isFinite(parsed) ? Math.round(parsed) : 0;
};

export const formatMadPrice = (amount: number): string => `${new Intl.NumberFormat('fr-MA', {
  maximumFractionDigits: 0,
}).format(Math.max(0, Math.round(amount)))} MAD`;
