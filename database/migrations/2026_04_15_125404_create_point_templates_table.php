<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('fixed')->comment('fixed, percentage_of_price');
            $table->double('value', 10, 2)->default(0);
            $table->double('min_threshold', 10, 2)->nullable();
            $table->double('max_threshold', 10, 2)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_templates');
    }
};
