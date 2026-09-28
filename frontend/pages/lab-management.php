<?php
// Laboratory & Investigations Dashboard (full-width EHMS module page).
// Server-side prologue: logged-in staff name/role, and the laboratory
// investigation catalogue (category -> test + price) used by the Entries Form.
require_once __DIR__ . '/../../backend/config/config.php';
$__labStaff = getCurrentUserName() ?: 'STAFF';
$__labRole  = getCurrentUserRole() ?: 'STAFF';

// ---- Laboratory investigation catalogue (single source of truth) -------------
// Edit prices here; the "Lab to be Done" dropdown and the auto-generated
// price field are both rendered from this list.
$__labCategories = [
    'hematology'   => 'Hematology',
    'parasitology' => 'Parasitology & Serology',
    'biochemistry' => 'Biochemistry',
    'microbiology' => 'Microbiology',
];
$__labCatalog = [
    'hematology' => [
        ['fbc',         'Full Blood Count (FBC)',                    50.00],
        ['hb',          'Hemoglobin (Hb) Level',                      20.00],
        ['blood_group', 'Blood Grouping & Rh Factor',                 30.00],
        ['sickling',    'Sickling Test / Hb Electrophoresis',         25.00],
        ['esr',         'Erythrocyte Sedimentation Rate (ESR)',       30.00],
    ],
    'parasitology' => [
        ['malaria_rdt', 'Malaria RDT',                                15.00],
        ['malaria_mps', 'Malaria Microscopy (MPS)',                   25.00],
        ['widal',       'Widal Test (Typhoid)',                       30.00],
        ['hbsag',       'Hepatitis B Screening (HBsAg)',              35.00],
        ['hiv',         'HIV Screening Test',                         20.00],
        ['vdrl',        'Syphilis (VDRL/RPR)',                        20.00],
    ],
    'biochemistry' => [
        ['fbs',   'Fasting Blood Sugar (FBS)',                       15.00],
        ['rbs',   'Random Blood Sugar (RBS)',                        15.00],
        ['lft',   'Liver Function Tests (LFTs)',                    120.00],
        ['kft',   'Kidney Function Tests (KFTs)',                   130.00],
        ['lipid', 'Lipid Profile',                                   110.00],
    ],
    'microbiology' => [
        ['urine_re', 'Urine Routine Examination',                     20.00],
        ['urine_cs', 'Urine Culture & Sensitivity',                   80.00],
        ['stool_re', 'Stool Routine Examination',                     20.00],
        ['hvs_cs',   'High Vaginal Swab (HVS) C&S',                  90.00],
    ],
];
$__labPrice = function ($code) use ($__labCatalog) {
    foreach ($__labCatalog as $items) {
        foreach ($items as $it) { if ($it[0] === $code) return $it[2]; }
    }
    return 0.0;
};
?>
<style>
/* ================= LAB & INVESTIGATIONS : FULL-WIDTH MODULE =================
   Scoped under #lab-page. No Bootstrap — every utility defined here.
   Font Awesome is available (the shell loads
   frontend/assets/fontawesome/css/all.min.css). No inline footer: the shell
   auto-appends the system support banner to every module page. */
#lab-page{background:#dce7f2;color:#333;font-size:13px;min-height:100vh;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;padding-bottom:30px}
#lab-page *,#lab-page *::before,#lab-page *::after{box-sizing:border-box}

/* ---- TOP BAR (HMS - HEALTHCARE MANAGEMENT SYSTEM branding) ---- */
#lab-page .lab-topbar{background-color:#0b5fa5;color:#fff;padding:8px 20px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 5px rgba(0,0,0,0.15);flex-wrap:wrap;gap:8px}
#lab-page .lab-topbar .title-group{display:flex;align-items:center;gap:10px}
#lab-page .lab-topbar .title-group svg{width:22px;height:22px;fill:none;stroke:#ffffff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#lab-page .lab-topbar-title{font-size:16px;font-weight:bold;letter-spacing:.5px;text-transform:uppercase}
#lab-page .lab-topbar-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
#lab-page .lab-topbar-right > span{font-size:11px}
#lab-page .lab-nav-btn{background-color:#0088cc;color:#fff;border:none;padding:5px 12px;font-size:11px;font-weight:bold;border-radius:3px;cursor:pointer;text-decoration:none;font-family:inherit;display:inline-flex;align-items:center;gap:5px}
#lab-page .lab-nav-btn:hover{background-color:#006699}

/* ---- MODULE HEADER + ACTIONS ---- */
#lab-page .lab-wrap{max-width:1240px;margin:0 auto;padding:12px 16px 16px}
#lab-page .lab-modhead{background:linear-gradient(135deg,#0D47A1,#0072BC);color:#fff;padding:9px 16px;border-radius:6px 6px 0 0;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;box-shadow:0 2px 6px rgba(13,71,161,.22)}
#lab-page .lab-modhead .left{display:flex;align-items:center;gap:10px}
#lab-page .lab-modhead .left svg{width:22px;height:22px;fill:none;stroke:#ffffff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#lab-page .lab-modhead h2{margin:0;font-size:15px;font-weight:800;text-transform:uppercase;letter-spacing:.6px}
#lab-page .lab-modhead .actions{display:flex;gap:8px;flex-wrap:wrap}
#lab-page .lab-h-btn{border:none;border-radius:3px;cursor:pointer;font-weight:bold;font-size:11px;text-transform:uppercase;letter-spacing:.4px;display:inline-flex;align-items:center;gap:6px;padding:6px 14px;font-family:inherit;transition:background .15s,filter .15s}
#lab-page .lab-h-btn svg{width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#lab-page .lab-h-btn.secondary{background-color:#5a6b7c;color:#fff}
#lab-page .lab-h-btn.secondary:hover{background-color:#48596a}
#lab-page .lab-h-btn.light{background-color:#fff;color:#0D47A1}
#lab-page .lab-h-btn.light:hover{filter:brightness(.96)}

/* ---- PANELS ---- */
#lab-page .lab-panel{background:#fff;border:1px solid #b2c8de;border-top:none;border-radius:0 0 6px 6px;overflow:hidden;box-shadow:0 3px 8px rgba(0,0,0,.06)}
#lab-page .lab-panel + .lab-panel{margin-top:14px}
#lab-page .lab-panel-head{background-color:#0b5fa5;color:#fff;font-weight:bold;font-size:12px;padding:9px 14px;text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
#lab-page .lab-panel-head.dark{background-color:#1f2933}
#lab-page .lab-panel-head .head-left{display:flex;align-items:center;gap:8px}
#lab-page .lab-panel-head svg{width:17px;height:17px;fill:none;stroke:#ffffff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#lab-page .lab-panel-head .head-link{color:#FFD166;font-size:10px;text-transform:uppercase;cursor:pointer;text-decoration:underline;font-weight:bold;background:none;border:none;font-family:inherit}
#lab-page .lab-panel-head .head-link:hover{color:#fff}
#lab-page .lab-live-sync{display:inline-block;background:#27ae60;color:#fff;font-size:9px;font-weight:bold;letter-spacing:.4px;padding:3px 9px;border-radius:999px;text-transform:uppercase}

/* ---- ENTRIES FORM ---- */
#lab-page .lab-form{display:grid;grid-template-columns:1fr 1fr;gap:12px 16px;padding:16px 18px;background-color:#f8fafc}
#lab-page .lab-field{display:flex;flex-direction:column;gap:5px}
#lab-page .lab-field-wide{grid-column:span 1}
#lab-page .lab-field label{font-weight:700;color:#222;font-size:11px;text-transform:uppercase;letter-spacing:.3px}
#lab-page .lab-field label .req{color:#e74c3c;font-weight:bold}
#lab-page .lab-field select,#lab-page .lab-field input{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;font-family:inherit}
#lab-page .lab-field select:focus,#lab-page .lab-field input:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#lab-page .lab-field input[readonly]{background-color:#eef3f8;color:#555;cursor:not-allowed}
#lab-page .lab-form-actions{grid-column:span 2;display:flex;justify-content:flex-end;margin-top:2px}
#lab-page .lab-btn-submit{background-color:#0b5fa5;color:#fff;border:none;padding:9px 24px;font-weight:bold;font-size:12px;border-radius:3px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.4px;display:inline-flex;align-items:center;gap:8px}
#lab-page .lab-btn-submit:hover{background-color:#004080}
#lab-page .lab-btn-submit svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

/* ---- VIEW TOOLBAR (search + MIS views) ---- */
#lab-page .lab-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:10px 14px;border-bottom:1px solid #e1e8f0;background-color:#f8fafc}
#lab-page .lab-toolbar .search{display:flex;align-items:center;gap:8px;flex:1 1 240px;min-width:200px}
#lab-page .lab-toolbar .search svg{width:16px;height:16px;fill:none;stroke:#0b5fa5;stroke-width:2;flex-shrink:0}
#lab-page .lab-toolbar .search input{flex:1;padding:6px 9px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;font-family:inherit;background:#fff}
#lab-page .lab-toolbar .search input:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#lab-page .lab-chip{border:1px solid #b2c8de;background:#fff;color:#0b5fa5;border-radius:3px;padding:5px 11px;font-weight:bold;font-size:10px;text-transform:uppercase;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px}
#lab-page .lab-chip:hover{background:#e2edf7}
#lab-page .lab-chip.active{background:#d2e3f3;border-color:#0b5fa5;color:#0b3e8f}
#lab-page .lab-chip i{font-size:11px}

/* ---- TABLE ---- */
#lab-page .lab-table-wrap{background-color:#fff;max-height:460px;overflow-y:auto}
#lab-page .lab-table{width:100%;border-collapse:collapse;font-size:11px}
#lab-page .lab-table th{background-color:#e6eef5;color:#222;font-weight:bold;text-align:left;padding:8px;border-bottom:2px solid #b2c8de;position:sticky;top:0;z-index:1;white-space:nowrap;text-transform:uppercase;font-size:10.5px;letter-spacing:.3px}
#lab-page .lab-table td{padding:7px 8px;border-bottom:1px solid #e1e8f0;vertical-align:middle}
#lab-page .lab-table tbody tr:hover{background-color:#eef4fb}
#lab-page .lab-table .first-col{padding-left:14px}
#lab-page .lab-table .act-col{text-align:center;white-space:nowrap}
#lab-page .lab-empty{text-align:center;padding:16px;color:#8a94a6}
#lab-page .lab-badge{display:inline-block;padding:2px 8px;border-radius:3px;font-weight:bold;font-size:10px;text-transform:uppercase;letter-spacing:.3px}
#lab-page .lab-status-ready{color:#1E7A34;background:#E9F9EF}
#lab-page .lab-status-sent{color:#0D47A1;background:#E8F4FD}
#lab-page .lab-status-abs{color:#B9770E;background:#FEF5E0}
#lab-page .lab-status-not{color:#B23B3B;background:#FDEBEC}
#lab-page .lab-btn-outline{background-color:#fff;color:#0b5fa5;border:1px solid #b2c8de;padding:4px 11px;font-weight:bold;font-size:10px;border-radius:3px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#lab-page .lab-btn-outline:hover{background-color:#e2edf7}
#lab-page .lab-btn-success{background-color:#27ae60;color:#fff;border:none;padding:4px 11px;font-weight:bold;font-size:10px;border-radius:3px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#lab-page .lab-btn-success:hover{background-color:#1e8c4d}
#lab-page .lab-btn-success svg{width:12px;height:12px;fill:none;stroke:currentColor;stroke-width:2}

/* ---- RESULT ENTRY MODAL (scoped) ---- */
#lab-page .modal{display:none;position:fixed;inset:0;z-index:2000;background:rgba(15,45,89,.55);align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto}
#lab-page .modal.show{display:flex}
#lab-page .modal-content{background:#fff;border-radius:8px;width:min(640px,100%);box-shadow:0 12px 34px rgba(0,0,0,.25);overflow:hidden}
#lab-page .modal-header{background-color:#0b5fa5;color:#fff;padding:13px 18px;display:flex;justify-content:space-between;align-items:center}
#lab-page .modal-header h3{margin:0;font-size:14px;text-transform:uppercase;letter-spacing:.4px}
#lab-page .modal-close{background:none;border:none;color:#fff;font-size:22px;line-height:1;cursor:pointer;padding:0 4px}
#lab-page .modal-close:hover{color:#FFD54F}
#lab-page .modal-body{padding:18px 20px;background:#F8FAFC}
#lab-page .modal-body .form-group{margin-bottom:14px}
#lab-page .modal-body label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#64748b;margin-bottom:5px}
#lab-page .modal-body input,#lab-page .modal-body select,#lab-page .modal-body textarea{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;font-family:inherit;box-sizing:border-box}
#lab-page .modal-body input[readonly]{background-color:#eef3f8;color:#555}
#lab-page .modal-body textarea{resize:vertical}
#lab-page .modal-body input:focus,#lab-page .modal-body select:focus,#lab-page .modal-body textarea:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#lab-page .form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px;padding-top:14px;border-top:1px solid #e1e8f0}
#lab-page .btn{border:none;border-radius:3px;cursor:pointer;font-weight:bold;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:8px 18px;font-family:inherit;display:inline-flex;align-items:center;gap:6px}
#lab-page .btn-primary{background-color:#0072BC;color:#fff}
#lab-page .btn-primary:hover{filter:brightness(1.08)}
#lab-page .btn-secondary{background:#F1F5F9;color:#34495E;border:1px solid #C0C0C0}
#lab-page .btn-secondary:hover{background:#E2E8F0}

@media (max-width:760px){
  #lab-page .lab-form{grid-template-columns:1fr}
  #lab-page .lab-form-actions{grid-column:span 1}
  #lab-page .lab-topbar .lab-tag-hide{display:none}
}
</style>

<!-- LAB & INVESTIGATIONS DASHBOARD -->
<div id="lab-page">

  <!-- TOP BAR -->
  <div class="lab-topbar">
    <div class="title-group">
      <svg viewBox="0 0 24 24"><path d="M10 2v7.31M14 2v7.31M8.5 2h7M14 9.3a6.5 6.5 0 1 1-4 0M5.5 15.5h13"/></svg>
      <div class="lab-topbar-title">Laboratory</div>
    </div>
    <div class="lab-topbar-right">
      <span class="lab-tag-hide">HMS - HEALTHCARE MANAGEMENT SYSTEM</span>
      <span class="lab-nav-btn" onclick="labGoHome()">HOME</span>
      <span class="lab-nav-btn" onclick="labGoBack()">&lt; BACK</span>
      <span class="lab-nav-btn" onclick="labGoPassword()">PASSWORD</span>
    </div>
  </div>

  <div class="lab-wrap">

    <!-- TOP MODULE HEADER -->
    <div class="lab-modhead">
      <div class="left">
        <svg viewBox="0 0 24 24"><path d="M10 2v7.31M14 2v7.31M8.5 2h7M14 9.3a6.5 6.5 0 1 1-4 0M5.5 15.5h13"/></svg>
        <h2>Lab &amp; Investigations Dashboard</h2>
      </div>
      <div class="actions">
        <button type="button" class="lab-h-btn secondary" onclick="labGoBack()">
          <svg viewBox="0 0 24 24"><path d="M9 14L4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"/></svg>
          Cancel
        </button>
        <button type="button" class="lab-h-btn light" onclick="labRefresh()">
          <svg viewBox="0 0 24 24"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
          Refresh Data
        </button>
      </div>
    </div>

    <!-- ENTRIES FORM - REQUEST LAB TEST -->
    <div class="lab-panel">
      <div class="lab-panel-head">
        <span class="head-left">
          <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Entries Form - Request Lab Test
        </span>
      </div>
      <form class="lab-form" id="labEntryForm">
        <div class="lab-field">
          <label for="entryPatient">Patient ID or Name <span class="req">*</span></label>
          <select id="entryPatient" required>
            <option value="">-- Select Visit --</option>
          </select>
        </div>

        <div class="lab-field">
          <label for="labType">Lab Type <span class="req">*</span></label>
          <select id="labType" required onchange="updateLabTests()">
            <option value="">Select Lab Type</option>
            <?php foreach ($__labCategories as $__key => $__label): ?>
              <option value="<?php echo htmlspecialchars($__key, ENT_QUOTES); ?>"><?php echo htmlspecialchars($__label, ENT_QUOTES); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="lab-field">
          <label for="labToDone">Lab to be Done (Parameters) <span class="req">*</span></label>
          <select id="labToDone" required onchange="updateLabPrice()">
            <option value="">-- Select Lab Parameter --</option>
            <?php foreach ($__labCatalog as $__cat => $__items): ?>
              <optgroup label="<?php echo htmlspecialchars($__labCategories[$__cat], ENT_QUOTES); ?>" data-category="<?php echo htmlspecialchars($__cat, ENT_QUOTES); ?>">
                <?php foreach ($__items as $__it): ?>
                  <option value="<?php echo htmlspecialchars($__it[1], ENT_QUOTES); ?>"
                          data-code="<?php echo htmlspecialchars($__it[0], ENT_QUOTES); ?>"
                          data-price="<?php echo number_format($__it[2], 2, '.', ''); ?>"><?php echo htmlspecialchars($__it[1], ENT_QUOTES); ?></option>
                <?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="lab-field">
          <label for="price">Price (GHS)</label>
          <input type="text" id="price" placeholder="GHS 0.00" readonly>
        </div>

        <div class="lab-form-actions">
          <button type="submit" class="lab-btn-submit">
            <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Submit Request
          </button>
        </div>
      </form>
    </div>

    <!-- REQUESTED LABS TABLE -->
    <div class="lab-panel">
      <div class="lab-panel-head dark">
        <span class="head-left">
          <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
          <span id="labMainTitle">ALL LABS REQUESTED BY THE DOCTOR</span>
        </span>
        <span style="display:flex;align-items:center;gap:10px;">
          <a class="head-link" id="labViewAllLink" style="display:none;" onclick="setLabView('requested')">View All Requests</a>
          <span class="lab-live-sync">Live Sync</span>
        </span>
      </div>

      <!-- Search + MIS view filters -->
      <div class="lab-toolbar">
        <div class="search">
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" id="labGlobalSearch" placeholder="By Patient / Test / Req. ID..." oninput="renderLabMain()">
        </div>
        <button type="button" class="lab-chip" id="misDispatched" onclick="setLabView('dispatched')"><i class="fa fa-envelope-open"></i> Dispatched (<span id="labDispCount">0</span>)</button>
        <button type="button" class="lab-chip" id="misReady" onclick="setLabView('ready')"><i class="fa fa-check-circle"></i> Ready (<span id="labReadyCount">0</span>)</button>
        <button type="button" class="lab-chip" id="misNotready" onclick="setLabView('notready')"><i class="fa fa-times-circle"></i> Not Ready (<span id="labNotReadyCount">0</span>)</button>
        <button type="button" class="lab-chip" onclick="labNav('mis')"><i class="fa fa-chart-line"></i> DHIMS Report</button>
      </div>

      <div class="lab-table-wrap">
        <div id="labMainView"><div class="lab-empty">Loading...</div></div>
      </div>
    </div>

  </div>
</div>

<!-- Lab Result Entry Modal -->
<div class="modal" id="lab-result-modal">
    <div class="modal-content" style="max-width:640px;">
        <div class="modal-header">
            <h3>Lab Result Entry</h3>
            <button class="modal-close" id="close-lab-result-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="labResultEntryForm">
                <div class="form-group">
                    <label for="entryRequisition">Requisition *</label>
                    <select id="entryRequisition">
                        <option value="">-- Awaiting requisitions --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="entrySpecimen">Specimen Type</label>
                    <input type="text" id="entrySpecimen" placeholder="e.g. Venous Blood (EDTA Tube)">
                </div>
                <div class="form-group">
                    <label for="entryTechnician">Lab Technician / Staff</label>
                    <input type="text" id="entryTechnician" value="<?php echo htmlspecialchars($__labStaff, ENT_QUOTES); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="entryResults">Test Results *</label>
                    <textarea id="entryResults" rows="3" placeholder="e.g. Hb: 12.5 g/dL, WBC: 11.2 x10^3/µL, MP: Positive (+), Platelets: 240 x10^3/µL..."></textarea>
                </div>
                <div class="form-group">
                    <label for="entryNormalRange">Normal / Reference Range</label>
                    <textarea id="entryNormalRange" rows="2" placeholder="e.g. Hb 12.0-16.0 g/dL, WBC 4.0-10.0 x10^3/µL"></textarea>
                </div>
                <div class="form-group">
                    <label for="entryInterpretation">Pathologist / Technician Remarks</label>
                    <textarea id="entryInterpretation" rows="2" placeholder="Clinical notes or observations..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-secondary" data-save="draft">Save Draft</button>
                    <button type="submit" class="btn btn-primary" data-save="final" id="entryMarkReadyBtn">Mark as Ready &amp; Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var CURRENT_LAB_STAFF = <?php echo json_encode($__labStaff); ?>;
var labData = [];
var labResultsMap = {};
var visitsCache = [];
var DISPATCH_KEY = 'hms:lab-dispatched';
var LAB_VIEW = 'requested';

async function initLabManagement() {
    setupLabEvents();
    await Promise.all([loadVisits(), loadLabRequests()]);
}

function setupLabEvents() {
    var entryForm = document.getElementById('labEntryForm');
    if (entryForm) entryForm.addEventListener('submit', handleLabEntrySubmit);
    var closeRes = document.getElementById('close-lab-result-modal');
    if (closeRes) closeRes.addEventListener('click', closeResultModal);
    var entryForm2 = document.getElementById('labResultEntryForm');
    if (entryForm2) entryForm2.addEventListener('submit', handleEntrySubmit);
}

/* ================= TOP BAR / NAV ================= */
function labNav(key) {
    if (key === 'home') {
        if (window.navigateTo) { window.navigateTo('dashboard'); return; }
        if (window.loadPage) { window.loadPage('dashboard'); return; }
    }
    if (key === 'mis' && window.loadModuleTab) { window.loadModuleTab('mis'); return; }
    if (window.loadModuleTab) window.loadModuleTab(key);
}

function labGoHome() { labNav('home'); }

function labGoBack() {
    if (window.history && window.history.length > 1) window.history.back();
    else labNav('home');
}

function labGoPassword() {
    if (window.navigateTo) window.navigateTo('change-password');
    else if (window.loadPage) window.loadPage('change-password');
}

/* ================= REFRESH DATA ================= */
function labRefresh() {
    Promise.all([loadVisits(), loadLabRequests()]).then(function () {
        showAlert('Lab data refreshed.', 'success');
    }).catch(function () {
        showAlert('Refresh failed — please try again.', 'error');
    });
}

/* ================= ENTRIES FORM (Lab Request) ================= */
async function loadVisits() {
    try {
        var r = await fetch('/hms/backend/api/visits.php');
        var d = await r.json();
        if (d.success) visitsCache = d.visits || [];
    } catch (e) {
        console.error('Visits load error:', e);
        visitsCache = [];
    }
    var sel = document.getElementById('entryPatient');
    sel.innerHTML = '<option value="">-- Select Visit --</option>'
        + visitsCache.map(function (v) {
            return '<option value="' + v.id + '">' + escHtml(v.visit_number + ' — ' + v.patient_name + ' (' + v.hospital_number + ')') + '</option>';
        }).join('');
}

/* Filter the "Lab to be Done" options to the selected Lab Type (optgroups) */
function updateLabTests() {
    var sel = document.getElementById('labToDone');
    var cat = document.getElementById('labType').value;
    if (!sel) return;
    Array.prototype.forEach.call(sel.querySelectorAll('optgroup'), function (grp) {
        var show = !cat || grp.getAttribute('data-category') === cat;
        grp.hidden = !show;
        grp.disabled = !show;
        Array.prototype.forEach.call(grp.querySelectorAll('option'), function (opt) {
            opt.hidden = !show;
            opt.disabled = !show;
        });
    });
    if (cat) {
        sel.querySelector('option[value=""]').textContent = '-- Select Investigation --';
    } else {
        sel.querySelector('option[value=""]').textContent = '-- Select Lab Parameter --';
    }
    sel.value = '';
    document.getElementById('price').value = '';
}

/* Auto-generate the price from the selected investigation's data-price */
function updateLabPrice() {
    var sel = document.getElementById('labToDone');
    var opt = sel && sel.options[sel.selectedIndex];
    var priceField = document.getElementById('price');
    if (!opt || !opt.value || !opt.getAttribute('data-price')) {
        priceField.value = '';
        return;
    }
    priceField.value = 'GHS ' + opt.getAttribute('data-price');
}

async function handleLabEntrySubmit(e) {
    e.preventDefault();
    var visitId = document.getElementById('entryPatient').value;
    var testType = document.getElementById('labToDone').value.trim();
    var labType = document.getElementById('labType').value;
    if (!visitId) { showAlert('Select the patient / visit first.', 'error'); return; }
    if (!labType) { showAlert('Select the lab type.', 'error'); return; }
    if (!testType) { showAlert('Select the lab investigation / parameter.', 'error'); return; }

    var sel = document.getElementById('labToDone');
    var opt = sel.options[sel.selectedIndex];
    var price = opt.getAttribute('data-price') || '0.00';
    var code = opt.getAttribute('data-code') || '';

    var data = {
        visit_id: visitId,
        test_type: testType,
        test_description: 'Category: ' + labType + (code ? ' (' + code + ')' : '') + ' | Price: GHS ' + price,
        urgency: 'routine'
    };
    try {
        var r = await fetch('/hms/backend/api/lab.php?action=request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var d = await r.json();
        if (d.success) {
            showAlert('Lab request submitted for ' + testType + ' — GHS ' + price + '.', 'success');
            document.getElementById('labEntryForm').reset();
            document.getElementById('price').value = '';
            setLabView('requested');
            await loadLabRequests();
        } else {
            showAlert(d.error || 'Request failed', 'error');
        }
    } catch (e) {
        console.error('Lab request error:', e);
        showAlert('Network error. Please try again.', 'error');
    }
}

/* ================= LAB DATA / VIEWS ================= */
async function loadLabRequests() {
    try {
        var r = await fetch('/hms/backend/api/lab.php?action=requests');
        var d = await r.json();
        labData = (d.success && d.requests) ? d.requests : [];
    } catch (e) {
        console.error('Lab load error:', e);
        labData = [];
    }
    await loadLabResults();
    renderLabCounts();
    renderLabMain();
    populateEntryRequisition();
}

async function loadLabResults() {
    labResultsMap = {};
    try {
        var r = await fetch('/hms/backend/api/lab.php?action=results');
        var d = await r.json();
        if (d.success) {
            (d.results || []).forEach(function (res) {
                if (res.lab_request_id !== null && res.lab_request_id !== undefined) {
                    labResultsMap[String(res.lab_request_id)] = res;
                }
            });
        }
    } catch (e) { console.error('Lab results load error:', e); }
}

function renderLabCounts() {
    var notReady = labData.filter(function (r) { return r.status === 'pending' || r.status === 'in_progress'; }).length;
    var ready = labData.filter(function (r) { return r.status === 'completed'; }).length;
    document.getElementById('labNotReadyCount').textContent = notReady;
    document.getElementById('labReadyCount').textContent = ready;
    document.getElementById('labDispCount').textContent = readDispatched().length;
}

function labStatusBadge(status) {
    if (status === 'completed') return '<span class="lab-badge lab-status-ready">Ready</span>';
    if (status === 'in_progress') return '<span class="lab-badge lab-status-sent">In Progress</span>';
    if (status === 'cancelled') return '<span class="lab-badge lab-status-abs">ABS</span>';
    return '<span class="lab-badge lab-status-not">Not Ready</span>;
}

function setLabView(view) {
    LAB_VIEW = view;
    var valid = ['requested', 'ready', 'notready', 'dispatched'];
    if (valid.indexOf(view) === -1) view = 'requested';
    document.getElementById('misDispatched').classList.toggle('active', view === 'dispatched');
    document.getElementById('misReady').classList.toggle('active', view === 'ready');
    document.getElementById('misNotready').classList.toggle('active', view === 'notready');
    var titles = {
        requested: 'ALL LABS REQUESTED BY THE DOCTOR',
        ready: 'READY & VERIFIED LABS',
        notready: 'NOT READY / IN-PROGRESS LABS',
        dispatched: 'DISPATCHED REPORTS HISTORY'
    };
    document.getElementById('labMainTitle').textContent = titles[view];
    document.getElementById('labViewAllLink').style.display = view === 'requested' ? 'none' : 'inline';
    renderLabMain();
}

function renderLabMain() {
    var holder = document.getElementById('labMainView');
    if (!holder) return;
    var q = (document.getElementById('labGlobalSearch').value || '').trim().toLowerCase();
    var filter = function (rows) {
        if (!q) return rows;
        return rows.filter(function (r) {
            return String(r.patient_name || '').toLowerCase().indexOf(q) >= 0
                || String(r.hospital_number || '').toLowerCase().indexOf(q) >= 0
                || String(r.test_type || '').toLowerCase().indexOf(q) >= 0
                || String(r.id || '').indexOf(q) >= 0;
        });
    };
    var actionCell = function (r) {
        if (r.status === 'pending' || r.status === 'in_progress') {
            return '<button class="lab-btn-outline" onclick="openEntryFor(' + r.id + ')">Process</button>';
        }
        if (r.status === 'completed') {
            return '<button class="lab-btn-success" onclick="dispatchReport(' + r.id + ')">'
                + '<svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Dispatch</button>';
        }
        return '<span style="color:#8a94a6;">—</span>';
    };

    if (LAB_VIEW === 'requested') {
        var rows = filter(labData);
        holder.innerHTML = '<table class="lab-table"><thead><tr>'
            + '<th class="first-col">Req ID</th><th>Patient Name</th><th>Lab Test</th><th>Doctor</th><th>Request Date</th><th>Status</th><th class="act-col">Action</th>'
            + '</tr></thead><tbody>' + (rows.length
                ? rows.map(function (r) {
                    return '<tr>'
                        + '<td class="first-col" style="font-weight:700;">LAB ' + escHtml(r.id) + '</td>'
                        + '<td style="font-weight:700;">' + escHtml(r.patient_name || '--') + '</td>'
                        + '<td>' + escHtml(r.test_type || '--') + '</td>'
                        + '<td>' + escHtml(r.doctor_name || '--') + '</td>'
                        + '<td>' + fmtLabTime(r.requested_at) + '</td>'
                        + '<td>' + labStatusBadge(r.status) + '</td>'
                        + '<td class="act-col">' + actionCell(r) + '</td>'
                        + '</tr>';
                }).join('')
                : '<tr><td colspan="7" class="lab-empty">' + (q ? 'No requests match "' + escHtml(q) + '".' : 'No lab requests yet — use the Entries Form above to add one.') + '</td></tr>')
            + '</tbody></table>';
        return;
    }

    if (LAB_VIEW === 'notready') {
        var nrows = filter(labData.filter(function (r) { return r.status === 'pending' || r.status === 'in_progress'; }));
        holder.innerHTML = '<table class="lab-table"><thead><tr>'
            + '<th class="first-col">Req ID</th><th>Patient Name</th><th>Lab Test</th><th>Request Date</th><th>Status</th><th class="act-col">Action</th>'
            + '</tr></thead><tbody>' + (nrows.length
                ? nrows.map(function (r) {
                    return '<tr>'
                        + '<td class="first-col" style="font-weight:700;">LAB ' + escHtml(r.id) + '</td>'
                        + '<td style="font-weight:700;">' + escHtml(r.patient_name || '--') + '</td>'
                        + '<td>' + escHtml(r.test_type || '--') + '</td>'
                        + '<td>' + fmtLabTime(r.requested_at) + '</td>'
                        + '<td>' + labStatusBadge(r.status) + '</td>'
                        + '<td class="act-col"><button class="lab-btn-outline" onclick="openEntryFor(' + r.id + ')">Enter Results</button></td>'
                        + '</tr>';
                }).join('')
                : '<tr><td colspan="6" class="lab-empty">No in-progress / not-ready labs.</td></tr>')
            + '</tbody></table>';
        return;
    }

    if (LAB_VIEW === 'ready') {
        var dispatched = readDispatched();
        var rrows = filter(labData.filter(function (r) {
            return r.status === 'completed' && !dispatched.some(function (d) { return d.id === String(r.id); });
        }));
        holder.innerHTML = '<table class="lab-table"><thead><tr>'
            + '<th class="first-col">Req ID</th><th>Patient Name</th><th>Lab Test</th><th>Result</th><th>Verified / Completed</th><th class="act-col">Action</th>'
            + '</tr></thead><tbody>' + (rrows.length
                ? rrows.map(function (r) {
                    var labRes = labResultsMap[String(r.id)] || null;
                    var resultText = labRes ? String(labRes.results || '--') : String(r.result_status || '--');
                    var doneAt = labRes && labRes.completed_at ? fmtLabTime(labRes.completed_at) : fmtLabTime(r.requested_at);
                    return '<tr>'
                        + '<td class="first-col" style="font-weight:700;">LAB ' + escHtml(r.id) + '</td>'
                        + '<td style="font-weight:700;">' + escHtml(r.patient_name || '--') + '</td>'
                        + '<td>' + escHtml(r.test_type || '--') + '</td>'
                        + '<td style="max-width:240px;white-space:pre-wrap;">' + escHtml(resultText) + '</td>'
                        + '<td>' + (labRes && labRes.verified_name ? escHtml(labRes.verified_name) + '<br>' : '') + doneAt + '</td>'
                        + '<td class="act-col"><button class="lab-btn-success" onclick="dispatchReport(' + r.id + ')">Dispatch Report</button></td>'
                        + '</tr>';
                }).join('')
                : '<tr><td colspan="6" class="lab-empty">No ready & verified labs.</td></tr>')
            + '</tbody></table>';
        return;
    }

    /* dispatched */
    var dlist = readDispatched();
    var drows = dlist.map(function (d) {
        var r = labData.find(function (x) { return String(x.id) === d.id; }) || null;
        return '<tr>'
            + '<td class="first-col" style="font-weight:700;">DSP ' + escHtml(d.id) + '</td>'
            + '<td style="font-weight:700;">' + escHtml(r ? r.patient_name : '--') + '</td>'
            + '<td>' + escHtml(r ? r.test_type : '--') + '</td>'
            + '<td>' + (d.at ? fmtLabTime(d.at) : '--') + '</td>'
            + '<td class="act-col"><button class="lab-btn-outline" onclick="printReport(' + (r ? r.id : 0) + ')">Print PDF</button></td>'
            + '</tr>';
    }).join('');
    holder.innerHTML = '<table class="lab-table"><thead><tr>'
        + '<th class="first-col">Dispatch ID</th><th>Patient Name</th><th>Lab Test</th><th>Dispatch Date</th><th class="act-col">Report</th>'
        + '</tr></thead><tbody>' + (drows || '<tr><td colspan="5" class="lab-empty">No dispatched reports yet — dispatch a ready lab above.</td></tr>')
        + '</tbody></table>';
}

/* ================= RESULT ENTRY ================= */
function populateEntryRequisition() {
    var sel = document.getElementById('entryRequisition');
    if (!sel) return;
    var keep = sel.value;
    var rows = labData.filter(function (r) { return r.status === 'pending' || r.status === 'in_progress'; });
    sel.innerHTML = rows.length
        ? rows.map(function (r) { return '<option value="' + r.id + '">LAB ' + r.id + ' — ' + escHtml(r.patient_name) + ' (' + escHtml(r.test_type) + ')</option>'; }).join('')
        : '<option value="">-- Awaiting requisitions --</option>';
    if (keep && rows.some(function (r) { return String(r.id) === String(keep); })) sel.value = keep;
}

function openEntryFor(requestId) {
    populateEntryRequisition();
    var sel = document.getElementById('entryRequisition');
    if (sel.options.length && Array.from(sel.options).some(function (o) { return String(o.value) === String(requestId); })) {
        sel.value = String(requestId);
    }
    document.getElementById('labResultEntryForm').reset();
    document.getElementById('lab-result-modal').classList.add('show');
}

function closeResultModal() {
    document.getElementById('lab-result-modal').classList.remove('show');
}

async function handleEntrySubmit(e) {
    e.preventDefault();
    var reqId = document.getElementById('entryRequisition').value;
    if (!reqId) {
        showAlert('Select a requisition first — there are no pending lab requests to record results for.', 'error');
        return;
    }
    var saveAs = e.submitter && e.submitter.getAttribute ? (e.submitter.getAttribute('data-save') || 'final') : 'final';
    var results = document.getElementById('entryResults').value.trim();
    var spec = document.getElementById('entrySpecimen').value.trim();
    if (spec) results = results ? 'Specimen: ' + spec + '\n' + results : 'Specimen: ' + spec;
    if (!results) {
        showAlert('Enter the result values before saving.', 'error');
        return;
    }
    var data = {
        lab_request_id: reqId,
        results: results,
        normal_range: document.getElementById('entryNormalRange').value.trim(),
        interpretation: document.getElementById('entryInterpretation').value.trim(),
        status: saveAs
    };
    try {
        var r = await fetch('/hms/backend/api/lab.php?action=result', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var d = await r.json();
        if (d.success) {
            showAlert(saveAs === 'final' ? 'Result saved and marked as READY.' : 'Result saved as draft.', 'success');
            closeResultModal();
            setLabView(saveAs === 'final' ? 'ready' : 'notready');
            await loadLabRequests();
        } else {
            showAlert(d.error || 'Save failed', 'error');
        }
    } catch (e) {
        console.error('Entry save error:', e);
        showAlert('Network error. Please try again.', 'error');
    }
}

/* ================= DISPATCH (browser-local log; backend has no 'dispatched' status yet) ================= */
function readDispatched() {
    try {
        var raw = localStorage.getItem(DISPATCH_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch (e) { return []; }
}

function dispatchReport(requestId) {
    var list = readDispatched();
    if (!list.some(function (d) { return String(d.id) === String(requestId); })) {
        list.push({ id: String(requestId), at: new Date().toISOString() });
        try { localStorage.setItem(DISPATCH_KEY, JSON.stringify(list)); } catch (e) {}
    }
    showAlert('Report marked as dispatched.', 'success');
    renderLabCounts();
    if (LAB_VIEW === 'ready') setLabView('dispatched');
    loadLabRequests();
}

function printReport(requestId) {
    var r = labData.find(function (x) { return String(x.id) === String(requestId); });
    showAlert('Report LAB ' + (r ? r.id : '') + ' queued for printing — use Ctrl+P to export the PDF.', 'info');
}

/* ================= HELPERS ================= */
function fmtLabTime(v) {
    return window.fmtDateTime ? window.fmtDateTime(v) : (v || '--');
}

function escHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
</script>