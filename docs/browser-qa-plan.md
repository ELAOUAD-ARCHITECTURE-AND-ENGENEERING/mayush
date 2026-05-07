# Browser QA Plan

Date: 2026-05-06

Use this plan against a seeded staging environment with mail, queue, storage, and payment/carrier test credentials configured. Capture the URL, browser, user role, and screenshots for every failure.

## 1. Registration

- Steps: open registration, submit weak password, submit valid customer details, verify account/session.
- Expected result: weak password is rejected; valid account is created and lands on the expected customer area.
- Failure signals: validation missing, 500 error, user created with weak password, redirect loop.

## 2. Login

- Steps: log in with valid and invalid credentials.
- Expected result: valid credentials authenticate; invalid credentials show validation/error message.
- Failure signals: failed login count mismatch, blank page, dashboard access without auth.

## 3. Reset Password

- Steps: request reset, inspect test mailbox/log, open reset link, submit new password, retry old password.
- Expected result: token works once; new password logs in; old password fails.
- Failure signals: no mail, invalid token always accepted, old password still works.

## 4. Product Search

- Steps: search single-word, multi-word, partial keyword, and special-character queries.
- Expected result: relevant products render and no-result state is clear.
- Failure signals: only last word searched, SQL/JS error, no-result page crashes.

## 5. Product Filters

- Steps: apply category, brand, price, rating, seller/vendor, sort, then paginate.
- Expected result: query string persists and results match selected filters.
- Failure signals: filters reset on pagination, wrong counts, broken URLs.

## 6. Product Details

- Steps: open simple and variant products from listing.
- Expected result: images, price, stock, seller, and SEO tags render.
- Failure signals: broken images, missing price, missing seller, metadata crash.

## 7. Variant Selection

- Steps: select valid, out-of-stock, and invalid combinations.
- Expected result: price, SKU, stock, image, and cart availability update.
- Failure signals: stale price, wrong stock, purchase enabled for unavailable variant.

## 8. Add To Cart

- Steps: add simple and variant product, adjust quantity, view modal.
- Expected result: item is added with correct price/quantity and cart modal opens.
- Failure signals: 404/405, wrong price, modal buttons point to wrong route.

## 9. Buy Now

- Steps: click buy now for simple, variant, guest, and out-of-stock cases.
- Expected result: valid purchase reaches checkout/login; invalid stock is blocked.
- Failure signals: route error, missing variant payload, bypassed stock validation.

## 10. Checkout

- Steps: cart to shipping, payment selection, order confirmation using test gateway/COD.
- Expected result: order is created once and payment status follows gateway response.
- Failure signals: duplicate order/payment, failed callback marks paid, checkout redirect loop.

## 11. Contact Form

- Steps: open contact page, submit invalid and valid forms.
- Expected result: invalid data validates; valid submission queues/sends according to config.
- Failure signals: POST maps to admin GET, CSRF failure, 500 on send.

## 12. Follow Seller

- Steps: as guest click follow, then as customer follow/unfollow twice.
- Expected result: guest is redirected to login; customer follow is idempotent.
- Failure signals: 405, duplicate rows, unfollow uses GET, no feedback.

## 13. Seller Notes

- Steps: seller creates note, opens note modal, edits, deletes; second seller attempts access.
- Expected result: own notes work; cross-seller access is blocked.
- Failure signals: modal AJAX mismatch, wrong seller data visible, list breaks after error.

## 14. Product Create/Edit

- Steps: seller creates product with thumbnail/gallery/variants, then edits text only and images.
- Expected result: images persist when unchanged and update when replaced.
- Failure signals: post-create 404 images, saved info lost, duplicate/corrupt variant rows.

## 15. Stock Alert Subscription

- Steps: open out-of-stock product as guest and customer, submit alert, submit duplicate.
- Expected result: visible form appears only when out of stock; duplicate handled gracefully.
- Failure signals: no form, missing email validation, duplicate subscriptions.

## 16. Affiliate Apply

- Steps: customer opens affiliate apply, submits valid data, retries duplicate application.
- Expected result: first application succeeds; duplicate is blocked or explained.
- Failure signals: 404/405, no status change, duplicate applications.

## 17. Wallet Recharge

- Steps: open wallet, initiate recharge, simulate success/failure/duplicate callback.
- Expected result: failed callback does not credit; successful callback credits once.
- Failure signals: missing recharge action, double credit, unknown callback 500.

## 18. Order Tracking

- Steps: customer opens own order tracking, another user's order, and missing tracking order.
- Expected result: own tracking renders; unauthorized access blocked; empty state is controlled.
- Failure signals: fake production carrier data, 500 on missing carrier, data leak.

## 19. Admin Sitemap Button

- Steps: admin triggers sitemap generation; non-admin attempts same route.
- Expected result: admin gets success/failure message; non-admin blocked.
- Failure signals: button uses wrong method/route, generator crashes, sitemap invalid XML.

## 20. Product Import/Export

- Steps: download template, import valid/invalid products, export product list, compare fields.
- Expected result: valid rows import, invalid rows report errors, export includes business-critical data.
- Failure signals: missing required columns, broken image URLs, incomplete export.
