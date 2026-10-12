{{-- MODAL 2: Detail Prestasi --}}
<div id="detailModal" class="modal" style="display: none; z-index: 20010 !important;">
    <div class="modal-content modal-detail">
        <div class="modal-hdr">
            <div>
                <h4 class="modal-hdr-title">Detail Prestasi Mahasiswa</h4>
                <p class="modal-hdr-sub" id="detail_nama"></p>
            </div>
            <button type="button" onclick="closeDetailModal()" class="modal-close-btn">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <div class="modal-body-pad">
            <div class="detail-info-grid">
                <div class="detail-info-item">
                    <span class="detail-info-label">Kategori</span>
                    <strong id="detail_kategori" class="detail-info-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Jenis Kegiatan</span>
                    <strong id="detail_kegiatan" class="detail-info-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Nama Acara / Kegiatan</span>
                    <strong id="detail_event" class="detail-info-value" style="color: #1D4ED8;"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Diselenggarakan Oleh</span>
                    <strong id="detail_organizer" class="detail-info-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Tahun Kegiatan</span>
                    <strong id="detail_event_year" class="detail-info-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Semester Kegiatan</span>
                    <strong id="detail_semester" class="detail-info-value" style="color: #0284C7;"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Tingkat</span>
                    <strong id="detail_tingkat" class="detail-info-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Peran / Jabatan</span>
                    <strong id="detail_peran" class="detail-info-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Poin SKP</span>
                    <strong id="detail_skp" class="detail-info-value skp-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Tanggal Diajukan</span>
                    <strong id="detail_tanggal" class="detail-info-value"></strong>
                </div>
            </div>

            <div class="detail-section">
                <span class="detail-section-label">File Bukti / Piagam</span>
                <div id="detail_file_container" class="detail-file-box">
                    <!-- Diisi via JS -->
                </div>
            </div>

            <div class="detail-section">
                <span class="detail-section-label">Catatan Reviewer</span>
                <div class="detail-notes-box">
                    <div class="detail-notes-inner">
                        <i class="bi bi-chat-left-text detail-notes-icon"></i>
                        <div>
                            <p id="detail_catatan" class="detail-catatan-text"></p>
                            <p class="detail-reviewer-text">Reviewer: <span id="detail_reviewer" style="font-weight: 400;"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="detail_action_container" class="detail-action-bar">
                <!-- Diisi via JS -->
            </div>
        </div>
    </div>
</div>
