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

    {{-- Status Banner Verifikasi Pembayaran --}}
    @php $paymentStatus = $skpiRegistration->payment_status ?? 'pending'; @endphp

    @if($paymentStatus === 'approved')
    <div style="background: #E8F5E9; border: 1px solid #a5d6a7; border-radius: 16px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 14px;">
        <i class="bi bi-patch-check-fill" style="color: #2e7d32; font-size: 22px; margin-top: 2px; flex-shrink: 0;"></i>
        <div>
            <strong style="color: #1b5e20; font-size: 15px;">Dokumen Sudah Terverifikasi</strong>
            <p style="margin: 4px 0 0; color: #388e3c; font-size: 13px;">Bukti pembayaran wisuda dan naskah publikasi Anda telah diverifikasi oleh Admin. Tidak perlu mengunggah ulang kecuali ada perubahan.</p>
        </div>
    </div>
    @elseif($paymentStatus === 'rejected')
    <div style="background: #FFEBEE; border: 1px solid #ef9a9a; border-radius: 16px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 14px;">
        <i class="bi bi-x-octagon-fill" style="color: #c62828; font-size: 22px; margin-top: 2px; flex-shrink: 0;"></i>
        <div>
            <strong style="color: #b71c1c; font-size: 15px;">Dokumen Ditolak — Perlu Upload Ulang</strong>
            @if($skpiRegistration->payment_approval_notes)
            <p style="margin: 4px 0 6px; color: #c62828; font-size: 13px;"><strong>Alasan:</strong> {{ $skpiRegistration->payment_approval_notes }}</p>
            @endif
            <p style="margin: 4px 0 0; color: #d32f2f; font-size: 13px;">Silakan unggah ulang dokumen yang benar di bawah ini, lalu klik <strong>Simpan Dokumen</strong>.</p>
        </div>
    </div>
    @elseif($skpiRegistration->doc_pembayaran_wisuda || $skpiRegistration->doc_naskah_publikasi)
    <div style="background: #FFF8E1; border: 1px solid #ffe082; border-radius: 16px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 14px;">
        <i class="bi bi-hourglass-split" style="color: #f57f17; font-size: 22px; margin-top: 2px; flex-shrink: 0;"></i>
        <div>
            <strong style="color: #e65100; font-size: 15px;">Menunggu Verifikasi Admin</strong>
            <p style="margin: 4px 0 0; color: #bf360c; font-size: 13px;">Dokumen Anda sudah diunggah dan sedang dalam antrian review oleh Admin. Anda masih bisa mengunggah ulang jika ada dokumen yang salah.</p>
        </div>
    </div>
    @endif

    <div class="form-card" style="background: white; border-radius: 20px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <form action="{{ route('student.skpi.pembayaran.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Field: Pembayaran Wisuda --}}
            <div style="margin-bottom: 28px; padding-bottom: 24px; border-bottom: 1px solid #f0f0f0;">
                <label for="doc_pembayaran_wisuda" style="font-weight: 700; display: block; margin-bottom: 6px; color: #333; font-size: 14px;">
                   Bukti Pembayaran Wisuda
                </label>
                <p style="font-size: 12px; color: #888; margin-bottom: 10px;">Format PDF, Maks. 5MB. Pastikan terlihat jelas (nama, nominal, tanggal, dan stempel).</p>
                <input type="file" name="doc_pembayaran_wisuda" id="doc_pembayaran_wisuda" class="form-control" accept=".pdf"
                    style="width: 100%; padding: 12px; border: 1px solid {{ $paymentStatus === 'rejected' ? '#ef9a9a' : '#ddd' }}; border-radius: 8px;"
                    @disabled(!$canEditRegistration)>
                @if($skpiRegistration->doc_pembayaran_wisuda)
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px; padding: 8px 12px; background: #f5f5f5; border-radius: 8px; font-size: 13px;">
                      
                        <span style="color: #555;">File saat ini:</span>
                        <a href="{{ asset('storage/' . $skpiRegistration->doc_pembayaran_wisuda) }}" target="_blank" style="color: #1565c0; font-weight: 600;">Lihat File</a>
                        @if($paymentStatus === 'rejected')
                        <span class="badge bg-danger ms-auto" style="font-size: 11px;">Ditolak</span>
                        @elseif($paymentStatus === 'approved')
                        <span class="badge bg-success ms-auto" style="font-size: 11px;"><i class="bi bi-check-circle"></i> Terverifikasi</span>
                        @else
                        <span class="badge bg-warning text-dark ms-auto" style="font-size: 11px;">Menunggu Review</span>
                        @endif
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
                <input type="file" name="doc_naskah_publikasi" id="doc_naskah_publikasi" class="form-control" accept=".pdf"
                    style="width: 100%; padding: 12px; border: 1px solid {{ $paymentStatus === 'rejected' ? '#ef9a9a' : '#ddd' }}; border-radius: 8px;"
                    @disabled(!$canEditRegistration)>
                @if($skpiRegistration->doc_naskah_publikasi)
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px; padding: 8px 12px; background: #f5f5f5; border-radius: 8px; font-size: 13px;">
                        <span style="color: #555;">File saat ini:</span>
                        <a href="{{ asset('storage/' . $skpiRegistration->doc_naskah_publikasi) }}" target="_blank" style="color: #1565c0; font-weight: 600;">Lihat File</a>
                        @if($paymentStatus === 'rejected')
                        <span class="badge bg-danger ms-auto" style="font-size: 11px;">Ditolak</span>
                        @elseif($paymentStatus === 'approved')
                        <span class="badge bg-success ms-auto" style="font-size: 11px;"><i class="bi bi-check-circle"></i> Terverifikasi</span>
                        @else
                        <span class="badge bg-warning text-dark ms-auto" style="font-size: 11px;">Menunggu Review</span>
                        @endif
                    </div>
                @endif
                @error('doc_naskah_publikasi')<small class="text-danger" style="color: #C62828;">{{ $message }}</small>@enderror
            </div>

            @if($canEditRegistration)
            <div style="text-align: right; border-top: 1px solid #eee; padding-top: 20px; display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                @if($paymentStatus === 'rejected')
                <span style="font-size: 13px; color: #c62828;"><i class="bi bi-info-circle"></i> Unggah ulang kedua dokumen yang telah diperbaiki.</span>
                @endif
                <button type="submit" style="background: linear-gradient(135deg, #FF9800, #FF7043); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px;">
                    <i class="bi bi-upload"></i> Simpan Dokumen
                </button>
            </div>
            @else
            <div style="padding: 12px 16px; background: #f9f9f9; border-radius: 8px; font-size: 13px; color: #777; text-align: center;">
                <i class="bi bi-lock-fill"></i> Pengeditan tidak tersedia saat status pengajuan sedang diproses.
            </div>
            @endif
        </form>
    </div>
@endsection
