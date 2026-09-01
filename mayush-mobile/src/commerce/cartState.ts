import AsyncStorage from '@react-native-async-storage/async-storage';
import { authState } from './authState';
import { cartService } from '../services/api/cartService';
import type { LaravelCartListResponse } from '../services/api/cartService';

export const CART_STORAGE_KEY = 'mayush-mobile:cart-state';

export interface CartVariantOption {
  variantId: string;
  label: string;
  unitPriceMad: number;
}

export interface CartVariantSelection extends CartVariantOption {
  quantity?: number;
}

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
  sellerName?: string;
  maxQuantity?: number;
  /** Product-owned choices available to the cart variant editor. */
  variantOptions?: CartVariantOption[];
}

export type CartPromotionType = 'fixed' | 'percentage';

export interface CartPromotion {
  promoId: string;
  code: string;
  type: CartPromotionType;
  value: number;
  minimumSubtotalMad?: number;
  sellerId?: string;
  expiresAt?: string;
  visible: boolean;
  title: string;
  expiryLabel?: string;
}

export type PromotionValidationCode =
  | 'VALID'
  | 'INVALID_CODE'
  | 'MINIMUM_NOT_REACHED'
  | 'NOT_ELIGIBLE'
  | 'EXPIRED';

export interface PromotionValidationResult {
  code: PromotionValidationCode;
  promotion?: CartPromotion;
  discountMad: number;
}

export interface CartState {
  lines: CartLine[];
  /** Durable identity only. Modal, toast, and validation UI never live here. */
  appliedPromotionId?: string;
}

export interface CartTotals {
  itemCount: number;
  subtotalMad: number;
  discountMad: number;
  totalMad: number;
  appliedPromotionId?: string;
  promotionCode?: string;
}

export interface SellerCartProjection {
  sellerId: string;
  sellerName: string;
  lines: CartLine[];
  subtotalMad: number;
}

export type CartConflictChange =
  | { kind: 'price'; lineId: string; oldPriceMad: number; newPriceMad: number }
  | { kind: 'stock'; lineId: string; oldQuantity: number; newQuantity: number }
  | { kind: 'unavailable'; lineId: string }
  | { kind: 'promotion_invalidated'; promotionId: string };

/**
 * The promotion clock is deliberately fixed for these frontend-only prototype
 * fixtures so the Figma offer dates remain deterministic on every machine.
 */
export const PROMOTION_FIXTURE_NOW = '2025-05-15T12:00:00.000Z';

export const CART_PROMOTION_CATALOG: readonly CartPromotion[] = [
  { promoId: 'promo-mayush10', code: 'MAYUSH10', type: 'fixed', value: 450, minimumSubtotalMad: 1000, expiresAt: '2027-12-31T23:59:59.000Z', visible: false, title: '450 MAD de réduction' },
  { promoId: 'promo-welcome15', code: 'WELCOME15', type: 'percentage', value: 15, minimumSubtotalMad: 1000, expiresAt: '2025-05-31T23:59:59.000Z', visible: true, title: '-15% sur votre commande', expiryLabel: '31/05/2025' },
  { promoId: 'promo-deco100', code: 'DECO100', type: 'fixed', value: 100, minimumSubtotalMad: 1500, expiresAt: '2025-06-15T23:59:59.000Z', visible: true, title: '100 MAD de réduction', expiryLabel: '15/06/2025' },
  { promoId: 'promo-maison10', code: 'MAISON10', type: 'percentage', value: 10, minimumSubtotalMad: 2000, expiresAt: '2025-06-30T23:59:59.000Z', visible: true, title: '-10% sur votre commande', expiryLabel: '30/06/2025' },
  { promoId: 'promo-extra200', code: 'EXTRA200', type: 'fixed', value: 200, minimumSubtotalMad: 3000, expiresAt: '2025-07-10T23:59:59.000Z', visible: true, title: '200 MAD de réduction', expiryLabel: '10/07/2025' },
  { promoId: 'promo20-incompatible', code: 'PROMO20', type: 'percentage', value: 20, sellerId: 'seller-promo20-only', expiresAt: '2027-12-31T23:59:59.000Z', visible: false, title: '-20% sur une sélection partenaire' },
] as const;

export const emptyCartState = (): CartState => ({ lines: [] });

/**
 * Runtime cache for promotions validated by the server. Keyed by promoId.
 * Allows getPromotionById to resolve server-issued coupons that are not in
 * the static CART_PROMOTION_CATALOG.
 */
const serverPromotionCache = new Map<string, CartPromotion>();

const normalizedQuantity = (quantity: number, maxQuantity?: number) => {
  const normalized = Math.max(1, Math.floor(quantity));
  return typeof maxQuantity === 'number' ? Math.min(normalized, Math.max(1, Math.floor(maxQuantity))) : normalized;
};

const getSubtotalMad = (state: CartState): number => state.lines.reduce(
  (subtotal, line) => subtotal + (Math.max(0, Math.round(line.unitPriceMad)) * line.quantity),
  0,
);

export const getPromotionById = (promoId?: string): CartPromotion | undefined => {
  if (!promoId) return undefined;
  return serverPromotionCache.get(promoId) ?? CART_PROMOTION_CATALOG.find((promotion) => promotion.promoId === promoId);
};

export const getPromotionByCode = (code: string): CartPromotion | undefined => {
  const normalizedCode = code.trim().toUpperCase();
  return CART_PROMOTION_CATALOG.find((promotion) => promotion.code === normalizedCode);
};

export const calculatePromotionDiscountMad = (subtotalMad: number, promotion: CartPromotion): number => {
  const safeSubtotal = Math.max(0, Math.round(subtotalMad));
  const rawDiscount = promotion.type === 'percentage'
    ? Math.round((safeSubtotal * promotion.value) / 100)
    : Math.round(promotion.value);
  return Math.min(safeSubtotal, Math.max(0, rawDiscount));
};

export const validatePromotion = (
  state: CartState,
  promotionOrCode: CartPromotion | string | undefined,
  at: string = PROMOTION_FIXTURE_NOW,
): PromotionValidationResult => {
  const promotion = typeof promotionOrCode === 'string'
    ? getPromotionByCode(promotionOrCode)
    : promotionOrCode;
  if (!promotion) return { code: 'INVALID_CODE', discountMad: 0 };

  const subtotalMad = getSubtotalMad(state);
  if (promotion.expiresAt && Date.parse(promotion.expiresAt) < Date.parse(at)) {
    return { code: 'EXPIRED', promotion, discountMad: 0 };
  }
  if (typeof promotion.minimumSubtotalMad === 'number' && subtotalMad < promotion.minimumSubtotalMad) {
    return { code: 'MINIMUM_NOT_REACHED', promotion, discountMad: 0 };
  }
  if (promotion.sellerId && !state.lines.some((line) => line.sellerId === promotion.sellerId)) {
    return { code: 'NOT_ELIGIBLE', promotion, discountMad: 0 };
  }
  return { code: 'VALID', promotion, discountMad: calculatePromotionDiscountMad(subtotalMad, promotion) };
};

export const revalidateCartPromotion = (state: CartState): CartState => {
  if (!state.appliedPromotionId) return state;
  const promotion = getPromotionById(state.appliedPromotionId);
  const validation = validatePromotion(state, promotion);
  return validation.code === 'VALID' ? state : { ...state, appliedPromotionId: undefined };
};

export const applyPromotionCode = (
  state: CartState,
  code: string,
): { cart: CartState; validation: PromotionValidationResult } => {
  const validation = validatePromotion(state, code);
  if (validation.code !== 'VALID' || !validation.promotion) return { cart: state, validation };
  return { cart: { ...state, appliedPromotionId: validation.promotion.promoId }, validation };
};

export const removeCartPromotion = (state: CartState): CartState => (
  state.appliedPromotionId ? { ...state, appliedPromotionId: undefined } : state
);

export const getAvailablePromotions = (state: CartState): CartPromotion[] => CART_PROMOTION_CATALOG
  .filter((promotion) => promotion.visible && validatePromotion(state, promotion).code === 'VALID')
  .map((promotion) => ({ ...promotion }));

export interface SelectedVariantCartInput {
  productId: number | string;
  name: string;
  variant: string;
  quantity: number;
  unitPriceMad: number;
  imageUri?: string;
  sellerId?: string;
  sellerName?: string;
  maxQuantity?: number;
  variantOptions?: CartVariantOption[];
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
    quantity: normalizedQuantity(input.quantity, input.maxQuantity),
    unitPriceMad: Math.max(0, Math.round(input.unitPriceMad)),
    imageUri: input.imageUri,
    imageAsset: input.imageUri,
    sellerId: input.sellerId,
    sellerName: input.sellerName,
    maxQuantity: input.maxQuantity,
    variantOptions: input.variantOptions,
  };
};

const equivalentLine = (left: CartLine, right: CartLine): boolean =>
  String(left.productId) === String(right.productId)
  && (left.variantId || left.variant) === (right.variantId || right.variant);

export const addCartLine = (state: CartState, line: CartLine): CartState => {
  const quantity = normalizedQuantity(line.quantity, line.maxQuantity);
  const existingIndex = state.lines.findIndex((item) => item.id === line.id || equivalentLine(item, line));
  const nextState = existingIndex === -1
    ? { ...state, lines: [...state.lines, { ...line, quantity }] }
    : {
      ...state,
      lines: state.lines.map((item, index) => index === existingIndex
        ? {
          ...item,
          quantity: normalizedQuantity(item.quantity + quantity, item.maxQuantity),
          unitPriceMad: Math.max(0, Math.round(line.unitPriceMad)),
          imageUri: line.imageUri || item.imageUri,
        }
        : item),
    };
  return revalidateCartPromotion(nextState);
};

export const updateCartLineQuantity = (state: CartState, lineId: string, quantity: number): CartState => {
  const line = state.lines.find((item) => item.id === lineId);
  if (!line) return state;
  const nextState = quantity <= 0
    ? { ...state, lines: state.lines.filter((item) => item.id !== lineId) }
    : {
      ...state,
      lines: state.lines.map((item) => item.id === lineId
        ? { ...item, quantity: normalizedQuantity(quantity, item.maxQuantity) }
        : item),
    };
  return revalidateCartPromotion(nextState);
};

/**
 * Applies buyer-acknowledged catalog conflicts through the canonical CartState.
 * Historical BuyerOrder snapshots are intentionally outside this mutation path.
 */
export const applyCartConflictChanges = (
  state: CartState,
  changes: readonly CartConflictChange[],
): CartState => {
  const byLineId = new Map(changes
    .filter((change): change is Exclude<CartConflictChange, { kind: 'promotion_invalidated' }> => change.kind !== 'promotion_invalidated')
    .map((change) => [change.lineId, change]));
  const invalidatesPromotion = changes.some((change) => change.kind === 'promotion_invalidated'
    && change.promotionId === state.appliedPromotionId);
  const lines = state.lines.flatMap((line) => {
    const change = byLineId.get(line.id);
    if (!change) return [{ ...line }];
    if (change.kind === 'unavailable' || (change.kind === 'stock' && change.newQuantity <= 0)) return [];
    if (change.kind === 'price') return [{ ...line, unitPriceMad: Math.max(0, Math.round(change.newPriceMad)) }];
    return [{ ...line, quantity: normalizedQuantity(Math.min(line.quantity, change.newQuantity), line.maxQuantity) }];
  });
  return revalidateCartPromotion({
    lines,
    appliedPromotionId: invalidatesPromotion ? undefined : state.appliedPromotionId,
  });
};

export interface CartVariantUpdateResult {
  cart: CartState;
  updated: boolean;
  mergedIntoLineId?: string;
}

export const updateCartLineVariant = (
  state: CartState,
  lineId: string,
  selection: CartVariantSelection,
): CartVariantUpdateResult => {
  const line = state.lines.find((item) => item.id === lineId);
  if (!line) return { cart: state, updated: false };
  const options = line.variantOptions || [{
    variantId: line.variantId || line.variant,
    label: line.selectedVariantText || line.variant,
    unitPriceMad: line.unitPriceMad,
  }];
  const validOption = options.find((option) => option.variantId === selection.variantId);
  if (!validOption) return { cart: state, updated: false };

  const nextQuantity = normalizedQuantity(selection.quantity ?? line.quantity, line.maxQuantity);
  const duplicate = state.lines.find((item) => item.id !== lineId
    && String(item.productId) === String(line.productId)
    && (item.variantId || item.variant) === validOption.variantId);
  if (duplicate) {
    const nextState = {
      ...state,
      lines: state.lines
        .filter((item) => item.id !== lineId)
        .map((item) => item.id === duplicate.id
          ? { ...item, quantity: normalizedQuantity(item.quantity + nextQuantity, item.maxQuantity), unitPriceMad: validOption.unitPriceMad }
          : item),
    };
    return { cart: revalidateCartPromotion(nextState), updated: true, mergedIntoLineId: duplicate.id };
  }

  const nextState = {
    ...state,
    lines: state.lines.map((item) => item.id === lineId
      ? {
        ...item,
        variant: validOption.label,
        variantId: validOption.variantId,
        selectedVariantText: validOption.label,
        unitPriceMad: Math.max(0, Math.round(validOption.unitPriceMad)),
        quantity: nextQuantity,
      }
      : item),
  };
  return { cart: revalidateCartPromotion(nextState), updated: true };
};

export const groupCartLinesBySeller = (state: CartState): SellerCartProjection[] => {
  const groups = new Map<string, SellerCartProjection>();
  state.lines.forEach((line) => {
    const sellerId = line.sellerId || 'seller-mayush';
    const current = groups.get(sellerId) || {
      sellerId,
      sellerName: line.sellerName || (sellerId === 'seller-mayush' ? 'Mayush Design' : sellerId),
      lines: [],
      subtotalMad: 0,
    };
    current.lines.push(line);
    current.subtotalMad += Math.max(0, Math.round(line.unitPriceMad)) * line.quantity;
    groups.set(sellerId, current);
  });
  return [...groups.values()].map((group) => ({ ...group, lines: [...group.lines] }));
};

export const getCartTotals = (state: CartState): CartTotals => {
  const subtotalMad = getSubtotalMad(state);
  const itemCount = state.lines.reduce((count, line) => count + line.quantity, 0);
  const promotion = getPromotionById(state.appliedPromotionId);
  const validation = validatePromotion(state, promotion);
  const discountMad = validation.code === 'VALID' ? validation.discountMad : 0;
  return {
    itemCount,
    subtotalMad,
    discountMad,
    totalMad: Math.max(0, subtotalMad - discountMad),
    appliedPromotionId: validation.code === 'VALID' ? promotion?.promoId : undefined,
    promotionCode: validation.code === 'VALID' ? promotion?.code : undefined,
  };
};

export const hydrateCartState = (value: unknown): CartState => {
  if (!value || typeof value !== 'object' || !Array.isArray((value as CartState).lines)) return emptyCartState();
  const parsed = value as CartState;
  const safeLines = parsed.lines.filter((line) => line
    && typeof line.id === 'string'
    && (typeof line.productId === 'string' || typeof line.productId === 'number')
    && typeof line.quantity === 'number'
    && typeof line.unitPriceMad === 'number');
  return revalidateCartPromotion({
    lines: safeLines.map((line) => ({ ...line, quantity: normalizedQuantity(line.quantity, line.maxQuantity) })),
    appliedPromotionId: typeof parsed.appliedPromotionId === 'string' ? parsed.appliedPromotionId : undefined,
  });
};

export const parseMadPrice = (value: string): number => {
  const normalized = value.replace(/[^0-9,.-]/g, '').replace(',', '.');
  const parsed = Number.parseFloat(normalized);
  return Number.isFinite(parsed) ? Math.round(parsed) : 0;
};

export const formatMadPrice = (amount: number): string => `${new Intl.NumberFormat('fr-MA', {
  maximumFractionDigits: 0,
}).format(Math.max(0, Math.round(amount)))} MAD`;

// ── Server → Local Cart Mapper ──

/**
 * Converts a LaravelCartListResponse from cartService.getCart() into the
 * local CartState shape. Seller groups are flattened into lines; each line
 * carries sellerId/sellerName derived from the shop group.
 */
export const mapServerCartToState = (response: LaravelCartListResponse): CartState => {
  const lines: CartLine[] = [];
  for (const group of response.data) {
    for (const item of group.cart_items) {
      const variant = item.variation || 'Standard';
      lines.push({
        id: String(item.id),
        productId: item.product_id,
        name: item.product_name,
        productName: item.product_name,
        variant,
        variantId: variant,
        selectedVariantText: variant,
        quantity: Math.max(1, item.quantity),
        unitPriceMad: parseMadPrice(String(item.price)),
        imageUri: item.product_thumbnail_image || undefined,
        imageAsset: item.product_thumbnail_image || undefined,
        sellerId: group.owner_id ? String(group.owner_id) : undefined,
        sellerName: group.name || undefined,
        maxQuantity: item.upper_limit ?? undefined,
      });
    }
  }
  return revalidateCartPromotion({ lines });
};

// ── CartStateManager ──

class CartStateManager {
  private state: CartState = emptyCartState();
  private listeners = new Set<() => void>();
  private hydrated: boolean = false;

  constructor() {
    authState.subscribe(() => {
      const user = authState.getUser();
      if (user) {
        void this.hydrateFromServer(user.id);
      } else {
        void this.hydrateFromStorage();
      }
    });
  }

  // ── Observer ──

  public subscribe(listener: () => void): () => void {
    this.listeners.add(listener);
    return () => this.listeners.delete(listener);
  }

  private notify(): void {
    this.listeners.forEach((listener) => listener());
  }

  // ── Read accessors ──

  public isHydrated(): boolean {
    return this.hydrated;
  }

  public getState(): CartState {
    return this.state;
  }

  public getTotals(): CartTotals {
    return getCartTotals(this.state);
  }

  public getLinesBySeller(): SellerCartProjection[] {
    return groupCartLinesBySeller(this.state);
  }

  // ── Hydration ──

  public async hydrate(): Promise<void> {
    const user = authState.getUser();
    if (user) {
      await this.hydrateFromServer(user.id);
    } else {
      await this.hydrateFromStorage();
    }
  }

  private async hydrateFromServer(userId: string): Promise<void> {
    try {
      const response = await cartService.getCart({ userId });
      this.state = mapServerCartToState(response);
      this.hydrated = true;
      this.notify();
      void this.persistLocal();
    } catch {
      // Server unreachable — fall back to local snapshot
      await this.hydrateFromStorage();
    }
  }

  private async hydrateFromStorage(): Promise<void> {
    try {
      const stored = await AsyncStorage.getItem(CART_STORAGE_KEY);
      if (stored) {
        const parsed = JSON.parse(stored);
        this.state = hydrateCartState(parsed);
      } else {
        this.state = emptyCartState();
      }
    } catch {
      this.state = emptyCartState();
    }
    this.hydrated = true;
    this.notify();
  }

  private async persistLocal(): Promise<void> {
    try {
      await AsyncStorage.setItem(CART_STORAGE_KEY, JSON.stringify(this.state));
    } catch {
      // Ignore storage errors
    }
  }

  private async refreshFromServer(): Promise<void> {
    const user = authState.getUser();
    if (!user) return;
    try {
      const response = await cartService.getCart({ userId: user.id });
      this.state = mapServerCartToState(response);
      this.notify();
      void this.persistLocal();
    } catch {
      // Keep current in-memory state on network failure
    }
  }

  // ── Mutations ──

  public async addLine(line: CartLine): Promise<void> {
    const user = authState.getUser();
    if (user) {
      try {
        await cartService.addToCart({
          productId: line.productId,
          variant: line.variant,
          quantity: line.quantity,
          userId: user.id,
        });
        await this.refreshFromServer();
        return;
      } catch {
        // Fall through to local mutation
      }
    }
    this.state = addCartLine(this.state, line);
    this.notify();
    void this.persistLocal();
  }

  public async updateQuantity(lineId: string, quantity: number): Promise<void> {
    const user = authState.getUser();
    if (user) {
      const numericId = parseInt(lineId, 10);
      if (!isNaN(numericId)) {
        try {
          await cartService.changeQuantity(numericId, quantity);
          await this.refreshFromServer();
          return;
        } catch {
          // Fall through to local mutation
        }
      }
    }
    this.state = updateCartLineQuantity(this.state, lineId, quantity);
    this.notify();
    void this.persistLocal();
  }

  public async removeLine(lineId: string): Promise<void> {
    const user = authState.getUser();
    if (user) {
      const numericId = parseInt(lineId, 10);
      if (!isNaN(numericId)) {
        try {
          await cartService.removeCartItem(numericId);
          await this.refreshFromServer();
          return;
        } catch {
          // Fall through to local mutation
        }
      }
    }
    this.state = updateCartLineQuantity(this.state, lineId, 0);
    this.notify();
    void this.persistLocal();
  }

  public async applyPromoCode(code: string): Promise<PromotionValidationResult> {
    const user = authState.getUser();
    if (user) {
      try {
        const response = await cartService.applyCoupon({ couponCode: code, userId: user.id });
        if (response.result) {
          // Server accepted the coupon — build a synthetic CartPromotion from server data
          // and register it in the cache so getPromotionById can resolve it later.
          const normalizedCode = code.trim().toUpperCase();
          const promoId = `server-${normalizedCode}`;
          const discountMad = response.discount ?? 0;
          const serverPromotion: CartPromotion = {
            promoId,
            code: normalizedCode,
            type: 'fixed',
            value: discountMad,
            visible: false,
            title: response.coupon_code ? `Code promo ${response.coupon_code}` : `Code promo ${normalizedCode}`,
          };
          serverPromotionCache.set(promoId, serverPromotion);
          this.state = { ...this.state, appliedPromotionId: promoId };
          this.notify();
          void this.persistLocal();
          return { code: 'VALID', promotion: serverPromotion, discountMad };
        }
        // Server rejected — do not mutate state
        return { code: 'INVALID_CODE', discountMad: 0 };
      } catch {
        // Network/server error — fall through to local validation for offline support
      }
    }
    const { cart, validation } = applyPromotionCode(this.state, code);
    this.state = cart;
    this.notify();
    void this.persistLocal();
    return validation;
  }

  public async removePromo(): Promise<void> {
    this.state = removeCartPromotion(this.state);
    this.notify();
    void this.persistLocal();
  }

  public reset(): void {
    this.state = emptyCartState();
    this.hydrated = false;
    this.notify();
    void AsyncStorage.removeItem(CART_STORAGE_KEY).catch(() => undefined);
  }
}

export const cartStateManager = new CartStateManager();
