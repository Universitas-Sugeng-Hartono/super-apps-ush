@extends('students.layouts.super-app')

@section('content')
    <div class="stats-card">
        <div class="stats-header">
            <h3>Tambah Log Bimbingan</h3>
        </div>
    </div>

    <form action="{{ route('student.final-project.guidance.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-card">
            <h4>Informasi Bimbingan</h4>
            
            <div class="form-group">
                <label>Pilih Pembimbing *</label>
                <select name="supervisor_id" class="form-control" required>
                    <option value="">-- Pilih Pembimbing --</option>
                    @if($finalProject->supervisor1)
                        <option value="{{ $finalProject->supervisor1->id }}">
                            Pembimbing 1 - {{ $finalProject->supervisor1->name }}
                        </option>
                    @endif
                    @if($finalProject->supervisor2)
                        <option value="{{ $finalProject->supervisor2->id }}">
                            Pembimbing 2 - {{ $finalProject->supervisor2->name }}
                        </option>
                    @endif
                </select>
                @error('supervisor_id')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Tanggal Bimbingan *</label>
                <input type="date" name="guidance_date" class="form-control" value="{{ old('guidance_date', date('Y-m-d')) }}" required>
                @error('guidance_date')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Materi yang Dibimbing *</label>
                <textarea name="materials_discussed" class="form-control" rows="4" required placeholder="Contoh: Membahas Bab 3 tentang metodologi penelitian, diskusi tentang teknik pengumpulan data...">{{ old('materials_discussed') }}</textarea>
                @error('materials_discussed')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Catatan Mahasiswa (Opsional)</label>
                <textarea name="student_notes" class="form-control" rows="3" placeholder="Tambahan catatan atau poin penting dari bimbingan...">{{ old('student_notes') }}</textarea>
                @error('student_notes')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>File Lampiran (Opsional)</label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
                <small>Format: PDF, DOC, DOCX. Max: 5MB</small>
                @error('file')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('student.final-project.guidance.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Simpan Log Bimbingan</button>
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

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
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
