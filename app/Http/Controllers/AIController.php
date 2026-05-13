<?php

namespace App\Http\Controllers;

use App\Models\AiPrompt;
use App\Models\AiUsageLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AIController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:manage_ai_configuration'])->only(
            'ai_configuration',
            'ai_templates',
            'update',
            'add_edit_products'
        );
    }

    public function ai_token_usage(Request $request)
    {
        $query = AiUsageLog::with('user')->latest();

        if ($request->filled('date')) {
            $dates = explode(' to ', $request->date);
            if (count($dates) === 2) {
                try {
                    $query->whereBetween('created_at', [
                        Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay(),
                        Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay(),
                    ]);
                } catch (\Throwable) {
                    // Invalid UI date filters are ignored so reports remain available.
                }
            }
        }

        $summaryQuery = clone $query;
        $logs = $query->paginate(20);
        $totalRequests = (clone $summaryQuery)->count();
        $totalTokens = (clone $summaryQuery)->sum('total_tokens');
        $avgPerRequest = $totalRequests > 0 ? round($totalTokens / $totalRequests) : 0;
        $date = $request->date;

        return view('backend.reports.ai_token_usage', compact('logs', 'totalRequests', 'totalTokens', 'avgPerRequest', 'date'));
    }

    public function ai_configuration()
    {
        return view('backend.setup_configurations.ai_configurations.ai_config');
    }

    public function ai_templates()
    {
        $prompt_templates = AiPrompt::get();

        return view('backend.setup_configurations.ai_configurations.prompt_templates', compact('prompt_templates'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'prompt' => ['required', 'string'],
        ]);

        $aiPrompt = AiPrompt::findOrFail(decrypt($id));
        $aiPrompt->update(['prompt' => $request->prompt]);

        flash(translate('Prompt updated successfully'))->success();
        return back();
    }

    public function add_edit_products()
    {
        return view('backend.setup_configurations.ai_configurations.add_edit');
    }
}
