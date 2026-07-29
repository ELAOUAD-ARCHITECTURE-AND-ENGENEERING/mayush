<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\VisitorMetric;
use App\Models\HealthMetric;
use Illuminate\Http\Request;
use PDF;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Generate a technical and commerce summary PDF report.
     */
    public function generateTechnicalReport(Request $request)
    {
        $since = now()->subDays(7);
        
        $data = [
            'report_date' => now()->locale(app()->getLocale())->translatedFormat('d F Y'),
            'period' => 'Last 7 Days',
            'commerce' => [
                'total_revenue' => Order::where('created_at', '>=', $since)->sum('grand_total'),
                'order_count' => Order::where('created_at', '>=', $since)->count(),
                'avg_order_value' => Order::where('created_at', '>=', $since)->avg('grand_total') ?? 0,
            ],
            'technical' => [
                'total_visits' => VisitorMetric::where('created_at', '>=', $since)->count(),
                'error_count' => HealthMetric::where('type', 'error')->where('created_at', '>=', $since)->count(),
                'avg_latency' => HealthMetric::where('type', 'latency')->where('created_at', '>=', $since)->avg('value') ?? 0,
            ],
            'top_pages' => VisitorMetric::where('created_at', '>=', $since)
                ->select('url', \DB::raw('count(*) as count'))
                ->groupBy('url')
                ->orderByDesc('count')
                ->limit(5)
                ->get(),
        ];

        $pdf = PDF::loadView('backend.reports.technical_analytics', $data);
        
        return $pdf->download('Technical_Report_'.now()->format('Y-m-d').'.pdf');
    }
}
