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
        if (!Schema::hasTable('lpj_templates')) {
            Schema::create('lpj_templates', function (Blueprint $table) {
                $table->id();
                $table->string('nama_template')->default('Template Laporan Pertanggungjawaban (LPJ)');
                $table->string('link_lpj', 500)->nullable();
                $table->string('file_template')->nullable();
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });

            // Insert initial default template record
            DB::table('lpj_templates')->insert([
                'nama_template' => 'Template Resmi Laporan Pertanggungjawaban (LPJ)',
                'link_lpj' => 'https://docs.google.com/document/d/1official-template-lpj/edit?usp=sharing',
                'file_template' => null,
                'keterangan' => 'Gunakan template dokumen resmi ini sebagai acuan penulisan LPJ kegiatan kepanitiaan.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpj_templates');
    }
};
