# Checklist for Classified Products Promotion Module Redesign

## Backend (API & DB)
- [ ] Create `promotions` table migration (id, product_id, user_id, tier, start_date, end_date, status, notes, timestamps).
- [ ] Define `Promotion` model with `CustomerProduct` relationship and status constants (`awaiting_admin_review`, `approved`, `rejected`, `expired`).
- [ ] Implement `POST /api/v2/promotions` (Create Promotion):
    - [ ] Validate `user_id` credits (check `remaining_uploads` or new `promotion_credits`).
    - [ ] Validate `product_id` (must belong to user, not already promoted).
    - [ ] Validate `start_date` and `end_date` (no overlap for same product).
    - [ ] Validate `tier` (enum).
    - [ ] Check `max_promotions` (10 per user).
    - [ ] Create `Promotion` record with status `awaiting_admin_review`.
    - [ ] Return success message or error (insufficient credits).
- [ ] Implement `PATCH /api/v2/promotions/{id}` (Update Promotion Status):
    - [ ] Validate admin permissions.
    - [ ] Update `status` field.
    - [ ] If `approved`: Ensure product is `published` (or update `published` status if needed).
    - [ ] Return updated promotion object.
- [ ] Implement `GET /api/v2/promotions` (List Promotions):
    - [ ] Support query params: `status`, `page`, `sort`.
    - [ ] Return paginated list of promotions with product details.
- [ ] Add API Resource for `Promotion` and `CustomerProduct` (JSON format).
- [ ] Write PHPUnit tests for all API endpoints and validation logic.

## Frontend (Seller Dashboard)
- [ ] Update `CustomerProduct` creation form (`create.blade.php`):
    - [ ] Add "Promote this product" toggle.
    - [ ] Show promotion fields (Tier, Dates, Notes) when toggled.
    - [ ] Check credits via API before submission.
    - [ ] Show warning popup if insufficient credits.
- [ ] Create "Promotions" tab in Seller Dashboard sidebar (`seller.blade.php`):
    - [ ] Link to new `promotions.index` route.
- [ ] Create `promotions.index` view:
    - [ ] Display table of promotions (Active, Pending, Expired).
    - [ ] Add "Promote" button for existing products (opens modal).
- [ ] Implement promotion modal:
    - [ ] Select product (if not already selected).
    - [ ] Enter Tier, Dates, Notes.
    - [ ] Submit via AJAX to `POST /api/v2/promotions`.
    - [ ] Handle success/error responses.

## Frontend (Admin Dashboard)
- [ ] Create "Pending Promotions" page (`admin.promotions.index`):
    - [ ] Fetch pending promotions via API/Controller.
    - [ ] Display table with product snapshot, tier, dates, notes.
    - [ ] Add "Approve" and "Reject" buttons.
- [ ] Implement approval logic:
    - [ ] Confirm action via modal.
    - [ ] Send PATCH request to API.
    - [ ] Update UI on success (remove from list or change status).

## Frontend (Public Slider)
- [ ] Create new Blade component `x-classified-product-slider`.
- [ ] Implement HTML structure for infinite slider (container, wrapper, slides).
- [ ] Write CSS for responsive layout:
    - [ ] Mobile: 2-row masonry grid (using CSS Grid/Flexbox).
    - [ ] Desktop: Single-row horizontal strip (overflow-x: auto, scroll-snap-type: x mandatory).
    - [ ] Lazy loading styles (placeholder/skeleton).
- [ ] Write JavaScript (Vanilla/jQuery) for slider behavior:
    - [ ] Infinite scrolling (append items on scroll end).
    - [ ] Touch swipe, mouse drag, keyboard navigation (passive listeners, rAF throttling).
    - [ ] Show/hide prev/next buttons and dots based on overflow.
- [ ] Replace existing static grid in `index.blade.php` with `x-classified-product-slider`.

## Testing & Docs
- [ ] Set up Jest for JS testing (if not already present).
- [ ] Write Jest tests for slider component logic (swipe, drag, snap).
- [ ] Set up Cypress for E2E testing (if not already present).
- [ ] Write Cypress test:
    - [ ] Seller logs in -> Creates product with promotion -> Submits.
    - [ ] Admin logs in -> Approves promotion.
    - [ ] Verify promotion appears on homepage slider.
    - [ ] Verify slider interaction (swipe/click).
- [ ] Generate API documentation (OpenAPI 3.0/Swagger).
- [ ] Create migration script to remove legacy 6-item limit column (if exists) and verify data integrity.
