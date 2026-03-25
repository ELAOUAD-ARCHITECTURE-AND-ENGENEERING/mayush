# Security Restrictions: Customer Product Management

This document outlines the access control measures implemented to prevent unauthorized access to classified products, promotions, and related management features. These features are strictly reserved for users with the "seller" (vendor) or "admin" roles.

## 1. User Interface Restrictions
- **Customer Dashboard Sidebar**: The "Classified Products" menu item has been completely removed from the customer's view (`resources/views/frontend/inc/user_side_nav.blade.php`). This ensures they cannot navigate to the product listing or creation pages via the UI.

## 2. Backend Web Authorization (Controllers)
The `App\Http\Controllers\CustomerProductController` has been updated with explicit role checks. If a user with `user_type == 'customer'` attempts to access these routes, they are denied access.

### Restricted Methods:
- `index()`: Redirects to dashboard with an error flash message.
- `create()`: Redirects to dashboard with an error flash message.
- `store()`: Aborts with a `403 Forbidden` status.
- `edit()`: Redirects to dashboard with an error flash message.
- `update()`: Aborts with a `403 Forbidden` status.
- `destroy()`: Aborts with a `403 Forbidden` status.
- `store_promotion()`: Aborts with a `403 Forbidden` status.

*Security Note: Direct URL manipulation (e.g., manually navigating to `/customer_products/create`) will trigger these backend checks and deny access.*

### Product Ownership Verification:
In addition to role-based checks, the following methods verify that the authenticated user owns the product being accessed:
- `edit($id)`: Checks `$product->user_id === Auth::id()`. Aborts 403 if mismatched.
- `update($id)`: Checks `$customer_product->user_id === Auth::id()`. Aborts 403 if mismatched.
- `destroy($id)`: Checks `$product->user_id === Auth::id()`. Aborts 403 if mismatched.
- `store_promotion()`: Checks `$product->user_id !== Auth::user()->id`. Aborts 403 if mismatched.

This prevents a seller from editing, updating, deleting, or promoting another seller's classified product even if they know the product ID.

## 3. API Authorization Restrictions
The API endpoints used by mobile apps or headless clients are also protected. The `App\Http\Controllers\Api\V2\CustomerProductController` enforces strict role validation.

### Restricted API Endpoints:
- `POST /api/v2/classified/store`: Returns `403 Forbidden` JSON response.
- `POST /api/v2/classified/update/{id}`: Returns `403 Forbidden` JSON response.
- `DELETE /api/v2/classified/delete/{id}`: Returns `403 Forbidden` JSON response.
- `POST /api/v2/classified/change-status/{id}`: Returns `403 Forbidden` JSON response.

*Security Note: Customers attempting to bypass the web interface by sending direct API requests via Postman or other tools will be blocked by these checks.*

## 4. Admin Promotions Controller Guard
The `App\Http\Controllers\PromotionController` (web) includes a constructor middleware that verifies `user_type === 'admin'`. This is a defense-in-depth measure on top of the admin route group middleware, ensuring that only admin users can access the promotions management interface (`/admin/promotions`) or update promotion statuses.

The `App\Http\Controllers\Api\V2\PromotionController@update` method also explicitly checks `user_type !== 'admin'` before allowing status changes via the API.

## 5. Automatic Promotion Expiry
The `promotions:expire` Artisan command runs daily via the Laravel scheduler. It automatically sets the status of any `approved` promotion whose `end_date` has passed to `expired`. This ensures promoted products are no longer displayed after their promotion window closes.

## Verification
These restrictions ensure that even if a customer discovers a valid route or API endpoint, the server will explicitly reject the request based on their `user_type` attribute in the database. Ownership verification further ensures sellers are isolated from each other's data.
