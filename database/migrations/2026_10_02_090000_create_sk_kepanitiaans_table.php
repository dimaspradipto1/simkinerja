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
        Schema::create('sk_kepanitiaans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kepanitiaan');
            $table->string('nomor_sk')->nullable();
            $table->string('tahun_akademik')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->string('file_sk');
            $table->text('keterangan')->nullable();
            $table->json('hasil_ekstraksi')->nullable();
            $table->unsignedInteger('total_anggota')->default(0);
            $table->unsignedInteger('total_matched')->default(0);
            $table->string('status')->default('Aktif');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sk_kepanitiaans');
    }
};
