<?php

use App\Enums\EmailDeliveryBatchStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_daily_quotas', function (Blueprint $table): void {
            $table->unsignedInteger('sent_messages')->default(0)->after('quota_date');
            $table->unsignedInteger('reserved_messages')->default(0)->after('sent_messages');
        });

        Schema::table('email_delivery_batches', function (Blueprint $table): void {
            $table->unsignedInteger('attempt_count')->default(0)->after('recipient_count');
            $table->timestamp('last_attempted_at')->nullable()->after('attempt_count');
        });

        Schema::table('email_deliveries', function (Blueprint $table): void {
            $table->unique(
                ['automation_id', 'customer_id', 'scheduled_for'],
                'email_deliveries_schedule_unique'
            );
        });

        DB::table('mail_daily_quotas')->update([
            'sent_messages' => DB::raw('(select count(*) from email_delivery_batches where email_delivery_batches.quota_date = mail_daily_quotas.quota_date and email_delivery_batches.status = '.DB::getPdo()->quote(EmailDeliveryBatchStatus::SENT->value).')'),
            'reserved_messages' => DB::raw('(select count(*) from email_delivery_batches where email_delivery_batches.quota_date = mail_daily_quotas.quota_date and email_delivery_batches.status in ('.DB::getPdo()->quote(EmailDeliveryBatchStatus::QUEUED->value).', '.DB::getPdo()->quote(EmailDeliveryBatchStatus::PROCESSING->value).', '.DB::getPdo()->quote(EmailDeliveryBatchStatus::SENDING->value).'))'),
        ]);
    }

    public function down(): void
    {
        Schema::table('email_deliveries', function (Blueprint $table): void {
            $table->dropUnique('email_deliveries_schedule_unique');
        });

        Schema::table('email_delivery_batches', function (Blueprint $table): void {
            $table->dropColumn(['attempt_count', 'last_attempted_at']);
        });

        Schema::table('mail_daily_quotas', function (Blueprint $table): void {
            $table->dropColumn(['sent_messages', 'reserved_messages']);
        });
    }
};
