<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpirePromotions extends Command
{
    protected $signature = 'promotions:expire';
    protected $description = 'Auto-expire promotions whose end_date has passed';

    public function handle()
    {
        $count = Promotion::where('status', 'approved')
            ->where('end_date', '<', Carbon::now())
            ->update(['status' => 'expired']);

        if ($count > 0) {
            Log::info("Promotions:expire — Expired {$count} promotion(s).");
            $this->info("Expired {$count} promotion(s).");
        } else {
            $this->info('No promotions to expire.');
        }

        return Command::SUCCESS;
    }
}
