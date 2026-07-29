<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\CmiCallbackLog;
use Illuminate\Support\Facades\DB;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class SystemHealthController extends Controller
{
    public function index(MasterSupervisorRepository $masters)
    {
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

        // Chart Data: Last 7 Days CMI Failures & Payment Attempts
        $chartDates = collect(range(6, 0))->map(function ($daysAgo) {
            return now()->subDays($daysAgo)->locale(app()->getLocale())->translatedFormat('M d');
        })->values()->toArray();

        $cmiChartData = [];
        $paymentChartData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $cmiChartData[] = CmiCallbackLog::where('processing_status', '!=', 'success')
                ->whereDate('created_at', $date)->count();
                
            $paymentChartData[] = PaymentAttempt::whereDate('created_at', $date)->count();
        }

        // Summary Analytics
        $totalPaymentsToday = PaymentAttempt::whereDate('created_at', now()->format('Y-m-d'))->count();
        $successRate = $totalPaymentsToday > 0 ? 
            round((PaymentAttempt::whereDate('created_at', now()->format('Y-m-d'))->where('status', 'success')->count() / $totalPaymentsToday) * 100) : 100;

        return view('backend.system.health', compact(
            'horizonStatus',
            'failedJobs',
            'pendingImages',
            'shippedUnpaid',
            'stuckPayments',
            'cmiFailures',
            'duplicateCmi',
            'chartDates',
            'cmiChartData',
            'paymentChartData',
            'successRate'
        ));
    }
}
