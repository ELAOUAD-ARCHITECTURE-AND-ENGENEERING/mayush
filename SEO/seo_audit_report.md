# SEO & GEO Audit Report

**Date:** 2026-04-24
**Target:** https://mayushdesign.com

## 1. Technical SEO Audit
- **Title:** MAYUSH DESIGN | L'élégance au Cœur de Votre Espace
- **Description:** Missing completely.
- **H1 Tags:** Found multiple H1 tags ['MAYUSH DESIGN', 'Abonnez-vous à notre newsletter']. Usually best practice is one H1 per page.
- **Open Graph (OG) Tags:** Missing core tags like og:title, og:description, og:image.

## 2. Generative Engine Optimization (GEO) Access
- **robots.txt:** Cloudflare managed content blocks critical AI bots: ClaudeBot, GPTBot, Amazonbot, Applebot-Extended. We need an explicit local obots.txt that attempts to allow them if Cloudflare honors it.
- **JSON-LD Schema:** No pplication/ld+json detected. This is vital for AI context.
- **Sitemap:** Missing / empty.

## Action Plan
1. Override obots.txt to allow Googlebot, Bingbot, PerplexityBot, ChatGPT-User, ClaudeBot, GPTBot.
2. Generate base sitemap.xml.
3. Add Schema.org JSON-LD to pp.blade.php.
