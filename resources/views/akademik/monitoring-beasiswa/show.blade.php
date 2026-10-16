@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <div>
                <h3>Detail Mahasiswa / Ambassador - {{ $student->nama_lengkap }}</h3>
                <p class="text-muted mb-0" style="font-size: 13px;">Informasi profil lengkap, capaian akademik, dan status beasiswa mahasiswa (Read-only Biro Akademik).</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('akademik.monitoring-beasiswa.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Monitoring
                </a>
            </div>
        </div>

        <div class="form-card">
            <h4>Informasi Utama</h4>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" class="form-control readonly-input" value="{{ $student->nama_lengkap ?? '-' }}" readonly>
                </div>

                <div class="form-group">
                    <label>NIM</label>
                    <input type="text" class="form-control readonly-input font-monospace" value="{{ $student->nim ?? '-' }}" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Periode Masuk (Bulan & Tahun)</label>
                    <input type="text" class="form-control readonly-input" value="{{ $student->tanggal_masuk ? \Carbon\Carbon::parse($student->tanggal_masuk)->translatedFormat('F Y') : ($student->angkatan ? 'Angkatan ' . $student->angkatan : '-') }}" readonly>
                </div>

                <div class="form-group">
                    <label>Program Studi</label>
                    <input type="text" class="form-control readonly-input" value="{{ $student->program_studi ?? '-' }}" readonly>
                </div>
            </div>

            <!-- Status Penetapan & Beasiswa -->
            <div class="form-row">
                <div class="form-group">
                    <label>Status Penetapan</label>
                    <div class="readonly-input status-display-wrap">
                        @if($student->status === 'studentambassador')
                            <span class="status-badge-ambassador">
                                <i class="bi bi-star-fill"></i> Student Ambassador (Duta Kampus)
                            </span>
                        @else
                            <span class="status-badge-mahasiswa">
                                <i class="bi bi-person-fill"></i> Mahasiswa Reguler
                            </span>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label>Beasiswa (%)</label>
                    <input type="text" class="form-control readonly-input font-weight-bold" value="{{ $student->beasiswa ?: '0%' }}" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Semester</label>
                    <input type="text" class="form-control readonly-input" value="{{ $student->semester ? 'Semester ' . $student->semester : '-' }}" readonly>
                </div>

                <div class="form-group">
                    <label>Dosen PA</label>
                    <input type="text" class="form-control readonly-input" value="{{ $student->dosenPA->name ?? ($student->id_lecturer ? 'ID: ' . $student->id_lecturer : '-') }}" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nilai IPK</label>
                    <input type="text" class="form-control readonly-input" value="{{ filled($student->ipk) ? number_format((float)$student->ipk, 2) : 'Belum ada IPK' }}" readonly>
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <input type="text" class="form-control readonly-input" value="{{ $student->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki' }}" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>No. Telepon / WhatsApp</label>
                    <input type="text" class="form-control readonly-input" value="{{ $student->no_telepon ?: '-' }}" readonly>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="text" class="form-control readonly-input" value="{{ $student->email ?: '-' }}" readonly>
                </div>
            </div>

            <div class="form-group">
                <label>Alamat Domisili</label>
                <textarea class="form-control readonly-input" rows="3" readonly>{{ $student->alamat ?: '-' }}</textarea>
            </div>

            <div class="form-group">
                <label>Catatan / Rekam Prestasi Ambassador</label>
                <textarea class="form-control readonly-input" rows="3" readonly>{{ $student->notes ?: 'Belum ada catatan.' }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('akademik.monitoring-beasiswa.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Monitoring
            </a>
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
    .readonly-input {
        background-color: #F1F5F9 !important;
        color: #334155 !important;
        border-color: #CBD5E1 !important;
        cursor: default !important;
    }
    .status-display-wrap {
        display: flex;
        align-items: center;
        min-height: 44px;
        padding: 6px 14px !important;
    }
    .status-badge-ambassador {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: linear-gradient(135deg, #FFF8E1, #FFECB3);
        color: #B78103;
        border: 1px solid #FFE082;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 700;
    }
    .status-badge-mahasiswa {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: #E2E8F0;
        color: #475569;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 600;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
    }
    .btn-secondary {
        padding: 10px 18px;
        background: #ECEFF1;
        color: #455A64;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-secondary:hover {
        background: #CFD8DC;
        color: #263238;
    }
</style>
@endsection
