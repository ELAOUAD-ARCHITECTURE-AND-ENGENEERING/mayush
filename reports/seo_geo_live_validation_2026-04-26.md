# SEO/GEO Live Validation - 2026-04-26

## Target

- Production URL: `https://mayushdesign.com`
- Command: `python3 SEO/audit.py https://mayushdesign.com --timeout 20`

## Result

- Passed: 33
- Failed: 9

## Passing Checks

- Homepage HTTP status is `200`.
- Homepage title length is valid.
- Meta description length is valid.
- Canonical URL is present and production HTTPS.
- Homepage has exactly one meaningful H1.
- Open Graph and Twitter card tags are present.
- JSON-LD parses successfully.
- `robots.txt` returns `200` with `text/plain; charset=UTF-8`.
- Googlebot, Bingbot, GPTBot, ChatGPT-User, ClaudeBot, PerplexityBot, Google-Extended, and CCBot have explicit live rules and are not blocked.
- OAI-SearchBot, Claude-Web, and anthropic-ai are not blocked by the wildcard rule, but explicit live rules are not deployed yet.
- `robots.txt` includes `Sitemap: https://mayushdesign.com/sitemap.xml`.
- `sitemap.xml` returns `200` with XML content type.

## Failing Checks

- Live `robots.txt` is missing explicit `OAI-SearchBot`, `Claude-Web`, and `anthropic-ai` rules.
- Live `robots.txt` is missing `Content-Signal: ai-train=yes, search=yes, ai-input=yes`.
- Expanded audit checks fail `Content-Signal ai-train`, `Content-Signal search`, and `Content-Signal ai-input`.
- Live `sitemap.xml` has whitespace before the XML declaration.
- Strict sitemap XML parsing fails with `XML or text declaration not at start of entity`.

## Ground Truth

Canonical `https://mayushdesign.com/robots.txt` is now fixed live and returns the production robots file.

The repository now expands `public/robots.txt` with `OAI-SearchBot`, `Claude-Web`, `anthropic-ai`, and `Content-Signal: ai-train=yes, search=yes, ai-input=yes`. These new entries are pending deployment and cache refresh.

The live sitemap is served as XML, but the response body still begins with whitespace before `<?xml`. Local `public/sitemap.xml` starts correctly at byte 0, so production likely has a stale/static sitemap copy or webserver static-file precedence bypassing Laravel's normalized route response.

## Required Action

1. Deploy the latest repository update containing expanded AI crawler rules and Content Signals.
2. Clear Laravel route/config/view caches on production.
3. Regenerate production sitemap with `APP_URL=https://mayushdesign.com`.
4. Confirm the live sitemap body starts with `<?xml` at byte 0; if not, remove or overwrite the stale/static production sitemap copy that starts with a newline.
5. Purge Cloudflare cache for `/robots.txt` and `/sitemap.xml`.
6. Re-check Cloudflare managed robots/content-signal settings from `docs/seo/cloudflare-robots-geo-runbook.md`; keep GPTBot, OAI-SearchBot, ChatGPT-User, ClaudeBot, Claude-Web, anthropic-ai, PerplexityBot, Google-Extended, CCBot, Googlebot, and Bingbot allowed.
7. Run the new `Live SEO GEO Validation` GitHub workflow or run:

```bash
python3 SEO/audit.py https://mayushdesign.com --timeout 20
```

Target: `0 failed`.
