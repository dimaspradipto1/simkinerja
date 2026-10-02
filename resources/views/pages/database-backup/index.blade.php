@extends('layouts.dashboard.template')

@section('page-style-class', 'menu-page-backup')

@section('content')
<div class="pagetitle">
    <h1>Backup Database &amp; Storage</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Pengaturan Sistem</li>
            <li class="breadcrumb-item active">Backup Database &amp; Storage</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0 backup-icon">
                            <i class="bi bi-server fs-4" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Ekspor Backup Database</h5>
                            <p class="text-muted mb-0">Unduh struktur tabel dan seluruh data database aplikasi sebagai berkas SQL.</p>
                        </div>
                    </div>

                    <dl class="row mb-4">
                        <dt class="col-sm-4 text-muted">Koneksi</dt>
                        <dd class="col-sm-8"><span class="badge bg-light text-dark border">{{ strtoupper($databaseDriver) }}</span></dd>
                        <dt class="col-sm-4 text-muted">Database</dt>
                        <dd class="col-sm-8"><code>{{ $databaseName ?: '-' }}</code></dd>
                        <dt class="col-sm-4 text-muted">Isi backup</dt>
                        <dd class="col-sm-8">Struktur dan data semua tabel MySQL</dd>
                    </dl>

                    @if($databaseDriver === 'mysql')
                        <div class="alert alert-info small mt-auto" role="alert">
                            <i class="bi bi-shield-lock-fill me-1" aria-hidden="true"></i>
                            Hanya administrator yang dapat mengunduh backup. Simpan berkas SQL di lokasi aman karena dapat berisi seluruh data aplikasi.
                        </div>
                        <a href="{{ route('database-backup.export') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 align-self-start">
                            <i class="bi bi-download"></i>
                            Unduh Backup SQL
                        </a>
                    @else
                        <div class="alert alert-warning mt-auto mb-0" role="alert">
                            Ekspor SQL ini membutuhkan koneksi database MySQL. Koneksi aktif saat ini adalah
                            <strong>{{ strtoupper($databaseDriver) }}</strong>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center flex-shrink-0 backup-icon">
                            <i class="bi bi-folder-symlink fs-4" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Backup Storage Public</h5>
                            <p class="text-muted mb-0">Arsipkan semua file pada folder <code>storage/app/public</code> ke dalam berkas ZIP.</p>
                        </div>
                    </div>

                    <div class="alert alert-warning small mt-auto" role="alert">
                        Backup dapat memuat dokumen dan lampiran yang dapat diakses melalui aplikasi. Simpan arsip di lokasi aman.
                    </div>

                    <a href="{{ route('database-backup.storage-public') }}" class="btn btn-success d-inline-flex align-items-center gap-2 align-self-start">
                        <i class="bi bi-download"></i>
                        Unduh Backup Storage ZIP
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
