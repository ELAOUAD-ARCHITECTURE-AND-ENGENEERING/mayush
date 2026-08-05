# Mayush Mobile Design System - Typography Decision Document

## Overview

This document records typography decisions, font family assignments, scale mappings, and fallback behaviors for the Mayush Mobile application.

---

## 1. Font Family Strategy

- **Latin (French / English)**: Uses React Native `System` font stack (`San Francisco` on iOS, `Roboto` on Android) mapped with exact weight and letter spacing parameters matching Mayush brand guidelines.
- **Arabic (RTL)**: Uses React Native `System` font stack (`San Francisco Arabic` / `SF Arabic` on iOS, `Noto Naskh Arabic` / `Roboto` on Android) with adjusted line heights to prevent baseline clipping.
- **Runtime Font Loading Status**: Fully supported via native system typography without external font load delays. Custom web-font loading tracked as optional enhancement (`PIXEL_PARITY_WITH_DOCUMENTED_RENDERING_DIFFERENCE`).

---

## 2. Typography Scale Mapping

| Style Name | Size (px) | Line Height (px) | Weight | Visual Role |
| :--- | :--- | :--- | :--- | :--- |
| `display` | 30 | 38 | Bold (700) | Hero titles & splash branding |
| `pageTitle` | 24 | 32 | Bold (700) | Primary screen headers |
| `sectionTitle` | 20 | 28 | SemiBold (600) | Section section headers |
| `cardTitle` | 17 | 24 | Medium (500) | Product & card titles |
| `body` | 15 | 22 | Regular (400) | Primary body text |
| `strongBody` | 15 | 22 | SemiBold (600) | Emphasized body text |
| `smallBody` | 13 | 18 | Regular (400) | Secondary subtitles & meta |
| `caption` | 11 | 15 | Regular (400) | Footnotes & timestamps |
| `button` | 15 | 22 | SemiBold (600) | Action button labels |
| `priceLarge` | 20 | 28 | Bold (700) | Primary price highlights |
| `priceRegular` | 15 | 22 | Bold (700) | Card price tags |
| `badge` | 11 | 15 | SemiBold (600) | Discount & status badges |
| `inputLabel` | 13 | 18 | Medium (500) | Form field labels |
