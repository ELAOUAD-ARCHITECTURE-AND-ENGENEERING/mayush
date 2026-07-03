<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->longText('content')->nullable();
                $table->longText('reply')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('follow_sellers')) {
            Schema::create('follow_sellers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('shop_id')->index();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('notes')) {
            Schema::table('notes', function (Blueprint $table) {
                if (!Schema::hasColumn('notes', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                }
                if (!Schema::hasColumn('notes', 'note_type')) {
                    $table->string('note_type')->nullable();
                }
                if (!Schema::hasColumn('notes', 'seller_access')) {
                    $table->boolean('seller_access')->default(false);
                }
            });
        }

        if (!Schema::hasTable('note_translations')) {
            Schema::create('note_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('note_id')->index();
                $table->string('lang', 10)->index();
                $table->longText('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->string('type')->nullable();
                $table->string('title')->nullable();
                $table->string('slug')->nullable()->unique();
                $table->longText('content')->nullable();
                $table->string('meta_title')->nullable();
                $table->longText('meta_description')->nullable();
                $table->longText('tags')->nullable();
                $table->string('meta_image')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('page_translations')) {
            Schema::create('page_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('page_id')->index();
                $table->string('lang', 10)->index();
                $table->string('title')->nullable();
                $table->longText('content')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('uploads') && !Schema::hasColumn('uploads', 'external_link')) {
            Schema::table('uploads', function (Blueprint $table) {
                $table->text('external_link')->nullable()->after('file_name');
            });
        }

        if (!Schema::hasTable('affiliate_stats')) {
            Schema::create('affiliate_stats', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedInteger('no_of_click')->default(0);
                $table->unsignedInteger('no_of_order_item')->default(0);
                $table->unsignedInteger('no_of_delivered')->default(0);
                $table->unsignedInteger('no_of_canceled')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('affiliate_payments')) {
            Schema::create('affiliate_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('affiliate_user_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->double('amount', 20, 2)->default(0);
                $table->string('payment_method')->nullable();
                $table->text('payment_details')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wallets')) {
            Schema::create('wallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->double('amount', 20, 2)->default(0);
                $table->string('payment_method')->nullable();
                $table->longText('payment_details')->nullable();
                $table->string('payment_reference')->nullable()->unique();
                $table->boolean('approval')->default(true);
                $table->boolean('offline_payment')->default(false);
                $table->string('reciept')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('wallets', function (Blueprint $table) {
                if (!Schema::hasColumn('wallets', 'payment_reference')) {
                    $table->string('payment_reference')->nullable()->unique()->after('payment_details');
                }
                if (!Schema::hasColumn('wallets', 'approval')) {
                    $table->boolean('approval')->default(true);
                }
                if (!Schema::hasColumn('wallets', 'offline_payment')) {
                    $table->boolean('offline_payment')->default(false);
                }
                if (!Schema::hasColumn('wallets', 'reciept')) {
                    $table->string('reciept')->nullable();
                }
            });
        }
    }
};
