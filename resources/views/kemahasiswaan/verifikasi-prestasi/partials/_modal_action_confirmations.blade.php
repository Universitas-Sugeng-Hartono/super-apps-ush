{{-- MODAL 3: Approve --}}
<div id="approveModal" class="modal" style="display: none; z-index: 20050 !important;">
    <div class="modal-content modal-form">
        <div class="modal-hdr" style="padding: 0 0 14px; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: transparent;">
            <h4 style="margin: 0; color: #166534; font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-check-circle-fill" style="color: #16A34A; font-size: 20px;"></i> Setujui Prestasi Mahasiswa
            </h4>
            <button type="button" onclick="closeApproveModal()" style="background: #F1F5F9; border: 1px solid #E2E8F0; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; font-size: 16px;" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="approveForm" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="approve_notes" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Catatan Verifikasi (Opsional)</label>
                <textarea id="approve_notes" name="approval_notes" class="form-control textarea-control" rows="3" placeholder="Tambahkan catatan verifikasi jika diperlukan..."></textarea>
            </div>
            <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" class="btn-cancel" onclick="closeApproveModal()" style="padding: 8px 16px; border-radius: 8px; font-weight: 600; background: #F1F5F9; border: 1px solid #CBD5E1; color: #475569; cursor: pointer;">Batal</button>
                <button type="submit" class="btn-approve confirm" style="padding: 8px 18px; border-radius: 8px; font-weight: 600; background: #16A34A; border: none; color: white; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-check-circle"></i> Setujui Prestasi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: Reject --}}
<div id="rejectModal" class="modal" style="display: none; z-index: 20050 !important;">
    <div class="modal-content modal-form">
        <div class="modal-hdr" style="padding: 0 0 14px; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: transparent;">
            <h4 style="margin: 0; color: #991B1B; font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-x-circle-fill" style="color: #DC2626; font-size: 20px;"></i> Tolak Prestasi Mahasiswa
            </h4>
            <button type="button" onclick="closeRejectModal()" style="background: #F1F5F9; border: 1px solid #E2E8F0; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; font-size: 16px;" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="reject_notes" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Alasan Penolakan <span class="text-danger">*</span></label>
                <textarea id="reject_notes" name="approval_notes" class="form-control textarea-control" rows="3" required placeholder="Tuliskan alasan penolakan agar mahasiswa bisa memperbaiki data..."></textarea>
            </div>
            <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" class="btn-cancel" onclick="closeRejectModal()" style="padding: 8px 16px; border-radius: 8px; font-weight: 600; background: #F1F5F9; border: 1px solid #CBD5E1; color: #475569; cursor: pointer;">Batal</button>
                <button type="submit" class="btn-reject confirm" style="padding: 8px 18px; border-radius: 8px; font-weight: 600; background: #DC2626; border: none; color: white; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-x-circle"></i> Tolak Prestasi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 5: Approve All --}}
<div id="approveAllModal" class="modal" style="display: none; z-index: 20050 !important;">
    <div class="modal-content modal-form">
        <div class="modal-hdr" style="padding: 0 0 14px; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: transparent;">
            <h4 style="margin: 0; color: #166534; font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-check-all" style="color: #16A34A; font-size: 22px;"></i> Approve Semua Prestasi Mahasiswa
            </h4>
            <button type="button" onclick="closeApproveAllModal()" style="background: #F1F5F9; border: 1px solid #E2E8F0; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; font-size: 16px;" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <p style="font-size: 13px; color: #64748B; margin-bottom: 14px;">Anda akan menyetujui seluruh data prestasi berstatus <strong>Pending</strong> untuk mahasiswa yang dipilih.</p>
        <form id="approveAllForm" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="approve_all_notes" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Catatan Verifikasi (Opsional)</label>
                <textarea id="approve_all_notes" name="approval_notes" class="form-control textarea-control" rows="3" placeholder="Tambahkan catatan verifikasi jika diperlukan..."></textarea>
            </div>
            <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" class="btn-cancel" onclick="closeApproveAllModal()" style="padding: 8px 16px; border-radius: 8px; font-weight: 600; background: #F1F5F9; border: 1px solid #CBD5E1; color: #475569; cursor: pointer;">Batal</button>
                <button type="submit" class="btn-approve confirm" style="padding: 8px 18px; border-radius: 8px; font-weight: 600; background: #16A34A; border: none; color: white; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-check-all"></i> Setujui Semua
                </button>
            </div>
        </form>
    </div>
</div>
