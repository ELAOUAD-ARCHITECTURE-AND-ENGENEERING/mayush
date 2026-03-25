# Classified Products Promotion Module Redesign Specification

## 1. Overview
This project involves a complete redesign of the classified products promotion module to provide a more flexible and robust system for sellers to promote their products. The new system will support two distinct workflows for promotion, a highly performant and responsive frontend slider, and a comprehensive backend management system with API endpoints and validation.

## 2. Workflows
### 2.1. Promote Existing Product
- **User Action**: Seller selects an existing classified product from their dashboard.
- **System Action**: Displays a "Promote" option.
- **Flow**:
    1.  Seller clicks "Promote".
    2.  System checks user credits/balance.
    3.  If sufficient credits: Show promotion configuration form (Tier, Dates, Notes).
    4.  If insufficient credits: Show system warning popup prompting to top up.
    5.  Seller submits request.
    6.  System creates a `pending-promotion` record with status `awaiting_admin_review`.

### 2.2. Create & Promote New Product
- **User Action**: Seller creates a new classified product.
- **System Action**: specific "Promote this product" checkbox/toggle in the creation form.
- **Flow**:
    1.  Seller fills in product details.
    2.  Seller toggles "Promote this product".
    3.  System shows promotion configuration fields (Tier, Dates, Notes).
    4.  Seller submits.
    5.  System checks user credits/balance.
    6.  If sufficient: Product created + Promotion request created (`awaiting_admin_review`).
    7.  If insufficient: Show warning, allow saving product without promotion or prompt to top up.

## 3. Frontend Architecture
### 3.1. Infinite Horizontal Slider
- **Behavior**:
    - **Infinite Scrolling**: Automatically appends items when reaching the end (if > 6 items).
    - **Scroll Snap**: Pixel-perfect alignment using CSS `scroll-snap`.
    - **Lazy Loading**: Images load only when near the viewport.
    - **Interactions**:
        - Touch swipe (passive listeners).
        - Mouse drag.
        - Keyboard navigation (Arrow keys).
        - Throttling: `requestAnimationFrame` for 60fps performance.
- **Responsiveness**:
    - **Mobile (<= 768px)**: 2-row masonry grid layout.
    - **Desktop (> 768px)**: Single-row horizontal strip.
    - **Scaling**: Starts at 2 visible cards on 320px, scales to 6+ on large screens.
- **Controls**:
    - ARIA-compliant Previous/Next buttons (visible only on overflow).
    - Dot indicators (visible only on overflow).

### 3.2. Seller Dashboard
- **UI Updates**:
    - New "Promotions" tab/section.
    - List of active/pending/expired promotions.
    - "Promote" button on existing product list.
    - Credit balance display and "Top Up" modal.

### 3.3. Admin Dashboard
- **Approval Queue**:
    - List of promotions with status `awaiting_admin_review`.
    - Details view: Product snapshot, Tier, Dates, Notes.
    - Actions: Approve, Reject.

## 4. Backend Architecture
### 4.1. Database Schema
- **Table**: `promotions`
    - `id` (PK)
    - `product_id` (FK to `customer_products`)
    - `user_id` (FK to `users`)
    - `tier` (enum/string)
    - `start_date` (datetime)
    - `end_date` (datetime)
    - `status` (enum: `awaiting_admin_review`, `approved`, `rejected`, `expired`)
    - `notes` (text)
    - `created_at`, `updated_at`

### 4.2. API Endpoints
- **POST /api/v2/promotions**
    - Body: `{ productId?, newProductDraft?, tier, startDate, endDate, notes }`
    - Logic: Handles both existing and new product promotion requests. Validates credits.
- **PATCH /api/v2/promotions/{id}**
    - Body: `{ status: 'approved' | 'rejected' | 'expired' }`
    - Logic: Updates status. If approved, activates promotion.
- **GET /api/v2/promotions**
    - Query Params: `status=awaiting_admin_review`, `page`, `sort`.
    - Logic: Returns paginated list of promotions.
- **GraphQL**: (Optional/Bonus if time permits, focus on REST first as per standard Laravel setup).

### 4.3. Validation
- **Max Promotions**: A seller cannot have more than 10 `approved` (live) promotions at once.
- **No Overlap**: Date ranges for the same product cannot overlap.
- **Credits**: User must have sufficient credits (logic to be defined based on existing credit system).

## 5. Testing
- **Unit Tests (Jest)**:
    - Slider component logic (swipe, drag, snap).
    - Promotion creation form validation.
- **E2E Tests (Cypress)**:
    - Full flow: Login -> Select Product -> Promote -> Admin Login -> Approve -> Verify on Homepage.
    - Slider interaction on mobile/desktop viewports.
- **Backend Tests (PHPUnit)**:
    - API endpoint validation.
    - Database constraints (overlap, max limit).

## 6. Migration
- **Script**: Remove the hard-coded 6-item limit column/logic from the legacy system.
- **Data Preservation**: Ensure existing data is not lost during the transition.
