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
        Schema::table('automations', function (Blueprint $table) {
            $table->smallInteger('repeat_count')
                ->nullable()
                ->after('is_active');
        });

        Schema::table('automation_schedules', function (Blueprint $table) {
            $table->date('run_date')
                ->nullable()
                ->after('automation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('automation_schedules', function (Blueprint $table) {
            $table->dropColumn('run_date');
        });

        Schema::table('automations', function (Blueprint $table) {
            $table->dropColumn('repeat_count');
        });
    }
};
