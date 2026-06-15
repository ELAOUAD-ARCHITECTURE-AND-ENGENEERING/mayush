<?php

namespace Tests\Feature\Frontend;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CompareFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_added_to_compare_without_duplicates(): void
    {
        $product = Product::factory()->create();

        $this->post(route('compare.addToCompare'), ['id' => $product->id])
            ->assertOk();

        $this->post(route('compare.addToCompare'), ['id' => $product->id])
            ->assertOk();

        $compare = session('compare');

        $this->assertInstanceOf(Collection::class, $compare);
        $this->assertSame([$product->id], $compare->values()->all());
    }

    public function test_compare_remove_and_reset_use_delete_and_update_session(): void
    {
        $first = Product::factory()->create();
        $second = Product::factory()->create();

        $this->withSession(['compare' => collect([$first->id, $second->id])])
            ->delete(route('compare.remove'), ['id' => $first->id])
            ->assertRedirect();

        $this->assertSame([$second->id], session('compare')->values()->all());

        $this->delete(route('compare.reset'))
            ->assertRedirect();

        $this->assertFalse(session()->has('compare'));
    }

    public function test_compare_destructive_actions_do_not_accept_get(): void
    {
        $product = Product::factory()->create();

        $this->assertContains(
            $this->get(route('compare.remove', ['id' => $product->id]))->getStatusCode(),
            [404, 405]
        );

        $this->assertContains(
            $this->get(route('compare.reset'))->getStatusCode(),
            [404, 405]
        );
    }

    public function test_compare_page_uses_csrf_delete_forms_and_add_to_cart_modal_hook(): void
    {
        $markup = file_get_contents(resource_path('views/frontend/view_compare.blade.php'));

        $this->assertStringContainsString("route('compare.reset')", $markup);
        $this->assertStringContainsString("@method('DELETE')", $markup);
        $this->assertStringContainsString("route('compare.remove')", $markup);
        $this->assertStringContainsString('name="id"', $markup);
        $this->assertStringContainsString('showAddToCartModal({{ $item }})', $markup);
        $this->assertStringNotContainsString("route('compare.remove', ['id' => \$item])", $markup);
    }

    public function test_add_to_cart_modal_can_be_opened_for_compare_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Compare Cart Product',
            'photos' => '',
        ]);
        $product->stocks()->create([
            'variant' => '',
            'sku' => 'COMPARE-CART-1',
            'price' => 120,
            'qty' => 5,
        ]);

        $this->post(route('cart.showCartModal'), ['id' => $product->id])
            ->assertOk()
            ->assertSee('Compare Cart Product');
    }
}
