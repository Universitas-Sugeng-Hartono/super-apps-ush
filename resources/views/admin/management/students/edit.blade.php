@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3>Edit Mahasiswa - {{ $student->nama_lengkap }}</h3>
            <a href="{{ session('management_students_url', route('admin.management.students.index')) }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <form action="{{ route('admin.management.students.update', $student->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-card">
                <h4>Data Mahasiswa</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap', $student->nama_lengkap) }}" required>
                        @error('nama_lengkap')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>NIM *</label>
                        <input type="text" name="nim" class="form-control" value="{{ old('nim', $student->nim) }}" required>
                        @error('nim')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Periode Masuk (Bulan & Tahun) *</label>
                        <input type="month" name="periode_masuk" class="form-control" value="{{ old('periode_masuk', $student->tanggal_masuk ? $student->tanggal_masuk->format('Y-m') : '') }}" required>
                        @error('periode_masuk')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Dosen PA *</label>
                        <select name="id_lecturer" class="form-control" required>
                            <option value="">Pilih Dosen PA</option>
                            @foreach($lecturers as $lecturer)
                                <option value="{{ $lecturer->id }}" {{ old('id_lecturer', $student->id_lecturer) == $lecturer->id ? 'selected' : '' }}>
                                    {{ $lecturer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_lecturer')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Program Studi *</label>
                        <select name="program_studi" class="form-control" required>
                            <option value="">-- Pilih Program Studi --</option>
                            @foreach($studyPrograms as $prodi)
                                <option value="{{ $prodi->name }}" {{ old('program_studi', $student->program_studi) == $prodi->name ? 'selected' : '' }}>
                                    {{ $prodi->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('program_studi')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Jenis Kelamin *</label>
                        <select name="jenis_kelamin" class="form-control" required>
                            <option value="L" {{ old('jenis_kelamin', $student->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $student->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}">
                        @error('email')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" name="no_telepon" class="form-control" value="{{ old('no_telepon', $student->no_telepon) }}">
                        @error('no_telepon')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat *</label>
                    <textarea name="alamat" class="form-control" rows="3" required>{{ old('alamat', $student->alamat) }}</textarea>
                    @error('alamat')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $student->notes) }}</textarea>
                    @error('notes')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ session('management_students_url', route('admin.management.students.index')) }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
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
        transition: border-color 0.3s;
        font-family: inherit;
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

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        padding: 12px 30px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 112, 67, 0.4);
    }
</style>
@endpush

