@extends('admin.layouts.super-app')

@php
    use App\Models\StudentAchievement;

    $formatDate = fn ($date) => $date ? $date->translatedFormat('d F Y') : '-';

    // Kategori transkrip SKPI sesuai dokumen panduan
    $transkripCategories = [
        'wajib' => 'Wajib Universitas',
        'organisasi' => 'Kegiatan Bidang Organisasi dan Kepemimpinan',
        'penalaran' => 'Kegiatan Bidang Penalaran dan Keilmuan',
        'minat_bakat' => 'Kegiatan Bidang Minat dan Bakat',
        'kepedulian_sosial' => 'Kegiatan Bidang Kepedulian Sosial',
        'lainnya' => 'Kegiatan Lainnya',
        'volunteer' => 'Volunteer Mahasiswa',
    ];

    // Group achievements by category
    $groupedByCategory = [];
    $skpByCategory = [];
    foreach ($transkripCategories as $catKey => $catLabel) {
        $items = $selectedAchievements->where('category', $catKey)->values();
        $groupedByCategory[$catKey] = $items;
        $skpByCategory[$catKey] = $items->sum('skp_points');
    }

    $totalSkp = array_sum($skpByCategory);

    // Predikat SKPI S1
    if ($totalSkp > 251) {
        $predikat = 'Sangat Baik';
    } elseif ($totalSkp >= 151) {
        $predikat = 'Baik';
    } elseif ($totalSkp >= 80) {
        $predikat = 'Cukup';
    } else {
        $predikat = '-';
    }

    // Build text for old template sections
    $buildAchievementText = function ($achievement) {
        return collect([
            $achievement->activity_type_label ?? $achievement->activity_type,
            filled($achievement->level) && $achievement->level !== '-' ? '(' . $achievement->level . ')' : null,
            filled($achievement->participation_role) && $achievement->participation_role !== '-' ? $achievement->participation_role : null,
        ])->filter()->implode(' - ');
    };

    $templatePayload = [
        'nomor' => $selectedRegistration?->nomor_skpi ?? $documentMeta['nomor_skpi'] ?? '',
        'nama' => $selectedRegistration?->nama_lengkap ?? '',
        'ttl' => collect([
            $selectedRegistration?->tempat_lahir,
            $selectedRegistration?->tanggal_lahir?->translatedFormat('d F Y'),
        ])->filter()->implode(', '),
        'nim' => $selectedRegistration?->nim ?? '',
        'tahun_masuk' => $selectedRegistration?->angkatan ?? '',
        'no_ijazah' => $selectedRegistration?->nomor_ijazah ?? '',
        'gelar' => $selectedRegistration?->gelar ?? '',
        'sk_pt' => $academicProfile?->sk_pendirian_perguruan_tinggi ?? '',
        'nama_pt' => $academicProfile?->nama_perguruan_tinggi ?? 'UNIVERSITAS SUGENG HARTONO',
        'akr_pt' => $academicProfile?->akreditasi_perguruan_tinggi ?? '',
        'prodi' => $selectedStudent?->program_studi ?? '',
        'akr_prodi' => $academicProfile?->akreditasi_program_studi ?? '',
        'jenis_jenjang' => $academicProfile?->jenis_dan_jenjang_pendidikan ?? '',
        'kkni_level' => $academicProfile?->jenjang_kualifikasi_kkni ?? '',
        'entry_req' => $academicProfile?->persyaratan_penerimaan ?? '',
        'bahasa_pengantar' => $academicProfile?->bahasa_pengantar_kuliah ?? 'Inggris / Indonesia',
        'no_akr_pt' => $academicProfile?->nomor_akreditasi_perguruan_tinggi ?? '',
        'sistem_penilaian' => $academicProfile?->sistem_penilaian ?? 'Skala/Scale : 0-4 : A=4, A-=3.75, B+=3.5, B=3, C=2, D=1, E=0',
        'lama_studi' => $academicProfile?->lama_studi ?? '',
        'no_akr_prodi' => $academicProfile?->nomor_akreditasi_program_studi ?? '',
        'prof_status' => $academicProfile?->status_profesi ?? '-',
        'prestasi' => $groupedByCategory['penalaran']->map($buildAchievementText)->values()->all(),
        'organisasi' => $groupedByCategory['organisasi']->map($buildAchievementText)->values()->all(),
        'magang' => $groupedByCategory['lainnya']->map($buildAchievementText)->values()->all(),
        'pelatihan' => [],
        'sertif' => $groupedByCategory['wajib']->map($buildAchievementText)->values()->all(),
        'skripsi_id' => optional($automaticEntries->first())->event ?? ($selectedStudent?->finalProject?->title ?? ''),
        'skripsi_en' => optional($automaticEntries->first())->event ?? ($selectedStudent?->finalProject?->title ?? ''),
        'kota_tgl' => $documentMeta['authorization_place_date'] ?? ('Sukoharjo, ' . now()->translatedFormat('d F Y')),
        'vice_rector_name' => $documentMeta['vice_rector_name'] ?? '',
        'vice_rector_title' => $documentMeta['vice_rector_title'] ?? 'Wakil Rektor I Universitas Sugeng Hartono',
        'signature_url' => $documentMeta['signature_url'] ?? null,
    ];
@endphp

@section('content')
    <div class="page-shell">
        <div class="hero-card">
            <div>
                <span class="hero-badge">Generate Draft SKPI</span>
                <h3>Siapkan Data Cetak SKPI</h3>
            </div>
            <div class="hero-stats">
                <div class="stat-chip">
                    <span>Mahasiswa Approved</span>
                    <strong>{{ $stats['approved_registrations'] }}</strong>
                </div>
                <div class="stat-chip">
                    <span>Prestasi Approved</span>
                    <strong>{{ $stats['approved_achievements'] }}</strong>
                </div>
                <div class="stat-chip">
                    <span>Prestasi Dipilih</span>
                    <strong>{{ $stats['selected_achievements'] }}</strong>
                </div>
            </div>
        </div>

        <div class="content-card">
            <form method="GET" action="{{ route('admin.skpi.generate-skpi.index') }}" class="generate-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="registration_id">Mahasiswa / Pendaftar SKPI Approved</label>
                        <select name="registration_id" id="registration_id" class="form-control" onchange="this.form.submit()">
                            <option value="">Pilih mahasiswa</option>
                            @foreach($approvedRegistrations as $registration)
                                <option value="{{ $registration->id }}" {{ $selectedRegistrationId === $registration->id ? 'selected' : '' }}>
                                    {{ $registration->nim }} - {{ $registration->nama_lengkap }} - {{ $registration->student->program_studi ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="achievement_ids">Aktivitas / Prestasi Approved Yang Akan Dipakai</label>
                        <select name="achievement_ids[]" id="achievement_ids" class="form-control multi-select" multiple size="6">
                            @forelse($approvedAchievements as $achievement)
                                <option value="{{ $achievement->id }}" {{ in_array($achievement->id, $selectedAchievementIds, true) ? 'selected' : '' }}>
                                    {{ $achievement->category_label }} - {{ $achievement->activity_type_label ?? $achievement->activity_type }} ({{ $achievement->level }}) {{ $achievement->skp_points }} SKP
                                </option>
                            @empty
                                <option value="" disabled>Belum ada prestasi approved</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-funnel"></i> Terapkan Pilihan
                    </button>
                </div>
            </form>
        </div>


        @if($selectedRegistration && $selectedStudent)
            <div class="summary-grid">
                <div class="summary-card">
                    <span class="summary-label">Pemegang SKPI</span>
                    <h4>{{ $selectedRegistration->nama_lengkap }}</h4>
                    <p>{{ $selectedRegistration->nim }} • {{ $selectedStudent->program_studi ?? '-' }}</p>
                    <div class="summary-meta">
                        <span>Status Pendaftaran</span>
                        <strong>Approved</strong>
                    </div>
                    <div class="summary-meta">
                        <span>Disetujui Oleh</span>
                        <strong>{{ $selectedRegistration->approver->name ?? '-' }}</strong>
                    </div>
                </div>

                <div class="summary-card">
                    <span class="summary-label">Profil Akademik Prodi</span>
                    <h4>{{ $selectedStudyProgram->name ?? 'Belum Terhubung' }}</h4>
                    <p>{{ $academicProfile ? 'Data akademik prodi sudah ditemukan untuk generate.' : 'Data akademik prodi belum diinput di menu Input Data Akademik.' }}</p>
                    <div class="summary-meta">
                        <span>Akreditasi Prodi</span>
                        <strong>{{ $academicProfile?->akreditasi_program_studi ?? '-' }}</strong>
                    </div>
                    <div class="summary-meta">
                        <span>Jenjang KKNI</span>
                        <strong>{{ $academicProfile?->jenjang_kualifikasi_kkni ?? '-' }}</strong>
                    </div>
                </div>

                <div class="summary-card">
                    <span class="summary-label">Aktivitas Terpilih</span>
                    <h4>{{ $selectedAchievements->count() }} Data</h4>
                    <p>{{ $selectedAchievements->count() > 0 ? 'Data aktivitas ini siap ditarik ke draft generate SKPI.' : 'Belum ada data approved yang dipilih untuk mahasiswa ini.' }}</p>
                    <div class="summary-meta">
                        <span>Total Approved</span>
                        <strong>{{ $approvedAchievements->count() }}</strong>
                    </div>
                    <div class="summary-meta">
                        <span>Judul Tugas Akhir</span>
                        <strong>{{ $selectedStudent->finalProject?->title ?? '-' }}</strong>
                    </div>
                </div>
            </div>

            <div class="content-grid">
                <div class="content-card">
                    <div class="section-header">
                        <div>
                            <h4>Data Akademik Mahasiswa</h4>
                            <p>Data akademik ini ikut disiapkan saat proses generate SKPI.</p>
                        </div>
                    </div>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <span>Program Studi</span>
                            <strong>{{ $selectedStudent->program_studi ?? '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>IPK</span>
                            <strong>{{ $selectedStudent->ipk ?? '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>SKS</span>
                            <strong>{{ $selectedStudent->sks ?? '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Judul Skripsi / Tugas Akhir</span>
                            <strong>{{ $selectedStudent->finalProject?->title ?? '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Tanggal Masuk</span>
                            <strong>{{ $selectedStudent->tanggal_masuk?->translatedFormat('d F Y') ?? '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Tanggal Lulus</span>
                            <strong>{{ $selectedStudent->tanggal_lulus?->translatedFormat('d F Y') ?? '-' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="section-header">
                        <div>
                            <h4>Data Pemegang SKPI</h4>
                            <p>Field utama hasil pendaftaran mahasiswa yang sudah approved.</p>
                        </div>
                    </div>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <span>Nama Lengkap</span>
                            <strong>{{ $selectedRegistration->nama_lengkap }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Tempat / Tanggal Lahir</span>
                            <strong>{{ $selectedRegistration->tempat_lahir }}, {{ $selectedRegistration->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>NIM</span>
                            <strong>{{ $selectedRegistration->nim }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Tahun Masuk</span>
                            <strong>{{ $selectedRegistration->angkatan }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Nomor Ijazah</span>
                            <strong>{{ $selectedRegistration->nomor_ijazah }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Gelar</span>
                            <strong>{{ $selectedRegistration->gelar }}</strong>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="section-header">
                        <div>
                            <h4>Identitas Penyelenggara Program</h4>
                            <p>Data ini diambil dari menu input data akademik sesuai program studi mahasiswa.</p>
                        </div>
                    </div>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <span>Nama Perguruan Tinggi</span>
                            <strong>{{ $academicProfile?->nama_perguruan_tinggi ?? '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>SK Pendirian PT</span>
                            <strong>{{ $academicProfile?->sk_pendirian_perguruan_tinggi ?? '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Akreditasi PT</span>
                            <strong>{{ $academicProfile?->akreditasi_perguruan_tinggi ?? '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Akreditasi Prodi</span>
                            <strong>{{ $academicProfile?->akreditasi_program_studi ?? '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Jenis / Jenjang</span>
                            <strong>{{ $academicProfile?->jenis_dan_jenjang_pendidikan ?? '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Bahasa Pengantar</span>
                            <strong>{{ $academicProfile?->bahasa_pengantar_kuliah ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="section-header">
                    <div>
                        <h4>Aktivitas Yang Siap Masuk SKPI</h4>
                        <p>Daftar ini berisi data manual yang sudah approved dan dipilih dari dropdown, ditambah skripsi yang otomatis diambil dari Tugas Akhir.</p>
                    </div>
                </div>

                <div class="achievement-list">
                    @foreach($automaticEntries as $entry)
                        <div class="achievement-item auto-item">
                            <div class="achievement-icon">
                                <i class="bi bi-journal-richtext"></i>
                            </div>
                            <div class="achievement-body">
                                <h5>{{ $entry->achievement }}</h5>
                                <p>{{ $entry->category_label }} • {{ $entry->event }} • Sumber: {{ $entry->source }}</p>
                            </div>
                            <span class="status-badge info">Otomatis</span>
                        </div>
                    @endforeach

                    @forelse($selectedAchievements as $achievement)
                        <div class="achievement-item">
                            <div class="achievement-icon">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <div class="achievement-body">
                                <h5>{{ $achievement->activity_type_label ?? $achievement->activity_type }}</h5>
                                <p>{{ $achievement->category_label }} • {{ $achievement->level }} • {{ $achievement->participation_role ?? '-' }} • <strong>{{ $achievement->skp_points }} SKP</strong></p></p>
                            </div>
                            <span class="status-badge active">Approved</span>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <h4>Belum ada prestasi terpilih</h4>
                            <p>Pilih mahasiswa terlebih dahulu atau pilih prestasi approved dari dropdown di atas.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="content-card note-card">
                <div class="section-header">
                    <div>
                        <h4>Catatan Generate</h4>
                        <p>Halaman ini sekarang fokus untuk menghasilkan PDF final. Preview HTML dipakai sebagai acuan tampilan, lalu dokumen diunduh dalam format `PDF`.</p>
                    </div>
                </div>
                <div class="form-actions">
                    <form method="POST" action="{{ route('admin.skpi.generate-skpi.export-pdf') }}" class="inline-action-form">
                        @csrf
                        <input type="hidden" name="registration_id" value="{{ $selectedRegistrationId }}">
                        @foreach($selectedAchievementIds as $achievementId)
                            <input type="hidden" name="achievement_ids[]" value="{{ $achievementId }}">
                        @endforeach
                        <button type="submit" class="btn-primary">
                            <i class="bi bi-file-earmark-pdf"></i> Generate SKPI.pdf
                        </button>
                    </form>
                    <button type="button" class="btn-secondary" id="generateTemplateBtn">
                        <i class="bi bi-file-earmark-richtext"></i> Preview HTML
                    </button>
                </div>

            </div>

            <div class="content-card template-preview-card" id="templatePreviewCard">
                <div class="section-header">
                    <div>
                        <h4>Preview Template SKPI</h4>

                    </div>
                </div>
                <div class="template-preview-shell">
                    <div id="templatePreviewArea">
                        <div class="empty-state compact-empty">
                            <i class="bi bi-file-earmark-text"></i>
                            <h4>Template belum digenerate</h4>
                            <p>Klik tombol <strong>Preview HTML</strong> untuk mengecek data sebelum mengunduh hasil `SKPI.pdf`.</p>
                        </div>
                    </div>
                </div>
            </div>


            @include('admin.skpi.generate.template')
        @else
            <div class="empty-state">
                <i class="bi bi-person-x"></i>
                <h4>Belum ada mahasiswa approved</h4>
                <p>Menu generate akan aktif setelah ada pendaftaran SKPI mahasiswa yang sudah disetujui.</p>
            </div>
        @endif
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
    .summary-card,
    .empty-state {
        background: white;
        border-radius: 18px;
        padding: 24px;
        box-shadow: var(--shadow);
    }

    .hero-card {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        background: linear-gradient(135deg, #FFF8EE, #FFFFFF);
        border: 1px solid #F4E5CD;
    }

    .hero-badge,
    .summary-label,
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .hero-badge,
    .summary-label {
        background: #FFF1DA;
        color: #D97706;
    }

    .hero-card h3,
    .section-header h4,
    .summary-card h4,
    .achievement-body h5,
    .empty-state h4 {
        margin: 10px 0 8px;
        color: #213555;
        font-weight: 700;
    }

    .hero-card p,
    .section-header p,
    .summary-card p,
    .detail-item span,
    .achievement-body p,
    .empty-state p,
    .form-group small {
        margin: 0;
        color: #6B7280;
        line-height: 1.6;
    }

    .hero-stats,
    .summary-grid,
    .form-grid,
    .detail-grid {
        display: grid;
        gap: 16px;
    }

    .hero-stats {
        grid-template-columns: repeat(3, minmax(120px, 1fr));
        width: min(420px, 100%);
    }

    .stat-chip,
    .summary-card,
    .detail-item,
    .achievement-item {
        border: 1px solid #ECE6DA;
        border-radius: 16px;
        background: #FFFFFF;
    }

    .stat-chip {
        padding: 16px;
    }

    .stat-chip span,
    .summary-meta span,
    .detail-item span {
        display: block;
        font-size: 12px;
        margin-bottom: 8px;
    }

    .stat-chip strong,
    .summary-meta strong,
    .detail-item strong {
        font-size: 16px;
        color: #213555;
    }

    .generate-form .form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
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
        padding: 12px 14px;
        font-size: 14px;
        font-family: inherit;
        color: #1F2937;
        background: #FFFFFF;
    }

    .form-control:focus {
        outline: none;
        border-color: #D97706;
        box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.12);
    }

    .multi-select {
        min-height: 164px;
    }

    .form-actions {
        margin-top: 18px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .btn-primary,
    .btn-secondary {
        border: none;
        border-radius: 12px;
        padding: 12px 18px;
        font-size: 14px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-primary:disabled,
    .btn-secondary:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }

    .btn-primary {
        background: #D97706;
        color: white;
    }

    .btn-secondary {
        background: #EEF2F7;
        color: #64748B;
    }

    .print-btn {
        display: none;
    }

    .summary-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .summary-card {
        padding: 20px;
    }

    .summary-card h4 {
        margin-top: 10px;
    }

    .summary-meta {
        margin-top: 14px;
    }

    .content-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .template-upload-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 20px;
    }

    .template-upload-form,
    .template-status-card {
        border: 1px solid #ECE6DA;
        border-radius: 16px;
        background: #FFFFFF;
        padding: 20px;
    }

    .placeholder-guide-card {
        margin-top: 20px;
        border: 1px solid #ECE6DA;
        border-radius: 16px;
        background: linear-gradient(135deg, #FFF8ED, #FFFFFF);
        padding: 20px;
    }

    .placeholder-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
    }

    .placeholder-grid code {
        display: block;
        padding: 10px 12px;
        border-radius: 12px;
        background: #FFFFFF;
        border: 1px solid #F0D9BA;
        color: #9A3412;
        font-size: 13px;
        font-weight: 700;
    }

    .template-status-card h4 {
        margin: 10px 0 8px;
        color: #213555;
        font-weight: 700;
    }

    .template-status-card p {
        margin: 0;
        color: #6B7280;
        line-height: 1.6;
    }

    .inline-action-form {
        margin: 0;
    }

    .inline-note {
        margin: 14px 0 0;
        font-size: 13px;
        line-height: 1.6;
        color: #7C5A10;
    }

    .signature-preview-card {
        margin-top: 18px;
        padding: 16px;
        border: 1px solid #ECE6DA;
        border-radius: 14px;
        background: #FFFCF7;
    }

    .signature-preview-card span {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #6B7280;
        margin-bottom: 10px;
    }

    .signature-preview-card img {
        max-height: 90px;
        max-width: 280px;
        object-fit: contain;
        display: block;
    }

    .section-header {
        margin-bottom: 18px;
    }

    .detail-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .detail-item {
        padding: 16px;
        background: #FAFBFC;
    }

    .achievement-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .achievement-item {
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .achievement-item.auto-item {
        background: #F8FAFC;
    }

    .achievement-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, #FF9800, #FFB347);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .achievement-body {
        flex: 1;
    }

    .achievement-body h5 {
        margin: 0 0 6px;
    }

    .status-badge.active {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.info {
        background: #E3F2FD;
        color: #1565C0;
    }

    /* ── Transkrip & Rincian Tables ── */
    .transkrip-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .transkrip-table th,
    .transkrip-table td {
        border: 1px solid #E5E7EB;
        padding: 10px 14px;
        text-align: left;
    }

    .transkrip-table thead th {
        background: #213555;
        color: white;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .transkrip-table .cat-row td {
        background: #FFF8E1;
        font-weight: 700;
        color: #92400E;
        border-bottom: 2px solid #F4E5CD;
    }

    .transkrip-table .sub-cat-row td {
        background: #FAFBFC;
        color: #374151;
    }

    .transkrip-table .total-row td {
        background: #213555;
        color: white;
        font-weight: 700;
        font-size: 15px;
    }

    .transkrip-table .subtotal-row td {
        background: #F3F4F6;
        font-weight: 700;
        color: #213555;
    }

    .rincian-cat-title {
        margin: 0 0 10px;
        padding: 10px 14px;
        background: #FFF1DA;
        border-radius: 10px;
        color: #92400E;
        font-weight: 700;
        font-size: 16px;
    }

    .rincian-sub-title {
        margin: 18px 0 8px;
        padding: 8px 14px;
        background: #FAFBFC;
        border-left: 3px solid #D97706;
        color: #374151;
        font-weight: 700;
        font-size: 14px;
    }

    .rincian-table {
        margin-bottom: 8px;
    }

    .doc-link {
        color: #1D4ED8;
        text-decoration: none;
        font-size: 18px;
    }

    .text-muted {
        color: #9CA3AF;
    }

    .note-card {
        background: linear-gradient(135deg, #FFF8E1, #FFFFFF);
    }

    .template-preview-card {
        display: none;
    }

    .template-preview-card.visible {
        display: block;
    }

    .template-preview-shell {
        background: #DFDBD2;
        border-radius: 18px;
        padding: 24px;
        overflow-x: auto;
    }

    .compact-empty {
        padding: 32px 20px;
    }

    .template-file-preview,
    .template-word-preview {
        background: #FFFFFF;
        border-radius: 16px;
        padding: 18px;
        min-height: 420px;
    }

    .template-file-preview object,
    .template-file-preview iframe {
        width: 100%;
        min-height: 820px;
        border: none;
        border-radius: 12px;
        background: #FFFFFF;
    }

    .template-word-preview {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        gap: 12px;
    }

    .template-word-preview i {
        font-size: 52px;
        color: #D97706;
    }

    .template-word-preview h4 {
        margin: 0;
        color: #213555;
        font-weight: 700;
    }

    .template-word-preview p {
        margin: 0;
        color: #6B7280;
        max-width: 560px;
    }

    .skpi-doc {
        background: #FFFFFF;
        width: 100%;
        max-width: 820px;
        margin: 0 auto;
        box-shadow: 0 4px 40px rgba(0, 0, 0, 0.18);
        font-family: "Times New Roman", Georgia, serif;
        font-size: 11px;
        line-height: 1.5;
        color: #000000;
    }

    .skpi-doc table {
        width: 100%;
        border-collapse: collapse;
    }

    .skpi-doc td,
    .skpi-doc th {
        border: 1px solid #000000;
        padding: 5px 8px;
        vertical-align: top;
    }

    .doc-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 0 90px 10px 8px;
    }

    .doc-header .logo-col {
        width: 82px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .doc-header .logo-col img {
        width: 68px;
        height: 68px;
        object-fit: contain;
    }

    .doc-header .logo-placeholder {
        width: 70px;
        height: 70px;
        background: #F0F0F0;
        border: 1px dashed #999999;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        color: #999999;
        text-align: center;
    }

    .doc-header .identity-col {
        flex: 1;
        color: #7D7D7D;
    }

    .doc-header .identity-col .campus-title {
        font-size: 25px;
        font-weight: bold;
        line-height: 1.1;
        margin-bottom: 3px;
    }

    .doc-header .identity-col .campus-subtitle {
        font-size: 18px;
        font-style: italic;
        font-weight: bold;
    }

    .doc-rule {
        border-top: 1px solid #000000;
        margin: 0 90px 9px 90px;
    }

    .doc-title-block {
        margin: 0 90px 10px 90px;
        margin-bottom: 10px;
    }

    .doc-main-title {
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 2px;
    }

    .doc-sub-title {
        font-size: 11px;
        font-style: italic;
        font-weight: bold;
        margin-bottom: 2px;
    }

    .doc-number {
        font-size: 11px;
    }

    .doc-intro {
        padding: 0 90px 10px 90px;
        font-size: 10.5px;
    }

    .doc-intro p {
        margin-bottom: 6px;
    }

    .doc-intro .en {
        font-style: italic;
    }

    .doc-section {
        border-top: none;
        margin-left: 90px;
        margin-right: 90px;
        margin-top: 14px;
    }

    .section-title-row {
        padding: 5px 8px;
        background: #EFEFEF;
        font-weight: bold;
        font-size: 11px;
        border-bottom: 1px solid #000000;
    }

    .section-title-row .en {
        font-style: italic;
        font-weight: normal;
    }

    .field-table .label-col {
        width: 38%;
    }

    .field-table .sep-col {
        width: 3%;
        text-align: center;
    }

    .field-table .value-col {
        width: 59%;
    }

    .field-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .field-table tr:nth-child(odd) td {
        background: #F1F1F1;
    }

    .field-table tr:nth-child(even) td {
        background: #FFFFFF;
    }

    .field-table td {
        border: none;
        padding: 8px 10px;
    }

    .list-section {
        padding: 0;
    }

    .list-section .list-block:nth-child(odd),
    .thesis-section {
        background: #F1F1F1;
    }

    .list-section .list-block:nth-child(even) {
        background: #FFFFFF;
    }

    .list-section .list-block,
    .thesis-section {
        padding: 8px 10px;
    }

    .list-section .list-cat {
        font-weight: bold;
        font-size: 10.5px;
        margin: 0 0 4px;
    }

    .list-section .list-cat .en,
    .list-section .li-en,
    .thesis-section .thesis-en,
    .outcomes-table .cat-header .en {
        font-style: italic;
        font-weight: normal;
    }

    .list-section ul,
    .thesis-section ul {
        margin-left: 20px;
        margin-bottom: 0;
    }

    .list-section li {
        margin-bottom: 2px;
        font-size: 10.5px;
    }

    .list-section .li-id,
    .thesis-section .thesis-id {
        font-weight: bold;
    }

    .thesis-section {
        border-top: none;
    }

    .thesis-section p {
        font-size: 10.5px;
        margin: 2px 0;
    }

    .outcomes-table td,
    .kkni-table td,
    .footer-table td {
        border: 1px solid #000000;
        padding: 6px 8px;
        font-size: 10px;
        vertical-align: top;
    }

    .footer-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .footer-table td {
        border: none;
        padding: 0;
    }

    .footer-table td:first-child {
        padding-right: 20px;
    }

    .footer-table td:last-child {
        padding-left: 20px;
    }

    .outcomes-table .cat-header {
        background: #E8E8E8;
        font-weight: bold;
        font-size: 10.5px;
    }

    .auth-section {
        border-top: 1px solid #000000;
        padding: 8px;
        font-size: 10.5px;
    }

    .auth-section .place-date {
        margin-bottom: 2px;
    }

    .auth-section .sign-block {
        margin-top: 60px;
        font-weight: bold;
        font-size: 11px;
    }

    .auth-section .sign-block.has-signature {
        margin-top: 18px;
    }

    .auth-section .signature-image {
        display: block;
        max-height: 80px;
        max-width: 220px;
        margin-bottom: 8px;
        object-fit: contain;
    }

    .auth-section ._nm {
        text-decoration: underline;
    }

    .kkni-table ul,
    .footer-table ul {
        margin-left: 14px;
    }

    .kkni-table li,
    .footer-table li {
        margin-bottom: 2px;
    }

    .footer-table .address-col {
        font-size: 10px;
    }

    .footer-table .bold {
        font-weight: bold;
    }

    .empty-state {
        text-align: center;
        padding: 56px 24px;
    }

    .empty-state i {
        font-size: 64px;
        color: #D1D5DB;
        margin-bottom: 14px;
    }

    @media (max-width: 1100px) {
        .hero-card {
            flex-direction: column;
            gap: 20px;
        }

        .hero-stats,
        .summary-grid,
        .generate-form .form-grid,
        .detail-grid,
        .template-upload-grid,
        .content-grid {
            grid-template-columns: 1fr !important;
        }
    }

    @media (max-width: 768px) {
        .page-shell {
            gap: 16px;
        }

        .hero-card,
        .content-card,
        .summary-card {
            padding: 20px;
        }

        .hero-stats {
            grid-template-columns: repeat(2, 1fr) !important;
            width: 100%;
        }

        .stat-chip {
            padding: 12px;
        }

        .stat-chip strong {
            font-size: 14px;
        }

        .achievement-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            padding: 15px;
        }

        .achievement-icon {
            width: 44px;
            height: 44px;
            font-size: 20px;
        }

        .achievement-body h5 {
            font-size: 14px;
        }

        .achievement-body p {
            font-size: 11px;
        }

        .status-badge {
            align-self: flex-start;
        }

        /* ── Preview Adjustments ── */
        .template-preview-shell {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 12px;
        }

        .skpi-doc {
            width: fit-content;
            min-width: 100%;
            margin: 0 auto !important;
            padding: 20px 0 !important;
        }

        .doc-intro,
        .doc-section {
            margin-left: 15px !important;
            margin-right: 15px !important;
        }

        .doc-header {
            padding: 0 15px !important;
        }

        .doc-intro {
            padding-top: 5px !important;
            padding-bottom: 5px !important;
        }

        .auth-section {
            padding: 15px !important;
        }

        .template-preview-shell table {
            font-size: 9px !important;
        }
        
        .section-title-row {
            font-size: 10px !important;
        }

        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .form-actions button,
        .form-actions form {
            width: 100%;
        }

        .btn-primary,
        .btn-secondary {
            width: 100%;
            justify-content: center;
        }
    }

    @media print {
        body {
            background: #FFFFFF;
            padding-bottom: 0 !important;
        }

        .header-section,
        .bottom-nav,
        .sidebar-nav,
        .hero-card,
        .generate-form,
        .summary-grid,
        .content-grid,
        .achievement-list,
        .note-card,
        .section-header,
        #generateTemplateBtn,
        #printTemplateBtn {
            display: none !important;
        }

        .page-shell,
        .template-preview-card,
        .template-preview-shell {
            display: block !important;
            padding: 0 !important;
            background: #FFFFFF !important;
            box-shadow: none !important;
            border: none !important;
        }

        .skpi-doc {
            box-shadow: none;
            max-width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const skpiTemplatePayload = @json($templatePayload);
    const hasSelectedSkpiData = @json((bool) ($selectedRegistration && $selectedStudent));
    const logoSrc = @json(asset('ush.png'));

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatMultiline(value) {
        return escapeHtml(value).replace(/\n/g, '<br>');
    }

    function parseItem(raw) {
        const parts = String(raw ?? '').split('|');
        return {
            id: (parts[0] || '').trim(),
            en: (parts[1] || '').trim(),
        };
    }

    function buildListHTML(items) {
        if (!items.length) {
            return '<li><span class="li-id">-</span><br><span class="li-en">-</span></li>';
        }

        return items.map((raw) => {
            const parsed = parseItem(raw);
            const englishLine = parsed.en || parsed.id;
            return `<li><span class="li-id">${escapeHtml(parsed.id)}</span><br><span class="li-en">${escapeHtml(englishLine)}</span></li>`;
        }).join('');
    }

    function renderSkpiTemplate() {
        if (!hasSelectedSkpiData) {
            return;
        }

        let html = document.getElementById('skpiDocumentTemplate').innerHTML;
        const logoMarkup = logoSrc
            ? `<img src="${escapeHtml(logoSrc)}" alt="Logo USH">`
            : '<div class="logo-placeholder">Logo<br>USH</div>';
        const signatureMarkup = skpiTemplatePayload.signature_url
            ? `<img src="${escapeHtml(skpiTemplatePayload.signature_url)}" alt="Tanda tangan" class="signature-image" style="max-height:60px; max-width:200px; display:block;">`
            : '';
        const signBlockClass = skpiTemplatePayload.signature_url ? 'has-signature' : '';

        const replacements = {
            '%%LOGO%%': logoMarkup,
            '%%NOMOR%%': escapeHtml(skpiTemplatePayload.nomor || '___________________'),
            '%%NAMA%%': escapeHtml(skpiTemplatePayload.nama || '___________________'),
            '%%TTL%%': escapeHtml(skpiTemplatePayload.ttl || '___________________'),
            '%%NIM%%': escapeHtml(skpiTemplatePayload.nim || '___________________'),
            '%%TAHUN_MASUK%%': escapeHtml(skpiTemplatePayload.tahun_masuk || '___'),
            '%%NO_IJAZAH%%': escapeHtml(skpiTemplatePayload.no_ijazah || '___________________'),
            '%%GELAR%%': escapeHtml(skpiTemplatePayload.gelar || '___'),
            '%%SK_PT%%': escapeHtml(skpiTemplatePayload.sk_pt || '___________________'),
            '%%NAMA_PT%%': escapeHtml(skpiTemplatePayload.nama_pt || 'UNIVERSITAS SUGENG HARTONO'),
            '%%AKR_PT%%': escapeHtml(skpiTemplatePayload.akr_pt || '___________________'),
            '%%PRODI%%': escapeHtml(skpiTemplatePayload.prodi || '___________________'),
            '%%AKR_PRODI%%': escapeHtml(skpiTemplatePayload.akr_prodi || '___________________'),
            '%%JENIS_JENJANG%%': escapeHtml(skpiTemplatePayload.jenis_jenjang || '___________________'),
            '%%KKNI_LEVEL%%': escapeHtml(skpiTemplatePayload.kkni_level || '___'),
            '%%ENTRY_REQ%%': escapeHtml(skpiTemplatePayload.entry_req || '___________________'),
            '%%BAHASA_PENGANTAR%%': escapeHtml(skpiTemplatePayload.bahasa_pengantar || 'Inggris / Indonesia'),
            '%%NO_AKR_PT%%': escapeHtml(skpiTemplatePayload.no_akr_pt || '___________________'),
            '%%SISTEM_PENILAIAN%%': formatMultiline(skpiTemplatePayload.sistem_penilaian || '___________________'),
            '%%LAMA_STUDI%%': escapeHtml(skpiTemplatePayload.lama_studi || '___________________'),
            '%%NO_AKR_PRODI%%': escapeHtml(skpiTemplatePayload.no_akr_prodi || '___________________'),
            '%%PROF_STATUS%%': escapeHtml(skpiTemplatePayload.prof_status || '-'),
            '%%PRESTASI%%': buildListHTML(skpiTemplatePayload.prestasi || []),
            '%%ORGANISASI%%': buildListHTML(skpiTemplatePayload.organisasi || []),
            '%%MAGANG%%': buildListHTML(skpiTemplatePayload.magang || []),
            '%%PELATIHAN%%': buildListHTML(skpiTemplatePayload.pelatihan || []),
            '%%SERTIF%%': buildListHTML(skpiTemplatePayload.sertif || []),
            '%%SKRIPSI_ID%%': escapeHtml(skpiTemplatePayload.skripsi_id || '___________________'),
            '%%SKRIPSI_EN%%': escapeHtml(skpiTemplatePayload.skripsi_en || '___________________'),
            '%%KOTA_TGL%%': escapeHtml(skpiTemplatePayload.kota_tgl || '___________________'),
            '%%VICE_RECTOR_NAME%%': escapeHtml(skpiTemplatePayload.vice_rector_name || '____________________________________'),
            '%%VICE_RECTOR_TITLE%%': escapeHtml(skpiTemplatePayload.vice_rector_title || 'Wakil Rektor I Universitas Sugeng Hartono'),
            '%%SIGNATURE%%': signatureMarkup,
            '%%SIGN_BLOCK_CLASS%%': signBlockClass,
        };

        Object.entries(replacements).forEach(([key, replacement]) => {
            html = html.replaceAll(key, replacement);
        });

        document.getElementById('templatePreviewArea').innerHTML = html;
        document.getElementById('templatePreviewCard').classList.add('visible');
        document.getElementById('printTemplateBtn').style.display = 'inline-flex';
        document.getElementById('templatePreviewCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const generateButton = document.getElementById('generateTemplateBtn');

        if (generateButton) {
            generateButton.addEventListener('click', renderSkpiTemplate);
        }
    });
</script>
@endpush
