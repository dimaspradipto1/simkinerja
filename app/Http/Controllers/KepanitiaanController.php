<?php

namespace App\Http\Controllers;

use App\Http\Requests\KepanitiaanRequest;
use App\Models\PeriodeAkademik;
use App\Models\Kepanitiaan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class KepanitiaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        if ($request->ajax()) {
            $query = Kepanitiaan::with(['user', 'periodeAkademik', 'taggedUsers']);

            if ($authUser && !$request->filled('jabatan') && !$authUser->isSuperAdmin()) {
                $query->where(function ($q) use ($authUser) {
                    $q->where('user_id', $authUser->id)
                      ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                          $qu->where('users.id', $authUser->id);
                      });
                });
            }

            if ($request->filled('jabatan')) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', function ($qu) use ($request) {
                        $qu->where('jabatan', $request->jabatan);
                    })->orWhereHas('taggedUsers', function ($qt) use ($request) {
                        $qt->where('jabatan', $request->jabatan);
                    });
                });
            }

            if ($request->filled('periode_akademik_id')) {
                $query->where('periode_akademik_id', $request->periode_akademik_id);
            }

            $query->latest();

            $totalCount = (clone $query)->count();
            $selesaiCount = (clone $query)->where('status', 'Selesai')->count();
            $prosesCount = (clone $query)->whereIn('status', ['Proses', 'Berjalan'])->count();
            $belumCount = (clone $query)->where('status', 'Belum Dimulai')->count();
            $percent = $totalCount > 0 ? round(($selesaiCount / $totalCount) * 100) : 0;

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) use ($authUser) {
                    $canBulk = false;
                    if ($authUser) {
                        if ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit()) {
                            $canBulk = true;
                        } elseif ($row->user_id === $authUser->id) {
                            $canBulk = true;
                        }
                    }
                    if ($canBulk) {
                        return '<input type="checkbox" class="form-check-input select-row-checkbox" value="' . $row->id . '" style="cursor: pointer; width: 18px; height: 18px;">';
                    }
                    return '';
                })
                ->filterColumn('task_details', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('uraian_tugas', 'like', "%{$keyword}%")
                          ->orWhereHas('user', function ($u) use ($keyword) {
                              $u->where('name', 'like', "%{$keyword}%")
                                ->orWhere('jabatan', 'like', "%{$keyword}%");
                          })
                          ->orWhereHas('taggedUsers', function ($tu) use ($keyword) {
                              $tu->where('name', 'like', "%{$keyword}%");
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

                    // 2.5 Special Card for Tagged Users (Rekan Kerja) - Vertical List Layout
                    if ($row->taggedUsers->count() > 0) {
                        $html .= '<div class="mt-2 p-2 px-3 bg-white rounded border shadow-sm d-flex flex-column gap-1" style="border-left: 4px solid #0284c7 !important; word-break: break-word;">';
                        $html .= '<span class="text-secondary small fw-bold mb-1"><i class="bi bi-people-fill text-info me-1"></i>Rekan Kerja:</span>';
                        $html .= '<div class="d-flex flex-column gap-1 ps-2">';
                        
                        $sortedUsers = $row->taggedUsers->sortByDesc(function ($u) {
                            $name = $u->name;
                            if (preg_match('/^Prof\./i', $name)) return 100;
                            if (preg_match('/^Assoc\./i', $name)) return 90;
                            if (preg_match('/^Dr\./i', $name)) return 80;
                            if (preg_match('/^dr\./i', $name)) return 70;
                            if (preg_match('/^Ir\./i', $name)) return 60;
                            if (preg_match('/,\s*M\.[A-Za-z]/i', $name)) return 40;
                            if (preg_match('/,\s*S\.[A-Za-z]/i', $name)) return 20;
                            return 0;
                        });

                        $idx = 1;
                        foreach ($sortedUsers as $tu) {
                            $html .= '<div class="fw-semibold text-dark" style="font-size: 0.85rem;">' . $idx . '. ' . e($tu->name) . ' <span class="text-muted fw-normal" style="font-size: 0.78rem;">(' . e($tu->jabatan ?? '-') . ')</span></div>';
                            $idx++;
                        }
                        $html .= '</div>';
                        $html .= '</div>';
                    }

                    // 3. Time Details Card Box
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
                    
                    // Left Column
                    $html .= '<div class="col-12 col-md-6">';
                    $html .= '<div class="mb-2">';
                    $html .= '<label class="form-label mb-1 text-secondary small fw-semibold text-nowrap d-block" style="font-size: 0.78rem;"><i class="bi bi-clock-history me-1"></i>Estimasi Pelaksanaan:</label>';
                    $html .= '<div class="fw-semibold text-dark ps-1" style="font-size: 0.85rem; word-break: break-word;">' . e($estFormatted) . '</div>';
                    $html .= '</div>';

                    $html .= '<div>';
                    $html .= '<label class="form-label mb-1 text-secondary small fw-semibold text-nowrap d-block" style="font-size: 0.78rem;"><i class="bi bi-play-circle-fill text-primary me-1"></i>Waktu Mulai:</label>';
                    $html .= '<div class="fw-semibold text-primary ps-1" style="font-size: 0.85rem; word-break: break-word;">' . e($mulaiFormatted) . '</div>';
                    $html .= '</div>';
                    $html .= '</div>';

                    // Right Column
                    $html .= '<div class="col-12 col-md-6">';
                    $html .= '<div class="mb-2">';
                    $html .= '<label class="form-label mb-1 text-secondary small fw-semibold text-nowrap d-block" style="font-size: 0.78rem;"><i class="bi bi-check-circle-fill text-success me-1"></i>Waktu Selesai:</label>';
                    $html .= '<div class="fw-semibold text-success ps-1" style="font-size: 0.85rem; word-break: break-word;">' . e($selesaiFormatted) . '</div>';
                    $html .= '</div>';

                    $html .= '<div>';
                    $html .= '<label class="form-label mb-1 text-secondary small fw-semibold text-nowrap d-block" style="font-size: 0.78rem;"><i class="bi bi-hourglass-split me-1"></i>Total Durasi:</label>';
                    $html .= '<div class="fw-semibold text-dark ps-1" style="font-size: 0.85rem; word-break: break-word;">' . e($durasiStr) . '</div>';
                    $html .= '</div>';
                    $html .= '</div>';

                    $html .= '</div>'; // end row
                    $html .= '</div>'; // end card

                    // Form Inline Upload
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

                        // Hasil Tersimpan
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
                        $html .= '</div>';
                        $html .= '</div>';

                        $html .= '</div>';
                        $html .= '</form>';
                    }

                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('action', function ($row) use ($authUser) {
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

                    $canEditDelete = false;
                    if ($authUser) {
                        if ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit()) {
                            $canEditDelete = true;
                        } elseif ($row->user_id === $authUser->id) {
                            $canEditDelete = true;
                        }
                    }

                    if ($canEditDelete) {
                        $editUrl = route('kepanitiaan.edit', $row->id);
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-info btn-quick-tag d-inline-flex align-items-center justify-content-center me-1" style="height: 32px; width: 32px; padding: 0;" data-id="' . $row->id . '" data-uraian="' . e($row->uraian_tugas) . '" data-tags="' . e(json_encode($row->taggedUsers->pluck('id')->toArray())) . '" title="Tag Rekan Kerja"><i class="bi bi-person-plus"></i></button>';
                        $btn .= '<a href="' . $editUrl . '" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center me-1" style="height: 32px; width: 32px; padding: 0;" title="Edit Tugas"><i class="bi bi-pencil"></i></a>';
                        $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.deleteKepanitiaan(' . $row->id . ')" class="btn btn-sm btn-outline-danger btn-delete-timer d-inline-flex align-items-center justify-content-center" style="height: 32px; width: 32px; padding: 0;" title="Hapus Tugas"><i class="bi bi-trash"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('voice_narration', function ($row) {
                    $uraian = e($row->uraian_tugas);
                    $estMulai = !empty($row->estimasi_tanggal_mulai) ? date('d/m/Y', strtotime($row->estimasi_tanggal_mulai)) : '';
                    $estJamMulai = !empty($row->estimasi_jam_mulai) ? substr($row->estimasi_jam_mulai, 0, 5) : '';
                    $estSelesai = !empty($row->estimasi_tanggal_selesai) ? date('d/m/Y', strtotime($row->estimasi_tanggal_selesai)) : '';
                    $estJamSelesai = !empty($row->estimasi_jam_selesai) ? substr($row->estimasi_jam_selesai, 0, 5) : '';

                    $narration = "Tugas: " . $uraian . ". ";
                    if ($estMulai) {
                        $narration .= "Estimasi mulai tanggal " . $estMulai;
                        if ($estJamMulai) $narration .= " pukul " . $estJamMulai;
                        $narration .= ". ";
                    }
                    if ($estSelesai) {
                        $narration .= "Estimasi selesai tanggal " . $estSelesai;
                        if ($estJamSelesai) $narration .= " pukul " . $estJamSelesai;
                        $narration .= ". ";
                    }
                    return $narration;
                })
                ->addColumn('attachment_status', function ($row) {
                    $hasFile = !empty($row->file) ? 1 : 0;
                    $hasUrl = !empty($row->url_external) ? 1 : 0;
                    return ($hasFile || $hasUrl) ? 1 : 0;
                })
                ->rawColumns(['checkbox', 'task_details', 'action'])
                ->with('overall_rekap', [
                    'total' => $totalCount,
                    'selesai' => $selesaiCount,
                    'proses' => $prosesCount,
                    'belum' => $belumCount,
                    'percent' => $percent
                ])
                ->make(true);
        }

        $usersWithJabatan = [];
        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                $usersWithJabatan = User::whereNotNull('jabatan')
                    ->where('jabatan', '!=', '')
                    ->orderBy('jabatan', 'asc')
                    ->get();
            } elseif ($authUser->isPimpinanUnit()) {
                $usersWithJabatan = User::whereNotNull('jabatan')
                    ->where('jabatan', '!=', '')
                    ->where('unit', $authUser->unit)
                    ->orderBy('jabatan', 'asc')
                    ->get();
            }
        }

        $periodeAkademiks = PeriodeAkademik::orderBy('nama_periode', 'desc')->get();
        $defaultPeriodeId = PeriodeAkademik::latest()->first()->id ?? null;

        return view('pages.kepanitiaan.index', compact('usersWithJabatan', 'periodeAkademiks', 'defaultPeriodeId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::orderBy('name')->get();
        $periodeAkademiks = PeriodeAkademik::orderBy('id', 'asc')->get();
        $defaultPeriode = PeriodeAkademik::first();
        $defaultPeriodeId = $defaultPeriode ? $defaultPeriode->id : null;

        return view('pages.kepanitiaan.create', compact('users', 'periodeAkademiks', 'defaultPeriodeId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KepanitiaanRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = $validated['user_id'] ?? Auth::id();

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('kepanitiaan', 'public');
        }

        if ($request->has('start_now') && $request->start_now) {
            $validated['waktu_mulai'] = now()->format('H:i:s');
            $validated['tanggal_mulai'] = now()->format('Y-m-d');
            $validated['status'] = 'Berjalan';
        } else {
            $validated['status'] = 'Belum Dimulai';
        }

        $kepanitiaan = Kepanitiaan::create($validated);

        if ($request->has('tagged_users')) {
            $kepanitiaan->taggedUsers()->sync($request->input('tagged_users'));
        }

        Alert::success('Berhasil', 'Kepanitiaan berhasil ditambahkan')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('kepanitiaan.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kepanitiaan $kepanitiaan)
    {
        $authUser = Auth::user();
        if ($authUser && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit()) {
            if ($kepanitiaan->user_id !== $authUser->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengedit tugas ini.');
            }
        }

        $users = User::orderBy('name')->get();
        $periodeAkademiks = PeriodeAkademik::orderBy('id', 'asc')->get();

        return view('pages.kepanitiaan.edit', compact('kepanitiaan', 'users', 'periodeAkademiks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KepanitiaanRequest $request, Kepanitiaan $kepanitiaan)
    {
        $authUser = Auth::user();
        if ($authUser && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit()) {
            if ($kepanitiaan->user_id !== $authUser->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk memperbarui tugas ini.');
            }
        }

        $validated = $request->validated();

        if ($request->hasFile('file')) {
            if ($kepanitiaan->file && Storage::disk('public')->exists($kepanitiaan->file)) {
                Storage::disk('public')->delete($kepanitiaan->file);
            }
            $validated['file'] = $request->file('file')->store('kepanitiaan', 'public');
        }

        $kepanitiaan->update($validated);

        $kepanitiaan->taggedUsers()->sync($request->input('tagged_users', []));

        Alert::success('Berhasil', 'Kepanitiaan berhasil diperbarui')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('kepanitiaan.index');
    }

    /**
     * Start task timer (Play button).
     */
    public function start(Request $request, Kepanitiaan $kepanitiaan)
    {
        $clientTime = $request->input('client_time') ?? now()->format('H:i:s');
        $clientDate = $request->input('client_date') ?? now()->format('Y-m-d');

        $kepanitiaan->update([
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
    public function stop(Request $request, Kepanitiaan $kepanitiaan)
    {
        $clientTime = $request->input('client_time') ?? now()->format('H:i:s');
        $clientDate = $request->input('client_date') ?? now()->format('Y-m-d');

        $updateData = [
            'waktu_selesai' => $clientTime,
            'tanggal_selesai' => $clientDate,
            'status' => 'Selesai',
        ];

        if ($request->hasFile('file')) {
            if ($kepanitiaan->file && Storage::disk('public')->exists($kepanitiaan->file)) {
                Storage::disk('public')->delete($kepanitiaan->file);
            }
            $updateData['file'] = $request->file('file')->store('kepanitiaan', 'public');
        }

        if ($request->filled('url_external')) {
            $updateData['url_external'] = $request->input('url_external');
        }

        $kepanitiaan->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Waktu penyelesaian tugas (' . substr($clientTime, 0, 5) . ') berhasil dicatat.',
        ]);
    }

    /**
     * Upload attachment inline.
     */
    public function uploadAttachment(Request $request, Kepanitiaan $kepanitiaan)
    {
        $updateData = [];

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            if ($uploadedFile->isValid()) {
                if ($kepanitiaan->file && Storage::disk('public')->exists($kepanitiaan->file)) {
                    Storage::disk('public')->delete($kepanitiaan->file);
                }
                $updateData['file'] = $uploadedFile->store('kepanitiaan', 'public');
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
            $kepanitiaan->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berkas dan link eksternal berhasil disimpan.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Kepanitiaan $kepanitiaan)
    {
        $authUser = Auth::user();
        if ($authUser && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit()) {
            if ($kepanitiaan->user_id !== $authUser->id) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki hak akses untuk menghapus tugas ini.'
                    ], 403);
                }
                abort(403, 'Anda tidak memiliki hak akses untuk menghapus tugas ini.');
            }
        }

        if ($kepanitiaan->file && Storage::disk('public')->exists($kepanitiaan->file)) {
            Storage::disk('public')->delete($kepanitiaan->file);
        }

        $kepanitiaan->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kepanitiaan berhasil dihapus'
            ]);
        }

        Alert::success('Berhasil', 'Kepanitiaan berhasil dihapus')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('kepanitiaan.index');
    }

    /**
     * Delete multiple records at once.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:kepanitiaans,id',
        ]);

        $authUser = Auth::user();
        $query = Kepanitiaan::whereIn('id', $request->ids);

        if ($authUser && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit()) {
            $query->where('user_id', $authUser->id);
        } elseif ($authUser && $authUser->isPimpinanUnit()) {
            $query->whereHas('user', function ($q) use ($authUser) {
                $q->where('unit', $authUser->unit);
            });
        }

        $items = $query->get();
        $count = 0;

        foreach ($items as $item) {
            if ($item->file && Storage::disk('public')->exists($item->file)) {
                Storage::disk('public')->delete($item->file);
            }
            $item->delete();
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menghapus {$count} rencana kerja kepanitiaan terpilih.",
        ]);
    }

    /**
     * Quick update tagged users via modal.
     */
    public function updateTags(Request $request, Kepanitiaan $kepanitiaan)
    {
        $authUser = Auth::user();
        if ($authUser && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit()) {
            if ($kepanitiaan->user_id !== $authUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk mengubah tag tugas ini.'
                ], 403);
            }
        }

        $request->validate([
            'tagged_users' => ['nullable', 'array'],
            'tagged_users.*' => ['exists:users,id'],
        ]);

        $kepanitiaan->taggedUsers()->sync($request->input('tagged_users', []));

        return response()->json([
            'success' => true,
            'message' => 'Rekan kerja berhasil diperbarui.'
        ]);
    }

    /**
     * Download Excel Import Template
     */
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Kepanitiaan');

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

        $headerRange = 'A1:G1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('15432D');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setARGB('FFFFFF');

        $sheet->setCellValue('A2', 'Kepanitiaan Laporan Kinerja Mingguan');
        $sheet->setCellValue('B2', '2026/2027 Semester Ganjil');
        $sheet->setCellValue('C2', 'Senin');
        $sheet->setCellValue('D2', date('Y-m-d'));
        $sheet->setCellValue('E2', '08:00');
        $sheet->setCellValue('F2', date('Y-m-d'));
        $sheet->setCellValue('G2', '16:00');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'template_import_kepanitiaan.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import from Excel file.
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

            $header = array_map(function ($h) {
                return strtolower(trim((string) $h));
            }, $rows[0]);

            $uraianIndex = array_search('uraian_tugas', $header) ?: 0;
            $periodeIndex = array_search('periode_akademik', $header);
            $hariIndex = array_search('hari', $header);
            $estTglMulaiIndex = array_search('estimasi_tanggal_mulai', $header);
            $estJamMulaiIndex = array_search('estimasi_jam_mulai', $header);
            $estTglSelesaiIndex = array_search('estimasi_tanggal_selesai', $header);
            $estJamSelesaiIndex = array_search('estimasi_jam_selesai', $header);

            $count = 0;
            $authUserId = auth()->id();
            $defaultPeriode = PeriodeAkademik::first();
            $fallbackPeriodeId = $request->input('periode_akademik_id') ? (int) $request->input('periode_akademik_id') : ($defaultPeriode ? $defaultPeriode->id : 1);

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $uraianTugas = isset($row[$uraianIndex]) ? trim((string) $row[$uraianIndex]) : '';

                if (empty($uraianTugas)) {
                    continue;
                }

                $rawPeriode = ($periodeIndex !== false && !empty($row[$periodeIndex])) ? trim((string) $row[$periodeIndex]) : null;
                $rowPeriodeId = $this->resolvePeriodeAkademik($rawPeriode, $fallbackPeriodeId);

                $hariVal = ($hariIndex !== false && !empty($row[$hariIndex])) ? trim((string) $row[$hariIndex]) : null;
                $rawEstTglMulai = ($estTglMulaiIndex !== false && !empty($row[$estTglMulaiIndex])) ? $row[$estTglMulaiIndex] : null;
                $rawEstJamMulai = ($estJamMulaiIndex !== false && !empty($row[$estJamMulaiIndex])) ? $row[$estJamMulaiIndex] : null;
                $rawEstTglSelesai = ($estTglSelesaiIndex !== false && !empty($row[$estTglSelesaiIndex])) ? $row[$estTglSelesaiIndex] : null;
                $rawEstJamSelesai = ($estJamSelesaiIndex !== false && !empty($row[$estJamSelesaiIndex])) ? $row[$estJamSelesaiIndex] : null;

                $estTglMulaiVal = $this->parseExcelDate($rawEstTglMulai);
                $estJamMulaiVal = $this->parseExcelTime($rawEstJamMulai);
                $estTglSelesaiVal = $this->parseExcelDate($rawEstTglSelesai);
                $estJamSelesaiVal = $this->parseExcelTime($rawEstJamSelesai);

                Kepanitiaan::create([
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

            Alert::success('Berhasil', "Berhasil mengimpor {$count} data Rencana Kerja Kepanitiaan.")
                ->toToast()
                ->autoClose(4000)
                ->timerProgressBar();

            return redirect()->back();
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Gagal memproses file Excel: ' . $e->getMessage())->toToast();
            return redirect()->back();
        }
    }

    protected function resolvePeriodeAkademik(?string $rawPeriodeName, int $fallbackPeriodeId): int
    {
        $rawPeriodeName = trim((string) $rawPeriodeName);
        if (empty($rawPeriodeName)) return $fallbackPeriodeId;

        $exact = PeriodeAkademik::where('nama_periode', $rawPeriodeName)->first();
        if ($exact) return $exact->id;

        $normalize = function (string $str): string {
            $s = strtolower($str);
            $s = str_replace('ganjil', 'gasal', $s);
            $s = str_replace('semester', '', $s);
            return preg_replace('/\s+/', ' ', trim($s));
        };

        $normInput = $normalize($rawPeriodeName);
        $allPeriodes = PeriodeAkademik::all();

        foreach ($allPeriodes as $p) {
            if ($normalize($p->nama_periode) === $normInput) return $p->id;
        }

        foreach ($allPeriodes as $p) {
            $normDb = $normalize($p->nama_periode);
            if (!empty($normInput) && (str_contains($normDb, $normInput) || str_contains($normInput, $normDb))) {
                return $p->id;
            }
        }

        $newPeriode = PeriodeAkademik::firstOrCreate(['nama_periode' => $rawPeriodeName]);
        return $newPeriode->id;
    }

    protected function parseExcelDate($value): ?string
    {
        if (empty($value)) return null;
        $valueStr = trim((string) $value);
        if ($valueStr === '' || $valueStr === '-') return null;

        if (is_numeric($valueStr) && (float) $valueStr > 20000) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $valueStr);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        $formats = ['Y-m-d', 'm/d/Y', 'n/j/Y', 'd/m/Y', 'j/n/Y', 'Y/m/d', 'd-m-Y', 'j-n-Y', 'Y.m.d', 'd.m.Y', 'm-d-Y', 'n-j-Y'];
        foreach ($formats as $fmt) {
            try {
                $dt = \DateTime::createFromFormat('!' . $fmt, $valueStr);
                if ($dt && $dt->format($fmt) === $valueStr) return $dt->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        try {
            $ts = strtotime($valueStr);
            if ($ts !== false && $ts > 0) return date('Y-m-d', $ts);
        } catch (\Throwable $e) {}

        return null;
    }

    protected function parseExcelTime($value): ?string
    {
        if (empty($value)) return null;
        $valueStr = trim((string) $value);
        if ($valueStr === '' || $valueStr === '-') return null;

        if (is_numeric($valueStr) && (float) $valueStr < 1.0) {
            try {
                $seconds = round((float) $valueStr * 86400);
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $secs = $seconds % 60;
                return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
            } catch (\Throwable $e) {}
        }

        $formats = ['H:i:s', 'H:i', 'G:i:s', 'G:i', 'g:i A', 'g:i a', 'h:i A', 'h:i a'];
        foreach ($formats as $fmt) {
            try {
                $dt = \DateTime::createFromFormat($fmt, $valueStr);
                if ($dt) return $dt->format('H:i:s');
            } catch (\Throwable $e) {}
        }

        try {
            $ts = strtotime($valueStr);
            if ($ts !== false) return date('H:i:s', $ts);
        } catch (\Throwable $e) {}

        return null;
    }

    /**
     * Export to Excel.
     */
    public function exportExcel(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isPimpinanUnit() && !$authUser->isAdmin())) {
            Alert::error('Gagal', 'Akses ditolak. Fitur ini khusus Pimpinan dan Admin.')->toToast();
            return redirect()->back();
        }

        $query = Kepanitiaan::with(['user', 'periodeAkademik', 'taggedUsers']);

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                // Admin -> All
            } elseif ($authUser->isPimpinanUnit()) {
                $query->whereHas('user', function ($q) use ($authUser) {
                    $q->where('unit', $authUser->unit);
                });
            } else {
                $query->where(function ($q) use ($authUser) {
                    $q->where('user_id', $authUser->id)
                      ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                          $qu->where('users.id', $authUser->id);
                      });
                });
            }
        }

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        if ($request->filled('user_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('user_id', $request->user_id)
                  ->orWhereHas('taggedUsers', function ($qu) use ($request) {
                      $qu->where('users.id', $request->user_id);
                  });
            });
        }

        if ($request->filled('jabatan')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function ($qu) use ($request) {
                    $qu->where('jabatan', $request->jabatan);
                })->orWhereHas('taggedUsers', function ($qt) use ($request) {
                    $qt->where('jabatan', $request->jabatan);
                });
            });
        }

        $items = $query->latest()->get();

        $namaStaff = 'SEMUA STAFF';
        $jabatanStaff = 'SEMUA JABATAN';
        $unitStaff = 'SEMUA UNIT';

        if ($request->filled('user_id') && ($u = User::find($request->user_id))) {
            $namaStaff = strtoupper($u->name);
            $jabatanStaff = strtoupper($u->jabatan ?? '-');
            $unitStaff = strtoupper($u->unit ?? '-');
        } elseif ($request->filled('jabatan') && ($u = User::where('jabatan', $request->jabatan)->first())) {
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
        } elseif ($authUser && !$authUser->isSuperAdmin()) {
            $namaStaff = strtoupper($authUser->name);
            $jabatanStaff = strtoupper($authUser->jabatan ?? '-');
            $unitStaff = strtoupper($authUser->unit ?? '-');
        }

        $periodeText = 'PERIODE AKADEMIK';
        if ($request->filled('periode_akademik_id') && ($p = PeriodeAkademik::find($request->periode_akademik_id))) {
            $periodeText = strtoupper($p->nama_periode);
        } elseif ($items->count() > 0 && $items->first()->periodeAkademik) {
            $periodeText = strtoupper($items->first()->periodeAkademik->nama_periode);
        } else {
            $latestP = PeriodeAkademik::latest()->first();
            if ($latestP) $periodeText = strtoupper($latestP->nama_periode);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Kepanitiaan');

        // Header styles
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI RENCANA KERJA KEPANITIAAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A3', 'Nama Pegawai:');
        $sheet->setCellValue('B3', $namaStaff);
        $sheet->setCellValue('A4', 'Jabatan:');
        $sheet->setCellValue('B4', $jabatanStaff);
        $sheet->setCellValue('A5', 'Unit Kerja:');
        $sheet->setCellValue('B5', $unitStaff);
        $sheet->setCellValue('A6', 'Periode:');
        $sheet->setCellValue('B6', $periodeText);

        $sheet->getStyle('A3:A6')->getFont()->setBold(true);

        $cols = ['No', 'Uraian Tugas', 'Hari', 'Estimasi Mulai', 'Estimasi Selesai', 'Realisasi Mulai', 'Realisasi Selesai', 'Durasi', 'Rekan Kerja (Tag)', 'Status'];
        $rowNum = 8;
        foreach ($cols as $idx => $colName) {
            $colLetter = chr(65 + $idx);
            $sheet->setCellValue($colLetter . $rowNum, $colName);
        }

        $headerRange = 'A8:J8';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('15432D');

        $i = 1;
        foreach ($items as $item) {
            $rowNum++;
            $estM = ($item->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($item->estimasi_tanggal_mulai)) : '') . ($item->estimasi_jam_mulai ? ' ' . substr($item->estimasi_jam_mulai, 0, 5) : '');
            $estS = ($item->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($item->estimasi_tanggal_selesai)) : '') . ($item->estimasi_jam_selesai ? ' ' . substr($item->estimasi_jam_selesai, 0, 5) : '');
            $realM = ($item->tanggal_mulai ? date('d/m/Y', strtotime($item->tanggal_mulai)) : '') . ($item->waktu_mulai ? ' ' . substr($item->waktu_mulai, 0, 5) : '');
            $realS = ($item->tanggal_selesai ? date('d/m/Y', strtotime($item->tanggal_selesai)) : '') . ($item->waktu_selesai ? ' ' . substr($item->waktu_selesai, 0, 5) : '');

            // Calculate duration
            $dur = '-';
            if ($item->waktu_mulai && $item->waktu_selesai && $item->waktu_selesai !== '00:00:00') {
                $startTs = strtotime(($item->tanggal_mulai ?: date('Y-m-d')) . ' ' . $item->waktu_mulai);
                $endTs = strtotime(($item->tanggal_selesai ?: date('Y-m-d')) . ' ' . $item->waktu_selesai);
                $diff = max(0, $endTs - $startTs);
                $h = floor($diff / 3600);
                $m = floor(($diff % 3600) / 60);
                $dur = ($h > 0 ? $h . 'j ' : '') . $m . 'm';
            }

            $sheet->setCellValue('A' . $rowNum, $i++);
            $sheet->setCellValue('B' . $rowNum, $item->uraian_tugas);
            $sheet->setCellValue('C' . $rowNum, $item->hari ?: '-');
            $sheet->setCellValue('D' . $rowNum, $estM ?: '-');
            $sheet->setCellValue('E' . $rowNum, $estS ?: '-');
            $sheet->setCellValue('F' . $rowNum, $realM ?: '-');
            $sheet->setCellValue('G' . $rowNum, $realS ?: '-');
            $sheet->setCellValue('H' . $rowNum, $dur);
            $sheet->setCellValue('I' . $rowNum, $item->taggedUsers->pluck('name')->implode(', ') ?: '-');
            $sheet->setCellValue('J' . $rowNum, $item->status);
        }

        // Borders and auto widths
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'CCCCCC'],
                ],
            ],
        ];
        $sheet->getStyle('A8:J' . $rowNum)->applyFromArray($styleArray);

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $safeStaff = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $namaStaff)));
        $safePeriode = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $periodeText)));
        $filename = ($safeStaff ?: 'Semua Staff') . '_' . ($safePeriode ?: 'Periode Akademik') . '_laporan rencana kerja kepanitiaan.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export to PDF.
     */
    public function exportPdf(Request $request)
    {
        $authUser = auth()->user();
        $query = Kepanitiaan::with(['user', 'periodeAkademik', 'taggedUsers']);

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                if ($request->filled('user_id')) {
                    $query->where(function ($q) use ($request) {
                        $q->where('user_id', $request->user_id)
                          ->orWhereHas('taggedUsers', function ($qu) use ($request) {
                              $qu->where('users.id', $request->user_id);
                          });
                    });
                }
            } elseif ($authUser->isPimpinanUnit()) {
                $query->whereHas('user', function ($q) use ($authUser) {
                    $q->where('unit', $authUser->unit);
                });
                if ($request->filled('user_id')) {
                    $query->where(function ($q) use ($request) {
                        $q->where('user_id', $request->user_id)
                          ->orWhereHas('taggedUsers', function ($qu) use ($request) {
                              $qu->where('users.id', $request->user_id);
                          });
                    });
                }
            } else {
                $query->where(function ($q) use ($authUser) {
                    $q->where('user_id', $authUser->id)
                      ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                          $qu->where('users.id', $authUser->id);
                      });
                });
            }
        }

        if ($request->filled('jabatan')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function ($qu) use ($request) {
                    $qu->where('jabatan', $request->jabatan);
                })->orWhereHas('taggedUsers', function ($qt) use ($request) {
                    $qt->where('jabatan', $request->jabatan);
                });
            });
        }

        $items = $query->latest()->get();

        $namaStaff = 'SEMUA STAFF';
        $jabatanStaff = 'SEMUA JABATAN';
        $unitStaff = 'SEMUA UNIT';

        if ($request->filled('user_id') && ($u = User::find($request->user_id))) {
            $namaStaff = strtoupper($u->name);
            $jabatanStaff = strtoupper($u->jabatan ?? '-');
            $unitStaff = strtoupper($u->unit ?? '-');
        } elseif ($request->filled('jabatan') && ($u = User::where('jabatan', $request->jabatan)->first())) {
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
        } elseif ($authUser && !$authUser->isSuperAdmin()) {
            $namaStaff = strtoupper($authUser->name);
            $jabatanStaff = strtoupper($authUser->jabatan ?? '-');
            $unitStaff = strtoupper($authUser->unit ?? '-');
        }

        $periodeText = 'PERIODE AKADEMIK';
        if ($request->filled('periode_akademik_id') && ($p = PeriodeAkademik::find($request->periode_akademik_id))) {
            $periodeText = strtoupper($p->nama_periode);
        } elseif ($items->count() > 0 && $items->first()->periodeAkademik) {
            $periodeText = strtoupper($items->first()->periodeAkademik->nama_periode);
        } else {
            $latestP = PeriodeAkademik::latest()->first();
            if ($latestP) {
                $periodeText = strtoupper($latestP->nama_periode);
            }
        }

        $safeStaff = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $namaStaff)));
        $safePeriode = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $periodeText)));
        $filename = ($safeStaff ?: 'Semua Staff') . '_' . ($safePeriode ?: 'Periode Akademik') . '_laporan rencana kerja kepanitiaan.pdf';

        $pdf = Pdf::loadView('pages.kepanitiaan.pdf', compact('items', 'namaStaff', 'jabatanStaff', 'unitStaff', 'periodeText'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
