@extends('students.layouts.super-app')

@section('content')
    @if(session('error'))
        <div class="alert-card alert-error">
            <i class="bi bi-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif



    <div class="page-card">
        <div>
            <span class="page-eyebrow">{{ ($skpiRegistration && $skpiRegistration->status === 'needs_revision') ? 'Form Revisi' : 'Form Draft' }}</span>
            <h3>Form Pengajuan SKPI</h3>
        </div>
        <div class="page-badge-wrap">
            <span class="status-badge {{ $registrationStatus['badge_class'] }}">{{ $registrationStatus['label'] }}</span>
        </div>
    </div>

    @if($skpiRegistration && $skpiRegistration->approval_notes)
        @php
            $isRejected = $skpiRegistration->status === 'rejected';
            $alertClass = $isRejected ? 'alert-error' : 'alert-note';
            $alertBg = $isRejected ? '#FFEBEE' : '#E3F2FD';
            $alertColor = $isRejected ? '#C62828' : '#1565C0';
            $alertIcon = $isRejected ? 'bi-x-octagon-fill' : 'bi-chat-left-text';
            $alertTitle = $isRejected ? 'Pengajuan Ditolak' : 'Catatan Revisi dari Admin';
        @endphp
        <div class="alert-card {{ $alertClass }}" style="background: {{ $alertBg }}; color: {{ $alertColor }}; padding: 16px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 12px; border: 1px solid {{ $isRejected ? '#ef9a9a' : '#bbdefb' }};">
            <i class="bi {{ $alertIcon }}" style="font-size: 20px; margin-top: 2px;"></i>
            <div>
                <strong style="display: block; margin-bottom: 4px; font-size: 15px;">{{ $alertTitle }}</strong>
                <p style="margin: 0; font-size: 14px; line-height: 1.5;">{{ $skpiRegistration->approval_notes }}</p>
                @if($isRejected)
                <p style="margin: 6px 0 0 0; font-size: 13px; opacity: 0.9;">Silakan perbaiki data Anda dan simpan ulang form ini.</p>
                @endif
            </div>
        </div>
    @endif

    @if($holderMeta['filled_count'] < $holderMeta['total_count'])
        <div class="alert-card alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <div>
                <strong>Data Belum Lengkap!</strong>
                <p>Terdapat {{ $holderMeta['total_count'] - $holderMeta['filled_count'] }} field data yang masih kosong. Mohon hubungi Dosen Pembimbing Anda jika ada data yang kurang lengkap.</p>
            </div>
        </div>
    @endif

    {{-- <div class="helper-grid">
        <div class="helper-card">
            <div class="helper-icon">
                <i class="bi bi-database-check"></i>
            </div>
            <div>
                <h5>Auto-fill dari Sistem</h5>
                <p>Nama lengkap, NIM, tahun masuk, dan data lain yang sudah tersedia langsung dimunculkan agar Anda tidak perlu mengisi ulang dari nol.</p>
            </div>
        </div>

        <div class="helper-card">
            <div class="helper-icon blue">
                <i class="bi bi-arrow-repeat"></i>
            </div>
            <div>
                <h5>Status Pengajuan</h5>
                <p>{{ $registrationStatus['description'] }}</p>
            </div>
        </div>
    </div> --}}

    <div class="form-card">
        <div class="card-head">
            <div>
                <h4>Form Pengajuan SKPI</h4>
                <p>Silakan tinjau dan lengkapi semua field berikut.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('student.skpi.daftar.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="tanggal_masuk" value="{{ $student->tanggal_masuk ? $student->tanggal_masuk->format('Y-m') : '' }}">
            <div class="form-grid">

                <div class="form-group">
                    <label for="ipk">IPK (Include skripsi)</label>
                    <input id="ipk" type="text" name="ipk" class="form-control" value="{{ $holderData['ipk'] }}" placeholder="Contoh: 3.85" @disabled(!$canEditRegistration)>
                    @error('ipk')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="sks">Total SKS (Include skripsi)</label>
                    <input id="sks" type="number" name="sks" class="form-control" value="{{ $holderData['sks'] }}" placeholder="Contoh: 144" @disabled(!$canEditRegistration)>
                    @error('sks')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group full-width">
                    <label for="judul_ta_indo">Judul Tugas Akhir (Indonesia)</label>
                    <textarea id="judul_ta_indo" name="judul_ta_indo" class="form-control" rows="2" placeholder="Masukkan judul bahasa Indonesia" @disabled(!$canEditRegistration)>{{ $holderData['judul_ta_indo'] }}</textarea>
                    @error('judul_ta_indo')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group full-width">
                    <label for="judul_ta_inggris">Judul Tugas Akhir (Inggris)</label>
                    <textarea id="judul_ta_inggris" name="judul_ta_inggris" class="form-control" rows="2" placeholder="Masukkan judul bahasa Inggris (opsional)" @disabled(!$canEditRegistration)>{{ $holderData['judul_ta_inggris'] }}</textarea>
                    @error('judul_ta_inggris')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="periode_lulus">periode lulus(sk yudisium/kelulusan)</label>
                    @php
                        $periodeValue = $holderData['periode_lulus'] ?? '';
                        if (strlen($periodeValue) === 7) {
                            $periodeValue .= '-01';
                        }
                    @endphp
                    <input id="periode_lulus" type="date" name="periode_lulus" class="form-control" value="{{ $periodeValue }}" @disabled(!$canEditRegistration) onchange="calculateLamaStudi()">
                    @error('periode_lulus')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="lama_studi">Lama Studi (Terhitung Otomatis)</label>
                    <input id="lama_studi" type="text" name="lama_studi" class="form-control readonly-field" value="{{ $holderData['lama_studi'] }}" readonly placeholder="0 Bulan">
                </div>

                {{-- Nomor Ijazah diisi oleh Masteradmin, bukan mahasiswa --}}
            </div>

            <div class="form-grid mt-4">
                <div class="form-group full-width">
                    <label for="doc_ijasah">Upload Ijazah Terakhir (PDF, Max 2MB)</label>
                    <input id="doc_ijasah" type="file" name="doc_ijasah" class="form-control" accept=".pdf" @disabled(!$canEditRegistration)>
                    @if($skpiRegistration && $skpiRegistration->doc_ijasah)
                        <small class="text-success mt-1 d-block"><i class="bi bi-check-circle"></i> File sudah diunggah: <a href="{{ asset('storage/' . $skpiRegistration->doc_ijasah) }}" target="_blank">Lihat File</a></small>
                    @endif
                    @error('doc_ijasah')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group full-width">
                    <label for="doc_ktp">Upload KTP (PDF, Max 2MB)</label>
                    <input id="doc_ktp" type="file" name="doc_ktp" class="form-control" accept=".pdf" @disabled(!$canEditRegistration)>
                    @if($skpiRegistration && $skpiRegistration->doc_ktp)
                        <small class="text-success mt-1 d-block"><i class="bi bi-check-circle"></i> File sudah diunggah: <a href="{{ asset('storage/' . $skpiRegistration->doc_ktp) }}" target="_blank">Lihat File</a></small>
                    @endif
                    @error('doc_ktp')<small class="text-danger">{{ $message }}</small>@enderror
                </div>


            </div>

            {{-- <div class="quick-links">
                <a href="{{ route('student.personal.editDataIndex') }}" class="quick-link">
                    <i class="bi bi-person-lines-fill"></i>
                    Lengkapi Profil Mahasiswa
                </a>
                <a href="{{ route('student.final-project.index') }}" class="quick-link">
                    <i class="bi bi-journal-check"></i>
                    Cek Data Tugas Akhir
                </a>
            </div> --}}

            <div class="form-actions">
                <a href="{{ route('student.skpi.daftar.index') }}" class="btn btn-soft">Kembali</a>
                @if($canEditRegistration)
                    <button type="submit" class="btn btn-primary-soft">{{ $skpiRegistration ? 'Update Pengajuan SKPI' : 'Simpan Pengajuan SKPI' }}</button>
                @endif
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function calculateLamaStudi() {
        const periodeLulusInput = document.getElementById('periode_lulus')?.value;
        const tanggalMasukInput = document.getElementById('tanggal_masuk')?.value;
        const lamaStudiInput = document.getElementById('lama_studi');

        if (!lamaStudiInput) return;

        if (!periodeLulusInput || !tanggalMasukInput) {
            lamaStudiInput.value = '';
            return;
        }

        
        const masukDate = new Date(tanggalMasukInput + '-01');
        let lulusDateStr = periodeLulusInput;
        if (lulusDateStr.length === 7) {
            lulusDateStr += '-01';
        }
        const lulusDate = new Date(lulusDateStr);

        if (lulusDate < masukDate) {
            lamaStudiInput.value = 'Data Tidak Valid';
            return;
        }

        let months = (lulusDate.getFullYear() - masukDate.getFullYear()) * 12;
        months -= masukDate.getMonth();
        months += lulusDate.getMonth();

        if (months <= 0) {
            lamaStudiInput.value = '0 Bulan';
            return;
        }

        const years = Math.floor(months / 12);
        const remainingMonths = months % 12;

        let result = [];
        if (years > 0) result.push(years + ' Tahun');
        if (remainingMonths > 0) result.push(remainingMonths + ' Bulan');

        lamaStudiInput.value = result.join(' ');
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateLamaStudi();
    });
</script>
@endpush

@push('css')
<style>
    .page-card,
    .helper-card,
    .form-card,
    .alert-card {
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
    }

    .page-card,
    .helper-card {
        display: flex;
        gap: 16px;
    }

    .page-card {
        padding: 24px;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        background: linear-gradient(135deg, #FFF8EE, #FFFFFF);
    }

    .page-eyebrow {
        display: inline-flex;
        padding: 5px 12px;
        border-radius: 999px;
        background: #FFF3E0;
        color: #E65100;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .page-card h3,
    .card-head h4,
    .helper-card h5 {
        margin: 0 0 8px;
        color: var(--text-dark);
        font-weight: 700;
    }

    .page-card p,
    .card-head p,
    .helper-card p,
    .form-group small {
        margin: 0;
        color: #666;
        line-height: 1.7;
        font-size: 14px;
    }

    .page-badge {
        display: inline-flex;
        padding: 7px 14px;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .page-badge-wrap {
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: flex-end;
    }

    .status-badge {
        display: inline-flex;
        padding: 7px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-badge.active {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.warning {
        background: #FFF3E0;
        color: #E65100;
    }

    .status-badge.info {
        background: #E3F2FD;
        color: #1565C0;
    }

    .status-badge.danger {
        background: #FFEBEE;
        color: #C62828;
    }

    .status-badge.muted {
        background: #F5F5F5;
        color: #757575;
    }

    .alert-card {
        padding: 16px 18px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .alert-card i {
        font-size: 18px;
        margin-top: 2px;
    }

    .alert-error {
        background: #FFEBEE;
        color: #C62828;
    }

    .alert-note {
        background: #FFF8E1;
        color: #795548;
    }

    .alert-warning {
        background: #FFF3E0;
        color: #E65100;
    }

    .alert-note strong,
    .alert-warning strong {
        display: block;
        margin-bottom: 4px;
    }

    .alert-note p,
    .alert-warning p {
        margin: 0;
        color: inherit;
    }

    .helper-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .helper-card {
        padding: 20px;
        align-items: flex-start;
    }

    .helper-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, #FF9800, #FF7043);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .helper-icon.blue {
        background: linear-gradient(135deg, #2196F3, #64B5F6);
    }

    .form-card {
        padding: 24px;
    }

    .card-head {
        margin-bottom: 22px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .form-group {
        display: grid;
        gap: 8px;
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
    }

    .form-control {
        border: 2px solid #E9E9E9;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 14px;
        background: #FAFAFA;
        transition: var(--transition-normal);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-orange);
        background: white;
        box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.12);
    }

    .form-control:disabled {
        background: #F5F5F5;
        color: #777;
        cursor: not-allowed;
    }

    .quick-links {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 22px;
    }

    .quick-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 14px;
        background: #FFF3E0;
        color: #E65100;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    .form-actions {
        margin-top: 24px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .btn {
        border: none;
        border-radius: 14px;
        padding: 11px 18px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
    }

    .btn-soft {
        background: #F5F5F5;
        color: var(--text-dark);
    }

    .btn-primary-soft {
        background: linear-gradient(135deg, #FF9800, #FF7043);
        color: white;
    }

    .text-danger {
        color: #C62828;
    }

    @media (max-width: 768px) {
        .page-card,
        .helper-card,
        .form-actions {
            flex-direction: column;
        }

        .helper-grid,
        .form-grid {
            grid-template-columns: 1fr;
        }

        .btn {
            width: 100%;
            text-align: center;
        }

        .page-badge-wrap {
            align-items: flex-start;
        }
    }
</style>
@endpush
