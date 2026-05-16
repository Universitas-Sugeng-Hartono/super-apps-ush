@extends('students.layouts.super-app')

@section('content')
    <div class="stats-card">
        <div class="stats-header">
            <h3>Pendaftaran Seminar Proposal</h3>
        </div>
        <p style="margin-top: 15px; color: #666; font-size: 14px;">
            Isi form berikut untuk mendaftar seminar proposal Tugas Akhir Anda.
        </p>
    </div>

    @if(session('error'))
        <div class="alert-danger">
            <i class="bi bi-x-circle"></i> {{ session('error') }}
        </div>
    @endif

    <form id="registrationForm" action="{{ route('student.final-project.proposal.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-card">
            <h4>Informasi Mahasiswa</h4>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" class="form-control" value="{{ auth()->guard('student')->user()->nama_lengkap }}" disabled>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>NIM</label>
                    <input type="text" class="form-control" value="{{ auth()->guard('student')->user()->nim }}" disabled>
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <input type="text" class="form-control" value="{{ auth()->guard('student')->user()->getCurrentSemester() }}" disabled>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h4>Informasi Tugas Akhir</h4>
            <div class="form-group">
                <label>Judul Tugas Akhir (Bahasa Indonesia) *</label>
                <input type="text" class="form-control" value="{{ $finalProject->title }}" disabled>
            </div>
            @if($finalProject->title_en)
            <div class="form-group">
                <label>Judul Tugas Akhir (Bahasa Inggris) *</label>
                <input type="text" class="form-control" style="font-style: italic;" value="{{ $finalProject->title_en }}" disabled>
            </div>
            @endif
            <small style="margin-top: -10px; display: block; margin-bottom: 20px;">Judul sudah didaftarkan. Hubungi dosen pembimbing jika ingin mengubah.</small>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Pembimbing 1 *</label>
                    <input type="text" class="form-control" value="{{ $finalProject->supervisor1->name ?? 'Belum ditentukan' }}" disabled>
                    @if(!$finalProject->supervisor1)
                        <small style="color: #FF9800;">Pembimbing 1 harus ditentukan oleh admin sebelum mendaftar sempro</small>
                    @endif
                </div>
                <div class="form-group">
                    <label>Pembimbing 2 (Opsional)</label>
                    <input type="text" class="form-control" value="{{ $finalProject->supervisor2->name ?? 'Belum ditentukan' }}" disabled>
                    <small>Pembimbing 2 bersifat opsional</small>
                </div>
            </div>
            <div class="info-box">
                <i class="bi bi-info-circle"></i>
                <div>
                    <strong>Jadwal Seminar</strong>
                    <div class="muted">
                        Jadwal sempro akan <b>ditentukan oleh Kaprodi</b> setelah pengajuan disetujui, dan akan muncul di kalender landing page.
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h4>Upload Dokumen Sempro</h4>
            <p style="margin-top: -10px; font-size: 13px; color: #666;">
                Format file: PDF/JPG/PNG. Maksimal ukuran file: 2MB per dokumen.
            </p>

            <div class="form-group">
                <label>Proposal Tugas Akhir *</label>
                <input type="file" name="proposal_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('proposal_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Form Penilaian Kelayakan Judul *</label>
                <input type="file" name="eligibility_form_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('eligibility_form_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Form Bimbingan Tugas Akhir *</label>
                <input type="file" name="guidance_form_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('guidance_form_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Form Persetujuan Seminar Proposal *</label>
                <input type="file" name="seminar_approval_form_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('seminar_approval_form_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Form Mengikuti Seminar Proposal TA *</label>
                <input type="file" name="seminar_attendance_form_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('seminar_attendance_form_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Kartu Rencana Studi Sem 1 - Sem Berjalan *</label>
                <input type="file" name="krs_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('krs_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Transkrip Nilai *</label>
                <input type="file" name="transcript_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('transcript_file')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('student.final-project.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary" id="submitBtn">Submit Pendaftaran</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.getElementById('registrationForm').addEventListener('submit', function() {
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Mengunggah Dokumen... Mohon Tunggu';
            submitBtn.style.cursor = 'not-allowed';
            submitBtn.style.opacity = '0.8';
        }
    });
</script>
@endpush

@push('css')
<style>
    .stats-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 25px;
    }

    .stats-header h3 {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    .alert-danger {
        background: #FFEBEE;
        color: #C62828;
        padding: 15px 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
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

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
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
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-orange);
    }

    .form-control:disabled {
        background: #F5F5F5;
        cursor: not-allowed;
    }

    .form-group small {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: #999;
    }

    .info-box {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 14px 16px;
        border-radius: 14px;
        background: #E3F2FD;
        border: 1px solid #BBDEFB;
        color: #1565C0;
        margin-top: 12px;
    }

    .info-box i {
        font-size: 20px;
        margin-top: 2px;
    }

    .info-box strong {
        display: block;
        font-size: 13px;
        margin-bottom: 2px;
    }

    .info-box .muted {
        font-size: 12px;
        color: #1E5AA5;
        line-height: 1.5;
    }

    .error {
        color: #C62828;
        font-size: 12px;
        display: block;
        margin-top: 5px;
    }

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
        box-shadow: 0 6px 20px rgba(255, 112, 67, 0.4);
    }

    .btn-secondary {
        background: #E0E0E0;
        color: #666;
    }

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

        .stats-card {
            padding: 15px;
        }

        .form-card {
            padding: 15px;
        }
    }

    .btn-secondary:hover {
        background: #D0D0D0;
    }
</style>
@endpush
