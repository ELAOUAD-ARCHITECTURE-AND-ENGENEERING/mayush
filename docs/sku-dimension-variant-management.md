# SKU Dimension Variant Management

Dynamic Dimension variants are created from the product SKU combination table when Dimension is the SKU axis. The table contributes extra selected Dimension choice values to the product form, so the existing combination generator creates a normal `product_stocks` row for each new variant when the product is saved.

## Data model

- `products.attributes` and `products.choice_options` hold the selected Dimension attribute and the product-local Dimension values.
- `product_stocks.id` is the unique row identifier for each generated variant stock record. The table already stores a stock row per variant with `variant`, `sku`, `price`, `qty`, `image`, `length`, `width`, `height`, and `dimension_unit`.
- The feature does not need a fixed variant count or a new variant table. Additional Dimension choices generate additional `product_stocks` records through `ProductStockService`.

## Validation

- A Dimension product must keep at least one Dimension value after table removals.
- Dimension values are normalized by trimming spaces and comparing case-insensitively before duplicate checks.
- Supported Dimension labels are `LxWxH unit`, `min-max unit`, and upper buckets such as `+1000cm`.
- The SKU table validates new rows while the user types. `ProductRequest` repeats the uniqueness and format checks before database writes.

## Client state flow

1. Clicking the plus button in any Dimension SKU row inserts an empty table row.
2. Typing a valid unique Dimension label assigns that row a hidden `choice_options_{dimension_id}[]` value and names its stock inputs using the generated variant key.
3. Editing price, SKU, quantity, dimensions, unit, or photo keeps using the existing product form state.
4. Removing a table row records `removed_sku_variants[]`; product save filters that Dimension choice and skips the removed stock row during stock rebuild.
5. Save remains explicit. The product form submit persists the product choices and rebuilt stock rows together.
