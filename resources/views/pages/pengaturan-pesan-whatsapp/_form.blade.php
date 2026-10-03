@php
    $statuses = [
        'Belum Dimulai' => 'Belum Dimulai',
        'Proses/Berjalan' => 'Proses / Berjalan',
        'Gagal' => 'Gagal',
    ];
    $defaults = [
        'Belum Dimulai' => [
            'isi_pesan' => "Yth. {nama_penerima},\n\nMengingatkan rencana {kategori} Anda belum dimulai:\n{uraian_tugas}\n\nEstimasi selesai: {estimasi_tanggal_selesai} {estimasi_jam_selesai}\n\nMohon mulai mengerjakan sesuai rencana. Pesan ini dikirim oleh {nama_pemimpin}.",
        ],
        'Proses/Berjalan' => [
            'isi_pesan' => "Yth. {nama_penerima},\n\nRencana {kategori} berikut sedang dalam proses:\n{uraian_tugas}\n\nEstimasi selesai: {estimasi_tanggal_selesai} {estimasi_jam_selesai}\n\nPesan ini dikirim oleh {nama_pemimpin}.",
        ],
        'Gagal' => [
            'isi_pesan' => "Yth. {nama_penerima},\n\nRencana {kategori} berikut telah melewati estimasi waktu penyelesaian dan berstatus gagal:\n{uraian_tugas}\n\nEstimasi selesai: {estimasi_tanggal_selesai} {estimasi_jam_selesai}\n\nMohon segera memperbarui progres atau mengajukan perpanjangan waktu. Pesan ini dikirim oleh {nama_pemimpin}.",
        ],
    ];
@endphp

<form action="{{ route('pengaturan-pesan-whatsapp.store') }}" method="POST" class="row g-3">
    @csrf

    <div class="col-12">
        <label for="penerima_user_ids" class="form-label fw-semibold">Tujuan Penerima <span class="text-danger">*</span></label>
        <select id="penerima_user_ids" name="penerima_user_ids[]" class="form-select @error('penerima_user_ids') is-invalid @enderror @error('penerima_user_ids.*') is-invalid @enderror" multiple required>
            @foreach ($users as $user)
                <option value="{{ $user->id }}"
                    @selected(in_array((string) $user->id, array_map('strval', old('penerima_user_ids', $configuredRecipientIds)), true))>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
        @error('penerima_user_ids')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @error('penerima_user_ids.*')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">
            Cari dan pilih satu atau beberapa pegawai aktif yang memiliki atasan pada struktur OTK dan nomor WhatsApp terdaftar.
            Pilihan penerima diterapkan untuk Rencana Kerja, Kepanitiaan, dan Insidentil.
        </div>
        @if ($users->isEmpty())
            <div class="alert alert-warning mt-2 mb-0">
                Belum ada bawahan OTK aktif dengan nomor WhatsApp. Lengkapi struktur OTK dan nomor WhatsApp pengguna terlebih dahulu.
            </div>
        @endif
    </div>

    @foreach ($statuses as $status => $label)
        @php
            $template = $messages[$status] ?? null;
            $rawTime = $template?->waktu_kirim ? substr((string) $template->waktu_kirim, 0, 5) : null;
            $time = old('pesan.' . $status . '.waktu_kirim', $rawTime);
            $hasTime = !empty($time);
            $isActive = old('pesan.' . $status . '.is_active', $template?->is_active ?? true);
        @endphp
        <div class="col-12">
            <div class="card border shadow-none mb-0">
                <div class="card-header bg-light fw-semibold">
                    Pemberitahuan: {{ $label }}
                    <span class="text-muted fw-normal">— berlaku otomatis untuk semua jenis rencana</span>
                </div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label for="pesan_{{ $loop->index }}_isi" class="form-label fw-semibold">Isi Pesan <span class="text-danger">*</span></label>
                        <textarea id="pesan_{{ $loop->index }}_isi"
                            name="pesan[{{ $status }}][isi_pesan]"
                            rows="4" maxlength="5000" required
                            class="form-control @error('pesan.' . $status . '.isi_pesan') is-invalid @enderror">{{ old('pesan.' . $status . '.isi_pesan', $template?->isi_pesan ?? $defaults[$status]['isi_pesan']) }}</textarea>
                        @error('pesan.' . $status . '.isi_pesan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Variabel: <code>{nama_penerima}</code>, <code>{kategori}</code>, <code>{uraian_tugas}</code>,
                            <code>{estimasi_tanggal_selesai}</code>, <code>{estimasi_jam_selesai}</code>, <code>{nama_pemimpin}</code>.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="jadwal_tipe_{{ $loop->index }}" class="form-label fw-semibold">Jadwal Pengiriman <span class="text-danger">*</span></label>
                        <select id="jadwal_tipe_{{ $loop->index }}" class="form-select js-jadwal-tipe" data-target="#time_wrapper_{{ $loop->index }}" data-time-input="#pesan_{{ $loop->index }}_time">
                            <option value="langsung" @selected(!$hasTime)>Saat status berubah (Waktu tidak ditentukan — otomatis langsung terkirim ke penerima)</option>
                            <option value="terjadwal" @selected($hasTime)>Terjadwal (Kirim sesuai waktu yang ditentukan)</option>
                        </select>
                        <div class="form-text">
                            Jika waktu tidak ditentukan, pesan otomatis langsung terkirim ke penerima yang dituju. Namun jika waktu ditentukan, pesan dikirim sesuai jadwal yang dituju.
                        </div>
                    </div>

                    <div class="col-md-6 js-time-wrapper" id="time_wrapper_{{ $loop->index }}" style="{{ !$hasTime ? 'display: none;' : '' }}">
                        <label for="pesan_{{ $loop->index }}_time" class="form-label fw-semibold">Waktu Pengiriman <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-clock"></i></span>
                            <input id="pesan_{{ $loop->index }}_time" type="time"
                                name="pesan[{{ $status }}][waktu_kirim]" value="{{ $time }}"
                                class="form-control js-whatsapp-delivery-time @error('pesan.' . $status . '.waktu_kirim') is-invalid @enderror">
                            <button type="button" class="btn btn-outline-secondary js-btn-reset-time" title="Kosongkan waktu (otomatis langsung terkirim)" data-target-select="#jadwal_tipe_{{ $loop->index }}">
                                <i class="bi bi-x-circle me-1"></i> Batal / Otomatis
                            </button>
                        </div>
                        @error('pesan.' . $status . '.waktu_kirim')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Tentukan jam pengiriman pesan (misal: 08:00).
                        </div>
                    </div>

                    <div class="col-12">
                        <input type="hidden" name="pesan[{{ $status }}][is_active]" value="0">
                        <div class="form-check form-switch">
                            <input type="checkbox" id="pesan_{{ $loop->index }}_active"
                                name="pesan[{{ $status }}][is_active]" value="1"
                                class="form-check-input @error('pesan.' . $status . '.is_active') is-invalid @enderror"
                                @checked((bool) $isActive)>
                            <label for="pesan_{{ $loop->index }}_active" class="form-check-label">
                                Aktifkan pengiriman pemberitahuan {{ strtolower($label) }}
                            </label>
                        </div>
                        @error('pesan.' . $status . '.is_active')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="col-12">
        <button type="submit" class="btn btn-primary" @disabled($users->isEmpty())>
            <i class="bi bi-send me-1"></i> Kirim
        </button>
    </div>
</form>

@push('scripts')
<script>
    $(function() {
        $('#penerima_user_ids').select2({
            theme: 'bootstrap-5',
            placeholder: 'Cari nama pegawai bawahan OTK...',
            closeOnSelect: false,
            width: '100%'
        });

        $('.js-jadwal-tipe').on('change', function() {
            var target = $(this).data('target');
            var timeInput = $(this).data('time-input');
            if ($(this).val() === 'terjadwal') {
                $(target).slideDown(150);
                if (!$(timeInput).val()) {
                    $(timeInput).val('08:00');
                }
            } else {
                $(target).slideUp(150);
                $(timeInput).val('');
            }
        });

        $('.js-btn-reset-time').on('click', function() {
            var selectTarget = $(this).data('target-select');
            $(selectTarget).val('langsung').trigger('change');
        });
    });
</script>
@endpush
