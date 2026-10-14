<?php

namespace App\Helpers;

use Carbon\Carbon;

class KinerjaWaktuHelper
{
    /**
     * Format target durasi dalam teks yang mudah dibaca (misal: "2 hari 4 jam", "1 hari", "5 jam").
     */
    public static function formatDurasiTarget(?int $hari, ?int $jam): ?string
    {
        $hari = (int) ($hari ?? 0);
        $jam = (int) ($jam ?? 0);

        if ($hari <= 0 && $jam <= 0) {
            return null;
        }

        $parts = [];
        if ($hari > 0) {
            $parts[] = $hari . ' hari';
        }
        if ($jam > 0) {
            $parts[] = $jam . ' jam';
        }

        return implode(' ', $parts);
    }

    /**
     * Hitung total detik dari durasi target (hari & jam).
     */
    public static function hitungDetikTarget(?int $hari, ?int $jam): int
    {
        $h = max(0, (int) ($hari ?? 0));
        $j = max(0, (int) ($jam ?? 0));
        return ($h * 86400) + ($j * 3600);
    }

    /**
     * Hitung durasi realisasi pengerjaan dalam detik.
     */
    public static function hitungDetikRealisasi($item): int
    {
        $durasiDetik = 0;

        // 1. Jika waktu mulai & selesai ada dan bukan 00:00:00
        if (!empty($item->waktu_mulai) && !empty($item->waktu_selesai) && $item->waktu_selesai !== '00:00:00') {
            $tglM = !empty($item->tanggal_mulai) ? $item->tanggal_mulai : now()->format('Y-m-d');
            $tglS = !empty($item->tanggal_selesai) ? $item->tanggal_selesai : $tglM;
            $startTs = strtotime($tglM . ' ' . $item->waktu_mulai);
            $endTs = strtotime($tglS . ' ' . $item->waktu_selesai);
            if ($endTs > $startTs) {
                $durasiDetik = $endTs - $startTs;
            } else {
                $tDiff = strtotime($item->waktu_selesai) - strtotime($item->waktu_mulai);
                if ($tDiff > 0) {
                    $durasiDetik = $tDiff;
                }
            }
        }

        // 2. Gunakan durasi_detik jika ada dan belum terisi
        if ($durasiDetik <= 0 && !empty($item->durasi_detik) && $item->durasi_detik > 0) {
            $durasiDetik = (int) $item->durasi_detik;
        }

        // 3. Jika sedang berjalan / proses
        if ($durasiDetik <= 0 && in_array($item->status, ['Proses', 'Berjalan'])) {
            if (!empty($item->waktu_mulai)) {
                $tglM = !empty($item->tanggal_mulai) ? $item->tanggal_mulai : now()->format('Y-m-d');
                $diff = time() - strtotime($tglM . ' ' . $item->waktu_mulai);
                if ($diff > 0) {
                    $durasiDetik = $diff;
                }
            } elseif (!empty($item->last_started_at)) {
                $diff = now()->diffInSeconds($item->last_started_at);
                if ($diff > 0) {
                    $durasiDetik = (int) $diff + (int) ($item->durasi_detik ?? 0);
                }
            }
        }

        // 4. Fallback selisih tanggal jika waktu tidak ada
        if ($durasiDetik <= 0 && !empty($item->tanggal_mulai) && !empty($item->tanggal_selesai)) {
            $d1 = strtotime($item->tanggal_mulai);
            $d2 = strtotime($item->tanggal_selesai);
            if ($d2 >= $d1) {
                $days = floor(($d2 - $d1) / 86400) + 1;
                $durasiDetik = $days * 8 * 3600; // Asumsi hari kerja 8 jam
            }
        }

        return max(0, (int) $durasiDetik);
    }

    /**
     * Format detik menjadi teks yang rapi (hari, jam, menit).
     */
    public static function formatDetikKeTeks(int $detik): string
    {
        if ($detik <= 0) {
            return '-';
        }

        $days = floor($detik / 86400);
        $hours = floor(($detik % 86400) / 3600);
        $minutes = floor(($detik % 3600) / 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = $days . ' hari';
        }
        if ($hours > 0) {
            $parts[] = $hours . ' jam';
        }
        if ($minutes > 0 || empty($parts)) {
            $parts[] = max(1, $minutes) . ' menit';
        }

        return implode(' ', $parts);
    }

    /**
     * Format total durasi pengerjaan realisasi (dengan keterangan berjalan jika status Proses/Berjalan).
     */
    public static function formatDurasiRealisasi($item): ?string
    {
        $detik = self::hitungDetikRealisasi($item);
        if ($detik <= 0) {
            return null;
        }

        $isBerjalan = in_array($item->status, ['Proses', 'Berjalan']) && empty($item->waktu_selesai);
        $teks = self::formatDetikKeTeks($detik);

        return $isBerjalan ? $teks . ' (sedang berjalan)' : $teks;
    }

    /**
     * Evaluasi ketepatan waktu pengerjaan tugas (On Time, Lebih Cepat, Terlambat, dll).
     *
     * Menghitung berdasarkan:
     * 1. Estimasi tanggal & jam selesai (Deadline)
     * 2. Estimasi durasi pengerjaan hari/jam (Target Duration)
     * 3. Waktu & tanggal realisasi penyelesaian (Actual Finish)
     * 4. Realisasi durasi aktif yang dicapai (Actual Duration)
     */
    public static function evaluasiKetepatanWaktu($item): array
    {
        $status = $item->status ?? 'Belum Dimulai';
        $now = Carbon::now();

        // 1. Ambil target estimasi deadline
        $estTglSelesai = $item->estimasi_tanggal_selesai;
        $estJamSelesai = $item->estimasi_jam_selesai ?: '23:59:59';
        $estDeadline = null;
        if (!empty($estTglSelesai)) {
            try {
                $estDeadline = Carbon::parse($estTglSelesai . ' ' . $estJamSelesai);
            } catch (\Throwable $e) {}
        }

        // 2. Ambil target durasi (hari / jam)
        $targetHari = $item->estimasi_durasi_hari;
        $targetJam = $item->estimasi_durasi_jam;
        $targetDurasiDetik = self::hitungDetikTarget($targetHari, $targetJam);
        $targetDurasiStr = self::formatDurasiTarget($targetHari, $targetJam);

        // 3. Ambil realisasi durasi
        $realDurasiDetik = self::hitungDetikRealisasi($item);
        $realDurasiStr = self::formatDurasiRealisasi($item);

        // 4. Ambil waktu realisasi selesai
        $actTglSelesai = $item->tanggal_selesai;
        $actJamSelesai = (!empty($item->waktu_selesai) && $item->waktu_selesai !== '00:00:00') ? $item->waktu_selesai : null;
        $actFinish = null;
        if (!empty($actTglSelesai)) {
            try {
                $actFinish = Carbon::parse($actTglSelesai . ' ' . ($actJamSelesai ?: '23:59:59'));
            } catch (\Throwable $e) {}
        }

        // ====================================================================
        // KASUS A: TUGAS TELAH SELESAI
        // ====================================================================
        if ($status === 'Selesai') {
            $isLate = false;
            $isEarly = false;
            $isOnTime = false;
            $diffText = '';
            $alasan = [];

            // A1. Cek terhadap Deadline Tanggal & Jam Selesai
            if ($estDeadline) {
                $finishTime = $actFinish ?: ($item->updated_at ? Carbon::parse($item->updated_at) : null);
                if ($finishTime) {
                    $diffInMinutes = $finishTime->diffInMinutes($estDeadline, false); // Positif jika finish <= deadline, negatif jika finish > deadline
                    
                    if ($finishTime->gt($estDeadline)) {
                        $isLate = true;
                        $lateSec = $finishTime->diffInSeconds($estDeadline);
                        $lateTeks = self::formatDetikKeTeks((int) $lateSec);
                        $alasan[] = "Terlambat {$lateTeks} melewati batas estimasi deadline";
                    } else {
                        // Finish sebelum atau tepat pada deadline
                        $earlySec = $estDeadline->diffInSeconds($finishTime);
                        if ($earlySec >= 3600) { // Lebih cepat minimal 1 jam
                            $isEarly = true;
                            $earlyTeks = self::formatDetikKeTeks((int) $earlySec);
                            $alasan[] = "Lebih cepat {$earlyTeks} dari batas estimasi";
                        } else {
                            $isOnTime = true;
                            $alasan[] = "Selesai tepat waktu sesuai batas estimasi";
                        }
                    }
                } else {
                    $isOnTime = true;
                    $alasan[] = "Tuntas selesai";
                }
            }

            // A2. Cek terhadap Target Durasi Pengerjaan (Hari / Jam) jika ditentukan
            $sisaDetik = null;
            $sisaTeks = null;
            $sisaStatus = null; // 'sisa' (hemat), 'lebih' (over), 'pas'

            if ($targetDurasiDetik > 0 && $realDurasiDetik > 0) {
                if ($realDurasiDetik > $targetDurasiDetik) {
                    $overSec = $realDurasiDetik - $targetDurasiDetik;
                    $overTeks = self::formatDetikKeTeks((int) $overSec);
                    $sisaDetik = -$overSec;
                    $sisaTeks = "+{$overTeks}";
                    $sisaStatus = 'lebih';
                    // Toleransi 15 menit
                    if ($overSec > 900) {
                        $isLate = true;
                        $isEarly = false;
                        $isOnTime = false;
                        $alasan[] = "Waktu pengerjaan melebihi target durasi (+{$overTeks})";
                    }
                } elseif ($realDurasiDetik < $targetDurasiDetik) {
                    $savedSec = $targetDurasiDetik - $realDurasiDetik;
                    $savedTeks = self::formatDetikKeTeks((int) $savedSec);
                    $sisaDetik = $savedSec;
                    $sisaTeks = "Sisa {$savedTeks}";
                    $sisaStatus = 'sisa';
                    if (!$isLate && $savedSec >= 3600) {
                        $isEarly = true;
                        $alasan[] = "Selesai hemat durasi {$savedTeks}";
                    }
                } else {
                    $sisaDetik = 0;
                    $sisaTeks = "Tepat target";
                    $sisaStatus = 'pas';
                }
            } elseif ($estDeadline && $actFinish) {
                // Jika tidak ada target durasi hari/jam, hitung sisa dari deadline estimasi vs waktu selesai
                if ($actFinish->lt($estDeadline)) {
                    $diffSec = $estDeadline->diffInSeconds($actFinish);
                    $diffTeks = self::formatDetikKeTeks((int) $diffSec);
                    $sisaDetik = $diffSec;
                    $sisaTeks = "Sisa {$diffTeks}";
                    $sisaStatus = 'sisa';
                } elseif ($actFinish->gt($estDeadline)) {
                    $diffSec = $actFinish->diffInSeconds($estDeadline);
                    $diffTeks = self::formatDetikKeTeks((int) $diffSec);
                    $sisaDetik = -$diffSec;
                    $sisaTeks = "+{$diffTeks}";
                    $sisaStatus = 'lebih';
                }
            }

            // Tentukan predikat akhir ketepatan
            if ($isLate) {
                return [
                    'kategori' => 'terlambat',
                    'status' => 'terlambat',
                    'label' => 'Selesai Terlambat',
                    'sublabel' => 'Terlambat',
                    'badge_color' => 'danger',
                    'badge_html' => '<span class="badge bg-danger text-white px-2 py-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Selesai Terlambat</span>',
                    'keterangan' => implode(' & ', $alasan) ?: 'Melewati estimasi waktu pengerjaan',
                    'target_durasi' => $targetDurasiStr,
                    'real_durasi' => $realDurasiStr,
                    'sisa_waktu_teks' => $sisaTeks,
                    'sisa_waktu_status' => $sisaStatus,
                    'is_on_time' => false,
                    'is_terlambat' => true,
                    'is_lebih_cepat' => false,
                ];
            }

            if ($isEarly) {
                return [
                    'kategori' => 'lebih_cepat',
                    'status' => 'lebih_cepat',
                    'label' => 'Selesai Lebih Cepat',
                    'sublabel' => 'Lebih Cepat',
                    'badge_color' => 'info',
                    'badge_html' => '<span class="badge bg-info text-white px-2 py-1"><i class="bi bi-lightning-charge-fill me-1"></i>Lebih Cepat</span>',
                    'keterangan' => implode(' & ', $alasan) ?: 'Selesai lebih awal dari target estimasi',
                    'target_durasi' => $targetDurasiStr,
                    'real_durasi' => $realDurasiStr,
                    'sisa_waktu_teks' => $sisaTeks,
                    'sisa_waktu_status' => $sisaStatus,
                    'is_on_time' => true,
                    'is_terlambat' => false,
                    'is_lebih_cepat' => true,
                ];
            }

            if ($isOnTime || $estDeadline || $targetDurasiDetik > 0) {
                return [
                    'kategori' => 'on_time',
                    'status' => 'on_time',
                    'label' => 'Selesai Tepat Waktu',
                    'sublabel' => 'Tepat Waktu',
                    'badge_color' => 'success',
                    'badge_html' => '<span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Tepat Waktu</span>',
                    'keterangan' => implode(' & ', $alasan) ?: 'Tuntas tepat waktu sesuai target',
                    'target_durasi' => $targetDurasiStr,
                    'real_durasi' => $realDurasiStr,
                    'sisa_waktu_teks' => $sisaTeks,
                    'sisa_waktu_status' => $sisaStatus,
                    'is_on_time' => true,
                    'is_terlambat' => false,
                    'is_lebih_cepat' => false,
                ];
            }

            // Selesai tanpa estimasi
            return [
                'kategori' => 'selesai',
                'status' => 'selesai',
                'label' => 'Selesai',
                'sublabel' => 'Tuntas',
                'badge_color' => 'success',
                'badge_html' => '<span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check2-all me-1"></i>Selesai</span>',
                'keterangan' => 'Tuntas dikerjakan (tanpa estimasi spesifik)',
                'target_durasi' => null,
                'real_durasi' => $realDurasiStr,
                'sisa_waktu_teks' => null,
                'sisa_waktu_status' => null,
                'is_on_time' => true,
                'is_terlambat' => false,
                'is_lebih_cepat' => false,
            ];
        }

        // ====================================================================
        // KASUS B: TUGAS BELUM SELESAI (Proses / Di-pause / Belum Dimulai)
        // ====================================================================
        $isOverdue = false;
        $overdueText = '';

        if ($estDeadline && $now->gt($estDeadline)) {
            $isOverdue = true;
            $diffSec = $now->diffInSeconds($estDeadline);
            $overdueText = self::formatDetikKeTeks((int) $diffSec);
        } elseif ($targetDurasiDetik > 0 && $realDurasiDetik > $targetDurasiDetik) {
            $isOverdue = true;
            $diffSec = $realDurasiDetik - $targetDurasiDetik;
            $overdueText = self::formatDetikKeTeks((int) $diffSec);
        }

        if ($isOverdue) {
            return [
                'kategori' => 'terlambat',
                'status' => 'terlambat',
                'label' => 'Terlambat (Lewat Batas)',
                'sublabel' => 'Terlambat',
                'badge_color' => 'danger',
                'badge_html' => '<span class="badge bg-danger text-white px-2 py-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Terlambat ' . $overdueText . '</span>',
                'keterangan' => "Melewati estimasi waktu pengerjaan selama {$overdueText}",
                'target_durasi' => $targetDurasiStr,
                'real_durasi' => $realDurasiStr,
                'is_on_time' => false,
                'is_terlambat' => true,
                'is_lebih_cepat' => false,
            ];
        }

        if ($status === 'Di-pause') {
            return [
                'kategori' => 'paused',
                'status' => 'paused',
                'label' => 'Sedang Dijeda (Di-pause)',
                'sublabel' => 'Dijeda',
                'badge_color' => 'warning',
                'badge_html' => '<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-pause-circle-fill me-1"></i>Dijeda (Paused)</span>',
                'keterangan' => $item->alasan_pause ? "Dijeda: {$item->alasan_pause}" : 'Pekerjaan sedang dijeda sementara',
                'target_durasi' => $targetDurasiStr,
                'real_durasi' => $realDurasiStr,
                'is_on_time' => false,
                'is_terlambat' => false,
                'is_lebih_cepat' => false,
            ];
        }

        if ($status === 'Berjalan' || $status === 'Proses') {
            return [
                'kategori' => 'proses',
                'status' => 'proses',
                'label' => 'Sedang Proses (On Track)',
                'sublabel' => 'On Track',
                'badge_color' => 'primary',
                'badge_html' => '<span class="badge bg-primary text-white px-2 py-1"><i class="bi bi-play-circle me-1"></i>Sedang Proses (On Track)</span>',
                'keterangan' => 'Dalam proses pengerjaan dan masih dalam rentang waktu estimasi',
                'target_durasi' => $targetDurasiStr,
                'real_durasi' => $realDurasiStr,
                'is_on_time' => false,
                'is_terlambat' => false,
                'is_lebih_cepat' => false,
            ];
        }

        // Belum Dimulai
        $menungguJadwal = false;
        if (!empty($item->estimasi_tanggal_mulai)) {
            try {
                $startEst = Carbon::parse($item->estimasi_tanggal_mulai . ' ' . ($item->estimasi_jam_mulai ?: '00:00:00'));
                if ($now->lt($startEst)) {
                    $menungguJadwal = true;
                }
            } catch (\Throwable $e) {}
        }

        if ($menungguJadwal) {
            return [
                'kategori' => 'menunggu',
                'status' => 'menunggu',
                'label' => 'Menunggu Jadwal',
                'sublabel' => 'Terjadwal',
                'badge_color' => 'secondary',
                'badge_html' => '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><i class="bi bi-calendar-event me-1"></i>Menunggu Jadwal</span>',
                'keterangan' => 'Jadwal pelaksanaan belum tiba',
                'target_durasi' => $targetDurasiStr,
                'real_durasi' => null,
                'is_on_time' => false,
                'is_terlambat' => false,
                'is_lebih_cepat' => false,
            ];
        }

        return [
            'kategori' => 'belum_dimulai',
            'status' => 'belum_dimulai',
            'label' => 'Belum Dimulai',
            'sublabel' => 'Belum Mulai',
            'badge_color' => 'secondary',
            'badge_html' => '<span class="badge bg-secondary px-2 py-1 text-white"><i class="bi bi-x-circle me-1"></i>Belum Dimulai</span>',
            'keterangan' => 'Tugas belum mulai dikerjakan oleh pegawai',
            'target_durasi' => $targetDurasiStr,
            'real_durasi' => null,
            'is_on_time' => false,
            'is_terlambat' => false,
            'is_lebih_cepat' => false,
        ];
    }
}
