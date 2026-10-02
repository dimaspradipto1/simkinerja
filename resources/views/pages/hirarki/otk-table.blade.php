@extends('layouts.dashboard.template')

@section('page-style-class', 'menu-page-otk')
@section('content')
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
                                    data-section-key="{{ $sectionIndex }}"
                                    data-coordinator-key="{{ $sectionIndex }}-{{ $coordinatorIndex }}"
                                    data-implementer-key="{{ $sectionIndex }}-{{ $coordinatorIndex }}-{{ $implementerIndex }}"
                                    data-search="{{ strtolower($section['unit'].' '.$employee->jabatan.' '.$employee->name.' '.$coordinator['label'].' '.$implementer['label']) }}"
                                >
                                    @if($sectionFirstRow)
                                        <td rowspan="{{ $sectionRowCount }}" class="unit-number" data-merge="section-number" data-merge-key="{{ $sectionIndex }}">{{ $section['number'] }}</td>
                                        <td rowspan="{{ $sectionRowCount }}" class="unit-name" data-merge="section-unit" data-merge-key="{{ $sectionIndex }}">{{ $section['unit'] }}</td>
                                        <td rowspan="{{ $sectionRowCount }}" class="merged-label" data-merge="section-responsibility" data-merge-key="{{ $sectionIndex }}">{{ $section['responsibility'] }}</td>
                                        @php $sectionFirstRow = false; @endphp
                                    @endif
                                    @if($coordinatorFirstRow)
                                        <td rowspan="{{ $coordinatorRowCount }}" class="merged-label" data-merge="coordinator" data-merge-key="{{ $sectionIndex }}-{{ $coordinatorIndex }}">{{ $coordinator['label'] }}</td>
                                        @php $coordinatorFirstRow = false; @endphp
                                    @endif
                                    @if($loop->first)
                                        <td rowspan="{{ $implementerRowCount }}" class="merged-label" data-merge="implementer" data-merge-key="{{ $sectionIndex }}-{{ $coordinatorIndex }}-{{ $implementerIndex }}">{{ $implementer['label'] }}</td>
                                    @endif
                                    <td>{{ $employee->jabatan }}</td>
                                    <td class="employee-name">
                                        <span>{{ $employee->name }}</span>
                                        <button
                                            type="button"
                                            class="edit-supervisor screen-only"
                                            data-user-id="{{ $employee->id }}"
                                            data-user-name="{{ $employee->name }}"
                                            data-user-position="{{ $employee->jabatan }}"
                                            data-user-unit="{{ $employee->unit }}"
                                            data-supervisor-id="{{ $employee->supervisor_id }}"
                                            aria-label="Edit data OTK {{ $employee->name }}"
                                            title="Edit data OTK"
                                        ><i class="bi bi-pencil-square"></i></button>
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
                <label for="addOtkPosition" class="form-label fw-semibold">Jabatan OTK</label>
                <input type="text" id="addOtkPosition" class="form-control mb-3" maxlength="255" required>
                <label for="addOtkUnit" class="form-label fw-semibold">Unit</label>
                <input type="text" id="addOtkUnit" class="form-control mb-3" maxlength="255" required>
                <label for="addOtkSupervisor" class="form-label fw-semibold">Atasan Langsung</label>
                <select id="addOtkSupervisor" class="form-select" style="width: 100%;" required>
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

    if ($supervisorSelect && jQuery.fn.select2) {
        $supervisorSelect.select2({
            dropdownParent: jQuery('#supervisorModal'),
            placeholder: 'Cari nama, jabatan, atau unit...',
            theme: 'bootstrap-5',
            width: '100%',
        });
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
    }

    document.querySelectorAll('[data-merge]').forEach((cell) => {
        mergedCells.set(`${cell.dataset.merge}:${cell.dataset.mergeKey}`, cell);
    });

    function restoreMergedCells(tbody, rows) {
        tbody.querySelectorAll('[data-merge]').forEach((cell) => cell.remove());

        const mergeLevels = [
            ['section-number', 'sectionKey'],
            ['section-unit', 'sectionKey'],
            ['section-responsibility', 'sectionKey'],
            ['coordinator', 'coordinatorKey'],
            ['implementer', 'implementerKey'],
        ];

        mergeLevels.forEach(([level, keyName], columnIndex) => {
            const groups = new Map();
            rows.forEach((row) => {
                const key = row.dataset[keyName];
                if (!groups.has(key)) groups.set(key, []);
                groups.get(key).push(row);
            });

            groups.forEach((groupRows, key) => {
                const cell = mergedCells.get(`${level}:${key}`);
                if (!cell) return;

                cell.rowSpan = groupRows.length;
                const firstRow = groupRows[0];
                firstRow.insertBefore(cell, firstRow.children[columnIndex] || null);
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
        document.querySelectorAll('.otk-section').forEach((tbody) => {
            const rows = [...tbody.querySelectorAll('.otk-row')];
            rows.forEach((row) => {
                row.hidden = query !== '' && !row.dataset.search.includes(query);
            });
            restoreMergedCells(tbody, rows.filter((row) => !row.hidden));
        });
    });

    document.querySelectorAll('.edit-supervisor').forEach((button) => {
        button.addEventListener('click', () => {
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
            jabatan: addOtkPosition.value.trim(),
            unit: addOtkUnit.value.trim(),
            supervisor_id: addOtkSupervisor.value,
        };
        if (!payload.user_id || !payload.jabatan || !payload.unit || !payload.supervisor_id) {
            await showAlert('Pilih pengguna, isi jabatan dan unit, serta tentukan atasan langsung.', 'warning');
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
})();
</script>
@endpush
