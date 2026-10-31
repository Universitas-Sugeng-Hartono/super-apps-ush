<script>
    const SKP_DICTIONARY = @json($skpDictionary ?? []);

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function showApproveModal(achievementId) {
        const modal = document.getElementById('approveModal');
        const form = document.getElementById('approveForm');
        form.action = `/kemahasiswaan/verifikasi-prestasi/${achievementId}/approve`;
        modal.style.display = 'flex';
    }

    function closeApproveModal() {
        document.getElementById('approveModal').style.display = 'none';
    }

    function showRejectModal(achievementId) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        form.action = `/kemahasiswaan/verifikasi-prestasi/${achievementId}/reject`;
        modal.style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    function showApproveAllModal(targetTab = '{{ $tab }}', count = null) {
        const modal = document.getElementById('approveAllModal');
        const tabInput = document.getElementById('approve_all_tab');
        const title = document.getElementById('approve_all_modal_title');
        const desc = document.getElementById('approve_all_modal_desc');
        const submitText = document.getElementById('approve_all_submit_text');
        const submitBtn = document.getElementById('approve_all_modal_submit');

        if (tabInput) tabInput.value = targetTab;

        if (targetTab === 'beasiswa') {
            if (title) title.innerHTML = '<i class="bi bi-award-fill" style="color: #D97706; font-size: 20px;"></i> Approve Semua Prestasi Mahasiswa Beasiswa';
            if (desc) desc.innerHTML = `Anda akan menyetujui seluruh <strong>${count ? count + ' ' : ''}data prestasi pending</strong> khusus mahasiswa <strong>Penerima Beasiswa & Ambassador</strong>. Data mahasiswa reguler tidak akan terpengaruh.`;
            if (submitText) submitText.textContent = 'Setujui Semua (Beasiswa)';
            if (submitBtn) submitBtn.style.background = '#059669';
        } else if (targetTab === 'non_beasiswa') {
            if (title) title.innerHTML = '<i class="bi bi-person-check-fill" style="color: #0284C7; font-size: 20px;"></i> Approve Semua Prestasi Mahasiswa Reguler';
            if (desc) desc.innerHTML = `Anda akan menyetujui seluruh <strong>${count ? count + ' ' : ''}data prestasi pending</strong> khusus mahasiswa <strong>Reguler (Non-Beasiswa)</strong>. Data mahasiswa beasiswa tidak akan terpengaruh.`;
            if (submitText) submitText.textContent = 'Setujui Semua (Reguler)';
            if (submitBtn) submitBtn.style.background = '#0284C7';
        } else {
            if (title) title.innerHTML = '<i class="bi bi-check-all" style="color: #16A34A; font-size: 22px;"></i> Approve Semua Prestasi Mahasiswa';
            if (desc) desc.innerHTML = `Anda akan menyetujui seluruh <strong>${count ? count + ' ' : ''}data prestasi pending</strong> untuk <strong>seluruh mahasiswa</strong>.`;
            if (submitText) submitText.textContent = 'Setujui Semua Data';
            if (submitBtn) submitBtn.style.background = '#16A34A';
        }

        modal.style.display = 'flex';
    }

    function closeApproveAllModal() {
        document.getElementById('approveAllModal').style.display = 'none';
    }

    let filterTimeout = null;
    function debounceFilter() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    }

    let currentStudentId = null;
    let currentStudentNama = '';
    let currentStudentNim = '';
    let currentStudentOldBeasiswa = '0%';
    let currentEvaluation = null;
    let activeAchievementsModal = null; // 'regular' or 'scholarship'

    function safeDecodeBase64Json(base64Str, fallbackVal = null) {
        if (!base64Str) return fallbackVal;
        try {
            return JSON.parse(decodeURIComponent(escape(atob(base64Str))));
        } catch (e1) {
            try {
                return JSON.parse(atob(base64Str));
            } catch (e2) {
                console.error("Gagal decode Base64 JSON:", e2);
                return fallbackVal;
            }
        }
    }

    function showRegularStudentAchievementsModal(btn) {
        activeAchievementsModal = 'regular';
        currentStudentId = btn.dataset.studentId;
        const nama = btn.dataset.nama;
        const nim = btn.dataset.nim || '-';
        const achievementsBase64 = btn.dataset.achievements;

        currentStudentNama = nama;
        currentStudentNim = nim;

        const achievements = safeDecodeBase64Json(achievementsBase64, []);

        document.getElementById('regular_achievements_nama').textContent = `${nama} (${nim})`;

        const container = document.getElementById('regular_achievements_table_container');

        if (achievements.length === 0) {
            container.innerHTML = '<p style="text-align:center; color:#666; margin: 20px 0;">Tidak ada prestasi yang sesuai dengan filter saat ini.</p>';
        } else {
            let tableHtml = `
            <table class="data-table" style="margin-top: 0;">
                <thead>
                    <tr>
                        <th style="width: 45px;">No</th>
                        <th>Kategori & Kegiatan</th>
                        <th style="width: 120px;">Tingkat</th>
                        <th style="width: 100px; text-align: center;">Poin SKP</th>
                        <th style="width: 110px;">Status</th>
                        <th style="min-width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
            `;

            achievements.forEach((ach, index) => {
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
                        <span class="badge-year">${escapeHtml(ach.tingkat)}</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge-point point-active" style="font-size: 11px;">
                            ${ach.skp} Poin
                        </span>
                    </td>
                    <td>
                        <span class="status-badge ${ach.statusClass}">${escapeHtml(ach.statusLabel)}</span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <button type="button" class="btn-view" data-ach="${encodedData}" data-nama="${escapeHtml(nama)}" onclick="showDetailModalFromEncoded(this)" style="padding: 4px 8px; font-size: 11px;">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                            <button type="button" class="btn-edit" data-ach="${encodedData}" data-nama="${escapeHtml(nama)}" onclick="showEditModalFromEncoded(this)" style="padding: 4px 8px; font-size: 11px; border-radius: 6px; background-color: #D97706; color: white; border: none; cursor: pointer;" title="Sunting Data Sertifikat">
                                <i class="bi bi-pencil-square"></i> Sunting
                            </button>
                            ${ach.status === 'approved' ? `
                                <form action="/kemahasiswaan/verifikasi-prestasi/${ach.id}/unapprove" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin membatalkan approval data ini? Status akan kembali menjadi pending.');">
                                    @csrf
                                    <button type="submit" class="btn-reject" style="padding: 4px 8px; font-size: 11px; border-radius: 6px; background-color: #64748B; color: white; border: none;" title="Batal Approve">
                                        <i class="bi bi-arrow-counterclockwise"></i> Batal
                                    </button>
                                </form>
                            ` : `
                                <button type="button" class="btn-approve" onclick="showApproveModal(${ach.id})" style="padding: 4px 8px; font-size: 11px; border-radius: 6px;" title="Approve">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button type="button" class="btn-reject" onclick="showRejectModal(${ach.id})" style="padding: 4px 8px; font-size: 11px; border-radius: 6px;" title="Reject">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            `}
                        </div>
                    </td>
                </tr>
                `;
            });

            tableHtml += `</tbody></table>`;
            container.innerHTML = tableHtml;
        }

        document.getElementById('regularStudentAchievementsModal').style.display = 'flex';
    }

    function closeRegularStudentAchievementsModal() {
        document.getElementById('regularStudentAchievementsModal').style.display = 'none';
    }

    function showStudentAchievementsModal(btn, initialTab) {
        activeAchievementsModal = 'scholarship';
        currentStudentId = btn.dataset.studentId;
        const nama = btn.dataset.nama;
        const nim = btn.dataset.nim || '-';
        const ipk = btn.dataset.ipk || '-';
        const poin = btn.dataset.poin || '0';
        const statusMhs = btn.dataset.statusMhs || 'Mahasiswa';
        const beasiswa = btn.dataset.beasiswa || '-';
        const isBeasiswa = btn.dataset.isBeasiswa === '1';
        const achievementsBase64 = btn.dataset.achievements;
        const evaluationBase64 = btn.dataset.evaluation;

        currentStudentNama = nama;
        currentStudentNim = nim;
        currentStudentOldBeasiswa = beasiswa;
        const achievements = safeDecodeBase64Json(achievementsBase64, []);
        const evaluation = safeDecodeBase64Json(evaluationBase64, null);
        currentEvaluation = evaluation;

        document.getElementById('student_achievements_nama').textContent = nama;
        document.getElementById('meta_nim').textContent = nim;
        document.getElementById('meta_semester').textContent = (evaluation && evaluation.semester) ? ('Sem ' + evaluation.semester) : '-';
        document.getElementById('meta_ipk').textContent = (evaluation && evaluation.ipk) ? evaluation.ipk : ipk;
        document.getElementById('meta_poin').textContent = poin + ' Poin';
        document.getElementById('meta_beasiswa').textContent = beasiswa;
        document.getElementById('meta_study_year').textContent = (evaluation && evaluation.study_year) ? ('Th ' + evaluation.study_year) : '-';
        document.getElementById('tab_cert_count').textContent = achievements.length;

        const badgesContainer = document.getElementById('student_achievements_badges');
        if (badgesContainer) {
            let badgeHtml = '';
            if (statusMhs === 'Student Ambassador') {
                badgeHtml += `<span class="badge-status-mhs badge-ambassador" style="font-size: 11px; padding: 2px 8px;">Student Ambassador</span>`;
            } else if (isBeasiswa) {
                badgeHtml += `<span class="badge-beasiswa" style="font-size: 11px; padding: 2px 8px;">Beasiswa ${escapeHtml(beasiswa)}</span>`;
            } else {
                badgeHtml += `<span class="badge-status-mhs badge-reguler" style="font-size: 11px; padding: 2px 8px;">Mahasiswa Reguler (Non-Beasiswa)</span>`;
            }
            badgesContainer.innerHTML = badgeHtml;
        }

        // Render evaluasi POKEMA 7 Bidang & Rekomendasi Beasiswa
        renderPokemaEvaluation(evaluation);
        const targetTab = initialTab || (isBeasiswa ? 'evaluation' : 'achievements');
        switchModalTab(targetTab);

        const container = document.getElementById('achievements_table_container');

        if (achievements.length === 0) {
            container.innerHTML = '<p style="text-align:center; color:#666; margin: 20px 0;">Tidak ada prestasi yang sesuai dengan filter saat ini.</p>';
        } else {
            let tableHtml = `
            <table class="data-table" style="margin-top: 0;">
                <thead>
                    <tr>
                        <th style="width: 45px;">No</th>
                        <th>Kategori & Kegiatan</th>
                        <th style="width: 120px;">Tingkat</th>
                        <th style="width: 100px; text-align: center;">Poin SKP</th>
                        <th style="width: 110px;">Status</th>
                        <th style="min-width: 220px;">Aksi</th>
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
                        <span class="badge-year">${ach.tingkat}</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge-point point-active">${ach.skp} SKP</span>
                    </td>
                    <td>
                        <span class="status-badge ${ach.statusClass}">${ach.statusLabel}</span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <button type="button" class="btn-view" data-ach="${encodedData}" data-nama="${escapeHtml(nama)}" onclick="showDetailModalFromEncoded(this)" style="padding: 4px 8px; font-size: 11px;">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                            <button type="button" class="btn-edit" data-ach="${encodedData}" data-nama="${escapeHtml(nama)}" onclick="showEditModalFromEncoded(this)" style="padding: 4px 8px; font-size: 11px; border-radius: 6px; background-color: #D97706; color: white; border: none; cursor: pointer;" title="Sunting Data Prestasi">
                                <i class="bi bi-pencil-square"></i> Sunting
                            </button>
                            ${ach.status === 'approved' ? `
                                <form action="/kemahasiswaan/verifikasi-prestasi/${ach.id}/unapprove" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin membatalkan approval data ini? Status akan kembali menjadi pending.');">
                                    @csrf
                                    <button type="submit" class="btn-reject" style="padding: 4px 8px; font-size: 11px; border-radius: 6px; background-color: #64748B; color: white; border: none;" title="Batal Approve">
                                        <i class="bi bi-arrow-counterclockwise"></i> Batal
                                    </button>
                                </form>
                            ` : `
                                <button type="button" class="btn-approve" onclick="showApproveModal(${ach.id})" style="padding: 4px 8px; font-size: 11px; border-radius: 6px;" title="Approve">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button type="button" class="btn-reject" onclick="showRejectModal(${ach.id})" style="padding: 4px 8px; font-size: 11px; border-radius: 6px;" title="Reject">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            `}
                        </div>
                    </td>
                </tr>
                `;
            });

            tableHtml += `</tbody></table>`;
            container.innerHTML = tableHtml;
        }

        document.getElementById('studentAchievementsModal').style.display = 'flex';
    }

    function closeStudentAchievementsModal() {
        document.getElementById('studentAchievementsModal').style.display = 'none';
    }

    function switchModalTab(tab) {
        const tabEval = document.getElementById('tabContentEvaluation');
        const tabAch = document.getElementById('tabContentAchievements');
        const btnEval = document.getElementById('tabBtnEvaluation');
        const btnAch = document.getElementById('tabBtnAchievements');

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

    function renderPokemaEvaluation(evalData) {
        const recContainer = document.getElementById('smart_recommendation_card');
        const barsContainer = document.getElementById('pokema_7_bars_grid');

        if (!evalData) {
            recContainer.innerHTML = '<p class="text-muted" style="padding: 10px;">Data evaluasi belum tersedia.</p>';
            barsContainer.innerHTML = '';
            return;
        }

        // Set active year toggle
        const currentYear = String(evalData.study_year || '1');
        ['1', '2', '3', 'total'].forEach(y => {
            const btn = document.getElementById('btnYear' + (y === 'total' ? 'Total' : y));
            if (btn) {
                if (String(y) === currentYear || (y === 'total' && (currentYear === '4' || currentYear === 'total'))) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            }
        });

        // 1. Recommendation Card
        const recType = evalData.action_type || 'maintain';
        let iconClass = 'bi-shield-check';
        let badgeLabel = 'STATUS PRESTASI';
        let titleText = escapeHtml(evalData.action_label || 'Status Prestasi Mahasiswa');

        if (recType === 'on_track') {
            iconClass = 'bi-graph-up-arrow';
            badgeLabel = 'PROGRES BERJALAN (ON TRACK)';
        } else if (recType === 'upgrade') {
            iconClass = 'bi-arrow-up-circle-fill';
            badgeLabel = 'REKOMENDASI PROMOSI';
        } else if (recType === 'maintain') {
            iconClass = 'bi-shield-check';
            badgeLabel = 'TARGET PERIODE TERPENUHI';
        } else if (recType === 'warning') {
            iconClass = 'bi-exclamation-triangle-fill';
            badgeLabel = 'PERLU PERHATIAN';
        } else if (recType === 'downgrade') {
            iconClass = 'bi-arrow-down-circle-fill';
            badgeLabel = 'EVALUASI AKHIR PERIODE';
        } else if (recType === 'revoke') {
            iconClass = 'bi-exclamation-octagon-fill';
            badgeLabel = 'EVALUASI AKHIR PERIODE';
        } else if (recType === 'reguler') {
            iconClass = 'bi-mortarboard-fill';
            badgeLabel = 'STANDAR BEASISWA BELUM TERCAPAI';
        } else if (recType === 'future') {
            iconClass = 'bi-calendar-range-fill';
            badgeLabel = 'TARGET PERIODE MENDATANG';
        }

        let smartBtnHtml = '';
        const safeName = escapeHtml(currentStudentNama);
        const safeNim = escapeHtml(currentStudentNim);
        const safeOldBeasiswa = escapeHtml(evalData.current_beasiswa || '0%');
        const safeNewBeasiswa = escapeHtml(evalData.recommended_beasiswa || evalData.qualified_beasiswa || evalData.current_beasiswa || '0%');
        const safeReason = escapeHtml(evalData.reason || '');
        const studentIpk = (evalData.ipk !== null && evalData.ipk !== undefined) ? evalData.ipk : '-';
        const studentPoin = evalData.total_approved_points || 0;
        const studentPeriode = escapeHtml(evalData.study_year_label || '');
        const studentTarget = evalData.target_current || evalData.total_target_points || 0;
        const studentFulfilledCount = evalData.fulfilled_fields_count || 0;
        const studentAllFieldsMet = evalData.all_fields_met ? 1 : 0;
        const studentUnfulfilledRaw = btoa(JSON.stringify(evalData.unfulfilled_fields_list || []));

        if (recType === 'on_track') {
            smartBtnHtml = `
                <button type="button" class="btn-smart-action btn-on_track" onclick="switchModalTab('achievements')">
                    Periksa Prestasi Masuk
                </button>
            `;
        } else if (recType === 'upgrade') {
            smartBtnHtml = `
                <button type="button" class="btn-smart-action btn-upgrade" onclick="openAdjustBeasiswaModal('${currentStudentId}', '${safeName}', '${safeNim}', '${safeOldBeasiswa}', '${safeNewBeasiswa}', '${safeReason}', false, '${studentIpk}', '${studentPoin}', '${studentPeriode}', '${studentTarget}', ${studentFulfilledCount}, ${studentAllFieldsMet}, '${studentUnfulfilledRaw}')">
                    Terapkan Promosi (${safeNewBeasiswa})
                </button>
            `;
        } else if (recType === 'warning') {
            smartBtnHtml = `
                <button type="button" class="btn-smart-action btn-warning" onclick="switchModalTab('achievements')">
                    Cek Prestasi Mahasiswa
                </button>
            `;
        } else if (recType === 'downgrade') {
            smartBtnHtml = `
                <button type="button" class="btn-smart-action btn-downgrade" onclick="openAdjustBeasiswaModal('${currentStudentId}', '${safeName}', '${safeNim}', '${safeOldBeasiswa}', '${safeNewBeasiswa}', '${safeReason}', false, '${studentIpk}', '${studentPoin}', '${studentPeriode}', '${studentTarget}', ${studentFulfilledCount}, ${studentAllFieldsMet}, '${studentUnfulfilledRaw}')">
                    Sesuaikan ke Beasiswa ${safeNewBeasiswa}
                </button>
            `;
        } else if (recType === 'revoke') {
            smartBtnHtml = `
                <button type="button" class="btn-smart-action btn-revoke" onclick="openAdjustBeasiswaModal('${currentStudentId}', '${safeName}', '${safeNim}', '${safeOldBeasiswa}', '0%', '${safeReason}', false, '${studentIpk}', '${studentPoin}', '${studentPeriode}', '${studentTarget}', ${studentFulfilledCount}, ${studentAllFieldsMet}, '${studentUnfulfilledRaw}')">
                    Cabut Beasiswa (Turun Reguler)
                </button>
            `;
        } else if (recType === 'reguler') {
            smartBtnHtml = `
                <button type="button" class="btn-smart-action" style="background: #0284C7; color: #FFFFFF;" onclick="switchModalTab('achievements')">
                    Periksa & Verifikasi Prestasi
                </button>
            `;
        } else if (recType === 'future') {
            smartBtnHtml = `
                <button type="button" class="btn-smart-action btn-future" onclick="switchModalTab('achievements')">
                    Lihat Rincian Prestasi
                </button>
            `;
        } else if (evalData.current_beasiswa && evalData.current_beasiswa !== '0%') {
            smartBtnHtml = `
                <button type="button" class="btn-smart-action btn-maintain" onclick="openAdjustBeasiswaModal('${currentStudentId}', '${safeName}', '${safeNim}', '${safeOldBeasiswa}', '${safeOldBeasiswa}', '${safeReason}', false, '${studentIpk}', '${studentPoin}', '${studentPeriode}', '${studentTarget}', ${studentFulfilledCount}, ${studentAllFieldsMet}, '${studentUnfulfilledRaw}')">
                    Pertahankan Beasiswa (${safeOldBeasiswa})
                </button>
            `;
        }

        const manualBtnHtml = (recType !== 'reguler') ? `
            <button type="button" class="btn-smart-action btn-manual" onclick="openAdjustBeasiswaModal('${currentStudentId}', '${safeName}', '${safeNim}', '${safeOldBeasiswa}', '${safeNewBeasiswa}', '', true, '${studentIpk}', '${studentPoin}', '${studentPeriode}', '${studentTarget}', ${studentFulfilledCount}, ${studentAllFieldsMet}, '${studentUnfulfilledRaw}')">
                Sesuaikan Manual
            </button>
        ` : '';

        // Live progress box
        let progressBoxHtml = '';
        const targetPts = evalData.target_current || evalData.total_target_points || 0;
        const currentPts = evalData.total_approved_points || 0;
        const pct = (evalData.progress_pct !== undefined && evalData.progress_pct !== null)
            ? evalData.progress_pct
            : (targetPts > 0 ? Math.round((currentPts / targetPts) * 1000) / 10 : 0);
        const remaining = (evalData.points_remaining !== undefined && evalData.points_remaining !== null)
            ? evalData.points_remaining
            : Math.max(0, targetPts - currentPts);

        if (targetPts > 0 && recType !== 'reguler') {
            const statusTag = remaining > 0
                ? `<span class="rec-remaining-tag">Sisa target: <strong>${remaining} Poin</strong></span>`
                : `<span class="rec-completed-tag">Target Poin Tercapai</span>`;

            progressBoxHtml = `
                <div class="rec-progress-box">
                    <div class="rec-progress-info">
                        <span>
                            Capaian: <strong>${currentPts} / ${targetPts} Poin</strong> (${pct}%)
                            ${evalData.ipk ? ` &bull; IPK: <strong>${evalData.ipk}</strong>` : ''}
                        </span>
                        ${statusTag}
                    </div>
                    <div class="rec-progress-track">
                        <div class="rec-progress-bar bar-${recType}" style="width: ${Math.min(100, Math.max(0, pct))}%;"></div>
                    </div>
                </div>
            `;
        }

        recContainer.className = `rec-card rec-${recType}`;
        recContainer.innerHTML = `
            <div class="rec-main-content">
                <div class="rec-top-meta">
                    <span class="rec-status-pill pill-${recType}">
                        ${badgeLabel}
                    </span>
                    <span class="rec-period-badge">
                        Periode: <strong>${escapeHtml(evalData.study_year_label || 'Tahun I')}</strong>
                        &bull; Target Acuan: <strong>Beasiswa ${escapeHtml(evalData.benchmark_tier_key || evalData.current_beasiswa || '75%')} (${targetPts} Poin)</strong>
                    </span>
                </div>
                <h4 class="rec-title">${titleText}</h4>
                <p class="rec-reason">${escapeHtml(evalData.reason)}</p>
                ${progressBoxHtml}
            </div>
            <div class="rec-actions">
                ${smartBtnHtml}
                ${manualBtnHtml}
            </div>
        `;

        // 2. Rincian & Akumulasi POKEMA Per Semester Grid
        const semContainer = document.getElementById('pokema_semester_bars_grid');
        if (semContainer) {
            let semHtml = '';
            
            // Dapatkan year_groups (didukung langsung dari API atau fallback dari breakdown)
            let yearGroups = evalData.year_groups;
            if (!yearGroups || !yearGroups.length) {
                const breakdown = evalData.semester_breakdown || [];
                const groupsMap = {};
                breakdown.forEach(sem => {
                    const y = sem.study_year || 1;
                    if (!groupsMap[y]) {
                        groupsMap[y] = {
                            study_year: y,
                            study_year_label: sem.study_year_label || `Tahun ${y}`,
                            semesters_range: y === 1 ? 'Semester 1–3' : (y === 2 ? 'Semester 4–6' : 'Semester 7–9'),
                            year_target: sem.year_target || 0,
                            total_points: 0,
                            is_current_year: false,
                            semesters: []
                        };
                    }
                    groupsMap[y].total_points += (sem.semester_points || 0);
                    if (sem.is_current) groupsMap[y].is_current_year = true;
                    groupsMap[y].semesters.push(sem);
                });
                yearGroups = Object.values(groupsMap).map(g => {
                    const yTgt = g.year_target;
                    const yPts = g.total_points;
                    g.progress_percent = yTgt > 0 ? Math.min(100, Math.round((yPts / yTgt) * 100)) : (yPts > 0 ? 100 : 0);
                    g.is_target_met = yTgt > 0 ? (yPts >= yTgt) : (yPts > 0);
                    g.points_remaining = Math.max(0, yTgt - yPts);
                    return g;
                });
            }

            if (!yearGroups || yearGroups.length === 0) {
                semContainer.innerHTML = '<p style="color: #94A3B8; font-size: 13px; margin: 0; padding: 10px 0;">Belum ada riwayat semester.</p>';
            } else {
                yearGroups.forEach(yGroup => {
                    const isCurYear = yGroup.is_current_year;
                    const isTargetMet = yGroup.is_target_met;
                    const pct = yGroup.progress_percent || 0;
                    
                    let noticeHtml = '';
                    if (isCurYear) {
                        if (isTargetMet) {
                            noticeHtml = `
                                <div class="year-period-notice notice-success">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span><strong>Target ${escapeHtml(yGroup.study_year_label)} Terpenuhi!</strong> Akumulasi saat ini <strong>${yGroup.total_points} Poin</strong> (Target acuan: ${yGroup.year_target} Poin).</span>
                                </div>
                            `;
                        } else {
                            noticeHtml = `
                                <div class="year-period-notice notice-warning">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span>Evaluasi beasiswa dinilai dari total akumulasi <strong>${escapeHtml(yGroup.semesters_range)}</strong> (Target: <strong>${yGroup.year_target} Poin</strong>). Mahasiswa butuh <strong>${yGroup.points_remaining} Poin lagi</strong> sebelum evaluasi akhir periode.</span>
                                </div>
                            `;
                        }
                    }

                    let semCardsHtml = '';
                    (yGroup.semesters || []).forEach(sem => {
                        const isCur = sem.is_current;
                        const isFuture = sem.semester > (evalData.semester || 1);
                        const currentBadge = isCur ? '<span class="badge-current-sem">Semester Aktif</span>' : '';
                        const isMilestone = sem.is_period_milestone;
                        const milestoneBadge = isMilestone ? '<span class="badge-milestone-sem"><i class="bi bi-flag-fill"></i> Evaluasi Akhir</span>' : '';
                        
                        const ipkBadge = sem.ipk 
                            ? `<span class="sem-meta-pill pill-ipk">IPK: <strong>${sem.ipk}</strong></span>` 
                            : (isCur 
                                ? '<span class="sem-meta-pill pill-empty">Belum ada nilai IPK</span>' 
                                : (isFuture 
                                    ? '<span class="sem-meta-pill pill-empty">Belum Berjalan</span>' 
                                    : '<span class="sem-meta-pill pill-empty">IPK belum tercatat</span>')
                              );

                        const semProgressPct = sem.semester_progress_percent !== undefined ? sem.semester_progress_percent : 0;
                        const isSemTargetMet = sem.is_semester_target_met;
                        
                        let semStatusBadge = '';
                        if (sem.semester_points >= sem.semester_target && sem.semester_target > 0) {
                            semStatusBadge = '<span style="color: #059669; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"><i class="bi bi-check-circle-fill"></i> Target Ideal Tercapai</span>';
                        } else if (sem.semester_points > 0) {
                            semStatusBadge = `<span style="color: #0284C7; font-size: 11px; font-weight: 600;">+${sem.semester_points} Poin ke ${escapeHtml(yGroup.study_year_label)}</span>`;
                        } else if (isCur) {
                            semStatusBadge = '<span style="color: #64748B; font-size: 11px;">Sedang berjalan</span>';
                        } else {
                            semStatusBadge = '<span style="color: #94A3B8; font-size: 11px;">Semester belum dimulai</span>';
                        }

                        let milestoneBoxHtml = '';
                        if (isMilestone) {
                            if (isTargetMet) {
                                milestoneBoxHtml = `
                                    <div class="sem-milestone-box milestone-ok">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>Target Beasiswa ${escapeHtml(yGroup.study_year_label)} Aman (${yGroup.total_points}/${yGroup.year_target} Poin)</span>
                                    </div>
                                `;
                            } else {
                                milestoneBoxHtml = `
                                    <div class="sem-milestone-box milestone-pending">
                                        <i class="bi bi-flag-fill"></i>
                                        <span>Titik Evaluasi: Kurang ${yGroup.points_remaining} Poin menuju target</span>
                                    </div>
                                `;
                            }
                        }

                        semCardsHtml += `
                            <div class="sem-grid-card ${isCur ? 'active' : ''} ${isMilestone ? 'milestone' : ''}">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 6px; margin-bottom: 8px;">
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                <span class="sem-title" style="font-size: 13px;">${escapeHtml(sem.semester_label)}</span>
                                                ${currentBadge}
                                                ${milestoneBadge}
                                            </div>
                                        </div>
                                        <div>
                                            ${ipkBadge}
                                        </div>
                                    </div>

                                    <div style="margin-bottom: 8px;">
                                        <div class="sem-card-points-label">Perolehan Semester:</div>
                                        <div style="display: flex; align-items: baseline; gap: 6px;">
                                            <span class="sem-card-points-val ${sem.semester_points === 0 ? 'zero' : ''}">${sem.semester_points} Poin</span>
                                            <span style="font-size: 11px; color: #64748B;">(${sem.achievements_count} Prestasi Disetujui)</span>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="sem-card-target-text">
                                            <span>Target ideal: <strong>${sem.semester_target} Poin</strong></span>
                                            ${semStatusBadge}
                                        </div>
                                        <div class="progress-track" style="height: 6px; background: #E2E8F0; border-radius: 999px;">
                                            <div class="progress-fill ${isSemTargetMet ? 'fill-green' : 'fill-blue'}" style="width: ${Math.min(100, Math.max(0, semProgressPct))}%; height: 100%; border-radius: 999px;"></div>
                                        </div>
                                    </div>
                                </div>

                                ${milestoneBoxHtml}
                            </div>
                        `;
                    });

                    semHtml += `
                        <div class="year-period-card ${isCurYear ? 'current-year' : ''}">
                            <div class="year-period-header">
                                <div class="year-period-title-wrap">
                                    <div class="year-period-title">
                                        <span class="year-pill"><i class="bi bi-calendar3 me-1 text-primary"></i> ${escapeHtml(yGroup.study_year_label)}</span>
                                        <span class="year-sem-range">${escapeHtml(yGroup.semesters_range)}</span>
                                        ${isCurYear ? '<span class="badge-year-current">Tahun Berjalan</span>' : ''}
                                    </div>
                                    <div class="year-period-target-sub">
                                        Target Acuan Evaluasi: <strong>${yGroup.year_target} Poin</strong> (SK Rektor)
                                    </div>
                                </div>
                                <div class="year-period-status-wrap">
                                    <div class="year-period-accum">
                                        <span>Total Akumulasi: <strong style="color: #0F172A; font-size: 15px;">${yGroup.total_points}</strong> / ${yGroup.year_target} Poin</span>
                                        <span class="year-accum-badge ${isTargetMet ? 'badge-met' : 'badge-progress'}">
                                            ${isTargetMet ? 'Target Terpenuhi' : `Kurang ${yGroup.points_remaining} Poin`}
                                        </span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="progress-track" style="height: 8px; width: 140px; background: #E2E8F0; border-radius: 999px;">
                                            <div class="progress-fill ${isTargetMet ? 'fill-green' : 'fill-blue'}" style="width: ${pct}%; height: 100%; border-radius: 999px;"></div>
                                        </div>
                                        <span style="font-size: 12px; font-weight: 700; color: #475569;">${pct}%</span>
                                    </div>
                                </div>
                            </div>

                            ${noticeHtml}

                            <div class="year-semesters-grid">
                                ${semCardsHtml}
                            </div>
                        </div>
                    `;
                });
                semContainer.innerHTML = semHtml;
            }
        }

        // 3. 7 Fields Summary Banner
        const summaryAlert = document.getElementById('pokema_fields_summary_alert');
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
                            <span>Seluruh 7 Bidang POKEMA Terpenuhi (${fulfilledCount}/7 Bidang Sesuai Standar)</span>
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
                            <span>Sebaran Bidang: <strong>${fulfilledCount} dari ${totalFields} Bidang Terpenuhi</strong> (${unfulfilledCount} Bidang Masih Belum Mencukupi)</span>
                        </div>
                        <span style="background: #F59E0B; color: white; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">Wajib Lengkap 7 Bidang</span>
                    </div>
                `;
            }
        }

        // 4. 7 Progress Bars Grid (Clean & Minimalist without Icons)
        let barsHtml = '';
        const progressList = Object.values(evalData.fields_progress || {});

        progressList.forEach(f => {
            const isOk = f.is_fulfilled !== undefined ? !!f.is_fulfilled : (f.target > 0 && f.current >= f.target);
            const fillClass = isOk ? 'fill-green' : 'fill-blue';
            const deficit = Math.max(0, (f.target || 0) - (f.current || 0));
            const badgeHtml = isOk
                ? `<span class="badge-field-status status-ok">Terpenuhi</span>`
                : `<span class="badge-field-status status-kurang">Kurang ${deficit} Poin</span>`;

            barsHtml += `
                <div class="pokema-field-row">
                    <div class="field-title-wrap">
                        <span class="field-name">
                            ${escapeHtml(f.label)}
                        </span>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="field-score">
                                <strong>${f.current}</strong> / ${f.target} Poin
                            </span>
                            ${badgeHtml}
                        </div>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill ${fillClass}" style="width: ${Math.min(100, Math.max(0, f.percent))}%;"></div>
                    </div>
                </div>
            `;
        });

        barsContainer.innerHTML = barsHtml;
    }

    function changeEvaluationYear(year) {
        if (!currentStudentId) return;

        fetch(`/kemahasiswaan/verifikasi-prestasi/students/${currentStudentId}/evaluation-data?year=${year}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.evaluation) {
                    currentEvaluation = data.evaluation;
                    renderPokemaEvaluation(data.evaluation);
                }
            })
            .catch(err => {
                console.error("Gagal memuat evaluasi tahun:", err);
            });
    }

    const POKEMA_RULES = {
        '100%': { name: 'Beasiswa 100%', minIpk: 3.75, targetTh1: 180, targetTh2: 180, targetTotal: 450, level: 4 },
        '75%':  { name: 'Beasiswa 75%',  minIpk: 3.50, targetTh1: 140, targetTh2: 140, targetTotal: 351, level: 3 },
        '50%':  { name: 'Beasiswa 50%',  minIpk: 3.25, targetTh1: 120, targetTh2: 120, targetTotal: 301, level: 2 },
        '25%':  { name: 'Beasiswa 25%',  minIpk: 3.00, targetTh1: 120, targetTh2: 120, targetTotal: 300, level: 1 },
        '0%':   { name: 'Non-Beasiswa (Reguler)', minIpk: 0.0, targetTh1: 0, targetTh2: 0, targetTotal: 250, level: 0 },
    };

    let currentAdjustStudentData = {
        studentId: null,
        nama: '',
        nim: '',
        oldBeasiswa: '0%',
        targetBeasiswa: '0%',
        recommendedBeasiswa: '0%',
        reason: '',
        isManual: false,
        ipk: null,
        poin: 0,
        periode: '',
        targetPoints: 0
    };

    function getSmartRecommendedTier(ipk, poin, periodLabel = 'Tahun I') {
        const ipkNum = parseFloat(ipk);
        const poinNum = parseInt(poin, 10) || 0;
        let yr = 1;
        if (typeof periodLabel === 'string') {
            if (periodLabel.includes('III') || periodLabel.includes('3')) yr = 3;
            else if (periodLabel.includes('II') || periodLabel.includes('2')) yr = 2;
            else yr = 1;
        }

        // Target ambang batas SK Rektor No: 2638/SK/01/2026:
        // Th I & II: 100% >= 179 (~180), 75% >= 140, 50% >= 120, 25% >= 120
        // Th III:    100% >= 92,         75% >= 71,  50% >= 61,  25% >= 61
        const targets = {
            '100%': { minIpk: 3.75, target: (yr === 3 ? 92 : 179) },
            '75%':  { minIpk: 3.50, target: (yr === 3 ? 71 : 140) },
            '50%':  { minIpk: 3.25, target: (yr === 3 ? 61 : 120) },
            '25%':  { minIpk: 3.00, target: (yr === 3 ? 61 : 120) },
        };

        if (isNaN(ipkNum)) return '0%';

        for (let t of ['100%', '75%', '50%', '25%']) {
            const rule = targets[t];
            if (ipkNum >= rule.minIpk && poinNum >= rule.target) {
                return t;
            }
        }

        return '0%';
    }

    function togglePedomanTable() {
        const wrap = document.getElementById('pedoman_table_wrap');
        const icon = document.getElementById('pedoman_table_icon');
        if (!wrap) return;
        if (wrap.style.display === 'none' || wrap.style.display === '') {
            wrap.style.display = 'block';
            if (icon) {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            }
        } else {
            wrap.style.display = 'none';
            if (icon) {
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            }
        }
    }

    function openAdjustBeasiswaModal(studentId, nama, nim, oldBeasiswa, targetBeasiswa, reason, isManual = false, ipkVal = null, poinVal = null, periodLabel = null, targetPointsVal = null, fulfilledCountVal = null, allFieldsMetVal = null, unfulfilledListRaw = null) {
        const modal = document.getElementById('adjustBeasiswaModal');
        const form = document.getElementById('adjustBeasiswaForm');
        form.action = `/kemahasiswaan/verifikasi-prestasi/students/${studentId}/update-beasiswa`;

        // Ambil data evaluasi lengkap jika ada
        let evalObj = null;
        if (typeof currentEvaluation !== 'undefined' && currentEvaluation && currentEvaluation.student_id == studentId) {
            evalObj = currentEvaluation;
        }

        // Fallbacks from currentEvaluation if values are not provided directly
        let finalIpk = ipkVal;
        let finalPoin = poinVal;
        let finalPeriode = periodLabel;
        let finalTargetPts = targetPointsVal;
        let finalFulfilledCount = fulfilledCountVal;
        let finalAllFieldsMet = allFieldsMetVal;
        let finalUnfulfilledList = [];

        if (evalObj) {
            if (!finalIpk || finalIpk === '-' || finalIpk === 'null') finalIpk = evalObj.ipk;
            if (finalPoin === null || finalPoin === undefined || finalPoin === '' || finalPoin === '0') finalPoin = evalObj.total_approved_points;
            if (!finalPeriode || finalPeriode === 'null') finalPeriode = evalObj.study_year_label || `Tahun ${evalObj.study_year || 1}`;
            if (!finalTargetPts || finalTargetPts === '0') finalTargetPts = evalObj.target_current || evalObj.total_target_points || 0;
            if (finalFulfilledCount === null || finalFulfilledCount === undefined) finalFulfilledCount = evalObj.fulfilled_fields_count;
            if (finalAllFieldsMet === null || finalAllFieldsMet === undefined) finalAllFieldsMet = evalObj.all_fields_met;
            if (evalObj.unfulfilled_fields_list && Array.isArray(evalObj.unfulfilled_fields_list)) {
                finalUnfulfilledList = evalObj.unfulfilled_fields_list;
            }
        }

        if (unfulfilledListRaw) {
            try {
                if (typeof unfulfilledListRaw === 'string' && unfulfilledListRaw.length > 0) {
                    finalUnfulfilledList = JSON.parse(atob(unfulfilledListRaw));
                } else if (Array.isArray(unfulfilledListRaw)) {
                    finalUnfulfilledList = unfulfilledListRaw;
                }
            } catch (e) {
                console.warn('Error parsing unfulfilled list:', e);
            }
        }

        // Rekomendasi Resmi Sistem:
        // Prioritaskan rekomendasi hasil evaluasi backend PHP yang telah mengkalkulasi IPK + 7 Bidang + Status Portal
        let resolvedRec = null;
        if (evalObj && (evalObj.recommended_beasiswa || evalObj.qualified_beasiswa)) {
            resolvedRec = evalObj.recommended_beasiswa || evalObj.qualified_beasiswa;
        }
        if (!resolvedRec && targetBeasiswa && targetBeasiswa !== '') {
            resolvedRec = targetBeasiswa;
        }
        if (!resolvedRec) {
            resolvedRec = getSmartRecommendedTier(finalIpk, finalPoin, finalPeriode);
        }
        if (!resolvedRec) {
            resolvedRec = oldBeasiswa || '0%';
        }

        // Tentukan target pilihan default di dropdown:
        // Jika targetBeasiswa diberikan (misal dari tombol aksi promosi/penyesuaian), gunakan targetBeasiswa
        // Jika mode manual, gunakan status saat ini mahasiswa (oldBeasiswa) agar admin dapat mengevaluasi
        let autoSelectedTarget = targetBeasiswa;
        if (!autoSelectedTarget || autoSelectedTarget === '') {
            autoSelectedTarget = resolvedRec || oldBeasiswa || '0%';
        }

        currentAdjustStudentData = {
            studentId: studentId,
            nama: nama,
            nim: nim,
            oldBeasiswa: oldBeasiswa || '0%',
            targetBeasiswa: autoSelectedTarget || '0%',
            recommendedBeasiswa: resolvedRec || '0%',
            reason: reason || '',
            isManual: isManual,
            ipk: (finalIpk !== null && finalIpk !== undefined && finalIpk !== '' && finalIpk !== '-') ? parseFloat(finalIpk) : null,
            poin: parseInt(finalPoin, 10) || 0,
            periode: finalPeriode || 'Tahun I',
            targetPoints: parseInt(finalTargetPts, 10) || 0,
            fulfilledCount: (finalFulfilledCount !== null && finalFulfilledCount !== undefined) ? parseInt(finalFulfilledCount, 10) : null,
            allFieldsMet: (finalAllFieldsMet === true || finalAllFieldsMet === 1 || finalAllFieldsMet === '1'),
            unfulfilledList: finalUnfulfilledList
        };

        document.getElementById('adjust_mhs_nama').textContent = nama;
        document.getElementById('adjust_mhs_nim').textContent = nim;
        document.getElementById('adjust_old_beasiswa').textContent = (oldBeasiswa && oldBeasiswa !== '0%') ? `Beasiswa ${oldBeasiswa}` : 'Non-Beasiswa (0%)';
        
        // Update badge Rekomendasi Sistem
        const recBadge = document.getElementById('adjust_rec_beasiswa_badge');
        if (recBadge) {
            if (resolvedRec === '0%') {
                recBadge.style.color = '#D97706';
                recBadge.innerHTML = `<span style="display: inline-flex; align-items: center; gap: 4px;"><i class="bi bi-clock-history"></i> 0% (Belum Memenuhi Target)</span>`;
            } else {
                recBadge.style.color = '#059669';
                recBadge.innerHTML = `<span style="display: inline-flex; align-items: center; gap: 4px;"><i class="bi bi-award-fill"></i> Beasiswa ${resolvedRec}</span>`;
            }
        }

        // IPK badge
        const ipkElem = document.getElementById('adjust_mhs_ipk');
        if (ipkElem) {
            if (currentAdjustStudentData.ipk !== null && !isNaN(currentAdjustStudentData.ipk)) {
                ipkElem.innerHTML = `<span class="badge" style="background: #EFF6FF; color: #1D4ED8; font-size: 14px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">${currentAdjustStudentData.ipk.toFixed(2)}</span>`;
            } else {
                ipkElem.innerHTML = `<span class="badge" style="background: #F1F5F9; color: #64748B; font-size: 13px; font-weight: 600; padding: 2px 8px; border-radius: 6px;">Belum Ada</span>`;
            }
        }

        // Poin badge
        const poinElem = document.getElementById('adjust_mhs_poin');
        if (poinElem) {
            let ptsText = `${currentAdjustStudentData.poin} Poin`;
            if (currentAdjustStudentData.targetPoints > 0) {
                ptsText = `${currentAdjustStudentData.poin} / ${currentAdjustStudentData.targetPoints} Poin`;
            }
            poinElem.innerHTML = `<span class="badge" style="background: #FEF3C7; color: #B45309; font-size: 14px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">${ptsText}</span>`;
        }

        // Sebaran 7 Bidang badge
        const bidangElem = document.getElementById('adjust_mhs_bidang');
        const bidangIcon = document.getElementById('adjust_mhs_bidang_icon');
        if (bidangElem) {
            const isAllMet = currentAdjustStudentData.allFieldsMet;
            const fCount = currentAdjustStudentData.fulfilledCount;
            if (fCount !== null && fCount !== undefined) {
                if (isAllMet || fCount >= 7) {
                    bidangElem.innerHTML = `<span class="badge" style="background: #ECFDF5; color: #047857; font-size: 13.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">7/7 Bidang ✓</span>`;
                    if (bidangIcon) {
                        bidangIcon.style.background = '#ECFDF5';
                        bidangIcon.style.color = '#059669';
                    }
                } else {
                    bidangElem.innerHTML = `<span class="badge" style="background: #FEF3C7; color: #B45309; font-size: 13.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">${fCount}/7 Bidang</span>`;
                    if (bidangIcon) {
                        bidangIcon.style.background = '#FEF3C7';
                        bidangIcon.style.color = '#D97706';
                    }
                }
            } else {
                bidangElem.innerHTML = `<span class="badge" style="background: #F1F5F9; color: #64748B; font-size: 13px; font-weight: 600; padding: 2px 8px; border-radius: 6px;">-</span>`;
            }
        }

        // Rincian Bidang yang Belum Terpenuhi
        const unfulfilledBox = document.getElementById('adjust_unfulfilled_box');
        const unfulfilledPills = document.getElementById('adjust_unfulfilled_pills');
        const unfulfilledTitle = document.getElementById('adjust_unfulfilled_title');
        if (unfulfilledBox && unfulfilledPills) {
            const isAllMet = currentAdjustStudentData.allFieldsMet;
            const unList = currentAdjustStudentData.unfulfilledList;
            if (!isAllMet && Array.isArray(unList) && unList.length > 0) {
                if (unfulfilledTitle) {
                    unfulfilledTitle.textContent = `${unList.length} dari 7 Bidang Belum Terpenuhi:`;
                }
                let pillsHtml = '';
                unList.forEach(item => {
                    pillsHtml += `<span style="background: #FFFFFF; border: 1px solid #FCD34D; color: #92400E; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="bi bi-x-circle-fill" style="color: #EF4444; font-size: 10px;"></i> ${escapeHtml(item)}
                    </span>`;
                });
                unfulfilledPills.innerHTML = pillsHtml;
                unfulfilledBox.style.display = 'block';
            } else {
                unfulfilledBox.style.display = 'none';
            }
        }

        // Periode badge
        const periodeElem = document.getElementById('adjust_mhs_periode');
        if (periodeElem) {
            periodeElem.textContent = currentAdjustStudentData.periode;
        }

        const select = document.getElementById('adjust_target_beasiswa');
        if (autoSelectedTarget && select.querySelector(`option[value="${autoSelectedTarget}"]`)) {
            select.value = autoSelectedTarget;
        } else if (resolvedRec && select.querySelector(`option[value="${resolvedRec}"]`)) {
            select.value = resolvedRec;
        } else {
            select.value = '75%';
        }

        const reasonBox = document.getElementById('adjust_reason_box');
        if (reasonBox) {
            let ptsNeeded = Math.max(0, currentAdjustStudentData.targetPoints - currentAdjustStudentData.poin);
            let analysisHtml = '';
            if (reason && !isManual) {
                analysisHtml = `<strong><i class="bi bi-info-circle-fill text-primary"></i> Analisis Sistem:</strong> ${reason}`;
            } else if (currentAdjustStudentData.oldBeasiswa !== '0%' && resolvedRec === '0%') {
                analysisHtml = `<strong><i class="bi bi-info-circle-fill text-primary"></i> Analisis Sistem:</strong> Capaian POKEMA saat ini <strong>${currentAdjustStudentData.poin} Poin</strong> dengan IPK <strong>${currentAdjustStudentData.ipk !== null ? currentAdjustStudentData.ipk.toFixed(2) : '-'}</strong>. Berdasarkan perhitungan poin realtime, belum memenuhi target Beasiswa ${currentAdjustStudentData.oldBeasiswa} (Target ${currentAdjustStudentData.targetPoints} Poin, Kurang ${ptsNeeded} Poin) dan belum mencapai batas minimum beasiswa (Min. 120 Poin).`;
            } else if (resolvedRec !== '0%' && resolvedRec === currentAdjustStudentData.oldBeasiswa) {
                analysisHtml = `<strong><i class="bi bi-check-circle-fill text-success"></i> Analisis Sistem:</strong> Target Beasiswa ${resolvedRec} telah terpenuhi penuh secara realtime (POKEMA: ${currentAdjustStudentData.poin} Poin, IPK: ${currentAdjustStudentData.ipk !== null ? currentAdjustStudentData.ipk.toFixed(2) : '-'}).`;
            } else {
                analysisHtml = `<strong><i class="bi bi-sliders text-primary"></i> Mode Manual:</strong> Evaluasi mandiri oleh Admin Kemahasiswaan. Pilihan diselaraskan dengan capaian IPK dan POKEMA mahasiswa saat ini.`;
            }
            reasonBox.innerHTML = analysisHtml;
            reasonBox.style.display = 'block';
        }

        const subtitle = document.getElementById('adjust_modal_subtitle');
        const title = document.getElementById('adjust_modal_title');
        if (isManual) {
            title.innerHTML = '<i class="bi bi-sliders text-primary"></i> Penyesuaian Beasiswa Manual';
            subtitle.textContent = 'Pilih kategori beasiswa secara manual sesuai kebijakan universitas.';
        } else {
            title.innerHTML = '<i class="bi bi-award-fill text-warning"></i> Penyesuaian Beasiswa Mahasiswa';
            subtitle.textContent = 'Verifikasi kelayakan dan perbarui status beasiswa berdasarkan capaian POKEMA & IPK.';
        }

        // Reset pedoman accordion
        const wrap = document.getElementById('pedoman_table_wrap');
        const icon = document.getElementById('pedoman_table_icon');
        if (wrap) wrap.style.display = 'none';
        if (icon) {
            icon.classList.remove('bi-chevron-up');
            icon.classList.add('bi-chevron-down');
        }

        // Evaluate compliance feedback
        onAdjustSelectChange();

        modal.style.display = 'flex';
    }

    function closeAdjustBeasiswaModal() {
        document.getElementById('adjustBeasiswaModal').style.display = 'none';
    }

    function onAdjustSelectChange() {
        const select = document.getElementById('adjust_target_beasiswa');
        if (!select) return;
        const selectedTier = select.value;

        const feedbackContainer = document.getElementById('adjust_compliance_feedback');
        if (!feedbackContainer) return;

        const rule = POKEMA_RULES[selectedTier] || { name: selectedTier, minIpk: 0, level: 0 };
        const oldRule = POKEMA_RULES[currentAdjustStudentData.oldBeasiswa] || { name: currentAdjustStudentData.oldBeasiswa, minIpk: 0, level: 0 };
        const ipk = currentAdjustStudentData.ipk;
        const poin = currentAdjustStudentData.poin;
        const recommended = currentAdjustStudentData.recommendedBeasiswa;
        const oldBeasiswa = currentAdjustStudentData.oldBeasiswa;
        const targetPts = currentAdjustStudentData.targetPoints || 0;

        let feedbackHtml = '';

        if (selectedTier === 'auto') {
            feedbackHtml = `
                <div style="background: #EEF2FF; border: 1px solid #C7D2FE; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #4338CA; display: flex; align-items: flex-start; gap: 8px;">
                    <i class="bi bi-arrow-repeat" style="font-size: 16px; color: #4F46E5; flex-shrink: 0; margin-top: 1px;"></i>
                    <div>
                        <strong style="color: #3730A3;">Reset ke Rekomendasi Otomatis Sistem:</strong><br>
                        Penyesuaian manual akan dihapus. Rekomendasi beasiswa untuk mahasiswa ini akan kembali ditentukan secara dinamis oleh sistem evaluasi POKEMA (${escapeHtml(recommended)}).
                    </div>
                </div>
            `;
            feedbackContainer.innerHTML = feedbackHtml;
            return;
        }

        if (selectedTier === '0%') {
            const isRec0 = (recommended === '0%');
            feedbackHtml = `
                <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #92400E; display: flex; align-items: flex-start; gap: 8px;">
                    <i class="bi bi-shield-exclamation" style="font-size: 16px; color: #D97706; flex-shrink: 0; margin-top: 1px;"></i>
                    <div>
                        <strong style="color: #78350F;">${isRec0 ? 'Sesuai Capaian Realtime: Penyesuaian ke Non-Beasiswa (0%)' : 'Peralihan ke Non-Beasiswa (Reguler)'}:</strong><br>
                        ${isRec0 && oldBeasiswa !== '0%' ? `Capaian POKEMA saat ini (${poin} Poin) belum mencapai ambang batas minimal beasiswa (Min. 120 Poin). ` : ''}
                        Status mahasiswa akan dialihkan ke mahasiswa reguler. Target evaluasi beasiswa per tahun dinonaktifkan dan beralih ke syarat kelulusan SKPI (Minimal 250 Poin).
                    </div>
                </div>
            `;
        } else if (ipk !== null && !isNaN(ipk) && rule.minIpk > 0 && ipk < rule.minIpk) {
            // IPK Warning
            feedbackHtml = `
                <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #991B1B; display: flex; align-items: flex-start; gap: 8px;">
                    <i class="bi bi-exclamation-octagon-fill" style="font-size: 16px; color: #EF4444; flex-shrink: 0; margin-top: 1px;"></i>
                    <div>
                        <strong style="color: #7F1D1D;">Perhatian Validasi IPK:</strong> IPK mahasiswa (<strong>${ipk.toFixed(2)}</strong>) di bawah syarat minimal <strong>${rule.name} (Min. IPK ${rule.minIpk.toFixed(2)})</strong>.<br>
                        <span style="color: #B91C1C; display: inline-block; margin-top: 3px;">
                            Jika tetap dilanjutkan, tindakan ini dicatat sebagai <strong>Penyesuaian Khusus oleh Admin</strong> dalam audit kemahasiswaan.
                        </span>
                    </div>
                </div>
            `;
        } else if (selectedTier === recommended && recommended !== '0%') {
            if (oldRule.level > rule.level) {
                // Downgrade sesuai hasil evaluasi (misal 7 bidang belum lengkap / portal ditutup)
                feedbackHtml = `
                    <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #92400E; display: flex; align-items: flex-start; gap: 8px;">
                        <i class="bi bi-arrow-down-circle-fill" style="font-size: 16px; color: #D97706; flex-shrink: 0; margin-top: 1px;"></i>
                        <div>
                            <strong style="color: #78350F;">Sesuai Hasil Evaluasi Sistem (Penyesuaian ke ${rule.name}):</strong><br>
                            Status beasiswa disesuaikan dari <strong>${oldBeasiswa}</strong> ke <strong>${rule.name}</strong> berdasarkan evaluasi akhir periode (POKEMA: <strong>${poin} Poin</strong>, IPK: <strong>${ipk !== null ? ipk.toFixed(2) : '-'}</strong>). Target POKEMA periode selanjutnya akan disesuaikan dengan standar ${rule.name}.
                        </div>
                    </div>
                `;
            } else if (rule.level > oldRule.level) {
                // Upgrade
                feedbackHtml = `
                    <div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #065F46; display: flex; align-items: flex-start; gap: 8px;">
                        <i class="bi bi-arrow-up-circle-fill" style="font-size: 16px; color: #059669; flex-shrink: 0; margin-top: 1px;"></i>
                        <div>
                            <strong style="color: #064E3B;">Sesuai Hasil Evaluasi (Promosi ke ${rule.name}):</strong><br>
                            Mahasiswa berhasil memenuhi seluruh syarat promosi ke <strong>${rule.name}</strong> (Capaian POKEMA: <strong>${poin} Poin</strong> & IPK: <strong>${ipk !== null ? ipk.toFixed(2) : '-'}</strong>).
                        </div>
                    </div>
                `;
            } else {
                // Maintain
                feedbackHtml = `
                    <div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #065F46; display: flex; align-items: flex-start; gap: 8px;">
                        <i class="bi bi-check-circle-fill" style="font-size: 16px; color: #10B981; flex-shrink: 0; margin-top: 1px;"></i>
                        <div>
                            <strong style="color: #064E3B;">Sesuai Rekomendasi Evaluasi (Pertahankan ${rule.name}):</strong><br>
                            Mahasiswa memenuhi syarat untuk mempertahankan <strong>${rule.name}</strong> (Capaian POKEMA: <strong>${poin} Poin</strong> & IPK: <strong>${ipk !== null ? ipk.toFixed(2) : '-'}</strong>). Memenuhi pedoman SK Rektor No: 2638/SK/01/2026.
                        </div>
                    </div>
                `;
            }
        } else if (selectedTier === oldBeasiswa && oldBeasiswa !== '0%') {
            const ptsRemaining = Math.max(0, targetPts - poin);
            feedbackHtml = `
                <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #1E40AF; display: flex; align-items: flex-start; gap: 8px;">
                    <i class="bi bi-info-circle-fill" style="font-size: 16px; color: #2563EB; flex-shrink: 0; margin-top: 1px;"></i>
                    <div>
                        <strong style="color: #1E3A8A;">Mempertahankan Status Berjalan (${rule.name}):</strong><br>
                        IPK mahasiswa (${ipk !== null ? ipk.toFixed(2) : '-'}) memenuhi syarat minimal (≥ ${rule.minIpk.toFixed(2)}). Capaian POKEMA saat ini <strong>${poin} Poin</strong> dari target <strong>${targetPts} Poin</strong> (Kurang <strong>${ptsRemaining} Poin</strong>).<br>
                        <span style="color: #1D4ED8; display: inline-block; margin-top: 3px;">
                            Mahasiswa masih dalam periode berjalan dan wajib melunasi sisa ${ptsRemaining} Poin di semester aktif untuk mempertahankan status beasiswa ini.
                        </span>
                    </div>
                </div>
            `;
        } else if (rule.level > oldRule.level) {
            feedbackHtml = `
                <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #1E40AF; display: flex; align-items: flex-start; gap: 8px;">
                    <i class="bi bi-arrow-up-circle-fill" style="font-size: 16px; color: #3B82F6; flex-shrink: 0; margin-top: 1px;"></i>
                    <div>
                        <strong style="color: #1E3A8A;">Promosi Kenaikan Beasiswa:</strong><br>
                        Status beasiswa dinaikkan dari <strong>${oldBeasiswa}</strong> ke <strong>${rule.name}</strong>. Target pemenuhan POKEMA akan disesuaikan dengan standar ${rule.name}.
                    </div>
                </div>
            `;
        } else if (rule.level < oldRule.level) {
            feedbackHtml = `
                <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #92400E; display: flex; align-items: flex-start; gap: 8px;">
                    <i class="bi bi-arrow-down-circle-fill" style="font-size: 16px; color: #F59E0B; flex-shrink: 0; margin-top: 1px;"></i>
                    <div>
                        <strong style="color: #78350F;">Penyesuaian Kategori Beasiswa:</strong><br>
                        Status beasiswa disesuaikan dari <strong>${oldBeasiswa}</strong> ke <strong>${rule.name}</strong>. Target POKEMA disesuaikan dengan ambang ${rule.name}.
                    </div>
                </div>
            `;
        } else {
            feedbackHtml = `
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #334155; display: flex; align-items: flex-start; gap: 8px;">
                    <i class="bi bi-shield-check" style="font-size: 16px; color: #64748B; flex-shrink: 0; margin-top: 1px;"></i>
                    <div>
                        <strong style="color: #1E293B;">Kategori Terpilih:</strong> Status beasiswa mahasiswa: <strong>${rule.name}</strong>.
                    </div>
                </div>
            `;
        }

        feedbackContainer.innerHTML = feedbackHtml;
    }

    function showDetailModalFromEncoded(btn) {
        const encodedData = btn.dataset.ach;
        const nama = btn.dataset.nama;
        const data = safeDecodeBase64Json(encodedData, {}) || {};
        data.nama = nama;

        document.getElementById('detail_nama').textContent = data.nama;
        document.getElementById('detail_kategori').textContent = data.kategori;
        document.getElementById('detail_kegiatan').textContent = data.kegiatan;
        if (document.getElementById('detail_event')) document.getElementById('detail_event').textContent = data.event || '-';
        if (document.getElementById('detail_organizer')) document.getElementById('detail_organizer').textContent = data.organizer || '-';
        if (document.getElementById('detail_event_year')) document.getElementById('detail_event_year').textContent = data.event_year || '-';
        if (document.getElementById('detail_semester')) document.getElementById('detail_semester').textContent = data.semester_label || (data.semester ? 'Semester ' + data.semester : '-');
        document.getElementById('detail_tingkat').textContent = data.tingkat;
        document.getElementById('detail_peran').textContent = data.peran;
        document.getElementById('detail_skp').textContent = data.skp;
        document.getElementById('detail_tanggal').textContent = data.tanggal;
        document.getElementById('detail_catatan').textContent = data.catatan;
        document.getElementById('detail_reviewer').textContent = data.reviewer;

        const fileContainer = document.getElementById('detail_file_container');
        if (data.file) {
            fileContainer.innerHTML = `<a href="${data.file}" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; color: #1D4ED8; text-decoration: none; font-weight: 600; padding: 10px 20px; background: white; border-radius: 8px; border: 1px solid #CBD5E1; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"><i class="bi bi-file-earmark-arrow-down" style="font-size: 16px;"></i> Lihat Dokumen / Sertifikat</a>`;
        } else {
            fileContainer.innerHTML = `<div style="color: #64748B; font-size: 13px; display: flex; flex-direction: column; align-items: center; gap: 4px;"><i class="bi bi-file-earmark-x" style="font-size: 24px; opacity: 0.5;"></i><span>Mahasiswa belum mengunggah file pendukung.</span></div>`;
        }

        const actionContainer = document.getElementById('detail_action_container');
        const encodedDataForAction = btoa(unescape(encodeURIComponent(JSON.stringify(data))));

        if (data.status === 'approved') {
            actionContainer.innerHTML = `
            <button type="button" class="btn-edit" data-ach="${encodedDataForAction}" data-nama="${escapeHtml(data.nama)}" onclick="showEditModalFromEncoded(this)" style="flex: 1; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; background-color: #D97706; color: white; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                <i class="bi bi-pencil-square"></i> Sunting Data
            </button>
            <form action="/kemahasiswaan/verifikasi-prestasi/${data.id}/unapprove" method="POST" style="display:inline; flex: 1;" onsubmit="return confirm('Yakin ingin membatalkan approval data ini? Status akan kembali menjadi pending.');">
                @csrf
                <button type="submit" class="btn-reject" style="width: 100%; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; background-color: #64748B; color: white;">
                    <i class="bi bi-arrow-counterclockwise"></i> Batal Approve
                </button>
            </form>
            `;
            actionContainer.style.display = 'flex';
            actionContainer.style.gap = '10px';
        } else {
            actionContainer.innerHTML = `
            <button type="button" class="btn-edit" data-ach="${encodedDataForAction}" data-nama="${escapeHtml(data.nama)}" onclick="showEditModalFromEncoded(this)" style="flex: 1; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; background-color: #D97706; color: white; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                <i class="bi bi-pencil-square"></i> Sunting Data
            </button>
            <button type="button" class="btn-approve" onclick="showApproveModal(${data.id})" style="flex: 1; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer;">
                <i class="bi bi-check-circle"></i> Approve
            </button>
            <button type="button" class="btn-reject" onclick="showRejectModal(${data.id})" style="flex: 1; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer;">
                <i class="bi bi-x-circle"></i> Reject
            </button>
            `;
            actionContainer.style.display = 'flex';
            actionContainer.style.gap = '10px';
        }

        if (activeAchievementsModal === 'regular') {
            document.getElementById('regularStudentAchievementsModal').style.display = 'none';
        } else {
            document.getElementById('studentAchievementsModal').style.display = 'none';
        }
        document.getElementById('detailModal').style.display = 'flex';
    }

    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
        if (activeAchievementsModal === 'regular') {
            document.getElementById('regularStudentAchievementsModal').style.display = 'flex';
        } else if (activeAchievementsModal === 'scholarship') {
            document.getElementById('studentAchievementsModal').style.display = 'flex';
        }
    }

    // ── LOGIK EDIT PRESTASI / SERTIFIKAT ──
    function onEditCategoryChange(selectedType = null) {
        const cat = document.getElementById('edit_category').value;
        const $actType = document.getElementById('edit_activity_type');
        $actType.innerHTML = '';

        if (SKP_DICTIONARY[cat] && SKP_DICTIONARY[cat].types) {
            const types = SKP_DICTIONARY[cat].types;
            Object.entries(types).forEach(([typeKey, typeData]) => {
                const opt = document.createElement('option');
                opt.value = typeKey;
                opt.textContent = typeData.label || typeKey;
                if (selectedType && selectedType === typeKey) {
                    opt.selected = true;
                }
                $actType.appendChild(opt);
            });
        }

        onEditActivityTypeChange();
    }

    function onEditActivityTypeChange(selectedLevel = null, selectedRole = null) {
        const cat = document.getElementById('edit_category').value;
        const actType = document.getElementById('edit_activity_type').value;
        const $level = document.getElementById('edit_level');
        const $role = document.getElementById('edit_participation_role');
        const $customPointContainer = document.getElementById('edit_custom_point_container');
        const $customPointInput = document.getElementById('edit_custom_skp_points');

        $level.innerHTML = '';
        $role.innerHTML = '';

        const isPmb = (actType === 'pmb');
        if ($customPointContainer && $customPointInput) {
            if (isPmb) {
                $customPointContainer.style.display = 'block';
                $customPointInput.disabled = false;
                $customPointInput.required = true;
                if (!$customPointInput.value) {
                    $customPointInput.value = 75;
                }
            } else {
                $customPointContainer.style.display = 'none';
                $customPointInput.disabled = true;
                $customPointInput.required = false;
            }
        }

        if (SKP_DICTIONARY[cat] && SKP_DICTIONARY[cat].types && SKP_DICTIONARY[cat].types[actType]) {
            const typeData = SKP_DICTIONARY[cat].types[actType];

            if (typeData.levels) {
                Object.entries(typeData.levels).forEach(([lvlKey, lvlLabel]) => {
                    const opt = document.createElement('option');
                    opt.value = lvlKey;
                    opt.textContent = lvlLabel;
                    if (selectedLevel && selectedLevel === lvlKey) {
                        opt.selected = true;
                    }
                    $level.appendChild(opt);
                });
            }

            if (typeData.roles) {
                Object.entries(typeData.roles).forEach(([roleKey, roleLabel]) => {
                    const opt = document.createElement('option');
                    opt.value = roleKey;
                    opt.textContent = roleLabel;
                    if (selectedRole && selectedRole === roleKey) {
                        opt.selected = true;
                    }
                    $role.appendChild(opt);
                });
            }
        }

        onEditLevelOrRoleChange();
    }

    function onEditCustomPointChange() {
        const $customPointInput = document.getElementById('edit_custom_skp_points');
        const $badge = document.getElementById('edit_skp_badge');
        if ($customPointInput && $badge) {
            const pts = parseInt($customPointInput.value, 10);
            $badge.textContent = (!isNaN(pts) ? pts : 0) + ' SKP';
        }
    }

    function onEditLevelOrRoleChange() {
        const cat = document.getElementById('edit_category').value;
        const actType = document.getElementById('edit_activity_type').value;
        const level = document.getElementById('edit_level').value;
        const role = document.getElementById('edit_participation_role').value;
        const $badge = document.getElementById('edit_skp_badge');
        const $customPointInput = document.getElementById('edit_custom_skp_points');

        if (actType === 'pmb') {
            let pts = $customPointInput ? parseInt($customPointInput.value, 10) : 75;
            if (isNaN(pts)) pts = 75;
            $badge.textContent = pts + ' SKP';
            return;
        }

        let points = 0;
        try {
            if (SKP_DICTIONARY[cat] && SKP_DICTIONARY[cat].types[actType] && SKP_DICTIONARY[cat].types[actType].points) {
                const pTable = SKP_DICTIONARY[cat].types[actType].points;
                if (pTable[level] && pTable[level][role] !== undefined) {
                    points = pTable[level][role];
                }
            }
        } catch (e) {
            points = 0;
        }

        $badge.textContent = points + ' SKP';
    }

    function showEditModalFromEncoded(btn) {
        const encodedData = btn.dataset.ach;
        const nama = btn.dataset.nama;
        const data = safeDecodeBase64Json(encodedData, {}) || {};
        data.nama = nama;
        showEditModalFromData(data);
    }

    function showEditModalFromData(data) {
        document.getElementById('editAchievementForm').action = `/kemahasiswaan/verifikasi-prestasi/${data.id}/update`;
        document.getElementById('edit_modal_subtitle').textContent = `Mahasiswa: ${data.nama || ''}`;

        const $cat = document.getElementById('edit_category');
        if (data.category_raw) {
            $cat.value = data.category_raw;
        }

        onEditCategoryChange(data.activity_type_raw);
        onEditActivityTypeChange(data.tingkat, data.peran);

        const $customPointInput = document.getElementById('edit_custom_skp_points');
        if ($customPointInput && data.activity_type_raw === 'pmb') {
            $customPointInput.value = data.skp || 75;
            document.getElementById('edit_skp_badge').textContent = (data.skp || 75) + ' SKP';
        }

        document.getElementById('edit_event').value = (data.event && data.event !== '-') ? data.event : '';
        document.getElementById('edit_organizer').value = (data.organizer && data.organizer !== '-') ? data.organizer : '';
        document.getElementById('edit_event_year').value = (data.event_year && data.event_year !== '-') ? data.event_year : '';
        if (document.getElementById('edit_semester')) document.getElementById('edit_semester').value = data.semester || '';
        document.getElementById('edit_status').value = data.status || 'approved';
        document.getElementById('edit_approval_notes').value = (data.catatan && data.catatan !== 'Belum ada catatan review.') ? data.catatan : '';

        const previewDiv = document.getElementById('edit_current_file_preview');
        if (data.file) {
            previewDiv.innerHTML = `<a href="${data.file}" target="_blank" style="color: #2563EB; font-weight: 600; text-decoration: none;"><i class="bi bi-file-earmark-check"></i> File saat ini (Klik untuk lihat)</a>`;
        } else {
            previewDiv.innerHTML = `<span style="color: #94A3B8;">Belum ada file diunggah.</span>`;
        }

        if (activeAchievementsModal === 'regular') {
            document.getElementById('regularStudentAchievementsModal').style.display = 'none';
        } else {
            document.getElementById('studentAchievementsModal').style.display = 'none';
        }
        document.getElementById('detailModal').style.display = 'none';
        document.getElementById('editAchievementModal').style.display = 'flex';
    }

    function closeEditAchievementModal() {
        document.getElementById('editAchievementModal').style.display = 'none';
        if (activeAchievementsModal === 'regular') {
            document.getElementById('regularStudentAchievementsModal').style.display = 'flex';
        } else if (activeAchievementsModal === 'scholarship') {
            document.getElementById('studentAchievementsModal').style.display = 'flex';
        }
    }

    // ── TOGGLE PORTAL UPLOAD AMBASSADOR & BEASISWA ──
    function togglePortalUpload(isOpen) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
            || '{{ csrf_token() }}';
        const badge = document.getElementById('portalStatusBadge');
        const desc = document.getElementById('portalLockDesc');
        const checkbox = document.getElementById('portalUploadSwitch');
        const inlineWrap = document.getElementById('portalLockInline');

        // Optimistic UI update
        if (badge) {
            badge.className = 'portal-status-badge ' + (isOpen ? 'badge-open' : 'badge-closed');
            badge.textContent = isOpen ? 'Dibuka (Aktif)' : 'Ditutup (Review)';
        }
        if (desc) {
            desc.textContent = isOpen 
                ? 'Status: DIBUKA. Mahasiswa dapat mengunggah dan memperbarui prestasi secara normal.'
                : 'Status: DITUTUP. Akses upload mahasiswa dikunci sementara untuk proses evaluasi kelayakan beasiswa semester.';
        }
        if (inlineWrap) {
            inlineWrap.title = isOpen
                ? 'Status: DIBUKA. Mahasiswa dapat mengunggah dan memperbarui prestasi secara normal.'
                : 'Status: DITUTUP. Akses upload mahasiswa dikunci sementara untuk proses evaluasi kelayakan beasiswa semester.';
        }

        fetch('{{ route("kemahasiswaan.verifikasi-prestasi.toggle-upload-portal") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_open: isOpen })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: isOpen ? 'success' : 'info',
                        title: isOpen ? 'Portal Dibuka' : 'Portal Ditutup Sementara',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end',
                        customClass: {
                            container: 'swal2-toast-navbar-offset'
                        }
                    });
                }
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error(data.message || 'Gagal mengubah status portal');
            }
        })
        .catch(err => {
            console.error('Error toggling portal:', err);
            // Revert state on error
            if (checkbox) checkbox.checked = !isOpen;
            if (badge) {
                badge.className = 'portal-status-badge ' + (!isOpen ? 'badge-open' : 'badge-closed');
                badge.textContent = !isOpen ? 'Dibuka (Aktif)' : 'Ditutup (Review)';
            }
            if (desc) {
                desc.textContent = !isOpen 
                    ? 'Status: DIBUKA. Mahasiswa dapat mengunggah dan memperbarui prestasi secara normal.'
                    : 'Status: DITUTUP. Akses upload mahasiswa dikunci sementara untuk proses evaluasi kelayakan beasiswa semester.';
            }
            if (inlineWrap) {
                inlineWrap.title = !isOpen
                    ? 'Status: DIBUKA. Mahasiswa dapat mengunggah dan memperbarui prestasi secara normal.'
                    : 'Status: DITUTUP. Akses upload mahasiswa dikunci sementara untuk proses evaluasi kelayakan beasiswa semester.';
            }
            alert('Gagal mengubah status portal upload: ' + err.message);
        });
    }

    function handleSendAllSiakadVerifikasi() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Kirim Rekomendasi ke SIAKAD?',
                html: `
                    <div style="text-align: left; font-size: 13.5px; color: #475569; line-height: 1.55;">
                        <p>Seluruh hasil rekomendasi beasiswa POKEMA untuk mahasiswa <strong>angkatan 2026 ke atas</strong> akan dikirimkan langsung ke kolom <strong>Rekomendasi</strong> pada menu <em>Rekomendasi Beasiswa Termin</em> di SIAKAD.</p>
                        <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 10px 14px; margin-top: 10px; color: #166534; font-size: 12.5px;">
                            <i class="bi bi-info-circle-fill"></i> Data yang dikirim berstatus <strong>Draft</strong> sehingga aman dan dapat ditinjau terlebih dahulu oleh Bagian Akademik & Keuangan di Siakad sebelum diterbitkan.
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10B981',
                cancelButtonColor: '#6B7280',
                confirmButtonText: '<i class="bi bi-send-check"></i> Ya, Kirim Sekarang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengirim Rekomendasi...',
                        text: 'Sedang memproses dan menyimpan data rekomendasi ke SIAKAD...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    document.getElementById('form-send-all-siakad').submit();
                }
            });
        } else {
            if (confirm('Kirim seluruh rekomendasi hasil evaluasi beasiswa ke SIAKAD?')) {
                document.getElementById('form-send-all-siakad').submit();
            }
        }
    }

    function handleApplyAllRecommendations() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Update Data Beasiswa?',
                html: `
                    <div style="text-align: left; font-size: 13.5px; color: #475569; line-height: 1.55;">
                        <p>Sistem akan menarik hasil penetapan termin resmi dari <strong>Keuangan di SIAKAD</strong> dan memperbarui kolom <strong>Beasiswa (%)</strong> mahasiswa di SuperApps.</p>
                        <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 10px 14px; margin-top: 10px; color: #166534; font-size: 12.5px;">
                            <i class="bi bi-shield-check"></i> Status beasiswa yang telah resmi di-ACC Keuangan (termasuk penyesuaian manual Anda) akan dikunci di SuperApps dan dijadikan dasar acuan evaluasi periode berikutnya.
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6B7280',
                confirmButtonText: '<i class="bi bi-cloud-arrow-down-fill"></i> Ya, Update Data Beasiswa',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memperbarui Data Beasiswa...',
                        text: 'Sedang menyinkronkan beasiswa resmi dari Keuangan SIAKAD...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    document.getElementById('form-apply-all-recommendations').submit();
                }
            });
        } else {
            if (confirm('Perbarui data beasiswa mahasiswa di SuperApps berdasarkan hasil penetapan termin resmi dari Keuangan di SIAKAD?')) {
                document.getElementById('form-apply-all-recommendations').submit();
            }
        }
    }
</script>
