@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3>Edit Dosen - {{ $user->name }}</h3>
            <a href="{{ route('admin.management.lecturers.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <form action="{{ route('admin.management.lecturers.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-card">
                <h4>Data Dosen</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        @error('name')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        @error('email')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Program Studi *</label>
                        <select name="program_studi" class="form-control" required>
                            <option value="">-- Pilih Program Studi --</option>
                            @foreach($studyPrograms as $prodi)
                                <option value="{{ $prodi->name }}" {{ old('program_studi', $user->program_studi) == $prodi->name ? 'selected' : '' }}>
                                    {{ $prodi->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('program_studi')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Role *</label>
                        <select name="role" class="form-control" required>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Dosen</option>
                            <option value="superadmin" {{ old('role', $user->role) == 'superadmin' ? 'selected' : '' }}>Kaprodi</option>
                            <option value="masteradmin" {{ old('role', $user->role) == 'masteradmin' ? 'selected' : '' }}>Superuser</option>
                        </select>
                        @error('role')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                    <small>Kosongkan jika tidak ingin mengubah password</small>
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Foto</label>
                        @if($user->photo)
                            <div class="current-file">
                                <img src="{{ asset('storage/' . $user->photo) }}" alt="Current Photo" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                                <p style="font-size: 12px; color: #666;">Foto saat ini</p>
                            </div>
                        @endif
                        <input type="file" name="photo" class="form-control" accept="image/*">
                        @error('photo')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Tanda Tangan</label>
                        @if($user->ttd)
                            <div class="current-file">
                                <img src="{{ asset('storage/' . $user->ttd) }}" alt="Current TTD" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                                <p style="font-size: 12px; color: #666;">TTD saat ini</p>
                            </div>
                        @endif
                        <input type="file" name="ttd" class="form-control" accept="image/*">
                        @error('ttd')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.management.lecturers.index') }}" class="btn-secondary">Batal</a>
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

