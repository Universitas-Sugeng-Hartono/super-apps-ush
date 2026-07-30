@extends('admin.template.index')

@push('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/trix@1.3.1/dist/trix.css">
    <style>
        .form-control:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        .btn-primary {
            background-color: #4361ee;
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #3a56d4;
        }

        .form-label {
            font-weight: 600;
            color: #333;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        trix-editor {
            min-height: 120px;
            font-size: 1rem;
            line-height: 1.6;
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 12px;
        }

        trix-editor:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-edit me-2"></i> Edit Mahasiswa - {{ $student->nama_lengkap }}
                        </h5>
                        <a href="{{ route('admin.students.index') }}" class="btn btn-dark btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.students.update', ['id' => $student->id]) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nama_lengkap" id="nama_lengkap"
                                    class="form-control form-control-lg"
                                    value="{{ old('nama_lengkap', $student->nama_lengkap) }}" required>
                                @error('nama_lengkap')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="nim" class="form-label">NIM <span class="text-danger">*</span></label>
                                <input type="text" name="nim" id="nim" class="form-control form-control-lg"
                                    value="{{ old('nim', $student->nim) }}" required>
                                @error('nim')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="angkatan" class="form-label">Angkatan</label>
                                <input type="text" name="angkatan" id="angkatan" class="form-control form-control-lg"
                                    value="{{ old('angkatan', $student->angkatan) }}">
                                @error('angkatan')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="program_studi" class="form-label">Program Studi</label>
                                <input type="text" name="program_studi" id="program_studi"
                                    class="form-control form-control-lg"
                                    value="{{ old('program_studi', $student->program_studi) }}">
                                @error('program_studi')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="mb-3">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="jenis_kelamin" class="form-control form-control-lg">
                                    <option value="L"
                                        {{ old('jenis_kelamin', $student->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                                        Laki-laki</option>
                                    <option value="P"
                                        {{ old('jenis_kelamin', $student->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                                        Perempuan</option>
                                </select>
                                @error('jenis_kelamin')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea name="alamat" id="alamat" rows="3" class="form-control form-control-lg">{{ old('alamat', $student->alamat) }}</textarea>
                                @error('alamat')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control form-control-lg"
                                    value="{{ old('email', $student->email) }}">
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="no_telepon" class="form-label">No. HP</label>
                                <input type="text" name="no_telepon" id="no_telepon" class="form-control form-control-lg"
                                    value="{{ old('no_telepon', $student->no_telepon) }}">
                                @error('no_telepon')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Catatan</label>
                                <input id="notes" type="hidden" name="notes"
                                    value="{{ old('notes', $student->notes) }}">
                                <trix-editor input="notes" class="form-control"></trix-editor>
                                @error('notes')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Update Mahasiswa
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/trix@1.3.1/dist/trix.js"></script>
@endpush
