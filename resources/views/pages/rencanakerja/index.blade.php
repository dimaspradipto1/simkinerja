@extends('layouts.dashboard.template')

@section('content')
<style>
    .card-header-green {
        background-color: #15432d;
        color: #ffffff;
        padding: 10px 16px;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        font-weight: 600;
        font-size: 0.95rem;
    }
    .btn-green-add {
        background-color: #2d6a4f;
        color: #ffffff !important;
        border: none;
        padding: 4px 12px;
        font-size: 0.85rem;
        border-radius: 4px;
    }
    .btn-green-add:hover {
        background-color: #1b4332;
    }
    .btn-mulai-green {
        background-color: #15432d;
        color: #ffffff !important;
        border: none;
        padding: 4px 14px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 4px;
    }
    .btn-mulai-green:hover {
        background-color: #0f3121;
    }
    .btn-selesai-red {
        background-color: #8b0000;
        color: #ffffff !important;
        border: none;
        padding: 4px 14px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 4px;
    }
    .btn-selesai-red:hover {
        background-color: #660000;
    }
    #rencanakerja-table tbody tr td {
        vertical-align: middle;
        padding: 10px 14px;
        border-bottom: 1px solid #edf2f7;
    }
    #rencanakerja-table thead {
        display: none; /* Clean list look matching reference screenshot */
    }
    @media (max-width: 767.98px) {
        .card-header-green {
            padding: 12px 14px;
        }
        .card-header-green .header-title-text {
            font-size: 0.95rem !important;
            line-height: 1.4 !important;
            margin-bottom: 4px;
        }
        .header-action-group {
            width: 100% !important;
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 6px !important;
            margin-top: 6px;
        }
        .header-action-group .btn {
            flex: 1 1 auto !important;
            justify-content: center !important;
            font-size: 0.8rem !important;
            padding: 6px 10px !important;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            float: none !important;
            text-align: left !important;
            margin-bottom: 0.75rem;
            width: 100% !important;
        }
        .dataTables_wrapper .dataTables_filter label {
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
            gap: 8px;
            font-weight: 600;
            color: #475569;
        }
        .dataTables_wrapper .dataTables_filter input {
            flex-grow: 1 !important;
            width: 100% !important;
            margin-left: 0 !important;
            border-radius: 6px !important;
            padding: 6px 12px !important;
        }

        /* Mobile Card Table Transformation - Stretch 100% Full Width to the Right */
        #rencanakerja-table {
            width: 100% !important;
            display: block !important;
            border-collapse: separate !important;
            border-spacing: 0 10px !important;
        }
        #rencanakerja-table tbody {
            display: block !important;
            width: 100% !important;
        }
        #rencanakerja-table tbody tr {
            display: block !important;
            width: 100% !important;
            background: #ffffff;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            padding: 12px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            margin-bottom: 10px !important;
            box-sizing: border-box !important;
        }
        #rencanakerja-table tbody tr td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            border: none !important;
            padding: 4px 0 !important;
        }
        #rencanakerja-table tbody tr td:first-child {
            display: none !important; /* Hide floating number column on mobile for clean card layout */
        }
        #rencanakerja-table tbody tr td:last-child {
            margin-top: 8px;
            padding-top: 8px !important;
            border-top: 1px dashed #cbd5e1 !important;
            width: 100% !important;
        }
        .action-buttons-wrap {
            justify-content: flex-start !important;
            width: 100% !important;
            gap: 8px !important;
        }
        .action-buttons-wrap .btn {
            font-size: 0.8rem !important;
        }
        .inline-upload-left {
            border-right: none !important;
            border-bottom: 1px solid #dee2e6 !important;
            padding-bottom: 1rem !important;
            margin-bottom: 0.5rem !important;
            padding-right: 0 !important;
        }
        .inline-upload-right {
            padding-left: 0 !important;
        }
    }
</style>

<div class="pagetitle">
    <h1>Rencana Kerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Rencana Kerja</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    @if(auth()->check() && (auth()->user()->isPimpinanUnit() || auth()->user()->isAdmin()))
    <!-- Top Section: Kriteria Checklist Jabatan (Khusus Pimpinan & Admin) -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <span class="header-title-text">Kriteria Checklist Jabatan</span>
                    <a href="{{ route('rencana-kerja.create') }}" class="btn btn-green-add text-nowrap ms-auto ms-md-0">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Tugas
                    </a>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <label class="form-label mb-0 fw-semibold text-secondary small text-nowrap">Kriteria Checklist untuk jabatan:</label>
                        <div style="min-width: 250px; max-width: 650px;" class="flex-grow-1">
                            <select class="form-select" id="filter-jabatan">
                                <option value="">-- Semua Jabatan --</option>
                                @foreach($usersWithJabatan as $u)
                                    <option value="{{ $u->jabatan }}" {{ (request('jabatan') == $u->jabatan || (!request()->has('jabatan') && auth()->user()->jabatan == $u->jabatan)) ? 'selected' : '' }}>
                                        {{ $u->jabatan }} - {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Bottom Section: Checklist Tugas -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="header-title-text fw-semibold text-break">
                        Rencana Kerja &mdash; <span id="judul-jabatan">{{ auth()->user()->jabatan ? auth()->user()->jabatan . ' - ' . auth()->user()->name : 'Semua Jabatan' }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap me-auto me-md-0">
                        <button type="button" id="btn-bulk-delete" class="btn btn-sm btn-danger text-white fw-bold text-nowrap d-none">
                            <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih (<span id="selected-count">0</span>)
                        </button>
                        <button type="button" id="btn-voice-rencanakerja" class="btn btn-sm btn-warning text-dark fw-bold text-nowrap">
                            <i class="bi bi-volume-up-fill me-1"></i> Suara
                        </button>
                        @if(auth()->check() && (auth()->user()->isPimpinanUnit() || auth()->user()->isAdmin()))
                            <button type="button" class="btn btn-sm btn-success text-white fw-semibold text-nowrap" style="background-color: #2d6a4f; border-color: #2d6a4f;" data-bs-toggle="modal" data-bs-target="#modalExportExcel">
                                <i class="bi bi-file-earmark-arrow-down-fill me-1"></i> Export Laporan
                            </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-light border text-dark fw-semibold text-nowrap" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
                            <i class="bi bi-file-earmark-excel text-success me-1"></i> Import Excel
                        </button>
                        @if(auth()->check() && (!auth()->user()->isPimpinanUnit() && !auth()->user()->isAdmin()))
                            <a href="{{ route('rencana-kerja.create') }}" class="btn btn-green-add text-nowrap">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Tugas
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover w-100" id="rencanakerja-table">
                            <thead>
                                <tr>
                                    <th width="3%" class="text-center align-middle">
                                        <input type="checkbox" id="check-all" class="form-check-input" style="cursor: pointer; width: 18px; height: 18px;" title="Pilih Semua">
                                    </th>
                                    <th width="4%">No</th>
                                    <th>Uraian Tugas</th>
                                    <th width="25%" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Export Excel & PDF -->
<div class="modal fade" id="modalExportExcel" tabindex="-1" aria-labelledby="modalExportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #15432d;">
                <h5 class="modal-title fw-bold" id="modalExportExcelLabel">
                    <i class="bi bi-file-earmark-arrow-down-fill me-2"></i> Export Laporan Rencana Kerja
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rencana-kerja.export-excel') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="export_periode_akademik_id" class="form-label fw-bold text-dark mb-1">
                            Pilih Periode Akademik <span class="text-danger">*</span>
                        </label>
                        <select name="periode_akademik_id" id="export_periode_akademik_id" class="form-select bg-white" required>
                            <option value="">-- Pilih Periode Akademik --</option>
                            @foreach($periodeAkademiks as $p)
                                <option value="{{ $p->id }}" {{ $p->id == $defaultPeriodeId ? 'selected' : '' }}>
                                    {{ $p->nama_periode }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(auth()->check() && (auth()->user()->isPimpinanUnit() || auth()->user()->isAdmin()))
                    <div class="mb-3">
                        <label for="export_jabatan" class="form-label fw-bold text-dark mb-1">
                            Pilih Jabatan / Staff <span class="text-muted fw-normal">(Opsional)</span>
                        </label>
                        <select name="jabatan" id="export_jabatan" class="form-select bg-white">
                            <option value="">-- Semua Staff / Jabatan --</option>
                            @foreach($usersWithJabatan as $u)
                                <option value="{{ $u->jabatan }}">{{ $u->name }} &mdash; {{ $u->jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <div class="d-flex gap-2">
                        <button type="submit" formaction="{{ route('rencana-kerja.export-excel') }}" class="btn btn-success px-3 text-white fw-bold" style="background-color: #15432d; border-color: #15432d;">
                            <i class="bi bi-file-earmark-excel-fill me-1"></i> Unduh Excel
                        </button>
                        <button type="submit" formaction="{{ route('rencana-kerja.export-pdf') }}" class="btn btn-danger px-3 text-white fw-bold">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Unduh PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #2d6a4f;">
                <h5 class="modal-title fw-bold" id="modalImportExcelLabel">
                    <i class="bi bi-file-earmark-excel me-2"></i> Import Rencana Kerja Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rencana-kerja.import-excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded" style="background-color: #e0f2fe; border: 1px solid #bae6fd;">
                        <p class="mb-1 text-dark" style="font-size: 0.9rem;">
                            Format header: <strong>uraian_tugas</strong>
                        </p>
                        <a href="{{ route('rencana-kerja.download-template') }}" class="btn btn-sm text-primary p-0 fw-bold border-0 bg-transparent" style="font-size: 0.95rem;">
                            <i class="bi bi-download me-1"></i> Download Template
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2">Periode Akademik Default <span class="text-muted fw-normal" style="font-size: 0.85rem;">(Opsional, jika di Excel kosong)</span></label>
                        <select name="periode_akademik_id" class="form-select bg-white">
                            <option value="">-- Otomatis Sesuai Data Excel --</option>
                            @if(isset($periodeAkademiks))
                                @foreach($periodeAkademiks as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_periode }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2">Pilih File Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file_excel" class="form-control bg-white" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 text-white fw-bold" style="background-color: #15432d; border-color: #15432d;">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#btn-voice-rencanakerja').on('click', function() {
            let jabatanVal = $('#filter-jabatan').length ? $('#filter-jabatan').val() : '';
            let voiceText = "Halaman Laporan Rencana Kerja untuk " + (jabatanVal ? "jabatan " + jabatanVal : "{{ auth()->user()->name }}") + ". ";

            let taskItems = [];
            if (typeof table !== 'undefined' && table.rows) {
                let rowsData = table.rows({ search: 'applied' }).data();
                for (let i = 0; i < rowsData.length; i++) {
                    let rData = rowsData[i];
                    if (rData && rData.voice_narration) {
                        let parts = rData.voice_narration.split('|||');
                        let uraianTitle = parts[0] || '';
                        let detailsStr = parts[1] || '';
                        taskItems.push("Uraian Kerja " + (i + 1) + ": " + uraianTitle + ". " + detailsStr);
                    }
                }
            }

            // Fallback to DOM parsing if DataTables rows are empty or initializing
            if (taskItems.length === 0) {
                $('#rencanakerja-table tbody tr').each(function(idx) {
                    let row = $(this);
                    let taskTitle = row.find('.text-dark.fw-semibold, .fw-semibold.text-dark, .lh-sm').first().text().trim();
                    if (taskTitle) {
                        let textContent = row.text();
                        let no = idx + 1;
                        let uraianStr = "Uraian Kerja " + no + ": " + taskTitle + ". ";

                        // Estimasi
                        let estStr = "Estimasi: ";
                        let estMatch = textContent.match(/Estimasi Pelaksanaan:\s*([^\n\r\t]+)/i);
                        if (estMatch && estMatch[1] && estMatch[1].trim() !== '-') {
                            estStr += estMatch[1].trim() + ". ";
                        } else {
                            estStr += "Belum ada. ";
                        }

                        // Waktu Mulai
                        let mulaiStr = "Waktu mulai: ";
                        let mulaiMatch = textContent.match(/Waktu Mulai:\s*([^\n\r\t]+)/i);
                        if (mulaiMatch && mulaiMatch[1] && mulaiMatch[1].trim() !== '-') {
                            mulaiStr += mulaiMatch[1].trim() + ". ";
                        } else {
                            mulaiStr += "Belum dimulai. ";
                        }

                        // Waktu Selesai
                        let selesaiStr = "Waktu selesai: ";
                        let selesaiMatch = textContent.match(/Waktu Selesai:\s*([^\n\r\t]+)/i);
                        if (selesaiMatch && selesaiMatch[1] && selesaiMatch[1].trim() !== '-') {
                            selesaiStr += selesaiMatch[1].trim() + ". ";
                        } else {
                            selesaiStr += "Belum selesai. ";
                        }

                        // Durasi
                        let durasiStr = "Durasi: ";
                        let durasiMatch = textContent.match(/Total Durasi:\s*([^\n\r\t]+)/i);
                        if (durasiMatch && durasiMatch[1] && durasiMatch[1].trim() !== '-') {
                            durasiStr += durasiMatch[1].trim() + ". ";
                        } else {
                            durasiStr += "Belum ada. ";
                        }

                        // Analisis
                        let statusText = "Belum Dimulai";
                        if (textContent.includes("Selesai")) statusText = "Selesai";
                        else if (textContent.includes("Proses")) statusText = "Proses";

                        let analisisStr = "Analisis: ";
                        if (statusText === "Selesai") {
                            analisisStr += "Selesai tepat waktu. ";
                        } else if (statusText === "Proses") {
                            analisisStr += "Masih berproses. ";
                        } else {
                            analisisStr += "Belum dikerjakan. ";
                        }

                        // Link Eksternal Only
                        let hasLink = row.find('.bi-link-45deg, .bi-box-arrow-up-right').length > 0 || textContent.includes('Link External') || textContent.includes('http');
                        let linkStr = "Link eksternal: " + (hasLink ? "Ada link eksternal." : "Tidak ada link eksternal.") + " ";

                        taskItems.push(uraianStr + estStr + mulaiStr + selesaiStr + durasiStr + analisisStr + linkStr);
                    }
                });
            }

            if (taskItems.length > 0) {
                voiceText += "Menampilkan " + taskItems.length + " uraian kerja: " + taskItems.join(" ");

                // Combined Rekapitulasi Capaian Kinerja across ALL tasks in dataset
                let totalCount = taskItems.length;
                let selesaiCount = 0;
                let prosesCount = 0;
                let belumCount = 0;

                if (typeof window.latestOverallRekap !== 'undefined' && window.latestOverallRekap) {
                    totalCount = window.latestOverallRekap.total || taskItems.length;
                    selesaiCount = window.latestOverallRekap.selesai || 0;
                    prosesCount = window.latestOverallRekap.proses || 0;
                    belumCount = window.latestOverallRekap.belum || 0;
                } else if (typeof table !== 'undefined' && table.rows) {
                    let rowsData = table.rows().data();
                    totalCount = rowsData.length;
                    for (let i = 0; i < rowsData.length; i++) {
                        let rData = rowsData[i];
                        let txt = rData ? (rData.task_details || '') + (rData.voice_narration || '') : '';
                        if (txt.includes('Selesai')) selesaiCount++;
                        else if (txt.includes('Proses')) prosesCount++;
                        else belumCount++;
                    }
                }

                let percent = totalCount > 0 ? Math.round((selesaiCount / totalCount) * 100) : 0;
                let rekapStr = " Rekapitulasi keseluruhan capaian kinerja: Dari gabungan " + totalCount + " rencana kerja, sebanyak " + selesaiCount + " tugas telah selesai, " + prosesCount + " tugas sedang berproses, dan " + belumCount + " tugas belum dimulai. ";
                rekapStr += "Tingkat capaian kinerja Anda secara keseluruhan mencapai " + percent + " persen. ";

                if (percent >= 80) {
                    rekapStr += "Capaian kinerja Anda sangat bagus dan sangat memuaskan! Pertahankan prestasi kinerja luar biasa ini!";
                } else if (percent >= 50) {
                    rekapStr += "Capaian kinerja Anda sudah baik, mari tingkatkan penyelesaian tugas-tugas yang masih berproses!";
                } else {
                    rekapStr += "Capaian kinerja Anda perlu peningkatan dan percepatan agar seluruh target tugas dapat rampung tepat waktu!";
                }

                voiceText += rekapStr;
            } else {
                voiceText += "Belum ada rincian uraian kerja yang ditampilkan pada tabel.";
            }

            toggleSpeech(voiceText, 'btn-voice-rencanakerja', 'Suara');
        });

        function toggleSpeech(text, btnId, defaultText) {
            if (!('speechSynthesis' in window)) {
                alert('Peramban Anda belum mendukung fitur narasi suara.');
                return;
            }

            const btn = $('#' + btnId);

            if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
                window.speechSynthesis.cancel();
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1"></i> ' + defaultText);
                return;
            }

            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 0.95;

            const voices = window.speechSynthesis.getVoices();
            const idVoice = voices.find(v => v.lang.includes('id') || v.lang.includes('ID'));
            if (idVoice) utterance.voice = idVoice;

            utterance.onstart = function() {
                btn.removeClass('btn-warning').addClass('btn-danger').html('<i class="bi bi-stop-fill me-1"></i> Stop');
            };

            utterance.onend = function() {
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1"></i> ' + defaultText);
            };

            utterance.onerror = function() {
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1"></i> ' + defaultText);
            };

            window.speechSynthesis.speak(utterance);
        }

        // Prevent Bootstrap 5 Modal focus trap from blocking Select2 search field input
        $(document).on('focusin', function(e) {
            if ($(e.target).closest('.select2-container').length) {
                e.stopImmediatePropagation();
            }
        });

        if ($('#filter-jabatan').length) {
            $('#filter-jabatan').select2({
                theme: 'bootstrap-5',
                placeholder: "Cari & pilih jabatan - nama...",
                allowClear: true
            });
        }

        function initExportModalSelect2() {
            if ($('#export_periode_akademik_id').length) {
                $('#export_periode_akademik_id').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#modalExportExcel'),
                    placeholder: "Cari & pilih periode akademik...",
                    allowClear: true
                });
            }

            if ($('#export_jabatan').length) {
                $('#export_jabatan').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#modalExportExcel'),
                    placeholder: "Cari & pilih nama staff / jabatan...",
                    allowClear: true
                });
            }
        }

        initExportModalSelect2();

        $('#modalExportExcel').on('shown.bs.modal', function () {
            initExportModalSelect2();
        });

        var table = $('#rencanakerja-table').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            autoWidth: false,
            ajax: {
                url: "{{ app()->environment('production') ? str_replace('http:', 'https:', route('rencana-kerja.index')) : route('rencana-kerja.index') }}",
                data: function(d) {
                    if ($('#filter-jabatan').length) {
                        d.jabatan = $('#filter-jabatan').val();
                    }
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center align-top pt-3', width: '3%' },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-muted fw-semibold text-center align-top pt-3', width: '4%' },
                { data: 'task_details', name: 'uraian_tugas', searchable: true, className: 'align-middle' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end align-top pt-3 text-nowrap', width: '25%' },
            ],
            language: {
                search: "Cari Tugas:",
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Belum ada rencana kerja / checklist tugas untuk jabatan ini",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ tugas",
                infoEmpty: "Tidak ada data"
            }
        });

        table.on('xhr.dt', function(e, settings, json, xhr) {
            if (json && json.overall_rekap) {
                window.latestOverallRekap = json.overall_rekap;
            }
        });

        if ($('#filter-jabatan').length) {
            $('#filter-jabatan').on('change', function() {
                var selectedText = $(this).find('option:selected').text();
                var val = $(this).val();
                $('#judul-jabatan').text(val ? selectedText.trim() : 'Semua Jabatan');

                if ($('#export_jabatan').length) {
                    $('#export_jabatan').val(val).trigger('change');
                }

                table.ajax.reload();
            });
        }

        // Multi Select & Bulk Delete State Management
        let selectedIds = new Set();

        function updateBulkDeleteButton() {
            let count = selectedIds.size;
            $('#selected-count').text(count);
            if (count > 0) {
                $('#btn-bulk-delete').removeClass('d-none');
            } else {
                $('#btn-bulk-delete').addClass('d-none');
            }
        }

        // Toggle check-all
        $(document).on('change', '#check-all', function() {
            let isChecked = $(this).is(':checked');
            $('.select-row-checkbox').prop('checked', isChecked);
            $('.select-row-checkbox').each(function() {
                let val = parseInt($(this).val());
                if (isChecked) {
                    selectedIds.add(val);
                } else {
                    selectedIds.delete(val);
                }
            });
            updateBulkDeleteButton();
        });

        // Individual row checkbox change
        $(document).on('change', '.select-row-checkbox', function() {
            let val = parseInt($(this).val());
            if ($(this).is(':checked')) {
                selectedIds.add(val);
            } else {
                selectedIds.delete(val);
            }

            let totalOnPage = $('.select-row-checkbox').length;
            let checkedOnPage = $('.select-row-checkbox:checked').length;
            $('#check-all').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
            $('#check-all').prop('indeterminate', checkedOnPage > 0 && checkedOnPage < totalOnPage);

            updateBulkDeleteButton();
        });

        // Sync checkbox status on table redraw (pagination, search, reload)
        table.on('draw', function() {
            let totalOnPage = $('.select-row-checkbox').length;
            let checkedOnPage = 0;

            $('.select-row-checkbox').each(function() {
                let val = parseInt($(this).val());
                if (selectedIds.has(val)) {
                    $(this).prop('checked', true);
                    checkedOnPage++;
                } else {
                    $(this).prop('checked', false);
                }
            });

            $('#check-all').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
            $('#check-all').prop('indeterminate', checkedOnPage > 0 && checkedOnPage < totalOnPage);

            updateBulkDeleteButton();
        });

        // Handle Bulk Delete Click
        $(document).on('click', '#btn-bulk-delete', function() {
            let ids = Array.from(selectedIds);
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Hapus Rencana Kerja Terpilih?',
                text: 'Apakah Anda yakin ingin menghapus ' + ids.length + ' data rencana kerja terpilih ini secara permanen?',
                icon: 'warning',
                showCancelButton: true,
                confirmColor: '#d33',
                cancelColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus ' + ids.length + ' Data',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('rencana-kerja.bulk-delete') }}",
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "ids": ids
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: response.message,
                                    showConfirmButton: false,
                                    timer: 4000,
                                    timerProgressBar: true
                                });
                                selectedIds.clear();
                                updateBulkDeleteButton();
                                $('#check-all').prop('checked', false).prop('indeterminate', false);
                                $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat menghapus data terpilih.'
                            });
                        }
                    });
                }
            });
        });
    });

    $(document).on('click', '.btn-start-timer', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) window.startTimer(id);
    });

    $(document).on('click', '.btn-stop-timer', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) window.stopTimer(id);
    });

    $(document).on('click', '.btn-delete-timer', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) window.deleteRencanaKerja(id);
    });

    // Handle inline file and url_external upload form submit
    $(document).on('submit', '.form-inline-upload', function(e) {
        e.preventDefault();
        var form = $(this);
        var id = form.data('id');
        var btn = form.find('.btn-simpan-inline');
        var formData = new FormData(this);
        formData.append('_token', "{{ csrf_token() }}");

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({
            url: "{{ url('rencana-kerja') }}/" + id + "/upload-attachment",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan');
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                    $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan');
                var msg = 'Terjadi kesalahan saat menyimpan berkas/link.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: msg
                });
            }
        });
    });

    // Get current laptop time formatted as HH:mm:ss and date YYYY-MM-DD
    window.getLaptopDateTime = function() {
        var now = new Date();
        var year = now.getFullYear();
        var month = String(now.getMonth() + 1).padStart(2, '0');
        var day = String(now.getDate()).padStart(2, '0');
        var hours = String(now.getHours()).padStart(2, '0');
        var minutes = String(now.getMinutes()).padStart(2, '0');
        var seconds = String(now.getSeconds()).padStart(2, '0');

        return {
            date: year + '-' + month + '-' + day,
            time: hours + ':' + minutes + ':' + seconds
        };
    };

    window.startTimer = function(id) {
        var dt = window.getLaptopDateTime();
        
        Swal.fire({
            title: 'Konfirmasi Pelaksanaan Tugas',
            text: 'Waktu mulai pelaksanaan tugas akan dicatat secara otomatis sesuai waktu sistem Anda (' + dt.time + ')',
            icon: 'question',
            showCancelButton: true,
            confirmColor: '#15432d',
            cancelColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-play-fill"></i> Ya, Mulai Pelaksanaan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id + "/start",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "client_time": dt.time,
                        "client_date": dt.date
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                            $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat mencatat waktu mulai.'
                        });
                    }
                });
            }
        });
    };

    window.stopTimer = function(id) {
        var dt = window.getLaptopDateTime();

        Swal.fire({
            title: 'Konfirmasi Penyelesaian Tugas',
            text: 'Waktu penyelesaian tugas akan dicatat secara otomatis sesuai waktu sistem Anda (' + dt.time + ')',
            icon: 'question',
            showCancelButton: true,
            confirmColor: '#8b0000',
            cancelColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-stop-fill"></i> Ya, Selesaikan Tugas',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id + "/stop",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "client_time": dt.time,
                        "client_date": dt.date
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                            $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat mencatat waktu selesai.'
                        });
                    }
                });
            }
        });
    };

    window.deleteRencanaKerja = function(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data rencana kerja ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmColor: '#d33',
            cancelColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                            $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menghapus data.'
                        });
                    }
                });
            }
        });
    };
</script>
@if(isset($dataTable))
    @if(app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif
@endif
@endpush
