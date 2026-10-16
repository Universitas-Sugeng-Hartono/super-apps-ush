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

        @if(session('success'))
            <div class="alert-success" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="bi bi-check-circle-fill" style="font-size: 18px;"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-danger" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="bi bi-exclamation-circle-fill" style="font-size: 18px;"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('kemahasiswaan.student-ambassador.update', $student->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-card">
                <h4>Informasi Utama</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap <i class="bi bi-lock-fill text-muted" style="font-size: 11px;"></i></label>
                        <input type="text" name="full_name" class="form-control readonly-input" value="{{ old('full_name', $student->nama_lengkap) }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>NIM <i class="bi bi-lock-fill text-muted" style="font-size: 11px;"></i></label>
                        <input type="text" name="nim" class="form-control readonly-input font-monospace" value="{{ old('nim', $student->nim) }}" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Periode Masuk (Bulan & Tahun) <i class="bi bi-lock-fill text-muted" style="font-size: 11px;"></i></label>
                        <input type="text" name="periode_masuk" class="form-control readonly-input" value="{{ $student->tanggal_masuk ? \Carbon\Carbon::parse($student->tanggal_masuk)->format('Y-m') : ($student->angkatan ?? '-') }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Program Studi <i class="bi bi-lock-fill text-muted" style="font-size: 11px;"></i></label>
                        <input type="text" name="program_studi" class="form-control readonly-input" value="{{ $student->program_studi ?? '-' }}" readonly>
                    </div>
                </div>

                <!-- Status Penetapan & Beasiswa -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Status Penetapan *</label>
                        <select name="status" class="form-control" required style="border-color: #0284C7; font-weight: 600;">
                            <option value="studentambassador" {{ old('status', $student->status) == 'studentambassador' ? 'selected' : '' }}>
                                Student Ambassador (Duta Kampus)
                            </option>
                            <option value="mahasiswa" {{ old('status', $student->status) == 'mahasiswa' || empty(old('status', $student->status)) ? 'selected' : '' }}>
                                Mahasiswa Reguler
                            </option>
                        </select>
                        <small class="text-muted">Ubah status apakah mahasiswa ini adalah Student Ambassador atau Mahasiswa biasa.</small>
                        @error('status')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Beasiswa (%) <i class="bi bi-lock-fill text-muted" style="font-size: 11px;"></i></label>
                        <input type="text" name="beasiswa" class="form-control readonly-input" value="{{ $student->beasiswa ?: '0%' }}" readonly>
                        <small class="text-muted">Persentase beasiswa diambil langsung dari SIAKAD.</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Semester <i class="bi bi-lock-fill text-muted" style="font-size: 11px;"></i></label>
                        <input type="text" name="semester" class="form-control readonly-input" value="{{ $student->semester ? 'Semester ' . $student->semester : '-' }}" readonly>
                        <small class="text-muted">Semester aktif mahasiswa dari SIAKAD.</small>
                    </div>

                    <div class="form-group">
                        <label>Dosen PA <i class="bi bi-lock-fill text-muted" style="font-size: 11px;"></i></label>
                        <input type="text" class="form-control readonly-input" value="{{ $student->dosenPA->name ?? ($student->academicAdvisor->name ?? ($student->id_lecturer ? 'ID: ' . $student->id_lecturer : '-')) }}" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nilai IPK <i class="bi bi-lock-fill text-muted" style="font-size: 11px;"></i></label>
                        <input type="text" name="ipk" class="form-control readonly-input" value="{{ $student->ipk !== null ? number_format((float)$student->ipk, 2) : '-' }}" readonly>
                        <small class="text-muted">IPK kumulatif terakhir dari KHS SIAKAD.</small>
                    </div>

                    <div class="form-group">
                        <label>Jenis Kelamin <i class="bi bi-lock-fill text-muted" style="font-size: 11px;"></i></label>
                        <input type="text" class="form-control readonly-input" value="{{ $student->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki' }}" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>No. Telepon / WhatsApp <i class="bi bi-lock-fill text-muted" style="font-size: 11px;"></i></label>
                        <input type="text" name="phone" class="form-control readonly-input" value="{{ $student->no_telepon ?: '-' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Email <i class="bi bi-lock-fill text-muted" style="font-size: 11px;"></i></label>
                        <input type="text" name="email" class="form-control readonly-input" value="{{ $student->email ?: '-' }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat Domisili <i class="bi bi-lock-fill text-muted" style="font-size: 11px;"></i></label>
                    <textarea name="address" class="form-control readonly-input" rows="3" readonly>{{ $student->alamat ?: '-' }}</textarea>
                </div>

                <div class="form-group">
                    <label>Catatan / Rekam Prestasi Ambassador</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan khusus, penugasan, atau rekam jejak ambassador...">{{ old('notes', $student->notes) }}</textarea>
                    <small class="text-muted">Catatan internal Bagian Kemahasiswaan terkait aktivitas atau penugasan mahasiswa.</small>
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

    .form-group label {
        font-weight: 600;
        font-size: 13px;
        color: #37474F;
        display: flex;
        align-items: center;
        gap: 6px;
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

    .readonly-input {
        background-color: #F1F5F9 !important;
        color: #64748B !important;
        border-color: #CBD5E1 !important;
        cursor: not-allowed !important;
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

    .btn-secondary:hover {
        background: #CFD8DC;
        color: #263238;
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

    .btn-primary:hover {
        opacity: 0.9;
    }
</style>
@endsection
