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
        Schema::table('semantic_embeddings', function (Blueprint $table) {
            $table->string('content_hash')->nullable()->after('embeddable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semantic_embeddings', function (Blueprint $table) {
            $table->dropColumn('content_hash');
        });
    }
};
