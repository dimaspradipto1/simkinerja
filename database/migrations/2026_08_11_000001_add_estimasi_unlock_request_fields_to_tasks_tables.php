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
        $tables = ['rencana_kerjas', 'kepanitiaans', 'insidentils'];

        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                Schema::table($t, function (Blueprint $table) use ($t) {
                    if (!Schema::hasColumn($t, 'estimasi_unlock_reason')) {
                        $table->text('estimasi_unlock_reason')->nullable()->after('status');
                    }
                    if (!Schema::hasColumn($t, 'estimasi_unlock_requested_at')) {
                        $table->dateTime('estimasi_unlock_requested_at')->nullable()->after('estimasi_unlock_reason');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['rencana_kerjas', 'kepanitiaans', 'insidentils'];

        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                Schema::table($t, function (Blueprint $table) use ($t) {
                    if (Schema::hasColumn($t, 'estimasi_unlock_requested_at')) {
                        $table->dropColumn('estimasi_unlock_requested_at');
                    }
                    if (Schema::hasColumn($t, 'estimasi_unlock_reason')) {
                        $table->dropColumn('estimasi_unlock_reason');
                    }
                });
            }
        }
    }
};
