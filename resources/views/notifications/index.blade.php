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
                <h3>Notification</h3>
                <p class="subtext">Notifikasi sistem untuk pengajuan & approval.</p>
            </div>
            <div class="actions">
                <span class="count">{{ $unreadCount }} belum dibaca</span>
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button class="btn-action" type="submit">
                        Tandai semua dibaca
                    </button>
                </form>
            </div>
        </div>

        @if($notifications->count() > 0)
            <div class="list">
                @foreach($notifications as $n)
                    <div class="item {{ $n->read_at ? '' : 'unread' }}">
                        <div class="meta">
                            <div class="title">
                                @if(!$n->read_at)
                                    <span class="dot"></span>
                                @endif
                                {{ $n->title }}
                            </div>
                            <div class="time">
                                {{ optional($n->created_at)->translatedFormat('d M Y H:i') }}
                                @if($n->type)
                                    <span class="chip">{{ $n->type }}</span>
                                @endif
                            </div>
                        </div>
                        @if($n->body)
                            <div class="body">{{ $n->body }}</div>
                        @endif
                        <div class="row-actions">
                            @if($n->url)
                                <a class="btn-link" href="{{ $n->url }}">Buka</a>
                            @endif
                            @if(!$n->read_at)
                                <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                                    @csrf
                                    <button class="btn-read" type="submit">Sudah dibaca</button>
                                </form>
                            @else
                                <span class="read-label">Sudah dibaca</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pagination-wrapper">
                {{ $notifications->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Belum ada notifikasi.</p>
            </div>
        @endif
    </div>
@endsection

@push('css')
<style>
    .card-header {
        display:flex; align-items:center; justify-content:space-between; gap: 14px;
        margin-bottom: 16px; padding-bottom: 14px;
        border-bottom: 2px solid rgba(0,0,0,0.06);
    }
    .subtext { margin: 6px 0 0; color:#777; font-size: 13px; font-weight: 500; }
    .actions { display:flex; align-items:center; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
    .count { background: rgba(33,150,243,0.12); color:#1565C0; border:1px solid rgba(33,150,243,0.2); padding:6px 10px; border-radius: 999px; font-weight: 800; font-size: 12px; }
    .btn-action { background: white; border: 1px solid rgba(255, 152, 0, 0.25); padding: 10px 12px; border-radius: 12px; font-weight: 800; color: var(--primary-orange); box-shadow: var(--shadow); }
    .btn-action:hover { filter: brightness(0.98); }

    .list { display:flex; flex-direction: column; gap: 12px; }
    .item { background: white; border-radius: 18px; padding: 16px; box-shadow: var(--shadow); border:1px solid rgba(0,0,0,0.06); }
    .item.unread { border-color: rgba(255, 152, 0, 0.25); }
    .meta { display:flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
    .title { font-weight: 900; color:#333; display:flex; align-items:center; gap: 10px; }
    .dot { width: 10px; height: 10px; border-radius: 50%; background: var(--primary-orange); box-shadow: 0 0 0 4px rgba(255,152,0,0.18); }
    .time { color:#777; font-size: 12px; font-weight: 700; display:flex; align-items:center; gap: 8px; flex-wrap: wrap; justify-content: flex-end; text-align: right; }
    .chip { background: rgba(156, 39, 176, 0.12); color: #6A1B9A; border: 1px solid rgba(156, 39, 176, 0.18); padding: 3px 8px; border-radius: 999px; font-weight: 900; font-size: 11px; }
    .body { margin-top: 10px; color:#444; font-size: 13px; line-height: 1.6; white-space: pre-wrap; }
    .row-actions { margin-top: 12px; display:flex; align-items:center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .btn-link { color: var(--primary-orange); font-weight: 900; text-decoration: none; }
    .btn-read { background: linear-gradient(135deg, var(--primary-orange), #FFB347); border: none; color: white; padding: 10px 14px; border-radius: 12px; font-weight: 900; box-shadow: 0 8px 22px rgba(255,152,0,0.25); }
    .btn-read:hover { filter: brightness(1.02); }
    .read-label { color: #2E7D32; font-weight: 900; font-size: 12px; background: #E8F5E9; padding: 6px 10px; border-radius: 999px; }
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
    .empty-state { text-align:center; padding: 50px 10px; color:#999; }
    .empty-state i { font-size: 56px; color:#E0E0E0; margin-bottom: 12px; }
    @media(max-width: 768px) {
        .meta { flex-direction: column; align-items: flex-start; }
        .time { justify-content: flex-start; text-align: left; }
    }
</style>
@endpush

