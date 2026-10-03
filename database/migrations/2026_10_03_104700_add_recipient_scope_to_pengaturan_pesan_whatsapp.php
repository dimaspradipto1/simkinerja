<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_pesan_whatsapp', function (Blueprint $table) {
            $table->string('tujuan_penerima', 40)
                ->default('pemilik_dan_atasan')
                ->after('waktu_kirim');
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_pesan_whatsapp', function (Blueprint $table) {
            $table->dropColumn('tujuan_penerima');
        });
    }
};
