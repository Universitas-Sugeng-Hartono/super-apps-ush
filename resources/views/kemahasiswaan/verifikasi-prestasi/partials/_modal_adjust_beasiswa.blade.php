{{-- MODAL 7: Penyesuaian Status Beasiswa Mahasiswa --}}
<div id="adjustBeasiswaModal" class="modal" style="display: none; position: fixed !important; top: 80px !important; left: 0 !important; right: 0 !important; bottom: 0 !important; inset: 80px 0 0 0 !important; z-index: 20030 !important; background: rgba(15, 23, 42, 0.65) !important; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); overflow-y: auto !important; padding: 24px 16px 50px !important; justify-content: center !important; align-items: flex-start !important;">
    <div class="modal-content modal-form" style="max-width: 580px; width: 100%; max-height: calc(100vh - 140px); display: flex; flex-direction: column; overflow: hidden; margin: 0 auto; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); background: #FFFFFF; border: 1px solid #E2E8F0;">
        <div class="modal-hdr" style="padding: 16px 20px 14px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; background: #FFFFFF;">
            <div>
                <h4 class="modal-hdr-title" id="adjust_modal_title" style="margin: 0; font-size: 16.5px; font-weight: 700; color: #1E293B;">
                    <i class="bi bi-award-fill text-warning"></i> Penyesuaian Beasiswa Mahasiswa
                </h4>
                <p class="modal-hdr-sub" id="adjust_modal_subtitle" style="margin: 3px 0 0; font-size: 12px; color: #64748B;">
                    Verifikasi kelayakan dan perbarui status beasiswa berdasarkan capaian POKEMA & IPK.
                </p>
            </div>
            <button type="button" onclick="closeAdjustBeasiswaModal()" style="background: #F1F5F9; border: 1px solid #E2E8F0; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; font-size: 18px; transition: all 0.2s; flex-shrink: 0;" onmouseover="this.style.background='#E2E8F0'; this.style.color='#0F172A';" onmouseout="this.style.background='#F1F5F9'; this.style.color='#475569';" aria-label="Tutup Modal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="adjustBeasiswaForm" method="POST" style="overflow-y: auto; padding: 16px 20px 20px; flex: 1 1 auto; max-height: calc(100vh - 220px);">
            @csrf
            <input type="hidden" id="adjust_sk_number" name="sk_number" value="2638/SK/01/2026">

            <div class="adjust-meta-box" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 14px; margin-bottom: 12px;">
                <!-- Header Info Mahasiswa & Periode -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                    <div style="font-size: 13px; color: #1E293B;">
                        <span style="color: #64748B; font-size: 12.5px;">Mahasiswa:</span> <strong id="adjust_mhs_nama">-</strong> 
                        (<span id="adjust_mhs_nim" class="font-monospace" style="color: #475569;">-</span>)
                    </div>
                    <div id="adjust_mhs_periode_badge" style="font-size: 11px; background: #EEF2FF; color: #4F46E5; border: 1px solid #C7D2FE; padding: 2px 8px; border-radius: 12px; font-weight: 600;">
                        <i class="bi bi-calendar-event"></i> <span id="adjust_mhs_periode">Tahun I</span>
                    </div>
                </div>

                <!-- Metrik Nyata (IPK & Poin POKEMA) -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; gap: 8px;">
                        <div style="width: 30px; height: 30px; border-radius: 6px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0;">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div>
                            <div style="font-size: 10.5px; color: #64748B; text-transform: uppercase; font-weight: 600;">IPK Mahasiswa</div>
                            <div id="adjust_mhs_ipk" style="font-size: 14px; font-weight: 700; color: #1E293B;">-</div>
                        </div>
                    </div>

                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; gap: 8px;">
                        <div style="width: 30px; height: 30px; border-radius: 6px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0;">
                            <i class="bi bi-trophy-fill"></i>
                        </div>
                        <div>
                            <div style="font-size: 10.5px; color: #64748B; text-transform: uppercase; font-weight: 600;">Poin POKEMA</div>
                            <div id="adjust_mhs_poin" style="font-size: 14px; font-weight: 700; color: #1E293B;">-</div>
                        </div>
                    </div>
                </div>

                <!-- Komparasi Status Saat Ini vs Rekomendasi Realtime -->
                <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 10px; align-items: center; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 10px 14px; margin-bottom: 10px;">
                    <div>
                        <span style="font-size: 10px; color: #64748B; display: block; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px;">Status Saat Ini</span>
                        <div id="adjust_old_beasiswa_display" style="font-size: 14px; font-weight: 700; color: #1E293B; margin-top: 2px;">
                            <span id="adjust_old_beasiswa">0%</span>
                        </div>
                    </div>
                    <div style="color: #94A3B8; font-size: 15px; text-align: center;">
                        <i class="bi bi-arrow-right"></i>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 10px; color: #64748B; display: block; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px;">Rekomendasi Realtime</span>
                        <div id="adjust_rec_beasiswa_display" style="margin-top: 2px;">
                            <strong id="adjust_rec_beasiswa_badge" style="font-size: 14px; font-weight: 700;">-</strong>
                        </div>
                    </div>
                </div>

                <!-- Analisis & Alasan Sistem -->
                <div id="adjust_reason_box" style="font-size: 11.5px; color: #475569; background: #FFFFFF; padding: 9px 12px; border-radius: 8px; border: 1px solid #E2E8F0; line-height: 1.45;">
                </div>
            </div>

            <!-- Pedoman Standar Beasiswa SK Rektor (Collapsible Table) -->
            <div style="margin-bottom: 12px;">
                <button type="button" onclick="togglePedomanTable()" style="background: #F8FAFC; border: 1px solid #CBD5E1; color: #334155; padding: 7px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 600; width: 100%; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: all 0.2s ease;">
                    <span><i class="bi bi-book-half text-primary" style="margin-right: 6px;"></i> Lihat Pedoman Standar SK Rektor No: 2638/SK/01/2026</span>
                    <i id="pedoman_table_icon" class="bi bi-chevron-down" style="font-size: 11px;"></i>
                </button>
                <div id="pedoman_table_wrap" style="display: none; margin-top: 6px; border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; background: #FFFFFF; font-size: 11px;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background: #F1F5F9; border-bottom: 1px solid #CBD5E1; color: #475569; font-weight: 600;">
                                <th style="padding: 5px 8px;">Kategori</th>
                                <th style="padding: 5px 8px;">Min. IPK</th>
                                <th style="padding: 5px 8px;">Target Th I</th>
                                <th style="padding: 5px 8px;">Target Th II</th>
                                <th style="padding: 5px 8px;">Total SK</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                <td style="padding: 5px 8px; font-weight: 600; color: #15803D;"><i class="bi bi-award-fill text-warning"></i> 100%</td>
                                <td style="padding: 5px 8px;">≥ 3.75</td>
                                <td style="padding: 5px 8px;">180 Poin</td>
                                <td style="padding: 5px 8px;">180 Poin</td>
                                <td style="padding: 5px 8px; font-weight: 600;">450 Poin</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                <td style="padding: 5px 8px; font-weight: 600; color: #0284C7;"><i class="bi bi-award-fill text-info"></i> 75%</td>
                                <td style="padding: 5px 8px;">≥ 3.50</td>
                                <td style="padding: 5px 8px;">140 Poin</td>
                                <td style="padding: 5px 8px;">140 Poin</td>
                                <td style="padding: 5px 8px; font-weight: 600;">351 Poin</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                <td style="padding: 5px 8px; font-weight: 600; color: #D97706;"><i class="bi bi-award-fill text-warning"></i> 50%</td>
                                <td style="padding: 5px 8px;">≥ 3.25</td>
                                <td style="padding: 5px 8px;">120 Poin</td>
                                <td style="padding: 5px 8px;">120 Poin</td>
                                <td style="padding: 5px 8px; font-weight: 600;">301 Poin</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                <td style="padding: 5px 8px; font-weight: 600; color: #64748B;"><i class="bi bi-award text-muted"></i> 25%</td>
                                <td style="padding: 5px 8px;">≥ 3.00</td>
                                <td style="padding: 5px 8px;">120 Poin</td>
                                <td style="padding: 5px 8px;">120 Poin</td>
                                <td style="padding: 5px 8px; font-weight: 600;">300 Poin</td>
                            </tr>
                            <tr style="background: #FAFAFA;">
                                <td style="padding: 5px 8px; font-weight: 600; color: #475569;">Reguler (0%)</td>
                                <td style="padding: 5px 8px;">-</td>
                                <td style="padding: 5px 8px;" colspan="2">Bebas target beasiswa</td>
                                <td style="padding: 5px 8px; font-weight: 600; color: #2563EB;">SKPI 250 Poin</td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="padding: 5px 8px; background: #F8FAFC; border-top: 1px solid #E2E8F0; color: #64748B; font-size: 10.5px;">
                        <i class="bi bi-info-circle"></i> Sesuai SK Rektor No: 2638/SK/01/2026. Evaluasi dilakukan tiap akhir tahun akademik.
                    </div>
                </div>
            </div>

            <!-- Pilihan Kategori Beasiswa -->
            <div class="form-group" style="margin-bottom: 10px;">
                <label for="adjust_target_beasiswa" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Pilihan Kategori Beasiswa *</label>
                <select id="adjust_target_beasiswa" name="beasiswa" class="form-control" required style="font-weight: 600;" onchange="onAdjustSelectChange()">
                    <option value="100%">Beasiswa 100% (Sangat Baik - Min. IPK 3.75, POKEMA > 450 Poin)</option>
                    <option value="75%">Beasiswa 75% (Baik Sekali - Min. IPK 3.50, POKEMA 351–450 Poin)</option>
                    <option value="50%">Beasiswa 50% (Baik - Min. IPK 3.25, POKEMA 301–350 Poin)</option>
                    <option value="25%">Beasiswa 25% (Cukup Baik - Min. IPK 3.00, POKEMA 300 Poin)</option>
                    <option value="0%">0% / Non-Beasiswa (Reguler - Target SKPI 250 Poin)</option>
                </select>
                <!-- Real-Time Compliance Feedback Container -->
                <div id="adjust_compliance_feedback" style="margin-top: 6px;"></div>
            </div>

            <div class="form-group" style="margin-bottom: 10px;">
                <label for="adjust_notes" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Catatan Penyesuaian / Pesan ke Mahasiswa (Opsional)</label>
                <textarea id="adjust_notes" name="notes" class="form-control textarea-control" rows="2" style="min-height: 55px; resize: vertical;" placeholder="Pesan ucapan selamat, instruksi, atau catatan Monev kemahasiswaan..."></textarea>
            </div>

            <!-- Audit Trail Safety Banner -->
            <div style="font-size: 11px; color: #475569; background: #F1F5F9; border: 1px solid #E2E8F0; border-radius: 6px; padding: 6px 10px; margin-bottom: 14px; display: flex; align-items: center; gap: 6px; line-height: 1.35;">
                <i class="bi bi-shield-check text-success" style="font-size: 14px; flex-shrink: 0;"></i>
                <span><strong>Audit Trail Aktif:</strong> Penetapan status beasiswa otomatis tersimpan dalam riwayat catatan mahasiswa dengan stempel waktu dan dasar SK 2638/SK/01/2026.</span>
            </div>

            <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                <button type="button" class="btn-cancel" onclick="closeAdjustBeasiswaModal()">Batal</button>
                <button type="submit" id="adjust_submit_btn" class="btn-approve confirm" style="background: #059669; border: none; padding: 9px 20px; border-radius: 8px; font-weight: 600; color: white; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                    <i class="bi bi-check-circle"></i> Terapkan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
