@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Kategori</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Pengaturan</li>
            <li class="breadcrumb-item active">Kategori</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title m-0 fw-bold text-dark fs-6"><i class="bi bi-tags-fill me-2 text-primary"></i>Daftar Kategori</h5>
                    <a href="{{ route('kategori.create') }}" class="btn btn-primary rounded btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
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
        function deleteKategori(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data kategori ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('kategori') }}/" + id,
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
                                $('#kategori-table').DataTable().ajax.reload();
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message || 'Gagal menghapus data.',
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush
