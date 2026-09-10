<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class AbsensiSetting
{
    /**
     * Path to the JSON persistence file.
     */
    protected static function getFilePath(): string
    {
        return storage_path('app/absensi_settings.json');
    }

    /**
     * Default attendance session configurations.
     */
    public static function defaults(): array
    {
        return [
            'absensi-pkkmb-pertama' => [
                'slug' => 'absensi-pkkmb-pertama',
                'kategori' => 'PKKMB',
                'nama' => 'Absensi PKKMB Hari Pertama',
                'is_active' => true,
                'keterangan' => 'Sesi absensi PKKMB hari ke-1',
            ],
            'absensi-pkkmb-kedua' => [
                'slug' => 'absensi-pkkmb-kedua',
                'kategori' => 'PKKMB',
                'nama' => 'Absensi PKKMB Hari Kedua',
                'is_active' => true,
                'keterangan' => 'Sesi absensi PKKMB hari ke-2',
            ],
            'absensi-pkkmb-ketiga' => [
                'slug' => 'absensi-pkkmb-ketiga',
                'kategori' => 'PKKMB',
                'nama' => 'Absensi PKKMB Hari Ketiga',
                'is_active' => true,
                'keterangan' => 'Sesi absensi PKKMB hari ke-3',
            ],
            'absensi-esq-pertama' => [
                'slug' => 'absensi-esq-pertama',
                'kategori' => 'ESQ',
                'nama' => 'Absensi ESQ Hari Pertama',
                'is_active' => true,
                'keterangan' => 'Sesi absensi ESQ hari ke-1',
            ],
            'absensi-esq-kedua' => [
                'slug' => 'absensi-esq-kedua',
                'kategori' => 'ESQ',
                'nama' => 'Absensi ESQ Hari Kedua',
                'is_active' => true,
                'keterangan' => 'Sesi absensi ESQ hari ke-2',
            ],
            'absensi-esq-ketiga' => [
                'slug' => 'absensi-esq-ketiga',
                'kategori' => 'ESQ',
                'nama' => 'Absensi ESQ Hari Ketiga',
                'is_active' => true,
                'keterangan' => 'Sesi absensi ESQ hari ke-3',
            ],
            'absensi-esq-keempat' => [
                'slug' => 'absensi-esq-keempat',
                'kategori' => 'ESQ',
                'nama' => 'Absensi ESQ Hari Keempat',
                'is_active' => true,
                'keterangan' => 'Sesi absensi ESQ hari ke-4',
            ],
            'absensi-milad-pertama' => [
                'slug' => 'absensi-milad-pertama',
                'kategori' => 'MILAD',
                'nama' => 'Absensi MILAD Hari Pertama',
                'is_active' => true,
                'keterangan' => 'Sesi absensi MILAD hari ke-1',
            ],
            'absensi-milad-kedua' => [
                'slug' => 'absensi-milad-kedua',
                'kategori' => 'MILAD',
                'nama' => 'Absensi MILAD Hari Kedua',
                'is_active' => true,
                'keterangan' => 'Sesi absensi MILAD hari ke-2',
            ],
            'absensi-kuliah-umum-pertama' => [
                'slug' => 'absensi-kuliah-umum-pertama',
                'kategori' => 'Kuliah Umum',
                'nama' => 'Absensi Kuliah Umum Hari Pertama',
                'is_active' => true,
                'keterangan' => 'Sesi absensi Kuliah Umum hari ke-1',
            ],
        ];
    }

    /**
     * Retrieve all attendance session settings as objects from database.
     */
    public static function all(): Collection
    {
        try {
            $dbSessions = \App\Models\KepanitiaanSesiAbsensi::with('kepanitiaan')->get();
            if ($dbSessions->isNotEmpty()) {
                return $dbSessions->map(function ($s) {
                    $kategori = 'Kepanitiaan';
                    if ($s->kepanitiaan && !empty($s->kepanitiaan->uraian_tugas)) {
                        $firstLine = trim(explode("\n", (string)$s->kepanitiaan->uraian_tugas)[0]);
                        $kategori = \Illuminate\Support\Str::limit($firstLine, 45);
                    } elseif (!empty($s->legacy_slug)) {
                        if (str_contains($s->legacy_slug, 'pkkmb')) $kategori = 'PKKMB';
                        elseif (str_contains($s->legacy_slug, 'esq')) $kategori = 'ESQ';
                        elseif (str_contains($s->legacy_slug, 'milad')) $kategori = 'MILAD';
                        elseif (str_contains($s->legacy_slug, 'kuliah-umum')) $kategori = 'Kuliah Umum';
                    }

                    return (object) [
                        'id' => $s->id,
                        'token' => $s->token,
                        'slug' => $s->legacy_slug ?: ('sesi-' . $s->id),
                        'kategori' => $kategori,
                        'nama' => $s->nama_sesi,
                        'is_active' => (bool) $s->is_active,
                        'keterangan' => 'Tanggal: ' . ($s->tanggal ? date('d/m/Y', strtotime($s->tanggal)) : '-') . ' | Token: ' . $s->token,
                    ];
                });
            }
        } catch (\Exception $e) {
            // fallback
        }

        $filePath = self::getFilePath();
        if (!File::exists($filePath)) {
            $defaults = self::defaults();
            File::ensureDirectoryExists(dirname($filePath));
            File::put($filePath, json_encode($defaults, JSON_PRETTY_PRINT));
            $data = $defaults;
        } else {
            $content = File::get($filePath);
            $data = json_decode($content, true);
            if (!is_array($data)) {
                $data = self::defaults();
                File::put($filePath, json_encode($data, JSON_PRETTY_PRINT));
            }
        }

        return collect($data)->map(function ($item) {
            return (object) [
                'id' => null,
                'token' => null,
                'slug' => $item['slug'],
                'kategori' => $item['kategori'],
                'nama' => $item['nama'],
                'is_active' => (bool) ($item['is_active'] ?? true),
                'keterangan' => $item['keterangan'] ?? null,
            ];
        });
    }

    /**
     * Retrieve all settings grouped by category.
     */
    public static function getAllGrouped(): Collection
    {
        return self::all()->groupBy('kategori');
    }

    /**
     * Check if a specific attendance session is active.
     */
    public static function isActive(string $slug): bool
    {
        $session = \App\Models\KepanitiaanSesiAbsensi::where('legacy_slug', $slug)
            ->orWhere('token', $slug)
            ->first();

        if ($session) {
            return (bool) $session->is_active;
        }

        $statuses = self::getAllStatuses();
        return $statuses[$slug] ?? true;
    }

    /**
     * Set active status directly.
     */
    public static function setStatus(string $slug, bool $isActive): void
    {
        $session = \App\Models\KepanitiaanSesiAbsensi::where('legacy_slug', $slug)
            ->orWhere('token', $slug)
            ->first();

        if ($session) {
            $session->is_active = $isActive;
            $session->save();
        }

        $filePath = self::getFilePath();
        if (File::exists($filePath)) {
            $content = File::get($filePath);
            $data = json_decode($content, true) ?: [];
            if (isset($data[$slug])) {
                $data[$slug]['is_active'] = $isActive;
                File::put($filePath, json_encode($data, JSON_PRETTY_PRINT));
            }
        }

        self::clearCache();
    }

    /**
     * Get associative array of slug => is_active.
     */
    public static function getAllStatuses(): array
    {
        return self::all()->pluck('is_active', 'slug')->toArray();
    }

    /**
     * Toggle the active status of a session.
     */
    public static function toggle(string $slug): object
    {
        $session = \App\Models\KepanitiaanSesiAbsensi::where('legacy_slug', $slug)
            ->orWhere('token', $slug)
            ->first();

        if (!$session && str_starts_with($slug, 'sesi-')) {
            $id = (int) str_replace('sesi-', '', $slug);
            $session = \App\Models\KepanitiaanSesiAbsensi::find($id);
        }

        if ($session) {
            $session->is_active = !$session->is_active;
            $session->save();

            self::clearCache();

            return (object) [
                'id' => $session->id,
                'token' => $session->token,
                'slug' => $session->legacy_slug ?: ('sesi-' . $session->id),
                'nama' => $session->nama_sesi,
                'is_active' => (bool) $session->is_active,
            ];
        }

        $filePath = self::getFilePath();
        $items = self::all()->keyBy('slug')->toArray();

        if (!isset($items[$slug])) {
            abort(404, "Sesi absensi dengan slug {$slug} tidak ditemukan.");
        }

        $items[$slug]->is_active = !$items[$slug]->is_active;

        $saveData = [];
        foreach ($items as $k => $obj) {
            $saveData[$k] = (array) $obj;
        }

        File::ensureDirectoryExists(dirname($filePath));
        File::put($filePath, json_encode($saveData, JSON_PRETTY_PRINT));

        self::clearCache();

        return (object) $saveData[$slug];
    }

    /**
     * Clear cached attendance statuses.
     */
    public static function clearCache(): void
    {
        Cache::forget('absensi_settings_statuses');
    }

    /**
     * Get the Eloquent model class for a specific attendance slug (Deprecated - now using KepanitiaanPresensi).
     */
    public static function getModelClass(?string $slug): ?string
    {
        return null;
    }

    /**
     * Render HTML attendance badges for an attendance record.
     */
    public static function renderAttendanceBadges($att): string
    {
        if (!$att) {
            return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 0.73rem; font-weight: 500;"><i class="bi bi-dash-circle me-1"></i>Belum Absen</span>';
        }

        $badges = [];

        // Datang
        $hadirDatang = $att->hadir_datang;
        $waktuDatang = $att->waktu_datang ? substr($att->waktu_datang, 0, 5) : '';
        if ($hadirDatang === 'Hadir') {
            $badges[] = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.73rem; font-weight: 600;" title="Absensi Datang tercatat"><i class="bi bi-box-arrow-in-right me-1"></i>Datang: ' . ($waktuDatang ? $waktuDatang . ' WIB' : 'Hadir') . '</span>';
        } elseif ($hadirDatang === 'Izin') {
            $badges[] = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size: 0.73rem; font-weight: 600;"><i class="bi bi-info-circle me-1"></i>Izin Datang</span>';
        } elseif ($hadirDatang === 'Sakit') {
            $badges[] = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 0.73rem; font-weight: 600;"><i class="bi bi-heart-pulse me-1"></i>Sakit</span>';
        } elseif ($hadirDatang === 'Alpha') {
            $badges[] = '<span class="badge bg-danger text-white px-2 py-1" style="font-size: 0.73rem; font-weight: 600;"><i class="bi bi-x-circle me-1"></i>Alpha</span>';
        } else {
            $badges[] = '<span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.73rem;"><i class="bi bi-clock me-1"></i>Belum Datang</span>';
        }

        // Pulang
        $hadirPulang = $att->hadir_pulang;
        $waktuPulang = $att->waktu_pulang ? substr($att->waktu_pulang, 0, 5) : '';
        if ($hadirPulang === 'Hadir') {
            $badges[] = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.73rem; font-weight: 600;" title="Absensi Pulang tercatat"><i class="bi bi-box-arrow-right me-1"></i>Pulang: ' . ($waktuPulang ? $waktuPulang . ' WIB' : 'Hadir') . '</span>';
        } elseif ($hadirPulang === 'Izin') {
            $badges[] = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size: 0.73rem; font-weight: 600;"><i class="bi bi-info-circle me-1"></i>Izin Pulang</span>';
        } elseif ($hadirDatang === 'Hadir' && empty($hadirPulang)) {
            $badges[] = '<span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.73rem;"><i class="bi bi-clock me-1"></i>Belum Pulang</span>';
        }

        return implode(' ', $badges);
    }

    /**
     * Render HTML attendance table card for the logged-in user.
     */
    public static function renderAttendanceTable(?array $absensiInfo, $myAtt): string
    {
        if (!$absensiInfo) return '';

        $datangStatus = $myAtt && $myAtt->hadir_datang ? '<span class="badge bg-success text-white px-2.5 py-1" style="font-size: 0.76rem; font-weight: 600;">' . e($myAtt->hadir_datang) . '</span>' : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-0.5" style="font-size: 0.72rem;">Belum Absen</span>';
        $datangWaktu = $myAtt && $myAtt->waktu_datang ? '<span class="fw-bold text-dark" style="font-size: 0.82rem;">' . substr($myAtt->waktu_datang, 0, 5) . ' <small class="text-muted fw-normal">WIB</small></span>' : '<span class="text-muted">-</span>';
        $datangCatatan = $myAtt && $myAtt->catatan_hadir_datang ? '<span class="text-secondary small">' . e($myAtt->catatan_hadir_datang) . '</span>' : '<span class="text-muted">-</span>';

        $pulangStatus = $myAtt && $myAtt->hadir_pulang ? '<span class="badge bg-primary text-white px-2.5 py-1" style="font-size: 0.76rem; font-weight: 600;">' . e($myAtt->hadir_pulang) . '</span>' : ($myAtt && $myAtt->hadir_datang ? '<span class="badge bg-light text-muted border px-2 py-0.5" style="font-size: 0.72rem;">Belum Pulang</span>' : '<span class="text-muted">-</span>');
        $pulangWaktu = $myAtt && $myAtt->waktu_pulang ? '<span class="fw-bold text-dark" style="font-size: 0.82rem;">' . substr($myAtt->waktu_pulang, 0, 5) . ' <small class="text-muted fw-normal">WIB</small></span>' : '<span class="text-muted">-</span>';
        $pulangCatatan = $myAtt && $myAtt->catatan_hadir_pulang ? '<span class="text-secondary small">' . e($myAtt->catatan_hadir_pulang) . '</span>' : '<span class="text-muted">-</span>';

        $buktiIzin = '<span class="badge bg-secondary text-white px-2 py-0.5" style="font-size: 0.72rem;">Tidak ada</span>';
        if ($myAtt && !empty($myAtt->bukti_izin)) {
            $buktiIzin = '<a href="' . asset('storage/' . $myAtt->bukti_izin) . '" target="_blank" class="badge bg-primary text-white text-decoration-none px-2 py-0.5" style="font-size: 0.72rem;"><i class="bi bi-file-earmark me-0.5"></i>Lihat</a>';
        }

        $isActive = !empty($absensiInfo['is_active']);
        $statusBadge = $isActive 
            ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.72rem;"><i class="bi bi-check-circle-fill me-1"></i>Sesi Aktif</span>'
            : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.72rem;"><i class="bi bi-lock-fill me-1"></i>Nonaktif (Tutup)</span>';

        $sesiId = $absensiInfo['id'] ?? null;
        if (!$sesiId && !empty($absensiInfo['slug'])) {
            $dbSesi = \App\Models\KepanitiaanSesiAbsensi::where('legacy_slug', $absensiInfo['slug'])->first();
            if ($dbSesi) {
                $sesiId = $dbSesi->id;
            }
        }

        $actionsHtml = '';
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        $isAdmin = $currentUser && ($currentUser->isAdmin() || $currentUser->isSuperAdmin());

        if ($sesiId && $isAdmin) {
            $exportUrl = route('presensi.sesi.export', $sesiId);
            $detailUrl = route('presensi.sesi.show', $sesiId);
            $safeNama = htmlspecialchars(addslashes($absensiInfo['nama'] ?? 'Sesi Presensi'), ENT_QUOTES, 'UTF-8');
            $actionsHtml = '
            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                <a href="' . $exportUrl . '" class="btn btn-sm btn-success text-white rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center shadow-xs" style="font-size: 0.8rem;" title="Download Rekap Absensi Excel">
                    <i class="bi bi-file-earmark-excel-fill" style="margin-right: 6px;"></i><span>Export Excel</span>
                </a>
                <button type="button" class="btn btn-sm btn-primary text-white rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center shadow-xs" onclick="window.openPresensiMultiSelectModal(' . $sesiId . ', \'' . $safeNama . '\')" title="Kelola Presensi & Multi-Select Pegawai">
                    <i class="bi bi-check2-square" style="margin-right: 6px;"></i><span>Multi-Select Presensi</span>
                </button>
                <a href="' . $detailUrl . '" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 d-inline-flex align-items-center shadow-xs" style="font-size: 0.8rem;" title="Buka Halaman Presensi Lengkap">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </div>';
        }

        $thStyle = 'background-color: #f1f5f9 !important; color: #1e293b !important; font-weight: 700 !important; font-size: 0.73rem !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; padding: 9px 10px !important; border: 1px solid #cbd5e1 !important; text-align: center !important; vertical-align: middle !important; white-space: nowrap !important; display: table-cell !important;';
        $tdStyle = 'padding: 9px 8px !important; border: 1px solid #e2e8f0 !important; text-align: center !important; vertical-align: middle !important; background-color: #ffffff !important; display: table-cell !important;';

        $html = '<div class="mt-2 p-3 bg-white rounded border shadow-sm d-flex flex-column gap-2" style="border-left: 4px solid #198754 !important; word-break: break-word; overflow-wrap: break-word;">';
        $html .= '  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom pb-2">';
        $html .= '    <span class="text-dark small fw-bold d-inline-flex align-items-center flex-wrap gap-1.5"><i class="bi bi-card-checklist text-success fs-6 me-1" style="margin-right: 6px !important;"></i>Status Kehadiran Absensi: <span class="text-success fw-bold">' . e($absensiInfo['nama']) . '</span> ' . $statusBadge . '</span>';
        $html .= $actionsHtml;
        $html .= '  </div>';

        $html .= '  <div class="table-responsive" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch;">';
        $html .= '    <table class="table table-sm table-bordered align-middle text-center mb-0 table-attendance-status" style="width: 100% !important; min-width: 650px !important; border-collapse: collapse !important; display: table !important; margin: 0 !important;">';
        $html .= '      <thead style="display: table-header-group !important;">';
        $html .= '        <tr style="display: table-row !important;">';
        $html .= '          <th style="' . $thStyle . ' min-width: 110px;"><i class="bi bi-box-arrow-in-right text-success me-2" style="margin-right: 6px !important;"></i>HADIR DATANG</th>';
        $html .= '          <th style="' . $thStyle . ' min-width: 95px;"><i class="bi bi-clock text-success me-2" style="margin-right: 6px !important;"></i>WAKTU DATANG</th>';
        $html .= '          <th style="' . $thStyle . ' min-width: 120px;"><i class="bi bi-chat-left-text text-muted me-2" style="margin-right: 6px !important;"></i>CATATAN DATANG</th>';
        $html .= '          <th style="' . $thStyle . ' min-width: 110px;"><i class="bi bi-box-arrow-right text-primary me-2" style="margin-right: 6px !important;"></i>HADIR PULANG</th>';
        $html .= '          <th style="' . $thStyle . ' min-width: 95px;"><i class="bi bi-clock text-primary me-2" style="margin-right: 6px !important;"></i>WAKTU PULANG</th>';
        $html .= '          <th style="' . $thStyle . ' min-width: 120px;"><i class="bi bi-chat-left-text text-muted me-2" style="margin-right: 6px !important;"></i>CATATAN PULANG</th>';
        $html .= '          <th style="' . $thStyle . ' min-width: 90px;"><i class="bi bi-paperclip text-secondary me-2" style="margin-right: 6px !important;"></i>BUKTI IZIN</th>';
        $html .= '        </tr>';
        $html .= '      </thead>';
        $html .= '      <tbody style="display: table-row-group !important;">';
        $html .= '        <tr style="display: table-row !important;">';
        $html .= '          <td style="' . $tdStyle . '">' . $datangStatus . '</td>';
        $html .= '          <td style="' . $tdStyle . '">' . $datangWaktu . '</td>';
        $html .= '          <td style="' . $tdStyle . '">' . $datangCatatan . '</td>';
        $html .= '          <td style="' . $tdStyle . '">' . $pulangStatus . '</td>';
        $html .= '          <td style="' . $tdStyle . '">' . $pulangWaktu . '</td>';
        $html .= '          <td style="' . $tdStyle . '">' . $pulangCatatan . '</td>';
        $html .= '          <td style="' . $tdStyle . '">' . $buktiIzin . '</td>';
        $html .= '        </tr>';
        $html .= '      </tbody>';
        $html .= '    </table>';
        $html .= '  </div>';
        $html .= '</div>';

        return $html;
    }
}
