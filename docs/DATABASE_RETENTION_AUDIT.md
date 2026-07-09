# Mayush Database Retention Audit

## 1. Summary

This audit is for database retention safety only. No retention pruning was implemented, no execute mode was added, and no scheduler entry was added for retention pruning. The inventory was gathered through read-only schema/count queries and classified conservatively so unknown future tables default to `UNKNOWN_PROTECTED`.

## 2. Total Tables Discovered

179 tables were discovered in the local MySQL database `amsadesign_db`.

## 3. Classification Rules

- `PROTECTED_FOREVER`: never automatically delete. Used for identity, seller, shop, catalog, upload/media, order, payment, refund, wallet, payout, shipping, accounting, legal, settings, permission, support, and core business records.
- `ARCHIVE_BEFORE_PRUNE`: do not delete directly. Export/archive first and require manual approval. Used for operational logs, audit/notification/analytics logs, webhook logs, and support diagnostics.
- `DIRECT_PRUNE_CANDIDATE`: possible future pruning candidate only after a dry run, tests, and manual approval. Used only for clear technical noise such as queue rows, expired reset tokens, Pulse metrics, and search/performance noise.
- `UNKNOWN_PROTECTED`: default for any table not explicitly classified. It must not be deleted until manually reviewed.

## 4. Table-by-Table Classification

| Table | Rows | Size | Key Columns | Date Columns | Category | Reason | Future Action |
| ----- | ---: | ---: | ----------- | ------------ | -------- | ------ | ------------- |
| addons | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Critical business/configuration record. | Never automatically delete. |
| addresses | 8 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | User identity and fulfillment data. | Never automatically delete. |
| affiliate_configs | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Affiliate configuration data. | Never automatically delete. |
| affiliate_logs | 0 | 16 KB | id, user_id, order_id | created_at, updated_at | PROTECTED_FOREVER | User/order affiliate evidence. | Never automatically delete. |
| affiliate_payments | 0 | 48 KB | id, affiliate_user_id, user_id | created_at, updated_at | PROTECTED_FOREVER | Payment/payout evidence. | Never automatically delete. |
| affiliate_stats | 0 | 32 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | User affiliate business data. | Never automatically delete. |
| affiliate_users | 1 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Affiliate user account data. | Never automatically delete. |
| affiliate_withdraw_requests | 0 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Withdrawal/payout request data. | Never automatically delete. |
| ai_prompts | 1 | 32 KB | id | created_at, updated_at | PROTECTED_FOREVER | Application prompt/configuration data. | Never automatically delete. |
| ai_usage_logs | 0 | 32 KB | id, user_id | created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Operational usage log tied to users. | Archive/export first; manual approval required. |
| analytics_daily_summaries | 0 | 80 KB | id | date, created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Operational analytics summary. | Archive/export first; manual approval required. |
| analytics_summaries | 0 | 32 KB | id | date, created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Operational analytics summary. | Archive/export first; manual approval required. |
| app_translations | 10727 | 1.5 MB | id | created_at, updated_at | PROTECTED_FOREVER | Localization content. | Never automatically delete. |
| area_translations | 1 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Localization content. | Never automatically delete. |
| areas | 3 | 16 KB | id | created_at, updated_at, deleted_at | PROTECTED_FOREVER | Geographic fulfillment data. | Never automatically delete. |
| attribute_category | 9 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog relationship data. | Never automatically delete. |
| attribute_translations | 1 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog localization data. | Never automatically delete. |
| attribute_values | 1313 | 96 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog attribute data. | Never automatically delete. |
| attributes | 33 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog attribute data. | Never automatically delete. |
| audit_logs | 509 | 96 KB | id, admin_user_id, target_user_id | created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Audit/support evidence. | Archive/export first; manual approval required. |
| banner_text_versions | 0 | 32 KB | id | created_at, updated_at | PROTECTED_FOREVER | Storefront content version data. | Never automatically delete. |
| blog_categories | 33 | 32 KB | id | created_at, updated_at, deleted_at | PROTECTED_FOREVER | Content taxonomy. | Never automatically delete. |
| blog_product | 0 | 80 KB | id, product_id | created_at, updated_at | PROTECTED_FOREVER | Blog/catalog relationship data. | Never automatically delete. |
| blog_subscriber_logs | 0 | 80 KB | id | subscribed_at, created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Subscription provider log. | Archive/export first; manual approval required. |
| blog_tag | 35 | 64 KB | id | created_at, updated_at | PROTECTED_FOREVER | Content taxonomy. | Never automatically delete. |
| blog_translations | 7 | 128 KB | id | created_at, updated_at | PROTECTED_FOREVER | Content localization data. | Never automatically delete. |
| blog_versions | 0 | 80 KB | id | created_at, updated_at | PROTECTED_FOREVER | Editorial version history. | Never automatically delete. |
| blogs | 8 | 224 KB | id, user_id, shop_id | read_time_minutes, published_at, submitted_at, reviewed_at, created_at, updated_at, deleted_at | PROTECTED_FOREVER | Editorial and seller content. | Never automatically delete. |
| brand_translations | 30 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Brand localization data. | Never automatically delete. |
| brands | 201 | 96 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog brand data. | Never automatically delete. |
| business_settings | 348 | 96 KB | id | created_at, updated_at | PROTECTED_FOREVER | Core application settings. | Never automatically delete. |
| carrier_range_prices | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Shipping configuration. | Never automatically delete. |
| carrier_ranges | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Shipping configuration. | Never automatically delete. |
| carriers | 0 | 16 KB | id, free_shipping | transit_time, created_at, updated_at | PROTECTED_FOREVER | Shipping carrier configuration. | Never automatically delete. |
| carts | 14 | 48 KB | id, user_id, temp_user_id, product_id, shipping_cost, shipping_type | created_at, updated_at | PROTECTED_FOREVER | Checkout/cart state is high-risk. | Never automatically delete. |
| categories | 423 | 176 KB | id | discount_start_date, discount_end_date, refund_request_time, created_at, updated_at | PROTECTED_FOREVER | Catalog category data. | Never automatically delete. |
| category_translations | 1079 | 112 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog localization data. | Never automatically delete. |
| cities | 18429 | 1.5 MB | - | created_at, updated_at, deleted_at | PROTECTED_FOREVER | Geographic fulfillment data. | Never automatically delete. |
| city_translations | 1 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Geographic localization data. | Never automatically delete. |
| club_point_details | 0 | 16 KB | id, user_id, order_id, product_id | created_at, updated_at | PROTECTED_FOREVER | Loyalty/order evidence. | Never automatically delete. |
| club_points | 0 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Loyalty balance data. | Never automatically delete. |
| cmi_callback_logs | 0 | 112 KB | id, combined_order_id, order_id | received_at, processed_at, created_at, updated_at | PROTECTED_FOREVER | Payment callback proof. | Never automatically delete. |
| colors | 143 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog attribute data. | Never automatically delete. |
| combined_orders | 121 | 64 KB | id, user_id, shipping_address, shipping_cost | created_at, updated_at | PROTECTED_FOREVER | Order/accounting record. | Never automatically delete. |
| commission_histories | 4 | 16 KB | id, order_id, seller_id | created_at, updated_at | PROTECTED_FOREVER | Seller commission evidence. | Never automatically delete. |
| contacts | 22 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Customer/contact business record. | Never automatically delete. |
| conversations | 3 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Support conversation data. | Never automatically delete. |
| countries | 246 | 15 KB | id | created_at, updated_at, deleted_at | PROTECTED_FOREVER | Geographic fulfillment data. | Never automatically delete. |
| coupon_usages | 0 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Coupon/accounting evidence. | Never automatically delete. |
| coupons | 0 | 16 KB | id, user_id | start_date, end_date, created_at, updated_at | PROTECTED_FOREVER | Promotion/coupon data. | Never automatically delete. |
| currencies | 26 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Commerce configuration. | Never automatically delete. |
| custom_alerts | 2 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Storefront alert content. | Never automatically delete. |
| custom_label_translations | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Storefront localization data. | Never automatically delete. |
| custom_labels | 24 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | User/storefront label data. | Never automatically delete. |
| custom_sale_alerts | 0 | 16 KB | id, product_id | created_at, updated_at | PROTECTED_FOREVER | Product merchandising data. | Never automatically delete. |
| customer_package_payments | 0 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Payment evidence. | Never automatically delete. |
| customer_package_translations | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Package localization data. | Never automatically delete. |
| customer_packages | 3 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Customer package configuration. | Never automatically delete. |
| customer_product_translations | 0 | 16 KB | id, customer_product_id | created_at, updated_at | PROTECTED_FOREVER | Customer product localization data. | Never automatically delete. |
| customer_products | 0 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Customer product/catalog data. | Never automatically delete. |
| dynamic_popups | 2 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Storefront content/configuration. | Never automatically delete. |
| element_styles | 38 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Storefront design configuration. | Never automatically delete. |
| element_translations | 0 | 16 KB | - | created_at, updated_at | PROTECTED_FOREVER | Storefront localization data. | Never automatically delete. |
| element_types | 7 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Storefront design configuration. | Never automatically delete. |
| elements | 1 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Storefront design configuration. | Never automatically delete. |
| elite_subscriptions | 0 | 32 KB | id, shop_id, transaction_id | billing_cycle, expires_at, created_at, updated_at | PROTECTED_FOREVER | Seller subscription/payment evidence. | Never automatically delete. |
| email_templates | 61 | 480 KB | id | created_at, updated_at | PROTECTED_FOREVER | Notification template configuration. | Never automatically delete. |
| email_verification_attempts | 0 | 32 KB | id, user_id | token_expires_at, created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Account/security support log. | Archive/export first; manual approval required. |
| failed_jobs | 1 | 32 KB | id | failed_at | DIRECT_PRUNE_CANDIDATE | Queue failure noise. | Future dry-run/manual approval only. |
| firebase_notifications | 0 | 16 KB | id | created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Notification delivery log. | Archive/export first; manual approval required. |
| flash_deal_products | 44 | 16 KB | id, product_id | created_at, updated_at | PROTECTED_FOREVER | Product promotion relationship. | Never automatically delete. |
| flash_deal_translations | 4 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Promotion localization data. | Never automatically delete. |
| flash_deals | 6 | 16 KB | id | start_date, end_date, created_at, updated_at | PROTECTED_FOREVER | Promotion data. | Never automatically delete. |
| follow_sellers | 0 | 16 KB | user_id, shop_id | - | PROTECTED_FOREVER | User/seller relationship data. | Never automatically delete. |
| frequently_bought_products | 0 | 16 KB | product_id, frequently_bought_product_id | - | PROTECTED_FOREVER | Catalog merchandising relationship. | Never automatically delete. |
| health_metrics | 8652 | 4.3 MB | id | created_at | DIRECT_PRUNE_CANDIDATE | Technical health metrics. | Future dry-run/manual approval only. |
| home_categories | 2 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Storefront merchandising data. | Never automatically delete. |
| image_optimization_states | 682 | 272 KB | id, upload_id | last_checked_at, optimized_at, created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Operational media pipeline state. | Archive/export first; manual approval required. |
| inventory_logs | 84 | 48 KB | id, product_id, user_id, order_id | created_at, updated_at | PROTECTED_FOREVER | Inventory/order evidence. | Never automatically delete. |
| jobs | 22 | 208 KB | id | reserved_at, available_at, created_at | DIRECT_PRUNE_CANDIDATE | Queue runtime rows. | Future dry-run/manual approval only. |
| languages | 3 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Localization configuration. | Never automatically delete. |
| last_viewed_products | 53 | 16 KB | id, user_id, product_id | created_at, updated_at | ARCHIVE_BEFORE_PRUNE | User behavior/support log. | Archive/export first; manual approval required. |
| manual_payment_methods | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Payment configuration. | Never automatically delete. |
| measurement_points | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog measurement configuration. | Never automatically delete. |
| messages | 3 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Support conversation messages. | Never automatically delete. |
| migrations | 118 | 16 KB | id | - | PROTECTED_FOREVER | Schema history ledger. | Never automatically delete. |
| model_has_permissions | 0 | 32 KB | permission_id, model_type, model_id | - | PROTECTED_FOREVER | Access control relationship. | Never automatically delete. |
| model_has_roles | 12 | 32 KB | role_id, model_type, model_id | - | PROTECTED_FOREVER | Access control relationship. | Never automatically delete. |
| note_translations | 1 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Content localization data. | Never automatically delete. |
| notes | 20 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | User/order support note data. | Never automatically delete. |
| notification_type_translations | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Notification localization data. | Never automatically delete. |
| notification_types | 29 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Notification configuration. | Never automatically delete. |
| notifications | 205 | 96 KB | id | read_at, created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Notification log. | Archive/export first; manual approval required. |
| onessta_city_maps | 0 | 64 KB | id | created_at, updated_at | PROTECTED_FOREVER | ONESSTA mapping configuration. | Never automatically delete. |
| onessta_pickup_city_maps | 0 | 64 KB | id | created_at, updated_at | PROTECTED_FOREVER | ONESSTA pickup mapping configuration. | Never automatically delete. |
| onessta_shipments | 0 | 112 KB | id, order_id | reported_date, created_at_remote, updated_at_remote, synced_at, created_at, updated_at | PROTECTED_FOREVER | Shipping/order proof. | Never automatically delete. |
| onessta_tracking_events | 0 | 32 KB | id | created_at_remote, new_date, created_at, updated_at | PROTECTED_FOREVER | Shipping tracking proof. | Never automatically delete. |
| onessta_webhook_logs | 0 | 64 KB | id | processed_at, created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Shipping webhook diagnostic log. | Archive/export first; manual approval required. |
| order_details | 132 | 96 KB | id, order_id, seller_id, product_id, shipping_cost, shipping_type | created_at, updated_at | PROTECTED_FOREVER | Order line/accounting record. | Never automatically delete. |
| order_tracking_histories | 0 | 32 KB | id, order_id | expected_delivery_date, created_at, updated_at | PROTECTED_FOREVER | Order tracking proof. | Never automatically delete. |
| orders | 132 | 224 KB | id, combined_order_id, user_id, seller_id, shipping_address, shipping_type, shipping_method, shipping_cost, tracking_code, shiprocket_order_id, steadfast_tracking_code, invoice_number | awb_assigned_at, pickup_scheduled_at, date, delivered_date, created_at, updated_at | PROTECTED_FOREVER | Order/payment/shipping/legal record. | Never automatically delete. |
| otp_configurations | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Security configuration. | Never automatically delete. |
| page_translations | 32 | 1.5 MB | id | created_at, updated_at | PROTECTED_FOREVER | Page localization content. | Never automatically delete. |
| pages | 14 | 272 KB | id | created_at, updated_at | PROTECTED_FOREVER | Page content. | Never automatically delete. |
| password_resets | 0 | 32 KB | - | created_at | DIRECT_PRUNE_CANDIDATE | Expired reset token table. | Future dry-run/manual approval only. |
| payku_payments | 0 | 16 KB | transaction_id, transaction_key | start, end, created_at, updated_at | PROTECTED_FOREVER | Payment gateway evidence. | Never automatically delete. |
| payku_transactions | 0 | 32 KB | id | notified_at, created_at, updated_at | PROTECTED_FOREVER | Payment gateway evidence. | Never automatically delete. |
| payment_attempts | 0 | 144 KB | id, user_id, combined_order_id, order_id | initiated_at, completed_at, failed_at, created_at, updated_at | PROTECTED_FOREVER | Payment attempt proof; successful attempts are protected. | Never automatically delete. |
| payment_informations | 0 | 48 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Payment information record. | Never automatically delete. |
| payment_methods | 25 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Payment method configuration. | Never automatically delete. |
| payment_tokens | 0 | 32 KB | id, user_id | last_used_at, created_at, updated_at | PROTECTED_FOREVER | Vault/payment token record. | Never automatically delete. |
| payments | 3 | 16 KB | id, seller_id | created_at, updated_at | PROTECTED_FOREVER | Seller payment evidence. | Never automatically delete. |
| permissions | 324 | 48 KB | id | created_at, updated_at | PROTECTED_FOREVER | Access control data. | Never automatically delete. |
| personal_access_tokens | 1 | 48 KB | id | last_used_at, expires_at, created_at, updated_at | DIRECT_PRUNE_CANDIDATE | Expired/revoked technical token data. | Future dry-run/manual approval only. |
| pickup_addresses | 0 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Seller/customer pickup data. | Never automatically delete. |
| pickup_point_translations | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Pickup localization data. | Never automatically delete. |
| pickup_points | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Pickup configuration. | Never automatically delete. |
| point_assignment_logs | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Loyalty point evidence. | Never automatically delete. |
| point_templates | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Loyalty configuration. | Never automatically delete. |
| preorder_products | 0 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Preorder catalog/business data. | Never automatically delete. |
| preorders | 0 | 16 KB | id, user_id, product_id | request_preorder_time, prepayment_confirmation_time, created_at, updated_at | PROTECTED_FOREVER | Preorder/payment evidence. | Never automatically delete. |
| product_categories | 616 | 64 KB | id, product_id | - | PROTECTED_FOREVER | Catalog relationship data. | Never automatically delete. |
| product_collection_product | 0 | 32 KB | id, product_id | created_at, updated_at | PROTECTED_FOREVER | Catalog collection relationship. | Never automatically delete. |
| product_collections | 7 | 32 KB | id, seller_ids | starts_at, ends_at, created_at, updated_at | PROTECTED_FOREVER | Storefront collection data. | Never automatically delete. |
| product_queries | 0 | 16 KB | id, seller_id, product_id | created_at, updated_at | PROTECTED_FOREVER | Product inquiry/support data. | Never automatically delete. |
| product_stocks | 898 | 96 KB | id, product_id | created_at, updated_at | PROTECTED_FOREVER | Inventory stock data. | Never automatically delete. |
| product_taxes | 528 | 64 KB | id, product_id | created_at, updated_at | PROTECTED_FOREVER | Product tax/accounting data. | Never automatically delete. |
| product_translations | 758 | 1.5 MB | id, product_id | created_at, updated_at | PROTECTED_FOREVER | Catalog localization data. | Never automatically delete. |
| product_views | 5172 | 2.4 MB | id, product_id, user_id | created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Product analytics log. | Archive/export first; manual approval required. |
| products | 504 | 1.9 MB | id, user_id, flat_shipping_cost, shipping_type, shipping_cost, est_shipping_days, show_estimated_shipping_time, shipping_note_id, show_shipping_note | discount_start_date, discount_end_date, created_at, updated_at, show_estimated_shipping_time | PROTECTED_FOREVER | Core catalog data. | Never automatically delete. |
| promotions | 0 | 64 KB | id, product_id, user_id | start_date, end_date, created_at, updated_at | PROTECTED_FOREVER | Product promotion data. | Never automatically delete. |
| proxypay_payments | 0 | 16 KB | id, order_id, user_id | created_at, updated_at | PROTECTED_FOREVER | Payment gateway evidence. | Never automatically delete. |
| pulse_aggregates | 853 | 384 KB | id | - | DIRECT_PRUNE_CANDIDATE | Pulse observability metrics. | Future dry-run/manual approval only. |
| pulse_entries | 37916 | 10.6 MB | id | - | DIRECT_PRUNE_CANDIDATE | Pulse observability metrics. | Future dry-run/manual approval only. |
| pulse_values | 1 | 64 KB | id | - | DIRECT_PRUNE_CANDIDATE | Pulse observability metrics. | Future dry-run/manual approval only. |
| refund_requests | 0 | 16 KB | id, user_id, seller_id, order_id | created_at, updated_at | PROTECTED_FOREVER | Refund/dispute evidence. | Never automatically delete. |
| registration_verification_codes | 16 | 48 KB | id | created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Account verification/security log. | Archive/export first; manual approval required. |
| reviews | 0 | 16 KB | id, product_id, user_id | created_at_is_custom, created_at, updated_at | PROTECTED_FOREVER | Product trust/support data. | Never automatically delete. |
| role_has_permissions | 183 | 32 KB | permission_id, role_id | - | PROTECTED_FOREVER | Access control relationship. | Never automatically delete. |
| role_translations | 2 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Access control localization. | Never automatically delete. |
| roles | 4 | 32 KB | id | created_at, updated_at | PROTECTED_FOREVER | Access control data. | Never automatically delete. |
| searches | 46 | 16 KB | id | created_at, updated_at | DIRECT_PRUNE_CANDIDATE | Search/debug noise. | Future dry-run/manual approval only. |
| seller_categories | 8 | 16 KB | id, seller_id | discount_start_date, discount_end_date, created_at, updated_at | PROTECTED_FOREVER | Seller/category relationship. | Never automatically delete. |
| seller_documents | 0 | 16 KB | id, shop_id | uploaded_at, created_at, updated_at | PROTECTED_FOREVER | Seller compliance documents. | Never automatically delete. |
| seller_packages | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Seller package data. | Never automatically delete. |
| seller_withdraw_requests | 1 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Seller payout evidence. | Never automatically delete. |
| sellers | 22 | 32 KB | id, user_id | invalid_at, created_at, updated_at | PROTECTED_FOREVER | Seller account data. | Never automatically delete. |
| semantic_embeddings | 11 | 176 KB | id | created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Search/AI operational index data. | Archive/export first; manual approval required. |
| shipping_box_sizes | 0 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Shipping configuration. | Never automatically delete. |
| shipping_systems | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Shipping configuration. | Never automatically delete. |
| shops | 16 | 32 KB | id, user_id, shipping_cost | package_invalid_at, documents_submitted_at, reviewed_at, created_at, updated_at | PROTECTED_FOREVER | Shop/seller business data. | Never automatically delete. |
| size_chart_details | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog sizing data. | Never automatically delete. |
| size_charts | 0 | 32 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog sizing data. | Never automatically delete. |
| sms_templates | 0 | 32 KB | id | created_at, updated_at | PROTECTED_FOREVER | Notification template configuration. | Never automatically delete. |
| social_credentials | 0 | 16 KB | id, user_id | created_at, updated_at, expires_at | PROTECTED_FOREVER | User identity/auth record. | Never automatically delete. |
| staff | 3 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Admin/staff account data. | Never automatically delete. |
| states | 4092 | 256 KB | id | created_at, updated_at, deleted_at | PROTECTED_FOREVER | Geographic fulfillment data. | Never automatically delete. |
| stock_alert_subscriptions | 0 | 48 KB | id, user_id, product_id | created_at, updated_at | PROTECTED_FOREVER | User/product notification relationship. | Never automatically delete. |
| stock_subscriptions | 0 | 48 KB | id, user_id, product_id | notified_at, created_at, updated_at | PROTECTED_FOREVER | User/product notification relationship. | Never automatically delete. |
| subscribers | 13 | 32 KB | id | created_at, updated_at | PROTECTED_FOREVER | Marketing consent/subscriber data. | Never automatically delete. |
| tags | 35 | 32 KB | id | created_at, updated_at | PROTECTED_FOREVER | Content/catalog taxonomy. | Never automatically delete. |
| taxes | 1 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Tax configuration. | Never automatically delete. |
| ticket_replies | 0 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Support ticket data. | Never automatically delete. |
| tickets | 4 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Support ticket data. | Never automatically delete. |
| top_banner_translations | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Storefront localization data. | Never automatically delete. |
| top_banners | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Storefront content data. | Never automatically delete. |
| transactions | 0 | 16 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Financial transaction evidence. | Never automatically delete. |
| translations | 36298 | 3.5 MB | id | created_at, updated_at | PROTECTED_FOREVER | Localization content. | Never automatically delete. |
| uploads | 5009 | 1.5 MB | id, user_id | created_at, updated_at, deleted_at | PROTECTED_FOREVER | Media/uploads linked to business content. | Never automatically delete. |
| user_coupons | 0 | 16 KB | user_id | expiry_date | PROTECTED_FOREVER | User coupon entitlement. | Never automatically delete. |
| users | 55 | 64 KB | id | email_verified_at, created_at, updated_at | PROTECTED_FOREVER | User identity/account data. | Never automatically delete. |
| vendor_performance_snapshots | 0 | 64 KB | id, seller_id | snapshot_date, created_at, updated_at | PROTECTED_FOREVER | Seller performance/dispute data. | Never automatically delete. |
| visitor_metrics | 11032 | 9.4 MB | id, user_id | time_spent, created_at, updated_at | ARCHIVE_BEFORE_PRUNE | Visitor analytics/support log. | Archive/export first; manual approval required. |
| wallets | 0 | 48 KB | id, user_id | created_at, updated_at | PROTECTED_FOREVER | Wallet/payment evidence. | Never automatically delete. |
| warranties | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog warranty data. | Never automatically delete. |
| warranty_translations | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog warranty localization data. | Never automatically delete. |
| wholesale_prices | 0 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Catalog pricing data. | Never automatically delete. |
| wishlists | 1 | 16 KB | id, user_id, product_id | created_at, updated_at | PROTECTED_FOREVER | User/product relationship data. | Never automatically delete. |
| zones | 1 | 16 KB | id | created_at, updated_at | PROTECTED_FOREVER | Geographic shipping configuration. | Never automatically delete. |

## 5. Protected Forever Tables

155 tables are protected forever: addons, addresses, affiliate_configs, affiliate_logs, affiliate_payments, affiliate_stats, affiliate_users, affiliate_withdraw_requests, ai_prompts, app_translations, area_translations, areas, attribute_category, attribute_translations, attribute_values, attributes, banner_text_versions, blog_categories, blog_product, blog_tag, blog_translations, blog_versions, blogs, brand_translations, brands, business_settings, carrier_range_prices, carrier_ranges, carriers, carts, categories, category_translations, cities, city_translations, club_point_details, club_points, cmi_callback_logs, colors, combined_orders, commission_histories, contacts, conversations, countries, coupon_usages, coupons, currencies, custom_alerts, custom_label_translations, custom_labels, custom_sale_alerts, customer_package_payments, customer_package_translations, customer_packages, customer_product_translations, customer_products, dynamic_popups, element_styles, element_translations, element_types, elements, elite_subscriptions, email_templates, flash_deal_products, flash_deal_translations, flash_deals, follow_sellers, frequently_bought_products, home_categories, inventory_logs, languages, manual_payment_methods, measurement_points, messages, migrations, model_has_permissions, model_has_roles, note_translations, notes, notification_type_translations, notification_types, onessta_city_maps, onessta_pickup_city_maps, onessta_shipments, onessta_tracking_events, order_details, order_tracking_histories, orders, otp_configurations, page_translations, pages, payku_payments, payku_transactions, payment_attempts, payment_informations, payment_methods, payment_tokens, payments, permissions, pickup_addresses, pickup_point_translations, pickup_points, point_assignment_logs, point_templates, preorder_products, preorders, product_categories, product_collection_product, product_collections, product_queries, product_stocks, product_taxes, product_translations, products, promotions, proxypay_payments, refund_requests, reviews, role_has_permissions, role_translations, roles, seller_categories, seller_documents, seller_packages, seller_withdraw_requests, sellers, shipping_box_sizes, shipping_systems, shops, size_chart_details, size_charts, sms_templates, social_credentials, staff, states, stock_alert_subscriptions, stock_subscriptions, subscribers, tags, taxes, ticket_replies, tickets, top_banner_translations, top_banners, transactions, translations, uploads, user_coupons, users, vendor_performance_snapshots, wallets, warranties, warranty_translations, wholesale_prices, wishlists, zones.

These are protected because they contain business, identity, catalog, payment, order, seller, shipping, wallet, payout, tax, legal, support, settings, access-control, content, or media records.

## 6. Archive Before Prune Tables

15 tables require archive/export and manual approval before any future pruning: ai_usage_logs, analytics_daily_summaries, analytics_summaries, audit_logs, blog_subscriber_logs, email_verification_attempts, firebase_notifications, image_optimization_states, last_viewed_products, notifications, onessta_webhook_logs, product_views, registration_verification_codes, semantic_embeddings, visitor_metrics.

These are operational logs or analytics/support records. They may matter for diagnostics, disputes, consent/security review, payment/shipping investigation, or performance history.

## 7. Direct Prune Candidates

9 tables are only candidates for a future dry-run/manual-approved pruning design: failed_jobs, health_metrics, jobs, password_resets, personal_access_tokens, pulse_aggregates, pulse_entries, pulse_values, searches.

These are technical queue, token, observability, or search-noise tables. They are not approved for deletion by this audit.

## 8. Unknown Protected Tables

No existing table from this inventory is currently in `UNKNOWN_PROTECTED`. Any new or unclassified table defaults to `UNKNOWN_PROTECTED` in `App\Services\Maintenance\DatabaseRetentionAuditService`.

## 9. Tables That Must Never Be Automatically Deleted

The protected blacklist always wins. Critical examples include users, sellers, staff, shops, products, product_stocks, product_translations, product_categories, categories, brands, uploads, orders, combined_orders, order_details, payment_attempts, cmi_callback_logs, payments, payment_tokens, wallets, transactions, refund_requests, onessta_shipments, onessta_tracking_events, conversations, messages, tickets, reviews, business_settings, addons, roles, permissions, taxes, and all related translations/configuration tables.

## 10. Recommended Next Step

It is safe to design a future dry-run-only pruning command later, but only if it has no execute mode at first, uses the protected blacklist from `config/mayush_retention.php`, reports affected rows without mutating data, and includes focused tests for every candidate table. Direct deletion should remain unavailable until a separate manual approval phase.
