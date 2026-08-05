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
            $query = Insidentil::with(['user', 'periodeAkademik', 'taggedUsers']);

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
                    
                    // Start/Stop Timer button
                    if ($row->status === 'Belum Dimulai') {
                        $btn .= '<button type="button" class="btn btn-success btn-sm btn-start" data-id="' . $row->id . '" title="Mulai Pekerjaan"><i class="bi bi-play-fill"></i></button>';
                    } elseif ($row->status === 'Proses' || $row->status === 'Berjalan') {
                        $btn .= '<button type="button" class="btn btn-danger btn-sm btn-stop" data-id="' . $row->id . '" title="Selesaikan Pekerjaan"><i class="bi bi-stop-fill"></i></button>';
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
     * Start task timer.
     */
    public function start(Insidentil $insidentil)
    {
        $insidentil->update([
            'status' => 'Proses',
            'tanggal_mulai' => date('Y-m-d'),
            'waktu_mulai' => date('H:i:s'),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Pekerjaan berhasil dimulai.']);
    }

    /**
     * Stop task timer.
     */
    public function stop(Insidentil $insidentil)
    {
        $insidentil->update([
            'status' => 'Selesai',
            'tanggal_selesai' => date('Y-m-d'),
            'waktu_selesai' => date('H:i:s'),
        ]);

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
            } elseif ($authUser->isPimpinanUnit()) {
                $query->where(function ($q) use ($authUser) {
                    $q->whereHas('user', function ($qu) use ($authUser) {
                        $qu->where('unit', $authUser->unit);
                    })->orWhereHas('taggedUsers', function ($qt) use ($authUser) {
                        $qt->where('users.id', $authUser->id);
                    });
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

        $insidentils = $query->latest()->get();
        $periode = PeriodeAkademik::find($request->periode_akademik_id);

        $pdf = Pdf::loadView('pages.insidentil.pdf', compact('insidentils', 'periode'));
        return $pdf->download('Rencana_Kerja_Insidentil_' . date('YmdHis') . '.pdf');
    }

    /**
     * Export data to Excel/CSV.
     */
    public function exportExcel(Request $request)
    {
        $authUser = Auth::user();
        $query = Insidentil::with(['user', 'periodeAkademik', 'taggedUsers']);

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
            } elseif ($authUser->isPimpinanUnit()) {
                $query->where(function ($q) use ($authUser) {
                    $q->whereHas('user', function ($qu) use ($authUser) {
                        $qu->where('unit', $authUser->unit);
                    })->orWhereHas('taggedUsers', function ($qt) use ($authUser) {
                        $qt->where('users.id', $authUser->id);
                    });
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

        $insidentils = $query->latest()->get();

        $filename = 'Rencana_Kerja_Insidentil_' . date('YmdHis') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($insidentils) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Uraian Tugas', 'Pembuat', 'Rekan Kerja', 'Hari', 'Estimasi Jam', 'Estimasi Tanggal', 'Status']);

            foreach ($insidentils as $index => $item) {
                $taggedStr = $item->taggedUsers->pluck('name')->implode(', ');
                fputcsv($file, [
                    $index + 1,
                    $item->uraian_tugas,
                    $item->user ? $item->user->name : '-',
                    $taggedStr ?: '-',
                    $item->hari ?? '-',
                    ($item->estimasi_jam_mulai ?? '-') . ' - ' . ($item->estimasi_jam_selesai ?? '-'),
                    ($item->estimasi_tanggal_mulai ?? '-') . ' - ' . ($item->estimasi_tanggal_selesai ?? '-'),
                    $item->status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
