@extends('admin.template.index')
@push('css')
@endpush
@section('content')
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Anda sedang melihat data mahasiswa berdasarkan dosen pembimbing <b>{{ $dosen->name }}</b>.
        Silakan pilih angkatan untuk melihat mahasiswa yang dibimbing.
    </div>

    <div class="row">
        @foreach ($angkatan as $tahun)
            <div class="col-xl-3 col-md-6 mb-4">
                <a href="{{ route('admin.students.getStudentsByBatchLecturer', ['batch' => $tahun->angkatan, 'id' => $dosen->id]) }}"
                    class="text-decoration-none">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Angkatan {{ $tahun->angkatan }}</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $tahun->total }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
@endpush
