@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <div>
            <h3>Pengelolaan Pembimbing Tugas Akhir</h3>
                <p class="subtext">Atur pembimbing 1 & 2 untuk mahasiswa (otomatis difilter per prodi untuk Kaprodi).</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-danger">
                <i class="bi bi-x-circle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="card-surface">
            <div class="section-title">
                <i class="bi bi-funnel"></i>
                <span>Filter & Pencarian</span>
            </div>
            <div class="filter-box">
                <form method="GET" action="{{ route('admin.final-project.supervisors.index') }}" class="filter-form">
                    <input type="text" name="search" class="search-input"
                        placeholder="Cari nama/NIM/judul..."
                        value="{{ $search ?? '' }}">

                    <select name="status" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="unassigned" {{ ($status ?? '') === 'unassigned' ? 'selected' : '' }}>Belum Ada Pembimbing 1</option>
                        <option value="needs_second" {{ ($status ?? '') === 'needs_second' ? 'selected' : '' }}>Pembimbing 2 Kosong</option>
                        <option value="assigned" {{ ($status ?? '') === 'assigned' ? 'selected' : '' }}>Sudah Ditentukan</option>
                    </select>

                    @if(($role ?? '') === 'masteradmin')
                        <select name="prodi" class="filter-select">
                            <option value="">Semua Prodi</option>
                            @foreach(($prodis ?? collect()) as $p)
                                <option value="{{ $p }}" {{ request('prodi') === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    @endif

                    <button type="submit" class="search-btn">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <a href="{{ route('admin.final-project.supervisors.index') }}" class="btn-reset">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </form>
            </div>
        </div>

        @if($finalProjects->count() > 0)
            <div class="card-surface">
                <div class="section-title">
                    <i class="bi bi-table"></i>
                    <span>Daftar Mahasiswa</span>
                </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>NIM</th>
                                <th>Prodi</th>
                            <th>Judul TA</th>
                            <th>Pembimbing 1</th>
                            <th>Pembimbing 2</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($finalProjects as $finalProject)
                            <tr>
                                <td>{{ $finalProject->student->nama_lengkap }}</td>
                                <td>{{ $finalProject->student->nim }}</td>
                                    <td>{{ $finalProject->student->program_studi ?? '-' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit((string) ($finalProject->title ?? ''), 50) }}</td>
                                <td>{{ $finalProject->supervisor1->name ?? '-' }}</td>
                                <td>{{ $finalProject->supervisor2->name ?? '-' }}</td>
                                <td>
                                        <button
                                            type="button"
                                            class="btn-edit"
                                            data-fp-id="{{ $finalProject->id }}"
                                            data-student-name="{{ $finalProject->student->nama_lengkap }}"
                                            data-nim="{{ $finalProject->student->nim }}"
                                            data-prodi="{{ $finalProject->student->program_studi }}"
                                            data-title="{{ $finalProject->title }}"
                                            data-super1="{{ $finalProject->supervisor_1_id }}"
                                            data-super2="{{ $finalProject->supervisor_2_id }}"
                                            onclick="openAssignModal(this)"
                                        >
                                            <i class="bi bi-person-check"></i> Atur Pembimbing
                                        </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $finalProjects->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Tidak ada data Tugas Akhir</p>
            </div>
        @endif
    </div>

    <!-- Modal Assign Pembimbing -->
    <div id="assignModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i class="bi bi-person-check"></i> Atur Pembimbing</h4>
                <button type="button" class="btn-close-modal" onclick="closeAssignModal()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="student-card">
                    <div class="student-main">
                        <strong id="mStudentName">-</strong>
                        <span class="muted" id="mStudentMeta">-</span>
                    </div>
                    <div class="student-title" id="mStudentTitle">-</div>
                </div>

                <form id="assignForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-row">
                        <div class="form-group">
                            <label for="mSupervisor1">Pembimbing 1 <span class="req">*</span></label>
                            <select name="supervisor_1_id" id="mSupervisor1" class="form-control" required></select>
                        </div>

                        <div class="form-group">
                            <label for="mSupervisor2">Pembimbing 2 (Opsional)</label>
                            <select name="supervisor_2_id" id="mSupervisor2" class="form-control"></select>
                        </div>
                    </div>

                    <div class="modal-hint">
                        <i class="bi bi-info-circle"></i>
                        Pembimbing akan otomatis difilter sesuai <strong>prodi mahasiswa</strong>.
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeAssignModal()">Batal</button>
                        <button type="submit" class="btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    .subtext {
        margin: 6px 0 0;
        color: #777;
        font-size: 13px;
        font-weight: 500;
    }

    .card-surface {
        background: white;
        border-radius: 18px;
        padding: 18px;
        box-shadow: var(--shadow);
        border: 1px solid rgba(255, 152, 0, 0.08);
        margin-top: 16px;
    }

    .section-title {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        color: #333;
        font-size: 14px;
        margin-bottom: 12px;
    }

    .section-title i {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.18), rgba(255, 179, 71, 0.10));
        color: var(--primary-orange);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .alert-danger {
        background: #FFEBEE;
        color: #C62828;
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-box {
        background: transparent;
        border: none;
        padding: 0;
    }

    .filter-form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-input {
        flex: 1;
        min-width: 220px;
        padding: 12px 15px;
        border: 2px solid #E0E0E0;
        border-radius: 10px;
        font-size: 14px;
        background: white;
    }

    .filter-select {
        padding: 12px 15px;
        border: 2px solid #E0E0E0;
        border-radius: 10px;
        font-size: 14px;
        min-width: 190px;
        background: white;
    }

    .search-btn {
        padding: 12px 20px;
        background: var(--primary-orange);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-reset {
        padding: 12px 16px;
        background: #E0E0E0;
        color: #555;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #F0F0F0;
        margin-top: 8px;
    }

    .data-table th,
    .data-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #E0E0E0;
    }

    .data-table th {
        background: linear-gradient(135deg, #FFF3E0, #FFFBF0);
        font-weight: 600;
        color: #333;
    }

    .data-table tr:hover {
        background: #FFFBF0;
    }

    .btn-edit {
        padding: 10px 14px;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        border-radius: 10px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-edit:hover {
        background: #FF7043;
        transform: translateY(-2px);
    }

    .pagination-wrapper {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* Custom Pagination Styling - Aggressive Override */
    .pagination-wrapper .pagination {
        display: flex !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        align-items: center !important;
        border: none !important;
        background: transparent !important;
    }

    .pagination-wrapper .pagination .page-item {
        margin: 0 !important;
        list-style: none !important;
        display: inline-block !important;
    }

    .pagination-wrapper .pagination .page-link {
        padding: 10px 16px !important;
        border: 2px solid #E0E0E0 !important;
        border-radius: 10px !important;
        color: #666 !important;
        text-decoration: none !important;
        background: white !important;
        transition: all 0.3s !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        min-width: 44px !important;
        text-align: center !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1.5 !important;
        position: relative !important;
        margin: 0 !important;
        margin-left: 0 !important;
    }

    .pagination-wrapper .pagination .page-link:hover {
        background: var(--primary-orange) !important;
        color: white !important;
        border-color: var(--primary-orange) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3) !important;
        z-index: 1 !important;
    }

    .pagination-wrapper .pagination .page-item.active .page-link {
        background: var(--primary-orange) !important;
        color: white !important;
        border-color: var(--primary-orange) !important;
        font-weight: 600 !important;
        z-index: 2 !important;
    }

    .pagination-wrapper .pagination .page-item.disabled .page-link {
        background: #F5F5F5 !important;
        color: #999 !important;
        border-color: #E0E0E0 !important;
        cursor: not-allowed !important;
        opacity: 0.6 !important;
        pointer-events: none !important;
    }

    .pagination-wrapper .pagination .page-item.disabled .page-link:hover {
        background: #F5F5F5 !important;
        color: #999 !important;
        border-color: #E0E0E0 !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .pagination-wrapper .pagination .page-link::before,
    .pagination-wrapper .pagination .page-link::after {
        display: none !important;
        content: none !important;
    }

    .pagination-wrapper .pagination .page-link svg,
    .pagination-wrapper .pagination .page-link i {
        display: none !important;
    }

    .pagination-wrapper .pagination .page-item:first-child .page-link {
        margin-left: 0 !important;
        border-top-left-radius: 10px !important;
        border-bottom-left-radius: 10px !important;
        font-size: 14px !important;
    }

    .pagination-wrapper .pagination .page-item:last-child .page-link {
        margin-right: 0 !important;
        border-top-right-radius: 10px !important;
        border-bottom-right-radius: 10px !important;
        font-size: 14px !important;
    }

    .pagination-wrapper .pagination .page-link span {
        display: inline !important;
        font-size: 14px !important;
    }

    .pagination-wrapper .pagination .page-link[aria-label*="Previous"],
    .pagination-wrapper .pagination .page-link[aria-label*="Next"] {
        font-size: 14px !important;
        padding: 10px 16px !important;
    }

    .pagination-wrapper {
        overflow: visible !important;
        width: 100% !important;
        position: relative !important;
    }

    .pagination-wrapper .pagination {
        max-width: 100% !important;
        overflow: visible !important;
        position: relative !important;
    }

    /* Modal */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background: white;
        border-radius: 18px;
        width: 92%;
        max-width: 720px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.25);
        overflow: hidden;
        animation: modalUp 0.25s ease;
    }

    @keyframes modalUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-header {
        padding: 18px 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #F2F2F2;
        background: linear-gradient(135deg, #FFF3E0, #FFFBF0);
    }

    .modal-header h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #333;
    }

    .btn-close-modal {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: none;
        background: rgba(0,0,0,0.06);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #333;
        font-size: 18px;
    }

    .modal-body {
        padding: 18px 22px 22px;
    }

    .student-card {
        border: 2px solid #F0F0F0;
        border-radius: 14px;
        padding: 14px;
        background: #FAFAFA;
        margin-bottom: 16px;
    }

    .student-main {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: baseline;
        margin-bottom: 8px;
    }

    .muted {
        color: #777;
        font-size: 13px;
        font-weight: 600;
    }

    .student-title {
        color: #333;
        font-size: 13px;
        line-height: 1.5;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #333;
    }

    .req { color: #E53935; }

    .form-control {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid #E0E0E0;
        border-radius: 12px;
        font-size: 14px;
        background: white;
    }

    .modal-hint {
        margin-top: 14px;
        background: #E3F2FD;
        color: #1565C0;
        border: 1px solid #BBDEFB;
        padding: 12px 14px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        font-weight: 600;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 16px;
    }

    .btn-primary, .btn-secondary {
        padding: 12px 18px;
        border-radius: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
    }

    .btn-secondary {
        background: #E0E0E0;
        color: #555;
    }

    @media (max-width: 768px) {
        .form-row { grid-template-columns: 1fr; }
        .btn-edit { width: 100%; justify-content: center; }
        .filter-select { min-width: 160px; }
        .card-surface { padding: 14px; }
    }
</style>
@endpush

@push('scripts')
<script>
const lecturersByProdi = @json($lecturersByProdi ?? []);

function buildOptions(selectEl, items, selectedId, includeEmptyLabel) {
    selectEl.innerHTML = '';
    if (includeEmptyLabel) {
        const optEmpty = document.createElement('option');
        optEmpty.value = '';
        optEmpty.textContent = includeEmptyLabel;
        selectEl.appendChild(optEmpty);
    }
    items.forEach((it) => {
        const opt = document.createElement('option');
        opt.value = String(it.id);
        opt.textContent = it.name;
        if (selectedId && String(selectedId) === String(it.id)) {
            opt.selected = true;
        }
        selectEl.appendChild(opt);
    });
}

function openAssignModal(btn) {
    const fpId = btn.dataset.fpId;
    const studentName = btn.dataset.studentName || '-';
    const nim = btn.dataset.nim || '-';
    const prodi = btn.dataset.prodi || 'Unknown';
    const title = btn.dataset.title || '-';
    const super1 = btn.dataset.super1 || '';
    const super2 = btn.dataset.super2 || '';

    document.getElementById('mStudentName').textContent = studentName;
    document.getElementById('mStudentMeta').textContent = `${nim} • ${prodi}`;
    document.getElementById('mStudentTitle').textContent = title;

    const form = document.getElementById('assignForm');
    form.action = `/admin/final-project/supervisors/${fpId}`;

    const list = lecturersByProdi[prodi] || lecturersByProdi['Unknown'] || [];
    const sel1 = document.getElementById('mSupervisor1');
    const sel2 = document.getElementById('mSupervisor2');

    buildOptions(sel1, list, super1, 'Pilih Pembimbing 1');
    buildOptions(sel2, list, super2, 'Tidak ada Pembimbing 2');

    document.getElementById('assignModal').style.display = 'flex';
}

function closeAssignModal() {
    document.getElementById('assignModal').style.display = 'none';
}

window.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAssignModal();
});
</script>
@endpush
