# Mayush Mobile — Mandatory Figma Design Guidelines

**File status:** Mandatory  
**Applies to:** Every Mayush mobile Figma page, component, pattern, prototype, and developer-handoff frame  
**Primary platform:** Native buyer mobile application for iOS and Android  
**Primary viewport:** 393 × 852 px  
**Languages:** French LTR and Arabic RTL  

---

## 1. Purpose

This file is the mandatory visual and structural authority for the Mayush Mobile Figma project.

Its purpose is to prevent the Figma result from drifting away from:

- the approved Mayush logo;
- the established Mayush color palette;
- the graphic charter shown in the generated screenshots;
- the recurring layout, spacing, component, icon, and interaction patterns;
- the premium furniture and interior-design identity of Mayush.

Every editable Figma screen must remain visibly faithful to its uploaded source screenshot while also using one coherent Mayush design system.

A screen is not considered complete because it is merely attractive. It must be:

1. faithful to the source screenshot;
2. faithful to the Mayush graphic charter;
3. consistent with the rest of the mobile application;
4. editable and built from reusable Figma components;
5. practical for future React Native implementation.

---

## 2. Mandatory source-of-truth order

When visual sources disagree, apply this order:

1. **Official Mayush logo asset**
2. **This guideline**
3. **Approved foundation and asset boards**
4. **The uploaded source screenshot for the current screen**
5. **Existing reusable Mayush Figma components**
6. **Designer interpretation**

Designer interpretation is the last resort. Codex must never invent a new Mayush visual language.

### Conflict rule

- Preserve the source screenshot’s composition, hierarchy, and UX intent.
- Correct any screenshot element that violates the official logo, palette, currency, localization, icon, navigation, or RTL rules in this document.
- Never “improve” a screen by replacing the Mayush visual identity with a generic Material, iOS, marketplace, or dashboard style.
- When a conflict cannot be resolved safely, mark it as `NEEDS PRODUCT DECISION` and continue with non-conflicting work.

---

## 3. Brand personality

The Mayush mobile interface must communicate:

- premium but approachable;
- warm and refined;
- contemporary furniture and interior design;
- calm, spacious, and trustworthy;
- Moroccan context without ornamental overload;
- strong product photography;
- restrained use of decorative motifs;
- clear commerce and checkout interactions.

### Visual character

Use:

- warm cream page backgrounds;
- white or soft-beige card surfaces;
- deep navy typography and navigation;
- Mayush orange for primary actions and key emphasis;
- generous spacing;
- rounded cards and controls;
- soft shadows;
- premium, realistic furniture photography;
- subtle Moroccan-contemporary atmosphere.

Avoid:

- cold blue corporate styling;
- neon colors;
- heavy gradients;
- excessive glassmorphism;
- overly dark interfaces;
- dense dashboard layouts;
- exaggerated shadows;
- random decorative colors;
- generic technology-app aesthetics;
- fake luxury effects such as excessive gold.

---

## 4. Official logo rules

The official Mayush logo is immutable.

### Mandatory

- Use only the official uploaded Mayush logo asset.
- Preserve the exact proportions, lettering, furniture motifs, and colors.
- Keep the full wordmark readable.
- Use the logo as an image/vector asset, not manually typed text.
- Use one consistent logo asset throughout the file.
- Use a compact logo only when it is an official approved logo variation.
- Keep sufficient clear space around the logo.

### Forbidden

- Do not redraw the logo.
- Do not approximate the logo with text.
- Do not change its colors.
- Do not stretch, compress, skew, rotate, outline, or crop it.
- Do not add `BUYER`, `APP`, `SHOP`, or any other word to the logo.
- Do not place the logo inside an arbitrary shape unless the screenshot explicitly requires it.
- Do not use different generated approximations on different screens.
- Do not use an invented monochrome version.

### Logo clear space

Use minimum clear space equal to approximately the height of the letter details around all sides.

### Logo placement

Preferred placements:

- centered at the top of authentication, onboarding, success, and system-state screens;
- centered or left-aligned inside the application header where shown by the source screenshot;
- never compete with the page title;
- never reduce the logo until its internal details become unreadable.

---

## 5. Mandatory color palette

Use Figma variables. Do not hardcode different colors independently on each screen.

### Brand primitives

| Token | Value | Use |
|---|---:|---|
| `brand/orange/500` | `#D97434` | Primary action, active state, major emphasis |
| `brand/orange/600` | `#C66528` | Pressed or strong action |
| `brand/orange/100` | `#F8E6D7` | Soft orange backgrounds |
| `brand/navy/900` | `#1F2A3A` | Main text, icons, navigation |
| `brand/navy/700` | `#344154` | Secondary dark elements |
| `neutral/black` | `#111111` | Rare high-contrast text |
| `neutral/gray/500` | `#A7AFBA` | Secondary icons and metadata |
| `neutral/gray/300` | `#D9DEE4` | Borders and disabled outlines |
| `neutral/gray/100` | `#F3F5F7` | Skeletons and inactive areas |
| `surface/cream` | `#F2E8DA` | Warm page or decorative surface |
| `surface/cream-light` | `#FAF6F0` | Main page background |
| `surface/white` | `#FFFFFF` | Cards, inputs, sheets |
| `border/warm` | `#E7DED3` | Default border and divider |

### Semantic colors

| Token | Value | Use |
|---|---:|---|
| `status/success` | `#2E8B57` | Success, in stock, confirmed |
| `status/success-soft` | `#E7F4EC` | Success background |
| `status/warning` | `#D98A24` | Warning, low stock |
| `status/warning-soft` | `#FAEFD9` | Warning background |
| `status/error` | `#C7473A` | Error, destructive action |
| `status/error-soft` | `#F8E5E2` | Error background |
| `status/info` | `#3F6D94` | Informational status only |
| `overlay/default` | `rgba(31,42,58,0.48)` | Modal and bottom-sheet overlay |

### Color restrictions

- Primary buttons must use Mayush orange.
- Main text and icons must use deep navy.
- Page backgrounds must remain warm cream or cream-light.
- Use green, warning orange, red, or blue only for semantic status.
- Do not introduce purple, cyan, bright yellow, pink, or unrelated brand colors.
- Do not use euro-style blue commerce themes.
- Do not replace Mayush orange with gold.

---

## 6. Typography

All text must remain editable Figma text.

### General rules

- Use one approved Latin font family and one compatible Arabic font family.
- Do not mix unrelated font families between screens.
- Use a clear hierarchy.
- Avoid excessively condensed or decorative interface fonts.
- Do not use the logo typeface as UI text.
- Load fonts before editing text through Figma MCP.

### Recommended hierarchy

| Style | Size | Weight | Line height |
|---|---:|---:|---:|
| Display | 32 | 700 | 40 |
| Page title | 28 | 700 | 36 |
| Section title | 22 | 700 | 29 |
| Card title | 17 | 600 | 24 |
| Body | 15 | 400 | 22 |
| Body strong | 15 | 600 | 22 |
| Body small | 13 | 400 | 19 |
| Caption | 12 | 400 | 16 |
| Button | 15 | 600 | 20 |
| Price large | 24 | 700 | 30 |
| Price regular | 17 | 700 | 23 |
| Badge | 11 | 600 | 14 |

### Arabic

- Use an Arabic font with excellent mobile readability.
- Maintain the same semantic hierarchy as French.
- Increase line height when needed.
- Avoid compressed Arabic text.
- Verify shaping, alignment, punctuation, numerals, and mixed `MAD` values.
- Never use mirrored Arabic glyphs or manually reversed strings.

---

## 7. Layout grid and spacing

### Main viewport

- Root mobile frame: `393 × 852`.
- Use vertical Auto Layout.
- Respect safe areas.
- Long pages must scroll; never scale down long content to fit one viewport.
- Source screenshots may be taller than the viewport, but editable screens must use a realistic scroll structure.

### Core spacing tokens

| Token | Value |
|---|---:|
| `space/4` | 4 |
| `space/8` | 8 |
| `space/12` | 12 |
| `space/16` | 16 |
| `space/20` | 20 |
| `space/24` | 24 |
| `space/32` | 32 |
| `space/40` | 40 |
| `space/48` | 48 |

### Default layout rules

- Screen horizontal padding: `20 px`.
- Compact horizontal padding: `16 px`.
- Section spacing: `24–32 px`.
- Card gap: `12–16 px`.
- Grid gap: `12 px`.
- Header content gap: `12 px`.
- Label-to-input gap: `8 px`.
- Input-to-input gap: `14–16 px`.
- Primary CTA bottom safe spacing: minimum `16 px`.

### Alignment

- Major titles align with page content.
- Cards use one consistent alignment.
- Do not mix centered and left-aligned blocks without a clear reason.
- Authentication and success screens may use centered content.
- Commerce lists and settings pages should normally align to the reading direction.

---

## 8. Radius, borders, and shadows

### Radius tokens

| Token | Value |
|---|---:|
| `radius/small` | 8 |
| `radius/medium` | 12 |
| `radius/large` | 16 |
| `radius/card` | 18 |
| `radius/input` | 14 |
| `radius/button` | 16 |
| `radius/modal` | 24 |
| `radius/pill` | 999 |

### Borders

- Default border: `1 px`.
- Use `border/warm` or the semantic status border.
- Avoid heavy black borders.
- Selected controls may use `2 px` Mayush orange.

### Shadows

Use restrained, soft elevation.

- Card: subtle shadow only.
- Sticky CTA/navigation: slightly stronger separation shadow.
- Bottom sheet/dialog: clear but soft elevation.
- Avoid large blurry shadows or dark floating effects.

---

## 9. Icon system

Use one coherent outline icon family.

### Icon rules

- Default size: `24 × 24`.
- Compact size: `20 × 20`.
- Small metadata icon: `16 × 16`.
- Stroke: approximately `1.75–2 px`.
- Rounded line caps and joins.
- Default icon color: deep navy.
- Active icon color: Mayush orange.
- Disabled icon color: neutral gray.
- Semantic icons may use semantic status colors.

### Forbidden

- Do not mix filled, outlined, 3D, emoji, and illustrated icons in the same navigation or control family.
- Do not create random icons with inconsistent stroke weights.
- Do not replace the Mayush icon set with generic icons when approved icons are already uploaded.
- Do not use text glyphs as icons.

---

## 10. Navigation

The buyer bottom navigation is fixed:

1. `Accueil`
2. `Catégories`
3. `Favoris`
4. `Panier`
5. `Compte`

### Mandatory rules

- Always use the same five tabs.
- Always use the same order.
- Always use the same icons.
- Use deep navy or gray for inactive tabs.
- Use Mayush orange for the active tab.
- Badge style must remain consistent.
- Bottom navigation should be a reusable component instance.
- Do not show bottom navigation on dedicated authentication, payment redirect, full-screen gallery, dialog, or terminal success screens unless required by the source flow.

### Forbidden

- Do not replace a tab with `Explorer` or `Commandes`.
- Do not add a sixth tab.
- Do not vary the active indicator between screens.
- Do not use different icon families across screens.

---

## 11. Headers

Create reusable header variants.

Required variants:

- logo header;
- back + title header;
- back + logo + actions;
- search header;
- title + actions;
- transparent media header.

### Header consistency

- Same safe-area behavior.
- Same icon-button dimensions.
- Same side padding.
- Same title typography.
- Same logo sizing.
- Same cart and notification badge treatment.

Do not redesign the header independently on every screen.

---

## 12. Buttons and controls

### Primary button

- Height: `54–56 px`.
- Background: Mayush orange.
- Text: white.
- Radius: `16 px`.
- Clear pressed, loading, and disabled states.
- One main primary action per screen or modal.

### Secondary button

- White or transparent background.
- Orange border and text.
- Same height and radius as primary when paired.

### Text button

- Mayush orange or deep navy depending on hierarchy.
- No ambiguous decorative underline unless links require it.

### Inputs

- Height: `54–56 px`.
- White surface.
- Warm border.
- Radius: `14 px`.
- Deep navy text.
- Gray placeholder.
- Consistent leading/trailing icons.
- Clear focus, filled, error, success, and disabled states.

### Touch targets

Minimum interactive area: `44 × 44 px`.

---

## 13. Cards and commerce components

### Product cards

Must consistently support:

- product image;
- wishlist action;
- category or seller metadata where appropriate;
- product name;
- current price in `MAD`;
- optional previous price;
- optional discount, new, low-stock, or out-of-stock badge;
- rating where relevant.

### Currency

- Use `MAD` only.
- Never use `€`, `$`, `FCFA`, `SAR`, or other currencies.
- Use consistent formatting, for example `2 950 MAD`.
- Do not use mixed decimal and thousands formats without a product decision.

### Moroccan context

- Default phone prefix: `+212`.
- Use realistic Moroccan cities and delivery context.
- Do not display “delivery throughout France.”
- Do not use foreign flags as default phone selectors.

---

## 14. Photography and imagery

### Product photography

- Premium furniture and interior products only.
- Use realistic, high-quality images.
- Warm neutral environments.
- Consistent crop and image treatment.
- Do not insert electronics, sneakers, random cars, or unrelated retail products.
- Do not embed interface text, prices, badges, or buttons inside product images.

### Decorative imagery

- Must support the Mayush interior-design identity.
- Use warm neutral materials, natural wood, boucle, linen, stone, plaster, and subtle Moroccan elements.
- Avoid decorative clutter.
- Do not overpower the product or interface.

---

## 15. Screen fidelity rule

Every editable screen must be reconstructed from its source screenshot.

### Required comparison process

For each screen:

1. Place the source screenshot beside the editable frame.
2. Identify:
   - root structure;
   - header;
   - major sections;
   - card geometry;
   - image proportions;
   - typography hierarchy;
   - spacing;
   - CTA placement;
   - navigation;
   - overlays;
   - screen state.
3. Rebuild the screen using variables, styles, and component instances.
4. Compare the editable frame against the source screenshot.
5. Correct visible drift before proceeding.

### Fidelity target

The editable screen must preserve:

- the same visual hierarchy;
- the same major composition;
- the same content density;
- the same image prominence;
- the same CTA hierarchy;
- the same modal or bottom-sheet behavior;
- the same warm premium Mayush feeling.

### Allowed adaptations

Only adapt when necessary for:

- standard 393 × 852 viewport;
- scrolling;
- safe areas;
- dynamic content;
- accessibility;
- reusable components;
- correct French/Arabic localization;
- mandatory brand corrections defined in this guideline.

### Forbidden deviations

- replacing a screenshot with a generic template;
- simplifying the screen until its visual identity is lost;
- adding unrequested sections;
- removing important sections;
- changing card proportions without reason;
- replacing furniture imagery with placeholders;
- changing the brand palette;
- changing navigation;
- inventing a new layout style.

---

## 16. French and Arabic RTL

Arabic is not a translated LTR screenshot. It is a true RTL layout.

### RTL rules

- Use RTL Auto Layout direction.
- Right-align Arabic labels and body text.
- Mirror back arrows and chevrons.
- Mirror icon placement inside inputs.
- Mirror horizontal order of navigation and relevant list structures.
- Preserve product photography orientation unless semantically necessary.
- Preserve readable number and price order.
- Keep `MAD` and `+212` understandable.
- Verify OTP direction and digit readability.
- Allow Arabic text expansion.
- Use the same Mayush components with RTL variants rather than unrelated Arabic-only designs.

---

## 17. Figma construction rules

Every final screen must be:

- editable;
- built with Auto Layout;
- built with variables;
- built with text and effect styles;
- composed from reusable component instances;
- named consistently;
- traceable to its source screenshot;
- suitable for Dev Mode and future React Native handoff.

### Never

- use the screenshot as the screen background;
- flatten interface text;
- flatten buttons or icons;
- detach component instances without a documented reason;
- hardcode visual values when a variable exists;
- build screens as unstructured groups;
- leave unnamed layers;
- create duplicate components for the same role.

---

## 18. Required naming

### Source

`SRC / <exact-original-filename>`

### Editable screen

`UI / <Feature> / <Screen> / <Locale> / <State>`

### Overlay

`OVERLAY / <Feature> / <Name> / <Locale> / <State>`

### Component

`CMP / <Category> / <Component>`

### Pattern

`PATTERN / <Feature> / <Pattern>`

### Variables

Use slash-separated semantic names.

---

## 19. Visual compliance gate

A screen cannot be marked complete until all answers are `YES`.

### Brand

- [ ] Official logo asset used without modification
- [ ] Mayush orange used for primary action
- [ ] Deep navy used for primary text/icons
- [ ] Warm cream/white surfaces used
- [ ] No unrelated brand colors
- [ ] Premium furniture/interior identity preserved

### Fidelity

- [ ] Source screenshot is present beside the editable screen
- [ ] Major composition matches the screenshot
- [ ] Section order matches
- [ ] Main image proportions match
- [ ] CTA hierarchy matches
- [ ] Density and whitespace feel equivalent
- [ ] No generic replacement layout was introduced

### Components

- [ ] Auto Layout used
- [ ] Variables bound
- [ ] Text styles used
- [ ] Reusable component instances used
- [ ] Icon family is consistent
- [ ] Bottom navigation is correct
- [ ] Header variant is correct

### Content

- [ ] Currency is MAD
- [ ] Morocco context is preserved
- [ ] French copy is not mixed with English/Arabic
- [ ] Arabic is true RTL
- [ ] No invented `BUYER` text
- [ ] No unrelated product categories

### Native readiness

- [ ] Text is editable
- [ ] Long text can expand
- [ ] Screen can scroll
- [ ] Safe areas are respected
- [ ] Touch targets are sufficient
- [ ] Sticky elements do not overlap content
- [ ] Loading/error/empty states are mapped

---

## 20. Failure conditions

A screen must be returned to correction when any of the following occurs:

- incorrect or recreated Mayush logo;
- wrong brand palette;
- euro or foreign currency;
- unrelated product imagery;
- inconsistent bottom navigation;
- generic design replacing the screenshot;
- major source-composition mismatch;
- mixed icon families;
- missing RTL mirroring;
- flattened, non-editable UI;
- hardcoded visual values despite existing tokens;
- clipped or overlapping text;
- inaccessible touch targets;
- unstructured layers;
- missing source traceability.

---

## 21. Required QA record

For every editable screen, record:

```text
Source filename:
Editable frame:
Feature:
Locale:
State:
Logo compliant: YES/NO
Palette compliant: YES/NO
Screenshot fidelity: PASS/FAIL
Navigation compliant: YES/NO/N/A
RTL compliant: YES/NO/N/A
Component reuse: PASS/FAIL
Auto Layout: PASS/FAIL
Visual screenshot checked: YES/NO
Remaining differences:
Correction performed:
Final status:
```

Allowed final statuses:

- `COMPLIANT`
- `COMPLIANT_WITH_DOCUMENTED_ADAPTATION`
- `NEEDS_CORRECTION`
- `NEEDS_PRODUCT_DECISION`

---

## 22. Final rule

The Mayush Figma project must look like one application designed by one system.

It must not look like a collection of unrelated generated screenshots or generic templates.

The official logo, palette, typography, iconography, spacing, navigation, components, photography, and RTL behavior must remain consistent throughout the complete Figma file.

No screen may proceed to developer handoff until it passes the visual compliance gate in this document.
