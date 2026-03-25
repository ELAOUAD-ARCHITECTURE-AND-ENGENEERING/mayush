<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Utility\EmailUtility;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class SendAbandonedCartReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:send_reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for unpaid abandoned orders after 18 hours';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Check if the column exists to avoid errors if migration failed
        $hasColumn = Schema::hasColumn('orders', 'abandoned_reminder_sent');

        $query = Order::where('payment_status', 'unpaid')
            ->whereNotIn('delivery_status', ['cancelled', 'delivered'])
            ->where('created_at', '<', Carbon::now()->subHours(18)) // Older than 18 hours
            ->where('created_at', '>', Carbon::now()->subHours(24)); // But less than 24 hours (before cancellation)

        if ($hasColumn) {
            $query->where('abandoned_reminder_sent', 0);
        }

        $orders = $query->get();
        $count = 0;

        foreach ($orders as $order) {
            try {
                EmailUtility::abandoned_cart_reminder($order);
                
                if ($hasColumn) {
                    $order->abandoned_reminder_sent = 1;
                    $order->save();
                }
                
                $count++;
            } catch (\Exception $e) {
                \Log::error("Error sending abandoned reminder for order {$order->id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully sent {$count} abandoned cart reminders.");
        return 0;
    }
}
