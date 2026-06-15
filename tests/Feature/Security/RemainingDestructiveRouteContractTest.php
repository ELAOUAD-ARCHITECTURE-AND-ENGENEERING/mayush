<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RemainingDestructiveRouteContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_remaining_web_destructive_routes_do_not_accept_get(): void
    {
        $deleteRoutes = [
            'destroy-attribute-value',
            'auction_product_destroy.admin',
            'product_bids_destroy.admin',
            'auction_product_destroy.seller',
            'product_bids_destroy.seller',
            'wholesale_product_destroy.admin',
            'wholesale_product_destroy.seller',
            'preorder-order.destroy',
            'preorder-conversations.destroy',
            'faq.destroy',
            'seller.preorder-order.destroy',
            'account_delete',
        ];

        foreach ($deleteRoutes as $routeName) {
            $methods = Route::getRoutes()->getByName($routeName)->methods();

            $this->assertContains('DELETE', $methods, "{$routeName} should accept DELETE.");
            $this->assertNotContains('GET', $methods, "{$routeName} should not accept GET.");
        }

        $sellerConversationMethods = Route::getRoutes()->getByName('seller.preorder-conversations.destroy')->methods();
        $this->assertContains('POST', $sellerConversationMethods);
        $this->assertNotContains('GET', $sellerConversationMethods);
    }

    public function test_preorder_delete_controls_use_delete_forms(): void
    {
        foreach ([
            'views/preorder/backend/orders/index.blade.php',
            'views/preorder/seller/orders/index.blade.php',
            'views/preorder/backend/conversations/index.blade.php',
            'views/preorder/backend/faqs/index.blade.php',
        ] as $relativePath) {
            $contents = file_get_contents(resource_path($relativePath));

            $this->assertStringContainsString("@method('DELETE')", $contents, "{$relativePath} should use DELETE forms.");
        }
    }

    public function test_account_delete_modal_uses_delete_form(): void
    {
        $contents = file_get_contents(resource_path('views/frontend/partials/account_delete_modal.blade.php'));

        $this->assertStringContainsString('account_delete_form', $contents);
        $this->assertStringContainsString("@method('DELETE')", $contents);
        $this->assertStringNotContainsString('account_delete_link', $contents);
    }

    public function test_attribute_value_delete_route_has_controller_action(): void
    {
        $route = Route::getRoutes()->getByName('destroy-attribute-value');

        $this->assertSame(\App\Http\Controllers\AttributeController::class . '@destroy_attribute_value', $route->getActionName());
        $this->assertTrue(method_exists(\App\Http\Controllers\AttributeController::class, 'destroy_attribute_value'));
    }
}
