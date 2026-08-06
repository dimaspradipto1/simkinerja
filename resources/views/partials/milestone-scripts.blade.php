<!-- Modal Tambah Point Milestone -->
<div class="modal fade" id="addMilestoneModal" tabindex="-1" aria-labelledby="addMilestoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header text-white" style="background-color: #15432d;">
                <h5 class="modal-title fw-bold" id="addMilestoneModalLabel"><i class="bi bi-flag-fill me-2"></i>Tambah Point Milestone</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAddMilestone">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="milestonable_id" id="modal_milestonable_id">
                    <input type="hidden" name="milestonable_type" id="modal_milestonable_type">

                    <div class="mb-3">
                        <label for="modal_nama_milestone" class="form-label fw-semibold text-dark">Nama / Uraian Point Milestone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_nama_milestone" name="nama_milestone" placeholder="Contoh: Pengumpulan Data awal, Draft Tahap 1, Review Direksi" required>
                    </div>

                    <div class="mb-3">
                        <label for="modal_catatan" class="form-label fw-semibold text-dark">Catatan Tambahan (Opsional)</label>
                        <textarea class="form-control" id="modal_catatan" name="catatan" rows="2" placeholder="Catatan singkat terkait point ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold px-4" style="background-color: #15432d; border-color: #15432d;"><i class="bi bi-plus-lg me-1"></i> Simpan Point</button>
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
            fetch(`/${taskType}/${id}/pause`, {
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
                    alert(data.message || 'Gagal men-jeda tugas.');
                }
            })
            .catch(err => console.error(err));
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
            fetch(`/milestone/${id}/pause`, {
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
                    alert(data.message || 'Gagal men-jeda milestone.');
                }
            })
            .catch(err => console.error(err));
        };

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
            if (typeof $('#rencanakerja-table').DataTable === 'function' && $.fn.DataTable.isDataTable('#rencanakerja-table')) {
                $('#rencanakerja-table').DataTable().ajax.reload(null, false);
            } else if (typeof $('#kepanitiaan-table').DataTable === 'function' && $.fn.DataTable.isDataTable('#kepanitiaan-table')) {
                $('#kepanitiaan-table').DataTable().ajax.reload(null, false);
            } else if (typeof $('#insidentil-table').DataTable === 'function' && $.fn.DataTable.isDataTable('#insidentil-table')) {
                $('#insidentil-table').DataTable().ajax.reload(null, false);
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
