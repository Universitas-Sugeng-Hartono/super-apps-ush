@extends('landing.layout')

@section('content')
    <div class="landing-wrap">
        <div class="landing-card w-100 ann-shell" style="max-width: 980px;">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="ann-icon" aria-hidden="true">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div>
                        <h3 class="mb-1 ann-title">Pengumuman</h3>
                        <div class="text-muted ann-subtitle">Daftar pengumuman yang sudah dipublikasikan</div>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <span class="ann-chip">
                                <i class="bi bi-list-ul"></i>
                                Total: {{ $announcements->total() }}
                            </span>
                            <span class="ann-chip">
                                <i class="bi bi-clock-history"></i>
                                Urut terbaru
                            </span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('landing') }}" class="btn btn-outline-primary-ush">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            @if($announcements->count() === 0)
                <div class="empty-state">
                    <div class="empty-illustration" aria-hidden="true">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <div class="fw-semibold">Belum ada pengumuman</div>
                    <div class="text-muted" style="font-size: 13px;">Silakan cek kembali nanti.</div>
                </div>
            @else
                <div class="row g-3">
                    @foreach($announcements as $a)
                        <div class="col-12">
                            <a href="{{ route('announcements.show', $a->id) }}" class="ann-card">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div class="flex-grow-1">
                                        <div class="ann-card-title">{{ $a->title }}</div>
                                        <div class="ann-meta">
                                            <span class="ann-meta-item">
                                                <i class="bi bi-calendar-event"></i>
                                                {{ $a->published_at ? $a->published_at->translatedFormat('d M Y H:i') : '' }}
                                            </span>
                                        </div>
                                        @if(!empty($a->content))
                                            <div class="ann-excerpt">
                                                {{ \Illuminate\Support\Str::words($a->content, 40, '...') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ann-cta" aria-hidden="true">
                                        <span class="ann-cta-text">Baca</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $announcements->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('css')
<style>
    .ann-shell { overflow: hidden; }

    .ann-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: rgba(30, 99, 197, 0.10);
        color: var(--primary);
        flex: 0 0 auto;
    }

    .ann-icon i { font-size: 22px; }

    .ann-title {
        font-weight: 800;
        margin: 0;
        letter-spacing: -0.2px;
    }

    .ann-subtitle { font-size: 13px; }

    .ann-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(234, 242, 255, 0.90);
        border: 1px solid rgba(30, 99, 197, 0.12);
        color: rgba(15, 23, 42, 0.72);
        font-size: 12px;
        font-weight: 600;
    }

    .ann-card {
        display: block;
        text-decoration: none;
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 10px 22px rgba(0,0,0,.06);
        padding: 16px;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        color: inherit;
    }

    .ann-card:hover {
        transform: translateY(-2px);
        border-color: rgba(30, 99, 197, 0.18);
        box-shadow: 0 16px 30px rgba(0,0,0,.08);
    }

    .ann-card-title {
        font-weight: 800;
        font-size: 16px;
        line-height: 1.3;
        margin-bottom: 6px;
    }

    .ann-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 8px;
        color: rgba(15, 23, 42, 0.58);
        font-size: 12px;
        font-weight: 600;
    }

    .ann-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        background: rgba(234, 242, 255, 0.65);
        border: 1px solid rgba(30, 99, 197, 0.10);
    }

    .ann-excerpt {
        color: rgba(15, 23, 42, 0.64);
        font-size: 13px;
        line-height: 1.55;
        margin-top: 2px;
    }

    .ann-cta {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 12px;
        background: rgba(30, 99, 197, 0.08);
        color: var(--primary);
        font-weight: 800;
        font-size: 13px;
        height: fit-content;
        white-space: nowrap;
    }

    .empty-state {
        border-radius: 16px;
        border: 1px dashed rgba(15, 23, 42, 0.18);
        background: rgba(255, 255, 255, 0.7);
        padding: 26px;
        text-align: center;
    }

    .empty-illustration {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        margin: 0 auto 10px;
        display: grid;
        place-items: center;
        background: rgba(30, 99, 197, 0.10);
        color: var(--primary);
    }

    .empty-illustration i { font-size: 26px; }
</style>
@endpush

