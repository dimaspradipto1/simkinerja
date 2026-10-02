<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class AbsensiKinerjaHarian extends Model
{
    use HasFactory;

    public const STANDARD_WORK_HOURS = 8;
    public const BREAK_HOURS = 1;

    protected $table = 'absensi_kinerja_harians';

    protected $fillable = [
        'user_id',
        'tanggal',
        'status_datang',
        'waktu_datang',
        'status_pulang',
        'waktu_pulang',
        'jam_kerja_standar',
        'jam_istirahat',
        'keterangan_kerja',
        'catatan',
        'bukti_izin',
    ];

    protected $casts = [
        'tanggal'          => 'date',
        'keterangan_kerja' => 'array',
    ];

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────

    /**
     * Jam kerja efektif tidak termasuk waktu istirahat.
     */
    public function getJamEfektifAttribute(): int
    {
        return max(0, $this->jam_kerja_standar ?? 8);
    }

    /**
     * Hitung durasi dari waktu datang sampai pulang, atau sampai saat ini jika masih bekerja.
     */
    public function getDurasiAktualMenitAttribute(): ?int
    {
        if (!$this->waktu_datang) {
            return null;
        }

        $timezone = config('app.timezone');
        $datang = Carbon::parse(
            $this->tanggal->format('Y-m-d') . ' ' . $this->waktu_datang,
            $timezone
        );
        $batasPulang = $datang->copy()->addHours(self::STANDARD_WORK_HOURS + self::BREAK_HOURS);

        if ($this->waktu_pulang) {
            $pulang = Carbon::parse(
                $this->tanggal->format('Y-m-d') . ' ' . $this->waktu_pulang,
                $timezone
            );
            if ($pulang->lt($datang)) {
                $pulang->addDay();
            }
        } else {
            $pulang = Carbon::now($timezone)->min($batasPulang);
        }

        return $pulang->greaterThan($datang)
            ? (int) $datang->diffInMinutes($pulang)
            : 0;
    }

    /**
     * Kembalikan keterangan kerja sebagai formatted string.
     */
    public function getKeteranganStringAttribute(): string
    {
        $list = $this->keterangan_kerja ?? [];
        if (empty($list)) return '-';
        $parts = [];
        foreach ($list as $i => $item) {
            $no     = $i + 1;
            $uraian = $item['uraian'] ?? '-';
            $parts[] = $no . '. ' . $uraian;
        }
        return implode('; ', $parts);
    }

    /**
     * Kembalikan keterangan kerja sebagai array untuk ditampilkan di tabel.
     */
    public function getKeteranganListAttribute(): array
    {
        return $this->keterangan_kerja ?? [];
    }

    // ─────────────────────────────────────────
    // Static Helper
    // ─────────────────────────────────────────

    /**
     * Ambil atau buat record absensi harian untuk user & tanggal tertentu.
     */
    public static function getOrCreate(int $userId, string $tanggal): static
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'tanggal' => $tanggal],
            [
                'status_datang'      => null,
                'waktu_datang'       => null,
                'status_pulang'      => null,
                'waktu_pulang'       => null,
                'jam_kerja_standar'  => 8,
                'jam_istirahat'      => 1,
                'keterangan_kerja'   => [],
            ]
        );
    }

    /**
     * Tambahkan entri keterangan kerja (tugas yang sedang/telah dikerjakan).
     * Akan update jika sudah ada entri dengan task_id & jenis yang sama.
     */
    public function addOrUpdateKeterangan(array $entry): void
    {
        $list = $this->keterangan_kerja ?? [];
        $found = false;
        foreach ($list as &$item) {
            if (
                isset($item['task_id'], $item['jenis']) &&
                $item['task_id'] === ($entry['task_id'] ?? null) &&
                $item['jenis'] === ($entry['jenis'] ?? null)
            ) {
                $item = array_merge($item, $entry);
                $found = true;
                break;
            }
        }
        unset($item);
        if (!$found) {
            $list[] = $entry;
        }
        $this->keterangan_kerja = $list;
        $this->saveQuietly();
    }

    /**
     * Set waktu datang jika belum diisi (hanya sekali per hari).
     */
    public function setWaktuDatang(string $waktu): void
    {
        if (empty($this->waktu_datang)) {
            $this->waktu_datang  = substr($waktu, 0, 5);
        }

        $this->jam_kerja_standar = self::STANDARD_WORK_HOURS;
        $this->jam_istirahat = self::BREAK_HOURS;
        if ($this->status_datang !== 'Hadir') {
            $this->status_datang = 'Hadir';
        }

        $this->saveQuietly();
    }

    /**
     * Close attendance once the standard work hours and break time have elapsed.
     */
    public function setWaktuPulang(string $waktu): void
    {
        if ($this->waktu_pulang) {
            return;
        }

        $this->status_datang = 'Hadir';
        $this->status_pulang = 'Hadir';
        $this->waktu_pulang  = substr($waktu, 0, 5);
        $this->saveQuietly();
    }

    /**
     * Automatically set checkout times for attendance records whose workday ended.
     */
    public static function closeCompletedWorkdays(?Carbon $now = null): int
    {
        $now ??= Carbon::now();
        $closed = 0;

        static::query()
            ->whereNotNull('waktu_datang')
            ->whereNull('waktu_pulang')
            ->orderBy('id')
            ->chunkById(200, function ($records) use ($now, &$closed): void {
                foreach ($records as $record) {
                    $arrival = Carbon::parse(
                        $record->tanggal->format('Y-m-d') . ' ' . $record->waktu_datang,
                        config('app.timezone')
                    );
                    $checkoutAt = $arrival->copy()->addHours(
                        self::STANDARD_WORK_HOURS + self::BREAK_HOURS
                    );

                    if ($now->lt($checkoutAt)) {
                        continue;
                    }

                    $record->setWaktuPulang($checkoutAt->format('H:i'));
                    $closed++;
                }
            });

        return $closed;
    }
}
