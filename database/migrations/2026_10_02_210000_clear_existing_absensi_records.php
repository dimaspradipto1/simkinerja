<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['kepanitiaan_presensis', 'absensi_kinerja_harians'] as $table) {
            if (Schema::hasTable($table)) {
                $proofPaths = Schema::hasColumn($table, 'bukti_izin')
                    ? DB::table($table)->whereNotNull('bukti_izin')->pluck('bukti_izin')->filter()->all()
                    : [];

                DB::table($table)->delete();

                if ($proofPaths !== []) {
                    Storage::disk('public')->delete($proofPaths);
                }
            }
        }
    }

    public function down(): void
    {
        // Attendance records removed by this migration cannot be restored.
    }
};
