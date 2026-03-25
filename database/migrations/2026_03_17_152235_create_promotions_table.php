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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id'); // Likely bigint
            $table->integer('user_id')->unsigned();    // Confirmed int
            $table->string('tier')->default('standard');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('status')->default('awaiting_admin_review'); // awaiting_admin_review, approved, rejected, expired
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('product_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
