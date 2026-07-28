# Mayush Search Relevance Dataset

## Purpose

This dataset is the source of truth for comparing MySQL, OpenSearch, and later
semantic search behavior. It must be versioned and reviewed; it must not be
generated only from model intuition.

## Ownership

- Dataset owner: Search Product Owner.
- Business reviewer: Marketplace or merchandising representative.
- Technical reviewer: Search/Backend Lead.
- Labeling QA: QA Lead.

## Minimum initial coverage

- 300 English queries.
- 300 French queries.
- 300 Arabic queries.
- 200 Darija Arabic-script queries.
- 200 Arabizi queries using Latin letters and digits.
- 200 mixed French/Darija/Arabic queries.
- 200 identifier, dimension, currency, brand, and attribute queries.
- 100 no-result and malicious-input queries.

Queries may belong to multiple categories, but every category must be recorded.

## Required cases

- Exact SKU, barcode, model, product ID, and seller reference searches.
- English, French, Arabic, Darija, Arabizi, and mixed-language queries.
- Dimensions such as `160x200`, `2m`, and `120 cm`.
- Currency terms such as `DH`, `MAD`, and Arabic currency words.
- Typos, accents, Arabic normalization, and mixed scripts.
- Long descriptive queries.
- Category and brand queries.
- Queries that should intentionally return no result.
- SQL, HTML, script, wildcard, and control-character payloads.

## Label scale

- `0`: irrelevant.
- `1`: weak or marginally related.
- `2`: relevant.
- `3`: highly relevant or exact target.

Identifier queries additionally record exact-match success. Ambiguous labels are
reviewed by a second reviewer and excluded from scored results until resolved.

## Measurement method

- Version every dataset change.
- Maintain an 80/20 development and holdout split.
- Report nDCG@10 and MRR with bootstrap 95% confidence intervals.
- Report results by language, query family, and query length.
- Use at least 7–14 days for online behavior metrics, depending traffic volume.
- Do not use a permanent arbitrary regression percentage without statistical
  and business justification.
