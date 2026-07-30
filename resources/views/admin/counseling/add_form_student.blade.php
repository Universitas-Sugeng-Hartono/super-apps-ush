@extends('admin.template.index')

@push('css')
    <style>
        /* Card Utama */
        .form-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 24px;
            background: #fff;
        }

        .form-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .form-header h5 {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 1.1rem;
            letter-spacing: .5px;
        }

        /* Biodata Mahasiswa */
        .biodata-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 16px;
            background: #fdfdfd;
        }

        .field-row {
            display: flex;
            align-items: center;
            margin-bottom: .75rem;
        }

        .field-label {
            flex: 0 0 160px;
            font-weight: 600;
            color: #212529;
        }

        .field-value {
            flex: 1;
            padding: .375rem .75rem;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: .3rem;
            color: #212529;
        }

        /* Pas Foto */
        .pas-frame {
            display: inline-block;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
        }

        .pas-foto {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .pas-frame--3x4 {
            width: 120px;
            aspect-ratio: 3/4;
        }

        /* Responsif Biodata */
        @media (max-width: 768px) {
            .field-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .field-label {
                margin-bottom: .25rem;
            }

            .field-value {
                width: 100%;
            }

            .pas-frame--3x4 {
                width: 90px;
            }
        }

        /* Tabel Riwayat */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead {
            background: #212529;
            color: #fff;
            font-size: 0.9rem;
        }

        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
            padding: 10px;
        }

        .table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        /* Badge matkul */
        .badge {
            display: inline-block;
            font-size: 0.75rem;
            padding: .35em .6em;
            border-radius: .25rem;
        }

        /* TTD Img */
        .ttd-img {
            max-height: 40px;
            object-fit: contain;
        }

        @media print {
            .form-card {
                box-shadow: none !important;
                border: none !important;
            }

            .table {
                border: 1px solid #000;
            }

            .table th,
            .table td {
                border: 1px solid #000;
            }
        }

        .badge-sm {
            font-size: 0.7rem;
            padding: 0.25em 0.4em;
            margin: 2px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="form-card mx-auto col-12 col-lg-12">
            <div class="form-header">
                <h5>KARTU KONSULTASI DENGAN PEMBIMBING AKADEMIK</h5>
            </div>

            <!-- Biodata -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-9 order-2 order-md-1">
                    <div class="biodata-card">
                        <div class="field-row">
                            <label class="field-label">Nama Mahasiswa</label>
                            <p class="field-value">{{ $student->nama_lengkap }}</p>
                        </div>
                        <div class="field-row">
                            <label class="field-label">NIM</label>
                            <p class="field-value">{{ $student->nim }}</p>
                        </div>
                        <div class="field-row">
                            <label class="field-label">Nama Orang Tua</label>
                            <p class="field-value">{{ $student->nama_orangtua ?? '-' }}</p>
                        </div>
                        <div class="field-row">
                            <label class="field-label">Alamat</label>
                            <p class="field-value">{{ $student->alamat }}</p>
                        </div>
                        <div class="field-row">
                            <label class="field-label">No. HP</label>
                            <p class="field-value">{{ $student->no_telepon }}</p>
                        </div>
                        <div class="field-row">
                            <label class="field-label">Dosen PA</label>
                            <p class="field-value">{{ $student->dosenPA->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3 order-1 order-md-2 text-center">
                    <div class="pas-frame pas-frame--3x4 mx-auto mb-2">
                        <img src="{{ asset('storage/' . $student->foto) }}" class="pas-foto" alt="Pas foto">
                    </div>
                </div>
            </div>

            <!-- Riwayat Konsultasi -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle small table-sm">
                    <thead>
                        <tr>
                            <th rowspan="2">No.</th>
                            <th rowspan="2">Semester</th>
                            <th rowspan="2">SKS</th>
                            <th rowspan="2">IP Semester Lalu</th>
                            <th rowspan="2">Tanggal</th>
                            <th rowspan="2">Komentar</th>
                            <th rowspan="2">Matkul Tidak Lulus</th>
                            <th rowspan="2">Matkul Sudah Diulang</th>
                            <th colspan="2">Tanda Tangan</th>
                            <th rowspan="2">Aksi</th>
                        </tr>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Dosen</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($history as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row['semester'] }}</td>
                                <td>{{ $row['sks'] }}</td>
                                <td>{{ $row['ip'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($row['tanggal'])->translatedFormat('l, d F Y') }}</td>
                                <td>{{ $row['komentar'] }}</td>
                                <td>
                                    @if ($row->failed_courses_objects->count())
                                        @foreach ($row->failed_courses_objects as $fc)
                                            <span
                                                class="badge badge-sm bg-danger text-white">{{ $fc->code_prefix }}{{ $fc->code_number }}
                                                - {{ $fc->name }}</span><br>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Tidak Ada</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($row->retaken_courses_objects->count())
                                        @foreach ($row->retaken_courses_objects as $rc)
                                            <span
                                                class="badge badge-sm bg-warning text-dark">{{ $rc->code_prefix }}{{ $rc->code_number }}
                                                - {{ $rc->name }}</span><br>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Tidak Ada</span>
                                    @endif
                                </td>
                                <td><img src="{{ asset('storage/' . $student->ttd) }}" class="ttd-img"></td>
                                <td><img src="{{ asset('storage/' . $student->dosenPA->ttd) }}" class="ttd-img"></td>
                                <td>
                                    <!-- Tombol Edit -->
                                    <a href="{{ route('admin.students.editcard', $row->id) }}"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Tombol Hapus trigger modal -->
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal{{ $row->id }}" data-bs-placement="top"
                                        title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>

                                    <!-- Modal Hapus -->
                                    <div class="modal fade" id="deleteModal{{ $row->id }}" tabindex="-1"
                                        aria-labelledby="deleteModalLabel{{ $row->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $row->id }}">
                                                        <i class="fas fa-exclamation-triangle me-1"></i> Konfirmasi Hapus
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-0">
                                                        Apakah Anda yakin ingin menghapus data konsultasi
                                                        <strong>Semester {{ $row['semester'] }}</strong> pada tanggal
                                                        <strong>{{ \Carbon\Carbon::parse($row['tanggal'])->translatedFormat('d M Y') }}</strong>?
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                        <i class="fas fa-times"></i> Batal
                                                    </button>
                                                    <form action="{{ route('admin.students.deletecard', $row->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-trash-alt"></i> Ya, Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>


                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">Belum ada riwayat konsultasi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <small>
                    <strong>NB:</strong><br>
                    1. Pertemuan konsultasi minimal 4 kali per semester.<br>
                    2. Kartu bimbingan ini dipegang oleh Dosen PA dan Mahasiswa.
                </small>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
@endpush
