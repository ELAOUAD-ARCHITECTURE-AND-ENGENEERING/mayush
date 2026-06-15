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
        Schema::create('vendor_performance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->index();
            $table->decimal('total_revenue', 15, 2)->default(0.00);
            $table->integer('dispute_count')->default(0);
            $table->integer('orders_count')->default(0);
            $table->decimal('avg_rating', 3, 2)->nullable();
            $table->date('snapshot_date')->index();
            $table->timestamps();
            
            $table->unique(['seller_id', 'snapshot_date'], 'vendor_snapshot_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_performance_snapshots');
    }
};
