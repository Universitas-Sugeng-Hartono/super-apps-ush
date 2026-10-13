@extends('admin.layouts.super-app')

@section('content')
<div class="page-shell">
    <div class="mb-3" style="padding-top: 10px;">
        <a href="{{ route('kemahasiswaan.dashboard') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard Kemahasiswaan
        </a>
    </div>

    <div class="hero-card">
        <div>
            <span class="hero-badge"><i class="bi bi-shield-check"></i> Verifikasi Prestasi & SKPI</span>
            <h3>Verifikasi Data Prestasi Mahasiswa</h3>
            <p>Kelola dan verifikasi prestasi mahasiswa dengan informasi lengkap IPK, total akumulasi poin SKP, status kemahasiswaan/ambassador, dan beasiswa.</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <span>Total Prestasi</span>
                <strong>{{ $stats['total'] }}</strong>
            </div>
            <div class="stat-card warning">
                <span>Pending</span>
                <strong>{{ $stats['pending'] }}</strong>
            </div>
            <div class="stat-card success">
                <span>Approved</span>
                <strong>{{ $stats['approved'] }}</strong>
            </div>
            <div class="stat-card danger">
                <span>Rejected</span>
                <strong>{{ $stats['rejected'] }}</strong>
            </div>
            <div class="stat-card" style="border-left: 4px solid #8B5CF6;">
                <span style="color: #6D28D9;">Ambassador</span>
                <strong style="color: #6D28D9;">{{ $stats['ambassador'] }}</strong>
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
    <div class="alert-danger" style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <div class="content-card">
        <form method="GET" action="{{ route('kemahasiswaan.verifikasi-prestasi.index') }}" class="filter-form" id="filterForm">
            <div class="filter-group search-group">
                <label for="search">Cari Data</label>
                <input type="text" id="search" name="search" class="form-control" value="{{ $search }}" placeholder="Nama, NIM, prodi, atau beasiswa (%)..." oninput="debounceFilter()">
            </div>
            <div class="filter-group">
                <label for="status">Status Verifikasi</label>
                <select id="status" name="status" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="status_mahasiswa">Status Mahasiswa</label>
                <select id="status_mahasiswa" name="status_mahasiswa" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Status</option>
                    <option value="studentambassador" {{ $statusMahasiswa === 'studentambassador' ? 'selected' : '' }}>Student Ambassador</option>
                    <option value="mahasiswa" {{ $statusMahasiswa === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa Reguler</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="beasiswa">Beasiswa (%)</label>
                <select id="beasiswa" name="beasiswa" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Mahasiswa</option>
                    <option value="has_beasiswa" {{ $beasiswaFilter === 'has_beasiswa' ? 'selected' : '' }}>Penerima Beasiswa (&gt; 0%)</option>
                    <option value="none" {{ $beasiswaFilter === 'none' ? 'selected' : '' }}>Non-Beasiswa (0%)</option>
                    @if(!empty($availableBeasiswa))
                        <optgroup label="Persentase Beasiswa">
                            @foreach($availableBeasiswa as $b)
                                <option value="{{ $b }}" {{ $beasiswaFilter === $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>
            <div class="filter-group">
                <label for="program_studi">Program Studi</label>
                <select id="program_studi" name="program_studi" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Program Studi</option>
                    @foreach($studyPrograms as $studyProgram)
                    <option value="{{ $studyProgram->name }}" {{ $programStudi === $studyProgram->name ? 'selected' : '' }}>
                        {{ $studyProgram->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="category">Jenis Kegiatan</label>
                <select id="category" name="category" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($categoryOptions as $value => $label)
                    <option value="{{ $value }}" {{ $category === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <a href="{{ route('kemahasiswaan.verifikasi-prestasi.index') }}" class="btn-reset" style="margin-top: 22px;">Reset</a>
            </div>
            <input type="hidden" name="tab" value="{{ $tab }}">
        </form>
    </div>

    {{-- Toolbar Baris: Segmented Tabs & Toggle Portal Upload Sejajar --}}
    <div class="category-toolbar-row">
        {{-- Segmented Toggle Pemisah Mahasiswa Beasiswa vs Non-Beasiswa --}}
        <div class="student-category-toggle-wrap">
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'all', 'page' => 1]) }}" 
               class="category-toggle-btn {{ $tab === 'all' ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Semua Mahasiswa 
                <span class="toggle-count-badge">{{ $tabCounts['all'] }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'beasiswa', 'page' => 1]) }}" 
               class="category-toggle-btn btn-beasiswa-tab {{ $tab === 'beasiswa' ? 'active' : '' }}">
                <i class="bi bi-award-fill"></i> Penerima Beasiswa & Ambassador 
                <span class="toggle-count-badge">{{ $tabCounts['beasiswa'] }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'non_beasiswa', 'page' => 1]) }}" 
               class="category-toggle-btn btn-reguler-tab {{ $tab === 'non_beasiswa' ? 'active' : '' }}">
                <i class="bi bi-person-fill"></i> Mahasiswa Reguler (Non-Beasiswa) 
                <span class="toggle-count-badge">{{ $tabCounts['non_beasiswa'] }}</span>
            </a>
        </div>

        {{-- Control: Status Portal Pengajuan Prestasi (Ambassador & Beasiswa) Disejajarkan di Sebelah Kanan --}}
        <div class="portal-lock-inline" id="portalLockInline" title="{{ !empty($isPortalOpen) ? 'Status: DIBUKA. Mahasiswa dapat mengunggah dan memperbarui prestasi secara normal.' : 'Status: DITUTUP. Akses upload mahasiswa dikunci sementara untuk proses evaluasi kelayakan beasiswa semester.' }}">
            <span class="portal-inline-label">Portal Upload:</span>
            <label class="switch-toggle" title="Klik untuk membuka atau menutup portal upload">
                <input type="checkbox" id="portalUploadSwitch" {{ !empty($isPortalOpen) ? 'checked' : '' }} onchange="togglePortalUpload(this.checked)">
                <span class="switch-slider"></span>
            </label>
            <span id="portalStatusBadge" class="portal-status-badge {{ !empty($isPortalOpen) ? 'badge-open' : 'badge-closed' }}">
                {{ !empty($isPortalOpen) ? 'Dibuka (Aktif)' : 'Ditutup (Review)' }}
            </span>
        </div>
    </div>

    @if($stats['pending'] > 0)
    <div class="content-card" style="background: #FFFBF0; border: 1px solid #F4E5CD;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap: wrap; gap: 12px;">
            <div>
                <strong style="color:#C46A00;"><i class="bi bi-lightning-charge"></i> {{ $stats['pending'] }} data menunggu verifikasi</strong>
                <p style="margin:4px 0 0; color:#6B7280; font-size:14px;">Klik tombol di samping untuk menyetujui seluruh data pending secara massal.</p>
            </div>
            <button type="button" class="btn-approve" onclick="showApproveAllModal()">
                <i class="bi bi-check-all"></i> Approve Semua Pending
            </button>
        </div>
    </div>
    @endif

    @if($students->count() > 0)
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 45px;">No</th>
                    <th>Mahasiswa</th>
                    <th>Program Studi</th>
                    <th style="text-align: center;">IPK</th>
                    <th style="text-align: center;">Total Point</th>
                    @if($tab !== 'non_beasiswa')
                    <th style="text-align: center;">Status Mahasiswa</th>
                    <th style="text-align: center;">Beasiswa (%)</th>
                    <th style="text-align: center; min-width: 175px;">Tindakan / Rekomendasi</th>
                    @endif
                    <th style="text-align: center;">Pengajuan</th>
                    <th style="text-align: center; width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                @php
                $achievementsData = $student->achievements->map(function ($ach) {
                    $statusClass = match($ach->status) {
                        'approved' => 'status-aktif',
                        'rejected' => 'status-nonaktif',
                        default => 'status-cuti',
                    };
                    $statusLabel = match($ach->status) {
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => 'Pending',
                    };

                    return [
                        'id' => $ach->id,
                        'category_raw' => $ach->category,
                        'activity_type_raw' => $ach->activity_type,
                        'kategori' => $ach->category_label,
                        'kegiatan' => $ach->activity_type_label ?? $ach->activity_type,
                        'event' => ($ach->event && $ach->event !== '-') ? $ach->event : null,
                        'organizer' => $ach->organizer ?: '-',
                        'event_year' => $ach->event_year ?: '-',
                        'semester' => $ach->semester ? (int)$ach->semester : null,
                        'semester_label' => $ach->semester ? 'Semester ' . $ach->semester : '-',
                        'tingkat' => $ach->level,
                        'peran' => $ach->participation_role ?? '-',
                        'skp' => $ach->skp_points ?? 0,
                        'status' => $ach->status,
                        'statusClass' => $statusClass,
                        'statusLabel' => $statusLabel,
                        'tanggal' => $ach->created_at?->format('d M Y H:i') ?? '-',
                        'file' => $ach->certificate ? asset('storage/' . $ach->certificate) : '',
                        'catatan' => $ach->approval_notes ?: 'Belum ada catatan review.',
                        'reviewer' => $ach->approver->name ?? 'Belum direview'
                    ];
                });

                $isAmbassador = ($student->status === 'studentambassador');
                $hasBeasiswa = filled($student->beasiswa) && !in_array($student->beasiswa, ['0%', '0', 'Non-Beasiswa'], true);
                $isBeasiswaTarget = ($isAmbassador || $hasBeasiswa);
                $ipkVal = is_numeric($student->ipk) ? number_format((float)$student->ipk, 2) : null;
                $approvedPoints = (int) ($student->total_skp_approved ?? 0);
                @endphp
                <tr>
                    <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                    <td>
                        <strong>{{ $student->nama_lengkap ?? '-' }}</strong><br>
                        <span class="font-monospace text-muted" style="font-size: 12px;">{{ $student->nim ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="badge-prodi">{{ $student->program_studi ?? '-' }}</span>
                    </td>
                    <td style="text-align: center;">
                        @if($ipkVal !== null)
                            <span class="badge-ipk {{ (float)$ipkVal >= 3.5 ? 'ipk-cumlaude' : ((float)$ipkVal >= 3.0 ? 'ipk-good' : '') }}">
                                {{ $ipkVal }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <span class="badge-point {{ $approvedPoints > 0 ? 'point-active' : '' }}">
                            {{ $approvedPoints }} Poin
                        </span>
                    </td>
                    @if($tab !== 'non_beasiswa')
                    <td style="text-align: center;">
                        @if($isAmbassador)
                            <span class="badge-status-mhs badge-ambassador">
                                Student Ambassador
                            </span>
                        @elseif($hasBeasiswa)
                            <span class="badge-status-mhs badge-beasiswa-mhs" style="background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                                Mahasiswa Beasiswa
                            </span>
                        @else
                            <span class="badge-status-mhs badge-reguler">
                                Mahasiswa Reguler
                            </span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($isBeasiswaTarget)
                            <span class="badge-beasiswa" title="Beasiswa {{ $student->beasiswa ?: 'Ambassador' }}">
                                {{ $student->beasiswa ?: 'Ambassador' }}
                            </span>
                        @else
                            <span class="badge-non-beasiswa" style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 11px; background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;">
                                Non-Beasiswa
                            </span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($isBeasiswaTarget && !empty($student->pokema_evaluation))
                            @php 
                                $eval = $student->pokema_evaluation; 
                                $recPercent = $eval['recommended_beasiswa'] ?? ($eval['qualified_beasiswa'] ?? '0%');
                                $currPercent = ($eval['current_beasiswa'] && $eval['current_beasiswa'] !== '0%') ? $eval['current_beasiswa'] : ($isAmbassador ? 'Ambassador' : 'Beasiswa');
                            @endphp
                            <div class="col-recommendation-cell">
                                @if($eval['action_type'] === 'upgrade')
                                    <span class="badge-rec badge-rec-upgrade" title="{{ $eval['reason'] }}">
                                        Rekomendasi {{ $recPercent }}
                                    </span>
                                    <span class="action-needed-badge">
                                        Perlu Tindakan
                                    </span>
                                @elseif($eval['action_type'] === 'downgrade')
                                    <span class="badge-rec badge-rec-downgrade" title="{{ $eval['reason'] }}">
                                        Rekomendasi {{ $recPercent }}
                                    </span>
                                    <span class="action-needed-badge">
                                        Perlu Tindakan
                                    </span>
                                @elseif($eval['action_type'] === 'revoke')
                                    <span class="badge-rec badge-rec-revoke" title="{{ $eval['reason'] }}">
                                        Rekomendasi 0%
                                    </span>
                                    <span class="action-needed-badge">
                                        Perlu Tindakan
                                    </span>
                                @elseif($eval['action_type'] === 'maintain')
                                    <span class="badge-rec badge-rec-maintain" title="{{ $eval['reason'] }}">
                                        Rekomendasi {{ $currPercent }}
                                    </span>
                                    <span class="action-ongoing-badge">
                                        Target Terpenuhi
                                    </span>
                                @elseif($eval['action_type'] === 'on_track')
                                    <span class="badge-rec badge-rec-on_track" title="{{ $eval['reason'] }}">
                                        On Track {{ $currPercent }}
                                    </span>
                                    <span class="action-ontrack-badge">
                                        Periode Berjalan ({{ $eval['progress_pct'] ?? 0 }}%)
                                    </span>
                                @elseif($eval['action_type'] === 'warning')
                                    <span class="badge-rec badge-rec-warning" title="{{ $eval['reason'] }}">
                                        Target {{ $currPercent }}
                                    </span>
                                    <span class="action-warning-badge">
                                        Sisa {{ $eval['points_remaining'] }} Poin
                                    </span>
                                @else
                                    <span class="badge-rec badge-rec-maintain">
                                        Rekomendasi {{ $recPercent }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-muted" style="font-size: 12px;">-</span>
                        @endif
                    </td>
                    @endif
                    <td style="text-align: center;">
                        @php
                        $pendingCount = $student->achievements->where('status', 'pending')->count();
                        @endphp
                        @if($pendingCount > 0)
                            <span class="badge-pending-count">
                                {{ $pendingCount }} Pending
                            </span>
                        @else
                            <span class="badge-pending-zero">
                                0 Pending
                            </span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div class="action-buttons" style="justify-content: center; gap: 6px; flex-wrap: wrap;">
                            @if($isBeasiswaTarget)
                                <button type="button" class="btn-view"
                                    data-student-id="{{ $student->id }}"
                                    data-nama="{{ $student->nama_lengkap ?? '-' }}"
                                    data-nim="{{ $student->nim ?? '-' }}"
                                    data-ipk="{{ $ipkVal ?? '-' }}"
                                    data-poin="{{ $approvedPoints }}"
                                    data-status-mhs="{{ $isAmbassador ? 'Student Ambassador' : 'Penerima Beasiswa' }}"
                                    data-beasiswa="{{ $hasBeasiswa ? $student->beasiswa : 'Ambassador' }}"
                                    data-is-beasiswa="1"
                                    data-achievements="{{ base64_encode(json_encode($achievementsData->values())) }}"
                                    data-evaluation="{{ base64_encode(json_encode($student->pokema_evaluation ?? [])) }}"
                                    onclick="showStudentAchievementsModal(this, 'evaluation')">
                                    <i class="bi bi-speedometer2"></i> Evaluasi & Prestasi
                                </button>
                                @if(isset($student->pokema_evaluation))
                                    @php $evalRow = $student->pokema_evaluation; @endphp
                                    @if($evalRow['action_type'] === 'upgrade')
                                        <button type="button" class="btn-quick-upgrade"
                                            title="{{ $evalRow['reason'] }}"
                                            onclick="openAdjustBeasiswaModal('{{ $student->id }}', '{{ addslashes($student->nama_lengkap) }}', '{{ $student->nim }}', '{{ $evalRow['current_beasiswa'] }}', '{{ $evalRow['qualified_beasiswa'] }}', '{{ addslashes($evalRow['reason']) }}', false, '{{ $evalRow['ipk'] ?? '-' }}', '{{ $evalRow['total_approved_points'] ?? 0 }}', '{{ addslashes($evalRow['study_year_label'] ?? '') }}', '{{ $evalRow['target_current'] ?? ($evalRow['total_target_points'] ?? 0) }}')">
                                            Naikkan Beasiswa
                                        </button>
                                    @elseif($evalRow['action_type'] === 'downgrade')
                                        <button type="button" class="btn-quick-downgrade"
                                            title="{{ $evalRow['reason'] }}"
                                            onclick="openAdjustBeasiswaModal('{{ $student->id }}', '{{ addslashes($student->nama_lengkap) }}', '{{ $student->nim }}', '{{ $evalRow['current_beasiswa'] }}', '{{ $evalRow['qualified_beasiswa'] }}', '{{ addslashes($evalRow['reason']) }}', false, '{{ $evalRow['ipk'] ?? '-' }}', '{{ $evalRow['total_approved_points'] ?? 0 }}', '{{ addslashes($evalRow['study_year_label'] ?? '') }}', '{{ $evalRow['target_current'] ?? ($evalRow['total_target_points'] ?? 0) }}')">
                                            Sesuaikan Beasiswa
                                        </button>
                                    @elseif($evalRow['action_type'] === 'revoke')
                                        <button type="button" class="btn-quick-downgrade" style="background: #DC2626;"
                                            title="{{ $evalRow['reason'] }}"
                                            onclick="openAdjustBeasiswaModal('{{ $student->id }}', '{{ addslashes($student->nama_lengkap) }}', '{{ $student->nim }}', '{{ $evalRow['current_beasiswa'] }}', '0%', '{{ addslashes($evalRow['reason']) }}', false, '{{ $evalRow['ipk'] ?? '-' }}', '{{ $evalRow['total_approved_points'] ?? 0 }}', '{{ addslashes($evalRow['study_year_label'] ?? '') }}', '{{ $evalRow['target_current'] ?? ($evalRow['total_target_points'] ?? 0) }}')">
                                            Cabut Beasiswa
                                        </button>
                                    @endif
                                @endif
                            @else
                                <button type="button" class="btn-view"
                                    data-student-id="{{ $student->id }}"
                                    data-nama="{{ $student->nama_lengkap ?? '-' }}"
                                    data-nim="{{ $student->nim ?? '-' }}"
                                    data-achievements="{{ base64_encode(json_encode($achievementsData->values())) }}"
                                    onclick="showRegularStudentAchievementsModal(this)">
                                    <i class="bi bi-list-task"></i> Lihat Prestasi
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <h4>Belum ada data prestasi</h4>
        <p>Data prestasi mahasiswa yang diajukan akan tampil di sini sesuai dengan filter yang dipilih.</p>
    </div>
    @endif

    @if($students->hasPages())
    <div class="pagination-wrap">
        {{ $students->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>


{{-- MODALS --}}
@include('kemahasiswaan.verifikasi-prestasi.partials._modal_regular_achievements')
@include('kemahasiswaan.verifikasi-prestasi.partials._modal_student_achievements')
@include('kemahasiswaan.verifikasi-prestasi.partials._modal_detail')
@include('kemahasiswaan.verifikasi-prestasi.partials._modal_action_confirmations')
@include('kemahasiswaan.verifikasi-prestasi.partials._modal_edit_achievement')
@include('kemahasiswaan.verifikasi-prestasi.partials._modal_adjust_beasiswa')
@endsection

@push('css')
@include('kemahasiswaan.verifikasi-prestasi.partials._styles')
@endpush

@push('scripts')
@include('kemahasiswaan.verifikasi-prestasi.partials._scripts')
@endpush
