# Phase 5 API Integration & Repository Report

## Overview

This report details the Laravel API integration, repository services, endpoint response mappings, header policy compliance, and state transitions for Phase 5 (`entry-discovery-product-vertical-slice`).

---

## 1. Integrated Laravel API Endpoints

| Endpoint | Method | Triggering Screen / Flow | Repository Method | Response DTO | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/api/v2/sliders` | `GET` | `SCR-DIS-001` (Home Hero Carousel) | `catalogService.getSliders()` | `SliderCollectionDto` | `INTEGRATED_REAL_DATA` |
| `/api/v2/categories/featured` | `GET` | `SCR-DIS-001` (Home Categories) | `catalogService.getFeaturedCategories()` | `CategoryDto[]` | `INTEGRATED_REAL_DATA` |
| `/api/v2/categories` | `GET` | `SCR-DIS-002` (Root Categories) | `catalogService.getRootCategories()` | `CategoryDto[]` | `INTEGRATED_REAL_DATA` |
| `/api/v2/products/todays-deal` | `GET` | `SCR-DIS-001` (Home Today's Deals) | `catalogService.getTodaysDeals()` | `ProductMiniDto[]` | `INTEGRATED_REAL_DATA` |
| `/api/v2/products/best-seller` | `GET` | `SCR-DIS-001` (Home Best Sellers) | `catalogService.getBestSellers()` | `ProductMiniDto[]` | `INTEGRATED_REAL_DATA` |
| `/api/v2/products/category/{slug}` | `GET` | `SCR-DIS-003` (Category Product List) | `catalogService.getCategoryProducts()` | `PaginatedCollectionDto<ProductMiniDto>` | `INTEGRATED_REAL_DATA` |
| `/api/v2/products/{id}` | `GET` | `SCR-PRD-001` (Product Details) | `catalogService.getProductDetails()` | `ProductDetailDto` | `INTEGRATED_REAL_DATA` |
| `/api/v2/products/variant/price` | `POST` | `SCR-PRD-002` (Variant Selector Sheet) | `catalogService.getVariantPrice()` | `VariantPriceResponseDto` | `INTEGRATED_REAL_DATA` |

---

## 2. API Header Policy Compliance

Every outgoing request issued via [`apiClient.ts`](file:///c:/laragon/www/mayush/mayush-mobile/src/services/api/apiClient.ts) strictly enforces:

```http
Accept: application/json
App-Language: fr | ar
Authorization: Bearer <access_token> (when available)
```

- **Content-Type Rule**: `Content-Type: application/json` is attached **ONLY when a request body is present** (e.g. `POST /api/v2/products/variant/price`). Bodyless `GET` requests omit `Content-Type`.

---

## 3. Server-Authoritative Variant Pricing

Variant price and stock calculations in [`VariantSelectorSheet.tsx`](file:///c:/laragon/www/mayush/mayush-mobile/src/screens/product/VariantSelectorSheet.tsx) are **strictly server-authoritative**:
- On every color swatch or option chip selection change, the app posts to `POST /api/v2/products/variant/price` with `{ slug, variants, color, quantity }`.
- The server response (`VariantPriceResponseDto`) determines `price`, `stock`, `in_stock`, and variant image.
- No local price arithmetic is performed by the mobile client.
