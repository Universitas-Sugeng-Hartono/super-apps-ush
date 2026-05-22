@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3>Management Mahasiswa</h3>
            <div class="header-actions">
                <div class="toggle-all-wrapper">
                    <label class="toggle-label">Toggle Semua:</label>
                    <label class="toggle-switch">
                        <input type="checkbox" id="toggleAllEdit" onchange="toggleAllStudents()">
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-status" id="toggleAllStatus">Kunci</span>
                </div>
                <a href="{{ route('admin.management.students.create') }}" class="btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Mahasiswa
                </a>
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

        <!-- Import CSV -->
        <div class="import-card">
            <div class="import-left">
                <div class="import-title">
                    <i class="bi bi-upload"></i> Import Mahasiswa (CSV)
                </div>
                <div class="import-hint">
                    <a href="{{ route('admin.management.students.template') }}" class="import-link">
                        Download template
                    </a>
                    lalu upload CSV. Password default: <b>12345678</b> (bcrypt). Kolom password boleh dikosongkan.
                </div>
            </div>
            <div class="import-right">
                <form method="POST" action="{{ route('admin.management.students.import') }}" enctype="multipart/form-data" class="import-form">
                    @csrf
                    <input type="file" name="import_file" class="file-input" accept=".csv,text/csv" required>
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-cloud-arrow-up"></i> Import
                    </button>
                </form>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="search-box">
            <form method="GET" action="{{ route('admin.management.students.index') }}" class="search-form">
                <input type="text" name="search" class="search-input" 
                       placeholder="Cari nama, NIM, email, atau angkatan..." 
                       value="{{ $search }}">
                <select name="program_studi" class="filter-select">
                    <option value="">Semua Program Studi</option>
                    @foreach($studyPrograms as $prodi)
                        <option value="{{ $prodi->name }}" {{ $programStudi == $prodi->name ? 'selected' : '' }}>
                            {{ $prodi->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="search-btn">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>

        @if($students->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIM</th>
                            <th>Angkatan</th>
                            <th>Program Studi</th>
                            <th>Dosen PA</th>
                            <th>Status</th>
                            <th>Counseling</th>
                            <th>Edit Profil</th>
                            <th>Akses SKPI</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                                <td>
                                    <strong>{{ $student->nama_lengkap }}</strong>
                                </td>
                                <td class="font-monospace">{{ $student->nim }}</td>
                                <td>
                                    <span class="badge-year">{{ $student->angkatan }}</span>
                                </td>
                                <td>
                                    <span class="badge-prodi">{{ $student->program_studi }}</span>
                                </td>
                                <td>
                                    {{ $student->dosenPA->name ?? '-' }}
                                </td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($student->status_mahasiswa) }}">
                                        {{ $student->status_mahasiswa }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-count">{{ $student->counselings_count }}</span>
                                </td>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" 
                                               class="toggle-edit" 
                                               data-student-id="{{ $student->id }}"
                                               data-student-name="{{ $student->nama_lengkap }}"
                                               {{ $student->is_edited ? 'checked' : '' }}
                                               onchange="toggleStudentEdit({{ $student->id }}, '{{ $student->nama_lengkap }}', this.checked)">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="edit-status" id="status-{{ $student->id }}">
                                        {{ $student->is_edited ? 'Edit' : 'Kunci' }}
                                    </span>
                                </td>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" 
                                               class="toggle-skpi" 
                                               data-student-id="{{ $student->id }}"
                                               data-student-name="{{ $student->nama_lengkap }}"
                                               {{ $student->is_skpi_unlocked ? 'checked' : '' }}
                                               onchange="toggleStudentSkpi({{ $student->id }}, '{{ $student->nama_lengkap }}', this.checked)">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="edit-status" id="skpi-status-{{ $student->id }}">
                                        {{ $student->is_skpi_unlocked ? 'Buka' : 'Kunci' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.management.students.show', $student->id) }}" class="btn-view">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        <a href="{{ route('admin.management.students.edit', $student->id) }}" class="btn-edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="{{ route('admin.management.students.reset-password', $student->id) }}"
                                           class="btn-reset"
                                           onclick="return confirm('Yakin ingin mereset password mahasiswa ini ke 12345678?')">
                                            <i class="bi bi-arrow-counterclockwise"></i> Reset Password
                                        </a>
                                        <form action="{{ route('admin.management.students.destroy', $student->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini?')"
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $students->appends(['search' => $search, 'program_studi' => $programStudi])->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Tidak ada data mahasiswa</p>
            </div>
        @endif
    </div>
@endsection

@push('css')
<style>
    .content-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: var(--shadow);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #F5F5F5;
    }

    .card-header h3 {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 112, 67, 0.4);
    }

    .alert-success {
        background: #E8F5E9;
        color: #2E7D32;
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
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

    .import-card {
        background: linear-gradient(135deg, rgba(255,152,0,0.08), rgba(255,251,240,1));
        border: 1px solid rgba(255,152,0,0.18);
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .import-title {
        font-weight: 800;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .import-hint {
        margin-top: 6px;
        font-size: 12px;
        color: #666;
        font-weight: 500;
    }

    .import-link {
        font-weight: 800;
        color: var(--primary-orange);
        text-decoration: none;
        border-bottom: 1px dashed rgba(255,152,0,0.55);
    }

    .import-link:hover {
        filter: brightness(0.95);
    }

    .import-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .import-form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .file-input {
        padding: 10px 12px;
        border: 2px solid #E0E0E0;
        border-radius: 10px;
        background: white;
        font-size: 13px;
        max-width: 320px;
    }

    .search-box {
        margin-bottom: 20px;
    }

    .search-form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-input {
        flex: 1;
        min-width: 200px;
        padding: 12px 15px;
        border: 2px solid #E0E0E0;
        border-radius: 10px;
        font-size: 14px;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary-orange);
    }

    .filter-select {
        padding: 12px 15px;
        border: 2px solid #E0E0E0;
        border-radius: 10px;
        font-size: 14px;
        background: white;
        min-width: 180px;
        cursor: pointer;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary-orange);
    }

    .search-btn {
        padding: 12px 20px;
        background: var(--primary-orange);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 16px;
        white-space: nowrap;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .data-table th {
        padding: 12px;
        text-align: left;
        background: #F5F5F5;
        font-weight: 600;
        color: #333;
        font-size: 13px;
    }

    .data-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #E0E0E0;
    }

    .data-table tr:hover {
        background: #F9F9F9;
    }

    .font-monospace {
        font-family: 'Courier New', monospace;
        color: #666;
    }

    .badge-year {
        background: #E8F5E9;
        color: #2E7D32;
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-prodi {
        background: #E3F2FD;
        color: #1976D2;
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.status-aktif {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.status-cuti {
        background: #FFF3E0;
        color: #F57C00;
    }

    .status-badge.status-lulus {
        background: #E3F2FD;
        color: #1976D2;
    }

    .badge-count {
        background: #E8F5E9;
        color: #2E7D32;
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-view {
        padding: 6px 12px;
        background: #2196F3;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-view:hover {
        background: #1976D2;
        transform: translateY(-2px);
    }

    .btn-edit {
        padding: 6px 12px;
        background: var(--primary-orange);
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-edit:hover {
        background: #FF7043;
        transform: translateY(-2px);
    }

    .btn-delete {
        padding: 6px 12px;
        background: #F44336;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-delete:hover {
        background: #E53935;
        transform: translateY(-2px);
    }

    .btn-reset {
        padding: 6px 12px;
        background: #FFC107;
        color: #212121;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-reset:hover {
        background: #FFB300;
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

    /* Remove all pseudo-elements and icons */
    .pagination-wrapper .pagination .page-link::before,
    .pagination-wrapper .pagination .page-link::after {
        display: none !important;
        content: none !important;
    }

    /* Remove any SVG or icon content */
    .pagination-wrapper .pagination .page-link svg,
    .pagination-wrapper .pagination .page-link i {
        display: none !important;
    }

    /* Fix first and last child */
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

    /* Hide any span or text that might contain chevrons */
    .pagination-wrapper .pagination .page-link span {
        display: inline !important;
        font-size: 14px !important;
    }

    /* Ensure no large arrows */
    .pagination-wrapper .pagination .page-link[aria-label*="Previous"],
    .pagination-wrapper .pagination .page-link[aria-label*="Next"] {
        font-size: 14px !important;
        padding: 10px 16px !important;
    }

    /* Fix any overflow */
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

    .empty-state {
        text-align: center;
        padding: 80px 20px;
    }

    .empty-state i {
        font-size: 80px;
        color: #E0E0E0;
        margin-bottom: 20px;
    }

    .empty-state p {
        color: #999;
        font-size: 16px;
    }

    /* Header Actions */
    .header-actions {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .toggle-all-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 16px;
        background: #F5F5F5;
        border-radius: 10px;
    }

    .toggle-label {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-dark);
        margin: 0;
    }

    .toggle-status {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-gray);
        min-width: 50px;
    }

    /* Toggle Switch */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
        margin: 0;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
        border-radius: 26px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }

    .toggle-switch input:checked + .toggle-slider {
        background-color: var(--primary-orange);
    }

    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }

    .toggle-switch input:focus + .toggle-slider {
        box-shadow: 0 0 1px var(--primary-orange);
    }

    .toggle-switch input:disabled + .toggle-slider {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Edit Status */
    .edit-status {
        font-size: 12px;
        font-weight: 600;
        margin-left: 8px;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-block;
    }

    .edit-status:has(+ .toggle-switch input:checked),
    .edit-status:has(~ .toggle-switch input:checked) {
        color: #2E7D32;
        background: #E8F5E9;
    }

    /* Table cell untuk toggle */
    .data-table tbody td {
        vertical-align: middle;
    }

    .data-table tbody td:has(.toggle-switch) {
        text-align: center;
    }
</style>
@endpush

@push('scripts')
<script>
    // Tampilkan toast saat ada pesan sukses dari server (misalnya reset password)
    @if(session('success'))
    window.addEventListener('load', function () {
        showToast('success', @json(session('success')));
    });
    @endif

    // Toggle edit untuk satu mahasiswa
    function toggleStudentEdit(studentId, studentName, isChecked) {
        const statusEl = document.getElementById('status-' + studentId);
        const toggleEl = event.target;
        
        // Disable toggle saat proses
        toggleEl.disabled = true;
        
        fetch(`/admin/management/students/${studentId}/toggle-edit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                is_edited: isChecked ? 1 : 0
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusEl.textContent = isChecked ? 'Edit' : 'Kunci';
                statusEl.style.color = isChecked ? '#2E7D32' : '#666';
                statusEl.style.background = isChecked ? '#E8F5E9' : '#F5F5F5';
                
                // Show toast notification
                showToast('success', data.message);
            } else {
                // Revert toggle jika gagal
                toggleEl.checked = !isChecked;
                showToast('error', 'Gagal mengubah status edit profil.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toggleEl.checked = !isChecked;
            showToast('error', 'Terjadi kesalahan saat mengubah status.');
        })
        .finally(() => {
            toggleEl.disabled = false;
        });
    }

    // Toggle skpi untuk satu mahasiswa
    function toggleStudentSkpi(studentId, studentName, isChecked) {
        const statusEl = document.getElementById('skpi-status-' + studentId);
        const toggleEl = event.target;
        
        // Disable toggle saat proses
        toggleEl.disabled = true;
        
        fetch(`/admin/management/students/${studentId}/toggle-skpi`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                is_skpi_unlocked: isChecked ? 1 : 0
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusEl.textContent = isChecked ? 'Buka' : 'Kunci';
                statusEl.style.color = isChecked ? '#2E7D32' : '#666';
                statusEl.style.background = isChecked ? '#E8F5E9' : '#F5F5F5';
                
                // Show toast notification
                showToast('success', data.message);
            } else {
                // Revert toggle jika gagal
                toggleEl.checked = !isChecked;
                showToast('error', 'Gagal mengubah status akses SKPI.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toggleEl.checked = !isChecked;
            showToast('error', 'Terjadi kesalahan saat mengubah status.');
        })
        .finally(() => {
            toggleEl.disabled = false;
        });
    }

    // Toggle semua mahasiswa
    function toggleAllStudents() {
        const toggleAll = document.getElementById('toggleAllEdit');
        const isChecked = toggleAll.checked;
        const statusEl = document.getElementById('toggleAllStatus');
        
        // Disable toggle saat proses
        toggleAll.disabled = true;
        
        fetch('/admin/management/students/toggle-all-edit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                is_edited: isChecked ? 1 : 0
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusEl.textContent = isChecked ? 'Edit' : 'Kunci';
                
                // Update semua toggle di table
                document.querySelectorAll('.toggle-edit').forEach(toggle => {
                    toggle.checked = isChecked;
                });
                
                // Update semua status
                document.querySelectorAll('.edit-status').forEach(status => {
                    status.textContent = isChecked ? 'Edit' : 'Kunci';
                    status.style.color = isChecked ? '#2E7D32' : '#666';
                    status.style.background = isChecked ? '#E8F5E9' : '#F5F5F5';
                });
                
                showToast('success', data.message);
            } else {
                toggleAll.checked = !isChecked;
                showToast('error', 'Gagal mengubah status edit profil untuk semua mahasiswa.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toggleAll.checked = !isChecked;
            showToast('error', 'Terjadi kesalahan saat mengubah status.');
        })
        .finally(() => {
            toggleAll.disabled = false;
        });
    }

    // Toast notification
    function showToast(type, message) {
        const toast = document.createElement('div');
        toast.className = `alert-${type}`;
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '10000';
        toast.style.minWidth = '300px';
        toast.style.maxWidth = '500px';
        toast.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'x-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>
@endpush
