# Design Document: Incomplete Features Completion

## Overview

This design document specifies the implementation approach for completing four critical features identified in the MayushDesign audit: CMI Payment Production Configuration, Seller Onboarding Documents, SEO Completion, and Test Coverage Expansion.

The Mayush platform is a Laravel 10 multi-vendor marketplace with existing implementations that require enhancement. This design builds upon the current architecture while addressing identified gaps.

## Architecture

### System Context

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           MayushDesign Platform                              │
├─────────────────┬─────────────────┬─────────────────┬───────────────────────┤
│   Payment Layer │  Seller Layer   │    SEO Layer    │     Testing Layer     │
├─────────────────┼─────────────────┼─────────────────┼───────────────────────┤
│  CmiController  │ OnboardingCtrl  │   SeoService    │   Playwright Tests    │
│  CmiCallback    │ SellerDocument  │  SchemaHelpers  │   PHPUnit Tests       │
│  IP Whitelist   │ Admin Review    │ Canonical URLs  │   Integration Tests   │
└─────────────────┴─────────────────┴─────────────────┴───────────────────────┘
```

### Existing Architecture Integration

The design integrates with the following existing components:

- **Payment**: `CmiController.php` handles CMI gateway communication with hash validation and idempotency
- **Seller**: `OnboardingController.php` manages document uploads; `SellerController.php` handles admin review
- **SEO**: `SeoService.php` provides schema generation for products, articles, and organization
- **Testing**: PHPUnit test suite in `tests/Feature`, `tests/Unit`, `tests/Integration`

## Components and Interfaces

### 1. CMI Payment Production Configuration

#### 1.1 IP Whitelist Middleware

**Purpose**: Validate incoming CMI callback requests against configured IP whitelist.

**Location**: `app/Http/Middleware/CmiIpWhitelist.php`

```php
interface CmiIpWhitelistInterface
{
    /**
     * Validate if request IP is in the allowed list.
     * 
     * @param Request $request
     * @return bool
     */
    public function isAllowed(Request $request): bool;
}
```

**Integration Points**:
- Register middleware in `app/Http/Kernel.php`
- Apply to CMI callback route in `routes/web.php`
- Configuration via `CMI_ALLOWED_IPS` environment variable

**Decision Logic**:
```
IF CMI_ALLOWED_IPS is configured AND request IP not in list
    THEN reject with 403 and log security event
ELSE
    proceed to callback processing
```

#### 1.2 Production Configuration Validator

**Purpose**: Validate CMI credentials before payment initiation.

**Location**: `app/Services/Payment/CmiConfigValidator.php`

```php
interface CmiConfigValidatorInterface
{
    /**
     * Validate all required CMI configuration.
     * 
     * @return ValidationResult
     */
    public function validate(): ValidationResult;
    
    /**
     * Check if test mode is enabled.
     */
    public function isTestMode(): bool;
    
    /**
     * Get the appropriate gateway URL based on mode.
     */
    public function getGatewayUrl(): string;
}
```

**Validation Rules**:
1. `CMI_MERCHANT_ID` must be non-empty and not contain placeholder values
2. `CMI_SECRET_KEY` must be non-empty and at least 16 characters
3. `CMI_GATEWAY_URL` must use HTTPS
4. Production mode requires production gateway URL

#### 1.3 Enhanced Error Handling

**Purpose**: Provide secure, user-friendly error responses without exposing configuration.

**Location**: Update `CmiController.php`

**Error Response Strategy**:
- Critical errors: Log with full details, return generic user message
- Validation errors: Log specific issue, return actionable user message
- Security events: Log with IP and timestamp, return 403

### 2. Seller Onboarding Documents

#### 2.1 Document Upload Service

**Purpose**: Handle secure document upload, validation, and storage.

**Location**: `app/Services/Seller/DocumentUploadService.php`

```php
interface DocumentUploadServiceInterface
{
    /**
     * Validate and store a seller document.
     * 
     * @param UploadedFile $file
     * @param Shop $shop
     * @param string $documentType
     * @return SellerDocument
     * @throws DocumentValidationException
     */
    public function upload(UploadedFile $file, Shop $shop, string $documentType): SellerDocument;
    
    /**
     * Validate file for security threats.
     * 
     * @param UploadedFile $file
     * @return bool
     */
    public function isSecure(UploadedFile $file): bool;
}
```

**File Validation Rules**:
- Allowed MIME types: `application/pdf`, `image/jpeg`, `image/png`, `image/webp`
- Maximum file size: 10MB (10,485,760 bytes)
- No double extensions (e.g., `.pdf.exe`)
- No embedded scripts in images
- Filename sanitization

#### 2.2 Document Security Scanner

**Purpose**: Detect malicious content in uploaded files.

**Location**: `app/Services/Security/DocumentSecurityScanner.php`

```php
interface DocumentSecurityScannerInterface
{
    /**
     * Scan file for malicious content.
     * 
     * @param string $filePath
     * @return SecurityScanResult
     */
    public function scan(string $filePath): SecurityScanResult;
    
    /**
     * Check for embedded scripts in images.
     */
    public function hasEmbeddedScripts(string $filePath): bool;
    
    /**
     * Validate file extension matches content.
     */
    public function extensionMatchesContent(UploadedFile $file): bool;
}
```

**Detection Patterns**:
- PHP tags in image files: `<?php`, `<?=`
- JavaScript in metadata
- Suspicious file headers
- Double extension attempts

#### 2.3 Admin Document Review Interface

**Purpose**: Provide admin interface for reviewing seller documents.

**Location**: `resources/views/backend/sellers/documents_modal.blade.php` (exists, needs enhancement)

**View Data Requirements**:
- Shop information with approval status
- List of uploaded documents with metadata
- Download links for each document
- Approve/Reject form with reason field
- Resubmission count and history

#### 2.4 Seller Document Views

**Purpose**: Frontend views for document upload and status display.

**Location**: `resources/views/seller/onboarding/index.blade.php` (needs creation/enhancement)

**View Components**:
- Document upload form for each required type
- Status indicators for existing documents
- Rejection reason display (if rejected)
- Resubmission form

### 3. SEO Completion

#### 3.1 Product Schema Enhancer

**Purpose**: Ensure all product pages have complete JSON-LD schema.

**Location**: Enhance `SeoService::productSchema()` method

**Schema Fields** (conditional):
```json
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "string",
  "image": ["url1", "url2"],
  "description": "string",
  "sku": "string",
  "mpn": "string",
  "brand": {"@type": "Brand", "name": "string"},
  "offers": {
    "@type": "Offer",
    "url": "string",
    "priceCurrency": "MAD",
    "price": "number",
    "availability": "https://schema.org/InStock|OutOfStock"
  },
  "aggregateRating": {  // Only if reviews exist
    "@type": "AggregateRating",
    "ratingValue": "number",
    "reviewCount": "integer"
  }
}
```

#### 3.2 Blog Article Schema Enhancer

**Purpose**: Complete Article schema for blog posts.

**Location**: Enhance `SeoService::articleSchema()` method

**Schema Fields**:
```json
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "string",
  "image": "url",
  "datePublished": "ISO8601",
  "dateModified": "ISO8601",
  "author": {"@type": "Organization", "name": "string"},
  "publisher": {
    "@type": "Organization",
    "name": "string",
    "logo": {"@type": "ImageObject", "url": "string"}
  }
}
```

#### 3.3 Canonical URL Service

**Purpose**: Generate correct canonical URLs for filtered pages.

**Location**: `app/Services/Seo/CanonicalUrlService.php`

```php
interface CanonicalUrlServiceInterface
{
    /**
     * Get canonical URL for current request.
     * Removes filter parameters while preserving category path.
     */
    public function getCanonical(Request $request): string;
    
    /**
     * Check if current URL matches canonical.
     */
    public function isCanonical(Request $request): bool;
}
```

**Canonical Rules**:
- Category pages: Remove all filter query parameters
- Search pages: Base search URL without filters
- Product pages: Product detail URL (no change)
- Homepage: Root URL

#### 3.4 Category Landing Page SEO

**Purpose**: Enhanced SEO fields for category pages.

**Database Changes**: Add columns to `categories` table via migration
- `meta_title` (string, nullable)
- `meta_description` (text, nullable)
- `meta_keywords` (string, nullable)
- `seo_description` (text, nullable) - For HTML description section

**View Integration**:
- Display `seo_description` above product grid
- Include custom meta tags in `<head>`
- Add breadcrumb schema

### 4. Test Coverage Expansion

#### 4.1 CMI Payment Integration Tests

**Location**: `tests/Integration/PaymentGateways/CmiCallbackTest.php`

**Test Scenarios**:
1. Success callback with valid hash and amount
2. Failure callback with ProcReturnCode != "00"
3. Hash validation rejection
4. Amount mismatch rejection
5. IP whitelist enforcement
6. Idempotency for duplicate transactions

#### 4.2 Seller Document Tests

**Location**: `tests/Feature/Seller/DocumentUploadTest.php`

**Test Scenarios**:
1. Valid document upload
2. Invalid file type rejection
3. File size limit enforcement
4. Double extension rejection
5. Malicious content detection

#### 4.3 Playwright E2E Tests

**Location**: `tests/BrowserQa/` (expand existing)

**Test Suites**:
1. Customer journey: Homepage → Search → Product → Cart → Checkout
2. Seller journey: Login → Document Upload → Dashboard
3. Admin journey: Login → Seller Review → Approve/Reject
4. Responsive tests: Mobile (375px), Tablet (768px), Desktop (1280px)

## Data Models

### Shop Model (Enhanced)

The `shops` table already has the required columns from migration `2026_05_06_100001`:

| Column | Type | Purpose |
|--------|------|---------|
| `approval_status` | enum | pending, under_review, approved, rejected |
| `rejection_reason` | text | Admin-provided rejection reason |
| `documents_submitted_at` | timestamp | When documents were uploaded |
| `reviewed_at` | timestamp | When admin reviewed |
| `reviewed_by` | integer | Admin user ID who reviewed |
| `resubmission_count` | tinyint | Number of resubmission attempts |

### SellerDocument Model

Existing model with complete structure:

| Column | Type | Purpose |
|--------|------|---------|
| `id` | bigint | Primary key |
| `shop_id` | integer | Foreign key to shops |
| `document_type` | enum | contract, government_id, business_registration, certification |
| `file_path` | string | Storage path |
| `original_name` | string | Original filename |
| `mime_type` | string | File MIME type |
| `file_size` | bigint | Size in bytes |
| `uploaded_at` | timestamp | Upload timestamp |

### Category Model (Enhanced)

New fields for SEO:

| Column | Type | Purpose |
|--------|------|---------|
| `meta_title` | string | Custom SEO title |
| `meta_description` | text | Custom meta description |
| `meta_keywords` | string | Meta keywords |
| `seo_description` | text | HTML description for landing pages |

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: IP Whitelist Enforcement

*For any* CMI callback request, if the `CMI_ALLOWED_IPS` configuration is non-empty and the request IP is not in the whitelist, the request SHALL be rejected with a 403 Forbidden response and the security event SHALL be logged.

**Validates: Requirements 1.3, 1.6**

### Property 2: Hash Validation Security

*For any* CMI callback request, if the hash does not match the calculated hash using the secret key, the request SHALL be rejected with a FAILURE response.

**Validates: Requirements 1.3, 5.2**

### Property 3: Idempotent Callback Processing

*For any* CMI transaction ID, if the transaction has already been successfully processed, subsequent callbacks with the same transaction ID SHALL return the same response without modifying order state.

**Validates: Requirements 4.1.6**

### Property 4: Document Security Validation

*For any* document upload, if the file has a double extension, contains embedded scripts, or has a MIME type that does not match its content, the upload SHALL be rejected with a specific error message indicating the security violation.

**Validates: Requirements 2.2, 5.1, 5.4**

### Property 5: Document Type Validation

*For any* document upload, if the file MIME type is not one of `application/pdf`, `image/jpeg`, `image/png`, or `image/webp`, or if the file size exceeds 10MB, the upload SHALL be rejected.

**Validates: Requirements 2.2**

### Property 6: Mandatory Document Completeness

*For any* shop with `approval_status` of "under_review", all three mandatory documents (contract, government_id, business_registration) SHALL exist in the seller_documents table.

**Validates: Requirements 2.3**

### Property 7: Resubmission Limit Enforcement

*For any* shop with `resubmission_count` >= 10, the system SHALL prevent further document uploads and display a contact support message.

**Validates: Requirements 2.1.5**

### Property 8: Product Schema Completeness

*For any* product page rendered, the JSON-LD schema SHALL include name, image, description, offers, and brand fields. The `aggregateRating` field SHALL be present if and only if the product has at least one approved review.

**Validates: Requirements 3.1, 3.6**

### Property 9: Article Schema Completeness

*For any* blog article page rendered, the JSON-LD schema SHALL include headline, image, datePublished, dateModified, author, and publisher fields with valid values.

**Validates: Requirements 3.2**

### Property 10: Canonical URL Cleanliness

*For any* category or search page URL with query parameters, the canonical URL SHALL point to the base URL without filter parameters.

**Validates: Requirements 3.3, 3.4**

### Property 11: Breadcrumb Schema Hierarchy

*For any* category page, the breadcrumb schema SHALL reflect the correct category hierarchy from Home to the current category.

**Validates: Requirements 3.1.3**

### Property 12: Admin Review Audit Trail

*For any* seller approval or rejection action, an AuditLog entry SHALL be created with the admin user ID, target user ID, action type, and timestamp.

**Validates: Requirements 5.3**

## Error Handling

### CMI Payment Errors

| Error Condition | HTTP Status | Response | Log Level |
|-----------------|-------------|----------|-----------|
| Hash mismatch | 200 | `FAILURE` | Error |
| Missing credentials | 302 | Redirect to home with error flash | Critical |
| IP not whitelisted | 403 | Forbidden | Warning |
| Amount mismatch | 200 | `FAILURE` | Error |
| Order not found | 200 | `FAILURE` | Error |

### Document Upload Errors

| Error Condition | Response | User Message |
|-----------------|----------|--------------|
| Invalid file type | 422 | "Only PDF, JPEG, PNG, and WEBP files are allowed" |
| File too large | 422 | "File size cannot exceed 10MB" |
| Double extension | 422 | "Invalid file extension" |
| Malicious content | 422 | "File contains prohibited content" |
| Storage failure | 500 | "Failed to upload document. Please try again" |

### Admin Review Errors

| Error Condition | Response | User Message |
|-----------------|----------|--------------|
| Missing rejection reason | 422 | "Rejection reason is required" |
| Shop not found | 404 | "Seller application not found" |
| Already processed | 400 | "This application has already been processed" |

## Testing Strategy

### Dual Testing Approach

- **Unit tests**: Specific examples, edge cases, error conditions
- **Integration tests**: Component interactions, database state
- **Property tests**: Universal properties across all inputs (where applicable)
- **E2E tests**: Full user journeys with Playwright

### Unit Testing Focus

1. `CmiConfigValidator` - Configuration validation logic
2. `DocumentUploadService` - File validation rules
3. `DocumentSecurityScanner` - Malicious content detection
4. `CanonicalUrlService` - URL generation logic
5. `SeoService` enhancements - Schema generation

### Integration Testing Focus

1. CMI callback processing with database state changes
2. Document upload with storage persistence
3. Admin review workflow with notifications
4. SEO schema rendering in views

### Property-Based Testing

Property-based testing is appropriate for:
- Hash validation (various input combinations)
- Document validation (file type/size combinations)
- Schema generation (product/article variations)

Property-based testing is NOT appropriate for:
- E2E browser tests (external browser behavior)
- IP whitelist checks (configuration verification)
- Notification sending (external service)

### Test Configuration

**PHPUnit Configuration**:
- Minimum 100 iterations for property tests
- Tag format: `@feature incomplete-features-completion`

**Playwright Configuration**:
- Viewports: 375px (mobile), 768px (tablet), 1280px (desktop)
- Browsers: Chromium, Firefox, WebKit
- Base URL: `http://localhost` (local testing)

### Test File Organization

```
tests/
├── Feature/
│   ├── Payment/
│   │   ├── CmiCallbackSecurityTest.php
│   │   └── CmiIpWhitelistTest.php
│   ├── Seller/
│   │   ├── DocumentUploadTest.php
│   │   └── SellerResubmissionTest.php
│   └── Seo/
│       └── CanonicalUrlTest.php
├── Integration/
│   └── PaymentGateways/
│       └── CmiCallbackIntegrationTest.php
├── Unit/
│   └── Services/
│       ├── DocumentSecurityScannerTest.php
│       └── CanonicalUrlServiceTest.php
└── BrowserQa/
    ├── CustomerJourneyTest.php
    ├── SellerJourneyTest.php
    ├── AdminJourneyTest.php
    └── ResponsiveLayoutTest.php
```

## Implementation Notes

### Existing Implementations to Preserve

1. **CMI Controller**: Already has hash validation, idempotency, and amount validation. Add IP whitelist check only.

2. **OnboardingController**: Already handles document upload. Add security scanner integration.

3. **SellerController**: Already has `showDocuments`, `approveApplication`, `rejectApplication` methods. Enhance with notification handling.

4. **SeoService**: Already has complete schema generation. Ensure views properly render the schemas.

### Database Migration Strategy

1. Run existing migrations first (they check for column existence)
2. Add new SEO columns to categories table
3. No data migration needed (new nullable columns)

### Backward Compatibility

- Keep `registration_approval` column in sync with `approval_status` for existing code
- Maintain existing notification types for seller status changes
- Preserve existing view partials while adding enhancements

### Security Considerations

1. Store documents outside public web root (`storage/app/private/`)
2. Use signed URLs for document downloads
3. Log all admin review actions for audit
4. Use constant-time comparison for hash validation (already implemented)
5. Sanitize filenames before storage
