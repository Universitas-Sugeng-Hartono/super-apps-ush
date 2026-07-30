@extends('admin.template.index')

@push('css')
    <style>
        .form-control:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, .25);
        }

        .btn-primary {
            background: #4361ee;
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #3a56d4;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
        }
    </style>
@endpush

@section('content')
    @php
        /** @var \App\Models\User $user */
        $isEdit = isset($user) && $user->exists;
    @endphp

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header {{ $isEdit ? 'bg-info text-white' : 'bg-success text-white' }}">
                        <h5 class="mb-0">
                            <i class="fas fa-user-edit me-2"></i>
                            {{ $isEdit ? 'Edit User - ' . $user->name : 'Tambah User' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ $isEdit ? route('user.admin.update', $user->id) : route('user.admin.store') }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            @if ($isEdit)
                                @method('PUT')
                            @endif

                            {{-- Nama (required) --}}
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control form-control-lg"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Username (unique, boleh kosong) --}}
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" id="username" class="form-control form-control-lg"
                                    value="{{ old('username', $user->username) }}">
                                @error('username')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- NIDN / NUPTK --}}
                            <div class="mb-3">
                                <label for="NIDNorNUPTK" class="form-label">NIDN / NUPTK</label>
                                <input type="text" name="NIDNorNUPTK" id="NIDNorNUPTK"
                                    class="form-control form-control-lg"
                                    value="{{ old('NIDNorNUPTK', $user->NIDNorNUPTK) }}">
                                @error('NIDNorNUPTK')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Role --}}
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select name="role" id="role" class="form-control form-control-lg">
                                    @php $roleNow = old('role', $user->role ?? 'admin'); @endphp
                                    <option value="admin" {{ $roleNow == 'admin' ? 'selected' : '' }}>Dosen</option>
                                    <option value="superadmin" {{ $roleNow == 'superadmin' ? 'selected' : '' }}>Kaprodi</option>
                                    <option value="masteradmin" {{ $roleNow == 'masteradmin' ? 'selected' : '' }}>Superuser</option>
                                </select>
                                @error('role')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email (required) --}}
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control form-control-lg"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Foto --}}
                            <div class="mb-3">
                                <label for="photo" class="form-label">Foto</label>
                                <input type="file" name="photo" id="photo" class="form-control form-control-lg"
                                    accept="image/*">
                                @if ($isEdit && $user->photo)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $user->photo) }}" alt="Foto"
                                            class="img-thumbnail" width="120">
                                    </div>
                                @endif
                                @error('photo')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- TTD --}}
                            <div class="mb-3">
                                <label for="ttd" class="form-label">Tanda Tangan</label>
                                <input type="file" name="ttd" id="ttd" class="form-control form-control-lg"
                                    accept="image/*">
                                @if ($isEdit && $user->ttd)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $user->ttd) }}" alt="TTD"
                                            class="img-thumbnail" width="120">
                                    </div>
                                @endif
                                @error('ttd')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Info password default saat create (optional notice) --}}
                            @unless ($isEdit)
                                <div class="alert alert-info">
                                    Password default akan diset ke <b>password</b>.
                                </div>
                            @endunless

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> {{ $isEdit ? 'Update User' : 'Simpan User' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @push('scripts')
    @endpush
