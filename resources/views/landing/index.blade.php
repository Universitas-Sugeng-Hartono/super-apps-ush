@extends('landing.layout')

@section('content')

<div class="landing-wrap" style="scale: 0.8;transform: translateY(-120px);max-height: 110vh">

    {{-- top bar --}}
    <div class="landing-card" style="background-color: #fff;">
        <div class="mb-4">
            <img class="brand-logo" src="{{ asset('ush.png') }}" alt="USH">
            <span class="fw-semibold"> Universitas Sugeng Hartono</span>
        </div>
    </div>
    {{-- ================= HEADER PUTIH ================= --}}
    <div class="landing-card w-100" style="max-width:1200px; padding-bottom:3.5rem; transform: translateY(-70px);">

        {{-- dekor --}}
        <img class="decor-megaphone tr" src="{{ asset('landing/megaphone.png') }}" alt="Megaphone">
        <img class="decor-megaphone bl" src="{{ asset('landing/megaphone.png') }}" alt="Megaphone">

        {{-- title --}}
        <h1 class="title-main" style="text-align: left;color: #383b42;">
            INFORMASI KAMPUS SUPER APPS<br>
            UNIVERSITAS SUGENG HARTONO (USH)
        </h1>

        <p class="subtitle mb-4">
            Pusat Informasi Terintegrasi untuk Civitas Akademika
        </p>

        {{-- ================= STAT CARDS ================= --}}
        <div class="row g-3">
            <div class="col-md-3">
                <div class="stat-card stat-blue p-3 h-100">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-label">Mahasiswa Terdaftar</div>
                            <div class="stat-value">{{ $studentsCount }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-mortarboard-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card stat-orange p-3 h-100">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-label">Dosen/Staff Terdaftar</div>
                            <div class="stat-value">{{ $lecturersCount }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-laptop fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card stat-green p-3 h-100">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-label">Prodi Terdaftar</div>
                            <div class="stat-value">{{ $prodiCount }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card p-3 h-100" style="background:#fff">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-label">Pengumuman Terbaru</div>
                            <div class="stat-value">
                                {{ $announcementsCount ?? $announcements->count() }}
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-megaphone-fill fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <br>

        <div class="row g-4">

            {{-- KALENDER --}}
            <div class="col-lg-7">
                <div class="panel card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-calendar3-event text-primary"></i>
                            <strong>Kalender Jadwal Sempro & Sidang ({{ $calendar['month']->translatedFormat('F Y') }})</strong>
                        </div>

                        <div class="table-responsive">
                            <table class="table calendar-table mb-0">
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
                                            @endphp
                                            <td>
                                                <span class="day-pill {{ !$inMonth ? 'day-muted' : '' }}">
                                                    {{ $cursor->day }}
                                                </span>
                                                @if($count > 0)
                                                    <div class="mt-2" style="display:flex; flex-direction:column; gap:6px;">
                                                        @foreach(array_slice($items, 0, 2) as $e)
                                                            <div style="font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 999px; border: 1px solid rgba(0,0,0,0.06);
                                                                background: {{ $e['type'] === 'Sidang' ? 'rgba(244,67,54,0.10)' : 'rgba(156,39,176,0.10)' }};
                                                                color: {{ $e['type'] === 'Sidang' ? '#C62828' : '#6A1B9A' }};">
                                                                {{ $e['type'] }} • {{ \Illuminate\Support\Str::limit($e['title'], 16) }}
                                                            </div>
                                                        @endforeach
                                                        @if($count > 2)
                                                            <div class="text-muted" style="font-size: 11px; font-weight: 700;">+{{ $count - 2 }} lagi</div>
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

                        <div class="mt-4">
                            <strong>Jadwal Terdekat</strong>
                            @if(($upcomingEvents?->count() ?? 0) > 0)
                                <div class="mt-2" style="display:flex; flex-direction:column; gap:10px;">
                                    @foreach($upcomingEvents as $e)
                                        <div style="padding: 12px; border-radius: 14px; border: 1px solid rgba(0,0,0,0.06); background: #fff;">
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom: 8px;">
                                                <div style="display:flex; align-items:center; gap:10px; min-width:0; flex:1;">
                                                    <span style="font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 999px; border: 1px solid rgba(0,0,0,0.06);
                                                        background: {{ $e['type'] === 'Sidang' ? 'rgba(244,67,54,0.10)' : 'rgba(156,39,176,0.10)' }};
                                                        color: {{ $e['type'] === 'Sidang' ? '#C62828' : '#6A1B9A' }};
                                                        white-space:nowrap;">
                                                        {{ $e['type'] }}
                                                    </span>
                                                    <div style="font-weight: 800; color:#333; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; flex:1;">
                                                        {{ $e['title'] }}
                                                    </div>
                                                </div>
                                                <div class="text-muted" style="font-size: 12px; font-weight: 700; white-space:nowrap;">
                                                    {{ $e['datetime']->translatedFormat('d M Y H:i') }}
                                                </div>
                                            </div>
                                            @if(!empty($e['project_title'] ?? ''))
                                                <div style="font-size: 13px; color:#555; font-weight: 600; margin-bottom: 6px; line-height: 1.4;">
                                                    {{ $e['project_title'] }}
                                                </div>
                                            @endif
                                            @if(!empty($e['approval_notes'] ?? ''))
                                                <div style="font-size: 12px; color:#666; line-height: 1.4; padding: 8px; background: rgba(0,0,0,0.02); border-radius: 8px; border-left: 3px solid {{ $e['type'] === 'Sidang' ? '#C62828' : '#6A1B9A' }};">
                                                    <strong style="color:#444;">Catatan Kaprodi:</strong> {{ $e['approval_notes'] }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted mt-1">
                                    Belum ada jadwal sempro/sidang untuk bulan ini.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- SIDEBAR --}}
            <div class="col-lg-5">

                {{-- Pengumuman --}}
                <div class="panel card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-megaphone-fill text-primary"></i>
                            <strong>Pengumuman Terbaru</strong>
                            </div>
                            <a href="{{ route('announcements.index') }}" class="text-primary fw-semibold" style="font-size: 12px; text-decoration: none;">
                                Lihat semua
                            </a>
                        </div>

                        @if($announcements->isEmpty())
                            <div class="text-muted">
                                Belum ada pengumuman yang dipublikasikan.
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($announcements->take(3) as $a)
                                    @php
                                        $maxWords = 250;
                                        $content = $a->content ?? '';
                                        $contentLimited = \Illuminate\Support\Str::words($content, $maxWords, '…');
                                        $isTruncated = trim($content) !== trim($contentLimited);
                                        $payload = [
                                            'id' => $a->id,
                                            'title' => $a->title,
                                            'date' => $a->published_at ? $a->published_at->translatedFormat('d M Y H:i') : '',
                                            'content' => $contentLimited,
                                            'truncated' => $isTruncated,
                                            'maxWords' => $maxWords,
                                            'url' => route('announcements.show', $a->id),
                                        ];
                                    @endphp
                                    <a href="#" class="list-group-item px-0 announcement-trigger"
                                       style="text-decoration:none; cursor:pointer;"
                                       data-announcement='@json($payload)'>
                                        <div class="fw-semibold">{{ $a->title }}</div>
                                        <div class="text-muted" style="font-size: 12px;">
                                            {{ $a->published_at ? $a->published_at->translatedFormat('d M Y H:i') : '' }}
                                        </div>
                                        @if(!empty($a->content))
                                            <div class="text-muted mt-1" style="font-size: 13px;">
                                                {{ \Illuminate\Support\Str::limit($a->content, 120) }}
                                            </div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Akses Cepat --}}
                <div class="panel card">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-info-circle-fill text-primary"></i>
                            <strong>Akses Cepat</strong>
                        </div>

                        <a href="{{ route('auth.login') }}" class="btn btn-outline-primary-ush w-100">
                            Masuk ke Portal
                        </a>

                        <div class="note mt-3">
                            Catatan: halaman ini publik. Untuk mengelola data silakan login terlebih dahulu.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- Modal Detail Pengumuman (klik item di sidebar) --}}
<div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="announcementModalTitle">Detail Pengumuman</h5>
                    <div class="text-muted" style="font-size: 12px;" id="announcementModalDate"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-muted mb-2" style="font-size: 12px;" id="announcementModalLimit"></div>
                <div id="announcementModalContent" style="white-space: pre-wrap; line-height: 1.6;"></div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-outline-primary-ush" id="announcementModalLink" href="{{ route('announcements.index') }}">
                    Buka halaman lengkap
                </a>
                <button type="button" class="btn btn-login" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('announcementModal');
        if (!modalEl) return;

        const titleEl = document.getElementById('announcementModalTitle');
        const dateEl = document.getElementById('announcementModalDate');
        const limitEl = document.getElementById('announcementModalLimit');
        const contentEl = document.getElementById('announcementModalContent');
        const linkEl = document.getElementById('announcementModalLink');

        document.querySelectorAll('.announcement-trigger').forEach((el) => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                const raw = el.getAttribute('data-announcement');
                if (!raw) return;

                let data = null;
                try { data = JSON.parse(raw); } catch (_) { return; }

                if (titleEl) titleEl.textContent = data.title || 'Detail Pengumuman';
                if (dateEl) dateEl.textContent = data.date || '';
                if (contentEl) contentEl.textContent = data.content || '';

                if (limitEl) {
                    const maxWords = data.maxWords || 250;
                    limitEl.textContent = data.truncated
                        ? `Konten ditampilkan maksimal ${maxWords} kata. (dipotong)`
                        : `Konten ditampilkan maksimal ${maxWords} kata.`;
                }

                if (linkEl && data.url) linkEl.setAttribute('href', data.url);

                const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                bsModal.show();
            });
        });
    });
</script>
@endpush
