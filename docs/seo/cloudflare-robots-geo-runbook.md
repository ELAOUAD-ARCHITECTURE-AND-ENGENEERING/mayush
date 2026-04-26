# Cloudflare Robots GEO Runbook

## Current Live Problem

The repository `public/robots.txt` allows search and AI crawlers, but live production is still served by Cloudflare Managed Content rules.

Live `https://mayushdesign.com/robots.txt` currently blocks:

- `GPTBot`
- `ClaudeBot`
- `Google-Extended`
- `CCBot`

It also omits:

- `Sitemap: https://mayushdesign.com/sitemap.xml`
- `Content-Signal: search=yes,ai-input=yes` when Cloudflare content signals are enabled

## Required Cloudflare Change

In Cloudflare for `mayushdesign.com`:

1. Open the domain dashboard.
2. Go to the robots/content-signal managed rules area.
3. Disable the managed rule that injects AI bot `Disallow: /` entries, or override it with a custom robots policy.
4. Ensure the live robots response includes:

```txt
User-agent: *
Allow: /

User-agent: GPTBot
Allow: /

User-agent: ChatGPT-User
Allow: /

User-agent: Google-Extended
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: Bingbot
Allow: /

User-agent: Googlebot
Allow: /

User-agent: CCBot
Allow: /

Sitemap: https://mayushdesign.com/sitemap.xml
```

If Cloudflare content signals remain enabled, the live response should explicitly allow AI input:

```txt
Content-Signal: search=yes,ai-input=yes,ai-train=no
```

## Sitemap Cache Check

The live sitemap currently has whitespace before the XML declaration, which strict XML parsers reject.

After deployment:

1. Regenerate the sitemap on production:

```bash
php8.2 artisan app:generate-sitemap --base-url=https://mayushdesign.com
```

2. Clear Laravel and edge caches.
3. Recheck:

```bash
python SEO/audit.py https://mayushdesign.com --timeout 20
```

The target result is `0 failed`.
