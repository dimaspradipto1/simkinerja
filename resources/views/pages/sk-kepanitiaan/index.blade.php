@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>SK Kepanitiaan</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Pengaturan Sistem</li>
            <li class="breadcrumb-item active">SK Kepanitiaan</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <!-- Info Banner -->
            <div class="alert alert-light border shadow-xs d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 py-2.5 px-3 rounded-3" style="border-left: 4px solid #0d6efd !important;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                    <span class="small text-dark">
                        Unggah berkas <strong>Surat Keputusan (SK) Kepanitiaan (PDF)</strong>. Sistem akan otomatis mendeteksi nama dan posisi personel, serta memperbarui peran &amp; status kepanitiaan pegawai terkait secara dinamis.
                    </span>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
                    <div>
                        <h5 class="card-title m-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Daftar SK Kepanitiaan
                        </h5>
                        <p class="text-muted small mb-0 mt-0.5">Kelola berkas SK kepanitiaan dan penetapan posisi personel.</p>
                    </div>
                    <a href="{{ route('sk-kepanitiaan.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold rounded-2 px-3 py-2">
                        <i class="bi bi-cloud-arrow-up-fill"></i> Upload SK Kepanitiaan
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        {{ $dataTable->table([
                            'class' => 'table table-hover align-middle w-100',
                            'style' => 'width:100%; font-size: 0.88rem;',
                        ]) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    @if (app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif

    <script>
        function deleteSk(id, name) {
            Swal.fire({
                title: 'Hapus SK Kepanitiaan?',
                html: 'Anda akan menghapus SK <strong>"' + name + '"</strong>.<br><small class="text-danger">Penugasan posisi kepanitiaan ini pada seluruh akun pegawai terkait juga akan otomatis dilepas.</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('sk-kepanitiaan') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                window.LaravelDataTables["sk-kepanitiaan-table"].ajax.reload();
                            } else {
                                Swal.fire('Gagal', res.message || 'Gagal menghapus data.', 'error');
                            }
                        },
                        error: function(xhr) {
                            const errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat menghapus SK.';
                            Swal.fire('Error', errMsg, 'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush
