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
        Schema::create('import_runs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('status')->default('pending');
            $table->string('original_filename')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedInteger('total_records')->nullable();
            $table->unsignedInteger('processed_records')->default(0);
            $table->unsignedInteger('customers_created')->default(0);
            $table->unsignedInteger('customers_updated')->default(0);
            $table->unsignedInteger('subscriptions_created')->default(0);
            $table->unsignedInteger('duplicate_subscriptions_skipped')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->json('errors')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_runs');
    }
};
