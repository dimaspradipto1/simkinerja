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
        Schema::create('laporan_pengembangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_akademik_id')->nullable()->constrained('periode_akademiks')->nullOnDelete();
            $table->string('tahun_akademik')->nullable(); // contoh: "2025/2026"
            $table->string('judul')->default('Laporan Pencapaian Pengembangan Universitas Ibnu Sina');
            
            // Capaian Program Kerja & Target Strategis
            $table->longText('capaian_program_kerja')->nullable();
            
            // Metrik Mahasiswa: pmb, retensi, kelulusan, total aktif, dll.
            $table->json('data_mahasiswa')->nullable();
            
            // Metrik Program Studi: nama, jenjang, status akreditasi, lembaga akreditasi, masa berlaku, pengembangan
            $table->json('data_prodi')->nullable();
            
            // Capaian Akademik & Non-Akademik: prestasi mahasiswa/dosen, kejuaraan, dll.
            $table->json('data_prestasi')->nullable();
            
            // Perkembangan SDM: Dosen S2/S3, Jafung, Sertifikasi Pendidik, Tendik, Pelatihan
            $table->json('data_sdm')->nullable();
            
            // Capaian Penelitian & Pengabdian Masyarakat: Publikasi, SINTA/Scopus, HKI, Buku, Hibah
            $table->json('data_penelitian_pkm')->nullable();
            
            // Perkembangan Kerjasama: Mitra Pemda/DUDI/PT/Internasional, Realisasi
            $table->json('data_kerjasama')->nullable();
            
            // Perkembangan Sarana & Prasarana: Fasilitas baru, lab, IT, gedung, dll.
            $table->json('data_sarpras')->nullable();
            
            // Kendala dan Permasalahan beserta Langkah Penyelesaian
            $table->longText('kendala_solusi')->nullable();
            
            // Rencana Tindak Lanjut
            $table->longText('tindak_lanjut')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_pengembangans');
    }
};
