<?php

namespace Tests\Feature\Maintenance;

use App\Models\FrequentlyBoughtProduct;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentToken;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExistingPruneCommandSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_prune_affinities_dry_run_deletes_nothing(): void
    {
        $products = Product::factory()->count(2)->create();
        $affinity = $this->createAffinity($products[0]->id, $products[1]->id, 'automated', now()->subDays(45));

        $this->artisan('inventory:prune-affinities', ['--days' => 30, '--dry-run' => true])
            ->expectsOutputToContain('DRY RUN')
            ->expectsOutputToContain('Table: frequently_bought_products')
            ->assertSuccessful();

        $this->assertDatabaseHas('frequently_bought_products', ['id' => $affinity->id]);
    }

    public function test_inventory_prune_affinities_never_deletes_products(): void
    {
        $products = Product::factory()->count(2)->create();
        $this->createAffinity($products[0]->id, $products[1]->id, 'automated', now()->subDays(45));

        $this->artisan('inventory:prune-affinities', ['--days' => 30])->assertSuccessful();

        $this->assertDatabaseHas('products', ['id' => $products[0]->id]);
        $this->assertDatabaseHas('products', ['id' => $products[1]->id]);
    }

    public function test_inventory_prune_affinities_never_deletes_product_stocks(): void
    {
        $products = Product::factory()->count(2)->create();
        $stock = ProductStock::factory()->create(['product_id' => $products[0]->id]);
        $this->createAffinity($products[0]->id, $products[1]->id, 'automated', now()->subDays(45));

        $this->artisan('inventory:prune-affinities', ['--days' => 30])->assertSuccessful();

        $this->assertDatabaseHas('product_stocks', ['id' => $stock->id]);
    }

    public function test_inventory_prune_affinities_never_deletes_orders_or_order_details(): void
    {
        $products = Product::factory()->count(2)->create();
        $order = Order::factory()->create();
        $orderDetail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $products[0]->id,
        ]);
        $this->createAffinity($products[0]->id, $products[1]->id, 'automated', now()->subDays(45));

        $this->artisan('inventory:prune-affinities', ['--days' => 30])->assertSuccessful();

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
        $this->assertDatabaseHas('order_details', ['id' => $orderDetail->id]);
    }

    public function test_inventory_prune_affinities_only_affects_stale_automated_rebuildable_rows(): void
    {
        $products = Product::factory()->count(4)->create();
        $staleAutomated = $this->createAffinity($products[0]->id, $products[1]->id, 'automated', now()->subDays(45));
        $manual = $this->createAffinity($products[0]->id, $products[2]->id, 'manual', now()->subDays(45));
        $recentAutomated = $this->createAffinity($products[0]->id, $products[3]->id, 'automated', now()->subDays(2));

        $this->artisan('inventory:prune-affinities', ['--days' => 30])->assertSuccessful();

        $this->assertDatabaseMissing('frequently_bought_products', ['id' => $staleAutomated->id]);
        $this->assertDatabaseHas('frequently_bought_products', ['id' => $manual->id]);
        $this->assertDatabaseHas('frequently_bought_products', ['id' => $recentAutomated->id]);
    }

    public function test_vault_prune_expired_dry_run_deletes_and_updates_nothing(): void
    {
        $expired = PaymentToken::factory()->expired()->active()->create();

        $this->artisan('vault:prune-expired', ['--dry-run' => true])
            ->expectsOutputToContain('DRY RUN')
            ->expectsOutputToContain('Table: payment_tokens')
            ->assertSuccessful();

        $this->assertDatabaseHas('payment_tokens', [
            'id' => $expired->id,
            'is_active' => true,
        ]);
    }

    public function test_vault_prune_expired_never_deletes_orders(): void
    {
        $order = Order::factory()->paid()->create();
        PaymentToken::factory()->expired()->active()->create();

        $this->artisan('vault:prune-expired')->assertSuccessful();

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_vault_prune_expired_never_deletes_successful_payment_attempts(): void
    {
        $paymentAttemptId = DB::table('payment_attempts')->insertGetId([
            'gateway' => 'cmi',
            'merchant_reference' => 'RETENTION-SAFE-ATTEMPT',
            'amount' => 100,
            'currency' => 'MAD',
            'status' => 'completed',
            'initiated_at' => now()->subHour(),
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        PaymentToken::factory()->expired()->active()->create();

        $this->artisan('vault:prune-expired')->assertSuccessful();

        $this->assertDatabaseHas('payment_attempts', [
            'id' => $paymentAttemptId,
            'status' => 'completed',
        ]);
    }

    public function test_vault_prune_expired_never_deletes_cmi_callback_logs(): void
    {
        $callbackLogId = DB::table('cmi_callback_logs')->insertGetId([
            'gateway' => 'cmi',
            'merchant_reference' => 'RETENTION-SAFE-CALLBACK',
            'gateway_reference' => 'CMI-CALLBACK-1',
            'payload_hash' => 'payload-hash',
            'signature_valid' => true,
            'is_duplicate' => false,
            'processing_status' => 'processed',
            'received_at' => now(),
            'processed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        PaymentToken::factory()->expired()->active()->create();

        $this->artisan('vault:prune-expired')->assertSuccessful();

        $this->assertDatabaseHas('cmi_callback_logs', [
            'id' => $callbackLogId,
            'processing_status' => 'processed',
        ]);
    }

    public function test_vault_prune_expired_never_deactivates_active_valid_payment_tokens(): void
    {
        $valid = PaymentToken::factory()->active()->create([
            'card_expiry_month' => now()->month,
            'card_expiry_year' => now()->year + 1,
        ]);
        PaymentToken::factory()->expired()->active()->create();

        $this->artisan('vault:prune-expired')->assertSuccessful();

        $this->assertDatabaseHas('payment_tokens', [
            'id' => $valid->id,
            'is_active' => true,
        ]);
    }

    public function test_vault_prune_expired_only_affects_expired_unusable_records(): void
    {
        $expired = PaymentToken::factory()->expired()->active()->create();
        $valid = PaymentToken::factory()->active()->create([
            'card_expiry_month' => now()->month,
            'card_expiry_year' => now()->year + 1,
        ]);
        $noExpiry = PaymentToken::factory()->active()->create([
            'card_expiry_month' => null,
            'card_expiry_year' => null,
        ]);

        $this->artisan('vault:prune-expired')->assertSuccessful();

        $this->assertDatabaseHas('payment_tokens', ['id' => $expired->id, 'is_active' => false]);
        $this->assertDatabaseHas('payment_tokens', ['id' => $valid->id, 'is_active' => true]);
        $this->assertDatabaseHas('payment_tokens', ['id' => $noExpiry->id, 'is_active' => true]);
    }

    public function test_scheduler_entries_are_documented_and_not_too_frequent(): void
    {
        $kernel = file_get_contents(app_path('Console/Kernel.php'));

        $this->assertStringContainsString("inventory:prune-affinities --days=30')->daily()", $kernel);
        $this->assertStringContainsString("vault:prune-expired')->dailyAt('02:00')", $kernel);
        $this->assertStringNotContainsString("inventory:prune-affinities --days=30')->everyMinute()", $kernel);
        $this->assertStringNotContainsString("vault:prune-expired')->everyMinute()", $kernel);
    }

    private function createAffinity(int $productId, int $relatedProductId, string $source, $updatedAt): FrequentlyBoughtProduct
    {
        $affinity = FrequentlyBoughtProduct::create([
            'product_id' => $productId,
            'frequently_bought_product_id' => $relatedProductId,
            'category_id' => null,
            'source' => $source,
            'affinity_score' => 2,
        ]);

        $affinity->forceFill([
            'created_at' => $updatedAt,
            'updated_at' => $updatedAt,
        ])->save();

        return $affinity;
    }
}
