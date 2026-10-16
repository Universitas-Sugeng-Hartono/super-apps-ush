@extends('admin.layouts.super-app')

@section('content')
<div class="content-card">
    <div class="card-header">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <h3 class="mb-0"><i class="bi bi-award-fill text-warning me-2"></i> Monitoring Mahasiswa Beasiswa & Student Ambassador</h3>
                @if(!empty($activePeriod))
                    <span class="badge" style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; font-size: 12.5px; font-weight: 700; border-radius: 20px; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-calendar-check-fill text-primary"></i> Periode Aktif: {{ $activePeriod['tahun_akademik'] ?? '-' }} (Semester {{ $activePeriod['semester'] ?? '-' }})
                    </span>
                @endif
            </div>
            <p class="text-muted mb-0" style="font-size: 13px;">Pemantauan komprehensif data akademik, capaian IPK KHS, persentase beasiswa, dan status penetapan Student Ambassador.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('akademik.dashboard') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
            <a href="{{ route('akademik.monitoring-beasiswa.export', request()->query()) }}" class="btn-outline-export" title="Unduh rekapitulasi data mahasiswa beasiswa dalam format CSV">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Quick Summary KPI Stats -->
    <div class="stats-row">
        <div class="stat-box ambassador">
            <div class="stat-icon"><i class="bi bi-award-fill"></i></div>
            <div class="stat-details">
                <span class="stat-count">{{ $totalAmbassador ?? 0 }}</span>
                <span class="stat-label">Student Ambassador</span>
                <span class="stat-sub">Duta Kampus USH</span>
            </div>
        </div>
        <div class="stat-box scholarship">
            <div class="stat-icon"><i class="bi bi-patch-check-fill"></i></div>
            <div class="stat-details">
                <span class="stat-count">{{ $totalScholarship ?? 0 }}</span>
                <span class="stat-label">Penerima Beasiswa</span>
                <span class="stat-sub">Beasiswa > 0%</span>
            </div>
        </div>
        <div class="stat-box ipk-avg">
            <div class="stat-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <div class="stat-details">
                <span class="stat-count">{{ $avgScholarshipIpk ? number_format($avgScholarshipIpk, 2) : '0.00' }}</span>
                <span class="stat-label">Rata-rata IPK Beasiswa</span>
                <span class="stat-sub">KHS SIAKAD</span>
            </div>
        </div>
        <div class="stat-box total">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-details">
                <span class="stat-count">{{ $totalStudents ?? 0 }}</span>
                <span class="stat-label">Total Mahasiswa Terpantau</span>
                <span class="stat-sub">Beasiswa & Ambassador</span>
            </div>
        </div>
    </div>

    <!-- Mini Tier Badges Breakdown -->
    <div class="tiers-breakdown-card">
        <div class="tier-label"><i class="bi bi-pie-chart-fill me-1"></i> Rincian Kategori Beasiswa:</div>
        <div class="tier-pills">
            <a href="{{ route('akademik.monitoring-beasiswa.index', array_merge(request()->query(), ['beasiswa' => '100%'])) }}" class="tier-pill tier-100 {{ ($beasiswaFilter ?? '') === '100%' ? 'is-active' : '' }}">
                <span class="pill-dot"></span> Beasiswa 100%: <b>{{ $scholarship100Count ?? 0 }}</b>
            </a>
            <a href="{{ route('akademik.monitoring-beasiswa.index', array_merge(request()->query(), ['beasiswa' => '75%'])) }}" class="tier-pill tier-75 {{ ($beasiswaFilter ?? '') === '75%' ? 'is-active' : '' }}">
                <span class="pill-dot"></span> Beasiswa 75%: <b>{{ $scholarship75Count ?? 0 }}</b>
            </a>
            <a href="{{ route('akademik.monitoring-beasiswa.index', array_merge(request()->query(), ['beasiswa' => '50%'])) }}" class="tier-pill tier-50 {{ ($beasiswaFilter ?? '') === '50%' ? 'is-active' : '' }}">
                <span class="pill-dot"></span> Beasiswa 50%: <b>{{ $scholarship50Count ?? 0 }}</b>
            </a>
            <a href="{{ route('akademik.monitoring-beasiswa.index', array_merge(request()->query(), ['beasiswa' => '25%'])) }}" class="tier-pill tier-25 {{ ($beasiswaFilter ?? '') === '25%' ? 'is-active' : '' }}">
                <span class="pill-dot"></span> Beasiswa 25%: <b>{{ $scholarship25Count ?? 0 }}</b>
            </a>
        </div>
    </div>

    <!-- Search & Filter Card (Ringkas: Search, Kategori Beasiswa, Program Studi) -->
    <div class="filter-card shadow-sm mb-4">
        <form method="GET" action="{{ route('akademik.monitoring-beasiswa.index') }}" class="filter-grid-monitoring">
            <div class="filter-item search-item">
                <label>Pencarian Mahasiswa</label>
                <div class="input-with-icon">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control-modern" value="{{ $search }}" placeholder="Ketik nama mahasiswa, NIM, atau email...">
                </div>
            </div>

            <div class="filter-item">
                <label>Kategori Beasiswa</label>
                <select name="beasiswa" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">Semua Kategori Beasiswa</option>
                    <option value="100%" {{ ($beasiswaFilter ?? '') === '100%' ? 'selected' : '' }}>Beasiswa 100%</option>
                    <option value="75%" {{ ($beasiswaFilter ?? '') === '75%' ? 'selected' : '' }}>Beasiswa 75%</option>
                    <option value="50%" {{ ($beasiswaFilter ?? '') === '50%' ? 'selected' : '' }}>Beasiswa 50%</option>
                    <option value="25%" {{ ($beasiswaFilter ?? '') === '25%' ? 'selected' : '' }}>Beasiswa 25%</option>
                </select>
            </div>

            <div class="filter-item">
                <label>Program Studi</label>
                <select name="program_studi" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">Semua Program Studi</option>
                    @foreach($programOptions as $prodi)
                        <option value="{{ $prodi }}" {{ $programStudi === $prodi ? 'selected' : '' }}>{{ $prodi }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-actions-monitoring">
                @if($search || $programStudi || $beasiswaFilter)
                    <a href="{{ route('akademik.monitoring-beasiswa.index') }}" class="btn-reset-modern" title="Reset Semua Filter">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                @endif
                <button type="submit" class="btn-search-modern">
                    <i class="bi bi-funnel"></i> Terapkan
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    @if($students->count() > 0)
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Mahasiswa & NIM</th>
                        <th>Program Studi & Angkatan</th>
                        <th style="text-align: center;">Semester</th>
                        <th style="text-align: center;">IPK</th>
                        <th style="text-align: center;">Beasiswa (%)</th>
                        <th>Status Penetapan</th>
                        <th style="text-align: center;">POKEMA & Evaluasi</th>
                        <th>Dosen PA</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center; min-width: 240px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        @php
                            $achievementsData = $student->achievements->map(function ($ach) {
                                $statusClass = match($ach->status) {
                                    'approved' => 'status-approved',
                                    'rejected' => 'status-rejected',
                                    default => 'status-pending',
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
                            $ipkVal = is_numeric($student->ipk) ? number_format((float)$student->ipk, 2) : null;
                            $approvedPoints = (int) ($student->total_skp_approved ?? 0);
                            $eval = $student->pokema_evaluation ?? [];
                            $recPercent = $eval['recommended_beasiswa'] ?? ($eval['qualified_beasiswa'] ?? ($student->beasiswa ?: '0%'));
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                            <td>
                                <div class="student-cell">
                                    <span class="student-name">{{ $student->nama_lengkap }}</span>
                                    <span class="student-nim font-monospace">{{ $student->nim }}</span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="prodi-text">{{ $student->program_studi ?? '-' }}</span>
                                    <span class="angkatan-badge">{{ $student->angkatan ? 'Angkatan ' . $student->angkatan : '-' }}</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-sem">{{ $student->semester ? 'Sem. ' . $student->semester : '-' }}</span>
                            </td>
                            <td style="text-align: center;">
                                @if(filled($student->ipk))
                                    @php
                                        $ipkValNum = (float)$student->ipk;
                                        $ipkClass = $ipkValNum >= 3.5 ? 'ipk-cumlaude' : ($ipkValNum >= 3.0 ? 'ipk-good' : 'ipk-fair');
                                    @endphp
                                    <span class="badge-ipk {{ $ipkClass }}">{{ number_format($ipkValNum, 2) }}</span>
                                @else
                                    <span class="text-muted" style="font-size: 12px;">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @php
                                    $pct = trim((string)$student->beasiswa);
                                    $bClass = match($pct) {
                                        '100%' => 'badge-beasiswa-100',
                                        '75%'  => 'badge-beasiswa-75',
                                        '50%'  => 'badge-beasiswa-50',
                                        '25%'  => 'badge-beasiswa-25',
                                        default => 'badge-beasiswa-0'
                                    };
                                @endphp
                                <span class="badge-beasiswa {{ $bClass }}">{{ $pct ?: '0%' }}</span>
                            </td>
                            <td>
                                @if($isAmbassador)
                                    <span class="status-badge-ambassador">
                                        <i class="bi bi-star-fill"></i> Student Ambassador
                                    </span>
                                @else
                                    <span class="status-badge-mahasiswa">
                                        <i class="bi bi-award-fill text-success"></i> Penerima Beasiswa
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if(!empty($eval))
                                    <div class="col-eval-cell">
                                        <span class="badge-eval badge-eval-{{ $eval['action_type'] ?? 'maintain' }}" title="{{ $eval['reason'] ?? '' }}">
                                            @if(($eval['action_type'] ?? '') === 'upgrade')
                                                <i class="bi bi-arrow-up-circle-fill"></i> Rekomendasi {{ $recPercent }}
                                            @elseif(($eval['action_type'] ?? '') === 'downgrade')
                                                <i class="bi bi-arrow-down-circle-fill"></i> Evaluasi {{ $recPercent }}
                                            @elseif(($eval['action_type'] ?? '') === 'revoke')
                                                <i class="bi bi-exclamation-octagon-fill"></i> Rekomendasi 0%
                                            @elseif(($eval['action_type'] ?? '') === 'warning')
                                                <i class="bi bi-exclamation-triangle-fill"></i> Perlu Perhatian
                                            @elseif(($eval['action_type'] ?? '') === 'on_track')
                                                <i class="bi bi-graph-up-arrow"></i> On Track
                                            @else
                                                <i class="bi bi-shield-check"></i> Target Terpenuhi
                                            @endif
                                        </span>
                                        <div class="eval-sub-text">
                                            <span class="eval-pts">{{ $approvedPoints }} Poin SKP</span>
                                            @if(isset($eval['fulfilled_fields_count']))
                                                <span class="eval-fields {{ ($eval['all_fields_met'] ?? false) ? 'text-success' : 'text-warning' }}">
                                                    &bull; {{ $eval['fulfilled_fields_count'] }}/7 Bidang {{ ($eval['all_fields_met'] ?? false) ? '✓' : '' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size: 12px;">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="dosen-pa-text" title="{{ $student->dosenPA->name ?? ($student->id_lecturer ? 'ID: ' . $student->id_lecturer : '-') }}">
                                    {{ $student->dosenPA->name ?? ($student->id_lecturer ? 'ID: ' . $student->id_lecturer : '-') }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @php
                                    $statusClass = match ($student->status_mahasiswa) {
                                        'Aktif' => 'status-approved',
                                        'Cuti' => 'status-pending',
                                        default => 'status-rejected',
                                    };
                                    $statusLabel = match ($student->status_mahasiswa) {
                                        'Aktif' => 'Aktif',
                                        'Cuti' => 'Cuti',
                                        default => $student->status_mahasiswa ?: 'Tidak Aktif',
                                    };
                                @endphp
                                <span class="badge-status {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td style="text-align: center;">
                                <div class="action-buttons" style="justify-content: center; gap: 6px; flex-wrap: wrap;">
                                    <button type="button" class="btn-evaluasi"
                                        data-student-id="{{ $student->id }}"
                                        data-nama="{{ $student->nama_lengkap ?? '-' }}"
                                        data-nim="{{ $student->nim ?? '-' }}"
                                        data-ipk="{{ $ipkVal ?? '-' }}"
                                        data-poin="{{ $approvedPoints }}"
                                        data-status-mhs="{{ $isAmbassador ? 'Student Ambassador' : 'Penerima Beasiswa' }}"
                                        data-beasiswa="{{ $student->beasiswa ?: ($isAmbassador ? 'Ambassador' : '0%') }}"
                                        data-achievements="{{ base64_encode(json_encode($achievementsData->values())) }}"
                                        data-evaluation="{{ base64_encode(json_encode($eval)) }}"
                                        onclick="showAkademikEvaluationModal(this, 'evaluation')"
                                        title="Buka Evaluasi Capaian POKEMA & Riwayat Prestasi Mahasiswa">
                                        <i class="bi bi-speedometer2"></i> Evaluasi Prestasi
                                    </button>
                                    <button type="button" class="btn-view" onclick="openStudentModal({{ json_encode([
                                        'nama' => $student->nama_lengkap,
                                        'nim' => $student->nim,
                                        'prodi' => $student->program_studi ?? '-',
                                        'angkatan' => $student->angkatan ?? '-',
                                        'tanggal_masuk' => $student->tanggal_masuk ? \Carbon\Carbon::parse($student->tanggal_masuk)->translatedFormat('F Y') : '-',
                                        'semester' => $student->semester ? 'Semester ' . $student->semester : '-',
                                        'ipk' => filled($student->ipk) ? number_format((float)$student->ipk, 2) : 'Belum Ada IPK',
                                        'beasiswa' => $student->beasiswa ?: '0%',
                                        'status' => $student->status === 'studentambassador' ? 'Student Ambassador (Duta Kampus)' : 'Mahasiswa Beasiswa',
                                        'status_is_ambassador' => $student->status === 'studentambassador',
                                        'status_mahasiswa' => $student->status_mahasiswa ?: 'Aktif',
                                        'gender' => $student->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki',
                                        'dosen_pa' => $student->dosenPA->name ?? ($student->id_lecturer ? 'ID: ' . $student->id_lecturer : '-'),
                                        'telepon' => $student->no_telepon ?: '-',
                                        'email' => $student->email ?: '-',
                                        'alamat' => $student->alamat ?: '-',
                                        'notes' => $student->notes ?: 'Belum ada catatan.'
                                    ]) }})" title="Lihat Detail Profil Mahasiswa">
                                        <i class="bi bi-person-badge"></i> Profil
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper mt-4">
            {{ $students->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-mortarboard-fill"></i>
            <p>Tidak ada data mahasiswa yang cocok dengan kriteria filter.</p>
        </div>
    @endif
</div>

{{-- Modal Evaluasi POKEMA & Detail Dokumen Bukti Mahasiswa --}}
@include('akademik.monitoring-beasiswa.partials._modal_evaluasi')

{{-- Modal Quick Detail Mahasiswa Shared Component --}}
@include('akademik.script')
@endsection

@push('css')
<style>
    .content-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #ECEFF1;
        flex-wrap: wrap;
        gap: 12px;
    }
    .card-header h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #1E293B;
    }
    .header-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .btn-secondary {
        padding: 9px 16px;
        background: #F1F5F9;
        color: #475569;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid #E2E8F0;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13.5px;
        transition: all 0.2s;
    }
    .btn-secondary:hover {
        background: #E2E8F0;
        color: #1E293B;
    }
    .btn-outline-export {
        padding: 9px 16px;
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13.5px;
        transition: all 0.2s;
    }
    .btn-outline-export:hover {
        background: #059669;
        color: white;
        border-color: #059669;
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    .stat-box {
        background: #F8FAFC;
        border-radius: 14px;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid #E2E8F0;
        transition: all 0.2s;
    }
    .stat-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.04);
    }
    .stat-box.ambassador .stat-icon { background: #FEF3C7; color: #B45309; }
    .stat-box.scholarship .stat-icon { background: #D1FAE5; color: #047857; }
    .stat-box.ipk-avg .stat-icon { background: #DBEAFE; color: #1D4ED8; }
    .stat-box.total .stat-icon { background: #EDE9FE; color: #6D28D9; }

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
        font-size: 24px;
        font-weight: 800;
        color: #1E293B;
        line-height: 1.1;
    }
    .stat-label {
        font-size: 12.5px;
        font-weight: 600;
        color: #475569;
        margin-top: 2px;
    }
    .stat-sub {
        font-size: 11px;
        color: #94A3B8;
    }

    /* Tiers Breakdown Card */
    .tiers-breakdown-card {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .tier-label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
    }
    .tier-pills {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .tier-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .tier-pill .pill-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    .tier-pill.tier-100 { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
    .tier-pill.tier-100 .pill-dot { background: #10B981; }
    .tier-pill.tier-75 { background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD; }
    .tier-pill.tier-75 .pill-dot { background: #0284C7; }
    .tier-pill.tier-50 { background: #F3E8FF; color: #6B21A8; border: 1px solid #E9D5FF; }
    .tier-pill.tier-50 .pill-dot { background: #9333EA; }
    .tier-pill.tier-25 { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
    .tier-pill.tier-25 .pill-dot { background: #F59E0B; }
    .tier-pill:hover, .tier-pill.is-active {
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        transform: translateY(-1px);
        font-weight: 700;
    }

    /* Filter Grid */
    .filter-card {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .filter-grid-monitoring {
        display: grid;
        grid-template-columns: minmax(240px, 2.2fr) minmax(180px, 1.2fr) minmax(180px, 1.2fr) auto;
        gap: 14px;
        align-items: flex-end;
    }
    @media (max-width: 900px) {
        .filter-grid-monitoring {
            grid-template-columns: 1fr;
        }
    }
    .filter-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .filter-item label {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }
    .input-with-icon {
        position: relative;
    }
    .input-with-icon i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
    }
    .form-control-modern {
        width: 100%;
        padding: 9px 12px 9px 34px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        font-size: 13px;
        background: white;
    }
    .form-select-modern {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        font-size: 13px;
        background: white;
    }
    .filter-actions-monitoring {
        display: flex;
        gap: 8px;
    }
    .btn-search-modern {
        padding: 9px 16px;
        background: #2563EB;
        color: white;
        border: none;
        border-radius: 9px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-reset-modern {
        padding: 9px 14px;
        background: #E2E8F0;
        color: #475569;
        border-radius: 9px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* Table */
    .table-responsive {
        overflow-x: auto;
    }
    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .data-table th {
        background: #F8FAFC;
        padding: 12px 14px;
        font-size: 12.5px;
        font-weight: 700;
        color: #475569;
        border-bottom: 2px solid #E2E8F0;
    }
    .data-table td {
        padding: 14px;
        border-bottom: 1px solid #F1F5F9;
        font-size: 13px;
        vertical-align: middle;
    }
    .student-cell {
        display: flex;
        flex-direction: column;
    }
    .student-name {
        font-weight: 700;
        color: #1E293B;
    }
    .student-nim {
        font-size: 12px;
        color: #64748B;
    }
    .prodi-text {
        font-weight: 600;
        color: #334155;
        display: block;
    }
    .angkatan-badge {
        font-size: 11.5px;
        color: #64748B;
    }
    .badge-sem {
        font-size: 11.5px;
        background: #F1F5F9;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 600;
        color: #475569;
    }

    /* IPK Badges */
    .badge-ipk {
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 12.5px;
        display: inline-block;
    }
    .ipk-cumlaude { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
    .ipk-good { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
    .ipk-fair { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }

    /* Beasiswa Badges */
    .badge-beasiswa {
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 12px;
        display: inline-block;
    }
    .badge-beasiswa-100 { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
    .badge-beasiswa-75  { background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD; }
    .badge-beasiswa-50  { background: #F3E8FF; color: #6B21A8; border: 1px solid #E9D5FF; }
    .badge-beasiswa-25  { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
    .badge-beasiswa-0   { background: #F1F5F9; color: #64748B; border: 1px solid #E2E8F0; }

    /* Ambassador Badges */
    .status-badge-ambassador {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background: linear-gradient(135deg, #FFF8E1, #FFECB3);
        color: #B78103;
        border: 1px solid #FFE082;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .status-badge-mahasiswa {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background: #F1F5F9;
        color: #64748B;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
    }
    .dosen-pa-text {
        font-size: 12.5px;
        color: #475569;
        max-width: 150px;
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Status Mahasiswa */
    .badge-status {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .status-approved { background: #DCFCE7; color: #166534; }
    .status-pending { background: #FEF3C7; color: #92400E; }
    .status-rejected { background: #FEE2E2; color: #991B1B; }

    /* Action Buttons */
    .btn-view {
        padding: 6px 14px;
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        color: #1D4ED8;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
    }
    .btn-view:hover {
        background: #2563EB;
        color: white;
        border-color: #2563EB;
    }

    .btn-evaluasi {
        padding: 6px 13px;
        background: #ECFDF5;
        border: 1px solid #A7F3D0;
        color: #065F46;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .btn-evaluasi:hover {
        background: #059669;
        color: white;
        border-color: #059669;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(5, 150, 105, 0.25);
    }

    /* POKEMA Evaluasi Column Badges */
    .col-eval-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }
    .badge-eval {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-eval-upgrade {
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
    }
    .badge-eval-maintain {
        background: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
    }
    .badge-eval-on_track {
        background: #F0F9FF;
        color: #0369A1;
        border: 1px solid #BAE6FD;
    }
    .badge-eval-warning {
        background: #FFFBEB;
        color: #92400E;
        border: 1px solid #FDE68A;
    }
    .badge-eval-downgrade {
        background: #FFFBEB;
        color: #B45309;
        border: 1px solid #FCD34D;
    }
    .badge-eval-revoke {
        background: #FEF2F2;
        color: #991B1B;
        border: 1px solid #FECACA;
    }
    .badge-eval-future, .badge-eval-reguler {
        background: #F8FAFC;
        color: #475569;
        border: 1px solid #CBD5E1;
    }
    .eval-sub-text {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        color: #64748B;
        font-weight: 600;
    }
    .eval-pts {
        color: #2563EB;
        font-weight: 700;
    }

    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #64748B;
    }
    .empty-state i {
        font-size: 40px;
        display: block;
        margin-bottom: 12px;
        color: #CBD5E1;
    }

    @media (max-width: 992px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .stats-row { grid-template-columns: 1fr; }
    }
</style>
@endpush
