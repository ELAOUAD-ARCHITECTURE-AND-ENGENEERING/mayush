# Requirements Document

## Introduction

This specification addresses the completion of four critical features identified in the MayushDesign audit that require implementation before production launch. The Mayush platform is a Laravel 10 multi-vendor marketplace for furniture and decor in Morocco, featuring CMI payment integration, seller verification, and blog-commerce capabilities.

The incomplete features span payment production configuration, seller onboarding documents, SEO schema completion, and test coverage expansion. Each feature has partial implementation that must be completed to ensure a production-ready platform.

## Glossary

- **CMI (Centre Monétique Interbancaire)**: Moroccan bank payment gateway supporting 3D Secure transactions in Moroccan Dirham (MAD)
- **Seller Document**: Verification document (contract, government ID, business registration) uploaded by sellers during onboarding
- **Schema Markup**: Structured data (JSON-LD) for SEO enabling rich search results
- **Canonical URL**: The preferred URL for duplicate content, preventing SEO penalties
- **E2E (End-to-End) Test**: Browser-based test simulating real user journeys
- **Playwright**: Node.js library for browser automation and E2E testing
- **Property-Based Test**: Testing approach using randomly generated inputs to verify universal properties
- **Shop Approval Workflow**: Admin-driven process to verify seller documents before shop activation
- **PreAuth Transaction**: Payment authorization that captures funds after verification
- **Hash Signature**: SHA-512 based security verification for CMI callbacks

---

## Requirement 1: CMI Payment Production Configuration

**User Story:** As a platform operator, I want to verify and configure production CMI credentials, so that customers can complete real payment transactions securely.

### Acceptance Criteria

1. WHEN a production environment is detected, THE System SHALL use the production CMI gateway URL (https://attijari.cmi.co.ma/fim/est3Dgate) instead of the test URL
2. WHEN CMI credentials are missing or invalid, THE System SHALL log a critical error and display a user-friendly message without exposing configuration details
3. WHEN a CMI callback is received, THE System SHALL validate the request IP against the configured `CMI_ALLOWED_IPS` whitelist if configured
4. WHEN a payment is initiated, THE System SHALL verify that `CMI_MERCHANT_ID` and `CMI_SECRET_KEY` are non-empty production values
5. WHILE test mode is enabled via `CMI_TEST_MODE=true`, THE System SHALL use the test gateway URL and log all transactions as test transactions
6. IF the callback IP validation fails, THEN THE System SHALL reject the callback with a failure response and log the security event
7. WHERE the `CMI_CALLBACK_URL` is configured, THE System SHALL ensure it is accessible from CMI servers and uses HTTPS

---

## Requirement 2: Seller Onboarding Documents

**User Story:** As a new seller, I want to upload my verification documents during onboarding, so that I can be verified and start selling on the marketplace.

### Acceptance Criteria

1. WHEN a seller accesses the onboarding documents page, THE System SHALL display a form showing all required document types with upload fields
2. WHEN a seller uploads a document, THE System SHALL validate the file type (PDF, JPEG, PNG, WEBP), file size (max 10MB), and store it securely
3. WHEN all mandatory documents (contract, government_id, business_registration) are uploaded, THE System SHALL update the shop's `approval_status` to "under_review"
4. WHEN a document fails validation, THE System SHALL display a specific error message indicating the validation failure reason
5. WHEN a seller views previously uploaded documents, THE System SHALL display each document's type, filename, upload date, and status

### Requirement 2.1: Admin Document Review Workflow

**User Story:** As an admin, I want to review seller verification documents, so that I can approve or reject seller applications with appropriate feedback.

#### Acceptance Criteria

1. WHEN an admin views the seller management page, THE System SHALL display shops with `approval_status` of "under_review" in a dedicated queue
2. WHEN an admin opens a seller review page, THE System SHALL display all uploaded documents with download links and document metadata
3. WHEN an admin approves a seller, THE System SHALL update the shop's `approval_status` to "approved", set `reviewed_by` to the admin's user ID, and send a notification to the seller
4. WHEN an admin rejects a seller, THE System SHALL update the shop's `approval_status` to "rejected", store the rejection reason, increment `resubmission_count`, and send a notification to the seller
5. WHILE a shop has been rejected 10 or more times, THE System SHALL prevent further resubmissions and display a contact support message

---

## Requirement 3: SEO Completion

**User Story:** As a marketing manager, I want complete SEO implementation with validated schemas and canonical rules, so that the platform ranks well in search engines.

### Acceptance Criteria

1. WHEN a product page is rendered, THE System SHALL include valid JSON-LD Product schema with name, image, description, offers, and aggregateRating if reviews exist
2. WHEN a blog article page is rendered, THE System SHALL include valid JSON-LD Article schema with headline, image, datePublished, author, and publisher
3. WHEN a category page is rendered, THE System SHALL include a canonical URL tag pointing to the unfiltered category URL
4. WHEN search filters are applied, THE System SHALL include `rel="canonical"` pointing to the base search URL without filter parameters
5. WHEN the homepage is rendered, THE System SHALL include valid Organization and WebSite schemas with SearchAction for site search
6. IF a product has no reviews, THE System SHALL omit the `aggregateRating` field from the Product schema

### Requirement 3.1: Category Landing Pages SEO

**User Story:** As an SEO specialist, I want SEO-optimized category landing pages, so that category pages rank for relevant search terms.

#### Acceptance Criteria

1. WHEN a category landing page is created, THE System SHALL support custom meta_title, meta_description, and meta_keywords fields
2. WHEN a category has a custom SEO description, THE System SHALL render it in an HTML description section above the product grid
3. WHEN a category page is accessed, THE System SHALL include breadcrumb schema with proper hierarchy (Home > Category)
4. WHERE a category has no products, THE System SHALL display a descriptive message and suggest related categories

---

## Requirement 4: Test Coverage Expansion

**User Story:** As a developer, I want comprehensive E2E and integration tests, so that I can confidently deploy changes without breaking critical functionality.

### Acceptance Criteria

1. WHEN the E2E test suite runs, THE System SHALL execute browser tests covering customer registration, product browsing, cart, and checkout flows
2. WHEN a payment integration test runs, THE System SHALL verify CMI payment flow with mock callbacks simulating success and failure scenarios
3. WHEN a seller workflow test runs, THE System SHALL verify document upload, admin review, and approval/rejection flows
4. WHEN responsive tests run, THE System SHALL verify critical pages render correctly on mobile (375px), tablet (768px), and desktop (1280px) viewports

### Requirement 4.1: Payment Integration Tests

**User Story:** As a QA engineer, I want payment integration tests for CMI, so that payment flows are verified before production deployment.

#### Acceptance Criteria

1. WHEN a CMI callback test runs with a valid success response, THE System SHALL verify the order status is updated to "paid"
2. WHEN a CMI callback test runs with ProcReturnCode "00", THE System SHALL verify ACTION=POSTAUTH is returned
3. WHEN a CMI callback test runs with a failure ProcReturnCode, THE System SHALL verify the order status remains unchanged
4. WHEN a hash validation test runs with an invalid hash, THE System SHALL verify the callback is rejected
5. WHEN an amount validation test runs with a mismatched amount, THE System SHALL verify the callback is rejected
6. WHEN an idempotency test runs with a duplicate transaction, THE System SHALL verify the same response is returned without duplicate processing

### Requirement 4.2: E2E Browser Tests with Playwright

**User Story:** As a developer, I want Playwright E2E tests for critical user journeys, so that UI behavior is verified across browsers.

#### Acceptance Criteria

1. WHEN the customer journey E2E test runs, THE System SHALL verify: homepage load, product search, product detail view, add to cart, and checkout initiation
2. WHEN the seller journey E2E test runs, THE System SHALL verify: seller login, document upload, and dashboard access
3. WHEN the admin journey E2E test runs, THE System SHALL verify: admin login, seller review queue access, and approval workflow
4. WHEN a mobile viewport E2E test runs, THE System SHALL verify navigation menu, product cards, and checkout form are usable

---

## Requirement 5: Security and Data Integrity

**User Story:** As a security-conscious platform operator, I want verified security measures for the incomplete features, so that customer and seller data is protected.

### Acceptance Criteria

1. WHEN a document is uploaded, THE System SHALL scan the file for malicious content and reject files with embedded scripts
2. WHEN a CMI callback is processed, THE System SHALL use constant-time comparison for hash validation to prevent timing attacks
3. WHEN admin review data is stored, THE System SHALL log all review actions with timestamps and admin IDs for audit purposes
4. IF a seller attempts to upload a file with a double extension (e.g., `.pdf.exe`), THE System SHALL reject the file
