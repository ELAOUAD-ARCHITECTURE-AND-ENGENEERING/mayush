# Mayush Mobile Design System - Pixel Parity Protocol

## Overview

This protocol specifies mandatory verification steps for side-by-side, overlay, and flicker visual comparison of implemented React Native components against source reference screenshots.

---

## 1. Comparison Methods

1. **Side-by-Side Comparison**:
   - Source screenshot and app rendering placed side-by-side at 1:1 scale (393 × 852 viewport reference).
2. **Overlay Comparison**:
   - Rendered component screenshot overlaid on top of source design reference at 50% opacity to detect padding, margin, or alignment drift.
3. **Flicker Comparison**:
   - Alternating between source design region and native rendering to expose geometry shifts.

---

## 2. Mandatory Acceptance Statuses

- **`PIXEL_PARITY_PASS`**: 100% visual match.
- **`PIXEL_PARITY_WITH_DOCUMENTED_ACCESSIBILITY_ADAPTATION`**: Adaptation made strictly for touch target (min 44×44) or contrast requirement.
- **`PIXEL_PARITY_WITH_DOCUMENTED_RENDERING_DIFFERENCE`**: Native platform font rendering difference.
- **`NEEDS_PIXEL_CORRECTION`**: Visual discrepancy detected; correction required before completion.
