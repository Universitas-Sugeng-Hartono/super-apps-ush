@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3>Detail Mahasiswa - {{ $student->nama_lengkap }}</h3>
            <a href="{{ route('admin.management.students.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="form-card">
            <h4>Data Mahasiswa</h4>

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
                    <label>Angkatan</label>
                    <input type="text" class="form-control" value="{{ $student->angkatan }}" disabled>
                </div>

                <div class="form-group">
                    <label>Dosen PA</label>
                    <input type="text" class="form-control" value="{{ $student->dosenPA->name ?? '-' }}" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Program Studi</label>
                    <input type="text" class="form-control" value="{{ $student->program_studi }}" disabled>
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <input type="text" class="form-control" value="{{ $student->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" class="form-control" value="{{ $student->email ?? '-' }}" disabled>
                </div>

                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" class="form-control" value="{{ $student->no_telepon ?? '-' }}" disabled>
                </div>
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea class="form-control" rows="3" disabled>{{ $student->alamat ?? '-' }}</textarea>
            </div>

            <div class="form-group">
                <label>Catatan</label>
                <textarea class="form-control" rows="3" disabled>{{ $student->notes ?? '-' }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.management.students.edit', $student->id) }}" class="btn-secondary">
                                           Edit
                                        </a>
        </div>
    </div>
@endsection

@push('css')
<style>
    .content-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: var(--shadow);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #F5F5F5;
    }

    .card-header h3 {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    .btn-secondary {
        background: #E0E0E0;
        color: #666;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-secondary:hover {
        background: #D0D0D0;
    }

    .form-card {
        background: #FAFAFA;
        border-radius: 12px;
        padding: 25px;
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
        gap: 20px;
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
        font-family: inherit;
        background: #F5F5F5;
        color: #555;
    }

    .form-control:disabled {
        cursor: not-allowed;
        opacity: 1;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
    }

    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            justify-content: stretch;
        }

        .form-actions .btn-secondary {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush
