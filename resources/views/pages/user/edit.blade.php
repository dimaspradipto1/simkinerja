@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Edit User</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.index') }}">User</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Edit User</h5>

                    <form action="{{ route('user.update', $user->id) }}" method="POST" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-md-6">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Masukkan nama lengkap">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Masukkan alamat email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="roles" class="form-label">Role Akses <span class="text-danger">*</span> <small class="text-muted">(Boleh pilih lebih dari satu)</small></label>
                            <select class="form-select select2 @error('roles') is-invalid @enderror" id="roles" name="roles[]" multiple="multiple">
                                @php
                                    $roleGroups = [
                                        'Level Administrator' => [
                                            'super admin' => 'Super Admin',
                                            'admin' => 'Admin (IT / Pengelola)',
                                        ],
                                        'Level Pimpinan' => [
                                            'pimpinan rektorat' => 'Pimpinan Rektorat (Rektor / Warek)',
                                            'pimpinan unit' => 'Pimpinan Unit (Dekan / Ka. Biro / Ka. Lembaga / Ka. Perpus)',
                                            'pimpinan sub unit' => 'Pimpinan Sub Unit (Wadek / Kaprodi / Sekprodi / Kabid / Ka. Lab)',
                                        ],
                                        'Level Pelaksana' => [
                                            'staff' => 'Staff (Pelaksana Administrasi / TU / Laboran / Kasir / Pustakawan)',
                                        ],
                                        'Penugasan Khusus' => [
                                            'kepanitiaan' => 'Kepanitiaan',
                                            'koordinator panitia' => 'Koordinator Panitia',
                                            'tim evaluasi' => 'Tim Evaluasi (Monev / LPMI)',
                                        ],
                                    ];
                                    $selectedRoles = old('roles', $user->roles ?? '');
                                    if (is_string($selectedRoles)) {
                                        $selectedRoles = array_map('trim', explode(',', strtolower($selectedRoles)));
                                    } elseif (is_array($selectedRoles)) {
                                        $selectedRoles = array_map(function($r) { return strtolower(trim((string)$r)); }, $selectedRoles);
                                    }
                                @endphp
                                @foreach($roleGroups as $groupLabel => $roles)
                                    <optgroup label="{{ $groupLabel }}">
                                        @foreach($roles as $value => $label)
                                            <option value="{{ $value }}" {{ in_array($value, (array)$selectedRoles) ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('roles')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="nidn" class="form-label">NUP</label>
                            <input type="text" class="form-control @error('nidn') is-invalid @enderror" id="nidn" name="nidn" value="{{ old('nidn', $user->nidn) }}" placeholder="Masukkan NUP (Nomor Urut Pegawai/Pendidik)">
                            @error('nidn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="unit" class="form-label">Unit</label>
                            <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit', $user->unit) }}" placeholder="Masukkan Unit">
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jabatan" class="form-label">Jabatan</label>
                            <input type="text" class="form-control @error('jabatan') is-invalid @enderror" id="jabatan" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" placeholder="Masukkan Jabatan">
                            @error('jabatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Dynamic Jabatan Kepanitiaan Section (Nullable) -->
                        <div class="col-12 mt-2">
                            <div class="card border rounded-3 shadow-none" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <div>
                                            <label class="form-label fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 0.92rem;">
                                                <i class="bi bi-people-fill text-success"></i> Jabatan Kepanitiaan <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.72rem;">Opsional / Nullable</span>
                                            </label>
                                            <div class="text-muted small" style="font-size: 0.8rem;">Tambahkan jabatan kepanitiaan jika pegawai terlibat dalam kepanitiaan kampus. Kosongkan jika tidak ada.</div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold d-inline-flex align-items-center" id="btn-add-kepanitiaan">
                                            <i class="bi bi-plus-circle me-1.5"></i> Tambah Kepanitiaan
                                        </button>
                                    </div>

                                    <datalist id="suggested-kepanitiaan-list">
                                        @if(isset($suggestedKepanitiaan))
                                            @foreach($suggestedKepanitiaan as $sk)
                                                <option value="{{ $sk }}">
                                            @endforeach
                                        @endif
                                    </datalist>

                                    <div id="kepanitiaan-container" class="d-flex flex-column gap-2 mt-2">
                                        @php
                                            $currentItems = old('jabatan_kepanitiaan', $user->daftar_jabatan_kepanitiaan ?? []);
                                        @endphp
                                        @if(!empty($currentItems) && is_array($currentItems))
                                            @foreach($currentItems as $idx => $item)
                                                <div class="kepanitiaan-row row g-2 align-items-center bg-white p-2 rounded-3 border">
                                                    <div class="col-md-6">
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-light text-muted"><i class="bi bi-bookmark"></i></span>
                                                            <input type="text" name="jabatan_kepanitiaan[{{ $idx }}][nama]" class="form-control" list="suggested-kepanitiaan-list" value="{{ $item['nama'] ?? '' }}" placeholder="Nama Kepanitiaan (Contoh: PKKMB, Akreditasi, Wisuda)">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-light text-muted"><i class="bi bi-person-badge"></i></span>
                                                            <input type="text" name="jabatan_kepanitiaan[{{ $idx }}][jabatan]" class="form-control" value="{{ $item['jabatan'] ?? '' }}" placeholder="Jabatan (Contoh: Ketua, Sekretaris, Anggota)">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1 text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-kepanitiaan rounded-pill px-2 py-1" title="Hapus Baris">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div id="empty-kepanitiaan-notice" class="text-center py-3 text-muted small border border-dashed rounded-3 bg-white mt-1 {{ !empty($currentItems) ? 'd-none' : '' }}">
                                        <i class="bi bi-info-circle me-1 text-info"></i> Belum ada jabatan kepanitiaan yang ditambahkan. Klik <strong>Tambah Kepanitiaan</strong> jika pegawai ini memiliki tugas kepanitiaan.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label for="status" class="form-label">Status Pegawai <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('status') is-invalid @enderror" id="status" name="status" value="{{ old('status', $user->status) }}" placeholder="Contoh: Aktif">
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="is_active" class="form-label">Status Akun <span class="text-danger">*</span></label>
                            <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', $user->is_active) == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active', $user->is_active) == '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Perbarui</button>
                            <a href="{{ route('user.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('#roles').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Pilih Role Akses (Bisa pilih lebih dari satu) --',
                allowClear: true,
                width: '100%',
                closeOnSelect: false
            });
        }

        // Dynamic Kepanitiaan Repeater
        let kepanitiaanIdx = $('#kepanitiaan-container .kepanitiaan-row').length;

        $('#btn-add-kepanitiaan').on('click', function() {
            $('#empty-kepanitiaan-notice').addClass('d-none');
            const rowHtml = `
                <div class="kepanitiaan-row row g-2 align-items-center bg-white p-2 rounded-3 border shadow-sm">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-bookmark"></i></span>
                            <input type="text" name="jabatan_kepanitiaan[${kepanitiaanIdx}][nama]" class="form-control" list="suggested-kepanitiaan-list" placeholder="Nama Kepanitiaan (Contoh: PKKMB, Akreditasi, Wisuda)">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-person-badge"></i></span>
                            <input type="text" name="jabatan_kepanitiaan[${kepanitiaanIdx}][jabatan]" class="form-control" placeholder="Jabatan (Contoh: Ketua, Sekretaris, Anggota)">
                        </div>
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-kepanitiaan rounded-pill px-2 py-1" title="Hapus Baris">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#kepanitiaan-container').append(rowHtml);
            kepanitiaanIdx++;
        });

        $(document).on('click', '.btn-remove-kepanitiaan', function() {
            $(this).closest('.kepanitiaan-row').remove();
            if ($('#kepanitiaan-container .kepanitiaan-row').length === 0) {
                $('#empty-kepanitiaan-notice').removeClass('d-none');
            }
        });
    });
</script>
@endpush
