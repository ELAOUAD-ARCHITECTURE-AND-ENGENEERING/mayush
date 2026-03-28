# Tasks for Classified Products Promotion Module Redesign

## Phase 1: Backend Infrastructure
- [ ] Create `promotions` table migration with fields: `id`, `product_id`, `user_id`, `tier`, `start_date`, `end_date`, `status`, `notes`.
- [ ] Create `Promotion` model with `CustomerProduct` relationship and status constants.
- [ ] Implement `PromotionController` (REST API) with endpoints:
    - [ ] `POST /api/v2/promotions`: Create promotion (validates user credits, max limit, date overlap).
    - [ ] `PATCH /api/v2/promotions/{id}`: Update status (approve/reject/expire).
    - [ ] `GET /api/v2/promotions`: List promotions (filter by status, paginate).
- [ ] Implement credit validation logic in `PromotionController` (check `remaining_uploads` or new `promotion_credits` field).
- [ ] Create API resource for `Promotion` and `CustomerProduct` (for consistent JSON response).
- [ ] Write PHPUnit tests for API endpoints and validation logic.

## Phase 2: Frontend (Seller Dashboard)
- [ ] Update `CustomerProduct` creation form to include "Promote this product" toggle.
- [ ] Implement promotion configuration modal/form (Tier, Dates, Notes) triggered by toggle or "Promote" button.
- [ ] Add "Promotions" tab to Seller Dashboard sidebar.
- [ ] Display list of promotions (active, pending, expired) in the new tab.
- [ ] Integrate credit check API call before submitting promotion request.
- [ ] Implement warning popup for insufficient credits.

## Phase 3: Frontend (Admin Dashboard)
- [ ] Create "Pending Promotions" page in Admin Dashboard.
- [ ] Display list of promotions with status `awaiting_admin_review`.
- [ ] Implement detail view for each promotion (product snapshot, dates, notes).
- [ ] Add "Approve" and "Reject" buttons with confirmation dialogs.
- [ ] Connect approval actions to `PATCH /api/v2/promotions/{id}` endpoint.

## Phase 4: Frontend (Public Slider)
- [ ] Create new responsive slider component (Blade + Vanilla JS/jQuery).
- [ ] Implement infinite horizontal scrolling logic (append items on scroll end).
- [ ] Implement touch swipe, mouse drag, and keyboard navigation.
- [ ] Add lazy loading for images and `scroll-snap` CSS.
- [ ] Implement responsive layout:
    - [ ] Mobile (<= 768px): 2-row masonry grid.
    - [ ] Desktop (> 768px): Single-row horizontal strip.
- [ ] Add ARIA-compliant controls (prev/next buttons, dots) visible only on overflow.
- [ ] Replace existing static grid in `index.blade.php` (and other relevant views) with the new slider component.

## Phase 5: Testing & Documentation
- [ ] Write Jest unit tests for slider component logic.
- [ ] Write Cypress E2E tests for full promotion flow (Seller -> Admin -> Public).
- [ ] Generate API documentation (OpenAPI 3.0/Swagger).
- [ ] Create migration script to remove legacy 6-item limit column (if applicable) and ensure data integrity.
