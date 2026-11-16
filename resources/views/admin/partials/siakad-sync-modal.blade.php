@if(!empty($siakadLastSyncSummary))
<!-- Modal Notifikasi Sinkronisasi SIAKAD -->
<div class="modal fade" id="modalSiakadSyncSummary" tabindex="-1" aria-labelledby="modalSiakadSyncSummaryLabel" aria-hidden="true" style="z-index: 100000 !important;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px; z-index: 100001 !important; pointer-events: auto !important;">
        <div class="modal-content" style="border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1); overflow: hidden; pointer-events: auto !important;">
            
            <!-- Header: Bersih & Minimalis -->
            <div class="modal-header" style="background: #1E293B; color: #FFFFFF; padding: 16px 20px; border-bottom: 1px solid #334155;">
                <div class="flex-grow-1">
                    <h5 class="modal-title fw-bold mb-1 text-white" id="modalSiakadSyncSummaryLabel" style="font-size: 16px;">
                        Hasil Sinkronisasi SIAKAD
                    </h5>
                    <div style="font-size: 12px; color: #94A3B8;">
                        {{ $siakadLastSyncSummary['source'] ?? 'Penarikan Otomatis (01:00 WIB)' }} • {{ $siakadLastSyncSummary['synced_at'] ?? '-' }}
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss-sync-modal aria-label="Tutup" style="cursor: pointer; pointer-events: auto !important;"></button>
            </div>

            <!-- Body: Data Rapi Tanpa Ikon Dekoratif Berlebihan -->
            <div class="modal-body" style="padding: 20px; background: #FFFFFF; pointer-events: auto !important;">
                @if(($siakadLastSyncSummary['status'] ?? 'success') === 'success')
                    <!-- Dua Kartu Ringkasan -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px 14px;">
                                <div style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">
                                    Semester & Nilai IPK
                                </div>
                                <div style="font-size: 24px; font-weight: 700; color: #0284C7; margin: 4px 0 2px;">
                                    {{ number_format($siakadLastSyncSummary['academic_updated'] ?? 0) }}
                                </div>
                                <div style="font-size: 11.5px; color: #475569;">
                                    Mahasiswa diperbarui
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px 14px;">
                                <div style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">
                                    Mahasiswa Baru
                                </div>
                                <div style="font-size: 24px; font-weight: 700; color: #059669; margin: 4px 0 2px;">
                                    {{ number_format($siakadLastSyncSummary['new_students'] ?? 0) }}
                                </div>
                                <div style="font-size: 11.5px; color: #475569;">
                                    {{ $siakadLastSyncSummary['new_beasiswa'] ?? 0 }} beasiswa, {{ $siakadLastSyncSummary['new_reguler'] ?? 0 }} reguler
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Ringkas Data -->
                    <div style="border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; font-size: 12.5px;">
                        <div style="display: flex; justify-content: space-between; padding: 9px 14px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0;">
                            <span style="color: #64748B;">Total Mahasiswa Diperiksa:</span>
                            <span style="font-weight: 600; color: #1E293B;">{{ number_format($siakadLastSyncSummary['total_checked'] ?? 0) }} Mahasiswa</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 9px 14px; background: #FFFFFF;">
                            <span style="color: #64748B;">Waktu Pemrosesan:</span>
                            <span style="font-weight: 600; color: #1E293B;">{{ $siakadLastSyncSummary['duration_seconds'] ?? 0 }} Detik</span>
                        </div>
                    </div>
                @else
                    <div style="background: #FEF2F2; border: 1px solid #FCA5A5; color: #991B1B; border-radius: 8px; padding: 14px; font-size: 13px;">
                        <strong>Sinkronisasi Gagal:</strong>
                        <div style="margin-top: 4px; font-size: 12px;">
                            {{ $siakadLastSyncSummary['error_message'] ?? 'Terjadi kendala koneksi ke server SIAKAD.' }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer: Tombol Tutup Sederhana -->
            <div class="modal-footer" style="padding: 12px 20px; background: #F8FAFC; border-top: 1px solid #E2E8F0; justify-content: flex-end; pointer-events: auto !important;">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" data-dismiss-sync-modal style="font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; pointer-events: auto !important;">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    #modalSiakadSyncSummary {
        z-index: 100000 !important;
    }
    #modalSiakadSyncSummary .modal-dialog {
        z-index: 100001 !important;
        pointer-events: auto !important;
    }
    #modalSiakadSyncSummary .modal-content {
        pointer-events: auto !important;
    }
    .modal-backdrop.show {
        z-index: 99990 !important;
    }
</style>

<script>
    (function () {
        const syncId = "{{ $siakadLastSyncSummary['sync_id'] ?? '' }}";

        // Fungsi tutup modal yang 100% fail-safe
        window.closeSiakadSyncModal = function () {
            if (syncId) {
                try {
                    localStorage.setItem("siakad_sync_seen_" + syncId, "1");
                } catch (e) {}
            }

            const modalEl = document.getElementById("modalSiakadSyncSummary");
            if (!modalEl) return;

            // 1. Coba tutup via Bootstrap jika instance tersedia
            try {
                if (window.bootstrap && window.bootstrap.Modal) {
                    const inst = window.bootstrap.Modal.getInstance(modalEl);
                    if (inst) {
                        inst.hide();
                    }
                }
            } catch (err) {}

            // 2. Direct DOM cleanup fallback agar PASTI tertutup
            modalEl.classList.remove("show");
            modalEl.style.display = "none";
            modalEl.setAttribute("aria-hidden", "true");
            document.body.classList.remove("modal-open");
            document.body.style.removeProperty("overflow");
            document.body.style.removeProperty("padding-right");

            // Bersihkan backdrop jika masih tertinggal
            document.querySelectorAll(".modal-backdrop").forEach(function (backdrop) {
                backdrop.remove();
            });
        };

        // Fungsi buka modal secara manual (misal saat badge di header diklik)
        window.openSiakadSyncModal = function () {
            const modalEl = document.getElementById("modalSiakadSyncSummary");
            if (!modalEl) return;

            // Pastikan modal menempel di document.body agar tidak terhalang stacking context container
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }

            try {
                if (window.bootstrap && window.bootstrap.Modal) {
                    const inst = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
                    inst.show();
                } else {
                    modalEl.style.display = "block";
                    modalEl.classList.add("show");
                }
            } catch (e) {
                modalEl.style.display = "block";
                modalEl.classList.add("show");
            }
        };

        function initSyncModal() {
            const modalEl = document.getElementById("modalSiakadSyncSummary");
            if (!modalEl) return;

            // Pindahkan modal langsung ke <body> agar terbebas dari .container { z-index: 1; position: relative; }
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }

            // Pasang event listener ke tombol-tombol dismiss
            modalEl.querySelectorAll("[data-dismiss-sync-modal], .btn-close, .btn-secondary").forEach(function (btn) {
                btn.addEventListener("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.closeSiakadSyncModal();
                });
            });

            // Klik di luar area modal (backdrop) untuk menutup
            modalEl.addEventListener("click", function (e) {
                if (e.target === modalEl) {
                    window.closeSiakadSyncModal();
                }
            });

            // Tekan tombol Escape untuk menutup
            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape" && modalEl.style.display !== "none") {
                    window.closeSiakadSyncModal();
                }
            });

            // Cek apakah sudah pernah dilihat sebelumnya
            let hasSeen = false;
            try {
                hasSeen = localStorage.getItem("siakad_sync_seen_" + syncId) === "1";
            } catch (e) {}

            if (!hasSeen) {
                setTimeout(function () {
                    window.openSiakadSyncModal();
                }, 300);
            }
        }

        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initSyncModal);
        } else {
            initSyncModal();
        }
    })();
</script>
@endif
