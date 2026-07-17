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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('provider_subscription_id')->nullable()->after('customer_id');
            $table->string('plan')->nullable()->after('provider_subscription_id');

            $table->index('provider_subscription_id');
            $table->index(['customer_id', 'started_at', 'expires_at', 'plan'], 'subscriptions_import_duplicate_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['provider_subscription_id']);
            $table->dropIndex('subscriptions_import_duplicate_index');
            $table->dropColumn(['provider_subscription_id', 'plan']);
        });
    }
};
