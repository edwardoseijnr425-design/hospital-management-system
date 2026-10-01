<style>
    /* ============ DOCTOR STATION : OPD/IPD notes & discharge summaries ============
       Scoped under #docstation. The shell supplies card/card-body/table/btn/
       modal/form-group, so only the station-specific pieces are defined here. */
    #docstation{--ds-red:#C0392B;--ds-ink:#0F2D59;--ds-mute:#64748B;--ds-line:#DCE4EC}
    #docstation .mb-2{margin-bottom:.5rem}
    #docstation .mb-3{margin-bottom:1rem}
    #docstation .mb-4{margin-bottom:1.5rem}
    #docstation .gap-2{gap:.5rem}
    #docstation .flex{display:flex}
    #docstation .wrap{flex-wrap:wrap}
    #docstation .items-center{align-items:center}
    #docstation .justify-between{justify-content:space-between}
    #docstation .text-muted{color:var(--ds-mute)}
    #docstation .fw-bold{font-weight:700}
    #docstation .text-uppercase{text-transform:uppercase}
    #docstation .small{font-size:12px}
    #docstation .container-fluid{padding:0}

    /* Red station banner, as in the pasted design */
    #ds-banner{background:var(--ds-red);color:#fff;border-radius:5px;padding:10px 14px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
    #ds-banner h5{margin:0;font-size:13px;font-weight:800;letter-spacing:.3px;text-transform:uppercase}
    #ds-banner .ds-sub{font-size:11px;opacity:.9;margin-top:2px}
    #ds-banner .ds-nav{background:#fff;color:var(--ds-red);border:none;border-radius:4px;padding:7px 13px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
    #ds-banner .ds-nav:hover{background:#F8D7D3}

    /* Stat strip: in-patients, notes written, summaries awaiting discharge */
    #ds-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:16px}
    #ds-stats .s{background:#fff;border:1px solid var(--ds-line);border-left:4px solid var(--ds-red);border-radius:5px;padding:10px 12px}
    #ds-stats .s .lbl{font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--ds-mute)}
    #ds-stats .s .val{font-size:21px;font-weight:800;color:var(--ds-ink);line-height:1.2;margin-top:3px}
    #ds-stats .s .sub{font-size:10.5px;color:var(--ds-mute)}
    #ds-stats .s.notes{border-left-color:#0b5fa5}
    #ds-stats .s.sum{border-left-color:#B9770E}
    #ds-stats .s.dis{border-left-color:#1E7A34}

    #ds-search{width:280px}
    #ds-table td{vertical-align:middle}
    #ds-table .ds-code{font-family:Consolas,'Courier New',monospace;font-weight:700;color:var(--ds-ink);font-size:11px}
    #ds-table .ds-name{font-weight:700;color:#1E293B}
    #ds-table .ds-note-count{font-size:10.5px;color:var(--ds-mute);margin-top:2px}
    #ds-table .ds-actions{display:flex;gap:5px;flex-wrap:wrap}
    #ds-table .ds-actions button{font-size:10px;padding:4px 8px;border-radius:3px;border:1px solid #CBD5E1;background:#fff;color:#334155;cursor:pointer;font-family:inherit;font-weight:600;display:inline-flex;align-items:center;gap:4px}
    #ds-table .ds-actions button.primary{background:var(--ds-red);border-color:var(--ds-red);color:#fff}
    #ds-table .ds-actions button.primary:hover{background:#96281B}
    #ds-table .ds-actions button:hover{background:#F1F5F9}
    #ds-table .ds-actions button.primary:hover{background:#96281B}
    #ds-table .ds-actions button.view:hover{background:#E7F3FC}
    #ds-table .ds-actions button.consult{background:#1E7A34;border-color:#1E7A34;color:#fff}
    #ds-table .ds-actions button.consult:hover{background:#16602a}
    #ds-empty{padding:22px;text-align:center;color:#8A94A6;font-size:12px}

    /* Entry modal */
    #ds-note-modal .modal-content{max-width:720px !important}
    #ds-note-modal .ds-head{background:var(--ds-red);color:#fff;margin:-16px -16px 16px;padding:12px 16px;border-radius:4px 4px 0 0}
    #ds-note-modal .ds-head h3{margin:0;font-size:14px;font-weight:800;letter-spacing:.3px}
    #ds-note-modal .ds-head .ds-head-sub{font-size:11.5px;opacity:.92;margin-top:3px}
    #ds-note-modal label{display:block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#64748B;margin-bottom:5px}
    #ds-note-modal select,#ds-note-modal textarea{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;font-family:inherit;background:#fff;color:#222;box-sizing:border-box}
    #ds-note-modal textarea{resize:vertical;min-height:130px;line-height:1.5}
    #ds-note-modal select:focus,#ds-note-modal textarea:focus{outline:none;border-color:var(--ds-red);box-shadow:0 0 4px rgba(192,57,43,.22)}
    #ds-note-modal .ds-type-hint{font-size:10.5px;color:#8A94A6;margin-top:5px}
    #ds-note-modal .ds-patient-bar{display:flex;justify-content:space-between;align-items:center;gap:10px;background:#F1F5F9;border:1px solid #C9D4E0;border-radius:4px;padding:9px 12px;margin-bottom:14px;font-size:12px;color:#334155;flex-wrap:wrap}
    #ds-note-modal .ds-patient-bar b{color:#0b5fa5}
    #ds-note-modal .ds-warn{background:#FEF5E0;border:1px solid #F0AD4E;border-left:4px solid #f0ad4e;color:#8A5A00;border-radius:4px;padding:9px 11px;font-size:11.5px;margin-bottom:14px;display:none}
    #ds-note-modal .ds-warn.show{display:block}
    #ds-note-modal .ds-summary-only{display:none}
    #ds-note-modal .ds-summary-only.show{display:block}
    #ds-note-modal .ds-foot-note{font-size:10.5px;color:#8A94A6;margin-top:6px}

    /* Notes history drawer inside the modal */
    #ds-history{border:1px solid var(--ds-line);border-radius:5px;overflow:hidden;margin-top:14px}
    #ds-history .hd{background:#E6EEF5;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#222;padding:7px 9px;border-bottom:2px solid #b2c8de;display:flex;justify-content:space-between;gap:8px;align-items:center}
    #ds-history .bd{max-height:200px;overflow-y:auto}
    #ds-history table{width:100%;border-collapse:collapse;font-size:11px}
    #ds-history th{background:#F1F5F9;font-size:9.5px;text-transform:uppercase;letter-spacing:.3px;color:#475569;padding:5px 8px;text-align:left;border-bottom:1px solid #E1E8F0;position:sticky;top:0}
    #ds-history td{padding:6px 8px;border-bottom:1px solid #EEF2F7;vertical-align:top}
    #ds-history .nt{font-weight:700;font-size:9.5px;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
    #ds-history .nt.opd{color:#0b5fa5}
    #ds-history .nt.ipd{color:#1E7A34}
    #ds-history .nt.dsum{color:#B9770E}
    #ds-history .txt{color:#334155;white-space:pre-wrap;line-height:1.45;max-height:96px;overflow-y:auto}
    #ds-history .meta{font-size:10px;color:#8A94A6;margin-top:3px;white-space:nowrap}
    #ds-history .rm{background:#fff;border:1px solid #F0B7B2;color:#C0392B;border-radius:3px;font-size:9.5px;font-weight:700;padding:3px 6px;cursor:pointer;font-family:inherit;white-space:nowrap}
    #ds-history .rm:hover{background:#FDECEA}
    #ds-history .empty{padding:14px;text-align:center;color:#8A94A6}

    /* ---------- Inline consultation form (expanded under a patient row) ---------- */
    #ds-expand > td{padding:0 !important;border-top:none}
    .ds-form{background:#F8FAFC;border-top:3px solid var(--ds-red);padding:16px 18px}

    /* Patient identity strip */
    .ds-ident{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;background:#fff;border:1px solid var(--ds-line);border-radius:6px;padding:14px 16px;margin-bottom:14px}
    .ds-ident .who{display:flex;align-items:center;gap:12px}
    .ds-ident .avatar{width:52px;height:52px;border-radius:50%;background:#FDECEA;border:2px solid #F0B7B2;color:var(--ds-red);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:17px;flex-shrink:0}
    .ds-ident .nm{font-weight:800;font-size:16px;color:#1E293B;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .ds-ident .hn{background:#E7F3FC;border:1px solid #A9CDE8;color:#0b5fa5;font-family:Consolas,'Courier New',monospace;font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:3px}
    .ds-ident .facts{font-size:11.5px;color:#64748B;margin-top:4px;line-height:1.6}
    .ds-ident .facts b{color:#334155}
    .ds-alert{display:inline-flex;align-items:center;gap:5px;background:#FEF5E0;border:1px solid #F0AD4E;color:#8A5A00;font-size:10.5px;font-weight:700;padding:5px 10px;border-radius:4px}
    .ds-stay{display:inline-flex;align-items:center;gap:5px;background:#E8F5EC;border:1px solid #9CD3AC;color:#1E7A34;font-size:10.5px;font-weight:700;padding:5px 10px;border-radius:4px}

    .ds-form .cols{display:grid;grid-template-columns:1fr 1.7fr;gap:14px}
    .ds-form .col{display:flex;flex-direction:column;gap:14px;min-width:0}
    .ds-box{background:#fff;border:1px solid var(--ds-line);border-radius:6px;padding:13px 15px}
    .ds-box > h4{margin:0 0 10px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--ds-red);display:flex;align-items:center;gap:6px}
    .ds-box > h4 .hint{margin-left:auto;font-weight:600;color:#94A3B8;letter-spacing:0;text-transform:none;font-size:10px}

    .ds-form label{display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#64748B;margin-bottom:4px}
    .ds-form input[type=text],.ds-form input[type=number],.ds-form input[type=datetime-local],.ds-form select,.ds-form textarea{width:100%;padding:7px 9px;border:1px solid #C9D4E0;border-radius:4px;font-size:12px;font-family:inherit;background:#fff;color:#1E293B;box-sizing:border-box}
    .ds-form textarea{resize:vertical;min-height:74px;line-height:1.5}
    .ds-form input:focus,.ds-form select:focus,.ds-form textarea:focus{outline:none;border-color:var(--ds-red);box-shadow:0 0 4px rgba(192,57,43,.18)}
    .ds-form .fld{margin-bottom:10px}
    .ds-form .fld:last-child{margin-bottom:0}
    .ds-form .two{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .ds-form .three{display:grid;grid-template-columns:2fr 1fr;gap:10px}

    /* Vitals tiles */
    .ds-vitals{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}
    .ds-vitals .v{background:#F8FAFC;border:1px solid var(--ds-line);border-radius:4px;padding:7px 9px}
    .ds-vitals .v .k{font-size:9.5px;color:#94A3B8;display:block;text-transform:uppercase;letter-spacing:.3px}
    .ds-vitals .v .n{font-size:13.5px;font-weight:800;color:#1E293B}
    .ds-vitals .v.none .n{color:#B6BECB;font-weight:600;font-size:11.5px}
    .ds-vitals .when{font-size:10px;color:#94A3B8;margin-top:8px;border-top:1px dashed var(--ds-line);padding-top:7px}
    .ds-vitals .flagged{color:#B9770E;font-size:10px;margin-top:6px;line-height:1.45}

    /* History list */
    .ds-hist{max-height:168px;overflow-y:auto;border:1px solid #EDF1F5;border-radius:4px}
    .ds-hist .h{padding:7px 9px;border-bottom:1px solid #F1F5F9;font-size:11px;color:#475569}
    .ds-hist .h:last-child{border-bottom:none}
    .ds-hist .h .w{font-weight:700;color:#1E293B}
    .ds-hist .h .w span{font-weight:600;color:#64748B}
    .ds-hist .h .txt{white-space:pre-wrap;line-height:1.45;margin-top:2px}
    .ds-hist .none{padding:12px;text-align:center;color:#94A3B8;font-size:11px}

    /* Allergy input carries the standing-allergy warning */
    .ds-allergy-wrap{position:relative}
    .ds-allergy-wrap input{border-color:#F0AD4E;background:#FFFDF7}
    .ds-allergy-note{font-size:10px;color:#8A5A00;margin-top:4px}

    /* Investigation checkboxes */
    .ds-inv{display:grid;grid-template-columns:repeat(2,1fr);gap:7px}
    .ds-inv label{display:flex;align-items:center;gap:7px;background:#F8FAFC;border:1px solid var(--ds-line);border-radius:4px;padding:7px 9px;font-size:11.5px;color:#334155;text-transform:none;letter-spacing:0;font-weight:600;margin:0;cursor:pointer}
    .ds-inv label:hover{background:#F1F5F9}
    .ds-inv label:has(input:checked){background:#E7F3FC;border-color:#A9CDE8;color:#0b5fa5}
    .ds-inv .kind{display:inline-block;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;padding:1px 5px;border-radius:2px;background:#E2E8F0;color:#475569;margin-left:auto}
    .ds-inv label:has(input:checked) .kind{background:#0b5fa5;color:#fff}

    /* Drug picker + prescription lines */
    .ds-rx-add{display:flex;gap:7px}
    .ds-rx-add input{flex:1}
    .ds-rx-add button{flex-shrink:0;background:var(--ds-red);border:1px solid var(--ds-red);color:#fff;border-radius:4px;padding:0 13px;font-size:11px;font-weight:700;cursor:pointer;font-family:inherit}
    .ds-rx-add button:hover{background:#96281B}
    .ds-rx-hint{font-size:10px;color:#94A3B8;margin-top:5px}
    .ds-rx-lines{margin-top:10px;display:flex;flex-direction:column;gap:6px}
    .ds-rx-line{border:1px solid var(--ds-line);border-radius:4px;padding:8px 9px;background:#F8FAFC}
    .ds-rx-line .top{display:flex;align-items:center;gap:8px}
    .ds-rx-line .top b{font-size:12px;color:#1E293B}
    .ds-rx-line .top .gen{font-size:10.5px;color:#64748B}
    .ds-rx-line .top .rm{margin-left:auto;background:#fff;border:1px solid #F0B7B2;color:#C0392B;border-radius:3px;font-size:9.5px;font-weight:700;padding:3px 7px;cursor:pointer;font-family:inherit}
    .ds-rx-line .top .rm:hover{background:#FDECEA}
    .ds-rx-line .flds{display:grid;grid-template-columns:repeat(4,1fr);gap:7px;margin-top:7px}
    .ds-rx-line .flds input{padding:5px 7px;font-size:11px}
    .ds-rx-line .flds label{font-size:9px;margin-bottom:3px}
    .ds-rx-empty{font-size:11px;color:#94A3B8;text-align:center;padding:10px;border:1px dashed var(--ds-line);border-radius:4px}
    .ds-pending{margin-top:10px;border-top:1px dashed var(--ds-line);padding-top:8px}
    .ds-pending .pd{font-size:10.5px;color:#475569;line-height:1.6}
    .ds-pending .pd b{color:#1E293B}

    /* Admit toggle */
    .ds-admit{border:1px solid var(--ds-line);border-radius:5px;padding:11px 13px;background:#fff}
    .ds-admit label.chk{display:flex;align-items:center;gap:8px;font-size:12px;color:#334155;text-transform:none;letter-spacing:0;font-weight:700;margin:0 0 9px;cursor:pointer}
    .ds-admit .wardgrid{display:grid;grid-template-columns:1fr 1fr;gap:9px}
    .ds-admit.is-admitted{background:#F0FDF4;border-color:#9CD3AC}
    .ds-admit .note{font-size:10.5px;color:#1E7A34;margin-top:8px;line-height:1.5}

    /* Action bar */
    .ds-actions-bar{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:14px;padding-top:13px;border-top:1px solid var(--ds-line);flex-wrap:wrap}
    .ds-actions-bar .note{font-size:10.5px;color:#8A94A6}
    .ds-actions-bar .btns{display:flex;gap:8px;flex-wrap:wrap}
    .ds-btn{border-radius:4px;padding:8px 15px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;border:1px solid transparent}
    .ds-btn.grey{background:#E2E8F0;border-color:#CBD5E1;color:#475569}
    .ds-btn.grey:hover{background:#CBD5E1}
    .ds-btn.blue{background:#E7F3FC;border-color:#A9CDE8;color:#0b5fa5}
    .ds-btn.blue:hover{background:#D3E8F8}
    .ds-btn.red{background:var(--ds-red);border-color:var(--ds-red);color:#fff}
    .ds-btn.red:hover{background:#96281B}
    .ds-btn:disabled{opacity:.55;cursor:not-allowed}
    .ds-saving{display:none;font-size:10.5px;color:#64748B;align-items:center;gap:6px}
    .ds-saving.show{display:inline-flex}

    /* Print: show only the consultation summary, hide the whole station. */
    #ds-print-area{display:none}
    @media print{
        body *{visibility:hidden}
        #ds-print-area,#ds-print-area *{visibility:visible}
        #ds-print-area{display:block;position:absolute;left:0;top:0;width:100%;padding:16px;font-size:11.5pt;color:#000}
        #ds-print-area h1{font-size:15pt;margin:0 0 2px}
        #ds-print-area .sub{font-size:9.5pt;color:#444;margin-bottom:12px}
        #ds-print-area h2{font-size:10pt;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #999;padding-bottom:3px;margin:14px 0 6px}
        #ds-print-area .kv{width:100%;border-collapse:collapse}
        #ds-print-area .kv td{padding:3px 6px;border-bottom:1px dotted #ccc;vertical-align:top}
        #ds-print-area .kv td:first-child{width:180px;font-weight:bold;color:#333}
        #ds-print-area .pre{white-space:pre-wrap;line-height:1.5}
        #ds-print-area ul{margin:4px 0;padding-left:20px}
    }

    @media (max-width:900px){
        .ds-form .cols{grid-template-columns:1fr}
        .ds-form .three{grid-template-columns:1fr}
        .ds-rx-line .flds{grid-template-columns:1fr 1fr}
    }
    @media (max-width:640px){
        #ds-banner{flex-direction:column;align-items:flex-start}
        #ds-search{width:100%}
    }
</style>

<div id="docstation" class="container-fluid">
    <!-- Station banner -->
    <div id="ds-banner">
        <div>
            <h5><i class="fa-solid fa-user-doctor"></i> Doctor Station &mdash; OPD/IPD Notes &amp; Discharge Summaries</h5>
            <div class="ds-sub">Clinical entries are written against the patient's stay and appear on the discharge record.</div>
        </div>
        <button type="button" class="ds-nav" id="ds-back-to-admissions"><i class="fa-solid fa-arrow-left"></i> Admissions</button>
    </div>

    <!-- Stats -->
    <div id="ds-stats">
        <div class="s"><div class="lbl">In-Patients</div><div class="val" id="ds-stat-inpatients">&mdash;</div><div class="sub">currently admitted</div></div>
        <div class="s notes"><div class="lbl">Round Notes Written</div><div class="val" id="ds-stat-notes">&mdash;</div><div class="sub">IPD daily notes</div></div>
        <div class="s sum"><div class="lbl">Summaries Awaiting</div><div class="val" id="ds-stat-summaries">&mdash;</div><div class="sub">no discharge summary yet</div></div>
        <div class="s dis"><div class="lbl">Stays With Summary</div><div class="val" id="ds-stat-done">&mdash;</div><div class="sub">ready for discharge</div></div>
    </div>

    <!-- In-patient list -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-stethoscope"></i> Current In-Patients &mdash; Clinical Entries</h2>
            <div class="flex items-center gap-2 wrap">
                <input type="text" id="ds-search" class="form-control" placeholder="Search patient, hospital no., ward or bed..." style="padding:6px 9px;border:1px solid #C9D4E0;border-radius:4px;font-size:12px;font-family:inherit;background:#fff;">
                <select id="ds-filter-entry" style="padding:6px 9px;border:1px solid #C9D4E0;border-radius:4px;font-size:12px;font-family:inherit;background:#fff;">
                    <option value="ALL">All Entries</option>
                    <option value="OPD_NOTE">OPD Consultation Note</option>
                    <option value="IPD_NOTE">IPD Daily Round Note</option>
                    <option value="DISCHARGE_SUMMARY">Discharge Summary</option>
                </select>
                <button class="btn btn-secondary btn-sm" id="ds-refresh-btn"><i class="fa-solid fa-rotate"></i> Refresh</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table id="ds-table">
                    <thead>
                        <tr>
                            <th>Hospital No.</th>
                            <th>Patient</th>
                            <th>Ward / Bed</th>
                            <th>Admitted</th>
                            <th>Diagnosis</th>
                            <th>Clinical Record</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ds-table-body">
                        <tr><td colspan="7" style="text-align:center;">Loading in-patients...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Clinical Entry Modal -->
<div class="modal" id="ds-note-modal">
    <div class="modal-content">
        <div class="ds-head">
            <h3>CLINICAL ENTRY</h3>
            <div class="ds-head-sub" id="ds-note-patient">&mdash;</div>
        </div>
        <div class="modal-body">
            <div class="ds-patient-bar">
                <span id="ds-bar-admission">&mdash;</span>
                <span id="ds-bar-bed">&mdash;</span>
            </div>

            <div class="ds-warn" id="ds-summary-warn">
                <i class="fa-solid fa-triangle-exclamation"></i>
                A discharge summary already exists for this stay. Saving another entry of this type is blocked &mdash;
                the patient still has to be discharged from the Admissions page.
            </div>

            <div class="form-group mb-3">
                <label for="ds-note-type">ENTRY TYPE *</label>
                <select id="ds-note-type">
                    <option value="OPD_NOTE">OPD Consultation Note</option>
                    <option value="IPD_NOTE" selected>IPD Daily Clinical Round Note</option>
                    <option value="DISCHARGE_SUMMARY">Discharge Summary</option>
                </select>
                <div class="ds-type-hint" id="ds-type-hint">Daily progress note for this admission. Appears on the patient's clinical record.</div>
            </div>

            <div class="form-group mb-2">
                <label for="ds-note-text">CLINICAL NOTES / RECOMMENDATIONS *</label>
                <textarea id="ds-note-text" placeholder="Enter clinical observations, diagnosis, treatment plan, or discharge summary..."></textarea>
                <div class="ds-foot-note">Saved against admission <span id="ds-foot-code">—</span> under your name, with a timestamp.</div>
            </div>

            <div id="ds-history">
                <div class="hd">
                    <span>CLINICAL RECORD FOR THIS STAY</span>
                    <span id="ds-history-count">0 entries</span>
                </div>
                <div class="bd" id="ds-history-body">
                    <div class="empty">Loading...</div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="ds-cancel-note">Close</button>
            <button class="btn btn-primary" id="ds-save-note"><i class="fa-solid fa-check"></i> Save Clinical Entry</button>
        </div>
    </div>
</div>

<!-- Off-screen summary used only for printing a consultation; hidden on screen. -->
<div id="ds-print-area"></div>

<script>
let dsInpatients = [];
let dsNotes = [];
let dsTarget = null;
let dsSearchTimer = null;

const DS_TYPE_LABELS = {
    OPD_NOTE: 'OPD Consultation Note',
    IPD_NOTE: 'IPD Daily Round Note',
    DISCHARGE_SUMMARY: 'Discharge Summary'
};
const DS_TYPE_HINTS = {
    OPD_NOTE: 'Outpatient consultation note for this patient. Stored with the visit when one is linked.',
    IPD_NOTE: 'Daily progress note for this admission. Appears on the patient’s clinical record.',
    DISCHARGE_SUMMARY: 'Written on the admission as its discharge summary. The patient is still discharged from the Admissions page, not here.'
};

function initDoctorStation() {
    document.getElementById('ds-refresh-btn').addEventListener('click', loadInpatients);
    document.getElementById('ds-cancel-note').addEventListener('click', closeNoteModal);
    document.getElementById('ds-save-note').addEventListener('click', saveClinicalNote);
    document.getElementById('ds-note-type').addEventListener('change', onNoteTypeChange);

    const back = document.getElementById('ds-back-to-admissions');
    if (back) back.addEventListener('click', () => navigateTo('admissions'));

    const filter = document.getElementById('ds-filter-entry');
    if (filter) filter.addEventListener('change', renderInpatients);

    const search = document.getElementById('ds-search');
    if (search) {
        search.addEventListener('input', () => {
            clearTimeout(dsSearchTimer);
            dsSearchTimer = setTimeout(loadInpatients, 300);
        });
    }

    loadInpatients();
}

async function loadInpatients() {
    const tbody = document.getElementById('ds-table-body');
    const q = document.getElementById('ds-search').value.trim();

    const params = new URLSearchParams();
    params.set('action', 'inpatients');
    if (q) params.set('q', q);

    try {
        const response = await fetch('/hms/backend/api/clinical_notes.php?' + params.toString());
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load in-patients');

        dsInpatients = data.inpatients || [];
        renderInpatients();
        renderStationStats();
    } catch (error) {
        console.error('Doctor station load error:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Failed to load in-patients</td></tr>';
    }
}

function renderInpatients() {
    const tbody = document.getElementById('ds-table-body');
    if (!tbody) return;

    // The entry-type filter narrows what each stay shows in its record column;
    // a stay with no entry of that type is hidden rather than shown empty.
    const filter = document.getElementById('ds-filter-entry').value;
    if (filter === 'ALL') {
        tbody.innerHTML = dsInpatients.length
            ? dsInpatients.map(inpatientRow).join('')
            : '<tr><td colspan="7"><div id="ds-empty">No in-patients found</div></td></tr>';
        return;
    }
    // A type-specific filter needs the notes per stay, so they are fetched once.
    loadNotesForType(filter);
}

function inpatientRow(p) {
    const notes = dsNotes.filter(n => String(n.admission_id) === String(p.admission_id));
    const record = clinicalRecordCell(p, notes);
    return '<tr data-ds-row="' + p.admission_id + '">'
        + '<td><span class="ds-code">' + escHtml(p.hospital_number || '-') + '</span></td>'
        + '<td><span class="ds-name">' + escHtml(p.patient_name || '-') + '</span>'
            + '<div class="ds-note-count">' + escHtml(p.gender || '') + (p.age !== null && p.age !== undefined ? ' &middot; ' + escHtml(p.age) + ' yrs' : '') + '</div></td>'
        + '<td>' + escHtml(p.ward_name || '-') + ' &middot; Bed ' + escHtml(p.bed_number || '-') + '</td>'
        + '<td>' + fmtDateTime(p.admission_date) + '<div class="ds-note-count">' + escHtml(p.admission_type || '') + '</div></td>'
        + '<td>' + (p.diagnosis ? escHtml(p.diagnosis) : '<span style="color:#95A5A6;">—</span>') + '</td>'
        + record
        + '<td><div class="ds-actions">'
            + '<button class="primary" data-ds-open="' + p.admission_id + '"><i class="fa-solid fa-plus"></i> Add Note / Discharge Summary</button>'
            + '<button class="view" data-ds-history="' + p.admission_id + '"><i class="fa-solid fa-clock-rotate-left"></i> Record</button>'
            + '<button class="consult" data-ds-consult="' + p.admission_id + '" title="Open the full consultation form for this patient"><i class="fa-solid fa-stethoscope"></i> Consultation Form</button>'
        + '</div></td>'
        + '</tr>';
}

function clinicalRecordCell(p, notes) {
    if (!notes.length) {
        return '<td><span style="color:#95A5A6;">No entries yet</span></td>';
    }
    const tagFor = t => t === 'DISCHARGE_SUMMARY' ? 'dsum' : (t === 'IPD_NOTE' ? 'ipd' : 'opd');
    const items = notes.slice(0, 3).map(n =>
        '<div class="nt ' + tagFor(n.note_type) + '">' + escHtml(DS_TYPE_LABELS[n.note_type] || n.note_type) + '</div>'
        + '<div class="txt" style="font-size:10.5px;color:#475569;max-height:34px;overflow:hidden;white-space:pre-wrap;">' + escHtml(n.clinical_note) + '</div>'
        + '<div class="meta">' + fmtDateTime(n.created_at) + ' &middot; ' + escHtml(n.doctor_name || '') + '</div>'
    ).join('<div style="height:6px"></div>');
    const more = notes.length > 3 ? '<div class="meta">+ ' + (notes.length - 3) + ' more entr' + (notes.length - 3 === 1 ? 'y' : 'ies') + '</div>' : '';
    return '<td>' + items + more + '</td>';
}

function renderStationStats() {
    let notes = 0, awaiting = 0, done = 0;
    dsInpatients.forEach(p => {
        if (Number(p.ipd_note_count) > 0) notes++;
        if (Number(p.summary_count) > 0) done++; else awaiting++;
    });
    setText('ds-stat-inpatients', dsInpatients.length);
    setText('ds-stat-notes', notes);
    setText('ds-stat-summaries', awaiting);
    setText('ds-stat-done', done);
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

async function loadNotesForType(type) {
    const tbody = document.getElementById('ds-table-body');
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Loading entries...</td></tr>';
    try {
        const response = await fetch('/hms/backend/api/clinical_notes.php?action=list&note_type=' + type);
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load entries');
        dsNotes = data.notes || [];

        const wanted = dsInpatients.filter(p => dsNotes.some(n => String(n.admission_id) === String(p.admission_id)));
        tbody.innerHTML = wanted.length
            ? wanted.map(inpatientRow).join('')
            : '<tr><td colspan="7"><div id="ds-empty">No in-patients with a ' + escHtml(DS_TYPE_LABELS[type] || type) + ' yet</div></td></tr>';
    } catch (error) {
        console.error('Doctor station notes error:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Failed to load entries</td></tr>';
    }
}

// Delegated clicks so re-rendering the table keeps the buttons working.
document.addEventListener('click', function (e) {
    const openBtn = e.target.closest('[data-ds-open]');
    if (openBtn) { openNoteModal(openBtn.getAttribute('data-ds-open'), true); return; }

    const histBtn = e.target.closest('[data-ds-history]');
    if (histBtn) { openNoteModal(histBtn.getAttribute('data-ds-history'), false); return; }

    const consultBtn = e.target.closest('[data-ds-consult]');
    if (consultBtn) {
        e.stopPropagation();
        toggleConsultationForm(consultBtn.getAttribute('data-ds-consult'));
        return;
    }

    const rmBtn = e.target.closest('[data-ds-delete-note]');
    if (rmBtn) { deleteClinicalNote(rmBtn.getAttribute('data-ds-delete-note')); return; }

    // Removing a drug line only edits the local draft; nothing is sent until the
    // doctor saves, so no confirm() is needed here.
    const rxRm = e.target.closest('[data-ds-rx-rm]');
    if (rxRm && dsForm) {
        dsForm.rxLines.splice(Number(rxRm.getAttribute('data-ds-rx-rm')), 1);
        renderRxLines();
    }
});

async function openNoteModal(admissionId, startNewEntry) {
    const target = dsInpatients.find(p => String(p.admission_id) === String(admissionId));
    if (!target) return;
    dsTarget = target;

    document.getElementById('ds-note-patient').textContent =
        (target.patient_name || 'Patient') + ' (' + (target.hospital_number || '-') + ')';
    document.getElementById('ds-bar-admission').innerHTML =
        '<b>ADMISSION:</b> ' + escHtml(target.admission_code || '-');
    document.getElementById('ds-bar-bed').innerHTML =
        '<b>WARD / BED:</b> ' + escHtml(target.ward_name || '-') + ' &middot; Bed ' + escHtml(target.bed_number || '-');
    document.getElementById('ds-foot-code').textContent = target.admission_code || '—';

    if (startNewEntry) {
        document.getElementById('ds-note-text').value = '';
        // A round note is the safe default: it is the one type always accepted
        // for a live stay, whereas a stay that already has a summary would have
        // its second summary refused by the API.
        document.getElementById('ds-note-type').value = 'IPD_NOTE';
    }
    onNoteTypeChange();

    document.getElementById('ds-note-modal').classList.add('show');
    await loadStayNotes(target.admission_id);
    if (startNewEntry) document.getElementById('ds-note-text').focus();
}

function closeNoteModal() {
    document.getElementById('ds-note-modal').classList.remove('show');
    dsTarget = null;
}

function onNoteTypeChange() {
    const type = document.getElementById('ds-note-type').value;
    const hint = document.getElementById('ds-type-hint');
    if (hint) hint.textContent = DS_TYPE_HINTS[type] || '';

    const warn = document.getElementById('ds-summary-warn');
    if (warn) warn.classList.toggle('show', type === 'DISCHARGE_SUMMARY' && dsTarget && Number(dsTarget.summary_count) > 0);
}

async function loadStayNotes(admissionId) {
    const body = document.getElementById('ds-history-body');
    const count = document.getElementById('ds-history-count');
    body.innerHTML = '<div class="empty">Loading...</div>';
    try {
        const response = await fetch('/hms/backend/api/clinical_notes.php?action=list&admission_id=' + admissionId);
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load the clinical record');

        const notes = data.notes || [];
        count.textContent = notes.length + (notes.length === 1 ? ' entry' : ' entries');

        if (!notes.length) {
            body.innerHTML = '<div class="empty">No clinical entries recorded for this stay yet.</div>';
            return;
        }
        const tagFor = t => t === 'DISCHARGE_SUMMARY' ? 'dsum' : (t === 'IPD_NOTE' ? 'ipd' : 'opd');
        body.innerHTML = '<table><thead><tr><th>Type</th><th>Entry</th><th>Recorded</th><th></th></tr></thead><tbody>'
            + notes.map(n =>
                '<tr>'
                + '<td><span class="nt ' + tagFor(n.note_type) + '">' + escHtml(DS_TYPE_LABELS[n.note_type] || n.note_type) + '</span></td>'
                + '<td><div class="txt">' + escHtml(n.clinical_note) + '</div></td>'
                + '<td><span class="meta" style="margin:0;">' + fmtDateTime(n.created_at) + '<br>' + escHtml(n.doctor_name || '') + '</span></td>'
                + '<td><button class="rm" data-ds-delete-note="' + n.id + '">Delete</button></td>'
                + '</tr>'
            ).join('')
            + '</tbody></table>';
    } catch (error) {
        body.innerHTML = '<div class="empty">Could not load the clinical record — ' + escHtml(error.message) + '</div>';
    }
}

async function saveClinicalNote() {
    if (!dsTarget) return;
    const text = document.getElementById('ds-note-text').value.trim();
    if (!text) { showAlert('Enter the clinical notes before saving', 'error'); return; }

    const type = document.getElementById('ds-note-type').value;
    const btn = document.getElementById('ds-save-note');
    btn.disabled = true;
    try {
        const res = await fetch('/hms/backend/api/clinical_notes.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                patient_id: dsTarget.patient_id,
                admission_id: dsTarget.admission_id,
                note_type: type,
                clinical_note: text
            })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not save the entry');

        showAlert((DS_TYPE_LABELS[type] || 'Clinical entry') + ' saved', 'success');
        document.getElementById('ds-note-text').value = '';
        closeNoteModal();
        await loadInpatients();
    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        btn.disabled = false;
    }
}

async function deleteClinicalNote(id) {
    if (!confirm('Delete this clinical entry?\n\nThe record cannot be recovered.')) return;
    try {
        const res = await fetch('/hms/backend/api/clinical_notes.php?action=delete&id=' + id, { method: 'DELETE' });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not delete the entry');

        showAlert('Clinical entry deleted', 'success');
        if (dsTarget) await loadStayNotes(dsTarget.admission_id);
        await loadInpatients();
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

/* ================= Inline consultation form =================
   State for the single expandable form. Only one patient is expanded at a time,
   so this is a plain object rather than a map keyed by admission. */
let dsForm = null;

function toggleConsultationForm(admissionId) {
    const row = document.querySelector('#ds-table-body tr[data-ds-row="' + admissionId + '"]');
    if (!row) return;

    const existing = row.nextElementSibling;
    if (existing && existing.classList.contains('ds-expand')) {
        existing.remove();
        if (dsForm) dsForm.row.classList.remove('is-open');
        dsForm = null;
        return;
    }

    if (dsForm && dsForm.row) {
        const prior = dsForm.row.nextElementSibling;
        if (prior && prior.classList.contains('ds-expand')) prior.remove();
        dsForm.row.classList.remove('is-open');
    }
    dsForm = { admissionId: admissionId, row: row, rxLines: [], drugs: [], saved: null };
    row.classList.add('is-open');

    const host = document.createElement('tr');
    host.className = 'ds-expand';
    host.innerHTML = '<td colspan="7"><div class="ds-form"><div style="text-align:center;padding:20px;color:#8A94A6;font-size:12px;">Loading consultation form...</div></div></td>';
    row.after(host);
    loadConsultationForm();
}

async function loadConsultationForm() {
    const ctx = await fetchJson('/hms/backend/api/consultation_form.php?action=context&patient_id=' + dsFormPatientId());
    if (!ctx) return;
    dsForm.ctx = ctx;
    dsForm.host = dsForm.row.nextElementSibling;
    dsForm.host.innerHTML = buildConsultationForm(ctx);
    wireConsultationForm();
    setupDrugPicker();
    if (!ctx.admission) loadWards();
}

function dsFormPatientId() {
    const p = dsInpatients.find(x => String(x.admission_id) === String(dsForm.admissionId));
    return p ? p.patient_id : 0;
}

async function fetchJson(url, options) {
    try {
        const res = await fetch(url, options);
        const data = await res.json();
        if (!data.success) {
            showAlert(data.error || 'Something went wrong', 'error');
            return null;
        }
        return data;
    } catch (error) {
        showAlert(error.message, 'error');
        return null;
    }
}

/* ---------- Rendering ---------- */
function initialsOf(name) {
    const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '?';
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function vitalTile(label, value, suffix) {
    if (value === null || value === undefined || value === '') {
        return '<div class="v none"><span class="k">' + escHtml(label) + '</span><span class="n">Not recorded</span></div>';
    }
    // Blood pressure arrives already composed as "120/80", so only round a
    // genuinely numeric reading — rounding the string would print NaN.
    const shown = (typeof value === 'number')
        ? String(Math.round(value * 10) / 10)
        : String(value);
    return '<div class="v"><span class="k">' + escHtml(label) + '</span><span class="n">' + escHtml(shown) + (suffix || '') + '</span></div>';
}

function buildConsultationForm(ctx) {
    const p = ctx.patient;
    const v = ctx.vitals;
    const hasAllergy = !!(p.allergies && p.allergies.trim());

    // Vitals held outside a believable range are blanked by the API. Saying so
    // is better than silently showing a dash as though nothing was ever taken.
    const dropped = (v.discarded || []).length;

    const history = [];
    (ctx.notes || []).forEach(n => history.push({
        when: n.created_at,
        who: n.doctor_name || '',
        what: n.note_type,
        text: n.clinical_note
    }));
    (ctx.consultations || []).forEach(c => history.push({
        when: c.consultation_date,
        who: c.doctor_name || '',
        what: c.consultation_type === 'new' ? 'New consultation' : (c.consultation_type === 'follow_up' ? 'Follow-up consultation' : 'Emergency consultation'),
        text: (c.diagnosis ? 'Diagnosis: ' + c.diagnosis : '') + (c.chief_complaint ? (c.diagnosis ? '\n' : '') + 'Complaint: ' + c.chief_complaint : ''),
        muted: true
    }));
    history.sort((a, b) => String(b.when).localeCompare(String(a.when)));

    const historyHtml = history.length
        ? history.slice(0, 25).map(h =>
            '<div class="h"><div class="w">' + fmtDateTime(h.when) + ' <span>(' + escHtml(h.who) + ')</span></div>'
            + '<div style="font-size:9.5px;color:' + (h.muted ? '#94A3B8' : '#0b5fa5') + ';text-transform:uppercase;letter-spacing:.3px;font-weight:700;">' + escHtml(h.what) + '</div>'
            + (h.text ? '<div class="txt">' + escHtml(h.text) + '</div>' : '')
            + '</div>').join('')
        : '<div class="none">No previous records for this patient.</div>';

    const invHtml = (ctx.investigations || []).map(i =>
        '<label><input type="checkbox" class="ds-inv-check" value="' + escHtml(i.key) + '">'
        + escHtml(i.label) + '<span class="kind">' + escHtml(i.kind === 'lab' ? 'Lab' : 'Imaging') + '</span></label>').join('');

    const deptHtml = '<option value="">General review (no clinic)</option>'
        + (ctx.departments || []).map(d => '<option value="' + d.id + '">' + escHtml(d.name) + '</option>').join('');

    const pendingHtml = (ctx.pending_prescriptions || []).length
        ? '<div class="ds-pending"><div style="font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;color:#64748B;margin-bottom:5px;">Already prescribed, awaiting dispensing</div>'
          + ctx.pending_prescriptions.map(r => '<div class="pd"><b>' + escHtml(r.drug_name) + '</b> &mdash; '
              + escHtml(r.dosage || '') + ' ' + escHtml(r.frequency || '') + ' ' + escHtml(r.duration || '') + '</div>').join('')
          + '</div>'
        : '';

    const admitBox = ctx.admission
        ? '<div class="ds-admit is-admitted">'
            + '<label class="chk"><input type="checkbox" id="ds-admit-check" checked disabled> Already admitted to a bed</label>'
            + '<div class="note"><i class="fa-solid fa-bed"></i> ' + escHtml(ctx.admission.ward_name) + ' &middot; Bed ' + escHtml(ctx.admission.bed_number)
            + '<br>Admission ' + escHtml(ctx.admission.admission_code) + '. No new admission is needed.</div>'
          + '</div>'
        : '<div class="ds-admit">'
            + '<label class="chk"><input type="checkbox" id="ds-admit-check"> Admit patient to ward / bed</label>'
            + '<div class="wardgrid" id="ds-wardgrid" style="display:none;">'
                + '<div><label for="ds-ward">Ward</label><select id="ds-ward"><option value="">Select ward</option></select></div>'
                + '<div><label for="ds-bed">Bed</label><select id="ds-bed" disabled><option value="">Select ward first</option></select></div>'
            + '</div>'
            + '<div class="note" id="ds-admit-note" style="display:none;">The bed is occupied by the Admissions Register, which owns bed state.</div>'
          + '</div>';

    return ''
        + '<div class="ds-ident">'
            + '<div class="who">'
                + '<div class="avatar">' + escHtml(initialsOf(p.name)) + '</div>'
                + '<div><div class="nm">' + escHtml(p.name) + '<span class="hn">' + escHtml(p.hospital_number) + '</span></div>'
                + '<div class="facts">Gender: <b>' + escHtml(p.gender || '-') + '</b> &bull; Age: <b>' + escHtml(p.age !== null ? p.age + ' yrs' : '-') + '</b>'
                + ' &bull; Blood Group: <b>' + escHtml(p.blood_group || 'Not recorded') + '</b>'
                + ' &bull; NHIS No: <b>' + escHtml(p.nhia_number || 'Not recorded') + '</b></div></div>'
            + '</div>'
            + '<div>'
                + (ctx.admission ? '<span class="ds-stay"><i class="fa-solid fa-bed"></i> ' + escHtml(ctx.admission.ward_name) + ' &middot; Bed ' + escHtml(ctx.admission.bed_number) + '</span>' : '')
                + (hasAllergy ? ' <span class="ds-alert"><i class="fa-solid fa-triangle-exclamation"></i> Allergy: ' + escHtml(p.allergies) + '</span>' : '')
            + '</div>'
        + '</div>'

        + '<div class="cols">'
        + '<div class="col">'

            + '<div class="ds-box"><h4><i class="fa-solid fa-clock-rotate-left"></i> Past Medical Records &amp; Histories</h4>'
                + '<div class="ds-hist">' + historyHtml + '</div></div>'

            + '<div class="ds-box"><h4><i class="fa-solid fa-heart-pulse"></i> Latest Vitals <span class="hint">entered by nursing</span></h4>'
                + '<div class="ds-vitals">'
                    + vitalTile('Blood Pressure', (v.blood_pressure_systolic !== null && v.blood_pressure_diastolic !== null)
                        ? v.blood_pressure_systolic + '/' + v.blood_pressure_diastolic : null, ' mmHg')
                    + vitalTile('Pulse Rate', v.heart_rate, ' bpm')
                    + vitalTile('Temperature', v.temperature, ' °C')
                    + vitalTile('SpO2 Level', v.oxygen_saturation, '%')
                + '</div>'
                + (v.recorded_at ? '<div class="when">Recorded ' + fmtDateTime(v.recorded_at) + (v.nurse_name ? ' by ' + escHtml(v.nurse_name) : '') + '</div>' : '')
                + (dropped ? '<div class="flagged"><i class="fa-solid fa-triangle-exclamation"></i> ' + dropped + ' reading' + (dropped === 1 ? ' was' : 's were') + ' recorded outside a possible range and ' + (dropped === 1 ? 'is' : 'are') + ' not shown. Check the vitals entry.</div>' : '')
            + '</div>'

            + '<div class="ds-box"><h4><i class="fa-solid fa-ban"></i> Allergic Update</h4>'
                + '<div class="ds-allergy-wrap"><input type="text" id="ds-allergies" placeholder="e.g. Penicillin, Sulfa drugs" value="' + escHtml(p.allergies || '') + '"></div>'
                + '<div class="ds-allergy-note">Saved to the patient record and shown as a warning on every clinical screen.</div>'
            + '</div>'

            + '<div class="ds-box"><h4><i class="fa-solid fa-bed-pulse"></i> Inpatient Admission</h4>' + admitBox + '</div>'

        + '</div>'
        + '<div class="col">'

            + '<div class="two">'
                + '<div class="ds-box"><h4><i class="fa-solid fa-comment-medical"></i> Presenting Complaints</h4>'
                    + '<textarea id="ds-complaints" placeholder="Patient complaints, duration, severity..."></textarea></div>'
                + '<div class="ds-box"><h4><i class="fa-solid fa-notes-medical"></i> Doctor Notes Update</h4>'
                    + '<textarea id="ds-doctor-notes" placeholder="Clinical examination findings, systemic review..."></textarea></div>'
            + '</div>'

            + '<div class="ds-box"><h4><i class="fa-solid fa-stethoscope"></i> Condition of the Patient / Provisional Diagnosis</h4>'
                + '<div class="three">'
                    + '<div><label for="ds-diagnosis">Provisional Diagnosis</label>'
                        + '<input type="text" id="ds-diagnosis" placeholder="e.g. Malaria, Upper Respiratory Tract Infection" value="' + escHtml(ctx.admission && ctx.admission.diagnosis ? ctx.admission.diagnosis : '') + '"></div>'
                    + '<div><label for="ds-diag-status">Diagnosis Status</label>'
                        + '<select id="ds-diag-status"><option value="New">New Diagnosis</option><option value="Old">Old / Chronic Diagnosis</option></select></div>'
                + '</div>'
            + '</div>'

            + '<div class="ds-box"><h4><i class="fa-solid fa-microscope"></i> Investigations / Radiology / Sonography Requests <span class="hint">raise real orders</span></h4>'
                + '<div class="ds-inv">' + invHtml + '</div></div>'

            + '<div class="ds-box"><h4><i class="fa-solid fa-pills"></i> Prescription (Medication Orders)</h4>'
                + '<div class="ds-rx-add">'
                    + '<input type="text" id="ds-drug-search" placeholder="Search stocked drugs by name or code..." list="ds-drug-list" autocomplete="off">'
                    + '<datalist id="ds-drug-list"></datalist>'
                    + '<button type="button" id="ds-drug-add"><i class="fa-solid fa-plus"></i> Add</button>'
                + '</div>'
                + '<div class="ds-rx-hint">Each drug added here creates a prescription row the pharmacy can dispense. Only drugs with stock in hand are listed.</div>'
                + '<div class="ds-rx-lines" id="ds-rx-lines"></div>'
                + pendingHtml
                + '<div class="fld" style="margin-top:11px;"><label for="ds-med-notes">Additional Medication Notes (free text)</label>'
                    + '<textarea id="ds-med-notes" placeholder="Advice, drugs not stocked, anything the pharmacy must know..." style="min-height:58px;">'
                    + escHtml(ctx.last_medication_notes || '') + '</textarea></div>'
            + '</div>'

            + '<div class="ds-box"><h4><i class="fa-solid fa-calendar-days"></i> Follow-Up Calendar</h4>'
                + '<div class="two">'
                    + '<div><label for="ds-followup">Review Date &amp; Time</label><input type="datetime-local" id="ds-followup"></div>'
                    + '<div><label for="ds-followup-clinic">Follow-Up Clinic / Unit</label><select id="ds-followup-clinic">' + deptHtml + '</select></div>'
                + '</div>'
                + '<div class="fld" style="margin-top:9px;"><label for="ds-followup-reason">Reason for review</label>'
                    + '<input type="text" id="ds-followup-reason" placeholder="e.g. Review after completing treatment"></div>'
            + '</div>'

        + '</div>'
        + '</div>'

        + '<div class="ds-actions-bar">'
            + '<div><span class="ds-saving" id="ds-saving"><i class="fa-solid fa-spinner fa-spin"></i> Saving...</span>'
                + '<span class="note">Saved under your name and stamped with the time.</span></div>'
            + '<div class="btns">'
                + '<button type="button" class="ds-btn grey" id="ds-collapse"><i class="fa-solid fa-xmark"></i> Close</button>'
                + '<button type="button" class="ds-btn grey" id="ds-save-draft"><i class="fa-regular fa-floppy-disk"></i> Save to Draft</button>'
                + '<button type="button" class="ds-btn blue" id="ds-print"><i class="fa-solid fa-print"></i> Print</button>'
                + '<button type="button" class="ds-btn red" id="ds-save"><i class="fa-solid fa-rotate"></i> Update Records</button>'
            + '</div>'
        + '</div>';
}

function wireConsultationForm() {
    const collapse = document.getElementById('ds-collapse');
    if (collapse) collapse.addEventListener('click', toggleConsultationForm.bind(null, dsForm.admissionId));

    const save = document.getElementById('ds-save');
    if (save) save.addEventListener('click', () => saveConsultationForm(false));
    const draft = document.getElementById('ds-save-draft');
    if (draft) draft.addEventListener('click', () => saveConsultationForm(true));
    const printBtn = document.getElementById('ds-print');
    if (printBtn) printBtn.addEventListener('click', printConsultation);

    const admit = document.getElementById('ds-admit-check');
    if (admit) {
        admit.addEventListener('change', () => {
            const grid = document.getElementById('ds-wardgrid');
            if (grid) grid.style.display = admit.checked ? 'grid' : 'none';
        });
    }

    const ward = document.getElementById('ds-ward');
    if (ward) ward.addEventListener('change', loadAvailableBeds);

    renderRxLines();
}

// Ward list for the admit toggle. Occupancy comes from admissions.php, which is
// the only place bed state is maintained.
async function loadWards() {
    const data = await fetchJson('/hms/backend/api/admissions.php?action=wards');
    if (!data) return;
    const select = document.getElementById('ds-ward');
    if (!select) return;

    const free = (data.wards || []).filter(w => w.beds_free > 0);
    if (!free.length) {
        select.innerHTML = '<option value="">No ward has a free bed</option>';
        select.disabled = true;
        const note = document.getElementById('ds-admit-note');
        if (note) { note.style.display = 'block'; note.textContent = 'Every bed is currently occupied. The patient cannot be admitted right now.'; }
        return;
    }
    select.innerHTML = '<option value="">Select ward</option>'
        + free.map(w => '<option value="' + w.id + '">' + escHtml(w.ward_name) + ' &mdash; ' + w.beds_free + ' free</option>').join('');
}

async function loadAvailableBeds() {
    const wardId = document.getElementById('ds-ward').value;
    const bedSelect = document.getElementById('ds-bed');
    if (!bedSelect) return;
    if (!wardId) {
        bedSelect.innerHTML = '<option value="">Select ward first</option>';
        bedSelect.disabled = true;
        return;
    }
    bedSelect.disabled = true;
    bedSelect.innerHTML = '<option value="">Loading beds...</option>';
    const data = await fetchJson('/hms/backend/api/admissions.php?action=available_beds&ward_id=' + wardId);
    if (!data) { bedSelect.innerHTML = '<option value="">Could not load beds</option>'; return; }
    if (!data.beds || !data.beds.length) {
        bedSelect.innerHTML = '<option value="">No free bed in this ward</option>';
        return;
    }
    bedSelect.disabled = false;
    bedSelect.innerHTML = '<option value="">Select bed</option>'
        + data.beds.map(b => '<option value="' + b.id + '">' + escHtml(b.bed_number) + (b.bed_type ? ' (' + escHtml(b.bed_type) + ')' : '') + '</option>').join('');
}

function setupDrugPicker() {
    const input = document.getElementById('ds-drug-search');
    const list = document.getElementById('ds-drug-list');
    const add = document.getElementById('ds-drug-add');
    if (!input || !add) return;

    let timer = null;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        const term = input.value.trim();
        timer = setTimeout(() => searchDrugs(term), 250);
    });

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); addDrugFromInput(); }
    });
    add.addEventListener('click', addDrugFromInput);

    searchDrugs('');
}

async function searchDrugs(term) {
    const list = document.getElementById('ds-drug-list');
    if (!list) return;
    const data = await fetchJson('/hms/backend/api/consultation_form.php?action=drugs&q=' + encodeURIComponent(term || ''));
    if (!data) return;
    dsForm.drugs = data.drugs || [];
    list.innerHTML = dsForm.drugs
        .map(d => '<option value="' + escHtml(d.drug_name) + '">' + escHtml(d.drug_code) + ' &mdash; ' + (d.quantity_in_stock || 0) + ' ' + escHtml(d.unit || 'in stock') + '</option>')
        .join('');
}

async function addDrugFromInput() {
    const input = document.getElementById('ds-drug-search');
    const term = input.value.trim();
    if (!term) return;

    // Match what was typed against the last search result set. The datalist
    // gives the suggestion but exposes no id, so the lookup happens here.
    const lower = term.toLowerCase();
    let drug = dsForm.drugs.find(d => d.drug_name.toLowerCase() === lower || d.drug_code.toLowerCase() === lower);

    if (!drug) {
        const data = await fetchJson('/hms/backend/api/consultation_form.php?action=drugs&q=' + encodeURIComponent(term));
        drug = (data && data.drugs || []).find(d => d.drug_name.toLowerCase() === lower || d.drug_code.toLowerCase() === lower);
    }
    if (!drug) {
        showAlert('"' + term + '" is not a stocked drug. Search the list and pick one so the pharmacy receives a valid order.', 'error');
        return;
    }
    if (dsForm.rxLines.some(l => l.drug_id === drug.id)) {
        showAlert(drug.drug_name + ' is already on this prescription', 'error');
        return;
    }

    dsForm.rxLines.push({
        drug_id: drug.id,
        drug_name: drug.drug_name,
        generic_name: drug.generic_name,
        dosage: '',
        frequency: '',
        duration: '',
        quantity: 1,
        instructions: ''
    });
    input.value = '';
    renderRxLines();
    const first = document.querySelector('#ds-rx-lines input.ds-rx-dosage');
    if (first) first.focus();
}

function renderRxLines() {
    const host = document.getElementById('ds-rx-lines');
    if (!host) return;

    if (!dsForm.rxLines.length) {
        host.innerHTML = '<div class="ds-rx-empty">No drugs added yet.</div>';
        return;
    }

    host.innerHTML = dsForm.rxLines.map((l, i) =>
        '<div class="ds-rx-line">'
        + '<div class="top"><b>' + escHtml(l.drug_name) + '</b>'
            + (l.generic_name && l.generic_name.toLowerCase() !== l.drug_name.toLowerCase()
                ? '<span class="gen">' + escHtml(l.generic_name) + '</span>' : '')
            + '<button type="button" class="rm" data-ds-rx-rm="' + i + '">Remove</button></div>'
        + '<div class="flds">'
            + '<div>' + '<label>Dosage</label><input type="text" class="ds-rx-dosage" data-ds-rx-field="dosage" data-ds-rx-i="' + i + '" value="' + escHtml(l.dosage) + '" placeholder="e.g. 1g"></div>'
            + '<div><label>Frequency</label><input type="text" data-ds-rx-field="frequency" data-ds-rx-i="' + i + '" value="' + escHtml(l.frequency) + '" placeholder="e.g. TDS"></div>'
            + '<div><label>Duration</label><input type="text" data-ds-rx-field="duration" data-ds-rx-i="' + i + '" value="' + escHtml(l.duration) + '" placeholder="e.g. 5 days"></div>'
            + '<div><label>Quantity to issue</label><input type="number" min="1" step="1" class="ds-rx-qty" data-ds-rx-field="quantity" data-ds-rx-i="' + i + '" value="' + escHtml(l.quantity == null ? 1 : l.quantity) + '" title="The pharmacy will not dispense more than this"></div>'
        + '</div>'
        + '<div style="margin-top:6px;"><label>Instructions (optional)</label><input type="text" data-ds-rx-field="instructions" data-ds-rx-i="' + i + '" value="' + escHtml(l.instructions) + '" placeholder="e.g. after food"></div>'
        + '</div>').join('');
}

async function saveConsultationForm(asDraft) {
    if (!dsForm || !dsForm.ctx) return;

    // Read the prescription lines back out of the inputs rather than trusting the
    // in-memory copy, which can be stale if the doctor typed into them directly.
    const lines = [];
    document.querySelectorAll('#ds-rx-lines [data-ds-rx-field]').forEach(el => {
        const i = Number(el.getAttribute('data-ds-rx-i'));
        if (!dsForm.rxLines[i]) return;
        dsForm.rxLines[i][el.getAttribute('data-ds-rx-field')] = el.value.trim();
    });
    dsForm.rxLines.forEach(l => lines.push({
        drug_id: l.drug_id, dosage: l.dosage, frequency: l.frequency,
        duration: l.duration, quantity: l.quantity, instructions: l.instructions
    }));

    const investigations = [];
    document.querySelectorAll('#docstation .ds-inv-check').forEach(c => {
        if (c.checked) investigations.push(c.value);
    });

    const wantsAdmit = document.getElementById('ds-admit-check')
        && document.getElementById('ds-admit-check').checked
        && !document.getElementById('ds-admit-check').disabled;

    let wardId = '', bedId = '';
    if (wantsAdmit) {
        wardId = (document.getElementById('ds-ward') || {}).value || '';
        bedId = (document.getElementById('ds-bed') || {}).value || '';
        if (!wardId || !bedId) {
            showAlert('Choose the ward and bed, or untick the admission box.', 'error');
            return;
        }
    }

    const payload = {
        patient_id: dsForm.ctx.patient.id,
        diagnosis_status: document.getElementById('ds-diag-status').value,
        presenting_complaints: document.getElementById('ds-complaints').value.trim(),
        doctor_notes: document.getElementById('ds-doctor-notes').value.trim(),
        provisional_diagnosis: document.getElementById('ds-diagnosis').value.trim(),
        medication_notes: document.getElementById('ds-med-notes').value.trim(),
        allergies: document.getElementById('ds-allergies').value.trim(),
        investigations: investigations,
        prescriptions: lines,
        save_as_draft: !!asDraft
    };

    const followUp = document.getElementById('ds-followup').value;
    if (followUp) {
        payload.follow_up_datetime = followUp;
        payload.follow_up_department_id = document.getElementById('ds-followup-clinic').value;
        payload.follow_up_reason = document.getElementById('ds-followup-reason').value.trim();
    }

    setSaving(true);
    const saved = await fetchJson('/hms/backend/api/consultation_form.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    setSaving(false);
    if (!saved) return;

    const parts = [];
    parts.push((asDraft ? 'Draft saved' : 'Consultation saved'));
    if (saved.created.lab) parts.push(saved.created.lab + ' lab request' + (saved.created.lab === 1 ? '' : 's'));
    if (saved.created.radiology) parts.push(saved.created.radiology + ' imaging request' + (saved.created.radiology === 1 ? '' : 's'));
    if (saved.created.prescriptions) parts.push(saved.created.prescriptions + ' prescription' + (saved.created.prescriptions === 1 ? '' : 's'));
    if (saved.appointment_id) parts.push('follow-up booked');
    if (saved.skipped_duplicates) parts.push(saved.skipped_duplicates + ' duplicate request' + (saved.skipped_duplicates === 1 ? '' : 's') + ' skipped');
    showAlert(parts.join(' · '), 'success');

    // Admission is a separate call to the Admissions Register. It runs after the
    // consultation is safely committed, so a full ward cannot lose the clinical
    // record that was just written.
    if (wantsAdmit) {
        const admit = await fetchJson('/hms/backend/api/admissions.php?action=admit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                patient_id: dsForm.ctx.patient.id,
                ward_id: Number(wardId),
                bed_id: Number(bedId),
                admission_type: 'Routine',
                admitting_doctor: dsForm.ctx.doctor_name || '',
                diagnosis: payload.provisional_diagnosis
            })
        });
        if (admit) {
            showAlert('Patient admitted — admission ' + (admit.admission_code || ''), 'success');
        } else {
            showAlert('Consultation saved, but the admission was refused. The bed may have just been taken. Open Admissions to check.', 'error');
        }
    }

    if (wantsAdmit) {
        // An admission changes the in-patient list itself, so the list is
        // rebuilt and the form closed: the patient now appears as a row.
        await loadInpatients();
        closeConsultationForm();
        return;
    }
    // Otherwise nothing in this table moved — the encounter went to
    // consultations, lab/radiology requests and appointments, not to the
    // clinical notes this list renders. Reloading would throw away the text that
    // was just saved, so the form stays open exactly as it was left.
}

function closeConsultationForm() {
    if (!dsForm) return;
    const host = dsForm.row && dsForm.row.nextElementSibling;
    if (host && host.classList.contains('ds-expand')) host.remove();
    if (dsForm.row) dsForm.row.classList.remove('is-open');
    dsForm = null;
}

function setSaving(on) {
    const el = document.getElementById('ds-saving');
    if (el) el.classList.toggle('show', !!on);
    ['ds-save', 'ds-save-draft'].forEach(id => {
        const b = document.getElementById(id);
        if (b) b.disabled = !!on;
    });
}

function printConsultation() {
    if (!dsForm || !dsForm.ctx) return;
    const p = dsForm.ctx.patient;
    const v = dsForm.ctx.vitals;

    const val = (el, fallback) => {
        const e = document.getElementById(el);
        const t = e ? e.value.trim() : '';
        return t || fallback || '—';
    };
    const investigations = [];
    document.querySelectorAll('#docstation .ds-inv-check').forEach(c => { if (c.checked) investigations.push(c.value.split(':')[1]); });

    const rxRows = [];
    document.querySelectorAll('#ds-rx-lines [data-ds-rx-field]').forEach(el => {
        const i = Number(el.getAttribute('data-ds-rx-i'));
        if (!dsForm.rxLines[i]) return;
        dsForm.rxLines[i][el.getAttribute('data-ds-rx-field')] = el.value.trim();
    });
    dsForm.rxLines.forEach(l => rxRows.push(
        '<tr><td>' + escHtml(l.drug_name) + '</td><td>' + escHtml(l.dosage || '—') + '</td>'
        + '<td>' + escHtml(l.frequency || '—') + '</td><td>' + escHtml(l.duration || '—') + '</td>'
        + '<td>' + escHtml(l.instructions || '—') + '</td></tr>'));

    // Local wall-clock stamp, formatted through the shell's own date helper so
    // the printed header matches every other date on the portal.
    const now = new Date();
    const localStamp = now.getFullYear() + '-'
        + String(now.getMonth() + 1).padStart(2, '0') + '-'
        + String(now.getDate()).padStart(2, '0') + ' '
        + String(now.getHours()).padStart(2, '0') + ':'
        + String(now.getMinutes()).padStart(2, '0');

    document.getElementById('ds-print-area').innerHTML = ''
        + '<h1>Consultation Record</h1>'
        + '<div class="sub">Eddie Health Care Solutions &middot; printed ' + escHtml(fmtDateTime(localStamp)) + '</div>'
        + '<h2>Patient</h2><table class="kv">'
            + '<tr><td>Name</td><td>' + escHtml(p.name) + '</td></tr>'
            + '<tr><td>Hospital No.</td><td>' + escHtml(p.hospital_number) + '</td></tr>'
            + '<tr><td>Gender / Age</td><td>' + escHtml(p.gender || '—') + ' / ' + escHtml(p.age !== null ? p.age + ' yrs' : '—') + '</td></tr>'
            + '<tr><td>Blood Group</td><td>' + escHtml(p.blood_group || '—') + '</td></tr>'
            + '<tr><td>NHIS No.</td><td>' + escHtml(p.nhia_number || '—') + '</td></tr>'
            + '<tr><td>Allergies</td><td>' + escHtml(p.allergies || 'None recorded') + '</td></tr>'
        + '</table>'
        + '<h2>Latest Vitals (nursing)</h2><table class="kv">'
            + '<tr><td>Blood Pressure</td><td>' + escHtml((v.blood_pressure_systolic || '—') + '/' + (v.blood_pressure_diastolic || '—')) + ' mmHg</td></tr>'
            + '<tr><td>Pulse</td><td>' + escHtml(v.heart_rate || '—') + ' bpm</td></tr>'
            + '<tr><td>Temperature</td><td>' + escHtml(v.temperature || '—') + ' °C</td></tr>'
            + '<tr><td>SpO2</td><td>' + escHtml(v.oxygen_saturation || '—') + '%</td></tr>'
        + '</table>'
        + '<h2>Presenting Complaints</h2><div class="pre">' + escHtml(val('ds-complaints')) + '</div>'
        + '<h2>Provisional Diagnosis</h2><div class="pre">' + escHtml(val('ds-diagnosis')) + '</div>'
        + '<h2>Doctor Notes</h2><div class="pre">' + escHtml(val('ds-doctor-notes')) + '</div>'
        + (investigations.length ? '<h2>Investigations Requested</h2><ul>' + investigations.map(i => '<li>' + escHtml(i) + '</li>').join('') + '</ul>' : '')
        + (rxRows.length ? '<h2>Prescription</h2><table class="kv"><tr><td style="width:auto;">Drug</td><td style="width:90px;">Dosage</td><td style="width:90px;">Frequency</td><td style="width:90px;">Duration</td><td>Instructions</td></tr>' + rxRows.join('') + '</table>' : '')
        + (val('ds-med-notes', '') !== '—' ? '<h2>Medication Notes</h2><div class="pre">' + escHtml(val('ds-med-notes')) + '</div>' : '')
        + '<h2>Follow-Up</h2><table class="kv"><tr><td>Review Date</td><td>' + escHtml(val('ds-followup')) + '</td></tr>'
        + '<tr><td>Clinic</td><td>' + escHtml(document.getElementById('ds-followup-clinic').selectedOptions[0].textContent) + '</td></tr></table>';

    window.print();
}
</script>
