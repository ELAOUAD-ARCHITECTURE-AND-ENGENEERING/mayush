# Mayush Search Vocabulary and Transliteration Contract

Status: **design-only; no synonym or transliteration expansion is active**

## Ownership and change workflow

- Vocabulary owner: Search Product Owner.
- Domain reviewer: Marketplace merchandising representative familiar with Moroccan furniture/decor vocabulary.
- Language reviewers: French, Arabic and Darija reviewers.
- Technical reviewer: Search/Backend Lead.
- QA owner: Search relevance QA.

Every vocabulary release must have a version, source/change reason, reviewer approvals, affected locales, examples, expected query classes, regression results and a rollback reference. Vocabulary changes are deployed as one reviewable artifact and are never scattered through controllers or Blade templates.

## Planned vocabulary groups

- French, English and Arabic product/category synonyms.
- Darija Arabic-script aliases.
- Latin Darija and Arabizi aliases.
- Furniture, decoration, materials, colors and common misspellings.
- Dimensions and units: `160x200`, `2m`, `120 cm`, `m²`.
- Currency terms: `DH`, `MAD`, `درهم`.
- Exact brand, model, SKU, barcode, product ID and seller-reference aliases.

## Darija and Arabizi rules

1. Preserve the original query for telemetry/debugging and display context.
2. Detect Arabizi at token level; do not globally replace digits.
3. Expand only through reviewed, domain-specific aliases with confidence and locale metadata.
4. Keep exact identifier/number fields separate from normalized text fields.
5. Treat `5zana`, `3oud` and `7did` as candidate Darija tokens only when the surrounding token/context supports the alias; `5`, `3`, `7`, prices, quantities, dimensions and model numbers remain literal in numeric query classes.
6. Mixed queries such as `table beige b taman mzyan` must preserve both product terms and numeric/currency terms.
7. Arabic normalization must be tested separately from Arabic stemming; no analyzer choice is final until the relevance dataset compares exact, normalized and light-stemmed fields.

## Required regression examples

| Query | Required protection |
| --- | --- |
| `khzana` / `5zana` | Candidate aliasing may connect the forms; neither form may be discarded |
| `3oud` | Candidate aliasing may connect the form; digit `3` is not globally rewritten |
| `7did` | Candidate aliasing may connect the form; digit `7` is not globally rewritten |
| `table beige b taman mzyan` | Mixed French/Darija token boundaries remain searchable |
| `160x200`, `120 cm`, `2m` | Dimensions remain exact numeric tokens |
| `500 DH`, `500 MAD`, `500 درهم` | Price/currency tokens remain literal and filter-safe |
| `SKU-5`, `model 3A`, `barcode 7003` | Exact identifiers are never transliterated or stemmed aggressively |

No OpenSearch mappings or synonym files should be finalized until this contract has a reviewed vocabulary version and the relevance dataset contains these cases.
