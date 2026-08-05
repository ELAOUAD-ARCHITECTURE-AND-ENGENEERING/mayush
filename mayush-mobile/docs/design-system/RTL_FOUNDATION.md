# Mayush Mobile Design System - RTL & Directional Foundation

## Overview

This document specifies the definitive Right-To-Left (RTL) architecture for supporting Arabic localization (`MvpAppLanguage: 'ar'`) alongside French LTR (`MvpAppLanguage: 'fr'`).

---

## 1. Centralized Direction Principles

1. **No Fragmented Conditions**: Components MUST NOT scatter ad-hoc `language === 'ar'` checks. Layout direction is controlled via `useTheme().isRTL` and flexbox direction properties.
2. **Text Alignment**: `MayushText` automatically defaults to `right` when `isRTL == true` and `left` when `isRTL == false`.
3. **Flex Direction**: `Inline` primitive automatically supports `reverseRTL={true}` to flip element order in RTL.
4. **Bottom Tab Bar**: `BottomTabBar` reverses tab item visual order when `isRTL == true`.
5. **Back Arrow Icon**: Back arrow points right (`→`) in RTL (`ar`) and left (`←`) in LTR (`fr`).
6. **Numeric & Phone Prefix Content**: Phone country code (`+212`) and order numbers retain LTR alignment even inside RTL forms.

---

## 2. Mandatory Non-Mirroring Rules

- **Brand Logo & Artwork**: `MayushLogo` and brand vectors must NEVER be mirrored.
- **Product Images**: Product thumbnails, hero banners, and gallery photos must NEVER be mirrored horizontally.
- **Star Rating Graphics**: Rating icons preserve left-to-right filling unless explicitly required by design specification.
