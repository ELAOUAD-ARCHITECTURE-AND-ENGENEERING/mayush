/**
 * Mayush Mobile API Data Transfer Objects (DTOs)
 * 
 * Strict Type definitions matching exact Laravel API response and request payloads.
 * Direct 1:1 mapping with Laravel API Resources and Controller returns.
 */

export type BackendLanguage = 'fr' | 'ar' | 'en';
export type MvpAppLanguage = 'fr' | 'ar';

// Shared Generic API Response Envelopes
export interface ApiSuccessResponse<T> {
  result: true;
  message?: string;
  data: T;
}

export interface ApiErrorResponse {
  result: false;
  message: string | string[];
  status?: number;
}

export interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  path: string;
  per_page: number;
  to: number;
  total: number;
}

export interface PaginationLinks {
  first: string;
  last: string;
  prev: string | null;
  next: string | null;
}

export interface PaginatedCollectionDto<T> {
  data: T[];
  links?: PaginationLinks;
  meta?: PaginationMeta;
  result?: boolean;
}

// User & Address DTOs
export interface UserDto {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  phone?: string;
}

export interface AddressDto {
  id: number;
  user_id: number;
  address: string;
  country_name: string;
  city_name: string;
  postal_code: string;
  phone: string;
  set_default: number;
}

// Catalog DTOs
export interface CategoryDto {
  id: number;
  name: string;
  nameFr?: string;
  nameAr?: string;
  banner: string;
  icon: string;
  number_of_children: number;
  links: {
    products: string;
    sub_categories: string;
  };
}

export interface ProductImageDto {
  variant?: string;
  path: string;
  assetPath?: string;
}

export interface ProductMiniDto {
  id: number;
  name: string;
  nameFr?: string;
  nameAr?: string;
  priceMad?: number;
  formattedPrice?: string;
  thumbnail_image: string;
  has_discount: boolean;
  discount: string | null;
  stroked_price: string;
  main_price: string;
  rating: number;
  sales: number;
  links: {
    details: string;
  };
}

export interface ProductDetailDto {
  id: number;
  name: string;
  title?: string;
  added_by: string;
  seller_id: number;
  shop_id: number;
  shop_name: string;
  shop_logo: string;
  photos: string[];
  images?: ProductImageDto[];
  fallbackImageAsset?: string;
  thumbnail_img: string;
  tags: string[];
  price_high_low: string;
  priceFormatted?: string;
  choice_options: {
    name: string;
    title: string;
    options: string[];
  }[];
  variants?: { label?: string }[];
  colors: string[];
  has_discount: boolean;
  discount: string | null;
  stroked_price: string;
  main_price: string;
  calculable_price: number;
  currency_symbol: string;
  current_stock: number;
  unit: string;
  min_qty: number;
  low_stock_quantity: number;
  discount_type: 'percent' | 'amount' | null;
  rating: number;
  rating_count: number;
  description: string;
  digital: number;
  cash_on_delivery: number;
  est_shipping_days: number | null;
}

export interface VariantPriceRequestDto {
  slug: string;
  variants?: string;
  color?: string;
}

export interface VariantPriceResponseDto {
  result: boolean;
  message?: string;
  data?: {
    variant?: string;
    price: string;
    price_string?: string;
    stock: number;
    stock_txt?: string;
    digital?: number;
  };
}

export interface CartItemDto {
  id: number;
  product_id: number;
  product_name: string;
  product_thumbnail: string;
  variation: string;
  price: number;
  currency_symbol: string;
  tax: number;
  shipping_cost: number;
  quantity: number;
  lower_limit: number;
  upper_limit: number;
}

export interface CartSummaryDto {
  sub_total: string;
  tax: string;
  shipping_cost: string;
  discount: string;
  grand_total: string;
  grand_total_value: number;
  coupon_code?: string;
  coupon_applied?: boolean;
}
