@extends('students.layouts.super-app')

@section('content')
    <div class="stats-card">
        <div class="stats-header">
            <h3>
                @if($defense->status === 'rejected')
                    Edit & Ajukan Ulang Sidang TA
                @else
                    Revisi Dokumen Sidang TA
                @endif
            </h3>
        </div>
        <p style="margin-top: 15px; color: #666; font-size: 14px;">
            @if($defense->status === 'rejected')
                Perbaiki dan ajukan ulang pendaftaran sidang Tugas Akhir Anda.
            @else
                Upload ulang dokumen yang perlu direvisi.
            @endif
        </p>
    </div>

    {{-- Catatan penolakan --}}
    @if($defense->status === 'rejected' && $defense->approval_notes)
        <div class="alert-rejected">
            <i class="bi bi-x-circle-fill"></i>
            <div>
                <div class="alert-title">Alasan Penolakan</div>
                <div class="alert-body">{{ $defense->approval_notes }}</div>
            </div>
        </div>
    @endif

    {{-- Banner revisi dokumen --}}
    @if($hasNeedsRevision && $defense->status !== 'rejected')
        <div class="alert-revision">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <div class="alert-title">Dokumen perlu direvisi</div>
                <div class="alert-body">Upload ulang Draft Final TA yang sudah diperbaiki.</div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-danger">
            <i class="bi bi-x-circle"></i> {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('student.final-project.defense.update', $defense->id) }}"
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

        {{-- Data Pribadi --}}
        <div class="form-card">
            <h4>Data Pribadi</h4>
            <p class="hint">Periksa dan perbaiki data berikut jika diperlukan.</p>
            <div class="form-row">
                <div class="form-group">
                    <label>NIK (16 digit) *</label>
                    <input type="text" name="nik" class="form-control"
                           value="{{ old('nik', auth()->guard('student')->user()->nik ?? '') }}"
                           inputmode="numeric" maxlength="16" required>
                    @error('nik')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>NISN *</label>
                    <input type="text" name="nisn" class="form-control"
                           value="{{ old('nisn', auth()->guard('student')->user()->nisn ?? '') }}"
                           maxlength="20" required>
                    @error('nisn')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Tempat Lahir *</label>
                    <input type="text" name="tempat_lahir" class="form-control"
                           value="{{ old('tempat_lahir', auth()->guard('student')->user()->tempat_lahir ?? '') }}"
                           required>
                    @error('tempat_lahir')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Tanggal Lahir *</label>
                    <input type="date" name="tanggal_lahir" class="form-control"
                           value="{{ old('tanggal_lahir', optional(auth()->guard('student')->user()->tanggal_lahir)->format('Y-m-d')) }}"
                           required>
                    @error('tanggal_lahir')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Ibu Kandung *</label>
                    <input type="text" name="nama_ibu_kandung" class="form-control"
                           value="{{ old('nama_ibu_kandung', auth()->guard('student')->user()->nama_ibu_kandung ?? '') }}"
                           required>
                    @error('nama_ibu_kandung')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>No. Telp Aktif *</label>
                    <input type="text" name="no_telepon" class="form-control"
                           value="{{ old('no_telepon', auth()->guard('student')->user()->no_telepon ?? '') }}"
                           required>
                    @error('no_telepon')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        {{-- Informasi TA --}}
        <div class="form-card">
            <h4>Informasi Tugas Akhir</h4>
            <div class="form-group">
                <label>Judul Tugas Akhir (Bahasa Indonesia)</label>
                <input type="text" class="form-control" value="{{ $finalProject->title }}" disabled>
            </div>
            @if($finalProject->title_en)
            <div class="form-group">
                <label>Judul Tugas Akhir (Bahasa Inggris)</label>
                <input type="text" class="form-control" style="font-style: italic;" value="{{ $finalProject->title_en }}" disabled>
            </div>
            @endif
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
            <h4>Dokumen Sidang</h4>
            @php
                $labels = [
                    'ukt_semester_8_file'          => '1. Bebas biaya pendidikan (UKT Semester 8) *',
                    'bebas_perpustakaan_file'      => '2. Bebas peminjaman buku perpustakaan *',
                    'persetujuan_dospem_file'      => '3. Formulir persetujuan Dosen Pembimbing TA *',
                    'lembar_konsultasi_file'       => '4. Lembar Konsultasi TA (min. 8x konsultasi) *',
                    'transkrip_nilai_file'         => '5. Transkrip Nilai Sementara USH (sudah disahkan) *',
                    'turnitin_file'                => '6. Hasil Turnitin/plagiarism (maksimal 25%) *',
                    'sertifikat_pkkmb_file'        => '7. Sertifikat PKKMB *',
                    'final_draft_file'             => '8. Dokumen/Makalah TA *',
                    'dokumen_pendukung_prodi_file' => '9. Dokumen pendukung Prodi (jika ada)'
                ];
            @endphp

            @foreach($defenseDocsKeys as $inputName => $docTitle)
                @php
                    $docModel = $existingDocs[$inputName] ?? null;
                    $docNeedsRevision = $docModel && in_array($docModel->review_status, ['needs_revision', 'rejected']);
                @endphp
                <div class="form-group {{ $docNeedsRevision ? 'group-revision' : '' }}" style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px dashed #eee;">
                    <label>
                        {{ $labels[$inputName] }}
                        @if($docNeedsRevision)
                            <span class="badge-revision">Perlu Revisi</span>
                        @endif
                    </label>

                    {{-- Catatan revisi dari dosen --}}
                    @if($docNeedsRevision && $docModel?->review_notes)
                        <div class="revision-note">
                            <i class="bi bi-chat-left-text-fill"></i>
                            <span>{{ $docModel->review_notes }}</span>
                        </div>
                    @endif

                    @if($docModel)
                        <div class="existing-file {{ $docNeedsRevision ? 'existing-file-revision' : '' }}">
                            <i class="bi bi-file-earmark-{{ $docNeedsRevision ? 'x' : 'check' }}"></i>
                            <span>File saat ini tersimpan —</span>
                            <a href="{{ asset('storage/' . ltrim($docModel->file_path, '/')) }}"
                               target="_blank">Lihat File</a>
                            <span class="file-version">v{{ $docModel->version }}</span>
                        </div>
                    @endif

                    <input type="file" name="{{ $inputName }}" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <small>
                        @if($docNeedsRevision)
                            <span style="color: #E65100; font-weight: 700;">
                                <i class="bi bi-exclamation-circle"></i> Wajib upload ulang
                            </span>
                        @elseif($docModel)
                            Kosongkan jika tidak ingin mengganti file lama
                        @else
                            File belum ada, harap upload
                        @endif
                    </small>
                    @error($inputName)<span class="error">{{ $message }}</span>@enderror
                </div>
            @endforeach
        </div>

        <div class="form-actions">
            <a href="{{ route('student.final-project.defense.show', $defense->id) }}"
               class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                @if($defense->status === 'rejected')
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
        background: white; border-radius: 20px;
        padding: 20px; box-shadow: var(--shadow); margin-bottom: 25px;
    }
    .stats-header h3 { font-size: 20px; font-weight: 600; margin: 0; }

    .alert-rejected {
        display: flex; gap: 14px; align-items: flex-start;
        background: #FFEBEE; border: 1px solid #FFCDD2;
        color: #C62828; padding: 18px 20px;
        border-radius: 16px; margin-bottom: 20px;
    }
    .alert-rejected i { font-size: 22px; flex-shrink: 0; margin-top: 2px; }

    .alert-revision {
        display: flex; gap: 14px; align-items: flex-start;
        background: #FFF8E1; border: 1px solid #FFE082;
        color: #E65100; padding: 18px 20px;
        border-radius: 16px; margin-bottom: 20px;
    }
    .alert-revision i { font-size: 22px; flex-shrink: 0; margin-top: 2px; }

    .alert-danger {
        background: #FFEBEE; color: #C62828;
        padding: 15px 20px; border-radius: 15px;
        margin-bottom: 20px; display: flex;
        align-items: center; gap: 10px; font-weight: 600;
    }

    .alert-title { font-weight: 800; font-size: 14px; margin-bottom: 4px; }
    .alert-body { font-size: 13px; line-height: 1.6; }

    .form-card {
        background: white; border-radius: 20px;
        padding: 25px; box-shadow: var(--shadow); margin-bottom: 20px;
    }
    .form-card h4 {
        font-size: 16px; font-weight: 600;
        margin: 0 0 20px; color: var(--primary-orange);
    }
    .hint {
        margin-top: -10px; margin-bottom: 18px;
        font-size: 13px; color: #666; font-weight: 500;
    }
    .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
    .form-group { margin-bottom: 20px; }
    .form-group label {
        display: flex; align-items: center;
        font-size: 14px; font-weight: 500;
        margin-bottom: 8px; color: #333; gap: 8px;
    }
    .form-control {
        width: 100%; padding: 12px 15px;
        border: 2px solid #E0E0E0; border-radius: 10px;
        font-size: 14px; transition: border-color 0.3s; box-sizing: border-box;
    }
    .form-control:focus { outline: none; border-color: var(--primary-orange); }
    .form-control:disabled { background: #F5F5F5; cursor: not-allowed; }
    .form-group small { display: block; margin-top: 5px; font-size: 12px; color: #999; }

    .group-revision { border-left: 3px solid #F57C00; padding-left: 14px; }

    .revision-note {
        display: flex; gap: 8px; align-items: flex-start;
        background: #FFF8E1; border: 1px solid #FFE082;
        color: #E65100; padding: 10px 14px; border-radius: 10px;
        font-size: 13px; font-weight: 600; margin-bottom: 8px; line-height: 1.5;
    }
    .revision-note i { flex-shrink: 0; margin-top: 2px; }

    .existing-file {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px; background: #E8F5E9;
        border-radius: 10px; font-size: 13px;
        color: #2E7D32; margin-bottom: 8px; font-weight: 600;
    }
    .existing-file-revision {
        background: #FFF3E0 !important;
        color: #E65100 !important;
    }
    .existing-file a { color: #1B5E20; font-weight: 800; text-decoration: underline; }
    .existing-file-revision a { color: #BF360C !important; }
    .file-version {
        margin-left: auto; font-size: 11px;
        background: rgba(0,0,0,0.08); padding: 2px 8px; border-radius: 6px;
    }

    .badge-revision {
        display: inline-block; background: #FFF3E0;
        color: #E65100; font-size: 11px; font-weight: 800;
        padding: 2px 8px; border-radius: 8px;
    }

    .error { color: #C62828; font-size: 12px; display: block; margin-top: 5px; }

    .form-actions {
        display: flex; gap: 15px;
        justify-content: flex-end; margin-top: 30px;
    }
    .btn-primary, .btn-secondary {
        padding: 12px 30px; border-radius: 12px;
        font-weight: 600; font-size: 14px; cursor: pointer;
        transition: all 0.3s; text-decoration: none;
        display: inline-block; border: none;
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

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn-primary, .btn-secondary {
            width: 100%;
            text-align: center;
        }

        .stats-card, .form-card {
            padding: 15px;
        }
    }
</style>
@endpush
