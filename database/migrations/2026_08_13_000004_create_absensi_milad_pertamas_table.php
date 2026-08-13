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
        Schema::create('absensi_milad_pertamas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('hadir_datang')->nullable();
            $table->time('waktu_datang')->nullable();
            $table->text('catatan_hadir_datang')->nullable();
            $table->string('hadir_pulang')->nullable();
            $table->time('waktu_pulang')->nullable();
            $table->text('catatan_hadir_pulang')->nullable();
            $table->string('bukti_izin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_milad_pertamas');
    }
};
