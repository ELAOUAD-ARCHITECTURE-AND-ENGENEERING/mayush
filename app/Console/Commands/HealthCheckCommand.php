<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\CmiCallbackLog;
use Carbon\Carbon;

class HealthCheckCommand extends Command
{
    protected $signature = 'mayush:health-check';
    protected $description = 'Run a safe, read-only health check of the Mayush application';

    public function handle()
    {
        $this->info('Running Mayush Health Check...');

        // 1. Basic App Info
        $env = app()->environment();
        $debug = config('app.debug') ? 'Enabled (WARNING)' : 'Disabled (OK)';
        
        // 2. Connections
        $dbConnected = $this->checkDatabaseConnection();
        $cacheDriver = config('cache.default');
        $queueConnection = config('queue.default');

        // 3. Jobs
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 'Table missing';
        $failedImages = Schema::hasTable('failed_image_jobs') ? DB::table('failed_image_jobs')->count() : 'N/A';
        
        // 4. Payment Anomalies
        $recentFailedCmi = class_exists(CmiCallbackLog::class) ? 
            CmiCallbackLog::where('processing_status', '!=', 'success')->where('created_at', '>=', now()->subDay())->count() : 'N/A';
        $recentDuplicateCmi = class_exists(CmiCallbackLog::class) ? 
            CmiCallbackLog::where('processing_status', 'ignored_duplicate')->where('created_at', '>=', now()->subDay())->count() : 'N/A';
        $stalePayments = class_exists(PaymentAttempt::class) ? 
            PaymentAttempt::whereIn('status', ['initiated', 'pending'])->where('created_at', '<', now()->subHours(24))->count() : 'N/A';
        $recentExpirations = class_exists(PaymentAttempt::class) ? 
            PaymentAttempt::where('status', 'expired')->where('updated_at', '>=', now()->subDay())->count() : 'N/A';

        // 5. Orders & Shipping
        $shippedUnpaid = class_exists(Order::class) ? 
            Order::where('delivery_status', 'shipped')->where('payment_status', 'unpaid')->count() : 'N/A';
        $failedOnessta = Schema::hasTable('onessta_shipment_logs') ? 
            DB::table('onessta_shipment_logs')->where('status', 'failed')->where('created_at', '>=', now()->subDay())->count() : 'N/A';

        // 6. Disk Space
        $diskSpace = function_exists('disk_free_space') ? round(disk_free_space(storage_path()) / 1024 / 1024 / 1024, 2) . ' GB free' : 'Unknown';

        $this->table(
            ['Metric', 'Value'],
            [
                ['Environment', $env],
                ['Debug Mode', $debug],
                ['Database Connection', $dbConnected ? 'OK' : 'FAILED'],
                ['Cache Driver', $cacheDriver],
                ['Queue Connection', $queueConnection],
                ['Failed Jobs', $failedJobs],
                ['Failed Image Jobs', $failedImages],
                ['Recent Failed CMI (24h)', $recentFailedCmi],
                ['Duplicate CMI (24h)', $recentDuplicateCmi],
                ['Stale Payments (>24h)', $stalePayments],
                ['Recent Expirations (24h)', $recentExpirations],
                ['Shipped & Unpaid Orders', $shippedUnpaid],
                ['Failed ONESSTA Shipments', $failedOnessta],
                ['Storage Disk Space', $diskSpace],
            ]
        );

        if (!$dbConnected) {
            $this->error('Database connection failed.');
            return 1;
        }

        $this->info('Health check completed.');
        return 0;
    }

    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
