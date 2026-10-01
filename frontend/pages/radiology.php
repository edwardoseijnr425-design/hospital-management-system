<?php
// Radiology & Imaging Module (full-width EHMS module page).
// Layout, CSS scoping and JS conventions mirror frontend/pages/lab-management.php
// exactly - #rad-page scope, HMS topbar, module banner, feature cards, one order
// form panel, one tabbed worklist panel, scoped modals, vanilla JS.
//
// Server-side prologue: logged-in staff name, and the imaging modality
// catalogue (modality -> exam + body part) that drives the "Examination to be
// Performed" dropdown and auto-fills the region field.
require_once __DIR__ . '/../../backend/config/config.php';
$__radStaff = getCurrentUserName() ?: 'STAFF';
$__radRole  = getCurrentUserRole() ?: 'STAFF';

// ---- Imaging modality catalogue (single source of truth) -------------------
// Mirrors backend/api/radiology.php?action=catalogue and the studies the Doctor
// Station already writes (consultation_form.php). Add a study here and it appears
// in the order form automatically.
$__radCatalog = [
    'xray' => [
        ['Chest X-Ray (PA & Lateral)',    'Chest'],
        ['Chest X-Ray (Single View)',     'Chest'],
        ['Abdominal X-Ray (Plain)',       'Abdomen'],
        ['Pelvic X-Ray',                  'Pelvis'],
        ['X-Ray of the Skull',            'Head'],
        ['Spine X-Ray (Lumbar)',          'Lumbar Spine'],
        ['Extremity X-Ray',               'Extremity'],
    ],
    'ultrasound' => [
        ['Abdominal Ultrasound',          'Abdomen'],
        ['Obstetric Ultrasound',          'Abdomen / Pelvis'],
        ['Pelvic Ultrasound',             'Pelvis'],
        ['Thyroid Ultrasound',            'Neck'],
        ['Breast Ultrasound',             'Breast'],
        ['Renal Ultrasound (KUB)',        'Abdomen'],
    ],
    'ct' => [
        ['CT Head (Brain)',               'Head'],
        ['CT Chest (High Resolution)',    'Chest'],
        ['CT Abdomen & Pelvis (Contrast)','Abdomen / Pelvis'],
        ['CT Spine (Lumbar / Cervical)',  'Spine'],
    ],
    'mri' => [
        ['MRI Brain',                     'Head'],
        ['MRI Lumbar Spine',              'Lumbar Spine'],
        ['MRI Knee',                      'Knee'],
        ['MRI Abdomen',                   'Abdomen'],
    ],
    'mammography' => [
        ['Mammogram (Bilateral)',         'Breast'],
        ['Mammogram (Unilateral)',        'Breast'],
    ],
    'fluoroscopy' => [
        ['Barium Swallow',                'Chest / Oesophagus'],
        ['Barium Meal',                   'Abdomen'],
        ['IV Urography',                  'Abdomen / Pelvis'],
    ],
];
// Flatten for the two-step (modality -> [{exam, body_part}]) dropdown.
$__radStudyData = [];
foreach ($__radCatalog as $__mod => $__studies) {
    $__radStudyData[$__mod] = [];
    foreach ($__studies as $__s) {
        $__radStudyData[$__mod][] = ['exam_type' => $__s[0], 'body_part' => $__s[1]];
    }
}
?>
<style>
/* ============= RADIOLOGY & IMAGING MODULE : FULL-WIDTH ======================
   Scoped under #rad-page. No Bootstrap - every utility used here is defined
   below. Font Awesome is available (the shell loads
   frontend/assets/fontawesome/css/all.min.css).
   Structure intentionally mirrors #lab-page so the two departments read as
   one system: same topbar, banner, cards, panels, tabs, tables and modals.
   No inline support footer: the shell auto-appends the system banner. */
#rad-page{background:#dce7f2;color:#333;font-size:13px;min-height:100vh;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;padding-bottom:30px}
#rad-page *,#rad-page *::before,#rad-page *::after{box-sizing:border-box}

/* ---- TOP BAR (HMS - HEALTHCARE MANAGEMENT SYSTEM branding) ---- */
#rad-page .rad-topbar{background-color:#0b5fa5;color:#fff;padding:8px 20px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 5px rgba(0,0,0,0.15);flex-wrap:wrap;gap:8px}
#rad-page .rad-topbar .title-group{display:flex;align-items:center;gap:10px}
#rad-page .rad-topbar .title-group svg{width:22px;height:22px;fill:none;stroke:#ffffff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#rad-page .rad-topbar-title{font-size:16px;font-weight:bold;letter-spacing:.5px;text-transform:uppercase}
#rad-page .rad-topbar-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
#rad-page .rad-topbar-right > span{font-size:11px}
#rad-page .rad-nav-btn{background-color:#0088cc;color:#fff;border:none;padding:5px 12px;font-size:11px;font-weight:bold;border-radius:3px;cursor:pointer;text-decoration:none;font-family:inherit;display:inline-flex;align-items:center;gap:5px}
#rad-page .rad-nav-btn:hover{background-color:#006699}

/* ---- MODULE HEADER BANNER ---- */
#rad-page .rad-wrap{max-width:1240px;margin:0 auto;padding:12px 16px 16px}
#rad-page .rad-modhead{background:linear-gradient(135deg,#0D47A1,#0072BC);color:#fff;padding:9px 16px;border-radius:6px 6px 0 0;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;box-shadow:0 2px 6px rgba(13,71,161,.22)}
#rad-page .rad-modhead .left{display:flex;align-items:center;gap:10px}
#rad-page .rad-modhead .left svg{width:22px;height:22px;fill:none;stroke:#ffffff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#rad-page .rad-modhead h2{margin:0;font-size:15px;font-weight:800;text-transform:uppercase;letter-spacing:.6px}
#rad-page .rad-modhead .actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
#rad-page .rad-h-btn{border:none;border-radius:3px;cursor:pointer;font-weight:bold;font-size:11px;text-transform:uppercase;letter-spacing:.4px;display:inline-flex;align-items:center;gap:6px;padding:6px 14px;font-family:inherit;transition:background .15s,filter .15s}
#rad-page .rad-h-btn svg{width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#rad-page .rad-h-btn.light{background-color:#fff;color:#0D47A1}
#rad-page .rad-h-btn.light:hover{filter:brightness(.96)}
#rad-page .rad-live-badge{background:#fff;color:#1E7A34;border-radius:999px;font-size:10px;font-weight:bold;letter-spacing:.4px;padding:4px 11px;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase}
#rad-page .rad-live-badge i{color:#27ae60}

/* ---- FEATURE NAVIGATION GRID ---- */
#rad-page .rad-feature-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:14px 0 16px}
#rad-page .rad-feature-card{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid #b2c8de;border-radius:6px;padding:14px 16px;cursor:pointer;text-align:left;font-family:inherit;box-shadow:0 2px 5px rgba(0,0,0,.07);transition:transform .15s,box-shadow .15s,border-color .15s}
#rad-page .rad-feature-card:hover{transform:translateY(-2px);box-shadow:0 6px 14px rgba(0,0,0,.14);border-color:#0b5fa5}
#rad-page .rad-feature-card svg{width:40px;height:40px;flex-shrink:0;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#rad-page .rad-feature-card .fc-title{font-size:12.5px;font-weight:800;color:#c0392b;text-transform:uppercase;letter-spacing:.4px;margin:0 0 2px;display:block}
#rad-page .rad-feature-card .fc-sub{font-size:11px;color:#64748b;margin:0;display:block}

/* ---- PANELS ---- */
#rad-page .rad-panel{background:#fff;border:1px solid #b2c8de;border-top:none;border-radius:0 0 6px 6px;overflow:hidden;box-shadow:0 3px 8px rgba(0,0,0,.06)}
#rad-page .rad-panel + .rad-panel{margin-top:14px}
#rad-page .rad-panel.is-first{border-top:1px solid #b2c8de;border-radius:6px}
#rad-page .rad-panel-head{background-color:#0b5fa5;color:#fff;font-weight:bold;font-size:12px;padding:9px 14px;text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
#rad-page .rad-panel-head.dark{background-color:#1f2933;padding:0}
#rad-page .rad-panel-head .head-left{display:flex;align-items:center;gap:8px}
#rad-page .rad-panel-head svg{width:17px;height:17px;fill:none;stroke:#ffffff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

/* ---- ORDER FORM ---- */
#rad-page .rad-form{display:grid;grid-template-columns:repeat(6,1fr);gap:12px 16px;padding:16px 18px;background-color:#f8fafc}
#rad-page .rad-field{display:flex;flex-direction:column;gap:5px}
#rad-page .rad-field label{font-weight:700;color:#222;font-size:11px;text-transform:uppercase;letter-spacing:.3px}
#rad-page .rad-field label .req{color:#e74c3c;font-weight:bold}
#rad-page .rad-field select,#rad-page .rad-field input,#rad-page .rad-field textarea{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;font-family:inherit}
#rad-page .rad-field textarea{resize:vertical}
#rad-page .rad-field select:focus,#rad-page .rad-field input:focus,#rad-page .rad-field textarea:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#rad-page .rad-field input[readonly]{background-color:#eef3f8;color:#1E7A34;font-weight:bold;cursor:not-allowed}
#rad-page .span-2{grid-column:span 2}
#rad-page .span-3{grid-column:span 3}
#rad-page .span-4{grid-column:span 4}
#rad-page .span-6{grid-column:span 6}
#rad-page .rad-form-actions{grid-column:span 6;display:flex;justify-content:flex-end;gap:10px;margin-top:2px}
#rad-page .rad-btn-submit{background-color:#0b5fa5;color:#fff;border:none;padding:9px 24px;font-weight:bold;font-size:12px;border-radius:3px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.4px;display:inline-flex;align-items:center;gap:8px}
#rad-page .rad-btn-submit:hover{background-color:#004080}
#rad-page .rad-btn-submit svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

/* ---- TABS ---- */
#rad-page .rad-tabs{display:flex;flex-wrap:wrap}
#rad-page .rad-tab{background:transparent;border:none;border-right:1px solid rgba(255,255,255,.18);color:#dbe6f2;font-family:inherit;font-weight:bold;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:11px 16px;cursor:pointer}
#rad-page .rad-tab:last-child{border-right:none}
#rad-page .rad-tab:hover{background:rgba(255,255,255,.08);color:#fff}
#rad-page .rad-tab.active{background:#0b5fa5;color:#fff;box-shadow:inset 0 -3px 0 #FFD166}
#rad-page .rad-tab .tab-count{background:rgba(255,255,255,.2);border-radius:999px;padding:1px 6px;margin-left:5px;font-size:10px}
#rad-page .rad-tab.active .tab-count{background:#FFD166;color:#0D47A1}

/* ---- VIEW TOOLBAR ---- */
#rad-page .rad-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:10px 14px;border-bottom:1px solid #e1e8f0;background-color:#f8fafc}
#rad-page .rad-toolbar .search{display:flex;align-items:center;gap:8px;flex:1 1 240px;min-width:200px}
#rad-page .rad-toolbar .search svg{width:16px;height:16px;fill:none;stroke:#0b5fa5;stroke-width:2;flex-shrink:0}
#rad-page .rad-toolbar .search input{flex:1;padding:6px 9px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;font-family:inherit;background:#fff}
#rad-page .rad-toolbar .search input:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#rad-page .rad-counts{font-size:10.5px;color:#64748b;display:flex;gap:12px;flex-wrap:wrap}
#rad-page .rad-counts b{color:#0b5fa5}
#rad-page .rad-filters{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
#rad-page .rad-filters select{padding:5px 8px;border:1px solid #b2c8de;border-radius:3px;font-size:11px;font-family:inherit;background:#fff}

/* ---- TAB PANES + TABLE ---- */
#rad-page .rad-pane{display:none}
#rad-page .rad-pane.active{display:block}
#rad-page .rad-table-wrap{background-color:#fff;max-height:460px;overflow-y:auto}
#rad-page .rad-table{width:100%;border-collapse:collapse;font-size:11px}
#rad-page .rad-table th{background-color:#e6eef5;color:#222;font-weight:bold;text-align:left;padding:8px;border-bottom:2px solid #b2c8de;position:sticky;top:0;z-index:1;white-space:nowrap;text-transform:uppercase;font-size:10.5px;letter-spacing:.3px}
#rad-page .rad-table td{padding:7px 8px;border-bottom:1px solid #e1e8f0;vertical-align:middle}
#rad-page .rad-table tbody tr:hover{background-color:#eef4fb}
#rad-page .rad-table .first-col{padding-left:14px}
#rad-page .rad-table .act-col{text-align:center;white-space:nowrap}
#rad-page .rad-empty{text-align:center;padding:18px;color:#8a94a6}
#rad-page .rad-badge{display:inline-block;padding:2px 8px;border-radius:3px;font-weight:bold;font-size:10px;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
#rad-page .rad-badge.grey{color:#64748B;background:#EEF1F5}
#rad-page .rad-badge.amber{color:#B9770E;background:#FEF5E0}
#rad-page .rad-badge.blue{color:#0D47A1;background:#E8F4FD}
#rad-page .rad-badge.green{color:#1E7A34;background:#E9F9EF}
#rad-page .rad-badge.red{color:#B23B3B;background:#FDEBEC}
#rad-page .rad-badge.purple{color:#6D28D9;background:#F1EBFE}
#rad-page .rad-btn-outline{background-color:#fff;color:#0b5fa5;border:1px solid #b2c8de;padding:4px 11px;font-weight:bold;font-size:10px;border-radius:3px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#rad-page .rad-btn-outline:hover{background-color:#e2edf7}
#rad-page .rad-btn-primary{background-color:#0b5fa5;color:#fff;border:none;padding:4px 12px;font-weight:bold;font-size:10px;border-radius:3px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#rad-page .rad-btn-primary:hover{background-color:#004080}
#rad-page .rad-btn-success{background-color:#27ae60;color:#fff;border:none;padding:4px 11px;font-weight:bold;font-size:10px;border-radius:3px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#rad-page .rad-btn-success:hover{background-color:#1e8c4d}
#rad-page .rad-btn-danger{background-color:#c0392b;color:#fff;border:none;padding:4px 11px;font-weight:bold;font-size:10px;border-radius:3px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px}
#rad-page .rad-btn-danger:hover{background-color:#96281b}
#rad-page .rad-btn-success svg,#rad-page .rad-btn-outline svg{width:12px;height:12px;fill:none;stroke:currentColor;stroke-width:2}
#rad-page .rad-muted{color:#8a94a6}
#rad-page .rad-sub{font-size:10px;color:#64748b;display:block}

/* ---- MODALS (scoped; both live inside #rad-page) ---- */
#rad-page .modal{display:none;position:fixed;inset:0;z-index:2000;background:rgba(15,45,89,.55);align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto}
#rad-page .modal.show{display:flex}
#rad-page .modal.centered{align-items:center}
#rad-page .modal-content{background:#fff;border-radius:8px;width:min(640px,100%);box-shadow:0 12px 34px rgba(0,0,0,.25);overflow:hidden}
#rad-page .modal-header{background-color:#0b5fa5;color:#fff;padding:13px 18px;display:flex;justify-content:space-between;align-items:center;gap:10px}
#rad-page .modal-header h3{margin:0;font-size:14px;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;gap:8px}
#rad-page .modal-header.danger{background-color:#c0392b}
#rad-page .modal-header svg{width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#rad-page .modal-close{background:none;border:none;color:#fff;font-size:22px;line-height:1;cursor:pointer;padding:0 4px}
#rad-page .modal-close:hover{color:#FFD54F}
#rad-page .modal-body{padding:18px 20px;background:#F8FAFC}
#rad-page .modal-body.text-center{text-align:center}
#rad-page .rad-alert-title{font-size:15px;font-weight:bold;color:#222;margin:0 0 6px}
#rad-page .rad-alert-sub{font-size:12px;color:#64748b;margin:0}
#rad-page .modal-body .form-group{margin-bottom:14px}
#rad-page .modal-body label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#64748b;margin-bottom:5px}
#rad-page .modal-body input,#rad-page .modal-body select,#rad-page .modal-body textarea{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;font-family:inherit;box-sizing:border-box}
#rad-page .modal-body input[readonly]{background-color:#eef3f8;color:#555}
#rad-page .modal-body textarea{resize:vertical}
#rad-page .modal-body input:focus,#rad-page .modal-body select:focus,#rad-page .modal-body textarea:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#rad-page .rad-context{background:#fff;border:1px solid #b2c8de;border-radius:4px;padding:10px 12px;margin-bottom:14px;font-size:12px;line-height:1.6}
#rad-page .rad-context b{color:#0D47A1}
#rad-page .form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px;padding-top:14px;border-top:1px solid #e1e8f0}
#rad-page .form-actions.center{justify-content:center;border-top:none;margin-top:8px;padding-top:0}
#rad-page .btn{border:none;border-radius:3px;cursor:pointer;font-weight:bold;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:8px 18px;font-family:inherit;display:inline-flex;align-items:center;gap:6px}
#rad-page .btn-primary{background:#0072BC;color:#fff}
#rad-page .btn-primary:hover{filter:brightness(1.08)}
#rad-page .btn-secondary{background:#F1F5F9;color:#34495E;border:1px solid #C0C0C0}
#rad-page .btn-secondary:hover{background:#E2E8F0}

/* ---- REPORT PREVIEW ---- */
#rad-page .rad-report-box{background:#fff;border:1px solid #b2c8de;border-radius:4px;padding:12px 14px;font-size:12px;line-height:1.65;max-height:210px;overflow-y:auto}
#rad-page .rad-report-box .rb-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#64748b;margin:0 0 3px}
#rad-page .rad-report-box .rb-val{white-space:pre-wrap;margin:0 0 10px;color:#222}
#rad-page .rad-report-box .rb-val:last-child{margin-bottom:0}

@media (max-width:760px){
  #rad-page .rad-feature-grid{grid-template-columns:1fr}
  #rad-page .rad-form{grid-template-columns:1fr}
  #rad-page .span-2,#rad-page .span-3,#rad-page .span-4,#rad-page .span-6,#rad-page .rad-form-actions{grid-column:span 1}
  #rad-page .rad-topbar .rad-tag-hide{display:none}
}
</style>

<!-- RADIOLOGY & IMAGING MODULE -->
<div id="rad-page">

  <!-- TOP BAR -->
  <div class="rad-topbar">
    <div class="title-group">
      <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
      <div class="rad-topbar-title">Radiology</div>
    </div>
    <div class="rad-topbar-right">
      <span class="rad-tag-hide">HMS - HEALTHCARE MANAGEMENT SYSTEM</span>
      <span class="rad-nav-btn" onclick="radGoHome()">HOME</span>
      <span class="rad-nav-btn" onclick="radGoBack()">&lt; BACK</span>
      <span class="rad-nav-btn" onclick="radGoPassword()">PASSWORD</span>
    </div>
  </div>

  <div class="rad-wrap">

    <!-- MODULE HEADER BANNER -->
    <div class="rad-modhead">
      <div class="left">
        <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        <h2>Radiology &amp; Imaging Module</h2>
      </div>
      <div class="actions">
        <span class="rad-live-badge" id="radLiveBadge"><i class="fa fa-sync fa-spin"></i> Live Auto-Refresh</span>
        <button type="button" class="rad-h-btn light" onclick="radRefresh()">
          <svg viewBox="0 0 24 24"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- FEATURE NAVIGATION GRID -->
    <div class="rad-feature-grid">
      <button type="button" class="rad-feature-card" onclick="radGoSection('radFormSection')">
        <svg viewBox="0 0 24 24" stroke="#d9534f"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        <span>
          <span class="fc-title">Order Imaging Study</span>
          <span class="fc-sub">Request an X-ray, CT, MRI, ultrasound or scan</span>
        </span>
      </button>

      <button type="button" class="rad-feature-card" onclick="radCardTab('scheduled')">
        <svg viewBox="0 0 24 24" stroke="#f0ad4e"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span>
          <span class="fc-title">Schedule &amp; Worklist</span>
          <span class="fc-sub">Slot studies and manage the imaging queue</span>
        </span>
      </button>

      <button type="button" class="rad-feature-card" onclick="radCardTab('reported')">
        <svg viewBox="0 0 24 24" stroke="#7c3aed"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <span>
          <span class="fc-title">Reported Reports</span>
          <span class="fc-sub">Drafts awaiting radiologist verification</span>
        </span>
      </button>

      <button type="button" class="rad-feature-card" onclick="radCardTab('verified')">
        <svg viewBox="0 0 24 24" stroke="#5cb85c"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        <span>
          <span class="fc-title">Verified &amp; Dispatched</span>
          <span class="fc-sub">Release final reports to the requesting doctor</span>
        </span>
      </button>
    </div>

    <!-- ORDER FORM -->
    <div class="rad-panel is-first" id="radFormSection">
      <div class="rad-panel-head">
        <span class="head-left">
          <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Imaging Order Form
        </span>
      </div>
      <form class="rad-form" id="radEntryForm">
        <div class="rad-field span-3">
          <label for="radPatient">Patient / Visit <span class="req">*</span></label>
          <select id="radPatient" required>
            <option value="">-- Select Patient --</option>
          </select>
        </div>

        <div class="rad-field span-2">
          <label for="radModality">Modality <span class="req">*</span></label>
          <select id="radModality" onchange="updateRadStudies()" required>
            <option value="">-- Modality --</option>
            <option value="xray">X-Ray (Radiography)</option>
            <option value="ultrasound">Ultrasound / Sonography</option>
            <option value="ct">CT Scan</option>
            <option value="mri">MRI</option>
            <option value="mammography">Mammography</option>
            <option value="fluoroscopy">Fluoroscopy / Contrast</option>
          </select>
        </div>

        <div class="rad-field span-3">
          <label for="radStudy">Examination to be Performed <span class="req">*</span></label>
          <select id="radStudy" onchange="syncRadBodyPart()" required>
            <option value="">-- Select Modality First --</option>
          </select>
        </div>

        <div class="rad-field span-2">
          <label for="radLaterality">Laterality</label>
          <select id="radLaterality">
            <option value="">Not applicable</option>
            <option value="Left">Left</option>
            <option value="Right">Right</option>
            <option value="Bilateral">Bilateral</option>
          </select>
        </div>

        <div class="rad-field span-2">
          <label for="radUrgency">Priority <span class="req">*</span></label>
          <select id="radUrgency" required>
            <option value="routine">Routine</option>
            <option value="urgent">Urgent</option>
            <option value="emergency">STAT / Emergency</option>
          </select>
        </div>

        <div class="rad-field span-6">
          <label for="radIndication">Clinical Indication / Reason for Exam</label>
          <textarea id="radIndication" rows="2" placeholder="e.g. Productive cough, fever, right-sided chest pain - rule out pneumonia / TB..."></textarea>
        </div>

        <div class="rad-form-actions">
          <button type="reset" class="btn btn-secondary">Clear</button>
          <button type="submit" class="rad-btn-submit">
            <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Submit Imaging Order
          </button>
        </div>
      </form>
    </div>

    <!-- WORKLIST PANEL -->
    <div class="rad-panel" id="radTabTables">
      <div class="rad-panel-head dark">
        <div class="rad-tabs" id="radTabs" role="tablist">
          <button type="button" class="rad-tab active" id="radTabWorklist" onclick="radSwitchTab('worklist')">Worklist</button>
          <button type="button" class="rad-tab" id="radTabPending" onclick="radSwitchTab('pending')">Awaiting Schedule</button>
          <button type="button" class="rad-tab" id="radTabScheduled" onclick="radSwitchTab('scheduled')">Scheduled</button>
          <button type="button" class="rad-tab" id="radTabInprogress" onclick="radSwitchTab('inprogress')">In Progress</button>
          <button type="button" class="rad-tab" id="radTabReported" onclick="radSwitchTab('reported')">Reported</button>
          <button type="button" class="rad-tab" id="radTabVerified" onclick="radSwitchTab('verified')">Verified</button>
          <button type="button" class="rad-tab" id="radTabDispatched" onclick="radSwitchTab('dispatched')">Dispatched</button>
        </div>
      </div>

      <div class="rad-toolbar">
        <div class="search">
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" id="radGlobalSearch" placeholder="By Patient / Accession / Exam / Region..." oninput="renderRadPanes()">
        </div>
        <div class="rad-filters">
          <select id="radFilterUrgency" onchange="loadRadRequests()" title="Filter by priority">
            <option value="">All Priorities</option>
            <option value="emergency">STAT / Emergency</option>
            <option value="urgent">Urgent</option>
            <option value="routine">Routine</option>
          </select>
          <select id="radFilterModality" onchange="loadRadRequests()" title="Filter by modality">
            <option value="">All Modalities</option>
            <?php foreach ($__radCatalog as $__mod => $__studies): ?>
              <option value="<?php echo htmlspecialchars($__studies[0][0], ENT_QUOTES); ?>"><?php echo htmlspecialchars(ucfirst($__mod), ENT_QUOTES); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="rad-counts">
          <span>All: <b id="cAll">0</b></span>
          <span>Awaiting: <b id="cPending">0</b></span>
          <span>Scheduled: <b id="cScheduled">0</b></span>
          <span>In Progress: <b id="cInProgress">0</b></span>
          <span>Reported: <b id="cReported">0</b></span>
          <span>Verified: <b id="cVerified">0</b></span>
          <span>STAT: <b id="cEmergency">0</b></span>
        </div>
      </div>

      <div class="rad-table-wrap">
        <div class="rad-pane active" id="radPaneWorklist"><div class="rad-empty">Loading...</div></div>
        <div class="rad-pane" id="radPanePending"></div>
        <div class="rad-pane" id="radPaneScheduled"></div>
        <div class="rad-pane" id="radPaneInprogress"></div>
        <div class="rad-pane" id="radPaneReported"></div>
        <div class="rad-pane" id="radPaneVerified"></div>
        <div class="rad-pane" id="radPaneDispatched"></div>
      </div>
    </div>

  </div>

  <!-- NEW ORDER ALERT -->
  <div class="modal centered" id="radNewRequestModal">
    <div class="modal-content" style="max-width:520px;">
      <div class="modal-header danger">
        <h3>
          <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2.121 2.121 0 0 1-3.46 0"/></svg>
          New Imaging Order Alert
        </h3>
        <button class="modal-close" onclick="closeRadNewModal()">&times;</button>
      </div>
      <div class="modal-body text-center">
        <p class="rad-alert-title">A new imaging study has been ordered by a clinician!</p>
        <p class="rad-alert-sub" id="radAlertDetails">--</p>
      </div>
      <div class="form-actions center">
        <button type="button" class="btn btn-secondary" onclick="closeRadNewModal()">Close Alert</button>
        <button type="button" class="btn btn-primary" onclick="viewRadNewRequest()">View Order</button>
      </div>
    </div>
  </div>

  <!-- REPORT ENTRY MODAL -->
  <div class="modal" id="rad-report-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Radiology Report
        </h3>
        <button class="modal-close" onclick="closeRadReportModal()">&times;</button>
      </div>
      <div class="modal-body">
        <form id="radReportForm">
          <div class="form-group">
            <label for="radReportStudy">Study <span class="req">*</span></label>
            <select id="radReportStudy">
              <option value="">-- Awaiting studies --</option>
            </select>
          </div>
          <div class="rad-context" id="radReportContext">Select a study to load its clinical context.</div>
          <div class="form-group">
            <label for="radReportRadiologist">Reporting Radiologist</label>
            <input type="text" id="radReportRadiologist" value="<?php echo htmlspecialchars($__radStaff, ENT_QUOTES); ?>" readonly>
          </div>
          <div class="form-group">
            <label for="radReportTechnique">Technique / Projection</label>
            <input type="text" id="radReportTechnique" placeholder="e.g. Plain film, PA &amp; lateral views">
          </div>
          <div class="form-group">
            <label for="radReportFindings">Findings <span class="req">*</span></label>
            <textarea id="radReportFindings" rows="4" placeholder="Describe the imaging findings..."></textarea>
          </div>
          <div class="form-group">
            <label for="radReportImpression">Impression / Conclusion</label>
            <textarea id="radReportImpression" rows="2" placeholder="e.g. Right lower zone consolidation consistent with pneumonia..."></textarea>
          </div>
          <div class="form-group">
            <label for="radReportImage">Image / Film Reference</label>
            <input type="text" id="radReportImage" placeholder="e.g. PACS accession or film number">
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-secondary" data-save="draft">Save Draft</button>
            <button type="submit" class="btn btn-primary" data-save="final">Verify &amp; Finalise</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- VIEW / PREVIEW MODAL -->
  <div class="modal" id="rad-view-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
          Imaging Study Detail
        </h3>
        <button class="modal-close" onclick="closeRadViewModal()">&times;</button>
      </div>
      <div class="modal-body">
        <div class="rad-context" id="radViewContext">--</div>
        <div class="rad-report-box" id="radViewReport">
          <p class="rad-sub">No report has been recorded for this study yet.</p>
        </div>
        <div class="form-actions">
          <button type="button" class="btn btn-secondary" onclick="closeRadViewModal()">Close</button>
          <button type="button" class="btn btn-primary" onclick="reportFromView()">Open Report</button>
        </div>
      </div>
    </div>
  </div><!-- /.rad-wrap -->

  <!-- SCHEDULE MODAL -->
  <div class="modal centered" id="radScheduleModal">
    <div class="modal-content" style="max-width:460px;">
      <div class="modal-header">
        <h3>
          <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Schedule Imaging Slot
        </h3>
        <button class="modal-close" onclick="closeRadScheduleModal()">&times;</button>
      </div>
      <div class="modal-body">
        <div class="rad-context" id="radScheduleContext">--</div>
        <div class="form-group">
          <label for="radScheduleSlot">Appointment Slot <span class="req">*</span></label>
          <input type="datetime-local" id="radScheduleSlot">
        </div>
        <div class="form-actions center">
          <button type="button" class="btn btn-secondary" onclick="closeRadScheduleModal()">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="confirmRadSchedule()">Confirm Slot</button>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
var CURRENT_RAD_STAFF = <?php echo json_encode($__radStaff); ?>;
var RAD_ROLE          = <?php echo json_encode($__radRole); ?>;
var radStudiesData    = <?php echo json_encode($__radStudyData); ?>;

/* exam_type -> modality lookup. The exam_type strings all come from the
   catalogue above, so the worklist filter never has to guess from free text. */
var RAD_MODALITY_OF = (function () {
    var out = {};
    Object.keys(radStudiesData).forEach(function (mod) {
        radStudiesData[mod].forEach(function (s) { out[s.exam_type] = mod; });
    });
    return out;
})();

var radVisits = [];
var radData = [];
var radView = 'worklist';
var radKnownIds = {};
var RAD_DISPATCH_KEY = 'hms:radiology-dispatched';
var RAD_VIEW_ID = null;
var RAD_SCHED_ID = null;
var RAD_POLL_MS = 20000;

async function initRadiology() {
    setupRadEvents();
    await Promise.all([loadRadVisits(), loadRadRequests()]);
    startRadRefresh();
}

function setupRadEvents() {
    var entry = document.getElementById('radEntryForm');
    if (entry) entry.addEventListener('submit', handleRadEntrySubmit);

    var rep = document.getElementById('radReportForm');
    if (rep) rep.addEventListener('submit', handleRadReportSubmit);

    /* Study picker drives the context panel and prefills any existing report. */
    var study = document.getElementById('radReportStudy');
    if (study) study.addEventListener('change', function () { onRadStudyChange(this.value); });
}

/* ================= TOP BAR / NAV ================= */
function radGoHome() {
    if (window.navigateTo) { window.navigateTo('dashboard'); return; }
    if (window.loadPage) window.loadPage('dashboard');
}

function radGoBack() {
    if (window.history && window.history.length > 1) window.history.back();
    else radGoHome();
}

function radGoPassword() {
    if (window.navigateTo) window.navigateTo('change-password');
    else if (window.loadPage) window.loadPage('change-password');
}

function radGoSection(id) {
    var el = document.getElementById(id);
    if (el && el.scrollIntoView) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function radCardTab(view) {
    radSwitchTab(view);
    radGoSection('radTabTables');
}

/* ================= REFRESH / LIVE AUTO-REFRESH ================= */
function radRefresh() {
    loadRadRequests().then(function () {
        showAlert('Radiology worklist refreshed.', 'success');
    }).catch(function () {
        showAlert('Refresh failed - please try again.', 'error');
    });
}

function startRadRefresh() {
    /* The shell calls this init hook on every mount, so clear any earlier
       handle first - otherwise re-visits stack duplicate pollers. */
    if (window.__radPollTimer) { clearInterval(window.__radPollTimer); window.__radPollTimer = null; }
    window.__radPollTimer = setInterval(radLivePoll, RAD_POLL_MS);
}

async function radLivePoll() {
    if (!document.getElementById('radTabs')) return;   /* page unmounted */
    try {
        var r = await fetch('/hms/backend/api/radiology.php?action=requests' + radFilterParams());
        var d = await r.json();
        if (d && d.success) {
            var reqs = d.requests || [];
            var fresh = reqs.filter(function (x) { return !radKnownIds[String(x.id)]; });
            reqs.forEach(function (x) { radKnownIds[String(x.id)] = true; });
            radData = reqs;
            renderRadPanes();
            populateRadReportStudy();
            if (fresh.length) showRadNewAlert(fresh[0]);
        }
    } catch (e) {
        console.error('Radiology live refresh error:', e);
    }
}

/* ================= VISITS / PATIENT SELECT ================= */
async function loadRadVisits() {
    try {
        var r = await fetch('/hms/backend/api/visits.php');
        var d = await r.json();
        if (d.success) radVisits = d.visits || [];
    } catch (e) {
        console.error('Visits load error:', e);
        radVisits = [];
    }
    renderRadVisitSelect();
}

function renderRadVisitSelect() {
    var sel = document.getElementById('radPatient');
    if (!sel) return;
    var keep = sel.value;
    sel.innerHTML = '<option value="">-- Select Patient --</option>'
        + radVisits.map(function (v) {
            return '<option value="' + v.id + '">' + escHtml(v.visit_number + ' - ' + v.patient_name + ' (' + v.hospital_number + ')') + '</option>';
        }).join('');
    if (keep) sel.value = keep;
}

/* ================= MODALITY / STUDY CASCADE ================= */
function updateRadStudies() {
    var study = document.getElementById('radStudy');
    var mod = document.getElementById('radModality').value;
    if (!study) return;
    study.innerHTML = '<option value="">-- Select Examination --</option>';
    if (mod && radStudiesData[mod]) {
        radStudiesData[mod].forEach(function (s) {
            var opt = document.createElement('option');
            opt.value = s.exam_type;
            opt.setAttribute('data-part', s.body_part);
            opt.textContent = s.exam_type;
            study.appendChild(opt);
        });
    }
}

/* The region is carried by the catalogue entry. Laterality is only offered for
   studies where it changes the study itself, so chest/head/abdomen orders are
   not cluttered with a control that has no meaning for them. */
function syncRadBodyPart() {
    var study = document.getElementById('radStudy');
    var lat = document.getElementById('radLaterality');
    if (!study || !lat) return;
    var studyName = study.value || '';
    var matters = /knee|breast|extremity|hand|wrist|ankle|foot|shoulder|hip|elbow/i.test(studyName);
    lat.disabled = !matters;
    if (!matters) lat.value = '';
    lat.options[0].textContent = matters ? 'Laterality' : 'Not applicable';
}

/* ================= ORDER SUBMIT ================= */
async function handleRadEntrySubmit(e) {
    e.preventDefault();
    var visitId = document.getElementById('radPatient').value;
    var mod = document.getElementById('radModality').value;
    var studySel = document.getElementById('radStudy');
    var study = studySel.value;
    var latEl = document.getElementById('radLaterality');

    if (!visitId) { showAlert('Select the patient / visit first.', 'error'); return; }
    if (!mod) { showAlert('Select the imaging modality.', 'error'); return; }
    if (!study) { showAlert('Select the examination to be performed.', 'error'); return; }

    /* Laterality is appended to the catalogue region so a left/right study
       stays on the one field the worklist can display directly. */
    var opt = studySel.options[studySel.selectedIndex];
    var region = (opt && opt.getAttribute('data-part')) || '';
    var laterality = (latEl && !latEl.disabled) ? latEl.value : '';
    var bodyPart = laterality ? (region ? region + ' (' + laterality + ')' : laterality) : region;

    var data = {
        visit_id: visitId,
        exam_type: study,
        body_part: bodyPart,
        clinical_indication: document.getElementById('radIndication').value.trim(),
        urgency: document.getElementById('radUrgency').value
    };

    try {
        var r = await fetch('/hms/backend/api/radiology.php?action=request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var d = await r.json();
        if (d.success) {
            showAlert('Imaging order submitted: ' + study + '.', 'success');
            document.getElementById('radEntryForm').reset();
            document.getElementById('radUrgency').value = 'routine';
            updateRadStudies();
            syncRadBodyPart();
            radSwitchTab('pending');
            await loadRadRequests();
        } else {
            showAlert(d.error || 'Submit failed', 'error');
        }
    } catch (err) {
        console.error('Radiology order error:', err);
        showAlert('Network error. Please try again.', 'error');
    }
}

/* ================= WORKLIST DATA ================= */
function radFilterParams() {
    var p = new URLSearchParams();
    p.set('action', 'requests');
    var urg = document.getElementById('radFilterUrgency');
    var mod = document.getElementById('radFilterModality');
    if (urg && urg.value) p.set('urgency', urg.value);
    if (mod && mod.value) p.set('q', mod.value);   /* server matches exam_type on q */
    var s = document.getElementById('radGlobalSearch');
    var typed = s ? (s.value || '').trim() : '';
    /* A typed search must not be silently replaced by the modality filter, so
       the search box wins and the modality filter is applied client-side. */
    if (typed) p.set('q', typed);
    return '?' + p.toString();
}

async function loadRadRequests() {
    try {
        var r = await fetch('/hms/backend/api/radiology.php' + radFilterParams());
        var d = await r.json();
        var reqs = (d.success && d.requests) ? d.requests : [];
        radKnownIds = {};
        reqs.forEach(function (x) { radKnownIds[String(x.id)] = true; });
        /* Modality is a catalogue attribute, not a column, so it is filtered
           here to keep the server query simple. */
        var modEl = document.getElementById('radFilterModality');
        if (modEl && modEl.value) {
            reqs = reqs.filter(function (x) { return RAD_MODALITY_OF[x.exam_type] === modEl.value; });
        }
        radData = reqs;
    } catch (e) {
        console.error('Radiology load error:', e);
        radData = [];
    }
    renderRadPanes();
    populateRadReportStudy();
}

/* ================= TABS ================= */
var RAD_TABS = ['worklist', 'pending', 'scheduled', 'inprogress', 'reported', 'verified', 'dispatched'];

function radSwitchTab(view) {
    if (RAD_TABS.indexOf(view) === -1) view = 'worklist';
    radView = view;
    RAD_TABS.forEach(function (t) {
        var id = t.charAt(0).toUpperCase() + t.slice(1);
        var btn = document.getElementById('radTab' + id);
        var pane = document.getElementById('radPane' + id);
        if (btn) btn.classList.toggle('active', t === view);
        if (pane) pane.classList.toggle('active', t === view);
    });
    renderRadPanes();
}

/* ================= BADGES ================= */
function radUrgencyBadge(u) {
    if (u === 'emergency') return '<span class="rad-badge red">STAT</span>';
    if (u === 'urgent') return '<span class="rad-badge amber">Urgent</span>';
    return '<span class="rad-badge grey">Routine</span>';
}

function radStatusBadge(r) {
    if (r.status === 'completed') {
        var rs = r.result_status;
        if (rs === 'final') return '<span class="rad-badge green">Verified</span>';
        if (rs === 'amended') return '<span class="rad-badge purple">Amended</span>';
        return '<span class="rad-badge green">Completed</span>';
    }
    if (r.status === 'in_progress') return '<span class="rad-badge blue">In Progress</span>';
    if (r.status === 'scheduled') return '<span class="rad-badge blue">Scheduled</span>';
    if (r.status === 'cancelled') return '<span class="rad-badge grey">Cancelled</span>';
    return '<span class="rad-badge amber">Pending</span>';
}

/* ================= FILTERS ================= */
function radPassFilter(r) {
    var el = document.getElementById('radGlobalSearch');
    var q = el ? (el.value || '').trim().toLowerCase() : '';
    if (!q) return true;
    return String(r.patient_name || '').toLowerCase().indexOf(q) >= 0
        || String(r.hospital_number || '').toLowerCase().indexOf(q) >= 0
        || String(r.exam_type || '').toLowerCase().indexOf(q) >= 0
        || String(r.body_part || '').toLowerCase().indexOf(q) >= 0
        || String(r.clinical_indication || '').toLowerCase().indexOf(q) >= 0
        || String(r.doctor_name || '').toLowerCase().indexOf(q) >= 0
        || String(r.id || '').indexOf(q) >= 0;
}

function radFind(id) {
    return radData.find(function (x) { return String(x.id) === String(id); }) || null;
}

/* ================= TABLES ================= */
function radPatientCell(r) {
    var bits = [];
    if (r.age != null && r.age !== '') bits.push(r.age + 'y');
    if (r.gender) bits.push(String(r.gender).charAt(0).toUpperCase() + String(r.gender).slice(1));
    return '<td style="font-weight:700;">' + escHtml(r.patient_name || '--')
        + '<span class="rad-sub">' + escHtml(r.hospital_number || '') + (bits.length ? ' | ' + escHtml(bits.join(' | ')) : '') + '</span></td>';
}

function radExamCell(r) {
    return '<td>' + escHtml(r.exam_type || '--')
        + (r.body_part ? '<span class="rad-sub">Region: ' + escHtml(r.body_part) + '</span>' : '')
        + (r.clinical_indication ? '<span class="rad-sub" title="' + escHtml(r.clinical_indication) + '">Indication: ' + escHtml(String(r.clinical_indication).slice(0, 46)) + (String(r.clinical_indication).length > 46 ? '…' : '') + '</span>' : '')
        + '</td>';
}

/* Standard worklist table. Columns: accession, patient, exam, priority, doctor,
   requested, scheduled slot, status, actions. */
function radWorklistTable(rows, q, emptyMsg) {
    return '<table class="rad-table"><thead><tr>'
        + '<th class="first-col">Accession</th><th>Patient</th><th>Examination / Region</th><th>Priority</th><th>Referring</th><th>Requested</th><th>Scheduled</th><th>Status</th><th class="act-col">Action</th>'
        + '</tr></thead><tbody>' + (rows.length
            ? rows.map(function (r) {
                return '<tr>'
                    + '<td class="first-col" style="font-weight:700;">RAD ' + escHtml(r.id) + '</td>'
                    + radPatientCell(r)
                    + radExamCell(r)
                    + '<td>' + radUrgencyBadge(r.urgency) + '</td>'
                    + '<td>' + escHtml(r.doctor_name || '--') + '</td>'
                    + '<td>' + fmtRadTime(r.requested_at) + '</td>'
                    + '<td>' + (r.scheduled_at ? fmtRadTime(r.scheduled_at) : '<span class="rad-muted">-</span>') + '</td>'
                    + '<td>' + radStatusBadge(r) + '</td>'
                    + '<td class="act-col">' + radActions(r) + '</td>'
                    + '</tr>';
            }).join('')
            : '<tr><td colspan="9" class="rad-empty">' + escHtml(emptyMsg) + '</td></tr>')
        + '</tbody></table>';
}

/* Action buttons follow where the study is in the workflow. A verified report
   cannot be walked backwards by the API, so those rows stop offering status
   edits and only offer dispatch / view. */
function radActions(r) {
    var out = [];
    if (r.status === 'cancelled') {
        return '<span class="rad-muted">-</span>';
    }
    if (r.status === 'pending') {
        out.push('<button class="rad-btn-primary" onclick="openRadSchedule(' + r.id + ')">Schedule</button>');
    }
    if (r.status === 'scheduled') {
        out.push('<button class="rad-btn-outline" onclick="openRadSchedule(' + r.id + ')">Re-slot</button>');
        out.push('<button class="rad-btn-primary" onclick="setRadStatus(' + r.id + ', \'in_progress\')">Start</button>');
    }
    if (r.status === 'pending' || r.status === 'scheduled' || r.status === 'in_progress') {
        out.push('<button class="rad-btn-success" onclick="openRadReport(' + r.id + ')">Report</button>');
    }
    if (r.status === 'completed' && (r.result_status === 'final' || r.result_status === 'amended')) {
        if (!radIsDispatched(r.id)) {
            out.push('<button class="rad-btn-success" onclick="radDispatch(' + r.id + ')">Dispatch</button>');
        }
    }
    out.push('<button class="rad-btn-outline" onclick="openRadView(' + r.id + ')">View</button>');
    return out.join(' ');
}

/* Report-listing table (Reported / Verified panes): findings + impression. */
function radReportTable(rows, q, emptyMsg) {
    return '<table class="rad-table"><thead><tr>'
        + '<th class="first-col">Accession</th><th>Patient</th><th>Examination</th><th>Findings</th><th>Impression</th><th>Radiologist / Verified</th><th>Status</th><th class="act-col">Action</th>'
        + '</tr></thead><tbody>' + (rows.length
            ? rows.map(function (r) {
                var verified = (r.verified_name ? escHtml(r.verified_name) + '<br>' : '') + fmtRadTime(r.completed_at);
                return '<tr>'
                    + '<td class="first-col" style="font-weight:700;">RAD ' + escHtml(r.id) + '</td>'
                    + radPatientCell(r)
                    + radExamCell(r)
                    + '<td style="max-width:230px;white-space:pre-wrap;">' + escHtml(truncate(r.findings, 150)) + '</td>'
                    + '<td style="max-width:190px;white-space:pre-wrap;">' + escHtml(truncate(r.impression, 110)) + '</td>'
                    + '<td>' + escHtml(r.radiologist_name || '--') + '<span class="rad-sub">' + verified + '</span></td>'
                    + '<td>' + radStatusBadge(r) + '</td>'
                    + '<td class="act-col">' + radReportActions(r) + '</td>'
                    + '</tr>';
            }).join('')
            : '<tr><td colspan="8" class="rad-empty">' + escHtml(emptyMsg) + '</td></tr>')
        + '</tbody></table>';
}

function radReportActions(r) {
    var out = ['<button class="rad-btn-outline" onclick="openRadView(' + r.id + ')">View</button>'];
    /* A draft still needs the radiologist to verify it. A final report may be
       reopened as an amendment, which the API records as 'amended'. */
    if (r.result_status === 'draft') {
        out.unshift('<button class="rad-btn-success" onclick="openRadReport(' + r.id + ')">Verify</button>');
    } else if (r.result_status === 'final') {
        out.unshift('<button class="rad-btn-outline" onclick="openRadReport(' + r.id + ')">Amend</button>');
    } else {
        out.unshift('<button class="rad-btn-primary" onclick="openRadReport(' + r.id + ')">Edit</button>');
    }
    if ((r.result_status === 'final' || r.result_status === 'amended') && !radIsDispatched(r.id)) {
        out.push('<button class="rad-btn-success" onclick="radDispatch(' + r.id + ')">Dispatch</button>');
    }
    return out.join(' ');
}

function renderRadPanes() {
    if (!document.getElementById('radGlobalSearch')) return;   /* page unmounted */
    var q = (document.getElementById('radGlobalSearch').value || '').trim().toLowerCase();
    var dispatched = radReadDispatched();
    var active = radData.filter(function (r) { return r.status !== 'cancelled'; });

    setCount('cAll', active.length);
    setCount('cPending', active.filter(function (r) { return r.status === 'pending'; }).length);
    setCount('cScheduled', active.filter(function (r) { return r.status === 'scheduled'; }).length);
    setCount('cInProgress', active.filter(function (r) { return r.status === 'in_progress'; }).length);
    setCount('cReported', active.filter(function (r) { return r.result_status === 'draft'; }).length);
    setCount('cVerified', active.filter(function (r) { return r.result_status === 'final' || r.result_status === 'amended'; }).length);
    setCount('cEmergency', active.filter(function (r) { return r.urgency === 'emergency'; }).length);

    var noMatch = q ? 'Nothing matches "' + q + '".' : '';

    setPane('radPaneWorklist', radWorklistTable(
        active.filter(radPassFilter), q,
        q || 'No imaging orders yet - use the order form above to request a study.'
    ));

    setPane('radPanePending', radWorklistTable(
        active.filter(function (r) { return r.status === 'pending'; }).filter(radPassFilter), q,
        q || 'Nothing awaiting scheduling - every pending study has been slotted.'
    ));

    setPane('radPaneScheduled', radWorklistTable(
        active.filter(function (r) { return r.status === 'scheduled'; }).filter(radPassFilter), q,
        q || 'No studies are currently scheduled.'
    ));

    setPane('radPaneInprogress', radWorklistTable(
        active.filter(function (r) { return r.status === 'in_progress'; }).filter(radPassFilter), q,
        q || 'No study is currently being acquired.'
    ));

    setPane('radPaneReported', radReportTable(
        active.filter(function (r) { return r.result_status === 'draft'; }).filter(radPassFilter), q,
        q || 'No draft reports are awaiting verification.'
    ));

    setPane('radPaneVerified', radReportTable(
        active.filter(function (r) { return r.result_status === 'final' || r.result_status === 'amended'; })
            .filter(radPassFilter).filter(function (r) { return !dispatched.some(function (x) { return String(x.id) === String(r.id); }); }), q,
        q || 'No verified reports are waiting to be dispatched.'
    ));

    /* Dispatched - browser-local log; radiology_requests has no 'dispatched'
       status, so this mirrors how lab-management.php tracks hand-off. */
    var dlist = dispatched.map(function (d) {
        var r = radFind(d.id);
        return '<tr>'
            + '<td class="first-col" style="font-weight:700;">RAD ' + escHtml(d.id) + '</td>'
            + radPatientCell(r || {})
            + radExamCell(r || {})
            + '<td>' + (r && r.radiologist_name ? escHtml(r.radiologist_name) : '--') + '</td>'
            + '<td>' + (d.at ? fmtRadTime(d.at) : '--') + '</td>'
            + '<td class="act-col"><button class="rad-btn-outline" onclick="openRadView(' + escHtml(d.id) + ')">View</button></td>'
            + '</tr>';
    }).join('');
    setPane('radPaneDispatched', '<table class="rad-table"><thead><tr>'
        + '<th class="first-col">Accession</th><th>Patient</th><th>Examination</th><th>Reported By</th><th>Dispatched</th><th class="act-col">Report</th>'
        + '</tr></thead><tbody>' + (dlist || '<tr><td colspan="6" class="rad-empty">No reports dispatched yet - dispatch a verified study above.</td></tr>')
        + '</tbody></table>');
}

function setCount(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; }
function setPane(id, html) { var el = document.getElementById(id); if (el) el.innerHTML = html; }

/* ================= SCHEDULING ================= */
function openRadSchedule(requestId) {
    var r = radFind(requestId);
    if (!r) { showAlert('Study no longer available - refresh the worklist.', 'error'); return; }
    RAD_SCHED_ID = r.id;

    var ctx = document.getElementById('radScheduleContext');
    ctx.innerHTML = '<b>Accession:</b> RAD ' + escHtml(r.id)
        + '<br><b>Patient:</b> ' + escHtml(r.patient_name || '--') + ' (' + escHtml(r.hospital_number || '--') + ')'
        + '<br><b>Study:</b> ' + escHtml(r.exam_type || '--')
        + (r.body_part ? ' &nbsp;|&nbsp; <b>Region:</b> ' + escHtml(r.body_part) : '')
        + ' &nbsp;|&nbsp; <b>Priority:</b> ' + escHtml(String(r.urgency || '').toUpperCase());

    var slot = document.getElementById('radScheduleSlot');
    /* Pre-fill with the existing slot, else default to now so the radiographer
       only adjusts the time. */
    var d = r.scheduled_at ? new Date(r.scheduled_at.replace(' ', 'T')) : new Date();
    if (isNaN(d.getTime())) d = new Date();
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    slot.value = d.toISOString().slice(0, 16);

    document.getElementById('radScheduleModal').classList.add('show');
}

function confirmRadSchedule() {
    var r = radFind(RAD_SCHED_ID);
    var slotEl = document.getElementById('radScheduleSlot');
    if (!r || !slotEl || !slotEl.value) {
        showAlert('Pick a schedule slot first.', 'error');
        return;
    }
    setRadStatus(r.id, 'scheduled', slotEl.value);
    document.getElementById('radScheduleModal').classList.remove('show');
}

function closeRadScheduleModal() {
    var m = document.getElementById('radScheduleModal');
    if (m) m.classList.remove('show');
}

async function setRadStatus(id, status, scheduledAt) {
    try {
        var payload = { status: status };
        if (scheduledAt) payload.scheduled_at = scheduledAt;
        var r = await fetch('/hms/backend/api/radiology.php?action=status&id=' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        var d = await r.json();
        if (d.success) {
            showAlert('Study RAD ' + id + ' marked as ' + status.replace('_', ' ') + '.', 'success');
            await loadRadRequests();
        } else {
            showAlert(d.error || 'Update failed', 'error');
        }
    } catch (err) {
        console.error('Radiology status error:', err);
        showAlert('Network error. Please try again.', 'error');
    }
}

/* ================= NEW ORDER ALERT ================= */
function showRadNewAlert(req) {
    var modal = document.getElementById('radNewRequestModal');
    var details = document.getElementById('radAlertDetails');
    if (!modal || !details) return;
    details.textContent = 'Patient: ' + (req.patient_name || '--')
        + ' | Study: ' + (req.exam_type || '--')
        + ' | Priority: ' + String(req.urgency || '').toUpperCase()
        + ' | Ordered by: ' + (req.doctor_name || '--')
        + ' | ' + fmtRadTime(req.requested_at);
    modal.classList.add('show');
}

function closeRadNewModal() {
    var m = document.getElementById('radNewRequestModal');
    if (m) m.classList.remove('show');
}

function viewRadNewRequest() {
    closeRadNewModal();
    radSwitchTab('worklist');
    radGoSection('radTabTables');
}

/* ================= REPORT ENTRY ================= */
function populateRadReportStudy() {
    var sel = document.getElementById('radReportStudy');
    if (!sel) return;
    var keep = sel.value;
    /* Anything not cancelled can receive or revise a report. */
    var rows = radData.filter(function (r) { return r.status !== 'cancelled'; });
    sel.innerHTML = rows.length
        ? rows.map(function (r) {
            var tag = r.result_status ? ' [' + String(r.result_status).toUpperCase() + ']' : '';
            return '<option value="' + r.id + '">RAD ' + r.id + ' - ' + escHtml(r.patient_name || '--')
                + ' (' + escHtml(r.exam_type || '--') + ')' + tag + '</option>';
        }).join('')
        : '<option value="">-- No studies available --</option>';
    if (keep && rows.some(function (r) { return String(r.id) === String(keep); })) sel.value = keep;
}

function openRadReport(requestId) {
    populateRadReportStudy();
    var sel = document.getElementById('radReportStudy');
    if (sel && requestId != null
        && Array.prototype.some.call(sel.options, function (o) { return String(o.value) === String(requestId); })) {
        sel.value = String(requestId);
    }
    onRadStudyChange(sel ? sel.value : '');
    document.getElementById('rad-report-modal').classList.add('show');
}

/* Fills the context panel and prefills the textarea from an existing report so
   amending a verified study does not start from a blank form. */
function onRadStudyChange(value) {
    var r = radFind(value);
    var ctx = document.getElementById('radReportContext');
    if (!ctx) return;
    if (!r) { ctx.textContent = 'Select a study to load its clinical context.'; return; }

    ctx.innerHTML = '<b>Accession:</b> RAD ' + escHtml(r.id)
        + ' &nbsp;|&nbsp; <b>Patient:</b> ' + escHtml(r.patient_name || '--') + ' (' + escHtml(r.hospital_number || '--') + ')'
        + (r.age != null ? ', ' + escHtml(r.age) + 'y' : '')
        + (r.gender ? ', ' + escHtml(r.gender) : '')
        + '<br><b>Study:</b> ' + escHtml(r.exam_type || '--')
        + (r.body_part ? ' &nbsp;|&nbsp; <b>Region:</b> ' + escHtml(r.body_part) : '')
        + ' &nbsp;|&nbsp; <b>Priority:</b> ' + escHtml(String(r.urgency || '').toUpperCase())
        + (r.doctor_name ? '<br><b>Referring doctor:</b> ' + escHtml(r.doctor_name) : '')
        + (r.clinical_indication ? '<br><b>Indication:</b> ' + escHtml(r.clinical_indication) : '');

    /* Existing report: prefill. The API stores technique as the first line of
       findings, so it is split back out for editing. */
    document.getElementById('radReportFindings').value = r.findings || '';
    document.getElementById('radReportImpression').value = r.impression || '';
    document.getElementById('radReportImage').value = r.image_path || '';
    var tech = document.getElementById('radReportTechnique');
    var findings = String(r.findings || '');
    var m = findings.match(/^Technique:\s*(.*)\r?\n([\s\S]*)$/);
    tech.value = m ? m[1].trim() : '';
    document.getElementById('radReportFindings').value = m ? m[2].trim() : findings;
}

function closeRadReportModal() {
    var m = document.getElementById('rad-report-modal');
    if (m) m.classList.remove('show');
}

async function handleRadReportSubmit(e) {
    e.preventDefault();
    var reqId = document.getElementById('radReportStudy').value;
    var findings = document.getElementById('radReportFindings').value.trim();
    var saveAs = (e.submitter && e.submitter.getAttribute) ? (e.submitter.getAttribute('data-save') || 'final') : 'final';

    if (!reqId) {
        showAlert('Select a study first - there are no imaging orders to report on.', 'error');
        return;
    }
    if (!findings) {
        showAlert('Enter the imaging findings before saving.', 'error');
        return;
    }

    try {
        var r = await fetch('/hms/backend/api/radiology.php?action=result', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                radiology_request_id: reqId,
                technique: document.getElementById('radReportTechnique').value.trim(),
                findings: findings,
                impression: document.getElementById('radReportImpression').value.trim(),
                image_path: document.getElementById('radReportImage').value.trim(),
                status: saveAs
            })
        });
        var d = await r.json();
        if (d.success) {
            showAlert(saveAs === 'final' ? 'Report verified and finalised.' : 'Report saved as draft.', 'success');
            closeRadReportModal();
            radSwitchTab(saveAs === 'final' ? 'verified' : 'reported');
            await loadRadRequests();
        } else {
            showAlert(d.error || 'Save failed', 'error');
        }
    } catch (err) {
        console.error('Report save error:', err);
        showAlert('Network error. Please try again.', 'error');
    }
}

/* ================= VIEW MODAL ================= */
function openRadView(requestId) {
    var r = radFind(requestId);
    RAD_VIEW_ID = requestId;
    var ctx = document.getElementById('radViewContext');
    var rep = document.getElementById('radViewReport');
    if (!ctx || !rep) return;
    if (!r) { ctx.textContent = 'Study not found - refresh the worklist.'; return; }

    ctx.innerHTML = '<b>Accession:</b> RAD ' + escHtml(r.id)
        + ' &nbsp;|&nbsp; <b>Patient:</b> ' + escHtml(r.patient_name || '--') + ' (' + escHtml(r.hospital_number || '--') + ')'
        + '<br><b>Study:</b> ' + escHtml(r.exam_type || '--')
        + (r.body_part ? ' &nbsp;|&nbsp; <b>Region:</b> ' + escHtml(r.body_part) : '')
        + ' &nbsp;|&nbsp; <b>Priority:</b> ' + escHtml(String(r.urgency || '').toUpperCase())
        + ' &nbsp;|&nbsp; <b>Status:</b> ' + radStatusBadge(r)
        + '<br><b>Referring doctor:</b> ' + escHtml(r.doctor_name || '--')
        + ' &nbsp;|&nbsp; <b>Requested:</b> ' + fmtRadTime(r.requested_at)
        + (r.scheduled_at ? ' &nbsp;|&nbsp; <b>Scheduled:</b> ' + fmtRadTime(r.scheduled_at) : '')
        + (r.clinical_indication ? '<br><b>Indication:</b> ' + escHtml(r.clinical_indication) : '');

    if (r.findings) {
        rep.innerHTML = '<p class="rb-label">Findings</p><p class="rb-val">' + escHtml(r.findings) + '</p>'
            + '<p class="rb-label">Impression / Conclusion</p><p class="rb-val">' + escHtml(r.impression || 'Not stated') + '</p>'
            + '<p class="rb-label">Reported by</p><p class="rb-val">' + escHtml(r.radiologist_name || '--')
            + ' &nbsp;|&nbsp; ' + escHtml(String(r.result_status || '').toUpperCase())
            + (r.verified_name ? ' &nbsp;|&nbsp; Verified by ' + escHtml(r.verified_name) : '')
            + (r.image_path ? ' &nbsp;|&nbsp; Image ref: ' + escHtml(r.image_path) : '')
            + '</p>';
    } else {
        rep.innerHTML = '<p class="rad-sub">No report has been recorded for this study yet.</p>';
    }
    document.getElementById('rad-view-modal').classList.add('show');
}

function closeRadViewModal() {
    var m = document.getElementById('rad-view-modal');
    if (m) m.classList.remove('show');
}

function reportFromView() {
    var id = RAD_VIEW_ID;
    closeRadViewModal();
    openRadReport(id);
}

/* ================= DISPATCH (browser-local log) ================= */
function radReadDispatched() {
    try {
        var raw = localStorage.getItem(RAD_DISPATCH_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch (e) { return []; }
}

function radIsDispatched(id) {
    return radReadDispatched().some(function (d) { return String(d.id) === String(id); });
}

function radDispatch(id) {
    var list = radReadDispatched();
    if (!list.some(function (d) { return String(d.id) === String(id); })) {
        list.push({ id: String(id), at: new Date().toISOString() });
        try { localStorage.setItem(RAD_DISPATCH_KEY, JSON.stringify(list)); } catch (e) {}
    }
    showAlert('Report RAD ' + id + ' marked as dispatched.', 'success');
    renderRadPanes();
    radSwitchTab('dispatched');
}

/* ================= HELPERS ================= */
function truncate(s, n) {
    s = String(s || '');
    return s.length > n ? s.slice(0, n) + '…' : s;
}

function fmtRadTime(v) {
    return window.fmtDateTime ? window.fmtDateTime(v) : (v || '--');
}

function escHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
</script>
