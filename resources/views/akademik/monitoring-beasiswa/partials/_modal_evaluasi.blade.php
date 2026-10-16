{{-- =========================================================================
     MODAL EVALUASI POKEMA & DETAIL PRESTASI MAHASISWA (BIRO AKADEMIK)
     Universitas Sugeng Hartono - Monitoring Beasiswa & Student Ambassador
     File: resources/views/akademik/monitoring-beasiswa/partials/_modal_evaluasi.blade.php
     ========================================================================= --}}

<!-- 1. MODAL EVALUASI POKEMA 7 BIDANG & PRESTASI MAHASISWA -->
<div id="akademikEvaluationModal" class="akademik-modal-backdrop" style="display: none;" aria-hidden="true" role="dialog">
    <div class="akademik-modal-dialog akademik-modal-lg">
        <div class="akademik-modal-content">
            <!-- Header -->
            <div class="akademik-modal-hdr">
                <div>
                    <h4 class="akademik-modal-hdr-title">
                        <i class="bi bi-speedometer2 text-warning me-2"></i> Evaluasi Prestasi & POKEMA Mahasiswa
                    </h4>
                    <div style="display: flex; gap: 8px; align-items: center; margin-top: 5px; flex-wrap: wrap;">
                        <span id="akademik_eval_nama" style="font-weight: 700; font-size: 14.5px; color: #FFFFFF;"></span>
                        <span id="akademik_eval_badges" style="display: inline-flex; gap: 6px;"></span>
                    </div>
                </div>
                <button type="button" onclick="closeAkademikEvaluationModal()" class="akademik-modal-close" aria-label="Tutup" title="Tutup Modal (Esc)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Quick Summary Bar -->
            <div id="akademik_eval_meta_bar" class="akademik-meta-bar">
                <div>NIM: <strong id="ak-meta-nim">-</strong></div>
                <div>Semester: <strong id="ak-meta-semester" style="color: #0284C7;">-</strong></div>
                <div>IPK: <strong id="ak-meta-ipk" style="color: #1E293B;">-</strong></div>
                <div>Total SKP Disetujui: <strong id="ak-meta-poin" style="color: #2563EB;">0 Poin</strong></div>
                <div>Beasiswa Saat Ini: <strong id="ak-meta-beasiswa" style="color: #059669;">-</strong></div>
                <div>Periode Studi: <strong id="ak-meta-study-year" style="color: #6366F1;">-</strong></div>
            </div>

            <!-- Modal Tabs Navigation -->
            <div class="akademik-tabs-nav">
                <button type="button" class="akademik-tab-btn active" id="akTabBtnEvaluation" onclick="switchAkademikModalTab('evaluation')">
                    <i class="bi bi-graph-up-arrow"></i> Evaluasi POKEMA 7 Bidang & Target
                </button>
                <button type="button" class="akademik-tab-btn" id="akTabBtnAchievements" onclick="switchAkademikModalTab('achievements')">
                    <i class="bi bi-award-fill"></i> Riwayat Prestasi & Sertifikat (<span id="ak_tab_cert_count">0</span>)
                </button>
            </div>

            <!-- Modal Body Scrollable -->
            <div class="akademik-modal-body-scroll">
                <!-- TAB 1: EVALUASI POKEMA 7 BIDANG & TARGET BEASISWA -->
                <div id="akTabContentEvaluation">
                    <!-- Smart Recommendation Card -->
                    <div id="akademik_smart_recommendation_card">
                        <!-- Diisi via JS -->
                    </div>

                    <!-- Rincian & Akumulasi POKEMA Per Semester -->
                    <div class="akademik-pokema-semester-section">
                        <div class="ak-section-header">
                            <div>
                                <h5 class="ak-section-title">
                                    <i class="bi bi-calendar3 me-1 text-primary"></i> Rincian & Akumulasi POKEMA Per Semester
                                </h5>
                                <p class="ak-section-sub">
                                    Tracking capaian prestasi dan konseling tiap semester menuju pemenuhan target tahunan beasiswa.
                                </p>
                            </div>
                        </div>

                        <div id="akademik_pokema_semester_bars_grid" class="ak-semester-bars-grid">
                            <!-- Diisi via JS -->
                        </div>
                    </div>

                    <!-- 7 Fields POKEMA Section -->
                    <div class="akademik-pokema-fields-container">
                        <div class="ak-fields-header">
                            <div>
                                <h5 class="ak-section-title">
                                    <i class="bi bi-pie-chart-fill me-1 text-success"></i> Capaian 7 Bidang Kegiatan POKEMA
                                </h5>
                                <p class="ak-section-sub">
                                    Standar minimal distribusi kegiatan berdasarkan ketentuan SK Rektor No: 2638/SK/01/2026.
                                </p>
                            </div>
                            <div class="ak-year-toggle-wrap">
                                <span class="ak-year-label">Target Acuan:</span>
                                <div class="ak-btn-group-year">
                                    <button type="button" class="ak-btn-year active" id="akBtnYear1" onclick="changeAkademikEvaluationYear(1)">Th I (40%)</button>
                                    <button type="button" class="ak-btn-year" id="akBtnYear2" onclick="changeAkademikEvaluationYear(2)">Th II (40%)</button>
                                    <button type="button" class="ak-btn-year" id="akBtnYear3" onclick="changeAkademikEvaluationYear(3)">Th III (20%)</button>
                                    <button type="button" class="ak-btn-year" id="akBtnYearTotal" onclick="changeAkademikEvaluationYear('total')">Total (100%)</button>
                                </div>
                            </div>
                        </div>

                        <!-- 7 Fields Fulfillment Banner -->
                        <div id="akademik_pokema_fields_summary_alert" style="margin-bottom: 14px; display: none;"></div>

                        <!-- 7 Progress Bars Grid -->
                        <div id="akademik_pokema_7_bars_grid" class="ak-pokema-bars-grid">
                            <!-- Diisi via JS -->
                        </div>
                    </div>
                </div>

                <!-- TAB 2: DAFTAR PRESTASI & SERTIFIKAT -->
                <div id="akTabContentAchievements" style="display: none;">
                    <div class="table-responsive" id="akademik_achievements_table_container">
                        <!-- Diisi via JS -->
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="akademik-modal-footer">
                <div class="text-muted" style="font-size: 12.5px;">
                    <i class="bi bi-info-circle me-1"></i> Data evaluasi bersumber dari capaian prestasi POKEMA tervalidasi & riwayat KHS SIAKAD.
                </div>
                <button type="button" class="btn-akademik-close" onclick="closeAkademikEvaluationModal()">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 2. MODAL DETAIL PRESTASI & BUKTI SERTIFIKAT (READ-ONLY) -->
<div id="akademikPrestasiDetailModal" class="akademik-modal-backdrop" style="display: none; z-index: 1000001 !important;" aria-hidden="true" role="dialog">
    <div class="akademik-modal-dialog akademik-modal-md">
        <div class="akademik-modal-content">
            <div class="akademik-modal-hdr" style="background: #1E293B;">
                <div>
                    <h4 class="akademik-modal-hdr-title">
                        <i class="bi bi-file-earmark-check-fill text-warning me-2"></i> Detail Prestasi & Bukti Pendukung
                    </h4>
                    <p class="akademik-modal-hdr-sub" id="ak_detail_nama" style="margin: 0; opacity: 0.9;"></p>
                </div>
                <button type="button" onclick="closeAkademikDetailPrestasiModal()" class="akademik-modal-close" aria-label="Tutup" title="Kembali ke Modal Evaluasi">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="akademik-modal-body-scroll" style="padding: 22px 24px;">
                <div class="ak-detail-grid">
                    <div class="ak-detail-item">
                        <span class="ak-detail-label">Kategori POKEMA</span>
                        <strong id="ak_detail_kategori" class="ak-detail-val">-</strong>
                    </div>
                    <div class="ak-detail-item">
                        <span class="ak-detail-label">Jenis Kegiatan</span>
                        <strong id="ak_detail_kegiatan" class="ak-detail-val">-</strong>
                    </div>
                    <div class="ak-detail-item full">
                        <span class="ak-detail-label">Nama Acara / Kegiatan</span>
                        <strong id="ak_detail_event" class="ak-detail-val" style="color: #1D4ED8; font-size: 14px;">-</strong>
                    </div>
                    <div class="ak-detail-item">
                        <span class="ak-detail-label">Penyelenggara</span>
                        <strong id="ak_detail_organizer" class="ak-detail-val">-</strong>
                    </div>
                    <div class="ak-detail-item">
                        <span class="ak-detail-label">Tahun Kegiatan</span>
                        <strong id="ak_detail_event_year" class="ak-detail-val">-</strong>
                    </div>
                    <div class="ak-detail-item">
                        <span class="ak-detail-label">Semester Pelaksanaan</span>
                        <strong id="ak_detail_semester" class="ak-detail-val" style="color: #0284C7;">-</strong>
                    </div>
                    <div class="ak-detail-item">
                        <span class="ak-detail-label">Tingkat Prestasi</span>
                        <strong id="ak_detail_tingkat" class="ak-detail-val">-</strong>
                    </div>
                    <div class="ak-detail-item">
                        <span class="ak-detail-label">Peran / Kedudukan</span>
                        <strong id="ak_detail_peran" class="ak-detail-val">-</strong>
                    </div>
                    <div class="ak-detail-item">
                        <span class="ak-detail-label">Bobot Poin SKP</span>
                        <strong id="ak_detail_skp" class="ak-detail-val" style="color: #059669; font-size: 15px;">-</strong>
                    </div>
                    <div class="ak-detail-item">
                        <span class="ak-detail-label">Status Verifikasi</span>
                        <div id="ak_detail_status_badge">-</div>
                    </div>
                    <div class="ak-detail-item">
                        <span class="ak-detail-label">Tanggal Pengajuan</span>
                        <strong id="ak_detail_tanggal" class="ak-detail-val">-</strong>
                    </div>
                </div>

                <!-- Dokumen Bukti -->
                <div class="ak-section-box mt-3">
                    <span class="ak-detail-label mb-2 d-block">Berkas Bukti / Piagam / Sertifikat</span>
                    <div id="ak_detail_file_container">
                        <!-- Diisi via JS -->
                    </div>
                </div>

                <!-- Reviewer & Catatan -->
                <div class="ak-section-box mt-3" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <i class="bi bi-chat-square-quote-fill text-muted" style="font-size: 20px; flex-shrink: 0; margin-top: 2px;"></i>
                        <div>
                            <span class="ak-detail-label">Catatan Hasil Review Kemahasiswaan</span>
                            <p id="ak_detail_catatan" style="margin: 4px 0 6px; font-size: 13px; color: #334155; line-height: 1.45;"></p>
                            <span style="font-size: 11.5px; color: #64748B;">Diverifikasi oleh: <strong id="ak_detail_reviewer" style="color: #1E293B;">-</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="akademik-modal-footer">
                <button type="button" class="btn-akademik-close" onclick="closeAkademikDetailPrestasiModal()">
                    <i class="bi bi-arrow-left"></i> Kembali ke Evaluasi
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Backdrop & Dialog Styling */
    .akademik-modal-backdrop {
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
        z-index: 999999 !important;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 40px 20px 30px !important;
        box-sizing: border-box !important;
        overflow-y: auto !important;
    }
    .akademik-modal-dialog {
        width: 100% !important;
        margin: auto !important;
        display: flex !important;
        max-height: calc(100vh - 70px) !important;
        animation: akModalZoomIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
        position: relative !important;
        z-index: 1000000 !important;
    }
    .akademik-modal-lg { max-width: 1140px !important; }
    .akademik-modal-md { max-width: 640px !important; }

    @keyframes akModalZoomIn {
        from { opacity: 0; transform: translateY(20px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .akademik-modal-content {
        background: #FFFFFF !important;
        border-radius: 18px !important;
        width: 100% !important;
        max-height: calc(100vh - 70px) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4) !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
    }
    .akademik-modal-hdr {
        background: #1E293B !important;
        border-bottom: 3px solid #F59E0B !important;
        padding: 18px 24px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        color: white !important;
        flex-shrink: 0 !important;
    }
    .akademik-modal-hdr-title {
        margin: 0 !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        color: #FFFFFF !important;
        display: flex;
        align-items: center;
    }
    .akademik-modal-close {
        background: rgba(255, 255, 255, 0.12) !important;
        border: none !important;
        font-size: 16px !important;
        color: #FFFFFF !important;
        cursor: pointer !important;
        width: 34px !important;
        height: 34px !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.2s !important;
    }
    .akademik-modal-close:hover {
        background: rgba(239, 68, 68, 0.8) !important;
        transform: scale(1.05);
    }
    .akademik-meta-bar {
        background: #F8FAFC;
        border-bottom: 1px solid #E2E8F0;
        padding: 10px 24px;
        display: flex;
        gap: 20px;
        font-size: 13px;
        color: #475569;
        flex-wrap: wrap;
        flex-shrink: 0;
    }
    .akademik-meta-bar strong {
        color: #1E293B;
    }
    .akademik-tabs-nav {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid #E2E8F0;
        padding: 0 24px;
        background: #F8FAFC;
        flex-shrink: 0;
    }
    .akademik-tab-btn {
        padding: 12px 18px;
        font-size: 13px;
        font-weight: 700;
        border: none;
        background: transparent;
        color: #64748B;
        cursor: pointer;
        transition: all 0.2s;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }
    .akademik-tab-btn:hover { color: #1E293B; }
    .akademik-tab-btn.active {
        color: #2563EB;
        border-bottom-color: #2563EB;
        background: #FFFFFF;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    .akademik-modal-body-scroll {
        padding: 22px 24px;
        overflow-y: auto;
        flex: 1 1 auto;
    }
    .akademik-modal-footer {
        padding: 14px 24px;
        background: #F8FAFC;
        border-top: 1px solid #E2E8F0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        flex-wrap: wrap;
        gap: 10px;
    }
    .btn-akademik-close {
        padding: 8px 18px;
        background: #E2E8F0;
        color: #334155;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-akademik-close:hover {
        background: #CBD5E1;
        color: #0F172A;
    }

    /* Smart Recommendation Card */
    .ak-rec-card {
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        transition: all 0.25s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .ak-rec-card.rec-on_track {
        background: linear-gradient(135deg, #F0F9FF 0%, #E0F2FE 100%);
        border: 1.5px solid #7DD3FC;
    }
    .ak-rec-card.rec-upgrade {
        background: linear-gradient(135deg, #ECFDF5 0%, #F0FDF4 100%);
        border: 1.5px solid #6EE7B7;
    }
    .ak-rec-card.rec-maintain {
        background: linear-gradient(135deg, #EFF6FF 0%, #F0F9FF 100%);
        border: 1.5px solid #93C5FD;
    }
    .ak-rec-card.rec-warning {
        background: linear-gradient(135deg, #FFFBEB 0%, #FEFCE8 100%);
        border: 1.5px solid #FCD34D;
    }
    .ak-rec-card.rec-downgrade {
        background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
        border: 1.5px solid #FBBF24;
    }
    .ak-rec-card.rec-revoke {
        background: linear-gradient(135deg, #FEF2F2 0%, #FFF1F2 100%);
        border: 1.5px solid #FECACA;
    }
    .ak-rec-card.rec-future, .ak-rec-card.rec-reguler {
        background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
        border: 1.5px solid #CBD5E1;
    }
    .ak-rec-main {
        flex: 1 1 540px;
        min-width: 280px;
    }
    .ak-rec-top-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .ak-rec-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }
    .ak-rec-pill.pill-on_track { background: #0284C7; color: #FFFFFF; }
    .ak-rec-pill.pill-upgrade { background: #059669; color: #FFFFFF; }
    .ak-rec-pill.pill-maintain { background: #2563EB; color: #FFFFFF; }
    .ak-rec-pill.pill-warning { background: #D97706; color: #FFFFFF; }
    .ak-rec-pill.pill-downgrade { background: #D97706; color: #FFFFFF; }
    .ak-rec-pill.pill-revoke { background: #DC2626; color: #FFFFFF; }
    .ak-rec-pill.pill-future, .ak-rec-pill.pill-reguler { background: #64748B; color: #FFFFFF; }

    .ak-rec-title {
        font-size: 16.5px;
        font-weight: 800;
        color: #0F172A;
        margin: 0 0 6px 0;
    }
    .ak-rec-reason {
        font-size: 13px;
        line-height: 1.5;
        color: #334155;
        margin: 0 0 10px 0;
    }
    .ak-rec-progress-box {
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 8px;
        padding: 9px 12px;
        margin-top: 8px;
    }
    .ak-rec-progress-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #334155;
        font-weight: 600;
        margin-bottom: 6px;
        flex-wrap: wrap;
        gap: 6px;
    }
    .ak-rec-track {
        height: 7px;
        background: #E2E8F0;
        border-radius: 999px;
        overflow: hidden;
    }
    .ak-rec-bar {
        height: 100%;
        border-radius: 999px;
        transition: width 0.4s ease;
    }
    .ak-rec-bar.bar-on_track { background: linear-gradient(90deg, #0284C7, #38BDF8); }
    .ak-rec-bar.bar-upgrade { background: linear-gradient(90deg, #059669, #34D399); }
    .ak-rec-bar.bar-maintain { background: linear-gradient(90deg, #2563EB, #60A5FA); }
    .ak-rec-bar.bar-warning { background: linear-gradient(90deg, #D97706, #FBBF24); }
    .ak-rec-bar.bar-downgrade { background: linear-gradient(90deg, #D97706, #FBBF24); }
    .ak-rec-bar.bar-revoke { background: linear-gradient(90deg, #DC2626, #F87171); }
    .ak-rec-bar.bar-future { background: linear-gradient(90deg, #64748B, #94A3B8); }

    .btn-ak-view-certs {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        background: #2563EB;
        color: #FFFFFF;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
    }
    .btn-ak-view-certs:hover {
        background: #1D4ED8;
        transform: translateY(-1px);
    }

    /* Semester Breakdown */
    .akademik-pokema-semester-section {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 22px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .ak-section-header {
        margin-bottom: 14px;
    }
    .ak-section-title {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #1E293B;
        display: flex;
        align-items: center;
    }
    .ak-section-sub {
        margin: 3px 0 0;
        font-size: 12px;
        color: #64748B;
    }
    .ak-semester-bars-grid {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .ak-sem-card {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 14px 16px;
        transition: all 0.2s ease;
    }
    .ak-sem-card.active {
        background: #F0F9FF;
        border-color: #BAE6FD;
        box-shadow: 0 0 0 1px #0284C7;
    }
    .ak-sem-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        flex-wrap: wrap;
        gap: 8px;
    }
    .ak-sem-title {
        font-size: 14px;
        font-weight: 700;
        color: #0F172A;
    }
    .ak-sem-year-badge {
        font-size: 11px;
        font-weight: 600;
        background: #E2E8F0;
        color: #475569;
        padding: 2px 8px;
        border-radius: 999px;
    }
    .ak-badge-current-sem {
        font-size: 10px;
        font-weight: 700;
        background: #0284C7;
        color: #FFFFFF;
        padding: 2px 8px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* 7 Fields Section */
    .akademik-pokema-fields-container {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 18px 20px;
    }
    .ak-fields-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .ak-year-toggle-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ak-year-label {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }
    .ak-btn-group-year {
        display: inline-flex;
        background: #F1F5F9;
        padding: 3px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
    }
    .ak-btn-year {
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 700;
        border: none;
        background: transparent;
        color: #64748B;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .ak-btn-year.active {
        background: #FFFFFF;
        color: #2563EB;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .ak-pokema-bars-grid {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .ak-field-row {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 12px 16px;
        transition: all 0.2s;
    }
    .ak-field-row:hover {
        border-color: #CBD5E1;
        background: #F1F5F9;
    }
    .ak-field-title-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }
    .ak-field-name {
        font-size: 13px;
        font-weight: 700;
        color: #1E293B;
    }
    .ak-field-score {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }

    /* Detail Prestasi Grid */
    .ak-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px 18px;
    }
    .ak-detail-item {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .ak-detail-item.full {
        grid-column: 1 / -1;
    }
    .ak-detail-label {
        font-size: 11.5px;
        font-weight: 700;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .ak-detail-val {
        font-size: 13.5px;
        font-weight: 600;
        color: #1E293B;
    }
    .ak-section-box {
        padding: 14px 16px;
        border-radius: 10px;
    }

    @media (max-width: 768px) {
        .akademik-modal-backdrop { padding: 15px 10px !important; }
        .ak-detail-grid { grid-template-columns: 1fr; }
        .akademik-meta-bar { gap: 10px; padding: 10px 16px; }
        .akademik-modal-body-scroll { padding: 16px 14px; }
    }
</style>

<script>
    // State management modal evaluasi akademik
    let currentAkademikStudentId = null;
    let currentAkademikStudentNama = '';
    let currentAkademikStudentNim = '';
    let currentAkademikAchievements = [];
    let currentAkademikEvaluation = null;

    function relocateAkademikModalsToBody() {
        const modalEval = document.getElementById('akademikEvaluationModal');
        const modalDetail = document.getElementById('akademikPrestasiDetailModal');
        if (modalEval && modalEval.parentElement !== document.body) {
            document.body.appendChild(modalEval);
        }
        if (modalDetail && modalDetail.parentElement !== document.body) {
            document.body.appendChild(modalDetail);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', relocateAkademikModalsToBody);
    } else {
        relocateAkademikModalsToBody();
    }

    function showAkademikEvaluationModal(btn, initialTab = 'evaluation') {
        relocateAkademikModalsToBody();

        currentAkademikStudentId = btn.dataset.studentId;
        const nama = btn.dataset.nama || '-';
        const nim = btn.dataset.nim || '-';
        const ipk = btn.dataset.ipk || '-';
        const poin = btn.dataset.poin || '0';
        const statusMhs = btn.dataset.statusMhs || 'Mahasiswa';
        const beasiswa = btn.dataset.beasiswa || '-';
        const achievementsBase64 = btn.dataset.achievements;
        const evaluationBase64 = btn.dataset.evaluation;

        currentAkademikStudentNama = nama;
        currentAkademikStudentNim = nim;
        currentAkademikAchievements = safeDecodeBase64Json(achievementsBase64, []);
        currentAkademikEvaluation = safeDecodeBase64Json(evaluationBase64, null);

        document.getElementById('akademik_eval_nama').textContent = nama;
        document.getElementById('ak-meta-nim').textContent = nim;
        document.getElementById('ak-meta-semester').textContent = (currentAkademikEvaluation && currentAkademikEvaluation.semester) ? ('Semester ' + currentAkademikEvaluation.semester) : '-';
        document.getElementById('ak-meta-ipk').textContent = (currentAkademikEvaluation && currentAkademikEvaluation.ipk) ? currentAkademikEvaluation.ipk : ipk;
        document.getElementById('ak-meta-poin').textContent = poin + ' Poin';
        document.getElementById('ak-meta-beasiswa').textContent = beasiswa;
        document.getElementById('ak-meta-study-year').textContent = (currentAkademikEvaluation && currentAkademikEvaluation.study_year_label) ? currentAkademikEvaluation.study_year_label : '-';
        document.getElementById('ak_tab_cert_count').textContent = currentAkademikAchievements.length;

        // Render Badges
        const badgesContainer = document.getElementById('akademik_eval_badges');
        if (badgesContainer) {
            let badgeHtml = '';
            if (statusMhs === 'Student Ambassador') {
                badgeHtml += `<span class="badge-status-mhs badge-ambassador" style="font-size: 11px; padding: 2px 8px; background: #FFFBEB; color: #B45309; border: 1px solid #FCD34D; border-radius: 20px;"><i class="bi bi-star-fill"></i> Student Ambassador</span>`;
            } else {
                badgeHtml += `<span class="badge-beasiswa" style="font-size: 11px; padding: 2px 8px; border-radius: 20px;">Beasiswa ${escapeHtml(beasiswa)}</span>`;
            }
            badgesContainer.innerHTML = badgeHtml;
        }

        // Render Tab 1 (POKEMA)
        renderAkademikPokemaEvaluation(currentAkademikEvaluation);

        // Render Tab 2 (Achievements Table)
        renderAkademikAchievementsTable(currentAkademikAchievements, nama);

        // Switch to initial tab
        switchAkademikModalTab(initialTab);

        const modalEl = document.getElementById('akademikEvaluationModal');
        if (modalEl) {
            modalEl.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeAkademikEvaluationModal() {
        const modalEl = document.getElementById('akademikEvaluationModal');
        if (modalEl) {
            modalEl.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function switchAkademikModalTab(tab) {
        const tabEval = document.getElementById('akTabContentEvaluation');
        const tabAch = document.getElementById('akTabContentAchievements');
        const btnEval = document.getElementById('akTabBtnEvaluation');
        const btnAch = document.getElementById('akTabBtnAchievements');

        if (!tabEval || !tabAch) return;

        if (tab === 'evaluation') {
            tabEval.style.display = 'block';
            tabAch.style.display = 'none';
            btnEval.classList.add('active');
            btnAch.classList.remove('active');
        } else {
            tabEval.style.display = 'none';
            tabAch.style.display = 'block';
            btnEval.classList.remove('active');
            btnAch.classList.add('active');
        }
    }

    function renderAkademikPokemaEvaluation(evalData) {
        const recContainer = document.getElementById('akademik_smart_recommendation_card');
        const barsContainer = document.getElementById('akademik_pokema_7_bars_grid');

        if (!evalData) {
            recContainer.innerHTML = '<p class="text-muted" style="padding: 10px;">Data evaluasi belum tersedia.</p>';
            barsContainer.innerHTML = '';
            return;
        }

        // 1. Set active year toggle
        const currentYear = String(evalData.study_year || '1');
        ['1', '2', '3', 'total'].forEach(y => {
            const btn = document.getElementById('akBtnYear' + (y === 'total' ? 'Total' : y));
            if (btn) {
                if (String(y) === currentYear || (y === 'total' && (currentYear === '4' || currentYear === 'total'))) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            }
        });

        // 2. Smart Recommendation Box
        const recType = evalData.action_type || 'maintain';
        let badgeLabel = 'STATUS PRESTASI';
        let titleText = escapeHtml(evalData.action_label || 'Status Prestasi Mahasiswa');

        if (recType === 'on_track') {
            badgeLabel = 'PROGRES BERJALAN (ON TRACK)';
        } else if (recType === 'upgrade') {
            badgeLabel = 'REKOMENDASI PROMOSI BEASISWA';
        } else if (recType === 'maintain') {
            badgeLabel = 'TARGET PERIODE TERPENUHI';
        } else if (recType === 'warning') {
            badgeLabel = 'PERLU PERHATIAN AKADEMIK';
        } else if (recType === 'downgrade') {
            badgeLabel = 'EVALUASI AKHIR PERIODE (PENYESUAIAN)';
        } else if (recType === 'revoke') {
            badgeLabel = 'EVALUASI AKHIR PERIODE (DIBAWAH STANDAR)';
        } else if (recType === 'future') {
            badgeLabel = 'TARGET PERIODE MENDATANG';
        }

        const targetPts = evalData.target_current || evalData.total_target_points || 0;
        const currentPts = evalData.total_approved_points || 0;
        const pct = (evalData.progress_pct !== undefined && evalData.progress_pct !== null)
            ? evalData.progress_pct
            : (targetPts > 0 ? Math.round((currentPts / targetPts) * 1000) / 10 : 0);
        const remaining = (evalData.points_remaining !== undefined && evalData.points_remaining !== null)
            ? evalData.points_remaining
            : Math.max(0, targetPts - currentPts);

        let progressBoxHtml = '';
        if (targetPts > 0) {
            const statusTag = remaining > 0
                ? `<span style="font-size: 11px; color: #0369A1; background: #E0F2FE; padding: 2px 8px; border-radius: 6px; font-weight: 700;">Sisa target: ${remaining} Poin</span>`
                : `<span style="font-size: 11px; color: #047857; background: #D1FAE5; padding: 2px 8px; border-radius: 6px; font-weight: 700;">Target Poin Terpenuhi ✓</span>`;

            progressBoxHtml = `
                <div class="ak-rec-progress-box">
                    <div class="ak-rec-progress-info">
                        <span>
                            Capaian: <strong>${currentPts} / ${targetPts} Poin</strong> (${pct}%)
                            ${evalData.ipk ? ` &bull; IPK: <strong>${evalData.ipk}</strong>` : ''}
                        </span>
                        ${statusTag}
                    </div>
                    <div class="ak-rec-track">
                        <div class="ak-rec-bar bar-${recType}" style="width: ${Math.min(100, Math.max(0, pct))}%;"></div>
                    </div>
                </div>
            `;
        }

        recContainer.className = `ak-rec-card rec-${recType}`;
        recContainer.innerHTML = `
            <div class="ak-rec-main">
                <div class="ak-rec-top-meta">
                    <span class="ak-rec-pill pill-${recType}">
                        ${badgeLabel}
                    </span>
                    <span style="font-size: 12px; color: #64748B;">
                        Periode: <strong>${escapeHtml(evalData.study_year_label || 'Tahun I')}</strong>
                        &bull; Acuan: <strong>Beasiswa ${escapeHtml(evalData.benchmark_tier_key || evalData.current_beasiswa || '75%')} (${targetPts} Poin)</strong>
                    </span>
                </div>
                <h4 class="ak-rec-title">${titleText}</h4>
                <p class="ak-rec-reason">${escapeHtml(evalData.reason || '')}</p>
                ${progressBoxHtml}
            </div>
            <div class="ak-rec-actions">
                <button type="button" class="btn-ak-view-certs" onclick="switchAkademikModalTab('achievements')">
                    <i class="bi bi-journal-check"></i> Lihat Riwayat Prestasi (${currentAkademikAchievements.length})
                </button>
            </div>
        `;

        // 3. Rincian & Akumulasi Semester
        const semContainer = document.getElementById('akademik_pokema_semester_bars_grid');
        if (semContainer) {
            let semHtml = '';
            const breakdown = evalData.semester_breakdown || [];
            if (breakdown.length === 0) {
                semContainer.innerHTML = '<p style="color: #94A3B8; font-size: 13px; margin: 0; padding: 10px 0;">Belum ada riwayat semester.</p>';
            } else {
                breakdown.forEach(sem => {
                    const isCur = sem.is_current;
                    const currentBadge = isCur ? '<span class="ak-badge-current-sem">Semester Aktif</span>' : '';
                    const ipkBadge = sem.ipk 
                        ? `<span style="font-size: 12px; font-weight: 700; background: #EFF6FF; color: #1D4ED8; padding: 3px 8px; border-radius: 6px; border: 1px solid #BFDBFE;">IPK: ${sem.ipk}</span>` 
                        : '<span style="font-size: 11px; color: #94A3B8; font-style: italic;">Konseling belum tercatat</span>';
                    
                    let milestoneHtml = '';
                    if (sem.is_period_milestone) {
                        if (sem.is_year_target_met) {
                            milestoneHtml = `<div style="margin-top: 8px; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0;"><i class="bi bi-check-circle-fill me-1"></i> Evaluasi Akhir ${escapeHtml(sem.study_year_label)}: Akumulasi <strong>${sem.cumulative_points} / ${sem.year_target} Poin</strong> (Target Terpenuhi)</div>`;
                        } else {
                            const kurang = Math.max(0, sem.year_target - sem.cumulative_points);
                            milestoneHtml = `<div style="margin-top: 8px; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Evaluasi Akhir ${escapeHtml(sem.study_year_label)}: Akumulasi <strong>${sem.cumulative_points} / ${sem.year_target} Poin</strong> (Kurang ${kurang} Poin menuju target beasiswa)</div>`;
                        }
                    }

                    semHtml += `
                        <div class="ak-sem-card ${isCur ? 'active' : ''}">
                            <div class="ak-sem-card-header">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span class="ak-sem-title">${escapeHtml(sem.semester_label)}</span>
                                    <span class="ak-sem-year-badge">${escapeHtml(sem.study_year_label)}</span>
                                    ${currentBadge}
                                </div>
                                <div>
                                    ${ipkBadge}
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 13px; color: #475569;">
                                <span>
                                    Perolehan ${escapeHtml(sem.semester_label)}: <strong style="color: #0284C7; font-size: 14px;">${sem.semester_points} Poin</strong> (${sem.achievements_count} Prestasi Disetujui)
                                </span>
                                <span>
                                    Akumulasi Berjalan ${escapeHtml(sem.study_year_label)}: <strong style="color: #1E293B; font-size: 14px;">${sem.cumulative_points} Poin</strong> / ${sem.year_target} Poin
                                </span>
                            </div>
                            ${milestoneHtml}
                        </div>
                    `;
                });
                semContainer.innerHTML = semHtml;
            }
        }

        // 4. 7 Fields Summary Banner
        const summaryAlert = document.getElementById('akademik_pokema_fields_summary_alert');
        if (summaryAlert) {
            const progressList = Object.values(evalData.fields_progress || {});
            const totalFields = progressList.length || 7;
            const fulfilledCount = (evalData.fulfilled_fields_count !== undefined)
                ? evalData.fulfilled_fields_count
                : progressList.filter(f => f.current >= f.target).length;
            const allMet = (evalData.all_fields_met !== undefined)
                ? evalData.all_fields_met
                : (fulfilledCount === totalFields && totalFields > 0);

            if (allMet) {
                summaryAlert.style.display = 'block';
                summaryAlert.innerHTML = `
                    <div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 8px; color: #065F46; font-size: 13px; font-weight: 600;">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 16px;"></i>
                            <span>Seluruh 7 Bidang POKEMA Terpenuhi (${fulfilledCount}/7 Bidang Sesuai Standar SK Rektor)</span>
                        </div>
                        <span style="background: #10B981; color: white; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">Lengkap 100%</span>
                    </div>
                `;
            } else {
                const unfulfilledCount = Math.max(0, totalFields - fulfilledCount);
                summaryAlert.style.display = 'block';
                summaryAlert.innerHTML = `
                    <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 8px; color: #92400E; font-size: 13px; font-weight: 600;">
                            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 16px;"></i>
                            <span>Sebaran Bidang: <strong>${fulfilledCount} dari ${totalFields} Bidang Terpenuhi</strong> (${unfulfilledCount} Bidang Belum Mencukupi)</span>
                        </div>
                        <span style="background: #F59E0B; color: white; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">Wajib 7 Bidang</span>
                    </div>
                `;
            }
        }

        // 5. 7 Progress Bars Grid
        let barsHtml = '';
        const progressList = Object.values(evalData.fields_progress || {});

        progressList.forEach(f => {
            const isOk = f.is_fulfilled !== undefined ? !!f.is_fulfilled : (f.target > 0 && f.current >= f.target);
            const fillStyle = isOk ? 'background: linear-gradient(90deg, #10B981, #059669);' : 'background: linear-gradient(90deg, #3B82F6, #2563EB);';
            const deficit = Math.max(0, (f.target || 0) - (f.current || 0));
            const badgeHtml = isOk
                ? `<span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: #D1FAE5; color: #065F46;">Terpenuhi</span>`
                : `<span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: #FEF3C7; color: #92400E;">Kurang ${deficit} Poin</span>`;

            barsHtml += `
                <div class="ak-field-row">
                    <div class="ak-field-title-wrap">
                        <span class="ak-field-name">${escapeHtml(f.label)}</span>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="ak-field-score">
                                <strong>${f.current}</strong> / ${f.target} Poin
                            </span>
                            ${badgeHtml}
                        </div>
                    </div>
                    <div class="ak-rec-track" style="height: 8px;">
                        <div style="height: 100%; border-radius: 999px; transition: width 0.4s ease; width: ${Math.min(100, Math.max(0, f.percent))}%; ${fillStyle}"></div>
                    </div>
                </div>
            `;
        });

        barsContainer.innerHTML = barsHtml;
    }

    function changeAkademikEvaluationYear(year) {
        if (!currentAkademikStudentId) return;

        fetch(`/akademik/monitoring-beasiswa/${currentAkademikStudentId}/evaluation-data?year=${year}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.evaluation) {
                    currentAkademikEvaluation = data.evaluation;
                    renderAkademikPokemaEvaluation(data.evaluation);
                }
            })
            .catch(err => {
                console.error("Gagal memuat evaluasi periode:", err);
            });
    }

    function renderAkademikAchievementsTable(achievements, nama) {
        const container = document.getElementById('akademik_achievements_table_container');
        if (!container) return;

        if (!achievements || achievements.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 40px 20px; color: #64748B;">
                    <i class="bi bi-award" style="font-size: 38px; opacity: 0.4; display: block; margin-bottom: 8px;"></i>
                    <p style="margin: 0; font-size: 14px; font-weight: 600;">Belum ada data prestasi yang diunggah oleh mahasiswa ini.</p>
                </div>
            `;
            return;
        }

        let tableHtml = `
            <table class="data-table" style="margin-top: 0;">
                <thead>
                    <tr>
                        <th style="width: 45px;">No</th>
                        <th>Kategori & Kegiatan</th>
                        <th style="width: 120px;">Tingkat</th>
                        <th style="width: 100px; text-align: center;">Poin SKP</th>
                        <th style="width: 110px; text-align: center;">Status</th>
                        <th style="width: 130px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
        `;

        achievements.forEach((ach, index) => {
            ach.nama = nama;
            const encodedData = btoa(unescape(encodeURIComponent(JSON.stringify(ach))));
            const titleText = ach.event ? ach.event : ach.kegiatan;
            const subText = ach.event ? `${ach.kegiatan} ${ach.organizer && ach.organizer !== '-' ? '• ' + ach.organizer : ''} ${ach.event_year && ach.event_year !== '-' ? '(' + ach.event_year + ')' : ''} ${ach.semester ? '• Sem ' + ach.semester : ''}` : `${ach.kategori} ${ach.semester ? '• Sem ' + ach.semester : ''}`;

            tableHtml += `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <strong style="font-size: 13px; color: #1E293B;">${escapeHtml(titleText)}</strong>
                            ${ach.semester ? `<span style="background-color: #E0F2FE; color: #0284C7; font-weight: 700; font-size: 11px; padding: 2px 7px; border-radius: 6px; border: 1px solid #BAE6FD;">Sem ${ach.semester}</span>` : ''}
                        </div>
                        <span style="font-size: 12px; color: #64748B;">${escapeHtml(subText)}</span>
                    </td>
                    <td>
                        <span class="badge-year" style="font-size: 11px;">${escapeHtml(ach.tingkat || '-')}</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge-point point-active" style="font-size: 12px; font-weight: 700;">${ach.skp} SKP</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge-status ${ach.statusClass || ''}" style="font-size: 11px;">${escapeHtml(ach.statusLabel || ach.status)}</span>
                    </td>
                    <td style="text-align: right;">
                        <button type="button" class="btn-view" onclick="showAkademikDetailPrestasiModal('${encodedData}', '${escapeHtml(nama)}')" style="padding: 5px 10px; font-size: 12px; border-radius: 6px;" title="Lihat Detail & Dokumen Pendukung">
                            <i class="bi bi-file-earmark-text"></i> Bukti & Detail
                        </button>
                    </td>
                </tr>
            `;
        });

        tableHtml += `</tbody></table>`;
        container.innerHTML = tableHtml;
    }

    function showAkademikDetailPrestasiModal(encodedData, nama) {
        relocateAkademikModalsToBody();
        const data = safeDecodeBase64Json(encodedData, {}) || {};
        data.nama = nama;

        document.getElementById('ak_detail_nama').textContent = data.nama;
        document.getElementById('ak_detail_kategori').textContent = data.kategori || '-';
        document.getElementById('ak_detail_kegiatan').textContent = data.kegiatan || '-';
        document.getElementById('ak_detail_event').textContent = data.event || data.kegiatan || '-';
        document.getElementById('ak_detail_organizer').textContent = data.organizer || '-';
        document.getElementById('ak_detail_event_year').textContent = data.event_year || '-';
        document.getElementById('ak_detail_semester').textContent = data.semester_label || (data.semester ? 'Semester ' + data.semester : '-');
        document.getElementById('ak_detail_tingkat').textContent = data.tingkat || '-';
        document.getElementById('ak_detail_peran').textContent = data.peran || '-';
        document.getElementById('ak_detail_skp').textContent = (data.skp || '0') + ' Poin SKP';
        document.getElementById('ak_detail_tanggal').textContent = data.tanggal || '-';
        document.getElementById('ak_detail_catatan').textContent = data.catatan || 'Belum ada catatan review.';
        document.getElementById('ak_detail_reviewer').textContent = data.reviewer || 'Belum direview';

        const statusBadgeWrap = document.getElementById('ak_detail_status_badge');
        statusBadgeWrap.innerHTML = `<span class="badge-status ${data.statusClass || ''}">${escapeHtml(data.statusLabel || data.status)}</span>`;

        const fileContainer = document.getElementById('ak_detail_file_container');
        if (data.file) {
            fileContainer.innerHTML = `
                <a href="${data.file}" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; color: #1D4ED8; text-decoration: none; font-weight: 700; padding: 10px 18px; background: #EFF6FF; border-radius: 8px; border: 1px solid #BFDBFE; transition: all 0.2s;">
                    <i class="bi bi-file-earmark-arrow-down-fill" style="font-size: 18px; color: #2563EB;"></i>
                    <span>Buka & Unduh Dokumen Sertifikat</span>
                    <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 12px;"></i>
                </a>
            `;
        } else {
            fileContainer.innerHTML = `
                <div style="color: #64748B; font-size: 13px; display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #F8FAFC; border-radius: 8px; border: 1px dashed #CBD5E1;">
                    <i class="bi bi-file-earmark-x" style="font-size: 20px; opacity: 0.5;"></i>
                    <span>Mahasiswa tidak melampirkan file dokumen bukti.</span>
                </div>
            `;
        }

        // Hide evaluation modal temporarily to avoid overlapping
        document.getElementById('akademikEvaluationModal').style.display = 'none';
        document.getElementById('akademikPrestasiDetailModal').style.display = 'flex';
    }

    function closeAkademikDetailPrestasiModal() {
        document.getElementById('akademikPrestasiDetailModal').style.display = 'none';
        document.getElementById('akademikEvaluationModal').style.display = 'flex';
    }

    // Helper utilities
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function safeDecodeBase64Json(base64Str, fallback) {
        if (!base64Str) return fallback;
        try {
            const decoded = decodeURIComponent(escape(atob(base64Str)));
            return JSON.parse(decoded);
        } catch (e) {
            try {
                return JSON.parse(atob(base64Str));
            } catch (e2) {
                console.error('Failed to decode base64 json:', e2);
                return fallback;
            }
        }
    }

    // Backdrop click dismiss for akademik modals
    document.addEventListener('click', function(e) {
        const modalEval = document.getElementById('akademikEvaluationModal');
        const modalDetail = document.getElementById('akademikPrestasiDetailModal');
        if (modalDetail && e.target === modalDetail) {
            closeAkademikDetailPrestasiModal();
        } else if (modalEval && e.target === modalEval) {
            closeAkademikEvaluationModal();
        }
    });

    // Escape key press
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modalDetail = document.getElementById('akademikPrestasiDetailModal');
            const modalEval = document.getElementById('akademikEvaluationModal');
            if (modalDetail && modalDetail.style.display === 'flex') {
                closeAkademikDetailPrestasiModal();
            } else if (modalEval && modalEval.style.display === 'flex') {
                closeAkademikEvaluationModal();
            }
        }
    });
</script>
