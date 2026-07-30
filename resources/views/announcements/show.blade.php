@extends('landing.layout')

@section('content')
    <div class="landing-wrap">
        <div class="landing-card w-100" style="max-width: 980px;">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="detail-icon" aria-hidden="true">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div>
                        <h3 class="mb-1 detail-title">{{ $announcement->title }}</h3>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="detail-chip">
                                <i class="bi bi-calendar-event"></i>
                                {{ $announcement->published_at ? $announcement->published_at->translatedFormat('d M Y H:i') : '' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('announcements.index') }}" class="btn btn-outline-primary-ush">
                        <i class="bi bi-list"></i> Semua Pengumuman
                    </a>
                    <a href="{{ route('landing') }}" class="btn btn-outline-primary-ush">
                        <i class="bi bi-house"></i> Landing
                    </a>
                </div>
            </div>

            <div class="detail-body">
                {{ $announcement->content }}
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    .detail-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: rgba(30, 99, 197, 0.10);
        color: var(--primary);
        flex: 0 0 auto;
    }

    .detail-icon i { font-size: 22px; }

    .detail-title {
        font-weight: 900;
        letter-spacing: -0.2px;
        margin: 0;
        line-height: 1.25;
    }

    .detail-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(234, 242, 255, 0.90);
        border: 1px solid rgba(30, 99, 197, 0.12);
        color: rgba(15, 23, 42, 0.72);
        font-size: 12px;
        font-weight: 700;
    }

    .detail-body {
        background: rgba(255, 255, 255, 0.65);
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 16px;
        padding: 18px;
        white-space: pre-wrap;
        line-height: 1.7;
        color: rgba(15, 23, 42, 0.82);
        font-size: 14px;
    }
</style>
@endpush

