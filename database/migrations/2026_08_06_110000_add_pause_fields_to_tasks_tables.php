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
                    if (!Schema::hasColumn($t, 'last_started_at')) {
                        $table->dateTime('last_started_at')->nullable()->after('status');
                    }
                    if (!Schema::hasColumn($t, 'durasi_detik')) {
                        $table->unsignedInteger('durasi_detik')->default(0)->after('last_started_at');
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
                    if (Schema::hasColumn($t, 'last_started_at')) {
                        $table->dropColumn('last_started_at');
                    }
                    if (Schema::hasColumn($t, 'durasi_detik')) {
                        $table->dropColumn('durasi_detik');
                    }
                });
            }
        }
    }
};
