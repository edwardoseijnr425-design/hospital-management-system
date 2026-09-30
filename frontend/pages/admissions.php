<style>
    #adm-patient-results{position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #C9D4E0;border-top:none;border-radius:0 0 6px 6px;box-shadow:0 6px 14px rgba(15,45,89,.12);max-height:220px;overflow-y:auto;z-index:50}
    #adm-patient-results div{padding:9px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #EEF2F7;color:#1E293B}
    #adm-patient-results div:hover,#adm-patient-results div.sel{background:#E7F3FC;color:#0F2D59}
    #adm-patient-results .hpno{color:#0072BC;font-weight:700}
    #adm-patient-results .empty{padding:10px 12px;color:#64748B;cursor:default}
    #adm-patient-results .empty:hover{background:#fff;color:#64748B}

    /* ---- Discharge screen: billing summary + clinical summary ---- */
    #discharge-modal .modal-content{max-width:880px !important}
    #adm-bill-card{border:1px solid #DCE4EC;border-radius:6px;overflow:hidden;margin-bottom:16px}
    #adm-bill-head{background:#0b5fa5;color:#fff;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.4px;padding:8px 12px;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
    #adm-bill-head button{background:#fff;color:#0b5fa5;border:none;border-radius:3px;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.3px;padding:5px 10px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:5px}
    #adm-bill-head button:hover{background:#E7F3FC}
    #adm-bill-body{background:#fff}
    #adm-bill-scroll{max-height:190px;overflow-y:auto}
    #adm-bill-table{width:100%;border-collapse:collapse;font-size:11px}
    #adm-bill-table th{background:#E6EEF5;color:#222;font-size:10px;text-transform:uppercase;letter-spacing:.3px;text-align:left;padding:6px 8px;border-bottom:2px solid #b2c8de;position:sticky;top:0}
    #adm-bill-table td{padding:6px 8px;border-bottom:1px solid #E1E8F0}
    #adm-bill-table .num{text-align:right;white-space:nowrap}
    #adm-bill-table .ctr{text-align:center}
    #adm-bill-table .empty{text-align:center;padding:14px;color:#8A94A6}
/* Item category leads the line-item row; the invoice it belongs to is shown
   underneath the description, since one invoice spans several categories. */
#adm-bill-table .cat{font-weight:700;font-size:9.5px;text-transform:uppercase;letter-spacing:.3px;color:#0F2D59;white-space:nowrap}
.adm-bill-sec-label{background:#F1F5F9;color:#475569;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;padding:5px 8px;border-bottom:1px solid #E1E8F0}
/* ---- Invoice table above the line items ---- */
#adm-bill-inv-scroll{max-height:170px;overflow-y:auto;border-bottom:1px solid #E1E8F0}
#adm-bill-inv-table{width:100%;border-collapse:collapse;font-size:11px}
#adm-bill-inv-table th{background:#E6EEF5;color:#222;font-size:10px;text-transform:uppercase;letter-spacing:.3px;text-align:left;padding:6px 8px;border-bottom:2px solid #b2c8de;position:sticky;top:0}
#adm-bill-inv-table td{padding:6px 8px;border-bottom:1px solid #E1E8F0}
#adm-bill-inv-table .num{text-align:right;white-space:nowrap}
#adm-bill-inv-table .empty{text-align:center;padding:14px;color:#8A94A6}
.inv-no{font-family:Consolas,'Courier New',monospace;font-weight:700;color:#0F2D59;font-size:10.5px}
#adm-bill-sublabel{display:block;color:#94A3B8;font-family:Consolas,'Courier New',monospace;font-size:9.5px;margin-top:2px}
.inv-sub{display:block;color:#94A3B8;font-family:Consolas,'Courier New',monospace;font-size:9.5px;font-weight:400;margin-top:2px}
.stat{display:inline-block;padding:2px 8px;border-radius:10px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
.stat-paid{background:#DCFCE7;color:#166534}
.stat-pending{background:#FEF3C7;color:#92400E}
.stat-partial{background:#FEF3C7;color:#92400E}
.stat-draft{background:#E2E8F0;color:#475569}
.stat-cancelled{background:#FEE2E2;color:#991B1B}
    #adm-bill-foot{background:#F8FAFC;border-top:1px solid #E1E8F0;padding:9px 12px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;font-size:11px;color:#64748B}
    #adm-bill-foot .due{color:#C0392B;font-weight:800;font-size:12.5px}
    #adm-bill-foot .clear{color:#1E7A34;font-weight:800}
    #adm-outcome-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    #adm-outcome-grid .full{grid-column:span 2}
    #adm-outcome-grid label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#64748B;margin-bottom:5px}
    #adm-outcome-grid select,#adm-outcome-grid input,#adm-outcome-grid textarea{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;font-family:inherit;background:#fff;color:#222;box-sizing:border-box}
    #adm-outcome-grid textarea{resize:vertical}
    #adm-outcome-grid select:focus,#adm-outcome-grid input:focus,#adm-outcome-grid textarea:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
    #adm-outcome-grid .req{color:#e74c3c}

    /* ---- Discharge modal: patient admission + doctor's entries panels ---- */
    #discharge-modal .adm-design-panel{background:#fff;border:1px solid #cbd5e1;border-radius:6px;padding:14px;margin-bottom:16px;display:flex;flex-direction:column;gap:12px}
    #discharge-modal .adm-panel-title{font-weight:700;color:#1e3a8a;font-size:13px;border-bottom:1px solid #e2e8f0;padding-bottom:6px}
    #discharge-modal .adm-panel-row{display:flex;gap:12px}
    #discharge-modal .adm-panel-col{flex:1;display:flex;flex-direction:column;gap:4px}
    #discharge-modal .adm-panel-col>label{font-size:10px;font-weight:700;color:#1e3a8a}
    #discharge-modal .adm-select{border:1px solid #cbd5e1;border-radius:4px;padding:7px 10px;font-size:11.5px;background:#f8fafc;color:#1e293b;width:100%;font-family:inherit}
    #discharge-modal .adm-select:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
    #discharge-modal .adm-date-input-group{display:flex;align-items:center;gap:7px;border:1px solid #cbd5e1;border-radius:4px;padding:7px 10px;background:#f8fafc;font-size:11.5px;color:#1e293b}
    #discharge-modal .adm-date-input-group svg{flex-shrink:0;color:#1e3a8a}
    #discharge-modal .adm-doc-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:11.5px}
    #discharge-modal .adm-doc-card{background:#f8fafc;padding:10px;border-radius:4px;border:1px solid #e2e8f0}
    #discharge-modal .adm-doc-card strong{color:#1e3a8a;display:block;margin-bottom:4px;font-size:11.5px}
    #discharge-modal .adm-doc-card p{color:#334155;line-height:1.4;margin:0;white-space:pre-wrap}
    #discharge-modal .adm-doc-card.full{grid-column:1/-1}
    #discharge-modal .adm-nav-btn{background-color:#1e3a8a;color:#fff;border:none;border-radius:4px;padding:8px 14px;font-size:11px;font-weight:700;cursor:pointer;font-family:inherit;letter-spacing:.3px;display:inline-flex;align-items:center;gap:6px}
    #discharge-modal .adm-nav-btn:hover{background-color:#16306e}
    @media (max-width:640px){
        #discharge-modal .adm-panel-row{flex-direction:column}
        #discharge-modal .adm-doc-grid{grid-template-columns:1fr}
        #discharge-modal .adm-doc-card.full{grid-column:span 1}
    }
    #adm-pending-warning{background:#FEF5E0;border:1px solid #F0AD4E;border-left:4px solid #f0ad4e;color:#8A5A00;border-radius:4px;padding:10px 12px;font-size:12px;margin-bottom:14px;display:none;align-items:flex-start;gap:9px}
    #adm-pending-warning.show{display:flex}
    #adm-pending-warning svg{width:16px;height:16px;fill:none;stroke:#B9770E;stroke-width:2;flex-shrink:0;margin-top:1px}
    #adm-charge-modal .modal-content{max-width:520px !important}
    #adm-charge-total{display:flex;justify-content:space-between;align-items:center;background:#F1F5F9;border:1px solid #C9D4E0;border-radius:4px;padding:10px 12px;font-size:12px}
    #adm-charge-total b{color:#1E7A34;font-size:15px}

    /* ---- Ward occupancy overview: counts are taken from the beds table ---- */
    #ward-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(138px,1fr));gap:10px;margin-bottom:16px}
    #ward-stats .ws{background:#F8FAFC;border:1px solid #DCE4EC;border-left:4px solid #0b5fa5;border-radius:5px;padding:10px 12px}
    #ward-stats .ws .lbl{font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#64748B}
    #ward-stats .ws .val{font-size:21px;font-weight:800;color:#0F2D59;line-height:1.2;margin-top:3px}
    #ward-stats .ws .sub{font-size:10.5px;color:#64748B}
    #ward-stats .ws.occ{border-left-color:#C0392B}
    #ward-stats .ws.free{border-left-color:#1E7A34}
    #ward-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(258px,1fr));gap:12px}
    #ward-grid .wcard{border:1px solid #DCE4EC;border-radius:6px;background:#fff;padding:12px 13px}
    #ward-grid .wcard .nm{font-weight:700;color:#0F2D59;font-size:13.5px;line-height:1.25}
    #ward-grid .wcard .cd{font-size:10.5px;color:#64748B;margin-top:2px;word-break:break-all}
    #ward-grid .wcard .tags{display:flex;gap:5px;flex-wrap:wrap;margin-top:7px}
    #ward-grid .wcard .tag{font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;background:#E6EEF5;color:#33526F;border-radius:3px;padding:3px 6px}
    #ward-grid .wcard .tag.hot{background:#FDECEA;color:#C0392B}
    #ward-grid .wcard .bar{height:8px;border-radius:4px;background:#E2E8F0;overflow:hidden;margin-top:9px}
    #ward-grid .wcard .bar i{display:block;height:100%;background:#0b5fa5}
    #ward-grid .wcard .bar i.full{background:#C0392B}
    #ward-grid .wcard .figs{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:9px;text-align:center;font-size:10px;color:#64748B}
    #ward-grid .wcard .figs b{display:block;font-size:15px;color:#1E293B;margin-top:1px}
    #ward-grid .wcard .figs b.warn{color:#C0392B}
    #ward-grid .empty{grid-column:1/-1;text-align:center;padding:22px;color:#8A94A6;font-size:12px}
    #ward-no-beds{font-size:10.5px;color:#8A5A00;background:#FEF5E0;border:1px solid #F0AD4E;border-radius:4px;padding:5px 8px;margin-top:8px}

    /* ---- Transfer Ward & Bed ---- */
    #transfer-modal .modal-content{max-width:640px !important}
    #transfer-current{background:#F0F4F8;border:1px solid #DCE4EC;border-radius:6px;padding:11px 12px;font-size:12.5px;margin-bottom:14px}
    #transfer-current div+div{margin-top:4px}
    #transfer-current b{color:#0072BC}
    #transfer-move{display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:#E7F3FC;border:1px solid #b2c8de;border-radius:5px;padding:10px 12px;font-size:12.5px;margin-bottom:14px}
    #transfer-move .arrow{color:#0b5fa5}
    #transfer-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    #transfer-grid .full{grid-column:span 2}
    #transfer-grid label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#64748B;margin-bottom:5px}
    #transfer-grid select,#transfer-grid textarea{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;font-family:inherit;background:#fff;color:#222;box-sizing:border-box}
    #transfer-grid textarea{resize:vertical}
    #transfer-grid select:focus,#transfer-grid textarea:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
    #transfer-grid .opt{font-size:10.5px;color:#64748B;margin-top:5px}
    #transfer-history{border:1px solid #DCE4EC;border-radius:5px;overflow:hidden;margin-top:14px}
    #transfer-history .th-hd{background:#E6EEF5;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#222;padding:7px 9px;border-bottom:2px solid #b2c8de;display:flex;justify-content:space-between;gap:8px}
    #transfer-history .th-bd{max-height:170px;overflow-y:auto}
    #transfer-history table{width:100%;border-collapse:collapse;font-size:11px}
    #transfer-history th{background:#F1F5F9;font-size:9.5px;text-transform:uppercase;letter-spacing:.3px;color:#475569;padding:5px 8px;text-align:left;border-bottom:1px solid #E1E8F0}
    #transfer-history td{padding:6px 8px;border-bottom:1px solid #EEF2F7;vertical-align:top}
    #transfer-history .mv{font-weight:600;color:#1E293B}
    #transfer-history .why{color:#64748B;font-style:italic}
    #transfer-history .empty{padding:14px;text-align:center;color:#8A94A6}
    @media (max-width:640px){
        #adm-outcome-grid{grid-template-columns:1fr}
        #adm-outcome-grid .full{grid-column:span 1}
        #transfer-grid{grid-template-columns:1fr}
        #transfer-grid .full{grid-column:span 1}
    }

    /* ---- DRAFT ADMISSIONS (in-progress admissions pending finalization) ---- */
    #draft-banner{background:#E2E8F0;border:1px solid #CBD5E1;border-left:4px solid #64748B;color:#334155;border-radius:5px;padding:11px 14px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
    #draft-banner .ttl{font-weight:800;font-size:12.5px;letter-spacing:.3px;text-transform:uppercase;color:#334155}
    #draft-banner .sub{font-size:11.5px;color:#64748B;margin-top:2px}
    #draft-banner button{background:#334155;color:#fff;border:none;border-radius:4px;padding:7px 13px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
    #draft-banner button:hover{background:#1E293B}
    #draft-table td .dr-code{font-family:Consolas,'Courier New',monospace;font-weight:700;color:#334155;font-size:11px}
    #draft-table .draft-tag{display:inline-block;padding:2px 8px;border-radius:10px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
    .draft-tag.pending{background:#F1F5F9;color:#475569}
    .draft-tag.finalized{background:#DCFCE7;color:#166534}
    .draft-tag.cancelled{background:#FEE2E2;color:#991B1B}
    #draft-table .dr-actions{display:flex;gap:5px;flex-wrap:wrap}
    #draft-table .dr-actions button{font-size:10px;padding:4px 8px;border-radius:3px;border:1px solid #CBD5E1;background:#fff;color:#334155;cursor:pointer;font-family:inherit;font-weight:600;display:inline-flex;align-items:center;gap:4px}
    #draft-table .dr-actions button.fin{background:#0b5fa5;border-color:#0b5fa5;color:#fff}
    #draft-table .dr-actions button.fin:hover{background:#094c85}
    #draft-table .dr-actions button.ed:hover{background:#F1F5F9}
    #draft-table .dr-actions button.del{color:#C0392B;border-color:#F0B7B2}
    #draft-table .dr-actions button.del:hover{background:#FDECEA}
    #draft-modal .modal-content{max-width:640px !important}
    #draft-patient-results{position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #C9D4E0;border-top:none;border-radius:0 0 6px 6px;box-shadow:0 6px 14px rgba(15,45,89,.12);max-height:220px;overflow-y:auto;z-index:50}
    #draft-patient-results div{padding:9px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #EEF2F7;color:#1E293B}
    #draft-patient-results div:hover{background:#E7F3FC;color:#0F2D59}
    #draft-patient-results .hpno{color:#0072BC;font-weight:700}
    #draft-patient-results .empty{padding:10px 12px;color:#64748B;cursor:default}
    #draft-patient-results .empty:hover{background:#fff;color:#64748B}
    #draft-current{display:flex;justify-content:space-between;align-items:center;gap:10px;background:#F1F5F9;border:1px solid #C9D4E0;border-radius:4px;padding:9px 12px;margin-bottom:14px;font-size:12px;color:#334155}
    #draft-current b{color:#0072BC}
    #draft-current .no-bed{color:#8A5A00;font-size:11px}
</style>

<!-- ================= WARD OCCUPANCY ================= -->
<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-hospital"></i> Ward Occupancy</h2>
        <div style="display:flex;gap:8px;align-items:center;">
            <select id="filter-ward-type" style="padding:6px 9px;border:1px solid #C9D4E0;border-radius:4px;font-size:12px;font-family:inherit;background:#fff;">
                <option value="">All Ward Types</option>
            </select>
            <button class="btn btn-secondary btn-sm" id="refresh-wards-btn"><i class="fa-solid fa-rotate"></i> Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div id="ward-stats"></div>
        <div id="ward-grid">
            <div class="empty">Loading ward occupancy...</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-bed-pulse"></i> Admissions Register</h2>
        <div style="display:flex;gap:8px;">
            <button class="btn btn-primary btn-sm" id="admit-patient-btn"><i class="fa-solid fa-user-plus"></i> Admit Patient</button>
            <button class="btn btn-secondary btn-sm" id="refresh-admissions-btn"><i class="fa-solid fa-rotate"></i> Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <select id="filter-adm-ward">
                    <option value="">All Wards</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filter-adm-status">
                    <option value="">All Status</option>
                    <option value="Admitted">Admitted</option>
                    <option value="Discharged">Discharged</option>
                </select>
            </div>
            <div class="form-group" style="flex:1;min-width:220px;">
                <input type="text" id="filter-adm-q" placeholder="Search patient, hospital no. or admission code...">
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Admission Code</th>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Hospital No.</th>
                        <th>Ward</th>
                        <th>Bed</th>
                        <th>Type</th>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th>Discharge Summary</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="admissions-table">
                    <tr><td colspan="11" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= DRAFT ADMISSIONS ================= -->
<div id="draft-banner">
    <div>
        <div class="ttl"><i class="fa-solid fa-file-pen"></i> Draft Admissions &mdash; In-Progress Admissions Pending Finalization</div>
        <div class="sub">A draft does not occupy a bed. Finalizing it creates the real admission and occupies the bed.</div>
    </div>
    <button type="button" id="new-draft-btn"><i class="fa-solid fa-plus"></i> Create New Draft</button>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-file-lines"></i> Pending Draft Admissions</h2>
        <div style="display:flex;gap:8px;align-items:center;">
            <select id="filter-draft-status" style="padding:6px 9px;border:1px solid #C9D4E0;border-radius:4px;font-size:12px;font-family:inherit;background:#fff;">
                <option value="DRAFT">Pending Drafts</option>
                <option value="FINALIZED">Finalized</option>
                <option value="CANCELLED">Discarded</option>
                <option value="ALL">All Drafts</option>
            </select>
            <input type="text" id="filter-draft-q" placeholder="Search patient, hospital no. or draft no..." style="padding:6px 9px;border:1px solid #C9D4E0;border-radius:4px;font-size:12px;font-family:inherit;background:#fff;width:250px;">
            <button class="btn btn-secondary btn-sm" id="refresh-drafts-btn"><i class="fa-solid fa-rotate"></i> Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table id="draft-table">
                <thead>
                    <tr>
                        <th>Draft No</th>
                        <th>Patient</th>
                        <th>Hospital No.</th>
                        <th>Ward / Bed</th>
                        <th>Admission Date</th>
                        <th>Admitting Doctor</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="drafts-table">
                    <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Draft Admission Modal: create a new draft, or edit a pending one -->
<div class="modal" id="draft-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-file-pen"></i> <span id="draft-modal-title">Create Draft Admission</span></h3>
            <button class="modal-close" id="close-draft-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="draft-form">
                <div id="draft-current" style="display:none;">
                    <span id="draft-current-text"></span>
                    <span class="no-bed" id="draft-current-note"></span>
                </div>

                <div class="form-group" style="position:relative;">
                    <label for="draft-patient">Patient *</label>
                    <input type="text" id="draft-patient" placeholder="Type name or hospital number..." autocomplete="off" disabled>
                    <input type="hidden" id="draft-patient-id">
                    <div id="draft-patient-results" style="display:none;"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="draft-ward">Ward</label>
                        <select id="draft-ward">
                            <option value="">Select Ward</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="draft-bed">Bed</label>
                        <select id="draft-bed">
                            <option value="">Select Ward first</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="draft-date">Admission Date &amp; Time</label>
                        <input type="datetime-local" id="draft-date">
                    </div>
                    <div class="form-group">
                        <label for="draft-type">Admission Type</label>
                        <select id="draft-type">
                            <option value="Routine">Routine</option>
                            <option value="Emergency">Emergency</option>
                            <option value="Elective">Elective</option>
                            <option value="Transfer">Transfer</option>
                            <option value="Maternity">Maternity</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="draft-doctor">Admitting Doctor</label>
                        <input type="text" id="draft-doctor" placeholder="e.g. Dr. K. Mensah">
                    </div>
                    <div class="form-group">
                        <label for="draft-diagnosis">Provisional Diagnosis</label>
                        <input type="text" id="draft-diagnosis" placeholder="Reason for admission (optional)">
                    </div>
                </div>

                <div class="form-group">
                    <label for="draft-notes">Admission Notes</label>
                    <textarea id="draft-notes" placeholder="Additional clinical notes (optional)"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancel-draft">Cancel</button>
            <button class="btn btn-primary" id="save-draft"><i class="fa-solid fa-check"></i> Save Draft</button>
        </div>
    </div>
</div>

<!-- Admit Patient Modal -->
<div class="modal" id="admit-modal">
    <div class="modal-content" style="max-width:640px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-plus"></i> Admit Patient</h3>
            <button class="modal-close" id="close-admit-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="admit-form">
                <div class="form-group" style="position:relative;">
                    <label for="adm-patient">Patient *</label>
                    <input type="text" id="adm-patient" placeholder="Type name or hospital number..." autocomplete="off">
                    <input type="hidden" id="adm-patient-id">
                    <div id="adm-patient-results" style="display:none;"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="adm-ward">Ward *</label>
                        <select id="adm-ward" required>
                            <option value="">Select Ward</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="adm-bed">Bed *</label>
                        <select id="adm-bed" required>
                            <option value="">Select Ward first</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="adm-date">Admission Date &amp; Time *</label>
                        <input type="datetime-local" id="adm-date" required>
                    </div>
                    <div class="form-group">
                        <label for="adm-type">Admission Type *</label>
                        <select id="adm-type" required>
                            <option value="Routine">Routine</option>
                            <option value="Emergency">Emergency</option>
                            <option value="Elective">Elective</option>
                            <option value="Transfer">Transfer</option>
                            <option value="Maternity">Maternity</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="adm-doctor">Admitting Doctor</label>
                        <input type="text" id="adm-doctor" placeholder="e.g. Dr. K. Mensah">
                    </div>
                    <div class="form-group">
                        <label for="adm-referred">Referred By</label>
                        <input type="text" id="adm-referred" placeholder="Referral source / department">
                    </div>
                </div>

                <div class="form-group">
                    <label for="adm-diagnosis">Provisional Diagnosis</label>
                    <input type="text" id="adm-diagnosis" placeholder="Reason for admission (optional)">
                </div>

                <div class="form-group">
                    <label for="adm-notes">Admission Notes</label>
                    <textarea id="adm-notes" placeholder="Additional clinical notes (optional)"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancel-admit">Cancel</button>
            <button class="btn btn-primary" id="save-admit"><i class="fa-solid fa-check"></i> Admit Patient</button>
        </div>
    </div>
</div>

<!-- Discharge Modal: admission info + discharge process + doctor's entries + billing (behind a toggle) -->
<div class="modal" id="discharge-modal">
    <div class="modal-content" style="max-width:880px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-right-from-bracket"></i> Discharge Patient</h3>
            <button class="modal-close" id="close-discharge-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="discharge-summary" style="background:#F0F4F8;border:1px solid #DCE4EC;border-radius:6px;padding:12px;margin-bottom:16px;font-size:13px;"></div>

            <!-- Toggle Button to Show/Hide Billing Details -->
            <div style="margin-bottom:12px;">
                <button type="button" id="toggle-billing-btn" class="adm-nav-btn">
                    <i class="fa-solid fa-receipt"></i> <span id="toggle-billing-label">SHOW BILLING DETAILS</span>
                </button>
            </div>

            <!-- Discharge Process & Admission Info Panel -->
            <div id="patient-admission-panel" class="adm-design-panel">
                <div class="adm-panel-title">PATIENT DISCHARGE &amp; ADMISSION MANAGEMENT</div>
                <div class="adm-panel-row">
                    <div class="adm-panel-col">
                        <label>ADMISSION DATE &amp; TIME</label>
                        <div class="adm-date-input-group">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <span id="adm-admission-date">—</span>
                        </div>
                    </div>
                    <div class="adm-panel-col">
                        <label for="discharge-outcome">DISCHARGE PROCESS <span style="color:#e74c3c;">*</span></label>
                        <select id="discharge-outcome" class="adm-select" required>
                            <option value="">-- Select Status / Outcome --</option>
                            <option value="Improved">Improved</option>
                            <option value="Unchanged">Unchanged</option>
                            <option value="Referred">Referred</option>
                            <option value="Transferred Out">Transferred Out</option>
                            <option value="Discharged on Medical Advice">Discharged on Medical Advice</option>
                            <option value="Absconded">Absconded</option>
                            <option value="Died">Died</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Doctor's Entries & Medical Record History (read from the admission + linked visit) -->
            <div class="adm-design-panel">
                <div class="adm-panel-title">DOCTOR'S ENTRIES &amp; MEDICAL RECORD HISTORY</div>
                <div class="adm-doc-grid">
                    <div class="adm-doc-card">
                        <strong>Presenting History / Complaints:</strong>
                        <p id="adm-presenting-history">Loading...</p>
                    </div>
                    <div class="adm-doc-card">
                        <strong>Clinical Examination:</strong>
                        <p id="adm-clinical-exam">No clinical examination record on file.</p>
                    </div>
                </div>
                <div class="adm-doc-card full">
                    <strong>Diagnosis &amp; Doctor's Notes:</strong>
                    <p id="adm-doctor-diagnosis">No admission diagnosis recorded.</p>
                </div>
            </div>

            <!-- Billing Summary Container (hidden by default, toggled by the button above) -->
            <div id="billing-summary-container" style="display:none;">
                <div style="font-weight:700;color:#1e3a8a;font-size:13px;margin-bottom:8px;">BILLING SUMMARY</div>

                <!-- Outstanding-billing warning (shown only when a real balance is due) -->
                <div id="adm-pending-warning">
                    <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span id="adm-pending-warning-text"></span>
                </div>

                <!-- BILLING SUMMARY (live invoices + line items) -->
                <div id="adm-bill-card">
                    <div id="adm-bill-head">
                        <span><i class="fa-solid fa-receipt"></i> Billing Summary</span>
                        <span style="display:flex;gap:8px;align-items:center;">
                            <span style="color:#D1E5F7;font-size:10px;" id="adm-bill-count"></span>
                            <button type="button" id="adm-open-charge"><i class="fa-solid fa-plus"></i> Add Billing Item</button>
                        </span>
                    </div>
                    <div id="adm-bill-body">
                        <div class="adm-bill-sec-label">Invoices</div>
                        <div id="adm-bill-inv-scroll">
                            <div class="empty" style="padding:14px;text-align:center;color:#8A94A6;font-size:12px;">Loading invoices...</div>
                        </div>
                        <div class="adm-bill-sec-label">Itemised Charges</div>
                        <div id="adm-bill-scroll">
                            <div class="empty" style="padding:14px;text-align:center;color:#8A94A6;font-size:12px;">Loading billing summary...</div>
                        </div>
                    </div>
                    <div id="adm-bill-foot">
                        <span>Auto-generated from the patient's invoices &amp; billing items</span>
                        <span>Fee To Be Paid: <span id="adm-bill-due" class="due">GHS 0.00</span></span>
                    </div>
                </div>
            </div>

            <!-- CLINICAL DISCHARGE SUMMARY -->
            <div id="adm-outcome-grid">
                <div>
                    <label for="discharge-followup">Follow-up Date</label>
                    <input type="date" id="discharge-followup">
                </div>
                <div>
                    <label for="discharge-final-dx">Final Diagnosis <span class="req">*</span></label>
                    <input type="text" id="discharge-final-dx" maxlength="255" placeholder="Confirmed diagnosis at discharge">
                </div>
                <div class="full">
                    <label for="discharge-notes">Discharge Notes</label>
                    <textarea id="discharge-notes" rows="2" placeholder="Summary of discharge status / instructions (optional)"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancel-discharge">Cancel</button>
            <button class="btn btn-danger" id="confirm-discharge"><i class="fa-solid fa-check"></i> Confirm Discharge</button>
        </div>
    </div>
</div>

<!-- ADD BILLING CHARGE MODAL -->
<div class="modal" id="adm-charge-modal">
    <div class="modal-content" style="max-width:520px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-cart-plus"></i> Add Patient Billing Charge</h3>
            <button class="modal-close" id="close-adm-charge">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="adm-charge-invoice">Add To Invoice <span class="req" style="color:#e74c3c">*</span></label>
                <select id="adm-charge-invoice">
                    <option value="">-- Select an open invoice --</option>
                </select>
            </div>
            <div class="form-group">
                <label for="adm-charge-category">Billing Category <span class="req" style="color:#e74c3c">*</span></label>
                <select id="adm-charge-category">
                    <option value="">-- Select Item / Category --</option>
                    <option value="other|dressing">Gauze / Dressing Material</option>
                    <option value="other|maintenance">Maintenance Fee</option>
                    <option value="other|consumables">Medical Consumables (Syringes, Gloves, IV sets)</option>
                    <option value="bed|accommodation">Accommodation / Bed Charge</option>
                    <option value="other|other">Other Custom Charge</option>
                </select>
            </div>
            <div class="form-group">
                <label for="adm-charge-desc">Description / Details <span class="req" style="color:#e74c3c">*</span></label>
                <input type="text" id="adm-charge-desc" maxlength="255" placeholder="e.g. Sterile Gauze Pack (X-large) or Ward Stay Day 3">
            </div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label for="adm-charge-qty">Quantity</label>
                    <input type="number" id="adm-charge-qty" min="1" step="1" value="1">
                </div>
                <div class="form-group">
                    <label for="adm-charge-price">Unit Price (GHS)</label>
                    <input type="number" id="adm-charge-price" min="0" step="0.01" placeholder="0.00">
                </div>
            </div>
            <div id="adm-charge-total">
                <span class="fw-bold" style="color:#475569;">Total Line Amount:</span>
                <b id="adm-charge-total-value">GHS 0.00</b>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancel-adm-charge">Cancel</button>
            <button class="btn btn-primary" id="save-adm-charge"><i class="fa-solid fa-check"></i> Add to Invoice</button>
        </div>
    </div>
</div>

<!-- TRANSFER WARD & BED MODAL -->
<div class="modal" id="transfer-modal">
    <div class="modal-content" style="max-width:640px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-arrows-right-left"></i> Transfer Ward &amp; Bed</h3>
            <button class="modal-close" id="close-transfer-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="transfer-current"></div>

            <div id="transfer-move">
                <i class="fa-solid fa-arrow-right-arrow-left arrow"></i>
                <span id="transfer-move-text">Select the destination ward and bed.</span>
            </div>

            <div id="transfer-grid">
                <div>
                    <label for="transfer-ward">To Ward <span style="color:#e74c3c;">*</span></label>
                    <select id="transfer-ward">
                        <option value="">-- Select Ward --</option>
                    </select>
                </div>
                <div>
                    <label for="transfer-bed">To Bed <span style="color:#e74c3c;">*</span></label>
                    <select id="transfer-bed">
                        <option value="">Select Ward first</option>
                    </select>
                </div>
                <div class="full">
                    <label for="transfer-reason">Reason for Transfer</label>
                    <textarea id="transfer-reason" rows="2" maxlength="255"
                        placeholder="Optional — e.g. clinical need, isolation, bed unavailable"></textarea>
                    <div class="opt">Left blank, the move is still recorded with a timestamp and the staff member who made it.</div>
                </div>
            </div>

            <div id="transfer-history">
                <div class="th-hd">
                    <span>Bed History For This Stay</span>
                    <span id="transfer-history-count" style="color:#64748B;font-weight:600;"></span>
                </div>
                <div class="th-bd" id="transfer-history-body">
                    <div class="empty">Loading movement history...</div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancel-transfer">Cancel</button>
            <button class="btn btn-primary" id="confirm-transfer"><i class="fa-solid fa-arrows-right-left"></i> Transfer Patient</button>
        </div>
    </div>
</div>

<script>
let admissionsData = [];
let dischargeTarget = null;
let wardsList = [];
let wardStats = null;
let transferTarget = null;

async function initAdmissions() {
    setupAdmissionListeners();
    await Promise.all([loadAdmissionWards(), loadAdmissions()]);
    await loadWardOccupancy();
    // Draft admissions sit alongside the register on this page. Listeners are
    // attached before the load so the section is usable as soon as it renders.
    setupDraftListeners();
    await loadDrafts();
}

async function loadAdmissionWards() {
    try {
        const response = await fetch('/hms/backend/api/wards.php');
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load wards');

        wardsList = data.wards || [];
        const bindings = {'filter-adm-ward': '', 'adm-ward': 'Select Ward'};
        Object.keys(bindings).forEach(id => {
            const sel = document.getElementById(id);
            if (!sel) return;
            sel.innerHTML = '<option value="">' + bindings[id] + '</option>' +
                wardsList.map(w => `<option value="${w.id}">${escHtml(w.ward_name)}</option>`).join('');
        });
    } catch (error) {
        console.error('Admission wards load error:', error);
    }
}

async function loadAdmissions() {
    const tbody = document.getElementById('admissions-table');
    const wardId = document.getElementById('filter-adm-ward').value;
    const status = document.getElementById('filter-adm-status').value;
    const q = document.getElementById('filter-adm-q').value.trim();

    const params = new URLSearchParams();
    if (wardId) params.set('ward_id', wardId);
    if (status) params.set('status', status);
    if (q) params.set('q', q);

    try {
        const response = await fetch('/hms/backend/api/admissions.php?' + params.toString());
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load admissions');
        admissionsData = data.admissions || [];
        renderAdmissions(tbody);
    } catch (error) {
        console.error('Admissions load error:', error);
        tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">Failed to load admissions</td></tr>';
    }
}

function renderAdmissions(tbody) {
    if (!tbody) return;
    if (!admissionsData.length) {
        tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">No admissions found</td></tr>';
        return;
    }
    tbody.innerHTML = admissionsData.map(a => {
        const isAdmitted = a.status === 'Admitted';
        const badge = isAdmitted ? 'badge-info' : 'badge-secondary';
        const bedLabel = a.bed_number + (a.bed_type ? ` (${a.bed_type})` : '');
        const actionCell = isAdmitted
            ? `<button class="btn btn-secondary btn-sm" data-transfer="${a.id}" title="Move to another ward or bed"><i class="fa-solid fa-arrows-right-left"></i> Transfer</button>
               <button class="btn btn-danger btn-sm" data-discharge="${a.id}"><i class="fa-solid fa-right-from-bracket"></i> Discharge</button>`
            : '<span style="color:#95A5A6;">—</span>';
        return `
        <tr>
            <td><strong style="color:#0F2D59;">${escHtml(a.admission_code)}</strong></td>
            <td>${fmtDateTime(a.admission_date)}</td>
            <td><strong>${escHtml(a.patient_name || '-')}</strong></td>
            <td>${escHtml(a.hospital_number || '-')}</td>
            <td>${escHtml(a.ward_name || '-')}</td>
            <td>${escHtml(bedLabel)}</td>
            <td><span class="badge badge-secondary">${escHtml(a.admission_type || 'Routine')}</span></td>
            <td>${escHtml(a.admitting_doctor || '-')}</td>
            <td><span class="badge ${badge}">${escHtml(a.status)}</span></td>
            <td>${dischargedMeta(a)}</td>
            <td style="white-space:nowrap;">${actionCell}</td>
        </tr>`;
    }).join('');
}

// Discharge summary cell: outcome, final diagnosis and follow-up date are
// stored on the admission, so they show here for already-discharged patients.
function dischargedMeta(a) {
    if (a.status !== 'Discharged') return '<span style="color:#95A5A6;">—</span>';
    const parts = [];
    if (a.discharge_outcome) parts.push('<span class="badge badge-info">' + escHtml(a.discharge_outcome) + '</span>');
    if (a.final_diagnosis) parts.push('<div style="font-size:11px;margin-top:3px;">' + escHtml(a.final_diagnosis) + '</div>');
    if (a.follow_up_date) parts.push('<div style="font-size:10.5px;color:#64748B;margin-top:2px;">Follow-up: ' + escHtml(a.follow_up_date) + '</div>');
    if (a.discharged_at) parts.push('<div style="font-size:10.5px;color:#64748B;">' + fmtDateTime(a.discharged_at) + '</div>');
    return parts.length ? parts.join(' ') : '<span style="color:#95A5A6;">—</span>';
}

function setupAdmissionListeners() {
    const refreshBtn = document.getElementById('refresh-admissions-btn');
    if (refreshBtn) refreshBtn.addEventListener('click', loadAdmissions);

    document.getElementById('admit-patient-btn').addEventListener('click', openAdmitModal);
    document.getElementById('close-admit-modal').addEventListener('click', closeAdmitModal);
    document.getElementById('cancel-admit').addEventListener('click', closeAdmitModal);
    document.getElementById('save-admit').addEventListener('click', submitAdmission);
    document.getElementById('close-discharge-modal').addEventListener('click', closeDischargeModal);
    document.getElementById('cancel-discharge').addEventListener('click', closeDischargeModal);
    document.getElementById('confirm-discharge').addEventListener('click', submitDischarge);
    document.getElementById('toggle-billing-btn').addEventListener('click', toggleBillingDetails);

    // Billing charge dialog (opened from the discharge billing summary)
    document.getElementById('adm-open-charge').addEventListener('click', openChargeModal);
    document.getElementById('close-adm-charge').addEventListener('click', closeChargeModal);
    document.getElementById('cancel-adm-charge').addEventListener('click', closeChargeModal);
    document.getElementById('save-adm-charge').addEventListener('click', saveCharge);

    // Transfer Ward & Bed
    document.getElementById('refresh-wards-btn').addEventListener('click', loadWardOccupancy);
    document.getElementById('filter-ward-type').addEventListener('change', renderWards);
    document.getElementById('close-transfer-modal').addEventListener('click', closeTransferModal);
    document.getElementById('cancel-transfer').addEventListener('click', closeTransferModal);
    document.getElementById('confirm-transfer').addEventListener('click', submitTransfer);
    document.getElementById('transfer-ward').addEventListener('change', function() {
        loadTransferBeds(this.value);
    });
    document.getElementById('adm-charge-qty').addEventListener('input', updateChargeTotal);
    document.getElementById('adm-charge-price').addEventListener('input', updateChargeTotal);
    document.getElementById('adm-charge-category').addEventListener('change', function() {
        // Prefill a sensible description from the chosen category.
        const map = {
            'other|dressing': 'Dressing Material / Gauze',
            'other|maintenance': 'Maintenance Fee',
            'other|consumables': 'Medical Consumables',
            'bed|accommodation': 'Accommodation / Bed Charge',
            'other|other': 'Custom Charge'
        };
        const desc = document.getElementById('adm-charge-desc');
        if (desc && !desc.value.trim()) {
            const label = map[this.value];
            if (label) desc.value = label;
        }
    });

    document.getElementById('filter-adm-ward').addEventListener('change', loadAdmissions);
    document.getElementById('filter-adm-status').addEventListener('change', loadAdmissions);

    let card;
    let qTimer;
    const qInput = document.getElementById('filter-adm-q');
    if (qInput) qInput.addEventListener('input', function() {
        clearTimeout(qTimer);
        qTimer = setTimeout(loadAdmissions, 400);
    });

    document.getElementById('adm-ward').addEventListener('change', function() {
        loadAvailableBeds(this.value);
    });

    // Patient search with results dropdown
    const patInput = document.getElementById('adm-patient');
    const patResults = document.getElementById('adm-patient-results');
    patInput.addEventListener('input', function() {
        clearTimeout(card);
        const query = this.value.trim();
        if (query.length < 2) {
            document.getElementById('adm-patient-id').value = '';
            patResults.style.display = 'none';
            patResults.innerHTML = '';
            return;
        }
        card = setTimeout(() => searchAdmissionPatients(query), 300);
    });
    document.addEventListener('click', function(e) {
        if (!patResults.contains(e.target) && e.target !== patInput) patResults.style.display = 'none';
    });

    // Discharge + Transfer buttons (event delegation)
    const tbody = document.getElementById('admissions-table');
    tbody.addEventListener('click', function(e) {
        const dis = e.target.closest('[data-discharge]');
        if (dis) {
            openDischargeModal(parseInt(dis.getAttribute('data-discharge'), 10));
            return;
        }
        const tr = e.target.closest('[data-transfer]');
        if (tr) openTransferModal(parseInt(tr.getAttribute('data-transfer'), 10));
    });
}

function nowLocal() {
    const d = new Date();
    const p = n => ('0' + n).slice(-2);
    return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + 'T' + p(d.getHours()) + ':' + p(d.getMinutes());
}

async function searchAdmissionPatients(query) {
    const resultsBox = document.getElementById('adm-patient-results');
    try {
        const response = await fetch('/hms/backend/api/patients.php?action=search&q=' + encodeURIComponent(query) + '&limit=10');
        const data = await response.json();
        const patients = (data.success && data.patients) ? data.patients : [];
        if (!patients.length) {
            resultsBox.innerHTML = '<div class="empty">No matching patients found</div>';
            resultsBox.style.display = 'block';
            return;
        }
        resultsBox.innerHTML = patients.map((p, i) => `
            <div data-pid="${p.id}" data-name="${escHtml((p.first_name || '') + ' ' + (p.last_name || ''))}" data-hn="${escHtml(p.hospital_number || '')}">
                <span class="hpno">${escHtml((p.hospital_number || ''))}</span> &nbsp; ${escHtml((p.first_name || '') + ' ' + (p.last_name || ''))}${p.gender ? ' · ' + escHtml(p.gender) : ''}
            </div>`).join('');
        resultsBox.style.display = 'block';
        resultsBox.querySelectorAll('div[data-pid]').forEach(el => {
            el.addEventListener('click', () => {
                document.getElementById('adm-patient-id').value = el.getAttribute('data-pid');
                document.getElementById('adm-patient').value = el.getAttribute('data-hn') + ' - ' + el.getAttribute('data-name');
                resultsBox.style.display = 'none';
                resultsBox.innerHTML = '';
            });
        });
    } catch (error) {
        console.error('Patient search error:', error);
        resultsBox.style.display = 'none';
    }
}

async function loadAvailableBeds(wardId) {
    const bedSel = document.getElementById('adm-bed');
    if (!wardId) {
        bedSel.innerHTML = '<option value="">Select Ward first</option>';
        return;
    }
    bedSel.innerHTML = '<option value="">Loading beds...</option>';
    try {
        const response = await fetch('/hms/backend/api/admissions.php?action=available_beds&ward_id=' + encodeURIComponent(wardId));
        const data = await response.json();
        const beds = (data.success && data.beds) ? data.beds : [];
        if (!beds.length) {
            bedSel.innerHTML = '<option value="">No available beds</option>';
            return;
        }
        bedSel.innerHTML = beds.map(b => `<option value="${b.id}">${escHtml(b.bed_number)}${b.bed_type ? ' (' + escHtml(b.bed_type) + ')' : ''}</option>`).join('');
    } catch (error) {
        console.error('Available beds load error:', error);
        bedSel.innerHTML = '<option value="">Failed to load beds</option>';
    }
}

function openAdmitModal() {
    document.getElementById('admit-form').reset();
    document.getElementById('adm-patient-id').value = '';
    document.getElementById('adm-patient-results').style.display = 'none';
    document.getElementById('adm-date').value = nowLocal();
    document.getElementById('adm-bed').innerHTML = '<option value="">Select Ward first</option>';
    document.getElementById('adm-ward').value = '';
    document.getElementById('admit-modal').classList.add('show');
    document.getElementById('adm-patient').focus();
}

function closeAdmitModal() {
    document.getElementById('admit-modal').classList.remove('show');
}

async function submitAdmission() {
    const patientId = document.getElementById('adm-patient-id').value;
    const wardId = document.getElementById('adm-ward').value;
    const bedId = document.getElementById('adm-bed').value;
    const dateVal = document.getElementById('adm-date').value;

    if (!patientId) { showAlert('Please select a patient first', 'error'); return; }
    if (!wardId) { showAlert('Please select a ward', 'error'); return; }
    if (!bedId) { showAlert('Please select a bed', 'error'); return; }
    if (!dateVal) { showAlert('Please set the admission date', 'error'); return; }

    const saveBtn = document.getElementById('save-admit');
    saveBtn.disabled = true;

    try {
        const response = await fetch('/hms/backend/api/admissions.php?action=admit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                patient_id: patientId,
                ward_id: wardId,
                bed_id: bedId,
                admission_date: dateVal,
                admission_type: document.getElementById('adm-type').value,
                admitting_doctor: document.getElementById('adm-doctor').value,
                diagnosis: document.getElementById('adm-diagnosis').value,
                referred_by: document.getElementById('adm-referred').value,
                notes: document.getElementById('adm-notes').value
            })
        });
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Admission failed');

        showAlert('Patient admitted — ' + data.admission_code, 'success');
        closeAdmitModal();
        await loadAdmissions();
        await loadWardOccupancy();
    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        saveBtn.disabled = false;
    }
}

function openDischargeModal(id) {
    const admission = admissionsData.find(a => a.id === id);
    if (!admission) return;
    dischargeTarget = admission;
    document.getElementById('discharge-summary').innerHTML =
        `<div><strong style="color:#0072BC;">PATIENT:</strong> ${escHtml(admission.patient_name || '-')} (${escHtml(admission.hospital_number || '-')})</div>
         <div><strong style="color:#0072BC;">WARD / BED:</strong> ${escHtml(admission.ward_name || '-')} · ${escHtml(admission.bed_number || '-')}</div>
         <div><strong style="color:#0072BC;">ADMITTED:</strong> ${fmtDateTime(admission.admission_date)} · ${escHtml(admission.admission_type || '')}</div>`;
    document.getElementById('discharge-notes').value = '';
    document.getElementById('discharge-outcome').value = '';
    document.getElementById('discharge-final-dx').value = '';
    document.getElementById('discharge-followup').value = '';
    document.getElementById('adm-pending-warning').classList.remove('show');
    document.getElementById('discharge-modal').classList.add('show');

    // Admission panel: real admission date/time + doctor's record history.
    document.getElementById('adm-admission-date').textContent = fmtDateTime(admission.admission_date);
    loadAdmissionDoctorEntries(admission);

    // Billing starts collapsed behind the toggle (matching the design).
    document.getElementById('billing-summary-container').style.display = 'none';
    document.getElementById('toggle-billing-label').textContent = 'SHOW BILLING DETAILS';

    loadAdmissionBilling();
}

function closeDischargeModal() {
    document.getElementById('discharge-modal').classList.remove('show');
    document.getElementById('adm-charge-modal').classList.remove('show');
    dischargeTarget = null;
}

// Toggle the billing summary container behind the "SHOW/HIDE BILLING DETAILS" button.
function toggleBillingDetails() {
    const box = document.getElementById('billing-summary-container');
    const label = document.getElementById('toggle-billing-label');
    const hidden = box.style.display === 'none' || !box.style.display;
    box.style.display = hidden ? 'block' : 'none';
    label.textContent = hidden ? 'HIDE BILLING DETAILS' : 'SHOW BILLING DETAILS';
}

/* ============ DISCHARGE : DOCTOR'S ENTRIES & MEDICAL RECORD HISTORY ============
   Real data only: the presenting complaint comes from the patient's latest
   visit, and the diagnosis / doctor's notes come from the admission's own
   clinical fields. A visit may not exist, so the complaint shows an empty
   state then. */
async function loadAdmissionDoctorEntries(admission) {
    const present = document.getElementById('adm-presenting-history');
    const dx = document.getElementById('adm-doctor-diagnosis');
    present.textContent = 'Loading...';
    dx.textContent = 'Loading...';
    try {
        const res = await fetch('/hms/backend/api/visits.php?patient_id=' + admission.patient_id + '&per_page=1');
        const data = await res.json();
        const visit = (data.success && data.visits && data.visits.length) ? data.visits[0] : null;
        const complaint = (visit && visit.chief_complaint) ? String(visit.chief_complaint).trim() : '';
        present.textContent = complaint || 'No recorded presenting complaint on file for this admission.';
    } catch (error) {
        present.textContent = 'Could not load the presenting history.';
    }
    const diagnosis = (admission.diagnosis || '').trim();
    const notes = (admission.notes || '').trim();
    if (diagnosis || notes) {
        const parts = [];
        if (diagnosis) parts.push('Primary Diagnosis: ' + diagnosis);
        if (notes) parts.push("Doctor's Notes: " + notes);
        dx.textContent = parts.join('\n\n');
    } else {
        dx.textContent = 'No admission diagnosis or notes recorded.';
    }
}

/* ============ DISCHARGE : BILLING SUMMARY (live invoice data) ============ */
let admBilling = { invoices: [], items: [], outstanding: 0 };

async function loadAdmissionBilling() {
    if (!dischargeTarget) return;
    const scroll = document.getElementById('adm-bill-scroll');
    scroll.innerHTML = '<div class="empty">Loading billing summary...</div>';
    try {
        const res = await fetch(
            '/hms/backend/api/invoices.php?action=patient_billing&patient_id=' + dischargeTarget.patient_id
            + (dischargeTarget.visit_id ? '&visit_id=' + dischargeTarget.visit_id : '')
        );
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not load billing');

        admBilling = { invoices: data.invoices || [], items: data.items || [], outstanding: data.outstanding || 0 };
        renderAdmissionBilling();
    } catch (error) {
        scroll.innerHTML = '<div class="empty">Could not load the billing summary — ' + escHtml(error.message) + '</div>';
        document.getElementById('adm-bill-due').textContent = 'GHS 0.00';
        document.getElementById('adm-bill-due').className = 'clear';
        document.getElementById('adm-bill-count').textContent = '';
    }
}

function renderAdmissionBilling() {
    const scroll = document.getElementById('adm-bill-scroll');
    const inv = admBilling.invoices;
    const items = admBilling.items;

    // Only unsettled invoices can take another charge.
    const openInvoices = inv.filter(i => i.status === 'pending' || i.status === 'partial' || i.status === 'draft');
    document.getElementById('adm-bill-count').textContent =
        inv.length ? (inv.length + (inv.length === 1 ? ' invoice' : ' invoices')) : '';

    renderAdmissionInvoices();

    if (!inv.length) {
        scroll.innerHTML = '<div class="empty">No invoice has been raised for this patient yet.</div>';
    } else {
        let html = '<table id="adm-bill-table"><thead><tr>'
            + '<th>Category</th><th>Description</th><th class="ctr">Qty</th><th class="num">Unit (GHS)</th><th class="num">Total (GHS)</th>'
            + '</tr></thead><tbody>';
        let any = false;
        inv.forEach(invoice => {
            const mine = items.filter(it => String(it.invoice_id) === String(invoice.id));
            if (!mine.length) {
                html += '<tr><td><span class="inv-no">' + escHtml(invoice.invoice_number) + '</span></td><td colspan="4" class="empty" style="padding:8px;">'
                    + 'No line items on this invoice &middot; <strong>' + escHtml(admStatusLabel(invoice.status)) + '</strong></td></tr>';
                any = true;
                return;
            }
            mine.forEach(it => {
                any = true;
                html += '<tr>'
                    + '<td class="cat">' + escHtml(admCatLabel(it.item_type)) + '</td>'
                    + '<td>' + escHtml(it.description || it.item_type)
                    + '<span class="inv-no inv-sub">' + escHtml(invoice.invoice_number) + '</span></td>'
                    + '<td class="ctr">' + Number(it.quantity || 1) + '</td>'
                    + '<td class="num">' + admMoney(it.unit_price) + '</td>'
                    + '<td class="num" style="font-weight:700;">' + admMoney(it.total_price) + '</td>'
                    + '</tr>';
            });
        });
        html += '</tbody></table>';
        scroll.innerHTML = any ? html : '<div class="empty">No billing items recorded.</div>';
    }

    // Fee to be paid = real outstanding balance across the open invoices.
    const dueEl = document.getElementById('adm-bill-due');
    const outstanding = Number(admBilling.outstanding) || 0;
    dueEl.textContent = admMoney(outstanding);
    dueEl.className = outstanding > 0 ? 'due' : 'clear';

    // Warn staff only when there is a genuine balance outstanding.
    const warn = document.getElementById('adm-pending-warning');
    if (outstanding > 0) {
        document.getElementById('adm-pending-warning-text').innerHTML =
            'This patient has an outstanding balance of <strong>' + admMoney(outstanding)
            + '</strong> on ' + (openInvoices.length === 1 ? '1 open invoice' : openInvoices.length + ' open invoices')
            + '. You can still discharge, but the balance stays on the account.';
        warn.classList.add('show');
    } else {
        warn.classList.remove('show');
    }

    // Keep the "add charge" invoice picker in step with the billing summary.
    const pick = document.getElementById('adm-charge-invoice');
    const keep = pick.value;
    pick.innerHTML = openInvoices.length
        ? openInvoices.map(i => '<option value="' + i.id + '">' + escHtml(i.invoice_number) + ' — ' + admMoney(i.net_amount) + ' (' + escHtml(admStatusLabel(i.status)) + ')</option>').join('')
        : '<option value="">-- No open invoice to add to --</option>';
    if (keep && openInvoices.some(i => String(i.id) === String(keep))) pick.value = keep;
    document.getElementById('adm-open-charge').disabled = !openInvoices.length;
    document.getElementById('adm-open-charge').style.opacity = openInvoices.length ? '1' : '.5';
    document.getElementById('adm-open-charge').style.cursor = openInvoices.length ? 'pointer' : 'not-allowed';
}

/* Invoice status, shown as a pill on each invoice row. */
function admStatusLabel(status) {
    return { draft: 'Draft', pending: 'Pending', partial: 'Partial', paid: 'Paid', cancelled: 'Cancelled' }[status] || status || '';
}

/* billing_items.item_type is an enum in the database; show it as the plain
   word the mockup used rather than the raw underscore value. */
function admCatLabel(type) {
    return {
        consultation: 'Consultation', procedure: 'Procedure', drug: 'Drug',
        lab_test: 'Lab Test', radiology: 'Radiology', bed: 'Bed', other: 'Other'
    }[type] || type || 'Other';
}

/* The invoice table above the line items. Every figure comes straight from
   the invoices row - net_amount is what the patient owes on that invoice,
   not a sum of its line items, so an invoice with a discount still reads
   correctly. */
function renderAdmissionInvoices() {
    const box = document.getElementById('adm-bill-inv-scroll');
    const inv = admBilling.invoices;
    if (!box) return;

    if (!inv.length) {
        box.innerHTML = '<div class="empty">No invoice has been raised for this patient yet.</div>';
        return;
    }

    let html = '<table id="adm-bill-inv-table"><thead><tr>'
        + '<th>Invoice No</th><th>Date</th><th>Status</th><th class="num">Net (GHS)</th>'
        + '</tr></thead><tbody>';
    inv.forEach(invoice => {
        const key = String(invoice.status || '').toLowerCase();
        html += '<tr>'
            + '<td><span class="inv-no">' + escHtml(invoice.invoice_number) + '</span></td>'
            + '<td>' + escHtml(fmtDateTime(invoice.created_at)) + '</td>'
            + '<td><span class="stat stat-' + escHtml(key) + '">' + escHtml(admStatusLabel(invoice.status)) + '</span></td>'
            + '<td class="num" style="font-weight:700;">' + admMoney(invoice.net_amount) + '</td>'
            + '</tr>';
    });
    html += '</tbody></table>';
    box.innerHTML = html;
}

function admMoney(v) {
    const n = Number(v) || 0;
    return 'GHS ' + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/* ============ ADD BILLING CHARGE ============ */
function updateChargeTotal() {
    const qty = Math.max(parseInt(document.getElementById('adm-charge-qty').value, 10) || 0, 0);
    const price = parseFloat(document.getElementById('adm-charge-price').value) || 0;
    document.getElementById('adm-charge-total-value').textContent = admMoney(qty * price);
}

function openChargeModal() {
    if (!dischargeTarget) return;
    const openInvoices = admBilling.invoices.filter(i => i.status !== 'paid' && i.status !== 'cancelled');
    if (!openInvoices.length) {
        showAlert('This patient has no open invoice — raise an invoice before adding charges.', 'error');
        return;
    }
    document.getElementById('adm-charge-category').value = '';
    document.getElementById('adm-charge-desc').value = '';
    document.getElementById('adm-charge-qty').value = '1';
    document.getElementById('adm-charge-price').value = '';
    updateChargeTotal();
    document.getElementById('adm-charge-modal').classList.add('show');
}

function closeChargeModal() {
    document.getElementById('adm-charge-modal').classList.remove('show');
}

async function saveCharge() {
    if (!dischargeTarget) return;
    const invoiceId = document.getElementById('adm-charge-invoice').value;
    const category = document.getElementById('adm-charge-category').value;
    const description = document.getElementById('adm-charge-desc').value.trim();
    const quantity = Math.max(parseInt(document.getElementById('adm-charge-qty').value, 10) || 0, 1);
    const unitPrice = parseFloat(document.getElementById('adm-charge-price').value) || 0;

    if (!invoiceId) { showAlert('Select the invoice to add this charge to.', 'error'); return; }
    if (!category) { showAlert('Select a billing category.', 'error'); return; }
    if (!description) { showAlert('Enter a description for the charge.', 'error'); return; }
    if (!(unitPrice > 0)) { showAlert('Enter a unit price greater than zero.', 'error'); return; }

    const parts = category.split('|');
    const saveBtn = document.getElementById('save-adm-charge');
    saveBtn.disabled = true;
    try {
        const res = await fetch('/hms/backend/api/invoices.php?action=add_item&invoice_id=' + invoiceId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                item_type: parts[0],
                description: description,
                quantity: quantity,
                unit_price: unitPrice
            })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not add the charge');

        closeChargeModal();
        showAlert('Charge added — invoice total is now ' + admMoney(data.net_amount), 'success');
        await loadAdmissionBilling();
    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        saveBtn.disabled = false;
    }
}

async function submitDischarge() {
    if (!dischargeTarget) return;

    const outcome = document.getElementById('discharge-outcome').value;
    const finalDx = document.getElementById('discharge-final-dx').value.trim();
    const followUp = document.getElementById('discharge-followup').value;

    if (!outcome) { showAlert('Select the discharge outcome.', 'error'); return; }
    if (!finalDx) { showAlert('Enter the final diagnosis.', 'error'); return; }

    // Last confirmation when a real balance is still outstanding.
    if (Number(admBilling.outstanding) > 0) {
        const proceed = window.confirm(
            'This patient still has ' + admMoney(admBilling.outstanding) + ' outstanding. '
            + 'Discharge anyway? The balance remains on the account.'
        );
        if (!proceed) return;
    }

    const confirmBtn = document.getElementById('confirm-discharge');
    confirmBtn.disabled = true;
    try {
        const response = await fetch('/hms/backend/api/admissions.php?action=discharge&id=' + dischargeTarget.id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                discharge_notes: document.getElementById('discharge-notes').value,
                discharge_outcome: outcome,
                final_diagnosis: finalDx,
                follow_up_date: followUp
            })
        });
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Discharge failed');

        showAlert('Patient discharged — bed released', 'success');
        closeDischargeModal();
        await loadAdmissions();
        await loadWardOccupancy();
    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        confirmBtn.disabled = false;
    }
}

/* ==================== WARD OCCUPANCY ====================
   Every figure comes from the beds table, so the overview reflects real
   occupancy rather than each ward's declared capacity. */
async function loadWardOccupancy() {
    const grid = document.getElementById('ward-grid');
    if (!grid) return;
    try {
        const res = await fetch('/hms/backend/api/admissions.php?action=wards');
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not load ward occupancy');

        wardStats = data;
        renderWardTypeFilter();
        renderWards();
    } catch (error) {
        grid.innerHTML = '<div class="empty">Failed to load ward occupancy — ' + escHtml(error.message) + '</div>';
        document.getElementById('ward-stats').innerHTML = '';
    }
}

function renderWardTypeFilter() {
    const sel = document.getElementById('filter-ward-type');
    if (!sel || !wardStats) return;
    const types = (wardStats.wards || []).map(w => w.ward_type).filter(Boolean);
    const uniq = types.filter((t, i) => types.indexOf(t) === i).sort();
    const keep = sel.value;
    sel.innerHTML = '<option value="">All Ward Types</option>'
        + uniq.map(t => `<option value="${escHtml(t)}">${escHtml(t)}</option>`).join('');
    if (keep && uniq.indexOf(keep) !== -1) sel.value = keep;
}

function renderWards() {
    const grid = document.getElementById('ward-grid');
    const statsBox = document.getElementById('ward-stats');
    if (!grid || !wardStats) return;

    const all = wardStats.wards || [];
    const t = wardStats.totals || {};
    const filter = document.getElementById('filter-ward-type').value;
    const wards = filter ? all.filter(w => w.ward_type === filter) : all;

    // The totals always describe the whole hospital, so filtering the grid
    // never makes the headline figures disagree with the wards listed below.
    const stat = (label, value, sub, cls) =>
        `<div class="ws${cls ? ' ' + cls : ''}"><div class="lbl">${label}</div>`
        + `<div class="val">${value}</div><div class="sub">${sub}</div></div>`;
    statsBox.innerHTML =
        stat('Active Wards', all.length, all.length === 1 ? 'ward open' : 'wards open')
        + stat('Patients Admitted', t.patients || 0, 'currently in a bed', 'occ')
        + stat('Beds Occupied', t.beds_occupied || 0, 'of ' + (t.beds_total || 0) + ' beds', 'occ')
        + stat('Beds Free', t.beds_free || 0, 'ready for admission', 'free');

    if (!wards.length) {
        grid.innerHTML = all.length
            ? '<div class="empty">No ward matches this ward type.</div>'
            : '<div class="empty">No active wards have been set up yet.</div>';
        return;
    }

    grid.innerHTML = wards.map(w => {
        const occupied = w.beds_occupied;
        const total = w.beds_total;
        const pct = w.occupancy_pct || 0;
        const full = total > 0 && occupied >= total;
        const barW = Math.min(pct, 100);
        const tags = [];
        if (w.ward_type) tags.push('<span class="tag">' + escHtml(w.ward_type) + '</span>');
        if (w.floor_level) tags.push('<span class="tag">Floor ' + escHtml(w.floor_level) + '</span>');
        if (full) tags.push('<span class="tag hot">Full</span>');
        if (w.beds_reserved) tags.push('<span class="tag">' + w.beds_reserved + ' reserved</span>');
        if (w.beds_maintenance) tags.push('<span class="tag">' + w.beds_maintenance + ' out of service</span>');

        return `<div class="wcard">
            <div>
                <div class="nm">${escHtml(w.ward_name || '-')}</div>
                <div class="cd">${escHtml(w.ward_code || '-')} &middot; declared capacity ${w.capacity}</div>
                <div class="tags">${tags.join('')}</div>
            </div>
            <div class="bar"><i class="${full ? 'full' : ''}" style="width:${barW}%"></i></div>
            <div class="figs">
                <div>Occupied<b class="${full ? 'warn' : ''}">${occupied}</b></div>
                <div>Free<b>${w.beds_free}</b></div>
                <div>Admitted<b>${w.patients_admitted}</b></div>
            </div>
            ${total === 0 ? '<div id="ward-no-beds">No beds have been created for this ward yet.</div>' : ''}
        </div>`;
    }).join('');
}

/* ==================== TRANSFER WARD & BED ==================== */
function openTransferModal(id) {
    const admission = admissionsData.find(a => a.id === id);
    if (!admission || admission.status !== 'Admitted') return;

    transferTarget = admission;
    document.getElementById('transfer-current').innerHTML =
        `<div><b>PATIENT:</b> ${escHtml(admission.patient_name || '-')} (${escHtml(admission.hospital_number || '-')})</div>
         <div><b>ADMISSION:</b> ${escHtml(admission.admission_code || '-')} &middot; admitted ${fmtDateTime(admission.admission_date)}</div>
         <div><b>CURRENT WARD / BED:</b> ${escHtml(admission.ward_name || '-')} &middot; ${escHtml(admission.bed_number || '-')}</div>`;

    document.getElementById('transfer-reason').value = '';
    document.getElementById('transfer-move-text').textContent = 'Select the destination ward and bed.';

    // The current ward is hidden: a transfer to the bed the patient is already
    // in is rejected, and offering it only invites a dead end.
    const sel = document.getElementById('transfer-ward');
    const other = wardsList.filter(w => String(w.id) !== String(admission.ward_id));
    sel.innerHTML = '<option value="">-- Select Ward --</option>'
        + other.map(w => `<option value="${w.id}">${escHtml(w.ward_name)}</option>`).join('');

    document.getElementById('transfer-bed').innerHTML = '<option value="">Select Ward first</option>';
    document.getElementById('transfer-modal').classList.add('show');
    loadTransferHistory();
}

function closeTransferModal() {
    document.getElementById('transfer-modal').classList.remove('show');
    transferTarget = null;
}

async function loadTransferBeds(wardId) {
    const bedSel = document.getElementById('transfer-bed');
    const moveText = document.getElementById('transfer-move-text');
    if (!wardId) {
        bedSel.innerHTML = '<option value="">Select Ward first</option>';
        moveText.textContent = 'Select the destination ward and bed.';
        return;
    }
    bedSel.innerHTML = '<option value="">Loading beds...</option>';
    moveText.textContent = 'Loading available beds...';
    try {
        const res = await fetch('/hms/backend/api/admissions.php?action=available_beds&ward_id=' + encodeURIComponent(wardId));
        const data = await res.json();
        const beds = (data.success && data.beds) ? data.beds : [];
        if (!beds.length) {
            bedSel.innerHTML = '<option value="">No free beds in this ward</option>';
            moveText.textContent = 'This ward has no free bed — pick another ward.';
            return;
        }
        bedSel.innerHTML = beds.map(b =>
            `<option value="${b.id}">${escHtml(b.bed_number)}${b.bed_type ? ' (' + escHtml(b.bed_type) + ')' : ''}</option>`).join('');
        moveText.textContent = beds.length === 1
            ? '1 free bed available.'
            : beds.length + ' free beds available.';
    } catch (error) {
        bedSel.innerHTML = '<option value="">Failed to load beds</option>';
        moveText.textContent = 'Could not load the beds for this ward.';
    }
}

async function loadTransferHistory() {
    const body = document.getElementById('transfer-history-body');
    const count = document.getElementById('transfer-history-count');
    if (!transferTarget) return;
    body.innerHTML = '<div class="empty">Loading movement history...</div>';
    try {
        const res = await fetch('/hms/backend/api/admissions.php?action=transfers&id=' + transferTarget.id);
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not load the history');

        const rows = data.transfers || [];
        count.textContent = rows.length
            ? (rows.length === 1 ? '1 move recorded' : rows.length + ' moves recorded')
            : 'no moves yet';

        if (!rows.length) {
            body.innerHTML = '<div class="empty">This patient has not been moved since admission.</div>';
            return;
        }
        body.innerHTML = '<table><thead><tr>'
            + '<th>From</th><th>To</th><th>When</th><th>Reason</th>'
            + '</tr></thead><tbody>'
            + rows.map(t => `<tr>
                <td class="mv">${escHtml(t.from_ward || '-')}<br><span style="color:#64748B;font-weight:400;">${escHtml(t.from_bed || '-')}</span></td>
                <td class="mv">${escHtml(t.to_ward || '-')}<br><span style="color:#64748B;font-weight:400;">${escHtml(t.to_bed || '-')}</span></td>
                <td>${fmtDateTime(t.created_at)}<br><span style="color:#64748B;">${escHtml(t.moved_by_name || 'Unknown')}</span></td>
                <td class="why">${t.reason ? escHtml(t.reason) : '—'}</td>
            </tr>`).join('')
            + '</tbody></table>';
    } catch (error) {
        body.innerHTML = '<div class="empty">Could not load the history — ' + escHtml(error.message) + '</div>';
    }
}

async function submitTransfer() {
    if (!transferTarget) return;

    const bedId = document.getElementById('transfer-bed').value;
    const reason = document.getElementById('transfer-reason').value.trim();

    if (!bedId) { showAlert('Select the bed to transfer the patient to.', 'error'); return; }

    const btn = document.getElementById('confirm-transfer');
    btn.disabled = true;
    try {
        const res = await fetch('/hms/backend/api/admissions.php?action=transfer&id=' + transferTarget.id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ bed_id: bedId, reason: reason })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Transfer failed');

        showAlert('Patient transferred to ' + data.ward_name + ' / ' + data.bed_number, 'success');
        closeTransferModal();
        await loadAdmissions();
        await loadWardOccupancy();
    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        btn.disabled = false;
    }
}

/* ===================== DRAFT ADMISSIONS ===================== */
let draftsData = [];
let draftEditingId = null;
let draftSearchTimer = null;

function setupDraftListeners() {
    document.getElementById('new-draft-btn').addEventListener('click', () => openDraftModal());
    document.getElementById('close-draft-modal').addEventListener('click', closeDraftModal);
    document.getElementById('cancel-draft').addEventListener('click', closeDraftModal);
    document.getElementById('save-draft').addEventListener('click', submitDraft);

    const statusSel = document.getElementById('filter-draft-status');
    if (statusSel) statusSel.addEventListener('change', loadDrafts);

    const search = document.getElementById('filter-draft-q');
    if (search) {
        search.addEventListener('input', () => {
            clearTimeout(draftSearchTimer);
            draftSearchTimer = setTimeout(loadDrafts, 300);
        });
    }

    document.getElementById('refresh-drafts-btn').addEventListener('click', loadDrafts);

    const wardSel = document.getElementById('draft-ward');
    if (wardSel) wardSel.addEventListener('change', loadDraftBeds);

    const patientInput = document.getElementById('draft-patient');
    if (patientInput) {
        patientInput.addEventListener('input', () => {
            clearTimeout(draftSearchTimer);
            draftSearchTimer = setTimeout(searchDraftPatients, 250);
        });
    }
}

async function loadDrafts() {
    const tbody = document.getElementById('drafts-table');
    const status = document.getElementById('filter-draft-status').value;
    const q = document.getElementById('filter-draft-q').value.trim();

    const params = new URLSearchParams();
    if (status) params.set('status', status);
    if (q) params.set('q', q);

    try {
        const response = await fetch('/hms/backend/api/draft_admissions.php?' + params.toString());
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load draft admissions');
        draftsData = data.drafts || [];
        renderDrafts(tbody);
    } catch (error) {
        console.error('Draft admissions load error:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Failed to load draft admissions</td></tr>';
    }
}

function renderDrafts(tbody) {
    if (!tbody) return;
    if (!draftsData.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No draft admissions found</td></tr>';
        return;
    }
    tbody.innerHTML = draftsData.map(d => {
        const isPending = d.status === 'DRAFT';
        const tagClass = isPending ? 'pending' : (d.status === 'FINALIZED' ? 'finalized' : 'cancelled');
        const wardBed = d.ward_name
            ? escHtml(d.ward_name) + ' &middot; Bed ' + escHtml(d.bed_number || '-')
            : '<span style="color:#8A5A00;">No ward / bed chosen yet</span>';

        let actions;
        if (isPending) {
            actions = '<button class="fin" data-draft-finalize="' + d.id + '" title="Create the real admission and occupy the bed"><i class="fa-solid fa-check"></i> Finalize</button>'
                    + '<button class="ed" data-draft-edit="' + d.id + '"><i class="fa-solid fa-pen"></i> Edit</button>'
                    + '<button class="del" data-draft-cancel="' + d.id + '"><i class="fa-solid fa-trash"></i> Discard</button>';
        } else if (d.status === 'FINALIZED' && d.finalized_admission_code) {
            actions = '<span class="draft-tag finalized">Admitted as ' + escHtml(d.finalized_admission_code) + '</span>';
        } else {
            actions = '<span style="color:#95A5A6;">&mdash;</span>';
        }

        return '<tr>'
            + '<td><span class="dr-code">' + escHtml(d.draft_number) + '</span><br><span class="draft-tag ' + tagClass + '">' + escHtml(d.status) + '</span></td>'
            + '<td><strong>' + escHtml(d.patient_name || '-') + '</strong></td>'
            + '<td>' + escHtml(d.hospital_number || '-') + '</td>'
            + '<td>' + wardBed + '</td>'
            + '<td>' + (d.admission_date ? fmtDateTime(d.admission_date) : '<span style="color:#95A5A6;">&mdash;</span>') + '</td>'
            + '<td>' + escHtml(d.admitting_doctor || '-') + '</td>'
            + '<td><div class="dr-actions">' + actions + '</div></td>'
            + '</tr>';
    }).join('');
}

// Delegated clicks on the draft table, so re-rendering keeps the handlers live.
document.addEventListener('click', function (e) {
    const finalizeBtn = e.target.closest('[data-draft-finalize]');
    if (finalizeBtn) { finalizeDraft(finalizeBtn.getAttribute('data-draft-finalize')); return; }

    const editBtn = e.target.closest('[data-draft-edit]');
    if (editBtn) { openDraftModal(editBtn.getAttribute('data-draft-edit')); return; }

    const cancelBtn = e.target.closest('[data-draft-cancel]');
    if (cancelBtn) { discardDraft(cancelBtn.getAttribute('data-draft-cancel')); }
});

function patientDisplayName(p) {
    return [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ').trim();
}

async function searchDraftPatients() {
    const input = document.getElementById('draft-patient');
    const results = document.getElementById('draft-patient-results');
    const term = input.value.trim();
    if (term.length < 2) { results.style.display = 'none'; return; }

    try {
        const response = await fetch('/hms/backend/api/patients.php?action=search&q=' + encodeURIComponent(term));
        const data = await response.json();
        const patients = data.patients || [];
        if (!patients.length) {
            results.innerHTML = '<div class="empty">No patient found</div>';
            results.style.display = 'block';
            return;
        }
        results.innerHTML = patients.map(p =>
            '<div data-draft-pick="' + p.id + '"><span class="hpno">' + escHtml(p.hospital_number)
            + '</span> &mdash; ' + escHtml(patientDisplayName(p)) + '</div>'
        ).join('');
        results.style.display = 'block';
        results.querySelectorAll('[data-draft-pick]').forEach(el => {
            el.addEventListener('click', () => {
                document.getElementById('draft-patient-id').value = el.getAttribute('data-draft-pick');
                input.value = el.textContent.trim();
                results.style.display = 'none';
            });
        });
    } catch (error) {
        console.error('Draft patient search error:', error);
    }
}

async function loadDraftBeds() {
    const wardId = document.getElementById('draft-ward').value;
    const bedSel = document.getElementById('draft-bed');
    if (!wardId) { bedSel.innerHTML = '<option value="">No bed selected (draft)</option>'; return; }

    try {
        const response = await fetch('/hms/backend/api/admissions.php?action=available_beds&ward_id=' + wardId);
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load beds');
        const beds = data.beds || [];
        bedSel.innerHTML = '<option value="">No bed selected (draft)</option>'
            + beds.map(b => '<option value="' + b.id + '">' + escHtml(b.bed_number)
                + (b.bed_type ? ' (' + escHtml(b.bed_type) + ')' : '') + '</option>').join('');
    } catch (error) {
        console.error('Draft beds load error:', error);
        bedSel.innerHTML = '<option value="">Could not load beds</option>';
    }
}

function openDraftModal(draftId) {
    document.getElementById('draft-form').reset();
    document.getElementById('draft-patient-results').style.display = 'none';
    document.getElementById('draft-current').style.display = 'none';
    document.getElementById('draft-date').value = nowLocal();

    document.getElementById('draft-ward').innerHTML = '<option value="">Select Ward</option>'
        + wardsList.map(w => '<option value="' + w.id + '">' + escHtml(w.ward_name) + '</option>').join('');
    document.getElementById('draft-bed').innerHTML = '<option value="">No bed selected (draft)</option>';

    if (draftId) {
        // Editing: the patient cannot be swapped on an existing draft, and the
        // saved values are loaded into the same form the create path uses.
        const draft = draftsData.find(d => String(d.id) === String(draftId));
        if (!draft) return;

        draftEditingId = draft.id;
        document.getElementById('draft-modal-title').textContent = 'Edit Draft Admission';
        document.getElementById('save-draft').innerHTML = '<i class="fa-solid fa-check"></i> Update Draft';

        document.getElementById('draft-current').style.display = 'flex';
        document.getElementById('draft-current-text').innerHTML = '<b>' + escHtml(draft.draft_number)
            + '</b> &middot; ' + escHtml(draft.patient_name || '') + ' (' + escHtml(draft.hospital_number || '') + ')';
        document.getElementById('draft-current-note').textContent = draft.bed_id ? '' : 'No bed chosen yet';

        document.getElementById('draft-patient-id').value = draft.patient_id;
        const patientInput = document.getElementById('draft-patient');
        patientInput.value = (draft.patient_name || '') + ' (' + (draft.hospital_number || '') + ')';
        patientInput.disabled = true;

        document.getElementById('draft-ward').value = draft.ward_id || '';
        document.getElementById('draft-date').value = (draft.admission_date || '').replace(' ', 'T').slice(0, 16);
        document.getElementById('draft-type').value = draft.admission_type || 'Routine';
        document.getElementById('draft-doctor').value = draft.admitting_doctor || '';
        document.getElementById('draft-diagnosis').value = draft.diagnosis || '';
        document.getElementById('draft-notes').value = draft.notes || '';

        loadDraftBeds().then(() => {
            if (!draft.bed_id) return;
            const bedSel = document.getElementById('draft-bed');
            // The draft's bed may since have been taken by an admitted patient,
            // so it is re-inserted as a labelled option rather than silently
            // dropped — the finalize step is what rejects a taken bed.
            if (!bedSel.querySelector('option[value="' + draft.bed_id + '"]')) {
                bedSel.insertAdjacentHTML('afterbegin', '<option value="' + draft.bed_id + '">'
                    + escHtml(draft.bed_number || 'Bed') + ' (current draft bed)</option>');
            }
            bedSel.value = draft.bed_id;
        });
    } else {
        draftEditingId = null;
        document.getElementById('draft-modal-title').textContent = 'Create Draft Admission';
        document.getElementById('save-draft').innerHTML = '<i class="fa-solid fa-check"></i> Save Draft';
        const patientInput = document.getElementById('draft-patient');
        patientInput.disabled = false;
        patientInput.value = '';
        document.getElementById('draft-patient-id').value = '';
    }

    document.getElementById('draft-modal').classList.add('show');
}

function closeDraftModal() {
    document.getElementById('draft-modal').classList.remove('show');
    draftEditingId = null;
}

async function submitDraft() {
    const patientId = document.getElementById('draft-patient-id').value;
    if (!patientId) { showAlert('Please select a patient first', 'error'); return; }

    const payload = {
        patient_id: patientId,
        ward_id: document.getElementById('draft-ward').value || null,
        bed_id: document.getElementById('draft-bed').value || null,
        admission_date: document.getElementById('draft-date').value || null,
        admission_type: document.getElementById('draft-type').value,
        admitting_doctor: document.getElementById('draft-doctor').value.trim(),
        diagnosis: document.getElementById('draft-diagnosis').value.trim(),
        notes: document.getElementById('draft-notes').value.trim()
    };

    const btn = document.getElementById('save-draft');
    btn.disabled = true;
    try {
        const action = draftEditingId ? 'update&id=' + draftEditingId : 'create';
        const res = await fetch('/hms/backend/api/draft_admissions.php?action=' + action, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not save the draft');

        showAlert(draftEditingId ? 'Draft admission updated' : 'Draft admission ' + data.draft_number + ' saved', 'success');
        closeDraftModal();
        await loadDrafts();
    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        btn.disabled = false;
    }
}

async function finalizeDraft(id) {
    const draft = draftsData.find(d => String(d.id) === String(id));
    if (!draft) return;

    if (!draft.ward_id || !draft.bed_id) {
        showAlert('Choose a ward and bed on this draft before finalizing', 'error');
        openDraftModal(id);
        return;
    }
    if (!confirm('Finalize draft ' + draft.draft_number + '?\n\nThis admits '
        + (draft.patient_name || 'the patient') + ' to ' + draft.ward_name + ' / Bed ' + draft.bed_number
        + ' and occupies the bed.')) return;

    try {
        const res = await fetch('/hms/backend/api/draft_admissions.php?action=finalize&id=' + id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not finalize the draft');

        showAlert('Draft finalized — admission ' + data.admission_code + ' created', 'success');
        await loadDrafts();
        await loadAdmissions();
        await loadWardOccupancy();
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

async function discardDraft(id) {
    const draft = draftsData.find(d => String(d.id) === String(id));
    if (!draft) return;
    if (!confirm('Discard draft ' + draft.draft_number + ' for ' + (draft.patient_name || 'this patient')
        + '?\n\nThis cannot be undone.')) return;

    try {
        const res = await fetch('/hms/backend/api/draft_admissions.php?action=cancel&id=' + id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not discard the draft');

        showAlert('Draft admission discarded', 'success');
        await loadDrafts();
    } catch (error) {
        showAlert(error.message, 'error');
    }
}
</script>