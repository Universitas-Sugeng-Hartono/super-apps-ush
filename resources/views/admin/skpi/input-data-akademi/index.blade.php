@extends('admin.layouts.super-app')

@section('content')
<div class="skpi-setup-shell">
    <div class="mb-3" style="padding-top: 10px;">
        <a href="{{ route('admin.skpi.index') }}" class="text-decoration-none text-secondary" style="font-weight: 600; font-size: 15px; display: inline-flex; align-items: center; gap: 8px;">
            <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama SKPI
        </a>
    </div>
    {{-- Page Header --}}
    <div class="setup-header">
        <div class="header-main">
            <div class="title-area">
                <h1>Konfigurasi Akademik SKPI</h1>
                <p>Lengkapi profil program studi dan informasi pengesahan dokumen SKPI.</p>
            </div>
            <div class="header-actions">
                <button type="button" class="btn-primary-custom" onclick="openProdiModal()">
                    <i class="bi bi-plus-circle"></i>
                    <span>Tambah Prodi</span>
                </button>
            </div>
        </div>

        <div class="header-stats">
            <div class="stats-group">
                <div class="stat-item">
                    <span class="label">Total Prodi</span>
                    <span class="value">{{ $stats['total_programs'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="label">Sudah Dikonfigurasi</span>
                    <span class="value success">{{ $stats['configured_programs'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="label">Draft</span>
                    <span class="value warning">{{ $stats['total_programs'] - $stats['configured_programs'] }}</span>
                </div>
            </div>

            <div class="edit-mode-toggle">
                <button type="button" id="btn-lock" class="btn-mode active" onclick="setEditMode(false)">
                    <i class="bi bi-lock-fill"></i> Kunci
                </button>
                <button type="button" id="btn-unlock" class="btn-mode" onclick="setEditMode(true)">
                    <i class="bi bi-pencil-square"></i> Edit Aktif
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="custom-alert success">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="setup-layout">
        {{-- Sidebar: Program Studi --}}
        <aside class="prodi-sidebar">
            <div class="sidebar-inner">
                <div class="sidebar-label">Program Studi</div>
                <div class="prodi-list">
                    @foreach($studyPrograms as $studyProgram)
                    <div class="prodi-item-wrapper">
                        <a href="{{ route('admin.skpi.input-data-akademi.index', ['study_program_id' => $studyProgram->id]) }}"
                            class="prodi-card {{ $selectedStudyProgramId === $studyProgram->id ? 'active' : '' }}">
                            <div class="prodi-info">
                                <strong>{{ $studyProgram->name }}</strong>
                                <div class="prodi-meta">
                                    <span class="count">{{ $studyProgram->skpi_completed_fields }}/{{ $studyProgram->skpi_total_fields }} fields</span>
                                    <span class="dot">·</span>
                                    <span class="status {{ $studyProgram->skpi_ready ? 'ready' : 'draft' }}">
                                        {{ $studyProgram->skpi_ready ? 'Lengkap' : 'Draft' }}
                                    </span>
                                </div>
                            </div>
                            <div class="prodi-arrow">
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </a>
                        <form method="POST" action="{{ route('admin.skpi.input-data-akademi.destroy-prodi', $studyProgram->id) }}"
                            onsubmit="return confirm('Hapus Program Studi {{ addslashes($studyProgram->name) }}?');" class="delete-prodi-form">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-icon-delete"><i class="bi bi-x"></i></button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        </aside>

        {{-- Main Content: Form Tabs --}}
        <main class="setup-content">
            @if($selectedStudyProgram)
            <div class="content-container">
                {{-- Tabs Navigation --}}
                <div class="tabs-nav">
                    <button class="tab-btn active" onclick="switchTab('profile')">
                        <i class="bi bi-building"></i>
                        <span>Profil Akademik</span>
                    </button>
                    <button class="tab-btn" onclick="switchTab('curriculum')">
                        <i class="bi bi-journal-bookmark"></i>
                        <span>Sistem & Penilaian</span>
                    </button>
                    <button class="tab-btn" onclick="switchTab('authorization')">
                        <i class="bi bi-vector-pen"></i>
                        <span>Pengesahan Dokumen</span>
                    </button>
                    <button class="tab-btn" onclick="switchTab('kualifikasi')">
                        <i class="bi bi-award"></i>
                        <span>Kualifikasi &amp; Capaian</span>
                    </button>
                </div>

                {{-- Tab Content: Profil Akademik --}}
                <div id="tab-profile" class="tab-pane active">
                    <form method="POST" action="{{ route('admin.skpi.input-data-akademi.store') }}" class="setup-form">
                        @csrf
                        <input type="hidden" name="study_program_id" value="{{ $selectedStudyProgram->id }}">

                        <div class="form-section">
                            <div class="section-title">Informasi Dasar Perguruan Tinggi</div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>SK Pendirian PT</label>
                                    <input type="text" name="sk_pendirian_perguruan_tinggi" class="form-control"
                                        value="{{ old('sk_pendirian_perguruan_tinggi', $academicProfile->sk_pendirian_perguruan_tinggi) }}"
                                        placeholder="Contoh: SK Mendiknas No. ...">
                                    @error('sk_pendirian_perguruan_tinggi')<span class="error-msg">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group">
                                    <label>Nama Perguruan Tinggi</label>
                                    <input type="text" name="nama_perguruan_tinggi" class="form-control"
                                        value="{{ old('nama_perguruan_tinggi', $academicProfile->nama_perguruan_tinggi) }}"
                                        placeholder="Universitas Sugeng Hartono">
                                    @error('nama_perguruan_tinggi')<span class="error-msg">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group">
                                    <label>Akreditasi PT</label>
                                    <input type="text" name="akreditasi_perguruan_tinggi" class="form-control"
                                        value="{{ old('akreditasi_perguruan_tinggi', $academicProfile->akreditasi_perguruan_tinggi) }}"
                                        placeholder="Contoh: Baik Sekali">
                                    @error('akreditasi_perguruan_tinggi')<span class="error-msg">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group">
                                    <label>Nomor Akreditasi PT</label>
                                    <input type="text" name="nomor_akreditasi_perguruan_tinggi" class="form-control"
                                        value="{{ old('nomor_akreditasi_perguruan_tinggi', $academicProfile->nomor_akreditasi_perguruan_tinggi) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="section-title">Informasi Program Studi</div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Akreditasi Prodi</label>
                                    <input type="text" name="akreditasi_program_studi" class="form-control"
                                        value="{{ old('akreditasi_program_studi', $academicProfile->akreditasi_program_studi) }}"
                                        placeholder="Contoh: Unggul">
                                </div>
                                <div class="form-group">
                                    <label>Nomor Akreditasi Prodi</label>
                                    <input type="text" name="nomor_akreditasi_program_studi" class="form-control"
                                        value="{{ old('nomor_akreditasi_program_studi', $academicProfile->nomor_akreditasi_program_studi) }}">
                                </div>
                                <div class="form-group">
                                    <label>Jenis & Jenjang Pendidikan</label>
                                    <input type="text" name="jenis_dan_jenjang_pendidikan" class="form-control"
                                        value="{{ old('jenis_dan_jenjang_pendidikan', $academicProfile->jenis_dan_jenjang_pendidikan) }}"
                                        placeholder="Akademik - Sarjana">
                                </div>
                                <div class="form-group">
                                    <label>Level KKNI</label>
                                    <input type="text" name="jenjang_kualifikasi_kkni" class="form-control"
                                        value="{{ old('jenjang_kualifikasi_kkni', $academicProfile->jenjang_kualifikasi_kkni) }}"
                                        placeholder="Level 6">
                                </div>
                                <div class="form-group">
                                    <label>Lama Studi</label>
                                    <input type="text" name="lama_studi" class="form-control"
                                        value="{{ old('lama_studi', $academicProfile->lama_studi) }}"
                                        placeholder="4 tahun / 8 semester">
                                </div>
                                <div class="form-group">
                                    <label>Bahasa Pengantar</label>
                                    <input type="text" name="bahasa_pengantar_kuliah" class="form-control"
                                        value="{{ old('bahasa_pengantar_kuliah', $academicProfile->bahasa_pengantar_kuliah) }}"
                                        placeholder="Indonesia / Inggris">
                                </div>
                                <div class="form-group form-group-span">
                                    <label>
                                        Gelar Lulusan
                                    </label>
                                    <input type="text" name="gelar_lulusan" class="form-control"
                                        value="{{ old('gelar_lulusan', $academicProfile->gelar_lulusan) }}"
                                        placeholder="Contoh: S.Kom., S.E., A.Md.">
                                    <span class="field-hint"><i class="bi bi-info-circle"></i> Gelar ini akan otomatis muncul pada form mahasiswa yang mendaftar dari prodi ini.</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-footer-save">
                            <button type="submit" class="btn-save-large">
                                <i class="bi bi-save2-fill"></i> Simpan Profil Prodi
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Tab Content: Curriculum & Penilaian --}}
                <div id="tab-curriculum" class="tab-pane">
                    <form method="POST" action="{{ route('admin.skpi.input-data-akademi.store') }}" class="setup-form">
                        @csrf
                        <input type="hidden" name="study_program_id" value="{{ $selectedStudyProgram->id }}">

                        <div class="form-section">
                            <div class="section-title">Persyaratan & Sistem Penilaian</div>
                            <div class="form-group-full">
                                <label>Persyaratan Penerimaan</label>
                                <textarea name="persyaratan_penerimaan" class="form-control fixed-height" rows="5"
                                    placeholder="Tuliskan persyaratan sesuai template SKPI">{{ old('persyaratan_penerimaan', $academicProfile->persyaratan_penerimaan) }}</textarea>
                            </div>
                            <div class="form-group-full" style="margin-top: 20px;">
                                <label>Sistem Penilaian</label>
                                <textarea name="sistem_penilaian" class="form-control fixed-height" rows="5"
                                    placeholder="Contoh: Skala 0-4 dengan keterangan A=4, B=3, dst.">{{ old('sistem_penilaian', $academicProfile->sistem_penilaian) }}</textarea>
                            </div>
                            <div class="form-group" style="margin-top: 20px;">
                                <label>Status Profesi (Bila Ada)</label>
                                <input type="text" name="status_profesi" class="form-control"
                                    value="{{ old('status_profesi', $academicProfile->status_profesi) }}"
                                    placeholder="Contoh: Sebutan profesi tertentu">
                            </div>
                        </div>

                        <div class="form-footer-save">
                            <button type="submit" class="btn-save-large">
                                <i class="bi bi-save2-fill"></i> Simpan Sistem & Penilaian
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Tab Content: Authorization (Global) --}}
                <div id="tab-authorization" class="tab-pane">
                    <form id="form-pengesahan-new"
                        method="POST"
                        action="{{ route('admin.skpi.generate-skpi.metadata.store') }}"
                        enctype="multipart/form-data"
                        class="setup-form">
                        @csrf
                        <input type="hidden" name="_from" value="input-data-akademi">

                        <div class="form-section">
                            <div class="section-title">Pengesah Dokumen SKPI (Global)</div>
                            <p class="section-desc">Pengaturan ini berlaku untuk seluruh dokumen SKPI yang akan di-generate.</p>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Nomor SKPI Terakhir</label>
                                    <input type="text" name="nomor_skpi" class="form-control"
                                        value="{{ old('nomor_skpi', $documentMeta['nomor_skpi'] ?? '') }}">
                                </div>
                                <div class="form-group">
                                    <label>Kota & Tanggal Pengesahan</label>
                                    <input type="text" name="authorization_place_date" class="form-control"
                                        value="{{ old('authorization_place_date', $documentMeta['authorization_place_date'] ?? ('Sukoharjo, ' . now()->translatedFormat('d F Y'))) }}">
                                </div>
                                <div class="form-group">
                                    <label>Nama Penandatangan</label>
                                    <input type="text" name="vice_rector_name" class="form-control"
                                        value="{{ old('vice_rector_name', $documentMeta['vice_rector_name'] ?? '') }}">
                                </div>
                                <div class="form-group">
                                    <label>Jabatan Penandatangan</label>
                                    <input type="text" name="vice_rector_title" class="form-control"
                                        value="{{ old('vice_rector_title', $documentMeta['vice_rector_title'] ?? 'Wakil Rektor I') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="section-title">Digital Signature (TTD)</div>
                            <div class="signature-manager">
                                <div class="current-sig">
                                    @if(!empty($documentMeta['signature_data_uri']) || !empty($documentMeta['signature_url']))
                                    <div class="sig-preview-box">
                                        <img src="{{ $documentMeta['signature_data_uri'] ?? $documentMeta['signature_url'] }}" id="sig-img-preview">
                                        <span class="preview-badge">Aktif</span>
                                    </div>
                                    @else
                                    <div class="sig-empty-box" id="sig-empty">
                                        <i class="bi bi-image"></i>
                                        <span>Belum ada TTD</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="sig-upload">
                                    <label for="sig-input" class="upload-area">
                                        <i class="bi bi-cloud-upload"></i>
                                        <strong>Upload Tanda Tangan Baru</strong>
                                        <span>PNG/JPG transparan lebih baik</span>
                                        <input type="file" name="signature_image" id="sig-input" hidden onchange="previewSignature(this)">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-footer-save">
                            <button type="submit" class="btn-save-large">
                                <i class="bi bi-save2-fill"></i> Simpan Pengaturan Pengesahan
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Tab Content: Point 4 – Kualifikasi & Capaian Pembelajaran (per prodi) --}}
                <div id="tab-kualifikasi" class="tab-pane">
                    <form method="POST" action="{{ route('admin.skpi.input-data-akademi.store-learning-outcome') }}" class="setup-form">
                        @csrf
                        <input type="hidden" name="study_program_id" value="{{ $selectedStudyProgram->id }}">

                        <div class="form-section">
                            <div class="section-title">Point 4 – Informasi Tentang Kualifikasi &amp; Hasil yang Dicapai</div>
                            <p class="section-desc">
                                Isi sesuai capaian pembelajaran resmi Program Studi <strong>{{ $selectedStudyProgram->name }}</strong>.
                                Konten ini akan dicetak di Point 4 dokumen SKPI mahasiswa dari prodi ini.
                            </p>

                            {{-- Kategori 1: Sikap --}}
                            <div class="category-section" style="margin-bottom: 40px; border-bottom: 1px solid #E2E8F0; padding-bottom: 20px;">
                                <h5 style="font-weight: 600; margin-bottom: 15px;"><span class="cp-badge cp-sikap">1</span> Sikap / tata nilai (Attitudes)</h5>

                                <div id="repeater-sikap">
                                    @php
                                    // Pastikan jadi array, antisipasi null atau data lama berupa string
                                    $rawSikap = old('cp_sikap', $learningOutcome->cp_sikap);
                                    $rawSikapEn = old('cp_sikap_en', $learningOutcome->cp_sikap_en);

                                    $sikapList = is_array($rawSikap) ? $rawSikap : (is_string($rawSikap) && !empty(trim($rawSikap)) ? [$rawSikap] : []);
                                    $sikapEnList = is_array($rawSikapEn) ? $rawSikapEn : (is_string($rawSikapEn) && !empty(trim($rawSikapEn)) ? [$rawSikapEn] : []);

                                    if (empty($sikapList)) $sikapList = ['']; // Minimal 1 row kosong
                                    @endphp

                                    @foreach($sikapList as $index => $sikapText)
                                    <div class="repeater-item form-group-dual" style="margin-bottom: 20px; padding: 15px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0; position: relative;">
                                        <div class="lang-column">
                                            <label>Poin (Indonesian)</label>
                                            <textarea name="cp_sikap[]" class="form-control" rows="3" placeholder="Tuliskan poin Sikap...">{{ $sikapText }}</textarea>
                                        </div>
                                        <div class="lang-column">
                                            <label>Point (English)</label>
                                            <textarea name="cp_sikap_en[]" class="form-control" rows="3" placeholder="Write Attitude point in English...">{{ $sikapEnList[$index] ?? '' }}</textarea>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-point" style="position: absolute; top: -10px; right: -10px; border-radius: 50%; padding: 5px 8px; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><i class="bi bi-trash"></i></button>
                                    </div>
                                    @endforeach
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-primary btn-add-point" data-target="#repeater-sikap" data-name="cp_sikap" data-name-en="cp_sikap_en">
                                    <i class="bi bi-plus-circle"></i> Tambah Poin Sikap
                                </button>
                                <div class="mt-2" style="font-size: 13px; color: #64748B;">Placeholder Word: <code>${CP_SIKAP}</code> dan <code>${CP_SIKAP_EN}</code></div>
                            </div>

                            {{-- Kategori 2: Kemampuan Kerja & Pengetahuan --}}
                            <div class="category-section" style="margin-bottom: 30px;">
                                <h5 style="font-weight: 600; margin-bottom: 15px;"><span class="cp-badge cp-pengetahuan">2</span> Kemampuan Kerja Dan Penguasaan Pengetahuan</h5>

                                <div id="repeater-pengetahuan">
                                    @php
                                    $rawP = old('cp_pengetahuan', $learningOutcome->cp_pengetahuan);
                                    $rawPEn = old('cp_pengetahuan_en', $learningOutcome->cp_pengetahuan_en);

                                    $pList = is_array($rawP) ? $rawP : (is_string($rawP) && !empty(trim($rawP)) ? [$rawP] : []);
                                    $pEnList = is_array($rawPEn) ? $rawPEn : (is_string($rawPEn) && !empty(trim($rawPEn)) ? [$rawPEn] : []);

                                    if (empty($pList)) $pList = ['']; // Minimal 1 row kosong
                                    @endphp

                                    @foreach($pList as $index => $pText)
                                    <div class="repeater-item form-group-dual" style="margin-bottom: 20px; padding: 15px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0; position: relative;">
                                        <div class="lang-column">
                                            <label>Poin (Indonesian)</label>
                                            <textarea name="cp_pengetahuan[]" class="form-control" rows="4" placeholder="Tuliskan poin Kemampuan Kerja & Pengetahuan...">{{ $pText }}</textarea>
                                        </div>
                                        <div class="lang-column">
                                            <label>Point (English)</label>
                                            <textarea name="cp_pengetahuan_en[]" class="form-control" rows="4" placeholder="Write Work ability & Knowledge point in English...">{{ $pEnList[$index] ?? '' }}</textarea>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-point" style="position: absolute; top: -10px; right: -10px; border-radius: 50%; padding: 5px 8px; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><i class="bi bi-trash"></i></button>
                                    </div>
                                    @endforeach
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-success btn-add-point" data-target="#repeater-pengetahuan" data-name="cp_pengetahuan" data-name-en="cp_pengetahuan_en">
                                    <i class="bi bi-plus-circle"></i> Tambah Poin Kemampuan Kerja
                                </button>
                                <div class="mt-2" style="font-size: 13px; color: #64748B;">Placeholder Word: <code>${CP_PENGETAHUAN}</code> dan <code>${CP_PENGETAHUAN_EN}</code></div>
                            </div>
                        </div>

                        <div class="form-footer-save">
                            <button type="submit" class="btn-save-large">
                                <i class="bi bi-save2-fill"></i> Simpan Kualifikasi &amp; Capaian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="empty-selection-state">
                <div class="empty-box">
                    <i class="bi bi-arrow-left-circle"></i>
                    <h3>Pilih Program Studi</h3>
                    <p>Silakan pilih program studi di sebelah kiri untuk mulai mengisi data akademik SKPI.</p>
                </div>
            </div>
            @endif
        </main>
    </div>
</div>


{{-- Modal Tambah Prodi --}}
<div id="prodiModal" class="custom-modal-overlay" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Tambah Program Studi</h3>
            <button onclick="closeProdiModal()"><i class="bi bi-x"></i></button>
        </div>
        <form action="{{ route('admin.skpi.input-data-akademi.store-prodi') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Program Studi</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Informatika" required autofocus>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeProdiModal()">Batal</button>
                <button type="submit" class="btn-confirm">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(tabId) {
        // Remove active class from all buttons and panes
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));

        // Add active class to selected button and pane
        if (event) {
            event.currentTarget.classList.add('active');
        } else {
            const btn = document.querySelector(`.tab-btn[onclick*="switchTab('${tabId}')"]`);
            if (btn) btn.classList.add('active');
        }
        document.getElementById('tab-' + tabId).classList.add('active');

        // Save active tab to localStorage if you want persistence
        localStorage.setItem('skpi_active_tab', tabId);

        // Re-apply lock state to new inputs in tab
        const isLocked = document.querySelector('.setup-content').classList.contains('is-locked');
        setInputsState(isLocked);
    }

    function setEditMode(enable) {
        const lockBtn = document.getElementById('btn-lock');
        const unlockBtn = document.getElementById('btn-unlock');
        const content = document.querySelector('.setup-content');

        if (enable) {
            lockBtn.classList.remove('active');
            unlockBtn.classList.add('active');
            content.classList.remove('is-locked');
        } else {
            lockBtn.classList.add('active');
            unlockBtn.classList.remove('active');
            content.classList.add('is-locked');
        }

        setInputsState(!enable);
        localStorage.setItem('skpi_edit_mode', enable ? 'unlocked' : 'locked');
    }

    function setInputsState(disabled) {
        const container = document.querySelector('.setup-content');
        if (!container) return;

        const inputs = container.querySelectorAll('input, textarea, select, button[type="submit"]');
        inputs.forEach(input => {
            // Don't disable the tab buttons
            if (!input.classList.contains('tab-btn')) {
                input.disabled = disabled;
            }
        });
    }

    // Modal Handlers
    function openProdiModal() {
        document.getElementById('prodiModal').style.display = 'flex';
    }

    function closeProdiModal() {
        document.getElementById('prodiModal').style.display = 'none';
    }


    function previewSignature(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('sig-img-preview');
                if (img) {
                    img.src = e.target.result;
                } else {
                    // If empty, create image element
                    const empty = document.getElementById('sig-empty');
                    empty.innerHTML = `<img src="${e.target.result}" id="sig-img-preview"><span class="preview-badge">Preview</span>`;
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Auto-restore tab and edit mode from localStorage
    window.onload = () => {
        const activeTab = localStorage.getItem('skpi_active_tab');
        if (activeTab) {
            const btn = document.querySelector(`.tab-btn[onclick="switchTab('${activeTab}')"]`);
            if (btn) btn.click();
        }

        const editMode = localStorage.getItem('skpi_edit_mode') || 'locked';
        setEditMode(editMode === 'unlocked');
    };

    // Tambah Poin
    document.querySelectorAll('.btn-add-point').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetContainer = document.querySelector(targetId);
            const nameId = this.getAttribute('data-name');
            const nameEn = this.getAttribute('data-name-en');

            // Ambil label placeholder dari row pertama
            const firstRow = targetContainer.querySelector('.repeater-item');
            const labels = firstRow ? firstRow.querySelectorAll('.lang-column label') : null;
            const labelId = (labels && labels.length > 0) ? labels[0].innerText : 'Poin (Indonesian)';
            const labelEn = (labels && labels.length > 1) ? labels[1].innerText : 'Point (English)';

            const newItem = document.createElement('div');
            newItem.className = 'repeater-item form-group-dual';
            newItem.style.cssText = 'margin-bottom: 20px; padding: 15px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0; position: relative;';
            newItem.innerHTML = `
                <div class="lang-column">
                    <label>${labelId}</label>
                    <textarea name="${nameId}[]" class="form-control" rows="3" placeholder="Tuliskan poin..."></textarea>
                </div>
                <div class="lang-column">
                    <label>${labelEn}</label>
                    <textarea name="${nameEn}[]" class="form-control" rows="3" placeholder="Write point..."></textarea>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-point" style="position: absolute; top: -10px; right: -10px; border-radius: 50%; padding: 5px 8px; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><i class="bi bi-trash"></i></button>
            `;
            targetContainer.appendChild(newItem);
        });
    });

    // Hapus Poin
    document.addEventListener('click', function(e) {
        if (e.target && e.target.closest('.btn-remove-point')) {
            const btn = e.target.closest('.btn-remove-point');
            const item = btn.closest('.repeater-item');
            const container = item.parentNode;

            if (container.querySelectorAll('.repeater-item').length > 1) {
                item.remove();
            } else {
                alert('Minimal harus ada 1 form isian. Kosongkan isinya jika tidak ingin dicetak.');
            }
        }
    });
</script>
@endpush

@push('css')
<style>
    :root {
        --bg-soft: #F1F5F9;
        --primary: #29375d;
        --accent: #FF9800;
        --success: #10B981;
        --warning: #F59E0B;
        --border: #E2E8F0;
        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }

    .skpi-setup-shell {
        max-width: 1300px;
        margin: 0 auto;
        padding-bottom: 50px;
    }

    /* Header */
    .setup-header {
        background: white;
        padding: 30px;
        border-radius: 20px;
        margin-bottom: 25px;
        box-shadow: var(--card-shadow);
    }

    .header-main {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .title-area h1 {
        font-size: 26px;
        font-weight: 800;
        color: var(--primary);
        margin: 0 0 5px;
    }

    .title-area p {
        color: #64748B;
        font-size: 14px;
        margin: 0;
    }

    .header-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid var(--border);
    }

    .stats-group {
        display: flex;
        gap: 30px;
    }

    .stat-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .stat-item .label {
        font-size: 12px;
        color: #94A3B8;
        font-weight: 600;
        text-transform: uppercase;
    }

    .stat-item .value {
        font-size: 22px;
        font-weight: 700;
        color: var(--primary);
    }

    .stat-item .value.success {
        color: var(--success);
    }

    .stat-item .value.warning {
        color: var(--warning);
    }

    /* Layout */
    .setup-layout {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 25px;
        align-items: start;
    }

    /* Sidebar */
    .prodi-sidebar {
        position: sticky;
        top: 100px;
    }

    .sidebar-inner {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--card-shadow);
    }

    .sidebar-label {
        font-size: 13px;
        font-weight: 700;
        color: #94A3B8;
        text-transform: uppercase;
        margin-bottom: 15px;
        padding-left: 5px;
    }

    .prodi-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .prodi-item-wrapper {
        position: relative;
    }

    .prodi-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-radius: 14px;
        background: #F8FAFC;
        text-decoration: none;
        border: 1px solid transparent;
        transition: all 0.2s;
    }

    .prodi-card:hover {
        background: #F1F5F9;
        transform: translateX(5px);
    }

    .prodi-card.active {
        background: white;
        border-color: var(--accent);
        box-shadow: 0 10px 15px -3px rgba(255, 152, 0, 0.1);
    }

    .prodi-info strong {
        display: block;
        font-size: 15px;
        color: var(--primary);
        margin-bottom: 4px;
    }

    .prodi-meta {
        font-size: 11px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .prodi-meta .count {
        color: #64748B;
    }

    .prodi-meta .status.ready {
        color: var(--success);
        font-weight: 700;
    }

    .prodi-meta .status.draft {
        color: var(--warning);
        font-weight: 700;
    }

    .prodi-arrow {
        color: #CBD5E1;
        font-size: 14px;
    }

    .prodi-card.active .prodi-arrow {
        color: var(--accent);
    }

    .btn-icon-delete {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 22px;
        height: 22px;
        background: #EF4444;
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 12px;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    .prodi-item-wrapper:hover .btn-icon-delete {
        opacity: 1;
    }

    /* Content Area */
    .setup-content {
        min-width: 0;
    }

    .content-container {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }

    /* Tabs */
    .tabs-nav {
        display: flex;
        background: #F8FAFC;
        border-bottom: 1px solid var(--border);
        padding: 0 10px;
    }

    .tab-btn {
        flex: 1;
        padding: 20px 15px;
        border: none;
        background: transparent;
        font-size: 14px;
        font-weight: 600;
        color: #64748B;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.2s;
        border-bottom: 3px solid transparent;
    }

    .tab-btn:hover {
        color: var(--primary);
        background: #F1F5F9;
    }

    .tab-btn.active {
        color: var(--accent);
        border-bottom-color: var(--accent);
    }

    .tab-btn i {
        font-size: 18px;
    }

    .tab-pane {
        display: none;
        padding: 35px;
    }

    .tab-pane.active {
        display: block;
    }

    /* Form Styles */
    .form-section {
        margin-bottom: 40px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 2px solid #F1F5F9;
    }

    .section-desc {
        color: #64748B;
        font-size: 13px;
        margin-top: -15px;
        margin-bottom: 25px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group-full {
        width: 100%;
    }

    .form-group-span {
        grid-column: 1 / -1;
    }

    .label-with-badge {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .label-badge-admin {
        display: inline-flex;
        padding: 2px 8px;
        border-radius: 999px;
        background: linear-gradient(135deg, #29375d, #3d5080);
        color: white;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.03em;
    }

    .field-hint {
        font-size: 12px;
        color: #64748B;
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: -4px;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 700;
        color: #475569;
    }

    .form-control {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        color: var(--primary);
        transition: all 0.2s;
    }

    .form-control:focus {
        background: white;
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.1);
    }

    .form-control.fixed-height {
        resize: none;
    }

    .error-msg {
        color: #EF4444;
        font-size: 12px;
        font-weight: 500;
    }

    .form-footer-save {
        margin-top: 20px;
        padding-top: 30px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
    }

    .btn-save-large {
        background: var(--accent);
        color: white;
        border: none;
        padding: 15px 35px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 10px 15px -3px rgba(255, 152, 0, 0.3);
        transition: all 0.2s;
    }

    .btn-save-large:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 25px -5px rgba(255, 152, 0, 0.4);
    }

    /* Signature Manager */
    .signature-manager {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 25px;
        align-items: center;
        background: #F8FAFC;
        padding: 25px;
        border-radius: 16px;
        border: 1px solid var(--border);
    }

    .sig-preview-box {
        position: relative;
        width: 240px;
        height: 120px;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
    }

    .sig-preview-box img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
    }

    .preview-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        background: var(--success);
        color: white;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 700;
    }

    .sig-empty-box {
        width: 240px;
        height: 120px;
        background: #F1F5F9;
        border: 2px dashed #CBD5E1;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94A3B8;
        gap: 8px;
    }

    .upload-area {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        border: 2px dashed #CBD5E1;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .upload-area:hover {
        border-color: var(--accent);
        background: white;
    }

    .upload-area i {
        font-size: 30px;
        color: var(--accent);
        margin-bottom: 5px;
    }

    /* Empty States */
    .empty-selection-state {
        padding: 100px 35px;
        text-align: center;
    }

    .empty-box i {
        font-size: 60px;
        color: #E2E8F0;
        margin-bottom: 15px;
        display: block;
    }

    .empty-box h3 {
        color: var(--primary);
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .empty-box p {
        color: #64748B;
        font-size: 15px;
    }

    /* Buttons */
    .btn-primary-custom {
        background: var(--primary);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary-custom:hover {
        background: #1E293B;
        transform: translateY(-1px);
    }

    /* Custom Alert */
    .custom-alert {
        padding: 15px 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        font-size: 14px;
    }

    .custom-alert.success {
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
    }

    /* Modal */
    .custom-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-card {
        background: white;
        width: 100%;
        max-width: 450px;
        border-radius: 20px;
        overflow: hidden;
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        padding: 20px 25px;
        background: #F8FAFC;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }

    .modal-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: var(--primary);
        margin: 0;
    }

    .modal-header button {
        background: transparent;
        border: none;
        font-size: 24px;
        color: #94A3B8;
        cursor: pointer;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        padding: 15px 25px;
        background: #F8FAFC;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        border-top: 1px solid var(--border);
    }

    .btn-cancel {
        background: transparent;
        border: 1px solid var(--border);
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-confirm {
        background: var(--accent);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
    }

    /* Responsiveness */
    @media (max-width: 1024px) {
        .setup-layout {
            grid-template-columns: 1fr;
        }

        .prodi-sidebar {
            position: static;
            margin-bottom: 25px;
        }

        .prodi-list {
            flex-direction: row;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .prodi-item-wrapper {
            flex: 0 0 250px;
        }
    }

    /* Edit Mode Toggle Styles */
    .edit-mode-toggle {
        display: flex;
        background: #F1F5F9;
        padding: 4px;
        border-radius: 12px;
        border: 1px solid var(--border);
    }

    .btn-mode {
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        color: #64748B;
        background: transparent;
    }

    .btn-mode.active#btn-lock {
        background: #FEE2E2;
        color: #DC2626;
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.1);
    }

    .btn-mode.active#btn-unlock {
        background: var(--success);
        color: white;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    }

    .setup-content.is-locked .tab-pane {
        opacity: 0.85;
    }

    .setup-content.is-locked .btn-save-large,
    .setup-content.is-locked .btn-add-point,
    .setup-content.is-locked .btn-remove-point {
        display: none;
    }

    .setup-content.is-locked .form-control {
        background: #F8FAFC;
        border-color: #E2E8F0;
        cursor: not-allowed;
    }

    /* === Point 4: Capaian Pembelajaran Badges === */
    .section-desc {
        font-size: 13px;
        color: #64748B;
        margin: -8px 0 20px;
        line-height: 1.6;
    }

    .cp-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 800;
        margin-right: 6px;
        vertical-align: middle;
        flex-shrink: 0;
    }

    .cp-sikap {
        background: #DBEAFE;
        color: #1D4ED8;
    }

    .cp-pengetahuan {
        background: #D1FAE5;
        color: #065F46;
    }

    .cp-ktr-umum {
        background: #FEF3C7;
        color: #92400E;
    }

    .cp-ktr-khusus {
        background: #F3E8FF;
        color: #6B21A8;
    }

    .field-hint {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: #94A3B8;
    }

    .field-hint code {
        background: #F1F5F9;
        border: 1px solid #E2E8F0;
        border-radius: 4px;
        padding: 1px 5px;
        font-size: 11px;
        color: #475569;
        font-family: 'Courier New', monospace;
    }

    /* Bilingual Dual Column Layout */
    .form-group-dual {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }

    .lang-column {
        display: flex;
        flex-direction: column;
    }

    @media (max-width: 768px) {
        .form-group-dual {
            grid-template-columns: 1fr;
            gap: 15px;
        }
    }
</style>@endpush