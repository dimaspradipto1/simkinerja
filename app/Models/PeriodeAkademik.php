<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodeAkademik extends Model
{
    protected $fillable = [
        'nama_periode',
    ];

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
