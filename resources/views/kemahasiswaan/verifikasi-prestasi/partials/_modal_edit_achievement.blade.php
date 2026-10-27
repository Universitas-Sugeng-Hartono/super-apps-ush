{{-- MODAL 6: Edit / Sunting Data Sertifikat Mahasiswa --}}
<div id="editAchievementModal" class="modal" style="display: none; z-index: 20020 !important;">
    <div class="modal-content modal-form" style="max-width: 650px; width: 90%; max-height: 90vh; overflow-y: auto; text-align: left;">
        <div class="modal-hdr" style="padding-bottom: 12px; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h4 class="modal-hdr-title" style="margin: 0; color: #1E293B; font-size: 18px; font-weight: 700; text-align: left;">
                    <i class="bi bi-pencil-square" style="color: #2563EB;"></i> Sunting / Lengkapi Data Prestasi
                </h4>
                <p class="modal-hdr-sub" id="edit_modal_subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px; text-align: left;"></p>
            </div>
            <button type="button" onclick="closeEditAchievementModal()" class="modal-close-btn">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <form id="editAchievementForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_category" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Kategori SKPI *</label>
                    <select id="edit_category" name="category" class="form-control" required onchange="onEditCategoryChange()">
                        @foreach($categoryOptions as $catVal => $catLabel)
                        <option value="{{ $catVal }}">{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_activity_type" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Jenis Kegiatan *</label>
                    <select id="edit_activity_type" name="activity_type" class="form-control" required onchange="onEditActivityTypeChange()">
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 14px; margin-bottom: 0;">
                <label for="edit_event" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Nama Acara / Kegiatan *</label>
                <input type="text" id="edit_event" name="event" class="form-control" required placeholder="Contoh: Seminar Nasional Teknologi 2026">
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px; margin-top: 14px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_organizer" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Penyelenggara *</label>
                    <input type="text" id="edit_organizer" name="organizer" class="form-control" required placeholder="Contoh: BEM USH / Kemendikbud">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_event_year" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Tahun Kegiatan *</label>
                    <input type="text" id="edit_event_year" name="event_year" class="form-control" required placeholder="Contoh: 2025">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_semester" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Semester</label>
                    <select id="edit_semester" name="semester" class="form-control">
                        <option value="">-</option>
                        @for($s = 1; $s <= 14; $s++)
                        <option value="{{ $s }}">Sem {{ $s }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_level" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Tingkat Kegiatan *</label>
                    <select id="edit_level" name="level" class="form-control" required onchange="onEditLevelOrRoleChange()">
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_participation_role" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Peran / Posisi / Juara *</label>
                    <select id="edit_participation_role" name="participation_role" class="form-control" required onchange="onEditLevelOrRoleChange()">
                    </select>
                </div>
            </div>

            <div id="edit_custom_point_container" class="form-group" style="display: none; margin-top: 14px; margin-bottom: 0;">
                <label for="edit_custom_skp_points" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Nilai Poin POKEMA (Range 15 - 75) *
                </label>
                <input type="number" id="edit_custom_skp_points" name="skp_points" class="form-control" min="15" max="75" placeholder="Contoh: 75" oninput="onEditCustomPointChange()">
                <small style="color: #64748B; font-size: 12px;">Khusus kegiatan Penerimaan Mahasiswa Baru, masukkan nilai antara 15 hingga 75 poin.</small>
            </div>

            <div style="margin-top: 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 13px; color: #475569; font-weight: 600;">Kalkulasi Poin SKP:</span>
                <span id="edit_skp_badge" style="background: #2563EB; color: white; padding: 4px 12px; border-radius: 999px; font-weight: 700; font-size: 13px;">0 SKP</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_status" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Status Verifikasi *</label>
                    <select id="edit_status" name="status" class="form-control" required>
                        <option value="approved">Approved (Disetujui)</option>
                        <option value="pending">Pending (Menunggu)</option>
                        <option value="rejected">Rejected (Ditolak)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_certificate" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Ganti File Bukti / Sertifikat (Opsional)</label>
                    <input type="file" id="edit_certificate" name="certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <div id="edit_current_file_preview" style="font-size: 12px; margin-top: 4px;"></div>
                </div>
            </div>

            <div class="form-group" style="margin-top: 14px; margin-bottom: 0;">
                <label for="edit_approval_notes" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Catatan Reviewer / Catatan Revisi (Opsional)</label>
                <textarea id="edit_approval_notes" name="approval_notes" class="form-control textarea-control" rows="3" placeholder="Catatan untuk mahasiswa atau alasan perubahan data..."></textarea>
            </div>

            <div class="modal-actions" style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-cancel" onclick="closeEditAchievementModal()">Batal</button>
                <button type="submit" class="btn-approve confirm" style="background: #2563EB;">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
