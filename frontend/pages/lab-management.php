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
/* ============= LAB MANAGEMENT & INVESTIGATION MODULE ============
   Scoped under #lab-page. No Bootstrap/Tailwind — every utility used here is
   defined below. Font Awesome is available. Element ids/classes consumed by the
   page JS (labTabs, cAll..cDispatched, lab-pane, lab-table, ...) are preserved. */
#lab-page{
    --brand:#1b659d; --brand-dk:#155380;
    --s50:#F8FAFC; --s100:#F1F5F9; --s200:#E2E8F0; --s300:#CBD5E1;
    --s400:#94A3B8; --s500:#64748B; --s600:#475569; --s700:#334155; --s800:#1E293B;
    background:var(--s50);border-radius:12px;padding:14px;color:var(--s700);font-size:12px;
    font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
}
#lab-page *,#lab-page *::before,#lab-page *::after{box-sizing:border-box}

/* ---- MODULE HEADER BANNER ---- */
#lab-page .lab-wrap{max-width:1240px;margin:0 auto}
#lab-page .lab-modhead{background:var(--brand);color:#fff;padding:14px 16px;border-radius:12px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;box-shadow:0 1px 2px rgba(15,23,42,.08)}
#lab-page .lab-modhead .left{display:flex;align-items:center;gap:12px;min-width:0}
#lab-page .mh-ic{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22);border-radius:10px;width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto}
#lab-page .mh-ic i{font-size:19px;color:#BFDBFE}
#lab-page .lab-modhead h2{margin:0;font-size:14px;font-weight:800;text-transform:uppercase;letter-spacing:.5px}
#lab-page .mh-sub{margin:2px 0 0;font-size:11px;color:#BFDBFE}
#lab-page .lab-modhead .actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
#lab-page .lab-h-btn{border:1px solid rgba(255,255,255,.22);background:rgba(255,255,255,.10);color:#fff;border-radius:8px;cursor:pointer;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.4px;display:inline-flex;align-items:center;gap:6px;padding:7px 13px;font-family:inherit;transition:background .15s}
#lab-page .lab-h-btn:hover{background:rgba(255,255,255,.2)}
#lab-page .lab-h-btn svg{width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#lab-page .lab-live-badge{background:rgba(16,185,129,.18);color:#A7F3D0;border:1px solid rgba(52,211,153,.35);border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.4px;padding:5px 12px;display:inline-flex;align-items:center;gap:7px;text-transform:uppercase}
#lab-page .lab-live-badge .dot{width:8px;height:8px;border-radius:50%;background:#34D399;animation:labPulse 1.6s infinite}
@keyframes labPulse{0%,100%{opacity:1}50%{opacity:.35}}

/* ---- QUICK STAT TILES ---- */
#lab-page .lab-feature-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:14px 0}
#lab-page .lab-feature-card{display:flex;align-items:center;justify-content:space-between;gap:12px;background:#fff;border:1px solid var(--s200);border-radius:12px;padding:14px 16px;cursor:pointer;text-align:left;font-family:inherit;box-shadow:0 1px 2px rgba(15,23,42,.05);transition:border-color .15s,box-shadow .15s,transform .15s}
#lab-page .lab-feature-card:hover{border-color:var(--brand);box-shadow:0 6px 16px rgba(15,23,42,.10);transform:translateY(-1px)}
#lab-page .fc-text{display:flex;flex-direction:column;gap:2px;min-width:0}
#lab-page .fc-title{font-size:10px;font-weight:800;color:var(--s400);text-transform:uppercase;letter-spacing:.4px}
#lab-page .fc-num{font-size:17px;font-weight:800;color:var(--brand)}
#lab-page .fc-num b{font-weight:800}
#lab-page .fc-num .u{font-size:11px;font-weight:700;color:var(--s500)}
#lab-page .fc-sub{font-size:10px;color:var(--s500)}
#lab-page .fc-ic{width:46px;height:46px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;font-size:18px}
#lab-page .fc-ic.blue{background:#EFF6FF;color:var(--brand);border:1px solid #BFDBFE}
#lab-page .fc-ic.green{background:#ECFDF5;color:#059669;border:1px solid #A7F3D0}
#lab-page .fc-ic.amber{background:#FFFBEB;color:#D97706;border:1px solid #FDE68A}
#lab-page .fc-ic.slate{background:var(--s100);color:var(--s600);border:1px solid var(--s200)}

/* ---- PANELS ---- */
#lab-page .lab-panel{background:#fff;border:1px solid var(--s200);border-radius:12px;overflow:hidden;box-shadow:0 1px 2px rgba(15,23,42,.05)}
#lab-page .lab-panel + .lab-panel{margin-top:14px}
#lab-page .lab-panel.is-first{margin-top:0}
#lab-page .lab-panel-head{background:var(--brand);color:#fff;font-weight:800;font-size:11px;padding:11px 16px;text-transform:uppercase;letter-spacing:.4px;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
#lab-page .lab-panel-head .head-left{display:flex;align-items:center;gap:8px}
#lab-page .lab-panel-head svg{width:16px;height:16px;fill:none;stroke:#ffffff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#lab-page .lab-panel-head.dark{background:var(--s800);padding:0}
#lab-page .lab-panel-head.dark svg{fill:none;stroke:#fff}

/* ---- ENTRIES / TECHNICIAN FORM ---- */
#lab-page .lab-form{display:grid;grid-template-columns:repeat(6,1fr);gap:12px 16px;padding:16px 18px;background:#fff}
#lab-page .lab-field{display:flex;flex-direction:column;gap:5px}
#lab-page .lab-field label{font-weight:800;color:var(--s600);font-size:10px;text-transform:uppercase;letter-spacing:.4px}
#lab-page .lab-field label .req{color:#E74C3C;font-weight:800}
#lab-page .lab-field select,#lab-page .lab-field input{width:100%;padding:9px 11px;border:1px solid var(--s300);border-radius:8px;font-size:12px;background:var(--s50);color:var(--s800);font-weight:600;font-family:inherit;transition:border-color .15s,box-shadow .15s}
#lab-page .lab-field select:focus,#lab-page .lab-field input:focus{outline:none;border-color:var(--brand);background:#fff;box-shadow:0 0 0 3px rgba(27,101,157,.15)}
#lab-page .lab-field input[readonly]{background:var(--s100);color:var(--brand);font-weight:800;cursor:not-allowed}
#lab-page .span-3{grid-column:span 3}
#lab-page .span-4{grid-column:span 4}
#lab-page .span-2{grid-column:span 2}
#lab-page .span-6{grid-column:span 6}
#lab-page .lab-form-actions{grid-column:span 6;display:flex;justify-content:flex-end;margin-top:2px;padding-top:14px;border-top:1px solid var(--s200)}
#lab-page .lab-btn-submit{background:var(--brand);color:#fff;border:none;padding:10px 22px;font-weight:800;font-size:11px;border-radius:8px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.4px;display:inline-flex;align-items:center;gap:8px;box-shadow:0 1px 2px rgba(27,101,157,.25)}
#lab-page .lab-btn-submit:hover{background:var(--brand-dk)}
#lab-page .lab-btn-submit svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

/* ---- TABS ---- */
#lab-page .lab-tabs{display:flex;flex-wrap:wrap}
#lab-page .lab-tab{background:transparent;border:none;border-right:1px solid rgba(255,255,255,.12);color:#CBD5E1;font-family:inherit;font-weight:800;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:12px 16px;cursor:pointer;transition:background .15s,color .15s}
#lab-page .lab-tab:last-child{border-right:none}
#lab-page .lab-tab:hover{background:rgba(255,255,255,.08);color:#fff}
#lab-page .lab-tab.active{background:var(--brand);color:#fff}

/* ---- VIEW TOOLBAR (search) ---- */
#lab-page .lab-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:10px 14px;border-bottom:1px solid var(--s200);background:var(--s50)}
#lab-page .lab-toolbar .search{display:flex;align-items:center;gap:8px;flex:1 1 240px;min-width:200px}
#lab-page .lab-toolbar .search svg{width:16px;height:16px;fill:none;stroke:var(--s400);stroke-width:2;flex-shrink:0}
#lab-page .lab-toolbar .search input{flex:1;padding:8px 10px;border:1px solid var(--s300);border-radius:8px;font-size:12px;font-family:inherit;background:#fff;color:var(--s800)}
#lab-page .lab-toolbar .search input:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 3px rgba(27,101,157,.15)}
#lab-page .lab-counts{font-size:10px;color:var(--s500);font-weight:600;display:flex;gap:12px;flex-wrap:wrap}
#lab-page .lab-counts b{color:var(--s800)}

/* ---- TAB PANES + TABLE ---- */
#lab-page .lab-pane{display:none}
#lab-page .lab-pane.active{display:block}
#lab-page .lab-table-wrap{background:#fff;max-height:520px;overflow-y:auto}
#lab-page .lab-table{width:100%;border-collapse:collapse;font-size:11px}
#lab-page .lab-table th{background:var(--s100);color:var(--s600);font-weight:800;text-align:left;padding:10px;border-bottom:1px solid var(--s200);position:sticky;top:0;z-index:1;white-space:nowrap;text-transform:uppercase;font-size:10px;letter-spacing:.4px}
#lab-page .lab-table td{padding:9px 10px;border-bottom:1px solid var(--s100);vertical-align:middle}
#lab-page .lab-table tbody tr:hover{background:var(--s50)}
#lab-page .lab-table .first-col{padding-left:14px}
#lab-page .lab-table .act-col{text-align:center;white-space:nowrap}
#lab-page .lab-empty{text-align:center;padding:22px;color:var(--s400)}
#lab-page .lab-badge{display:inline-block;padding:2px 9px;border-radius:999px;font-weight:800;font-size:10px;text-transform:uppercase;letter-spacing:.3px}
#lab-page .lab-status-ready{color:#047857;background:#ECFDF5}
#lab-page .lab-status-sent{color:var(--brand);background:#EFF6FF}
#lab-page .lab-status-abs{color:#B45309;background:#FFFBEB}
#lab-page .lab-status-not{color:#B91C1C;background:#FEF2F2}
#lab-page .lab-btn-outline{background:#fff;color:var(--brand);border:1px solid var(--s300);padding:5px 11px;font-weight:800;font-size:10px;border-radius:8px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#lab-page .lab-btn-outline:hover{background:var(--s100)}
#lab-page .lab-btn-primary{background:var(--brand);color:#fff;border:none;padding:5px 12px;font-weight:800;font-size:10px;border-radius:8px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#lab-page .lab-btn-primary:hover{background:var(--brand-dk)}
#lab-page .lab-btn-success{background:#059669;color:#fff;border:none;padding:5px 11px;font-weight:800;font-size:10px;border-radius:8px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#lab-page .lab-btn-success:hover{background:#047857}
#lab-page .lab-btn-success svg{width:12px;height:12px;fill:none;stroke:currentColor;stroke-width:2}

/* ---- MODALS (scoped; both live inside #lab-page) ---- */
#lab-page .modal{display:none;position:fixed;inset:0;z-index:2000;background:rgba(15,23,42,.55);align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto}
#lab-page .modal.show{display:flex}
#lab-page .modal.centered{align-items:center}
#lab-page .modal-content{background:#fff;border-radius:14px;width:min(640px,100%);box-shadow:0 20px 44px rgba(15,23,42,.3);overflow:hidden}
#lab-page .modal-header{background:var(--brand);color:#fff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;gap:10px}
#lab-page .modal-header h3{margin:0;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;gap:8px}
#lab-page .modal-header.danger{background:#B91C1C}
#lab-page .modal-header svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#lab-page .modal-close{background:none;border:none;color:#fff;font-size:22px;line-height:1;cursor:pointer;padding:0 4px}
#lab-page .modal-close:hover{color:#FDE68A}
#lab-page .modal-body{padding:18px 20px;background:var(--s50)}
#lab-page .modal-body.text-center{text-align:center}
#lab-page .lab-alert-title{font-size:14px;font-weight:800;color:var(--s800);margin:0 0 6px}
#lab-page .lab-alert-sub{font-size:12px;color:var(--s500);margin:0}
#lab-page .modal-body .form-group{margin-bottom:14px}
#lab-page .modal-body label{display:block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--s600);margin-bottom:5px}
#lab-page .modal-body input,#lab-page .modal-body select,#lab-page .modal-body textarea{width:100%;padding:9px 11px;border:1px solid var(--s300);border-radius:8px;font-size:12px;background:var(--s50);color:var(--s800);font-family:inherit;box-sizing:border-box}
#lab-page .modal-body input[readonly]{background:var(--s100);color:var(--s500)}
#lab-page .modal-body textarea{resize:vertical}
#lab-page .modal-body input:focus,#lab-page .modal-body select:focus,#lab-page .modal-body textarea:focus{outline:none;border-color:var(--brand);background:#fff;box-shadow:0 0 0 3px rgba(27,101,157,.15)}
#lab-page .form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px;padding-top:14px;border-top:1px solid var(--s200)}
#lab-page .form-actions.center{justify-content:center;border-top:none;margin-top:8px;padding-top:0}
#lab-page .btn{border:none;border-radius:8px;cursor:pointer;font-weight:800;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:9px 18px;font-family:inherit;display:inline-flex;align-items:center;gap:6px}
#lab-page .btn-primary{background:var(--brand);color:#fff}
#lab-page .btn-primary:hover{background:var(--brand-dk)}
#lab-page .btn-secondary{background:var(--s100);color:var(--s700);border:1px solid var(--s300)}
#lab-page .btn-secondary:hover{background:var(--s200)}

@media (max-width:980px){
  #lab-page .lab-feature-grid{grid-template-columns:repeat(2,1fr)}
}
@media (max-width:760px){
  #lab-page .lab-feature-grid{grid-template-columns:1fr}
  #lab-page .lab-form{grid-template-columns:1fr}
  #lab-page .span-3,#lab-page .span-4,#lab-page .span-2,#lab-page .span-6,#lab-page .lab-form-actions{grid-column:span 1}
}

/* ---- CANCEL / RESCHEDULE TOGGLE (inside the technician form panel) ---- */
#lab-page .lab-cr-head{padding:16px 18px 0;background:#fff}
#lab-page .lab-cr-divider{height:2px;background:#FECDD3;border-radius:2px;position:relative;cursor:pointer;user-select:none;transition:background .2s}
#lab-page .lab-cr-divider:hover{background:#FDA4AF}
#lab-page .lab-cr-divider:focus-visible{outline:2px solid var(--brand);outline-offset:4px}
#lab-page .lab-cr-divider span{position:absolute;top:-10px;left:6px;background:#E11D48;color:#fff;font-size:10px;font-weight:800;padding:4px 12px;border-radius:999px;text-transform:uppercase;letter-spacing:.5px;box-shadow:0 2px 6px rgba(190,18,60,.3)}
#lab-page .lab-cr-divider .dhi-hint{font-weight:600;text-transform:none;letter-spacing:.2px;opacity:.85}
#lab-page .lab-cr-divider .dhi-caret{display:inline-block;margin-left:8px;font-size:9px;font-style:normal;opacity:.9;transition:transform .25s ease}
#lab-page .lab-cr-divider.open .dhi-caret{transform:rotate(180deg)}
#lab-page #labCrBody .lab-form{background:#FFF1F2;border-top:1px solid #FECDD3}
#lab-page .lab-cr-hint{grid-column:span 6;font-size:10.5px;color:#92400E;background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px;padding:9px 11px;margin:0}
@media (max-width:760px){
  #lab-page .lab-cr-hint{grid-column:span 1}
}
</style>

<!-- LAB MANAGEMENT & INVESTIGATION MODULE -->
<div id="lab-page">

  <div class="lab-wrap">

  <!-- MODULE HEADER BANNER -->
  <div class="lab-modhead">
    <div class="left">
      <span class="mh-ic"><i class="fa-solid fa-flask-vial"></i></span>
      <div>
        <h2>Laboratory Management &amp; Investigation Module</h2>
        <p class="mh-sub">Lab Technician Workspace &bull; Process Specimen, Verify Results &amp; Dispatch Reports</p>
      </div>
    </div>
    <div class="actions">
      <span class="lab-live-badge" id="labLiveBadge"><span class="dot"></span> Live Auto-Refresh</span>
      <button type="button" class="lab-h-btn" onclick="labRefresh()">
        <svg viewBox="0 0 24 24"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
        Refresh
      </button>
    </div>
  </div>

  <!-- QUICK STAT TILES -->
  <div class="lab-feature-grid">
    <button type="button" class="lab-feature-card" onclick="labGoSection('labFormSection')">
      <span class="fc-text">
        <span class="fc-title">Sample Management</span>
        <span class="fc-num"><b id="labStatSample">0</b> <span class="u">Logged</span></span>
        <span class="fc-sub">Register &amp; process patient specimen</span>
      </span>
      <span class="fc-ic blue"><i class="fa-solid fa-vial"></i></span>
    </button>

    <button type="button" class="lab-feature-card" onclick="labCardTab('ready')">
      <span class="fc-text">
        <span class="fc-title">Lab Verification &amp; Ready Labs</span>
        <span class="fc-num"><b id="labStatReady">0</b> <span class="u">Ready</span></span>
        <span class="fc-sub">Verify completed tests &amp; authorize results</span>
      </span>
      <span class="fc-ic green"><i class="fa-solid fa-square-check"></i></span>
    </button>

    <button type="button" class="lab-feature-card" onclick="labCardTab('pending')">
      <span class="fc-text">
        <span class="fc-title">Pending Tests</span>
        <span class="fc-num"><b id="labStatPending">0</b> <span class="u">Queued</span></span>
        <span class="fc-sub">View pending lab test requests</span>
      </span>
      <span class="fc-ic amber"><i class="fa-solid fa-clock-rotate-left"></i></span>
    </button>

    <button type="button" class="lab-feature-card" onclick="labCardTab('dispatched')">
      <span class="fc-text">
        <span class="fc-title">Report Dispatched</span>
        <span class="fc-num"><b id="labStatDispatched">0</b> <span class="u">Reports</span></span>
        <span class="fc-sub">Access dispatched lab records</span>
      </span>
      <span class="fc-ic slate"><i class="fa-solid fa-envelope-circle-check"></i></span>
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

      <!-- FEATURE 2b: CANCEL OR RESCHEDULE (toggle to show / view, with reasons) -->
      <div class="lab-cr-head">
        <div class="lab-cr-divider" id="labCrToggle" role="button" tabindex="0"
             aria-expanded="false" aria-controls="labCrBody"
             title="Press to show or hide Cancel / Reschedule">
          <span>Cancel or Reschedule Investigation <b class="dhi-hint">- toggle or press to view</b><i class="dhi-caret" aria-hidden="true">&#9660;</i></span>
        </div>
      </div>

      <div id="labCrBody" style="display:none;">
        <form class="lab-form" id="labCancelRescheduleForm">
          <div class="lab-field span-3">
            <label for="crRequisition">Investigation / Requisition <span class="req">*</span></label>
            <select id="crRequisition" required>
              <option value="">-- Select Requisition --</option>
            </select>
          </div>

          <div class="lab-field span-3">
            <label for="crAction">Action <span class="req">*</span></label>
            <select id="crAction" onchange="labCrToggleAction()" required>
              <option value="">-- Select Action --</option>
              <option value="cancel">Cancel Test</option>
              <option value="reschedule">Reschedule Test</option>
            </select>
          </div>

          <div class="lab-field span-3" id="crWhenField" style="display:none;">
            <label for="crWhen">New Date &amp; Time <span class="req">*</span></label>
            <input type="datetime-local" id="crWhen">
          </div>

          <div class="lab-field span-3">
            <label for="crReason">Reason <span class="req">*</span></label>
            <select id="crReason" required>
              <option value="">-- Select Reason --</option>
              <option>Patient not ready / not on bed</option>
              <option>Sample not collected</option>
              <option>Insufficient sample</option>
              <option>Sample haemolysed / clotted</option>
              <option>Machine / analyser fault</option>
              <option>Reagent unavailable</option>
              <option>Patient deferred / requested later</option>
              <option>Duplicate request</option>
              <option>Wrong test ordered</option>
              <option>Other (state in details)</option>
            </select>
          </div>

          <div class="lab-field span-6">
            <label for="crNotes">Details / Notes</label>
            <input type="text" id="crNotes" placeholder="Optional details, e.g. collected in the wrong tube, patient fasting not observed...">
          </div>

          <p class="lab-cr-hint">Cancelling stops the test and records the reason against the request. Rescheduling keeps the request open and returns it to the pending worklist on the new date.</p>

          <div class="lab-form-actions">
            <button type="submit" class="lab-btn-submit" id="labCrSubmit">
              <svg viewBox="0 0 24 24"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
              Submit Cancel / Reschedule
            </button>
          </div>
        </form>
      </div>
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

    /* Cancel / Reschedule toggle */
    var crToggle = document.getElementById('labCrToggle');
    if (crToggle) {
        crToggle.addEventListener('click', function () { labCrToggle(); });
        crToggle.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' || ev.key === ' ' || ev.key === 'Spacebar') {
                ev.preventDefault();
                labCrToggle();
            }
        });
    }
    var crForm = document.getElementById('labCancelRescheduleForm');
    if (crForm) crForm.addEventListener('submit', handleCancelReschedule);
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
                    + '<td>' + labStatusBadge(r.status) + labCrInfo(r) + '</td>'
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
    /* Mirror the toolbar counts into the quick-stat tiles. */
    var tileMap = { cAll: 'labStatSample', cReady: 'labStatReady', cPending: 'labStatPending', cDispatched: 'labStatDispatched' };
    var tile = tileMap[id] ? document.getElementById(tileMap[id]) : null;
    if (tile) tile.textContent = val;
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
    populateCrRequisitions();
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

/* ================= CANCEL / RESCHEDULE (Lab Technician Form) =================
   Toggle reveals a panel that cancels or reschedules an outstanding request
   (pending / in_progress) with a reason. Reschedule also needs the new
   date/time; the backend stores the reason and returns the request to pending. */
function labCrToggle(open) {
    var divider = document.getElementById('labCrToggle');
    var body = document.getElementById('labCrBody');
    if (!divider || !body) return;
    if (typeof open !== 'boolean') open = (body.style.display === 'none' || !body.style.display);
    body.style.display = open ? 'block' : 'none';
    divider.classList.toggle('open', open);
    divider.setAttribute('aria-expanded', open ? 'true' : 'false');
    var hint = divider.querySelector('.dhi-hint');
    if (hint) hint.textContent = open ? '- press to hide' : '- toggle or press to view';
}

function labCrToggleAction() {
    var action = document.getElementById('crAction');
    var whenField = document.getElementById('crWhenField');
    var whenInput = document.getElementById('crWhen');
    if (!action || !whenField) return;
    var isResched = action.value === 'reschedule';
    whenField.style.display = isResched ? 'flex' : 'none';
    if (whenInput) whenInput.required = isResched;
}

function populateCrRequisitions() {
    var sel = document.getElementById('crRequisition');
    if (!sel) return;
    var keep = sel.value;
    var rows = (labData || []).filter(function (r) { return r.status === 'pending' || r.status === 'in_progress'; });
    sel.innerHTML = '<option value="">-- Select Requisition --</option>'
        + rows.map(function (r) {
            return '<option value="' + r.id + '">LAB ' + escHtml(r.id) + ' - '
                + escHtml(r.patient_name || '') + ' (' + escHtml(r.test_type || '') + ')</option>';
        }).join('');
    if (keep && rows.some(function (r) { return String(r.id) === String(keep); })) sel.value = keep;
}

async function handleCancelReschedule(e) {
    e.preventDefault();
    var id = document.getElementById('crRequisition').value;
    var action = document.getElementById('crAction').value;
    var reasonSel = document.getElementById('crReason').value;
    var notes = document.getElementById('crNotes').value.trim();
    var when = document.getElementById('crWhen').value;

    if (!id) { showAlert('Select the requisition to cancel or reschedule.', 'error'); return; }
    if (!action) { showAlert('Choose Cancel or Reschedule.', 'error'); return; }
    if (!reasonSel) { showAlert('Select a reason.', 'error'); return; }
    if (action === 'reschedule' && !when) { showAlert('Enter the new date and time.', 'error'); return; }

    var reason = reasonSel + (notes ? ' - ' + notes : '');
    var payload = { id: id, reason: reason };
    if (action === 'reschedule') payload.scheduled_at = when;

    try {
        var r = await fetch('/hms/backend/api/lab.php?action=' + (action === 'cancel' ? 'cancel' : 'reschedule'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        var d = await r.json();
        if (d.success) {
            showAlert(action === 'cancel'
                ? 'Test cancelled. Reason recorded: ' + reason
                : 'Test rescheduled to ' + when.replace('T', ' ') + '.', 'success');
            e.target.reset();
            labCrToggleAction();
            await loadLabRequests();
        } else {
            showAlert(d.error || 'Action failed.', 'error');
        }
    } catch (err) {
        console.error('Cancel/reschedule error:', err);
        showAlert('Network error. Please try again.', 'error');
    }
}

/* Small note under a request's status: the cancellation reason, or the date a
   request was moved to. */
function labCrInfo(r) {
    if (r.status === 'cancelled' && r.status_reason) {
        return '<div style="font-size:9.5px;color:#B23B3B;margin-top:3px;max-width:190px;white-space:normal;">'
            + escHtml(r.status_reason) + '</div>';
    }
    if (r.rescheduled_to) {
        return '<div style="font-size:9.5px;color:#B9770E;margin-top:3px;max-width:190px;white-space:normal;">Rescheduled: '
            + fmtLabTime(r.rescheduled_to) + (r.status_reason ? ' - ' + escHtml(r.status_reason) : '') + '</div>';
    }
    return '';
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
