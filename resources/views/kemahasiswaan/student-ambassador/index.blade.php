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
                <button type="button" class="btn-sync-siakad" id="btn-sync-siakad" onclick="handleSyncSiakad()" title="Tarik data mahasiswa & IPK terbaru dari SIAKAD">
                    <i class="bi bi-arrow-repeat"></i> Sinkron Siakad
                </button>
                <button type="button" class="btn-refresh-all-siakad" id="btn-refresh-all-siakad" onclick="handleRefreshAllSiakad()" title="Perbarui seluruh data IPK, Semester, Beasiswa, No. Telepon, dan Alamat dari SIAKAD">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Data SIAKAD
                </button>
                <a href="{{ route('kemahasiswaan.student-ambassador.create') }}" class="btn-outline-manual" title="Tambah data secara manual">
                    <i class="bi bi-plus-circle"></i> Tambah Manual
                </a>
            </div>
        </div>

        <form id="form-sync-siakad" action="{{ route('kemahasiswaan.student-ambassador.sync-siakad') }}" method="POST" style="display: none;">
            @csrf
        </form>

        <!-- Siakad Integration Notice -->
        <!-- <div class="siakad-sync-banner">
            <div class="siakad-sync-icon">
                <i class="bi bi-link-45deg"></i>
            </div>
            <div class="siakad-sync-body">
                <div class="siakad-sync-header">
                    <strong>Sumber Data: Siakad (Rekomendasi Beasiswa Termin)</strong>
                    <span class="siakad-tag"><i class="bi bi-check2-circle"></i> Siakad Port 8090 / siakad_terbaru_10</span>
                </div>
                <p class="siakad-sync-desc">
                    Penambahan Student Ambassador dilakukan dengan menarik data dari menu <b>Rekomendasi Beasiswa Termin</b> di Siakad. Semester & Nilai IPK diambil langsung dari data resmi Siakad (bukan dari bimbingan PA). Tombol <b>Edit</b> pada tabel tetap dapat digunakan untuk penyesuaian data mandiri.
                </p>
            </div>
        </div> -->

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
                            <th style="text-align: center;">Semester</th>
                            <th style="text-align: center;">IPK</th>
                            <th style="text-align: center;">Beasiswa (%)</th>
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
                                <td style="text-align: center;">
                                    @php
                                        $displaySem = $student->semester ?: ($student->angkatan ? $student->getCurrentSemester() : null);
                                    @endphp
                                    @if(filled($displaySem))
                                        <span class="badge-semester">Sem {{ $displaySem }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if(filled($student->ipk))
                                        <span class="badge-ipk {{ (float)$student->ipk >= 3.5 ? 'ipk-cumlaude' : ((float)$student->ipk >= 3.0 ? 'ipk-good' : '') }}">
                                            {{ number_format((float)$student->ipk, 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @php
                                        $hasBeasiswa = filled($student->beasiswa) && !in_array($student->beasiswa, ['0%', '0', 'Non-Beasiswa'], true);
                                    @endphp
                                    @if($hasBeasiswa)
                                        <span class="badge-beasiswa" title="Beasiswa {{ $student->beasiswa }}">
                                            <i class="bi bi-mortarboard-fill"></i> {{ $student->beasiswa }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size: 12px;">0%</span>
                                    @endif
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
                                        @if($student->nim && $student->nim !== '-')
                                            <form action="{{ route('kemahasiswaan.student-ambassador.send-single-siakad', $student->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Kirim rekomendasi beasiswa mahasiswa ini ({{ $student->beasiswa ?: '0%' }}) ke kolom Rekomendasi di SIAKAD?')">
                                                @csrf
                                                <button type="submit" class="btn-send-single-siakad" title="Kirim Rekomendasi ke SIAKAD">
                                                    <i class="bi bi-send-check"></i>
                                                </button>
                                            </form>
                                        @endif
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

    /* Siakad Sync Button */
    .btn-sync-siakad {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: linear-gradient(135deg, #00796B, #004D40);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(0, 121, 107, 0.25);
    }
    .btn-sync-siakad:hover {
        background: linear-gradient(135deg, #004D40, #00332C);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 121, 107, 0.35);
        color: white;
    }
    .btn-sync-siakad:active {
        transform: translateY(0);
    }

    /* Siakad Refresh All Button */
    .btn-refresh-all-siakad {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: linear-gradient(135deg, #0284C7, #0369A1);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
    }
    .btn-refresh-all-siakad:hover {
        background: linear-gradient(135deg, #0369A1, #075985);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(2, 132, 199, 0.35);
        color: white;
    }
    .btn-refresh-all-siakad:active {
        transform: translateY(0);
    }

    .btn-send-single-siakad {
        background: #ECFDF5;
        border: 1px solid #A7F3D0;
        color: #047857;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 14px;
    }
    .btn-send-single-siakad:hover {
        background: #059669;
        border-color: #059669;
        color: white;
        transform: translateY(-1px);
    }

    /* Manual Add Button */
    .btn-outline-manual {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 15px;
        background: white;
        color: #475569;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-outline-manual:hover {
        background: #F8FAFC;
        border-color: #94A3B8;
        color: #1E293B;
    }

    /* Siakad Sync Banner */
    .siakad-sync-banner {
        background: linear-gradient(135deg, rgba(0, 121, 107, 0.08), rgba(240, 253, 250, 1));
        border: 1px solid rgba(0, 121, 107, 0.25);
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 22px;
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }
    .siakad-sync-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #00796B;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0, 121, 107, 0.25);
    }
    .siakad-sync-body {
        flex: 1;
    }
    .siakad-sync-header {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 4px;
    }
    .siakad-sync-header strong {
        font-size: 15px;
        color: #004D40;
    }
    .siakad-tag {
        font-size: 11px;
        font-weight: 700;
        background: #E0F2F1;
        color: #00796B;
        padding: 2px 8px;
        border-radius: 99px;
        border: 1px solid #B2DFDB;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .siakad-sync-desc {
        font-size: 13px;
        color: #475569;
        margin: 0;
        line-height: 1.5;
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

    /* Badge IPK */
    .badge-ipk {
        display: inline-block;
        font-weight: 700;
        font-size: 13px;
        padding: 3px 8px;
        border-radius: 6px;
        background: #F1F5F9;
        color: #334155;
        border: 1px solid #E2E8F0;
    }
    .badge-ipk.ipk-good {
        background: #EFF6FF;
        color: #1D4ED8;
        border-color: #BFDBFE;
    }
    .badge-ipk.ipk-cumlaude {
        background: #ECFDF5;
        color: #047857;
        border-color: #A7F3D0;
    }

    /* Badge Beasiswa */
    .badge-beasiswa {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        background: #ECFDF5;
        color: #059669;
        border: 1px solid #A7F3D0;
        max-width: 170px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    /* Badge Semester */
    .badge-semester {
        display: inline-block;
        font-weight: 700;
        font-size: 12px;
        padding: 3px 9px;
        border-radius: 6px;
        background: #F0F9FF;
        color: #0284C7;
        border: 1px solid #BAE6FD;
        white-space: nowrap;
    }
</style>
@endsection

@push('scripts')
<script>
function handleSyncSiakad() {
    Swal.fire({
        title: 'Memeriksa Data Siakad...',
        html: 'Menghubungkan ke Siakad Rekomendasi Beasiswa Termin...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            fetch("{{ route('kemahasiswaan.student-ambassador.preview-siakad') }}")
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Koneksi Siakad Gagal',
                            text: data.message || 'Tidak dapat terhubung ke database Siakad.'
                        });
                        return;
                    }

                    let sampleHtml = '';
                    if (data.sample && data.sample.length > 0) {
                        sampleHtml = '<div style="margin-top: 10px; max-height: 140px; overflow-y: auto; border: 1px solid #E2E8F0; border-radius: 8px; padding: 8px; background: white;">' +
                            '<table style="width: 100%; font-size: 11px; text-align: left;">' +
                            '<thead style="color: #64748B; border-bottom: 1px solid #CBD5E1;"><tr><th style="padding: 2px 4px;">NIM</th><th style="padding: 2px 4px;">Nama</th><th style="padding: 2px 4px;">Sem</th><th style="padding: 2px 4px;">IPK</th><th style="padding: 2px 4px;">Beasiswa</th></tr></thead>' +
                            '<tbody>';
                        data.sample.forEach(s => {
                            sampleHtml += `<tr><td style="padding: 3px 4px; font-weight: 600;">${s.nim}</td><td style="padding: 3px 4px;">${s.name}</td><td style="padding: 3px 4px;">Sem ${s.semester || '-'}</td><td style="padding: 3px 4px;">${s.ipk ? Number(s.ipk).toFixed(2) : '-'}</td><td style="padding: 3px 4px; color: #00796B; font-weight: 600;">${s.beasiswa}</td></tr>`;
                        });
                        sampleHtml += '</tbody></table></div>';
                    }

                    Swal.fire({
                        title: 'Sinkronisasi Data Siakad',
                        html: `
                            <div style="text-align: left; font-size: 14px;">
                                <div style="background: #E0F2F1; border-left: 4px solid #00796B; padding: 12px; border-radius: 6px; margin-bottom: 12px;">
                                    <strong><i class="bi bi-info-circle-fill" style="color: #00796B;"></i> Ditemukan ${data.total} mahasiswa</strong> pada menu <em>Rekomendasi Beasiswa Termin</em> Siakad.
                                </div>
                                <table style="width: 100%; font-size: 13px; margin-bottom: 8px;">
                                    <tr>
                                        <td style="padding: 4px 0; color: #64748B;">Mahasiswa Baru (Ditambahkan):</td>
                                        <td style="padding: 4px 0; font-weight: 700; color: #2E7D32; text-align: right;">+${data.new_count} Mahasiswa</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; color: #64748B;">Mahasiswa Terdaftar (Diperbarui):</td>
                                        <td style="padding: 4px 0; font-weight: 700; color: #0288D1; text-align: right;">${data.update_count} Mahasiswa</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; color: #64748B;">Sumber Acuan Nilai:</td>
                                        <td style="padding: 4px 0; font-weight: 600; text-align: right; color: #004D40;">Semester & IPK Siakad</td>
                                    </tr>
                                </table>
                                ${sampleHtml}
                                <p style="font-size: 12px; color: #64748B; margin-top: 10px; margin-bottom: 0;">Seluruh mahasiswa yang ditarik akan ditetapkan statusnya sebagai <b>Student Ambassador</b> dengan semester, IPK, dan persentase beasiswa sesuai data resmi Siakad.</p>
                            </div>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#00796B',
                        cancelButtonColor: '#94A3B8',
                        confirmButtonText: '<i class="bi bi-arrow-repeat"></i> Ya, Sinkronkan Sekarang',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Sedang Menyinkronkan...',
                                html: 'Sistem sedang memproses sinkronisasi data dari Siakad...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            document.getElementById('form-sync-siakad').submit();
                        }
                    });
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: err.message || 'Gagal memuat pratinjau data Siakad.'
                    });
                });
        }
    });
}

function handleRefreshAllSiakad() {
    Swal.fire({
        title: 'Refresh Data dari SIAKAD?',
        html: `
            <div style="text-align: left; font-size: 13.5px; color: #475569; line-height: 1.55;">
                <p>Sistem akan memperbarui data seluruh mahasiswa (<strong>Nilai IPK, Semester, Beasiswa, No. Telepon, dan Alamat</strong>) sekaligus dari data terkini di SIAKAD.</p>
                <div style="background: #F0F9FF; border: 1px solid #BAE6FD; border-radius: 8px; padding: 10px 12px; margin-top: 10px; color: #0369A1; font-size: 12.5px;">
                    <i class="bi bi-info-circle-fill"></i> Data status penetapan dan catatan internal kemahasiswaan yang tersimpan di SuperApps akan tetap dipertahankan.
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0284C7',
        cancelButtonColor: '#6B7280',
        confirmButtonText: '<i class="bi bi-arrow-clockwise"></i> Ya, Refresh Semua Data',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memperbarui Data...',
                text: 'Sedang memproses dan memperbarui data mahasiswa dari SIAKAD...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('form-sync-siakad').submit();
        }
    });
}
</script>
@endpush
