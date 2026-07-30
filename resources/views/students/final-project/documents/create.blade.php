@extends('students.layouts.super-app')

@section('content')
    <div class="stats-card">
        <div class="stats-header">
            <h3>Upload Dokumen</h3>
        </div>
    </div>

    <form action="{{ route('student.final-project.documents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-card">
            <h4>Informasi Dokumen</h4>
            
            <div class="form-group">
                <label>Tipe Dokumen *</label>
                <select name="document_type" class="form-control" required>
                    <option value="">-- Pilih Tipe --</option>
                    <option value="proposal">Proposal</option>
                    <option value="chapter">Bab/Chapter</option>
                    <option value="full_draft">Draft Lengkap</option>
                    <option value="final">Final/Revisi Akhir</option>
                    <option value="presentation">Presentasi/Slide</option>
                    <option value="other">Lainnya</option>
                </select>
                @error('document_type')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Judul Dokumen *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Contoh: Bab 1 - Pendahuluan">
                <small>Jika dokumen dengan tipe dan judul sama sudah ada, versi akan otomatis bertambah</small>
                @error('title')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>File Dokumen *</label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx" required>
                <small>Format: PDF, DOC, DOCX, PPT, PPTX. Max: 10MB</small>
                @error('file')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('student.final-project.documents.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Upload Dokumen</button>
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

    select.form-control {
        cursor: pointer;
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
