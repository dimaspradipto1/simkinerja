<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PeriodeAkademik extends Model
{
    protected $fillable = [
        'nama_periode',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Cek apakah kolom 'is_active' ada di tabel periode_akademiks.
     * Jika belum ada di database (misal belum migrate di server production),
     * fungsi ini otomatis menambahkan kolom tersebut on-the-fly agar query tidak error 500.
     */
    public static function hasIsActiveColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        try {
            if (Schema::hasTable('periode_akademiks')) {
                if (!Schema::hasColumn('periode_akademiks', 'is_active')) {
                    Schema::table('periode_akademiks', function (Blueprint $table) {
                        $table->boolean('is_active')->default(false)->after('nama_periode');
                    });

                    // Set satu periode terbaru sebagai default aktif jika ada record
                    $latest = DB::table('periode_akademiks')->orderByDesc('id')->first();
                    if ($latest) {
                        DB::table('periode_akademiks')->where('id', $latest->id)->update(['is_active' => true]);
                    }
                }
                $hasColumn = Schema::hasColumn('periode_akademiks', 'is_active');
                return $hasColumn;
            }
        } catch (\Throwable $e) {
            // Jika alter table gagal (misal hak akses user db terbatas), fallback gracefully
        }

        $hasColumn = false;
        return $hasColumn;
    }

    /**
     * Dapatkan periode akademik yang sedang aktif
     */
    public static function getActive()
    {
        if (static::hasIsActiveColumn()) {
            return static::where('is_active', true)->first() ?? static::first();
        }
        return static::first();
    }

    /**
     * Dapatkan semua periode akademik terurut (aktif di paling atas)
     */
    public static function getAllOrdered()
    {
        if (static::hasIsActiveColumn()) {
            return static::orderByDesc('is_active')->orderBy('id', 'asc')->get();
        }
        return static::orderBy('id', 'asc')->get();
    }

    /**
     * Dapatkan ID periode akademik yang sedang aktif
     */
    public static function getActiveId()
    {
        $active = static::getActive();
        return $active ? $active->id : null;
    }

    /**
     * Relasi ke Triwulan
     */
    public function triwulans()
    {
        return $this->hasMany(Triwulan::class)->orderBy('urutan', 'asc');
    }

    /**
     * Format nama periode menjadi format T.A. yang jelas, misal: "T.A. 2026/2027 (Gasal)"
     */
    public function getFormattedTaAttribute(): string
    {
        $name = trim($this->nama_periode ?? '');
        if (!$name) {
            return '-';
        }

        // Jika pola seperti "2026/2027 Gasal" atau "2026/2027 (Gasal)"
        if (preg_match('/^(\d{4}\/\d{4})\s*(.*)$/i', $name, $matches)) {
            $tahun = $matches[1];
            $semester = trim($matches[2] ?? '', "() -\t\n\r\0\x0B");
            if (!empty($semester)) {
                return "T.A. {$tahun} ({$semester})";
            }
            return "T.A. {$tahun}";
        }

        // Jika sudah diawali T.A. atau Tahun Akademik
        if (stripos($name, 'T.A.') === 0 || stripos($name, 'Tahun Akademik') === 0) {
            return $name;
        }

        return "T.A. {$name}";
    }
}
