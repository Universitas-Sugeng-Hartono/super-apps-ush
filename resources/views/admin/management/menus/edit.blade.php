@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3>Edit Menu - {{ $menuItem->name }}</h3>
            <a href="{{ route('admin.management.menus.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <form action="{{ route('admin.management.menus.update', $menuItem->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-card">
                <h4>Informasi Menu</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Menu *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $menuItem->name) }}" required>
                        @error('name')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Icon (Bootstrap Icons)</label>
                        <input type="text" name="icon" class="form-control" value="{{ old('icon', $menuItem->icon) }}" placeholder="Contoh: bi bi-people-fill">
                        <small>Gunakan class Bootstrap Icons</small>
                        @error('icon')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $menuItem->description) }}</textarea>
                    @error('description')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>URL</label>
                        <input type="text" name="url" class="form-control" value="{{ old('url', $menuItem->url) }}">
                        <small>Atau gunakan Route Name di bawah</small>
                        @error('url')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Route Name</label>
                        <input type="text" name="route_name" class="form-control" value="{{ old('route_name', $menuItem->route_name) }}">
                        <small>Prioritas lebih tinggi dari URL</small>
                        @error('route_name')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Target</label>
                        <select name="target" class="form-control">
                            <option value="_self" {{ old('target', $menuItem->target) == '_self' ? 'selected' : '' }}>Tab Saat Ini</option>
                            <option value="_blank" {{ old('target', $menuItem->target) == '_blank' ? 'selected' : '' }}>Tab Baru</option>
                        </select>
                        @error('target')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Urutan (Order)</label>
                        <input type="number" name="order" class="form-control" value="{{ old('order', $menuItem->order) }}" min="0">
                        <small>Semakin kecil angka, semakin atas posisinya</small>
                        @error('order')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Role yang Bisa Akses</label>
                    <div class="checkbox-group">
                        @php
                            $selectedRoles = $menuItem->roles ? explode(',', $menuItem->roles) : [];
                        @endphp
                        @foreach($roles as $role)
                            <label class="checkbox-label">
                                <input type="checkbox" name="roles[]" value="{{ $role }}" {{ in_array($role, old('roles', $selectedRoles)) ? 'checked' : '' }}>
                                <span>{{ \App\Models\User::roleLabel($role) }}</span>
                            </label>
                        @endforeach
                    </div>
                    <small>Kosongkan jika semua role bisa akses</small>
                    @error('roles')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Badge Text (Opsional)</label>
                        <input type="text" name="badge_text" class="form-control" value="{{ old('badge_text', $menuItem->badge_text) }}">
                        @error('badge_text')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Badge Color (Opsional)</label>
                        <select name="badge_color" class="form-control">
                            <option value="">Pilih Warna</option>
                            <option value="active" {{ old('badge_color', $menuItem->badge_color) == 'active' ? 'selected' : '' }}>Hijau (Aktif)</option>
                            <option value="warning" {{ old('badge_color', $menuItem->badge_color) == 'warning' ? 'selected' : '' }}>Orange (Warning)</option>
                            <option value="info" {{ old('badge_color', $menuItem->badge_color) == 'info' ? 'selected' : '' }}>Biru (Info)</option>
                            <option value="pending" {{ old('badge_color', $menuItem->badge_color) == 'pending' ? 'selected' : '' }}>Kuning (Pending)</option>
                        </select>
                        @error('badge_color')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $menuItem->is_active) ? 'checked' : '' }}>
                        <span>Aktifkan Menu</span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.management.menus.index') }}" class="btn-secondary">Batal</a>
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

    .form-group small {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: #999;
    }

    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 10px;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-weight: normal;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
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
