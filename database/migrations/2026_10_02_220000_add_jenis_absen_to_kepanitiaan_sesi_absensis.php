<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kepanitiaan_sesi_absensis', function (Blueprint $table) {
            $table->string('jenis_absen', 20)->default('datang')->after('nama_sesi');
        });
    }

    public function down(): void
    {
        Schema::table('kepanitiaan_sesi_absensis', function (Blueprint $table) {
            $table->dropColumn('jenis_absen');
        });
    }
};
