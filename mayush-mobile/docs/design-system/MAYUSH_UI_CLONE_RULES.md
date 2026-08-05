# Mayush Mobile Design System - UI Clone & Pixel Parity Policy

## Executive Mandate

> [!IMPORTANT]  
> The design-reference screenshots under `mayush-mobile/design-reference/mayush-mobile-design/` represent the **exact visual target**. They are NOT suggestions, moodboards, or generic inspiration. The React Native implementation must reconstruct the visual design as accurately as technically possible.

---

## 1. Core Implementation Rules

1. **Native Editable Components**:
   - Complete screenshots MUST NEVER be shipped as screen background images.
   - Screenshot text MUST NEVER replace native, localized `<Text>` components.
   - Screenshot buttons MUST NEVER replace interactive, accessible `<TouchableOpacity>` or `<Pressable>` components.

2. **Official Brand & Graphic Assets**:
   - The Mayush logo MUST use the official vector/high-res source asset copied to `mayush-mobile/assets/brand/`. Generated text-based approximations are strictly forbidden.
   - Category and product icons MUST use exact approved SVG/PNG vectors.

3. **Pixel Parity Standards**:
   - Every screen implemented in future phases MUST undergo side-by-side visual comparison, overlay comparison, and pixel-difference QA.
   - "Close enough", "similar", or "looks okay" are NOT acceptable completion statuses.
   - Allowed acceptance statuses:
     - `PIXEL_PARITY_PASS`
     - `PIXEL_PARITY_WITH_DOCUMENTED_ACCESSIBILITY_ADAPTATION`
     - `PIXEL_PARITY_WITH_DOCUMENTED_RENDERING_DIFFERENCE`
     - `NEEDS_PIXEL_CORRECTION`

4. **Zero Generic Framework Styling**:
   - Default visual themes from Material Design, React Native Paper, or iOS defaults MUST NOT override the Mayush visual identity.
   - All spacing, typography, colors, radii, and shadows MUST come strictly from the Mayush Design Tokens.
