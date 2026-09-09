@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Laporan Pertanggungjawaban (LPJ)</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Rekapitulasi & Laporan</li>
            <li class="breadcrumb-item active">LPJ</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary bg-primary-subtle" style="width: 36px; height: 36px;">
                            <i class="bi bi-file-earmark-text-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="card-title m-0 fw-bold text-dark fs-6">Daftar Laporan Pertanggungjawaban (LPJ)</h5>
                            <small class="text-muted">Kelola dan arsipkan berkas serta tautan LPJ kegiatan</small>
                        </div>
                    </div>
                    <a href="{{ route('lpj.create') }}" class="btn btn-primary rounded btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-2 fw-semibold shadow-xs">
                        <i class="bi bi-plus-lg"></i> Tambah LPJ
                    </a>
                </div>
                <div class="card-body mt-2">
                    <div class="table-responsive">
                        {{ $dataTable->table([
                            'class' => 'table table-striped table-bordered align-middle',
                            'style' => 'width:100%; overflow-x: auto',
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
        function deleteLpj(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data LPJ beserta berkas terkait akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('lpj') }}/" + id,
                        type: 'DELETE',
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                $('#lpj-table').DataTable().ajax.reload();
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message || 'Gagal menghapus data LPJ.',
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data LPJ.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush
