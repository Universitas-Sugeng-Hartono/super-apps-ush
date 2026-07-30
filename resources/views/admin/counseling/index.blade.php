@extends('admin.template.index')
@push('css')
@endpush
@section('content')
    <div class="row">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary btn-lg shadow">
                <i class="fas fa-plus-circle me-2"></i> Add New Student
            </a>
        </div>
        {{-- <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('admin.counseling.getStudentsByBatchByCourse') }}" class="btn btn-primary btn-lg shadow">
                <i class="fas fa-plus-circle me-2"></i> Data students by Course
            </a>
        </div> --}}
        @foreach ($angkatan as $tahun)
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="card text-center">
                    {{-- <div class="card-header">
                        Class of {{ $tahun->angkatan }}
                    </div> --}}
                    <div class="card-body">
                        <h5 class="card-title">Students Class of {{ $tahun->angkatan }}</h5>
                        <p class="card-text">See who’s in the class of <b>{{ $tahun->angkatan }}</b> and explore their data.
                        </p>
                        <p>Explore</p>
                        <a href="{{ route('admin.counseling.getStudentsByBatch', ['batch' => $tahun->angkatan]) }}"
                            class="btn btn-primary">Data</a>
                        <a href="{{ route('admin.counseling.getStudentsByBatchByCourse', ['batch' => $tahun->angkatan]) }}"
                            class="btn btn-primary">Course</a>
                    </div>
                    <div class="card-footer text-body-secondary">
                        Total Students: {{ $tahun->total }}
                    </div>

                </div>

            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
@endpush
