# Mayush Mobile Buyer MVP - Server-Authoritative Data Rules

## Executive Principle

> [!CAUTION]  
> The Mayush Laravel backend is the **sole authoritative financial, inventory, and order state engine**. The React Native mobile application MUST NEVER calculate prices, taxes, shipping costs, discounts, or stock levels locally. Client UI components format strings for display, but backend server calculations dictate all financial transactions.

---

## 1. Server-Authoritative Data Matrix

| Field Name | Source API Endpoint | Response JSON Field | Refresh Trigger | Stale-Data Behavior | Error Recovery |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Product Base Price** | `GET /api/v2/products/{id}` | `main_price`, `stroked_price`, `calculable_price` | Product Detail Mount | Re-fetch from API | Show fallback error |
| **Variant Calculated Price** | `POST /api/v2/products/variant/price` | `data.price` | Color / Choice Option Selected | Disable CTA until response returns | Reset selection |
| **Available Stock Quantity** | `POST /api/v2/products/variant/price` | `data.stock`, `data.in_stock` | Variant Select / Add to Cart | Disable "Add to Cart" if 0 | Show "Out of Stock" badge |
| **Cart Subtotal** | `POST /api/v2/cart-summary` | `sub_total` | Quantity +/- / Item Remove | Re-query `cart-summary` | Block Checkout |
| **Tax & GST Charges** | `POST /api/v2/cart-summary` | `tax`, `gst` | Address Selection / Qty Change | Re-query `cart-summary` | Block Checkout |
| **Shipping Fees** | `POST /api/v2/update-shipping-type-in-cart` | `shipping_cost` | Delivery Option Select | Re-query `cart-summary` | Re-select delivery method |
| **Coupon Discount** | `POST /api/v2/coupon-apply` | `discount` | Promo Code Submitted | Remove invalid coupon | Show "Invalid Promo Code" error |
| **Grand Total** | `POST /api/v2/cart-summary` | `grand_total`, `grand_total_value` | Final Review Render | Must match `/order/store` payload | Block Payment Submission |
| **Order Payment Status** | `GET /api/v2/purchase-history-details/{id}` | `payment_status` (`paid` \| `unpaid`) | CMI Gateway Return / Correlation Signal | Re-query `purchase-history-details` | Show "Payment Verification Failed" |
| **Order Delivery Status** | `GET /api/v2/purchase-history-details/{id}` | `delivery_status` | History Mount | Re-fetch order status | N/A |

---

## 2. Order ID Verification & Mapping Rules

1. **CombinedOrder vs Individual Order ID**:
   - `POST /api/v2/order/store` creates a `CombinedOrder` master record and returns `{combined_order_id}`.
   - `GET /api/v2/purchase-history-details/{id}` expects an individual `Order` primary key (`orders.id`).
   - A `CombinedOrder` ID **MUST NOT be passed directly** to `purchase-history-details/{id}`.
   - The app MUST first query `GET /api/v2/purchase-history` to locate the individual `order_id` associated with the created order.

2. **Browser Return Non-Authority**:
   - CMI Hosted WebBrowser modal returns (deep link signals) are **purely UI trigger signals**. Browser returns MUST NEVER be treated as proof of payment.
   - The Order Confirmation screen (`SCR-CHK-009`) MUST NOT infer payment success locally. It MUST verify `payment_status == 'paid'` via `GET /api/v2/purchase-history-details/{order_id}` before rendering confirmation details.
