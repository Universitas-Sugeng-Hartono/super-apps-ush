@extends('students.layouts.super-app')

@section('content')
    <div class="stats-card">
        <div class="stats-header">
            <h3>Pengajuan Judul Tugas Akhir</h3>
        </div>
        <p style="margin-top: 15px; color: #666; font-size: 14px;">
            Ajukan judul Tugas Akhir Anda untuk mendapatkan persetujuan dari admin.
        </p>
    </div>

    @if(session('error'))
        <div class="alert-danger">
            <i class="bi bi-x-circle"></i> {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('student.final-project.title.store') }}" method="POST">
        @csrf
        
        <div class="form-card">
            <h4>Judul Tugas Akhir</h4>
            <div class="form-group">
                <label>Judul Tugas Akhir (Bahasa Indonesia) *</label>
                <textarea name="title" class="form-control" rows="3" placeholder="Masukkan judul dalam Bahasa Indonesia..." required>{{ old('title', $finalProject->title) }}</textarea>
                <small>Minimal 10 karakter, maksimal 500 karakter</small>
                @error('title')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Judul Tugas Akhir (Bahasa Inggris) *</label>
                <textarea name="title_en" class="form-control" rows="3" placeholder="Masukkan judul dalam Bahasa Inggris..." required>{{ old('title_en', $finalProject->title_en) }}</textarea>
                <small>Final project title in English version</small>
                @error('title_en')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('student.final-project.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Ajukan Judul</button>
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
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-orange);
    }

    .form-group small {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: #999;
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

    .btn-secondary:hover {
        background: #D0D0D0;
    }
</style>
@endpush

