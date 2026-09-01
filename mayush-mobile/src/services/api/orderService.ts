/**
 * Mayush Mobile Orders, Tracking & Returns API Service
 * Interacts with Laravel PurchaseHistoryController, OrderController, and RefundRequestController.
 */

import { apiClient } from './apiClient';

export interface LaravelOrderSummary {
  id: number;
  code: string;
  user_id: number;
  payment_type: string;
  payment_status: string;
  payment_status_string: string;
  delivery_status: string;
  delivery_status_string: string;
  grand_total: string;
  date: string;
  links: {
    details: string;
  };
}

export interface LaravelOrderMiniListResponse {
  data: LaravelOrderSummary[];
  links: {
    first: string;
    last: string;
    prev?: string;
    next?: string;
  };
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
  };
  success: boolean;
  status: number;
}

export interface LaravelOrderDetailItem {
  id: number;
  product_id: number;
  product_name: string;
  variation?: string;
  price: string;
  tax: string;
  shipping_cost: string;
  coupon_discount: string;
  quantity: number;
  payment_status: string;
  payment_status_string: string;
  delivery_status: string;
  delivery_status_string: string;
  refund_section: boolean;
  refund_button: boolean;
  refund_label: string;
  refund_request_status: number;
}

export interface LaravelOrderDetail {
  id: number;
  code: string;
  user_id: number;
  manually_payable: boolean;
  shipping_address: {
    name: string;
    email: string;
    address: string;
    country: string;
    state?: string;
    city: string;
    postal_code: string;
    phone: string;
  };
  payment_type: string;
  payment_status: string;
  payment_status_string: string;
  delivery_status: string;
  delivery_status_string: string;
  grand_total: string;
  plane_grand_total: number;
  coupon_discount: string;
  shipping_cost: string;
  subtotal: string;
  tax: string;
  date: string;
  cancel_request: boolean;
  carrier_name?: string;
  tracking_code?: string;
  tracking_url?: string;
}

export const orderService = {
  /**
   * Fetch purchase history list with optional filters (payment_status, delivery_status, page)
   */
  async getPurchaseHistory(params: { payment_status?: string; delivery_status?: string; page?: number } = {}): Promise<LaravelOrderMiniListResponse> {
    return apiClient<LaravelOrderMiniListResponse>('/api/v2/purchase-history', {
      method: 'GET',
      params: {
        payment_status: params.payment_status || undefined,
        delivery_status: params.delivery_status || undefined,
        page: params.page || undefined,
      },
    });
  },

  /**
   * Fetch full order details by order ID
   */
  async getOrderDetails(orderId: number | string): Promise<{ data: LaravelOrderDetail[]; success: boolean; status: number }> {
    return apiClient<{ data: LaravelOrderDetail[]; success: boolean; status: number }>(`/api/v2/purchase-history-details/${orderId}`, {
      method: 'GET',
    });
  },

  /**
   * Fetch order items for a specific order
   */
  async getOrderItems(orderId: number | string): Promise<{ data: LaravelOrderDetailItem[]; success: boolean; status: number }> {
    return apiClient<{ data: LaravelOrderDetailItem[]; success: boolean; status: number }>(`/api/v2/purchase-history-items/${orderId}`, {
      method: 'GET',
    });
  },

  /**
   * Cancel an order within statutory period
   */
  async cancelOrder(orderId: number | string): Promise<{ result: boolean; message: string }> {
    return apiClient<{ result: boolean; message: string }>(`/api/v2/order/cancel/${orderId}`, {
      method: 'GET',
    });
  },

  /**
   * Submit return / refund request for an order line item
   */
  async submitRefundRequest(params: { orderDetailId: number | string; reason: string }): Promise<{ success: boolean; message: string }> {
    return apiClient<{ success: boolean; message: string }>('/api/v2/refund-request/send', {
      method: 'POST',
      body: {
        id: params.orderDetailId,
        reason: params.reason,
      },
    });
  },

  /**
   * Reorder an existing order
   */
  async reOrder(orderId: number | string): Promise<{ success_msgs: string[]; failed_msgs: string[] }> {
    return apiClient<{ success_msgs: string[]; failed_msgs: string[] }>(`/api/v2/re-order/${orderId}`, {
      method: 'GET',
    });
  },
};
