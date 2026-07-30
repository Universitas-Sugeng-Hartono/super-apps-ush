@php
    $layout = 'landing.layout';
    if (auth()->check()) {
        $layout = 'admin.layouts.super-app';
    } elseif (session()->has('student_id')) {
        $layout = 'students.layouts.super-app';
    }
@endphp

@extends($layout)

@section('content')
    <div class="content-card">
        <div class="card-header">
            <div>
                <h3>Calendar</h3>
                <p class="subtext">Jadwal Sempro & Sidang yang sudah ditentukan Kaprodi.</p>
            </div>
            <div class="month-nav">
                <a class="btn-nav" href="{{ route('calendar.index', ['month' => $prevMonth]) }}">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <div class="month-label">
                    {{ $calendar['month']->translatedFormat('F Y') }}
                </div>
                <a class="btn-nav" href="{{ route('calendar.index', ['month' => $nextMonth]) }}">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        <div class="grid">
            <div class="card-surface">
                <div class="section-title">
                    <i class="bi bi-calendar3-event"></i>
                    <span>Kalender Bulanan</span>
                </div>

                <div class="table-responsive">
                    <table class="calendar-table">
                        <thead>
                            <tr>
                                <th>Sen</th><th>Sel</th><th>Rab</th>
                                <th>Kam</th><th>Jum</th><th>Sab</th><th>Min</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                            $cursor = $calendar['start']->copy();
                            $end = $calendar['end']->copy();
                        @endphp

                        @while($cursor <= $end)
                            <tr>
                                @for($i=0;$i<7;$i++)
                                    @php
                                        $key = $cursor->format('Y-m-d');
                                        $inMonth = $cursor->month === $calendar['month']->month;
                                        $items = $eventsByDate[$key] ?? [];
                                        $count = is_array($items) ? count($items) : 0;
                                        $today = $cursor->isToday();
                                    @endphp
                                    <td class="{{ !$inMonth ? 'day-muted' : '' }} {{ $today ? 'day-today' : '' }}">
                                        <div class="day-top">
                                            <span class="day-pill">{{ $cursor->day }}</span>
                                            @if($count > 0)
                                                <span class="day-count">{{ $count }}</span>
                                            @endif
                                        </div>

                                        @if($count > 0)
                                            <div class="day-events">
                                                @foreach(array_slice($items, 0, 2) as $e)
                                                    <div class="event-chip {{ $e['type'] === 'Sidang' ? 'chip-danger' : 'chip-purple' }}">
                                                        {{ $e['type'] }}
                                                        <span class="muted">•</span>
                                                        {{ \Illuminate\Support\Str::limit($e['student_name'], 14) }}
                                                    </div>
                                                @endforeach
                                                @if($count > 2)
                                                    <div class="more">+{{ $count - 2 }} lagi</div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    @php $cursor->addDay(); @endphp
                                @endfor
                            </tr>
                        @endwhile
                        </tbody>
                    </table>
                </div>

                <div class="legend">
                    <span class="event-chip chip-purple">Sempro</span>
                    <span class="event-chip chip-danger">Sidang</span>
                    <span class="muted">Angka di kanan tanggal menunjukkan jumlah event pada hari tersebut.</span>
                </div>

                <div class="upcoming">
                    <div class="section-title" style="margin-top:14px;">
                        <i class="bi bi-lightning-charge"></i>
                        <span>Jadwal Terdekat</span>
                    </div>
                    @if(($upcomingEvents?->count() ?? 0) > 0)
                        <div class="upcoming-list">
                            @foreach($upcomingEvents as $e)
                                <div class="upcoming-item">
                                    <div class="up-content">
                                        <div class="up-header">
                                            <div class="up-left">
                                                <span class="event-chip {{ $e['type'] === 'Sidang' ? 'chip-danger' : 'chip-purple' }}">{{ $e['type'] }}</span>
                                                <div class="up-title">{{ $e['student_name'] }}</div>
                                            </div>
                                            <div class="up-time">{{ $e['datetime']->translatedFormat('d M Y H:i') }}</div>
                                        </div>
                                        @if(!empty($e['project_title'] ?? ''))
                                            <div class="up-project-title">{{ $e['project_title'] }}</div>
                                        @endif
                                        @if(!empty($e['approval_notes'] ?? ''))
                                            <div class="up-notes">
                                                <strong>Catatan Kaprodi:</strong> {{ $e['approval_notes'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="muted">Belum ada jadwal terdekat.</div>
                    @endif
                </div>
            </div>

            <div class="card-surface">
                <div class="section-title">
                    <i class="bi bi-list-check"></i>
                    <span>Aktivitas Kalender</span>
                </div>

                @if(($eventsPage?->count() ?? 0) > 0)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Mahasiswa</th>
                                    <th>Judul TA</th>
                                    <th>Catatan Kaprodi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eventsPage as $e)
                                    <tr>
                                        <td>{{ $e['datetime']->translatedFormat('d M Y H:i') }}</td>
                                        <td>
                                            <span class="event-chip {{ $e['type'] === 'Sidang' ? 'chip-danger' : 'chip-purple' }}">
                                                {{ $e['type'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600;">{{ $e['student_name'] }}</div>
                                            <div style="font-size: 11px; color: #999;">{{ $e['nim'] }} • {{ $e['prodi'] }}</div>
                                        </td>
                                        <td>
                                            @if(!empty($e['project_title'] ?? ''))
                                                <div style="font-size: 12px; line-height: 1.4;">{{ $e['project_title'] }}</div>
                                            @else
                                                <span class="muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($e['approval_notes'] ?? ''))
                                                <div style="font-size: 12px; line-height: 1.4; color: #666;">{{ $e['approval_notes'] }}</div>
                                            @else
                                                <span class="muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $e['status'] === 'approved' ? 'active' : 'warning' }}">
                                                {{ ucfirst($e['status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-wrapper">
                        {{ $eventsPage->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Belum ada jadwal untuk bulan ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    .content-card {
        background: transparent;
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 2px solid rgba(0,0,0,0.06);
    }

    .subtext {
        margin: 6px 0 0;
        color: #777;
        font-size: 13px;
        font-weight: 500;
    }

    .month-nav {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-nav {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: white;
        border: 1px solid rgba(255, 152, 0, 0.18);
        box-shadow: var(--shadow);
        color: var(--primary-orange);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .month-label {
        background: white;
        padding: 10px 14px;
        border-radius: 14px;
        border: 1px solid rgba(255, 152, 0, 0.18);
        box-shadow: var(--shadow);
        font-weight: 800;
        color: #333;
        min-width: 180px;
        text-align: center;
    }

    .grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 16px;
    }

    .card-surface {
        background: white;
        border-radius: 18px;
        padding: 18px;
        box-shadow: var(--shadow);
        border: 1px solid rgba(255, 152, 0, 0.08);
    }

    .section-title {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        color: #333;
        font-size: 14px;
        margin-bottom: 12px;
    }

    .section-title i {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.18), rgba(255, 179, 71, 0.10));
        color: var(--primary-orange);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .calendar-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #F0F0F0;
    }

    .calendar-table th {
        background: linear-gradient(135deg, #FFF3E0, #FFFBF0);
        padding: 10px 8px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #666;
    }

    .calendar-table td {
        vertical-align: top;
        padding: 10px;
        border-top: 1px solid #F0F0F0;
        border-right: 1px solid #F0F0F0;
        height: 110px;
        background: white;
    }

    .calendar-table tr td:last-child {
        border-right: none;
    }

    .day-muted {
        background: #FAFAFA !important;
        opacity: 0.7;
    }

    .day-today {
        outline: 2px solid rgba(255, 152, 0, 0.35);
        outline-offset: -2px;
    }

    .day-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .day-pill {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        background: rgba(255, 152, 0, 0.12);
        color: #333;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 12px;
    }

    .day-count {
        background: rgba(33, 150, 243, 0.12);
        color: #1565C0;
        border: 1px solid rgba(33, 150, 243, 0.2);
        font-weight: 800;
        border-radius: 999px;
        padding: 2px 8px;
        font-size: 11px;
    }

    .day-events {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .event-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .chip-purple {
        background: rgba(156, 39, 176, 0.12);
        color: #6A1B9A;
        border: 1px solid rgba(156, 39, 176, 0.18);
    }

    .chip-danger {
        background: rgba(244, 67, 54, 0.12);
        color: #C62828;
        border: 1px solid rgba(244, 67, 54, 0.18);
    }

    .more {
        font-size: 11px;
        color: #777;
        font-weight: 700;
        margin-left: 2px;
    }

    .legend {
        margin-top: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .upcoming-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .upcoming-item {
        padding: 12px;
        border-radius: 14px;
        border: 1px solid rgba(0,0,0,0.06);
        background: #fff;
    }

    .up-content {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .up-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .up-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1;
    }

    .up-title {
        font-weight: 900;
        color: #333;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 260px;
    }

    .up-time {
        font-weight: 800;
        color: #666;
        font-size: 12px;
        white-space: nowrap;
    }

    .up-project-title {
        font-size: 12px;
        color: #555;
        font-weight: 600;
        line-height: 1.4;
        margin-top: 4px;
    }

    .up-notes {
        font-size: 11px;
        color: #666;
        line-height: 1.4;
        padding: 8px;
        background: rgba(0,0,0,0.02);
        border-radius: 8px;
        border-left: 3px solid var(--primary-orange);
        margin-top: 4px;
    }

    .up-notes strong {
        color: #444;
        display: block;
        margin-bottom: 4px;
    }

    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #F0F0F0;
    }

    .data-table thead {
        background: linear-gradient(135deg, #FFF3E0, #FFFBF0);
    }

    .data-table th {
        padding: 12px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #666;
    }

    .data-table td {
        padding: 12px;
        border-top: 1px solid #F0F0F0;
        font-size: 13px;
        color: #333;
        vertical-align: top;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
    }

    .status-badge.active {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.warning {
        background: #FFF3E0;
        color: #E65100;
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

    .empty-state {
        text-align: center;
        padding: 50px 10px;
        color: #999;
    }

    .empty-state i {
        font-size: 56px;
        color: #E0E0E0;
        margin-bottom: 12px;
    }

    @media (max-width: 1100px) {
        .grid { grid-template-columns: 1fr; }
        .calendar-table td { height: auto; }
    }
</style>
@endpush

