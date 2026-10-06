@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <div>
                <h3>Edit Mahasiswa / Ambassador - {{ $student->nama_lengkap }}</h3>
                <p class="text-muted mb-0" style="font-size: 13px;">Perbarui data atau ubah status penetapan Student Ambassador.</p>
            </div>
            <a href="{{ route('kemahasiswaan.student-ambassador.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <form action="{{ route('kemahasiswaan.student-ambassador.update', $student->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-card">
                <h4>Informasi Utama</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $student->nama_lengkap) }}" required>
                        @error('full_name')<span class="error">{{ $message }}</span>@enderror
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
                        <input type="month" name="periode_masuk" class="form-control" value="{{ old('periode_masuk', $student->tanggal_masuk ? \Carbon\Carbon::parse($student->tanggal_masuk)->format('Y-m') : '') }}" required>
                        @error('periode_masuk')<span class="error">{{ $message }}</span>@enderror
                    </div>

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
                </div>

                <!-- Status Penetapan & Beasiswa -->
                <div class="form-row">
                    <div class="form-group highlight-box">
                        <label><i class="bi bi-award-fill text-warning me-1"></i> Status Penetapan *</label>
                        <select name="status" class="form-control status-select" required>
                            <option value="studentambassador" {{ old('status', $student->status) == 'studentambassador' ? 'selected' : '' }}>
                                ⭐ Student Ambassador (Duta Kampus)
                            </option>
                            <option value="mahasiswa" {{ old('status', $student->status) == 'mahasiswa' || empty(old('status', $student->status)) ? 'selected' : '' }}>
                                👤 Mahasiswa Reguler
                            </option>
                        </select>
                        <small class="text-muted">Ubah status apakah mahasiswa ini adalah Student Ambassador atau Mahasiswa biasa.</small>
                        @error('status')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label><i class="bi bi-mortarboard-fill text-success me-1"></i> Beasiswa (%)</label>
                        <input type="text" name="beasiswa" list="beasiswaList" class="form-control" value="{{ old('beasiswa', $student->beasiswa) }}" placeholder="Pilih atau ketik persentase, misal: 100%, 75%, 50%, 25%, 0%">
                        <datalist id="beasiswaList">
                            <option value="100%">100% (Beasiswa Penuh - Sangat Baik)</option>
                            <option value="75%">75% (Baik Sekali)</option>
                            <option value="50%">50% (Baik)</option>
                            <option value="25%">25% (Cukup Baik)</option>
                            <option value="0%">0% (Reguler / Non-Beasiswa)</option>
                        </datalist>
                        <small class="text-muted">Pilih atau ketik persentase beasiswa (misal: 100%, 75%, 50%, 25%, atau 0% untuk reguler).</small>
                        @error('beasiswa')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Dosen PA</label>
                        <select name="id_lecturer" class="form-control">
                            <option value="">Pilih Dosen PA (Opsional)</option>
                            @foreach($lecturers as $lecturer)
                                <option value="{{ $lecturer->id }}" {{ old('id_lecturer', $student->id_lecturer) == $lecturer->id ? 'selected' : '' }}>
                                    {{ $lecturer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_lecturer')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select name="gender" class="form-control">
                            <option value="L" {{ old('gender', $student->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('gender', $student->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->no_telepon) }}">
                        @error('phone')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}">
                        @error('email')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat Domisili</label>
                    <textarea name="address" class="form-control" rows="3">{{ old('address', $student->alamat) }}</textarea>
                    @error('address')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Catatan / Rekam Prestasi Ambassador</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $student->notes) }}</textarea>
                    @error('notes')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('kemahasiswaan.student-ambassador.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                </button>
            </div>
        </form>
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
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-orange);
    }

    .status-select {
        font-weight: 600;
        color: #2c3e50;
    }

    .error {
        color: #D32F2F;
        font-size: 12px;
        font-weight: 500;
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
    }

    .btn-primary {
        padding: 10px 22px;
        background: var(--primary-orange);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>
@endsection
