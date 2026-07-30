<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 10px;
    color: #333;
    background: #fff;
    width: 210mm;
    height: 297mm;
    overflow: hidden;
}
.cv-wrapper {
    width: 210mm;
    height: 297mm;
    position: relative;
    overflow: hidden;
}

.col-left {
    position: absolute;
    top: 0;
    left: 0;
    width: 65mm;
    height: 297mm;
    background: #1a2332;
    color: #fff;
    padding: 20px 14px;
    overflow: hidden;
}

.col-right {
    position: absolute;
    top: 0;
    left: 65mm;
    width: 125mm; /* ← 210mm - 65mm - 20mm (padding kanan) */
    height: 297mm;
    background: #fff;
    padding: 20px 14px;
    overflow: hidden;
}
/* ===== LEFT STYLES ===== */
.photo-wrap {
    text-align: center;
    margin-bottom: 14px;
}

.photo-wrap img {
    width: 75px;
    height: 75px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #FF9800;
}

.photo-placeholder {
    width: 75px;
    height: 75px;
    border-radius: 50%;
    background: #2d3e55;
    border: 3px solid #FF9800;
    margin: 0 auto;
    text-align: center;
    line-height: 75px;
    font-size: 28px;
    color: #FF9800;
}

.left-section { margin-bottom: 14px; }

.left-section-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #FF9800;
    border-bottom: 1px solid #FF9800;
    padding-bottom: 4px;
    margin-bottom: 8px;
}

.left-item {
    margin-bottom: 6px;
    font-size: 12px;
    line-height: 1.4;
    color: #ccc;
    word-break: break-all;
}

.left-item strong {
    display: block;
    color: #fff;
    font-size: 12px;
    margin-bottom: 1px;
}

.skill-item {
    background: #2d3e55;
    border: 1px solid #3d4e65;
    border-radius: 4px;
    padding: 6px 8px;
    margin-bottom: 6px;
    color: #ffffff;
    line-height: 1.3;
}

.skill-item .skill-title {
    color: #FF9800;
    font-size: 9.5px;
    font-weight: 700;
    display: block;
    margin-bottom: 1px;
}

.skill-item .skill-sub {
    color: #a0b0c5;
    font-size: 8px;
    display: block;
}

/* ===== RIGHT STYLES ===== */
.name-block {
    border-bottom: 2px solid #1a2332;
    padding: 12px 0 14px 0;
    margin-top: 35px;
    margin-bottom: 14px;
}

.name-block h1 {
    font-size: 19px;
    font-weight: 700;
    color: #1a2332;
    text-transform: uppercase;
    letter-spacing: 1px;
    line-height: 1.2;
    margin-bottom: 6px;
    word-break: break-word;
}

.name-block .subtitle {
    font-size: 12px;
    color: #FF9800;
    font-weight: 600;
    margin-top: 3px;
}

.name-block .nim {
    font-size: 8.5px;
    color: #888;
    margin-top: 2px;
}

.right-section {
    margin-bottom: 12px;
}

.right-section-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #1a2332;
    border-left: 3px solid #FF9800;
    padding-left: 8px;
    margin-bottom: 8px;
}

.right-section p {
    font-size: 9.5px;
    color: #555;
    line-height: 1.7;
    text-align: justify;
    word-break: break-word;
    overflow-wrap: break-word;
    white-space: normal;
    width: 100%;
}

.entry {
    margin-bottom: 6px;
    padding-bottom: 6px;
    border-bottom: 1px dashed #eee;
}

.entry:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.entry-title {
    font-weight: 700;
    color: #1a2332;
    font-size: 12px;
    word-break: break-word;
}

.entry-sub {
    color: #FF9800;
    font-size: 12px;
    margin-top: 1px;
    word-break: break-word;
}

.entry-meta {
    color: #888;
    font-size: 8px;
    margin-top: 3px;
}

.badge-level {
    display: inline-block;
    background: #FFF3E0;
    color: #E65100;
    border: 1px solid rgba(230, 81, 0, 0.1);
    border-radius: 3px;
    padding: 1px 4px;
    font-size: 7.5px;
    font-weight: 700;
    vertical-align: middle;
}

/* Certificate Compact List */
.cert-list {
    margin-top: 4px;
}

.cert-item {
    margin-bottom: 5px;
    padding-bottom: 4px;
    border-bottom: 1px solid #f5f5f5;
}

.cert-item:last-child {
    border-bottom: none;
}

.cert-title {
    font-weight: 600;
    font-size: 10px;
    color: #1a2332;
    display: inline-block;
}

.cert-sub {
    font-size: 8.5px;
    color: #888;
    margin-left: 5px;
}

.tugas-akhir-box {
    background: #f8f9fa;
    border-left: 3px solid #1a2332;
    border-radius: 3px;
    padding: 7px 10px;
}

.tugas-akhir-box .ta-label {
    font-size: 12px;
    color: #888;
    margin-bottom: 2px;
}

.tugas-akhir-box .ta-title {
    font-size: 9.5px;
    font-weight: 700;
    color: #1a2332;
    line-height: 1.4;
    word-break: break-word;
}

.tugas-akhir-box .ta-prodi {
    font-size: 8.5px;
    color: #FF9800;
    margin-top: 2px;
}

.empty-text {
    color: #bbb;
    font-size: 9px;
    font-style: italic;
}
    </style>
</head>
<body>
<div class="cv-wrapper">

    {{-- ===== KOLOM KIRI ===== --}}
    <div class="col-left">

        <div class="photo-wrap">
            @if($fotoDataUri)
                <img src="{{ $fotoDataUri }}" alt="Foto">
            @else
                <div class="photo-placeholder">
                    {{ strtoupper(substr($student->nama_lengkap, 0, 1)) }}
                </div>
            @endif
        </div>
        <div class="left-section">
            <div class="left-section-title">Personal</div>
                @if($student->tanggal_lahir)
                    <div class="left-item">
                        <strong>Tempat / Tgl Lahir</strong>
                        {{ $student->tempat_lahir ?? '-' }},
                        {{ $student->tanggal_lahir->format('d M Y') }}
                    </div>
                @endif
                @if($student->jenis_kelamin)
                    <div class="left-item">
                        <strong>Jenis Kelamin</strong>
                        {{ $student->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </div>
                @endif
            </div>
        <div class="left-section">
            <div class="left-section-title">Contact</div>
            @if($student->no_telepon)
                <div class="left-item">
                    <strong>Phone</strong>{{ $student->no_telepon }}
                </div>
            @endif
            @if($student->email)
                <div class="left-item">
                    <strong>Email</strong>{{ $student->email }}
                </div>
            @endif
            @if($student->alamat)
                <div class="left-item">
                    <strong>Address</strong>{{ Str::limit($student->alamat, 80) }}
                </div>
            @endif
        </div>

        <div class="left-section">
            <div class="left-section-title">Academic</div>
            <div class="left-item">
                <strong>Program Studi</strong>{{ $student->program_studi ?? '-' }}
            </div>
            <div class="left-item">
                <strong>Angkatan</strong>{{ $student->angkatan ?? '-' }}
            </div>
            <div class="left-item">
                <strong>IPK</strong>{{ $student->ipk ?? '-' }} / 4.00
            </div>
            <div class="left-item">
                <strong>Total SKS</strong>{{ $student->sks ?? '-' }} SKS
            </div>
            @if($student->tanggal_masuk)
                <div class="left-item">
                    <strong>Tahun Masuk</strong>{{ $student->tanggal_masuk->format('Y') }}
                </div>
            @endif
            @if($student->tanggal_lulus)
                <div class="left-item">
                    <strong>Tahun Lulus</strong>{{ $student->tanggal_lulus->format('Y') }}
                </div>
            @endif
        </div>



    </div>

    {{-- ===== KOLOM KANAN ===== --}}
    <div class="col-right">

        <div class="name-block">
            <h1>{{ $student->nama_lengkap }}</h1>
            <div class="subtitle">
                {{ $student->program_studi ?? 'Mahasiswa' }} &mdash; Universitas Sugeng Hartono
            </div>
            <div class="nim">
                NIM: {{ $student->nim }}
                &nbsp;|&nbsp; Angkatan {{ $student->angkatan }}
                @if($student->jenis_kelamin)
                    &nbsp;|&nbsp; {{ $student->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                @endif
            </div>
        </div>
        {{-- Summary --}}
       {{-- Summary --}}
        @if($summary)
            <div class="right-section">
                <div class="right-section-title">Summary</div>
                <p style="font-size:9.5px; color:#555; line-height:1.7; text-align:justify; padding-right: 12px;">
                    {{ $summary }}
                </p>
            </div>
        @endif
        @if($student->finalProject?->title)
            <div class="right-section">
                <div class="right-section-title">Tugas Akhir</div>
                <div class="tugas-akhir-box">
                    <div class="ta-label">Judul Skripsi / Tugas Akhir</div>
                    <div class="ta-title">{{ $student->finalProject->title }}</div>
                    <div class="ta-prodi">{{ $student->program_studi }}</div>
                </div>
            </div>
        @endif

        @if($prestasi->count())
            <div class="right-section">
                <div class="right-section-title">Prestasi & Penghargaan</div>
                @foreach($prestasi->take(5) as $p)
                    <div class="entry">
                        <div class="entry-title">{{ $p->activity_type_label ?? $p->achievement }}</div>
                        <div class="entry-sub">{{ $p->event !== '-' ? $p->event : '' }}</div>
                        @if(($p->level && $p->level !== '-') || ($p->participation_role && $p->participation_role !== '-'))
                        <div class="entry-meta">
                            @if($p->level && $p->level !== '-')
                                <span class="badge-level">{{ $p->level }}</span>
                            @endif
                            @if($p->participation_role && $p->participation_role !== '-')
                                {!! ($p->level && $p->level !== '-') ? '&sdot;' : '' !!} {{ $p->participation_role }}
                            @endif
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($organisasi->count())
            <div class="right-section">
                <div class="right-section-title">Organisasi</div>
                @foreach($organisasi->take(4) as $o)
                    <div class="entry">
                        <div class="entry-title">{{ $o->activity_type_label ?? $o->achievement }}</div>
                        <div class="entry-sub">{{ $o->event !== '-' ? $o->event : '' }}</div>
                        @if(($o->level && $o->level !== '-') || ($o->participation_role && $o->participation_role !== '-'))
                        <div class="entry-meta">
                            @if($o->level && $o->level !== '-')
                                <span class="badge-level">{{ $o->level }}</span>
                            @endif
                            @if($o->participation_role && $o->participation_role !== '-')
                                {!! ($o->level && $o->level !== '-') ? '&sdot;' : '' !!} {{ $o->participation_role }}
                            @endif
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($magang->count())
            <div class="right-section">
                <div class="right-section-title">Pengalaman Kerja / Magang</div>
                @foreach($magang->take(4) as $m)
                    <div class="entry">
                        <div class="entry-title">{{ $m->activity_type_label ?? $m->achievement }}</div>
                        <div class="entry-sub">{{ $m->event !== '-' ? $m->event : '' }}</div>
                        @if(($m->level && $m->level !== '-') || ($m->participation_role && $m->participation_role !== '-'))
                        <div class="entry-meta">
                            @if($m->level && $m->level !== '-')
                                <span class="badge-level">{{ $m->level }}</span>
                            @endif
                            @if($m->participation_role && $m->participation_role !== '-')
                                {!! ($m->level && $m->level !== '-') ? '&sdot;' : '' !!} {{ $m->participation_role }}
                            @endif
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($sertifikat->count())
            <div class="right-section">
                <div class="right-section-title">Sertifikat & Keahlian</div>
                <div class="cert-list">
                @foreach($sertifikat->take(6) as $s)
                    @php
                        $title = $s->activity_type_label ?? ($s->achievement !== '-' ? $s->achievement : null);
                        $sub = $s->event !== '-' ? $s->event : null;
                    @endphp

                    @if($title || $sub)
                        <div class="cert-item">
                            <span class="cert-title">{{ $title }}</span>
                            @if($sub)
                                <span class="cert-sub">&mdash; {{ $sub }}</span>
                            @endif
                            @if($s->level && $s->level !== '-')
                                <span class="badge-level" style="margin-left: 5px;">{{ $s->level }}</span>
                            @endif
                        </div>
                    @endif
                @endforeach
                </div>
            </div>
        @endif

        @if(!$prestasi->count() && !$organisasi->count() && !$magang->count() && !$student->finalProject?->title)
            <div class="right-section">
                <p class="empty-text">Belum ada data prestasi, organisasi, magang, atau tugas akhir.</p>
            </div>
        @endif

    </div>

</div>
</body>
</html>
