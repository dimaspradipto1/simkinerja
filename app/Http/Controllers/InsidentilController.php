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
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InsidentilController extends Controller
{
    use \App\Traits\ManagesKegiatanLifecycle;

    protected string $attachmentFolder = 'insidentils';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        if ($request->ajax()) {
            $query = Insidentil::with(['user', 'periodeAkademik', 'taggedUsers', 'milestones', 'parent', 'subKegiatans']);

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

            $query->orderByRaw('COALESCE(parent_id, id) DESC, (CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END) ASC, id ASC');

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
                          })
                          ->orWhereHas('subKegiatans', function ($sq) use ($keyword) {
                              $sq->where('uraian_tugas', 'like', "%{$keyword}%")
                                 ->orWhere('url_external', 'like', "%{$keyword}%");
                          });
                    });
                })
                ->addColumn('task_details', function ($row) use ($authUser) {
                    $html = '<div class="d-flex flex-column gap-2 py-1 w-100" style="max-width: 100%; box-sizing: border-box;">';
                    
                    // 1. Task Title & Action Buttons Bar (Responsive Mobile Optimized)
                    $collapseId = 'collapseInsidentil_' . $row->id;
                    $actionBtns = $this->renderActionButtons($row, $authUser, 'insidentil', 'deleteInsidentil', $collapseId);

                    $isSub = !empty($row->parent_id);
                    $parentTitle = ($isSub && $row->parent) ? e($row->parent->uraian_tugas) : '';

                    $borderStyle = $isSub ? 'border-left: 4px solid #198754 !important;' : '';
                    $html .= '<div class="task-card-item p-3 bg-white rounded-3 border mb-3 shadow-2xs d-flex flex-column gap-2" style="width: 100%; max-width: 100%; box-sizing: border-box; overflow: visible; ' . $borderStyle . '">';
                    
                    if ($isSub) {
                        $html .= '  <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">';
                        $html .= '      <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 0.76rem;"><i class="bi bi-arrow-return-right"></i> Sub Kegiatan</span>';
                        $html .= '      <span class="small text-secondary fw-semibold">Induk: <strong class="text-dark text-break">' . $parentTitle . '</strong></span>';
                        $html .= '  </div>';
                    } elseif ($row->subKegiatans && $row->subKegiatans->count() > 0) {
                        $html .= '  <div class="d-flex align-items-center gap-2 mb-1">';
                        $html .= '      <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-0.5 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;"><i class="bi bi-diagram-3-fill"></i> ' . $row->subKegiatans->count() . ' Sub Kegiatan</span>';
                        $html .= '  </div>';
                    }

                    $iconClass = $isSub ? 'bi-check2-square text-success' : 'bi-card-checklist text-primary';
                    $html .= '  <div class="d-flex align-items-center justify-content-between gap-2 pb-1" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '" aria-expanded="false" aria-controls="' . $collapseId . '" title="Klik untuk membuka / menutup rincian">';
                    $html .= '      <div class="text-dark fw-bold fs-6 lh-sm d-flex align-items-center gap-2 text-break flex-grow-1" style="font-size: 0.95rem;">';
                    $html .= '          <i class="bi ' . $iconClass . ' fs-5 flex-shrink-0"></i>';
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

                    // 2. Metadata Chips (Kategori, Periode, Hari, Pegawai)
                    $chips = [];
                    $kat = $row->kategori ?: 'Rencana Kerja Insidentil';
                    $chips[] = '<span class="badge px-2 py-1" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-weight: 600; font-size: 0.78rem;"><i class="bi bi-tag-fill me-1"></i>' . e($kat) . '</span>';

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
                    if ($row->taggedUsers && $row->taggedUsers->count() > 0) {
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

                    if ($row->status === 'Di-pause') {
                        $html .= '<div class="col-12 mt-2 pt-2 border-top">';
                        $html .= '<div class="p-2 px-3 rounded-2 border" style="background-color: #fffbeb; border-color: #fde68a !important;">';
                        $html .= '<div class="d-flex align-items-center justify-content-between mb-1">';
                        $html .= '<label class="form-label mb-0 text-warning-emphasis fw-bold small d-flex align-items-center gap-1">';
                        $html .= '<i class="bi bi-pause-circle-fill text-warning"></i> Penyebab Di-pause:';
                        $html .= '</label>';
                        $html .= '<span class="badge bg-warning text-dark fw-semibold" style="font-size: 0.7rem;">Status: Di-pause</span>';
                        $html .= '</div>';
                        $html .= '<div class="mt-1">';
                        $html .= '<textarea name="alasan_pause" class="form-control form-control-sm border-warning-subtle shadow-2xs field-alasan-pause" data-id="' . $row->id . '" data-type="insidentil" rows="2" placeholder="Tuliskan penyebab atau kendala pekerjaan di-pause...">' . e($row->alasan_pause ?? '') . '</textarea>';
                        $html .= '</div>';
                        $html .= '<div class="d-flex justify-content-end mt-2">';
                        $html .= '<button type="button" class="btn btn-sm btn-warning text-dark fw-bold px-3 btn-simpan-alasan-pause" data-id="' . $row->id . '" data-type="insidentil" style="font-size: 0.78rem;">';
                        $html .= '<i class="bi bi-check2-circle me-1"></i> Simpan Penyebab Pause';
                        $html .= '</button>';
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '</div>';
                    }

                    $html .= '</div>'; // end row
                    $html .= '</div>'; // end card

                    // Milestone Points Widget Section
                    $html .= \App\Helpers\MilestoneHelper::renderWidget($row->milestones, $row->id, \App\Models\Insidentil::class);

                    // Form Inline Upload Berkas, Link, Hasil Kerja, & Rencana Tindak Lanjut di bawah Milestone
                    $html .= '<form class="form-inline-upload mt-2 p-3 bg-light rounded border shadow-sm" data-id="' . $row->id . '" enctype="multipart/form-data">';
                    $html .= '<div class="row g-2">';
                    
                    if ($row->status === 'Di-pause') {
                        $html .= '<div class="col-12 mb-2">';
                        $html .= '<label class="form-label mb-1 text-warning-emphasis small fw-bold"><i class="bi bi-pause-circle-fill text-warning me-1"></i>Penyebab Di-pause:</label>';
                        $html .= '<textarea name="alasan_pause" class="form-control form-control-sm" rows="2" placeholder="Isikan penyebab atau kendala pekerjaan di-pause...">' . e($row->alasan_pause ?? '') . '</textarea>';
                        $html .= '</div>';
                    }

                    // Field TinyMCE Hasil Kerja
                    $html .= '<div class="col-12 col-md-6 mb-2">';
                    $html .= '<label class="form-label mb-1 text-dark small fw-bold"><i class="bi bi-file-earmark-text-fill text-success me-1"></i>Hasil Kerja:</label>';
                    $html .= '<textarea name="hasil_kerja" class="form-control form-control-sm tinymce-editor" id="hasil_kerja_' . $row->id . '" rows="3" placeholder="Isikan rincian hasil kerja...">' . e($row->hasil_kerja ?? '') . '</textarea>';
                    $html .= '</div>';

                    // Field TinyMCE Rencana Tindak Lanjut
                    $html .= '<div class="col-12 col-md-6 mb-2">';
                    $html .= '<label class="form-label mb-1 text-dark small fw-bold"><i class="bi bi-arrow-right-circle-fill text-primary me-1"></i>Rencana Tindak Lanjut:</label>';
                    $html .= '<textarea name="rencana_tindak_lanjut" class="form-control form-control-sm tinymce-editor" id="tindak_lanjut_' . $row->id . '" rows="3" placeholder="Isikan rencana tindak lanjut...">' . e($row->rencana_tindak_lanjut ?? '') . '</textarea>';
                    $html .= '</div>';

                    // Upload Berkas & External Link
                    $html .= '<div class="col-12 col-md-6 inline-upload-left">';
                    $html .= '<label class="form-label mb-1 text-secondary small fw-semibold d-block"><i class="bi bi-paperclip me-1"></i>Unggah Berkas Kinerja:</label>';
                    $html .= '<input type="file" name="file" class="form-control form-control-sm bg-white input-file-inline">';
                    $html .= '</div>';

                    $html .= '<div class="col-12 col-md-6 inline-upload-right">';
                    $html .= '<label class="form-label mb-1 text-secondary small fw-semibold d-block"><i class="bi bi-link-45deg me-1"></i>Tautan Google Drive / External:</label>';
                    $html .= '<input type="url" name="url_external" class="form-control form-control-sm bg-white input-url-inline" placeholder="https://drive.google.com/..." value="' . e($row->url_external ?? '') . '">';
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

                    if ($row->url_external) {
                        $hasResult = true;
                        $html .= '<a href="' . e($row->url_external) . '" target="_blank" class="btn btn-sm btn-outline-primary text-nowrap fw-semibold px-3 d-inline-flex align-items-center" style="font-size: 0.8rem; height: 30px;"><i class="bi bi-box-arrow-up-right me-1"></i> Buka Link Eksternal</a>';
                    }
                    $html .= '</div>'; // end flex header

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

                    // Estimasi
                    $estMulai = !empty($row->estimasi_tanggal_mulai) ? date('d/m/Y', strtotime($row->estimasi_tanggal_mulai)) : '';
                    $estJamMulai = !empty($row->estimasi_jam_mulai) ? substr($row->estimasi_jam_mulai, 0, 5) : '';
                    $estSelesai = !empty($row->estimasi_tanggal_selesai) ? date('d/m/Y', strtotime($row->estimasi_tanggal_selesai)) : '';
                    $estJamSelesai = !empty($row->estimasi_jam_selesai) ? substr($row->estimasi_jam_selesai, 0, 5) : '';

                    $estStr = 'Estimasi pelaksanaan: ';
                    if ($estMulai && $estSelesai && $estMulai === $estSelesai) {
                        $estStr .= 'Tanggal ' . $estMulai;
                        if ($estJamMulai && $estJamSelesai) {
                            $estStr .= ' mulai jam ' . $estJamMulai . ' sampai ' . $estJamSelesai . ' WIB';
                        }
                    } else {
                        if ($estMulai) $estStr .= 'Mulai ' . $estMulai . ($estJamMulai ? ' jam ' . $estJamMulai . ' WIB ' : ' ');
                        if ($estSelesai) $estStr .= 'sampai ' . $estSelesai . ($estJamSelesai ? ' jam ' . $estJamSelesai . ' WIB' : '');
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

                    $analisisStr = '';
                    if (!empty($row->hasil_kerja)) {
                        $analisisStr .= 'Hasil kerja: ' . strip_tags($row->hasil_kerja) . '. ';
                    }
                    if (!empty($row->rencana_tindak_lanjut)) {
                        $analisisStr .= 'Rencana tindak lanjut: ' . strip_tags($row->rencana_tindak_lanjut) . '. ';
                    }

                    $linkStr = '';
                    if (!empty($row->file)) {
                        $linkStr .= 'Berkas lampiran tersedia untuk diunduh. ';
                    }
                    if (!empty($row->url_external)) {
                        $linkStr .= 'Tautan eksternal Google Drive tersedia. ';
                    }

                    return $uraian . '|||' . $estStr . $mulaiStr . $selesaiStr . $durasiStr . $analisisStr . $linkStr;
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
        $extensionSupervisors = collect();
        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                $usersWithJabatan = User::whereNotNull('jabatan')->where('jabatan', '!=', '')->orderBy('jabatan', 'asc')->get();
            } elseif ($authUser->isPimpinanUnit()) {
                $usersWithJabatan = User::whereNotNull('jabatan')->where('jabatan', '!=', '')->where('unit', $authUser->unit)->orderBy('jabatan', 'asc')->get();
            } else {
                $extensionSupervisors = $authUser->getDirectSupervisors();
            }
        }

        $pendingRequests = collect([]);
        if ($authUser && !$authUser->isRektor()) {
            $pendingRequestsQuery = Insidentil::whereNotNull('estimasi_unlock_requested_at');

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
                $pendingRequestsQuery->whereRaw('1 = 0');
            }

            $pendingRequests = $pendingRequestsQuery->with(['user', 'periodeAkademik'])->latest('estimasi_unlock_requested_at')->get();
        }

        $defaultPeriode = PeriodeAkademik::first();
        $defaultPeriodeId = $defaultPeriode ? $defaultPeriode->id : null;

        return view('pages.insidentil.index', compact('periodeAkademiks', 'users', 'usersWithJabatan', 'extensionSupervisors', 'pendingRequests', 'defaultPeriodeId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    

    /**
     * Helper to render action buttons for task row (at top header and action column).
     */
    private function renderActionButtons($row, $authUser, $routePrefix = 'insidentil', $deleteJsFunc = 'deleteInsidentil', $collapseId = null)
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

            // Tombol Presensi & Scan Barcode Khusus Rekan Kerja yang Di-tag
            if ($absensiInfo) {
                if (!empty($absensiInfo['id'])) {
                    $btn .= '<a href="' . route('presensi.sesi.show', $absensiInfo['id']) . '" class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center gap-1 shadow-2xs" style="padding: 7px 12px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Presensi ' . e($absensiInfo['nama']) . '"><i class="bi bi-calendar2-check"></i> <span class="d-none d-md-inline">Presensi</span></a>';
                }
                $btn .= '<button type="button" class="btn btn-primary text-white fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm btn-scan-barcode-modal btn-mobile-stack" data-slug="' . e($absensiInfo['slug']) . '" data-title="' . e($absensiInfo['nama']) . '" onclick="window.openScanBarcodeModal(this)" style="background-color: #0d6efd; border-color: #0d6efd; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Scan Barcode Kehadiran ' . e($absensiInfo['nama']) . '"><i class="bi bi-qr-code-scan"></i> Scan Barcode</button>';
            }

            if ($collapseId) {
                $btn .= '<button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2 shadow-2xs btn-task-toggle btn-mobile-stack" data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '" aria-expanded="false" aria-controls="' . $collapseId . '" style="font-weight: 600; border-radius: 8px; padding: 8px 14px; font-size: 0.84rem; min-height: 38px;" title="Buka / Tutup Rincian"><span class="toggle-text">Rincian</span><i class="bi bi-chevron-down toggle-icon transition-transform"></i></button>';
            }

            $btn .= '</div>';
            return $btn;
        }

        $btn .= '<div class="timer-status-group d-inline-flex align-items-center gap-2">';
        if ($status === 'Selesai' || $waktuSelesai) {
            $btn .= '<button type="button" class="btn text-white fw-bold disabled" style="background-color: #2d6a4f; border-color: #2d6a4f; opacity: 1; cursor: default; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;"><i class="bi bi-check-circle-fill me-1"></i> Selesai</button>';
            if ($authUser && $authUser->canUnlockTasks()) {
                $btn .= '<button type="button" class="btn btn-warning text-dark fw-bold btn-reopen-task" data-id="' . $row->id . '" data-type="' . $routePrefix . '" data-uraian="' . e($row->uraian_tugas) . '" data-pegawai="' . e($row->user->name ?? '-') . '" data-reason="" onclick="window.reopenTask(this)" style="padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Buka kunci tugas yang sudah selesai agar dapat dikerjakan lagi"><i class="bi bi-unlock-fill me-1"></i> Buka Kunci</button>';
            }
        } elseif ($isExpired) {
            if (!empty($row->estimasi_unlock_reason) && !empty($row->estimasi_unlock_requested_at)) {
                if ($isLeader) {
                    $btn .= '<button type="button" class="btn btn-warning text-dark fw-bold" data-id="' . $row->id . '" data-uraian="' . e($row->uraian_tugas) . '" data-pegawai="' . e($row->user->name ?? '-') . '" data-reason="' . e($row->estimasi_unlock_reason) . '" data-tgl-selesai="' . e($row->estimasi_tanggal_selesai ?? date('Y-m-d')) . '" data-jam-selesai="' . e($row->estimasi_jam_selesai ? substr($row->estimasi_jam_selesai, 0, 5) : '23:59') . '" data-route="' . route($routePrefix . '.approve-extension', $row->id) . '" onclick="window.openApproveExtensionModal(this)" style="padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Permohonan perpanjangan waktu diajukan pegawai. Klik untuk setujui & beri waktu baru"><i class="bi bi-clock-history me-1"></i> Beri Perpanjangan Waktu</button>';
                } else {
                    $btn .= '<span class="badge bg-warning text-dark py-2 px-3 d-inline-flex align-items-center justify-content-center" style="font-size: 0.82rem; border-radius: 6px; min-height: 38px;" title="Permohonan perpanjangan waktu sedang menunggu persetujuan pimpinan"><i class="bi bi-hourglass-split me-1"></i> Menunggu Persetujuan Pimpinan</span>';
                }
            } else {
                $btn .= '<button type="button" class="btn btn-danger text-white fw-bold btn-request-extension" data-id="' . $row->id . '" data-uraian="' . e($row->uraian_tugas) . '" data-route="' . route($routePrefix . '.request-unlock-estimasi', $row->id) . '" onclick="window.openExtensionModal(this)" style="padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Tugas gagal karena melewati estimasi waktu. Klik untuk minta perpanjangan waktu ke pimpinan"><i class="bi bi-clock-history me-1"></i> Tugas Gagal - Minta Perpanjangan Waktu</button>';
            }
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

        // Presensi & Barcode Absensi Button untuk Pemilik Tugas / Pimpinan
        if ($absensiInfo) {
            if (!empty($absensiInfo['id'])) {
                $btn .= '<a href="' . route('presensi.sesi.show', $absensiInfo['id']) . '" class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center gap-1 shadow-2xs" style="padding: 7px 12px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Presensi ' . e($absensiInfo['nama']) . '"><i class="bi bi-calendar2-check"></i> <span class="d-none d-md-inline">Presensi</span></a>';
            }
            $btn .= '<button type="button" class="btn btn-primary text-white fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm btn-scan-barcode-modal btn-mobile-stack" data-slug="' . e($absensiInfo['slug']) . '" data-title="' . e($absensiInfo['nama']) . '" onclick="window.openScanBarcodeModal(this)" style="background-color: #0d6efd; border-color: #0d6efd; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Scan Barcode Kehadiran ' . e($absensiInfo['nama']) . '"><i class="bi bi-qr-code-scan"></i> Scan Barcode</button>';
            $btn .= '<button type="button" class="btn text-white fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm btn-barcode-absensi btn-mobile-stack" data-slug="' . e($absensiInfo['slug']) . '" data-title="' . e($absensiInfo['nama']) . '" data-url="' . $absensiUrl . '" onclick="window.openBarcodeModal(this)" style="background-color: #0f172a; border-color: #0f172a; padding: 8px 14px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Buka Barcode Absensi ' . e($absensiInfo['nama']) . '"><i class="bi bi-qr-code"></i> Barcode Absensi</button>';
        }

        $canEditDelete = $isLeader || $isOwner;

        if ($canEditDelete) {
            $editUrl = route($routePrefix . '.edit', $row->id);
            $btn .= '<div class="owner-actions-group d-inline-flex align-items-center gap-2">';
            if (!$row->parent_id) {
                $firstLineTitle = trim(explode("\n", (string)$row->uraian_tugas)[0]);
                $safeTitle = htmlspecialchars(addslashes($firstLineTitle), ENT_QUOTES, 'UTF-8');
                $btn .= '<button type="button" class="btn btn-outline-success d-inline-flex align-items-center justify-content-center gap-1 shadow-2xs btn-add-sub-kegiatan" onclick="window.openAddSubKegiatanModal(' . $row->id . ', \'' . $safeTitle . '\', \'' . $routePrefix . '\')" style="padding: 7px 12px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" title="Tambah Sub Kegiatan untuk tugas ini"><i class="bi bi-diagram-3"></i> <span class="d-none d-md-inline">+ Sub Kegiatan</span></button>';
            }
            $btn .= '<button type="button" class="btn btn-outline-info btn-quick-tag d-inline-flex align-items-center justify-content-center gap-1" style="padding: 7px 12px; font-size: 0.84rem; border-radius: 8px; min-height: 38px;" data-id="' . $row->id . '" data-uraian="' . e($row->uraian_tugas) . '" data-tags="' . e(json_encode($row->taggedUsers->pluck('id')->toArray())) . '" title="Tag Rekan Kerja"><i class="bi bi-person-plus"></i> <span class="d-none d-md-inline">Tag</span></button>';
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
        $isAdmin = $authUser && $authUser->isSystemAdmin();
        if (!$isAdmin || empty($data['user_id'])) {
            $data['user_id'] = $authUser->id;
        }

        if (!empty($data['parent_id'])) {
            $parentObj = Insidentil::find($data['parent_id']);
            if ($parentObj) {
                $data['periode_akademik_id'] = $data['periode_akademik_id'] ?? $parentObj->periode_akademik_id;
            }
        }

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('insidentils', 'public');
        }

        $isStartNow = $request->has('start_now') && $request->start_now;
        if ($isStartNow) {
            $data['status'] = 'Proses';
            $data['tanggal_mulai'] = date('Y-m-d');
            $data['waktu_mulai'] = date('H:i:s');
            $data['last_started_at'] = now();
        } else {
            $data['status'] = 'Belum Dimulai';
        }

        $insidentil = Insidentil::create($data);

        if ($request->filled('tagged_users')) {
            $insidentil->taggedUsers()->sync($request->tagged_users);
            $insidentil->syncToKegiatan();
        }
        \App\Helpers\AbsensiKinerjaHelper::syncTaskKeterangan($insidentil);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sub Kegiatan Insidentil berhasil ditambahkan.',
                'data' => $insidentil
            ]);
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

        $isPimpinanOrAdmin = $authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit();
        $estimasiLocked = !$isPimpinanOrAdmin;

        $periodeAkademiks = PeriodeAkademik::orderBy('nama_periode', 'desc')->get();
        $users = User::orderBy('name', 'asc')->get();
        $insidentil->load('taggedUsers');

        return view('pages.insidentil.edit', compact('insidentil', 'periodeAkademiks', 'users', 'estimasiLocked'));
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

        $isPimpinanOrAdmin = $authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit();

        $data = $request->validated();

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
                unset($data[$field]);
            }
        } elseif ($insidentil->estimasi_unlock_requested_at) {
            // Pimpinan menyimpan perubahan pada tugas yang punya permintaan tertunda -> tandai selesai.
            $data['estimasi_unlock_reason'] = null;
            $data['estimasi_unlock_requested_at'] = null;
        }

        if ($request->hasFile('file')) {
            if ($insidentil->file && Storage::disk('public')->exists($insidentil->file)) {
                Storage::disk('public')->delete($insidentil->file);
            }
            $data['file'] = $request->file('file')->store('insidentils', 'public');
        }

        $insidentil->update($data);

        if ($request->has('tagged_users')) {
            $insidentil->taggedUsers()->sync($request->tagged_users ?? []);
            $insidentil->syncToKegiatan();
        }
        \App\Helpers\AbsensiKinerjaHelper::syncTaskKeterangan($insidentil);

        Alert::success('Berhasil', 'Data Rencana Kerja Insidentil berhasil diperbarui.');
        return redirect()->route('insidentil.index');
    }

    /**
     * Bawahan mengajukan permintaan perubahan Estimasi Pelaksanaan ke pimpinan.
     */
    public function requestUnlockEstimasi(Request $request, Insidentil $insidentil)
    {
        return $this->executeRequestUnlockEstimasi($request, $insidentil);
    }

    /**
     * Pimpinan menyetujui dan memberikan perpanjangan estimasi waktu pengerjaan tugas.
     */
    public function approveExtension(Request $request, Insidentil $insidentil)
    {
        return $this->executeApproveExtension($request, $insidentil);
    }

    /**
     * Pegawai mengajukan permohonan buka kembali tugas yang tidak sengaja diselesaikan.
     */
    public function requestReopen(Request $request, Insidentil $insidentil)
    {
        return $this->executeRequestReopen($request, $insidentil);
    }

    /**
     * Pimpinan atau Admin membuka kembali tugas yang sudah selesai.
     */
    public function reopen(Request $request, Insidentil $insidentil)
    {
        return $this->executeReopen($request, $insidentil);
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
     * Start/Resume task timer.
     */
    public function start(Request $request, Insidentil $insidentil)
    {
        return $this->executeStart($request, $insidentil);
    }

    /**
     * Pause task timer.
     */
    public function pause(Request $request, Insidentil $insidentil)
    {
        return $this->executePause($request, $insidentil);
    }

    /**
     * Stop task timer.
     */
    public function stop(Request $request, Insidentil $insidentil)
    {
        return $this->executeStop($request, $insidentil);
    }

    /**
     * Upload attachment file or external link.
     */
    public function uploadAttachment(Request $request, Insidentil $insidentil)
    {
        return $this->executeUploadAttachment($request, $insidentil);
    }


    /**
     * Download Excel Import Template for Insidentil
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Insidentil');

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
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('15432D');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(25);

        $sheet->setCellValue('A2', 'Penanganan Gangguan Jaringan Kampus');
        $sheet->setCellValue('B2', '2026/2027 Semester Ganjil');
        $sheet->setCellValue('C2', 'Senin');
        $sheet->setCellValue('D2', date('Y-m-d'));
        $sheet->setCellValue('E2', '08:00');
        $sheet->setCellValue('F2', date('Y-m-d'));
        $sheet->setCellValue('G2', '16:00');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_import_insidentil.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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
        return $this->executeUpdateTags($request, $insidentil);
    }


    /**
     * Export data to PDF.
     */
    public function exportPdf(Request $request)
    {
        $authUser = Auth::user();
        $query = Insidentil::with(['user', 'periodeAkademik', 'taggedUsers', 'milestones']);

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
                $query->where(function ($q) use ($authUser) {
                    $q->whereHas('user', function ($qu) use ($authUser) {
                        $qu->where('unit', $authUser->unit);
                    })->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                        $qu->where('unit', $authUser->unit);
                    });
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
                $targetId = $authUser->id;
                $query->where(function ($q) use ($targetId) {
                    $q->where('user_id', $targetId)
                      ->orWhereHas('taggedUsers', function ($qu) use ($targetId) {
                          $qu->where('users.id', $targetId);
                      });
                });
            }
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

        $items = $query->latest()->get();

        $namaStaff = 'SEMUA STAFF';
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
        } elseif ($items->count() > 0 && $items->pluck('user_id')->unique()->count() === 1) {
            $firstUser = $items->first()->user;
            if ($firstUser) {
                $namaStaff = strtoupper($firstUser->name);
                $jabatanStaff = strtoupper($firstUser->jabatan ?? '-');
                $unitStaff = strtoupper($firstUser->unit ?? '-');
            }
        }

        // Periode Akademik / Rentang Tanggal Text (Lookup by request, item relation, or latest record)
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
            } elseif ($items->count() > 0 && $items->first()->periodeAkademik) {
                $periodeText = strtoupper($items->first()->periodeAkademik->nama_periode);
            }
        }

        $safeStaff = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $namaStaff)));
        $safePeriode = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $periodeText)));
        $filename = ($safeStaff ?: 'Semua Staff') . '_' . ($safePeriode ?: 'Periode') . '_laporan rencana kerja insidentil.pdf';

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
        $query = Insidentil::with(['user', 'periodeAkademik', 'taggedUsers', 'milestones']);

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
                $query->where(function ($q) use ($authUser) {
                    $q->whereHas('user', function ($qu) use ($authUser) {
                        $qu->where('unit', $authUser->unit);
                    })->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                        $qu->where('unit', $authUser->unit);
                    });
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
                $targetId = $authUser->id;
                $query->where(function ($q) use ($targetId) {
                    $q->where('user_id', $targetId)
                      ->orWhereHas('taggedUsers', function ($qu) use ($targetId) {
                          $qu->where('users.id', $targetId);
                      });
                });
            }
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

        $items = $query->latest()->get();

        $namaStaff = 'SEMUA STAFF';
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
        } elseif ($items->count() > 0 && $items->pluck('user_id')->unique()->count() === 1) {
            $firstUser = $items->first()->user;
            if ($firstUser) {
                $namaStaff = strtoupper($firstUser->name);
                $jabatanStaff = strtoupper($firstUser->jabatan ?? '-');
                $unitStaff = strtoupper($firstUser->unit ?? '-');
            }
        }

        // Periode Akademik / Rentang Tanggal Text (Lookup by request, item relation, or latest record)
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
            } elseif ($items->count() > 0 && $items->first()->periodeAkademik) {
                $periodeText = strtoupper($items->first()->periodeAkademik->nama_periode);
            }
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Insidentil');

        // Row 1: Title (Merged A1:K1 across all columns)
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

    // End of InsidentilController
}
