<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('kategoris')) {
            Schema::create('kategoris', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kategori');
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });

            // Insert default categories
            DB::table('kategoris')->insert([
                [
                    'nama_kategori' => 'Rencana Kerja',
                    'deskripsi' => 'Kategori rencana tugas kerja utama / rutin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'nama_kategori' => 'Kepanitiaan',
                    'deskripsi' => 'Kategori rencana kerja kegiatan kepanitiaan',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'nama_kategori' => 'Insidentil',
                    'deskripsi' => 'Kategori rencana kerja penugasan insidentil / mendadak',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategoris');
    }
};
