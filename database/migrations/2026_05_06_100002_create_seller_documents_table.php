<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerDocumentsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('seller_documents')) {
            Schema::create('seller_documents', function (Blueprint $table) {
                $table->id();
                $table->integer('shop_id');
                $table->enum('document_type', ['contract', 'government_id', 'business_registration', 'certification']);
                $table->string('file_path');          // relative path inside storage/app/private/seller-documents/
                $table->string('original_name');      // original filename shown in UI
                $table->string('mime_type', 100);
                $table->unsignedBigInteger('file_size'); // in bytes
                $table->timestamp('uploaded_at')->useCurrent();
                $table->timestamps();

                $table->index(['shop_id', 'document_type']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('seller_documents');
    }
}
