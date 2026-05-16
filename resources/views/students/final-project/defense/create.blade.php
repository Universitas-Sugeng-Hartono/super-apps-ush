@extends('students.layouts.super-app')

@section('content')
    <div class="stats-card">
        <div class="stats-header">
            <h3>Pendaftaran Sidang Tugas Akhir</h3>
        </div>
        <p style="margin-top: 10px; color: #666; font-size: 14px;">
            Isi form berikut untuk mendaftar sidang Tugas Akhir Anda.
        </p>
    </div>

    @if(session('error'))
        <div class="alert-danger">
            <i class="bi bi-x-circle"></i> {{ session('error') }}
        </div>
    @endif

    <form id="registrationForm" action="{{ route('student.final-project.defense.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-card">
            <h4>Informasi Mahasiswa</h4>
            <div class="form-group">
                <label for="namaLengkap">Nama Lengkap</label>
                <input id="namaLengkap" type="text" class="form-control" value="{{ auth()->guard('student')->user()->nama_lengkap }}" disabled>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="nim">NIM</label>
                    <input id="nim" type="text" class="form-control" value="{{ auth()->guard('student')->user()->nim }}" disabled>
                </div>
                <div class="form-group">
                    <label for="semester">Semester</label>
                    <input id="semester" type="text" class="form-control" value="{{ auth()->guard('student')->user()->getCurrentSemester() }}" disabled>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h4>Data Pribadi</h4>
            <p class="hint">
                Lengkapi biodata berikut sesuai data resmi. Data ini akan disimpan ke profil Anda.
            </p>
            <div class="form-row">
                <div class="form-group">
                    <label for="nik">NIK (sesuai KTP - 16 digit) *</label>
                    <input id="nik" type="text" name="nik" class="form-control"
                        value="{{ old('nik', auth()->guard('student')->user()->nik ?? '') }}"
                        inputmode="numeric" maxlength="16" placeholder="Contoh: 3574xxxxxxxxxxxx" required>
                    @error('nik')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="nisn">NISN *</label>
                    <input id="nisn" type="text" name="nisn" class="form-control"
                        value="{{ old('nisn', auth()->guard('student')->user()->nisn ?? '') }}"
                        inputmode="numeric" maxlength="20" placeholder="Masukkan NISN" required>
                    @error('nisn')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tempatLahir">Tempat Lahir *</label>
                    <input id="tempatLahir" type="text" name="tempat_lahir" class="form-control"
                        value="{{ old('tempat_lahir', auth()->guard('student')->user()->tempat_lahir ?? '') }}"
                        placeholder="Contoh: Surabaya" required>
                    @error('tempat_lahir')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="tanggalLahir">Tanggal Lahir *</label>
                    <input id="tanggalLahir" type="date" name="tanggal_lahir" class="form-control"
                        value="{{ old('tanggal_lahir', optional(auth()->guard('student')->user()->tanggal_lahir)->format('Y-m-d')) }}"
                        required>
                    @error('tanggal_lahir')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="namaIbu">Nama Ibu Kandung *</label>
                    <input id="namaIbu" type="text" name="nama_ibu_kandung" class="form-control"
                        value="{{ old('nama_ibu_kandung', auth()->guard('student')->user()->nama_ibu_kandung ?? '') }}"
                        placeholder="Masukkan nama ibu kandung" required>
                    @error('nama_ibu_kandung')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="noTelepon">No. Telp Aktif *</label>
                    <input id="noTelepon" type="text" name="no_telepon" class="form-control"
                        value="{{ old('no_telepon', auth()->guard('student')->user()->no_telepon ?? '') }}"
                        inputmode="tel" placeholder="Contoh: 08xxxxxxxxxx" required>
                    @error('no_telepon')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-card">
            <h4>Informasi Tugas Akhir</h4>
            <div class="form-group">
                <label for="judulTa">Judul Tugas Akhir (Bahasa Indonesia)</label>
                <input id="judulTa" type="text" class="form-control" value="{{ $finalProject->title }}" disabled>
            </div>
            @if($finalProject->title_en)
            <div class="form-group">
                <label for="judulTaEn">Judul Tugas Akhir (Bahasa Inggris)</label>
                <input id="judulTaEn" type="text" class="form-control" style="font-style: italic;" value="{{ $finalProject->title_en }}" disabled>
            </div>
            @endif
            
            <div class="form-row">
                <div class="form-group">
                    <label for="pembimbing1">Pembimbing 1</label>
                    <input id="pembimbing1" type="text" class="form-control" value="{{ $finalProject->supervisor1->name ?? 'Belum ditentukan' }}" disabled>
                </div>
                <div class="form-group">
                    <label for="pembimbing2">Pembimbing 2</label>
                    <input id="pembimbing2" type="text" class="form-control" value="{{ $finalProject->supervisor2->name ?? '-' }}" disabled>
                </div>
            </div>
            <div class="info-box">
                <i class="bi bi-info-circle"></i>
                <div>
                    <strong>Jadwal Sidang</strong>
                    <div class="muted">
                        Jadwal sidang akan <b>ditentukan oleh Kaprodi</b> setelah pendaftaran disetujui, dan akan muncul di kalender landing page.
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h4>Upload Dokumen</h4>
            <p style="margin-top: -10px; font-size: 13px; color: #666; margin-bottom: 20px;">
                Format file: PDF/JPG/PNG. Maksimal ukuran file: 2MB per dokumen.
            </p>

            <div class="form-group">
                <label for="ukt_semester_8_file">1. Bebas biaya pendidikan (UKT Semester 8) *</label>
                <input id="ukt_semester_8_file" type="file" name="ukt_semester_8_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('ukt_semester_8_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="bebas_perpustakaan_file">2. Bebas peminjaman buku perpustakaan *</label>
                <input id="bebas_perpustakaan_file" type="file" name="bebas_perpustakaan_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('bebas_perpustakaan_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="persetujuan_dospem_file">3. Formulir persetujuan Dosen Pembimbing TA *</label>
                <input id="persetujuan_dospem_file" type="file" name="persetujuan_dospem_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('persetujuan_dospem_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="lembar_konsultasi_file">4. Lembar Konsultasi TA (min. 8x konsultasi) *</label>
                <input id="lembar_konsultasi_file" type="file" name="lembar_konsultasi_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('lembar_konsultasi_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="transkrip_nilai_file">5. Transkrip Nilai Sementara USH (sudah disahkan) *</label>
                <input id="transkrip_nilai_file" type="file" name="transkrip_nilai_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('transkrip_nilai_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="turnitin_file">6. Hasil Turnitin/plagiarism (maksimal 25%) *</label>
                <input id="turnitin_file" type="file" name="turnitin_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('turnitin_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="sertifikat_pkkmb_file">7. Sertifikat PKKMB *</label>
                <input id="sertifikat_pkkmb_file" type="file" name="sertifikat_pkkmb_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('sertifikat_pkkmb_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="finalDraft">8. Dokumen/Makalah TA *</label>
                <input id="finalDraft" type="file" name="final_draft_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('final_draft_file')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="dokumen_pendukung_prodi_file">9. Dokumen pendukung Prodi (jika ada)</label>
                <input id="dokumen_pendukung_prodi_file" type="file" name="dokumen_pendukung_prodi_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                @error('dokumen_pendukung_prodi_file')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('student.final-project.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary" id="submitBtn">Submit Pendaftaran Sidang</button>
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

    .hint {
        margin-top: -10px;
        margin-bottom: 18px;
        font-size: 13px;
        color: #666;
        font-weight: 500;
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

        .stats-card, .form-card {
            padding: 15px;
        }
    }

    .btn-secondary:hover {
        background: #D0D0D0;
    }
</style>
@endpush
