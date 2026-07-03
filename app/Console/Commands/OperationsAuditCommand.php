<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\CmiCallbackLog;
use Illuminate\Support\Facades\DB;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class OperationsAuditCommand extends Command
{
    protected $signature = 'mayush:operations:audit';
    protected $description = 'Audit operational health metrics for NOC alerting';

    public function handle(MasterSupervisorRepository $masters)
    {
        $this->info('Starting Mayush Operations Audit...');

        // 1. Horizon Status
        try {
            $horizonStatus = count($masters->all()) > 0 ? 'Active' : 'Inactive';
        } catch (\Exception $e) {
            $horizonStatus = 'Error (Redis Down)';
        }

        // 2. Queue Health
        $failedJobs = DB::table('failed_jobs')->count();
        $pendingImages = DB::table('jobs')->where('queue', 'images')->count();

        // 3. Operational Anomalies
        $shippedUnpaid = Order::where('delivery_status', 'shipped')
            ->where('payment_status', 'unpaid')
            ->count();

        $stuckPayments = PaymentAttempt::whereIn('status', ['initiated', 'pending'])
            ->where('created_at', '<', now()->subHours(24))
            ->count();

        $cmiFailures = CmiCallbackLog::where('processing_status', '!=', 'success')
            ->where('created_at', '>=', now()->subDay())
            ->count();
            
        $duplicateCmi = CmiCallbackLog::where('processing_status', 'ignored_duplicate')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $this->table(
            ['Metric', 'Value', 'Status'],
            [
                ['Horizon', $horizonStatus, $horizonStatus === 'Active' ? '<info>OK</info>' : '<error>FAIL</error>'],
                ['Failed Jobs', $failedJobs, $failedJobs == 0 ? '<info>OK</info>' : '<error>WARN</error>'],
                ['Pending Image Jobs', $pendingImages, '<comment>INFO</comment>'],
                ['Shipped & Unpaid Orders', $shippedUnpaid, $shippedUnpaid == 0 ? '<info>OK</info>' : '<error>CRITICAL</error>'],
                ['Stuck Payments (>24h)', $stuckPayments, $stuckPayments == 0 ? '<info>OK</info>' : '<comment>WARN</comment>'],
                ['CMI Failures (24h)', $cmiFailures, $cmiFailures == 0 ? '<info>OK</info>' : '<error>WARN</error>'],
                ['Duplicate CMI (24h)', $duplicateCmi, $duplicateCmi == 0 ? '<info>OK</info>' : '<comment>INFO</comment>'],
            ]
        );

        $hasCritical = $shippedUnpaid > 0 || $horizonStatus !== 'Active';
        
        if ($hasCritical) {
            $this->error('Critical operational anomalies detected!');
            return 1;
        }

        $this->info('Audit complete. All systems nominal.');
        return 0;
    }
}
