<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CancelUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel_unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel unpaid orders older than 24 hours and restock their items';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orders = Order::where('payment_status', 'unpaid')
            ->whereNotIn('delivery_status', ['cancelled', 'delivered'])
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            DB::beginTransaction();
            try {
                foreach ($order->orderDetails as $orderDetail) {
                    product_restock($orderDetail);
                    $orderDetail->delivery_status = 'cancelled';
                    $orderDetail->save();
                }

                $order->delivery_status = 'cancelled';
                $order->save();
                DB::commit();
                $count++;
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error("Error cancelling unpaid order {$order->id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully cancelled {$count} unpaid abandoned orders.");
        return 0;
    }
}
