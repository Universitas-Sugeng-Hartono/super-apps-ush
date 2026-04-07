@extends('students.layouts.super-app')

@section('content')
    <div class="stats-card">
        <div class="stats-header">
            <h3>
                @if($proposal->status === 'rejected')
                    Edit & Ajukan Ulang Sempro
                @else
                    Revisi Dokumen Sempro
                @endif
            </h3>
        </div>
        <p style="margin-top: 15px; color: #666; font-size: 14px;">
            @if($proposal->status === 'rejected')
                Perbaiki dan ajukan ulang pendaftaran seminar proposal Anda.
            @else
                Upload ulang dokumen yang perlu direvisi. Dokumen lain tidak perlu diubah.
            @endif
        </p>
    </div>

    {{-- Catatan penolakan proposal (jika rejected) --}}
    @if($proposal->status === 'rejected' && $proposal->approval_notes)
        <div class="alert-rejected">
            <i class="bi bi-x-circle-fill"></i>
            <div>
                <div class="alert-title">Alasan Penolakan Proposal</div>
                <div class="alert-body">{{ $proposal->approval_notes }}</div>
            </div>
        </div>
    @endif

    {{-- Cek apakah semua dokumen sudah approved --}}
    @php
        $allDocsApproved = collect($existingDocs)->every(
            fn($doc) => $doc && $doc->review_status === 'approved'
        );
    @endphp

    {{-- Banner semua dokumen sudah approved --}}
    @if($allDocsApproved && $proposal->status === 'rejected')
        <div class="alert-info">
            <i class="bi bi-check-circle-fill"></i>
            <div>
                <div class="alert-title">Semua dokumen sudah disetujui</div>
                <div class="alert-body">
                    Dokumen Anda sudah lengkap dan disetujui oleh dosen.
                    Klik "Ajukan Ulang" untuk mengajukan kembali tanpa perlu upload ulang.
                </div>
            </div>
        </div>
    @endif

    {{-- Banner ada dokumen yang perlu direvisi --}}
    @if(!$allDocsApproved && $proposal->status !== 'rejected')
        <div class="alert-revision">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <div class="alert-title">Ada dokumen yang perlu direvisi</div>
                <div class="alert-body">
                    Upload ulang hanya dokumen yang ditandai
                    <span class="badge-revision-inline">Perlu Revisi</span>.
                    Dokumen lain tidak perlu diubah.
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-danger">
            <i class="bi bi-x-circle"></i> {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('student.final-project.proposal.update', $proposal->id) }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Informasi Mahasiswa --}}
        <div class="form-card">
            <h4>Informasi Mahasiswa</h4>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" class="form-control"
                       value="{{ auth()->guard('student')->user()->nama_lengkap }}" disabled>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>NIM</label>
                    <input type="text" class="form-control"
                           value="{{ auth()->guard('student')->user()->nim }}" disabled>
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <input type="text" class="form-control"
                           value="{{ auth()->guard('student')->user()->getCurrentSemester() }}" disabled>
                </div>
            </div>
        </div>

        {{-- Informasi TA --}}
        <div class="form-card">
            <h4>Informasi Tugas Akhir</h4>
            <div class="form-group">
                <label>Judul Tugas Akhir</label>
                <input type="text" class="form-control"
                       value="{{ $finalProject->title }}" disabled>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Pembimbing 1</label>
                    <input type="text" class="form-control"
                           value="{{ $finalProject->supervisor1->name ?? '-' }}" disabled>
                </div>
                <div class="form-group">
                    <label>Pembimbing 2</label>
                    <input type="text" class="form-control"
                           value="{{ $finalProject->supervisor2->name ?? '-' }}" disabled>
                </div>
            </div>
        </div>

        {{-- Upload Dokumen --}}
        <div class="form-card">
            <h4>Dokumen Sempro</h4>

            @if(!$allDocsApproved)
                <p style="margin-top: -10px; font-size: 13px; color: #666; margin-bottom: 20px;">
                    Format file: PDF/JPG/PNG. Maksimal 10MB per dokumen.
                </p>
            @endif

            @foreach([
                'proposal_file'                => 'Proposal Tugas Akhir',
                'eligibility_form_file'        => 'Form Penilaian Kelayakan Judul',
                'guidance_form_file'           => 'Form Bimbingan Tugas Akhir',
                'seminar_approval_form_file'   => 'Form Persetujuan Seminar Proposal',
                'seminar_attendance_form_file' => 'Form Mengikuti Seminar Proposal TA',
                'krs_file'                     => 'Kartu Rencana Studi Sem 1 - Sem Berjalan',
                'transcript_file'              => 'Transkrip Nilai',
            ] as $field => $label)
                @php
                    $oldDoc = $existingDocs[$field] ?? null;
                    $needsRevision = $oldDoc && $oldDoc->review_status === 'needs_revision';
                    $isRejectedDoc = $oldDoc && $oldDoc->review_status === 'rejected';
                    $isApproved = $oldDoc && $oldDoc->review_status === 'approved';
                    $isRejectedProposal = $proposal->status === 'rejected';
                    $needsAttention = $needsRevision || $isRejectedDoc;
                    $required = $needsAttention || ($isRejectedProposal && !$isApproved);
                @endphp


                <div class="form-group {{ $needsAttention ? 'group-revision' : ($isApproved ? 'group-approved' : '') }}">

                    <label>
                        {{ $label }}
                        @if($isApproved)
                            <span class="badge-approved">Disetujui</span>
                        @elseif($isRejectedDoc)
                            <span class="badge-revision badge-rejected">Ditolak</span>
                        @elseif($needsRevision)
                            <span class="badge-revision">Perlu Revisi</span>
                        @endif
                    </label>



                    {{-- Catatan revisi dari dosen --}}
                   @if($needsAttention && $oldDoc->review_notes)
                        <div class="revision-note {{ $isRejectedDoc ? 'revision-note-rejected' : '' }}">
                            <i class="bi bi-chat-left-text-fill"></i>
                            <span>{{ $oldDoc->review_notes }}</span>
                        </div>
                    @endif


                    {{-- File lama --}}
                    @if($oldDoc)
                        <div class="existing-file
                            {{ $needsAttention ? 'existing-file-revision' : '' }}
                            {{ $isApproved ? 'existing-file-approved' : '' }}">
                            <i class="bi bi-file-earmark-{{ $isApproved ? 'check' : ($needsAttention ? 'x' : 'text') }}"></i>
                            <span>File tersimpan —</span>
                            <a href="{{ asset('storage/' . ltrim($oldDoc->file_path, '/')) }}"
                               target="_blank">Lihat File</a>
                            <span class="file-version">v{{ $oldDoc->version }}</span>
                        </div>
                    @endif

                    {{-- Input file hanya untuk dokumen yang belum approved --}}
                    @if(!$isApproved)
                        <input type="file" name="{{ $field }}" class="form-control"
                               accept=".pdf,.jpg,.jpeg,.png"
                               {{ $required ? 'required' : '' }}>
                        <small>
                            @if($isRejectedDoc)
                                <span style="color: #C62828; font-weight: 700;">
                                    <i class="bi bi-x-circle"></i> Dokumen ditolak, wajib upload ulang
                                </span>
                            @elseif($needsRevision)
                                <span style="color: #E65100; font-weight: 700;">
                                    <i class="bi bi-exclamation-circle"></i> Wajib upload ulang
                                </span>
                            @elseif($oldDoc)
                                Kosongkan jika tidak ingin mengganti file lama
                            @else
                                File belum ada, harap upload
                            @endif
                        </small>
                    @else
                        <small style="color: #2E7D32; font-weight: 600;">
                            <i class="bi bi-check-circle"></i>
                            Dokumen sudah disetujui, tidak perlu diupload ulang
                        </small>
                    @endif

                    @error($field)<span class="error">{{ $message }}</span>@enderror
                </div>
            @endforeach
        </div>

        <div class="form-actions">
            <a href="{{ route('student.final-project.proposal.show', $proposal->id) }}"
               class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                @if($proposal->status === 'rejected')
                    Ajukan Ulang
                @else
                    Upload Revisi
                @endif
            </button>
        </div>
    </form>
@endsection

@push('css')
<style>
    .stats-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 25px;
    }
    .stats-header h3 { font-size: 20px; font-weight: 600; margin: 0; }

    .alert-rejected {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        background: #FFEBEE;
        border: 1px solid #FFCDD2;
        color: #C62828;
        padding: 18px 20px;
        border-radius: 16px;
        margin-bottom: 20px;
    }
    .alert-rejected i { font-size: 22px; flex-shrink: 0; margin-top: 2px; }

    .alert-info {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        background: #E3F2FD;
        border: 1px solid #BBDEFB;
        color: #1565C0;
        padding: 18px 20px;
        border-radius: 16px;
        margin-bottom: 20px;
    }
    .alert-info i { font-size: 22px; flex-shrink: 0; margin-top: 2px; }

    .alert-revision {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        background: #FFF8E1;
        border: 1px solid #FFE082;
        color: #E65100;
        padding: 18px 20px;
        border-radius: 16px;
        margin-bottom: 20px;
    }
    .alert-revision i { font-size: 22px; flex-shrink: 0; margin-top: 2px; }

    .alert-danger {
        background: #FFEBEE;
        color: #C62828;
        padding: 15px 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }

    .alert-title { font-weight: 800; font-size: 14px; margin-bottom: 4px; }
    .alert-body { font-size: 13px; line-height: 1.6; }

    .badge-revision-inline {
        display: inline-block;
        background: #FFF3E0;
        color: #E65100;
        font-size: 11px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 8px;
    }

    .form-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }
    .form-card h4 {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 20px;
        color: var(--primary-orange);
    }
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    .form-group { margin-bottom: 20px; }
    .form-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 8px;
        color: #333;
    }
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #E0E0E0;
        border-radius: 10px;
        font-size: 14px;
        transition: border-color 0.3s;
        box-sizing: border-box;
    }
    .form-control:focus { outline: none; border-color: var(--primary-orange); }
    .form-control:disabled { background: #F5F5F5; cursor: not-allowed; }
    .form-group small { display: block; margin-top: 5px; font-size: 12px; color: #999; }

    .group-revision {
        border-left: 3px solid #F57C00;
        padding-left: 14px;
    }
    .group-approved {
        border-left: 3px solid #4CAF50;
        padding-left: 14px;
    }

    .revision-note {
        display: flex;
        gap: 8px;
        align-items: flex-start;
        background: #FFF8E1;
        border: 1px solid #FFE082;
        color: #E65100;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 8px;
        line-height: 1.5;
    }
    .revision-note i { flex-shrink: 0; margin-top: 2px; }

    .existing-file {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: #F5F5F5;
        border-radius: 10px;
        font-size: 13px;
        color: #555;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .existing-file-revision {
        background: #FFF3E0 !important;
        color: #E65100 !important;
    }
    .existing-file-approved {
        background: #E8F5E9 !important;
        color: #2E7D32 !important;
    }
    .existing-file a { color: inherit; font-weight: 800; text-decoration: underline; }
    .file-version {
        margin-left: auto;
        font-size: 11px;
        background: rgba(0,0,0,0.08);
        padding: 2px 8px;
        border-radius: 6px;
    }

    .badge-revision {
        display: inline-block;
        background: #FFF3E0;
        color: #E65100;
        font-size: 11px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 8px;
    }
    .badge-approved {
        display: inline-block;
        background: #E8F5E9;
        color: #2E7D32;
        font-size: 11px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 8px;
    }

    .error { color: #C62828; font-size: 12px; display: block; margin-top: 5px; }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
    }
    .btn-primary, .btn-secondary {
        padding: 12px 30px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
        border: none;
    }
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255,112,67,0.4);
    }
    .btn-secondary { background: #E0E0E0; color: #666; }
    .btn-secondary:hover { background: #D0D0D0; }

     .badge-rejected {
        background: #FFEBEE;
        color: #C62828;
    }

    .revision-note-rejected {
        background: #FFEBEE;
        border: 1px solid #FFCDD2;
        color: #C62828;
    }
</style>
@endpush
