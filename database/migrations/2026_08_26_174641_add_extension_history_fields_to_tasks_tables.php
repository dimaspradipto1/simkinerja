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
        $tables = ['rencana_kerjas', 'kepanitiaans', 'insidentils', 'kegiatans'];

        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                Schema::table($t, function (Blueprint $table) use ($t) {
                    if (!Schema::hasColumn($t, 'estimasi_tanggal_selesai_awal')) {
                        $table->date('estimasi_tanggal_selesai_awal')->nullable()->after('estimasi_tanggal_selesai');
                    }
                    if (!Schema::hasColumn($t, 'estimasi_jam_selesai_awal')) {
                        $table->time('estimasi_jam_selesai_awal')->nullable()->after('estimasi_tanggal_selesai_awal');
                    }
                    if (!Schema::hasColumn($t, 'estimasi_unlock_approved_at')) {
                        $table->dateTime('estimasi_unlock_approved_at')->nullable()->after('estimasi_unlock_requested_at');
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
        $tables = ['rencana_kerjas', 'kepanitiaans', 'insidentils', 'kegiatans'];

        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                Schema::table($t, function (Blueprint $table) use ($t) {
                    if (Schema::hasColumn($t, 'estimasi_unlock_approved_at')) {
                        $table->dropColumn('estimasi_unlock_approved_at');
                    }
                    if (Schema::hasColumn($t, 'estimasi_jam_selesai_awal')) {
                        $table->dropColumn('estimasi_jam_selesai_awal');
                    }
                    if (Schema::hasColumn($t, 'estimasi_tanggal_selesai_awal')) {
                        $table->dropColumn('estimasi_tanggal_selesai_awal');
                    }
                });
            }
        }
    }
};
