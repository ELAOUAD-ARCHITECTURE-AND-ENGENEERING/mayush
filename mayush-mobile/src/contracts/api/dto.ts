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

// Catalog DTOs
export interface CategoryDto {
  id: number;
  name: string;
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
}

export interface ProductMiniDto {
  id: number;
  name: string;
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
  added_by: string;
  seller_id: number;
  shop_id: number;
  shop_name: string;
  shop_logo: string;
  photos: string[];
  thumbnail_img: string;
  tags: string[];
  price_high_low: string;
  choice_options: {
    name: string;
    title: string;
    options: string[];
  }[];
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
  quantity?: number;
}

export interface VariantPriceResponseDataDto {
  price: string;
  stock: number;
  stock_txt: string;
  digital: number;
  variant: string;
  variation: string;
  max_limit: number;
  in_stock: number;
  image: string;
}

export interface VariantPriceResponseDto {
  result: boolean;
  message?: string;
  data?: VariantPriceResponseDataDto;
}

// Cart DTOs
export interface CartItemDto {
  id: number;
  status: number;
  owner_id: number;
  user_id: number;
  product_id: number;
  product_name: string;
  auction_product: number;
  product_thumbnail_image: string;
  variation: string;
  price: string;
  currency_symbol: string;
  tax: string;
  gst: string;
  shipping_cost: number;
  quantity: number;
  lower_limit: number;
  upper_limit: number;
  digital: number;
  stock: number;
}

export interface CartSellerGroupDto {
  name: string;
  owner_id: number;
  sub_total: string;
  cart_items: CartItemDto[];
}

export interface CartListResponseDto {
  grand_total: string;
  data: CartSellerGroupDto[];
}

export interface CartSummaryDto {
  sub_total: string;
  tax: string;
  gst: string;
  shipping_cost: string;
  discount: string;
  grand_total: string;
  grand_total_value: number;
  coupon_code: string;
  coupon_applied: boolean;
}

export interface AddToCartRequestDto {
  id: number;
  variant?: string;
  quantity: number;
  user_id?: number;
  temp_user_id?: string;
  cost_matrix?: string;
}

export interface AddToCartResponseDto {
  result: boolean;
  temp_user_id?: string;
  message: string;
}

// Auth DTOs (MVP Scope: Email/Phone Password Auth only)
export interface LoginRequestDto {
  email: string;
  password: string;
  login_by: 'email' | 'phone';
  temp_user_id?: string;
}

export interface SignupRequestDto {
  name: string;
  email_or_phone: string;
  password: string;
  password_confirmation: string;
  register_by: 'email' | 'phone';
  temp_user_id?: string;
}

export interface UserDto {
  id: number;
  type: string;
  name: string;
  email: string | null;
  avatar: string | null;
  avatar_original: string | null;
  phone: string | null;
  email_verified: boolean;
}

export interface AuthResponseDto {
  result: boolean;
  message: string | string[];
  access_token?: string;
  token_type?: string;
  expires_at?: string | null;
  user?: UserDto;
}

// Address DTOs
export interface AddressDto {
  id: number;
  user_id: number;
  address: string;
  country_id: number;
  country_name: string;
  state_id: number;
  state_name: string;
  city_id: number;
  city_name: string;
  area_id?: number;
  area_name?: string;
  postal_code: string;
  phone: string;
  set_default: number;
  location_available?: boolean;
  lat?: number;
  lang?: number;
}

export interface CreateAddressRequestDto {
  address: string;
  country_id: number;
  state_id: number;
  city_id: number;
  area_id?: number;
  postal_code: string;
  phone: string;
}

export interface CountryDto {
  id: number;
  code: string;
  name: string;
  status: number;
}

export interface StateDto {
  id: number;
  country_id: number;
  name: string;
  status: number;
}

export interface CityDto {
  id: number;
  state_id: number;
  name: string;
  cost: number;
  status: number;
}

// Payment & Order DTOs
export type VerifiedPaymentType = 'cash_on_delivery' | 'cmi' | 'wallet';

export interface PaymentTypeDto {
  payment_type: string;
  payment_type_key: VerifiedPaymentType | string;
  image: string;
  name: string;
  title: string;
  offline_payment_id: number;
  details: string;
}

export interface CreateOrderRequestDto {
  payment_type: VerifiedPaymentType;
}

export interface CreateOrderResponseDto {
  combined_order_id: number;
  result: boolean;
  message: string;
}

export interface PurchaseHistoryItemDto {
  id: number; // Note: This is individual orders.id (Order ID), NOT CombinedOrder ID
  code: string;
  user_id: number;
  payment_type: string;
  payment_status: 'paid' | 'unpaid';
  payment_status_string: string;
  delivery_status: 'pending' | 'confirmed' | 'on_delivery' | 'delivered' | 'cancelled';
  delivery_status_string: string;
  grand_total: string;
  date: string;
  links: {
    details: string;
  };
}
