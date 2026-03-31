<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderDetail;
use App\Models\FrequentlyBoughtProduct;
use App\Models\Order;
use DB;

class UpdateFrequentlyBought extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:update-affinities {--days=90 : Number of days to analyze}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze order history to automatically identify frequently bought together products (MA-107)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Analyzing order history for the last $days days...");

        // 1. Get all successful order IDs in the timeframe
        $orderIds = Order::where('created_at', '>=', now()->subDays($days))
            ->whereNotIn('delivery_status', ['cancelled'])
            ->pluck('id');

        if ($orderIds->isEmpty()) {
            $this->warn("No orders found in the given timeframe.");
            return;
        }

        // 2. Group product IDs by order
        $orderProducts = OrderDetail::whereIn('order_id', $orderIds)
            ->select('order_id', 'product_id')
            ->get()
            ->groupBy('order_id')
            ->map(function ($items) {
                return $items->pluck('product_id')->unique()->values()->all();
            })
            ->filter(function ($products) {
                return count($products) > 1; // Only care about multi-item orders
            });

        if ($orderProducts->isEmpty()) {
            $this->warn("No multi-item orders found to analyze affinities.");
            return;
        }

        $this->info("Found " . $orderProducts->count() . " multi-item orders. Calculating affinities...");

        $affinities = [];

        // 3. Count co-occurrences
        foreach ($orderProducts as $products) {
            $count = count($products);
            for ($i = 0; $i < $count; $i++) {
                for ($j = 0; $j < $count; $j++) {
                    if ($i === $j) continue;
                    
                    $p1 = $products[$i];
                    $p2 = $products[$j];
                    
                    if (!isset($affinities[$p1])) $affinities[$p1] = [];
                    if (!isset($affinities[$p1][$p2])) $affinities[$p1][$p2] = 0;
                    
                    $affinities[$p1][$p2]++;
                }
            }
        }

        // 4. Update the database
        $this->info("Updating frequently_bought_products table...");
        
        DB::beginTransaction();
        try {
            // Remove old automated entries
            FrequentlyBoughtProduct::where('source', 'automated')->delete();

            $totalInserted = 0;
            foreach ($affinities as $productId => $relatedProducts) {
                // Sort by frequency descending and take top 5
                arsort($relatedProducts);
                $topProducts = array_slice($relatedProducts, 0, 5, true);

                foreach ($topProducts as $relatedId => $count) {
                    FrequentlyBoughtProduct::create([
                        'product_id' => $productId,
                        'frequently_bought_product_id' => $relatedId,
                        'source' => 'automated',
                        'affinity_score' => $count
                    ]);
                    $totalInserted++;
                }
            }

            DB::commit();
            $this->info("Successfully updated $totalInserted automated affinities.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to update affinities: " . $e->getMessage());
        }
    }
}
