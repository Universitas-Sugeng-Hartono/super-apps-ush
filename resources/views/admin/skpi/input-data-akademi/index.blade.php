@extends('admin.layouts.super-app')

@section('content')
    <div class="page-shell">

        <div class="hero-card">
            <div>
                <span class="hero-badge">Identitas Penyelenggara Program</span>
                <h3>Input Data Akademik SKPI</h3>
                <p>Lengkapi data akademik sesuai tabel pada template SKPI. Penyimpanan dipisahkan per program studi agar akreditasi dan profil program bisa dikelola dengan rapi.</p>
            </div>
            <div class="hero-stats">
                <div class="stat-chip">
                    <span class="stat-label">Total Prodi</span>
                    <strong>{{ $stats['total_programs'] }}</strong>
                </div>
                <div class="stat-chip">
                    <span class="stat-label">Sudah Diisi</span>
                    <strong>{{ $stats['configured_programs'] }}</strong>
                </div>
                <div class="stat-chip">
                    <span class="stat-label">Siap Dipakai</span>
                    <strong>{{ $stats['ready_programs'] }}</strong>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success">
                <i class="bi bi-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-error">
                <i class="bi bi-exclamation-triangle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="layout-grid">

            {{-- ── Sidebar Daftar Prodi ──────────────────────────── --}}
            <aside class="sidebar-card">
                <div class="sidebar-head">
                    <div style="display: flex; justify-content: space-between; align-items: start; gap: 10px;">
                        <div>
                            <h4>Daftar Program Studi</h4>
                            <p>Pilih prodi yang ingin dilengkapi datanya.</p>
                        </div>
                        <button type="button" class="btn-add-prodi" onclick="openProdiModal()" title="Tambah Prodi Baru">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>

                @if($studyPrograms->count() > 0)
                    <div class="program-list">
                        @foreach($studyPrograms as $studyProgram)
                            <div class="program-row {{ $selectedStudyProgramId === $studyProgram->id ? 'active' : '' }}">
                                <a href="{{ route('admin.skpi.input-data-akademi.index', ['study_program_id' => $studyProgram->id]) }}"
                                   class="program-item">
                                    <div>
                                        <strong>{{ $studyProgram->name }}</strong>
                                        <span>{{ $studyProgram->skpi_completed_fields }}/{{ $studyProgram->skpi_total_fields }} field terisi</span>
                                    </div>
                                    <span class="program-status {{ $studyProgram->skpi_ready ? 'ready' : 'draft' }}">
                                        {{ $studyProgram->skpi_ready ? 'Aktif' : 'Draft' }}
                                    </span>
                                </a>
                                <form method="POST"
                                      action="{{ route('admin.skpi.input-data-akademi.destroy-prodi', $studyProgram->id) }}"
                                      onsubmit="return confirm('Yakin ingin menghapus Program Studi {{ addslashes($studyProgram->name) }}? Data akademik SKPI untuk prodi ini juga akan terhapus.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete-prodi" title="Hapus Program Studi">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-side-state">
                        <i class="bi bi-journal-x"></i>
                        <p>Belum ada master program studi aktif.</p>
                    </div>
                @endif
            </aside>

            {{-- ── Content Stack ─────────────────────────────────── --}}
            <div class="content-stack">

                {{-- Summary prodi terpilih --}}
                <div class="summary-card">
                    <div>
                        <span class="summary-label">Program Studi Aktif</span>
                        <h4>{{ $selectedStudyProgram->name ?? 'Belum dipilih' }}</h4>
                        <p>Gunakan form di bawah untuk mengisi seluruh bagian identitas penyelenggara program sesuai template SKPI.</p>
                    </div>
                    <div class="progress-panel">
                        <span class="progress-label">Kelengkapan Data</span>
                        <strong>{{ $stats['selected_completed_fields'] }}/{{ $stats['selected_total_fields'] }}</strong>
                    </div>
                </div>

                {{-- ── Form Data Akademik ──────────────────────────── --}}
                @if($selectedStudyProgram)
                    <form method="POST" action="{{ route('admin.skpi.input-data-akademi.store') }}" class="content-card">
                        @csrf
                        <input type="hidden" name="study_program_id" value="{{ $selectedStudyProgram->id }}">

                        <div class="form-header">
                            <div>
                                <h4>Form Identitas Penyelenggara Program</h4>
                                <p>Field disusun mengikuti urutan tabel pada dokumen SKPI.</p>
                            </div>
                            <button type="submit" class="btn-save">
                                <i class="bi bi-save"></i> Simpan Data Akademik
                            </button>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Program Studi</label>
                                <input type="text" class="form-control static-field" value="{{ $selectedStudyProgram->name }}" disabled>
                                <small>Diambil dari master program studi aktif.</small>
                            </div>

                            <div class="form-group">
                                <label for="sk_pendirian_perguruan_tinggi">SK Pendirian Perguruan Tinggi</label>
                                <input type="text" id="sk_pendirian_perguruan_tinggi" name="sk_pendirian_perguruan_tinggi" class="form-control"
                                       value="{{ old('sk_pendirian_perguruan_tinggi', $academicProfile->sk_pendirian_perguruan_tinggi) }}"
                                       placeholder="Contoh: SK Mendiknas No. ...">
                                @error('sk_pendirian_perguruan_tinggi')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="nama_perguruan_tinggi">Nama Perguruan Tinggi</label>
                                <input type="text" id="nama_perguruan_tinggi" name="nama_perguruan_tinggi" class="form-control"
                                       value="{{ old('nama_perguruan_tinggi', $academicProfile->nama_perguruan_tinggi) }}"
                                       placeholder="Contoh: Universitas Sugeng Hartono">
                                @error('nama_perguruan_tinggi')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="akreditasi_perguruan_tinggi">Akreditasi Perguruan Tinggi</label>
                                <input type="text" id="akreditasi_perguruan_tinggi" name="akreditasi_perguruan_tinggi" class="form-control"
                                       value="{{ old('akreditasi_perguruan_tinggi', $academicProfile->akreditasi_perguruan_tinggi) }}"
                                       placeholder="Contoh: Baik Sekali">
                                @error('akreditasi_perguruan_tinggi')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="akreditasi_program_studi">Akreditasi Program Studi</label>
                                <input type="text" id="akreditasi_program_studi" name="akreditasi_program_studi" class="form-control"
                                       value="{{ old('akreditasi_program_studi', $academicProfile->akreditasi_program_studi) }}"
                                       placeholder="Contoh: Unggul">
                                @error('akreditasi_program_studi')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="jenis_dan_jenjang_pendidikan">Jenis dan Jenjang Pendidikan</label>
                                <input type="text" id="jenis_dan_jenjang_pendidikan" name="jenis_dan_jenjang_pendidikan" class="form-control"
                                       value="{{ old('jenis_dan_jenjang_pendidikan', $academicProfile->jenis_dan_jenjang_pendidikan) }}"
                                       placeholder="Contoh: Akademik - Sarjana">
                                @error('jenis_dan_jenjang_pendidikan')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="jenjang_kualifikasi_kkni">Jenjang Kualifikasi Sesuai KKNI</label>
                                <input type="text" id="jenjang_kualifikasi_kkni" name="jenjang_kualifikasi_kkni" class="form-control"
                                       value="{{ old('jenjang_kualifikasi_kkni', $academicProfile->jenjang_kualifikasi_kkni) }}"
                                       placeholder="Contoh: Level 6">
                                @error('jenjang_kualifikasi_kkni')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="bahasa_pengantar_kuliah">Bahasa Pengantar Kuliah</label>
                                <input type="text" id="bahasa_pengantar_kuliah" name="bahasa_pengantar_kuliah" class="form-control"
                                       value="{{ old('bahasa_pengantar_kuliah', $academicProfile->bahasa_pengantar_kuliah) }}"
                                       placeholder="Contoh: Bahasa Indonesia / Inggris">
                                @error('bahasa_pengantar_kuliah')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="nomor_akreditasi_perguruan_tinggi">Nomor Akreditasi Perguruan Tinggi</label>
                                <input type="text" id="nomor_akreditasi_perguruan_tinggi" name="nomor_akreditasi_perguruan_tinggi" class="form-control"
                                       value="{{ old('nomor_akreditasi_perguruan_tinggi', $academicProfile->nomor_akreditasi_perguruan_tinggi) }}"
                                       placeholder="Nomor sertifikat / SK akreditasi PT">
                                @error('nomor_akreditasi_perguruan_tinggi')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="lama_studi">Lama Studi</label>
                                <input type="text" id="lama_studi" name="lama_studi" class="form-control"
                                       value="{{ old('lama_studi', $academicProfile->lama_studi) }}"
                                       placeholder="Contoh: 4 tahun / 8 semester">
                                @error('lama_studi')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="nomor_akreditasi_program_studi">Nomor Akreditasi Program Studi</label>
                                <input type="text" id="nomor_akreditasi_program_studi" name="nomor_akreditasi_program_studi" class="form-control"
                                       value="{{ old('nomor_akreditasi_program_studi', $academicProfile->nomor_akreditasi_program_studi) }}"
                                       placeholder="Nomor sertifikat / SK akreditasi prodi">
                                @error('nomor_akreditasi_program_studi')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group form-group-full">
                                <label for="persyaratan_penerimaan">Persyaratan Penerimaan</label>
                                <textarea id="persyaratan_penerimaan" name="persyaratan_penerimaan" class="form-control textarea-control" rows="4"
                                          placeholder="Tuliskan persyaratan penerimaan mahasiswa sesuai template SKPI">{{ old('persyaratan_penerimaan', $academicProfile->persyaratan_penerimaan) }}</textarea>
                                @error('persyaratan_penerimaan')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group form-group-full">
                                <label for="sistem_penilaian">Sistem Penilaian</label>
                                <textarea id="sistem_penilaian" name="sistem_penilaian" class="form-control textarea-control" rows="5"
                                          placeholder="Contoh: Skala 0-4 dengan keterangan A = 4, B = 3, dst.">{{ old('sistem_penilaian', $academicProfile->sistem_penilaian) }}</textarea>
                                @error('sistem_penilaian')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group form-group-full">
                                <label for="status_profesi">Status Profesi (Bila Ada)</label>
                                <input type="text" id="status_profesi" name="status_profesi" class="form-control"
                                       value="{{ old('status_profesi', $academicProfile->status_profesi) }}"
                                       placeholder="Contoh: Tidak ada / Sebutan profesi tertentu">
                                @error('status_profesi')<span class="error-text">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </form>
                @else
                    <div class="content-card empty-main-state">
                        <i class="bi bi-journal-x"></i>
                        <h4>Program studi belum tersedia</h4>
                        <p>Tambahkan master program studi aktif terlebih dahulu sebelum mengisi data akademik SKPI.</p>
                    </div>
                @endif

                {{-- ── Card Pengesahan SKPI ────────────────────────── --}}
                <div class="content-card">
                    <div class="form-header">
                        <div>
                            <h4>Informasi Pengesahan SKPI</h4>
                            <p>Data penandatangan yang akan tercetak pada dokumen SKPI. Berlaku global untuk semua generate.</p>
                        </div>
                        <button type="submit" form="form-pengesahan" class="btn-save">
                            <i class="bi bi-save"></i> Simpan Pengesahan
                        </button>
                    </div>

                    <form id="form-pengesahan"
                          method="POST"
                          action="{{ route('admin.skpi.generate-skpi.metadata.store') }}"
                          enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_from" value="input-data-akademi">

                        <div class="form-grid">

                            <div class="form-group">
                                <label for="nomor_skpi">Nomor Surat SKPI</label>
                                <input type="text"
                                       id="nomor_skpi"
                                       name="nomor_skpi"
                                       class="form-control"
                                       value="{{ old('nomor_skpi', $documentMeta['nomor_skpi'] ?? '') }}"
                                       placeholder="Contoh: 5335/UN15.9/PP/2020">
                                @error('nomor_skpi')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="authorization_place_date">Kota &amp; Tanggal Pengesahan</label>
                                <input type="text"
                                       id="authorization_place_date"
                                       name="authorization_place_date"
                                       class="form-control"
                                       value="{{ old('authorization_place_date', $documentMeta['authorization_place_date'] ?? ('Sukoharjo, ' . now()->translatedFormat('d F Y'))) }}"
                                       placeholder="Contoh: Sukoharjo, 08 April 2026">
                                @error('authorization_place_date')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="vice_rector_name">Nama Pengesah</label>
                                <input type="text"
                                       id="vice_rector_name"
                                       name="vice_rector_name"
                                       class="form-control"
                                       value="{{ old('vice_rector_name', $documentMeta['vice_rector_name'] ?? '') }}"
                                       placeholder="Masukan nama dan gelar yang mengesahkan">
                                @error('vice_rector_name')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="vice_rector_title">Jabatan Penandatangan</label>
                                <input type="text"
                                       id="vice_rector_title"
                                       name="vice_rector_title"
                                       class="form-control"
                                       value="{{ old('vice_rector_title', $documentMeta['vice_rector_title'] ?? 'Wakil Rektor I Universitas Sugeng Hartono') }}"
                                       placeholder="Contoh: Wakil Rektor I Universitas Sugeng Hartono">
                                @error('vice_rector_title')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group form-group-full">
                                <label>Tanda Tangan</label>
                                <div class="ttd-wrapper">

                                    @if(!empty($documentMeta['signature_url']))
                                        <div class="ttd-current">
                                            <img src="{{ $documentMeta['signature_url'] }}"
                                                 alt="Tanda tangan aktif"
                                                 class="ttd-img"
                                                 id="ttdPreviewImg">
                                            <span class="ttd-current-label">Tanda tangan aktif · upload baru untuk mengganti</span>
                                        </div>
                                    @else
                                        <div class="ttd-current" id="ttdPreviewWrapper" style="display:none;">
                                            <img src="" alt="Preview tanda tangan" class="ttd-img" id="ttdPreviewImg">
                                            <span class="ttd-current-label">Preview · belum disimpan</span>
                                        </div>
                                    @endif

                                    <label for="signature_image" class="ttd-drop">
                                        <i class="bi bi-cloud-arrow-up ttd-icon"></i>
                                        <span class="ttd-drop-label" id="ttdDropLabel">
                                            @if(!empty($documentMeta['signature_url']))
                                                Ganti tanda tangan
                                            @else
                                                Klik untuk upload tanda tangan
                                            @endif
                                        </span>
                                        <span class="ttd-drop-hint">JPG · PNG · WEBP &nbsp;·&nbsp; Maks. 2 MB</span>
                                        <input type="file"
                                               id="signature_image"
                                               name="signature_image"
                                               accept=".png,.jpg,.jpeg,.webp"
                                               class="ttd-input"
                                               onchange="handleTtdPreview(this)">
                                    </label>

                                </div>
                                @error('signature_image')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                        </div>
                    </form>
                </div>
                {{-- ── /Card Pengesahan ─────────────────────────────── --}}

            </div>
        </div>
    </div>

    {{-- ── Modal Tambah Program Studi ──────────────────────────── --}}
    <div id="modalTambahProdi" class="prodi-modal-overlay" style="display: none;">
        <div class="prodi-modal-card">
            <div class="prodi-modal-header">
                <h5>Tambah Program Studi</h5>
                <button type="button" class="btn-close-modal" onclick="closeProdiModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form action="{{ route('admin.skpi.input-data-akademi.store-prodi') }}" method="POST">
                @csrf
                <div class="prodi-modal-body">
                    <div class="form-group">
                        <label for="prodi_name">Nama Program Studi</label>
                        <input type="text" id="prodi_name" name="name" class="form-control" placeholder="Contoh: Sistem Informasi" required>
                    </div>
                </div>
                <div class="prodi-modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeProdiModal()">Batal</button>
                    <button type="submit" class="btn-save" style="padding: 10px 16px;">Simpan Prodi</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('css')
<style>
    .page-shell {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .hero-card,
    .content-card,
    .sidebar-card,
    .summary-card,
    .alert-success,
    .alert-error {
        background: white;
        border-radius: 15px;
        padding: 24px;
        box-shadow: var(--shadow);
    }

    .hero-card {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: flex-start;
        background: linear-gradient(135deg, #FFF8EE, #FFFFFF);
        border: 1px solid #F2E7D8;
    }

    .hero-card h3 {
        margin: 10px 0 12px;
        font-size: 28px;
        font-weight: 700;
        color: #213555;
    }

    .hero-card p,
    .summary-card p,
    .sidebar-head p,
    .empty-side-state p,
    .empty-main-state p,
    .form-header p {
        margin: 0;
        color: #6B7280;
        line-height: 1.6;
    }

    .hero-badge,
    .summary-label {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        background: #FFF1DA;
        color: #D97706;
        font-size: 12px;
        font-weight: 700;
    }

    .hero-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(120px, 1fr));
        gap: 12px;
        width: min(420px, 100%);
    }

    .stat-chip {
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid #F1E4D3;
        border-radius: 16px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .stat-label,
    .program-item span,
    .progress-label,
    .form-group small {
        font-size: 12px;
        color: #8A94A6;
    }

    .stat-chip strong,
    .progress-panel strong {
        font-size: 26px;
        color: #213555;
        line-height: 1;
    }

    .alert-success,
    .alert-error {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: #EDF9F0;
        color: #1E7A44;
        border: 1px solid #D6EFD9;
    }

    .alert-error {
        background: #FFF1F2;
        color: #B91C1C;
        border: 1px solid #FECACA;
    }

    .layout-grid {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 24px;
        align-items: start;
    }

    .sidebar-card {
        position: sticky;
        top: 24px;
    }

    .sidebar-head {
        margin-bottom: 18px;
    }

    .sidebar-head h4,
    .summary-card h4,
    .form-header h4,
    .empty-main-state h4 {
        margin: 0 0 8px;
        font-size: 22px;
        font-weight: 700;
        color: #213555;
    }

    .program-list,
    .content-stack {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .program-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        padding: 16px;
        border-radius: 14px;
        border: 1px solid #E5E7EB;
        background: #FAFAFA;
        transition: all 0.2s ease;
    }

    .program-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        min-width: 0;
        flex: 1;
        text-decoration: none;
    }

    .program-item strong {
        display: block;
        margin-bottom: 6px;
        color: #213555;
        font-size: 15px;
    }

    .program-row:hover,
    .program-row.active {
        transform: translateY(-1px);
        border-color: #F4C97A;
        background: #FFF9EF;
    }

    .btn-delete-prodi {
        width: 34px;
        height: 34px;
        border: none;
        border-radius: 8px;
        background: #FEE2E2;
        color: #B91C1C;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease;
    }

    .btn-delete-prodi:hover {
        background: #DC2626;
        color: white;
        transform: translateY(-1px);
    }

    .program-status {
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .program-status.ready {
        background: #E8F7EE;
        color: #1E7A44;
    }

    .program-status.draft {
        background: #FFF1DA;
        color: #D97706;
    }

    .summary-card {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: center;
        border: 1px solid #F1E8D8;
        background: linear-gradient(135deg, #FFFFFF, #FFF9F0);
    }

    .progress-panel {
        min-width: 180px;
        padding: 18px;
        border-radius: 16px;
        background: #FFFFFF;
        border: 1px solid #EDE5D6;
        text-align: center;
    }

    .form-header {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: flex-start;
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid #EEE7DB;
    }

    .btn-save {
        flex-shrink: 0;
        border: none;
        border-radius: 12px;
        background: #D97706;
        color: white;
        padding: 12px 18px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s ease, transform 0.15s ease;
    }

    .btn-save:hover {
        background: #B86102;
        transform: translateY(-1px);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group-full {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-size: 14px;
        font-weight: 700;
        color: #374151;
    }

    .form-control {
        width: 100%;
        border: 1px solid #D9DEE8;
        border-radius: 12px;
        padding: 13px 14px;
        font-size: 14px;
        color: #1F2937;
        font-family: inherit;
        background: #FFFFFF;
    }

    .form-control:focus {
        outline: none;
        border-color: #D97706;
        box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.12);
    }

    .static-field {
        background: #F9FAFB;
        color: #6B7280;
    }

    .textarea-control {
        resize: vertical;
        min-height: 120px;
    }

    .error-text {
        font-size: 12px;
        color: #DC2626;
    }

    .empty-side-state,
    .empty-main-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 10px;
        padding: 30px 20px;
    }

    .empty-side-state i,
    .empty-main-state i {
        font-size: 40px;
        color: #D1D5DB;
    }

    /* ── Upload Tanda Tangan ───────────────────────────────────── */
    .ttd-wrapper {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .ttd-current {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        border-radius: 14px;
        border: 1px dashed #D1D5DB;
        background: #F9FAFB;
        width: fit-content;
    }

    .ttd-img {
        max-height: 64px;
        max-width: 220px;
        object-fit: contain;
        display: block;
    }

    .ttd-current-label {
        font-size: 12px;
        color: #8A94A6;
    }

    .ttd-drop {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 24px 20px;
        border: 2px dashed #E5E7EB;
        border-radius: 14px;
        background: #FAFAFA;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease;
        text-align: center;
    }

    .ttd-drop:hover {
        border-color: #F4C97A;
        background: #FFF9EF;
    }

    .ttd-icon {
        font-size: 26px;
        color: #D97706;
    }

    .ttd-drop-label {
        font-size: 14px;
        font-weight: 700;
        color: #374151;
    }

    .ttd-drop-hint {
        font-size: 12px;
        color: #8A94A6;
    }

    .ttd-input {
        display: none;
    }

    /* ── Modal & Button Tambah Prodi ───────────────────────────── */
    .btn-add-prodi {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: #F4F5F7;
        color: #213555;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .btn-add-prodi:hover {
        background: #D97706;
        color: white;
    }

    .prodi-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(17, 24, 39, 0.6);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1050;
        opacity: 0;
        animation: modalFadeIn 0.2s forwards ease;
    }

    @keyframes modalFadeIn {
        to { opacity: 1; }
    }

    .prodi-modal-card {
        background: white;
        border-radius: 16px;
        width: 100%;
        max-width: 440px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        transform: translateY(20px);
        animation: modalSlideUp 0.3s forwards cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modalSlideUp {
        to { transform: translateY(0); }
    }

    .prodi-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid #E5E7EB;
        background: #FAFAFA;
    }

    .prodi-modal-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #1F2937;
    }

    .btn-close-modal {
        background: none;
        border: none;
        color: #9CA3AF;
        font-size: 18px;
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: background 0.15s, color 0.15s;
    }

    .btn-close-modal:hover {
        background: #F3F4F6;
        color: #EF4444;
    }

    .prodi-modal-body {
        padding: 24px;
    }

    .prodi-modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        padding: 16px 24px;
        background: #FAFAFA;
        border-top: 1px solid #E5E7EB;
    }

    .btn-modal-cancel {
        background: white;
        border: 1px solid #D1D5DB;
        color: #374151;
        padding: 9px 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-modal-cancel:hover {
        background: #F9FAFB;
        border-color: #9CA3AF;
    }

    @media (max-width: 1100px) {
        .layout-grid,
        .form-grid,
        .hero-stats {
            grid-template-columns: 1fr;
        }

        .hero-card,
        .summary-card,
        .form-header {
            flex-direction: column;
        }

        .sidebar-card {
            position: static;
        }

        .progress-panel,
        .btn-save {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function handleTtdPreview(input) {
    if (!input.files || !input.files[0]) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        const wrapper = document.getElementById('ttdPreviewWrapper');
        const img     = document.getElementById('ttdPreviewImg');

        if (wrapper) wrapper.style.display = 'inline-flex';
        if (img)     img.src = e.target.result;

        const label = document.getElementById('ttdDropLabel');
        if (label)  label.textContent = input.files[0].name;
    };

    reader.readAsDataURL(input.files[0]);
}
</script>
<script>
// Logic Tambah Prodi
function openProdiModal() {
    const modal = document.getElementById('modalTambahProdi');
    modal.style.display = 'flex';
    // Gunakan timeout agar render modal selesai, baru set focus
    setTimeout(() => {
        document.getElementById('prodi_name').focus();
    }, 100);
}

function closeProdiModal() {
    const modal = document.getElementById('modalTambahProdi');
    modal.style.display = 'none';
    document.getElementById('prodi_name').value = '';
}

// Tutup modal jika klik di luar card
window.addEventListener('click', function(e) {
    const overlay = document.getElementById('modalTambahProdi');
    if (e.target === overlay) {
        closeProdiModal();
    }
});
</script>
@endpush
