@extends('layouts.dashboard.template')

@section('page-style-class', 'menu-page-hierarchy-legacy')
@section('content')
<div class="pagetitle">
    <h1><i class="bi bi-diagram-3-fill me-2"></i>Struktur OTK</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Pengaturan Sistem</li>
            <li class="breadcrumb-item active">Struktur OTK</li>
        </ol>
    </nav>
</div>



<section class="section">
    <div class="page-header-banner">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
                <h2><i class="bi bi-diagram-3-fill me-2"></i>Pengaturan Struktur OTK (2024-2029)</h2>
                <p>Mengatur hierarki resmi Universitas Ibnu Sina: atasan langsung mengevaluasi dan memantau bawahannya sesuai bidang.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <span class="stat-pill"><i class="bi bi-people-fill me-1"></i>{{ $allUsers->count() }} Pegawai</span>
                <span class="stat-pill"><i class="bi bi-person-check me-1"></i>{{ $pimpinan->count() }} Pimpinan</span>
                @if($unassigned->count() > 0)
                <span class="stat-pill" style="background:rgba(251,191,36,.25);border-color:rgba(251,191,36,.5);">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $unassigned->count() }} Belum Diatur
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="summary-bar">
        <div class="summary-stat">
            <div class="num">{{ $allUsers->whereNotNull('supervisor_id')->count() }}</div>
            <div class="lbl">Sudah Punya Atasan</div>
        </div>
        <div class="summary-divider"></div>
        <div class="summary-stat">
            <div class="num" style="color:#d97706;">{{ $unassigned->count() }}</div>
            <div class="lbl">Belum Diatur</div>
        </div>
        <div class="summary-divider"></div>
        <div class="summary-stat">
            <div class="num">{{ $pimpinan->count() }}</div>
            <div class="lbl">Total Pimpinan</div>
        </div>
        
        <div class="ms-auto d-flex gap-2 flex-wrap">
            <button class="btn btn-sm btn-success fw-bold text-white shadow-sm" onclick="confirmSyncOtk()" style="background:#15432D;border-color:#15432D;">
                <i class="bi bi-arrow-repeat me-1"></i>Terapkan Standar OTK UIS
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="confirmResetAll()">
                <i class="bi bi-trash3 me-1"></i>Reset Semua
            </button>
            <a href="{{ route('user.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-people me-1"></i>Kelola Pengguna
            </a>
        </div>
    </div>

    <!-- FILTER TAB KATEGORI OTK -->
    <ul class="nav nav-tabs nav-tabs-custom" id="otkCategoryTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active d-inline-flex align-items-center gap-2" onclick="filterCategory('all', this)">
                <i class="bi bi-grid-fill"></i><span>Semua Struktur</span>
                <span class="badge-tab">{{ $hierarchyTree->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link d-inline-flex align-items-center gap-2" onclick="filterCategory('rektorat', this)">
                <i class="bi bi-bank2"></i><span>Rektorat & Biro</span>
                <span class="badge-tab">{{ $hierarchyTree->where('category', 'rektorat')->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link d-inline-flex align-items-center gap-2" onclick="filterCategory('lembaga', this)">
                <i class="bi bi-shield-check"></i><span>Lembaga (LPPM & LPMI)</span>
                <span class="badge-tab">{{ $hierarchyTree->where('category', 'lembaga')->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link d-inline-flex align-items-center gap-2" onclick="filterCategory('feb', this)">
                <i class="bi bi-briefcase-fill"></i><span>Fakultas Ekonomi & Bisnis</span>
                <span class="badge-tab">{{ $hierarchyTree->where('category', 'feb')->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link d-inline-flex align-items-center gap-2" onclick="filterCategory('fst', this)">
                <i class="bi bi-cpu-fill"></i><span>Fakultas Sains & Teknologi</span>
                <span class="badge-tab">{{ $hierarchyTree->where('category', 'fst')->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link d-inline-flex align-items-center gap-2" onclick="filterCategory('fikes', this)">
                <i class="bi bi-heart-pulse-fill"></i><span>Fakultas Ilmu Kesehatan</span>
                <span class="badge-tab">{{ $hierarchyTree->where('category', 'fikes')->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="row g-4">
        <!-- KOLOM KIRI: TREE PIMPINAN & BAWAHAN -->
        <div class="col-lg-8">
            <div class="search-box-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="treeSearchInput" placeholder="Cari nama pimpinan, bawahan, atau jabatan..." oninput="handleSearch()">
            </div>

            <div id="treeContainer">
                @forelse($hierarchyTree as $node)
                @php 
                    $leader = $node['leader']; 
                    $subs = $node['subordinates']; 
                    $category = $node['category'] ?? 'rektorat';
                @endphp
                <div class="leader-card" id="card-leader-{{ $leader->id }}" data-category="{{ $category }}" data-search="{{ strtolower($leader->name . ' ' . $leader->jabatan . ' ' . $subs->pluck('name')->implode(' ') . ' ' . $subs->pluck('jabatan')->implode(' ')) }}">
                    <div class="leader-header" onclick="toggleCard({{ $leader->id }})">
                        <div class="leader-avatar">{{ strtoupper(substr($leader->name, 0, 1)) }}</div>
                        <div class="leader-info">
                            <div class="leader-name">{{ $leader->name }}</div>
                            <div class="leader-jabatan">{{ $leader->jabatan ?? 'Pimpinan' }}</div>
                        </div>
                        @if(!empty($leader->unit) && $leader->unit !== '-')
                        <span class="leader-unit-badge">{{ $leader->unit }}</span>
                        @endif
                        <span class="leader-count" id="count-{{ $leader->id }}">{{ $subs->count() }}</span>
                        <i class="bi bi-chevron-down leader-toggle" id="toggle-icon-{{ $leader->id }}"></i>
                    </div>
                    <div class="leader-body" id="body-{{ $leader->id }}">
                        <div id="sub-list-{{ $leader->id }}">
                            @forelse($subs as $sub)
                            <div class="sub-item" id="sub-item-{{ $sub->id }}" data-search="{{ strtolower($sub->name . ' ' . $sub->jabatan) }}">
                                <div class="sub-avatar">{{ strtoupper(substr($sub->name, 0, 1)) }}</div>
                                <div class="sub-info">
                                    <div class="sub-name">{{ $sub->name }}</div>
                                    <div class="sub-jabatan">{{ $sub->jabatan ?? '-' }}</div>
                                    @if(!empty($sub->unit) && $sub->unit !== '-')
                                    <div class="sub-unit">{{ $sub->unit }}</div>
                                    @endif
                                </div>
                                <button class="btn-remove-sub" onclick="removeSub({{ $sub->id }}, '{{ addslashes($sub->name) }}', {{ $leader->id }})">
                                    <i class="bi bi-x-lg me-1"></i>Lepas
                                </button>
                            </div>
                            @empty
                            <div class="text-center py-3 text-muted" id="empty-msg-{{ $leader->id }}">
                                <i class="bi bi-person-dash fs-4 d-block mb-1 opacity-50"></i>
                                <small>Belum ada bawahan langsung</small>
                            </div>
                            @endforelse
                        </div>
                        <div class="add-sub-area" onclick="openAssignModal({{ $leader->id }}, '{{ addslashes($leader->name) }}')">
                            <i class="bi bi-plus-circle-fill text-success fs-5"></i>
                            <span class="add-sub-text">Tambah Bawahan untuk {{ $leader->name }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-diagram-3 fs-1 d-block mb-2 opacity-30"></i>
                    <p>Belum ada pimpinan terdaftar.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- KOLOM KANAN: UNASSIGNED & PANDUAN -->
        <div class="col-lg-4">
            <h6 class="fw-bold text-dark mb-3">
                <i class="bi bi-exclamation-triangle me-2 text-warning"></i>Belum Diatur ({{ $unassigned->count() }})
            </h6>
            <div class="unassigned-card mb-4">
                <div class="unassigned-header">
                    <i class="bi bi-person-question-fill fs-5"></i>
                    <div>
                        <div class="fw-bold" style="font-size:.92rem;">Pegawai Belum Memiliki Atasan</div>
                        <div style="font-size:.76rem;opacity:.85;">Klik Assign untuk menempatkan</div>
                    </div>
                </div>
                <div id="unassigned-list" style="max-height:400px;overflow-y:auto;">
                    @forelse($unassigned as $u)
                    <div class="unassigned-item" id="unassigned-{{ $u->id }}">
                        <div class="unassigned-avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                        <div class="sub-info" style="flex:1;min-width:0;">
                            <div class="sub-name">{{ $u->name }}</div>
                            <div class="sub-jabatan">{{ $u->jabatan ?? '-' }}</div>
                            <div class="sub-unit">{{ $u->unit ?? '-' }}</div>
                        </div>
                        <button class="btn btn-sm" 
                            style="background:#fef3c7;border:1px solid #fde68a;color:#92400e;font-size:.72rem;border-radius:8px;white-space:nowrap;"
                            onclick="openAssignForUser({{ $u->id }}, '{{ addslashes($u->name) }}')">
                            <i class="bi bi-person-plus me-1"></i>Assign
                        </button>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                        <small class="fw-semibold">Semua pegawai sudah memiliki atasan sesuai OTK!</small>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- CARD PANDUAN OTK -->
            <div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden;">
                <div class="card-header border-0" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);color:white;padding:12px 16px;">
                    <i class="bi bi-journal-text me-2"></i><strong>Struktur Organisasi (OTK)</strong>
                </div>
                <div class="card-body p-3">
                    <ul class="list-unstyled mb-0" style="font-size:.8rem;color:#374151;">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>LPTI:</strong> Khusus membawahi Divisi TI, Programmer, & Jaringan. Pegawai fakultas (seperti TU FST) tidak dicampur ke LPTI.</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Fakultas:</strong> Dekan memegang kendali atas Wadek, Kaprodi, Sekprodi, UPPM, UPMI, TU, & Laboran fakultas masing-masing.</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Lembaga:</strong> LPPM & LPMI berada langsung di bawah Rektorat.</li>
                        <li class="mb-0"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Otomatisasi:</strong> Klik tombol <em>Terapkan Standar OTK</em> di atas kapan saja untuk menyelaraskan seluruh pegawai.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Modal Assign Bawahan --}}
<div class="modal fade modal-assign" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="assignModalLabel"><i class="bi bi-person-plus-fill me-2"></i>Tambah Bawahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <p class="text-muted mb-3" id="assignModalDesc" style="font-size:.88rem;"></p>
                <label class="form-label fw-semibold small">Pilih Pegawai (bisa lebih dari satu)</label>
                <select id="assignUserSelect" class="form-select" multiple style="width:100%;">
                    @foreach($allUsers as $u)
                        @if(!$u->isPimpinanRektorat() && !$u->isAdmin())
                        <option value="{{ $u->id }}" data-supervisor="{{ $u->supervisor_id }}">
                            {{ $u->name }}{{ $u->jabatan ? ' — '.$u->jabatan : '' }}{{ $u->unit ? ' ('.$u->unit.')' : '' }}
                        </option>
                        @endif
                    @endforeach
                </select>
                <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Pegawai yang sudah punya atasan lain akan dipindahkan ke pimpinan ini.</div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success px-4" id="btnConfirmAssign" onclick="confirmAssign()">
                    <i class="bi bi-check-circle me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Assign Supervisor --}}
<div class="modal fade modal-assign" id="assignSupervisorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="bi bi-person-lines-fill me-2"></i>Atur Atasan Langsung</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <p class="text-muted mb-3" id="supervisorModalDesc" style="font-size:.88rem;"></p>
                <label class="form-label fw-semibold small">Pilih Atasan</label>
                <select id="supervisorSelect" class="form-select" style="width:100%;">
                    <option value="">— Tidak Ada Atasan —</option>
                    @foreach($pimpinan as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}{{ $p->jabatan ? ' — '.$p->jabatan : '' }}{{ $p->unit ? ' ('.$p->unit.')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning px-4" onclick="confirmSupervisorAssign()">
                    <i class="bi bi-check-circle me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<div class="toast-custom" id="toastArea"></div>
@endsection

@push('scripts')
<script>
let currentLeaderId = null, currentLeaderName = '', currentUserId = null, currentUserName = '';
const csrfToken = '{{ csrf_token() }}';
let activeCategory = 'all';

function showToast(msg, type = 'success') {
    const c = { success: '#15432D', danger: '#dc2626', warning: '#d97706' };
    const ic = { success: 'check-circle-fill', danger: 'x-circle-fill', warning: 'exclamation-triangle-fill' };
    const id = 'toast-' + Date.now();
    document.getElementById('toastArea').insertAdjacentHTML('beforeend',
        `<div id="${id}" style="background:white;border-radius:12px;padding:13px 18px;box-shadow:0 8px 32px rgba(0,0,0,.15);border-left:4px solid ${c[type]};display:flex;align-items:center;gap:12px;margin-top:8px;animation:fadeInUp .3s ease;">
            <i class="bi bi-${ic[type]}" style="color:${c[type]};font-size:1.1rem;flex-shrink:0;"></i>
            <span style="font-size:.86rem;color:#1f2937;font-weight:500;">${msg}</span>
        </div>`);
    setTimeout(() => { const el = document.getElementById(id); if (el) { el.style.opacity = '0'; el.style.transition = 'opacity .4s'; } }, 3000);
    setTimeout(() => { const el = document.getElementById(id); if (el) el.remove(); }, 3500);
}

function toggleCard(id) {
    const body = document.getElementById('body-' + id);
    const icon = document.getElementById('toggle-icon-' + id);
    const hidden = body.style.display === 'none';
    body.style.display = hidden ? 'block' : 'none';
    icon.classList.toggle('collapsed', !hidden);
}

function filterCategory(category, btnElement) {
    activeCategory = category;
    document.querySelectorAll('#otkCategoryTabs .nav-link').forEach(el => el.classList.remove('active'));
    btnElement.classList.add('active');
    handleSearch();
}

function handleSearch() {
    const q = (document.getElementById('treeSearchInput').value || '').trim().toLowerCase();
    const cards = document.querySelectorAll('.leader-card');

    cards.forEach(card => {
        const cat = card.getAttribute('data-category');
        const matchesCategory = (activeCategory === 'all' || cat === activeCategory);
        const searchData = card.getAttribute('data-search') || '';
        const matchesSearch = !q || searchData.includes(q);

        if (matchesCategory && matchesSearch) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function confirmSyncOtk() {
    if (!confirm('Terapkan struktur organisasi dan tata kerja (OTK) UIS 2024-2029 secara otomatis?\n\nIni akan menghubungkan pimpinan dan staf sesuai SK resmi (Rektorat, Biro, LPPM, LPMI, FEB, FST, dan FIKes).')) {
        return;
    }

    showToast('Sedang menerapkan struktur OTK...', 'warning');

    fetch('{{ route("hirarki.sync-otk") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast(d.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(d.message || 'Gagal sinkronisasi.', 'danger');
        }
    })
    .catch(() => showToast('Gagal terhubung ke server.', 'danger'));
}

function openAssignModal(leaderId, leaderName) {
    currentLeaderId = leaderId; currentLeaderName = leaderName;
    document.getElementById('assignModalDesc').textContent = `Pilih pegawai yang akan menjadi bawahan langsung dari ${leaderName}.`;
    document.getElementById('assignModalLabel').innerHTML = `<i class="bi bi-person-plus-fill me-2"></i>Tambah Bawahan — ${leaderName}`;
    if (window._assignS2) { $('#assignUserSelect').val(null).trigger('change'); }
    new bootstrap.Modal(document.getElementById('assignModal')).show();
    setTimeout(() => {
        if (!window._assignS2) {
            window._assignS2 = true;
            $('#assignUserSelect').select2({
                dropdownParent: $('#assignModal'),
                placeholder: 'Cari nama / jabatan / unit...',
                allowClear: true,
                width: '100%'
            });
        }
    }, 300);
}

function confirmAssign() {
    const ids = $('#assignUserSelect').val();
    if (!ids || !ids.length) {
        showToast('Pilih minimal satu pegawai.', 'warning');
        return;
    }
    const btn = document.getElementById('btnConfirmAssign');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

    fetch('{{ route("hirarki.bulk-assign") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ supervisor_id: currentLeaderId, subordinate_ids: ids }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast(d.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(d.message || 'Terjadi kesalahan.', 'danger');
        }
    })
    .catch(() => showToast('Gagal terhubung.', 'danger'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Simpan';
    });
}

function removeSub(userId, userName, leaderId) {
    if (!confirm(`Lepas ${userName} dari struktur OTK ini?`)) return;

    fetch(`/hirarki/${userId}/update`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ supervisor_id: null }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast(d.message, 'success');
            const el = document.getElementById('sub-item-' + userId);
            if (el) el.remove();
            const c = document.getElementById('count-' + leaderId);
            if (c) c.textContent = Math.max(0, parseInt(c.textContent) - 1);
        } else {
            showToast(d.message || 'Gagal melepas.', 'danger');
        }
    })
    .catch(() => showToast('Gagal terhubung.', 'danger'));
}

function openAssignForUser(userId, userName) {
    currentUserId = userId; currentUserName = userName;
    document.getElementById('supervisorModalDesc').textContent = `Pilih atasan langsung untuk: ${userName}`;
    new bootstrap.Modal(document.getElementById('assignSupervisorModal')).show();
    setTimeout(() => {
        if (!window._supS2) {
            window._supS2 = true;
            $('#supervisorSelect').select2({
                dropdownParent: $('#assignSupervisorModal'),
                placeholder: 'Pilih atasan...',
                width: '100%'
            });
        }
    }, 300);
}

function confirmSupervisorAssign() {
    const supId = $('#supervisorSelect').val();
    fetch(`/hirarki/${currentUserId}/update`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ supervisor_id: supId || null }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast(d.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('assignSupervisorModal')).hide();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(d.message || 'Gagal menyimpan.', 'danger');
        }
    })
    .catch(() => showToast('Gagal terhubung.', 'danger'));
}

function confirmResetAll() {
    if (!confirm('Ini akan MENGHAPUS semua pengaturan OTK!\n\nLanjutkan?')) return;

    fetch('{{ route("hirarki.reset-all") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast(d.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(d.message || 'Gagal reset.', 'danger');
        }
    })
    .catch(() => showToast('Gagal terhubung.', 'danger'));
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[id^="body-"]').forEach(el => el.style.display = 'block');
});
</script>
@endpush
