# Agent Readiness Implementation Subplan

Parent workstream: Full SEO/GEO Remediation Plan For Mayush  
Date: 2026-04-26  
Status: In progress. Phase 1 is implemented in the repository; Phase 2 crawler-policy audit checks are implemented. Link headers, Markdown negotiation, API catalog, and agent skills remain planned work.

## Objective

Close the real agent-readiness gaps found by the Cloudflare scan while keeping the project truthful. This subplan adds crawl policy clarity, agent discovery headers, optional Markdown negotiation, and measured validation. It intentionally avoids publishing placeholder OAuth, MCP, or payment metadata before Mayush has real services behind those documents.

## Scope Rules

- SEO/GEO-critical work comes first: robots, AI crawler policy, content signals, sitemap, and validation.
- Discovery documents must describe real routes and supported capabilities.
- Protected API/auth/payment metadata must not be published until the underlying platform support exists.
- All changes should preserve the current Laravel/Blade architecture. No Vue/Vite work is required.

## Phase 1 - Robots And AI Usage Policy

Priority: P1  
Owner: repository + production cache

Tasks:

1. Update `public/robots.txt`.
2. Keep existing rules for:
   - `Googlebot`
   - `Bingbot`
   - `GPTBot`
   - `ChatGPT-User`
   - `ClaudeBot`
   - `PerplexityBot`
   - `Google-Extended`
   - `CCBot`
3. Add explicit rules for:
   - `OAI-SearchBot`
   - `Claude-Web`
   - `anthropic-ai`
4. Decide and document the AI content usage policy.
5. Add a `Content-Signal` directive that matches that policy.

Recommended SEO/GEO-maximizing policy:

```txt
Content-Signal: ai-train=yes, search=yes, ai-input=yes
```

Alternative protective policy:

```txt
Content-Signal: ai-train=no, search=yes, ai-input=yes
```

If the protective policy is selected, separately review whether `GPTBot` and `Google-Extended` should remain fully allowed.

Acceptance checks:

- `https://mayushdesign.com/robots.txt` returns `200`.
- Content type includes `text/plain`.
- Sitemap directive is present.
- All required AI/search user agents are explicitly covered.
- Content-Signal exists and matches the documented policy.

## Phase 2 - Audit Tool Expansion

Priority: P1  
Owner: repository

Tasks:

1. Update `SEO/audit.py` bot list to include `OAI-SearchBot`, `Claude-Web`, and `anthropic-ai`.
2. Add checks for:
   - `Content-Signal`
   - `ai-train`
   - `search`
   - `ai-input`
3. Keep failures explicit instead of aborting after the first failed section.
4. Add test coverage for the new robots/content-signal checks.

Deferred to the next implementation slice:

   - homepage `Link` headers
   - `Accept: text/markdown` negotiation
   - `/.well-known/api-catalog` status and content type

Acceptance checks:

- `python -m py_compile SEO/audit.py` passes.
- `python SEO/audit.py https://mayushdesign.com --timeout 20` reports all sections.
- CI live validation includes the new checks.

## Phase 3 - Link Headers And Minimal Discovery Surface

Priority: P2  
Owner: repository

Tasks:

1. Create a small config-backed discovery map.
2. Add response `Link` headers only for resources that exist.
3. Start with conservative links:

```http
Link: </.well-known/api-catalog>; rel="api-catalog"; type="application/linkset+json"
Link: </openapi.json>; rel="service-desc"; type="application/openapi+json"
```

4. Add headers only on public `GET` HTML responses, beginning with the homepage.
5. Do not advertise OAuth, MCP, ACP, x402, UCP, or MPP endpoints yet.

Acceptance checks:

- Homepage returns expected `Link` headers.
- Headers do not point to missing or blocked URLs.
- Browser HTML behavior is unchanged.

## Phase 4 - API Catalog And OpenAPI Publication

Priority: P2  
Owner: repository + API owner review

Tasks:

1. Decide whether Mayush wants public automated API discovery.
2. If yes, publish a truthful `/.well-known/api-catalog`.
3. Convert or expose the existing `docs/api/v2/promotions.yaml` through a stable public route only if it is safe to advertise.
4. Create `/openapi.json` or `/openapi.yaml` that covers only supported, documented APIs.
5. Mark protected endpoints clearly with bearer-token requirements.
6. Do not include private admin, payment callback, or internal operational routes.

Acceptance checks:

- `/.well-known/api-catalog` returns `application/linkset+json`.
- The catalog links resolve with `200`.
- OpenAPI validates.
- No unsupported or sensitive endpoints are advertised.

## Phase 5 - Markdown For Agents

Priority: P2  
Owner: Cloudflare first, repository fallback

Tasks:

1. Prefer enabling Cloudflare Markdown for Agents at the zone level if available.
2. If Cloudflare support is not available, design a Laravel fallback for public pages:
   - only `GET` requests
   - only indexable public pages
   - only when `Accept: text/markdown` is present
   - return `Content-Type: text/markdown; charset=UTF-8`
3. Start with homepage, category listing, product detail, blog listing, blog detail, seller shop, and policy pages.
4. Keep browser default as HTML.

Acceptance checks:

- `curl -H "Accept: text/markdown" https://mayushdesign.com/` returns Markdown.
- Default browser request still returns HTML.
- Markdown output contains title, H1, canonical URL, summary, and important links.

## Phase 6 - Agent Skills Index

Priority: P3  
Owner: repository + product owner

Tasks:

1. Decide which public agent skills are safe and useful.
2. Candidate read-only skills:
   - product discovery
   - category browsing
   - seller-shop lookup
   - policy lookup
   - order tracking guidance without exposing order data
3. Create skill documents under `public/.well-known/agent-skills/`.
4. Generate SHA-256 digests.
5. Publish `/.well-known/agent-skills/index.json`.

Acceptance checks:

- Index returns `application/json`.
- Every skill URL returns `200`.
- Every digest matches the published skill document.
- No skill executes sensitive account, checkout, payment, or seller actions.

## Phase 7 - Deferred Architecture Decisions

Priority: P4  
Owner: product + security + engineering

Do not implement these as quick SEO fixes:

- OAuth/OIDC discovery
- OAuth protected resource metadata
- MCP server card
- WebMCP browser tools
- x402
- Universal Commerce Protocol
- Agentic Commerce Protocol
- Machine Payment Protocol

Decision criteria before implementation:

- There is a real service behind the discovery document.
- Authentication and authorization are designed.
- Abuse, fraud, and rate limiting are reviewed.
- API docs and schemas are accurate.
- The business wants third-party agents to use the capability.

## Suggested First Pull Request

Title: `seo: expand agent robots policy and audit checks`

Included changes:

- Update `public/robots.txt`.
- Add Content-Signal according to approved policy.
- Extend `SEO/audit.py`.
- Add/extend focused tests.
- Update `SEO/Agent_Readiness_Cloudflare_Scan_Report.md` with post-change results.

Excluded changes:

- OAuth/OIDC metadata
- MCP server card
- WebMCP
- Agent payment protocols
- API catalog unless the API owner approves public discovery

## Validation Commands

```bash
php artisan test tests/Feature/SeoRemediationTest.php --stop-on-failure
python -m py_compile SEO/audit.py
python SEO/audit.py https://mayushdesign.com --timeout 20
curl -sS -D - -o NUL https://mayushdesign.com/robots.txt
curl -sS https://mayushdesign.com/robots.txt
curl -sS -D - -o NUL -H "Accept: text/markdown" https://mayushdesign.com/
curl -sS -D - -o NUL https://mayushdesign.com/.well-known/api-catalog
```

## Rollback Plan

- Revert the PR commit if robots parsing, homepage rendering, or route discovery breaks.
- Keep `/robots.txt` and `/sitemap.xml` route tests as permanent regression coverage.
- If a discovery document accidentally exposes unsupported APIs, remove the route and remove matching `Link` headers in the same rollback.
