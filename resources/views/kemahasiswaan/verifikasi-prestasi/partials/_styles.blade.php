<link rel="stylesheet" href="{{ asset('admin/css/skpi-verifikasi-data.css') }}">
<style>
    /* Styling khusus kolom baru Kemahasiswaan */
    .filter-form {
        display: grid;
        grid-template-columns: 2fr 1fr 1.1fr 1.1fr 1.2fr 1fr 1.2fr auto;
        gap: 12px;
        align-items: flex-end;
    }

    @media (max-width: 1400px) {
        .filter-form {
            grid-template-columns: 2fr 1fr 1fr 1fr;
        }
    }

    @media (max-width: 1200px) {
        .filter-form {
            grid-template-columns: 1fr 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .filter-form {
            grid-template-columns: 1fr;
        }
    }

    /* Button Export Laporan Beasiswa */
    .btn-export-beasiswa {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #107C41, #0E5E31);
        color: #FFFFFF !important;
        border: none;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(16, 124, 65, 0.25);
    }
    .btn-export-beasiswa:hover {
        background: linear-gradient(135deg, #0E5E31, #0A4323);
        color: #FFFFFF !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 124, 65, 0.35);
    }
    .btn-export-beasiswa:active {
        transform: translateY(0);
    }
    .dropdown-menu .dropdown-item {
        font-size: 13px;
        padding: 8px 16px;
        font-weight: 500;
        display: flex;
        align-items: center;
        transition: background-color 0.15s ease;
    }
    .dropdown-menu .dropdown-item:hover {
        background-color: #F1F5F9;
    }

    /* Badge IPK */
    .badge-ipk {
        display: inline-block;
        font-weight: 700;
        font-size: 13px;
        padding: 3px 8px;
        border-radius: 6px;
        background: #F1F5F9;
        color: #334155;
        border: 1px solid #E2E8F0;
    }
    .badge-ipk.ipk-good {
        background: #EFF6FF;
        color: #1D4ED8;
        border-color: #BFDBFE;
    }
    .badge-ipk.ipk-cumlaude {
        background: #ECFDF5;
        color: #047857;
        border-color: #A7F3D0;
    }

    /* Badge Point SKP */
    .badge-point {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-weight: 700;
        font-size: 12px;
        padding: 3px 10px;
        border-radius: 999px;
        background: #F8FAFC;
        color: #64748B;
        border: 1px solid #E2E8F0;
    }
    .badge-point.point-active {
        background: #FEF3C7;
        color: #B45309;
        border-color: #FDE68A;
    }

    /* Badge Status Mahasiswa */
    .badge-status-mhs {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        white-space: nowrap;
    }
    .badge-ambassador {
        background: #F3E8FF;
        color: #7C3AED;
        border: 1px solid #DDD6FE;
    }
    .badge-reguler {
        background: #F1F5F9;
        color: #475569;
        border: 1px solid #E2E8F0;
    }

    /* Badge Beasiswa */
    .badge-beasiswa {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        background: #ECFDF5;
        color: #059669;
        border: 1px solid #A7F3D0;
        max-width: 170px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Badge Rekomendasi di Tabel */
    .badge-rec {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .badge-rec-upgrade {
        background: #D1FAE5;
        color: #065F46;
        border: 1px solid #A7F3D0;
    }
    .badge-rec-on_track {
        background: #E0F2FE;
        color: #0369A1;
        border: 1px solid #BAE6FD;
    }
    .badge-rec-maintain {
        background: #DBEAFE;
        color: #1E40AF;
        border: 1px solid #BFDBFE;
    }
    .badge-rec-warning {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
    }
    .badge-rec-downgrade {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
    }
    .badge-rec-revoke {
        background: #FEE2E2;
        color: #991B1B;
        border: 1px solid #FECACA;
    }
    /* Kolom Tindakan / Rekomendasi di Tabel */
    .col-recommendation-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
    }
    .action-needed-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 700;
        padding: 1px 7px;
        border-radius: 4px;
        background: #FFF1F2;
        color: #BE123C;
        border: 1px solid #FECDD3;
        letter-spacing: 0.2px;
        white-space: nowrap;
    }
    .action-ongoing-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 600;
        padding: 1px 7px;
        border-radius: 4px;
        background: #F0FDF4;
        color: #15803D;
        border: 1px solid #BBF7D0;
        white-space: nowrap;
    }
    .action-ontrack-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 600;
        padding: 1px 7px;
        border-radius: 4px;
        background: #F0F9FF;
        color: #0369A1;
        border: 1px solid #BAE6FD;
        white-space: nowrap;
    }
    .action-warning-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 600;
        padding: 1px 7px;
        border-radius: 4px;
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
        white-space: nowrap;
    }
    .badge-pending-count {
        display: inline-block;
        padding: 3px 9px;
        font-size: 11px;
        font-weight: 700;
        border-radius: 6px;
        background: #FFF7ED;
        color: #C2410C;
        border: 1px solid #FFEDD5;
        white-space: nowrap;
    }
    .badge-pending-zero {
        display: inline-block;
        padding: 3px 9px;
        font-size: 11px;
        font-weight: 500;
        color: #94A3B8;
        white-space: nowrap;
    }
    /* Toolbar Row: Tab Navigasi & Toggle Portal Sejajar */
    .category-toolbar-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }
    .toolbar-controls-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-apply-all-recommendations-toolbar {
        background: #2563EB;
        color: #FFFFFF;
        border: none;
        border-radius: 12px;
        padding: 0 16px;
        height: 48px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-apply-all-recommendations-toolbar:hover {
        background: #1D4ED8;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.35);
        transform: translateY(-1px);
    }
    .btn-apply-all-recommendations-toolbar:active {
        transform: translateY(0);
    }
    .btn-send-all-siakad-toolbar {
        background: #10B981;
        color: #FFFFFF;
        border: none;
        border-radius: 12px;
        padding: 0 16px;
        height: 48px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(16, 185, 129, 0.25);
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-send-all-siakad-toolbar:hover {
        background: #059669;
        box-shadow: 0 3px 8px rgba(16, 185, 129, 0.35);
        transform: translateY(-1px);
    }
    .btn-send-all-siakad-toolbar:active {
        transform: translateY(0);
    }
    .portal-lock-inline {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 5px 12px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        height: 48px;
        box-sizing: border-box;
    }
    .portal-inline-label {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        white-space: nowrap;
    }
    .switch-toggle {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        margin: 0;
        cursor: pointer;
    }
    .switch-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .switch-slider {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #CBD5E1;
        transition: .3s;
        border-radius: 24px;
    }
    .switch-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
    }
    .switch-toggle input:checked + .switch-slider {
        background-color: #10B981;
    }
    .switch-toggle input:checked + .switch-slider:before {
        transform: translateX(20px);
    }
    .portal-status-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 9px;
        border-radius: 6px;
        white-space: nowrap;
        transition: all 0.2s;
    }
    .portal-status-badge.badge-open {
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
    }
    .portal-status-badge.badge-closed {
        background: #FFF1F2;
        color: #BE123C;
        border: 1px solid #FECDD3;
    }
    .btn-quick-upgrade {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 11px;
        border-radius: 7px;
        background: #059669;
        color: #FFFFFF;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 1px 3px rgba(5, 150, 105, 0.3);
        white-space: nowrap;
    }
    .btn-quick-upgrade:hover {
        background: #047857;
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(5, 150, 105, 0.4);
    }
    .btn-quick-downgrade {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 11px;
        border-radius: 7px;
        background: #D97706;
        color: #FFFFFF;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 1px 3px rgba(217, 119, 6, 0.3);
        white-space: nowrap;
    }
    .btn-quick-downgrade:hover {
        background: #B45309;
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(217, 119, 6, 0.4);
    }

    /* Modal Tabs Navigation */
    .modal-tabs-nav {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid #E2E8F0;
        padding: 0 24px;
        background: #F8FAFC;
    }
    .modal-tab-btn {
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
        gap: 6px;
    }
    .modal-tab-btn:hover {
        color: #1E293B;
    }
    .modal-tab-btn.active {
        color: #2563EB;
        border-bottom-color: #2563EB;
        background: #FFFFFF;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    /* Smart Recommendation Card */
    .rec-card {
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        transition: all 0.25s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .rec-card.rec-on_track {
        background: linear-gradient(135deg, #F0F9FF 0%, #E0F2FE 100%);
        border: 1.5px solid #7DD3FC;
    }
    .rec-card.rec-upgrade {
        background: linear-gradient(135deg, #ECFDF5 0%, #F0FDF4 100%);
        border: 1.5px solid #6EE7B7;
    }
    .rec-card.rec-maintain {
        background: linear-gradient(135deg, #EFF6FF 0%, #F0F9FF 100%);
        border: 1.5px solid #93C5FD;
    }
    .rec-card.rec-warning {
        background: linear-gradient(135deg, #FFFBEB 0%, #FEFCE8 100%);
        border: 1.5px solid #FCD34D;
    }
    .rec-card.rec-downgrade {
        background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
        border: 1.5px solid #FBBF24;
    }
    .rec-card.rec-revoke {
        background: linear-gradient(135deg, #FEF2F2 0%, #FFF1F2 100%);
        border: 1.5px solid #FECACA;
    }
    .rec-card.rec-reguler {
        background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
        border: 1.5px solid #CBD5E1;
    }
    .rec-card.rec-future {
        background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
        border: 1.5px solid #CBD5E1;
    }
    .rec-main-content {
        flex: 1 1 540px;
        min-width: 280px;
    }
    .rec-top-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .rec-status-pill {
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
    .pill-on_track {
        background: #0284C7;
        color: #FFFFFF;
    }
    .pill-upgrade {
        background: #059669;
        color: #FFFFFF;
    }
    .pill-maintain {
        background: #2563EB;
        color: #FFFFFF;
    }
    .pill-warning {
        background: #D97706;
        color: #FFFFFF;
    }
    .pill-downgrade {
        background: #D97706;
        color: #FFFFFF;
    }
    .pill-revoke {
        background: #DC2626;
        color: #FFFFFF;
    }
    .pill-reguler {
        background: #64748B;
        color: #FFFFFF;
    }
    .pill-future {
        background: #475569;
        color: #FFFFFF;
    }
    .rec-period-badge {
        font-size: 12px;
        color: #64748B;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .rec-title {
        font-size: 16px;
        font-weight: 800;
        color: #0F172A;
        margin: 0 0 6px 0;
        line-height: 1.35;
    }
    .rec-reason {
        font-size: 13px;
        line-height: 1.5;
        color: #334155;
        margin: 0 0 10px 0;
    }
    .rec-progress-box {
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 8px;
        padding: 9px 12px;
        margin-top: 8px;
    }
    .rec-progress-info {
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
    .rec-remaining-tag {
        font-size: 11px;
        color: #0369A1;
        background: #E0F2FE;
        padding: 2px 7px;
        border-radius: 6px;
    }
    .rec-completed-tag {
        font-size: 11px;
        color: #047857;
        background: #D1FAE5;
        padding: 2px 7px;
        border-radius: 6px;
    }
    .rec-progress-track {
        height: 7px;
        background: #E2E8F0;
        border-radius: 999px;
        overflow: hidden;
    }
    .rec-progress-bar {
        height: 100%;
        border-radius: 999px;
        transition: width 0.4s ease;
    }
    .rec-progress-bar.bar-on_track {
        background: linear-gradient(90deg, #0284C7, #38BDF8);
    }
    .rec-progress-bar.bar-upgrade {
        background: linear-gradient(90deg, #059669, #34D399);
    }
    .rec-progress-bar.bar-maintain {
        background: linear-gradient(90deg, #2563EB, #60A5FA);
    }
    .rec-progress-bar.bar-warning {
        background: linear-gradient(90deg, #D97706, #FBBF24);
    }
    .rec-progress-bar.bar-downgrade {
        background: linear-gradient(90deg, #D97706, #FBBF24);
    }
    .rec-progress-bar.bar-revoke {
        background: linear-gradient(90deg, #DC2626, #F87171);
    }
    .rec-progress-bar.bar-future {
        background: linear-gradient(90deg, #64748B, #94A3B8);
    }
    .rec-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    /* Smart Action Buttons */
    .btn-smart-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    }
    .btn-smart-action.btn-on_track {
        background: #0284C7;
        color: #FFFFFF;
    }
    .btn-smart-action.btn-on_track:hover {
        background: #0369A1;
    }
    .btn-smart-action.btn-future {
        background: #475569;
        color: #FFFFFF;
    }
    .btn-smart-action.btn-future:hover {
        background: #334155;
    }
    .btn-smart-action.btn-upgrade {
        background: #059669;
        color: #FFFFFF;
    }
    .btn-smart-action.btn-upgrade:hover {
        background: #047857;
    }
    .btn-smart-action.btn-maintain {
        background: #2563EB;
        color: #FFFFFF;
    }
    .btn-smart-action.btn-maintain:hover {
        background: #1D4ED8;
    }
    .btn-smart-action.btn-warning {
        background: #D97706;
        color: #FFFFFF;
    }
    .btn-smart-action.btn-warning:hover {
        background: #B45309;
    }
    .btn-smart-action.btn-downgrade {
        background: #D97706;
        color: #FFFFFF;
    }
    .btn-smart-action.btn-downgrade:hover {
        background: #B45309;
    }
    .btn-smart-action.btn-revoke {
        background: #DC2626;
        color: #FFFFFF;
    }
    .btn-smart-action.btn-revoke:hover {
        background: #B91C1C;
    }
    .btn-smart-action.btn-manual {
        background: #FFFFFF;
        color: #475569;
        border: 1.5px solid #CBD5E1;
    }
    .btn-smart-action.btn-manual:hover {
        background: #F1F5F9;
        color: #1E293B;
    }

    /* Semester Breakdown Styles */
    .pokema-semester-section {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .semester-bars-grid {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .sem-card {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 14px 16px;
        transition: all 0.2s ease;
    }
    .sem-card:hover {
        border-color: #CBD5E1;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }
    .sem-card-active {
        background: #F0F9FF;
        border-color: #BAE6FD;
        box-shadow: 0 0 0 1px #0284C7;
    }
    .sem-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        flex-wrap: wrap;
        gap: 8px;
    }
    .sem-title {
        font-size: 14px;
        font-weight: 700;
        color: #0F172A;
    }
    .sem-year-badge {
        font-size: 11px;
        font-weight: 600;
        background: #E2E8F0;
        color: #475569;
        padding: 2px 8px;
        border-radius: 999px;
    }
    .badge-current-sem {
        font-size: 10px;
        font-weight: 700;
        background: #0284C7;
        color: #FFFFFF;
        padding: 2px 8px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .sem-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 6px;
    }
    .pill-ipk {
        background: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
    }
    .pill-ip {
        background: #F1F5F9;
        color: #334155;
        border: 1px solid #E2E8F0;
    }
    .pill-empty {
        background: #F8FAFC;
        color: #94A3B8;
        border: 1px dashed #CBD5E1;
        font-size: 11px;
    }
    .sem-stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 13px;
    }
    .sem-milestone {
        margin-top: 8px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .milestone-success {
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
    }
    .milestone-warning {
        background: #FFFBEB;
        color: #92400E;
        border: 1px solid #FDE68A;
    }

    /* 7 POKEMA Bars Section */
    .pokema-fields-container {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 18px 20px;
    }
    .pokema-fields-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .year-toggle-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-group-year {
        display: inline-flex;
        background: #F1F5F9;
        padding: 3px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
    }
    .btn-year {
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
    .btn-year.active {
        background: #FFFFFF;
        color: #2563EB;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .pokema-bars-grid {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .pokema-field-row {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 12px 16px;
        transition: all 0.2s;
    }
    .pokema-field-row:hover {
        border-color: #CBD5E1;
        background: #F1F5F9;
    }
    .field-title-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }
    .field-name {
        font-size: 13px;
        font-weight: 700;
        color: #1E293B;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .field-score {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }
    .progress-track {
        height: 9px;
        background: #E2E8F0;
        border-radius: 999px;
        overflow: hidden;
        position: relative;
    }
    .progress-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.4s ease;
    }
    .progress-fill.fill-green {
        background: linear-gradient(90deg, #10B981, #059669);
    }
    .progress-fill.fill-blue {
        background: linear-gradient(90deg, #3B82F6, #2563EB);
    }
    .badge-field-status {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-field-status.status-ok {
        background: #D1FAE5;
        color: #065F46;
    }
    .badge-field-status.status-kurang {
        background: #FEF3C7;
        color: #92400E;
    }

    /* Segmented Toggle Bar Mahasiswa */
    .student-category-toggle-wrap {
        display: flex;
        gap: 8px;
        background: #F1F5F9;
        padding: 6px;
        border-radius: 12px;
        margin-bottom: 0;
        border: 1px solid #E2E8F0;
        width: fit-content;
        max-width: 100%;
        overflow-x: auto;
    }
    .category-toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        color: #64748B;
        text-decoration: none;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .category-toggle-btn:hover {
        color: #1E293B;
        background: rgba(255, 255, 255, 0.7);
    }
    .category-toggle-btn.active {
        background: #FFFFFF;
        color: #0F172A;
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }
    .category-toggle-btn.btn-beasiswa-tab.active {
        color: #047857;
    }
    .category-toggle-btn.btn-reguler-tab.active {
        color: #0369A1;
    }
    .toggle-count-badge {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #E2E8F0;
        color: #475569;
        line-height: 1.2;
    }
    .category-toggle-btn.active .toggle-count-badge {
        background: #0F172A;
        color: #FFFFFF;
    }
    .category-toggle-btn.btn-beasiswa-tab.active .toggle-count-badge {
        background: #047857;
        color: #FFFFFF;
    }
    .category-toggle-btn.btn-reguler-tab.active .toggle-count-badge {
        background: #0284C7;
        color: #FFFFFF;
    }

    /* Pending Badge Pill di Toolbar */
    .toggle-pending-badge {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #FEF3C7;
        color: #B45309;
        border: 1px solid #FDE68A;
        line-height: 1.2;
    }
    .toggle-pending-badge.badge-pending-gold {
        background: #FEF3C7;
        color: #B45309;
        border-color: #FDE68A;
    }
    .toggle-pending-badge.badge-pending-blue {
        background: #E0F2FE;
        color: #0369A1;
        border-color: #BAE6FD;
    }
    .category-toggle-btn.active .toggle-pending-badge {
        background: #FEF3C7;
        color: #B45309;
    }
    .category-toggle-btn.btn-beasiswa-tab.active .toggle-pending-badge {
        background: #D1FAE5;
        color: #065F46;
        border-color: #A7F3D0;
    }
    .category-toggle-btn.btn-reguler-tab.active .toggle-pending-badge {
        background: #E0F2FE;
        color: #0369A1;
        border-color: #BAE6FD;
    }

    /* Button Approve Semua Massal Khusus Kategori */
    .btn-approve-beasiswa-all {
        background: #059669 !important;
        border-color: #059669 !important;
        color: #FFFFFF !important;
        box-shadow: 0 1px 3px rgba(5, 150, 105, 0.25);
    }
    .btn-approve-beasiswa-all:hover {
        background: #047857 !important;
        box-shadow: 0 3px 6px rgba(5, 150, 105, 0.35);
        transform: translateY(-1px);
    }
    .btn-approve-reguler-all {
        background: #0284C7 !important;
        border-color: #0284C7 !important;
        color: #FFFFFF !important;
        box-shadow: 0 1px 3px rgba(2, 132, 199, 0.25);
    }
    .btn-approve-reguler-all:hover {
        background: #0369A1 !important;
        box-shadow: 0 3px 6px rgba(2, 132, 199, 0.35);
        transform: translateY(-1px);
    }

    /* Button Verifikasi Prestasi Reguler */
    .btn-view-reguler {
        background: #0284C7 !important;
        border-color: #0284C7 !important;
        color: #FFFFFF !important;
    }
    .btn-view-reguler:hover {
        background: #0369A1 !important;
        border-color: #0369A1 !important;
        color: #FFFFFF !important;
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(2, 132, 199, 0.3);
    }

    /* ========================================================
       MODAL POSITIONING & OVERFLOW FIX (CLEAN OFFSET BELOW NAVBAR)
       ======================================================== */
    .modal {
        position: fixed !important;
        top: 80px !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        inset: 80px 0 0 0 !important;
        background: rgba(15, 23, 42, 0.6) !important;
        backdrop-filter: blur(4px) !important;
        -webkit-backdrop-filter: blur(4px) !important;
        overflow-y: auto !important;
        padding: 24px 16px 50px !important;
        display: flex;
        justify-content: center !important;
        align-items: flex-start !important;
    }

    #studentAchievementsModal,
    #regularStudentAchievementsModal {
        z-index: 10001 !important;
    }

    #detailModal {
        z-index: 20010 !important;
    }

    #editAchievementModal {
        z-index: 20020 !important;
    }

    #adjustBeasiswaModal {
        z-index: 20030 !important;
        padding-top: 24px !important;
        padding-bottom: 50px !important;
    }

    #approveModal,
    #rejectModal,
    #approveAllModal {
        z-index: 20050 !important;
        align-items: center !important;
    }

    #approveModal .modal-content,
    #rejectModal .modal-content,
    #approveAllModal .modal-content {
        max-width: 500px !important;
        width: 100% !important;
        margin: auto !important;
        border-radius: 16px !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45) !important;
        background: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
        padding: 24px !important;
    }

    #adjustBeasiswaModal .modal-content {
        max-width: 580px !important;
        width: 100% !important;
        margin: 0 auto !important;
        border-radius: 16px !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35) !important;
        max-height: calc(100vh - 140px) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        background: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
    }

    /* SweetAlert2 Toast Positioning & Stacking */
    .swal2-container {
        z-index: 99999 !important;
    }
    .swal2-container.swal2-top-end,
    .swal2-container.swal2-top-right,
    .swal2-toast-navbar-offset {
        padding-top: 92px !important;
        padding-right: 24px !important;
    }
</style>
