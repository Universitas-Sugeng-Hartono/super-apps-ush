@extends('admin.layouts.super-app')

@section('content')
    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #2196F3, #64B5F6);">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-info">
                <h5>Total Mahasiswa</h5>
                <h3>{{ $stats['total'] }}</h3>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #9C27B0, #BA68C8);">
                <i class="bi bi-file-text"></i>
            </div>
            <div class="stat-info">
                <h5>Proposal</h5>
                <h3>{{ $stats['proposal'] }}</h3>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #FF9800, #FFB347);">
                <i class="bi bi-search"></i>
            </div>
            <div class="stat-info">
                <h5>Penelitian</h5>
                <h3>{{ $stats['research'] }}</h3>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #F44336, #FF8A80);">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <div class="stat-info">
                <h5>Sidang</h5>
                <h3>{{ $stats['defense'] }}</h3>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4CAF50, #81C784);">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-info">
                <h5>Selesai</h5>
                <h3>{{ $stats['completed'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Pending Items -->
    <div class="pending-section">
        <h4><i class="bi bi-exclamation-circle"></i> Item Pending Approval</h4>
        <div class="pending-grid">
            <a href="{{ route('admin.final-project.titles.index') }}" class="pending-card">
                <div class="pending-count">{{ $pendingItems['titles'] ?? 0 }}</div>
                <p>Pengajuan Judul</p>
            </a>
            <a href="{{ route('admin.final-project.proposals.index') }}" class="pending-card">
                <div class="pending-count">{{ $pendingItems['proposals'] }}</div>
                <p>Pendaftaran Sempro</p>
            </a>
            <a href="{{ route('admin.final-project.defenses.index') }}" class="pending-card">
                <div class="pending-count">{{ $pendingItems['defenses'] }}</div>
                <p>Pendaftaran Sidang</p>
            </a>
            <a href="{{ route('admin.final-project.guidance.index') }}" class="pending-card">
                <div class="pending-count">{{ $pendingItems['guidance'] }}</div>
                <p>Log Bimbingan</p>
            </a>
            <a href="{{ route('admin.final-project.documents.index') }}" class="pending-card">
                <div class="pending-count">{{ $pendingItems['documents'] }}</div>
                <p>Dokumen</p>
            </a>
        </div>
    </div>

    <!-- Management Links -->
    <div class="content-card" style="margin-top: 20px;">
        <div class="card-header">
            <h3>Pengelolaan</h3>
        </div>
        <div class="management-links">
            <a href="{{ route('admin.final-project.supervisors.index') }}" class="management-link">
                <i class="bi bi-people"></i>
                <span>Pengelolaan Pembimbing</span>
            </a>
        </div>
    </div>
    <br>

    <!-- Students List -->
    <div class="content-card">
        <div class="card-header">
            <h3>Mahasiswa Bimbingan</h3>
            <div class="filters">
                <select onchange="window.location.href='?status='+this.value" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="proposal" {{ request('status') == 'proposal' ? 'selected' : '' }}>Proposal</option>
                    <option value="research" {{ request('status') == 'research' ? 'selected' : '' }}>Penelitian</option>
                    <option value="defense" {{ request('status') == 'defense' ? 'selected' : '' }}>Sidang</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
        </div>

        @if($finalProjects->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Judul</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($finalProjects as $fp)
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <strong>{{ $fp->student->nama_lengkap }}</strong>
                                        <small>{{ $fp->student->nim }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 14px; font-weight: 600; color: #333; line-height: 1.4;">
                                        {{ $fp->title ?? '-' }}
                                    </div>
                                    @if($fp->title && $fp->title_en)
                                        <div style="font-size: 12px; color: #666; font-style: italic; margin-top: 4px; line-height: 1.4;">
                                            {{ $fp->title_en }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $fp->status }}">
                                        {{ ucfirst($fp->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="progress-mini">
                                        <div class="progress-fill" style="width: {{ $fp->progress_percentage }}%"></div>
                                    </div>
                                    <small>{{ $fp->progress_percentage }}%</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.final-project.show', $fp->id) }}" class="btn-view">
                                        <i class="bi bi-eye"></i> Lihat
                                    </a>
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
                <p>Belum ada mahasiswa bimbingan</p>
            </div>
        @endif
    </div>
@endsection

@push('css')
<style>
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
    }

    .stat-info h5 {
        font-size: 13px;
        color: #666;
        margin: 0 0 5px;
    }

    .stat-info h3 {
        font-size: 28px;
        font-weight: 700;
        margin: 0;
        color: #333;
    }

    .pending-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: var(--shadow);
        margin-bottom: 30px;
    }

    .pending-section h4 {
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 20px;
        color: #FF9800;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pending-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .pending-card {
        background: #FFF3E0;
        border: 2px solid #FFB347;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s;
    }

    .pending-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(255,152,0,0.3);
    }

    .pending-count {
        font-size: 36px;
        font-weight: 700;
        color: #F57C00;
        margin-bottom: 8px;
    }

    .pending-card p {
        font-size: 14px;
        color: #666;
        margin: 0;
    }

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
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #F5F5F5;
    }

    .card-header h3 {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    .filter-select {
        padding: 8px 15px;
        border: 2px solid #E0E0E0;
        border-radius: 8px;
        font-size: 14px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead {
        background: #F5F5F5;
    }

    .data-table th {
        padding: 12px;
        text-align: left;
        font-size: 13px;
        font-weight: 600;
        color: #666;
    }

    .data-table td {
        padding: 15px 12px;
        border-top: 1px solid #F0F0F0;
    }

    .student-info strong {
        display: block;
        font-size: 14px;
        color: #333;
        margin-bottom: 3px;
    }

    .student-info small {
        font-size: 12px;
        color: #999;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.status-proposal { background: #E1BEE7; color: #6A1B9A; }
    .status-badge.status-research { background: #BBDEFB; color: #1565C0; }
    .status-badge.status-defense { background: #FFCCBC; color: #D84315; }
    .status-badge.status-completed { background: #C8E6C9; color: #2E7D32; }

    .progress-mini {
        width: 100%;
        max-width: 150px;
        height: 8px;
        background: #E0E0E0;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 5px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
    }

    .btn-view {
        background: #E3F2FD;
        color: #1976D2;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-view:hover {
        background: #BBDEFB;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state i {
        font-size: 60px;
        margin-bottom: 15px;
    }

    .management-links {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .management-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px 20px;
        background: #F5F5F5;
        border-radius: 10px;
        text-decoration: none;
        color: #333;
        transition: all 0.3s;
        border: 2px solid transparent;
    }

    .management-link:hover {
        background: #FFF3E0;
        border-color: var(--primary-orange);
        transform: translateY(-2px);
    }

    .management-link i {
        font-size: 20px;
        color: var(--primary-orange);
    }

    .management-link span {
        font-weight: 500;
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
</style>
@endpush
