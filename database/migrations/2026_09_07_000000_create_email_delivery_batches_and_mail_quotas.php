<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_daily_quotas', function (Blueprint $table): void {
            $table->id();
            $table->date('quota_date')->unique();
            $table->unsignedInteger('sent_recipients')->default(0);
            $table->unsignedInteger('reserved_recipients')->default(0);
            $table->timestamp('next_send_at')->nullable();
            $table->timestamps();
        });

        Schema::create('email_delivery_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('automation_id')->constrained();
            $table->foreignId('email_template_id')->constrained();
            $table->date('quota_date');
            $table->timestamp('scheduled_for');
            $table->timestamp('available_at');
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('sending_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->unsignedInteger('recipient_count');
            $table->string('status');
            $table->string('provider_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'available_at']);
            $table->index(['automation_id', 'email_template_id']);
        });

        Schema::table('email_deliveries', function (Blueprint $table): void {
            $table->foreignId('email_delivery_batch_id')
                ->nullable()
                ->after('email_template_id')
                ->constrained('email_delivery_batches')
                ->nullOnDelete();
            $table->index(['status', 'scheduled_for']);
        });
    }

    public function down(): void
    {
        Schema::table('email_deliveries', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('email_delivery_batch_id');
            $table->dropIndex(['status', 'scheduled_for']);
        });

        Schema::dropIfExists('email_delivery_batches');
        Schema::dropIfExists('mail_daily_quotas');
    }
};
