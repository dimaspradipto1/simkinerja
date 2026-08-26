<?php

namespace App\Http\Controllers;

use App\Http\Requests\KegiatanRequest;
use App\Models\PeriodeAkademik;
use App\Models\Kegiatan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class KegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        if ($request->ajax()) {
            $query = Kegiatan::with(['user', 'periodeAkademik', 'taggedUsers', 'milestones']);

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
                ->addColumn('task_details', function ($row) use ($authUser) {
                    $html = '<div class="d-flex flex-column gap-2 py-1 w-100" style="max-width: 100%; box-sizing: border-box;">';
                    
                    // 1. Task Title & Action Buttons Bar
                    $actionBtns = $this->renderActionButtons($row, $authUser, 'kegiatan', 'deleteKegiatan');

                    $html .= '<div class="p-2 px-3 bg-light rounded border mb-1 shadow-2xs d-flex flex-column gap-2">';
                    $html .= '  <div class="text-dark fw-bold fs-6 lh-sm d-flex align-items-center gap-2 text-break" style="font-size: 0.98rem;">';
                    $html .= '      <i class="bi bi-card-checklist text-primary fs-5"></i>';
                    $html .= '      <span>' . e($row->uraian_tugas) . '</span>';
                    $html .= '  </div>';
                    $html .= '  <div class="pt-2 border-top d-flex flex-wrap align-items-center justify-content-start gap-2">';
                    $html .= '      <span class="text-secondary small fw-semibold me-1"><i class="bi bi-gear-fill me-1"></i>Aksi & Status:</span>';
                    $html .=        $actionBtns;
                    $html .= '  </div>';
                    $html .= '</div>';

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

                    // 2.5 Tagged Users (Rekan Kerja)
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
                    if ($row->waktu_selesai) {
                        $tglSelesai = $row->tanggal_selesai ? date('d/m/Y', strtotime($row->tanggal_selesai)) : '';
                        $jamSelesai = substr($row->waktu_selesai, 0, 5);
                        $selesaiFormatted = $tglSelesai ? ($tglSelesai . ' (' . $jamSelesai . ' WIB)') : '(' . $jamSelesai . ' WIB)';
                    }

                    $html .= '<div class="p-2 px-3 bg-white rounded border border-light-subtle shadow-2xs mt-1">';
                    $html .= '  <div class="row g-2 small">';
                    $html .= '      <div class="col-12 col-md-4"><span class="text-muted d-block"><i class="bi bi-clock-history me-1 text-secondary"></i>Estimasi:</span><span class="fw-semibold text-dark">' . $estFormatted . '</span></div>';
                    $html .= '      <div class="col-12 col-md-4"><span class="text-muted d-block"><i class="bi bi-play-circle me-1 text-success"></i>Mulai:</span><span class="fw-semibold text-dark">' . $mulaiFormatted . '</span></div>';
                    $html .= '      <div class="col-12 col-md-4"><span class="text-muted d-block"><i class="bi bi-check-circle me-1 text-primary"></i>Selesai:</span><span class="fw-semibold text-dark">' . $selesaiFormatted . '</span></div>';
                    $html .= '  </div>';
                    $html .= '</div>';

                    // 4. Attachments & Work Results
                    $hasContent = false;
                    $contentHtml = '<div class="p-2 px-3 bg-white rounded border border-light-subtle shadow-2xs mt-1 d-flex flex-column gap-2">';

                    $attachRow = '<div class="d-flex flex-wrap align-items-center gap-2">';
                    $hasAttach = false;
                    if ($row->file) {
                        $hasAttach = true;
                        $hasContent = true;
                        $attachRow .= '<a href="' . asset('storage/' . $row->file) . '" target="_blank" class="btn btn-xs btn-outline-primary d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 0.75rem;"><i class="bi bi-file-earmark-arrow-down"></i> Unduh Berkas</a>';
                    }
                    if ($row->url_external) {
                        $hasAttach = true;
                        $hasContent = true;
                        $attachRow .= '<a href="' . e($row->url_external) . '" target="_blank" class="btn btn-xs btn-outline-info d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 0.75rem;"><i class="bi bi-box-arrow-up-right"></i> Tautan / Link</a>';
                    }
                    $attachRow .= '</div>';

                    if ($hasAttach) {
                        $contentHtml .= $attachRow;
                    }

                    if ($row->hasil_kerja) {
                        $hasContent = true;
                        $contentHtml .= '<div class="text-secondary small" style="font-size: 0.8rem;"><strong class="text-dark"><i class="bi bi-check2-square text-success me-1"></i>Hasil Kerja:</strong> ' . nl2br(e($row->hasil_kerja)) . '</div>';
                    }

                    if ($row->rencana_tindak_lanjut) {
                        $hasContent = true;
                        $contentHtml .= '<div class="text-secondary small" style="font-size: 0.8rem;"><strong class="text-dark"><i class="bi bi-arrow-right-circle text-warning me-1"></i>Tindak Lanjut:</strong> ' . nl2br(e($row->rencana_tindak_lanjut)) . '</div>';
                    }

                    $contentHtml .= '</div>';

                    if ($hasContent) {
                        $html .= $contentHtml;
                    }

                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['checkbox', 'task_details'])
                ->with([
                    'totalCount' => $totalCount,
                    'selesaiCount' => $selesaiCount,
                    'prosesCount' => $prosesCount,
                    'belumCount' => $belumCount,
                    'percent' => $percent,
                ])
                ->make(true);
        }

        $jabatanList = User::whereNotNull('jabatan')->where('jabatan', '!=', '')->distinct()->pluck('jabatan');
        $periodeAkademiks = PeriodeAkademik::orderBy('id', 'asc')->get();
        $users = User::orderBy('name')->get();

        return view('pages.kegiatan.index', compact('jabatanList', 'periodeAkademiks', 'users'));
    }

    /**
     * Helper to render unified task action buttons.
     */
    private function renderActionButtons($row, $authUser, $routePrefix, $deleteJsFunc)
    {
        $status = $row->status;
        $waktuMulai = $row->waktu_mulai;
        $waktuSelesai = $row->waktu_selesai;

        $btn = '<div class="d-inline-flex flex-wrap gap-1 align-items-center">';

        $allMilestonesSelesai = false;
        if ($row->milestones && $row->milestones->count() > 0) {
            $allMilestonesSelesai = $row->milestones->every(function ($m) {
                return $m->status === 'Selesai';
            });
        }

        if ($allMilestonesSelesai) {
            $status = 'Selesai';
            $waktuSelesai = true;
        }

        if (!$waktuMulai && $status !== 'Di-pause' && $status !== 'Selesai') {
            $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.startTimer(' . $row->id . ')" class="btn btn-sm text-white fw-bold text-nowrap px-3 me-1 btn-start-timer" style="background-color: #15432d; border-color: #15432d; height: 32px; min-width: 85px; cursor: pointer;"><i class="bi bi-play-fill me-1"></i> Mulai</button>';
        } elseif (($waktuMulai || $status === 'Di-pause') && !$waktuSelesai && $status !== 'Selesai') {
            if ($status === 'Di-pause') {
                $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.startTimer(' . $row->id . ')" class="btn btn-sm btn-success text-white fw-bold text-nowrap px-2 me-1 btn-resume-timer" style="height: 32px; cursor: pointer;" title="Lanjut Pekerjaan"><i class="bi bi-play-fill me-1"></i> Lanjut</button>';
            } else {
                $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.pauseTaskTimer(' . $row->id . ', \'' . $routePrefix . '\')" class="btn btn-sm btn-warning text-dark fw-bold text-nowrap px-2 me-1 btn-pause-timer" style="height: 32px; cursor: pointer;" title="Jeda Pekerjaan"><i class="bi bi-pause-fill me-1"></i> Pause</button>';
            }
            $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.stopTimer(' . $row->id . ')" class="btn btn-sm btn-danger text-white fw-bold text-nowrap px-2 me-1 btn-stop-timer" style="background-color: #8b0000; border-color: #8b0000; height: 32px; cursor: pointer;" title="Selesaikan Pekerjaan"><i class="bi bi-stop-fill me-1"></i> Berhenti</button>';
        } else {
            $btn .= '<button type="button" class="btn btn-sm text-white fw-bold text-nowrap px-3 me-1 disabled" style="background-color: #2d6a4f; border-color: #2d6a4f; opacity: 1; height: 32px; cursor: default;"><i class="bi bi-check-circle-fill me-1"></i> Selesai</button>';
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
            $editUrl = route($routePrefix . '.edit', $row->id);
            $btn .= '<button type="button" class="btn btn-sm btn-outline-info btn-quick-tag d-inline-flex align-items-center justify-content-center me-1" style="height: 32px; width: 32px; padding: 0;" data-id="' . $row->id . '" data-uraian="' . e($row->uraian_tugas) . '" data-tags="' . e(json_encode($row->taggedUsers->pluck('id')->toArray())) . '" title="Tag Rekan Kerja"><i class="bi bi-person-plus"></i></button>';
            $btn .= '<a href="' . $editUrl . '" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center me-1" style="height: 32px; width: 32px; padding: 0;" title="Edit Tugas"><i class="bi bi-pencil"></i></a>';
            $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.' . $deleteJsFunc . '(' . $row->id . ')" class="btn btn-sm btn-outline-danger btn-delete-timer d-inline-flex align-items-center justify-content-center" style="height: 32px; width: 32px; padding: 0;" title="Hapus Tugas"><i class="bi bi-trash"></i></button>';
        }
        $btn .= '</div>';
        return $btn;
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

        return view('pages.kegiatan.create', compact('users', 'periodeAkademiks', 'defaultPeriodeId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KegiatanRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = $validated['user_id'] ?? Auth::id();

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('kegiatan', 'public');
        }

        if ($request->has('start_now') && $request->start_now) {
            $validated['waktu_mulai'] = now()->format('H:i:s');
            $validated['tanggal_mulai'] = now()->format('Y-m-d');
            $validated['status'] = 'Berjalan';
        } else {
            $validated['status'] = 'Belum Dimulai';
        }

        $kegiatan = Kegiatan::create($validated);

        if ($request->has('tagged_users')) {
            $kegiatan->taggedUsers()->sync($request->input('tagged_users'));
        }

        Alert::success('Berhasil', 'Kegiatan berhasil ditambahkan')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('kegiatan.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kegiatan $kegiatan)
    {
        $authUser = Auth::user();
        if ($authUser && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit()) {
            if ($kegiatan->user_id !== $authUser->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengedit tugas ini.');
            }
        }

        $isPimpinanOrAdmin = $authUser && ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit());
        $estimasiLocked = !$isPimpinanOrAdmin;

        $users = User::orderBy('name')->get();
        $periodeAkademiks = PeriodeAkademik::orderBy('id', 'asc')->get();

        return view('pages.kegiatan.edit', compact('kegiatan', 'users', 'periodeAkademiks', 'estimasiLocked'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KegiatanRequest $request, Kegiatan $kegiatan)
    {
        $authUser = Auth::user();
        if ($authUser && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit()) {
            if ($kegiatan->user_id !== $authUser->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk memperbarui tugas ini.');
            }
        }

        $isPimpinanOrAdmin = $authUser && ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit());

        $validated = $request->validated();

        $estimasiFields = [
            'estimasi_tanggal_mulai', 'estimasi_tanggal_selesai', 'estimasi_jam_mulai', 'estimasi_jam_selesai',
            'tanggal_mulai', 'tanggal_selesai', 'waktu_mulai', 'waktu_selesai',
        ];

        if (!$isPimpinanOrAdmin) {
            foreach ($estimasiFields as $field) {
                unset($validated[$field]);
            }
        } elseif ($kegiatan->estimasi_unlock_requested_at) {
            $validated['estimasi_unlock_reason'] = null;
            $validated['estimasi_unlock_requested_at'] = null;
        }

        if ($request->hasFile('file')) {
            if ($kegiatan->file && Storage::disk('public')->exists($kegiatan->file)) {
                Storage::disk('public')->delete($kegiatan->file);
            }
            $validated['file'] = $request->file('file')->store('kegiatan', 'public');
        }

        $kegiatan->update($validated);

        $kegiatan->taggedUsers()->sync($request->input('tagged_users', []));

        Alert::success('Berhasil', 'Kegiatan berhasil diperbarui')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('kegiatan.index');
    }

    /**
     * Request unlock estimasi from pimpinan.
     */
    public function requestUnlockEstimasi(Request $request, Kegiatan $kegiatan)
    {
        $authUser = Auth::user();

        if (!$authUser || $kegiatan->user_id !== $authUser->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk tugas ini.');
        }

        $request->validate([
            'estimasi_unlock_reason' => 'required|string|max:1000',
        ]);

        $kegiatan->update([
            'estimasi_unlock_reason' => $request->input('estimasi_unlock_reason'),
            'estimasi_unlock_requested_at' => now(),
        ]);

        Alert::success('Berhasil', 'Permintaan perubahan estimasi telah dikirim ke pimpinan.')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->back();
    }

    /**
     * Helper to automatically log milestone points for task timer events.
     */
    private function autoLogMilestone($kegiatan, $action, $now)
    {
        if ($action === 'start') {
            $pausedMilestones = $kegiatan->milestones()->where('status', 'Di-pause')->get();
            foreach ($pausedMilestones as $pm) {
                $pm->status = 'Selesai';
                if (!$pm->waktu_selesai) {
                    $pm->waktu_selesai = $now;
                }
                $pm->save();
            }

            $runningMilestone = $kegiatan->milestones()->where('status', 'Berjalan')->first();
            if (!$runningMilestone) {
                $milestoneCount = $kegiatan->milestones()->count();
                $title = ($milestoneCount === 0) ? 'Mulai Pelaksanaan Kegiatan' : 'Melanjutkan Kegiatan';
                $kegiatan->milestones()->create([
                    'nama_milestone' => $title,
                    'status' => 'Berjalan',
                    'waktu_mulai' => $now,
                    'last_started_at' => $now,
                    'durasi_detik' => 0,
                ]);
            }
        } elseif ($action === 'pause') {
            $runningMilestones = $kegiatan->milestones()->where('status', 'Berjalan')->get();
            foreach ($runningMilestones as $m) {
                if ($m->last_started_at) {
                    $elapsedSeconds = (int) abs($now->diffInSeconds($m->last_started_at));
                    $m->durasi_detik = (int) ($m->durasi_detik ?? 0) + $elapsedSeconds;
                }
                $m->last_started_at = null;
                $m->status = 'Di-pause';
                $m->save();
            }
        } elseif ($action === 'stop') {
            $activeMilestones = $kegiatan->milestones()->whereIn('status', ['Berjalan', 'Di-pause'])->get();
            foreach ($activeMilestones as $m) {
                if ($m->status === 'Berjalan' && $m->last_started_at) {
                    $elapsedSeconds = (int) abs($now->diffInSeconds($m->last_started_at));
                    $m->durasi_detik = (int) ($m->durasi_detik ?? 0) + $elapsedSeconds;
                }
                $m->last_started_at = null;
                $m->status = 'Selesai';
                if (!$m->waktu_selesai) {
                    $m->waktu_selesai = $now;
                }
                $m->save();
            }

            $lastMilestone = $kegiatan->milestones()->latest()->first();
            if (!$lastMilestone || $lastMilestone->nama_milestone !== 'Selesai Kegiatan') {
                $kegiatan->milestones()->create([
                    'nama_milestone' => 'Selesai Kegiatan',
                    'status' => 'Selesai',
                    'waktu_mulai' => $now,
                    'waktu_selesai' => $now,
                    'durasi_detik' => 0,
                ]);
            }
        }
    }

    /**
     * Start task timer.
     */
    public function start(Request $request, Kegiatan $kegiatan)
    {
        $now = now();
        $isResume = ($kegiatan->status === 'Di-pause');

        $updateData = [
            'status' => 'Berjalan',
            'is_paused' => false,
            'paused_at' => null,
        ];

        if (!$kegiatan->waktu_mulai) {
            $updateData['waktu_mulai'] = $now->format('H:i:s');
            $updateData['tanggal_mulai'] = $now->format('Y-m-d');
        }

        if ($isResume && $kegiatan->paused_at) {
            $pausedSeconds = (int) abs($now->diffInSeconds($kegiatan->paused_at));
            $updateData['total_paused_seconds'] = (int) ($kegiatan->total_paused_seconds ?? 0) + $pausedSeconds;
        }

        $kegiatan->update($updateData);

        $this->autoLogMilestone($kegiatan, 'start', $now);

        return response()->json([
            'success' => true,
            'message' => $isResume ? 'Kegiatan berhasil dilanjutkan.' : 'Kegiatan berhasil dimulai.',
            'waktu_mulai' => $kegiatan->waktu_mulai,
            'tanggal_mulai' => $kegiatan->tanggal_mulai,
            'status' => 'Berjalan',
        ]);
    }

    /**
     * Pause task timer.
     */
    public function pause(Request $request, Kegiatan $kegiatan)
    {
        $now = now();

        $kegiatan->update([
            'status' => 'Di-pause',
            'is_paused' => true,
            'paused_at' => $now,
        ]);

        $this->autoLogMilestone($kegiatan, 'pause', $now);

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan berhasil dijeda (di-pause).',
            'status' => 'Di-pause',
        ]);
    }

    /**
     * Stop task timer.
     */
    public function stop(Request $request, Kegiatan $kegiatan)
    {
        $now = now();

        $request->validate([
            'hasil_kerja' => 'nullable|string',
            'rencana_tindak_lanjut' => 'nullable|string',
        ]);

        $updateData = [
            'waktu_selesai' => $now->format('H:i:s'),
            'tanggal_selesai' => $now->format('Y-m-d'),
            'status' => 'Selesai',
            'is_paused' => false,
            'paused_at' => null,
            'hasil_kerja' => $request->input('hasil_kerja', $kegiatan->hasil_kerja),
            'rencana_tindak_lanjut' => $request->input('rencana_tindak_lanjut', $kegiatan->rencana_tindak_lanjut),
        ];

        if ($request->hasFile('file')) {
            if ($kegiatan->file && Storage::disk('public')->exists($kegiatan->file)) {
                Storage::disk('public')->delete($kegiatan->file);
            }
            $updateData['file'] = $request->file('file')->store('kegiatan', 'public');
        }

        if ($request->filled('url_external')) {
            $updateData['url_external'] = $request->input('url_external');
        }

        $kegiatan->update($updateData);

        $this->autoLogMilestone($kegiatan, 'stop', $now);

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan berhasil diselesaikan.',
            'waktu_selesai' => $kegiatan->waktu_selesai,
            'tanggal_selesai' => $kegiatan->tanggal_selesai,
            'status' => 'Selesai',
        ]);
    }

    /**
     * Upload attachment.
     */
    public function uploadAttachment(Request $request, Kegiatan $kegiatan)
    {
        $request->validate([
            'file' => 'nullable|file|max:10240',
            'url_external' => 'nullable|url',
        ]);

        $updateData = [];

        if ($request->hasFile('file')) {
            if ($kegiatan->file && Storage::disk('public')->exists($kegiatan->file)) {
                Storage::disk('public')->delete($kegiatan->file);
            }
            $updateData['file'] = $request->file('file')->store('kegiatan', 'public');
        }

        if ($request->filled('url_external')) {
            $updateData['url_external'] = $request->input('url_external');
        }

        $kegiatan->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Bukti kegiatan berhasil diunggah.',
        ]);
    }

    /**
     * Bulk delete items.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.'], 400);
        }

        $authUser = Auth::user();
        $query = Kegiatan::whereIn('id', $ids);

        if ($authUser && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit()) {
            $query->where('user_id', $authUser->id);
        }

        $items = $query->get();
        foreach ($items as $item) {
            if ($item->file && Storage::disk('public')->exists($item->file)) {
                Storage::disk('public')->delete($item->file);
            }
            $item->delete();
        }

        return response()->json([
            'success' => true,
            'message' => count($items) . ' data kegiatan berhasil dihapus.',
        ]);
    }

    /**
     * Update tagged users.
     */
    public function updateTags(Request $request, Kegiatan $kegiatan)
    {
        $request->validate([
            'tagged_users' => 'nullable|array',
            'tagged_users.*' => 'exists:users,id',
        ]);

        $kegiatan->taggedUsers()->sync($request->input('tagged_users', []));

        return response()->json([
            'success' => true,
            'message' => 'Rekan kerja berhasil diperbarui.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kegiatan $kegiatan)
    {
        $authUser = Auth::user();
        if ($authUser && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit()) {
            if ($kegiatan->user_id !== $authUser->id) {
                return response()->json(['success' => false, 'message' => 'Anda tidak berhak menghapus data ini.'], 403);
            }
        }

        if ($kegiatan->file && Storage::disk('public')->exists($kegiatan->file)) {
            Storage::disk('public')->delete($kegiatan->file);
        }

        $kegiatan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan berhasil dihapus.',
        ]);
    }

    /**
     * Export to PDF.
     */
    public function exportPdf(Request $request)
    {
        $authUser = Auth::user();
        $query = Kegiatan::with(['user', 'periodeAkademik', 'taggedUsers'])->latest();

        if ($authUser && !$request->filled('jabatan') && !$authUser->isSuperAdmin()) {
            $query->where(function ($q) use ($authUser) {
                $q->where('user_id', $authUser->id)
                  ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                      $qu->where('users.id', $authUser->id);
                  });
            });
        }

        if ($request->filled('jabatan')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('jabatan', $request->jabatan);
            });
        }

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        $kegiatans = $query->get();
        $periode = $request->filled('periode_akademik_id') ? PeriodeAkademik::find($request->periode_akademik_id) : null;

        $pdf = Pdf::loadView('pages.kegiatan.pdf', compact('kegiatans', 'periode'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Kegiatan_' . date('YmdHis') . '.pdf');
    }
}
