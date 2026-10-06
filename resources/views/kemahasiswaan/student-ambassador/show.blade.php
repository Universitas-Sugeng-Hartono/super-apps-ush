@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <div>
                <h3>Detail Mahasiswa / Ambassador - {{ $student->nama_lengkap }}</h3>
                <p class="text-muted mb-0" style="font-size: 13px;">Informasi profil lengkap dan status penetapan Student Ambassador.</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('kemahasiswaan.student-ambassador.edit', $student->id) }}" class="btn-primary">
                    <i class="bi bi-pencil"></i> Edit Data
                </a>
                <a href="{{ route('kemahasiswaan.student-ambassador.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="form-card">
            <h4>Informasi Utama & Status</h4>

            <div class="form-row">
                <div class="form-group highlight-box">
                    <label>Status Penetapan</label>
                    <div>
                        @if($student->status === 'studentambassador')
                            <span class="status-badge-ambassador">
                                <i class="bi bi-star-fill"></i> Student Ambassador (Duta Kampus)
                            </span>
                        @else
                            <span class="status-badge-mahasiswa">
                                <i class="bi bi-person"></i> Mahasiswa Reguler
                            </span>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label>Program Studi</label>
                    <input type="text" class="form-control" value="{{ $student->program_studi }}" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Beasiswa (%)</label>
                    <input type="text" class="form-control" value="{{ $student->beasiswa ?: '0%' }}" disabled>
                </div>

                <div class="form-group">
                    <label>Nilai IPK</label>
                    <input type="text" class="form-control" value="{{ $student->ipk ? number_format((float)$student->ipk, 2) : 'Belum diisi / 0.00' }}" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" class="form-control" value="{{ $student->nama_lengkap }}" disabled>
                </div>

                <div class="form-group">
                    <label>NIM</label>
                    <input type="text" class="form-control" value="{{ $student->nim }}" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Periode Masuk</label>
                    <input type="text" class="form-control" value="{{ $student->tanggal_masuk ? \Carbon\Carbon::parse($student->tanggal_masuk)->translatedFormat('F Y') : ($student->angkatan ?? '-') }}" disabled>
                </div>

                <div class="form-group">
                    <label>Dosen PA</label>
                    <input type="text" class="form-control" value="{{ $student->dosenPA->name ?? '-' }}" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <input type="text" class="form-control" value="{{ $student->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}" disabled>
                </div>

                <div class="form-group">
                    <label>No. Telepon / WhatsApp</label>
                    <input type="text" class="form-control" value="{{ $student->no_telepon ?? '-' }}" disabled>
                </div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="text" class="form-control" value="{{ $student->email ?? '-' }}" disabled>
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea class="form-control" rows="2" disabled>{{ $student->alamat ?? '-' }}</textarea>
            </div>

            <div class="form-group">
                <label>Catatan / Rekam Prestasi Ambassador</label>
                <textarea class="form-control" rows="3" disabled>{{ $student->notes ?? 'Belum ada catatan.' }}</textarea>
            </div>
        </div>
    </div>

<style>
    .content-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #ECEFF1;
    }

    .card-header h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #2c3e50;
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .form-card {
        background: #F8F9FA;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        border: 1px solid #ECEFF1;
    }

    .form-card h4 {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 16px;
        font-weight: 600;
        color: #37474F;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 16px;
    }

    .form-group.highlight-box {
        background: #FFFDE7;
        padding: 12px 16px;
        border-radius: 10px;
        border: 1.5px solid #FFF59D;
    }

    .form-group label {
        font-weight: 600;
        font-size: 13px;
        color: #37474F;
    }

    .form-control {
        padding: 11px 14px;
        border: 1.5px solid #CFD8DC;
        border-radius: 10px;
        font-size: 14px;
        background: white;
    }

    .form-control:disabled {
        background: #ECEFF1;
        color: #37474F;
        cursor: not-allowed;
    }

    .status-badge-ambassador {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: linear-gradient(135deg, #FFF8E1, #FFECB3);
        color: #B78103;
        border: 1px solid #FFE082;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
    }

    .status-badge-mahasiswa {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: #ECEFF1;
        color: #455A64;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .btn-secondary {
        padding: 10px 18px;
        background: #ECEFF1;
        color: #455A64;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-primary {
        padding: 10px 18px;
        background: var(--primary-orange);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>
@endsection
