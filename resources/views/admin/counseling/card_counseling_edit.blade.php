@extends('admin.template.index')

@push('css')
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Select2 default wrapper */
        .select2-container {
            width: 100% !important;
        }

        /* Dropdown ketika terbuka */
        .select2-container .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 4px;
        }

        /* Styling setiap pilihan (tag) */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #0d6efd;
            /* Bootstrap primary */
            border: none;
            border-radius: 0.25rem;
            color: #fff;
            padding: 2px 8px;
            margin-top: 4px;
            font-size: 0.875rem;
        }

        /* Tombol X di tag */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
            margin-right: 4px;
        }

        /* Placeholder */
        .select2-container--default .select2-selection--multiple .select2-selection__placeholder {
            color: #6c757d;
        }

        /* Fokus */
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Dropdown options */
        .select2-dropdown {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
            padding: 4px 0;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #0d6efd;
            color: white;
        }

        .select2-results__option {
            padding: 8px 12px;
            font-size: 0.9rem;
        }
    </style>
@endpush


@section('content')
    <div class="container-fluid py-4">
        <div class="card form-card mx-auto col-12 col-lg-10">
            <div class="form-header">
                <h5>Edit Konsultasi Mahasiswa</h5>
            </div>

            <form action="{{ route('admin.students.updatecard', $row->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nama Mahasiswa</label>
                    <input type="text" class="form-control" value="{{ $student->nama_lengkap }}" disabled>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Semester</label>
                        <input type="text" name="semester" class="form-control"
                            value="{{ old('semester', $row->semester) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">SKS</label>
                        <input type="number" name="sks" class="form-control" value="{{ old('sks', $row->sks) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">IP Semester Lalu</label>
                        <input type="number" step="0.01" name="ip" class="form-control"
                            value="{{ old('ip', $row->ip) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $row->tanggal) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Komentar</label>
                    <textarea name="komentar" class="form-control" rows="3">{{ old('komentar', $row->komentar) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Matkul Tidak Lulus</label>
                    <select name="failed_courses[]" class="form-select select2" multiple>
                        @foreach ($allCourses as $course)
                            <option value="{{ $course->id }}"
                                {{ $row->failed_courses_objects->pluck('id')->contains($course->id) ? 'selected' : '' }}>
                                {{ $course->code_prefix }}{{ $course->code_number }} - {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Matkul Sudah Diulang</label>
                    <select name="retaken_courses[]" class="form-select select2" multiple>
                        @foreach ($allCourses as $course)
                            <option value="{{ $course->id }}"
                                {{ $row->retaken_courses_objects->pluck('id')->contains($course->id) ? 'selected' : '' }}>
                                {{ $course->code_prefix }}{{ $course->code_number }} - {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.students.showCardByLecture', $student->id) }}"
                        class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
                placeholder: "Pilih Mata Kuliah",
                allowClear: true
            });
        });
    </script>
@endpush
