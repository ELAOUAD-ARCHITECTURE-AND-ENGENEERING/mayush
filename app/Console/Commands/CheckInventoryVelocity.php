<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\InventoryLog;
use App\Models\User;
use App\Notifications\PredictiveRestockNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CheckInventoryVelocity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-velocity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze inventory velocity and send predictive restock alerts (MA-106)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting inventory velocity check...');

        // 1. Get all active products
        $products = Product::where('published', 1)->get();
        $adminUsers = User::where('user_type', 'admin')->get();

        $alertsCount = 0;

        foreach ($products as $product) {
            // 2. Calculate 7-day velocity (units sold per day)
            $totalUnitsSold = InventoryLog::where('product_id', $product->id)
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->where('quantity_delta', '<', 0)
                ->sum(DB::raw('ABS(quantity_delta)'));

            $dailyVelocity = $totalUnitsSold / 7;

            if ($dailyVelocity > 0) {
                $currentStock = $product->current_stock;
                $daysRemaining = $currentStock / $dailyVelocity;

                // 3. Threshold check (3 days)
                if ($daysRemaining <= 3) {
                    $this->line("Product [{$product->name}]: {$daysRemaining} days remaining (Stock: {$currentStock}, Velocity: {$dailyVelocity})");
                    
                    $notificationData = [
                        'product' => [
                            'id' => $product->id,
                            'name' => $product->name,
                        ],
                        'days_remaining' => $daysRemaining,
                        'current_stock'  => $currentStock,
                    ];

                    // Notify Seller (Always notify via Dashboard, maybe Email)
                    if ($product->user) {
                        $product->user->notify(new PredictiveRestockNotification($notificationData));
                    }

                    // Notify Admin (Deduplicated: only once until stock changes or 7 days pass)
                    $adminCacheKey = 'admin_restock_alert_' . $product->id . '_' . $currentStock;
                    if (!Cache::has($adminCacheKey)) {
                        foreach ($adminUsers as $admin) {
                            $admin->notify(new PredictiveRestockNotification($notificationData));
                        }
                        // Cache for 7 days to prevent repeated emails for the same stock level
                        Cache::put($adminCacheKey, true, now()->addDays(7));
                        $this->info("  Alert sent to Admin for [{$product->name}]");
                    } else {
                        $this->comment("  Admin alert suppressed (already sent for this stock level)");
                    }

                    $alertsCount++;
                }
            }
        }

        $this->info("Inventory check complete. {$alertsCount} alerts processed.");
    }
}
