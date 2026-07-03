<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('banner_text_versions', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key');
            $table->string('lang', 30)->nullable();
            $table->longText('value');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamps();

            $table->index(['setting_key', 'lang', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_text_versions');
    }
};
