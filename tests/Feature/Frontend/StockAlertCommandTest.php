<?php

namespace Tests\Feature\Frontend;

use App\Mail\StockAlertMail;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class StockAlertCommandTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    public function test_command_queues_alert_for_back_in_stock_subscription_once(): void
    {
        Mail::fake();

        $user = User::factory()->customer()->create(['email' => 'buyer@example.test']);
        $product = Product::factory()->outOfStock()->create(['current_stock' => 0]);
        ProductStock::factory()->create([
            'product_id' => $product->id,
            'variant' => '',
            'qty' => 5,
        ]);

        $subscription = StockSubscription::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'email' => $user->email,
            'notified_at' => null,
        ]);

        $this->artisan('stock:send-alerts')
            ->expectsOutput('Stock alerts processed successfully. 1 notification(s) queued.')
            ->assertExitCode(0);

        Mail::assertQueued(StockAlertMail::class, function (StockAlertMail $mail) use ($product, $user) {
            return $mail->product->is($product) && $mail->user->is($user);
        });

        $this->assertNotNull($subscription->fresh()->notified_at);

        $this->artisan('stock:send-alerts')
            ->expectsOutput('Stock alerts processed successfully. 0 notification(s) queued.')
            ->assertExitCode(0);

        Mail::assertQueued(StockAlertMail::class, 1);
    }

    public function test_command_does_not_notify_until_matching_variant_is_in_stock(): void
    {
        Mail::fake();

        $product = Product::factory()->outOfStock()->create([
            'variant_product' => 1,
            'current_stock' => 0,
        ]);
        ProductStock::factory()->create([
            'product_id' => $product->id,
            'variant' => 'Red-XL',
            'qty' => 0,
        ]);
        ProductStock::factory()->create([
            'product_id' => $product->id,
            'variant' => 'Blue-XL',
            'qty' => 8,
        ]);

        $subscription = StockSubscription::create([
            'product_id' => $product->id,
            'variant' => 'Red-XL',
            'email' => 'guest@example.test',
            'notified_at' => null,
        ]);

        $this->artisan('stock:send-alerts')
            ->expectsOutput('Stock alerts processed successfully. 0 notification(s) queued.')
            ->assertExitCode(0);

        Mail::assertNothingQueued();
        $this->assertNull($subscription->fresh()->notified_at);

        ProductStock::where('product_id', $product->id)
            ->where('variant', 'Red-XL')
            ->update(['qty' => 3]);

        $this->artisan('stock:send-alerts')
            ->expectsOutput('Stock alerts processed successfully. 1 notification(s) queued.')
            ->assertExitCode(0);

        Mail::assertQueued(StockAlertMail::class, 1);
        $this->assertNotNull($subscription->fresh()->notified_at);
    }

    public function test_failed_mail_queue_is_logged_and_subscription_remains_pending(): void
    {
        $product = Product::factory()->outOfStock()->create(['current_stock' => 4]);
        $subscription = StockSubscription::create([
            'product_id' => $product->id,
            'email' => 'buyer@example.test',
            'notified_at' => null,
        ]);

        Mail::shouldReceive('to')
            ->once()
            ->with('buyer@example.test')
            ->andThrow(new RuntimeException('SMTP unavailable'));

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message, array $context) use ($subscription, $product) {
                return $message === 'Failed to queue stock alert notification.'
                    && $context['subscription_id'] === $subscription->id
                    && $context['product_id'] === $product->id
                    && $context['email'] === 'buyer@example.test'
                    && $context['error'] === 'SMTP unavailable';
            });

        $this->artisan('stock:send-alerts')
            ->expectsOutput('Stock alerts processed successfully. 0 notification(s) queued.')
            ->assertExitCode(0);

        $this->assertNull($subscription->fresh()->notified_at);
    }
}
