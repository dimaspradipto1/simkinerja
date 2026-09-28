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
        $tables = ['rencana_kerjas', 'kepanitiaans', 'insidentils', 'kegiatans', 'milestones'];

        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                Schema::table($t, function (Blueprint $table) use ($t) {
                    if (!Schema::hasColumn($t, 'alasan_pause')) {
                        $table->text('alasan_pause')->nullable();
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
        $tables = ['rencana_kerjas', 'kepanitiaans', 'insidentils', 'kegiatans', 'milestones'];

        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                Schema::table($t, function (Blueprint $table) use ($t) {
                    if (Schema::hasColumn($t, 'alasan_pause')) {
                        $table->dropColumn('alasan_pause');
                    }
                });
            }
        }
    }
};
