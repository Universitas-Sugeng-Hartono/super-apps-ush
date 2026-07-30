@extends('admin.template.index')

@push('css')
    <style>
        .student-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            background: #fff;
            margin-bottom: 24px;
            border: 3px solid #4146AF;
        }

        .student-header {
            font-weight: bold;
            font-size: 1rem;
            margin-bottom: 8px;
            color: #333;
        }

        .course-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 8px;
            margin: 4px;
            font-size: 0.8rem;
        }

        .failed {
            background: #ffe0e0;
            color: #c00;
        }

        .retaken {
            background: #e0f7e9;
            color: #006400;
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <h3>Students Counseling Report</h3>
        <p>This is a report of students who failed or retaken courses</p>

        <div class="row">
            @forelse($students as $student)
                <div class="col-xl-3 col-md-6">
                    <div class="student-card p-3">
                        <div class="student-header">
                            {{ $student->nama_lengkap }} <br>
                            <small>({{ $student->nim }})</small>
                        </div>
                        <div><strong>Batch:</strong> {{ $student->angkatan }}</div>
                        <div><strong>Program:</strong> {{ $student->program_studi }}</div>

                        @if ($student->counselings->isNotEmpty())
                            <div class="accordion mt-3" id="accordionStudent{{ $student->id }}">
                                @foreach ($student->counselings as $index => $counseling)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $student->id }}-{{ $index }}">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapse{{ $student->id }}-{{ $index }}"
                                                aria-expanded="false"
                                                aria-controls="collapse{{ $student->id }}-{{ $index }}">
                                                Semester {{ $counseling->semester }}
                                                | SKS: {{ $counseling->sks }}
                                                | IP: {{ $counseling->ip ?? '-' }}
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $student->id }}-{{ $index }}"
                                            class="accordion-collapse collapse"
                                            aria-labelledby="heading{{ $student->id }}-{{ $index }}"
                                            data-bs-parent="#accordionStudent{{ $student->id }}">
                                            <div class="accordion-body">
                                                <div>
                                                    <strong>Date:</strong>
                                                    {{ \Carbon\Carbon::parse($counseling->tanggal)->locale('id')->translatedFormat('l, d F Y') }}
                                                </div>
                                                <div><em>{{ $counseling->komentar }}</em></div>

                                                <div class="mt-2">
                                                    <strong>Failed Courses
                                                        ({{ $counseling->failed_courses_detail->count() }})
                                                    </strong><br>
                                                    @forelse($counseling->failed_courses_detail as $course)
                                                        <span class="course-badge failed">{{ $course->name }}
                                                            ({{ $course->sks }} SKS)
                                                        </span>
                                                    @empty
                                                        <span>- None -</span>
                                                    @endforelse
                                                </div>

                                                <div class="mt-2">
                                                    <strong>Retaken Courses</strong><br>
                                                    @forelse($counseling->retaken_courses_detail as $course)
                                                        <span class="course-badge retaken">{{ $course->name }}
                                                            ({{ $course->sks }} SKS)
                                                        </span>
                                                    @empty
                                                        <span>- None -</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-2"><em>No counseling records</em></p>
                        @endif
                    </div>
                </div>
                @empty
                    <p>No students found.</p>
                @endforelse
            </div>
        </div>

    @endsection
