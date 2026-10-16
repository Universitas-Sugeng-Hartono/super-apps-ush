{{-- =========================================================================
     MODAL DETAIL MAHASISWA & MONITORING AKADEMIK (SHARED COMPONENT)
     Universitas Sugeng Hartono - Biro Akademik
     File: resources/views/akademik/script.blade.php
     ========================================================================= --}}

<!-- Modal Detail Mahasiswa Read-Only (Relocated directly to body via JS to avoid z-index/clipping conflicts) -->
<div class="custom-modal-backdrop" id="modalStudentDetailShared" style="display: none;" aria-hidden="true" role="dialog">
    <div class="custom-modal-dialog">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <div class="custom-modal-title-box">
                    <h5 class="custom-modal-title" id="m-nama">-</h5>
                    <p class="custom-modal-subtitle" id="m-nim">-</p>
                </div>
                <button type="button" class="btn-close-custom" onclick="closeStudentModal()" aria-label="Tutup" title="Tutup Modal (Esc)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="custom-modal-body">
                <div class="modal-grid-detail">
                    <div class="m-field">
                        <label>Program Studi</label>
                        <div class="m-val" id="m-prodi">-</div>
                    </div>
                    <div class="m-field">
                        <label>Periode Masuk / Angkatan</label>
                        <div class="m-val" id="m-periode">-</div>
                    </div>
                    <div class="m-field">
                        <label>Status Penetapan</label>
                        <div class="m-val" id="m-status-wrap">-</div>
                    </div>
                    <div class="m-field">
                        <label>Beasiswa (%)</label>
                        <div class="m-val font-weight-bold" id="m-beasiswa">-</div>
                    </div>
                    <div class="m-field">
                        <label>Semester</label>
                        <div class="m-val" id="m-semester">-</div>
                    </div>
                    <div class="m-field">
                        <label>Nilai IPK (KHS SIAKAD)</label>
                        <div class="m-val font-weight-bold text-primary" id="m-ipk">-</div>
                    </div>
                    <div class="m-field">
                        <label>Dosen Pembimbing Akademik</label>
                        <div class="m-val" id="m-dosen-pa">-</div>
                    </div>
                    <div class="m-field">
                        <label>Status Mahasiswa</label>
                        <div class="m-val" id="m-status-mahasiswa">-</div>
                    </div>
                    <div class="m-field">
                        <label>Jenis Kelamin</label>
                        <div class="m-val" id="m-gender">-</div>
                    </div>
                    <div class="m-field">
                        <label>No. Telepon / WhatsApp</label>
                        <div class="m-val" id="m-telepon">-</div>
                    </div>
                    <div class="m-field full-width">
                        <label>Email</label>
                        <div class="m-val" id="m-email">-</div>
                    </div>
                    <div class="m-field full-width">
                        <label>Alamat Domisili</label>
                        <div class="m-val" id="m-alamat">-</div>
                    </div>
                    <div class="m-field full-width">
                        <label>Catatan / Rekam Prestasi</label>
                        <div class="m-val text-muted fst-italic" id="m-notes">-</div>
                    </div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-dismiss" onclick="closeStudentModal()">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* =========================================================================
       CUSTOM MODAL STYLES (Escapes .container stacking context)
       ========================================================================= */
    .custom-modal-backdrop {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.72) !important;
        backdrop-filter: blur(6px) !important;
        -webkit-backdrop-filter: blur(6px) !important;
        z-index: 999999 !important; /* Di atas header-section (z-index 9998) */
        display: none;
        align-items: center;
        justify-content: center;
        padding: 50px 20px 30px !important;
        box-sizing: border-box !important;
        overflow-y: auto !important;
    }
    .custom-modal-dialog {
        width: 100% !important;
        max-width: 800px !important;
        margin: auto !important;
        display: flex !important;
        max-height: calc(100vh - 80px) !important;
        animation: modalZoomIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
        position: relative !important;
        z-index: 1000000 !important;
    }
    @keyframes modalZoomIn {
        from { opacity: 0; transform: translateY(24px) scale(0.96); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .custom-modal-content {
        background: #ffffff !important;
        border-radius: 20px !important;
        width: 100% !important;
        max-height: calc(100vh - 80px) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.45) !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
    }
    .custom-modal-header {
        padding: 20px 26px 16px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        border-bottom: 1px solid #E2E8F0 !important;
        background: #ffffff !important;
        flex-shrink: 0 !important;
    }
    .custom-modal-title-box {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .custom-modal-title {
        font-size: 20px !important;
        font-weight: 800 !important;
        color: #1E293B !important;
        margin: 0 !important;
        line-height: 1.2 !important;
    }
    .custom-modal-subtitle {
        font-size: 13.5px !important;
        color: #64748B !important;
        margin: 0 !important;
        font-weight: 600 !important;
    }
    .btn-close-custom {
        width: 40px !important;
        height: 40px !important;
        border-radius: 12px !important;
        border: 1px solid #CBD5E1 !important;
        background: #F8FAFC !important;
        color: #334155 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        padding: 0 !important;
        font-size: 17px !important;
        flex-shrink: 0 !important;
    }
    .btn-close-custom:hover {
        background: #FEE2E2 !important;
        color: #DC2626 !important;
        border-color: #FECACA !important;
        transform: scale(1.08) !important;
    }
    .custom-modal-body {
        padding: 20px 26px 24px !important;
        overflow-y: auto !important;
        flex: 1 !important;
    }
    .custom-modal-footer {
        padding: 14px 26px !important;
        background: #F8FAFC !important;
        border-top: 1px solid #F1F5F9 !important;
        display: flex !important;
        justify-content: flex-end !important;
        flex-shrink: 0 !important;
    }
    .btn-modal-dismiss {
        background: #64748B;
        color: white;
        border: none;
        border-radius: 10px;
        padding: 9px 22px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-modal-dismiss:hover {
        background: #475569;
        color: white;
    }
    .modal-grid-detail {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 14px !important;
        background: #F8FAFC !important;
        padding: 18px !important;
        border-radius: 14px !important;
        border: 1px solid #E2E8F0 !important;
    }
    .m-field {
        display: flex !important;
        flex-direction: column !important;
        gap: 4px !important;
    }
    .m-field.full-width { grid-column: 1 / -1 !important; }
    .m-field label {
        font-size: 11.5px !important;
        font-weight: 700 !important;
        color: #64748B !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
        margin: 0 !important;
    }
    .m-val {
        font-size: 13.5px !important;
        color: #1E293B !important;
        background: white !important;
        padding: 8px 12px !important;
        border-radius: 8px !important;
        border: 1px solid #CBD5E1 !important;
        min-height: 38px !important;
        display: flex !important;
        align-items: center !important;
        line-height: 1.4 !important;
    }

    @media (max-width: 600px) {
        .custom-modal-backdrop { padding: 15px 10px !important; }
        .custom-modal-dialog { max-height: calc(100vh - 20px) !important; }
        .custom-modal-content { max-height: calc(100vh - 20px) !important; }
        .custom-modal-header { padding: 16px 18px 12px !important; }
        .custom-modal-body { padding: 16px 18px !important; }
        .modal-grid-detail { grid-template-columns: 1fr !important; }
    }
</style>

<script>
    // Pindahkan modal langsung ke <body> agar terbebas dari .container stacking context
    function relocateStudentModalToBody() {
        const modal = document.getElementById('modalStudentDetailShared');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', relocateStudentModalToBody);
    } else {
        relocateStudentModalToBody();
    }

    function openStudentModal(data) {
        relocateStudentModalToBody();

        document.getElementById('m-nama').innerText = data.nama || '-';
        document.getElementById('m-nim').innerText = 'NIM: ' + (data.nim || '-');
        document.getElementById('m-prodi').innerText = data.prodi || '-';
        document.getElementById('m-periode').innerText = (data.tanggal_masuk || '-') + ' (' + (data.angkatan || '-') + ')';
        
        const statusWrap = document.getElementById('m-status-wrap');
        if (data.status_is_ambassador) {
            statusWrap.innerHTML = '<span class="status-badge-ambassador" style="background:#FFFBEB;color:#B45309;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:5px;border:1px solid #FCD34D;"><i class="bi bi-star-fill"></i> ' + (data.status || 'Student Ambassador') + '</span>';
        } else {
            statusWrap.innerHTML = '<span class="status-badge-mahasiswa" style="background:#F1F5F9;color:#475569;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:5px;border:1px solid #E2E8F0;"><i class="bi bi-person"></i> ' + (data.status || 'Mahasiswa Reguler') + '</span>';
        }

        document.getElementById('m-beasiswa').innerText = data.beasiswa || '0%';
        document.getElementById('m-semester').innerText = data.semester || '-';
        document.getElementById('m-ipk').innerText = data.ipk || '-';
        document.getElementById('m-dosen-pa').innerText = data.dosen_pa || '-';
        document.getElementById('m-status-mahasiswa').innerText = data.status_mahasiswa || 'Aktif';
        document.getElementById('m-gender').innerText = data.gender || '-';
        document.getElementById('m-telepon').innerText = data.telepon || '-';
        document.getElementById('m-email').innerText = data.email || '-';
        document.getElementById('m-alamat').innerText = data.alamat || '-';
        document.getElementById('m-notes').innerText = data.notes || 'Belum ada catatan.';

        const modalEl = document.getElementById('modalStudentDetailShared');
        if (modalEl) {
            modalEl.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeStudentModal() {
        const modalEl = document.getElementById('modalStudentDetailShared');
        if (modalEl) {
            modalEl.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // Klik di luar area dialog (backdrop) untuk menutup
    document.addEventListener('click', function(e) {
        const modalEl = document.getElementById('modalStudentDetailShared');
        if (modalEl && e.target === modalEl) {
            closeStudentModal();
        }
    });

    // Tekan tombol Escape untuk menutup
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStudentModal();
        }
    });
</script>
