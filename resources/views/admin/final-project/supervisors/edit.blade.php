@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3>Edit Pembimbing Tugas Akhir</h3>
            <a href="{{ route('admin.final-project.supervisors.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="form-card">
            <div class="student-info">
                <h4>{{ $finalProject->student->nama_lengkap }}</h4>
                <p><strong>NIM:</strong> {{ $finalProject->student->nim }}</p>
                <p><strong>Judul TA:</strong> {{ $finalProject->title }}</p>
            </div>

            <form action="{{ route('admin.final-project.supervisors.update', $finalProject->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group">
                        <label>Pembimbing 1 *</label>
                        <select name="supervisor_1_id" class="form-control" required>
                            <option value="">Pilih Pembimbing 1</option>
                            @foreach($lecturers as $lecturer)
                                <option value="{{ $lecturer->id }}" 
                                    {{ $finalProject->supervisor_1_id == $lecturer->id ? 'selected' : '' }}>
                                    {{ $lecturer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supervisor_1_id')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Pembimbing 2 (Opsional)</label>
                        <select name="supervisor_2_id" class="form-control">
                            <option value="">Tidak ada Pembimbing 2</option>
                            @foreach($lecturers as $lecturer)
                                <option value="{{ $lecturer->id }}" 
                                    {{ $finalProject->supervisor_2_id == $lecturer->id ? 'selected' : '' }}>
                                    {{ $lecturer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supervisor_2_id')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.final-project.supervisors.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Simpan</button>
                </div>
            </form>
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
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-top: 20px;
    }

    .student-info {
        background: #F5F5F5;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 25px;
    }

    .student-info h4 {
        margin: 0 0 10px;
        color: var(--primary-orange);
    }

    .student-info p {
        margin: 5px 0;
        color: #666;
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
        transition: border-color 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-orange);
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

