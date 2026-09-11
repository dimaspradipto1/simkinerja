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

        // Otomatis stop dan selesaikan tugas kepanitiaan yang estimasi waktunya sudah habis
        Kepanitiaan::autoStopExpiredTasks();

        if ($request->ajax()) {
            $query = Kepanitiaan::with(['user', 'periodeAkademik', 'taggedUsers', 'milestones']);

            if ($authUser && !$request->filled('jabatan') && !$authUser->isAdmin()) {
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
            $prosesCount = (clone $query)->whereIn('status', ['Proses', 'Berjalan', 'Di-pause'])->count();
            $belumCount = (clone $query)->where(function($q) {
                $q->whereNull('status')->orWhere('status', 'Belum Dimulai');
            })->count();
            $percent = $totalCount > 0 ? round(($selesaiCount / $totalCount) * 100) : 0;

            if ($request->filled('status')) {
                $statusFilter = $request->status;
                if ($statusFilter === 'Selesai') {
                    $query->where('status', 'Selesai');
                } elseif ($statusFilter === 'Proses') {
                    $query->whereIn('status', ['Proses', 'Berjalan', 'Di-pause']);
                } elseif ($statusFilter === 'Belum Dimulai') {
                    $query->where(function($q) {
                        $q->whereNull('status')->orWhere('status', 'Belum Dimulai');
                    });
                }
            }

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
                    
                    // 1. Task Title & Action Buttons Bar (Responsive Mobile Optimized)
                    $collapseId = 'collapseKepanitiaan_' . $row->id;
                    $actionBtns = $this->renderActionButtons($row, $authUser, 'kepanitiaan', 'deleteKepanitiaan', $collapseId);

                    $html .= '<div class="task-card-item p-3 bg-white rounded-3 border mb-3 shadow-2xs d-flex flex-column gap-2" style="width: 100%; max-width: 100%; box-sizing: border-box; overflow: visible;">';
                    $html .= '  <div class="d-flex align-items-center justify-content-between gap-2 pb-1" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '" aria-expanded="false" aria-controls="' . $collapseId . '" title="Klik untuk membuka / menutup rincian">';
                    $html .= '      <div class="text-dark fw-bold fs-6 lh-sm d-flex align-items-center gap-2 text-break flex-grow-1" style="font-size: 0.95rem;">';
                    $html .= '          <i class="bi bi-card-checklist text-primary fs-5 flex-shrink-0"></i>';
                    $html .= '          <span class="text-break">' . e($row->uraian_tugas) . '</span>';
                    $html .= '      </div>';
                    $html .= '      <i class="bi bi-chevron-down text-secondary fs-6 transition-transform toggle-icon flex-shrink-0"></i>';
                    $html .= '  </div>';
                    $html .= '  <div class="mt-2 pt-2.5 border-top task-action-container">';
                    $html .= '      <span class="text-secondary small fw-semibold task-action-label"><i class="bi bi-gear-fill me-1"></i>Aksi & Status:</span>';
                    $html .=        $actionBtns;
                    $html .= '  </div>';
                    $html .= '</div>';

                    // Collapsible Content Container (Dropdown section)
                    $html .= '<div class="collapse" id="' . $collapseId . '">';
                    $html .= '  <div class="d-flex flex-column gap-2 pt-1">';

                    // 2. Metadata Chips (Periode, Hari, Pegawai)
                    $chips = [];
                    if ($row->periodeAkademik) {
                        $chips[] = '<span class="badge px-2 py-1 text-wrap text-break text-start" style="background-color: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-weight: 600; font-size: 0.76rem; line-height: 1.3;"><i class="bi bi-mortarboard-fill me-1"></i>' . e($row->periodeAkademik->nama_periode) . '</span>';
                    }

                    if ($row->user) {
                        $chips[] = '<span class="badge px-2 py-1 bg-light text-dark border fw-normal text-wrap text-break text-start" style="font-size: 0.76rem; line-height: 1.3;"><i class="bi bi-person-fill text-secondary me-1"></i>' . e($row->user->name) . ' <span class="text-muted">(' . e($row->user->jabatan ?? '-') . ')</span></span>';
                    }

                    if ($row->hari) {
                        $chips[] = '<span class="badge px-2 py-1 text-wrap text-break text-start" style="background-color: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe; font-weight: 600; font-size: 0.76rem; line-height: 1.3;"><i class="bi bi-calendar-day me-1"></i>' . e($row->hari) . '</span>';
                    }

                    if (!empty($chips)) {
                        $html .= '<div class="d-flex flex-wrap align-items-center gap-1.5 mb-1">' . implode('', $chips) . '</div>';
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

                    // 2.6 Card Status Kehadiran Absensi (Khusus Pengguna yang Sedang Login)
                    $absensiSessionsList = $this->resolveAbsensiSessions($row);
                    if (!empty($absensiSessionsList)) {
                        foreach ($absensiSessionsList as $absensiInfo) {
                            // Jika sesi dikunci / tidak aktif, tidak perlu ditampilkan sama sekali
                            if (empty($absensiInfo['is_active'])) {
                                continue;
                            }

                            $myAtt = null;
                            if (!empty($absensiInfo['id'])) {
                                $myAtt = \App\Models\KepanitiaanPresensi::where('kepanitiaan_sesi_absensi_id', $absensiInfo['id'])
                                    ->where('user_id', auth()->id())
                                    ->first();
                            }
                            if (!$myAtt) {
                                $modelClass = \App\Models\AbsensiSetting::getModelClass($absensiInfo['slug'] ?? null);
                                if ($modelClass && class_exists($modelClass)) {
                                    $myAtt = $modelClass::where('user_id', auth()->id())->first();
                                }
                            }

                            $html .= \App\Models\AbsensiSetting::renderAttendanceTable($absensiInfo, $myAtt);
                        }
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

                    // Milestone Points Widget Section
                    $html .= \App\Helpers\MilestoneHelper::renderWidget($row->milestones, $row->id, \App\Models\Kepanitiaan::class);

                    // Form Inline Upload Berkas, Link, Hasil Kerja, & Rencana Tindak Lanjut di bawah Milestone
                    $html .= '<form class="form-inline-upload mt-2 p-3 bg-light rounded border shadow-sm" data-id="' . $row->id . '" enctype="multipart/form-data">';
                    $html .= '<div class="row g-2">';
                    
                    // Field TinyMCE Hasil Kerja
                    $html .= '<div class="col-12 col-md-6 mb-2">';
                    $html .= '<label class="form-label mb-1 text-dark small fw-bold"><i class="bi bi-file-earmark-text-fill text-success me-1"></i>Hasil Kerja:</label>';
                    $html .= '<textarea name="hasil_kerja" class="form-control form-control-sm tinymce-editor" id="kepanitiaan_hasil_kerja_' . $row->id . '" rows="3" placeholder="Isikan rincian hasil kerja...">' . e($row->hasil_kerja ?? '') . '</textarea>';
                    $html .= '</div>';

                    // Field TinyMCE Rencana Tindak Lanjut
                    $html .= '<div class="col-12 col-md-6 mb-2">';
                    $html .= '<label class="form-label mb-1 text-dark small fw-bold"><i class="bi bi-arrow-right-circle-fill text-primary me-1"></i>Rencana Tindak Lanjut:</label>';
                    $html .= '<textarea name="rencana_tindak_lanjut" class="form-control form-control-sm tinymce-editor" id="kepanitiaan_tindak_lanjut_' . $row->id . '" rows="3" placeholder="Isikan rencana tindak lanjut...">' . e($row->rencana_tindak_lanjut ?? '') . '</textarea>';
                    $html .= '</div>';

                    // Upload Berkas & External Link
                    $html .= '<div class="col-12 col-md-6 inline-upload-left">';
                    $html .= '<label class="form-label mb-1 text-secondary small fw-semibold d-block"><i class="bi bi-paperclip me-1"></i>Unggah Berkas Kinerja:</label>';
                    $html .= '<input type="file" name="file" class="form-control form-control-sm bg-white input-file-inline">';
                    $html .= '</div>';

                    $externalOnly = '';
                    if (!empty($row->url_external)) {
                        $parts = explode(',', $row->url_external);
                        $nonAbsensi = array_filter($parts, function($p) {
                            foreach (\App\Models\AbsensiSetting::defaults() as $s => $c) {
                                if (str_contains($p, $s)) return false;
                            }
                            return true;
                        });
                        $externalOnly = implode(',', $nonAbsensi);
                    }

                    $html .= '<div class="col-12 col-md-6 inline-upload-right">';
                    $html .= '<label class="form-label mb-1 text-secondary small fw-semibold d-block"><i class="bi bi-link-45deg me-1"></i>Tautan Google Drive / External:</label>';
                    $html .= '<input type="url" name="url_external" class="form-control form-control-sm bg-white input-url-inline" placeholder="https://drive.google.com/..." value="' . e($externalOnly) . '">';
                    $html .= '</div>';

                    $html .= '<div class="col-12 mt-3 text-start">';
                    $html .= '<button type="submit" class="btn btn-sm text-white px-4 fw-bold btn-simpan-inline" style="background-color: #15432d; border-color: #15432d; height: 34px;"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Hasil & Tindak Lanjut</button>';
                    $html .= '</div>';

                    // Hasil Tersimpan (Di bawah Tombol Simpan)
                    $html .= '<div class="col-12 mt-3 pt-2 border-top">';
                    $html .= '<div class="d-flex align-items-center flex-wrap gap-2 mb-1">';
                    $html .= '<span class="text-dark small fw-bold me-2"><i class="bi bi-folder-check me-1"></i>Hasil Tersimpan:</span>';
                    
                    $hasResult = false;
                    if ($row->file) {
                        $hasResult = true;
                        $fileUrl = asset('storage/' . $row->file);
                        $html .= '<a href="' . $fileUrl . '" target="_blank" class="btn btn-sm btn-outline-success text-nowrap fw-semibold px-3 d-inline-flex align-items-center" style="font-size: 0.8rem; height: 30px;"><i class="bi bi-file-earmark-check-fill me-1"></i> Unduh / Lihat Berkas</a>';
                    }

                    if (!empty($row->url_external)) {
                        $parts = array_filter(array_map('trim', explode(',', $row->url_external)));
                        foreach ($parts as $u) {
                            $hasResult = true;
                            $linkTitle = 'Buka Link Eksternal';
                            foreach (\App\Models\AbsensiSetting::defaults() as $s => $cfg) {
                                if (str_contains($u, $s)) {
                                    $linkTitle = 'Buka ' . $cfg['nama'];
                                    break;
                                }
                            }
                            $html .= '<a href="' . e($u) . '" target="_blank" class="btn btn-sm btn-outline-primary text-nowrap fw-semibold px-3 d-inline-flex align-items-center me-1 mb-1" style="font-size: 0.8rem; height: 30px;"><i class="bi bi-box-arrow-up-right me-1"></i> ' . e($linkTitle) . '</a>';
                        }
                    }
                    $html .= '</div>';

                    if (!empty($row->hasil_kerja)) {
                        $hasResult = true;
                        $html .= '<div class="w-100 p-2 mt-2 rounded bg-white border small text-dark"><strong class="text-success d-block mb-1"><i class="bi bi-file-earmark-text-fill me-1"></i>Hasil Kerja:</strong><div>' . $row->hasil_kerja . '</div></div>';
                    }

                    if (!empty($row->rencana_tindak_lanjut)) {
                        $hasResult = true;
                        $html .= '<div class="w-100 p-2 mt-2 rounded bg-white border small text-dark"><strong class="text-primary d-block mb-1"><i class="bi bi-arrow-right-circle-fill me-1"></i>Rencana Tindak Lanjut:</strong><div>' . $row->rencana_tindak_lanjut . '</div></div>';
                    }

                    if (!$hasResult) {
                        $html .= '<span class="text-muted small fst-italic">Belum ada berkas, link, atau hasil tersimpan</span>';
                    }

                    $html .= '</div>'; // end col-12
                    $html .= '</div>'; // end row
                    $html .= '</form>';

                    $html .= '  </div>'; // end collapse inner d-flex
                    $html .= '</div>'; // end collapse

                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('action', function ($row) use ($authUser) {
                    return '';
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

        $pendingRequests = collect([]);
        if ($authUser && !$authUser->isRektor()) {
            $pendingRequestsQuery = Kepanitiaan::whereNotNull('estimasi_unlock_requested_at')
                ->where('status', '!=', 'Selesai');

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

        return view('pages.kepanitiaan.index', compact('usersWithJabatan', 'periodeAkademiks', 'defaultPeriodeId', 'pendingRequests'));
    }

    /**
     * Helper to render action buttons for task row (at top header and action column).
     */
    private function renderActionButtons($row, $authUser, $routePrefix = 'kepanitiaan', $deleteJsFunc = 'deleteKepanitiaan', $collapseId = null)
    {
        $btn = '<div class="action-buttons-wrap d-flex align-items-center flex-wrap gap-2">';

        $waktuMulai = !empty($row->waktu_mulai) && $row->waktu_mulai !== '00:00:00';
        $waktuSelesai = !empty($row->waktu_selesai) && $row->waktu_selesai !== '00:00:00';
        $status = $row->status ?? 'Belum Dimulai';

        $hasMilestones = $row->relationLoaded('milestones') ? ($row->milestones && $row->milestones->count() > 0) : ($row->milestones()->count() > 0);
        $allMilestonesSelesai = false;
        if ($hasMilestones) {
            $uncompleted = $row->relationLoaded('milestones')
                ? $row->milestones->where('status', '!=', 'Selesai')->count()
                : $row->milestones()->where('status', '!=', 'Selesai')->count();
            $allMilestonesSelesai = ($uncompleted === 0);
        }

        if ($allMilestonesSelesai) {
            $status = 'Selesai';
            $waktuSelesai = true;
        }

        // Jika tugas belum selesai tapi waktu estimasi telah habis, otomatis tandai Selesai
        if ($status !== 'Selesai' && !empty($row->estimasi_tanggal_selesai)) {
            $endTime = $row->estimasi_jam_selesai ?: '23:59:59';
            $estimasiEnd = \Carbon\Carbon::parse($row->estimasi_tanggal_selesai . ' ' . $endTime);
            if (\Carbon\Carbon::now()->gte($estimasiEnd)) {
                $status = 'Selesai';
                $waktuSelesai = true;
            }
        }

        $isOwner = $authUser && ($row->user_id === $authUser->id);
        $isLeader = $authUser && ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit());
        $isTaggedOnly = !$isOwner && !$isLeader;

        // Hanya ambil sesi yang AKTIF (tidak dikunci oleh admin)
        $absensiSessions = array_values(array_filter($this->resolveAbsensiSessions($row), function ($s) {
            return !empty($s['is_active']);
        }));

        // KHUSUS PENGGUNA YANG DI-TAG (READ ONLY): Tidak ada tombol timer/edit/delete, tampilkan tombol Scan Barcode
        if ($isTaggedOnly) {
            $btn .= '<div class="badge-status-group d-inline-flex align-items-center gap-2">';
            if ($status === 'Selesai' || $waktuSelesai) {
                $btn .= '<span class="badge bg-success text-white py-2 px-3 d-inline-flex align-items-center justify-content-center" style="font-size: 0.82rem; border-radius: 8px; min-height: 38px;"><i class="bi bi-check-circle-fill me-1"></i> Selesai</span>';
            } elseif ($status === 'Berjalan' || $status === 'Proses') {
                $btn .= '<span class="badge bg-primary text-white py-2 px-3 d-inline-flex align-items-center justify-content-center" style="font-size: 0.82rem; border-radius: 8px; min-height: 38px;"><i class="bi bi-play-circle-fill me-1"></i> Berjalan</span>';
            } elseif ($status === 'Di-pause') {
                $btn .= '<span class="badge bg-warning text-dark py-2 px-3 d-inline-flex align-items-center justify-content-center" style="font-size: 0.82rem; border-radius: 8px; min-height: 38px;"><i class="bi bi-pause-circle-fill me-1"></i> Di-pause</span>';
            } else {
                $btn .= '<span class="badge bg-secondary text-white py-2 px-3 d-inline-flex align-items-center justify-content-center" style="font-size: 0.82rem; border-radius: 8px; min-height: 38px;"><i class="bi bi-hourglass me-1"></i> Belum Dimulai</span>';
            }

            $btn .= '<span class="badge bg-info-subtle text-info border border-info-subtle py-2 px-3 d-inline-flex align-items-center justify-content-center" style="font-size: 0.82rem; border-radius: 8px; min-height: 38px;" title="Anda ditugaskan sebagai Rekan Kerja pada tugas ini"><i class="bi bi-person-check-fill me-1"></i> Rekan Kerja</span>';
            $btn .= '</div>';

            // Tombol Scan Barcode Khusus Rekan Kerja yang Di-tag (Hanya Scan Barcode, Barcode Absensi disembunyikan)
            if (!empty($absensiSessions)) {
                if (count($absensiSessions) === 1) {
                    $sess = $absensiSessions[0];
                    $scanUrl = $sess['scan_url'] ?? route('presensi.scan-proses', ['token' => $sess['token'] ?? '']);
                    $btn .= '<button type="button" class="btn btn-primary text-white fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm btn-scan-barcode-modal btn-mobile-stack" data-slug="' . e($sess['slug']) . '" data-token="' . e($sess['token'] ?? '') . '" data-scan-url="' . e($scanUrl) . '" data-title="' . e($sess['nama']) . '" onclick="window.openScanBarcodeModal(this)" style="background-color: #0d6efd; border-color: #0d6efd; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Scan Barcode Kehadiran ' . e($sess['nama']) . '"><i class="bi bi-qr-code-scan"></i> Scan Barcode</button>';
                } else {
                    $sessionsPayload = [];
                    foreach ($absensiSessions as $sess) {
                        $scanUrl = $sess['scan_url'] ?? route('presensi.scan-proses', ['token' => $sess['token'] ?? '']);
                        $sessionsPayload[] = [
                            'slug' => $sess['slug'],
                            'token' => $sess['token'] ?? '',
                            'nama' => $sess['nama'],
                            'scan_url' => $scanUrl,
                            'is_active' => (bool)($sess['is_active'] ?? true),
                            'url'  => $scanUrl,
                        ];
                    }
                    $sessionsJson = htmlspecialchars(json_encode($sessionsPayload), ENT_QUOTES, 'UTF-8');
                    $btn .= '<button type="button" class="btn btn-primary text-white fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm btn-mobile-stack" onclick="window.openMultiSessionPicker(this, \'scan\')" data-sessions="' . $sessionsJson . '" style="background-color: #0d6efd; border-color: #0d6efd; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Pilih Sesi Scan Barcode"><i class="bi bi-qr-code-scan"></i> Scan Barcode (' . count($absensiSessions) . ') <i class="bi bi-chevron-down ms-1" style="font-size: 0.72rem;"></i></button>';
                }
            }

            // Tombol Submit LPJ Khusus Rekan Kerja yang Di-tag
            $firstLineTitle = trim(explode("\n", (string)$row->uraian_tugas)[0]);
            $lpjUrlTagged = route('lpj.create', [
                'kepanitiaan_id' => $row->id,
                'kepanitiaan' => $row->uraian_tugas,
                'nama_lpj' => 'LPJ ' . $firstLineTitle,
                'tanggal' => $row->tanggal_selesai ?: ($row->tanggal_mulai ?: date('Y-m-d')),
            ]);
            $btn .= '<a href="' . $lpjUrlTagged . '" class="btn btn-outline-success d-inline-flex align-items-center justify-content-center gap-1 btn-mobile-stack" style="padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Submit LPJ Kepanitiaan ini"><i class="bi bi-file-earmark-plus-fill"></i> <span>Submit LPJ</span></a>';

            if ($collapseId) {
                $btn .= '<button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2 shadow-2xs btn-task-toggle btn-mobile-stack" data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '" aria-expanded="false" aria-controls="' . $collapseId . '" style="font-weight: 600; border-radius: 8px; padding: 8px 14px; font-size: 0.84rem; min-height: 38px;" title="Buka / Tutup Rincian"><span class="toggle-text">Rincian</span><i class="bi bi-chevron-down toggle-icon transition-transform"></i></button>';
            }

            $btn .= '</div>';
            return $btn;
        }

        $btn .= '<div class="timer-status-group d-inline-flex align-items-center gap-2">';
        if ($status === 'Selesai' || $waktuSelesai) {
            $btn .= '<button type="button" class="btn text-white fw-bold disabled" style="background-color: #2d6a4f; border-color: #2d6a4f; opacity: 1; cursor: default; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;"><i class="bi bi-check-circle-fill me-1"></i> Selesai</button>';
        } elseif (!$waktuMulai && $status !== 'Di-pause') {
            $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.startTimer(' . $row->id . ')" class="btn text-white fw-bold btn-start-timer" style="background-color: #15432d; border-color: #15432d; cursor: pointer; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;"><i class="bi bi-play-fill me-1"></i> Mulai</button>';
        } else {
            if ($status === 'Di-pause') {
                $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.startTimer(' . $row->id . ')" class="btn btn-success text-white fw-bold btn-resume-timer" style="cursor: pointer; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Lanjut Pekerjaan"><i class="bi bi-play-fill me-1"></i> Lanjut</button>';
            } else {
                $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.pauseTaskTimer(' . $row->id . ', \'' . $routePrefix . '\')" class="btn btn-warning text-dark fw-bold btn-pause-timer" style="cursor: pointer; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Jeda Pekerjaan"><i class="bi bi-pause-fill me-1"></i> Pause</button>';
            }
            $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.stopTimer(' . $row->id . ')" class="btn btn-danger text-white fw-bold btn-stop-timer" style="background-color: #8b0000; border-color: #8b0000; cursor: pointer; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Selesaikan Pekerjaan"><i class="bi bi-stop-fill me-1"></i> Berhenti</button>';
        }
        $btn .= '</div>';

        // Barcode Absensi Button untuk Pemilik Tugas / Pimpinan
        if (!empty($absensiSessions)) {
            if (count($absensiSessions) === 1) {
                $sess = $absensiSessions[0];
                $scanUrl = $sess['scan_url'] ?? route('presensi.scan-proses', ['token' => $sess['token'] ?? '']);
                $absensiUrl = $scanUrl;
                $btn .= '<button type="button" class="btn btn-primary text-white fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm btn-scan-barcode-modal btn-mobile-stack" data-slug="' . e($sess['slug']) . '" data-token="' . e($sess['token'] ?? '') . '" data-scan-url="' . e($scanUrl) . '" data-title="' . e($sess['nama']) . '" onclick="window.openScanBarcodeModal(this)" style="background-color: #0d6efd; border-color: #0d6efd; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Scan Barcode Kehadiran ' . e($sess['nama']) . '"><i class="bi bi-qr-code-scan"></i> Scan Barcode</button>';
                $btn .= '<button type="button" class="btn text-white fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm btn-barcode-absensi btn-mobile-stack" data-slug="' . e($sess['slug']) . '" data-token="' . e($sess['token'] ?? '') . '" data-scan-url="' . e($scanUrl) . '" data-title="' . e($sess['nama']) . '" data-url="' . $absensiUrl . '" onclick="window.openBarcodeModal(this)" style="background-color: #0f172a; border-color: #0f172a; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Buka Barcode Absensi ' . e($sess['nama']) . '"><i class="bi bi-qr-code"></i> Barcode Absensi</button>';
            } else {
                $sessionsPayload = [];
                foreach ($absensiSessions as $sess) {
                    $scanUrl = $sess['scan_url'] ?? route('presensi.scan-proses', ['token' => $sess['token'] ?? '']);
                    $sessionsPayload[] = [
                        'slug' => $sess['slug'],
                        'token' => $sess['token'] ?? '',
                        'nama' => $sess['nama'],
                        'scan_url' => $scanUrl,
                        'is_active' => (bool)($sess['is_active'] ?? true),
                        'url'  => $scanUrl,
                    ];
                }
                $sessionsJson = htmlspecialchars(json_encode($sessionsPayload), ENT_QUOTES, 'UTF-8');
                $btn .= '<button type="button" class="btn btn-primary text-white fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm btn-mobile-stack" onclick="window.openMultiSessionPicker(this, \'scan\')" data-sessions="' . $sessionsJson . '" style="background-color: #0d6efd; border-color: #0d6efd; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Pilih Sesi Scan Barcode"><i class="bi bi-qr-code-scan"></i> Scan Barcode (' . count($absensiSessions) . ') <i class="bi bi-chevron-down ms-1" style="font-size: 0.72rem;"></i></button>';
                $btn .= '<button type="button" class="btn text-white fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm btn-mobile-stack" onclick="window.openMultiSessionPicker(this, \'barcode\')" data-sessions="' . $sessionsJson . '" style="background-color: #0f172a; border-color: #0f172a; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Pilih Sesi Barcode Absensi"><i class="bi bi-qr-code"></i> Barcode Absensi (' . count($absensiSessions) . ') <i class="bi bi-chevron-down ms-1" style="font-size: 0.72rem;"></i></button>';
            }
        }

        $canEditDelete = $isLeader || $isOwner;

        if ($canEditDelete) {
            $editUrl = route($routePrefix . '.edit', $row->id);
            $firstLineTitle = trim(explode("\n", (string)$row->uraian_tugas)[0]);
            $lpjUrl = route('lpj.create', [
                'kepanitiaan_id' => $row->id,
                'kepanitiaan' => $row->uraian_tugas,
                'nama_lpj' => 'LPJ ' . $firstLineTitle,
                'tanggal' => $row->tanggal_selesai ?: ($row->tanggal_mulai ?: date('Y-m-d')),
            ]);

            $safeTitle = htmlspecialchars(addslashes($firstLineTitle), ENT_QUOTES, 'UTF-8');
            $btn .= '<div class="owner-actions-group d-inline-flex align-items-center gap-2">';
            $btn .= '<button type="button" class="btn btn-outline-primary btn-manage-sesi d-inline-flex align-items-center justify-content-center gap-1" style="padding: 7px 12px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" onclick="window.openManageSesiModal(' . $row->id . ', \'' . $safeTitle . '\')" title="Presensi Kepanitiaan"><i class="bi bi-calendar2-check"></i> <span class="d-none d-md-inline">Presensi</span></button>';
            $btn .= '<button type="button" class="btn btn-outline-info btn-quick-tag d-inline-flex align-items-center justify-content-center gap-1" style="padding: 7px 12px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" data-id="' . $row->id . '" data-uraian="' . e($row->uraian_tugas) . '" data-tags="' . e(json_encode($row->taggedUsers->pluck('id')->toArray())) . '" title="Tag Rekan Kerja"><i class="bi bi-person-plus"></i> <span class="d-none d-md-inline">Tag</span></button>';
            $btn .= '<a href="' . $lpjUrl . '" class="btn btn-outline-success btn-create-lpj d-inline-flex align-items-center justify-content-center gap-1" style="padding: 7px 12px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Submit LPJ Kepanitiaan ini"><i class="bi bi-file-earmark-plus-fill"></i> <span class="d-none d-md-inline">Submit LPJ</span></a>';
            $btn .= '<a href="' . $editUrl . '" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-1" style="padding: 7px 12px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Edit Tugas"><i class="bi bi-pencil"></i> <span class="d-none d-md-inline">Edit</span></a>';
            $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.' . $deleteJsFunc . '(' . $row->id . ')" class="btn btn-outline-danger btn-delete-timer d-inline-flex align-items-center justify-content-center gap-1" style="padding: 7px 12px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Hapus Tugas"><i class="bi bi-trash"></i> <span class="d-none d-md-inline">Hapus</span></button>';
            $btn .= '</div>';
        }

        if ($collapseId) {
            $btn .= '<button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2 shadow-2xs btn-task-toggle btn-mobile-stack" data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '" aria-expanded="false" aria-controls="' . $collapseId . '" style="font-weight: 600; border-radius: 8px; padding: 8px 14px; font-size: 0.84rem; min-height: 38px;" title="Buka / Tutup Rincian"><span class="toggle-text">Rincian</span><i class="bi bi-chevron-down toggle-icon transition-transform"></i></button>';
        }

        $btn .= '</div>';
        return $btn;
    }

    /**
     * Helper to resolve attendance sessions from task data (url_external or keywords in uraian_tugas).
     * Returns an array of attendance session configs.
     * @return array<int, array>
     */
    private function resolveAbsensiSessions($row): array
    {
        // 1. Cek relasi dinamis sesiAbsensis dari database terlebih dahulu
        if ($row && method_exists($row, 'sesiAbsensis')) {
            $dbSessions = $row->sesiAbsensis;
            if ($dbSessions && $dbSessions->count() > 0) {
                $matched = [];
                foreach ($dbSessions as $s) {
                    $matched[] = [
                        'id' => $s->id,
                        'token' => $s->token,
                        'slug' => $s->legacy_slug ?: ('sesi-' . $s->id),
                        'nama' => $s->nama_sesi,
                        'is_active' => (bool) $s->is_active,
                        'kategori' => 'Kepanitiaan',
                        'scan_url' => route('presensi.scan-proses', ['token' => $s->token]),
                        'model_type' => 'dynamic',
                    ];
                }
                return array_values($matched);
            }
        }

        // 2. Fallback untuk data lama / URL external
        $defaults = \App\Models\AbsensiSetting::defaults();
        $statuses = \App\Models\AbsensiSetting::getAllStatuses();
        $matched = [];

        // Cek semua slug yang cocok dari url_external
        if (!empty($row->url_external)) {
            foreach ($defaults as $slug => $cfg) {
                if (str_contains($row->url_external, $slug)) {
                    $dbS = \App\Models\KepanitiaanSesiAbsensi::where('legacy_slug', $slug)->first();
                    if ($dbS) {
                        $cfg['id'] = $dbS->id;
                        $cfg['token'] = $dbS->token;
                        $cfg['scan_url'] = route('presensi.scan-proses', ['token' => $dbS->token]);
                        $cfg['is_active'] = (bool) $dbS->is_active;
                    } else {
                        $cfg['is_active'] = (bool)($statuses[$slug] ?? ($cfg['is_active'] ?? true));
                    }
                    $matched[$slug] = $cfg;
                }
            }
        }

        // Deteksi cerdas dari uraian_tugas jika belum ada yang cocok dari url_external
        if (empty($matched)) {
            $text = strtolower($row->uraian_tugas ?? '');

            // PKKMB
            if (str_contains($text, 'pkkmb')) {
                if (str_contains($text, 'hari 3') || str_contains($text, 'hari ke-3') || str_contains($text, 'hari ketiga') || str_contains($text, '3 hari')) {
                    $matched['absensi-pkkmb-ketiga'] = $defaults['absensi-pkkmb-ketiga'] ?? $defaults['absensi-pkkmb-pertama'];
                } elseif (str_contains($text, 'hari 2') || str_contains($text, 'hari ke-2') || str_contains($text, 'hari kedua')) {
                    $matched['absensi-pkkmb-kedua'] = $defaults['absensi-pkkmb-kedua'] ?? $defaults['absensi-pkkmb-pertama'];
                } else {
                    $matched['absensi-pkkmb-pertama'] = $defaults['absensi-pkkmb-pertama'];
                }
            }

            // MILAD
            if (str_contains($text, 'milad')) {
                if (str_contains($text, 'hari 2') || str_contains($text, 'hari ke-2') || str_contains($text, 'hari kedua')) {
                    $matched['absensi-milad-kedua'] = $defaults['absensi-milad-kedua'] ?? $defaults['absensi-milad-pertama'];
                } else {
                    $matched['absensi-milad-pertama'] = $defaults['absensi-milad-pertama'];
                }
            }

            // ESQ
            if (str_contains($text, 'esq')) {
                if (str_contains($text, 'hari 4') || str_contains($text, 'hari ke-4') || str_contains($text, 'hari keempat')) {
                    $matched['absensi-esq-keempat'] = $defaults['absensi-esq-keempat'] ?? $defaults['absensi-esq-pertama'];
                } elseif (str_contains($text, 'hari 3') || str_contains($text, 'hari ke-3') || str_contains($text, 'hari ketiga')) {
                    $matched['absensi-esq-ketiga'] = $defaults['absensi-esq-ketiga'] ?? $defaults['absensi-esq-pertama'];
                } elseif (str_contains($text, 'hari 2') || str_contains($text, 'hari ke-2') || str_contains($text, 'hari kedua')) {
                    $matched['absensi-esq-kedua'] = $defaults['absensi-esq-kedua'] ?? $defaults['absensi-esq-pertama'];
                } else {
                    $matched['absensi-esq-pertama'] = $defaults['absensi-esq-pertama'];
                }
            }

            // Kuliah Umum
            if (str_contains($text, 'kuliah umum')) {
                $matched['absensi-kuliah-umum-pertama'] = $defaults['absensi-kuliah-umum-pertama'];
            }

            foreach ($matched as $slug => &$cfg) {
                $dbS = \App\Models\KepanitiaanSesiAbsensi::where('legacy_slug', $slug)->first();
                if ($dbS) {
                    $cfg['id'] = $dbS->id;
                    $cfg['token'] = $dbS->token;
                    $cfg['scan_url'] = route('presensi.scan-proses', ['token' => $dbS->token]);
                    $cfg['is_active'] = (bool) $dbS->is_active;
                } else {
                    $cfg['is_active'] = (bool)($statuses[$slug] ?? ($cfg['is_active'] ?? true));
                }
            }
            unset($cfg);
        }

        return array_values($matched);
    }

    /**
     * Backward-compatible helper to resolve single attendance session.
     */
    private function resolveAbsensiSession($row): ?array
    {
        $sessions = $this->resolveAbsensiSessions($row);
        return !empty($sessions) ? $sessions[0] : null;
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
        $absensiSessions = \App\Models\AbsensiSetting::getAllGrouped();

        return view('pages.kepanitiaan.create', compact('users', 'periodeAkademiks', 'defaultPeriodeId', 'absensiSessions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KepanitiaanRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = $validated['user_id'] ?? Auth::id();

        if ($request->has('jenis_absensi') || $request->filled('url_external')) {
            $jenisAbsensi = array_filter((array) $request->input('jenis_absensi', []));
            $urls = array_map(function ($slug) {
                return url($slug);
            }, $jenisAbsensi);

            $manualUrl = $request->input('url_external');
            if (!empty($manualUrl)) {
                $parts = array_filter(array_map('trim', explode(',', $manualUrl)));
                foreach ($parts as $p) {
                    if (!in_array($p, $urls)) {
                        $urls[] = $p;
                    }
                }
            }

            if (!empty($urls)) {
                $validated['url_external'] = implode(',', array_unique($urls));
            } else {
                $validated['url_external'] = null;
            }
        }

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

        // Simpan sesi presensi baru jika diisi dari form
        if ($request->filled('nama_sesi_presensi')) {
            $sesiNames = (array) $request->input('nama_sesi_presensi');
            $sesiTgls = (array) $request->input('tanggal_sesi_presensi', []);
            foreach ($sesiNames as $idx => $sName) {
                $trimmed = trim((string)$sName);
                if (!empty($trimmed)) {
                    \App\Models\KepanitiaanSesiAbsensi::create([
                        'kepanitiaan_id' => $kepanitiaan->id,
                        'nama_sesi' => $trimmed,
                        'tanggal' => !empty($sesiTgls[$idx]) ? $sesiTgls[$idx] : ($kepanitiaan->tanggal_mulai ?: date('Y-m-d')),
                        'token' => \Illuminate\Support\Str::random(24),
                        'is_active' => true,
                    ]);
                }
            }
        }

        if ($request->has('tagged_users')) {
            $kepanitiaan->taggedUsers()->sync($request->input('tagged_users'));
            $kepanitiaan->syncToKegiatan();
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

        $isPimpinanOrAdmin = $authUser && ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit());
        $estimasiLocked = !$isPimpinanOrAdmin;

        $users = User::orderBy('name')->get();
        $periodeAkademiks = PeriodeAkademik::orderBy('id', 'asc')->get();

        return view('pages.kepanitiaan.edit', compact('kepanitiaan', 'users', 'periodeAkademiks', 'estimasiLocked'));
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

        $isPimpinanOrAdmin = $authUser && ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit());

        $validated = $request->validated();

        // Tanggal & Waktu (estimasi maupun realisasi) terkunci untuk bawahan begitu tugas
        // sudah tersimpan; hanya Pimpinan/Admin yang boleh mengubahnya langsung.
        // Realisasi (tanggal_mulai/selesai, waktu_mulai/selesai) tetap bisa terisi otomatis
        // lewat tombol Start/Pause/Stop karena rute itu tidak lewat method update() ini.
        $estimasiFields = [
            'estimasi_tanggal_mulai', 'estimasi_tanggal_selesai', 'estimasi_jam_mulai', 'estimasi_jam_selesai',
            'tanggal_mulai', 'tanggal_selesai', 'waktu_mulai', 'waktu_selesai',
        ];

        if (!$isPimpinanOrAdmin) {
            foreach ($estimasiFields as $field) {
                unset($validated[$field]);
            }
        } elseif ($kepanitiaan->estimasi_unlock_requested_at) {
            // Pimpinan menyimpan perubahan pada tugas yang punya permintaan tertunda -> tandai selesai.
            $validated['estimasi_unlock_reason'] = null;
            $validated['estimasi_unlock_requested_at'] = null;
        }

        if ($request->has('jenis_absensi')) {
            $jenisAbsensi = array_filter((array) $request->input('jenis_absensi'));
            $urls = array_map(function ($slug) {
                return url($slug);
            }, $jenisAbsensi);

            $manualUrl = $request->input('url_external');
            if (!empty($manualUrl)) {
                $parts = array_filter(array_map('trim', explode(',', $manualUrl)));
                foreach ($parts as $p) {
                    if (!in_array($p, $urls)) {
                        $urls[] = $p;
                    }
                }
            }

            $validated['url_external'] = !empty($urls) ? implode(',', array_unique($urls)) : null;
        }

        if ($request->hasFile('file')) {
            if ($kepanitiaan->file && Storage::disk('public')->exists($kepanitiaan->file)) {
                Storage::disk('public')->delete($kepanitiaan->file);
            }
            $validated['file'] = $request->file('file')->store('kepanitiaan', 'public');
        }

        $kepanitiaan->update($validated);

        // Simpan sesi presensi baru jika ditambahkan dari form edit
        if ($request->filled('nama_sesi_presensi')) {
            $sesiNames = (array) $request->input('nama_sesi_presensi');
            $sesiTgls = (array) $request->input('tanggal_sesi_presensi', []);
            foreach ($sesiNames as $idx => $sName) {
                $trimmed = trim((string)$sName);
                if (!empty($trimmed)) {
                    \App\Models\KepanitiaanSesiAbsensi::create([
                        'kepanitiaan_id' => $kepanitiaan->id,
                        'nama_sesi' => $trimmed,
                        'tanggal' => !empty($sesiTgls[$idx]) ? $sesiTgls[$idx] : ($kepanitiaan->tanggal_mulai ?: date('Y-m-d')),
                        'token' => \Illuminate\Support\Str::random(24),
                        'is_active' => true,
                    ]);
                }
            }
        }

        $kepanitiaan->taggedUsers()->sync($request->input('tagged_users', []));
        $kepanitiaan->syncToKegiatan();

        Alert::success('Berhasil', 'Kepanitiaan berhasil diperbarui')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('kepanitiaan.index');
    }

    /**
     * Bawahan mengajukan permintaan perubahan Estimasi Pelaksanaan ke pimpinan.
     */
    public function requestUnlockEstimasi(Request $request, Kepanitiaan $kepanitiaan)
    {
        $authUser = Auth::user();

        if (!$authUser) {
            abort(403, 'Anda belum login.');
        }

        $canAccess = false;
        if ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit() || $kepanitiaan->user_id === $authUser->id) {
            $canAccess = true;
        } elseif ($kepanitiaan->relationLoaded('taggedUsers') ? $kepanitiaan->taggedUsers->contains($authUser->id) : $kepanitiaan->taggedUsers()->where('users.id', $authUser->id)->exists()) {
            $canAccess = true;
        }

        if (!$canAccess) {
            abort(403, 'Anda tidak memiliki hak akses untuk tugas ini.');
        }

        $request->validate([
            'estimasi_unlock_reason' => 'required|string|max:1000',
        ]);

        $reason = $request->input('estimasi_unlock_reason');
        if ($request->filled('pimpinan_id')) {
            $pimpinan = \App\Models\User::find($request->input('pimpinan_id'));
            if ($pimpinan) {
                $reason = "[Tujuan: " . $pimpinan->name . " - " . ($pimpinan->jabatan ?: $pimpinan->roles) . "] " . $reason;
            }
        }

        $kepanitiaan->update([
            'estimasi_unlock_reason' => $reason,
            'estimasi_unlock_requested_at' => now(),
        ]);

        Alert::success('Berhasil', 'Permintaan perubahan estimasi telah dikirim ke pimpinan.')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->back();
    }

    /**
     * Pimpinan menyetujui dan memberikan perpanjangan estimasi waktu pengerjaan tugas.
     */
    public function approveExtension(Request $request, Kepanitiaan $kepanitiaan)
    {
        $authUser = Auth::user();

        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit())) {
            abort(403, 'Hanya pimpinan atau administrator yang dapat menyetujui perpanjangan waktu.');
        }

        $request->validate([
            'estimasi_tanggal_selesai' => 'required|date',
            'estimasi_jam_selesai'     => 'required|string',
            'catatan_pimpinan'         => 'nullable|string|max:1000',
        ]);

        $updateData = [
            'estimasi_tanggal_selesai'     => $request->input('estimasi_tanggal_selesai'),
            'estimasi_jam_selesai'         => $request->input('estimasi_jam_selesai'),
            'estimasi_unlock_requested_at'  => null, // Reset request flag
            'estimasi_unlock_approved_at'   => now(),
        ];

        if (empty($kepanitiaan->estimasi_tanggal_selesai_awal)) {
            $updateData['estimasi_tanggal_selesai_awal'] = $kepanitiaan->estimasi_tanggal_selesai;
            $updateData['estimasi_jam_selesai_awal']     = $kepanitiaan->estimasi_jam_selesai;
        }

        if ($request->filled('catatan_pimpinan')) {
            $existing = $kepanitiaan->estimasi_unlock_reason ? $kepanitiaan->estimasi_unlock_reason . " | " : "";
            $updateData['estimasi_unlock_reason'] = $existing . "[Disetujui oleh " . $authUser->name . "]: " . $request->input('catatan_pimpinan');
        }

        $kepanitiaan->update($updateData);

        Alert::success('Berhasil', 'Perpanjangan waktu tugas berhasil disetujui & diberikan kepada pegawai.')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->back();
    }

    /**
     * Helper to automatically log milestone points for task timer events.
     */
    private function autoLogMilestone($kepanitiaan, $action, $now)
    {
        if ($action === 'start') {
            // Mark any paused milestones as completed for phase 1
            $pausedMilestones = $kepanitiaan->milestones()->where('status', 'Di-pause')->get();
            foreach ($pausedMilestones as $pm) {
                $pm->status = 'Selesai';
                if (!$pm->waktu_selesai) {
                    $pm->waktu_selesai = $now;
                }
                $pm->save();
            }

            $runningMilestone = $kepanitiaan->milestones()->where('status', 'Berjalan')->first();
            if (!$runningMilestone) {
                $milestoneCount = $kepanitiaan->milestones()->count();
                $title = ($milestoneCount === 0) ? 'Mulai Pelaksanaan Pekerjaan' : 'Melanjutkan Pekerjaan';
                $kepanitiaan->milestones()->create([
                    'nama_milestone' => $title,
                    'status' => 'Berjalan',
                    'waktu_mulai' => $now,
                    'last_started_at' => $now,
                    'durasi_detik' => 0,
                ]);
            }
        } elseif ($action === 'pause') {
            $runningMilestones = $kepanitiaan->milestones()->where('status', 'Berjalan')->get();
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
            $activeMilestones = $kepanitiaan->milestones()->whereIn('status', ['Berjalan', 'Di-pause'])->get();
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

            $lastMilestone = $kepanitiaan->milestones()->latest()->first();
            if (!$lastMilestone || $lastMilestone->nama_milestone !== 'Selesai Pekerjaan') {
                $kepanitiaan->milestones()->create([
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
     * Start/Resume task timer (Play button).
     */
    public function start(Request $request, Kepanitiaan $kepanitiaan)
    {
        $now = \Carbon\Carbon::now();

        if ($kepanitiaan->status !== 'Selesai' && !empty($kepanitiaan->estimasi_tanggal_selesai)) {
            $endTime = $kepanitiaan->estimasi_jam_selesai ?: '23:59:59';
            $estimasiEnd = \Carbon\Carbon::parse($kepanitiaan->estimasi_tanggal_selesai . ' ' . $endTime);
            if ($now->gt($estimasiEnd)) {
                $kepanitiaan->update([
                    'status' => 'Selesai',
                    'waktu_selesai' => $kepanitiaan->waktu_selesai ?: $endTime,
                    'tanggal_selesai' => $kepanitiaan->tanggal_selesai ?: $kepanitiaan->estimasi_tanggal_selesai,
                    'last_started_at' => null,
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Waktu estimasi tugas telah habis. Tugas otomatis dihentikan dan ditandai selesai.',
                ]);
            }
        }

        $clientTime = $request->input('client_time') ?? now()->format('H:i:s');
        $clientDate = $request->input('client_date') ?? now()->format('Y-m-d');

        $updateData = [
            'status' => 'Berjalan',
            'last_started_at' => $now,
        ];

        if (empty($kepanitiaan->waktu_mulai) || $kepanitiaan->waktu_mulai === '00:00:00') {
            $updateData['waktu_mulai'] = $clientTime;
            $updateData['tanggal_mulai'] = $clientDate;
        }

        $kepanitiaan->update($updateData);

        // Auto log milestone point for start/resume
        $this->autoLogMilestone($kepanitiaan, 'start', $now);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dijalankan (' . substr($clientTime, 0, 5) . ').',
        ]);
    }

    /**
     * Pause task timer (Pause button).
     */
    public function pause(Request $request, Kepanitiaan $kepanitiaan)
    {
        $now = \Carbon\Carbon::now();

        if ($kepanitiaan->status === 'Berjalan' && $kepanitiaan->last_started_at) {
            $elapsedSeconds = (int) abs($now->diffInSeconds($kepanitiaan->last_started_at));
            $kepanitiaan->durasi_detik = (int) ($kepanitiaan->durasi_detik ?? 0) + $elapsedSeconds;
        }

        $kepanitiaan->last_started_at = null;
        $kepanitiaan->status = 'Di-pause';
        $kepanitiaan->save();

        // Auto log milestone point for pause
        $this->autoLogMilestone($kepanitiaan, 'pause', $now);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil di-pause.',
        ]);
    }

    /**
     * Stop task timer (Stop button).
     */
    public function stop(Request $request, Kepanitiaan $kepanitiaan)
    {
        $now = \Carbon\Carbon::now();

        $clientTime = $request->input('client_time') ?? now()->format('H:i:s');
        $clientDate = $request->input('client_date') ?? now()->format('Y-m-d');

        if ($kepanitiaan->status === 'Berjalan' && $kepanitiaan->last_started_at) {
            $elapsedSeconds = (int) abs($now->diffInSeconds($kepanitiaan->last_started_at));
            $kepanitiaan->durasi_detik = (int) ($kepanitiaan->durasi_detik ?? 0) + $elapsedSeconds;
        }

        $updateData = [
            'waktu_selesai' => $clientTime,
            'tanggal_selesai' => $clientDate,
            'status' => 'Selesai',
            'last_started_at' => null,
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

        // Auto log milestone point for stop
        $this->autoLogMilestone($kepanitiaan, 'stop', $now);

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
            $newManualUrl = trim((string) $request->input('url_external'));
            $existingUrls = !empty($kepanitiaan->url_external) ? explode(',', $kepanitiaan->url_external) : [];
            $absensiUrls = array_filter($existingUrls, function($p) {
                foreach (\App\Models\AbsensiSetting::defaults() as $s => $c) {
                    if (str_contains($p, $s)) return true;
                }
                return false;
            });
            if (!empty($newManualUrl)) {
                $absensiUrls[] = $newManualUrl;
            }
            $updateData['url_external'] = !empty($absensiUrls) ? implode(',', array_unique($absensiUrls)) : null;
        }

        if ($request->has('hasil_kerja')) {
            $updateData['hasil_kerja'] = $request->input('hasil_kerja');
        }

        if ($request->has('rencana_tindak_lanjut')) {
            $updateData['rencana_tindak_lanjut'] = $request->input('rencana_tindak_lanjut');
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

        if ($authUser && ($authUser->isAdmin() || $authUser->isPimpinanRektorat())) {
            // Admin & Pimpinan Rektorat dapat menghapus semua tugas kepanitiaan tanpa batas unit
        } elseif ($authUser && $authUser->isPimpinanUnit()) {
            $query->whereHas('user', function ($q) use ($authUser) {
                $q->where('unit', $authUser->unit);
            });
        } elseif ($authUser) {
            $query->where('user_id', $authUser->id);
        }

        $items = $query->get();
        $count = 0;

        foreach ($items as $item) {
            if ($item->file && Storage::disk('public')->exists($item->file)) {
                Storage::disk('public')->delete($item->file);
            }
            // Hapus sesi presensi & presensi jika ada
            if ($item->sesiAbsensis()->exists()) {
                foreach ($item->sesiAbsensis as $sesi) {
                    $sesi->presensis()->delete();
                    $sesi->delete();
                }
            }
            $item->taggedUsers()->detach();
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
        $kepanitiaan->syncToKegiatan();

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
     * Import from Excel file (Mendukung Template Standar & File Hasil Export Laporan).
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

            // 1. Ekstraksi Periode dari Judul File (jika format Export Laporan)
            $extractedPeriodeName = null;
            if (!empty($rows[0][0]) && is_string($rows[0][0])) {
                if (preg_match('/\(([^)]+)\)/', $rows[0][0], $pMatch)) {
                    $cand = trim($pMatch[1]);
                    if (!empty($cand) && strtoupper($cand) !== 'PERIODE AKADEMIK') {
                        $extractedPeriodeName = $cand;
                    }
                }
            }

            // 2. Deteksi Target User (Jika Admin/Superadmin mengimpor file export milik staff)
            $authUserId = auth()->id();
            $targetUserId = $authUserId;
            $authUser = auth()->user();
            if ($authUser && ($authUser->isAdmin() || $authUser->isSuperAdmin() || $authUser->isPimpinanRektorat())) {
                for ($r = 1; $r <= min(5, count($rows) - 1); $r++) {
                    $cell0 = isset($rows[$r][0]) ? strtoupper(trim((string) $rows[$r][0])) : '';
                    if (strpos($cell0, 'NAMA STAFF') !== false && !empty($rows[$r][2])) {
                        $staffName = trim((string) $rows[$r][2]);
                        if (!empty($staffName) && strtoupper($staffName) !== 'SEMUA STAFF') {
                            $matchedUser = User::where('name', $staffName)->first();
                            if ($matchedUser) {
                                $targetUserId = $matchedUser->id;
                            }
                        }
                        break;
                    }
                }
            }

            // 3. Cari Baris Header (Scan hingga baris ke-15)
            $headerRowIndex = null;
            $isExportLaporan = false;
            $header = [];

            for ($r = 0; $r < min(15, count($rows)); $r++) {
                $rowLower = array_map(function ($val) {
                    return strtolower(trim((string) $val));
                }, $rows[$r]);

                // Deteksi file Export Laporan
                if (in_array('uraian tugas', $rowLower) || in_array('estimasi pelaksanaan', $rowLower)) {
                    $headerRowIndex = $r;
                    $isExportLaporan = true;
                    $header = $rowLower;
                    break;
                }

                // Deteksi Template Import Standar
                if (in_array('uraian_tugas', $rowLower)) {
                    $headerRowIndex = $r;
                    $isExportLaporan = false;
                    $header = $rowLower;
                    break;
                }
            }

            // Fallback jika tidak terdeteksi
            if ($headerRowIndex === null) {
                $headerRowIndex = 0;
                $isExportLaporan = false;
                $header = array_map(function ($val) {
                    return strtolower(trim((string) $val));
                }, $rows[0]);
            }

            // 4. Resolusi Periode Akademik Default
            $defaultPeriode = PeriodeAkademik::first();
            $fallbackPeriodeId = $request->input('periode_akademik_id') 
                ? (int) $request->input('periode_akademik_id') 
                : ($defaultPeriode ? $defaultPeriode->id : 1);

            if ($request->filled('periode_akademik_id')) {
                $resolvedGlobalPeriodeId = (int) $request->input('periode_akademik_id');
            } elseif ($extractedPeriodeName) {
                $resolvedGlobalPeriodeId = $this->resolvePeriodeAkademik($extractedPeriodeName, $fallbackPeriodeId);
            } else {
                $resolvedGlobalPeriodeId = $fallbackPeriodeId;
            }

            // 5. Pemetaan Kolom Berdasarkan Format
            $estTglMulaiIndex = false;
            $estJamMulaiIndex = false;
            $estTglSelesaiIndex = false;
            $estJamSelesaiIndex = false;
            $estimasiIndex = false;
            $realisasiIndex = false;
            $hasilKerjaIndex = false;
            $rtlIndex = false;
            $buktiIndex = false;
            $periodeIndex = false;
            $statusIndex = false;

            if ($isExportLaporan) {
                $uraianIndex = array_search('uraian tugas', $header);
                if ($uraianIndex === false) $uraianIndex = array_search('uraian_tugas', $header) ?: 2;
                $hariIndex = array_search('hari', $header);
                $estimasiIndex = array_search('estimasi pelaksanaan', $header);
                $realisasiIndex = array_search('realisasi pelaksanaan', $header);
                $statusIndex = array_search('status & berkas', $header);
                if ($statusIndex === false) $statusIndex = array_search('status', $header);
                $hasilKerjaIndex = array_search('indikator kinerja', $header);
                if ($hasilKerjaIndex === false) $hasilKerjaIndex = array_search('hasil_kerja', $header);
                $rtlIndex = array_search('rencana tindak lanjut', $header);
                if ($rtlIndex === false) $rtlIndex = array_search('rencana_tindak_lanjut', $header);
                $buktiIndex = array_search('bukti', $header);
            } else {
                $uraianIndex = array_search('uraian_tugas', $header);
                if ($uraianIndex === false) $uraianIndex = 0;
                $periodeIndex = array_search('periode_akademik', $header);
                $hariIndex = array_search('hari', $header);
                $estTglMulaiIndex = array_search('estimasi_tanggal_mulai', $header);
                $estJamMulaiIndex = array_search('estimasi_jam_mulai', $header);
                $estTglSelesaiIndex = array_search('estimasi_tanggal_selesai', $header);
                $estJamSelesaiIndex = array_search('estimasi_jam_selesai', $header);
                $statusIndex = array_search('status', $header);
            }

            $count = 0;

            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                $uraianTugas = isset($row[$uraianIndex]) ? trim((string) $row[$uraianIndex]) : '';
                if (empty($uraianTugas)) {
                    continue;
                }

                // Bersihkan tag rekan kerja dari uraian tugas jika ada
                $uraianTugas = preg_replace('/\n?\[Tag:.*?\]/is', '', $uraianTugas);
                $uraianTugas = trim($uraianTugas);
                if (empty($uraianTugas)) {
                    continue;
                }

                // Resolusi Periode Akademik per-baris jika di file template ada kolom periode
                if ($periodeIndex !== false && !empty($row[$periodeIndex])) {
                    $rowPeriodeId = $this->resolvePeriodeAkademik(trim((string) $row[$periodeIndex]), $resolvedGlobalPeriodeId);
                } else {
                    $rowPeriodeId = $resolvedGlobalPeriodeId;
                }

                // Hari
                $hariVal = ($hariIndex !== false && !empty($row[$hariIndex])) ? trim((string) $row[$hariIndex]) : null;
                if ($hariVal === '-') $hariVal = null;

                // Estimasi Waktu & Jam
                $estTglMulaiVal = null;
                $estJamMulaiVal = null;
                $estTglSelesaiVal = null;
                $estJamSelesaiVal = null;

                if ($isExportLaporan && $estimasiIndex !== false && !empty($row[$estimasiIndex])) {
                    $estText = (string) $row[$estimasiIndex];
                    if (preg_match('/Mulai:\s*([0-9\/\-\.]+)(?:\s+([0-9:]+))?/i', $estText, $mStart)) {
                        $estTglMulaiVal = $this->parseExcelDate($mStart[1]);
                        $estJamMulaiVal = isset($mStart[2]) ? $this->parseExcelTime($mStart[2]) : null;
                    }
                    if (preg_match('/Selesai:\s*([0-9\/\-\.]+)(?:\s+([0-9:]+))?/i', $estText, $mEnd)) {
                        $estTglSelesaiVal = $this->parseExcelDate($mEnd[1]);
                        $estJamSelesaiVal = isset($mEnd[2]) ? $this->parseExcelTime($mEnd[2]) : null;
                    }
                } elseif (!$isExportLaporan) {
                    $rawEstTglMulai = ($estTglMulaiIndex !== false && !empty($row[$estTglMulaiIndex])) ? $row[$estTglMulaiIndex] : null;
                    $rawEstJamMulai = ($estJamMulaiIndex !== false && !empty($row[$estJamMulaiIndex])) ? $row[$estJamMulaiIndex] : null;
                    $rawEstTglSelesai = ($estTglSelesaiIndex !== false && !empty($row[$estTglSelesaiIndex])) ? $row[$estTglSelesaiIndex] : null;
                    $rawEstJamSelesai = ($estJamSelesaiIndex !== false && !empty($row[$estJamSelesaiIndex])) ? $row[$estJamSelesaiIndex] : null;

                    $estTglMulaiVal = $this->parseExcelDate($rawEstTglMulai);
                    $estJamMulaiVal = $this->parseExcelTime($rawEstJamMulai);
                    $estTglSelesaiVal = $this->parseExcelDate($rawEstTglSelesai);
                    $estJamSelesaiVal = $this->parseExcelTime($rawEstJamSelesai);
                }

                // Realisasi Waktu (khusus format export laporan jika ada)
                $tglMulaiVal = null;
                $waktuMulaiVal = null;
                $tglSelesaiVal = null;
                $waktuSelesaiVal = null;

                if ($isExportLaporan && $realisasiIndex !== false && !empty($row[$realisasiIndex])) {
                    $realText = (string) $row[$realisasiIndex];
                    if (preg_match('/Mulai:\s*([0-9\/\-\.]+)(?:\s+([0-9:]+))?/i', $realText, $mRStart)) {
                        $tglMulaiVal = $this->parseExcelDate($mRStart[1]);
                        $waktuMulaiVal = isset($mRStart[2]) ? $this->parseExcelTime($mRStart[2]) : null;
                    }
                    if (preg_match('/Selesai:\s*([0-9\/\-\.]+)(?:\s+([0-9:]+))?/i', $realText, $mREnd)) {
                        $tglSelesaiVal = $this->parseExcelDate($mREnd[1]);
                        $waktuSelesaiVal = isset($mREnd[2]) ? $this->parseExcelTime($mREnd[2]) : null;
                    }
                }

                // Status
                $statusVal = 'Belum Dimulai';
                if ($statusIndex !== false && !empty($row[$statusIndex])) {
                    $rawStatus = trim((string) $row[$statusIndex]);
                    if (stripos($rawStatus, 'Selesai') !== false) {
                        $statusVal = 'Selesai';
                    } elseif (stripos($rawStatus, 'Proses') !== false || stripos($rawStatus, 'Berjalan') !== false) {
                        $statusVal = 'Proses';
                    } elseif (stripos($rawStatus, 'Belum') !== false) {
                        $statusVal = 'Belum Dimulai';
                    }
                }

                // Indikator Kinerja / Hasil Kerja
                $hasilKerjaVal = null;
                if ($hasilKerjaIndex !== false && !empty($row[$hasilKerjaIndex])) {
                    $txt = trim((string) $row[$hasilKerjaIndex]);
                    if ($txt !== '-' && $txt !== '') {
                        $hasilKerjaVal = $txt;
                    }
                }

                // Rencana Tindak Lanjut
                $rtlVal = null;
                if ($rtlIndex !== false && !empty($row[$rtlIndex])) {
                    $txt = trim((string) $row[$rtlIndex]);
                    if ($txt !== '-' && $txt !== '') {
                        $rtlVal = $txt;
                    }
                }

                // External Link Bukti
                $urlExternalVal = null;
                if ($buktiIndex !== false && !empty($row[$buktiIndex])) {
                    $buktiText = (string) $row[$buktiIndex];
                    if (preg_match('/Link External:\s*(https?:\/\/[^\s\n\r]+)/i', $buktiText, $mUrl)) {
                        $urlExternalVal = trim($mUrl[1]);
                    }
                }

                Kepanitiaan::create([
                    'user_id' => $targetUserId,
                    'periode_akademik_id' => $rowPeriodeId,
                    'uraian_tugas' => $uraianTugas,
                    'hari' => $hariVal,
                    'estimasi_tanggal_mulai' => $estTglMulaiVal,
                    'estimasi_jam_mulai' => $estJamMulaiVal,
                    'estimasi_tanggal_selesai' => $estTglSelesaiVal,
                    'estimasi_jam_selesai' => $estJamSelesaiVal,
                    'tanggal_mulai' => $tglMulaiVal,
                    'waktu_mulai' => $waktuMulaiVal,
                    'tanggal_selesai' => $tglSelesaiVal,
                    'waktu_selesai' => $waktuSelesaiVal,
                    'status' => $statusVal,
                    'hasil_kerja' => $hasilKerjaVal,
                    'rencana_tindak_lanjut' => $rtlVal,
                    'url_external' => $urlExternalVal,
                ]);

                $count++;
            }

            $sourceFormat = $isExportLaporan ? 'File Hasil Export Laporan' : 'Template Standar';
            Alert::success('Berhasil', "Berhasil mengimpor {$count} data Rencana Kerja Kepanitiaan (Terdeteksi format: {$sourceFormat}).")
                ->toToast()
                ->autoClose(5000)
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

        $query = Kepanitiaan::with(['user', 'periodeAkademik', 'taggedUsers', 'milestones']);

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
        // Row 1: Title (Merged A1:K1 across all columns)
        $titleText = 'LAPORAN RENCANA KERJA KEPANITIAAN DAN REALISASI KERJA (' . $periodeText . ')';
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

        // Row 3, 4, 5: Metadata Staff (Periode Akademik removed to prevent duplication)
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

        // Row 7: Table Header Columns (10 Grouped Columns matching PDF layout)
        $headers = [
            'A7' => 'NO',
            'B7' => 'HARI',
            'C7' => 'URAIAN TUGAS',
            'D7' => 'ESTIMASI PELAKSANAAN',
            'E7' => 'REALISASI PELAKSANAAN',
            'F7' => 'DURASI',
            'G7' => 'STATUS & BERKAS',
            'H7' => 'INDIKATOR KINERJA',
            'I7' => 'RENCANA TINDAK LANJUT',
            'J7' => 'BUKTI',
            'K7' => 'MILESTONE',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerRange = 'A7:K7';
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
                    if ($days > 0) $durasiParts[] = $days . 'h';
                    if ($hours > 0) $durasiParts[] = $hours . 'j';
                    if ($minutes > 0) $durasiParts[] = $minutes . 'm';
                    if ($seconds > 0 || empty($durasiParts)) $durasiParts[] = $seconds . 's';
                    $durasiStr = implode(' ', $durasiParts);
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
            $hasilKerjaText = !empty($item->hasil_kerja) ? trim(strip_tags($item->hasil_kerja)) : '-';

            $buktiStr = 'Berkas: ' . $fileLinkStr . "\n" .
                        'Link External: ' . $extLinkStr;

            $taggedNames = '';
            if ($item->taggedUsers->count() > 0) {
                $taggedNames = "\n[Tag: " . implode(', ', $item->taggedUsers->pluck('name')->toArray()) . ']';
            }

            $milestoneStr = '-';
            if ($item->milestones->count() > 0) {
                $milestoneLines = [];
                foreach ($item->milestones as $ms) {
                    $milestoneLines[] = '- ' . $ms->nama_milestone . ' (' . $ms->status . ', ' . $ms->formatted_durasi . ')';
                }
                $milestoneStr = implode("\n", $milestoneLines);
            }

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $item->hari ?? '-');
            $sheet->setCellValue('C' . $rowNum, $item->uraian_tugas . $taggedNames);
            $sheet->setCellValue('D' . $rowNum, $estStr);
            $sheet->setCellValue('E' . $rowNum, $realStr);
            $sheet->setCellValue('F' . $rowNum, $durasiStr);
            $sheet->setCellValue('G' . $rowNum, $item->status ?? 'Selesai');
            $sheet->setCellValue('H' . $rowNum, $hasilKerjaText);
            $sheet->setCellValue('I' . $rowNum, !empty($item->rencana_tindak_lanjut) ? trim(strip_tags($item->rencana_tindak_lanjut)) : '-');
            $sheet->setCellValue('J' . $rowNum, $buktiStr);
            $sheet->setCellValue('K' . $rowNum, $milestoneStr);

            // Alignments & Styles
            $sheet->getStyle('A' . $rowNum . ':B' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getStyle('D' . $rowNum . ':E' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getStyle('F' . $rowNum . ':G' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getStyle('H' . $rowNum . ':K' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

            $rowNum++;
        }

        // Apply Borders & Wrap Text
        $lastRow = max(7, $rowNum - 1);
        $tableRange = 'A7:K' . $lastRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setRGB('CCCCCC');
        $sheet->getStyle($tableRange)->getAlignment()->setWrapText(true);

        // Column Widths
        $colWidths = [
            'A' => 6,
            'B' => 10,
            'C' => 35,
            'D' => 26,
            'E' => 26,
            'F' => 12,
            'G' => 15,
            'H' => 32,
            'I' => 32,
            'J' => 32,
            'K' => 35,
        ];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
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
        $query = Kepanitiaan::with(['user', 'periodeAkademik', 'taggedUsers', 'milestones']);

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
