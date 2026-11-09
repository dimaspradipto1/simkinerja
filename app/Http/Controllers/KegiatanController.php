<?php

namespace App\Http\Controllers;

use App\Http\Requests\KegiatanRequest;
use App\Models\Kategori;
use App\Models\PeriodeAkademik;
use App\Models\Kegiatan;
use App\Models\RencanaKerja;
use App\Models\Kepanitiaan;
use App\Models\Insidentil;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KegiatanController extends Controller
{
    use \App\Traits\ManagesKegiatanLifecycle;

    protected string $attachmentFolder = 'kegiatan';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        if ($request->ajax()) {
            $query = Kegiatan::with(['user', 'periodeAkademik', 'taggedUsers', 'milestones']);

            if ($request->filled('jabatan') && $request->jabatan !== '__own__') {
                if ($request->jabatan === '__all__' || $request->jabatan === 'all') {
                    // Tampilkan semua jabatan (hanya jika eksplisit dipilih opsi "Semua Jabatan")
                } else {
                    $query->where(function ($q) use ($request) {
                        $q->whereHas('user', function ($qu) use ($request) {
                            $qu->where('jabatan', $request->jabatan);
                        })->orWhereHas('taggedUsers', function ($qt) use ($request) {
                            $qt->where('jabatan', $request->jabatan);
                        });
                    });
                }
            } else {
                // Default: jika kriteria checklist untuk jabatan tidak dipilih, tampilkan data sendiri (user login + tagged)
                if ($authUser) {
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

            $query->orderBy('id', 'desc');

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
                    $html .= '      <i class="bi bi-card-checklist text-primary fs-5 flex-shrink-0"></i>';
                    $html .= '      <span>' . e($row->uraian_tugas) . '</span>';
                    $html .= '  </div>';
                    $html .= '  <div class="pt-2 border-top d-flex flex-wrap align-items-center justify-content-start gap-2">';
                    $html .= '      <span class="text-secondary small fw-semibold me-1"><i class="bi bi-gear-fill me-1"></i>Aksi & Status:</span>';
                    $html .=        $actionBtns;
                    $html .= '  </div>';
                    $html .= '</div>';
                    $html .= '</div>';

                    // 2. Metadata Chips (Periode, Hari, Pegawai)
                    $chips = [];
                    if ($row->periodeAkademik) {
                        $taLabel = $row->periodeAkademik->formatted_ta ?? ('T.A. ' . $row->periodeAkademik->nama_periode);
                        $chips[] = '<span class="badge px-2 py-1" style="background-color: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-weight: 600; font-size: 0.78rem;"><i class="bi bi-mortarboard-fill me-1"></i>' . e($taLabel) . '</span>';
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

                    // 2.5 Special Card for Tagged Users (Rekan Kerja) - Kartu Tersendiri
                    if ($row->taggedUsers->count() > 0) {
                        $html .= '<div class="mt-1 p-2 px-3 bg-white rounded border shadow-sm d-flex flex-column gap-1" style="border-left: 4px solid #0284c7 !important; word-break: break-word; overflow-wrap: break-word;">';
                        $html .= '<span class="text-secondary small fw-bold mb-1 d-inline-flex align-items-center"><i class="bi bi-people-fill text-info me-2" style="margin-right: 8px !important;"></i>Rekan Kerja:</span>';
                        $html .= '<div class="d-flex flex-column gap-1 ps-1 ps-sm-2">';

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
                            $html .= '<div class="fw-semibold text-dark text-break" style="font-size: 0.83rem; line-height: 1.35;">' . $idx . '. ' . e($tu->name) . ' <span class="text-muted fw-normal d-block d-sm-inline" style="font-size: 0.76rem;">(' . e($tu->jabatan ?? '-') . ')</span></div>';
                            $idx++;
                        }
                        $html .= '</div>';
                        $html .= '</div>';
                    }

                    // 2.6 Card Status Kehadiran Absensi Kinerja Harian
                    $html .= \App\Helpers\AbsensiKinerjaHelper::renderKartuAbsensiHarian($row, auth()->id() ?: $row->user_id, 'kegiatan');

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

                    if ($row->status === 'Di-pause') {
                        $html .= '<div class="mt-2 p-2 px-3 rounded-2 border" style="background-color: #fffbeb; border-color: #fde68a !important;">';
                        $html .= '<div class="d-flex align-items-center justify-content-between mb-1">';
                        $html .= '<label class="form-label mb-0 text-warning-emphasis fw-bold small d-flex align-items-center gap-1">';
                        $html .= '<i class="bi bi-pause-circle-fill text-warning"></i> Penyebab Di-pause:';
                        $html .= '</label>';
                        $html .= '<span class="badge bg-warning text-dark fw-semibold" style="font-size: 0.7rem;">Status: Di-pause</span>';
                        $html .= '</div>';
                        $html .= '<div class="mt-1">';
                        $html .= '<textarea name="alasan_pause" class="form-control form-control-sm border-warning-subtle shadow-2xs field-alasan-pause" data-id="' . $row->id . '" data-type="kegiatan" rows="2" placeholder="Tuliskan penyebab atau kendala kegiatan di-pause...">' . e($row->alasan_pause ?? '') . '</textarea>';
                        $html .= '</div>';
                        $html .= '<div class="d-flex justify-content-end mt-2">';
                        $html .= '<button type="button" class="btn btn-sm btn-warning text-dark fw-bold px-3 btn-simpan-alasan-pause" data-id="' . $row->id . '" data-type="kegiatan" style="font-size: 0.78rem;">';
                        $html .= '<i class="bi bi-check2-circle me-1"></i> Simpan Penyebab Pause';
                        $html .= '</button>';
                        $html .= '</div>';
                        $html .= '</div>';
                    }

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
                        $cleanHk = trim(strip_tags(html_entity_decode((string)$row->hasil_kerja, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                        $contentHtml .= '<div class="text-secondary small" style="font-size: 0.8rem;"><strong class="text-dark"><i class="bi bi-check2-square text-success me-1"></i>Hasil Kerja:</strong> ' . nl2br(e($cleanHk)) . '</div>';
                    }

                    if ($row->rencana_tindak_lanjut) {
                        $hasContent = true;
                        $cleanTl = trim(strip_tags(html_entity_decode((string)$row->rencana_tindak_lanjut, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                        $contentHtml .= '<div class="text-secondary small" style="font-size: 0.8rem;"><strong class="text-dark"><i class="bi bi-arrow-right-circle text-warning me-1"></i>Tindak Lanjut:</strong> ' . nl2br(e($cleanTl)) . '</div>';
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

        $jabatanList = User::whereNotNull('jabatan')->where('jabatan', '!=', '')->distinct()->orderBy('jabatan')->pluck('jabatan');
        $periodeAkademiks = PeriodeAkademik::orderBy('id', 'asc')->get();
        $defaultPeriode = PeriodeAkademik::first();
        $defaultPeriodeId = $defaultPeriode ? $defaultPeriode->id : null;
        $usersWithJabatan = User::whereNotNull('jabatan')->where('jabatan', '!=', '')->orderBy('name')->get();

        return view('pages.kegiatan.index', compact('jabatanList', 'periodeAkademiks', 'defaultPeriodeId', 'usersWithJabatan'));
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

        $isExpired = false;
        if ($status !== 'Selesai' && !empty($row->estimasi_tanggal_selesai)) {
            $endTime = $row->estimasi_jam_selesai ?: '23:59:59';
            $estimasiEnd = \Carbon\Carbon::parse($row->estimasi_tanggal_selesai . ' ' . $endTime);
            if (\Carbon\Carbon::now()->gt($estimasiEnd)) {
                $isExpired = true;
            }
        }

        $isOwner = $authUser && ($row->user_id === $authUser->id);
        $isLeader = $authUser && ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit());
        $isTaggedOnly = !$isOwner && !$isLeader;

        // Absensi harian: tidak perlu resolve sesi per tugas lagi (1 record per hari per user)
        $absensiInfo = null;
        $absensiUrl  = '';
        $scanUrl     = '';

        // KHUSUS PENGGUNA YANG DI-TAG (READ ONLY): Tidak ada tombol timer/edit/delete, tampilkan tombol Scan Barcode
        if ($isTaggedOnly) {
            if ($status === 'Selesai' || $waktuSelesai) {
                $btn .= '<span class="badge bg-success text-white px-2 py-1 d-inline-flex align-items-center me-1" style="height: 32px; font-size: 0.8rem;"><i class="bi bi-check-circle-fill me-1"></i> Selesai</span>';
            } elseif ($status === 'Berjalan' || $status === 'Proses') {
                $btn .= '<span class="badge bg-primary text-white px-2 py-1 d-inline-flex align-items-center me-1" style="height: 32px; font-size: 0.8rem;"><i class="bi bi-play-circle-fill me-1"></i> Berjalan</span>';
            } elseif ($status === 'Di-pause') {
                $btn .= '<span class="badge bg-warning text-dark px-2 py-1 d-inline-flex align-items-center me-1" style="height: 32px; font-size: 0.8rem;"><i class="bi bi-pause-circle-fill me-1"></i> Di-pause</span>';
            } else {
                $btn .= '<span class="badge bg-secondary text-white px-2 py-1 d-inline-flex align-items-center me-1" style="height: 32px; font-size: 0.8rem;"><i class="bi bi-hourglass me-1"></i> Belum Dimulai</span>';
            }

            $btn .= '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 d-inline-flex align-items-center me-1" style="height: 32px; font-size: 0.78rem;" title="Anda ditugaskan sebagai Rekan Kerja pada tugas ini"><i class="bi bi-person-check-fill me-1"></i> Rekan Kerja</span>';

            // Tombol Scan Barcode Khusus Rekan Kerja yang Di-tag (Hanya Scan Barcode, Barcode Absensi disembunyikan)
            if ($absensiInfo) {
                $btn .= '<button type="button" class="btn btn-sm btn-primary text-white fw-bold text-nowrap px-3 me-1 d-inline-flex align-items-center gap-1 shadow-sm btn-scan-barcode-modal" data-slug="' . e($absensiInfo['slug']) . '" data-title="' . e($absensiInfo['nama']) . '" onclick="window.openScanBarcodeModal(this)" style="background-color: #0d6efd; border-color: #0d6efd; height: 32px;" title="Scan Barcode Kehadiran ' . e($absensiInfo['nama']) . '"><i class="bi bi-qr-code-scan"></i> Scan Barcode</button>';
            }

            $btn .= '</div>';
            return $btn;
        }

        if ($status === 'Selesai' || $waktuSelesai) {
            $btn .= '<button type="button" class="btn btn-sm text-white fw-bold text-nowrap px-3 me-1 disabled" style="background-color: #2d6a4f; border-color: #2d6a4f; opacity: 1; height: 32px; cursor: default;"><i class="bi bi-check-circle-fill me-1"></i> Selesai</button>';
            // Tombol buka kunci HANYA ada di role admin dan pimpinan. Di role sendiri (staff) tidak ada.
            if ($authUser && $authUser->canUnlockTasks()) {
                $btn .= '<button type="button" class="btn btn-sm btn-warning text-dark fw-bold text-nowrap px-2 me-1 btn-reopen-task" data-id="' . $row->id . '" data-type="' . $routePrefix . '" data-uraian="' . e($row->uraian_tugas) . '" data-pegawai="' . e($row->user->name ?? '-') . '" data-reason="" onclick="window.reopenTask(this)" style="height: 32px;" title="Buka kunci kegiatan yang sudah selesai agar dapat dikerjakan lagi"><i class="bi bi-unlock-fill me-1"></i> Buka Kunci</button>';
            }
        } elseif ($isExpired) {
            if (!empty($row->estimasi_unlock_reason) && !empty($row->estimasi_unlock_requested_at)) {
                if ($isLeader) {
                    $btn .= '<button type="button" class="btn btn-sm btn-warning text-dark fw-bold text-nowrap px-2 me-1" data-id="' . $row->id . '" data-uraian="' . e($row->uraian_tugas) . '" data-pegawai="' . e($row->user->name ?? '-') . '" data-reason="' . e($row->estimasi_unlock_reason) . '" data-tgl-selesai="' . e($row->estimasi_tanggal_selesai ?? date('Y-m-d')) . '" data-jam-selesai="' . e($row->estimasi_jam_selesai ? substr($row->estimasi_jam_selesai, 0, 5) : '23:59') . '" data-route="' . route($routePrefix . '.approve-extension', $row->id) . '" onclick="window.openApproveExtensionModal(this)" style="height: 32px;" title="Permohonan perpanjangan waktu diajukan pegawai. Klik untuk setujui & beri waktu baru"><i class="bi bi-clock-history me-1"></i> Beri Perpanjangan Waktu</button>';
                } else {
                    $btn .= '<span class="badge bg-warning text-dark px-2 py-1 d-inline-flex align-items-center me-1" style="height: 32px; font-size: 0.8rem;" title="Permohonan perpanjangan waktu sedang menunggu persetujuan pimpinan"><i class="bi bi-hourglass-split me-1"></i> Menunggu Persetujuan Pimpinan</span>';
                }
            } else {
                $btn .= '<button type="button" class="btn btn-sm btn-danger text-white fw-bold text-nowrap px-2 me-1 btn-request-extension" data-id="' . $row->id . '" data-uraian="' . e($row->uraian_tugas) . '" data-route="' . route($routePrefix . '.request-unlock-estimasi', $row->id) . '" onclick="window.openExtensionModal(this)" style="height: 32px;" title="Tugas gagal karena melewati estimasi waktu. Klik untuk minta perpanjangan waktu ke pimpinan"><i class="bi bi-clock-history me-1"></i> Tugas Gagal - Minta Perpanjangan Waktu</button>';
            }
        } elseif (!$waktuMulai && $status !== 'Di-pause') {
            $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.startTimer(' . $row->id . ')" class="btn btn-sm text-white fw-bold text-nowrap px-3 me-1 btn-start-timer" style="background-color: #15432d; border-color: #15432d; height: 32px; min-width: 85px; cursor: pointer;"><i class="bi bi-play-fill me-1"></i> Mulai</button>';
        } else {
            if ($status === 'Di-pause') {
                $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.startTimer(' . $row->id . ')" class="btn btn-sm btn-success text-white fw-bold text-nowrap px-2 me-1 btn-resume-timer" style="height: 32px; cursor: pointer;" title="Lanjut Pekerjaan"><i class="bi bi-play-fill me-1"></i> Lanjut</button>';
            } else {
                $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.pauseTaskTimer(' . $row->id . ', \'' . $routePrefix . '\')" class="btn btn-sm btn-warning text-dark fw-bold text-nowrap px-2 me-1 btn-pause-timer" style="height: 32px; cursor: pointer;" title="Jeda Pekerjaan"><i class="bi bi-pause-fill me-1"></i> Pause</button>';
            }
            $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.stopTimer(' . $row->id . ')" class="btn btn-sm btn-danger text-white fw-bold text-nowrap px-2 me-1 btn-stop-timer" style="background-color: #8b0000; border-color: #8b0000; height: 32px; cursor: pointer;" title="Selesaikan Pekerjaan"><i class="bi bi-stop-fill me-1"></i> Berhenti</button>';
        }

        // Barcode Absensi Button untuk Pemilik Tugas / Pimpinan
        if ($absensiInfo) {
            $btn .= '<button type="button" class="btn btn-sm btn-primary text-white fw-bold text-nowrap px-2 me-1 d-inline-flex align-items-center gap-1 shadow-sm btn-scan-barcode-modal" data-slug="' . e($absensiInfo['slug']) . '" data-title="' . e($absensiInfo['nama']) . '" onclick="window.openScanBarcodeModal(this)" style="background-color: #0d6efd; border-color: #0d6efd; height: 32px;" title="Scan Barcode Kehadiran ' . e($absensiInfo['nama']) . '"><i class="bi bi-qr-code-scan"></i> Scan Barcode</button>';
            $btn .= '<button type="button" class="btn btn-sm text-white fw-bold text-nowrap px-2 me-1 d-inline-flex align-items-center gap-1 shadow-sm btn-barcode-absensi" data-slug="' . e($absensiInfo['slug']) . '" data-title="' . e($absensiInfo['nama']) . '" data-url="' . $absensiUrl . '" onclick="window.openBarcodeModal(this)" style="background-color: #0f172a; border-color: #0f172a; height: 32px;" title="Buka Barcode Absensi ' . e($absensiInfo['nama']) . '"><i class="bi bi-qr-code"></i> Barcode Absensi</button>';
        }

        $canEditDelete = $isLeader || $isOwner;

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
     * Helper to resolve attendance session from task data (url_external or keywords in uraian_tugas).
     */
    private function resolveAbsensiSession($row): ?array
    {
        $defaults = \App\Models\AbsensiSetting::defaults();

        // 1. Cek dari url_external
        if (!empty($row->url_external)) {
            foreach ($defaults as $slug => $cfg) {
                if (str_contains($row->url_external, $slug)) {
                    return $cfg;
                }
            }
        }

        // 2. Deteksi cerdas dari uraian_tugas jika kategori adalah Kepanitiaan atau memuat nama kegiatan kepanitiaan
        $text = strtolower($row->uraian_tugas ?? '');

        // PKKMB
        if (str_contains($text, 'pkkmb')) {
            if (str_contains($text, 'hari 3') || str_contains($text, 'hari ke-3') || str_contains($text, 'hari ketiga') || str_contains($text, '3 hari')) {
                return $defaults['absensi-pkkmb-ketiga'] ?? $defaults['absensi-pkkmb-pertama'];
            } elseif (str_contains($text, 'hari 2') || str_contains($text, 'hari ke-2') || str_contains($text, 'hari kedua')) {
                return $defaults['absensi-pkkmb-kedua'] ?? $defaults['absensi-pkkmb-pertama'];
            }
            return $defaults['absensi-pkkmb-pertama'];
        }

        // MILAD
        if (str_contains($text, 'milad')) {
            if (str_contains($text, 'hari 2') || str_contains($text, 'hari ke-2') || str_contains($text, 'hari kedua')) {
                return $defaults['absensi-milad-kedua'] ?? $defaults['absensi-milad-pertama'];
            }
            return $defaults['absensi-milad-pertama'];
        }

        // ESQ
        if (str_contains($text, 'esq')) {
            if (str_contains($text, 'hari 4') || str_contains($text, 'hari ke-4') || str_contains($text, 'hari keempat')) {
                return $defaults['absensi-esq-keempat'] ?? $defaults['absensi-esq-pertama'];
            }
            if (str_contains($text, 'hari 3') || str_contains($text, 'hari ke-3') || str_contains($text, 'hari ketiga')) {
                return $defaults['absensi-esq-ketiga'] ?? $defaults['absensi-esq-pertama'];
            }
            if (str_contains($text, 'hari 2') || str_contains($text, 'hari ke-2') || str_contains($text, 'hari kedua')) {
                return $defaults['absensi-esq-kedua'] ?? $defaults['absensi-esq-pertama'];
            }
            return $defaults['absensi-esq-pertama'];
        }

        // Kuliah Umum
        if (str_contains($text, 'kuliah umum')) {
            return $defaults['absensi-kuliah-umum-pertama'];
        }

        return null;
    }

    public function create()
    {
        $authUser = Auth::user();
        $users = User::orderBy('name')->get();
        $periodeAkademiks = PeriodeAkademik::orderBy('id', 'asc')->get();
        $kategoris = Kategori::orderBy('id', 'asc')->get();
        $defaultPeriode = PeriodeAkademik::first();
        $defaultPeriodeId = $defaultPeriode ? $defaultPeriode->id : null;

        $pendingRequests = collect([]);
        if ($authUser && !$authUser->isRektor()) {
            $pendingRequestsQuery = Kegiatan::whereNotNull('estimasi_unlock_requested_at');

            if ($authUser->isAdmin()) {
                // Admin can see all pending requests
            } elseif ($authUser->isPimpinanUnit()) {
                // Kepala Staff / Pimpinan Unit: only see requests from staff in their unit, or targeted to them
                $pendingRequestsQuery->where(function ($q) use ($authUser) {
                    $q->whereHas('user', function ($qu) use ($authUser) {
                        $qu->where('unit', $authUser->unit);
                    })->orWhere('estimasi_unlock_reason', 'like', '%[Tujuan: ' . $authUser->name . '%');
                });
            } else {
                // Regular staff does not see incoming requests banner
                $pendingRequestsQuery->whereRaw('1 = 0');
            }

            $pendingRequests = $pendingRequestsQuery->with(['user', 'periodeAkademik'])->latest('estimasi_unlock_requested_at')->get();
        }
        $absensiSessions = \App\Models\AbsensiSetting::getAllGrouped();

        return view('pages.kegiatan.create', compact('users', 'periodeAkademiks', 'defaultPeriodeId', 'kategoris', 'pendingRequests', 'absensiSessions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KegiatanRequest $request)
    {
        $validated = $request->validated();

        $authUser = Auth::user();
        $isAdmin = $authUser && $authUser->isSystemAdmin();
        if (!$isAdmin || empty($validated['user_id'])) {
            $validated['user_id'] = Auth::id();
        }
        $kategori = $validated['kategori'] ?? 'Rencana Kerja';
        unset($validated['parent_id']);

        if ($request->filled('jenis_absensi') && empty($validated['url_external'])) {
            $validated['url_external'] = url($request->input('jenis_absensi'));
        }

        if ($request->hasFile('file')) {
            $folder = 'rencana_kerja';
            if ($kategori === 'Kepanitiaan') {
                $folder = 'kepanitiaan';
            } elseif ($kategori === 'Insidentil') {
                $folder = 'insidentil';
            }
            $validated['file'] = $request->file('file')->store($folder, 'public');
        }

        if ($request->has('start_now') && $request->start_now) {
            $validated['waktu_mulai'] = now()->format('H:i:s');
            $validated['tanggal_mulai'] = now()->format('Y-m-d');
            $validated['last_started_at'] = now();
            $validated['status'] = 'Berjalan';
        } else {
            $validated['status'] = 'Belum Dimulai';
        }

        // Simpan langsung ke model target (tersimpan di tabel kegiatans secara otomatis melalui STI)
        if (trim($kategori) === 'Kepanitiaan') {
            $record = Kepanitiaan::create($validated);
            if ($request->has('tagged_users')) {
                $record->taggedUsers()->sync($request->input('tagged_users'));
            }

            // Simpan sesi presensi baru jika diisi dari form
            if ($request->filled('nama_sesi_presensi')) {
                $request->validate([
                    'nama_sesi_presensi' => ['array'],
                    'nama_sesi_presensi.*' => ['nullable', 'string', 'max:255'],
                    'tanggal_sesi_presensi' => ['nullable', 'array'],
                    'tanggal_sesi_presensi.*' => ['nullable', 'date'],
                    'jenis_absen_presensi' => ['required', 'array'],
                    'jenis_absen_presensi.*' => ['required', 'in:datang,pulang,rapat'],
                ]);
                $sesiNames = (array) $request->input('nama_sesi_presensi');
                $sesiTgls = (array) $request->input('tanggal_sesi_presensi', []);
                $sesiTypes = (array) $request->input('jenis_absen_presensi', []);
                foreach ($sesiNames as $idx => $sName) {
                    $trimmed = trim((string)$sName);
                    if (!empty($trimmed)) {
                        \App\Models\KepanitiaanSesiAbsensi::create([
                            'kepanitiaan_id' => $record->id,
                            'nama_sesi' => $trimmed,
                            'jenis_absen' => $sesiTypes[$idx] ?? 'datang',
                            'tanggal' => !empty($sesiTgls[$idx]) ? $sesiTgls[$idx] : ($record->tanggal_mulai ?: date('Y-m-d')),
                            'token' => \Illuminate\Support\Str::random(24),
                            'is_active' => true,
                        ]);
                    }
                }
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sub Kegiatan Kepanitiaan berhasil ditambahkan.',
                    'data' => $record
                ]);
            }

            Alert::success('Berhasil', 'Kegiatan berhasil disimpan dan masuk ke Rencana Kerja Kepanitiaan.')
                ->toToast()
                ->autoClose(4000)
                ->timerProgressBar();

            return redirect()->route('kepanitiaan.index');
        } elseif (trim($kategori) === 'Insidentil') {
            $record = Insidentil::create($validated);
            if ($request->has('tagged_users')) {
                $record->taggedUsers()->sync($request->input('tagged_users'));
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sub Kegiatan Insidentil berhasil ditambahkan.',
                    'data' => $record
                ]);
            }

            Alert::success('Berhasil', 'Kegiatan berhasil disimpan dan masuk ke Rencana Kerja Insidentil.')
                ->toToast()
                ->autoClose(4000)
                ->timerProgressBar();

            return redirect()->route('insidentil.index');
        } else {
            $record = RencanaKerja::create($validated);
            if ($request->has('tagged_users')) {
                $record->taggedUsers()->sync($request->input('tagged_users'));
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sub Kegiatan berhasil ditambahkan.',
                    'data' => $record
                ]);
            }

            Alert::success('Berhasil', 'Kegiatan berhasil disimpan dan masuk ke Rencana Kerja Utama.')
                ->toToast()
                ->autoClose(4000)
                ->timerProgressBar();

            return redirect()->route('rencana-kerja.index');
        }
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
        $kategoris = Kategori::orderBy('id', 'asc')->get();

        return view('pages.kegiatan.edit', compact('kegiatan', 'users', 'periodeAkademiks', 'kategoris', 'estimasiLocked'));
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
        unset($validated['parent_id'], $validated['sub_kegiatan_items']);
        if (!$authUser || !$authUser->isSystemAdmin()) {
            unset($validated['user_id']);
        }

        $estimasiFields = [
            'estimasi_tanggal_mulai', 'estimasi_tanggal_selesai', 'estimasi_jam_mulai', 'estimasi_jam_selesai',
            'estimasi_durasi_hari', 'estimasi_durasi_jam',
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

        // Jika kegiatan bertipe Kepanitiaan dan ada input sesi presensi baru
        if (trim($kegiatan->kategori) === 'Kepanitiaan' && $request->filled('nama_sesi_presensi')) {
            $request->validate([
                'nama_sesi_presensi' => ['array'],
                'nama_sesi_presensi.*' => ['nullable', 'string', 'max:255'],
                'tanggal_sesi_presensi' => ['nullable', 'array'],
                'tanggal_sesi_presensi.*' => ['nullable', 'date'],
                'jenis_absen_presensi' => ['required', 'array'],
                'jenis_absen_presensi.*' => ['required', 'in:datang,pulang,rapat'],
            ]);
            $sesiNames = (array) $request->input('nama_sesi_presensi');
            $sesiTgls = (array) $request->input('tanggal_sesi_presensi', []);
            $sesiTypes = (array) $request->input('jenis_absen_presensi', []);
            foreach ($sesiNames as $idx => $sName) {
                $trimmed = trim((string)$sName);
                if (!empty($trimmed)) {
                    \App\Models\KepanitiaanSesiAbsensi::create([
                        'kepanitiaan_id' => $kegiatan->id,
                        'nama_sesi' => $trimmed,
                        'jenis_absen' => $sesiTypes[$idx] ?? 'datang',
                        'tanggal' => !empty($sesiTgls[$idx]) ? $sesiTgls[$idx] : ($kegiatan->tanggal_mulai ?: date('Y-m-d')),
                        'token' => \Illuminate\Support\Str::random(24),
                        'is_active' => true,
                    ]);
                }
            }
        }

        $kegiatan->taggedUsers()->sync($request->input('tagged_users', []));

        Alert::success('Berhasil', 'Kegiatan berhasil diperbarui')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('kegiatan.create');
    }

    /**
     * Request unlock estimasi from pimpinan.
     */
    public function requestUnlockEstimasi(Request $request, Kegiatan $kegiatan)
    {
        return $this->executeRequestUnlockEstimasi($request, $kegiatan);
    }

    /**
     * Pimpinan menyetujui dan memberikan perpanjangan estimasi waktu pengerjaan tugas.
     */
    public function approveExtension(Request $request, Kegiatan $kegiatan)
    {
        return $this->executeApproveExtension($request, $kegiatan);
    }

    /**
     * Pegawai mengajukan permohonan buka kembali kegiatan yang tidak sengaja diselesaikan.
     */
    public function requestReopen(Request $request, Kegiatan $kegiatan)
    {
        return $this->executeRequestReopen($request, $kegiatan);
    }

    /**
     * Pimpinan atau Admin membuka kembali kegiatan yang sudah selesai.
     */
    public function reopen(Request $request, Kegiatan $kegiatan)
    {
        return $this->executeReopen($request, $kegiatan);
    }


    /**
     * Start task timer.
     */
    public function start(Request $request, Kegiatan $kegiatan)
    {
        return $this->executeStart($request, $kegiatan);
    }

    /**
     * Pause task timer.
     */
    public function pause(Request $request, Kegiatan $kegiatan)
    {
        return $this->executePause($request, $kegiatan);
    }

    /**
     * Stop task timer.
     */
    public function stop(Request $request, Kegiatan $kegiatan)
    {
        return $this->executeStop($request, $kegiatan);
    }

    /**
     * Upload attachment.
     */
    public function uploadAttachment(Request $request, Kegiatan $kegiatan)
    {
        return $this->executeUploadAttachment($request, $kegiatan);
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
        return $this->executeUpdateTags($request, $kegiatan);
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
    /**
     * Download Excel Import Template for Kegiatan.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Kegiatan');

        // Header Columns
        $headers = [
            'A1' => 'uraian_tugas',
            'B1' => 'kategori',
            'C1' => 'periode_akademik',
            'D1' => 'hari',
            'E1' => 'estimasi_tanggal_mulai',
            'F1' => 'estimasi_jam_mulai',
            'G1' => 'estimasi_tanggal_selesai',
            'H1' => 'estimasi_jam_selesai',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerRange = 'A1:H1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('15432D');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Sample Rows
        $sheet->setCellValue('A2', 'Menyusun Laporan Kinerja Mingguan');
        $sheet->setCellValue('B2', 'Rencana Kerja');
        $sheet->setCellValue('C2', '2026/2027 Semester Ganjil');
        $sheet->setCellValue('D2', 'Senin');
        $sheet->setCellValue('E2', date('Y-m-d'));
        $sheet->setCellValue('F2', '08:00');
        $sheet->setCellValue('G2', date('Y-m-d'));
        $sheet->setCellValue('H2', '16:00');

        $sheet->setCellValue('A3', 'Panitia Pelaksana Kegiatan PKKMB');
        $sheet->setCellValue('B3', 'Kepanitiaan');
        $sheet->setCellValue('C3', '2026/2027 Semester Ganjil');
        $sheet->setCellValue('D3', 'Selasa');
        $sheet->setCellValue('E3', date('Y-m-d'));
        $sheet->setCellValue('F3', '08:30');
        $sheet->setCellValue('G3', date('Y-m-d'));
        $sheet->setCellValue('H3', '15:00');

        $sheet->setCellValue('A4', 'Penanganan Gangguan Jaringan Kampus');
        $sheet->setCellValue('B4', 'Insidentil');
        $sheet->setCellValue('C4', '2026/2027 Semester Ganjil');
        $sheet->setCellValue('D4', 'Rabu');
        $sheet->setCellValue('E4', date('Y-m-d'));
        $sheet->setCellValue('F4', '10:00');
        $sheet->setCellValue('G4', date('Y-m-d'));
        $sheet->setCellValue('H4', '12:00');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_import_kegiatan.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import Kegiatan from Excel File.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'periode_akademik_id' => 'nullable|exists:periode_akademiks,id',
            'kategori' => 'nullable|string',
        ]);

        try {
            $file = $request->file('file_excel');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows) || count($rows) < 2) {
                Alert::error('Gagal', 'File Excel kosong atau tidak memiliki data yang valid.')->toToast();
                return redirect()->back();
            }

            $authUser = Auth::user();
            $targetUserId = $authUser ? $authUser->id : null;
            $defaultKategori = $request->input('kategori') ?: 'Rencana Kerja';

            $headerRowIndex = 0;
            $header = array_map(fn($v) => strtolower(trim((string)$v)), $rows[0]);
            
            $uraianIndex = array_search('uraian_tugas', $header);
            if ($uraianIndex === false) {
                foreach ($header as $i => $colName) {
                    if (str_contains($colName, 'uraian') || str_contains($colName, 'tugas') || str_contains($colName, 'kegiatan')) {
                        $uraianIndex = $i;
                        break;
                    }
                }
            }

            if ($uraianIndex === false) {
                for ($r = 1; $r < min(10, count($rows)); $r++) {
                    $testHeader = array_map(fn($v) => strtolower(trim((string)$v)), $rows[$r]);
                    foreach ($testHeader as $i => $colName) {
                        if (str_contains($colName, 'uraian') || str_contains($colName, 'tugas') || str_contains($colName, 'kegiatan')) {
                            $headerRowIndex = $r;
                            $header = $testHeader;
                            $uraianIndex = $i;
                            break 2;
                        }
                    }
                }
            }

            if ($uraianIndex === false) {
                Alert::error('Format Salah', 'Kolom Uraian Tugas / Kegiatan tidak ditemukan pada file Excel.')->toToast();
                return redirect()->back();
            }

            $kategoriIndex = false;
            $periodeIndex = false;
            $hariIndex = false;
            $estTglMulaiIndex = false;
            $estJamMulaiIndex = false;
            $estTglSelesaiIndex = false;
            $estJamSelesaiIndex = false;

            foreach ($header as $i => $col) {
                if (str_contains($col, 'kategori')) $kategoriIndex = $i;
                elseif (str_contains($col, 'periode')) $periodeIndex = $i;
                elseif (str_contains($col, 'hari')) $hariIndex = $i;
                elseif (str_contains($col, 'estimasi') && str_contains($col, 'mulai') && str_contains($col, 'tgl')) $estTglMulaiIndex = $i;
                elseif (str_contains($col, 'estimasi') && str_contains($col, 'mulai') && str_contains($col, 'jam')) $estJamMulaiIndex = $i;
                elseif (str_contains($col, 'estimasi') && str_contains($col, 'selesai') && str_contains($col, 'tgl')) $estTglSelesaiIndex = $i;
                elseif (str_contains($col, 'estimasi') && str_contains($col, 'selesai') && str_contains($col, 'jam')) $estJamSelesaiIndex = $i;
            }

            $defaultPeriodeId = $request->input('periode_akademik_id');
            if (!$defaultPeriodeId) {
                $p = PeriodeAkademik::first();
                $defaultPeriodeId = $p ? $p->id : null;
            }

            $count = 0;
            for ($r = $headerRowIndex + 1; $r < count($rows); $r++) {
                $row = $rows[$r];
                $uraian = !empty($row[$uraianIndex]) ? trim((string)$row[$uraianIndex]) : '';
                if (empty($uraian) || $uraian === '-' || strtolower($uraian) === 'uraian tugas' || strtolower($uraian) === 'uraian kegiatan') {
                    continue;
                }

                $kategoriVal = $defaultKategori;
                if ($kategoriIndex !== false && !empty($row[$kategoriIndex])) {
                    $rawKat = trim((string)$row[$kategoriIndex]);
                    if (stripos($rawKat, 'kepanitiaan') !== false) {
                        $kategoriVal = 'Kepanitiaan';
                    } elseif (stripos($rawKat, 'insidentil') !== false) {
                        $kategoriVal = 'Insidentil';
                    } elseif (stripos($rawKat, 'rencana') !== false) {
                        $kategoriVal = 'Rencana Kerja';
                    }
                }

                $rowPeriodeId = $defaultPeriodeId;
                if ($periodeIndex !== false && !empty($row[$periodeIndex])) {
                    $perText = trim((string)$row[$periodeIndex]);
                    $foundP = PeriodeAkademik::where('nama_periode', 'like', "%{$perText}%")->first();
                    if ($foundP) {
                        $rowPeriodeId = $foundP->id;
                    }
                }

                $hariVal = ($hariIndex !== false && !empty($row[$hariIndex])) ? trim((string)$row[$hariIndex]) : null;
                $estTglMulaiVal = ($estTglMulaiIndex !== false && !empty($row[$estTglMulaiIndex])) ? trim((string)$row[$estTglMulaiIndex]) : null;
                $estJamMulaiVal = ($estJamMulaiIndex !== false && !empty($row[$estJamMulaiIndex])) ? trim((string)$row[$estJamMulaiIndex]) : null;
                $estTglSelesaiVal = ($estTglSelesaiIndex !== false && !empty($row[$estTglSelesaiIndex])) ? trim((string)$row[$estTglSelesaiIndex]) : null;
                $estJamSelesaiVal = ($estJamSelesaiIndex !== false && !empty($row[$estJamSelesaiIndex])) ? trim((string)$row[$estJamSelesaiIndex]) : null;

                Kegiatan::create([
                    'user_id' => $targetUserId,
                    'periode_akademik_id' => $rowPeriodeId,
                    'kategori' => $kategoriVal,
                    'uraian_tugas' => $uraian,
                    'hari' => $hariVal,
                    'estimasi_tanggal_mulai' => $estTglMulaiVal ?: date('Y-m-d'),
                    'estimasi_jam_mulai' => $estJamMulaiVal ?: '08:00:00',
                    'estimasi_tanggal_selesai' => $estTglSelesaiVal ?: date('Y-m-d'),
                    'estimasi_jam_selesai' => $estJamSelesaiVal ?: '16:00:00',
                    'status' => 'Belum Dimulai',
                ]);

                $count++;
            }

            Alert::success('Berhasil', "Berhasil mengimpor {$count} data kegiatan.")
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
     * Build base query for export (PDF & Excel) supporting both options: (1) Periode Akademik, (2) Range Tanggal.
     */
    private function buildExportQuery(Request $request)
    {
        $authUser = Auth::user();
        $query = Kegiatan::with(['user', 'periodeAkademik', 'taggedUsers', 'milestones'])->latest();

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                if ($request->filled('user_id')) {
                    $query->where(function ($q) use ($request) {
                        $q->where('user_id', $request->user_id)
                          ->orWhereHas('taggedUsers', fn($qu) => $qu->where('users.id', $request->user_id));
                    });
                }
            } elseif ($authUser->isPimpinanUnit()) {
                $query->where(function ($q) use ($authUser) {
                    $q->whereHas('user', fn($qu) => $qu->where('unit', $authUser->unit))
                      ->orWhereHas('taggedUsers', fn($qu) => $qu->where('unit', $authUser->unit));
                });
                if ($request->filled('user_id')) {
                    $query->where(function ($q) use ($request) {
                        $q->where('user_id', $request->user_id)
                          ->orWhereHas('taggedUsers', fn($qu) => $qu->where('users.id', $request->user_id));
                    });
                }
            } else {
                $targetId = $authUser->id;
                $query->where(function ($q) use ($targetId) {
                    $q->where('user_id', $targetId)
                      ->orWhereHas('taggedUsers', fn($qu) => $qu->where('users.id', $targetId));
                });
            }
        }

        if ($request->filled('jabatan') && $request->jabatan !== '__all__' && $request->jabatan !== 'all') {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', fn($qu) => $qu->where('jabatan', $request->jabatan))
                  ->orWhereHas('taggedUsers', fn($qt) => $qt->where('jabatan', $request->jabatan));
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $filterType = $request->input('filter_type', 'periode');
        $startDate = $request->input('tanggal_mulai') ?: $request->input('start_date');
        $endDate = $request->input('tanggal_selesai') ?: $request->input('end_date');

        if ($filterType === 'range' || (!$request->filled('periode_akademik_id') && ($startDate || $endDate))) {
            if ($startDate && $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                      ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
                      ->orWhereBetween('estimasi_tanggal_mulai', [$startDate, $endDate])
                      ->orWhereBetween('estimasi_tanggal_selesai', [$startDate, $endDate])
                      ->orWhere(function ($sub) use ($startDate, $endDate) {
                          $sub->whereDate('created_at', '>=', $startDate)
                              ->whereDate('created_at', '<=', $endDate);
                      });
                });
            } elseif ($startDate) {
                $query->where(function ($q) use ($startDate) {
                    $q->whereDate('tanggal_mulai', '>=', $startDate)
                      ->orWhereDate('estimasi_tanggal_mulai', '>=', $startDate)
                      ->orWhereDate('created_at', '>=', $startDate);
                });
            } elseif ($endDate) {
                $query->where(function ($q) use ($endDate) {
                    $q->whereDate('tanggal_selesai', '<=', $endDate)
                      ->orWhereDate('estimasi_tanggal_selesai', '<=', $endDate)
                      ->orWhereDate('created_at', '<=', $endDate);
                });
            }
        } else {
            if ($request->filled('periode_akademik_id')) {
                $query->where('periode_akademik_id', $request->periode_akademik_id);
            }
        }

        return $query;
    }

    /**
     * Export Kegiatan to Excel.
     */
    public function exportExcel(Request $request)
    {
        $authUser = Auth::user();
        $query = $this->buildExportQuery($request);
        $items = $query->latest()->get();

        $filterType = $request->input('filter_type', 'periode');
        $startDate = $request->input('tanggal_mulai') ?: $request->input('start_date');
        $endDate = $request->input('tanggal_selesai') ?: $request->input('end_date');

        $periodeText = 'SEMUA PERIODE';
        if ($filterType === 'range' || (!$request->filled('periode_akademik_id') && ($startDate || $endDate))) {
            if ($startDate && $endDate) {
                $periodeText = date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate));
            } elseif ($startDate) {
                $periodeText = 'DARI ' . date('d/m/Y', strtotime($startDate));
            } elseif ($endDate) {
                $periodeText = 'SAMPAI ' . date('d/m/Y', strtotime($endDate));
            }
        } else {
            if ($request->filled('periode_akademik_id') && ($p = PeriodeAkademik::find($request->periode_akademik_id))) {
                $periodeText = strtoupper($p->nama_periode);
            } elseif ($items->count() > 0 && ($first = $items->first()) && $first->periodeAkademik) {
                $periodeText = strtoupper($first->periodeAkademik->nama_periode);
            }
        }

        $namaStaff = 'SEMUA PEGAWAI';
        $jabatanStaff = 'SEMUA JABATAN';
        $unitStaff = 'SEMUA UNIT';

        $targetUser = null;
        if ($request->filled('user_id')) {
            $targetUser = User::find($request->user_id);
        } elseif ($authUser && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit()) {
            $targetUser = $authUser;
        }

        if ($targetUser) {
            $namaStaff = strtoupper($targetUser->name);
            $jabatanStaff = strtoupper($targetUser->jabatan ?? '-');
            $unitStaff = strtoupper($targetUser->unit ?? '-');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Kegiatan');

        // Row 1: Title
        $titleText = 'LAPORAN REKAPITULASI KEGIATAN (' . $periodeText . ')';
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', $titleText);
        $sheet->getStyle('A1:L1')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:L1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('15432D');
        $sheet->getStyle('A1:L1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Metadata
        $sheet->setCellValue('A3', 'NAMA PEGAWAI');
        $sheet->setCellValue('B3', ': ' . $namaStaff);
        $sheet->getStyle('A3')->getFont()->setBold(true);

        $sheet->setCellValue('A4', 'JABATAN');
        $sheet->setCellValue('B4', ': ' . $jabatanStaff);
        $sheet->getStyle('A4')->getFont()->setBold(true);

        $sheet->setCellValue('A5', 'UNIT');
        $sheet->setCellValue('B5', ': ' . $unitStaff);
        $sheet->getStyle('A5')->getFont()->setBold(true);

        // Headers
        $headers = [
            'A7' => 'NO',
            'B7' => 'PEGAWAI',
            'C7' => 'KATEGORI',
            'D7' => 'HARI',
            'E7' => 'URAIAN KEGIATAN',
            'F7' => 'ESTIMASI PELAKSANAAN',
            'G7' => 'REALISASI PELAKSANAAN',
            'H7' => 'DURASI',
            'I7' => 'STATUS',
            'J7' => 'HASIL KEGIATAN',
            'K7' => 'RENCANA TINDAK LANJUT',
            'L7' => 'BUKTI / BERKAS',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerRange = 'A7:L7';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('15432D');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
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

                    $hours = floor($diffInSeconds / 3600);
                    $minutes = floor(($diffInSeconds % 3600) / 60);
                    $durasiStr = ($hours > 0 ? $hours . 'j ' : '') . $minutes . 'm';
                } catch (\Exception $e) {
                    $durasiStr = '-';
                }
            }

            $estStr = 'Mulai: ' . ($item->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($item->estimasi_tanggal_mulai)) : '-') . ($item->estimasi_jam_mulai ? ' ' . substr($item->estimasi_jam_mulai, 0, 5) : '') . "\n" .
                      'Selesai: ' . ($item->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($item->estimasi_tanggal_selesai)) : '-') . ($item->estimasi_jam_selesai ? ' ' . substr($item->estimasi_jam_selesai, 0, 5) : '');

            $realStr = 'Mulai: ' . ($item->tanggal_mulai ? date('d/m/Y', strtotime($item->tanggal_mulai)) : '-') . ($item->waktu_mulai ? ' ' . substr($item->waktu_mulai, 0, 5) : '') . "\n" .
                       'Selesai: ' . ($item->tanggal_selesai && $item->waktu_selesai !== '00:00:00' ? date('d/m/Y', strtotime($item->tanggal_selesai)) . ' ' . substr($item->waktu_selesai, 0, 5) : '-');

            $fileLinkStr = $item->file ? asset('storage/' . $item->file) : '-';
            $extLinkStr = $item->url_external ?? '-';
            $buktiStr = 'Berkas: ' . $fileLinkStr . "\nLink: ' . $extLinkStr";

            $pegawaiStr = ($item->user->name ?? '-') . "\n(" . ($item->user->jabatan ?? '-') . ')';
            if ($item->taggedUsers->count() > 0) {
                $pegawaiStr .= "\n[Tag: " . implode(', ', $item->taggedUsers->pluck('name')->toArray()) . ']';
            }

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $pegawaiStr);
            $sheet->setCellValue('C' . $rowNum, $item->kategori ?: 'Rencana Kerja');
            $sheet->setCellValue('D' . $rowNum, $item->hari ?: '-');
            $sheet->setCellValue('E' . $rowNum, $item->uraian_tugas);
            $sheet->setCellValue('F' . $rowNum, $estStr);
            $sheet->setCellValue('G' . $rowNum, $realStr);
            $sheet->setCellValue('H' . $rowNum, $durasiStr);
            $sheet->setCellValue('I' . $rowNum, $item->status ?: 'Belum Dimulai');
            $sheet->setCellValue('J' . $rowNum, strip_tags($item->hasil_kerja ?: '-'));
            $sheet->setCellValue('K' . $rowNum, strip_tags($item->rencana_tindak_lanjut ?: '-'));
            $sheet->setCellValue('L' . $rowNum, $buktiStr);

            $sheet->getStyle('A' . $rowNum . ':L' . $rowNum)->getAlignment()->setWrapText(true);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowNum . ':D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $rowNum . ':I' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        $lastRow = max(8, $rowNum - 1);
        $dataRange = 'A7:L' . $lastRow;
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan_Kegiatan_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export to PDF supporting both (1) Periode Akademik and (2) Range Tanggal.
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildExportQuery($request);
        $kegiatans = $query->get();

        $filterType = $request->input('filter_type', 'periode');
        $startDate = $request->input('tanggal_mulai') ?: $request->input('start_date');
        $endDate = $request->input('tanggal_selesai') ?: $request->input('end_date');

        $periodeText = '';
        if ($filterType === 'range' || (!$request->filled('periode_akademik_id') && ($startDate || $endDate))) {
            if ($startDate && $endDate) {
                $periodeText = date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate));
            } elseif ($startDate) {
                $periodeText = 'Dari ' . date('d/m/Y', strtotime($startDate));
            } elseif ($endDate) {
                $periodeText = 'Sampai ' . date('d/m/Y', strtotime($endDate));
            }
        } else {
            if ($request->filled('periode_akademik_id') && ($p = PeriodeAkademik::find($request->periode_akademik_id))) {
                $periodeText = $p->nama_periode;
            }
        }

        $periode = $request->filled('periode_akademik_id') ? PeriodeAkademik::find($request->periode_akademik_id) : null;

        $pdf = Pdf::loadView('pages.kegiatan.pdf', compact('kegiatans', 'periode', 'periodeText'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Kegiatan_' . date('YmdHis') . '.pdf');
    }

    /**
     * Get DataTable source and overall rekap counts for Kegiatan (Semua Kegiatan).
     */
    public function getRekapData(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $query = Kegiatan::with(['user', 'periodeAkademik', 'taggedUsers']);

        if (!$request->filled('jabatan') && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat()) {
            if ($authUser->isPimpinanUnit()) {
                $query->whereHas('user', function ($qu) use ($authUser) {
                    $qu->where('unit', $authUser->unit);
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

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status_filter')) {
            $st = $request->status_filter;
            if ($st === 'belum_selesai') {
                $query->where('status', '!=', 'Selesai');
            } elseif ($st === 'terlambat') {
                $nowStr = now()->format('Y-m-d');
                $query->where(function ($q) use ($nowStr) {
                    $q->where(function ($sub) use ($nowStr) {
                        $sub->where('status', '!=', 'Selesai')
                            ->whereNotNull('estimasi_tanggal_selesai')
                            ->where('estimasi_tanggal_selesai', '<', $nowStr);
                    })->orWhere(function ($sub) {
                        $sub->where('status', 'Selesai')
                            ->whereNotNull('estimasi_tanggal_selesai')
                            ->whereNotNull('tanggal_selesai')
                            ->whereColumn('tanggal_selesai', '>', 'estimasi_tanggal_selesai');
                    });
                });
            } elseif ($st === 'proses') {
                $query->whereIn('status', ['Proses', 'Berjalan']);
            } elseif ($st === 'belum_dimulai') {
                $query->where('status', 'Belum Dimulai');
            } elseif ($st === 'selesai') {
                $query->where('status', 'Selesai');
            }
        }

        if ($request->filled('jabatan')) {
            $filterValue = $request->jabatan;
            if (is_numeric($filterValue)) {
                $query->where('user_id', $filterValue);
            } else {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', function ($qu) use ($request) {
                        $qu->where('jabatan', $request->jabatan);
                    })->orWhereHas('taggedUsers', function ($qt) use ($request) {
                        $qt->where('jabatan', $request->jabatan);
                    });
                });
            }
        }

        $nowStr = now()->format('Y-m-d');
        $total = (clone $query)->count();
        $belum = (clone $query)->where('status', 'Belum Dimulai')->count();
        $proses = (clone $query)->whereIn('status', ['Proses', 'Berjalan'])->count();
        $selesai = (clone $query)->where('status', 'Selesai')->count();
        $terlambat = (clone $query)->where(function ($q) use ($nowStr) {
            $q->where(function ($sub) use ($nowStr) {
                $sub->where('status', '!=', 'Selesai')
                    ->whereNotNull('estimasi_tanggal_selesai')
                    ->where('estimasi_tanggal_selesai', '<', $nowStr);
            })->orWhere(function ($sub) {
                $sub->where('status', 'Selesai')
                    ->whereNotNull('estimasi_tanggal_selesai')
                    ->whereNotNull('tanggal_selesai')
                    ->whereColumn('tanggal_selesai', '>', 'estimasi_tanggal_selesai');
            });
        })->count();
        $percent = $total > 0 ? round(($selesai / $total) * 100) : 0;

        return DataTables::of($query)
            ->addColumn('pembuat', function ($row) {
                $nama = $row->user ? e($row->user->name) : '-';
                $jabatan = $row->user && $row->user->jabatan ? '<small class="text-muted d-block">' . e($row->user->jabatan) . '</small>' : '';
                return '<div class="fw-semibold text-dark">' . $nama . '</div>' . $jabatan;
            })
            ->addColumn('kategori_badge', function ($row) {
                $kat = $row->kategori ?: 'Rencana Kerja';
                $color = match ($kat) {
                    'Kepanitiaan' => 'info',
                    'Insidentil' => 'warning',
                    default => 'success',
                };
                $icon = match ($kat) {
                    'Kepanitiaan' => 'bi-people-fill',
                    'Insidentil' => 'bi-lightning-charge-fill',
                    default => 'bi-journal-check',
                };
                return '<span class="badge bg-' . $color . ' text-white px-2 py-1 text-nowrap"><i class="bi ' . $icon . ' me-1"></i>' . e($kat) . '</span>';
            })
            ->addColumn('estimasi_waktu', function ($row) {
                $html = '<div class="small">';
                if ($row->estimasi_tanggal_mulai && $row->estimasi_tanggal_selesai) {
                    $html .= '<div class="fw-semibold text-dark"><i class="bi bi-calendar3 me-1 text-secondary"></i>' . date('d/m/Y', strtotime($row->estimasi_tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($row->estimasi_tanggal_selesai)) . '</div>';
                } elseif ($row->estimasi_tanggal_selesai) {
                    $html .= '<div class="fw-semibold text-dark"><i class="bi bi-calendar-event me-1 text-secondary"></i>s/d ' . date('d/m/Y', strtotime($row->estimasi_tanggal_selesai)) . '</div>';
                }
                if ($row->estimasi_jam_mulai && $row->estimasi_jam_selesai) {
                    $html .= '<div class="text-muted"><i class="bi bi-clock me-1"></i>' . substr($row->estimasi_jam_mulai, 0, 5) . ' - ' . substr($row->estimasi_jam_selesai, 0, 5) . '</div>';
                }
                if ($row->estimasi_durasi_hari || $row->estimasi_durasi_jam) {
                    $durStr = \App\Helpers\KinerjaWaktuHelper::formatDurasiTarget($row->estimasi_durasi_hari, $row->estimasi_durasi_jam);
                    if ($durStr) {
                        $html .= '<div class="mt-1"><span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5" style="font-size:0.75rem;"><i class="bi bi-stopwatch me-1"></i>Target: ' . e($durStr) . '</span></div>';
                    }
                }
                $html .= '</div>';
                return $html ?: '<span class="text-muted small">-</span>';
            })
            ->addColumn('rekan_kerja', function ($row) {
                if ($row->taggedUsers->count() > 0) {
                    return '<div class="small text-secondary"><i class="bi bi-people me-1"></i>' . e($row->taggedUsers->pluck('name')->implode(', ')) . '</div>';
                }
                return '<span class="text-muted small">-</span>';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status === 'Selesai') {
                    return '<span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Selesai</span>';
                } elseif ($row->status === 'Proses' || $row->status === 'Berjalan') {
                    if ($row->is_paused) {
                        return '<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-pause-circle me-1"></i>Dijeda</span>';
                    }
                    return '<span class="badge bg-primary px-2 py-1"><i class="bi bi-play-circle me-1"></i>Proses</span>';
                }
                return '<span class="badge bg-secondary px-2 py-1"><i class="bi bi-x-circle me-1"></i>Belum Mulai</span>';
            })
            ->addColumn('analisis_kendala', function ($row) {
                $eval = \App\Helpers\KinerjaWaktuHelper::evaluasiKetepatanWaktu($row);

                if ($row->status === 'Selesai') {
                    $res = '<div class="mb-1">' . $eval['badge_html'] . '</div>';
                    $res .= '<div class="small text-muted mb-1"><i class="bi bi-info-circle me-1"></i>' . e($eval['keterangan']) . '</div>';
                    if ($row->tanggal_selesai) {
                        $selesaiStr = date('d/m/Y', strtotime($row->tanggal_selesai));
                        if ($row->waktu_selesai && $row->waktu_selesai !== '00:00:00') {
                            $selesaiStr .= ' (' . substr($row->waktu_selesai, 0, 5) . ' WIB)';
                        }
                        $res .= '<div class="small text-secondary"><i class="bi bi-calendar-check me-1"></i>Realisasi Selesai: ' . $selesaiStr . '</div>';
                    }
                    if ($eval['real_durasi']) {
                        $res .= '<div class="small mt-0.5" style="color: #0f766e;"><i class="bi bi-hourglass-split me-1"></i>Durasi Pengerjaan: <strong>' . e($eval['real_durasi']) . '</strong></div>';
                    }
                    return $res;
                }

                $badges = [];
                if ($eval['is_terlambat']) {
                    $badges[] = '<div class="mb-1">' . $eval['badge_html'] . '</div>';
                    $badges[] = '<div class="small text-danger mb-1"><i class="bi bi-exclamation-triangle me-1"></i>' . e($eval['keterangan']) . '</div>';
                }

                if ($row->status === 'Belum Dimulai') {
                    if (!$eval['is_terlambat'] && $eval['kategori'] === 'menunggu') {
                        $badges[] = '<div><span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><i class="bi bi-calendar-event me-1"></i>Menunggu Jadwal Mulai</span></div>';
                    } elseif (!$eval['is_terlambat']) {
                        $badges[] = '<div><span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>Kendala: Tugas Belum Dimulai Pegawai</span></div>';
                    }
                } elseif ($row->status === 'Proses' || $row->status === 'Berjalan') {
                    if ($row->is_paused) {
                        $badges[] = '<div><span class="badge bg-warning text-dark border border-warning px-2 py-1"><i class="bi bi-pause-circle-fill me-1"></i>Kendala: Tugas Sedang Dijeda (Paused)</span></div>';
                    } else {
                        $badges[] = '<div><span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1"><i class="bi bi-arrow-repeat me-1"></i>Sedang Dikerjakan (Belum Selesai)</span></div>';
                    }
                }

                if ($eval['real_durasi']) {
                    $badges[] = '<div class="small mt-1" style="color: #0f766e;"><i class="bi bi-hourglass-split me-1"></i>Durasi Berjalan: <strong>' . e($eval['real_durasi']) . '</strong></div>';
                }

                if (!empty($row->estimasi_unlock_reason)) {
                    $cleanReason = trim(strip_tags(html_entity_decode((string)$row->estimasi_unlock_reason, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                    $badges[] = '<div class="small text-danger mt-1 p-1 bg-danger-subtle rounded border border-danger-subtle"><i class="bi bi-chat-left-dots-fill me-1"></i><strong>Alasan Kendala:</strong> ' . e($cleanReason) . '</div>';
                }

                if (!empty($row->rencana_tindak_lanjut)) {
                    $cleanRtl = trim(strip_tags(html_entity_decode((string)$row->rencana_tindak_lanjut, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                    $badges[] = '<div class="small text-secondary mt-1"><i class="bi bi-arrow-right-circle-fill me-1"></i><strong>Tindak Lanjut:</strong> ' . e($cleanRtl) . '</div>';
                }

                return implode('', $badges) ?: '<span class="text-muted small">-</span>';
            })
            ->rawColumns(['pembuat', 'kategori_badge', 'estimasi_waktu', 'rekan_kerja', 'status_badge', 'analisis_kendala'])
            ->with('rekap', [
                'total' => $total,
                'belum' => $belum,
                'proses' => $proses,
                'selesai' => $selesai,
                'terlambat' => $terlambat,
                'percent' => $percent
            ])
            ->make(true);
    }
}
