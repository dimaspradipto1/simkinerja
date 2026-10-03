<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pengaturan_pesan_whatsapp')
            ->update(['tujuan_penerima' => 'pemilik_tugas']);
    }

    public function down(): void
    {
        DB::table('pengaturan_pesan_whatsapp')
            ->update(['tujuan_penerima' => 'pemilik_dan_atasan']);
    }
};
