@extends('admin.template.index')

@push('css')
    <style>
        .student-card {
            display: block;
            background: #ffffff;
            border-radius: 12px;
            transition: all 0.25s ease;
            border: 2px solid #e5e7eb;
            overflow: hidden;
            height: 100%;
            cursor: pointer;
        }

        .student-card input {
            display: none;
        }

        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .student-card.selected {
            border-color: #0d6efd;
            background: #f0f7ff;
        }

        .card-inner {
            padding: 20px;
            text-align: center;
        }

        .student-photo {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 15px;
            border: 3px solid #f1f1f1;
        }

        .student-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: #111827;
        }

        .student-email {
            font-size: 13px;
            color: #6b7280;
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <h2 class="mb-2">Change of Academic Advisor</h2>
        <p class="mb-4 text-muted">
            Select advisor and students to transfer.
        </p>

        <form action="{{ route('admin.students.bulkChangeAdvisor') }}" method="POST">
            @csrf

            {{-- PILIH DOSEN TUJUAN --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Assign To Advisor</label>
                <select name="advisor_id" class="form-select" required>
                    <option value="">-- Select Advisor --</option>
                    @foreach ($users as $dosen)
                        @if (in_array($dosen->role, ['admin', 'superadmin']))
                            <option value="{{ $dosen->id }}">
                                {{ $dosen->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- LIST MAHASISWA --}}
            <div class="row">
                @foreach ($student as $item)
                    <div class="col-md-4 col-lg-3 mb-4">
                        <label class="student-card">
                            <input type="checkbox" name="student_ids[]" value="{{ $item->id }}">
                            <div class="card-inner">
                                <img src="{{ $item->photo
                                    ? asset('storage/' . $item->photo)
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($item->nama_lengkap) . '&background=random&color=fff&size=128' }}"
                                    alt="{{ $item->nama_lengkap }}" class="student-photo">
                                <h6 class="student-name">{{ $item->nama_lengkap }}</h6>
                                <span class="student-email">{{ $item->email }}</span>
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    Change Advisor
                </button>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('.student-card input').forEach(input => {
            input.addEventListener('change', function() {
                this.closest('.student-card')
                    .classList.toggle('selected', this.checked);
            });
        });
    </script>
@endsection
