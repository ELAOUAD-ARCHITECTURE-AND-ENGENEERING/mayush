# SEO/GEO Live Validation - 2026-04-26

## Target

- Production URL: `https://mayushdesign.com`
- Command: `python SEO/audit.py https://mayushdesign.com --timeout 20`

## Result

- Passed: 19
- Failed: 4

## Passing Checks

- Homepage HTTP status is `200`.
- Homepage title length is valid.
- Meta description length is valid.
- Canonical URL is present and production HTTPS.
- Homepage has exactly one meaningful H1.
- Open Graph and Twitter card tags are present.
- JSON-LD parses successfully.
- Googlebot, Bingbot, GPTBot, ChatGPT-User, ClaudeBot, PerplexityBot, Google-Extended, and CCBot are not blocked by the currently returned live body.
- `sitemap.xml` returns `200` with XML content type.

## Failing Checks

- Canonical `https://mayushdesign.com/robots.txt` returns `404` HTML.
- Because canonical `/robots.txt` is not serving the production robots file, crawlers do not receive the `Sitemap: https://mayushdesign.com/sitemap.xml` directive.
- Live `sitemap.xml` has whitespace before the XML declaration.
- Strict sitemap XML parsing fails with `XML or text declaration not at start of entity`.

## Ground Truth

The repository `public/robots.txt` is already AI-friendly and includes the sitemap directive. Live `https://mayushdesign.com/public/robots.txt` serves that correct file, but canonical `https://mayushdesign.com/robots.txt` currently returns Laravel HTML with `404`.

This means the current production issue is no longer only Cloudflare managed robots policy. The canonical robots endpoint is not serving the repository robots file. A Laravel `/robots.txt` route has been added so the canonical endpoint works even when the server document root or rewrite rules expose static assets through `/public`.

The live sitemap is served as XML, but the response body begins with whitespace before `<?xml`. The local sitemap file starts correctly, so the route now normalizes the served sitemap body by removing a UTF-8 BOM and leading whitespace before returning it.

## Required Action

1. Deploy the latest commit containing the canonical `/robots.txt` route and sitemap response normalization.
2. Clear Laravel route/config/view caches on production.
3. Regenerate production sitemap with `APP_URL=https://mayushdesign.com`.
4. Purge Cloudflare cache for `/robots.txt` and `/sitemap.xml`.
5. Re-check Cloudflare managed robots/content-signal settings from `docs/seo/cloudflare-robots-geo-runbook.md`; keep GPTBot, ChatGPT-User, ClaudeBot, PerplexityBot, Google-Extended, CCBot, Googlebot, and Bingbot allowed.
6. Run the new `Live SEO GEO Validation` GitHub workflow or run:

```bash
python SEO/audit.py https://mayushdesign.com --timeout 20
```

Target: `0 failed`.
