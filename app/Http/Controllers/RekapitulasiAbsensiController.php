<?php

namespace App\Http\Controllers;

use App\Models\AbsensiEsqKedua;
use App\Models\AbsensiEsqKetiga;
use App\Models\AbsensiEsqPertama;
use App\Models\AbsensiKuliahUmumKedua;
use App\Models\AbsensiKuliahUmumKetiga;
use App\Models\AbsensiKuliahUmumPertama;
use App\Models\AbsensiMiladKedua;
use App\Models\AbsensiMiladKetiga;
use App\Models\AbsensiMiladPertama;
use App\Models\AbsensiPkkmbKedua;
use App\Models\AbsensiPkkmbKetiga;
use App\Models\AbsensiPkkmbPertama;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class RekapitulasiAbsensiController extends Controller
{
    /**
     * Helper to resolve attendance model based on kepanitiaan and day
     */
    private function getAttendanceModel(string $kepanitiaan, int $day): string
    {
        return match ($kepanitiaan) {
            'pkkmb' => match ($day) {
                2 => AbsensiPkkmbKedua::class,
                3 => AbsensiPkkmbKetiga::class,
                default => AbsensiPkkmbPertama::class,
            },
            'esq' => match ($day) {
                2 => AbsensiEsqKedua::class,
                3 => AbsensiEsqKetiga::class,
                default => AbsensiEsqPertama::class,
            },
            'milad' => match ($day) {
                2 => AbsensiMiladKedua::class,
                3 => AbsensiMiladKetiga::class,
                default => AbsensiMiladPertama::class,
            },
            'kuliah_umum' => match ($day) {
                2 => AbsensiKuliahUmumKedua::class,
                3 => AbsensiKuliahUmumKetiga::class,
                default => AbsensiKuliahUmumPertama::class,
            },
            default => AbsensiPkkmbPertama::class,
        };
    }

    /**
     * Helper to get target user query based on current user role
     */
    private function getBaseUsersQuery()
    {
        $authUser = auth()->user();
        $canViewAll = false;

        if ($authUser) {
            $isSuperAdmin = $authUser->isSuperAdmin();
            $isAdmin = $authUser->isAdmin();
            $isRektor = $authUser->hasRole('REKTOR') 
                     || $authUser->hasRole('rektor') 
                     || (trim(strtoupper($authUser->jabatan ?? '')) === 'REKTOR');

            $canViewAll = $isSuperAdmin || $isAdmin || $isRektor;
        }

        if ($canViewAll) {
            $excludedUserIds = User::all()
                ->filter(fn($u) => $u->isAdmin() || $u->isSuperAdmin())
                ->pluck('id');

            return User::select('id', 'name', 'nidn', 'unit', 'jabatan', 'email')
                ->whereNotIn('id', $excludedUserIds);
        }

        return User::select('id', 'name', 'nidn', 'unit', 'jabatan', 'email')
            ->where('id', $authUser ? $authUser->id : 0);
    }

    /**
     * Display the Rekapitulasi Absensi page.
     */
    public function index(Request $request)
    {
        $kepanitiaan = $request->query('kepanitiaan', 'pkkmb');
        $day = (int) $request->query('day', 1);

        return view('pages.rekapitulasi-absensi.index', compact('kepanitiaan', 'day'));
    }

    /**
     * Helper to format badge status
     */
    private function formatStatusBadge($status, $waktu = null, $prefix = '')
    {
        if (empty($status)) {
            return '<span class="badge bg-light text-muted border px-2 py-1 small">' . $prefix . 'Belum</span>';
        }

        $style = match ($status) {
            'Hadir' => 'background-color: #198754; color: white;',
            'Izin' => 'background-color: #ffc107; color: #000;',
            'Sakit' => 'background-color: #0dcaf0; color: #fff;',
            'Alpa' => 'background-color: #dc3545; color: white;',
            default => 'background-color: #6c757d; color: white;',
        };

        $timeStr = $waktu ? ' (' . substr($waktu, 0, 5) . ')' : '';
        return '<span class="badge rounded px-2 py-1 fw-bold" style="' . $style . '">' . $prefix . e($status) . $timeStr . '</span>';
    }

    /**
     * Get DataTables data for users who HAVE attended.
     */
    public function getDataSudah(Request $request)
    {
        $kepanitiaan = $request->query('kepanitiaan', 'pkkmb');
        $day = (int) $request->query('day', 1);

        if ($day === 0) {
            // Mode 3 Hari Sekaligus
            $m1 = $this->getAttendanceModel($kepanitiaan, 1);
            $m2 = $this->getAttendanceModel($kepanitiaan, 2);
            $m3 = $this->getAttendanceModel($kepanitiaan, 3);

            $att1 = $m1::all()->keyBy('user_id');
            $att2 = $m2::all()->keyBy('user_id');
            $att3 = $m3::all()->keyBy('user_id');

            $allAttendedIds = array_unique(array_merge(
                $att1->filter(fn($a) => !empty($a->hadir_datang) || !empty($a->hadir_pulang))->keys()->toArray(),
                $att2->filter(fn($a) => !empty($a->hadir_datang) || !empty($a->hadir_pulang))->keys()->toArray(),
                $att3->filter(fn($a) => !empty($a->hadir_datang) || !empty($a->hadir_pulang))->keys()->toArray()
            ));

            $usersQuery = $this->getBaseUsersQuery()->whereIn('id', $allAttendedIds);

            return DataTables::of($usersQuery)
                ->addIndexColumn()
                ->editColumn('name', function ($user) {
                    return '<div class="fw-bold text-dark" style="font-size: 0.95rem;">' . e($user->name) . '</div>' .
                           '<small class="text-muted"><i class="bi bi-person-vcard me-1"></i>NUP: ' . e($user->nidn ?: '-') . '</small>';
                })
                ->addColumn('unit_jabatan', function ($user) {
                    return '<div><span class="badge bg-light text-dark border me-1">' . e($user->unit ?: '-') . '</span></div>' .
                           '<small class="text-muted">' . e($user->jabatan ?: '-') . '</small>';
                })
                ->addColumn('absen_datang', function ($user) use ($att1, $att2, $att3) {
                    $d1 = $att1->get($user->id);
                    $d2 = $att2->get($user->id);
                    $d3 = $att3->get($user->id);

                    $h1 = $d1 && $d1->hadir_datang ? $this->formatStatusBadge($d1->hadir_datang, $d1->waktu_datang, 'H1: ') : '<span class="badge bg-light text-muted border">H1: -</span>';
                    $h2 = $d2 && $d2->hadir_datang ? $this->formatStatusBadge($d2->hadir_datang, $d2->waktu_datang, 'H2: ') : '<span class="badge bg-light text-muted border">H2: -</span>';
                    $h3 = $d3 && $d3->hadir_datang ? $this->formatStatusBadge($d3->hadir_datang, $d3->waktu_datang, 'H3: ') : '<span class="badge bg-light text-muted border">H3: -</span>';

                    return '<div class="d-flex flex-wrap gap-1">' . $h1 . $h2 . $h3 . '</div>';
                })
                ->addColumn('absen_pulang', function ($user) use ($att1, $att2, $att3) {
                    $d1 = $att1->get($user->id);
                    $d2 = $att2->get($user->id);
                    $d3 = $att3->get($user->id);

                    $h1 = $d1 && $d1->hadir_pulang ? $this->formatStatusBadge($d1->hadir_pulang, $d1->waktu_pulang, 'H1: ') : '<span class="badge bg-light text-muted border">H1: -</span>';
                    $h2 = $d2 && $d2->hadir_pulang ? $this->formatStatusBadge($d2->hadir_pulang, $d2->waktu_pulang, 'H2: ') : '<span class="badge bg-light text-muted border">H2: -</span>';
                    $h3 = $d3 && $d3->hadir_pulang ? $this->formatStatusBadge($d3->hadir_pulang, $d3->waktu_pulang, 'H3: ') : '<span class="badge bg-light text-muted border">H3: -</span>';

                    return '<div class="d-flex flex-wrap gap-1">' . $h1 . $h2 . $h3 . '</div>';
                })
                ->addColumn('bukti_izin', function ($user) use ($att1, $att2, $att3) {
                    $links = [];
                    foreach ([1 => $att1, 2 => $att2, 3 => $att3] as $dNum => $attSet) {
                        $att = $attSet->get($user->id);
                        if ($att && !empty($att->bukti_izin)) {
                            $links[] = '<a href="' . Storage::url($att->bukti_izin) . '" target="_blank" class="badge bg-primary-subtle text-primary border text-decoration-none">H' . $dNum . ' Bukti</a>';
                        }
                    }
                    return !empty($links) ? implode(' ', $links) : '<span class="text-muted small">-</span>';
                })
                ->rawColumns(['name', 'unit_jabatan', 'absen_datang', 'absen_pulang', 'bukti_izin'])
                ->make(true);
        }

        // Mode Single Day (1, 2, atau 3)
        $modelClass = $this->getAttendanceModel($kepanitiaan, $day);

        $attendances = $modelClass::where(function ($q) {
            $q->whereNotNull('hadir_datang')
              ->orWhereNotNull('hadir_pulang');
        })->get()->keyBy('user_id');

        $attendedUserIds = $attendances->keys()->toArray();

        $usersQuery = $this->getBaseUsersQuery()
            ->whereIn('id', $attendedUserIds);

        return DataTables::of($usersQuery)
            ->addIndexColumn()
            ->editColumn('name', function ($user) {
                return '<div class="fw-bold text-dark" style="font-size: 0.95rem;">' . e($user->name) . '</div>' .
                       '<small class="text-muted"><i class="bi bi-person-vcard me-1"></i>NUP: ' . e($user->nidn ?: '-') . '</small>';
            })
            ->addColumn('unit_jabatan', function ($user) {
                return '<div><span class="badge bg-light text-dark border me-1">' . e($user->unit ?: '-') . '</span></div>' .
                       '<small class="text-muted">' . e($user->jabatan ?: '-') . '</small>';
            })
            ->addColumn('absen_datang', function ($user) use ($attendances) {
                $att = $attendances->get($user->id);
                if (!$att || empty($att->hadir_datang)) {
                    return '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1">Belum Absen</span>';
                }

                $status = $att->hadir_datang;
                $waktu = $att->waktu_datang ? '<div class="small text-muted mt-1"><i class="bi bi-clock me-1"></i>' . substr($att->waktu_datang, 0, 5) . ' WIB</div>' : '';
                $catatan = $att->catatan_hadir_datang ? '<div class="small text-secondary fst-italic mt-1"><i class="bi bi-chat-left-text me-1"></i>' . e($att->catatan_hadir_datang) . '</div>' : '';

                $badgeClass = match ($status) {
                    'Hadir' => 'background-color: #198754; color: white;',
                    'Izin' => 'background-color: #ffc107; color: #000;',
                    'Sakit' => 'background-color: #0dcaf0; color: #fff;',
                    'Alpa' => 'background-color: #dc3545; color: white;',
                    default => 'background-color: #6c757d; color: white;',
                };

                return '<span class="badge rounded px-2 py-1 fw-bold" style="' . $badgeClass . '">' . e($status) . '</span>' . $waktu . $catatan;
            })
            ->addColumn('absen_pulang', function ($user) use ($attendances) {
                $att = $attendances->get($user->id);
                if (!$att || empty($att->hadir_pulang)) {
                    return '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1">Belum Pulang</span>';
                }

                $status = $att->hadir_pulang;
                $waktu = $att->waktu_pulang ? '<div class="small text-muted mt-1"><i class="bi bi-clock me-1"></i>' . substr($att->waktu_pulang, 0, 5) . ' WIB</div>' : '';
                $catatan = $att->catatan_hadir_pulang ? '<div class="small text-secondary fst-italic mt-1"><i class="bi bi-chat-left-text me-1"></i>' . e($att->catatan_hadir_pulang) . '</div>' : '';

                $badgeClass = match ($status) {
                    'Hadir' => 'background-color: #198754; color: white;',
                    'Izin' => 'background-color: #ffc107; color: #000;',
                    'Sakit' => 'background-color: #0dcaf0; color: #fff;',
                    'Alpa' => 'background-color: #dc3545; color: white;',
                    default => 'background-color: #6c757d; color: white;',
                };

                return '<span class="badge rounded px-2 py-1 fw-bold" style="' . $badgeClass . '">' . e($status) . '</span>' . $waktu . $catatan;
            })
            ->addColumn('bukti_izin', function ($user) use ($attendances) {
                $att = $attendances->get($user->id);
                if ($att && !empty($att->bukti_izin)) {
                    $url = Storage::url($att->bukti_izin);
                    return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"><i class="bi bi-file-earmark-arrow-down"></i> Lihat Bukti</a>';
                }
                return '<span class="text-muted small">-</span>';
            })
            ->rawColumns(['name', 'unit_jabatan', 'absen_datang', 'absen_pulang', 'bukti_izin'])
            ->make(true);
    }

    /**
     * Get DataTables data for users who HAVE NOT attended.
     */
    public function getDataBelum(Request $request)
    {
        $kepanitiaan = $request->query('kepanitiaan', 'pkkmb');
        $day = (int) $request->query('day', 1);

        if ($day === 0) {
            // Mode 3 Hari Sekaligus: Belum absen sama sekali di 3 hari
            $m1 = $this->getAttendanceModel($kepanitiaan, 1);
            $m2 = $this->getAttendanceModel($kepanitiaan, 2);
            $m3 = $this->getAttendanceModel($kepanitiaan, 3);

            $att1 = $m1::all()->keyBy('user_id');
            $att2 = $m2::all()->keyBy('user_id');
            $att3 = $m3::all()->keyBy('user_id');

            $allAttendedIds = array_unique(array_merge(
                $att1->filter(fn($a) => !empty($a->hadir_datang) || !empty($a->hadir_pulang))->keys()->toArray(),
                $att2->filter(fn($a) => !empty($a->hadir_datang) || !empty($a->hadir_pulang))->keys()->toArray(),
                $att3->filter(fn($a) => !empty($a->hadir_datang) || !empty($a->hadir_pulang))->keys()->toArray()
            ));

            $usersQuery = $this->getBaseUsersQuery()->whereNotIn('id', $allAttendedIds);

            return DataTables::of($usersQuery)
                ->addIndexColumn()
                ->editColumn('name', function ($user) {
                    return '<div class="fw-bold text-dark" style="font-size: 0.95rem;">' . e($user->name) . '</div>' .
                           '<small class="text-muted"><i class="bi bi-envelope me-1"></i>' . e($user->email ?: '-') . '</small>';
                })
                ->editColumn('nidn', function ($user) {
                    return '<span class="badge bg-light text-dark border">' . e($user->nidn ?: '-') . '</span>';
                })
                ->editColumn('unit', function ($user) {
                    return e($user->unit ?: '-');
                })
                ->editColumn('jabatan', function ($user) {
                    return e($user->jabatan ?: '-');
                })
                ->addColumn('status_datang', function () {
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-semibold"><i class="bi bi-x-circle me-1"></i>Belum Hadir (3 Hari)</span>';
                })
                ->addColumn('status_pulang', function () {
                    return '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1 fw-semibold"><i class="bi bi-dash-circle me-1"></i>Belum Pulang (3 Hari)</span>';
                })
                ->rawColumns(['name', 'nidn', 'unit', 'jabatan', 'status_datang', 'status_pulang'])
                ->make(true);
        }

        // Mode Single Day
        $modelClass = $this->getAttendanceModel($kepanitiaan, $day);

        $attendedUserIds = $modelClass::where(function ($q) {
            $q->whereNotNull('hadir_datang')
              ->orWhereNotNull('hadir_pulang');
        })->pluck('user_id')->toArray();

        $usersQuery = $this->getBaseUsersQuery()
            ->whereNotIn('id', $attendedUserIds);

        return DataTables::of($usersQuery)
            ->addIndexColumn()
            ->editColumn('name', function ($user) {
                return '<div class="fw-bold text-dark" style="font-size: 0.95rem;">' . e($user->name) . '</div>' .
                       '<small class="text-muted"><i class="bi bi-envelope me-1"></i>' . e($user->email ?: '-') . '</small>';
            })
            ->editColumn('nidn', function ($user) {
                return '<span class="badge bg-light text-dark border">' . e($user->nidn ?: '-') . '</span>';
            })
            ->editColumn('unit', function ($user) {
                return e($user->unit ?: '-');
            })
            ->editColumn('jabatan', function ($user) {
                return e($user->jabatan ?: '-');
            })
            ->addColumn('status_datang', function () {
                return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-semibold"><i class="bi bi-x-circle me-1"></i>Belum Datang</span>';
            })
            ->addColumn('status_pulang', function () {
                return '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1 fw-semibold"><i class="bi bi-dash-circle me-1"></i>Belum Pulang</span>';
            })
            ->rawColumns(['name', 'nidn', 'unit', 'jabatan', 'status_datang', 'status_pulang'])
            ->make(true);
    }

    /**
     * Get summary attendance statistics (Total, Datang, Pulang, Belum Datang, Belum Pulang).
     */
    public function getStats(Request $request)
    {
        $kepanitiaan = $request->query('kepanitiaan', 'pkkmb');
        $day = (int) $request->query('day', 1);

        $baseUsersQuery = $this->getBaseUsersQuery();
        $totalPegawai = $baseUsersQuery->count();
        $targetUserIds = $baseUsersQuery->pluck('id')->toArray();

        if ($day === 0) {
            // Stats Akumulasi 3 Hari
            $m1 = $this->getAttendanceModel($kepanitiaan, 1);
            $m2 = $this->getAttendanceModel($kepanitiaan, 2);
            $m3 = $this->getAttendanceModel($kepanitiaan, 3);

            $att1 = $m1::whereIn('user_id', $targetUserIds)->get();
            $att2 = $m2::whereIn('user_id', $targetUserIds)->get();
            $att3 = $m3::whereIn('user_id', $targetUserIds)->get();

            $sudahDatangTotal = $att1->whereNotNull('hadir_datang')->count() + $att2->whereNotNull('hadir_datang')->count() + $att3->whereNotNull('hadir_datang')->count();
            $sudahPulangTotal = $att1->whereNotNull('hadir_pulang')->count() + $att2->whereNotNull('hadir_pulang')->count() + $att3->whereNotNull('hadir_pulang')->count();

            $allAttendedIds = array_unique(array_merge(
                $att1->filter(fn($a) => !empty($a->hadir_datang) || !empty($a->hadir_pulang))->pluck('user_id')->toArray(),
                $att2->filter(fn($a) => !empty($a->hadir_datang) || !empty($a->hadir_pulang))->pluck('user_id')->toArray(),
                $att3->filter(fn($a) => !empty($a->hadir_datang) || !empty($a->hadir_pulang))->pluck('user_id')->toArray()
            ));

            $sudahAbsen = count($allAttendedIds);
            $belumAbsen = max(0, $totalPegawai - $sudahAbsen);

            $totalTargetPresensi = $totalPegawai * 3;
            $belumDatangTotal = max(0, $totalTargetPresensi - $sudahDatangTotal);
            $belumPulangTotal = max(0, $totalTargetPresensi - $sudahPulangTotal);

            return response()->json([
                'total_pegawai' => $totalPegawai,
                'sudah_absen' => $sudahAbsen,
                'belum_absen' => $belumAbsen,
                'sudah_datang' => $sudahDatangTotal,
                'belum_datang' => $belumDatangTotal,
                'sudah_pulang' => $sudahPulangTotal,
                'belum_pulang' => $belumPulangTotal,
                'is_all_days' => true,
            ]);
        }

        // Stats Single Day
        $modelClass = $this->getAttendanceModel($kepanitiaan, $day);
        $attendances = $modelClass::whereIn('user_id', $targetUserIds)->get();

        $sudahDatang = $attendances->whereNotNull('hadir_datang')->count();
        $sudahPulang = $attendances->whereNotNull('hadir_pulang')->count();
        $sudahLengkap = $attendances->filter(fn($a) => !empty($a->hadir_datang) && !empty($a->hadir_pulang))->count();
        
        $sudahAbsen = $attendances->filter(fn($a) => !empty($a->hadir_datang) || !empty($a->hadir_pulang))->count();
        $belumAbsen = max(0, $totalPegawai - $sudahAbsen);

        $belumDatang = max(0, $totalPegawai - $sudahDatang);
        $belumPulang = max(0, $totalPegawai - $sudahPulang);

        return response()->json([
            'total_pegawai' => $totalPegawai,
            'sudah_absen' => $sudahAbsen,
            'belum_absen' => $belumAbsen,
            'sudah_datang' => $sudahDatang,
            'belum_datang' => $belumDatang,
            'sudah_pulang' => $sudahPulang,
            'belum_pulang' => $belumPulang,
            'sudah_lengkap' => $sudahLengkap,
            'is_all_days' => false,
        ]);
    }
}
