@extends('admin.template.index')

@push('css')
    <style>
        .bg-pink {
            background-color: #FF69B4;
            color: #FFFFFF;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .btn-primary {
            background-color: #4361ee;
            border: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #3a56d4;
        }

        .btn-danger {
            background-color: #e63946;
            border: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #c1121f;
        }

        .text-primary {
            color: #4361ee !important;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.4em 0.6em;
            border-radius: 12px;
        }

        .badge-success {
            background-color: #4cc9f0;
            color: #0a0a0a;
        }

        .table thead th {
            background-color: #4361ee;
            color: white;
            font-weight: 600;
            text-align: center;
        }

        .table tbody td {
            vertical-align: middle;
            text-align: center;
            font-size: 0.9rem;
        }

        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table-hover tbody tr:hover {
            background-color: #e3f2fd !important;
        }

        .filter-container {
            background: #f1f3f5;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
        }

        .filter-container h6 {
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
        }

        .trix-content {
            min-height: 70px;
            font-size: 0.9rem;
        }

        .trix-editor {
            min-height: 80px !important;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .modal.animate__animated .modal-content {
            animation-duration: 0.4s;
        }

        .modal.fade .modal-dialog {
            transform: translate(0, -20px);
            transition: transform 0.3s ease-out;
        }

        .modal.show .modal-dialog {
            transform: none;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Anda sedang melihat daftar mahasiswa yang dibimbing oleh <b>{{ $dosen->name }}</b>,
            khusus untuk angkatan <b>{{ $batch }}</b>.
        </div>


        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary btn-lg shadow">
                <i class="fas fa-plus-circle me-2"></i> Add New Student
            </a>
        </div>
        @if (request()->is('admin/counseling/get-students/*'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Anda sedang melihat data mahasiswa berdasarkan angkatan {{ $batch }}. Klik tombol "Add New Student"
                untuk menambahkan
                mahasiswa baru.
            </div>
            <div class="filter-container mb-3">
                <form method="GET" action="{{ route('admin.counseling.getStudentsByBatch', $batch) }}" class="d-flex">
                    <input type="text" name="search" class="form-control me-2"
                        placeholder="Cari nama, NIM, atau angkatan..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </form>
            </div>
        @else
            <div class="filter-container mb-3">
                <form method="GET" action="{{ route('admin.students.index') }}" class="d-flex">
                    <input type="text" name="search" class="form-control me-2"
                        placeholder="Cari nama, NIM, atau angkatan..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </form>
            </div>
        @endif


        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Nama Lengkap</th>
                                <th>NIM</th>
                                <th>Angkatan</th>
                                <th>Jenis Kelamin</th>
                                <th>Maps</th>
                                <th>Notes</th>
                                <th>Total Counseling</th>
                                {{-- @if (request()->is('admin/counseling/get-students/*')) --}}
                                <th>Counseling</th>
                                <th>Edit Request Approval</th>
                                {{-- @endif --}}
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $student)
                                <tr>
                                    <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                                    <td><strong>{{ Str::limit($student->nama_lengkap, 40) }}</strong></td>
                                    <td class="text-muted font-monospace">{{ $student->nim }}</td>
                                    <td><span class="badge badge-success">{{ $student->angkatan }}</span></td>
                                    <td>
                                        <span
                                            class="badge {{ $student->jenis_kelamin === 'L' ? 'bg-primary text-white' : 'bg-pink  text-white' }}">
                                            {{ $student->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($student->alamat_lat !== null && $student->alamat_lng !== null)
                                            <a href="https://www.google.com/maps?q={{ $student->alamat_lat }},{{ $student->alamat_lng }}"
                                                target="_blank" class="btn btn-outline-primary">
                                                <i class="fas fa-map-marked-alt me-2"></i> Lihat
                                            </a>
                                        @else
                                            Belum di isi
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#notesModal" data-id="{{ $student->id }}"
                                            data-name="{{ $student->nama_lengkap }}" data-notes="{!! $student->notes !!}">
                                            <i class="fas fa-sticky-note"></i> Notes
                                        </button>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            {{ $student->counselings_count }} kali
                                        </span>
                                    </td>
                                    {{-- @if (request()->is('admin/counseling/get-students/*')) --}}
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            @if ($student->is_counseling == 0)
                                                <a href="{{ route('admin.counseling.openclose', $student->id) }}"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-plus-circle me-1"></i> Open
                                                </a>
                                            @else
                                                <a href="{{ route('admin.counseling.openclose', $student->id) }}"
                                                    class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-minus-circle me-1"></i> Closed
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.students.showCardByLecture', $student->id) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-file-alt me-1"></i> Check
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            @if ($student->is_edited == 0)
                                                <a href="{{ route('admin.counseling.opencloseedit', $student->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-plus-circle me-1"></i> Open
                                                </a>
                                            @else
                                                <a href="{{ route('admin.counseling.opencloseedit', $student->id) }}"
                                                    class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-minus-circle me-1"></i> Closed
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    {{-- @endif --}}
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            <a href="{{ route('admin.students.edit', $student->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                data-id="{{ $student->id }}" data-name="{{ $student->nama_lengkap }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <!-- Tombol reset password -->
                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                data-bs-toggle="modal" data-bs-target="#resetPasswordModal"
                                                data-id="{{ $student->id }}" data-name="{{ $student->nama_lengkap }}">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-5">
                                        <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                        <p class="mb-0">Tidak ada data mahasiswa.</p>
                                        <small class="text-muted">Silakan tambahkan data baru.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $students->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content animate__animated">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-exclamation-circle text-danger mb-3"
                            style="font-size: 4rem; animation: pulse 1.2s infinite;"></i>
                        <p class="mb-1">Apakah Anda yakin ingin menghapus <br><strong id="studentName"></strong>?</p>
                        <small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Notes Modal -->
    <div class="modal fade" id="notesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content animate__animated">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-sticky-note me-2"></i> Catatan Mahasiswa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-clipboard-list text-primary mb-3"
                            style="font-size: 4rem; animation: pulse 1.2s infinite;"></i>
                        <h6 id="notesStudentName" class="fw-bold"></h6>
                        <small class="text-muted">Catatan tambahan dari Dosen Pembimbing</small>
                    </div>
                    <div id="notesContent" class="border rounded p-3 bg-light trix-content"
                        style="min-height:100px; max-height:250px; overflow-y:auto;">
                        <span class="text-muted">Tidak ada catatan</span>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content animate__animated">
                <form id="resetPasswordForm" method="GET">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="fas fa-key me-2"></i> Reset Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-key text-warning mb-3"
                            style="font-size: 4rem; animation: pulse 1.2s infinite;"></i>
                        <p class="mb-1">Apakah Anda yakin ingin mereset password <br><strong
                                id="resetStudentName"></strong>?</p>
                        <small class="text-danger">Password baru akan menjadi <b>12345678</b></small>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Ya, Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof bootstrap === 'undefined') return;

            function attachModalAnimation(modalId, onShow = null) {
                const modal = document.getElementById(modalId);
                if (!modal) return;
                const content = modal.querySelector('.modal-content');
                content.classList.add('animate__animated');

                modal.addEventListener('show.bs.modal', function(event) {
                    if (onShow) onShow(event, modal);
                    content.classList.remove('animate__fadeOutDown');
                    content.classList.add('animate__fadeInUp');
                    content.addEventListener('animationend', function handler() {
                        content.classList.remove('animate__fadeInUp');
                        content.removeEventListener('animationend', handler);
                    }, {
                        once: true
                    });
                });

                modal.addEventListener('hide.bs.modal', function(event) {
                    if (modal.dataset.bsAnimating === 'true') return;
                    event.preventDefault();
                    modal.dataset.bsAnimating = 'true';
                    content.classList.remove('animate__fadeInUp');
                    content.classList.add('animate__fadeOutDown');
                    content.addEventListener('animationend', function handler() {
                        const instance = bootstrap.Modal.getInstance(modal);
                        if (instance) {
                            instance.hide();
                        } else {
                            modal.classList.remove('show');
                            modal.style.display = 'none';
                            document.body.classList.remove('modal-open');
                            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                            content.classList.remove('animate__fadeOutDown');
                            delete modal.dataset.bsAnimating;
                        }
                        content.removeEventListener('animationend', handler);
                    }, {
                        once: true
                    });
                });

                modal.addEventListener('hidden.bs.modal', function() {
                    content.classList.remove('animate__fadeInUp', 'animate__fadeOutDown');
                    delete modal.dataset.bsAnimating;
                });
            }

            // Delete Modal
            attachModalAnimation('deleteModal', function(event, modal) {
                if (modal.id === 'deleteModal' && event.relatedTarget) {
                    const btn = event.relatedTarget;
                    const studentId = btn.getAttribute('data-id');
                    const studentName = btn.getAttribute('data-name');
                    document.getElementById('studentName').textContent = studentName;
                    document.getElementById('deleteForm').action = "{{ url('admin/students') }}/" +
                        studentId;
                }
            });

            // Notes Modal
            attachModalAnimation('notesModal', function(event, modal) {
                if (modal.id === 'notesModal' && event.relatedTarget) {
                    const btn = event.relatedTarget;
                    const studentName = btn.getAttribute('data-name');
                    const notes = btn.getAttribute('data-notes');
                    document.getElementById('notesStudentName').textContent = studentName;
                    document.getElementById('notesContent').innerHTML = notes ?
                        notes : '<span class="text-muted">Tidak ada catatan</span>';
                }
            });

            // Reset Password Modal
            attachModalAnimation('resetPasswordModal', function(event, modal) {
                if (modal.id === 'resetPasswordModal' && event.relatedTarget) {
                    const btn = event.relatedTarget;
                    const studentId = btn.getAttribute('data-id');
                    const studentName = btn.getAttribute('data-name');
                    document.getElementById('resetStudentName').textContent = studentName;
                    document.getElementById('resetPasswordForm').action =
                        "{{ url('admin/students') }}/" + studentId + "/resetpassword";
                }
            });
        });
    </script>
@endpush
