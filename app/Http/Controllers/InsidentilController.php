<?php

namespace App\Http\Controllers;

use App\Http\Requests\InsidentilRequest;
use App\Models\PeriodeAkademik;
use App\Models\Insidentil;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class InsidentilController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        if ($request->ajax()) {
            $query = Insidentil::with(['user', 'periodeAkademik', 'taggedUsers', 'milestones']);

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
                    $html = '<div class="d-flex align-items-start">';
                    $html .= '<div class="me-2 mt-1"><i class="bi bi-card-checklist text-primary fs-5"></i></div>';
                    $html .= '<div>';
                    $html .= '<div class="fw-bold text-dark mb-1" style="font-size: 1rem;">' . e($row->uraian_tugas) . '</div>';
                    
                    if ($row->user) {
                        $html .= '<div class="small text-muted mb-1"><i class="bi bi-person-circle me-1"></i><strong>Pembuat:</strong> ' . e($row->user->name) . ' <span class="badge bg-light text-dark border ms-1">' . e($row->user->jabatan ?? '-') . '</span></div>';
                    }
                    
                    if ($row->taggedUsers->count() > 0) {
                        $html .= '<div class="small text-muted"><i class="bi bi-people-fill me-1 text-info"></i><strong>Rekan Kerja:</strong> ';
                        $tags = [];
                        foreach ($row->taggedUsers as $tu) {
                            $tags[] = '<span class="badge bg-info text-white me-1">' . e($tu->name) . '</span>';
                        }
                        $html .= implode('', $tags) . '</div>';
                    }
                    
                    $html .= \App\Helpers\MilestoneHelper::renderWidget($row->milestones, $row->id, \App\Models\Insidentil::class);
                    
                    $html .= '</div></div>';
                    return $html;
                })
                ->addColumn('estimasi_waktu', function ($row) {
                    $tglMulai = $row->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($row->estimasi_tanggal_mulai)) : '-';
                    $tglSelesai = $row->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($row->estimasi_tanggal_selesai)) : '-';
                    $jamMulai = $row->estimasi_jam_mulai ? date('H:i', strtotime($row->estimasi_jam_mulai)) : '-';
                    $jamSelesai = $row->estimasi_jam_selesai ? date('H:i', strtotime($row->estimasi_jam_selesai)) : '-';

                    return '<div class="small">' .
                           '<div><i class="bi bi-calendar-event me-1 text-secondary"></i>' . $tglMulai . ' s/d ' . $tglSelesai . '</div>' .
                           '<div><i class="bi bi-clock me-1 text-secondary"></i>' . $jamMulai . ' - ' . $jamSelesai . ' WIB</div>' .
                           '</div>';
                })
                ->addColumn('waktu_realisasi', function ($row) {
                    $tglMulai = $row->tanggal_mulai ? date('d/m/Y', strtotime($row->tanggal_mulai)) : '-';
                    $tglSelesai = $row->tanggal_selesai ? date('d/m/Y', strtotime($row->tanggal_selesai)) : '-';
                    $jamMulai = $row->waktu_mulai ? date('H:i', strtotime($row->waktu_mulai)) : '-';
                    $jamSelesai = $row->waktu_selesai ? date('H:i', strtotime($row->waktu_selesai)) : '-';

                    return '<div class="small">' .
                           '<div><i class="bi bi-calendar-check me-1 text-success"></i>' . $tglMulai . ' s/d ' . $tglSelesai . '</div>' .
                           '<div><i class="bi bi-stopwatch me-1 text-success"></i>' . $jamMulai . ' - ' . $jamSelesai . ' WIB</div>' .
                           '</div>';
                })
                ->addColumn('attachment', function ($row) {
                    $html = '';
                    if ($row->file) {
                        $url = asset('storage/' . $row->file);
                        $html .= '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-outline-primary mb-1 d-block"><i class="bi bi-file-earmark-text me-1"></i>Lihat Berkas</a>';
                    }
                    if ($row->url_external) {
                        $html .= '<a href="' . e($row->url_external) . '" target="_blank" class="btn btn-sm btn-outline-info d-block"><i class="bi bi-link-45deg me-1"></i>Tautan Luar</a>';
                    }
                    if (!$row->file && !$row->url_external) {
                        $html = '<span class="text-muted small">Tidak ada</span>';
                    }
                    return $html;
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row->status === 'Selesai') {
                        return '<span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Selesai</span>';
                    } elseif ($row->status === 'Proses' || $row->status === 'Berjalan') {
                        return '<span class="badge bg-primary px-2 py-1"><i class="bi bi-play-circle me-1"></i>Proses</span>';
                    }
                    return '<span class="badge bg-secondary px-2 py-1"><i class="bi bi-x-circle me-1"></i>Belum Dimulai</span>';
                })
                ->addColumn('action', function ($row) use ($authUser) {
                    $btn = '<div class="btn-group" role="group">';
                    
                    // Start/Pause/Stop Timer buttons
                    if ($row->status === 'Belum Dimulai') {
                        $btn .= '<button type="button" class="btn btn-success btn-sm btn-start" data-id="' . $row->id . '" title="Mulai Pekerjaan"><i class="bi bi-play-fill me-1"></i> Mulai</button>';
                    } elseif ($row->status === 'Di-pause') {
                        $btn .= '<button type="button" class="btn btn-success btn-sm btn-start" data-id="' . $row->id . '" title="Lanjut Pekerjaan"><i class="bi bi-play-fill me-1"></i> Lanjut</button>';
                        $btn .= '<button type="button" class="btn btn-danger btn-sm btn-stop" data-id="' . $row->id . '" title="Selesaikan Pekerjaan"><i class="bi bi-stop-fill me-1"></i> Berhenti</button>';
                    } elseif ($row->status === 'Proses' || $row->status === 'Berjalan') {
                        $btn .= '<button type="button" class="btn btn-warning btn-sm text-dark fw-bold" onclick="window.pauseTaskTimer(' . $row->id . ', \'insidentil\')" title="Jeda Pekerjaan"><i class="bi bi-pause-fill me-1"></i> Pause</button>';
                        $btn .= '<button type="button" class="btn btn-danger btn-sm btn-stop" data-id="' . $row->id . '" title="Selesaikan Pekerjaan"><i class="bi bi-stop-fill me-1"></i> Berhenti</button>';
                    }

                    // Edit & Delete Buttons based on permissions
                    $canManage = false;
                    if ($authUser) {
                        if ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit() || $row->user_id === $authUser->id) {
                            $canManage = true;
                        }
                    }

                    if ($canManage) {
                        $btn .= '<a href="' . route('insidentil.edit', $row->id) . '" class="btn btn-warning btn-sm text-white" title="Edit"><i class="bi bi-pencil"></i></a>';
                        $btn .= '<button type="button" class="btn btn-primary btn-sm btn-tag-user" data-id="' . $row->id . '" data-tags=\'' . json_encode($row->taggedUsers->pluck('id')->toArray()) . '\' title="Tag Rekan Kerja"><i class="bi bi-person-plus"></i></button>';
                        $btn .= '<button type="button" class="btn btn-outline-primary btn-sm btn-upload-file" data-id="' . $row->id . '" title="Upload Berkas"><i class="bi bi-upload"></i></button>';
                        $btn .= '<button type="button" class="btn btn-danger btn-sm btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="bi bi-trash"></i></button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['checkbox', 'task_details', 'estimasi_waktu', 'waktu_realisasi', 'attachment', 'status_badge', 'action'])
                ->with('stats', [
                    'total' => $totalCount,
                    'selesai' => $selesaiCount,
                    'proses' => $prosesCount,
                    'belum' => $belumCount,
                    'percent' => $percent
                ])
                ->make(true);
        }

        $periodeAkademiks = PeriodeAkademik::orderBy('nama_periode', 'desc')->get();
        $users = User::orderBy('name', 'asc')->get();

        $usersWithJabatan = [];
        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                $usersWithJabatan = User::whereNotNull('jabatan')->where('jabatan', '!=', '')->orderBy('jabatan', 'asc')->get();
            } elseif ($authUser->isPimpinanUnit()) {
                $usersWithJabatan = User::whereNotNull('jabatan')->where('jabatan', '!=', '')->where('unit', $authUser->unit)->orderBy('jabatan', 'asc')->get();
            }
        }

        return view('pages.insidentil.index', compact('periodeAkademiks', 'users', 'usersWithJabatan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $periodeAkademiks = PeriodeAkademik::orderBy('nama_periode', 'desc')->get();
        $users = User::orderBy('name', 'asc')->get();
        return view('pages.insidentil.create', compact('periodeAkademiks', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InsidentilRequest $request)
    {
        $data = $request->validated();
        $authUser = Auth::user();

        if (empty($data['user_id'])) {
            $data['user_id'] = $authUser->id;
        }

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('insidentils', 'public');
        }

        $data['status'] = 'Belum Dimulai';

        $insidentil = Insidentil::create($data);

        if ($request->filled('tagged_users')) {
            $insidentil->taggedUsers()->sync($request->tagged_users);
        }

        Alert::success('Berhasil', 'Data Rencana Kerja Insidentil berhasil ditambahkan.');
        return redirect()->route('insidentil.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Insidentil $insidentil)
    {
        return redirect()->route('insidentil.edit', $insidentil->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Insidentil $insidentil)
    {
        $authUser = Auth::user();

        if (!$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit() && $insidentil->user_id !== $authUser->id) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki hak akses untuk mengubah data ini.');
            return redirect()->route('insidentil.index');
        }

        $periodeAkademiks = PeriodeAkademik::orderBy('nama_periode', 'desc')->get();
        $users = User::orderBy('name', 'asc')->get();
        $insidentil->load('taggedUsers');

        return view('pages.insidentil.edit', compact('insidentil', 'periodeAkademiks', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InsidentilRequest $request, Insidentil $insidentil)
    {
        $authUser = Auth::user();

        if (!$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit() && $insidentil->user_id !== $authUser->id) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki hak akses untuk mengubah data ini.');
            return redirect()->route('insidentil.index');
        }

        $data = $request->validated();

        if ($request->hasFile('file')) {
            if ($insidentil->file && Storage::disk('public')->exists($insidentil->file)) {
                Storage::disk('public')->delete($insidentil->file);
            }
            $data['file'] = $request->file('file')->store('insidentils', 'public');
        }

        $insidentil->update($data);

        if ($request->has('tagged_users')) {
            $insidentil->taggedUsers()->sync($request->tagged_users ?? []);
        }

        Alert::success('Berhasil', 'Data Rencana Kerja Insidentil berhasil diperbarui.');
        return redirect()->route('insidentil.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Insidentil $insidentil)
    {
        $authUser = Auth::user();

        if (!$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit() && $insidentil->user_id !== $authUser->id) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk menghapus data ini.'], 403);
        }

        if ($insidentil->file && Storage::disk('public')->exists($insidentil->file)) {
            Storage::disk('public')->delete($insidentil->file);
        }

        $insidentil->taggedUsers()->detach();
        $insidentil->delete();

        return response()->json(['status' => 'success', 'message' => 'Data Rencana Kerja Insidentil berhasil dihapus.']);
    }

    /**
     * Helper to automatically log milestone points for task timer events.
     */
    private function autoLogMilestone($insidentil, $action, $now)
    {
        if ($action === 'start') {
            // Mark any paused milestones as completed for phase 1
            $pausedMilestones = $insidentil->milestones()->where('status', 'Di-pause')->get();
            foreach ($pausedMilestones as $pm) {
                $pm->status = 'Selesai';
                if (!$pm->waktu_selesai) {
                    $pm->waktu_selesai = $now;
                }
                $pm->save();
            }

            $runningMilestone = $insidentil->milestones()->where('status', 'Berjalan')->first();
            if (!$runningMilestone) {
                $milestoneCount = $insidentil->milestones()->count();
                $title = ($milestoneCount === 0) ? 'Mulai Pelaksanaan Pekerjaan' : 'Melanjutkan Pekerjaan';
                $insidentil->milestones()->create([
                    'nama_milestone' => $title,
                    'status' => 'Berjalan',
                    'waktu_mulai' => $now,
                    'last_started_at' => $now,
                    'durasi_detik' => 0,
                ]);
            }
        } elseif ($action === 'pause') {
            $runningMilestones = $insidentil->milestones()->where('status', 'Berjalan')->get();
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
            $activeMilestones = $insidentil->milestones()->whereIn('status', ['Berjalan', 'Di-pause'])->get();
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

            $lastMilestone = $insidentil->milestones()->latest()->first();
            if (!$lastMilestone || $lastMilestone->nama_milestone !== 'Selesai Pekerjaan') {
                $insidentil->milestones()->create([
                    'nama_milestone' => 'Selesai Pekerjaan',
                    'status' => 'Selesai',
                    'waktu_mulai' => $now,
                    'waktu_selesai' => $now,
                    'durasi_detik' => 0,
                ]);
            }
        }
    }

    /**
     * Start/Resume task timer.
     */
    public function start(Insidentil $insidentil)
    {
        $now = \Carbon\Carbon::now();
        $updateData = [
            'status' => 'Proses',
            'last_started_at' => $now,
        ];

        if (empty($insidentil->waktu_mulai)) {
            $updateData['tanggal_mulai'] = date('Y-m-d');
            $updateData['waktu_mulai'] = date('H:i:s');
        }

        $insidentil->update($updateData);

        // Auto log milestone point for start/resume
        $this->autoLogMilestone($insidentil, 'start', $now);

        return response()->json(['status' => 'success', 'message' => 'Pekerjaan berhasil dijalankan.']);
    }

    /**
     * Pause task timer.
     */
    public function pause(Insidentil $insidentil)
    {
        $now = \Carbon\Carbon::now();

        if (($insidentil->status === 'Proses' || $insidentil->status === 'Berjalan') && $insidentil->last_started_at) {
            $elapsedSeconds = (int) abs($now->diffInSeconds($insidentil->last_started_at));
            $insidentil->durasi_detik = (int) ($insidentil->durasi_detik ?? 0) + $elapsedSeconds;
        }

        $insidentil->last_started_at = null;
        $insidentil->status = 'Di-pause';
        $insidentil->save();

        // Auto log milestone point for pause
        $this->autoLogMilestone($insidentil, 'pause', $now);

        return response()->json(['status' => 'success', 'message' => 'Pekerjaan berhasil di-pause.']);
    }

    /**
     * Stop task timer.
     */
    public function stop(Insidentil $insidentil)
    {
        $now = \Carbon\Carbon::now();

        if (($insidentil->status === 'Proses' || $insidentil->status === 'Berjalan') && $insidentil->last_started_at) {
            $elapsedSeconds = (int) abs($now->diffInSeconds($insidentil->last_started_at));
            $insidentil->durasi_detik = (int) ($insidentil->durasi_detik ?? 0) + $elapsedSeconds;
        }

        $insidentil->update([
            'status' => 'Selesai',
            'tanggal_selesai' => date('Y-m-d'),
            'waktu_selesai' => date('H:i:s'),
            'last_started_at' => null,
        ]);

        // Auto log milestone point for stop
        $this->autoLogMilestone($insidentil, 'stop', $now);

        return response()->json(['status' => 'success', 'message' => 'Pekerjaan telah diselesaikan.']);
    }

    /**
     * Upload attachment file or external link.
     */
    public function uploadAttachment(Request $request, Insidentil $insidentil)
    {
        $request->validate([
            'file' => ['nullable', 'file', 'max:10240'],
            'url_external' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('file')) {
            if ($insidentil->file && Storage::disk('public')->exists($insidentil->file)) {
                Storage::disk('public')->delete($insidentil->file);
            }
            $insidentil->file = $request->file('file')->store('insidentils', 'public');
        }

        if ($request->filled('url_external')) {
            $insidentil->url_external = $request->url_external;
        }

        $insidentil->save();

        Alert::success('Berhasil', 'Lampiran berhasil diunggah.');
        return redirect()->back();
    }

    /**
     * Bulk delete items.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada data yang dipilih.'], 400);
        }

        $authUser = Auth::user();
        $insidentils = Insidentil::whereIn('id', $ids)->get();

        $deletedCount = 0;
        foreach ($insidentils as $item) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit() || $item->user_id === $authUser->id) {
                if ($item->file && Storage::disk('public')->exists($item->file)) {
                    Storage::disk('public')->delete($item->file);
                }
                $item->taggedUsers()->detach();
                $item->delete();
                $deletedCount++;
            }
        }

        return response()->json(['status' => 'success', 'message' => "{$deletedCount} data berhasil dihapus."]);
    }

    /**
     * Update tagged users modal.
     */
    public function updateTags(Request $request, Insidentil $insidentil)
    {
        $request->validate([
            'tagged_users' => ['nullable', 'array'],
            'tagged_users.*' => ['exists:users,id'],
        ]);

        $insidentil->taggedUsers()->sync($request->tagged_users ?? []);

        return response()->json(['status' => 'success', 'message' => 'Rekan kerja berhasil diperbarui.']);
    }

    /**
     * Export data to PDF.
     */
    public function exportPdf(Request $request)
    {
        $authUser = Auth::user();
        $query = Insidentil::with(['user', 'periodeAkademik', 'taggedUsers']);

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

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        $items = $query->latest()->get();

        $namaStaff = 'SEMUA STAFF';
        $jabatanStaff = 'SEMUA JABATAN';
        $unitStaff = 'SEMUA UNIT';

        if ($request->filled('user_id') && ($u = User::find($request->user_id))) {
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

        $periodeText = 'SEMUA PERIODE';
        if ($request->filled('periode_akademik_id') && ($p = PeriodeAkademik::find($request->periode_akademik_id))) {
            $periodeText = strtoupper($p->nama_periode);
        } elseif ($items->count() > 0 && $items->first()->periodeAkademik) {
            $periodeText = strtoupper($items->first()->periodeAkademik->nama_periode);
        }

        $safeStaff = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $namaStaff)));
        $safePeriode = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $periodeText)));
        $filename = ($safeStaff ?: 'Semua Staff') . '_' . ($safePeriode ?: 'Periode') . '_laporan insidentil.pdf';

        $pdf = Pdf::loadView('pages.insidentil.pdf', compact('items', 'namaStaff', 'jabatanStaff', 'unitStaff', 'periodeText'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    /**
     * Export data to Excel.
     */
    public function exportExcel(Request $request)
    {
        $authUser = Auth::user();
        $query = Insidentil::with(['user', 'periodeAkademik', 'taggedUsers']);

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

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        $items = $query->latest()->get();

        $namaStaff = 'SEMUA STAFF';
        $jabatanStaff = 'SEMUA JABATAN';
        $unitStaff = 'SEMUA UNIT';

        if ($request->filled('user_id') && ($u = User::find($request->user_id))) {
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

        $periodeText = 'SEMUA PERIODE';
        if ($request->filled('periode_akademik_id') && ($p = PeriodeAkademik::find($request->periode_akademik_id))) {
            $periodeText = strtoupper($p->nama_periode);
        } elseif ($items->count() > 0 && $items->first()->periodeAkademik) {
            $periodeText = strtoupper($items->first()->periodeAkademik->nama_periode);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Insidentil');

        // Row 1: Title
        $titleText = 'LAPORAN RENCANA KERJA INSIDENTIL (' . $periodeText . ')';
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', $titleText);
        $sheet->getStyle('A1:K1')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:K1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('15432D');
        $sheet->getStyle('A1:K1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Rows 3-6: Metadata
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

        $sheet->setCellValue('A6', 'PERIODE AKADEMIK');
        $sheet->setCellValue('B6', ':');
        $sheet->setCellValue('C6', $periodeText);
        $sheet->getStyle('A6')->getFont()->setBold(true);
        $sheet->getStyle('C6')->getFont()->setBold(true);

        // Row 8: Table Headers
        $headers = [
            'A8' => 'NO',
            'B8' => 'HARI',
            'C8' => 'URAIAN TUGAS',
            'D8' => 'REKAN KERJA',
            'E8' => 'EST. TGL MULAI',
            'F8' => 'EST. JAM MULAI',
            'G8' => 'EST. TGL SELESAI',
            'H8' => 'EST. JAM SELESAI',
            'I8' => 'TGL MULAI REALISASI',
            'J8' => 'TGL SELESAI REALISASI',
            'K8' => 'STATUS',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerRange = 'A8:K8';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('15432D');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(8)->setRowHeight(25);

        $rowNum = 9;
        $no = 1;

        foreach ($items as $item) {
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $item->hari ?? '-');
            $sheet->setCellValue('C' . $rowNum, $item->uraian_tugas);
            $sheet->setCellValue('D' . $rowNum, $item->taggedUsers->pluck('name')->implode(', ') ?: '-');
            $sheet->setCellValue('E' . $rowNum, $item->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($item->estimasi_tanggal_mulai)) : '-');
            $sheet->setCellValue('F' . $rowNum, $item->estimasi_jam_mulai ? substr($item->estimasi_jam_mulai, 0, 5) : '-');
            $sheet->setCellValue('G' . $rowNum, $item->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($item->estimasi_tanggal_selesai)) : '-');
            $sheet->setCellValue('H' . $rowNum, $item->estimasi_jam_selesai ? substr($item->estimasi_jam_selesai, 0, 5) : '-');
            $sheet->setCellValue('I' . $rowNum, $item->tanggal_mulai ? date('d/m/Y', strtotime($item->tanggal_mulai)) . ($item->waktu_mulai ? ' ' . substr($item->waktu_mulai, 0, 5) : '') : '-');
            $sheet->setCellValue('J' . $rowNum, $item->tanggal_selesai ? date('d/m/Y', strtotime($item->tanggal_selesai)) . ($item->waktu_selesai ? ' ' . substr($item->waktu_selesai, 0, 5) : '') : '-');
            $sheet->setCellValue('K' . $rowNum, $item->status ?? '-');

            $sheet->getStyle('A' . $rowNum . ':K' . $rowNum)->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $rowNum++;
        }

        // Borders
        if ($rowNum > 9) {
            $sheet->getStyle('A8:K' . ($rowNum - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FFCCCCCC'],
                    ],
                ],
            ]);
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $safeStaff = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $namaStaff)));
        $safePeriode = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $periodeText)));
        $filename = ($safeStaff ?: 'Semua Staff') . '_' . ($safePeriode ?: 'Periode') . '_laporan insidentil.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'insidentil_export_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
