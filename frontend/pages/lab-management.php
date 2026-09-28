<?php
// Laboratory & Investigations Module (full-width EHMS module page).
// Server-side prologue: logged-in staff name/role, and the laboratory
// investigation catalogue (category -> test + price) that drives both the
// "Lab Parameter / Test to be Done" dropdown and the auto price field.
require_once __DIR__ . '/../../backend/config/config.php';
$__labStaff = getCurrentUserName() ?: 'STAFF';
$__labRole  = getCurrentUserRole() ?: 'STAFF';

// ---- Laboratory investigation catalogue (single source of truth) ------------
// Edit prices here; the parameter dropdown, the auto-generated price field and
// the technician form all read from this one list.
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
// Flatten the catalogue for the two-step dropdown (category -> [{name, price}]).
$__labParamData = [];
foreach ($__labCatalog as $__cat => $__items) {
    $__labParamData[$__cat] = [];
    foreach ($__items as $__it) {
        $__labParamData[$__cat][] = [
            'name'  => $__it[1],
            'code'  => $__it[0],
            'price' => number_format($__it[2], 2, '.', ''),
        ];
    }
}
?>
<style>
/* ============= LAB MANAGEMENT & INVESTIGATION MODULE : FULL-WIDTH ============
   Scoped under #lab-page. No Bootstrap — every utility used here is defined
   below. Font Awesome is available (the shell loads
   frontend/assets/fontawesome/css/all.min.css).
   No inline support footer: the shell auto-appends the system banner. */
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

/* ---- MODULE HEADER BANNER ---- */
#lab-page .lab-wrap{max-width:1240px;margin:0 auto;padding:12px 16px 16px}
#lab-page .lab-modhead{background:linear-gradient(135deg,#0D47A1,#0072BC);color:#fff;padding:9px 16px;border-radius:6px 6px 0 0;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;box-shadow:0 2px 6px rgba(13,71,161,.22)}
#lab-page .lab-modhead .left{display:flex;align-items:center;gap:10px}
#lab-page .lab-modhead .left svg{width:22px;height:22px;fill:none;stroke:#ffffff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#lab-page .lab-modhead h2{margin:0;font-size:15px;font-weight:800;text-transform:uppercase;letter-spacing:.6px}
#lab-page .lab-modhead .actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
#lab-page .lab-h-btn{border:none;border-radius:3px;cursor:pointer;font-weight:bold;font-size:11px;text-transform:uppercase;letter-spacing:.4px;display:inline-flex;align-items:center;gap:6px;padding:6px 14px;font-family:inherit;transition:background .15s,filter .15s}
#lab-page .lab-h-btn svg{width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#lab-page .lab-h-btn.light{background-color:#fff;color:#0D47A1}
#lab-page .lab-h-btn.light:hover{filter:brightness(.96)}
#lab-page .lab-live-badge{background:#fff;color:#1E7A34;border-radius:999px;font-size:10px;font-weight:bold;letter-spacing:.4px;padding:4px 11px;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase}
#lab-page .lab-live-badge i{color:#27ae60}

/* ---- FEATURE 1: 2-COLUMN NAVIGATION GRID ---- */
#lab-page .lab-feature-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:14px 0 16px}
#lab-page .lab-feature-card{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid #b2c8de;border-radius:6px;padding:14px 16px;cursor:pointer;text-align:left;font-family:inherit;box-shadow:0 2px 5px rgba(0,0,0,.07);transition:transform .15s,box-shadow .15s,border-color .15s}
#lab-page .lab-feature-card:hover{transform:translateY(-2px);box-shadow:0 6px 14px rgba(0,0,0,.14);border-color:#0b5fa5}
#lab-page .lab-feature-card svg{width:40px;height:40px;flex-shrink:0;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#lab-page .lab-feature-card .fc-title{font-size:12.5px;font-weight:800;color:#c0392b;text-transform:uppercase;letter-spacing:.4px;margin:0 0 2px}
#lab-page .lab-feature-card .fc-sub{font-size:11px;color:#64748b;margin:0}

/* ---- PANELS ---- */
#lab-page .lab-panel{background:#fff;border:1px solid #b2c8de;border-top:none;border-radius:0 0 6px 6px;overflow:hidden;box-shadow:0 3px 8px rgba(0,0,0,.06)}
#lab-page .lab-panel + .lab-panel{margin-top:14px}
#lab-page .lab-panel.is-first{border-top:1px solid #b2c8de;border-radius:6px}
#lab-page .lab-panel-head{background-color:#0b5fa5;color:#fff;font-weight:bold;font-size:12px;padding:9px 14px;text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
#lab-page .lab-panel-head.dark{background-color:#1f2933;padding:0}
#lab-page .lab-panel-head .head-left{display:flex;align-items:center;gap:8px}
#lab-page .lab-panel-head svg{width:17px;height:17px;fill:none;stroke:#ffffff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#lab-page .lab-panel-head.dark svg{fill:none;stroke:#fff}

/* ---- ENTRIES / TECHNICIAN FORM ---- */
#lab-page .lab-form{display:grid;grid-template-columns:repeat(6,1fr);gap:12px 16px;padding:16px 18px;background-color:#f8fafc}
#lab-page .lab-field{display:flex;flex-direction:column;gap:5px}
#lab-page .lab-field label{font-weight:700;color:#222;font-size:11px;text-transform:uppercase;letter-spacing:.3px}
#lab-page .lab-field label .req{color:#e74c3c;font-weight:bold}
#lab-page .lab-field select,#lab-page .lab-field input{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;font-family:inherit}
#lab-page .lab-field select:focus,#lab-page .lab-field input:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#lab-page .lab-field input[readonly]{background-color:#eef3f8;color:#1E7A34;font-weight:bold;cursor:not-allowed}
#lab-page .span-3{grid-column:span 3}
#lab-page .span-4{grid-column:span 4}
#lab-page .span-2{grid-column:span 2}
#lab-page .span-6{grid-column:span 6}
#lab-page .lab-form-actions{grid-column:span 6;display:flex;justify-content:flex-end;margin-top:2px}
#lab-page .lab-btn-submit{background-color:#0b5fa5;color:#fff;border:none;padding:9px 24px;font-weight:bold;font-size:12px;border-radius:3px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.4px;display:inline-flex;align-items:center;gap:8px}
#lab-page .lab-btn-submit:hover{background-color:#004080}
#lab-page .lab-btn-submit svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

/* ---- TABS ---- */
#lab-page .lab-tabs{display:flex;flex-wrap:wrap}
#lab-page .lab-tab{background:transparent;border:none;border-right:1px solid rgba(255,255,255,.18);color:#dbe6f2;font-family:inherit;font-weight:bold;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:11px 16px;cursor:pointer}
#lab-page .lab-tab:last-child{border-right:none}
#lab-page .lab-tab:hover{background:rgba(255,255,255,.08);color:#fff}
#lab-page .lab-tab.active{background:#0b5fa5;color:#fff;box-shadow:inset 0 -3px 0 #FFD166}

/* ---- VIEW TOOLBAR (search) ---- */
#lab-page .lab-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:10px 14px;border-bottom:1px solid #e1e8f0;background-color:#f8fafc}
#lab-page .lab-toolbar .search{display:flex;align-items:center;gap:8px;flex:1 1 240px;min-width:200px}
#lab-page .lab-toolbar .search svg{width:16px;height:16px;fill:none;stroke:#0b5fa5;stroke-width:2;flex-shrink:0}
#lab-page .lab-toolbar .search input{flex:1;padding:6px 9px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;font-family:inherit;background:#fff}
#lab-page .lab-toolbar .search input:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#lab-page .lab-counts{font-size:10.5px;color:#64748b;display:flex;gap:12px;flex-wrap:wrap}
#lab-page .lab-counts b{color:#0b5fa5}

/* ---- TAB PANES + TABLE ---- */
#lab-page .lab-pane{display:none}
#lab-page .lab-pane.active{display:block}
#lab-page .lab-table-wrap{background-color:#fff;max-height:460px;overflow-y:auto}
#lab-page .lab-table{width:100%;border-collapse:collapse;font-size:11px}
#lab-page .lab-table th{background-color:#e6eef5;color:#222;font-weight:bold;text-align:left;padding:8px;border-bottom:2px solid #b2c8de;position:sticky;top:0;z-index:1;white-space:nowrap;text-transform:uppercase;font-size:10.5px;letter-spacing:.3px}
#lab-page .lab-table td{padding:7px 8px;border-bottom:1px solid #e1e8f0;vertical-align:middle}
#lab-page .lab-table tbody tr:hover{background-color:#eef4fb}
#lab-page .lab-table .first-col{padding-left:14px}
#lab-page .lab-table .act-col{text-align:center;white-space:nowrap}
#lab-page .lab-empty{text-align:center;padding:18px;color:#8a94a6}
#lab-page .lab-badge{display:inline-block;padding:2px 8px;border-radius:3px;font-weight:bold;font-size:10px;text-transform:uppercase;letter-spacing:.3px}
#lab-page .lab-status-ready{color:#1E7A34;background:#E9F9EF}
#lab-page .lab-status-sent{color:#0D47A1;background:#E8F4FD}
#lab-page .lab-status-abs{color:#B9770E;background:#FEF5E0}
#lab-page .lab-status-not{color:#B23B3B;background:#FDEBEC}
#lab-page .lab-btn-outline{background-color:#fff;color:#0b5fa5;border:1px solid #b2c8de;padding:4px 11px;font-weight:bold;font-size:10px;border-radius:3px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#lab-page .lab-btn-outline:hover{background-color:#e2edf7}
#lab-page .lab-btn-primary{background-color:#0b5fa5;color:#fff;border:none;padding:4px 12px;font-weight:bold;font-size:10px;border-radius:3px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#lab-page .lab-btn-primary:hover{background-color:#004080}
#lab-page .lab-btn-success{background-color:#27ae60;color:#fff;border:none;padding:4px 11px;font-weight:bold;font-size:10px;border-radius:3px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#lab-page .lab-btn-success:hover{background-color:#1e8c4d}
#lab-page .lab-btn-success svg{width:12px;height:12px;fill:none;stroke:currentColor;stroke-width:2}

/* ---- MODALS (scoped; both live inside #lab-page) ---- */
#lab-page .modal{display:none;position:fixed;inset:0;z-index:2000;background:rgba(15,45,89,.55);align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto}
#lab-page .modal.show{display:flex}
#lab-page .modal.centered{align-items:center}
#lab-page .modal-content{background:#fff;border-radius:8px;width:min(640px,100%);box-shadow:0 12px 34px rgba(0,0,0,.25);overflow:hidden}
#lab-page .modal-header{background-color:#0b5fa5;color:#fff;padding:13px 18px;display:flex;justify-content:space-between;align-items:center;gap:10px}
#lab-page .modal-header h3{margin:0;font-size:14px;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;gap:8px}
#lab-page .modal-header.danger{background-color:#c0392b}
#lab-page .modal-header svg{width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#lab-page .modal-close{background:none;border:none;color:#fff;font-size:22px;line-height:1;cursor:pointer;padding:0 4px}
#lab-page .modal-close:hover{color:#FFD54F}
#lab-page .modal-body{padding:18px 20px;background:#F8FAFC}
#lab-page .modal-body.text-center{text-align:center}
#lab-page .lab-alert-title{font-size:15px;font-weight:bold;color:#222;margin:0 0 6px}
#lab-page .lab-alert-sub{font-size:12px;color:#64748b;margin:0}
#lab-page .modal-body .form-group{margin-bottom:14px}
#lab-page .modal-body label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#64748b;margin-bottom:5px}
#lab-page .modal-body input,#lab-page .modal-body select,#lab-page .modal-body textarea{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;font-family:inherit;box-sizing:border-box}
#lab-page .modal-body input[readonly]{background-color:#eef3f8;color:#555}
#lab-page .modal-body textarea{resize:vertical}
#lab-page .modal-body input:focus,#lab-page .modal-body select:focus,#lab-page .modal-body textarea:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#lab-page .form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px;padding-top:14px;border-top:1px solid #e1e8f0}
#lab-page .form-actions.center{justify-content:center;border-top:none;margin-top:8px;padding-top:0}
#lab-page .btn{border:none;border-radius:3px;cursor:pointer;font-weight:bold;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:8px 18px;font-family:inherit;display:inline-flex;align-items:center;gap:6px}
#lab-page .btn-primary{background-color:#0072BC;color:#fff}
#lab-page .btn-primary:hover{filter:brightness(1.08)}
#lab-page .btn-secondary{background:#F1F5F9;color:#34495E;border:1px solid #C0C0C0}
#lab-page .btn-secondary:hover{background:#E2E8F0}

@media (max-width:760px){
  #lab-page .lab-feature-grid{grid-template-columns:1fr}
  #lab-page .lab-form{grid-template-columns:1fr}
  #lab-page .span-3,#lab-page .span-4,#lab-page .span-2,#lab-page .span-6,#lab-page .lab-form-actions{grid-column:span 1}
  #lab-page .lab-topbar .lab-tag-hide{display:none}
}
</style>

<!-- LAB MANAGEMENT & INVESTIGATION MODULE -->
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

    <!-- MODULE HEADER BANNER -->
    <div class="lab-modhead">
      <div class="left">
        <svg viewBox="0 0 24 24"><path d="M10 2v7.31M14 2v7.31M8.5 2h7M14 9.3a6.5 6.5 0 1 1-4 0M5.5 15.5h13"/></svg>
        <h2>Lab Management &amp; Investigation Module</h2>
      </div>
      <div class="actions">
        <span class="lab-live-badge" id="labLiveBadge"><i class="fa fa-sync fa-spin"></i> Live Auto-Refresh</span>
        <button type="button" class="lab-h-btn light" onclick="labRefresh()">
          <svg viewBox="0 0 24 24"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- FEATURE 1: 2-COLUMN NAVIGATION GRID -->
    <div class="lab-feature-grid">
      <button type="button" class="lab-feature-card" onclick="labGoSection('labFormSection')">
        <svg viewBox="0 0 24 24" stroke="#d9534f"><path d="M10 2v7.31M14 2v7.31M8.5 2h7M14 9.3a6.5 6.5 0 1 1-4 0M5.5 15.5h13"/></svg>
        <span>
          <span class="fc-title">Sample Management</span>
          <span class="fc-sub">Register and process patient specimens</span>
        </span>
      </button>

      <button type="button" class="lab-feature-card" onclick="labCardTab('ready')">
        <svg viewBox="0 0 24 24" stroke="#5cb85c"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        <span>
          <span class="fc-title">Lab Verification &amp; Ready Labs</span>
          <span class="fc-sub">Verify completed tests &amp; authorize results</span>
        </span>
      </button>

      <button type="button" class="lab-feature-card" onclick="labCardTab('pending')">
        <svg viewBox="0 0 24 24" stroke="#f0ad4e"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <span>
          <span class="fc-title">Pending Tests</span>
          <span class="fc-sub">View pending lab test requests</span>
        </span>
      </button>

      <button type="button" class="lab-feature-card" onclick="labCardTab('dispatched')">
        <svg viewBox="0 0 24 24" stroke="#0275d8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <span>
          <span class="fc-title">Report Dispatched</span>
          <span class="fc-sub">Access dispatched lab records</span>
        </span>
      </button>
    </div>

    <!-- FEATURE 2: LAB TECHNICIAN FORM - PROCESS INVESTIGATION -->
    <div class="lab-panel is-first" id="labFormSection">
      <div class="lab-panel-head">
        <span class="head-left">
          <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Lab Technician Form - Process Investigation
        </span>
      </div>
      <form class="lab-form" id="labEntryForm">
        <div class="lab-field span-3">
          <label for="entryPatient">Patient ID / Name <span class="req">*</span></label>
          <select id="entryPatient" required>
            <option value="">-- Select Patient --</option>
          </select>
        </div>

        <div class="lab-field span-3">
          <label for="labTypeSelect">Lab Type <span class="req">*</span></label>
          <select id="labTypeSelect" onchange="updateLabParameters()" required>
            <option value="">-- Select Lab Category --</option>
            <?php foreach ($__labCategories as $__key => $__label): ?>
              <option value="<?php echo htmlspecialchars($__key, ENT_QUOTES); ?>"><?php echo htmlspecialchars($__label, ENT_QUOTES); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="lab-field span-4">
          <label for="labParameterSelect">Lab Parameter / Test to be Done <span class="req">*</span></label>
          <select id="labParameterSelect" onchange="updateLabPrice()" required>
            <option value="">-- Select Lab Category First --</option>
          </select>
        </div>

        <div class="lab-field span-2">
          <label for="labPriceInput">Price (GHS)</label>
          <input type="text" id="labPriceInput" placeholder="GHS 0.00" readonly>
        </div>

        <div class="lab-form-actions">
          <button type="submit" class="lab-btn-submit">
            <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Save &amp; Submit Test
          </button>
        </div>
      </form>
    </div>

    <!-- FEATURE 3-5: LAB STATUS TABLES -->
    <div class="lab-panel" id="labTabTables">
      <div class="lab-panel-head dark">
        <div class="lab-tabs" id="labTabs" role="tablist">
          <button type="button" class="lab-tab active" id="labTabDoctors" onclick="labSwitchTab('doctors')">Doctor Requests</button>
          <button type="button" class="lab-tab" id="labTabPending" onclick="labSwitchTab('pending')">Pending Labs</button>
          <button type="button" class="lab-tab" id="labTabReady" onclick="labSwitchTab('ready')">Ready Labs</button>
          <button type="button" class="lab-tab" id="labTabNotReady" onclick="labSwitchTab('notready')">Not Ready Labs</button>
          <button type="button" class="lab-tab" id="labTabDispatched" onclick="labSwitchTab('dispatched')">Dispatched Labs</button>
        </div>
      </div>

      <div class="lab-toolbar">
        <div class="search">
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" id="labGlobalSearch" placeholder="By Patient / Test / Req. ID..." oninput="renderLabPanes()">
        </div>
        <div class="lab-counts">
          <span>All: <b id="cAll">0</b></span>
          <span>Pending: <b id="cPending">0</b></span>
          <span>In Progress: <b id="cNotReady">0</b></span>
          <span>Ready: <b id="cReady">0</b></span>
          <span>Dispatched: <b id="cDispatched">0</b></span>
        </div>
      </div>

      <div class="lab-table-wrap">
        <div class="lab-pane active" id="labPaneDoctors"><div class="lab-empty">Loading...</div></div>
        <div class="lab-pane" id="labPanePending"></div>
        <div class="lab-pane" id="labPaneReady"></div>
        <div class="lab-pane" id="labPaneNotReady"></div>
        <div class="lab-pane" id="labPaneDispatched"></div>
      </div>
    </div>

  </div>

  <!-- FEATURE 6: NEW LAB REQUEST ALERT (raised only for genuinely new requests) -->
  <div class="modal centered" id="labNewRequestModal">
    <div class="modal-content" style="max-width:520px;">
      <div class="modal-header danger">
        <h3>
          <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2.121 2.121 0 0 1-3.46 0"/></svg>
          New Lab Request Alert
        </h3>
        <button class="modal-close" onclick="closeNewRequestModal()">&times;</button>
      </div>
      <div class="modal-body text-center">
        <p class="lab-alert-title">A new lab test request has been ordered by the doctor!</p>
        <p class="lab-alert-sub" id="labAlertDetails">--</p>
      </div>
      <div class="form-actions center">
        <button type="button" class="btn btn-secondary" onclick="closeNewRequestModal()">Close Alert</button>
        <button type="button" class="btn btn-primary" onclick="viewNewRequest()">View Request</button>
      </div>
    </div>
  </div>

  <!-- Lab Result Entry Modal -->
  <div class="modal" id="lab-result-modal">
    <div class="modal-content">
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
            <textarea id="entryResults" rows="3" placeholder="e.g. Hb: 12.5 g/dL, WBC: 11.2 x10^3/uL, MP: Positive (+), Platelets: 240 x10^3/uL..."></textarea>
          </div>
          <div class="form-group">
            <label for="entryNormalRange">Normal / Reference Range</label>
            <textarea id="entryNormalRange" rows="2" placeholder="e.g. Hb 12.0-16.0 g/dL, WBC 4.0-10.0 x10^3/uL"></textarea>
          </div>
          <div class="form-group">
            <label for="entryInterpretation">Pathologist / Technician Remarks</label>
            <textarea id="entryInterpretation" rows="2" placeholder="Clinical notes or observations..."></textarea>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-secondary" data-save="draft">Save Draft</button>
            <button type="submit" class="btn btn-primary" data-save="final">Mark as Ready &amp; Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>

<script>
var CURRENT_LAB_STAFF = <?php echo json_encode($__labStaff); ?>;
var labCatLabels = <?php echo json_encode($__labCategories); ?>;
var labParametersData = <?php echo json_encode($__labParamData); ?>;

var labData = [];
var labResultsMap = {};
var visitsCache = [];
var DISPATCH_KEY = 'hms:lab-dispatched';
var LAB_VIEW = 'doctors';
var knownReqIds = {};
var labPollTimer = null;
var POLL_INTERVAL_MS = 20000;

async function initLabManagement() {
    setupLabEvents();
    await Promise.all([loadVisits(), loadLabRequests()]);
    startLiveRefresh();
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

function labGoSection(id) {
    var el = document.getElementById(id);
    if (el && el.scrollIntoView) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function labCardTab(view) {
    labSwitchTab(view);
    labGoSection('labTabTables');
}

/* ================= REFRESH / LIVE AUTO-REFRESH ================= */
function labRefresh() {
    Promise.all([loadVisits(), loadLabRequests()]).then(function () {
        showAlert('Lab data refreshed.', 'success');
    }).catch(function () {
        showAlert('Refresh failed - please try again.', 'error');
    });
}

function startLiveRefresh() {
    if (labPollTimer) clearInterval(labPollTimer);
    labPollTimer = setInterval(livePoll, POLL_INTERVAL_MS);
}

/* Silent poll: refreshes the tables in place and raises the new-request alert
   only when a request that was not there before actually appears. */
async function livePoll() {
    if (!document.getElementById('labTabs')) return;   /* page no longer mounted */
    try {
        var [labRes, visitRes] = await Promise.all([
            fetch('/hms/backend/api/lab.php?action=requests'),
            fetch('/hms/backend/api/visits.php')
        ]);
        var labJson = await labRes.json();
        var visitJson = await visitRes.json();

        if (visitJson && visitJson.success) {
            visitsCache = visitJson.visits || [];
            renderVisitSelect();
        }
        if (labJson && labJson.success) {
            var reqs = labJson.requests || [];
            var fresh = reqs.filter(function (x) { return !knownReqIds[String(x.id)]; });
            reqs.forEach(function (x) { knownReqIds[String(x.id)] = true; });
            labData = reqs;
            await loadLabResults();
            renderLabPanes();
            populateEntryRequisition();
            if (fresh.length) showNewRequestAlert(fresh[0]);
        }
    } catch (e) {
        console.error('Live refresh error:', e);
    }
}

/* ================= VISITS / PATIENT SELECT ================= */
async function loadVisits() {
    try {
        var r = await fetch('/hms/backend/api/visits.php');
        var d = await r.json();
        if (d.success) visitsCache = d.visits || [];
    } catch (e) {
        console.error('Visits load error:', e);
        visitsCache = [];
    }
    renderVisitSelect();
}

function renderVisitSelect() {
    var sel = document.getElementById('entryPatient');
    if (!sel) return;
    var keep = sel.value;
    sel.innerHTML = '<option value="">-- Select Patient --</option>'
        + visitsCache.map(function (v) {
            return '<option value="' + v.id + '">' + escHtml(v.visit_number + ' - ' + v.patient_name + ' (' + v.hospital_number + ')') + '</option>';
        }).join('');
    if (keep) sel.value = keep;
}

/* ================= DYNAMIC PARAMETERS & PRICE ================= */
function updateLabParameters() {
    var paramSel = document.getElementById('labParameterSelect');
    var cat = document.getElementById('labTypeSelect').value;
    if (!paramSel) return;
    paramSel.innerHTML = '<option value="">-- Select Lab Parameter --</option>';
    document.getElementById('labPriceInput').value = '';
    if (cat && labParametersData[cat]) {
        labParametersData[cat].forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = item.name;
            opt.setAttribute('data-code', item.code);
            opt.setAttribute('data-price', item.price);
            opt.textContent = item.name + ' - GHS ' + item.price;
            paramSel.appendChild(opt);
        });
    }
}

function updateLabPrice() {
    var sel = document.getElementById('labParameterSelect');
    var opt = sel && sel.options[sel.selectedIndex];
    document.getElementById('labPriceInput').value = 'GHS ' + (opt && opt.getAttribute('data-price') ? opt.getAttribute('data-price') : '0.00');
}

async function handleLabEntrySubmit(e) {
    e.preventDefault();
    var visitId = document.getElementById('entryPatient').value;
    var cat = document.getElementById('labTypeSelect').value;
    var sel = document.getElementById('labParameterSelect');
    var opt = sel.options[sel.selectedIndex];
    if (!visitId) { showAlert('Select the patient / visit first.', 'error'); return; }
    if (!cat) { showAlert('Select the lab category.', 'error'); return; }
    if (!opt || !opt.value) { showAlert('Select the lab parameter / test to be done.', 'error'); return; }

    var price = opt.getAttribute('data-price') || '0.00';
    var code = opt.getAttribute('data-code') || '';
    var label = labCatLabels[cat] || cat;
    var data = {
        visit_id: visitId,
        test_type: opt.value,
        test_description: 'Category: ' + label + (code ? ' (' + code + ')' : '') + ' | Price: GHS ' + price,
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
            showAlert('Test saved & submitted: ' + opt.value + ' - GHS ' + price + '.', 'success');
            document.getElementById('labEntryForm').reset();
            document.getElementById('labPriceInput').value = '';
            labSwitchTab('doctors');
            await loadLabRequests();
        } else {
            showAlert(d.error || 'Submit failed', 'error');
        }
    } catch (err) {
        console.error('Lab request error:', err);
        showAlert('Network error. Please try again.', 'error');
    }
}

/* ================= LAB DATA ================= */
async function loadLabRequests() {
    try {
        var r = await fetch('/hms/backend/api/lab.php?action=requests');
        var d = await r.json();
        labData = (d.success && d.requests) ? d.requests : [];
        (labData || []).forEach(function (x) { knownReqIds[String(x.id)] = true; });
    } catch (e) {
        console.error('Lab load error:', e);
        labData = [];
    }
    await loadLabResults();
    renderLabPanes();
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

/* ================= TABS ================= */
var LAB_TABS = ['doctors', 'pending', 'ready', 'notready', 'dispatched'];

function labSwitchTab(view) {
    if (LAB_TABS.indexOf(view) === -1) view = 'doctors';
    LAB_VIEW = view;
    LAB_TABS.forEach(function (t) {
        var btn = document.getElementById('labTab' + t.charAt(0).toUpperCase() + t.slice(1));
        var pane = document.getElementById('labPane' + t.charAt(0).toUpperCase() + t.slice(1));
        if (btn) btn.classList.toggle('active', t === view);
        if (pane) pane.classList.toggle('active', t === view);
    });
    renderLabPanes();
}

/* ================= TABLES ================= */
function labFilter(q) {
    if (!q) return function () { return true; };
    return function (r) {
        return String(r.patient_name || '').toLowerCase().indexOf(q) >= 0
            || String(r.hospital_number || '').toLowerCase().indexOf(q) >= 0
            || String(r.test_type || '').toLowerCase().indexOf(q) >= 0
            || String(r.id || '').indexOf(q) >= 0;
    };
}

function labStatusBadge(status) {
    if (status === 'completed') return '<span class="lab-badge lab-status-ready">Ready</span>';
    if (status === 'in_progress') return '<span class="lab-badge lab-status-sent">In Progress</span>';
    if (status === 'cancelled') return '<span class="lab-badge lab-status-abs">ABS</span>';
    return '<span class="lab-badge lab-status-not">Pending</span>';
}

function standardRequestTable(rows, q, actionCell, emptyMsg) {
    return '<table class="lab-table"><thead><tr>'
        + '<th class="first-col">Req ID</th><th>Patient ID/Name</th><th>Lab Test Requested</th><th>Doctor</th><th>Date &amp; Time</th><th>Status</th><th class="act-col">Action</th>'
        + '</tr></thead><tbody>' + (rows.length
            ? rows.map(function (r) {
                return '<tr>'
                    + '<td class="first-col" style="font-weight:700;">LAB ' + escHtml(r.id) + '</td>'
                    + '<td style="font-weight:700;">' + escHtml((r.patient_name || '--') + (r.hospital_number ? ' (' + r.hospital_number + ')' : '')) + '</td>'
                    + '<td>' + escHtml(r.test_type || '--') + '</td>'
                    + '<td>' + escHtml(r.doctor_name || '--') + '</td>'
                    + '<td>' + fmtLabTime(r.requested_at) + '</td>'
                    + '<td>' + labStatusBadge(r.status) + '</td>'
                    + '<td class="act-col">' + actionCell(r) + '</td>'
                    + '</tr>';
            }).join('')
            : '<tr><td colspan="7" class="lab-empty">' + escHtml(emptyMsg) + '</td></tr>')
        + '</tbody></table>';
}

function renderLabPanes() {
    var searchEl = document.getElementById('labGlobalSearch');
    if (!searchEl) return;   /* page not mounted */
    var q = (searchEl.value || '').trim().toLowerCase();
    var pass = labFilter(q);
    var dispatched = readDispatched();

    var processBtn = function (label) {
        return function (r) { return '<button class="lab-btn-primary" onclick="openEntryFor(' + r.id + ')">' + label + '</button>'; };
    };
    var dispatchBtn = function (r) {
        return '<button class="lab-btn-success" onclick="dispatchReport(' + r.id + ')">'
            + '<svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Dispatch</button>';
    };
    var defaultAction = function (r) {
        if (r.status === 'pending' || r.status === 'in_progress') return processBtn('Process Test')(r);
        if (r.status === 'completed') return dispatchBtn(r);
        return '<span style="color:#8a94a6;">-</span>';
    };

    /* counts */
    setCount('cAll', labData.length);
    setCount('cPending', labData.filter(function (r) { return r.status === 'pending'; }).length);
    setCount('cNotReady', labData.filter(function (r) { return r.status === 'in_progress'; }).length);
    setCount('cReady', labData.filter(function (r) { return r.status === 'completed'; }).length);
    setCount('cDispatched', dispatched.length);

    /* Doctor Requests - every request raised by a doctor */
    setPane('labPaneDoctors', standardRequestTable(
        labData.filter(pass), q, defaultAction,
        q ? 'No requests match "' + q + '".' : 'No lab requests yet - use the technician form above to add one.'
    ));

    /* Pending Labs - awaiting sample collection / analysis */
    setPane('labPanePending', standardRequestTable(
        labData.filter(function (r) { return r.status === 'pending'; }).filter(pass), q, processBtn('Process Test'),
        q ? 'No pending requests match "' + q + '".' : 'Nothing pending - all requested tests have been processed.'
    ));

    /* Not Ready Labs - currently under processing in the analyzer */
    setPane('labPaneNotReady', standardRequestTable(
        labData.filter(function (r) { return r.status === 'in_progress'; }).filter(pass), q, processBtn('Enter Results'),
        q ? 'Nothing in progress matches "' + q + '".' : 'No test is currently under processing.'
    ));

    /* Ready Labs - verified results awaiting dispatch */
    var ready = labData.filter(function (r) {
        return r.status === 'completed' && !dispatched.some(function (d) { return d.id === String(r.id); });
    }).filter(pass);
    setPane('labPaneReady', '<table class="lab-table"><thead><tr>'
        + '<th class="first-col">Req ID</th><th>Patient ID/Name</th><th>Lab Test</th><th>Result</th><th>Verified / Completed</th><th>Status</th><th class="act-col">Action</th>'
        + '</tr></thead><tbody>' + (ready.length
            ? ready.map(function (r) {
                var labRes = labResultsMap[String(r.id)] || null;
                var resultText = labRes ? String(labRes.results || '--') : String(r.result_status || '--');
                var doneAt = labRes && labRes.completed_at ? fmtLabTime(labRes.completed_at) : fmtLabTime(r.requested_at);
                return '<tr>'
                    + '<td class="first-col" style="font-weight:700;">LAB ' + escHtml(r.id) + '</td>'
                    + '<td style="font-weight:700;">' + escHtml(r.patient_name || '--') + '</td>'
                    + '<td>' + escHtml(r.test_type || '--') + '</td>'
                    + '<td style="max-width:240px;white-space:pre-wrap;">' + escHtml(resultText) + '</td>'
                    + '<td>' + (labRes && labRes.verified_name ? escHtml(labRes.verified_name) + '<br>' : '') + doneAt + '</td>'
                    + '<td>' + labStatusBadge(r.status) + '</td>'
                    + '<td class="act-col"><button class="lab-btn-success" onclick="dispatchReport(' + r.id + ')">Dispatch Report</button></td>'
                    + '</tr>';
            }).join('')
            : '<tr><td colspan="7" class="lab-empty">' + escHtml(q ? 'No ready labs match "' + q + '".' : 'No verified results are ready for dispatch yet.') + '</td></tr>')
        + '</tbody></table>');

    /* Dispatched Labs - history of reports handed out */
    var dlist = dispatched.map(function (d) {
        var r = labData.find(function (x) { return String(x.id) === d.id; }) || null;
        return '<tr>'
            + '<td class="first-col" style="font-weight:700;">DSP ' + escHtml(d.id) + '</td>'
            + '<td style="font-weight:700;">' + escHtml(r ? r.patient_name : '--') + '</td>'
            + '<td>' + escHtml(r ? r.test_type : '--') + '</td>'
            + '<td>' + (d.at ? fmtLabTime(d.at) : '--') + '</td>'
            + '<td class="act-col"><button class="lab-btn-outline" onclick="printReport(' + (r ? r.id : 0) + ')">Print PDF</button></td>'
            + '</tr>';
    }).join('');
    setPane('labPaneDispatched', '<table class="lab-table"><thead><tr>'
        + '<th class="first-col">Dispatch ID</th><th>Patient Name</th><th>Lab Test</th><th>Dispatch Date</th><th class="act-col">Report</th>'
        + '</tr></thead><tbody>' + (dlist || '<tr><td colspan="5" class="lab-empty">No dispatched reports yet - dispatch a ready lab above.</td></tr>')
        + '</tbody></table>');
}

function setCount(id, val) {
    var el = document.getElementById(id);
    if (el) el.textContent = val;
}

function setPane(id, html) {
    var el = document.getElementById(id);
    if (el) el.innerHTML = html;
}

/* ================= NEW REQUEST ALERT ================= */
function showNewRequestAlert(req) {
    var modal = document.getElementById('labNewRequestModal');
    var details = document.getElementById('labAlertDetails');
    if (!modal || !details) return;
    details.textContent = 'Patient: ' + (req.patient_name || '--')
        + ' | Test: ' + (req.test_type || '--')
        + ' | Doctor: ' + (req.doctor_name || '--')
        + ' | ' + fmtLabTime(req.requested_at);
    modal.classList.add('show');
}

function closeNewRequestModal() {
    var modal = document.getElementById('labNewRequestModal');
    if (modal) modal.classList.remove('show');
}

function viewNewRequest() {
    closeNewRequestModal();
    labSwitchTab('doctors');
    labGoSection('labTabTables');
}

/* ================= RESULT ENTRY ================= */
function populateEntryRequisition() {
    var sel = document.getElementById('entryRequisition');
    if (!sel) return;
    var keep = sel.value;
    var rows = labData.filter(function (r) { return r.status === 'pending' || r.status === 'in_progress'; });
    sel.innerHTML = rows.length
        ? rows.map(function (r) { return '<option value="' + r.id + '">LAB ' + r.id + ' - ' + escHtml(r.patient_name) + ' (' + escHtml(r.test_type) + ')</option>'; }).join('')
        : '<option value="">-- Awaiting requisitions --</option>';
    if (keep && rows.some(function (r) { return String(r.id) === String(keep); })) sel.value = keep;
}

function openEntryFor(requestId) {
    populateEntryRequisition();
    var sel = document.getElementById('entryRequisition');
    if (sel && Array.from(sel.options).some(function (o) { return String(o.value) === String(requestId); })) {
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
        showAlert('Select a requisition first - there are no pending lab requests to record results for.', 'error');
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
            labSwitchTab(saveAs === 'final' ? 'ready' : 'notready');
            await loadLabRequests();
        } else {
            showAlert(d.error || 'Save failed', 'error');
        }
    } catch (err) {
        console.error('Entry save error:', err);
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
    renderLabPanes();
    labSwitchTab('dispatched');
}

function printReport(requestId) {
    var r = labData.find(function (x) { return String(x.id) === String(requestId); });
    showAlert('Report LAB ' + (r ? r.id : '') + ' queued for printing - use Ctrl+P to export the PDF.', 'info');
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
