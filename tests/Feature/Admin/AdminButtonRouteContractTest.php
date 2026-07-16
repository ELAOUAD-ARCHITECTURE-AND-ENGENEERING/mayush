<?php

namespace Tests\Feature\Admin;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminButtonRouteContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_destructive_routes_do_not_accept_get_requests(): void
    {
        foreach ([
            'products.destroy',
            'sellers.destroy',
            'classified_products.destroy',
            'categories.destroy',
            'brands.destroy',
            'digitalproducts.destroy',
            'tax.destroy',
            'languages.destroy',
            'colors.destroy',
            'customers.destroy',
            'custom-pages.destroy',
            'roles.destroy',
            'staffs.destroy',
            'flash_deals.destroy',
            'subscriber.destroy',
            'orders.destroy',
            'admin.loyalty.points.templates.destroy',
            'top_banner.delete',
            'custom_label.delete',
            'custom-notifications.delete',
        ] as $routeName) {
            $methods = Route::getRoutes()->getByName($routeName)->methods();

            $this->assertContains('DELETE', $methods, "{$routeName} should accept DELETE.");
            $this->assertNotContains('GET', $methods, "{$routeName} should not accept GET.");
        }
    }

    public function test_legacy_seller_verification_view_points_to_current_review_workflow(): void
    {
        $seller = User::factory()->seller()->create();
        $shop = Shop::factory()->create([
            'user_id' => $seller->id,
            'verification_status' => 0,
            'verification_info' => json_encode([
                ['label' => 'Document', 'type' => 'text', 'value' => 'Provided'],
            ]),
        ]);

        $html = view('backend.sellers.verification', compact('shop'))->render();

        $this->assertStringContainsString('Legacy Verification Workflow Disabled', $html);
        $this->assertStringContainsString(
            route('sellers.registration_pending', ['review_shop' => $shop->id]),
            $html
        );
        $this->assertStringNotContainsString('method="POST"', $html);
        $this->assertStringNotContainsString(route('sellers.approve', $shop->id), $html);
        $this->assertStringNotContainsString(route('sellers.reject', $shop->id), $html);
    }

    public function test_admin_button_templates_use_delete_contracts(): void
    {
        $classified = file_get_contents(resource_path('views/backend/customer/classified_products/index.blade.php'));
        $products = file_get_contents(resource_path('views/backend/product/products/index.blade.php'));
        $sellerProfileProducts = file_get_contents(resource_path('views/backend/sellers/profile/seller_products.blade.php'));

        $this->assertStringNotContainsString("}}}}", $classified);
        $this->assertStringContainsString("@method('DELETE')", $classified);
        $this->assertStringContainsString("type: 'DELETE'", $products);
        $this->assertStringContainsString("@method('DELETE')", $sellerProfileProducts);
    }

    public function test_admin_catalog_templates_use_delete_contracts(): void
    {
        $categories = file_get_contents(resource_path('views/backend/product/categories/index.blade.php'));
        $brands = file_get_contents(resource_path('views/backend/product/brands/index.blade.php'));
        $digitalProducts = file_get_contents(resource_path('views/backend/product/digital_products/index.blade.php'));
        $taxes = file_get_contents(resource_path('views/backend/setup_configurations/tax/index.blade.php'));
        $languages = file_get_contents(resource_path('views/backend/setup_configurations/languages/index.blade.php'));
        $colors = file_get_contents(resource_path('views/backend/product/color/index.blade.php'));

        $this->assertStringContainsString("type: 'DELETE'", $categories);
        $this->assertStringContainsString("'X-CSRF-TOKEN'", $categories);
        $this->assertStringContainsString("type: 'DELETE'", $brands);
        $this->assertStringContainsString("'X-CSRF-TOKEN'", $brands);
        $this->assertStringContainsString("@method('DELETE')", $digitalProducts);
        $this->assertStringContainsString("@method('DELETE')", $taxes);
        $this->assertStringContainsString("@method('DELETE')", $languages);
        $this->assertStringContainsString("@method('DELETE')", $colors);
    }

    public function test_admin_backoffice_templates_use_delete_contracts(): void
    {
        foreach ([
            'views/backend/customer/customers/index.blade.php',
            'views/backend/customer/customers/unverified.blade.php',
            'views/backend/loyalty_points/templates.blade.php',
            'views/backend/marketing/flash_deals/index.blade.php',
            'views/backend/marketing/subscribers/index.blade.php',
            'views/backend/notification/custom_notification_history.blade.php',
            'views/backend/product/custom_label/partials/table.blade.php',
            'views/backend/sales/index.blade.php',
            'views/backend/staff/staffs/index.blade.php',
            'views/backend/staff/staff_roles/index.blade.php',
            'views/backend/website_settings/pages/index.blade.php',
            'views/backend/website_settings/topBanner/top_banner_list.blade.php',
        ] as $relativePath) {
            $contents = file_get_contents(resource_path($relativePath));

            $this->assertStringContainsString("@method('DELETE')", $contents, "{$relativePath} should use DELETE forms.");
        }
    }
}
