@extends('students.layouts.super-app')

@section('content')
    @if(session('error'))
        <div class="alert-card alert-error">
            <i class="bi bi-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif



    <div class="page-card">
        <div>
            <span class="page-eyebrow">Form Draft</span>
            <h3>Data Pemegang SKPI</h3>
            <p>Data di bawah ini otomatis mengambil data yang sudah ada di sistem. Jika ada yang belum tersedia,Jika ada Data  yang Kurang Lengkap Hubungi Dosen Pembimbing Anda.</p>
        </div>
        <div class="page-badge-wrap">
            <span class="page-badge">{{ $holderMeta['filled_count'] }}/{{ $holderMeta['total_count'] }} field terisi</span>
            <span class="status-badge {{ $registrationStatus['badge_class'] }}">{{ $registrationStatus['label'] }}</span>
        </div>
    </div>

    @if($skpiRegistration && $skpiRegistration->approval_notes)
        <div class="alert-card alert-note">
            <i class="bi bi-chat-left-text"></i>
            <div>
                <strong>Catatan Review Terakhir</strong>
                <p>{{ $skpiRegistration->approval_notes }}</p>
            </div>
        </div>
    @endif

    @if($holderMeta['filled_count'] < $holderMeta['total_count'])
        <div class="alert-card alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <div>
                <strong>Data Belum Lengkap!</strong>
                <p>Terdapat {{ $holderMeta['total_count'] - $holderMeta['filled_count'] }} field data yang masih kosong. Mohon hubungi Dosen Pembimbing Anda jika ada data yang kurang lengkap.</p>
            </div>
        </div>
    @endif

    {{-- <div class="helper-grid">
        <div class="helper-card">
            <div class="helper-icon">
                <i class="bi bi-database-check"></i>
            </div>
            <div>
                <h5>Auto-fill dari Sistem</h5>
                <p>Nama lengkap, NIM, tahun masuk, dan data lain yang sudah tersedia langsung dimunculkan agar Anda tidak perlu mengisi ulang dari nol.</p>
            </div>
        </div>

        <div class="helper-card">
            <div class="helper-icon blue">
                <i class="bi bi-arrow-repeat"></i>
            </div>
            <div>
                <h5>Status Pengajuan</h5>
                <p>{{ $registrationStatus['description'] }}</p>
            </div>
        </div>
    </div> --}}

    <div class="form-card">
        <div class="card-head">
            <div>
                <h4>Form Identitas Pemegang SKPI</h4>
                <p>Silakan tinjau dan lengkapi semua field berikut.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('student.skpi.daftar.store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap (sesuai nama ijasah)</label>
                    <input id="nama_lengkap" type="text" name="nama_lengkap" class="form-control" value="{{ $holderData['nama_lengkap'] }}" placeholder="Masukkan nama lengkap" @disabled(!$canEditRegistration)>
                    @error('nama_lengkap')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="nim">Nomor Induk Mahasiswa</label>
                    <input id="nim" type="text" name="nim" class="form-control" value="{{ $holderData['nim'] }}" placeholder="Masukkan NIM" @disabled(!$canEditRegistration)>
                    @error('nim')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="tempat_lahir">Tempat Lahir</label>
                    <input id="tempat_lahir" type="text" name="tempat_lahir" class="form-control" value="{{ $holderData['tempat_lahir'] }}" placeholder="Masukkan tempat lahir" @disabled(!$canEditRegistration)>
                    @error('tempat_lahir')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input id="tanggal_lahir" type="date" name="tanggal_lahir" class="form-control" value="{{ $holderData['tanggal_lahir'] }}" @disabled(!$canEditRegistration)>
                    @error('tanggal_lahir')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="angkatan">Tahun Masuk</label>
                    <input id="angkatan" type="number" name="angkatan" class="form-control" value="{{ $holderData['angkatan'] }}" min="1900" max="2100" placeholder="Contoh: 2022" @disabled(!$canEditRegistration)>
                    @error('angkatan')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="gelar">Gelar</label>
                    <input id="gelar" type="text" name="gelar" class="form-control readonly-field" value="{{ $holderData['gelar'] }}" placeholder="Gelar dari program studi..." readonly>
                    <small class="text-muted"><i class="bi bi-info-circle"></i> Gelar diatur oleh Masteradmin sesuai Program Studi Anda.</small>
                    @error('gelar')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                {{-- Nomor Ijazah diisi oleh Masteradmin, bukan mahasiswa --}}
            </div>

            {{-- <div class="quick-links">
                <a href="{{ route('student.personal.editDataIndex') }}" class="quick-link">
                    <i class="bi bi-person-lines-fill"></i>
                    Lengkapi Profil Mahasiswa
                </a>
                <a href="{{ route('student.final-project.index') }}" class="quick-link">
                    <i class="bi bi-journal-check"></i>
                    Cek Data Tugas Akhir
                </a>
            </div> --}}

            <div class="form-actions">
                <a href="{{ route('student.skpi.daftar.index') }}" class="btn btn-soft">Kembali</a>
                @if($canEditRegistration)
                    <button type="submit" class="btn btn-primary-soft">{{ $skpiRegistration ? 'Update Data Identitas' : 'Simpan Data Identitas' }}</button>
                @else
                    <a href="{{ route('student.skpi.daftar.show') }}" class="btn btn-primary-soft">Lihat Pengajuan</a>
                @endif
            </div>
        </form>
    </div>
@endsection

@push('css')
<style>
    .page-card,
    .helper-card,
    .form-card,
    .alert-card {
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
    }

    .page-card,
    .helper-card {
        display: flex;
        gap: 16px;
    }

    .page-card {
        padding: 24px;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        background: linear-gradient(135deg, #FFF8EE, #FFFFFF);
    }

    .page-eyebrow {
        display: inline-flex;
        padding: 5px 12px;
        border-radius: 999px;
        background: #FFF3E0;
        color: #E65100;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .page-card h3,
    .card-head h4,
    .helper-card h5 {
        margin: 0 0 8px;
        color: var(--text-dark);
        font-weight: 700;
    }

    .page-card p,
    .card-head p,
    .helper-card p,
    .form-group small {
        margin: 0;
        color: #666;
        line-height: 1.7;
        font-size: 14px;
    }

    .page-badge {
        display: inline-flex;
        padding: 7px 14px;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .page-badge-wrap {
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: flex-end;
    }

    .status-badge {
        display: inline-flex;
        padding: 7px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-badge.active {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.warning {
        background: #FFF3E0;
        color: #E65100;
    }

    .status-badge.info {
        background: #E3F2FD;
        color: #1565C0;
    }

    .status-badge.danger {
        background: #FFEBEE;
        color: #C62828;
    }

    .status-badge.muted {
        background: #F5F5F5;
        color: #757575;
    }

    .alert-card {
        padding: 16px 18px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .alert-card i {
        font-size: 18px;
        margin-top: 2px;
    }

    .alert-error {
        background: #FFEBEE;
        color: #C62828;
    }

    .alert-note {
        background: #FFF8E1;
        color: #795548;
    }

    .alert-warning {
        background: #FFF3E0;
        color: #E65100;
    }

    .alert-note strong,
    .alert-warning strong {
        display: block;
        margin-bottom: 4px;
    }

    .alert-note p,
    .alert-warning p {
        margin: 0;
        color: inherit;
    }

    .helper-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .helper-card {
        padding: 20px;
        align-items: flex-start;
    }

    .helper-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, #FF9800, #FF7043);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .helper-icon.blue {
        background: linear-gradient(135deg, #2196F3, #64B5F6);
    }

    .form-card {
        padding: 24px;
    }

    .card-head {
        margin-bottom: 22px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .form-group {
        display: grid;
        gap: 8px;
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
    }

    .form-control {
        border: 2px solid #E9E9E9;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 14px;
        background: #FAFAFA;
        transition: var(--transition-normal);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-orange);
        background: white;
        box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.12);
    }

    .form-control:disabled {
        background: #F5F5F5;
        color: #777;
        cursor: not-allowed;
    }

    .quick-links {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 22px;
    }

    .quick-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 14px;
        background: #FFF3E0;
        color: #E65100;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    .form-actions {
        margin-top: 24px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .btn {
        border: none;
        border-radius: 14px;
        padding: 11px 18px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
    }

    .btn-soft {
        background: #F5F5F5;
        color: var(--text-dark);
    }

    .btn-primary-soft {
        background: linear-gradient(135deg, #FF9800, #FF7043);
        color: white;
    }

    .text-danger {
        color: #C62828;
    }

    @media (max-width: 768px) {
        .page-card,
        .helper-card,
        .form-actions {
            flex-direction: column;
        }

        .helper-grid,
        .form-grid {
            grid-template-columns: 1fr;
        }

        .btn {
            width: 100%;
            text-align: center;
        }

        .page-badge-wrap {
            align-items: flex-start;
        }
    }
</style>
@endpush
