{{-- MODAL PREVIEW UTAMA: Khusus Mahasiswa Biasa (Non-Beasiswa) --}}
<div id="regularStudentAchievementsModal" class="modal" style="display: none; z-index: 10001 !important;">
    <div class="modal-content modal-achievements">
        <!-- Modal Header -->
        <div class="modal-hdr">
            <div>
                <h4 class="modal-hdr-title">Daftar Prestasi Mahasiswa</h4>
                <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px; flex-wrap: wrap;">
                    <p class="modal-hdr-sub" id="regular_achievements_nama" style="margin: 0; font-weight: 600;"></p>
                    <span class="badge-status-mhs badge-reguler" style="font-size: 11px; padding: 2px 8px;">Mahasiswa Reguler</span>
                </div>
            </div>
            <button type="button" onclick="closeRegularStudentAchievementsModal()" class="modal-close-btn">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <!-- Modal Body (Pure Table - Template Utama) -->
        <div class="modal-body-scroll">
            <div class="table-responsive" id="regular_achievements_table_container">
                <!-- Diisi via JS -->
            </div>
        </div>
    </div>
</div>
