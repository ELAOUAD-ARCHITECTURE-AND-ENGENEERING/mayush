<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This migration is a placeholder as the 6-item limit was hardcoded in the view logic
        // and has been removed by updating the codebase.
        // If there was a column in business_settings, we would remove it here.
    }

    public function down(): void
    {
        // Revert logic if needed
    }
};