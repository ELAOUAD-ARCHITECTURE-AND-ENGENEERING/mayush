<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $source = 'Please wait, redirecting to payment gateway...';
        $key = 'please_wait_redirecting_to_payment_gateway';

        DB::table('translations')->updateOrInsert(
            ['lang' => 'en', 'lang_key' => $key],
            ['lang_value' => $source, 'updated_at' => now(), 'created_at' => now()]
        );
        DB::table('translations')->updateOrInsert(
            ['lang' => 'fr', 'lang_key' => $key],
            ['lang_value' => 'Veuillez patienter, redirection vers la passerelle de paiement…', 'updated_at' => now(), 'created_at' => now()]
        );

        Cache::forget('translations-en');
        Cache::forget('translations-fr');
    }

    public function down(): void
    {
        // Shared translation rows are intentionally retained on rollback.
    }
};
