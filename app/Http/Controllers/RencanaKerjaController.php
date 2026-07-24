<?php

namespace App\Http\Controllers;

use App\Http\Requests\RencanaKerjaRequest;
use App\Models\PeriodeAkademik;
use App\Models\RencanaKerja;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class RencanaKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        if ($request->ajax()) {
            $query = RencanaKerja::with(['user', 'periodeAkademik']);

            if ($authUser) {
                if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                    // Superadmin, Admin, Pimpinan Rektorat -> Akses seluruh tugas universitas
                } elseif ($authUser->isPimpinanUnit()) {
                    // Pimpinan Unit -> Akses seluruh tugas di unitnya
                    $query->whereHas('user', function ($q) use ($authUser) {
                        $q->where('unit', $authUser->unit);
                    });
                } else {
                    // Staff / Pegawai Regular -> Hanya akses tugas milik sendiri
                    $query->where('user_id', $authUser->id);
                }
            }

            if ($request->filled('jabatan')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('jabatan', $request->jabatan);
                });
            }

            $query->latest();

            $totalCount = (clone $query)->count();
            $selesaiCount = (clone $query)->where('status', 'Selesai')->count();
            $prosesCount = (clone $query)->where('status', 'Proses')->count();
            $belumCount = (clone $query)->where('status', 'Belum Dimulai')->count();
            $percent = $totalCount > 0 ? round(($selesaiCount / $totalCount) * 100) : 0;

            return DataTables::of($query)
                ->addIndexColumn()
                ->filterColumn('task_details', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('uraian_tugas', 'like', "%{$keyword}%")
                          ->orWhereHas('user', function ($u) use ($keyword) {
                              $u->where('name', 'like', "%{$keyword}%")
                                ->orWhere('jabatan', 'like', "%{$keyword}%");
                          });
                    });
                })
                ->addColumn('task_details', function ($row) {
                    $html = '<div class="d-flex flex-column gap-2 py-1">';
                    
                    // 1. Task Description Title
                    $html .= '<div class="text-dark fw-semibold fs-6 lh-sm" style="font-size: 0.95rem;">' . e($row->uraian_tugas) . '</div>';

                    // 2. Metadata Chips (Periode, Hari, Pegawai)
                    $chips = [];
                    if ($row->periodeAkademik) {
                        $chips[] = '<span class="badge px-2 py-1" style="background-color: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-weight: 600; font-size: 0.78rem;"><i class="bi bi-mortarboard-fill me-1"></i>' . e($row->periodeAkademik->nama_periode) . '</span>';
                    }

                    if ($row->user) {
                        $chips[] = '<span class="badge px-2 py-1 bg-light text-dark border fw-normal" style="font-size: 0.78rem;"><i class="bi bi-person-fill text-secondary me-1"></i>' . e($row->user->name) . ' <span class="text-muted">(' . e($row->user->jabatan ?? '-') . ')</span></span>';
                    }

                    if ($row->hari) {
                        $chips[] = '<span class="badge px-2 py-1" style="background-color: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe; font-weight: 600; font-size: 0.78rem;"><i class="bi bi-calendar-day me-1"></i>' . e($row->hari) . '</span>';
                    }

                    if (!empty($chips)) {
                        $html .= '<div class="d-flex flex-wrap align-items-center gap-2 mb-1">' . implode('', $chips) . '</div>';
                    }

                    // 3. Time Details Card Box (Kiri: Estimasi & Mulai | Kanan: Selesai & Durasi)
                    $estFormatted = '-';
                    if ($row->estimasi_jam_mulai || $row->estimasi_tanggal_mulai) {
                        $tglEstMulai = $row->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($row->estimasi_tanggal_mulai)) : '';
                        $tglEstSelesai = $row->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($row->estimasi_tanggal_selesai)) : '';
                        $jamEstMulai = $row->estimasi_jam_mulai ? substr($row->estimasi_jam_mulai, 0, 5) : '';
                        $jamEstSelesai = $row->estimasi_jam_selesai ? substr($row->estimasi_jam_selesai, 0, 5) : '';

                        if ($tglEstMulai && $tglEstSelesai && $tglEstMulai === $tglEstSelesai) {
                            $jamPart = '';
                            if ($jamEstMulai && $jamEstSelesai) {
                                $jamPart = ' (' . $jamEstMulai . ' - ' . $jamEstSelesai . ' WIB)';
                            } elseif ($jamEstMulai) {
                                $jamPart = ' (' . $jamEstMulai . ' WIB)';
                            } elseif ($jamEstSelesai) {
                                $jamPart = ' (' . $jamEstSelesai . ' WIB)';
                            }
                            $estFormatted = $tglEstMulai . $jamPart;
                        } else {
                            $startPart = trim($tglEstMulai . ($jamEstMulai ? ' (' . $jamEstMulai . ' WIB)' : ''));
                            $endPart = trim($tglEstSelesai . ($jamEstSelesai ? ' (' . $jamEstSelesai . ' WIB)' : ''));
                            $estFormatted = trim($startPart . ($endPart ? ' - ' . $endPart : ''));
                        }
                    }

                    $mulaiFormatted = '-';
                    if ($row->waktu_mulai) {
                        $tglMulai = $row->tanggal_mulai ? date('d/m/Y', strtotime($row->tanggal_mulai)) : '';
                        $jamMulai = substr($row->waktu_mulai, 0, 5);
                        $mulaiFormatted = $tglMulai ? ($tglMulai . ' (' . $jamMulai . ' WIB)') : '(' . $jamMulai . ' WIB)';
                    }

                    $selesaiFormatted = '-';
                    if ($row->waktu_selesai && $row->waktu_selesai !== '00:00:00') {
                        $tglSelesai = $row->tanggal_selesai ? date('d/m/Y', strtotime($row->tanggal_selesai)) : '';
                        $jamSelesai = substr($row->waktu_selesai, 0, 5);
                        $selesaiFormatted = $tglSelesai ? ($tglSelesai . ' (' . $jamSelesai . ' WIB)') : '(' . $jamSelesai . ' WIB)';
                    }

                    $durasiStr = '-';
                    if (!empty($row->waktu_mulai) && !empty($row->waktu_selesai) && $row->waktu_selesai !== '00:00:00') {
                        try {
                            $tglMulaiStr = !empty($row->tanggal_mulai) ? $row->tanggal_mulai : now()->format('Y-m-d');
                            $tglSelesaiStr = !empty($row->tanggal_selesai) ? $row->tanggal_selesai : $tglMulaiStr;

                            $startTs = strtotime($tglMulaiStr . ' ' . $row->waktu_mulai);
                            $endTs = strtotime($tglSelesaiStr . ' ' . $row->waktu_selesai);

                            $diffInSeconds = max(0, $endTs - $startTs);

                            $days = floor($diffInSeconds / 86400);
                            $hours = floor(($diffInSeconds % 86400) / 3600);
                            $minutes = floor(($diffInSeconds % 3600) / 60);
                            $seconds = $diffInSeconds % 60;

                            $durasiParts = [];
                            if ($days > 0) $durasiParts[] = $days . 'h';
                            if ($hours > 0) $durasiParts[] = $hours . 'j';
                            if ($minutes > 0) $durasiParts[] = $minutes . 'm';
                            if ($seconds > 0 || empty($durasiParts)) $durasiParts[] = $seconds . 's';

                            $durasiStr = implode(' ', $durasiParts);
                        } catch (\Exception $e) {
                            $durasiStr = '-';
                        }
                    }

                    $html .= '<div class="mt-2 p-2 p-sm-3 bg-light rounded border shadow-sm" style="word-break: break-word;">';
                    $html .= '<div class="row g-2 align-items-center">';
                    
                    // Left Column: Estimasi & Mulai
                    $html .= '<div class="col-12 col-md-6">';
                    
                    $html .= '<div class="mb-2">';
                    $html .= '<label class="form-label mb-1 text-secondary small fw-semibold text-nowrap d-block" style="font-size: 0.78rem;"><i class="bi bi-clock-history me-1"></i>Estimasi Pelaksanaan:</label>';
                    $html .= '<div class="fw-semibold text-dark ps-1" style="font-size: 0.85rem; word-break: break-word;">' . e($estFormatted) . '</div>';
                    $html .= '</div>';

                    $html .= '<div>';
                    $html .= '<label class="form-label mb-1 text-secondary small fw-semibold text-nowrap d-block" style="font-size: 0.78rem;"><i class="bi bi-play-circle-fill text-primary me-1"></i>Waktu Mulai:</label>';
                    $html .= '<div class="fw-semibold text-primary ps-1" style="font-size: 0.85rem; word-break: break-word;">' . e($mulaiFormatted) . '</div>';
                    $html .= '</div>';

                    $html .= '</div>'; // end col left

                    // Right Column: Selesai & Durasi
                    $html .= '<div class="col-12 col-md-6">';

                    $html .= '<div class="mb-2">';
                    $html .= '<label class="form-label mb-1 text-secondary small fw-semibold text-nowrap d-block" style="font-size: 0.78rem;"><i class="bi bi-check-circle-fill text-success me-1"></i>Waktu Selesai:</label>';
                    $html .= '<div class="fw-semibold text-success ps-1" style="font-size: 0.85rem; word-break: break-word;">' . e($selesaiFormatted) . '</div>';
                    $html .= '</div>';

                    $html .= '<div>';
                    $html .= '<label class="form-label mb-1 text-secondary small fw-semibold text-nowrap d-block" style="font-size: 0.78rem;"><i class="bi bi-hourglass-split me-1"></i>Total Durasi:</label>';
                    $html .= '<div class="fw-semibold text-dark ps-1" style="font-size: 0.85rem; word-break: break-word;">' . e($durasiStr) . '</div>';
                    $html .= '</div>';

                    $html .= '</div>'; // end col right

                    $html .= '</div>'; // end row
                    $html .= '</div>'; // end card

                    // Form Inline Upload Berkas & Link di bawah nama jika tugas Selesai / Waktu Selesai terisi
                    if (!empty($row->waktu_selesai) && $row->waktu_selesai !== '00:00:00') {
                        $html .= '<form class="form-inline-upload mt-2 p-3 bg-light rounded border shadow-sm" data-id="' . $row->id . '" enctype="multipart/form-data">';
                        $html .= '<div class="row g-2 align-items-center">';
                        
                        $html .= '<div class="col-12 col-md-6 inline-upload-left">';
                        $html .= '<label class="form-label mb-1 text-secondary small fw-semibold d-block"><i class="bi bi-paperclip me-1"></i>Unggah Berkas Kinerja:</label>';
                        $html .= '<input type="file" name="file" class="form-control form-control-sm bg-white input-file-inline">';
                        $html .= '</div>';

                        $html .= '<div class="col-12 col-md-6 inline-upload-right">';
                        $html .= '<label class="form-label mb-1 text-secondary small fw-semibold d-block"><i class="bi bi-link-45deg me-1"></i>Tautan Google Drive / External:</label>';
                        $html .= '<input type="url" name="url_external" class="form-control form-control-sm bg-white input-url-inline" placeholder="https://drive.google.com/..." value="' . e($row->url_external ?? '') . '">';
                        $html .= '</div>';

                        $html .= '<div class="col-12 mt-2 text-start">';
                        $html .= '<button type="submit" class="btn btn-sm text-white px-4 fw-bold btn-simpan-inline" style="background-color: #15432d; border-color: #15432d; height: 32px;"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan</button>';
                        $html .= '</div>';

                        // Hasil Tersimpan (Di bawah Tombol Simpan)
                        $html .= '<div class="col-12 mt-3 pt-2 border-top">';
                        $html .= '<div class="d-flex align-items-center flex-wrap gap-2">';
                        $html .= '<span class="text-dark small fw-bold me-2"><i class="bi bi-folder-check me-1"></i>Hasil Tersimpan:</span>';
                        
                        $hasResult = false;
                        if ($row->file) {
                            $hasResult = true;
                            $fileUrl = asset('storage/' . $row->file);
                            $html .= '<a href="' . $fileUrl . '" target="_blank" class="btn btn-sm btn-outline-success text-nowrap fw-semibold px-3 d-inline-flex align-items-center" style="font-size: 0.8rem; height: 30px;"><i class="bi bi-file-earmark-check-fill me-1"></i> Unduh / Lihat Berkas</a>';
                        }

                        if ($row->url_external) {
                            $hasResult = true;
                            $html .= '<a href="' . e($row->url_external) . '" target="_blank" class="btn btn-sm btn-outline-primary text-nowrap fw-semibold px-3 d-inline-flex align-items-center" style="font-size: 0.8rem; height: 30px;"><i class="bi bi-box-arrow-up-right me-1"></i> Buka Link Eksternal</a>';
                        }

                        if (!$hasResult) {
                            $html .= '<span class="text-muted small fst-italic">Belum ada berkas atau link tersimpan</span>';
                        }
                        $html .= '</div>'; // end flex
                        $html .= '</div>'; // end col-12

                        $html .= '</div>'; // end row
                        $html .= '</form>';
                    }

                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-inline-flex gap-1 flex-wrap flex-sm-nowrap align-items-center justify-content-end action-buttons-wrap">';

                    $waktuMulai = !empty($row->waktu_mulai) && $row->waktu_mulai !== '00:00:00';
                    $waktuSelesai = !empty($row->waktu_selesai) && $row->waktu_selesai !== '00:00:00';

                    if (!$waktuMulai) {
                        $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.startTimer(' . $row->id . ')" class="btn btn-sm text-white fw-bold text-nowrap px-3 me-1 btn-start-timer" style="background-color: #15432d; border-color: #15432d; height: 32px; min-width: 90px; cursor: pointer;"><i class="bi bi-play-fill me-1"></i> Mulai</button>';
                    } elseif ($waktuMulai && !$waktuSelesai) {
                        $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.stopTimer(' . $row->id . ')" class="btn btn-sm btn-danger text-white fw-bold text-nowrap px-3 me-1 btn-stop-timer" style="background-color: #8b0000; border-color: #8b0000; height: 32px; min-width: 90px; cursor: pointer;"><i class="bi bi-stop-fill me-1"></i> Selesai</button>';
                    } else {
                        $btn .= '<button type="button" class="btn btn-sm text-white fw-bold text-nowrap px-3 me-1 disabled" style="background-color: #2d6a4f; border-color: #2d6a4f; opacity: 1; height: 32px; min-width: 90px; cursor: default;"><i class="bi bi-check-circle-fill me-1"></i> Selesai</button>';
                    }

                    $editUrl = route('rencana-kerja.edit', $row->id);
                    $btn .= '<a href="' . $editUrl . '" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center me-1" style="height: 32px; width: 32px; padding: 0;" title="Edit Tugas"><i class="bi bi-pencil"></i></a>';
                    $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.deleteRencanaKerja(' . $row->id . ')" class="btn btn-sm btn-outline-danger btn-delete-timer d-inline-flex align-items-center justify-content-center" style="height: 32px; width: 32px; padding: 0;" title="Hapus Tugas"><i class="bi bi-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('voice_narration', function ($row) {
                    $uraian = e($row->uraian_tugas);

                    // Estimasi
                    $estMulai = !empty($row->estimasi_tanggal_mulai) ? date('d/m/Y', strtotime($row->estimasi_tanggal_mulai)) : '';
                    $estJamMulai = !empty($row->estimasi_jam_mulai) ? substr($row->estimasi_jam_mulai, 0, 5) : '';
                    $estSelesai = !empty($row->estimasi_tanggal_selesai) ? date('d/m/Y', strtotime($row->estimasi_tanggal_selesai)) : '';
                    $estJamSelesai = !empty($row->estimasi_jam_selesai) ? substr($row->estimasi_jam_selesai, 0, 5) : '';

                    $estStr = 'Estimasi: ';
                    if ($estMulai && $estSelesai && $estMulai === $estSelesai) {
                        $estStr .= $estMulai . ($estJamMulai || $estJamSelesai ? ' jam ' . $estJamMulai . ' sampai ' . $estJamSelesai . ' WIB' : '');
                    } elseif ($estMulai || $estSelesai) {
                        $estStr .= trim($estMulai . ' ' . $estJamMulai . ' WIB sampai ' . $estSelesai . ' ' . $estJamSelesai . ' WIB');
                    } else {
                        $estStr .= 'Belum ada';
                    }
                    $estStr .= '. ';

                    // Waktu Mulai
                    $tglM = !empty($row->tanggal_mulai) ? date('d/m/Y', strtotime($row->tanggal_mulai)) . ' ' : '';
                    $mulaiStr = 'Waktu mulai: ' . (!empty($row->waktu_mulai) && $row->waktu_mulai !== '00:00:00' ? $tglM . 'jam ' . substr($row->waktu_mulai, 0, 5) . ' WIB' : 'Belum dimulai') . '. ';

                    // Waktu Selesai
                    $tglS = !empty($row->tanggal_selesai) ? date('d/m/Y', strtotime($row->tanggal_selesai)) . ' ' : '';
                    $selesaiStr = 'Waktu selesai: ' . (!empty($row->waktu_selesai) && $row->waktu_selesai !== '00:00:00' ? $tglS . 'jam ' . substr($row->waktu_selesai, 0, 5) . ' WIB' : 'Belum selesai') . '. ';

                    // Durasi
                    $durasiStr = 'Durasi: ';
                    if (!empty($row->waktu_mulai) && !empty($row->waktu_selesai) && $row->waktu_selesai !== '00:00:00') {
                        try {
                            $tglMulaiStr = !empty($row->tanggal_mulai) ? $row->tanggal_mulai : now()->format('Y-m-d');
                            $tglSelesaiStr = !empty($row->tanggal_selesai) ? $row->tanggal_selesai : $tglMulaiStr;
                            $startTs = strtotime($tglMulaiStr . ' ' . $row->waktu_mulai);
                            $endTs = strtotime($tglSelesaiStr . ' ' . $row->waktu_selesai);
                            $diffInSeconds = max(0, $endTs - $startTs);
                            $hours = floor($diffInSeconds / 3600);
                            $minutes = floor(($diffInSeconds % 3600) / 60);
                            $seconds = $diffInSeconds % 60;
                            $dParts = [];
                            if ($hours > 0) $dParts[] = $hours . ' jam';
                            if ($minutes > 0) $dParts[] = $minutes . ' menit';
                            if ($seconds > 0 || empty($dParts)) $dParts[] = $seconds . ' detik';
                            $durasiStr .= implode(' ', $dParts);
                        } catch (\Exception $e) {
                            $durasiStr .= 'Belum ada';
                        }
                    } else {
                        $durasiStr .= 'Belum ada';
                    }
                    $durasiStr .= '. ';

                    // Analisis Ketepatan Waktu
                    $analisisStr = 'Analisis: ';
                    if ($row->status === 'Selesai') {
                        if (!empty($row->estimasi_tanggal_selesai) && !empty($row->tanggal_selesai)) {
                            $estEnd = strtotime($row->estimasi_tanggal_selesai . ' ' . ($row->estimasi_jam_selesai ?? '23:59:59'));
                            $actEnd = strtotime($row->tanggal_selesai . ' ' . ($row->waktu_selesai ?? '00:00:00'));
                            $analisisStr .= ($actEnd <= $estEnd) ? 'Selesai tepat waktu.' : 'Selesai terlambat.';
                        } else {
                            $analisisStr .= 'Selesai tepat waktu.';
                        }
                    } elseif ($row->status === 'Proses') {
                        $analisisStr .= 'Masih berproses.';
                    } else {
                        $analisisStr .= 'Belum dikerjakan.';
                    }
                    $analisisStr .= ' ';

                    // Link Eksternal Only
                    $linkStr = 'Link eksternal: ' . (!empty($row->url_external) ? 'Ada link eksternal.' : 'Tidak ada link eksternal.') . ' ';

                    return $uraian . '|||' . $estStr . $mulaiStr . $selesaiStr . $durasiStr . $analisisStr . $linkStr;
                })
                ->with('overall_rekap', [
                    'total' => $totalCount,
                    'selesai' => $selesaiCount,
                    'proses' => $prosesCount,
                    'belum' => $belumCount,
                    'percent' => $percent,
                ])
                ->rawColumns(['task_details', 'action'])
                ->make(true);
        }

        $usersQuery = User::whereNotNull('jabatan')
            ->where('jabatan', '!=', '-');

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                // Superadmin, Admin, Pimpinan Rektorat -> Seluruh Jabatan
            } elseif ($authUser->isPimpinanUnit()) {
                // Pimpinan Unit -> Jabatan di unitnya
                $usersQuery->where('unit', $authUser->unit);
            } else {
                // Staff Regular -> Jabatan milik sendiri
                $usersQuery->where('id', $authUser->id);
            }
        }

        $usersWithJabatan = $usersQuery->orderBy('jabatan')->get(['id', 'name', 'jabatan']);
        $periodeAkademiks = PeriodeAkademik::orderBy('id', 'asc')->get();
        $defaultPeriode = PeriodeAkademik::where('nama_periode', '2025/2026 Semester Antara')->first()
            ?? PeriodeAkademik::first();
        $defaultPeriodeId = $defaultPeriode ? $defaultPeriode->id : null;

        return view('pages.rencanakerja.index', compact('usersWithJabatan', 'periodeAkademiks', 'defaultPeriodeId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $authUser = Auth::user();
        $usersQuery = User::query();

        if ($authUser && !$authUser->isAdmin()) {
            $usersQuery->where('unit', $authUser->unit);
        }

        $users = $usersQuery->orderBy('name')->get();
        $periodeAkademiks = PeriodeAkademik::orderBy('id', 'asc')->get();
        $defaultPeriode = PeriodeAkademik::where('nama_periode', '2025/2026 Semester Antara')->first()
            ?? PeriodeAkademik::first();
        $defaultPeriodeId = $defaultPeriode ? $defaultPeriode->id : null;

        return view('pages.rencanakerja.create', compact('users', 'periodeAkademiks', 'defaultPeriodeId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RencanaKerjaRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = $validated['user_id'] ?? Auth::id();

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('rencana_kerja', 'public');
        }

        if ($request->has('start_now') && $request->start_now) {
            $validated['waktu_mulai'] = now()->format('H:i:s');
            $validated['tanggal_mulai'] = now()->format('Y-m-d');
            $validated['status'] = 'Berjalan';
        } else {
            $validated['status'] = 'Belum Dimulai';
        }

        RencanaKerja::create($validated);

        Alert::success('Berhasil', 'Rencana Kerja berhasil ditambahkan')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('rencana-kerja.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RencanaKerja $rencanaKerja)
    {
        $authUser = Auth::user();
        $usersQuery = User::query();

        if ($authUser && !$authUser->isAdmin()) {
            $usersQuery->where('unit', $authUser->unit);
        }

        $users = $usersQuery->orderBy('name')->get();
        $periodeAkademiks = PeriodeAkademik::orderBy('id', 'asc')->get();

        return view('pages.rencanakerja.edit', compact('rencanaKerja', 'users', 'periodeAkademiks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RencanaKerjaRequest $request, RencanaKerja $rencanaKerja)
    {
        $validated = $request->validated();

        if ($request->hasFile('file')) {
            if ($rencanaKerja->file && Storage::disk('public')->exists($rencanaKerja->file)) {
                Storage::disk('public')->delete($rencanaKerja->file);
            }
            $validated['file'] = $request->file('file')->store('rencana_kerja', 'public');
        }

        $rencanaKerja->update($validated);

        Alert::success('Berhasil', 'Rencana Kerja berhasil diperbarui')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('rencana-kerja.index');
    }

    /**
     * Start task timer (Play button).
     */
    public function start(Request $request, RencanaKerja $rencanaKerja)
    {
        $clientTime = $request->input('client_time') ?? now()->format('H:i:s');
        $clientDate = $request->input('client_date') ?? now()->format('Y-m-d');

        $rencanaKerja->update([
            'waktu_mulai' => $clientTime,
            'tanggal_mulai' => $clientDate,
            'status' => 'Berjalan',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Waktu mulai pelaksanaan tugas (' . substr($clientTime, 0, 5) . ') berhasil dicatat.',
        ]);
    }

    /**
     * Stop task timer (Stop button).
     */
    public function stop(Request $request, RencanaKerja $rencanaKerja)
    {
        $clientTime = $request->input('client_time') ?? now()->format('H:i:s');
        $clientDate = $request->input('client_date') ?? now()->format('Y-m-d');

        $updateData = [
            'waktu_selesai' => $clientTime,
            'tanggal_selesai' => $clientDate,
            'status' => 'Selesai',
        ];

        if ($request->hasFile('file')) {
            if ($rencanaKerja->file && Storage::disk('public')->exists($rencanaKerja->file)) {
                Storage::disk('public')->delete($rencanaKerja->file);
            }
            $updateData['file'] = $request->file('file')->store('rencana_kerja', 'public');
        }

        if ($request->filled('url_external')) {
            $updateData['url_external'] = $request->input('url_external');
        }

        $rencanaKerja->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Waktu penyelesaian tugas (' . substr($clientTime, 0, 5) . ') berhasil dicatat.',
        ]);
    }

    /**
     * Upload attachment (file & url_external) inline.
     */
    public function uploadAttachment(Request $request, RencanaKerja $rencanaKerja)
    {
        $updateData = [];

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            if ($uploadedFile->isValid()) {
                if ($rencanaKerja->file && Storage::disk('public')->exists($rencanaKerja->file)) {
                    Storage::disk('public')->delete($rencanaKerja->file);
                }
                $updateData['file'] = $uploadedFile->store('rencana_kerja', 'public');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengunggah berkas: ' . $uploadedFile->getErrorMessage(),
                ], 422);
            }
        }

        if ($request->has('url_external')) {
            $updateData['url_external'] = $request->input('url_external');
        }

        if (!empty($updateData)) {
            $rencanaKerja->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berkas dan link eksternal berhasil disimpan.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, RencanaKerja $rencanaKerja)
    {
        if ($rencanaKerja->file && Storage::disk('public')->exists($rencanaKerja->file)) {
            Storage::disk('public')->delete($rencanaKerja->file);
        }

        $rencanaKerja->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Rencana Kerja berhasil dihapus'
            ]);
        }

        Alert::success('Berhasil', 'Rencana Kerja berhasil dihapus')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('rencana-kerja.index');
    }

    /**
     * Download Excel Import Template
     */
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Rencana Kerja');

        // Header Columns
        $headers = [
            'A1' => 'uraian_tugas',
            'B1' => 'periode_akademik',
            'C1' => 'hari',
            'D1' => 'estimasi_tanggal_mulai',
            'E1' => 'estimasi_jam_mulai',
            'F1' => 'estimasi_tanggal_selesai',
            'G1' => 'estimasi_jam_selesai',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        // Style Header
        $headerRange = 'A1:G1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('15432D');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setARGB('FFFFFF');

        // Sample Rows
        $sheet->setCellValue('A2', 'Menyusun Laporan Kinerja Mingguan');
        $sheet->setCellValue('B2', '2025/2026 Semester Antara');
        $sheet->setCellValue('C2', 'Senin');
        $sheet->setCellValue('D2', date('Y-m-d'));
        $sheet->setCellValue('E2', '08:00');
        $sheet->setCellValue('F2', date('Y-m-d'));
        $sheet->setCellValue('G2', '16:00');

        $sheet->setCellValue('A3', 'Mengikuti Rapat Koordinasi Tim');
        $sheet->setCellValue('B3', '2025/2026 Semester Antara');
        $sheet->setCellValue('C3', 'Selasa');
        $sheet->setCellValue('D3', date('Y-m-d'));
        $sheet->setCellValue('E3', '09:00');
        $sheet->setCellValue('F3', date('Y-m-d'));
        $sheet->setCellValue('G3', '11:30');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'template_import_rencana_kerja.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import Rencana Kerja from Excel File
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'periode_akademik_id' => 'nullable|exists:periode_akademiks,id',
        ]);

        try {
            $file = $request->file('file_excel');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows) || count($rows) < 2) {
                Alert::error('Gagal', 'File Excel kosong atau tidak memiliki data.')->toToast();
                return redirect()->back();
            }

            // Detect header columns
            $header = array_map(function ($h) {
                return strtolower(trim((string) $h));
            }, $rows[0]);

            $uraianIndex = array_search('uraian_tugas', $header);
            if ($uraianIndex === false) {
                $uraianIndex = 0;
            }

            $periodeIndex = array_search('periode_akademik', $header);
            $hariIndex = array_search('hari', $header);
            $estTglMulaiIndex = array_search('estimasi_tanggal_mulai', $header);
            $estJamMulaiIndex = array_search('estimasi_jam_mulai', $header);
            $estTglSelesaiIndex = array_search('estimasi_tanggal_selesai', $header);
            $estJamSelesaiIndex = array_search('estimasi_jam_selesai', $header);

            $count = 0;
            $authUserId = auth()->id();

            $defaultPeriode = PeriodeAkademik::where('nama_periode', '2025/2026 Semester Antara')->first()
                ?? PeriodeAkademik::first();
            $fallbackPeriodeId = $request->input('periode_akademik_id') ?? ($defaultPeriode ? $defaultPeriode->id : 1);

            $allPeriodes = PeriodeAkademik::all();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $uraianTugas = isset($row[$uraianIndex]) ? trim((string) $row[$uraianIndex]) : '';

                if (empty($uraianTugas)) {
                    continue;
                }

                $rowPeriodeId = $fallbackPeriodeId;
                if ($periodeIndex !== false && !empty($row[$periodeIndex])) {
                    $pName = trim((string) $row[$periodeIndex]);
                    $matchedPeriode = $allPeriodes->first(function ($p) use ($pName) {
                        return strtolower($p->nama_periode) === strtolower($pName) || stripos($p->nama_periode, $pName) !== false;
                    });
                    if ($matchedPeriode) {
                        $rowPeriodeId = $matchedPeriode->id;
                    }
                }

                $hariVal = ($hariIndex !== false && !empty($row[$hariIndex])) ? trim((string) $row[$hariIndex]) : null;
                $rawEstTglMulai = ($estTglMulaiIndex !== false && !empty($row[$estTglMulaiIndex])) ? $row[$estTglMulaiIndex] : null;
                $rawEstJamMulai = ($estJamMulaiIndex !== false && !empty($row[$estJamMulaiIndex])) ? $row[$estJamMulaiIndex] : null;
                $rawEstTglSelesai = ($estTglSelesaiIndex !== false && !empty($row[$estTglSelesaiIndex])) ? $row[$estTglSelesaiIndex] : null;
                $rawEstJamSelesai = ($estJamSelesaiIndex !== false && !empty($row[$estJamSelesaiIndex])) ? $row[$estJamSelesaiIndex] : null;

                $estTglMulaiVal = $this->parseExcelDate($rawEstTglMulai);
                $estJamMulaiVal = $this->parseExcelTime($rawEstJamMulai);
                $estTglSelesaiVal = $this->parseExcelDate($rawEstTglSelesai);
                $estJamSelesaiVal = $this->parseExcelTime($rawEstJamSelesai);

                RencanaKerja::create([
                    'user_id' => $authUserId,
                    'periode_akademik_id' => $rowPeriodeId,
                    'uraian_tugas' => $uraianTugas,
                    'hari' => $hariVal,
                    'estimasi_tanggal_mulai' => $estTglMulaiVal,
                    'estimasi_jam_mulai' => $estJamMulaiVal,
                    'estimasi_tanggal_selesai' => $estTglSelesaiVal,
                    'estimasi_jam_selesai' => $estJamSelesaiVal,
                    'status' => 'Belum Dimulai',
                ]);

                $count++;
            }

            Alert::success('Berhasil', "Berhasil mengimpor {$count} data Rencana Kerja.")
                ->toToast()
                ->autoClose(4000)
                ->timerProgressBar();

            return redirect()->back();
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Gagal memproses file Excel: ' . $e->getMessage())->toToast();
            return redirect()->back();
        }
    }

    /**
     * Helper to parse any Excel date format into YYYY-MM-DD string.
     */
    protected function parseExcelDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $valueStr = trim((string) $value);
        if ($valueStr === '' || $valueStr === '-') {
            return null;
        }

        // If numeric, it might be an Excel date serial number (e.g., 45497)
        if (is_numeric($valueStr) && (float) $valueStr > 20000) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $valueStr);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                // fallback
            }
        }

        // Common date formats found in MS Excel imports
        $formats = [
            'Y-m-d',
            'm/d/Y',
            'n/j/Y',
            'd/m/Y',
            'j/n/Y',
            'Y/m/d',
            'd-m-Y',
            'j-n-Y',
            'Y.m.d',
            'd.m.Y',
            'm-d-Y',
            'n-j-Y',
        ];

        foreach ($formats as $fmt) {
            try {
                $dt = \DateTime::createFromFormat('!' . $fmt, $valueStr);
                if ($dt && $dt->format($fmt) === $valueStr) {
                    return $dt->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // continue
            }
        }

        // General strtotime fallback
        try {
            $ts = strtotime($valueStr);
            if ($ts !== false && $ts > 0) {
                return date('Y-m-d', $ts);
            }
        } catch (\Throwable $e) {
            // continue
        }

        return null;
    }

    /**
     * Helper to parse any Excel time format into HH:MM:SS string.
     */
    protected function parseExcelTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $valueStr = trim((string) $value);
        if ($valueStr === '' || $valueStr === '-') {
            return null;
        }

        // If numeric, it might be an Excel day fraction (e.g. 0.38055)
        if (is_numeric($valueStr) && (float) $valueStr < 1.0) {
            try {
                $seconds = round((float) $valueStr * 86400);
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $secs = $seconds % 60;
                return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
            } catch (\Throwable $e) {
                // fallback
            }
        }

        // Common time formats found in MS Excel imports
        $formats = [
            'H:i:s',
            'H:i',
            'G:i:s',
            'G:i',
            'g:i A',
            'g:i a',
            'h:i A',
            'h:i a',
        ];

        foreach ($formats as $fmt) {
            try {
                $dt = \DateTime::createFromFormat($fmt, $valueStr);
                if ($dt) {
                    return $dt->format('H:i:s');
                }
            } catch (\Throwable $e) {
                // continue
            }
        }

        // General strtotime fallback
        try {
            $ts = strtotime($valueStr);
            if ($ts !== false) {
                return date('H:i:s', $ts);
            }
        } catch (\Throwable $e) {
            // continue
        }

        return null;
    }

    /**
     * Export Rencana Kerja to Excel (Khusus Pimpinan & Admin)
     */
    public function exportExcel(Request $request)
    {
        $authUser = Auth::user();

        if (!$authUser || (!$authUser->isPimpinanUnit() && !$authUser->isAdmin())) {
            Alert::error('Gagal', 'Akses ditolak. Fitur ini khusus Pimpinan dan Admin.')->toToast();
            return redirect()->back();
        }

        $query = RencanaKerja::with(['user', 'periodeAkademik']);

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                // Superadmin, Admin, Pimpinan Rektorat -> Seluruh data
            } elseif ($authUser->isPimpinanUnit()) {
                // Pimpinan Unit -> Unit miliknya
                $query->whereHas('user', function ($q) use ($authUser) {
                    $q->where('unit', $authUser->unit);
                });
            } else {
                // Staff regular -> Milik sendiri
                $query->where('user_id', $authUser->id);
            }
        }

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('jabatan')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('jabatan', $request->jabatan);
            });
        }

        $items = $query->latest()->get();

        // Staff Info Header Metadata
        $namaStaff = 'SEMUA STAFF';
        $jabatanStaff = 'SEMUA JABATAN';
        $unitStaff = 'SEMUA UNIT';

        if ($request->filled('user_id') && ($u = \App\Models\User::find($request->user_id))) {
            $namaStaff = strtoupper($u->name);
            $jabatanStaff = strtoupper($u->jabatan ?? '-');
            $unitStaff = strtoupper($u->unit ?? '-');
        } elseif ($items->count() > 0 && $items->pluck('user_id')->unique()->count() === 1) {
            $firstUser = $items->first()->user;
            if ($firstUser) {
                $namaStaff = strtoupper($firstUser->name);
                $jabatanStaff = strtoupper($firstUser->jabatan ?? '-');
                $unitStaff = strtoupper($firstUser->unit ?? '-');
            }
        } elseif ($request->filled('jabatan')) {
            $jabatanStaff = strtoupper($request->jabatan);
            $userWithJabatan = \App\Models\User::where('jabatan', $request->jabatan)->first();
            if ($userWithJabatan && $userWithJabatan->unit) {
                $unitStaff = strtoupper($userWithJabatan->unit);
            }
        } elseif ($authUser && $authUser->unit) {
            $unitStaff = strtoupper($authUser->unit);
        }

        // Periode Akademik Text (Lookup by request, item relation, or latest record)
        $periodeText = 'PERIODE AKADEMIK';
        if ($request->filled('periode_akademik_id') && ($p = \App\Models\PeriodeAkademik::find($request->periode_akademik_id))) {
            $periodeText = strtoupper($p->nama_periode);
        } elseif ($items->count() > 0 && $items->first()->periodeAkademik) {
            $periodeText = strtoupper($items->first()->periodeAkademik->nama_periode);
        } else {
            $latestP = \App\Models\PeriodeAkademik::latest()->first();
            if ($latestP) {
                $periodeText = strtoupper($latestP->nama_periode);
            }
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Rencana Kerja');

        // Row 1: Title (Merged A1:N1 across all columns to STATUS BERKAS)
        $titleText = 'LAPORAN RENCANA KERJA DAN REALISASI KERJA (' . $periodeText . ')';
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', $titleText);
        $sheet->getStyle('A1:N1')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:N1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('15432D');
        $sheet->getStyle('A1:N1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Row 3, 4, 5: Metadata Staff
        $sheet->setCellValue('A3', 'NAMA STAFF');
        $sheet->setCellValue('B3', ':');
        $sheet->setCellValue('C3', $namaStaff);
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('C3')->getFont()->setBold(true);

        $sheet->setCellValue('A4', 'JABATAN');
        $sheet->setCellValue('B4', ':');
        $sheet->setCellValue('C4', $jabatanStaff);
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('C4')->getFont()->setBold(true);

        $sheet->setCellValue('A5', 'UNIT');
        $sheet->setCellValue('B5', ':');
        $sheet->setCellValue('C5', $unitStaff);
        $sheet->getStyle('A5')->getFont()->setBold(true);
        $sheet->getStyle('C5')->getFont()->setBold(true);

        // Row 7: Table Header Columns (Exacly matching template)
        $headers = [
            'A7' => 'NO',
            'B7' => 'HARI',
            'C7' => 'URAIAN TUGAS',
            'D7' => 'ESTIMASI TGL MULAI',
            'E7' => 'ESTIMASI JAM MULAI',
            'F7' => 'ESTIMASI TGL SELESAI',
            'G7' => 'ESTIMASI JAM SELESAI',
            'H7' => 'TANGGAL MULAI',
            'I7' => 'WAKTU MULAI',
            'J7' => 'TANGGAL SELESAI',
            'K7' => 'WAKTU SELESAI',
            'L7' => 'DURASI',
            'M7' => 'LINK EKSTERNAL',
            'N7' => 'STATUS BERKAS',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerRange = 'A7:N7';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('15432D');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(7)->setRowHeight(25);

        $rowNum = 8;
        $no = 1;

        foreach ($items as $item) {
            $durasiStr = '-';
            if (!empty($item->waktu_mulai) && !empty($item->waktu_selesai) && $item->waktu_selesai !== '00:00:00') {
                try {
                    $tglMulaiStr = !empty($item->tanggal_mulai) ? $item->tanggal_mulai : now()->format('Y-m-d');
                    $tglSelesaiStr = !empty($item->tanggal_selesai) ? $item->tanggal_selesai : $tglMulaiStr;
                    $startTs = strtotime($tglMulaiStr . ' ' . $item->waktu_mulai);
                    $endTs = strtotime($tglSelesaiStr . ' ' . $item->waktu_selesai);
                    $diffInSeconds = max(0, $endTs - $startTs);

                    $days = floor($diffInSeconds / 86400);
                    $hours = floor(($diffInSeconds % 86400) / 3600);
                    $minutes = floor(($diffInSeconds % 3600) / 60);
                    $seconds = $diffInSeconds % 60;

                    $durasiParts = [];
                    if ($days > 0) $durasiParts[] = $days . ' hari';
                    if ($hours > 0) $durasiParts[] = $hours . ' jam';
                    if ($minutes > 0) $durasiParts[] = $minutes . ' menit';
                    if ($seconds > 0 || empty($durasiParts)) $durasiParts[] = $seconds . ' detik';
                    $durasiStr = implode(' ', $durasiParts);
                } catch (\Exception $e) {
                    $durasiStr = '-';
                }
            }

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $item->hari ?? '-');
            $sheet->setCellValue('C' . $rowNum, $item->uraian_tugas);
            $sheet->setCellValue('D' . $rowNum, $item->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($item->estimasi_tanggal_mulai)) : '-');
            $sheet->setCellValue('E' . $rowNum, $item->estimasi_jam_mulai ? substr($item->estimasi_jam_mulai, 0, 5) : '-');
            $sheet->setCellValue('F' . $rowNum, $item->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($item->estimasi_tanggal_selesai)) : '-');
            $sheet->setCellValue('G' . $rowNum, $item->estimasi_jam_selesai ? substr($item->estimasi_jam_selesai, 0, 5) : '-');
            $sheet->setCellValue('H' . $rowNum, $item->tanggal_mulai ? date('d/m/Y', strtotime($item->tanggal_mulai)) : '-');
            $sheet->setCellValue('I' . $rowNum, $item->waktu_mulai ? substr($item->waktu_mulai, 0, 5) : '-');
            $sheet->setCellValue('J' . $rowNum, $item->tanggal_selesai ? date('d/m/Y', strtotime($item->tanggal_selesai)) : '-');
            $sheet->setCellValue('K' . $rowNum, $item->waktu_selesai && $item->waktu_selesai !== '00:00:00' ? substr($item->waktu_selesai, 0, 5) : '-');
            $sheet->setCellValue('L' . $rowNum, $durasiStr);
            $sheet->setCellValue('M' . $rowNum, $item->url_external ?? '-');
            $sheet->setCellValue('N' . $rowNum, $item->file ? 'Ada Berkas' : 'Tidak Ada');

            // Alignment
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $rowNum . ':L' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('N' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        // Apply Borders to Table Range A7:N[lastRow]
        $lastRow = max(7, $rowNum - 1);
        $tableRange = 'A7:N' . $lastRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setRGB('CCCCCC');

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $safeStaff = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $namaStaff)));
        $safePeriode = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $periodeText)));

        $filename = ($safeStaff ?: 'Semua Staff') . '_' . ($safePeriode ?: 'Periode Akademik') . '_laporan rencana kerja.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export Rencana Kerja to PDF
     */
    public function exportPdf(Request $request)
    {
        $authUser = auth()->user();
        $query = RencanaKerja::with(['user', 'periodeAkademik']);

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                if ($request->filled('user_id')) {
                    $query->where('user_id', $request->user_id);
                }
            } elseif ($authUser->isPimpinanUnit()) {
                $query->whereHas('user', function ($q) use ($authUser) {
                    $q->where('unit', $authUser->unit);
                });
                if ($request->filled('user_id')) {
                    $query->where('user_id', $request->user_id);
                }
            } else {
                $query->where('user_id', $authUser->id);
            }
        }

        if ($request->filled('jabatan')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('jabatan', $request->jabatan);
            });
        }

        $items = $query->latest()->get();

        $namaStaff = 'SEMUA STAFF';
        $jabatanStaff = 'SEMUA JABATAN';
        $unitStaff = 'SEMUA UNIT';

        if ($request->filled('user_id') && ($u = \App\Models\User::find($request->user_id))) {
            $namaStaff = strtoupper($u->name);
            $jabatanStaff = strtoupper($u->jabatan ?? '-');
            $unitStaff = strtoupper($u->unit ?? '-');
        } elseif ($items->count() > 0 && $items->pluck('user_id')->unique()->count() === 1) {
            $firstUser = $items->first()->user;
            if ($firstUser) {
                $namaStaff = strtoupper($firstUser->name);
                $jabatanStaff = strtoupper($firstUser->jabatan ?? '-');
                $unitStaff = strtoupper($firstUser->unit ?? '-');
            }
        } elseif ($request->filled('jabatan')) {
            $jabatanStaff = strtoupper($request->jabatan);
            $userWithJabatan = \App\Models\User::where('jabatan', $request->jabatan)->first();
            if ($userWithJabatan && $userWithJabatan->unit) {
                $unitStaff = strtoupper($userWithJabatan->unit);
            }
        } elseif ($authUser && $authUser->unit) {
            $unitStaff = strtoupper($authUser->unit);
        }

        $periodeText = 'PERIODE AKADEMIK';
        if ($request->filled('periode_akademik_id') && ($p = \App\Models\PeriodeAkademik::find($request->periode_akademik_id))) {
            $periodeText = strtoupper($p->nama_periode);
        } elseif ($items->count() > 0 && $items->first()->periodeAkademik) {
            $periodeText = strtoupper($items->first()->periodeAkademik->nama_periode);
        } else {
            $latestP = \App\Models\PeriodeAkademik::latest()->first();
            if ($latestP) {
                $periodeText = strtoupper($latestP->nama_periode);
            }
        }

        $safeStaff = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $namaStaff)));
        $safePeriode = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $periodeText)));
        $filename = ($safeStaff ?: 'Semua Staff') . '_' . ($safePeriode ?: 'Periode Akademik') . '_laporan rencana kerja.pdf';

        $pdf = Pdf::loadView('pages.rencanakerja.pdf', compact('items', 'namaStaff', 'jabatanStaff', 'unitStaff', 'periodeText'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
