/**
 * Mayush Mobile Domain Models
 * 
 * Clean, normalized TypeScript data models used inside the React Native application.
 * Decoupled from raw backend keys where necessary.
 */

import { BackendLanguage, MvpAppLanguage } from '../api/dto';

export { BackendLanguage, MvpAppLanguage };

export type GuestCartIdentityState =
  | { status: 'absent' }
  | { status: 'active'; tempUserId: string }
  | { status: 'merge_pending'; tempUserId: string }
  | { status: 'merge_verified' }
  | { status: 'merge_failed'; tempUserId: string; reason: string };

export interface Money {
  formatted: string;
  amount: number;
  currency: 'MAD';
}

export interface CategoryModel {
  id: string;
  name: string;
  bannerUrl: string;
  iconUrl: string;
  childrenCount: number;
}

export interface ProductSummaryModel {
  id: string;
  name: string;
  thumbnailUrl: string;
  hasDiscount: boolean;
  discountPercentage?: string;
  originalPriceFormatted: string;
  currentPriceFormatted: string;
  rating: number;
  salesCount: number;
}

export interface ProductVariantChoice {
  name: string;
  title: string;
  options: string[];
}

export interface ProductDetailModel {
  id: string;
  name: string;
  shopId: string;
  shopName: string;
  photos: string[];
  thumbnailUrl: string;
  priceFormatted: string;
  originalPriceFormatted: string;
  hasDiscount: boolean;
  discountBadge?: string;
  colors: string[];
  variantChoices: ProductVariantChoice[];
  stockQuantity: number;
  inStock: boolean;
  minQuantity: number;
  unit: string;
  rating: number;
  ratingCount: number;
  descriptionHtml: string;
  isDigital: boolean;
  cashOnDeliveryAvailable: boolean;
}

export interface ActiveVariantSelection {
  color?: string;
  selectedOptions: Record<string, string>;
  quantity: number;
}

export interface CartItemModel {
  id: string;
  productId: string;
  productName: string;
  thumbnailUrl: string;
  variation: string;
  unitPriceFormatted: string;
  totalPriceFormatted: string;
  quantity: number;
  stockAvailable: number;
  minQuantity: number;
  sellerId: string;
  sellerName: string;
}

export interface CartSellerGroupModel {
  sellerId: string;
  sellerName: string;
  subtotalFormatted: string;
  items: CartItemModel[];
}

export interface CartSummaryModel {
  subtotalFormatted: string;
  taxFormatted: string;
  gstFormatted: string;
  shippingCostFormatted: string;
  discountFormatted: string;
  grandTotalFormatted: string;
  grandTotalRaw: number;
  couponCode?: string;
  couponApplied: boolean;
}

export interface UserModel {
  id: string;
  name: string;
  email?: string;
  phone?: string;
  avatarUrl?: string;
  isEmailVerified: boolean;
}

export interface UserAddressModel {
  id: string;
  addressLine: string;
  countryId: number;
  countryName: string;
  stateId: number;
  stateName: string;
  cityId: number;
  cityName: string;
  areaId?: number;
  areaName?: string;
  postalCode: string;
  phone: string;
  isDefault: boolean;
}

export type PaymentMethodKey = 'cash_on_delivery' | 'cmi' | 'wallet';

export interface PaymentOptionModel {
  key: PaymentMethodKey;
  title: string;
  description: string;
  imageUrl: string;
  isEnabled: boolean;
}

export interface OrderConfirmationModel {
  combinedOrderId: number;
  individualOrderId?: number; // Order ID for purchase-history-details lookup
  grandTotalFormatted: string;
  paymentType: PaymentMethodKey;
  createdAtISO: string;
}
