<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_type_translations')) {
            Schema::create('notification_type_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('notification_type_id');
                $table->foreign('notification_type_id')
                    ->references('id')
                    ->on('notification_types')
                    ->cascadeOnDelete();
                $table->string('lang', 10)->index();
                $table->string('name')->nullable();
                $table->text('default_text')->nullable();
                $table->timestamps();

                $table->unique(['notification_type_id', 'lang'], 'notification_type_lang_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_type_translations');
    }
};
