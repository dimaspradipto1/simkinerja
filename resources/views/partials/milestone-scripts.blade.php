<!-- Modal Tambah Sub Kegiatan Mandiri (Full Fitur & Bisa Di-start) -->
<div class="modal fade" id="modalAddSubKegiatan" tabindex="-1" aria-labelledby="modalAddSubKegiatanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header text-white" style="background-color: #15432d;">
                <h5 class="modal-title fw-bold" id="modalAddSubKegiatanLabel">
                    <i class="bi bi-diagram-3-fill me-2"></i>Tambah Sub Kegiatan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAddSubKegiatan">
                @csrf
                <input type="hidden" name="parent_id" id="sub_modal_parent_id">
                <input type="hidden" id="sub_modal_route_prefix" value="rencana-kerja">
                <div class="modal-body p-4">
                    <div class="mb-3 p-2.5 bg-light rounded-3 border">
                        <label class="form-label small text-muted mb-0.5 fw-semibold d-block">Kegiatan Induk / Utama:</label>
                        <div class="fw-bold text-dark text-break" id="sub_modal_parent_title">-</div>
                    </div>

                    <div class="mb-3">
                        <label for="sub_modal_uraian" class="form-label fw-semibold text-dark">
                            Uraian Sub Kegiatan <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="sub_modal_uraian" name="uraian_tugas" rows="3" placeholder="Tuliskan uraian sub kegiatan yang akan dikerjakan..." required></textarea>
                        <div class="form-text small text-muted">Sub kegiatan ini akan memiliki fitur mandiri (bisa mulai/start timer, pause, selesai) dan terhubung ke kegiatan induk.</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small text-secondary fw-semibold">Estimasi Tgl Selesai</label>
                            <input type="date" class="form-control form-control-sm" name="estimasi_tanggal_selesai" id="sub_modal_est_tgl">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-secondary fw-semibold">Estimasi Jam Selesai</label>
                            <input type="time" class="form-control form-control-sm" name="estimasi_jam_selesai" id="sub_modal_est_jam">
                        </div>
                    </div>

                    <div class="p-2.5 bg-light-subtle rounded border d-flex align-items-center justify-content-between">
                        <div>
                            <label class="form-check-label fw-bold text-dark d-block mb-0" for="sub_modal_start_now" style="cursor: pointer;">
                                <i class="bi bi-play-circle-fill text-success me-1"></i> Langsung Mulai (Start Timer)
                            </label>
                            <span class="small text-muted" style="font-size: 0.75rem;">Otomatis mencatat waktu mulai saat sub kegiatan disimpan.</span>
                        </div>
                        <div class="form-check form-switch mb-0 ms-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="sub_modal_start_now" name="start_now" value="1" style="cursor: pointer; width: 36px; height: 20px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <a href="#" id="sub_modal_full_form_link" class="btn btn-sm btn-link text-decoration-none text-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Form Lengkap
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-success fw-bold px-3" style="background-color: #15432d; border-color: #15432d;">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Sub Kegiatan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Sub Kegiatan / Point Milestone -->
<div class="modal fade" id="addMilestoneModal" tabindex="-1" aria-labelledby="addMilestoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header text-white" style="background-color: #15432d;">
                <h5 class="modal-title fw-bold" id="addMilestoneModalLabel"><i class="bi bi-diagram-3-fill me-2"></i>Tambah Sub Kegiatan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAddMilestone">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="milestonable_id" id="modal_milestonable_id">
                    <input type="hidden" name="milestonable_type" id="modal_milestonable_type">

                    <div class="mb-3">
                        <label for="modal_nama_milestone" class="form-label fw-semibold text-dark">Nama / Uraian Sub Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_nama_milestone" name="nama_milestone" placeholder="Contoh: Pengumpulan Data, Pembuatan Draft, Review Atasan, Finalisasi" required>
                        <div class="form-text small text-muted">Sub kegiatan akan menjadi tahapan kerja (milestone) yang dapat dipantau dan dihitung durasinya.</div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_catatan" class="form-label fw-semibold text-dark">Catatan / Keterangan (Opsional)</label>
                        <textarea class="form-control" id="modal_catatan" name="catatan" rows="2" placeholder="Catatan singkat terkait sub kegiatan ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold px-4" style="background-color: #15432d; border-color: #15432d;"><i class="bi bi-plus-lg me-1"></i> Simpan Sub Kegiatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Dynamic CSRF Token getter with Blade fallback
        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return (meta && meta.getAttribute('content')) ? meta.getAttribute('content') : '{{ csrf_token() }}';
        }

        if (typeof $ !== 'undefined' && $.ajaxSetup) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
        }

        // Helper to format seconds to human-readable format (00j 00m 00s)
        function formatSeconds(totalSeconds) {
            if (totalSeconds <= 0) return '0s';
            const days = Math.floor(totalSeconds / 86400);
            const hours = Math.floor((totalSeconds % 86400) / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            let parts = [];
            if (days > 0) parts.push(days + 'h');
            if (hours > 0) parts.push(hours + 'j');
            if (minutes > 0) parts.push(minutes + 'm');
            if (seconds > 0 || parts.length === 0) parts.push(seconds + 's');

            return parts.join(' ');
        }

        // Open Add Sub Kegiatan Modal (Kegiatan Mandiri dengan Timer Lengkap)
        window.openAddSubKegiatanModal = function (parentId, parentTitle, routePrefix) {
            var prefix = routePrefix || 'rencana-kerja';
            var parentIdEl = document.getElementById('sub_modal_parent_id');
            var parentTitleEl = document.getElementById('sub_modal_parent_title');
            var routePrefixEl = document.getElementById('sub_modal_route_prefix');
            var fullLinkEl = document.getElementById('sub_modal_full_form_link');
            var uraianEl = document.getElementById('sub_modal_uraian');
            var estTglEl = document.getElementById('sub_modal_est_tgl');
            var estJamEl = document.getElementById('sub_modal_est_jam');
            var startNowEl = document.getElementById('sub_modal_start_now');

            if (parentIdEl) parentIdEl.value = parentId;
            if (parentTitleEl) parentTitleEl.textContent = parentTitle || ('Kegiatan Utama #' + parentId);
            if (routePrefixEl) routePrefixEl.value = prefix;
            if (fullLinkEl) fullLinkEl.setAttribute('href', '/' + prefix + '/create?parent_id=' + parentId);
            if (uraianEl) uraianEl.value = '';
            if (estTglEl) estTglEl.value = '';
            if (estJamEl) estJamEl.value = '';
            if (startNowEl) startNowEl.checked = false;

            var modalEl = document.getElementById('modalAddSubKegiatan');
            if (modalEl) {
                var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.show();
            }
        };

        // Form Submit Handler for Sub Kegiatan
        var formAddSub = document.getElementById('formAddSubKegiatan');
        if (formAddSub) {
            formAddSub.addEventListener('submit', function (e) {
                e.preventDefault();
                var prefix = document.getElementById('sub_modal_route_prefix') ? document.getElementById('sub_modal_route_prefix').value : 'rencana-kerja';
                var url = '/' + prefix;
                var formData = new FormData(formAddSub);
                var btnSubmit = formAddSub.querySelector('button[type="submit"]');

                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
                }

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Simpan Sub Kegiatan';
                    }

                    if (data.success) {
                        var modalEl = document.getElementById('modalAddSubKegiatan');
                        var modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
                        if (modal) modal.hide();
                        formAddSub.reset();

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: data.message || 'Sub kegiatan berhasil disimpan!',
                                showConfirmButton: false,
                                timer: 3500,
                                timerProgressBar: true
                            });
                        }

                        // Reload appropriate DataTable
                        if (window.LaravelDataTables && window.LaravelDataTables['rencanakerja-table']) {
                            window.LaravelDataTables['rencanakerja-table'].ajax.reload(null, false);
                        } else if (typeof $ !== 'undefined' && $.fn && $.fn.DataTable) {
                            if ($.fn.DataTable.isDataTable('#rencanakerja-table')) {
                                $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                            } else if ($.fn.DataTable.isDataTable('#kegiatan-table')) {
                                $('#kegiatan-table').DataTable().ajax.reload(null, false);
                            } else if ($.fn.DataTable.isDataTable('#kepanitiaan-table')) {
                                $('#kepanitiaan-table').DataTable().ajax.reload(null, false);
                            } else if ($.fn.DataTable.isDataTable('#insidentil-table')) {
                                $('#insidentil-table').DataTable().ajax.reload(null, false);
                            } else {
                                window.location.reload();
                            }
                        } else {
                            window.location.reload();
                        }
                    } else {
                        var msg = data.message || 'Terjadi kesalahan saat menyimpan sub kegiatan.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                        } else {
                            alert(msg);
                        }
                    }
                })
                .catch(err => {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Simpan Sub Kegiatan';
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan jaringan atau server.' });
                    } else {
                        alert('Terjadi kesalahan jaringan atau server.');
                    }
                });
            });
        }

        // Open Add Milestone Modal
        window.openAddMilestoneModal = function (taskId, taskType) {
            document.getElementById('modal_milestonable_id').value = taskId;
            document.getElementById('modal_milestonable_type').value = taskType;
            document.getElementById('modal_nama_milestone').value = '';
            document.getElementById('modal_catatan').value = '';
            
            const modalEl = document.getElementById('addMilestoneModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        };

        // Form Submit Handler
        const formAdd = document.getElementById('formAddMilestone');
        if (formAdd) {
            formAdd.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(formAdd);

                fetch("{{ route('milestone.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const modalEl = document.getElementById('addMilestoneModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                        refreshTables();
                    } else {
                        alert(data.message || 'Gagal menyimpan point milestone');
                    }
                })
                .catch(err => {
                    console.error('Error adding milestone:', err);
                    alert('Terjadi kesalahan saat menambahkan point milestone.');
                });
            });
        }

        // Pause Main Task Timer
        window.pauseTaskTimer = function (id, taskType) {
            taskType = taskType || 'rencana-kerja';

            const doPause = function (alasan) {
                fetch(`/${taskType}/${id}/pause`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ alasan_pause: alasan || '' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success || data.status === 'success') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Pekerjaan Dijeda',
                                text: data.message || 'Tugas berhasil di-pause.',
                                icon: 'info',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                        refreshTables();
                    } else {
                        alert(data.message || 'Gagal men-jeda tugas.');
                    }
                })
                .catch(err => console.error(err));
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Jeda Pekerjaan (Pause)',
                    html: `
                        <div class="text-start">
                            <label class="form-label fw-semibold small text-muted mb-1">
                                <i class="bi bi-info-circle-fill text-warning me-1"></i>Penyebab Di-pause:
                            </label>
                            <textarea id="swal-pause-task-reason" class="form-control" rows="3" placeholder="Masukkan alasan atau penyebab pekerjaan di-pause (misal: menunggu data, istirahat, kendala teknis)..."></textarea>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-pause-fill me-1"></i> Pause Pekerjaan',
                    cancelButtonText: 'Batal',
                    focusConfirm: false,
                    preConfirm: () => {
                        const el = document.getElementById('swal-pause-task-reason');
                        return el ? el.value.trim() : '';
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        doPause(result.value);
                    }
                });
            } else {
                const reason = prompt('Penyebab di-pause (opsional):');
                if (reason !== null) {
                    doPause(reason);
                }
            }
        };

        // Start / Resume Milestone
        window.startMilestone = function (id) {
            fetch(`/milestone/${id}/start`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    refreshTables();
                } else {
                    alert(data.message || 'Gagal menjalankan milestone.');
                }
            })
            .catch(err => console.error(err));
        };

        // Pause Milestone
        window.pauseMilestone = function (id) {
            const doPauseMilestone = function (alasan) {
                fetch(`/milestone/${id}/pause`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ alasan_pause: alasan || '' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Milestone Dijeda',
                                text: data.message || 'Milestone berhasil di-pause.',
                                icon: 'info',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                        refreshTables();
                    } else {
                        alert(data.message || 'Gagal men-jeda milestone.');
                    }
                })
                .catch(err => console.error(err));
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Jeda Milestone (Pause)',
                    html: `
                        <div class="text-start">
                            <label class="form-label fw-semibold small text-muted mb-1">
                                <i class="bi bi-info-circle-fill text-warning me-1"></i>Penyebab Di-pause:
                            </label>
                            <textarea id="swal-pause-milestone-reason" class="form-control" rows="3" placeholder="Masukkan alasan atau penyebab point milestone di-pause..."></textarea>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-pause-fill me-1"></i> Pause Point',
                    cancelButtonText: 'Batal',
                    focusConfirm: false,
                    preConfirm: () => {
                        const el = document.getElementById('swal-pause-milestone-reason');
                        return el ? el.value.trim() : '';
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        doPauseMilestone(result.value);
                    }
                });
            } else {
                const reason = prompt('Penyebab di-pause (opsional):');
                if (reason !== null) {
                    doPauseMilestone(reason);
                }
            }
        };

        // Quick Save Alasan Pause from Detail Textarea
        $(document).on('click', '.btn-simpan-alasan-pause', function (e) {
            e.preventDefault();
            const btn = $(this);
            const id = btn.data('id');
            const taskType = btn.data('type') || 'rencana-kerja';
            const container = btn.closest('.border');
            const textarea = container.find('.field-alasan-pause');
            const alasan = textarea.val();

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            const formData = new FormData();
            formData.append('_token', getCsrfToken());
            formData.append('alasan_pause', alasan);

            fetch(`/${taskType}/${id}/upload-attachment`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Simpan Penyebab Pause');
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Penyebab pause berhasil disimpan.',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                    }
                    refreshTables();
                } else {
                    alert(data.message || 'Gagal menyimpan penyebab pause.');
                }
            })
            .catch(err => {
                console.error(err);
                btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Simpan Penyebab Pause');
                alert('Terjadi kesalahan saat menyimpan penyebab pause.');
            });
        });

        // Stop / Complete Milestone
        window.stopMilestone = function (id) {
            fetch(`/milestone/${id}/stop`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    refreshTables();
                } else {
                    alert(data.message || 'Gagal menghentikan milestone.');
                }
            })
            .catch(err => console.error(err));
        };

        // Delete Milestone
        window.deleteMilestone = function (id) {
            const confirmDelete = function () {
                fetch(`/milestone/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        refreshTables();
                    } else {
                        alert(data.message || 'Gagal menghapus milestone.');
                    }
                })
                .catch(err => console.error(err));
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Point Milestone?',
                    text: "Point milestone ini akan dihapus secara permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmColor: '#d33',
                    cancelColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        confirmDelete();
                    }
                });
            } else if (confirm('Apakah Anda yakin ingin menghapus point milestone ini?')) {
                confirmDelete();
            }
        };

        // Helper to refresh datatables across different pages
        function refreshTables() {
            if (typeof refreshAnyTable === 'function') {
                refreshAnyTable();
                return;
            }
            if (window.LaravelDataTables && window.LaravelDataTables['rencanakerja-table']) {
                window.LaravelDataTables['rencanakerja-table'].ajax.reload(null, false);
            } else if (window.LaravelDataTables && window.LaravelDataTables['kepanitiaan-table']) {
                window.LaravelDataTables['kepanitiaan-table'].ajax.reload(null, false);
            } else if (window.LaravelDataTables && window.LaravelDataTables['insidentil-table']) {
                window.LaravelDataTables['insidentil-table'].ajax.reload(null, false);
            } else if (typeof $('#rencanakerja-table').DataTable === 'function' && $.fn.DataTable.isDataTable('#rencanakerja-table')) {
                $('#rencanakerja-table').DataTable().ajax.reload(null, false);
            } else if (typeof $('#kepanitiaan-table').DataTable === 'function' && $.fn.DataTable.isDataTable('#kepanitiaan-table')) {
                $('#kepanitiaan-table').DataTable().ajax.reload(null, false);
            } else if (typeof $('#insidentil-table').DataTable === 'function' && $.fn.DataTable.isDataTable('#insidentil-table')) {
                $('#insidentil-table').DataTable().ajax.reload(null, false);
            } else if (typeof $('#kegiatan-table').DataTable === 'function' && $.fn.DataTable.isDataTable('#kegiatan-table')) {
                $('#kegiatan-table').DataTable().ajax.reload(null, false);
            } else {
                location.reload();
            }
        }

        // Live Timer Ticking Loop for Running Milestones
        setInterval(function () {
            const nowTs = Math.floor(Date.now() / 1000);

            // Loop through each container widget
            document.querySelectorAll('.milestone-widget-container').forEach(function (container) {
                let totalWidgetDetik = 0;

                container.querySelectorAll('.milestone-item').forEach(function (item) {
                    const status = item.getAttribute('data-status');
                    const baseDetik = parseInt(item.getAttribute('data-base-detik') || '0', 10);
                    const lastStartedTs = parseInt(item.getAttribute('data-last-started-ts') || '0', 10);

                    let activeDetik = baseDetik;

                    if (status === 'Berjalan' && lastStartedTs > 0) {
                        const elapsed = Math.max(0, nowTs - lastStartedTs);
                        activeDetik += elapsed;
                    }

                    totalWidgetDetik += activeDetik;

                    // Update live text on individual milestone item
                    const timerTextEl = item.querySelector('.milestone-timer-text');
                    if (timerTextEl) {
                        timerTextEl.textContent = formatSeconds(activeDetik);
                    }
                });

                // Update total duration on widget header
                const overallTextEl = container.querySelector('.overall-timer-text');
                if (overallTextEl) {
                    overallTextEl.textContent = formatSeconds(totalWidgetDetik);
                }
            });
        }, 1000);
    });
</script>
