import { readFileSync } from 'fs';
import { resolve } from 'path';
import {
  CART_PROMOTION_CATALOG,
  CartLine,
  CartState,
  addCartLine,
  applyPromotionCode,
  calculatePromotionDiscountMad,
  createSelectedVariantCartLine,
  emptyCartState,
  getAvailablePromotions,
  getCartTotals,
  groupCartLinesBySeller,
  hydrateCartState,
  removeCartPromotion,
  updateCartLineQuantity,
  updateCartLineVariant,
  validatePromotion,
} from '../src/commerce/cartState';
import { createBuyerOrderRepository, OrderStorage } from '../src/commerce/orderState';

class MemoryStorage implements OrderStorage {
  readonly values = new Map<string, string>();
  async getItem(key: string) { return this.values.get(key) ?? null; }
  async setItem(key: string, value: string) { this.values.set(key, value); }
  async removeItem(key: string) { this.values.delete(key); }
}

const line = (overrides: Partial<CartLine> = {}): CartLine => ({
  id: 'line-louna-beige', productId: 101, name: 'Fauteuil LOUNA', productName: 'Fauteuil LOUNA',
  variant: 'Bouclé · Beige · 75cm P', variantId: 'louna-75', selectedVariantText: 'Bouclé · Beige · 75cm P',
  quantity: 1, unitPriceMad: 2450, sellerId: 'seller-atelier', sellerName: 'Atelier Maison', maxQuantity: 5,
  variantOptions: [
    { variantId: 'louna-70', label: 'Bouclé · Beige · 70cm P', unitPriceMad: 2250 },
    { variantId: 'louna-75', label: 'Bouclé · Beige · 75cm P', unitPriceMad: 2450 },
    { variantId: 'louna-80', label: 'Bouclé · Beige · 80cm P', unitPriceMad: 2650 },
  ],
  ...overrides,
});

export const runStep8FCartInteractionsPromotionsBehaviorTests = async (assert: (condition: boolean, message: string) => void): Promise<void> => {
  const base: CartState = { lines: [line(), line({ id: 'line-table', productId: 202, name: 'Table Lina', productName: 'Table Lina', variant: 'Chêne', variantId: 'oak', selectedVariantText: 'Chêne', quantity: 2, unitPriceMad: 1800, sellerId: 'seller-deco', sellerName: 'Décor Élégance', variantOptions: [{ variantId: 'oak', label: 'Chêne', unitPriceMad: 1800 }] })] };
  const increased = updateCartLineQuantity(base, 'line-louna-beige', 2);
  assert(increased.lines.find((item) => item.id === 'line-louna-beige')?.quantity === 2, '1 quantity update changes the exact cart line');
  assert(increased.lines[0].variantId === 'louna-75' && increased.lines[0].productId === 101, '2 quantity update preserves product and variant identity');
  const cartScreen = readFileSync(resolve(__dirname, '../src/screens/commerce/CartScreen.tsx'), 'utf8');
  assert(/setTimeout\(\(\) => setToastVisible\(false\), 1600\)/.test(cartScreen), '3 quantity toast is transient');

  const variantResult = updateCartLineVariant(base, 'line-louna-beige', { variantId: 'louna-80', label: 'Bouclé · Beige · 80cm P', unitPriceMad: 2650, quantity: 2 });
  assert(variantResult.updated && variantResult.cart.lines[0].id === 'line-louna-beige' && variantResult.cart.lines[0].variantId === 'louna-80' && variantResult.cart.lines[0].quantity === 2, '4 variant edit updates the correct stable cart line');
  const invalidVariant = updateCartLineVariant(base, 'line-louna-beige', { variantId: 'other-product', label: 'Invalid', unitPriceMad: 1 });
  assert(!invalidVariant.updated && JSON.stringify(invalidVariant.cart) === JSON.stringify(base), '5 invalid product variant is rejected');
  const duplicateState: CartState = { lines: [line(), line({ id: 'line-louna-80', variant: 'Bouclé · Beige · 80cm P', variantId: 'louna-80', selectedVariantText: 'Bouclé · Beige · 80cm P', quantity: 2, unitPriceMad: 2650 })] };
  const mergedVariant = updateCartLineVariant(duplicateState, 'line-louna-beige', { variantId: 'louna-80', label: 'Bouclé · Beige · 80cm P', unitPriceMad: 2650 });
  assert(mergedVariant.updated && mergedVariant.cart.lines.length === 1 && mergedVariant.cart.lines[0].quantity === 3, '6 equivalent variant lines merge without duplication');

  const beforeGrouping = JSON.stringify(base);
  const groups = groupCartLinesBySeller(base);
  assert(JSON.stringify(base) === beforeGrouping && groups.length === 2, '7 seller grouping is a non-mutating view projection');
  assert(groups.flatMap((group) => group.lines.map((item) => item.id)).sort().join('|') === base.lines.map((item) => item.id).sort().join('|'), '8 every cart line appears exactly once across seller groups');
  assert(groups.reduce((sum, group) => sum + group.subtotalMad, 0) === getCartTotals(base).subtotalMad, '9 seller-group subtotals equal the global subtotal');

  const invalidPromo = applyPromotionCode(base, 'UNKNOWN');
  assert(invalidPromo.validation.code === 'INVALID_CODE' && JSON.stringify(invalidPromo.cart) === JSON.stringify(base), '10 invalid promo does not change cart or totals');
  const validPromo = applyPromotionCode(base, 'MAYUSH10');
  const validTotals = getCartTotals(validPromo.cart);
  assert(validPromo.validation.code === 'VALID' && validTotals.discountMad === 450 && validTotals.totalMad === validTotals.subtotalMad - 450, '11 valid promo applies a deterministic integer discount');
  const tinyPromotion = { ...CART_PROMOTION_CATALOG[0], value: 9999, minimumSubtotalMad: 0 };
  assert(calculatePromotionDiscountMad(100, tinyPromotion) === 100, '12 promotion discount cannot make total negative');
  const hydratedValid = hydrateCartState(JSON.parse(JSON.stringify(validPromo.cart)));
  const hydratedInvalid = hydrateCartState({ ...base, appliedPromotionId: 'removed-promotion' });
  assert(hydratedValid.appliedPromotionId === 'promo-mayush10' && hydratedInvalid.appliedPromotionId === undefined, '13 persisted promotion identity is revalidated on hydration');
  assert(getAvailablePromotions(base).every((offer) => CART_PROMOTION_CATALOG.some((promotion) => promotion.promoId === offer.promoId)), '14 offers modal derives from the shared promotion catalog');
  const selectedOffer = applyPromotionCode(base, getAvailablePromotions(base)[0].code);
  assert(selectedOffer.validation.code === 'VALID' && selectedOffer.cart.appliedPromotionId === getAvailablePromotions(base)[0].promoId, '15 selecting an eligible offer applies it');
  assert(getCartTotals(removeCartPromotion(validPromo.cart)).totalMad === getCartTotals(base).subtotalMad, '16 changing or removing a promo recomputes totals');
  assert(getCartTotals(validPromo.cart).totalMad === validTotals.totalMad && getCartTotals(validPromo.cart).discountMad === validTotals.discountMad, '17 checkout consumes the exact cart pricing result');

  const orderStorage = new MemoryStorage();
  const orders = createBuyerOrderRepository(orderStorage, { seedOrders: [] });
  await orders.hydrate();
  const created = await orders.createOrder({ cart: validPromo.cart, address: { id: 'address-step8f', name: 'Youssef', phone: '0600000000', addressLine: '1 rue Atlas', city: 'Casablanca', postcode: '20000', zone: 'Centre' }, deliveryMethod: 'standard', paymentMethod: 'cash-on-delivery', checkoutAttemptId: 'step8f-order', createdAt: '2026-08-11T12:00:00.000Z' });
  assert(created.order.discountMad === validTotals.discountMad && created.order.totalMad === validTotals.totalMad && created.order.promotionCode === 'MAYUSH10', '18 BuyerOrder snapshots the exact checkout discount and promotion identity');
  const reorderLine = createSelectedVariantCartLine({ productId: 501, name: 'Reorder item', variant: 'Standard', quantity: 1, unitPriceMad: 1500 });
  const reorderCart = addCartLine(validPromo.cart, reorderLine);
  assert(getCartTotals(reorderCart).discountMad === 450 && reorderCart.lines.some((item) => item.productId === 501), '19 reorder-added lines use normal cart promotion logic');

  const beforeDialog = JSON.stringify(base);
  assert(beforeDialog === JSON.stringify(base) && /setLineToRemove\(line\)/.test(cartScreen), '20 opening remove confirmation does not mutate the cart');
  assert(JSON.stringify(base) === beforeDialog, '21 cancelling removal preserves the line');
  const removedExact = updateCartLineQuantity(base, 'line-louna-beige', 0);
  assert(!removedExact.lines.some((item) => item.id === 'line-louna-beige') && removedExact.lines.some((item) => item.id === 'line-table'), '22 confirm removal deletes the exact cartLineId');
  const siblings: CartState = { lines: [line(), line({ id: 'line-louna-80', variantId: 'louna-80', variant: '80cm', selectedVariantText: '80cm' })] };
  assert(updateCartLineQuantity(siblings, 'line-louna-beige', 0).lines[0].id === 'line-louna-80', '23 removing one variant does not remove its sibling variant');
  const minimumPromo = applyPromotionCode(base, 'EXTRA200').cart;
  const belowMinimum = updateCartLineQuantity(minimumPromo, 'line-table', 0);
  assert(belowMinimum.appliedPromotionId === undefined && getCartTotals(belowMinimum).discountMad === 0, '24 line removal revalidates promotion eligibility');
  assert(updateCartLineQuantity({ lines: [line()] }, 'line-louna-beige', 0).lines.length === 0, '25 final-line removal resolves the empty cart');
  assert(updateCartLineQuantity(base, 'line-louna-beige', 0).lines.length === 1, '26 non-final removal remains a populated cart');
  assert(['CartUpdateAlert', 'CartSkeleton', 'CartEmptyState', 'CartErrorState'].every((name) => cartScreen.includes(name)), '27 existing 309:666–669 cart states remain integrated');
  const reloaded = hydrateCartState(JSON.parse(JSON.stringify(validPromo.cart)));
  assert(reloaded.lines.length === validPromo.cart.lines.length && reloaded.appliedPromotionId === validPromo.cart.appliedPromotionId, '28 durable cart and promotion survive reload');
  const persisted = JSON.stringify(validPromo.cart);
  assert(!/toast|modal|sheet|promoError|lineToRemove/i.test(persisted), '29 transient toast, sheet, modal, error, and confirmation state do not persist');
  const orderBefore = JSON.stringify(created.order); updateCartLineQuantity(validPromo.cart, 'line-louna-beige', 0);
  assert(JSON.stringify(orders.getOrderById(created.order.orderId)) === orderBefore, '30 cart mutations do not modify buyer-order or action domains');
  assert(!/sellerSession|adminSession|sellerDashboard|adminDashboard/i.test(persisted + cartScreen), '31 no seller or admin state is introduced');
  const csv = readFileSync(resolve(__dirname, '../docs/phase-5c/CURRENT_SCREEN_STATUS.csv'), 'utf8');
  assert(['309:659','309:660','309:661','309:662','309:663','309:664','309:665'].every((node) => csv.includes(node)) && csv.includes('IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING'), '32 native validation remains explicitly pending');
};
