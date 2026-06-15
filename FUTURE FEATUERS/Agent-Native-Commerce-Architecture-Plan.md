# Agent-Native Commerce Architecture Plan

Date: 2026-04-28
Project: Mayush
Status: Future architecture plan, not active SEO/GEO remediation

## Executive Position

Mayush is already agent-readable for the safe SEO/GEO scope: robots, sitemap, Link headers, Markdown negotiation, API catalog, OpenAPI, and Agent Skills are live and validated.

The next scanner gaps are not missing SEO tags. They are agent-native commerce capabilities that require real backend services, authentication, authorization, abuse controls, and product decisions. Publishing placeholder OAuth, MCP, A2A, WebMCP, x402, UCP, or ACP metadata would make the platform look more capable than it is and could create security or support risk.

This plan defines the correct path from agent-readable to agent-operable.

## Goals

- Let trusted agents discover Mayush products, categories, brands, sellers, and policies through stable machine-readable APIs.
- Keep checkout, payment, customer data, seller data, and order data protected until authentication and authorization are designed.
- Introduce OAuth/OIDC, MCP, A2A, WebMCP, and commerce protocols only when there is a real service behind each discovery document.
- Preserve the current Laravel architecture and avoid Vue/Vite assumptions.

## Non-Goals

- Do not publish fake OAuth/OIDC discovery metadata.
- Do not expose checkout, payment, account, seller, or order mutation tools in the first phase.
- Do not implement x402, MPP, UCP, or ACP just to improve a scanner score.
- Do not expose private admin, callback, or operational routes through OpenAPI.

## Current Baseline

Already implemented and validated:

- `robots.txt` with explicit AI/search crawler rules and Content Signals.
- `sitemap.xml` with production HTTPS URLs.
- Homepage agent discovery `Link` headers.
- `Accept: text/markdown` support for public HTML pages.
- `/.well-known/api-catalog`.
- `/openapi.json` for the documented promotions API surface.
- `/.well-known/agent-skills/index.json` and read-only skill documents.

Deferred scanner gaps:

- OAuth/OIDC discovery.
- OAuth protected resource metadata.
- MCP server card.
- A2A agent card.
- WebMCP tools.
- Agent/payment protocols: x402, MPP, UCP, ACP, AP2.

## Phase 1 - Read-Only Public Commerce APIs

Priority: P1
Risk: Low to medium
Dependency: API owner review

Build real public API endpoints that agents can safely use without account access.

Candidate endpoints:

- `GET /api/v2/products`
- `GET /api/v2/products/{slug}`
- `GET /api/v2/categories`
- `GET /api/v2/categories/{slug}/products`
- `GET /api/v2/brands`
- `GET /api/v2/brands/{slug}/products`
- `GET /api/v2/sellers`
- `GET /api/v2/sellers/{slug}`
- `GET /api/v2/policies`
- `GET /api/v2/policies/{slug}`

Required controls:

- Rate limiting.
- Pagination.
- Stable JSON response schemas.
- No customer, cart, checkout, payment, admin, seller-private, or inventory-sensitive data.
- OpenAPI documentation updated only for endpoints that exist and are intended to be public.

Acceptance checks:

- All endpoints return deterministic JSON.
- OpenAPI validates.
- Response schemas are tested.
- No private route or field is advertised.

## Phase 2 - Authentication Strategy

Priority: P1 before protected APIs
Risk: High
Dependency: product and security decision

Decide whether Mayush will be an OAuth/OIDC platform or remain API-key/Sanctum-only.

Options:

- External identity provider: best if Mayush wants standards-compliant OAuth/OIDC without owning the full identity surface.
- Laravel Passport: possible if Mayush wants to operate an OAuth authorization server.
- Sanctum/API tokens only: simpler, but not enough for true OIDC discovery.

Decision questions:

- Will third-party agents authenticate as users, sellers, partners, or system clients?
- Are scopes needed, such as `products:read`, `policies:read`, `orders:track`, `cart:write`, `checkout:create`?
- Will users explicitly consent to agent access?
- What revocation, audit log, and rate-limit model is required?

Do not publish:

- `/.well-known/openid-configuration`
- `/.well-known/oauth-authorization-server`
- `/.well-known/oauth-protected-resource`

until the authorization server, issuer, token endpoint, JWKS, supported grants, resources, and scopes are real.

## Phase 3 - OAuth Protected Resource Metadata

Priority: P2 after auth decision
Risk: Medium to high

Publish `/.well-known/oauth-protected-resource` only when protected agent APIs exist.

Minimum metadata:

- `resource`
- `authorization_servers`
- `scopes_supported`
- `bearer_methods_supported`
- optional `resource_documentation`

Expected scopes:

- `products:read`
- `policies:read`
- `orders:track`
- `cart:write`
- `checkout:create`

Acceptance checks:

- Metadata points to a real authorization server.
- Protected API `401` responses include a useful `WWW-Authenticate` header.
- Tokens are verified server-side.
- Scope enforcement is tested.

## Phase 4 - MCP Server

Priority: P3
Risk: High if tools mutate state

Build an MCP server only after Phase 1 APIs are stable.

Initial read-only tools:

- `search_products`
- `get_product`
- `list_categories`
- `get_seller_shop`
- `lookup_policy`

Excluded from first MCP release:

- add to cart
- checkout
- payment
- account updates
- seller/admin actions
- order cancellation or refund

Discovery:

- Publish an MCP server card only when the server endpoint is real.
- Keep capabilities narrow and truthful.
- Require auth for any non-public operation.

Acceptance checks:

- MCP tools map to documented APIs.
- Tool input schemas are strict.
- Rate limiting and logging are enabled.
- Prompt-injection and data-exfiltration risks are reviewed.

## Phase 5 - A2A Agent Card

Priority: P3
Risk: Medium to high

Publish `/.well-known/agent-card.json` only when Mayush operates an actual A2A-compatible agent service.

Possible agent role:

- Mayush shopping assistant for product discovery and policy answers.

Initial skills:

- product discovery
- category guidance
- seller lookup
- policy lookup

Do not advertise:

- checkout execution
- payment handling
- private order management

until those flows are explicitly designed.

Acceptance checks:

- Agent card points to a real service URL.
- Skills match implemented behavior.
- Authentication requirements are stated.
- Logs and abuse controls exist.

## Phase 6 - WebMCP Browser Tools

Priority: P4
Risk: Medium

WebMCP should be treated as experimental browser integration.

Safe first tools:

- search visible catalog content
- summarize current product page
- open product/category pages
- explain return/shipping policy

Controls:

- Feature flag.
- Read-only first.
- No automatic checkout or payment.
- Browser support detection.
- No sensitive data in tool outputs.

Acceptance checks:

- Browser behavior is unchanged for normal users.
- Tools are detected only on intended public pages.
- Tool output is bounded and sanitized.

## Phase 7 - Agentic Commerce Protocols

Priority: P5 strategic project
Risk: High

These are commerce architecture projects, not SEO tasks.

### x402

Use only if Mayush wants paid API/content access or machine-payable services. It requires HTTP `402 Payment Required` flows, payment verification, settlement, and a wallet/facilitator decision.

### MPP

Use only if OpenAPI operations should advertise machine payment metadata. Requires payment intent design and operation-level pricing.

### UCP

Use only if Mayush wants full agentic commerce interoperability for product discovery, checkout, order, fulfillment, policy, and returns. Requires `/.well-known/ucp`, signed requests, capability negotiation, and schema maintenance.

### ACP

Use only if Mayush chooses an ACP-compatible checkout/order API surface. Requires `/.well-known/acp.json`, checkout orchestration, inventory controls, payment controls, and fraud handling.

### AP2

Use only if Mayush supports agent payment authorization flows requiring user intent and cart mandates.

Acceptance checks:

- Legal, fraud, refund, and chargeback handling are reviewed.
- Inventory and pricing are authoritative in real time.
- Payment flows are tested end to end.
- User consent and audit trails are present.

## Recommended Roadmap

1. Build Phase 1 read-only public commerce APIs.
2. Expand `/openapi.json` to document those APIs.
3. Add API schema and privacy tests.
4. Decide authentication strategy.
5. Implement OAuth protected resource metadata only after auth is real.
6. Build read-only MCP tools on top of stable APIs.
7. Publish MCP and A2A discovery only after real services exist.
8. Evaluate UCP/ACP/x402 only as a dedicated agentic commerce product initiative.

## Success Metrics

- Public API uptime and latency.
- API 4xx/5xx rates.
- Rate-limit events.
- Agent referral traffic.
- Product discovery impressions from AI crawlers.
- Search Console and Bing Webmaster sitemap/index coverage.
- No private data exposure from agent endpoints.
- No unsupported capabilities advertised in discovery documents.

## References

- RFC 9728 OAuth 2.0 Protected Resource Metadata: https://www.rfc-editor.org/rfc/rfc9728
- RFC 8414 OAuth 2.0 Authorization Server Metadata: https://www.rfc-editor.org/rfc/rfc8414
- OpenID Connect Discovery 1.0: https://openid.net/specs/openid-connect-discovery-1_0.html
- Model Context Protocol authorization: https://modelcontextprotocol.io/specification/draft/basic/authorization
- A2A Agent Card discovery: https://a2a-protocol.org/dev/specification/
- x402 documentation: https://docs.x402.org/
- Universal Commerce Protocol: https://ucp.dev/specification/overview/
- Agentic Commerce Protocol: https://www.agenticcommerce.dev/
