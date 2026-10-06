@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <div>
                <h3>Management Student Ambassador</h3>
                <p class="text-muted mb-0" style="font-size: 13px;">Kelola penetapan status dan direktori Student Ambassador & Mahasiswa Universitas Sugeng Hartono.</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('kemahasiswaan.dashboard') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                </a>
                <a href="{{ route('kemahasiswaan.student-ambassador.create') }}" class="btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Mahasiswa / Ambassador
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
                    <i class="bi bi-upload"></i> Import Mahasiswa / Student Ambassador (CSV)
                </div>
                <div class="import-hint">
                    <a href="{{ route('kemahasiswaan.student-ambassador.template') }}" class="import-link">
                        Download template CSV
                    </a>
                    lalu upload file. Kolom status: <b>studentambassador</b> atau <b>mahasiswa</b>. Password default: <b>12345678</b>.
                </div>
            </div>
            <div class="import-right">
                <form method="POST" action="{{ route('kemahasiswaan.student-ambassador.import') }}" enctype="multipart/form-data" class="import-form" onsubmit="document.getElementById('import-btn').disabled=true; document.getElementById('import-btn').innerHTML='<i class=\'bi bi-hourglass-split\'></i> Memproses...';">
                    @csrf
                    <input type="file" name="import_file" class="file-input" accept=".csv,text/csv" required>
                    <button type="submit" class="btn-primary" id="import-btn">
                        <i class="bi bi-cloud-arrow-up"></i> Import CSV
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Summary Stats -->
        <div class="stats-row">
            <div class="stat-box ambassador">
                <div class="stat-icon"><i class="bi bi-award-fill"></i></div>
                <div class="stat-details">
                    <span class="stat-count">{{ $totalAmbassador ?? 0 }}</span>
                    <span class="stat-label">Student Ambassador</span>
                </div>
            </div>
            <div class="stat-box student">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <div class="stat-details">
                    <span class="stat-count">{{ $totalMahasiswa ?? 0 }}</span>
                    <span class="stat-label">Mahasiswa Reguler</span>
                </div>
            </div>
            <div class="stat-box total">
                <div class="stat-icon"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="stat-details">
                    <span class="stat-count">{{ ($totalAmbassador ?? 0) + ($totalMahasiswa ?? 0) }}</span>
                    <span class="stat-label">Total Terdaftar</span>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="search-box">
            <form method="GET" action="{{ route('kemahasiswaan.student-ambassador.index') }}" class="search-form">
                <input type="text" name="search" class="search-input" 
                       placeholder="Cari nama, NIM, email, atau periode masuk..." 
                       value="{{ $search }}">
                
                <select name="status" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="studentambassador" {{ $statusFilter == 'studentambassador' ? 'selected' : '' }}>Student Ambassador</option>
                    <option value="mahasiswa" {{ $statusFilter == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa Reguler</option>
                </select>

                <select name="program_studi" class="filter-select">
                    <option value="">Semua Program Studi</option>
                    @foreach($studyPrograms as $prodi)
                        <option value="{{ $prodi->name }}" {{ $programStudi == $prodi->name ? 'selected' : '' }}>
                            {{ $prodi->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="search-btn">
                    <i class="bi bi-search"></i> Filter
                </button>
                @if($search || $programStudi || $statusFilter)
                    <a href="{{ route('kemahasiswaan.student-ambassador.index') }}" class="btn-reset-filter">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                @endif
            </form>
        </div>

        @if($students->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Mahasiswa</th>
                            <th>NIM</th>
                            <th>Periode Masuk</th>
                            <th>Program Studi</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                                <td>
                                    <strong>{{ $student->nama_lengkap }}</strong>
                                    @if($student->email)
                                        <br><small class="text-muted">{{ $student->email }}</small>
                                    @endif
                                </td>
                                <td class="font-monospace"><strong>{{ $student->nim }}</strong></td>
                                <td>
                                    <span class="badge-year">{{ $student->tanggal_masuk ? $student->tanggal_masuk->translatedFormat('M Y') : ($student->angkatan ?? '-') }}</span>
                                </td>
                                <td>
                                    <span class="badge-prodi">{{ $student->program_studi }}</span>
                                </td>
                                <td>
                                    @if($student->status === 'studentambassador')
                                        <span class="status-badge-ambassador">
                                            <i class="bi bi-star-fill"></i> Student Ambassador
                                        </span>
                                    @else
                                        <span class="status-badge-mahasiswa">
                                            <i class="bi bi-person"></i> Mahasiswa
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <a href="{{ route('kemahasiswaan.student-ambassador.show', $student->id) }}" class="btn-view" title="Detail">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        <a href="{{ route('kemahasiswaan.student-ambassador.edit', $student->id) }}" class="btn-edit" title="Edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="{{ route('kemahasiswaan.student-ambassador.reset-password', $student->id) }}"
                                           class="btn-reset"
                                           onclick="return confirm('Yakin ingin mereset password mahasiswa ini ke 12345678?')"
                                           title="Reset Password">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </a>
                                        <form action="{{ route('kemahasiswaan.student-ambassador.destroy', $student->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $students->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="mt-2">Belum ada data Mahasiswa / Student Ambassador ditemukan.</p>
                <a href="{{ route('kemahasiswaan.student-ambassador.create') }}" class="btn-primary mt-2">
                    <i class="bi bi-plus-circle"></i> Tambah Sekarang
                </a>
            </div>
        @endif
    </div>

<style>
    .content-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .card-header h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #2c3e50;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: #ECEFF1;
        color: #455A64;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }

    .btn-secondary:hover {
        background: #CFD8DC;
        color: #263238;
    }

    /* Import Card */
    .import-card {
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.08), rgba(255, 251, 240, 1));
        border: 1px solid rgba(255, 152, 0, 0.22);
        border-radius: 14px;
        padding: 18px 20px;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .import-title {
        font-weight: 800;
        font-size: 15px;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .import-title i {
        color: var(--primary-orange);
        font-size: 18px;
    }

    .import-hint {
        margin-top: 6px;
        font-size: 13px;
        color: #607D8B;
        font-weight: 500;
    }

    .import-link {
        font-weight: 700;
        color: var(--primary-orange);
        text-decoration: none;
        border-bottom: 1.5px dashed rgba(255, 152, 0, 0.55);
    }

    .import-link:hover {
        filter: brightness(0.9);
    }

    .import-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .import-form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .file-input {
        padding: 9px 12px;
        border: 1.5px solid #CFD8DC;
        border-radius: 10px;
        background: white;
        font-size: 13px;
        max-width: 300px;
    }

    .file-input:focus {
        outline: none;
        border-color: var(--primary-orange);
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-box {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-radius: 12px;
        background: #F8F9FA;
        border: 1px solid #ECEFF1;
    }

    .stat-box.ambassador {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.12), rgba(255, 152, 0, 0.15));
        border-color: rgba(255, 193, 7, 0.3);
    }
    .stat-box.ambassador .stat-icon {
        background: linear-gradient(135deg, #FF9800, #FFC107);
        color: white;
    }

    .stat-box.student {
        background: linear-gradient(135deg, rgba(33, 150, 243, 0.08), rgba(3, 169, 244, 0.12));
        border-color: rgba(33, 150, 243, 0.3);
    }
    .stat-box.student .stat-icon {
        background: linear-gradient(135deg, #2196F3, #03A9F4);
        color: white;
    }

    .stat-box.total {
        background: linear-gradient(135deg, rgba(76, 175, 80, 0.08), rgba(139, 195, 74, 0.12));
        border-color: rgba(76, 175, 80, 0.3);
    }
    .stat-box.total .stat-icon {
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        color: white;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .stat-details {
        display: flex;
        flex-direction: column;
    }

    .stat-count {
        font-size: 22px;
        font-weight: 700;
        color: #2c3e50;
    }

    .stat-label {
        font-size: 13px;
        color: #78909C;
        font-weight: 500;
    }

    /* Alerts */
    .alert-success {
        padding: 14px 18px;
        background: #E8F5E9;
        color: #2E7D32;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }

    .alert-danger {
        padding: 14px 18px;
        background: #FFEBEE;
        color: #C62828;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }

    /* Search & Filter */
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
        min-width: 240px;
        padding: 11px 16px;
        border: 1.5px solid #E0E0E0;
        border-radius: 10px;
        font-size: 14px;
        transition: border-color 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary-orange);
    }

    .filter-select {
        padding: 11px 16px;
        border: 1.5px solid #E0E0E0;
        border-radius: 10px;
        font-size: 14px;
        background: white;
        min-width: 190px;
        cursor: pointer;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary-orange);
    }

    .search-btn {
        padding: 11px 20px;
        background: var(--primary-orange);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: opacity 0.2s;
    }

    .search-btn:hover {
        opacity: 0.9;
    }

    .btn-reset-filter {
        padding: 11px 16px;
        background: #ECEFF1;
        color: #546E7A;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: var(--primary-orange);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: opacity 0.2s;
    }

    .btn-primary:hover {
        opacity: 0.9;
        color: white;
    }

    /* Table */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        padding: 14px 16px;
        text-align: left;
        background: #F8F9FA;
        font-weight: 600;
        color: #455A64;
        font-size: 13px;
        border-bottom: 2px solid #ECEFF1;
    }

    .data-table td {
        padding: 16px;
        border-bottom: 1px solid #ECEFF1;
        font-size: 14px;
        vertical-align: middle;
    }

    .data-table tr:hover {
        background: #FAFAFA;
    }

    .badge-year {
        display: inline-block;
        padding: 4px 10px;
        background: #ECEFF1;
        color: #455A64;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-prodi {
        display: inline-block;
        padding: 4px 10px;
        background: #E3F2FD;
        color: #1976D2;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    /* Status Badges */
    .status-badge-ambassador {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: linear-gradient(135deg, #FFF8E1, #FFECB3);
        color: #B78103;
        border: 1px solid #FFE082;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.2);
    }

    .status-badge-mahasiswa {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #F5F5F5;
        color: #616161;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    /* Actions */
    .action-buttons {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .btn-view, .btn-edit, .btn-reset, .btn-delete {
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-view {
        background: #E3F2FD;
        color: #1976D2;
    }
    .btn-view:hover { background: #BBDEFB; color: #0D47A1; }

    .btn-edit {
        background: #FFF3E0;
        color: #E65100;
    }
    .btn-edit:hover { background: #FFE0B2; color: #BF360C; }

    .btn-reset {
        background: #ECEFF1;
        color: #455A64;
    }
    .btn-reset:hover { background: #CFD8DC; color: #263238; }

    .btn-delete {
        background: #FFEBEE;
        color: #C62828;
    }
    .btn-delete:hover { background: #FFCDD2; color: #B71C1C; }

    .empty-state {
        text-align: center;
        padding: 48px 16px;
    }

    .pagination-wrapper {
        margin-top: 24px;
        display: flex;
        justify-content: flex-end;
    }
</style>
@endsection
