# Agent Readiness Cloudflare Scan Ground Truth Report

Date: 2026-04-26  
Project: Mayush  
Production URL: https://mayushdesign.com  
Source input: Cloudflare/isitagentready scan findings supplied by the project owner

## Executive Summary

The scanner is useful, but its findings are not all equal SEO/GEO blockers.

Current live verification shows that the most important issue, canonical `/robots.txt`, is now fixed in production: it returns `200` with `Content-Type: text/plain; charset=UTF-8` and includes the sitemap directive. The scan result that said "robots.txt not found" was either taken before the latest deploy/cache refresh or was affected by a transient scanner abort.

Remaining real gaps:

- Live production does not yet include the new `Content-Signal` directive until the current repository update is deployed.
- Live production does not yet include the new explicit `OAI-SearchBot`, `Claude-Web`, and `anthropic-ai` rules until the current repository update is deployed.
- Live production does not yet negotiate `Accept: text/markdown` until the current repository update is deployed.
- Live production does not yet expose useful agent discovery `Link` headers until the current repository update is deployed.
- Live production does not yet expose the repository `/.well-known/api-catalog`, `/openapi.json`, or agent skills routes until the current repository update is deployed.

Items that should not be implemented blindly:

- OAuth/OIDC discovery, OAuth protected resource metadata, MCP server cards, WebMCP, x402, UCP, ACP, and MPP require real product decisions and backing services. Publishing placeholder metadata would misrepresent the platform and could expose unsupported or sensitive surfaces.

## Evidence Collected

Live checks run against production:

| Check | Result | Evidence |
| --- | --- | --- |
| `https://mayushdesign.com/robots.txt` | Pass | `200 OK`, `text/plain; charset=UTF-8` |
| Robots sitemap directive | Pass | `Sitemap: https://mayushdesign.com/sitemap.xml` present |
| Current robots AI entries | Partial pass | `GPTBot`, `ChatGPT-User`, `Google-Extended`, `PerplexityBot`, `ClaudeBot`, `Bingbot`, `Googlebot`, `CCBot` present |
| `Accept: text/markdown` on homepage | Fail | Returns `Content-Type: text/html; charset=UTF-8` |
| `/.well-known/api-catalog` | Fail | `404 Not Found` |
| `/.well-known/agent-skills/index.json` | Fail | `404 Not Found` |
| `/.well-known/openid-configuration` | Not implemented | `404 Not Found` |
| `/.well-known/oauth-authorization-server` | Not implemented | `404 Not Found` |
| `/.well-known/oauth-protected-resource` | Not implemented | `404 Not Found` |
| `/.well-known/mcp/server-card.json` | Not implemented | `404 Not Found` |
| `/.well-known/acp.json` | Blocked/not implemented | `403 Forbidden` |
| `/openapi.json` | Blocked/not implemented | `403 Forbidden` |

Repository evidence:

- Canonical `/robots.txt` route exists in `routes/web.php`.
- Public robots file exists at `public/robots.txt`.
- Repository remediation now adds `Content-Signal: ai-train=yes, search=yes, ai-input=yes`.
- Repository remediation now adds explicit `OAI-SearchBot`, `Claude-Web`, and `anthropic-ai` rules.
- Repository remediation now adds `/.well-known/api-catalog`, `/openapi.json`, `/docs/api`, and `/.well-known/agent-skills/*` Laravel routes.
- Repository remediation now adds public HTML `Link` headers and scoped Markdown-for-Agents negotiation.
- The OpenAPI document intentionally covers only the documented promotions API surface and declares both `System-Key` and bearer-token requirements.
- No OAuth, MCP, ACP, x402, UCP, MPP, or payment discovery metadata is published because no safe backing service exists yet.

## Finding-by-Finding Analysis

### 1. Publish `/robots.txt` With Clear Crawl Rules

Scanner issue: `robots.txt not found`

Ground truth: Fixed live after latest deployment. Production currently returns `200` plain text for `/robots.txt`.

Status: Confirmed fixed, keep monitoring.

Recommended action:

- Keep the Laravel route as a fallback for server document-root differences.
- Keep `public/robots.txt` as the source content.
- Add a CI/live audit check so `/robots.txt` must return `200`, `text/plain`, and include the sitemap directive.

### 2. Link Response Headers For Agent Discovery

Scanner issue: Could not check Link headers; operation aborted.

Ground truth: Homepage response currently does not expose agent discovery `Link` headers on live production. The repository now adds those headers for successful public HTML responses.

Status: Repository fix added, pending deploy/live validation.

Recommended action:

- Add response `Link` headers on the homepage and public HTML pages after discovery resources exist.
- Do not link to missing documents.
- Candidate headers after implementation:
  - `</.well-known/api-catalog>; rel="api-catalog"; type="application/linkset+json"`
  - `</openapi.json>; rel="service-desc"; type="application/openapi+json"`
  - `</docs/api>; rel="service-doc"; type="text/html"`

### 3. Markdown For Agents

Scanner issue: Site does not support Markdown for Agents.

Ground truth: Requests with `Accept: text/markdown` still receive HTML on live production. The repository now adds a scoped Laravel fallback that returns Markdown for successful public HTML responses.

Status: Repository fix added, pending deploy/live validation.

SEO/GEO impact: Medium. It can help agents extract public page content, but traditional SEO is not blocked.

Recommended action:

- Prefer Cloudflare Markdown for Agents if available in the zone, because it avoids risky origin-side HTML-to-Markdown conversion.
- If implemented in Laravel, scope it to public GET pages only and keep HTML as the browser default.
- Validate with `curl -H "Accept: text/markdown" https://mayushdesign.com/`.

### 4. AI Crawler Rules

Scanner issue: Cannot check AI rules without robots.txt.

Ground truth: The crawler rules are now checkable. Live entries are good but were incomplete relative to the scanner examples at the time of the scan.

Status: Repository fix added, pending deploy/live validation.

Recommended action:

- Deploy explicit entries for:
  - `OAI-SearchBot`
  - `Claude-Web`
  - `anthropic-ai`
- Keep `GPTBot`, `ChatGPT-User`, `Google-Extended`, `PerplexityBot`, `ClaudeBot`, `CCBot`, `Googlebot`, and `Bingbot`.
- Keep `SEO/audit.py` checking the expanded bot list.

### 5. Content Signals In `robots.txt`

Scanner issue: Cannot check Content Signals without robots.txt.

Ground truth: Robots is now available. Live production did not include a `Content-Signal` directive at scan time; the repository now includes one for the next deployment.

Status: Repository fix added, pending deploy/live validation.

Recommended action:

- Deploy the current SEO/GEO-maximizing policy:

```txt
Content-Signal: ai-train=yes, search=yes, ai-input=yes
```

- If Mayush later wants AI search visibility but not model training, change both signals and crawler rules together. Do not allow training crawlers while declaring `ai-train=no` unless that mixed policy is intentional and documented.

### 6. API Catalog

Scanner issue: API Catalog not found.

Ground truth: Missing on live production. The repository now publishes a Laravel-routed API catalog and `/openapi.json`.

Status: Repository fix added, pending deploy/live validation.

Recommended action:

- Keep the catalog minimal and only advertise the documented promotions API.
- Keep `System-Key` and bearer-token requirements explicit in `/openapi.json`.
- Do not advertise admin, payment callback, or private operational routes.

### 7. OAuth/OIDC Discovery Metadata

Scanner issue: No OAuth/OIDC discovery metadata found.

Ground truth: Missing. Current API uses Laravel Sanctum bearer auth patterns, not a full OIDC authorization server.

Status: Correctly absent for now.

Recommended action:

- Do not publish fake OIDC metadata.
- Implement only if Mayush chooses to become an OAuth/OIDC authorization server or integrates one.
- If future agent API access is required, evaluate Laravel Passport or an external identity provider.

### 8. OAuth Protected Resource Metadata

Scanner issue: No OAuth Protected Resource Metadata found. The scan listed this twice.

Ground truth: Missing. This depends on a real OAuth authorization server and defined scopes.

Status: Correctly absent for now.

Recommended action:

- Defer until OAuth/OIDC is real.
- If implemented later, define resources, issuer, authorization servers, and scopes first.

### 9. MCP Server Card

Scanner issue: MCP discovery returned `403` or no card.

Ground truth: No MCP server was found in the repo or route surface.

Status: Correctly absent for now.

Recommended action:

- Do not publish an MCP card unless Mayush operates an MCP server.
- If added later, the server card must point to a real transport endpoint and real capabilities.

### 10. Agent Skills Discovery Index

Scanner issue: Agent Skills index not found.

Ground truth: Missing on live production. The repository now publishes a read-only skills index and individual skill documents through Laravel routes.

Status: Repository fix added, pending deploy/live validation.

Recommended action:

- Consider publishing a small index only after deciding which public agent actions are safe.
- Good candidates: product search guidance, seller-shop discovery, order-tracking help page, returns-policy lookup.
- Each skill entry needs a stable URL and SHA-256 digest.

### 11. WebMCP Browser Tools

Scanner issue: No WebMCP tools detected on page load. The scan listed this twice.

Ground truth: No WebMCP code exists in the current Blade/public JavaScript surface.

Status: Optional and experimental.

Recommended action:

- Defer until browser support and business use cases are clear.
- If implemented, start with read-only tools such as product search or category navigation.
- Do not expose checkout, payment, account, or seller actions through WebMCP without a security review.

### 12. x402 HTTP Payments

Scanner note: x402 not detected.

Ground truth: Missing, but not required for current SEO/GEO.

Status: Defer.

Recommended action:

- Do not implement unless Mayush plans paid API/content access for agents.

### 13. Universal Commerce Protocol

Scanner note: UCP profile not found.

Ground truth: Missing.

Status: Defer.

Recommended action:

- Treat as strategic research, not an SEO blocker.

### 14. Agentic Commerce Protocol

Scanner note: ACP discovery returned `403`.

Ground truth: The route is blocked or not implemented.

Status: Defer.

Recommended action:

- Do not publish ACP metadata until Mayush has an ACP-compatible product/order/checkout API.
- If agent-native commerce becomes a priority, ACP should be planned with authentication, inventory, cart, payment, refunds, and fraud controls.

### 15. Machine Payment Protocol

Scanner issue: Could not check MPP discovery.

Ground truth: `/openapi.json` is blocked/not implemented and no `x-payment-info` extensions exist.

Status: Defer.

Recommended action:

- Do not implement unless Mayush wants machine-payable API operations.
- If future agent payments are desired, start by publishing accurate OpenAPI for public API operations before adding payment extensions.

## Priority Ranking

| Priority | Work | Reason |
| --- | --- | --- |
| P0 | Keep `/robots.txt` live at 200 and monitored | Core SEO/GEO crawlability |
| P1 | Add missing AI crawler names and Content Signals | Directly improves AI crawler policy clarity |
| P1 | Add live audit coverage for robots, content signals, and markdown negotiation | Prevents regression |
| P2 | Add API catalog and Link headers only after publishing accurate API docs | Useful agent discovery, but must be truthful |
| P2 | Enable Markdown for Agents through Cloudflare or scoped Laravel negotiation | Useful for AI content extraction |
| P3 | Agent skills index | Optional discovery layer |
| P4 | OAuth/OIDC, OAuth protected resource metadata, MCP, WebMCP, x402, UCP, ACP, MPP | Requires product/security architecture, not a quick SEO patch |

## Conclusion

The Cloudflare scan correctly points toward modern agent-readiness work, but the only urgent SEO/GEO-class issue was `/robots.txt`, and production now returns it successfully. The next practical work should be: expand robots AI directives, add Content Signals with a documented policy, add audit checks, and then decide whether Mayush truly wants public agent API discovery before publishing API catalog or auth metadata.

## References

- RFC 9309 Robots Exclusion Protocol: https://www.rfc-editor.org/rfc/rfc9309
- RFC 8288 Web Linking: https://www.rfc-editor.org/rfc/rfc8288
- RFC 9727 API Catalog: https://www.rfc-editor.org/rfc/rfc9727
- RFC 8414 OAuth Authorization Server Metadata: https://www.rfc-editor.org/rfc/rfc8414
- RFC 9728 OAuth Protected Resource Metadata: https://www.rfc-editor.org/rfc/rfc9728
- Cloudflare Markdown for Agents: https://developers.cloudflare.com/fundamentals/reference/markdown-for-agents/
- Content Signals: https://contentsignals.org/
- Cloudflare Agent Skills Discovery RFC: https://github.com/cloudflare/agent-skills-discovery-rfc
- WebMCP draft: https://webmachinelearning.github.io/webmcp/
- x402: https://www.x402.org/
- Agentic Commerce Protocol: https://www.agenticcommerce.dev/
