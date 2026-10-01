<style>
    /* ============ PHARMACY MANAGEMENT ============
       Scoped under #pharmacy. The shell supplies card/card-body/table/btn/modal/
       form-group/form-row, so only the pharmacy-specific pieces are defined here. */
    #pharmacy{--rx-blue:#1B659D;--rx-ink:#0F2D59;--rx-mute:#64748B;--rx-line:#DCE4EC}

    /* Header banner */
    #rx-banner{background:var(--rx-blue);color:#fff;border-radius:5px;padding:12px 16px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
    #rx-banner h5{margin:0;font-size:13.5px;font-weight:800;letter-spacing:.4px;text-transform:uppercase}
    #rx-banner .sub{font-size:11px;opacity:.9;margin-top:3px}
    #rx-banner .portal{background:#0A4A78;border:1px solid #14608F;color:#fff;border-radius:4px;padding:6px 12px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;display:inline-flex;align-items:center;gap:6px;white-space:nowrap}

    /* Stat strip: waiting to issue, issued today, stock units, items out of stock */
    #rx-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:14px}
    #rx-stats .s{background:#fff;border:1px solid var(--rx-line);border-left:3px solid var(--rx-blue);border-radius:4px;padding:9px 12px}
    #rx-stats .s.warn{border-left-color:#D97706}
    #rx-stats .s.bad{border-left-color:#DC2626}
    #rx-stats .s.good{border-left-color:#059669}
    #rx-stats .lbl{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--rx-mute)}
    #rx-stats .val{font-size:19px;font-weight:800;color:var(--rx-ink);line-height:1.25}
    #rx-stats .sub{font-size:9.5px;color:var(--rx-mute)}

    /* Sub-tabs */
    #rx-tabbar{display:flex;flex-wrap:wrap;gap:6px;background:#fff;border:1px solid var(--rx-line);border-radius:5px;padding:8px;margin-bottom:14px}
    #rx-tabbar button{background:#F1F5F9;color:#34495E;border:1px solid #CBD5E1;border-radius:4px;padding:8px 13px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;transition:background .15s,color .15s}
    #rx-tabbar button:hover{background:#E2E8F0}
    #rx-tabbar button.active{background:var(--rx-blue);color:#fff;border-color:var(--rx-blue)}
    #rx-pane{display:none}
    #rx-pane.active{display:block}

    /* Panels */
    #pharmacy .panel{background:#fff;border:1px solid var(--rx-line);border-radius:5px;margin-bottom:14px;overflow:hidden}
    #pharmacy .panel-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:11px 14px;border-bottom:1px solid var(--rx-line);background:#F8FAFC}
    #pharmacy .panel-head h3{margin:0;font-size:11.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--rx-blue);display:flex;align-items:center;gap:7px}
    #pharmacy .panel-body{padding:13px 14px}

    /* Toolbar */
    .rx-search{display:flex;align-items:center;gap:6px;background:#fff;border:1px solid #CBD5E1;border-radius:4px;padding:0 9px}
    .rx-search svg{width:13px;height:13px;fill:none;stroke:var(--rx-mute);stroke-width:2;flex-shrink:0}
    .rx-search input{border:none;outline:none;padding:6px 2px;font-size:11.5px;font-family:inherit;width:200px;background:transparent}

    /* Tables */
    #rx-table-wrap{overflow-x:auto}
    /* Every table on the page shares this styling, so it is selected by class
       rather than by a single id - ids must stay unique. */
    #pharmacy table.rx-tbl{width:100%;border-collapse:collapse;font-size:11.5px}
    #pharmacy table.rx-tbl thead th{background:#F1F5F9;color:#475569;font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;text-align:left;padding:8px 10px;border-bottom:2px solid var(--rx-line);white-space:nowrap}
    #pharmacy table.rx-tbl tbody td{padding:8px 10px;border-bottom:1px solid #EDF2F7;vertical-align:top;color:#334155}
    #pharmacy table.rx-tbl tbody tr:hover{background:#F8FAFC}
    #pharmacy table.rx-tbl .drug{font-weight:700;color:var(--rx-ink)}
    #pharmacy table.rx-tbl .sub{font-size:9.5px;color:var(--rx-mute);margin-top:2px}
    #pharmacy table.rx-tbl .num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
    #pharmacy table.rx-tbl .warnq{color:#DC2626;font-weight:700}

    /* Status pills */
    #pharmacy .pill{display:inline-block;padding:2px 8px;border-radius:9px;font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;border:1px solid;white-space:nowrap}
    #pharmacy .pill.amber{background:#FFFBEB;color:#B45309;border-color:#FDE68A}
    #pharmacy .pill.green{background:#ECFDF5;color:#047857;border-color:#A7F3D0}
    #pharmacy .pill.grey{background:#F1F5F9;color:#475569;border-color:#CBD5E1}
    #pharmacy .pill.red{background:#FEF2F2;color:#B91C1C;border-color:#FECACA}

    /* Allergies are a safety flag, not decoration - always shown in red. */
    #pharmacy .allergy{color:#B91C1C;font-weight:700}
    #pharmacy .none{color:#94A3B8;font-style:italic}

    /* Buttons */
    #pharmacy .rx-btn{border:none;border-radius:4px;padding:6px 13px;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;transition:filter .15s}
    #pharmacy .rx-btn.blue{background:var(--rx-blue);color:#fff}
    #pharmacy .rx-btn.blue:hover{filter:brightness(.88)}
    #pharmacy .rx-btn.grey{background:#E2E8F0;color:#334155;border:1px solid #CBD5E1}
    #pharmacy .rx-btn.grey:hover{background:#CBD5E1}
    #pharmacy .rx-btn:disabled{opacity:.5;cursor:not-allowed}

    /* Empty / loading states */
    #pharmacy .empty{padding:26px 14px;text-align:center;color:var(--rx-mute);font-size:11.5px;font-style:italic}

    /* Clinical entry cards (records tab) */
    #rx-records .entry{border:1px solid var(--rx-line);border-left:3px solid var(--rx-blue);border-radius:4px;padding:10px 12px;margin-bottom:9px;background:#F8FAFC}
    #rx-records .entry .top{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;font-size:10px;color:var(--rx-mute);border-bottom:1px solid var(--rx-line);padding-bottom:6px;margin-bottom:7px}
    #rx-records .entry .top b{color:var(--rx-ink);font-size:11px}
    #rx-records .grid{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-bottom:7px}
    #rx-records .fld .k{display:block;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--rx-blue);margin-bottom:2px}
    #rx-records .fld .v{font-size:11.5px;color:#334155;white-space:pre-wrap;line-height:1.5}

    /* Dispense dialog */
    #rx-dispense-modal .modal-content{max-width:540px}
    #rx-dispense-modal .modal-header{background:linear-gradient(135deg,#1B659D,#0F4C7C);color:#fff;padding:13px 18px}
    #rx-dispense-modal .modal-header h3{color:#fff;margin:0;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.4px}
    #rx-dispense-modal .modal-body{padding:16px 18px;background:#fff}
    #rx-dispense-modal .modal-footer{background:#F8FAFC;padding:11px 18px;display:flex;justify-content:flex-end;gap:9px}
    #rx-dispense-modal label{display:block;font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#475569;margin-bottom:4px}
    #rx-dispense-modal input,#rx-dispense-modal select,#rx-dispense-modal textarea{width:100%;padding:7px 9px;border:1px solid #CBD5E1;border-radius:4px;font-size:12px;font-family:inherit;background:#F8FAFC;outline:none}
    #rx-dispense-modal input:focus,#rx-dispense-modal textarea:focus{border-color:var(--rx-blue);background:#fff}
    #rx-dispense-modal .hint{font-size:10px;color:var(--rx-mute);margin-top:4px}
    #rx-dispense-modal .rx-line{display:flex;justify-content:space-between;gap:10px;font-size:11.5px;padding:5px 0;border-bottom:1px dotted #CBD5E1}
    #rx-dispense-modal .rx-line .k{color:var(--rx-mute)}
    #rx-dispense-modal .rx-line .v{font-weight:700;color:var(--rx-ink);text-align:right}
    #rx-dispense-modal .caps{margin-top:9px;padding:8px 10px;background:#FEF2F2;border:1px solid #FECACA;border-radius:4px;font-size:10.5px;color:#B91C1C;display:flex;align-items:center;gap:7px}

    /* Requisition form */
    #rx-req-form .fgrid{display:grid;grid-template-columns:1fr 1fr;gap:11px;margin-bottom:11px}
    #rx-req-form label{display:block;font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#475569;margin-bottom:4px}
    #rx-req-form select,#rx-req-form input,#rx-req-form textarea{width:100%;padding:8px 10px;border:1px solid #CBD5E1;border-radius:4px;font-size:12px;font-family:inherit;background:#F8FAFC;outline:none}
    #rx-req-form select:focus,#rx-req-form input:focus,#rx-req-form textarea:focus{border-color:var(--rx-blue);background:#fff}
    #rx-req-form .reqline{display:flex;gap:8px;align-items:center;padding:7px 9px;border:1px solid var(--rx-line);border-radius:4px;margin-bottom:6px;background:#F8FAFC}
    #rx-req-form .reqline select{flex:1}
    #rx-req-form .reqline input{width:88px}
    #rx-req-form .reqline .stk{font-size:10px;color:var(--rx-mute);white-space:nowrap;min-width:78px}
    #rx-req-form .reqline .rm{background:#FEE2E2;color:#B91C1C;border:none;border-radius:4px;padding:6px 9px;cursor:pointer;font-size:11px;font-family:inherit}

    @media (max-width:760px){
        #rx-records .grid,#rx-req-form .fgrid{grid-template-columns:1fr}
        .rx-search input{width:130px}
    }
</style>

<div id="pharmacy" class="container-fluid">

    <!-- Header banner -->
    <div id="rx-banner">
        <div>
            <h5><i class="fa-solid fa-pills"></i> Pharmacy Management System</h5>
            <div class="sub">Dispense medications, review doctor prescriptions, manage drug inventories and issue store requisitions.</div>
        </div>
        <div class="portal"><i class="fa-solid fa-house-medical"></i> Pharmacist Portal</div>
    </div>

    <!-- Stats -->
    <div id="rx-stats">
        <div class="s warn"><div class="lbl">Awaiting Dispense</div><div class="val" id="rx-stat-pending">&mdash;</div><div class="sub">prescriptions on the queue</div></div>
        <div class="s good"><div class="lbl">Issued Today</div><div class="val" id="rx-stat-issued">&mdash;</div><div class="sub">dispensed in the last 24h</div></div>
        <div class="s"><div class="lbl">Units In Stock</div><div class="val" id="rx-stat-units">&mdash;</div><div class="sub">across all stocked items</div></div>
        <div class="s bad"><div class="lbl">Out Of Stock</div><div class="val" id="rx-stat-oos">&mdash;</div><div class="sub">items needing restock</div></div>
    </div>

    <!-- Sub-tabs -->
    <div id="rx-tabbar">
        <button type="button" class="active" data-rx-tab="dispense"><i class="fa-solid fa-prescription"></i> Prescribed Drugs / Dispense</button>
        <button type="button" data-rx-tab="patients"><i class="fa-solid fa-users"></i> Patient Profiles</button>
        <button type="button" data-rx-tab="records"><i class="fa-solid fa-file-medical"></i> Doctor's Entries &amp; Records</button>
        <button type="button" data-rx-tab="drugs"><i class="fa-solid fa-capsules"></i> Drugs Setup (Inventory)</button>
        <button type="button" data-rx-tab="requisitions"><i class="fa-solid fa-boxes-stacked"></i> Requisitions (Stores)</button>
    </div>

    <!-- ===================== TAB 1: DISPENSE ===================== -->
    <div id="rx-pane-dispense" class="active" data-rx-pane="dispense">
        <div class="panel">
            <div class="panel-head">
                <h3><i class="fa-solid fa-prescription"></i> Pending Doctor Prescriptions for Dispensing</h3>
                <div class="d-flex gap-2">
                    <div class="rx-search" id="rx-search-queue">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                        <input type="text" id="rx-queue-search" placeholder="Patient, hospital no. or drug...">
                    </div>
                    <button type="button" class="rx-btn grey" id="rx-queue-refresh"><i class="fa-solid fa-rotate"></i> Refresh</button>
                </div>
            </div>
            <div class="rx-table-wrap" id="rx-queue-wrap">
                <table class="rx-tbl">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Prescribed Medication &amp; Dosage</th>
                            <th>Prescribing Doctor</th>
                            <th class="num">Qty</th>
                            <th class="num">In Stock</th>
                            <th>Status</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="rx-queue-body">
                        <tr><td colspan="7"><div class="empty">Loading prescriptions...</div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- recently issued -->
        <div class="panel">
            <div class="panel-head">
                <h3><i class="fa-solid fa-circle-check"></i> Recently Dispensed</h3>
            </div>
            <div class="rx-table-wrap">
                <table class="rx-tbl">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Drug</th>
                            <th class="num">Issued</th>
                            <th>Prescribed By</th>
                            <th>Dispensed</th>
                        </tr>
                    </thead>
                    <tbody id="rx-issued-body">
                        <tr><td colspan="5"><div class="empty">Loading...</div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================== TAB 2: PATIENTS ===================== -->
    <div id="rx-pane-patients" data-rx-pane="patients">
        <div class="panel">
            <div class="panel-head">
                <h3><i class="fa-solid fa-users"></i> Patient Profiles Directory</h3>
                <div class="d-flex gap-2">
                    <div class="rx-search" id="rx-search-patients">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                        <input type="text" id="rx-patient-search" placeholder="Name, hospital no. or phone...">
                    </div>
                </div>
            </div>
            <div class="rx-table-wrap">
                <table class="rx-tbl">
                    <thead>
                        <tr>
                            <th>Hospital No.</th>
                            <th>Patient Name</th>
                            <th>Gender / Age</th>
                            <th>NHIS No.</th>
                            <th>Phone</th>
                            <th>Allergies</th>
                        </tr>
                    </thead>
                    <tbody id="rx-patient-body">
                        <tr><td colspan="6"><div class="empty">Loading patients...</div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================== TAB 3: RECORDS ===================== -->
    <div id="rx-pane-records" data-rx-pane="records">
        <div class="panel">
            <div class="panel-head">
                <h3><i class="fa-solid fa-file-medical"></i> Doctor's Entries, Diagnosis &amp; Clinical Notes</h3>
                <div class="d-flex gap-2">
                    <select id="rx-note-type" style="padding:6px 9px;border:1px solid #CBD5E1;border-radius:4px;font-size:11px;font-family:inherit;">
                        <option value="">All entry types</option>
                        <option value="OPD_NOTE">OPD Note</option>
                        <option value="IPD_NOTE">IPD Round Note</option>
                        <option value="DISCHARGE_SUMMARY">Discharge Summary</option>
                    </select>
                    <button type="button" class="rx-btn grey" id="rx-records-refresh"><i class="fa-solid fa-rotate"></i> Refresh</button>
                </div>
            </div>
            <div class="panel-body" id="rx-records">
                <div class="empty">Loading clinical entries...</div>
            </div>
        </div>
    </div>

    <!-- ===================== TAB 4: DRUGS ===================== -->
    <div id="rx-pane-drugs" data-rx-pane="drugs">
        <div class="panel">
            <div class="panel-head">
                <h3><i class="fa-solid fa-capsules"></i> Pharmacy Drug Inventory Setup</h3>
                <div class="d-flex gap-2">
                    <div class="rx-search" id="rx-search-drugs">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                        <input type="text" id="rx-drug-search" placeholder="Code or drug name...">
                    </div>
                    <button type="button" class="rx-btn blue" id="rx-goto-inventory"><i class="fa-solid fa-plus"></i> Manage Inventory</button>
                </div>
            </div>
            <div class="rx-table-wrap">
                <table class="rx-tbl">
                    <thead>
                        <tr>
                            <th>Drug Code</th>
                            <th>Drug Name &amp; Strength</th>
                            <th>Store</th>
                            <th>Category</th>
                            <th class="num">Unit Price (GH&#8373;)</th>
                            <th class="num">Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="rx-drug-body">
                        <tr><td colspan="7"><div class="empty">Loading inventory...</div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================== TAB 5: REQUISITIONS ===================== -->
    <div id="rx-pane-requisitions" data-rx-pane="requisitions">
        <div class="panel">
            <div class="panel-head">
                <h3><i class="fa-solid fa-boxes-stacked"></i> Make Requisition Request (Medical or General Stores)</h3>
            </div>
            <div class="panel-body">
                <form id="rx-req-form" onsubmit="return false;">
                    <div class="fgrid">
                        <div>
                            <label>Target Store Department</label>
                            <select id="rx-req-store">
                                <option value="MEDICAL">Medical Stores (Drugs &amp; Consumables)</option>
                                <option value="GENERAL">General Stores (Stationery &amp; Equipment)</option>
                            </select>
                        </div>
                        <div>
                            <label>Requesting Department</label>
                            <select id="rx-req-dept"><option value="">My department (default)</option></select>
                        </div>
                    </div>

                    <label>Items Requested</label>
                    <div id="rx-req-lines"></div>
                    <button type="button" class="rx-btn grey" id="rx-req-add" style="margin-bottom:11px;"><i class="fa-solid fa-plus"></i> Add Item</button>

                    <div style="margin-bottom:11px;">
                        <label>Justification / Remarks</label>
                        <textarea id="rx-req-remarks" rows="2" placeholder="Reason for this requisition..."></textarea>
                    </div>

                    <div style="text-align:right;">
                        <button type="button" class="rx-btn blue" id="rx-req-submit"><i class="fa-solid fa-paper-plane"></i> Submit Requisition</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3><i class="fa-solid fa-list-check"></i> Requisition Log</h3>
            </div>
            <div class="rx-table-wrap">
                <table class="rx-tbl">
                    <thead>
                        <tr>
                            <th>Req. Code</th>
                            <th>Store</th>
                            <th>Items</th>
                            <th>Requested By</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="rx-req-body">
                        <tr><td colspan="6"><div class="empty">Loading requisitions...</div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Dispense dialog -->
<div class="modal" id="rx-dispense-modal" style="display:none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-pills"></i> Dispense Medication</h3>
                <button type="button" class="modal-close" data-rx-close="1" style="background:none;border:none;color:#fff;font-size:19px;cursor:pointer;line-height:1;">&times;</button>
            </div>
            <div class="modal-body">
                <div id="rx-dispense-summary"></div>

                <div class="fgrid" style="display:grid;grid-template-columns:1fr 1fr;gap:11px;margin-top:13px;">
                    <div>
                        <label>Quantity To Issue *</label>
                        <input type="number" id="rx-dispense-qty" min="1" step="1">
                        <div class="hint" id="rx-dispense-cap"></div>
                    </div>
                    <div>
                        <label>Batch Number</label>
                        <input type="text" id="rx-dispense-batch" placeholder="Defaults to stock on hand">
                    </div>
                </div>
                <div class="fgrid" style="display:grid;grid-template-columns:1fr 1fr;gap:11px;margin-top:11px;">
                    <div>
                        <label>Expiry Date</label>
                        <input type="date" id="rx-dispense-expiry">
                    </div>
                    <div>
                        <label>Notes</label>
                        <input type="text" id="rx-dispense-notes" placeholder="Optional">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="rx-btn grey" data-rx-close="1">Cancel</button>
                <button type="button" class="rx-btn blue" id="rx-dispense-confirm"><i class="fa-solid fa-check"></i> Confirm Dispense</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let rxQueue = [];
    let rxDrugs = [];
    // service_prices holds the price per drug (service_type='drug'), keyed by
    // inventory id. It is fetched separately and merged in, because the price
    // list only names the drug rather than carrying its id on the row we filter by.
    let rxDrugPrices = {};
    let rxReqLines = [];
    let rxDispense = null;
    let rxSearchTimer = null;

    /* ---------- helpers ---------- */
    async function rxFetch(url, options) {
        const res = await fetch(url, options);
        const data = await res.json().catch(() => ({}));
        if (!res.ok || data.success === false) {
            throw new Error(data.error || 'Request failed (' + res.status + ')');
        }
        return data;
    }

    function rxAge(years) {
        if (years === null || years === undefined) return 'age not recorded';
        return years + (years === 1 ? ' yr' : ' yrs');
    }

    function rxTitleCase(v) {
        if (!v) return '';
        return String(v).charAt(0).toUpperCase() + String(v).slice(1);
    }

    /* ---------- tabs ---------- */
    function rxSwitchTab(name) {
        document.querySelectorAll('#rx-tabbar button').forEach(b => {
            b.classList.toggle('active', b.getAttribute('data-rx-tab') === name);
        });
        document.querySelectorAll('[data-rx-pane]').forEach(p => {
            p.classList.toggle('active', p.getAttribute('data-rx-pane') === name);
        });
        // Load on first visit so the tab never shows an empty frame.
        if (name === 'patients' && !document.getElementById('rx-patient-body').dataset.loaded) loadPatients();
        if (name === 'records' && !document.getElementById('rx-records').dataset.loaded) loadRecords();
        if (name === 'drugs' && !document.getElementById('rx-drug-body').dataset.loaded) loadDrugs();
        if (name === 'requisitions') {
            // The item picker needs the stocked list whether or not the Drugs tab
            // has been opened, so it is fetched here too.
            loadReqOptions();
            loadReqDepartments();
            if (!document.getElementById('rx-req-body').dataset.loaded) loadRequisitions();
        }
    }

    /* ---------- tab 1: dispensing queue ---------- */
    async function loadQueue() {
        const body = document.getElementById('rx-queue-body');
        const q = document.getElementById('rx-queue-search').value.trim();
        const url = '/hms/backend/api/prescriptions.php?action=queue'
            + (q ? '&q=' + encodeURIComponent(q) : '');

        try {
            const data = await rxFetch(url);
            rxQueue = data.prescriptions || [];
            renderQueue();
            document.getElementById('rx-stat-pending').textContent = rxQueue.length;
        } catch (err) {
            body.innerHTML = '<tr><td colspan="7"><div class="empty">Could not load the dispensing queue: '
                + escHtml(err.message) + '</div></td></tr>';
        }
    }

    function renderQueue() {
        const body = document.getElementById('rx-queue-body');
        if (!rxQueue.length) {
            body.innerHTML = '<tr><td colspan="7"><div class="empty">Nothing waiting to be dispensed. '
                + 'Prescriptions appear here the moment a doctor writes them.</div></td></tr>';
            return;
        }

        body.innerHTML = rxQueue.map(rx => {
            // A prescription the shelf cannot cover is flagged before anyone clicks.
            const short = Number(rx.quantity_in_stock) < Number(rx.quantity);
            const stock = Number(rx.quantity_in_stock);
            const sig = [rx.dosage, rx.frequency, rx.duration].filter(Boolean).join(' ');

            return '<tr>'
                + '<td><div class="drug">' + escHtml(rxTitleCase(rx.patient_name)) + '</div>'
                    + '<div class="sub">' + escHtml(rx.hospital_number || '') + '</div></td>'
                + '<td><div class="drug">' + escHtml(rx.drug_name) + '</div>'
                    + (rx.generic_name && rx.generic_name !== rx.drug_name
                        ? '<div class="sub">' + escHtml(rx.generic_name) + '</div>' : '')
                    + (sig ? '<div class="sub">' + escHtml(sig) + '</div>' : '')
                    + (rx.instructions ? '<div class="sub">' + escHtml(rx.instructions) + '</div>' : '')
                    + '</td>'
                + '<td>' + escHtml(rx.doctor_name || 'â€”') + '</td>'
                + '<td class="num"><b>' + Number(rx.quantity) + '</b> ' + escHtml(rx.unit || '') + '</td>'
                + '<td class="num' + (short ? ' warnq' : '') + '">' + stock + '</td>'
                + '<td>' + (short
                    ? '<span class="pill red">Short Stock</span>'
                    : '<span class="pill amber">Pending Dispense</span>')
                    + (rx.waiting_days > 0 ? '<div class="sub">waiting ' + rx.waiting_days + 'd</div>' : '')
                    + '</td>'
                + '<td style="text-align:center;">'
                    + '<button type="button" class="rx-btn blue" data-rx-dispense="' + rx.id + '">Dispense</button>'
                    + '</td>'
                + '</tr>';
        }).join('');
    }

    async function loadIssued() {
        const body = document.getElementById('rx-issued-body');
        try {
            const data = await rxFetch('/hms/backend/api/prescriptions.php?action=dispensed');
            const rows = data.prescriptions || [];
            if (!rows.length) {
                body.innerHTML = '<tr><td colspan="5"><div class="empty">Nothing has been dispensed yet.</div></td></tr>';
                return;
            }
            body.innerHTML = rows.map(rx =>
                '<tr>'
                + '<td><div class="drug">' + escHtml(rxTitleCase(rx.patient_name)) + '</div>'
                    + '<div class="sub">' + escHtml(rx.hospital_number || '') + '</div></td>'
                + '<td>' + escHtml(rx.drug_name) + '</td>'
                + '<td class="num">' + Number(rx.quantity) + ' ' + escHtml(rx.unit || '') + '</td>'
                + '<td>' + escHtml(rx.doctor_name || 'â€”') + '</td>'
                + '<td>' + escHtml(fmtDateTime(rx.dispensed_at || rx.prescribed_at)) + '</td>'
                + '</tr>').join('');
        } catch (err) {
            body.innerHTML = '<tr><td colspan="5"><div class="empty">' + escHtml(err.message) + '</div></td></tr>';
        }
    }

    function openDispense(id) {
        const rx = rxQueue.find(x => String(x.id) === String(id));
        if (!rx) return;
        rxDispense = rx;

        // The ceiling is the smaller of "what the doctor prescribed" and "what is on
        // the shelf" - shown up front so the pharmacist is not offered an impossible amount.
        const prescribed = Math.max(Number(rx.quantity) || 1, 1);
        const stock = Number(rx.quantity_in_stock) || 0;
        const cap = Math.min(prescribed, stock);

        const rows = [
            ['Patient', rxTitleCase(rx.patient_name) + (rx.hospital_number ? ' (' + rx.hospital_number + ')' : '')],
            ['Drug', rx.drug_name + (rx.generic_name && rx.generic_name !== rx.drug_name ? ' (' + rx.generic_name + ')' : '')],
            ['Dosage', [rx.dosage, rx.frequency, rx.duration].filter(Boolean).join(' Â· ') || 'â€”'],
            ['Prescribed', prescribed + ' ' + (rx.unit || 'unit(s)')],
            ['In Stock', stock + ' ' + (rx.unit || 'unit(s)')],
            ['Prescribing Doctor', rx.doctor_name || 'â€”'],
            ['Prescribed On', fmtDateTime(rx.prescribed_at)]
        ];

        document.getElementById('rx-dispense-summary').innerHTML =
            rows.map(r => '<div class="rx-line"><span class="k">' + escHtml(r[0]) + '</span>'
                + '<span class="v">' + escHtml(r[1]) + '</span></div>').join('')
            + (cap <= 0
                ? '<div class="caps"><i class="fa-solid fa-triangle-exclamation"></i> '
                    + 'There is no stock of this drug. Restock it from Drugs Setup before issuing.</div>'
                : '');

        // Prefill with what can actually be issued, not the raw prescribed figure: if the
        // shelf holds less, prefilling the prescribed amount would fail on submit.
        document.getElementById('rx-dispense-qty').value = cap;
        document.getElementById('rx-dispense-qty').max = cap > 0 ? cap : 1;
        document.getElementById('rx-dispense-qty').disabled = cap <= 0;
        document.getElementById('rx-dispense-cap').textContent = cap > 0
            ? 'Up to ' + cap + ' ' + (rx.unit || 'unit(s)') + ' can be issued against this prescription.'
            : 'No units available.';
        document.getElementById('rx-dispense-batch').value = '';
        document.getElementById('rx-dispense-expiry').value = '';
        document.getElementById('rx-dispense-notes').value = '';
        document.getElementById('rx-dispense-confirm').disabled = cap <= 0;

        document.getElementById('rx-dispense-modal').style.display = 'flex';
    }

    function closeDispense() {
        document.getElementById('rx-dispense-modal').style.display = 'none';
        rxDispense = null;
    }

    async function confirmDispense() {
        if (!rxDispense) return;
        const btn = document.getElementById('rx-dispense-confirm');
        const qty = parseInt(document.getElementById('rx-dispense-qty').value, 10);

        if (!qty || qty < 1) {
            showAlert('Enter how many units to issue.', 'error');
            return;
        }

        btn.disabled = true;
        try {
            const res = await rxFetch('/hms/backend/api/prescriptions.php?action=dispense', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    prescription_id: rxDispense.id,
                    quantity: qty,
                    batch_number: document.getElementById('rx-dispense-batch').value.trim(),
                    expiry_date: document.getElementById('rx-dispense-expiry').value,
                    notes: document.getElementById('rx-dispense-notes').value.trim()
                })
            });

            closeDispense();
            showAlert('Issued ' + qty + ' x ' + rxDispense.drug_name
                + '. ' + res.quantity_in_stock + ' remaining in stock.', 'success');
            await Promise.all([loadQueue(), loadIssued(), loadStats()]);
        } catch (err) {
            showAlert(err.message, 'error');
        } finally {
            btn.disabled = false;
        }
    }

    /* ---------- tab 2: patients ---------- */
    let rxPatients = [];

    async function loadPatients() {
        const body = document.getElementById('rx-patient-body');
        const q = document.getElementById('rx-patient-search').value.trim();
        try {
            const data = await rxFetch('/hms/backend/api/patients.php'
                + (q ? '?search=' + encodeURIComponent(q) : ''));
            rxPatients = data.patients || [];
            body.dataset.loaded = '1';
            renderPatients();
        } catch (err) {
            body.innerHTML = '<tr><td colspan="6"><div class="empty">' + escHtml(err.message) + '</div></td></tr>';
        }
    }

    function renderPatients() {
        const body = document.getElementById('rx-patient-body');
        if (!rxPatients.length) {
            body.innerHTML = '<tr><td colspan="6"><div class="empty">No patients match that search.</div></td></tr>';
            return;
        }

        body.innerHTML = rxPatients.map(p => {
            const name = [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ');
            // Allergies are checked before handing a drug over, so they are never
            // collapsed into a quiet dash when they exist.
            const allergies = p.allergies && String(p.allergies).trim();
            return '<tr>'
                + '<td><b>' + escHtml(p.hospital_number || 'â€”') + '</b></td>'
                + '<td class="drug">' + escHtml(rxTitleCase(name)) + '</td>'
                + '<td>' + escHtml(rxTitleCase(p.gender) || 'â€”') + (p.age !== undefined && p.age !== null
                    ? ', ' + rxAge(p.age) : '') + '</td>'
                + '<td>' + escHtml(p.nhia_number || 'â€”') + '</td>'
                + '<td>' + escHtml(p.phone || 'â€”') + '</td>'
                + '<td>' + (allergies
                    ? '<span class="allergy"><i class="fa-solid fa-triangle-exclamation"></i> '
                        + escHtml(allergies) + '</span>'
                    : '<span class="none">None recorded</span>') + '</td>'
                + '</tr>';
        }).join('');
    }

    /* ---------- tab 3: records ---------- */
    async function loadRecords() {
        const host = document.getElementById('rx-records');
        const type = document.getElementById('rx-note-type').value;
        try {
            const data = await rxFetch('/hms/backend/api/clinical_notes.php'
                + (type ? '?note_type=' + encodeURIComponent(type) : ''));
            const notes = data.notes || [];
            host.dataset.loaded = '1';

            if (!notes.length) {
                host.innerHTML = '<div class="empty">No clinical entries have been written yet.</div>';
                return;
            }

            host.innerHTML = notes.map(n =>
                '<div class="entry">'
                + '<div class="top"><span><b>' + escHtml(rxTitleCase(n.patient_name)) + '</b>'
                    + (n.hospital_number ? ' &middot; ' + escHtml(n.hospital_number) : '')
                    + (n.admission_code ? ' &middot; ' + escHtml(n.admission_code) : '') + '</span>'
                    + '<span>' + escHtml(fmtDateTime(n.created_at)) + ' &middot; '
                    + escHtml(n.doctor_name || '') + '</span></div>'
                + '<div class="grid">'
                    + '<div class="fld"><span class="k">Entry Type</span><span class="v">'
                        + escHtml(String(n.note_type || '').replace(/_/g, ' ')) + '</span></div>'
                    + '<div class="fld"><span class="k">Admitting Doctor</span><span class="v">'
                        + escHtml(n.admitting_doctor || n.doctor_name || 'â€”') + '</span></div>'
                + '</div>'
                + '<div class="fld"><span class="k">Clinical Note</span><span class="v">'
                    + escHtml(n.clinical_note || 'No note recorded') + '</span></div>'
                + '</div>').join('');
        } catch (err) {
            host.innerHTML = '<div class="empty">' + escHtml(err.message) + '</div>';
        }
    }

    /* ---------- tab 4: drugs ---------- */
    async function loadDrugs() {
        const body = document.getElementById('rx-drug-body');
        const q = document.getElementById('rx-drug-search').value.trim();
        try {
            // inventory.php filters on `q`, not `search`.
            const url = '/hms/backend/api/inventory.php' + (q ? '?q=' + encodeURIComponent(q) : '');
            const data = await rxFetch(url);
            rxDrugs = data.items || data.inventory || [];
            body.dataset.loaded = '1';
            await loadDrugPrices();
            renderDrugs();
        } catch (err) {
            body.innerHTML = '<tr><td colspan="7"><div class="empty">' + escHtml(err.message) + '</div></td></tr>';
        }
    }

    async function loadDrugPrices() {
        try {
            const data = await rxFetch('/hms/backend/api/prices.php?service_type=drug');
            const next = {};
            (data.prices || []).forEach(p => {
                // The most recently effective price wins; several may exist per drug.
                if (next[p.service_id] === undefined || p.effective_date > next[p.service_id].effective_date) {
                    next[p.service_id] = p;
                }
            });
            rxDrugPrices = next;
        } catch (err) {
            rxDrugPrices = {};
        }
    }

    function renderDrugs() {
        const body = document.getElementById('rx-drug-body');
        if (!rxDrugs.length) {
            body.innerHTML = '<tr><td colspan="7"><div class="empty">No stocked items match.</div></td></tr>';
            return;
        }

        body.innerHTML = rxDrugs.map(d => {
            const stock = Number(d.quantity_in_stock);
            const low = stock <= Number(d.reorder_level);
            // A drug with no service_prices row has no price to show. Saying "not set"
            // is honest; inventing a figure would be wrong on a billing screen.
            const priced = rxDrugPrices[d.id];
            const priceCell = priced
                ? Number(priced.price).toFixed(2) + ' <span class="sub">' + escHtml(priced.currency || 'GHS') + '</span>'
                : '<span class="none">not set</span>';
            return '<tr>'
                + '<td><b>' + escHtml(d.drug_code || 'â€”') + '</b></td>'
                + '<td class="drug">' + escHtml(d.drug_name) + '</td>'
                + '<td>' + escHtml(d.store_type === 'GENERAL' ? 'General' : 'Medical') + '</td>'
                + '<td>' + escHtml(d.category || 'â€”') + '</td>'
                + '<td class="num">' + priceCell + '</td>'
                + '<td class="num' + (low ? ' warnq' : '') + '">' + stock + ' ' + escHtml(d.unit || '') + '</td>'
                + '<td>' + (!d.is_active
                    ? '<span class="pill grey">Inactive</span>'
                    : stock === 0
                        ? '<span class="pill red">Out of Stock</span>'
                        : low
                            ? '<span class="pill amber">Low Stock</span>'
                            : '<span class="pill green">In Stock</span>') + '</td>'
                + '</tr>';
        }).join('');
    }

    /* ---------- tab 5: requisitions ---------- */
    async function loadRequisitions() {
        const body = document.getElementById('rx-req-body');
        try {
            const data = await rxFetch('/hms/backend/api/inventory.php?action=requisitions');
            const rows = data.requisitions || [];
            body.dataset.loaded = '1';

            if (!rows.length) {
                body.innerHTML = '<tr><td colspan="6"><div class="empty">No requisitions have been raised yet.</div></td></tr>';
                return;
            }

            body.innerHTML = rows.map(r => {
                const items = (r.items || []).map(i =>
                    escHtml(i.drug_name) + ' &times; ' + Number(i.requested_qty)).join('<br>');
                const cls = r.status === 'Approved' ? 'green' : (r.status === 'Rejected' ? 'red' : 'amber');
                return '<tr>'
                    + '<td><b>' + escHtml(r.req_code) + '</b></td>'
                    + '<td>' + escHtml(r.store_type === 'GENERAL' ? 'General' : 'Medical') + '</td>'
                    + '<td>' + (items || '<span class="none">No items</span>') + '</td>'
                    + '<td>' + escHtml(r.requested_by_name || 'â€”') + '</td>'
                    + '<td>' + escHtml(fmtDateTime(r.created_at)) + '</td>'
                    + '<td><span class="pill ' + cls + '">' + escHtml(r.status) + '</span></td>'
                    + '</tr>';
            }).join('');
        } catch (err) {
            body.innerHTML = '<tr><td colspan="6"><div class="empty">' + escHtml(err.message) + '</div></td></tr>';
        }
    }

    function renderReqLines() {
        const host = document.getElementById('rx-req-lines');
        if (!rxReqLines.length) {
            host.innerHTML = '<div class="empty" style="padding:12px;">No items added yet.</div>';
            return;
        }

        const storeType = document.getElementById('rx-req-store').value;
        // Only active items of the chosen store can be requested, so the picker
        // cannot offer something the stores department would reject.
        const options = rxDrugs.filter(d => d.is_active && (d.store_type || 'MEDICAL') === storeType);

        host.innerHTML = rxReqLines.map((line, i) => {
            const chosen = options.find(o => String(o.id) === String(line.item_id));
            const stock = chosen ? Number(chosen.quantity_in_stock) : null;
            return '<div class="reqline">'
                + '<select data-rx-req-item="' + i + '">'
                    + '<option value="">Select an item...</option>'
                    + options.map(o => '<option value="' + o.id + '"'
                        + (String(o.id) === String(line.item_id) ? ' selected' : '') + '>'
                        + escHtml(o.drug_name) + ' (' + escHtml(o.drug_code || 'no code') + ')</option>').join('')
                    + '</select>'
                + '<input type="number" min="1" step="1" value="' + escHtml(line.requested_qty) + '" data-rx-req-qty="' + i + '">'
                + '<span class="stk">' + (stock === null ? 'â€”' : stock + ' in stock') + '</span>'
                + '<button type="button" class="rm" data-rx-req-rm="' + i + '" title="Remove"><i class="fa-solid fa-xmark"></i></button>'
                + '</div>';
        }).join('');
    }

    function addReqLine() {
        rxReqLines.push({ item_id: '', requested_qty: 1 });
        renderReqLines();
    }

    async function loadReqOptions() {
        if (rxDrugs.length) return;
        try {
            const data = await rxFetch('/hms/backend/api/inventory.php');
            rxDrugs = data.items || data.inventory || [];
            if (rxReqLines.length) renderReqLines();
        } catch (err) {
            showAlert('Could not load store items: ' + err.message, 'error');
        }
    }

    async function submitRequisition() {
        const lines = rxReqLines
            .map(l => ({ item_id: l.item_id, requested_qty: l.requested_qty }))
            .filter(l => l.item_id);

        if (!lines.length) {
            showAlert('Add at least one stock item before submitting.', 'error');
            return;
        }

        const btn = document.getElementById('rx-req-submit');
        btn.disabled = true;
        try {
            const res = await rxFetch('/hms/backend/api/inventory.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    store_type: document.getElementById('rx-req-store').value,
                    department_id: document.getElementById('rx-req-dept').value || null,
                    item_id: lines.map(l => l.item_id),
                    requested_qty: lines.map(l => l.requested_qty),
                    remarks: document.getElementById('rx-req-remarks').value.trim()
                })
            });

            showAlert('Requisition ' + res.req_code + ' submitted for approval.', 'success');
            rxReqLines = [];
            document.getElementById('rx-req-remarks').value = '';
            document.getElementById('rx-req-body').dataset.loaded = '';
            renderReqLines();
            await loadRequisitions();
        } catch (err) {
            showAlert(err.message, 'error');
        } finally {
            btn.disabled = false;
        }
    }

    async function loadReqDepartments() {
        const sel = document.getElementById('rx-req-dept');
        // Guard against double-loading: the tab can be revisited and the list is
        // appended as options, so a second pass would duplicate every row.
        if (sel.dataset.loaded) return;
        sel.dataset.loaded = '1';
        try {
            const data = await rxFetch('/hms/backend/api/users.php?action=departments');
            (data.departments || []).forEach(d => {
                const o = document.createElement('option');
                o.value = d.id;
                o.textContent = d.name;
                sel.appendChild(o);
            });
        } catch (err) {
            // A missing department list only affects the optional field, so it is not
            // surfaced as an error - the server falls back to the requester's own.
        }
    }

    /* ---------- stats ---------- */
    async function loadStats() {
        try {
            const data = await rxFetch('/hms/backend/api/inventory.php?action=stats');
            document.getElementById('rx-stat-units').textContent = data.total_units;
            document.getElementById('rx-stat-oos').textContent = data.out_of_stock;
        } catch (err) { /* stats are advisory; leave the placeholders */ }

        // "Issued today" needs the issued log, so it is derived rather than invented.
        try {
            const data = await rxFetch('/hms/backend/api/prescriptions.php?action=dispensed');
            const since = Date.now() - 86400000;
            const today = (data.prescriptions || []).filter(r => {
                const t = Date.parse(String(r.dispensed_at || '').replace(' ', 'T'));
                return t >= since;
            }).length;
            document.getElementById('rx-stat-issued').textContent = today;
        } catch (err) { /* as above */ }
    }

    /* ---------- wiring ---------- */
    // The shell injects this fragment after DOMContentLoaded has already fired and
    // then calls init<Page>() explicitly, so the wiring cannot live in a
    // DOMContentLoaded listener - it would never run in the real app.
    window.initPharmacy = function () {
        document.getElementById('rx-tabbar').addEventListener('click', function (e) {
            const btn = e.target.closest('[data-rx-tab]');
            if (btn) rxSwitchTab(btn.getAttribute('data-rx-tab'));
        });

        document.getElementById('rx-queue-search').addEventListener('input', function () {
            clearTimeout(rxSearchTimer);
            rxSearchTimer = setTimeout(loadQueue, 300);
        });
        document.getElementById('rx-queue-refresh').addEventListener('click', function () {
            loadQueue(); loadIssued();
        });

        // The queue body is re-rendered on every load, so its Dispense buttons are
        // handled by delegation on the table body rather than bound one by one.
        document.getElementById('rx-queue-body').addEventListener('click', function (e) {
            const btn = e.target.closest('[data-rx-dispense]');
            if (btn) openDispense(btn.getAttribute('data-rx-dispense'));
        });

        document.getElementById('rx-patient-search').addEventListener('input', function () {
            clearTimeout(rxSearchTimer);
            rxSearchTimer = setTimeout(loadPatients, 300);
        });

        document.getElementById('rx-note-type').addEventListener('change', loadRecords);
        document.getElementById('rx-records-refresh').addEventListener('click', loadRecords);

        document.getElementById('rx-drug-search').addEventListener('input', function () {
            clearTimeout(rxSearchTimer);
            rxSearchTimer = setTimeout(loadDrugs, 300);
        });
        // Editing stock is the Inventory module's job - this tab only reads.
        document.getElementById('rx-goto-inventory').addEventListener('click', function () {
            navigateTo('inventory_management');
        });

        document.getElementById('rx-req-add').addEventListener('click', function () {
            addReqLine();
        });
        document.getElementById('rx-req-store').addEventListener('change', function () {
            // Switching store invalidates any picked items, which belong to the other store.
            rxReqLines = rxReqLines.map(l => ({ item_id: '', requested_qty: l.requested_qty }));
            renderReqLines();
        });
        document.getElementById('rx-req-submit').addEventListener('click', submitRequisition);
        document.getElementById('rx-req-lines').addEventListener('change', function (e) {
            const itemSel = e.target.closest('[data-rx-req-item]');
            const qtyIn = e.target.closest('[data-rx-req-qty]');
            if (itemSel) {
                rxReqLines[Number(itemSel.getAttribute('data-rx-req-item'))].item_id = itemSel.value;
                renderReqLines();
            } else if (qtyIn) {
                const i = Number(qtyIn.getAttribute('data-rx-req-qty'));
                rxReqLines[i].requested_qty = Math.max(parseInt(qtyIn.value, 10) || 1, 1);
            }
        });
        document.getElementById('rx-req-lines').addEventListener('click', function (e) {
            const rm = e.target.closest('[data-rx-req-rm]');
            if (!rm) return;
            rxReqLines.splice(Number(rm.getAttribute('data-rx-req-rm')), 1);
            renderReqLines();
        });

        document.getElementById('rx-dispense-confirm').addEventListener('click', confirmDispense);
        document.querySelectorAll('#rx-dispense-modal [data-rx-close]').forEach(b => {
            b.addEventListener('click', closeDispense);
        });

        loadQueue();
        loadIssued();
        loadStats();
    };
})();
</script>