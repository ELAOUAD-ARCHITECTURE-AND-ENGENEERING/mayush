<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_events')) {
            Schema::create('notification_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('event_key', 100);
                $table->string('category', 50)->index();
                $table->string('severity', 30)->default('info')->index();
                $table->string('source_type', 100)->default('');
                $table->string('source_id', 100)->default('');
                $table->string('occurrence_key', 191);
                $table->json('payload')->nullable();
                $table->timestamp('occurred_at')->index();
                $table->timestamps();
                $table->unique(
                    ['event_key', 'source_type', 'source_id', 'occurrence_key'],
                    'notification_events_occurrence_unique'
                );
            });
        }

        if (!Schema::hasTable('notification_deliveries')) {
            Schema::create('notification_deliveries', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->uuid('event_id')->index();
                $table->string('recipient_type', 100);
                $table->unsignedBigInteger('recipient_id');
                $table->uuid('notification_id')->nullable()->index();
                $table->string('channel', 30);
                $table->string('state', 30)->default('queued')->index();
                $table->unsignedInteger('attempt_count')->default(0);
                $table->timestamp('scheduled_at')->nullable()->index();
                $table->string('provider_reference', 191)->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->string('failure_code', 100)->nullable();
                $table->timestamps();
                $table->unique(
                    ['event_id', 'recipient_type', 'recipient_id', 'channel'],
                    'notification_deliveries_recipient_channel_unique'
                );
                $table->index(
                    ['recipient_type', 'recipient_id', 'state'],
                    'notification_deliveries_recipient_state_index'
                );
            });
        }

        if (!Schema::hasTable('notification_delivery_attempts')) {
            Schema::create('notification_delivery_attempts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('delivery_id')->index();
                $table->unsignedInteger('attempt_number');
                $table->string('state', 30);
                $table->string('provider_response_category', 100)->nullable();
                $table->string('failure_code', 100)->nullable();
                $table->text('sanitized_error')->nullable();
                $table->timestamp('retry_at')->nullable();
                $table->timestamp('occurred_at')->index();
                $table->timestamps();
                $table->unique(
                    ['delivery_id', 'attempt_number', 'state'],
                    'notification_attempt_transition_unique'
                );
            });
        }

        if (!Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('notification_type_id')->nullable()->index();
                $table->string('event_key', 100);
                $table->boolean('in_app_enabled')->default(true);
                $table->boolean('broadcast_enabled')->default(true);
                $table->boolean('email_enabled')->default(true);
                $table->boolean('sms_enabled')->default(false);
                $table->boolean('push_enabled')->default(true);
                $table->timestamps();
                $table->unique(['user_id', 'event_key'], 'notification_preferences_user_event_unique');
            });
        }

        if (!Schema::hasTable('user_notification_settings')) {
            Schema::create('user_notification_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('timezone', 64)->default('UTC');
                $table->boolean('quiet_hours_enabled')->default(false);
                $table->time('quiet_hours_start')->nullable();
                $table->time('quiet_hours_end')->nullable();
                $table->boolean('in_app_enabled')->default(true);
                $table->boolean('broadcast_enabled')->default(true);
                $table->boolean('email_enabled')->default(true);
                $table->boolean('sms_enabled')->default(false);
                $table->boolean('push_enabled')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notification_devices')) {
            Schema::create('notification_devices', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('user_id')->index();
                $table->text('token');
                $table->string('token_hash', 64)->unique();
                $table->string('platform', 30)->default('unknown');
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamp('revoked_at')->nullable()->index();
                $table->timestamps();
            });
        }

        $this->extendNotificationsTable();
        $this->convergeNotificationTypes();
    }

    private function extendNotificationsTable(): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'event_id')) {
                $table->uuid('event_id')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('notifications', 'category')) {
                $table->string('category', 50)->nullable()->after('type')->index();
            }
            if (!Schema::hasColumn('notifications', 'severity')) {
                $table->string('severity', 30)->default('info')->after('category')->index();
            }
            if (!Schema::hasColumn('notifications', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('read_at')->index();
            }
        });
        $this->addInboxProjectionUniqueIndex();
    }

    private function addInboxProjectionUniqueIndex(): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        $index = 'notifications_event_recipient_unique';
        if (!Schema::hasIndex('notifications', $index)) {
            Schema::table('notifications', function (Blueprint $table) use ($index) {
                $table->unique(
                    ['event_id', 'notifiable_type', 'notifiable_id'],
                    $index
                );
            });
        }
    }

    private function convergeNotificationTypes(): void
    {
        if (!Schema::hasTable('notification_types')) {
            return;
        }

        Schema::table('notification_types', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_types', 'user_type')) {
                $table->string('user_type', 30)->nullable()->index();
            }
            if (!Schema::hasColumn('notification_types', 'addon')) {
                $table->string('addon', 100)->nullable()->index();
            }
            if (!Schema::hasColumn('notification_types', 'category')) {
                $table->string('category', 50)->default('system')->index();
            }
            if (!Schema::hasColumn('notification_types', 'severity')) {
                $table->string('severity', 30)->default('info');
            }
            if (!Schema::hasColumn('notification_types', 'mandatory_inbox')) {
                $table->boolean('mandatory_inbox')->default(false);
            }
            foreach (['in_app', 'broadcast', 'email', 'sms', 'push'] as $channel) {
                $column = 'default_'.$channel;
                if (!Schema::hasColumn('notification_types', $column)) {
                    $table->boolean($column)->default(in_array($channel, ['in_app', 'broadcast'], true));
                }
            }
            if (!Schema::hasColumn('notification_types', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        // Audit records are intentionally retained. Rollback is feature-flag based.
    }
};
