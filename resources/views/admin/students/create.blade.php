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

        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
        }

        /* Style Trix Editor */
        trix-toolbar [type="bold"],
        trix-toolbar [type="italic"],
        trix-toolbar [type="underline"],
        trix-toolbar [type="link"],
        trix-toolbar [type="blockquote"],
        trix-toolbar [type="heading-1"],
        trix-toolbar [type="heading-2"] {
            border-radius: 4px;
            margin: 0 1px;
        }

        trix-toolbar .button-group {
            margin: 0 4px;
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
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i> Tambah Mahasiswa Baru
                        </h5>
                        <a href="{{ route('admin.students.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.students.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="full_name" class="form-label">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="full_name" id="full_name" class="form-control form-control-lg"
                                    value="{{ old('full_name') }}" placeholder="Masukkan nama lengkap" required>
                                @error('full_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="nim" class="form-label">NIM <span class="text-danger">*</span></label>
                                <input type="text" name="nim" id="nim" class="form-control form-control-lg"
                                    value="{{ old('nim') }}" placeholder="Masukkan NIM (cont: 1234567890)" required>
                                @error('nim')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="batch" class="form-label">Angkatan <span class="text-danger">*</span></label>
                                <input type="text" name="batch" id="batch" class="form-control form-control-lg"
                                    value="{{ old('batch') }}" placeholder="Contoh: 2023, 2024" required>
                                @error('batch')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="mb-3">
                                <label for="program_studi" class="form-label">Program Studi <span
                                        class="text-danger">*</span></label>
                                <select name="program_studi" id="program_studi" class="form-control form-control-lg"
                                    required>
                                    <option value="" disabled selected>Pilih Program Studi</option>
                                    @foreach ($studyPrograms as $program)
                                        <option value="{{ $program->name }}"
                                            {{ old('program_studi') == $program->name ? 'selected' : '' }}>
                                            {{ $program->name }}</option>
                                    @endforeach
                                </select>
                                @error('program_studi')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="gender" class="form-label">Jenis Kelamin <span
                                        class="text-danger">*</span></label>
                                <select name="gender" id="gender" class="form-control form-control-lg" required>
                                    <option value="" disabled selected>Pilih jenis kelamin</option>
                                    <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('gender')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat (Opsional)</label>
                                <textarea name="address" id="address" rows="3" class="form-control form-control-lg"
                                    placeholder="Masukkan alamat lengkap">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email (opsional)</label>
                                <input type="email" name="email" id="email" class="form-control form-control-lg"
                                    value="{{ old('email') }}" placeholder="email@domain.com">
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">No. HP (opsional)</label>
                                <input type="text" name="phone" id="phone" class="form-control form-control-lg"
                                    value="{{ old('phone') }}" placeholder="081234567890">
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Catatan (opsional)</label>
                                <trix-editor input="notes" class="form-control"></trix-editor>
                                <input type="hidden" name="notes" id="notes" value="{{ old('notes') }}">
                                @error('notes')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Simpan Mahasiswa
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
    <script>
        document.addEventListener("trix-initialize", function(event) {
            let editor = event.target;
            console.log("Trix Editor initialized", editor);
        });
    </script>
@endpush
