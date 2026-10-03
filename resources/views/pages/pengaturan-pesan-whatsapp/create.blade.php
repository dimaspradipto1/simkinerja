@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Pengaturan Pesan WhatsApp</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Pengaturan Sistem</li>
            <li class="breadcrumb-item active">Pesan WhatsApp</li>
        </ol>
    </nav>
</div>

@php
    $modalItems = session('manual_wa_recipients', $existingManualItems ?? []);
    $hasModalItems = !empty($modalItems) && collect($modalItems)->contains(fn ($group) => !empty($group['items']));
    $shouldAutoOpenModal = session()->has('manual_wa_recipients') && $hasModalItems;
@endphp

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <h5 class="card-title fw-bold m-0 py-2">Template, Penerima, dan Jadwal Pengiriman</h5>
                        @if ($hasModalItems)
                            <button type="button" class="btn btn-outline-success btn-sm my-1" data-bs-toggle="modal" data-bs-target="#modalKirimWaManual">
                                <i class="bi bi-whatsapp me-1"></i> Buka Daftar Pengiriman Manual ({{ collect($modalItems)->first()['items'] ? count(collect($modalItems)->first()['items']) : 0 }} Pegawai)
                            </button>
                        @endif
                    </div>

                    <div class="alert alert-info">
                        Pilih pegawai bawahan OTK sebagai tujuan penerima. Setelah menekan tombol <strong>Kirim</strong>, jendela daftar pengiriman manual via WhatsApp akan terbuka otomatis dengan pesan yang sudah terisi untuk setiap penerima.
                    </div>

                    @include('pages.pengaturan-pesan-whatsapp._form')
                </div>
            </div>
        </div>
    </div>
</section>

@if ($hasModalItems)
<!-- Modal Pengiriman WhatsApp Manual -->
<div class="modal fade" id="modalKirimWaManual" tabindex="-1" aria-labelledby="modalKirimWaManualLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="modalKirimWaManualLabel">
                    <i class="bi bi-whatsapp me-2"></i> Pengiriman WhatsApp Manual ke Penerima
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-success d-flex align-items-center mb-3">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div>
                        <strong>Pengaturan berhasil disimpan!</strong>
                        <div>Klik tombol <span class="badge bg-success"><i class="bi bi-whatsapp"></i> Kirim WhatsApp</span> pada masing-masing pegawai di bawah untuk membuka WhatsApp Web/Desktop dengan isi pesan yang sudah terisi otomatis.</div>
                    </div>
                </div>

                <!-- Nav Tabs Status -->
                <ul class="nav nav-tabs nav-tabs-bordered mb-3" id="waStatusTabs" role="tablist">
                    @foreach ($modalItems as $statusKey => $group)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                id="tab-btn-{{ Str::slug($statusKey) }}"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-content-{{ Str::slug($statusKey) }}"
                                type="button" role="tab">
                                {{ $group['label'] }}
                                <span class="badge bg-secondary ms-1">{{ count($group['items']) }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>

                <!-- Tab Contents -->
                <div class="tab-content" id="waStatusTabsContent">
                    @foreach ($modalItems as $statusKey => $group)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                            id="tab-content-{{ Str::slug($statusKey) }}"
                            role="tabpanel">

                            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                                <span class="small text-muted">
                                    Mode:
                                    @if ($group['mode'] === 'terjadwal')
                                        <span class="badge bg-info text-dark">Terjadwal ({{ substr((string) $group['waktu_kirim'], 0, 5) }})</span>
                                    @else
                                        <span class="badge bg-primary">Saat status berubah / Otomatis</span>
                                    @endif
                                </span>
                                <span class="small text-muted">Total: <strong>{{ count($group['items']) }}</strong> penerima</span>
                            </div>

                            @if (empty($group['items']))
                                <div class="alert alert-light text-center py-4 border">
                                    <i class="bi bi-person-x fs-1 text-muted d-block mb-2"></i>
                                    Tidak ada penerima terdaftar dengan nomor WhatsApp aktif pada kategori ini.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">No</th>
                                                <th>Nama Pegawai</th>
                                                <th>Jabatan / Unit</th>
                                                <th>Nomor WhatsApp</th>
                                                <th>Preview Pesan</th>
                                                <th style="width: 170px;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($group['items'] as $item)
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td class="fw-semibold text-dark">{{ $item['recipient_name'] }}</td>
                                                    <td><span class="badge bg-light text-dark border">{{ $item['recipient_jabatan'] }}</span></td>
                                                    <td>
                                                        <a href="tel:{{ $item['clean_wa'] }}" class="text-decoration-none">
                                                            <i class="bi bi-telephone me-1 text-muted"></i>{{ $item['recipient_wa'] }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#preview-msg-{{ Str::slug($statusKey) }}-{{ $item['recipient_id'] }}">
                                                            <i class="bi bi-eye me-1"></i> Lihat Pesan
                                                        </button>
                                                        <div class="collapse mt-2" id="preview-msg-{{ Str::slug($statusKey) }}-{{ $item['recipient_id'] }}">
                                                            <div class="card card-body bg-light border-0 small text-pre-wrap p-2" style="white-space: pre-wrap; font-size: 0.85rem;">{{ $item['message'] }}</div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ $item['wa_url'] }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="btn btn-success btn-sm w-100 js-wa-send-btn">
                                                            <i class="bi bi-whatsapp me-1"></i> Kirim WhatsApp
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if ($shouldAutoOpenModal)
            var waModalEl = document.getElementById('modalKirimWaManual');
            if (waModalEl) {
                var waModal = new bootstrap.Modal(waModalEl);
                waModal.show();
            }
        @endif

        // Update button state upon clicking WhatsApp link
        document.querySelectorAll('.js-wa-send-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                this.classList.remove('btn-success');
                this.classList.add('btn-outline-success');
                this.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Sudah Dibuka';
            });
        });
    });
</script>
@endpush
