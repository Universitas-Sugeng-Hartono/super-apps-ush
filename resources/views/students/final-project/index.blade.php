@extends('students.layouts.super-app')

@section('content')
    @if(!empty($celebrationNotif))
        <div class="modal fade" id="celebrationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content celebration-card">
                    <div class="confetti" aria-hidden="true">
                        @for($i=0;$i<36;$i++)
                            <span class="confetti-piece" style="left: {{ ($i * 2.7) % 100 }}%; animation-delay: {{ ($i % 12) * 0.15 }}s; animation-duration: {{ 3.2 + ($i % 9) * 0.25 }}s;"></span>
                        @endfor
                    </div>

                    <div class="modal-body celebration-body">
                        <div class="celebration-icon">
                            <i class="bi bi-stars"></i>
                        </div>
                        <h4 class="celebration-title">{{ $celebrationNotif->title }}</h4>
                        @if($celebrationNotif->body)
                            <p class="celebration-text">{{ $celebrationNotif->body }}</p>
                        @endif

                        <div class="celebration-actions">
                            <button type="button" class="btn btn-light btn-close-soft" data-bs-dismiss="modal">
                                Tutup
                            </button>
                            <form method="POST" action="{{ route('notifications.read', $celebrationNotif->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-read-soft">
                                    Sudah dibaca
                                </button>
                            </form>
                        </div>
                        <div class="celebration-hint">
                            Jika klik <strong>Tutup</strong>, popup akan muncul lagi sampai Anda klik <strong>Sudah dibaca</strong>.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Header Card -->
    <div class="stats-card">
        <div class="stats-header">
            <div>
                <h3>Tugas Akhir</h3>
                <div style="margin-top: 8px;">
                    <p style="margin: 0; font-size: 14px; color: #333; font-weight: 600; line-height: 1.4;">
                        {{ $finalProject->title ?? 'Belum menentukan judul' }}
                    </p>
                    @if($finalProject->title && $finalProject->title_en)
                        <p style="margin: 4px 0 0; font-size: 13px; color: #666; font-style: italic; line-height: 1.4;">
                            {{ $finalProject->title_en }}
                        </p>
                    @endif
                </div>
            </div>
            <span class="semester-badge status-{{ $finalProject->status }}">{{ ucfirst($finalProject->status) }}</span>
        </div>
    </div>

    <!-- Progress & Stats -->
    <div class="stats-card">
        <div class="stats-header">
            <h3>Progress Tugas Akhir</h3>
            <span style="font-weight: 600; color: var(--primary-orange);">{{ $finalProject->progress_percentage }}%</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: {{ $finalProject->progress_percentage }}%;"></div>
        </div>

        <div class="stats-content" style="margin-top: 20px;">
            <div class="stat-item">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4CAF50, #81C784);">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h5>Bimbingan</h5>
                    <p>{{ $stats['approved_guidance_count'] }}/{{ $stats['total_guidance_count'] }}</p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon" style="background: linear-gradient(135deg, #2196F3, #64B5F6);">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="stat-info">
                    <h5>Dokumen</h5>
                    <p>{{ $stats['documents_count'] }}</p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon" style="background: linear-gradient(135deg, #FF9800, #FFB347);">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-info">
                    <h5>Pending</h5>
                    <p>{{ $stats['pending_documents'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pembimbing -->
    <div class="menu-section">
        <div class="section-header">
            <h3>Pembimbing</h3>
        </div>
        <div class="supervisor-grid">
            @if($finalProject->supervisor1)
            <div class="supervisor-card">
                <i class="bi bi-person-badge"></i>
                <h5>Pembimbing 1</h5>
                <p>{{ $finalProject->supervisor1->name }}</p>
            </div>
            @else
            <div class="supervisor-card" style="opacity: 0.6;">
                <i class="bi bi-person-badge"></i>
                <h5>Pembimbing 1</h5>
                <p>Belum ditentukan</p>
            </div>
            @endif

            @if($finalProject->supervisor2)
            <div class="supervisor-card">
                <i class="bi bi-person-badge"></i>
                <h5>Pembimbing 2</h5>
                <p>{{ $finalProject->supervisor2->name }}</p>
            </div>
            @else
            <div class="supervisor-card" style="opacity: 0.6;">
                <i class="bi bi-person-badge"></i>
                <h5>Pembimbing 2</h5>
                <p>Opsional</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="menu-section">
        <div class="section-header">
            <h3>Menu Utama</h3>
        </div>

        <div class="menu-grid">
            <!-- Ajukan Judul -->
            @if(!$finalProject->title || !$finalProject->title_approved_at)
            <a href="{{ $finalProject->title ? route('student.final-project.title.edit') : route('student.final-project.title.create') }}" class="menu-card">
                <div class="menu-icon" style="background: linear-gradient(135deg, #2196F3, #64B5F6);">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <h5>{{ $finalProject->title ? 'Edit Judul' : 'Ajukan Judul' }}</h5>
                <p>Pengajuan judul Tugas Akhir</p>
                @if($finalProject->title && !$finalProject->title_approved_at)
                    <span class="status-badge warning">Menunggu Approval</span>
                @else
                    <span class="status-badge info">Belum Diajukan</span>
                @endif
            </a>
            @endif

            <!-- Daftar Sempro -->
            {{-- <a href="{{ route('student.final-project.proposal.create') }}" class="menu-card"
               @if(!$finalProject->title_approved_at || !$finalProject->supervisor_1_id) style="opacity: 0.5; pointer-events: none;" @endif>
                <div class="menu-icon" style="background: linear-gradient(135deg, #9C27B0, #BA68C8);">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <h5>Daftar Sempro</h5>
                <p>Pendaftaran Seminar Proposal</p>
                @if(!$finalProject->title_approved_at)
                    <span class="status-badge warning">Judul Belum Disetujui</span>
                @elseif(!$finalProject->supervisor_1_id)
                    <span class="status-badge warning">Pembimbing Belum Ditentukan</span>
                @elseif($finalProject->proposal)
                    <span class="status-badge {{ $finalProject->proposal->status === 'approved' ? 'active' : 'warning' }}">
                        {{ ucfirst($finalProject->proposal->status) }}
                    </span>
                @else
                    <span class="status-badge info">Belum Daftar</span>
                @endif
            </a> --}}
            @php
                $proposal        = $finalProject->proposal;
                $proposalRejected = $proposal && $proposal->status === 'rejected';
                $proposalApproved = $proposal && $proposal->status === 'approved';
                $canAccessSempro  = $finalProject->title_approved_at && $finalProject->supervisor_1_id;

                $hasEditableProposalDocs = $proposal
                    ? $finalProject->documents
                        ->where('document_type', 'proposal')
                        ->whereIn('review_status', ['needs_revision', 'rejected'])
                        ->count() > 0
                    : false;
            @endphp

            <a href="{{
                    $proposalRejected || $hasEditableProposalDocs
                        ? route('student.final-project.proposal.edit', $proposal->id)
                        : ($proposal
                            ? route('student.final-project.proposal.show', $proposal->id)
                            : route('student.final-project.proposal.create'))
                }}"
            class="menu-card"
            @if(!$canAccessSempro && !$proposal) style="opacity: 0.5; pointer-events: none;" @endif>

                <div class="menu-icon" style="background: linear-gradient(135deg, #9C27B0, #BA68C8);">
                    <i class="bi bi-calendar-check"></i>
                </div>

                <h5>
                    @if($proposalRejected) Edit Sempro
                    @elseif($hasEditableProposalDocs) Edit Dokumen Sempro
                    @elseif($proposal) Lihat Sempro
                    @else Daftar Sempro
                    @endif
                </h5>
                <p>Pendaftaran Seminar Proposal</p>

                @if(!$finalProject->title_approved_at)
                    <span class="status-badge warning">Judul Belum Disetujui</span>
                @elseif(!$finalProject->supervisor_1_id)
                    <span class="status-badge warning">Pembimbing Belum Ditentukan</span>
                @elseif($proposalRejected)
                    <span class="status-badge danger">Ditolak — Klik untuk Edit</span>
                @elseif($hasEditableProposalDocs)
                    <span class="status-badge revision">Edit Dokumen</span>
                @elseif($proposalApproved)
                    <span class="status-badge active">Approved</span>
                @elseif($proposal)
                    <span class="status-badge warning">Menunggu Review</span>
                @else
                    <span class="status-badge info">Belum Daftar</span>
                @endif
            </a>

            {{-- <!-- Daftar Sidang -->
            <a href="{{ route('student.final-project.defense.create') }}" class="menu-card"
               @if(!$finalProject->proposal || $finalProject->proposal->status !== 'approved') style="opacity: 0.5; pointer-events: none;" @endif>
                <div class="menu-icon" style="background: linear-gradient(135deg, #FF5252, #FF8A80);">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <h5>Daftar Sidang</h5>
                <p>Pendaftaran Sidang TA</p>
                @if(!$finalProject->proposal || $finalProject->proposal->status !== 'approved')
                    <span class="status-badge warning">Sempro Belum Disetujui</span>
                @elseif($finalProject->defense)
                    <span class="status-badge {{ $finalProject->defense->status === 'approved' ? 'active' : 'warning' }}">
                        {{ ucfirst($finalProject->defense->status) }}
                    </span>
                @else
                    <span class="status-badge info">Belum Daftar</span>
                @endif
            </a> --}}

            @php
                $defense = $finalProject->defense;
                $defenseRejected = $defense && $defense->status === 'rejected';
                $defenseApproved = $defense && $defense->status === 'approved';

                $defenseHasEditableDocs = $defense
                    ? $finalProject->documents
                        ->where('document_type', 'final')
                        ->whereIn('review_status', ['needs_revision', 'rejected'])
                        ->count() > 0
                    : false;
            @endphp

            <a href="{{
                    $defenseRejected || $defenseHasEditableDocs
                        ? route('student.final-project.defense.edit', $defense->id)
                        : ($defense
                            ? route('student.final-project.defense.show', $defense->id)
                            : route('student.final-project.defense.create'))
                }}"
            class="menu-card"
            @if(!$finalProject->proposal || $finalProject->proposal->status !== 'approved') style="opacity: 0.5; pointer-events: none;" @endif>

                <div class="menu-icon" style="background: linear-gradient(135deg, #FF5252, #FF8A80);">
                    <i class="bi bi-clipboard-check"></i>
                </div>

              <h5>
                @if($defenseRejected)
                    Edit Sidang
                @elseif($defenseHasEditableDocs)
                    Edit Dokumen Sidang
                @elseif($defense)
                    Lihat Sidang
                @else
                    Daftar Sidang
                @endif
            </h5>

            <p>
                @if($defenseHasEditableDocs)
                    Revisi dokumen pendaftaran sidang TA
                @else
                    Pendaftaran Sidang TA
                @endif
            </p>

            @if(!$finalProject->proposal || $finalProject->proposal->status !== 'approved')
                <span class="status-badge warning">Sempro Belum Disetujui</span>
            @elseif($defenseRejected)
                <span class="status-badge danger">Ditolak - Klik untuk Edit</span>
            @elseif($defenseHasEditableDocs)
                <span class="status-badge revision">Edit Dokumen</span>
            @elseif($defenseApproved)
                <span class="status-badge active">Approved</span>
            @elseif($defense)
                <span class="status-badge warning">Menunggu Review</span>
            @else
                <span class="status-badge info">Belum Daftar</span>
            @endif

            </a>

            <!-- Log Bimbingan -->
            <a href="{{ route('student.final-project.guidance.index') }}" class="menu-card">
                <div class="menu-icon" style="background: linear-gradient(135deg, #4CAF50, #81C784);">
                    <i class="bi bi-journal-text"></i>
                </div>
                <h5>Log Bimbingan</h5>
                <p>Catatan bimbingan dengan dosen</p>
                <span class="status-badge active">{{ $stats['approved_guidance_count'] }} ACC</span>
            </a>

            <!-- Dokumen -->
            <a href="{{ route('student.final-project.documents.index') }}" class="menu-card">
                <div class="menu-icon" style="background: linear-gradient(135deg, #2196F3, #64B5F6);">
                    <i class="bi bi-folder"></i>
                </div>
                <h5>Dokumen</h5>
                <p>Upload dan kelola dokumen TA</p>
                <span class="status-badge active">{{ $stats['documents_count'] }} File</span>
            </a>
        </div>
    </div>
@endsection

@push('css')
<style>
    /* Pastikan modal selalu di atas backdrop & elemen layout yang z-index-nya tinggi */
    .modal-backdrop {
        z-index: 99990 !important;
    }
    .modal {
        z-index: 100000 !important;
    }

    .celebration-card {
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid rgba(255, 152, 0, 0.18);
        box-shadow: 0 18px 55px rgba(0,0,0,0.18);
    }
    .celebration-body {
        position: relative;
        padding: 22px 18px;
        text-align: center;
        background: radial-gradient(1200px 400px at 50% 0%, rgba(255,152,0,0.16), transparent 60%),
                    linear-gradient(135deg, rgba(255,255,255,1), rgba(255,251,240,1));
    }
    .celebration-icon {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        margin: 0 auto 12px;
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.22), rgba(255, 179, 71, 0.12));
        color: var(--primary-orange);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        box-shadow: 0 14px 35px rgba(255,152,0,0.18);
    }
    .celebration-title {
        margin: 0;
        font-weight: 900;
        color: #333;
    }
    .celebration-text {
        margin: 10px 0 0;
        color: #555;
        font-weight: 600;
        font-size: 13px;
        line-height: 1.6;
        white-space: pre-wrap;
    }
    .celebration-actions {
        margin-top: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-close-soft {
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 800;
        border: 1px solid rgba(0,0,0,0.08);
    }
    .btn-read-soft {
        border: none;
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 900;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        box-shadow: 0 10px 26px rgba(255,152,0,0.25);
    }
    .btn-read-soft:hover { filter: brightness(1.03); }
    .celebration-hint {
        margin-top: 12px;
        font-size: 12px;
        color: #777;
        font-weight: 600;
    }

    .confetti {
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
    }
    .confetti-piece {
        position: absolute;
        top: -12px;
        width: 9px;
        height: 14px;
        border-radius: 3px;
        background: var(--primary-orange);
        opacity: 0.85;
        animation: confetti-fall linear infinite;
        transform: rotate(0deg);
    }
    .confetti-piece:nth-child(3n) { background: #9C27B0; }
    .confetti-piece:nth-child(3n+1) { background: #2196F3; }
    .confetti-piece:nth-child(4n) { background: #4CAF50; }
    .confetti-piece:nth-child(5n) { background: #FF5252; }

    @keyframes confetti-fall {
        0% { transform: translateY(-12px) rotate(0deg); opacity: 0.0; }
        10% { opacity: 0.9; }
        100% { transform: translateY(520px) rotate(360deg); opacity: 0.15; }
    }

    .stats-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 25px;
    }

    .stats-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .stats-header h3 {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
    }

    .semester-badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: white;
    }

    .status-proposal { background: linear-gradient(135deg, #9C27B0, #BA68C8); }
    .status-research { background: linear-gradient(135deg, #2196F3, #64B5F6); }
    .status-defense { background: linear-gradient(135deg, #FF9800, #FFB347); }
    .status-completed { background: linear-gradient(135deg, #4CAF50, #81C784); }

    .progress-bar {
        width: 100%;
        height: 12px;
        background: #f0f0f0;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        transition: width 0.3s ease;
    }

    .stats-content {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 28px;
        color: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .stat-info h5 {
        font-size: 12px;
        color: var(--text-gray);
        margin: 0 0 5px;
    }

    .stat-info p {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    .supervisor-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .supervisor-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        box-shadow: var(--shadow);
    }

    .supervisor-card i {
        font-size: 40px;
        color: var(--primary-orange);
        margin-bottom: 10px;
    }

    .supervisor-card h5 {
        font-size: 12px;
        color: var(--text-gray);
        margin: 0 0 5px;
    }

    .supervisor-card p {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
    }

    .menu-section {
        margin-bottom: 30px;
    }

    .section-header {
        display: flex;
        justifycontent: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .section-header h3 {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .menu-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        box-shadow: var(--shadow);
        transition: var(--transition-normal);
        cursor: pointer;
        text-decoration: none;
        display: block;
    }

    .menu-card:hover {
        box-shadow: var(--shadow-hover);
        transform: translateY(-8px);
    }

    .menu-icon {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 35px;
        color: white;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .menu-card h5 {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
    }

    .menu-card p {
        font-size: 12px;
        color: var(--text-gray);
        margin-bottom: 12px;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-badge.active {
        background: #E8F5E9;
        color: #4CAF50;
    }

    .status-badge.warning {
        background: #FFF3E0;
        color: #FF9800;
    }

    .status-badge.info {
        background: #E3F2FD;
        color: #2196F3;
    }

    /* tambahan */
    .btn-edit {
        background: linear-gradient(135deg, #E53935, #EF5350);
        color: white;
        padding: 12px 20px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 900;
        box-shadow: 0 10px 26px rgba(198,40,40,0.22);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .status-badge.danger   { background: #FFEBEE; color: #C62828; }
    .status-badge.revision { background: #FFF8E1; color: #E65100; }


    @media (max-width: 768px) {
        .stats-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .semester-badge {
            order: -1; /* Pindahkan badge ke atas judul di mobile agar lebih terbaca */
        }
    }

    @media (min-width: 769px) {
        .menu-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('celebrationModal');
        if (!modalEl) return;
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: 'static', keyboard: false });
        bsModal.show();
    });
</script>
@endpush
