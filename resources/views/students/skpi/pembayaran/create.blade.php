@extends('students.layouts.super-app')

@section('content')
    @if(session('error'))
        <div class="alert-card alert-error" style="background: #FFEBEE; color: #C62828; padding: 16px; border-radius: 12px; margin-bottom: 20px;">
            <i class="bi bi-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if(session('success'))
        <div class="alert-card alert-success" style="background: #E8F5E9; color: #2E7D32; padding: 16px; border-radius: 12px; margin-bottom: 20px;">
            <i class="bi bi-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('info'))
        <div class="alert-card alert-info" style="background: #E3F2FD; color: #1565C0; padding: 16px; border-radius: 12px; margin-bottom: 20px;">
            <i class="bi bi-info-circle"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <div class="page-card" style="background: white; border-radius: 20px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span class="page-eyebrow" style="background: #FFF3E0; color: #E65100; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; margin-bottom: 8px; display: inline-block;">Dokumen Pendukung SKPI</span>
                <h3 style="margin: 0; color: #333; font-weight: 700;">Upload Pembayaran & Naskah Publikasi</h3>
            </div>
            <a href="{{ route('student.skpi.daftar.index') }}" class="btn btn-outline" style="border: 1px solid #ddd; padding: 8px 16px; border-radius: 8px; color: #333; text-decoration: none; font-weight: 600;">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Status Banner & Kondisi Form --}}
    @php 
        $paymentStatus = $skpiRegistration->payment_status ?? 'pending'; 
        $hasFiles = $skpiRegistration && ($skpiRegistration->doc_pembayaran_wisuda || $skpiRegistration->doc_naskah_publikasi);
        $showForm = !$hasFiles || $paymentStatus === 'rejected';
    @endphp

    @if($paymentStatus === 'approved')
    <div style="background: #E8F5E9; border: 1px solid #a5d6a7; border-radius: 16px; padding: 24px; margin-bottom: 24px; text-align: center;">
        <i class="bi bi-patch-check-fill" style="color: #2e7d32; font-size: 48px; margin-bottom: 16px; display: inline-block;"></i>
        <h3 style="color: #1b5e20; font-size: 20px; font-weight: 700; margin: 0 0 8px;">Dokumen Terverifikasi</h3>
        <p style="color: #388e3c; font-size: 14px; margin: 0 0 20px;">Bukti pembayaran dan naskah publikasi Anda telah disetujui oleh Admin.</p>
        
        <div style="display: flex; gap: 12px; justify-content: center;">
            <a href="{{ asset('storage/' . $skpiRegistration->doc_pembayaran_wisuda) }}" target="_blank" class="btn btn-outline" style="border: 1px solid #2e7d32; color: #2e7d32; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;">Lihat Pembayaran</a>
            <a href="{{ asset('storage/' . $skpiRegistration->doc_naskah_publikasi) }}" target="_blank" class="btn btn-outline" style="border: 1px solid #2e7d32; color: #2e7d32; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;">Lihat Naskah</a>
        </div>

        <hr style="border-color: #c8e6c9; margin: 24px 0;">
        <a href="{{ route('student.skpi.daftar.index') }}" style="background: #2e7d32; color: white; padding: 12px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; font-size: 15px;">
            Lanjut Mengisi Form Identitas SKPI <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    @elseif($paymentStatus === 'rejected')
    <div style="background: #FFEBEE; border: 1px solid #ef9a9a; border-radius: 16px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 14px;">
        <i class="bi bi-x-octagon-fill" style="color: #c62828; font-size: 22px; margin-top: 2px; flex-shrink: 0;"></i>
        <div>
            <strong style="color: #b71c1c; font-size: 15px;">Dokumen Ditolak — Perlu Diperbaiki</strong>
            @if($skpiRegistration->payment_approval_notes)
            <p style="margin: 4px 0 6px; color: #c62828; font-size: 13px;"><strong>Catatan Admin:</strong> {{ $skpiRegistration->payment_approval_notes }}</p>
            @endif
            <p style="margin: 4px 0 0; color: #d32f2f; font-size: 13px;">Silakan unggah ulang dokumen yang benar di bawah ini.</p>
        </div>
    </div>
    @elseif($hasFiles)
    <div id="success-state" style="background: #F3F4F6; border: 1px solid #E5E7EB; border-radius: 16px; padding: 32px 24px; margin-bottom: 24px; text-align: center;">
        <i class="bi bi-cloud-check-fill" style="color: #4B5563; font-size: 48px; margin-bottom: 16px; display: inline-block;"></i>
        <h3 style="color: #1F2937; font-size: 20px; font-weight: 700; margin: 0 0 8px;">Dokumen Berhasil Diunggah</h3>
        <p style="color: #4B5563; font-size: 14px; margin: 0 0 24px;">Dokumen Anda telah tersimpan</p>

        <a href="{{ route('student.skpi.daftar.index') }}" style="background: linear-gradient(135deg, #FF9800, #FF7043); color: white; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; font-size: 15px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(255, 112, 67, 0.3);">
            Lanjut Mengisi Form Identitas SKPI <i class="bi bi-arrow-right"></i>
        </a>
        
        <div style="font-size: 13px; color: #6B7280;">
            Ada file yang salah pilih? <button type="button" onclick="toggleForm()" style="background: none; border: none; color: #FF7043; font-weight: 600; cursor: pointer; padding: 0; text-decoration: underline;">Upload ulang dokumen</button>
        </div>
    </div>
    @endif

    <div id="upload-form-container" class="form-card" style="background: white; border-radius: 20px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); {{ $showForm ? '' : 'display: none;' }}">
        @if(!$showForm)
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #333;">Upload Ulang Dokumen</h4>
            <button type="button" onclick="toggleForm()" style="background: none; border: none; color: #888; cursor: pointer; font-size: 20px;"><i class="bi bi-x-lg"></i></button>
        </div>
        @endif

        <form action="{{ route('student.skpi.pembayaran.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Field: Pembayaran Wisuda --}}
            <div style="margin-bottom: 28px; padding-bottom: 24px; border-bottom: 1px solid #f0f0f0;">
                <label for="doc_pembayaran_wisuda" style="font-weight: 700; display: block; margin-bottom: 6px; color: #333; font-size: 14px;">
                   Bukti Pembayaran Wisuda
                </label>
                <p style="font-size: 12px; color: #888; margin-bottom: 10px;">Format PDF, Maks. 5MB. Pastikan terlihat jelas (nama, nominal, tanggal, dan stempel).</p>
                <input type="file" name="doc_pembayaran_wisuda" id="doc_pembayaran_wisuda" class="form-control" accept=".pdf" required
                    style="width: 100%; padding: 12px; border: 1px solid {{ $paymentStatus === 'rejected' ? '#ef9a9a' : '#ddd' }}; border-radius: 8px;"
                    @disabled(!$canEditRegistration)>
                @if($skpiRegistration && $skpiRegistration->doc_pembayaran_wisuda)
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px; padding: 8px 12px; background: #f5f5f5; border-radius: 8px; font-size: 13px;">
                        <span style="color: #555;">File sebelumnya:</span>
                        <a href="{{ asset('storage/' . $skpiRegistration->doc_pembayaran_wisuda) }}" target="_blank" style="color: #1565c0; font-weight: 600;">Lihat File</a>
                    </div>
                @endif
                @error('doc_pembayaran_wisuda')<small class="text-danger" style="color: #C62828;">{{ $message }}</small>@enderror
            </div>

            {{-- Field: Naskah Publikasi --}}
            <div style="margin-bottom: 24px;">
                <label for="doc_naskah_publikasi" style="font-weight: 700; display: block; margin-bottom: 6px; color: #333; font-size: 14px;">
                     Bukti Naskah Publikasi
                </label>
                <p style="font-size: 12px; color: #888; margin-bottom: 10px;">Format PDF, Maks. 5MB. Upload halaman judul atau bukti publikasi dari jurnal/prosiding.</p>
                <input type="file" name="doc_naskah_publikasi" id="doc_naskah_publikasi" class="form-control" accept=".pdf" required
                    style="width: 100%; padding: 12px; border: 1px solid {{ $paymentStatus === 'rejected' ? '#ef9a9a' : '#ddd' }}; border-radius: 8px;"
                    @disabled(!$canEditRegistration)>
                @if($skpiRegistration && $skpiRegistration->doc_naskah_publikasi)
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px; padding: 8px 12px; background: #f5f5f5; border-radius: 8px; font-size: 13px;">
                        <span style="color: #555;">File sebelumnya:</span>
                        <a href="{{ asset('storage/' . $skpiRegistration->doc_naskah_publikasi) }}" target="_blank" style="color: #1565c0; font-weight: 600;">Lihat File</a>
                    </div>
                @endif
                @error('doc_naskah_publikasi')<small class="text-danger" style="color: #C62828;">{{ $message }}</small>@enderror
            </div>

            @if($canEditRegistration)
            <div style="text-align: right; border-top: 1px solid #eee; padding-top: 20px; display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                @if(!$showForm)
                <button type="button" onclick="toggleForm()" style="background: none; border: none; color: #888; font-weight: 600; cursor: pointer; padding: 12px 16px;">
                    Batal
                </button>
                @endif
                <button type="submit" style="background: linear-gradient(135deg, #FF9800, #FF7043); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px;">
                    <i class="bi bi-upload"></i> Unggah Dokumen
                </button>
            </div>
            @else
            <div style="padding: 12px 16px; background: #f9f9f9; border-radius: 8px; font-size: 13px; color: #777; text-align: center;">
                <i class="bi bi-lock-fill"></i> Pengeditan tidak tersedia.
            </div>
            @endif
        </form>
    </div>

    <script>
        function toggleForm() {
            const form = document.getElementById('upload-form-container');
            const success = document.getElementById('success-state');
            
            if(form.style.display === 'none') {
                form.style.display = 'block';
                if(success) success.style.display = 'none';
            } else {
                form.style.display = 'none';
                if(success) success.style.display = 'block';
            }
        }
    </script>
@endsection
