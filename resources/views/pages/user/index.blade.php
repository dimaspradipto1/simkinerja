@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Manajemen User</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">User</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
                        <h5 class="card-title p-0 m-0">Daftar Pengguna</h5>
                        <div class="d-flex gap-2 align-items-center">
                            <button type="button" id="btn-bulk-delete" class="btn btn-danger btn-sm text-white fw-bold d-inline-flex align-items-center gap-1 d-none">
                                <i class="bi bi-trash-fill"></i> Hapus Terpilih (<span id="selected-count">0</span>)
                            </button>
                            <a href="{{ route('user.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                                <i class="bi bi-plus-circle"></i> Tambah
                            </a>
                            <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalImportExcel" style="background-color: #15432d; border-color: #15432d;">
                                <i class="bi bi-file-earmark-excel"></i> Import User Excel
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        {{ $dataTable->table([
                            'class' => 'table table-hover table-striped table-bordered w-100',
                            'style' => 'width:100%; overflow-x: auto',
                        ]) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #15432d;">
                <h5 class="modal-title fw-bold" id="modalImportExcelLabel">
                    <i class="bi bi-file-earmark-excel me-2"></i> Import User Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.import-excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded" style="background-color: #e0f2fe; border: 1px solid #bae6fd;">
                        <p class="mb-1 text-dark" style="font-size: 0.9rem;">
                            Format header: <strong>name, email, password, roles, nup, unit, jabatan, jabatan_pkkmb, jabatan_esq, jabatan_milad, jabatan_kuliah_umum</strong>.
                        </p>
                        <p class="mb-1 text-secondary small">
                            <i class="bi bi-check2-circle me-1 text-primary"></i> <strong>Role lebih dari satu:</strong> Pisahkan dengan koma pada kolom <code>roles</code> (contoh: <code>staff lpti, kepanitiaan</code>).
                        </p>
                        <p class="mb-1 text-success fw-semibold small">
                            <i class="bi bi-info-circle me-1"></i> Data user yang sudah ada (berdasarkan Email / NUP / Nama) akan otomatis diperbarui (Update Data).
                        </p>
                        <a href="{{ route('user.download-template') }}" class="btn btn-sm text-primary p-0 fw-bold border-0 bg-transparent mt-1 d-inline-block" style="font-size: 0.95rem;">
                            <i class="bi bi-download me-1"></i> Download Template Excel
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2">Pilih File Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file_excel" class="form-control bg-white" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 text-white fw-bold" style="background-color: #15432d; border-color: #15432d;">
                        <i class="bi bi-upload me-1"></i> Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ganti Password -->
<div class="modal fade" id="modalPassword" tabindex="-1" aria-labelledby="modalPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold" id="modalPasswordLabel"><i class="bi bi-key me-2"></i> Ganti Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPassword" onsubmit="submitPassword(event)">
                @csrf
                <input type="hidden" id="password_user_id">
                <div class="modal-body p-4">
                    <p class="text-muted mb-3" id="password_user_name"></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password" required minlength="6" placeholder="Minimal 6 karakter">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password_confirmation" required minlength="6" placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white"><i class="bi bi-save me-1"></i> Simpan Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @if (app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif

    <script>
        // Multi-select & Bulk Delete
        let selectedUserIds = new Set();

        function updateBulkDeleteButton() {
            let count = selectedUserIds.size;
            $('#selected-count').text(count);
            if (count > 0) {
                $('#btn-bulk-delete').removeClass('d-none');
            } else {
                $('#btn-bulk-delete').addClass('d-none');
            }
        }

        // Toggle check-all
        $(document).on('change', '#check-all-users', function() {
            let isChecked = $(this).is(':checked');
            $('.select-user-checkbox:not(:disabled)').prop('checked', isChecked);
            $('.select-user-checkbox:not(:disabled)').each(function() {
                let val = parseInt($(this).val());
                if (isChecked) {
                    selectedUserIds.add(val);
                } else {
                    selectedUserIds.delete(val);
                }
            });
            updateBulkDeleteButton();
        });

        // Individual checkbox change
        $(document).on('change', '.select-user-checkbox', function() {
            let val = parseInt($(this).val());
            if ($(this).is(':checked')) {
                selectedUserIds.add(val);
            } else {
                selectedUserIds.delete(val);
            }

            let totalOnPage = $('.select-user-checkbox:not(:disabled)').length;
            let checkedOnPage = $('.select-user-checkbox:checked').length;
            $('#check-all-users').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
            $('#check-all-users').prop('indeterminate', checkedOnPage > 0 && checkedOnPage < totalOnPage);

            updateBulkDeleteButton();
        });

        // Re-sync on table draw (pagination / search / reload)
        $(document).on('draw.dt', '#user-table', function() {
            let totalOnPage = $('.select-user-checkbox:not(:disabled)').length;
            let checkedOnPage = 0;

            $('.select-user-checkbox:not(:disabled)').each(function() {
                let val = parseInt($(this).val());
                if (selectedUserIds.has(val)) {
                    $(this).prop('checked', true);
                    checkedOnPage++;
                } else {
                    $(this).prop('checked', false);
                }
            });

            $('#check-all-users').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
            $('#check-all-users').prop('indeterminate', checkedOnPage > 0 && checkedOnPage < totalOnPage);

            updateBulkDeleteButton();
        });

        // Handle Bulk Delete Click
        $(document).on('click', '#btn-bulk-delete', function() {
            let ids = Array.from(selectedUserIds);
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Hapus User Terpilih?',
                text: 'Apakah Anda yakin ingin menghapus ' + ids.length + ' data user terpilih ini secara permanen?',
                icon: 'warning',
                showCancelButton: true,
                confirmColor: '#dc3545',
                cancelColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus ' + ids.length + ' User',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('user.bulk-delete') }}",
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "ids": ids
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message || 'Data user terpilih berhasil dihapus.'
                                });
                                selectedUserIds.clear();
                                updateBulkDeleteButton();
                                $('#check-all-users').prop('checked', false).prop('indeterminate', false);
                                $('#user-table').DataTable().ajax.reload(null, false);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat menghapus user terpilih.'
                            });
                        }
                    });
                }
            });
        });

        function openPasswordModal(id, name) {
            $('#password_user_id').val(id);
            $('#password_user_name').html('Ganti password untuk: <strong>' + name + '</strong>');
            $('#new_password').val('');
            $('#new_password_confirmation').val('');
            $('#modalPassword').modal('show');
        }

        function submitPassword(e) {
            e.preventDefault();
            const id = $('#password_user_id').val();
            const password = $('#new_password').val();
            const password_confirmation = $('#new_password_confirmation').val();

            if (password !== password_confirmation) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Konfirmasi password tidak cocok!'
                });
                return;
            }

            $.ajax({
                url: "{{ url('user') }}/" + id + "/password",
                type: 'PUT',
                data: {
                    _token: "{{ csrf_token() }}",
                    password: password,
                    password_confirmation: password_confirmation
                },
                success: function(response) {
                    $('#modalPassword').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message || 'Password berhasil diperbarui!'
                    });
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan saat memperbarui password.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: msg
                    });
                }
            });
        }

        function deleteUser(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data user yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmColor: '#dc3545',
                cancelColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('user') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message || 'User berhasil dihapus.'
                            }).then(() => {
                                if ($.fn.DataTable.isDataTable('#user-table')) {
                                    $('#user-table').DataTable().ajax.reload(null, false);
                                } else {
                                    location.reload();
                                }
                            });
                        },
                        error: function(xhr) {
                            let msg = 'Gagal menghapus user.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush
