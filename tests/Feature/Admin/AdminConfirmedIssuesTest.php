<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\Backend\PointManagementController;
use App\Http\Controllers\BusinessSettingsController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminConfirmedIssuesTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_admin_destinations_use_existing_controller_actions(): void
    {
        $this->assertSame(
            AffiliateController::class . '@users',
            Route::getRoutes()->getByName('refferals.users')->getActionName()
        );

        $this->assertSame(
            PointManagementController::class . '@templates',
            Route::getRoutes()->getByName('club_points.configs')->getActionName()
        );

        $this->assertSame(
            PointManagementController::class . '@dashboard',
            Route::getRoutes()->getByName('set_product_points')->getActionName()
        );
    }

    public function test_custom_product_visitors_has_separate_read_and_write_actions(): void
    {
        $this->assertSame(
            BusinessSettingsController::class . '@customProductVisitors',
            Route::getRoutes()->getByName('custom_product_visitors')->getActionName()
        );

        $this->assertSame(
            BusinessSettingsController::class . '@customProductVisitorsUpdate',
            Route::getRoutes()->getByName('custom_product_visitors.update')->getActionName()
        );
    }

    public function test_customer_package_edit_links_use_the_resource_parameter_name(): void
    {
        $index = file_get_contents(resource_path('views/backend/customer/customer_packages/index.blade.php'));
        $edit = file_get_contents(resource_path('views/backend/customer/customer_packages/edit.blade.php'));

        $this->assertStringContainsString("['customer_package'=>\$customer_package->id", $index);
        $this->assertStringContainsString("['customer_package'=>\$customer_package->id", $edit);
        $this->assertStringNotContainsString("['id'=>\$customer_package->id", $index);
        $this->assertStringNotContainsString("['id'=>\$customer_package->id", $edit);
    }

    public function test_shared_delete_confirmation_submits_a_delete_form(): void
    {
        $modal = file_get_contents(resource_path('views/modals/delete_modal.blade.php'));
        $script = file_get_contents(public_path('assets/js/aiz-core.js'));

        $this->assertStringContainsString('<form id="delete-form" action="" method="POST"', $modal);
        $this->assertStringContainsString("@method('DELETE')", $modal);
        $this->assertStringContainsString('$("#delete-form").attr("action", url);', $script);
        $this->assertStringNotContainsString('<a href="" id="delete-link"', $modal);
    }

    public function test_product_search_condition_is_grouped_with_sku_search(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/ProductController.php'));

        $this->assertStringContainsString(
            '$products = $products->where(function ($query) use ($sort_search)',
            $controller
        );
        $this->assertStringContainsString("->where('products.name', 'like'", $controller);
        $this->assertStringContainsString("->orWhereHas('stocks'", $controller);
    }

    public function test_product_filter_search_does_not_leak_unmatched_products(): void
    {
        $admin = \App\Models\User::factory()->admin()->create();
        $category = Category::factory()->create();
        $matched = Product::factory()->create([
            'name' => 'Audit Search Match Product',
            'category_id' => $category->id,
        ]);
        $unmatched = Product::factory()->create([
            'name' => 'Audit Search Hidden Product',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->get(route('products.filter', [
            'page' => 1,
            'product_type' => 'all_products',
            'search' => 'Audit Search Match',
            'seller_type' => 'all',
        ]));

        $response->assertOk();
        $html = $response->json('html');

        $this->assertStringContainsString($matched->name, $html);
        $this->assertStringNotContainsString($unmatched->name, $html);
    }
}
