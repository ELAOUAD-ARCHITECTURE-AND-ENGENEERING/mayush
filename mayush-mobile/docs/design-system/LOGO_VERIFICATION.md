# Mayush Mobile Design System - Dynamic Logo & Verification Architecture

## Overview

This document records the dynamic logo resolution architecture and verification status of the Mayush brand logo used in the Mayush Mobile application.

---

## 1. Dynamic Logo Architecture (Admin Panel Database Integration)

- **Dynamic Source Endpoint**: `GET /api/v2/business-settings` (`header_logo` / `system_logo_white` / `system_logo_black`)
- **Theme Provider Integration**: `ThemeProvider` maintains `logoUrl` in `ThemeContext` and can update dynamically whenever settings change in the Laravel admin panel.
- **Component Behavior**: [`MayushLogo`](file:///c:/laragon/www/mayush/mayush-mobile/src/design-system/components/brand/MayushLogo.tsx) accepts an optional `uri` prop or consumes `theme.logoUrl`. It attempts to load the dynamic logo image via remote HTTP URL.
- **Offline / Network Fallback**: If the network is pending, offline, or an image load error occurs (`onError`), `MayushLogo` automatically falls back to bundled asset `assets/brand/logo.png`.

---

## 2. Bundled Fallback Asset Source

| Candidate Path | Source | Dimensions / Format | Background | Visual Parity Status |
| :--- | :--- | :--- | :--- | :--- |
| `public/uploads/all/K1BBOXpN3PG1rK6WX5ppXhOFXMckNMH5o90kf9d8.webp` | **Mayush Web Production DB (Upload #2889)** | 160 × 160 px (WEBP) | Transparent | `VERIFIED_EXACT` |

- **Local Fallback File**: `mayush-mobile/assets/brand/logo.png`
- **Format**: PNG with transparent alpha channel
- **Status**: **`VERIFIED_EXACT`**
