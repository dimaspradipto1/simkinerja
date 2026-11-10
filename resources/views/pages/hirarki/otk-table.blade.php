@extends('layouts.dashboard.template')

@section('page-style-class', 'menu-page-otk')
@section('content')

<style>
/* Table Width & Layout: Fullwidth Responsif & Font Kompak */
body.menu-page-otk .otk-table-wrap {
    overflow-x: auto !important;
    width: 100% !important;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
body.menu-page-otk .otk-table {
    width: 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
    table-layout: auto !important;
    font-size: 0.62rem !important;
    line-height: 1.2 !important;
    margin: 0 !important;
}

/* Penyesuaian Lebar Kolom Header & Cell agar Pas Fullwidth */
body.menu-page-otk .otk-table th.number-column,
body.menu-page-otk .otk-table th:nth-child(1),
body.menu-page-otk .otk-table td.unit-number {
    width: 28px !important;
    min-width: 28px !important;
    text-align: center !important;
    padding: 2px 2px !important;
}
body.menu-page-otk .otk-table th.unit-column,
body.menu-page-otk .otk-table th:nth-child(2),
body.menu-page-otk .otk-table td.unit-name {
    width: 13% !important;
    min-width: 105px !important;
    font-size: 0.62rem !important;
    padding: 2px 4px !important;
}
body.menu-page-otk .otk-table th:nth-child(3),
body.menu-page-otk .otk-table td[data-merge="section-responsibility"] {
    width: 9% !important;
    min-width: 75px !important;
    font-size: 0.61rem !important;
    padding: 2px 3px !important;
}
body.menu-page-otk .otk-table th:nth-child(4),
body.menu-page-otk .otk-table td[data-merge="coordinator"] {
    width: 11% !important;
    min-width: 85px !important;
    font-size: 0.61rem !important;
    padding: 2px 4px !important;
}
body.menu-page-otk .otk-table th:nth-child(5),
body.menu-page-otk .otk-table td[data-merge="implementer"] {
    width: 12% !important;
    min-width: 95px !important;
    font-size: 0.62rem !important;
    padding: 2px 4px !important;
}
body.menu-page-otk .otk-table th:nth-child(6),
body.menu-page-otk .otk-table td.otk-cell-jabatan {
    width: 24% !important;
    min-width: 135px !important;
    font-size: 0.62rem !important;
    padding: 2px 4px !important;
}
body.menu-page-otk .otk-table th:nth-child(7),
body.menu-page-otk .otk-table td.employee-name {
    width: 28% !important;
    min-width: 165px !important;
    font-size: 0.63rem !important;
    padding: 2px 4px !important;
}

body.menu-page-otk .otk-table thead th {
    background: #a9d18e !important;
    color: #111827 !important;
    text-align: center !important;
    font-size: 0.64rem !important;
    font-weight: 700 !important;
    padding: 4px 3px !important;
    border: 1px solid #64748b !important;
}
body.menu-page-otk .otk-table.table-bordered > :not(caption) > * > * {
    border: 1px solid #cbd5e1 !important;
    padding: 2px 4px !important;
    vertical-align: middle !important;
}

/* Kolom NAMA */
body.menu-page-otk .otk-table td.employee-name {
    padding: 2px 4px !important;
}
body.menu-page-otk .otk-table tr[hidden] {
    display: none !important;
}
body.menu-page-otk .otk-table .employee-name-text {
    font-size: 0.63rem !important;
    font-weight: 600 !important;
    color: #111827;
}

/* Drag Handle & Drag Reordering Styling */
.otk-drag-handle {
    cursor: grab;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 11px;
    padding: 0 1px;
    transition: color 0.15s ease;
    user-select: none;
}
.otk-drag-handle:hover,
.otk-drag-handle:active {
    color: #15432D;
    cursor: grabbing;
}
tr.otk-row[draggable="true"] {
    cursor: default;
}
tr.otk-row.otk-row-dragging {
    opacity: 0.45;
    background-color: #fef08a !important;
}
tr.otk-row.otk-drag-over {
    background-color: #dcfce7 !important;
    outline: 2px dashed #16a34a !important;
}
tr.otk-row.otk-drag-over-top {
    border-top: 2px solid #16a34a !important;
}
tr.otk-row.otk-drag-over-bottom {
    border-bottom: 2px solid #16a34a !important;
}

/* Editable Cell Hover & Editing */
.otk-editable-cell {
    cursor: pointer;
    transition: background-color 0.15s ease;
}
.otk-editable-cell:hover {
    background-color: #f0fdf4 !important;
    box-shadow: inset 0 0 0 1px #86efac;
}
.otk-inline-input {
    width: 100% !important;
    font-size: 0.62rem !important;
    padding: 1px 3px !important;
    height: auto !important;
    border-radius: 3px !important;
    border: 1px solid #16a34a !important;
    background-color: #ffffff !important;
    box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.25) !important;
}

/* Tombol Aksi Row OTK (Move, Insert, Edit, Delete) */
.otk-row-actions {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    flex-shrink: 0;
    margin-left: 10px;
    opacity: 0.9;
    transition: opacity 0.2s ease;
}
.otk-row:hover .otk-row-actions {
    opacity: 1;
}
.otk-row-actions button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 19px;
    height: 19px;
    padding: 0;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    background: #ffffff;
    color: #4b5563;
    font-size: 10px;
    transition: all 0.15s ease;
    cursor: pointer;
    line-height: 1;
}
.otk-row-actions button:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
    color: #111827;
}
.otk-row-actions .btn-otk-move-up:hover,
.otk-row-actions .btn-otk-move-down:hover {
    background: #e0f2fe;
    border-color: #38bdf8;
    color: #0284c7;
}
.otk-row-actions .btn-otk-insert:hover {
    background: #dcfce7;
    border-color: #4ade80;
    color: #15803d;
}
.otk-row-actions .edit-supervisor:hover {
    background: #fef3c7;
    border-color: #fcd34d;
    color: #b45309;
}
.otk-row-actions .btn-otk-delete:hover {
    background: #fee2e2;
    border-color: #f87171;
    color: #b91c1c;
}

@media print {
    body.menu-page-otk .otk-table {
        min-width: 0 !important;
        width: 100% !important;
        table-layout: fixed !important;
    }
    body.menu-page-otk .otk-table th:nth-child(6) {
        width: 25% !important;
    }
    body.menu-page-otk .otk-table th:nth-child(7) {
        width: 24% !important;
    }
    body.menu-page-otk .otk-table td.employee-name {
        white-space: normal !important;
    }
    .otk-row-actions {
        display: none !important;
    }
}
</style>

<section class="section">
    <div class="otk-heading">
        <button type="button" class="btn btn-sm btn-outline-secondary screen-only edit-otk-title" id="editOtkTitlesButton" title="Edit judul OTK" aria-label="Edit judul OTK">
            <i class="bi bi-pencil-square"></i>
        </button>
        <h1 id="otkTitleLine1">{{ $otkTitles->title_line_1 }}</h1>
        <h2 id="otkTitleLine2">{{ $otkTitles->title_line_2 }}</h2>
    </div>

    <div class="otk-tools">
        <div class="otk-search screen-only">
            <i class="bi bi-search"></i>
            <input type="search" id="otkSearch" class="form-control" placeholder="Cari unit, jabatan, nama, atau atasan...">
        </div>
        <div class="otk-actions">
            <button type="button" class="btn btn-outline-primary screen-only" id="addOtkUserButton">
                <i class="bi bi-person-plus me-1"></i>Tambahkan Pengguna OTK
            </button>
            <input type="file" id="otkPdfInput" accept="application/pdf,.pdf" class="d-none">
            <button type="button" class="btn btn-primary screen-only" id="uploadOtkButton">
                <i class="bi bi-file-earmark-arrow-up me-1"></i>Upload SK OTK &amp; Sinkronkan
            </button>
            <button type="button" class="btn btn-success screen-only" id="syncOtkButton">
                <i class="bi bi-arrow-repeat me-1"></i>Sinkronkan Relasi Standar
            </button>
            <button type="button" class="btn btn-outline-danger screen-only" id="resetOtkButton">
                <i class="bi bi-trash3 me-1"></i>Reset OTK
            </button>
            <button type="button" class="btn btn-outline-secondary screen-only" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Cetak
            </button>
        </div>
    </div>

    <div class="table-responsive otk-table-wrap">
        <table class="table table-bordered align-middle otk-table" id="otkTable">
            <thead>
                <tr>
                    <th class="number-column">NO.</th>
                    <th class="unit-column">UNIT</th>
                    <th>TANGGUNG JAWAB REKTOR</th>
                    <th>KOORDINASI MELALUI WAKIL REKTOR</th>
                    <th>UNSUR PELAKSANA</th>
                    <th>JABATAN UIS</th>
                    <th>NAMA</th>
                </tr>
            </thead>
            @forelse($organizationSections as $sectionIndex => $section)
                @php
                    $sectionRowCount = collect($section['coordinators'])->sum(
                        fn ($coordinator) => collect($coordinator['implementers'])->sum(
                            fn ($implementer) => $implementer['employees']->count()
                        )
                    );
                    $sectionFirstRow = true;
                @endphp
                <tbody class="otk-section">
                    @foreach($section['coordinators'] as $coordinatorIndex => $coordinator)
                        @php
                            $coordinatorRowCount = collect($coordinator['implementers'])
                                ->sum(fn ($implementer) => $implementer['employees']->count());
                            $coordinatorFirstRow = true;
                        @endphp
                        @foreach($coordinator['implementers'] as $implementerIndex => $implementer)
                            @php $implementerRowCount = $implementer['employees']->count(); @endphp
                            @foreach($implementer['employees'] as $employee)
                                <tr
                                    class="otk-row"
                                    draggable="true"
                                    data-user-id="{{ $employee->id }}"
                                    data-section-key="{{ $sectionIndex }}"
                                    data-coordinator-key="{{ $sectionIndex }}-{{ $coordinatorIndex }}"
                                    data-implementer-key="{{ $sectionIndex }}-{{ $coordinatorIndex }}-{{ $implementerIndex }}"
                                    data-search="{{ strtolower($section['unit'].' '.$employee->jabatan.' '.$employee->name.' '.$coordinator['label'].' '.$implementer['label']) }}"
                                >
                                    @if($sectionFirstRow)
                                        <td rowspan="{{ $sectionRowCount }}" class="unit-number" data-merge="section-number" data-merge-key="{{ $sectionIndex }}">{{ $section['number'] }}</td>
                                        <td rowspan="{{ $sectionRowCount }}" class="unit-name otk-editable-cell" data-field="unit" data-merge="section-unit" data-merge-key="{{ $sectionIndex }}" title="Klik ganda untuk edit unit">{{ $section['unit'] }}</td>
                                        <td rowspan="{{ $sectionRowCount }}" class="merged-label otk-editable-cell" data-field="responsibility" data-merge="section-responsibility" data-merge-key="{{ $sectionIndex }}" title="Klik ganda untuk edit tanggung jawab">{{ $section['responsibility'] }}</td>
                                        @php $sectionFirstRow = false; @endphp
                                    @endif
                                    @if($coordinatorFirstRow)
                                        <td rowspan="{{ $coordinatorRowCount }}" class="merged-label otk-editable-cell" data-field="coordinator" data-merge="coordinator" data-merge-key="{{ $sectionIndex }}-{{ $coordinatorIndex }}" title="Klik ganda untuk edit koordinasi">{{ $coordinator['label'] }}</td>
                                        @php $coordinatorFirstRow = false; @endphp
                                    @endif
                                    @if($loop->first)
                                        <td rowspan="{{ $implementerRowCount }}" class="merged-label otk-editable-cell" data-field="implementer" data-merge="implementer" data-merge-key="{{ $sectionIndex }}-{{ $coordinatorIndex }}-{{ $implementerIndex }}" title="Klik ganda untuk edit unsur pelaksana">{{ $implementer['label'] }}</td>
                                    @endif
                                    <td class="otk-cell-jabatan otk-editable-cell" data-field="jabatan" title="Klik ganda untuk edit jabatan langsung">{{ $employee->jabatan }}</td>
                                    <td class="employee-name">
                                        <div class="d-flex align-items-center justify-content-between gap-1 w-100">
                                            <div class="d-flex align-items-center gap-1 text-truncate">
                                                <span class="otk-drag-handle screen-only" title="Tahan dan geser (drag) ke atas/bawah untuk memindahkan baris"><i class="bi bi-grip-vertical"></i></span>
                                                <span class="employee-name-text otk-editable-cell" data-field="name" title="Klik ganda untuk edit nama langsung">{{ $employee->name }}</span>
                                            </div>
                                            <div class="otk-row-actions screen-only">
                                                <button
                                                    type="button"
                                                    class="btn-otk-move btn-otk-move-up"
                                                    data-user-id="{{ $employee->id }}"
                                                    data-direction="up"
                                                    title="Pindahkan Baris ke Atas"
                                                    aria-label="Pindahkan ke Atas"
                                                ><i class="bi bi-arrow-up"></i></button>
                                                <button
                                                    type="button"
                                                    class="btn-otk-move btn-otk-move-down"
                                                    data-user-id="{{ $employee->id }}"
                                                    data-direction="down"
                                                    title="Pindahkan Baris ke Bawah"
                                                    aria-label="Pindahkan ke Bawah"
                                                ><i class="bi bi-arrow-down"></i></button>
                                                <button
                                                    type="button"
                                                    class="btn-otk-insert"
                                                    data-user-id="{{ $employee->id }}"
                                                    data-user-name="{{ $employee->name }}"
                                                    data-user-position="{{ $employee->jabatan }}"
                                                    data-user-unit="{{ $employee->unit }}"
                                                    data-supervisor-id="{{ $employee->supervisor_id }}"
                                                    title="Sisipkan Baris Baru di Sini"
                                                    aria-label="Sisipkan Baris"
                                                ><i class="bi bi-plus-lg"></i></button>
                                                <button
                                                    type="button"
                                                    class="edit-supervisor"
                                                    data-user-id="{{ $employee->id }}"
                                                    data-user-name="{{ $employee->name }}"
                                                    data-user-position="{{ $employee->jabatan }}"
                                                    data-user-unit="{{ $employee->unit }}"
                                                    data-supervisor-id="{{ $employee->supervisor_id }}"
                                                    title="Edit Data OTK"
                                                    aria-label="Edit data OTK {{ $employee->name }}"
                                                ><i class="bi bi-pencil-square"></i></button>
                                                <button
                                                    type="button"
                                                    class="btn-otk-delete text-danger"
                                                    data-user-id="{{ $employee->id }}"
                                                    data-user-name="{{ $employee->name }}"
                                                    title="Hapus Baris dari OTK"
                                                    aria-label="Hapus Baris"
                                                ><i class="bi bi-trash3"></i></button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach
                </tbody>
            @empty
                <tbody>
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data struktur organisasi.</td></tr>
                </tbody>
            @endforelse
        </table>
    </div>

    <div class="otk-footnote screen-only">
        <span><i class="bi bi-people-fill me-1"></i>{{ $allUsers->count() }} akun aktif</span>
        <span><i class="bi bi-person-check-fill me-1"></i>{{ $allUsers->whereNotNull('supervisor_id')->count() }} memiliki atasan langsung</span>
        <span><i class="bi bi-exclamation-circle-fill me-1"></i>{{ $unassigned->count() }} belum diatur</span>
    </div>

    <div class="otk-signatures">
        <div class="signature-date">BATAM, 22 AGUSTUS 2024</div>
        <div class="signature-grid">
            <div>
                <div>KETUA YAYASAN PENDIDIKAN IBNU SINA BATAM</div>
                <div class="signature-space"></div>
                <strong>(drg. ANDI TENRI UMMU, M.M)</strong>
            </div>
            <div>
                <div>REKTOR UNIVERSITAS IBNU SINA</div>
                <div class="signature-space"></div>
                <strong>(Dr. Ir. LARISANG, M.T., IPU)</strong>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="supervisorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Data OTK</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3" id="supervisorModalDescription"></p>
                <label for="otkUserName" class="form-label fw-semibold">Nama Pengguna</label>
                <input type="text" id="otkUserName" class="form-control mb-3" maxlength="255" required>
                <label for="otkUserPosition" class="form-label fw-semibold">Jabatan OTK</label>
                <input type="text" id="otkUserPosition" class="form-control mb-3" maxlength="255" required>
                <label for="otkUserUnit" class="form-label fw-semibold">Unit</label>
                <select id="otkUserUnit" class="form-select mb-3" required>
                    @foreach($allUsers->pluck('unit')->filter()->unique()->sort() as $unit)
                        <option value="{{ $unit }}">{{ $unit }}</option>
                    @endforeach
                </select>
                <label for="supervisorSelect" class="form-label fw-semibold">Atasan Langsung</label>
                <select id="supervisorSelect" class="form-select" style="width: 100%;">
                    <option value="">— Tidak Ada Atasan —</option>
                    @foreach($pimpinan as $leader)
                        <option value="{{ $leader->id }}">{{ $leader->name }} — {{ $leader->jabatan }} ({{ $leader->unit }})</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="saveSupervisorButton">
                    <i class="bi bi-check-circle me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addOtkUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Tambahkan Pengguna ke OTK</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Pilih akun aktif yang belum masuk OTK. Jabatan, unit, dan atasan akan diperbarui pada akun Pengguna.</p>
                <label for="addOtkUserSelect" class="form-label fw-semibold">Pengguna</label>
                <select id="addOtkUserSelect" class="form-select mb-3" style="width: 100%;">
                    <option value="">Pilih pengguna...</option>
                    @foreach($availableUsers as $user)
                        <option
                            value="{{ $user->id }}"
                            data-position="{{ $user->jabatan }}"
                            data-unit="{{ $user->unit }}"
                        >{{ $user->name }} — {{ $user->jabatan ?: 'Tanpa jabatan' }} ({{ $user->unit ?: 'Tanpa unit' }})</option>
                    @endforeach
                </select>
                <label for="addOtkPosition" class="form-label fw-semibold">Jabatan OTK <span class="text-muted fw-normal">(Opsional)</span></label>
                <input type="text" id="addOtkPosition" class="form-control mb-3" maxlength="255" placeholder="Boleh dikosongkan">
                <label for="addOtkUnit" class="form-label fw-semibold">Unit <span class="text-muted fw-normal">(Opsional)</span></label>
                <input type="text" id="addOtkUnit" class="form-control mb-3" maxlength="255" placeholder="Boleh dikosongkan">
                <label for="addOtkSupervisor" class="form-label fw-semibold">Atasan Langsung <span class="text-muted fw-normal">(Opsional)</span></label>
                <select id="addOtkSupervisor" class="form-select" style="width: 100%;">
                    <option value="">Pilih atasan...</option>
                    @foreach($pimpinan as $leader)
                        <option value="{{ $leader->id }}">{{ $leader->name }} — {{ $leader->jabatan }} ({{ $leader->unit }})</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="saveAddedOtkUserButton">
                    <i class="bi bi-check-circle me-1"></i>Tambahkan
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Modal Sisipkan Baris OTK -->
<div class="modal fade" id="insertOtkRowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #15432D 0%, #1e5c3e 100%);">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-plus-circle me-2"></i>Sisipkan Baris Baru di OTK</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Info Acuan -->
                <div class="alert alert-light border mb-3 py-2 px-3 small">
                    <span class="text-muted">Baris Acuan:</span> <strong id="insertRefUserName" class="text-dark">-</strong>
                    <input type="hidden" id="insertRefUserId">
                </div>

                <!-- Posisi Sisip -->
                <label class="form-label fw-bold small text-secondary mb-1">Posisi Sisipan Baris</label>
                <div class="d-flex gap-3 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="insertPosition" id="insertPosAfter" value="after" checked>
                        <label class="form-check-label small" for="insertPosAfter">
                            <i class="bi bi-arrow-down-short text-success fw-bold"></i> Tepat di <strong>Bawah</strong> Baris Ini
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="insertPosition" id="insertPosBefore" value="before">
                        <label class="form-check-label small" for="insertPosBefore">
                            <i class="bi bi-arrow-up-short text-primary fw-bold"></i> Tepat di <strong>Atas</strong> Baris Ini
                        </label>
                    </div>
                </div>

                <!-- Mode Pengguna -->
                <label class="form-label fw-bold small text-secondary mb-1">Pilihan Pengguna</label>
                <div class="d-flex gap-3 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="insertUserMode" id="insertModeExisting" value="existing" checked>
                        <label class="form-check-label small" for="insertModeExisting">Pilih Akun yang Sudah Ada</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="insertUserMode" id="insertModeNew" value="new">
                        <label class="form-check-label small" for="insertModeNew">Buat Pengguna Baru</label>
                    </div>
                </div>

                <!-- Dropdown Akun Existing -->
                <div id="insertExistingUserSection" class="mb-3">
                    <label for="insertExistingUserSelect" class="form-label small fw-semibold">Pilih Pengguna</label>
                    <select id="insertExistingUserSelect" class="form-select form-select-sm" style="width: 100%;">
                        <option value="">— Kosongkan / Lewati (Bypass) —</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}" data-position="{{ $user->jabatan }}" data-unit="{{ $user->unit }}">
                                {{ $user->name }} — {{ $user->jabatan ?: 'Tanpa Jabatan' }} ({{ $user->unit ?: 'Tanpa Unit' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Form Buat User Baru -->
                <div id="insertNewUserSection" class="mb-3 d-none">
                    <div class="mb-2">
                        <label for="insertNewName" class="form-label small fw-semibold">Nama Lengkap &amp; Gelar</label>
                        <input type="text" id="insertNewName" class="form-control form-control-sm" placeholder="Contoh: Nama Pegawai, S.Kom., M.Kom">
                    </div>
                    <div class="mb-2">
                        <label for="insertNewEmail" class="form-label small fw-semibold">Alamat Email</label>
                        <input type="email" id="insertNewEmail" class="form-control form-control-sm" placeholder="pegawai@uis.ac.id">
                        <small class="text-muted" style="font-size: 0.70rem;">Password default: <code>password123</code></small>
                    </div>
                </div>

                <!-- Jabatan UIS -->
                <div class="mb-3">
                    <label for="insertJabatan" class="form-label small fw-semibold">Jabatan UIS (Sesuai OTK) <span class="text-muted fw-normal">(Opsional)</span></label>
                    <input type="text" id="insertJabatan" class="form-control form-control-sm" placeholder="Contoh: STAFF ADMINISTRASI AKADEMIK (Boleh dikosongkan)">
                </div>

                <!-- Unit -->
                <div class="mb-3">
                    <label for="insertUnit" class="form-label small fw-semibold">Unit Kerja <span class="text-muted fw-normal">(Opsional)</span></label>
                    <input type="text" id="insertUnit" class="form-control form-control-sm" placeholder="Contoh: BAAK / FST / BAUK (Boleh dikosongkan)">
                </div>

                <!-- Atasan Langsung -->
                <div class="mb-2">
                    <label for="insertSupervisorSelect" class="form-label small fw-semibold">Atasan Langsung (Hierarki OTK)</label>
                    <select id="insertSupervisorSelect" class="form-select form-select-sm" style="width: 100%;">
                        <option value="">— Tidak Ada Atasan (Tingkat Pimpinan) —</option>
                        @foreach($pimpinan as $leader)
                            <option value="{{ $leader->id }}">{{ $leader->name }} — {{ $leader->jabatan }} ({{ $leader->unit }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-success fw-bold px-3" id="saveInsertOtkRowBtn" style="background-color: #15432D; border-color: #15432D;">
                    <i class="bi bi-plus-circle me-1"></i>Sisipkan Baris
                </button>
            </div>
        </div>
    </div>
</div>

@endsection



@push('scripts')
<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const supervisorModal = new bootstrap.Modal(document.getElementById('supervisorModal'));
    const supervisorSelect = document.getElementById('supervisorSelect');
    const $supervisorSelect = window.jQuery ? jQuery(supervisorSelect) : null;
    const userNameInput = document.getElementById('otkUserName');
    const userPositionInput = document.getElementById('otkUserPosition');
    const userUnitSelect = document.getElementById('otkUserUnit');
    const description = document.getElementById('supervisorModalDescription');
    const saveButton = document.getElementById('saveSupervisorButton');
    const editOtkTitlesButton = document.getElementById('editOtkTitlesButton');
    const otkTitleLine1 = document.getElementById('otkTitleLine1');
    const otkTitleLine2 = document.getElementById('otkTitleLine2');
    const uploadButton = document.getElementById('uploadOtkButton');
    const uploadInput = document.getElementById('otkPdfInput');
    const addOtkUserModalElement = document.getElementById('addOtkUserModal');
    const addOtkUserModal = new bootstrap.Modal(addOtkUserModalElement);
    const addOtkUserSelect = document.getElementById('addOtkUserSelect');
    const addOtkPosition = document.getElementById('addOtkPosition');
    const addOtkUnit = document.getElementById('addOtkUnit');
    const addOtkSupervisor = document.getElementById('addOtkSupervisor');
    const saveAddedOtkUserButton = document.getElementById('saveAddedOtkUserButton');
    let activeUserId = null;
    const mergedCells = new Map();

    if (window.jQuery && jQuery.fn.select2) {
        if ($supervisorSelect) {
            $supervisorSelect.select2({
                dropdownParent: jQuery('#supervisorModal'),
                placeholder: 'Cari nama, jabatan, atau unit...',
                theme: 'bootstrap-5',
                width: '100%',
            });
        }
        jQuery(addOtkUserSelect).select2({
            dropdownParent: jQuery(addOtkUserModalElement),
            placeholder: 'Cari nama pengguna...',
            theme: 'bootstrap-5',
            width: '100%',
        });
        jQuery(addOtkSupervisor).select2({
            dropdownParent: jQuery(addOtkUserModalElement),
            placeholder: 'Cari atasan...',
            theme: 'bootstrap-5',
            width: '100%',
        });
        jQuery('#insertSupervisorSelect').select2({
            dropdownParent: jQuery('#insertOtkRowModal'),
            placeholder: 'Cari nama atau jabatan atasan...',
            theme: 'bootstrap-5',
            width: '100%',
        });
        jQuery('#insertExistingUserSelect').select2({
            dropdownParent: jQuery('#insertOtkRowModal'),
            placeholder: 'Cari nama atau jabatan pengguna...',
            theme: 'bootstrap-5',
            width: '100%',
        });

        jQuery('#insertOtkRowModal').on('shown.bs.modal', function () {
            jQuery('#insertSupervisorSelect, #insertExistingUserSelect').select2({
                dropdownParent: jQuery('#insertOtkRowModal'),
                theme: 'bootstrap-5',
                width: '100%',
            });
        });

        // Autofocus kotak pencarian Select2 saat dibuka
        jQuery(document).on('select2:open', function () {
            const searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) {
                searchField.focus();
            }
        });
    }

    document.querySelectorAll('[data-merge]').forEach((cell) => {
        mergedCells.set(`${cell.dataset.merge}:${cell.dataset.mergeKey}`, cell);
    });

    function restoreMergedCells(tbody, visibleRows) {
        // 1. Lepaskan seluruh sel merged dari baris pada tbody ini
        tbody.querySelectorAll('[data-merge]').forEach((cell) => cell.remove());

        // Jika tidak ada baris yang cocok pada section ini, sembunyikan section
        if (!visibleRows || visibleRows.length === 0) {
            tbody.style.display = 'none';
            return;
        }
        tbody.style.display = '';

        const mergeLevels = [
            ['section-number', 'sectionKey'],
            ['section-unit', 'sectionKey'],
            ['section-responsibility', 'sectionKey'],
            ['coordinator', 'coordinatorKey'],
            ['implementer', 'implementerKey'],
        ];

        // Petakan untuk setiap firstRow sel-sel apa saja yang harus disisipkan (berurutan dari level 0 s.d 4)
        const rowCellsToInsert = new Map();

        mergeLevels.forEach(([level, keyName], levelIndex) => {
            const groups = new Map();
            visibleRows.forEach((row) => {
                const key = row.dataset[keyName];
                if (!groups.has(key)) groups.set(key, []);
                groups.get(key).push(row);
            });

            groups.forEach((groupRows, key) => {
                const cell = mergedCells.get(`${level}:${key}`);
                if (!cell) return;

                cell.rowSpan = groupRows.length;
                const firstRow = groupRows[0];
                if (!rowCellsToInsert.has(firstRow)) {
                    rowCellsToInsert.set(firstRow, []);
                }
                rowCellsToInsert.get(firstRow).push({ cell, levelIndex });
            });
        });

        // Sisipkan sel-sel secara berurutan TEPAT SEBELUM kolom Jabatan UIS (tidak akan pernah geser ke belakang Nama)
        rowCellsToInsert.forEach((cellList, row) => {
            cellList.sort((a, b) => a.levelIndex - b.levelIndex);
            const jabatanCell = row.querySelector('.otk-cell-jabatan') || row.children[row.children.length - 2];
            cellList.forEach(({ cell }) => {
                row.insertBefore(cell, jabatanCell);
            });
        });
    }

    function showAlert(message, icon = 'success') {
        return Swal.fire({
            title: icon === 'success' ? 'Berhasil' : 'Gagal',
            text: message,
            icon,
            confirmButtonText: 'OK',
        });
    }

    function confirmAction(message, title = 'Konfirmasi') {
        return Swal.fire({
            title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => result.isConfirmed);
    }

    editOtkTitlesButton.addEventListener('click', async () => {
        const result = await Swal.fire({
            title: 'Edit Judul OTK',
            html: '<div class="text-start">' +
                '<label class="form-label fw-semibold" for="otkTitleInput1">Judul baris pertama</label>' +
                '<textarea id="otkTitleInput1" class="form-control mb-3" rows="3" maxlength="255" style="resize:vertical"></textarea>' +
                '<label class="form-label fw-semibold" for="otkTitleInput2">Judul baris kedua</label>' +
                '<textarea id="otkTitleInput2" class="form-control" rows="3" maxlength="255" style="resize:vertical"></textarea>' +
                '</div>',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            didOpen: () => {
                const titleInput1 = document.getElementById('otkTitleInput1');
                const titleInput2 = document.getElementById('otkTitleInput2');
                titleInput1.value = otkTitleLine1.textContent.trim();
                titleInput2.value = otkTitleLine2.textContent.trim();
                titleInput1.focus();
            },
            preConfirm: () => {
                const titleLine1 = document.getElementById('otkTitleInput1').value.trim();
                const titleLine2 = document.getElementById('otkTitleInput2').value.trim();
                if (!titleLine1 || !titleLine2) {
                    Swal.showValidationMessage('Kedua baris judul wajib diisi.');
                    return false;
                }

                return { title_line_1: titleLine1, title_line_2: titleLine2 };
            },
        });
        if (!result.isConfirmed) return;

        editOtkTitlesButton.disabled = true;
        try {
            const response = await fetch(@json(route('hirarki.update-titles')), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(result.value),
            });
            const responseData = await response.json();
            if (!response.ok || !responseData.success) {
                throw new Error(responseData.message || 'Gagal memperbarui judul OTK.');
            }

            otkTitleLine1.textContent = result.value.title_line_1;
            otkTitleLine2.textContent = result.value.title_line_2;
            await showAlert(responseData.message);
        } catch (error) {
            await showAlert(error.message || 'Gagal terhubung ke server.', 'error');
        } finally {
            editOtkTitlesButton.disabled = false;
        }
    });

    document.getElementById('otkSearch').addEventListener('input', (event) => {
        const query = event.target.value.trim().toLowerCase();
        let totalVisible = 0;

        document.querySelectorAll('.otk-section').forEach((tbody) => {
            const rows = Array.from(tbody.querySelectorAll('.otk-row'));
            const matchingRows = [];

            rows.forEach((row) => {
                const searchData = (row.dataset.search || '').toLowerCase();
                const isMatch = (query === '' || searchData.includes(query));
                if (isMatch) {
                    row.style.display = '';
                    row.removeAttribute('hidden');
                    matchingRows.push(row);
                } else {
                    row.style.display = 'none';
                    row.setAttribute('hidden', '');
                }
            });

            totalVisible += matchingRows.length;
            restoreMergedCells(tbody, matchingRows);
        });

        let noResultTbody = document.getElementById('otkNoSearchResultRow');
        if (totalVisible === 0 && query !== '') {
            if (!noResultTbody) {
                const table = document.getElementById('otkTable');
                noResultTbody = document.createElement('tbody');
                noResultTbody.id = 'otkNoSearchResultRow';
                noResultTbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted fw-semibold"><i class="bi bi-search me-2"></i>Tidak ada data OTK yang sesuai dengan kata kunci "${query}".</td></tr>`;
                table.appendChild(noResultTbody);
            } else {
                noResultTbody.style.display = '';
                noResultTbody.querySelector('td').innerHTML = `<i class="bi bi-search me-2"></i>Tidak ada data OTK yang sesuai dengan kata kunci "${query}".`;
            }
        } else if (noResultTbody) {
            noResultTbody.style.display = 'none';
        }
    });

    document.getElementById('otkTable').addEventListener('click', (e) => {
        const button = e.target.closest('.edit-supervisor');
        if (!button) return;
        activeUserId = button.dataset.userId;
        description.textContent = `Perubahan nama, jabatan, unit, dan atasan akan memperbarui akun Pengguna ${button.dataset.userName}.`;
        userNameInput.value = button.dataset.userName;
        userPositionInput.value = button.dataset.userPosition;
        userUnitSelect.value = button.dataset.userUnit;
        supervisorSelect.value = button.dataset.supervisorId || '';
        if ($supervisorSelect && jQuery.fn.select2) {
            $supervisorSelect.trigger('change');
        }
        supervisorModal.show();
    });

    saveButton.addEventListener('click', async () => {
        if (!activeUserId) return;

        saveButton.disabled = true;
        try {
            const response = await fetch(`/hirarki/${activeUserId}/update`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    name: userNameInput.value.trim(),
                    jabatan: userPositionInput.value.trim(),
                    unit: userUnitSelect.value,
                    supervisor_id: supervisorSelect.value || null,
                }),
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal memperbarui data OTK.');
            }

            supervisorModal.hide();
            await showAlert(result.message);
            window.location.reload();
        } catch (error) {
            await showAlert(error.message || 'Gagal terhubung ke server.', 'error');
        } finally {
            saveButton.disabled = false;
        }
    });

    document.getElementById('addOtkUserButton').addEventListener('click', () => addOtkUserModal.show());
    addOtkUserSelect.addEventListener('change', () => {
        const selectedOption = addOtkUserSelect.selectedOptions[0];
        addOtkPosition.value = selectedOption?.dataset.position || '';
        addOtkUnit.value = selectedOption?.dataset.unit || '';
    });
    saveAddedOtkUserButton.addEventListener('click', async () => {
        const payload = {
            user_id: addOtkUserSelect.value,
            jabatan: addOtkPosition.value.trim() || '-',
            unit: addOtkUnit.value.trim() || '-',
            supervisor_id: addOtkSupervisor.value || null,
        };
        if (!payload.user_id) {
            await showAlert('Pilih pengguna aktif terlebih dahulu.', 'warning');
            return;
        }
        if (!await confirmAction('Pengguna akan dimasukkan ke OTK dan jabatan, unit, serta atasannya diperbarui pada menu Pengguna.', 'Tambahkan pengguna ke OTK?')) return;

        saveAddedOtkUserButton.disabled = true;
        try {
            const response = await fetch(@json(route('hirarki.add-user')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal menambahkan pengguna ke OTK.');
            }

            addOtkUserModal.hide();
            await showAlert(result.message);
            window.location.reload();
        } catch (error) {
            await showAlert(error.message || 'Gagal terhubung ke server.', 'error');
        } finally {
            saveAddedOtkUserButton.disabled = false;
        }
    });

    uploadButton.addEventListener('click', () => uploadInput.click());
    uploadInput.addEventListener('change', async () => {
        const file = uploadInput.files[0];
        if (!file) return;

        if (!await confirmAction('Sinkronkan anggota, jabatan, unit, dan relasi OTK dari PDF ini? Semua nama harus cocok dengan akun aktif yang sudah ada.', 'Upload dan sinkronkan SK OTK?')) {
            uploadInput.value = '';
            return;
        }

        uploadButton.disabled = true;
        const formData = new FormData();
        formData.append('file_sk', file);

        try {
            const response = await fetch(@json(route('hirarki.upload-otk')), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal menyinkronkan SK OTK.');
            }

            await showAlert(result.message);
            window.location.reload();
        } catch (error) {
            await showAlert(error.message || 'Gagal terhubung ke server.', 'error');
        } finally {
            uploadButton.disabled = false;
            uploadInput.value = '';
        }
    });

    document.getElementById('syncOtkButton').addEventListener('click', async () => {
        if (!await confirmAction('Terapkan struktur organisasi dan tata kerja (OTK) UIS 2024-2029 sesuai bagan?', 'Sinkronkan relasi OTK?')) return;

        try {
            const response = await fetch(@json(route('hirarki.sync-otk')), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal menerapkan OTK.');
            }

            await showAlert(result.message);
            window.location.reload();
        } catch (error) {
            await showAlert(error.message || 'Gagal terhubung ke server.', 'error');
        }
    });

    document.getElementById('resetOtkButton').addEventListener('click', async () => {
        if (!await confirmAction('Reset semua hubungan atasan-bawahan? Tindakan ini menghapus seluruh pengaturan OTK.', 'Reset OTK?')) return;

        try {
            const response = await fetch(@json(route('hirarki.reset-all')), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal mereset OTK.');
            }

            await showAlert(result.message);
            window.location.reload();
        } catch (error) {
            await showAlert(error.message || 'Gagal terhubung ke server.', 'error');
        }
    });

    // === FUNGSI TUKAR POSISI BARIS SECARA INSTAN & PERSISTEN ===
    async function swapRowData(sourceRow, targetRow, direction) {
        if (!sourceRow || !targetRow || sourceRow === targetRow) return false;

        const sourceUserId = sourceRow.dataset.userId || sourceRow.querySelector('.btn-otk-move')?.dataset.userId;
        const targetUserId = targetRow.dataset.userId || targetRow.querySelector('.btn-otk-move')?.dataset.userId;
        if (!sourceUserId || !targetUserId) return false;

        // Tukar sel Jabatan UIS dan Nama UIS di DOM secara instan tanpa refresh
        const sourceJabatanCell = sourceRow.children[sourceRow.children.length - 2];
        const sourceNameCell = sourceRow.children[sourceRow.children.length - 1];
        const targetJabatanCell = targetRow.children[targetRow.children.length - 2];
        const targetNameCell = targetRow.children[targetRow.children.length - 1];

        const tempJabatan = sourceJabatanCell.innerHTML;
        const tempName = sourceNameCell.innerHTML;
        const tempUserId = sourceRow.dataset.userId;
        const tempSearch = sourceRow.dataset.search;

        sourceJabatanCell.innerHTML = targetJabatanCell.innerHTML;
        sourceNameCell.innerHTML = targetNameCell.innerHTML;
        sourceRow.dataset.userId = targetRow.dataset.userId;
        sourceRow.dataset.search = targetRow.dataset.search;

        targetJabatanCell.innerHTML = tempJabatan;
        targetNameCell.innerHTML = tempName;
        targetRow.dataset.userId = tempUserId;
        targetRow.dataset.search = tempSearch;

        // Visual feedback highlight
        targetRow.style.transition = 'background-color 0.3s ease';
        targetRow.style.backgroundColor = '#dcfce7';
        setTimeout(() => {
            targetRow.style.backgroundColor = '';
        }, 600);

        try {
            const response = await fetch(@json(route('hirarki.move-row')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    user_id: sourceUserId,
                    swap_user_id: targetUserId,
                    direction: direction || 'down'
                }),
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal memindahkan baris di server.');
            }
            return true;
        } catch (error) {
            // Revert jika gagal
            const revertJabatan = sourceJabatanCell.innerHTML;
            const revertName = sourceNameCell.innerHTML;
            const revertUserId = sourceRow.dataset.userId;
            const revertSearch = sourceRow.dataset.search;

            sourceJabatanCell.innerHTML = targetJabatanCell.innerHTML;
            sourceNameCell.innerHTML = targetNameCell.innerHTML;
            sourceRow.dataset.userId = targetRow.dataset.userId;
            sourceRow.dataset.search = targetRow.dataset.search;

            targetJabatanCell.innerHTML = revertJabatan;
            targetNameCell.innerHTML = revertName;
            targetRow.dataset.userId = revertUserId;
            targetRow.dataset.search = revertSearch;

            await showAlert(error.message || 'Terjadi kesalahan saat memindahkan baris.', 'error');
            return false;
        }
    }

    // === FITUR PINDAHKAN BARIS DENGAN TOMBOL NAIK / TURUN ===
    document.getElementById('otkTable').addEventListener('click', async (e) => {
        const moveBtn = e.target.closest('.btn-otk-move');
        if (!moveBtn) return;
        e.stopPropagation();

        const currentRow = moveBtn.closest('tr.otk-row');
        if (!currentRow) return;

        const direction = moveBtn.dataset.direction;
        const allRows = Array.from(document.querySelectorAll('#otkTable tbody tr.otk-row'))
            .filter(r => r.style.display !== 'none');
        const currentIndex = allRows.indexOf(currentRow);
        if (currentIndex === -1) return;

        let targetIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;
        if (targetIndex < 0) {
            await showAlert('Baris sudah berada di posisi paling atas.', 'info');
            return;
        }
        if (targetIndex >= allRows.length) {
            await showAlert('Baris sudah berada di posisi paling bawah.', 'info');
            return;
        }

        const targetRow = allRows[targetIndex];
        await swapRowData(currentRow, targetRow, direction);
    });

    // === FITUR DRAG AND DROP BARIS (TARIK & GESER KE ATAS / BAWAH) ===
    function initOtkDragAndDrop() {
        let draggedRow = null;
        const table = document.getElementById('otkTable');
        if (!table) return;

        table.addEventListener('dragstart', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.classList.contains('otk-inline-input')) {
                e.preventDefault();
                return;
            }
            const row = e.target.closest('tr.otk-row');
            if (!row) return;

            draggedRow = row;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', row.dataset.userId || '');
            row.classList.add('otk-row-dragging');
        });

        table.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const targetRow = e.target.closest('tr.otk-row');
            if (!targetRow || targetRow === draggedRow) return;

            document.querySelectorAll('tr.otk-row.otk-drag-over').forEach(r => {
                if (r !== targetRow) r.classList.remove('otk-drag-over');
            });
            targetRow.classList.add('otk-drag-over');
        });

        table.addEventListener('dragleave', (e) => {
            const targetRow = e.target.closest('tr.otk-row');
            if (targetRow && !targetRow.contains(e.relatedTarget)) {
                targetRow.classList.remove('otk-drag-over');
            }
        });

        table.addEventListener('dragend', () => {
            if (draggedRow) {
                draggedRow.classList.remove('otk-row-dragging');
                draggedRow = null;
            }
            document.querySelectorAll('tr.otk-row.otk-drag-over').forEach(r => r.classList.remove('otk-drag-over'));
        });

        table.addEventListener('drop', async (e) => {
            e.preventDefault();
            const targetRow = e.target.closest('tr.otk-row');
            document.querySelectorAll('tr.otk-row.otk-drag-over').forEach(r => r.classList.remove('otk-drag-over'));

            if (!draggedRow || !targetRow || draggedRow === targetRow) {
                if (draggedRow) draggedRow.classList.remove('otk-row-dragging');
                draggedRow = null;
                return;
            }

            const allRows = Array.from(document.querySelectorAll('#otkTable tbody tr.otk-row'))
                .filter(r => r.style.display !== 'none');
            const sourceIndex = allRows.indexOf(draggedRow);
            const targetIndex = allRows.indexOf(targetRow);
            const direction = targetIndex < sourceIndex ? 'up' : 'down';

            const source = draggedRow;
            draggedRow.classList.remove('otk-row-dragging');
            draggedRow = null;

            await swapRowData(source, targetRow, direction);
        });
    }

    // === FITUR EDIT KOLOM LANGSUNG (INLINE EDIT DENGAN KLIK GANDA / ENTER) ===
    function initOtkInlineEdit() {
        const table = document.getElementById('otkTable');
        if (!table) return;

        table.addEventListener('dblclick', (e) => {
            const cell = e.target.closest('.otk-editable-cell');
            if (!cell || cell.querySelector('.otk-inline-input')) return;

            let textTarget = cell;
            if (cell.classList.contains('employee-name')) {
                textTarget = cell.querySelector('.employee-name-text') || cell;
            }

            const originalText = textTarget.textContent.trim();
            const field = cell.dataset.field || textTarget.dataset.field || 'name';
            const row = cell.closest('tr.otk-row') || cell.closest('tr');
            const userId = row ? row.dataset.userId : null;

            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control form-control-sm otk-inline-input';
            input.value = originalText;

            const originalHTML = textTarget.innerHTML;
            textTarget.innerHTML = '';
            textTarget.appendChild(input);
            input.focus();
            input.select();

            let isSaving = false;

            const saveEdit = async () => {
                if (isSaving) return;
                isSaving = true;

                const newValue = input.value.trim();
                if (!newValue || newValue === originalText) {
                    textTarget.innerHTML = originalHTML;
                    return;
                }

                // Render teks baru
                textTarget.textContent = newValue;

                if (userId && (field === 'name' || field === 'jabatan' || field === 'unit')) {
                    try {
                        const updateUrl = @json(url('/hirarki')) + '/' + userId + '/update';
                        const response = await fetch(updateUrl, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ [field]: newValue }),
                        });

                        const result = await response.json();
                        if (!response.ok || !result.success) {
                            throw new Error(result.message || 'Gagal menyimpan perubahan.');
                        }

                        // Efek hijau sukses
                        textTarget.style.transition = 'background-color 0.3s ease';
                        textTarget.style.backgroundColor = '#86efac';
                        setTimeout(() => {
                            textTarget.style.backgroundColor = '';
                        }, 600);
                    } catch (err) {
                        textTarget.innerHTML = originalHTML;
                        await showAlert(err.message || 'Gagal menyimpan perubahan.', 'error');
                    }
                } else {
                    // Update tampilan sel merged
                    textTarget.style.transition = 'background-color 0.3s ease';
                    textTarget.style.backgroundColor = '#86efac';
                    setTimeout(() => {
                        textTarget.style.backgroundColor = '';
                    }, 600);
                }
            };

            input.addEventListener('keydown', (ke) => {
                if (ke.key === 'Enter') {
                    ke.preventDefault();
                    input.blur();
                } else if (ke.key === 'Escape') {
                    ke.preventDefault();
                    textTarget.innerHTML = originalHTML;
                }
            });

            input.addEventListener('blur', () => {
                saveEdit();
            });
        });
    }

    // Aktifkan Drag & Drop dan Inline Edit
    initOtkDragAndDrop();
    initOtkInlineEdit();

    // === FITUR SISIPKAN BARIS (INSERT ROW) ===
    const insertModalElement = document.getElementById('insertOtkRowModal');
    const insertModal = new bootstrap.Modal(insertModalElement);
    const insertRefUserId = document.getElementById('insertRefUserId');
    const insertRefUserName = document.getElementById('insertRefUserName');
    const insertJabatan = document.getElementById('insertJabatan');
    const insertUnit = document.getElementById('insertUnit');
    const insertSupervisorSelect = document.getElementById('insertSupervisorSelect');
    const insertExistingUserSelect = document.getElementById('insertExistingUserSelect');
    const insertExistingSection = document.getElementById('insertExistingUserSection');
    const insertNewSection = document.getElementById('insertNewUserSection');
    const insertNewName = document.getElementById('insertNewName');
    const insertNewEmail = document.getElementById('insertNewEmail');
    const saveInsertBtn = document.getElementById('saveInsertOtkRowBtn');

    // Event delegation untuk tombol sisipkan baris
    document.getElementById('otkTable').addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-otk-insert');
        if (!btn) return;
        e.stopPropagation();
        insertRefUserId.value = btn.dataset.userId;
        insertRefUserName.textContent = `${btn.dataset.userName} (${btn.dataset.userPosition || '-'})`;
        insertUnit.value = btn.dataset.userUnit || '';
        insertSupervisorSelect.value = btn.dataset.supervisorId || '';
        if (window.jQuery && jQuery.fn.select2) {
            jQuery(insertSupervisorSelect).val(btn.dataset.supervisorId || '').trigger('change');
            jQuery(insertExistingUserSelect).val('').trigger('change');
        }
        insertJabatan.value = '';
        insertExistingUserSelect.value = '';
        insertNewName.value = '';
        insertNewEmail.value = '';
        document.getElementById('insertModeExisting').checked = true;
        insertExistingSection.classList.remove('d-none');
        insertNewSection.classList.add('d-none');
        insertModal.show();
    });

    document.querySelectorAll('input[name="insertUserMode"]').forEach((radio) => {
        radio.addEventListener('change', (e) => {
            if (e.target.value === 'new') {
                insertExistingSection.classList.add('d-none');
                insertNewSection.classList.remove('d-none');
            } else {
                insertExistingSection.classList.remove('d-none');
                insertNewSection.classList.add('d-none');
            }
        });
    });

    const onExistingUserChange = () => {
        const opt = insertExistingUserSelect.selectedOptions ? insertExistingUserSelect.selectedOptions[0] : null;
        if (opt && opt.value) {
            if (opt.dataset.position && !insertJabatan.value) {
                insertJabatan.value = opt.dataset.position;
            }
            if (opt.dataset.unit && !insertUnit.value) {
                insertUnit.value = opt.dataset.unit;
            }
        }
    };
    insertExistingUserSelect.addEventListener('change', onExistingUserChange);
    if (window.jQuery) {
        jQuery(insertExistingUserSelect).on('select2:select change', onExistingUserChange);
    }

    saveInsertBtn.addEventListener('click', async () => {
        const mode = document.querySelector('input[name="insertUserMode"]:checked').value;
        const position = document.querySelector('input[name="insertPosition"]:checked').value;
        const refId = insertRefUserId.value;
        const jabatan = insertJabatan.value.trim();
        const unit = insertUnit.value.trim();
        const supervisorId = insertSupervisorSelect.value;

        // Kolom bersifat nullable / opsional (boleh juga diisi)
        const payload = {
            reference_user_id: refId,
            position: position,
            user_mode: mode,
            jabatan: jabatan || '-',
            unit: unit || '-',
            supervisor_id: supervisorId || null,
        };

        if (mode === 'existing') {
            payload.existing_user_id = insertExistingUserSelect.value || null;
        } else {
            payload.new_name = insertNewName.value.trim() || '-';
            payload.new_email = insertNewEmail.value.trim() || null;
        }

        saveInsertBtn.disabled = true;
        try {
            const response = await fetch(@json(route('hirarki.insert-row')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal menyisipkan baris.');
            }

            insertModal.hide();
            await showAlert(result.message);
            window.location.reload();
        } catch (error) {
            await showAlert(error.message || 'Terjadi kesalahan.', 'error');
        } finally {
            saveInsertBtn.disabled = false;
        }
    });

    // === FITUR HAPUS BARIS (DELETE ROW) ===
    document.getElementById('otkTable').addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-otk-delete');
        if (!btn) return;
        e.stopPropagation();
        const userId = btn.dataset.userId;
        const userName = btn.dataset.userName;

        if (!await confirmAction(`Hapus baris "${userName}" dari penempatan OTK? Akun pengguna tetap tersimpan di sistem namun baris akan dikeluarkan dari tabel OTK.`, 'Hapus Baris OTK?')) {
            return;
        }

        btn.disabled = true;
        try {
            const response = await fetch(@json(route('hirarki.delete-row')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ user_id: userId }),
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal menghapus baris.');
            }

            await showAlert(result.message);
            window.location.reload();
        } catch (error) {
            await showAlert(error.message || 'Terjadi kesalahan.', 'error');
        } finally {
            btn.disabled = false;
        }
    });

})();
</script>
@endpush
