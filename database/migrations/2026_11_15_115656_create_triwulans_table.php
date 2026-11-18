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
        if (!Schema::hasTable('triwulans')) {
            Schema::create('triwulans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('periode_akademik_id')->nullable()->constrained('periode_akademiks')->nullOnDelete();
                $table->string('nama'); // TW1, TW2, TW3, TW4
                $table->string('label')->nullable(); // TW1 (Sep–Nov), dll.
                $table->string('rentang_bulan')->nullable(); // Sep–Nov, Des–Feb, Mar–Mei, Jun–Agu
                $table->string('keterangan')->nullable(); // September – November, dll.
                $table->unsignedTinyInteger('bulan_mulai'); // 9, 12, 3, 6
                $table->unsignedTinyInteger('bulan_selesai'); // 11, 2, 5, 8
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->boolean('is_active')->default(false);
                $table->unsignedTinyInteger('urutan')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triwulans');
    }
};
