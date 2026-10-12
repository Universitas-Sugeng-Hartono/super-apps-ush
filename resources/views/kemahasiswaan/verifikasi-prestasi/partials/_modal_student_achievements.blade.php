{{-- MODAL PREVIEW BARU: Khusus Mahasiswa Penerima Beasiswa & Ambassador --}}
<div id="studentAchievementsModal" class="modal" style="display: none; z-index: 10001 !important;">
    <div class="modal-content modal-achievements">
        <!-- Modal Header -->
        <div class="modal-hdr">
            <div>
                <h4 class="modal-hdr-title">Daftar Prestasi Mahasiswa</h4>
                <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px; flex-wrap: wrap;">
                    <p class="modal-hdr-sub" id="student_achievements_nama" style="margin: 0; font-weight: 600;"></p>
                    <span id="student_achievements_badges" style="display: flex; gap: 6px;"></span>
                </div>
            </div>
            <button type="button" onclick="closeStudentAchievementsModal()" class="modal-close-btn">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <!-- Quick Summary Bar inside modal -->
        <div id="student_achievements_meta_bar" style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 10px 24px; display: flex; gap: 20px; font-size: 13px; color: #475569; flex-wrap: wrap;">
            <div>NIM: <strong id="meta_nim" style="color: #1E293B;">-</strong></div>
            <div>Semester: <strong id="meta_semester" style="color: #0284C7;">-</strong></div>
            <div>IPK: <strong id="meta_ipk" style="color: #1E293B;">-</strong></div>
            <div>Total SKP (Semua Semester): <strong id="meta_poin" style="color: #2563EB;">0 Poin</strong></div>
            <div>Beasiswa Saat Ini: <strong id="meta_beasiswa" style="color: #059669;">-</strong></div>
            <div>Periode POKEMA: <strong id="meta_study_year" style="color: #6366F1;">-</strong></div>
        </div>

        <!-- Modal Tabs Navigation -->
        <div class="modal-tabs-nav">
            <button type="button" class="modal-tab-btn active" id="tabBtnEvaluation" onclick="switchModalTab('evaluation')">
                <i class="bi bi-speedometer2"></i> Evaluasi POKEMA 7 Bidang & Beasiswa
            </button>
            <button type="button" class="modal-tab-btn" id="tabBtnAchievements" onclick="switchModalTab('achievements')">
                <i class="bi bi-file-earmark-check"></i> Daftar Prestasi & Sertifikat (<span id="tab_cert_count">0</span>)
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body-scroll">
            <!-- TAB 1: EVALUASI POKEMA 7 BIDANG & BEASISWA -->
            <div id="tabContentEvaluation">
                <!-- Smart Recommendation Card -->
                <div id="smart_recommendation_card">
                    <!-- Populated via JS -->
                </div>

                <!-- Rincian & Akumulasi POKEMA Per Semester -->
                <div class="pokema-semester-section" style="margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <h5 style="margin: 0; font-size: 15px; font-weight: 700; color: #1E293B;">
                                <i class="bi bi-calendar3-range-fill" style="color: #0284C7;"></i> Rincian & Akumulasi POKEMA Per Semester
                            </h5>
                            <p style="margin: 3px 0 0; font-size: 12px; color: #64748B;">
                                Tracking capaian prestasi dan konseling tiap semester menuju target tahunan beasiswa.
                            </p>
                        </div>
                    </div>

                    <div id="pokema_semester_bars_grid" class="semester-bars-grid">
                        <!-- Populated via JS -->
                    </div>
                </div>

                <!-- 7 Fields POKEMA Section -->
                <div class="pokema-fields-container">
                    <div class="pokema-fields-header">
                        <div>
                            <h5 style="margin: 0; font-size: 15px; font-weight: 700; color: #1E293B;">
                                <i class="bi bi-bar-chart-fill" style="color: #2563EB;"></i> Capaian 7 Bidang Kegiatan POKEMA
                            </h5>
                            <p style="margin: 3px 0 0; font-size: 12px; color: #64748B;">
                                Standar minimal berdasarkan SK Rektor No: 2638/SK/01/2026.
                            </p>
                        </div>
                        <div class="year-toggle-wrap">
                            <span style="font-size: 12px; font-weight: 600; color: #475569;">Target Periode:</span>
                            <div class="btn-group-year">
                                <button type="button" class="btn-year active" id="btnYear1" onclick="changeEvaluationYear(1)">Th I (40%)</button>
                                <button type="button" class="btn-year" id="btnYear2" onclick="changeEvaluationYear(2)">Th II (40%)</button>
                                <button type="button" class="btn-year" id="btnYear3" onclick="changeEvaluationYear(3)">Th III (20%)</button>
                                <button type="button" class="btn-year" id="btnYearTotal" onclick="changeEvaluationYear('total')">Total (100%)</button>
                            </div>
                        </div>
                    </div>

                    <!-- 7 Progress Bars Grid -->
                    <div id="pokema_7_bars_grid" class="pokema-bars-grid">
                        <!-- Populated via JS -->
                    </div>
                </div>
            </div>

            <!-- TAB 2: DAFTAR PRESTASI & SERTIFIKAT -->
            <div id="tabContentAchievements" style="display: none;">
                <div class="table-responsive" id="achievements_table_container">
                    <!-- Diisi via JS -->
                </div>
            </div>
        </div>
    </div>
</div>
