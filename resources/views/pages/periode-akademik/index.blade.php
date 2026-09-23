@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Periode Akademik</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Periode Akademik</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title m-0 fw-bold text-dark fs-6"><i class="bi bi-calendar-event me-2 text-primary"></i>Daftar Periode Akademik</h5>
                    <a href="{{ route('periode-akademik.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold rounded-2 px-3 py-1.5">
                        <i class="bi bi-plus-circle"></i> Tambah Periode
                    </a>
                </div>
                <div class="card-body mt-3">
                    <div class="table-responsive">
                        {{ $dataTable->table([
                            'class' => 'table table-striped table-bordered',
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
        function deletePeriode(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data periode akademik ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmColor: '#dc3545',
                cancelColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('periode-akademik') }}/" + id,
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
                                $('#periodeakademik-table').DataTable().ajax.reload();
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
