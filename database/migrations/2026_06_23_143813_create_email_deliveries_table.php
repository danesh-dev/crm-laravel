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
        Schema::create('email_deliveries', function (Blueprint $table) {

            $table->id();

            $table->foreignId('automation_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('email_template_id')->constrained();
            $table->string('status');

            $table->timestamp('scheduled_for');
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->string('provider_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index([
                'automation_id',
                'customer_id'
            ]);

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_deliveries');
    }
};
