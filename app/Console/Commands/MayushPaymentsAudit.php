<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentAttempt;
use App\Models\CmiCallbackLog;
use App\Models\Order;
use Carbon\Carbon;

class MayushPaymentsAudit extends Command
{
    protected $signature = 'mayush:payments:audit';
    protected $description = 'Audit payments for pending attempts, duplicate callbacks, and inconsistencies';

    public function handle()
    {
        $this->info('Starting Payment Audit...');
        
        $pendingAttempts = PaymentAttempt::where('status', 'initiated')
            ->where('initiated_at', '<', Carbon::now()->subMinutes(30))
            ->get();
            
        if ($pendingAttempts->count() > 0) {
            $this->warn("Found {$pendingAttempts->count()} stale pending payment attempts (older than 30 mins).");
            foreach ($pendingAttempts as $attempt) {
                $this->line(" - Attempt ID: {$attempt->id} | OID: {$attempt->merchant_reference} | Amount: {$attempt->amount}");
            }
        } else {
            $this->info('No stale pending payment attempts found.');
        }

        $duplicateLogs = CmiCallbackLog::where('is_duplicate', true)
            ->where('received_at', '>=', Carbon::now()->subDays(7))
            ->get();

        if ($duplicateLogs->count() > 0) {
            $this->warn("Found {$duplicateLogs->count()} duplicate callbacks in the last 7 days.");
        } else {
            $this->info('No duplicate callbacks detected recently.');
        }

        $failedLogs = CmiCallbackLog::where('processing_status', 'failed')
            ->where('received_at', '>=', Carbon::now()->subDays(7))
            ->get();

        if ($failedLogs->count() > 0) {
            $this->warn("Found {$failedLogs->count()} failed callbacks in the last 7 days.");
        } else {
            $this->info('No failed callbacks detected recently.');
        }

        $shippedUnpaid = Order::where('delivery_status', '!=', 'pending')
            ->where('payment_status', 'unpaid')
            ->where('payment_type', '!=', 'cash_on_delivery')
            ->get();

        if ($shippedUnpaid->count() > 0) {
            $this->error("CRITICAL: Found {$shippedUnpaid->count()} shipped orders that are still unpaid!");
            foreach ($shippedUnpaid as $order) {
                $this->line(" - Order ID: {$order->id} | Code: {$order->code} | Status: {$order->delivery_status}");
            }
        } else {
            $this->info('All shipped orders appear to have valid payment statuses.');
        }

        $this->info('Audit Complete.');
    }
}
