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
// Shared with backend/api/radiology.php and the Service Prices picker via
// radiologyCatalogueData() in backend/includes/functions.php. Add a study
// there and it appears here automatically.
$__radCatalog = radiologyCatalogueData();

// Active imaging prices, keyed by exam name, so the order form can show a
// study's price the moment it is picked. Managed on the Service Prices page
// (service_type='radiology', keyed on service_ref).
$__radPrice = [];
try {
    foreach (Database::getInstance()->fetchAll(
        "SELECT service_ref, price, currency
           FROM service_prices
          WHERE service_type = 'radiology' AND is_active = 1
          ORDER BY effective_date ASC, id ASC"
    ) as $__pr) {
        if ($__pr['service_ref'] !== null && $__pr['service_ref'] !== '') {
            $__radPrice[$__pr['service_ref']] = [
                'price'    => (float)$__pr['price'],
                'currency' => $__pr['currency'] ?: 'GHS',
            ];
        }
    }
} catch (Exception $__e) {
    $__radPrice = [];
}
// Flatten for the two-step (modality -> [{exam, body_part}]) dropdown, tagging
// each study with its configured price (null when none is set).
$__radStudyData = [];
foreach ($__radCatalog as $__mod => $__studies) {
    $__radStudyData[$__mod] = [];
    foreach ($__studies as $__s) {
        $__radStudyData[$__mod][] = [
            'exam_type' => $__s[0],
            'body_part' => $__s[1],
            'price'     => $__radPrice[$__s[0]]['price'] ?? null,
            'currency'  => $__radPrice[$__s[0]]['currency'] ?? null,
        ];
    }
}
?>
<style>
/* ============= RADIOLOGY & IMAGING MODULE : FULL-WIDTH ======================
   Scoped under #rad-page. No Bootstrap and no Tailwind - every utility used
   here is defined below. Font Awesome is available (the shell loads
   frontend/assets/fontawesome/css/all.min.css).
   Restyled to the Radiology workspace mockup: slate palette, rounded cards, a
   #1b659d module banner, a two-column order form and a dark tabbed register.
   Behaviour (vanilla JS, element IDs, workflow) is unchanged.
   No inline support footer: the shell auto-appends the system banner. */
#rad-page{background:#f8fafc;color:#334155;font-size:12px;min-height:100vh;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;padding-bottom:24px}
#rad-page *,#rad-page *::before,#rad-page *::after{box-sizing:border-box}

/* ---- TOP BAR (slim utility strip - the blue module banner sits below) ---- */
#rad-page .rad-topbar{background:#fff;color:#475569;padding:7px 16px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e2e8f0;flex-wrap:wrap;gap:8px}
#rad-page .rad-topbar .title-group{display:flex;align-items:center;gap:8px}
#rad-page .rad-topbar .title-group svg{width:16px;height:16px;fill:none;stroke:#1b659d;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#rad-page .rad-topbar-title{font-size:12px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;color:#1e293b}
#rad-page .rad-topbar-right{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
#rad-page .rad-topbar-right > span{font-size:10px;color:#94a3b8}
#rad-page .rad-nav-btn{background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;padding:4px 11px;font-size:10px;font-weight:700;border-radius:7px;cursor:pointer;text-decoration:none;font-family:inherit;display:inline-flex;align-items:center;gap:5px}
#rad-page .rad-nav-btn:hover{background:#e2e8f0}

/* ---- MODULE HEADER BANNER ---- */
#rad-page .rad-wrap{max-width:1280px;margin:0 auto;padding:14px 16px 16px}
#rad-page .rad-modhead{background:linear-gradient(135deg,#0072BC,#0D47A1);color:#fff;padding:14px 16px;border-radius:8px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;box-shadow:0 2px 6px rgba(13,71,161,.22);border:1px solid #0B3E8F}
#rad-page .rad-modhead .left{display:flex;align-items:center;gap:10px}
#rad-page .rad-modhead .left svg{width:22px;height:22px;fill:none;stroke:#EAF2FC;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
#rad-page .rad-modhead h2{margin:0;font-size:15px;font-weight:800;text-transform:uppercase;letter-spacing:.6px}
#rad-page .rad-modhead .mod-sub{margin:2px 0 0;font-size:10px;font-weight:600;color:#CFE6F5;text-transform:uppercase;letter-spacing:.5px}
#rad-page .rad-modhead .actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
#rad-page .rad-h-btn{border:none;border-radius:4px;cursor:pointer;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.4px;display:inline-flex;align-items:center;gap:6px;padding:7px 12px;font-family:inherit;transition:background .15s}
#rad-page .rad-h-btn svg{width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#rad-page .rad-h-btn.light{background:#0072BC;color:#fff;border:1px solid rgba(255,255,255,.22)}
#rad-page .rad-h-btn.light:hover{background:#0D47A1}
#rad-page .rad-live-badge{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.35);border-radius:4px;font-size:10px;font-weight:bold;letter-spacing:.6px;padding:4px 10px;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase}
#rad-page .rad-live-badge .live-dot{width:8px;height:8px;border-radius:50%;background:#34D399;flex-shrink:0;animation:radPulse 1.6s ease-in-out infinite}
@keyframes radPulse{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(52,211,153,.7)}50%{opacity:.6;box-shadow:0 0 0 4px rgba(52,211,153,0)}}

/* ---- FEATURE NAVIGATION GRID ---- */
#rad-page .rad-feature-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:14px 0 16px}
#rad-page .rad-feature-card{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid #C0C0C0;border-radius:6px;padding:14px;cursor:pointer;text-align:left;font-family:inherit;box-shadow:0 2px 6px rgba(0,0,0,.06);transition:transform .15s,box-shadow .15s,border-color .15s}
#rad-page .rad-feature-card:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(15,45,89,.12);border-color:#0072BC}
#rad-page .rad-feature-card svg{width:44px;height:44px;flex-shrink:0;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;padding:8px;border-radius:6px;border:1px solid transparent}
#rad-page .rad-feature-card .fc-title{font-size:12px;font-weight:800;color:#0D47A1;text-transform:uppercase;letter-spacing:.4px;margin:0 0 2px;display:block}
#rad-page .rad-feature-card .fc-sub{font-size:10.5px;color:#64748b;margin:0;display:block}
/* Icon tile tint per card. A CSS rule outranks the inline stroke="" presentation
   attribute, so the tiles take these colours without touching the markup. */
#rad-page .rad-feature-card:nth-child(1) svg{stroke:#D32F2F;background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.2)}
#rad-page .rad-feature-card:nth-child(2) svg{stroke:#D97706;background:rgba(245,158,11,.08);border-color:rgba(245,158,11,.2)}
#rad-page .rad-feature-card:nth-child(3) svg{stroke:#6D28D9;background:rgba(124,58,237,.08);border-color:rgba(124,58,237,.2)}
#rad-page .rad-feature-card:nth-child(4) svg{stroke:#2E7D32;background:rgba(46,125,50,.08);border-color:rgba(46,125,50,.2)}

/* ---- PANELS ---- */
#rad-page .rad-panel{background:#fff;border:1px solid #C0C0C0;border-radius:4px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.06)}
#rad-page .rad-panel + .rad-panel{margin-top:14px}
#rad-page .rad-panel.is-first{border-radius:4px}
#rad-page .rad-panel-head{background:#fff;padding:14px 16px 0;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
#rad-page .rad-panel-head.dark{background:linear-gradient(135deg,#0072BC,#0D47A1);padding:0}
#rad-page .rad-panel-head .head-left{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#0072BC,#0D47A1);color:#fff;border-radius:3px;padding:8px 12px;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.4px;border:1px solid #0B3E8F}
#rad-page .rad-panel-head svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

/* ---- ORDER FORM ---- */
#rad-page .rad-form{display:grid;grid-template-columns:repeat(2,1fr);gap:12px 16px;padding:16px 16px 20px;background:#fff}
#rad-page .rad-field{display:flex;flex-direction:column;gap:5px}
#rad-page .rad-field label{font-weight:700;color:#475569;font-size:10px;text-transform:uppercase;letter-spacing:.5px}
#rad-page .rad-field label .req{color:#e11d48}
#rad-page .rad-field select,#rad-page .rad-field input,#rad-page .rad-field textarea{width:100%;padding:9px 11px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px;background:#f8fafc;color:#334155;font-family:inherit}
#rad-page .rad-field textarea{resize:vertical}
#rad-page .rad-field select:focus,#rad-page .rad-field input:focus,#rad-page .rad-field textarea:focus{outline:none;border-color:#1b659d;background:#fff;box-shadow:0 0 0 2px rgba(27,101,157,.18)}
#rad-page .rad-field input[readonly]{background:#f1f5f9;color:#15803d;font-weight:700;cursor:not-allowed}
#rad-page .span-2,#rad-page .span-3,#rad-page .span-4{grid-column:span 1}
#rad-page .span-6{grid-column:span 2}
#rad-page .rad-form-actions{grid-column:span 2;display:flex;justify-content:flex-end;gap:10px;margin-top:4px}
#rad-page .rad-btn-submit{background:#1b659d;color:#fff;border:none;padding:9px 20px;font-weight:700;font-size:11px;border-radius:8px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.4px;display:inline-flex;align-items:center;gap:8px;box-shadow:0 1px 2px rgba(15,23,42,.15)}
#rad-page .rad-btn-submit:hover{background:#155380}
#rad-page .rad-btn-submit svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

/* ---- TABS ---- */
#rad-page .rad-tabs{display:flex;flex-wrap:wrap}
#rad-page .rad-tab{background:transparent;border:none;color:#cbd5e1;font-family:inherit;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.5px;padding:11px 16px;cursor:pointer;transition:background .15s}
#rad-page .rad-tab:hover{background:#334155;color:#fff}
#rad-page .rad-tab.active{background:#1b659d;color:#fff}
#rad-page .rad-tab .tab-count{background:rgba(255,255,255,.2);border-radius:999px;padding:1px 6px;margin-left:5px;font-size:10px}
#rad-page .rad-tab.active .tab-count{background:#fff;color:#1b659d}

/* ---- VIEW TOOLBAR ---- */
#rad-page .rad-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:10px 14px;border-bottom:1px solid #e2e8f0;background:#f8fafc}
#rad-page .rad-toolbar .search{display:flex;align-items:center;gap:8px;flex:1 1 240px;min-width:200px}
#rad-page .rad-toolbar .search svg{width:16px;height:16px;fill:none;stroke:#94a3b8;stroke-width:2;flex-shrink:0}
#rad-page .rad-toolbar .search input{flex:1;padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px;font-family:inherit;background:#fff}
#rad-page .rad-toolbar .search input:focus{outline:none;border-color:#1b659d;box-shadow:0 0 0 2px rgba(27,101,157,.18)}
#rad-page .rad-counts{font-size:10px;color:#64748b;font-weight:600;display:flex;gap:12px;flex-wrap:wrap}
#rad-page .rad-counts b{color:#1b659d}
#rad-page #cEmergency{color:#e11d48}
#rad-page .rad-filters{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
#rad-page .rad-filters select{padding:6px 9px;border:1px solid #cbd5e1;border-radius:8px;font-size:11px;font-family:inherit;background:#fff}

/* ---- TAB PANES + TABLE ---- */
#rad-page .rad-pane{display:none}
#rad-page .rad-pane.active{display:block}
#rad-page .rad-table-wrap{background:#fff;max-height:520px;overflow-y:auto}
#rad-page .rad-table{width:100%;border-collapse:collapse;font-size:12px}
#rad-page .rad-table th{background:#f1f5f9;color:#475569;font-weight:700;text-align:left;padding:10px 12px;border-bottom:1px solid #e2e8f0;position:sticky;top:0;z-index:1;white-space:nowrap;text-transform:uppercase;font-size:10px;letter-spacing:.5px}
#rad-page .rad-table td{padding:10px 12px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
#rad-page .rad-table tbody tr:hover{background:#f8fafc}
#rad-page .rad-table .first-col{padding-left:16px}
#rad-page .rad-table .act-col{text-align:center;white-space:nowrap}
#rad-page .rad-empty{text-align:center;padding:28px 16px;color:#94a3b8;font-weight:500}
#rad-page .rad-badge{display:inline-block;padding:3px 9px;border-radius:6px;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
#rad-page .rad-badge.grey{color:#475569;background:#f1f5f9}
#rad-page .rad-badge.amber{color:#b45309;background:#fef3c7}
#rad-page .rad-badge.blue{color:#1d4ed8;background:#dbeafe}
#rad-page .rad-badge.green{color:#047857;background:#d1fae5}
#rad-page .rad-badge.red{color:#be123c;background:#ffe4e6}
#rad-page .rad-badge.purple{color:#6d28d9;background:#ede9fe}
#rad-page .rad-btn-outline{background:#fff;color:#1b659d;border:1px solid #cbd5e1;padding:5px 11px;font-weight:700;font-size:10px;border-radius:7px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#rad-page .rad-btn-outline:hover{background:#f1f5f9}
#rad-page .rad-btn-primary{background:#1b659d;color:#fff;border:none;padding:5px 12px;font-weight:700;font-size:10px;border-radius:7px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#rad-page .rad-btn-primary:hover{background:#155380}
#rad-page .rad-btn-success{background:#059669;color:#fff;border:none;padding:5px 11px;font-weight:700;font-size:10px;border-radius:7px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px}
#rad-page .rad-btn-success:hover{background:#047857}
#rad-page .rad-btn-danger{background:#dc2626;color:#fff;border:none;padding:5px 11px;font-weight:700;font-size:10px;border-radius:7px;cursor:pointer;font-family:inherit;text-transform:uppercase;letter-spacing:.3px}
#rad-page .rad-btn-danger:hover{background:#b91c1c}
#rad-page .rad-btn-success svg,#rad-page .rad-btn-outline svg{width:12px;height:12px;fill:none;stroke:currentColor;stroke-width:2}
#rad-page .rad-muted{color:#94a3b8}
#rad-page .rad-sub{font-size:10px;color:#64748b;display:block}

/* ---- MODALS (scoped; both live inside #rad-page) ---- */
#rad-page .modal{display:none;position:fixed;inset:0;z-index:2000;background:rgba(15,23,42,.55);align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto}
#rad-page .modal.show{display:flex}
#rad-page .modal.centered{align-items:center}
#rad-page .modal-content{background:#fff;border-radius:12px;width:min(640px,100%);box-shadow:0 12px 34px rgba(15,23,42,.25);overflow:hidden}
#rad-page .modal-header{background:#1b659d;color:#fff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;gap:10px}
#rad-page .modal-header h3{margin:0;font-size:13px;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:8px}
#rad-page .modal-header.danger{background:#dc2626}
#rad-page .modal-header svg{width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#rad-page .modal-close{background:none;border:none;color:#fff;font-size:22px;line-height:1;cursor:pointer;padding:0 4px}
#rad-page .modal-close:hover{color:#fde68a}
#rad-page .modal-body{padding:18px 20px;background:#f8fafc}
#rad-page .modal-body.text-center{text-align:center}
#rad-page .rad-alert-title{font-size:14px;font-weight:700;color:#1e293b;margin:0 0 6px}
#rad-page .rad-alert-sub{font-size:12px;color:#64748b;margin:0}
#rad-page .modal-body .form-group{margin-bottom:14px}
#rad-page .modal-body label{display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#475569;margin-bottom:5px}
#rad-page .modal-body input,#rad-page .modal-body select,#rad-page .modal-body textarea{width:100%;padding:9px 11px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px;background:#fff;color:#334155;font-family:inherit;box-sizing:border-box}
#rad-page .modal-body input[readonly]{background:#f1f5f9;color:#64748b}
#rad-page .modal-body textarea{resize:vertical}
#rad-page .modal-body input:focus,#rad-page .modal-body select:focus,#rad-page .modal-body textarea:focus{outline:none;border-color:#1b659d;box-shadow:0 0 0 2px rgba(27,101,157,.18)}
#rad-page .rad-context{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;margin-bottom:14px;font-size:12px;line-height:1.6;color:#475569}
#rad-page .rad-context b{color:#1b659d}
#rad-page .form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px;padding-top:14px;border-top:1px solid #e2e8f0}
#rad-page .form-actions.center{justify-content:center;border-top:none;margin-top:8px;padding-top:0}
#rad-page .btn{border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:9px 18px;font-family:inherit;display:inline-flex;align-items:center;gap:6px}
#rad-page .btn-primary{background:#1b659d;color:#fff}
#rad-page .btn-primary:hover{background:#155380}
#rad-page .btn-secondary{background:#f1f5f9;color:#334155;border:1px solid #e2e8f0}
#rad-page .btn-secondary:hover{background:#e2e8f0}

/* ---- REPORT PREVIEW ---- */
#rad-page .rad-report-box{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px;font-size:12px;line-height:1.65;max-height:210px;overflow-y:auto}
#rad-page .rad-report-box .rb-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#64748b;margin:0 0 3px}
#rad-page .rad-report-box .rb-val{white-space:pre-wrap;margin:0 0 10px;color:#222}
#rad-page .rad-report-box .rb-val:last-child{margin-bottom:0}

/* ---- CANCEL / RESCHEDULE TOGGLE (inside the order form panel) ---- */
#rad-page .rad-cr-head{padding:0 16px 18px;background:#fff}
#rad-page .rad-cr-divider{height:2px;background:#e2e8f0;border-radius:2px;position:relative;cursor:pointer;user-select:none;transition:background .2s}
#rad-page .rad-cr-divider:hover{background:#cbd5e1}
#rad-page .rad-cr-divider:focus-visible{outline:2px solid #1b659d;outline-offset:4px}
#rad-page .rad-cr-divider span{position:absolute;top:-9px;left:6px;background:#dc2626;color:#fff;font-size:10px;font-weight:700;padding:3px 12px;border-radius:999px;text-transform:uppercase;letter-spacing:.5px;box-shadow:0 2px 6px rgba(220,38,38,.3)}
#rad-page .rad-cr-divider .dhi-hint{font-weight:600;text-transform:none;letter-spacing:.2px;opacity:.85}
#rad-page .rad-cr-divider .dhi-caret{display:inline-block;margin-left:8px;font-size:9px;font-style:normal;opacity:.9;transition:transform .25s ease}
#rad-page .rad-cr-divider.open .dhi-caret{transform:rotate(180deg)}
#rad-page #radCrBody .rad-form{background:#fff5f5;border-top:1px solid #fee2e2}
#rad-page .rad-cr-hint{grid-column:span 2;font-size:10.5px;color:#92400e;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:8px 10px;margin:0}
#rad-page .rad-cr-info{display:block;font-size:10px;color:#be123c;margin-top:3px;max-width:170px;white-space:normal;line-height:1.35}

@media (max-width:1024px){
  #rad-page .rad-feature-grid{grid-template-columns:1fr 1fr}
}
@media (max-width:760px){
  #rad-page .rad-feature-grid{grid-template-columns:1fr}
  #rad-page .rad-form{grid-template-columns:1fr}
  #rad-page .span-2,#rad-page .span-3,#rad-page .span-4,#rad-page .span-6,#rad-page .rad-form-actions,#rad-page .rad-cr-hint{grid-column:span 1}
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
        <div>
          <h2>Radiology &amp; Imaging Module</h2>
          <p class="mod-sub">HMS &mdash; Healthcare Management System</p>
        </div>
      </div>
      <div class="actions">
        <span class="rad-live-badge" id="radLiveBadge"><span class="live-dot"></span> Live Auto-Refresh</span>
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
          <select id="radStudy" onchange="syncRadBodyPart(); updateRadPrice();" required>
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

        <div class="rad-field">
          <label for="radPrice">Exam Price</label>
          <input type="text" id="radPrice" readonly value="&mdash;" title="Set on the Service Prices page (Radiology)">
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

      <!-- CANCEL OR RESCHEDULE (toggle, with reasons) -->
      <div class="rad-cr-head">
        <div class="rad-cr-divider" id="radCrToggle" role="button" tabindex="0"
             aria-expanded="false" aria-controls="radCrBody"
             title="Press to show or hide Cancel / Reschedule">
          <span>Cancel or Reschedule Study <b class="dhi-hint">- toggle or press to view</b><i class="dhi-caret" aria-hidden="true">&#9660;</i></span>
        </div>
      </div>

      <div id="radCrBody" style="display:none;">
        <form class="rad-form" id="radCancelRescheduleForm">
          <div class="rad-field span-3">
            <label for="radCrRequisition">Study / Accession <span class="req">*</span></label>
            <select id="radCrRequisition" required>
              <option value="">-- Select Study --</option>
            </select>
          </div>

          <div class="rad-field span-3">
            <label for="radCrAction">Action <span class="req">*</span></label>
            <select id="radCrAction" onchange="radCrToggleAction()" required>
              <option value="">-- Select Action --</option>
              <option value="cancel">Cancel Study</option>
              <option value="reschedule">Reschedule Study</option>
            </select>
          </div>

          <div class="rad-field span-3" id="radCrWhenField" style="display:none;">
            <label for="radCrWhen">New Date &amp; Time <span class="req">*</span></label>
            <input type="datetime-local" id="radCrWhen">
          </div>

          <div class="rad-field span-3">
            <label for="radCrReason">Reason <span class="req">*</span></label>
            <select id="radCrReason" required>
              <option value="">-- Select Reason --</option>
              <option>Patient not ready / not on bed</option>
              <option>Patient not fasting / not prepared</option>
              <option>Machine / scanner fault</option>
              <option>Modality unavailable / already booked</option>
              <option>Contrast / consumable not available</option>
              <option>Patient deferred / requested later date</option>
              <option>Patient too unstable to attend</option>
              <option>Duplicate request</option>
              <option>Wrong study / wrong region ordered</option>
              <option>Other (state in details)</option>
            </select>
          </div>

          <div class="rad-field span-6">
            <label for="radCrNotes">Details / Notes</label>
            <input type="text" id="radCrNotes" placeholder="Optional details, e.g. CT scanner under maintenance until Friday, patient transferred to another ward...">
          </div>

          <p class="rad-cr-hint">Cancelling stops the study and records the reason against the request. Rescheduling keeps the study open and moves it to the new slot on the worklist.</p>

          <div class="rad-form-actions">
            <button type="submit" class="rad-btn-submit" id="radCrSubmit">
              <svg viewBox="0 0 24 24"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
              Submit Cancel / Reschedule
            </button>
          </div>
        </form>
      </div>
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
          <button type="button" class="rad-tab" id="radTabCancelled" onclick="radSwitchTab('cancelled')">Cancelled</button>
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
          <span>Cancelled: <b id="cCancelled">0</b></span>
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
        <div class="rad-pane" id="radPaneCancelled"></div>
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

/* exam_type -> configured price (from the Service Prices page), for the order
   form's auto-filled price field. Studies with no price are simply absent. */
var RAD_PRICE_OF = (function () {
    var out = {};
    Object.keys(radStudiesData).forEach(function (mod) {
        radStudiesData[mod].forEach(function (s) {
            if (s.price != null) out[s.exam_type] = s;
        });
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

    /* Cancel / Reschedule toggle + form */
    var crToggle = document.getElementById('radCrToggle');
    if (crToggle) {
        crToggle.addEventListener('click', function () { radCrToggle(); });
        crToggle.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' || ev.key === ' ' || ev.key === 'Spacebar') {
                ev.preventDefault();
                radCrToggle();
            }
        });
    }
    var crForm = document.getElementById('radCancelRescheduleForm');
    if (crForm) crForm.addEventListener('submit', handleRadCancelReschedule);
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
            populateRadCrRequisitions();
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
    updateRadPrice();
}

/* Show the picked study's configured price, or say so when none is set. */
function updateRadPrice() {
    var study = document.getElementById('radStudy');
    var out = document.getElementById('radPrice');
    if (!study || !out) return;
    var name = study.value || '';
    var p = RAD_PRICE_OF[name];
    if (p) {
        out.value = (p.currency || 'GHS') + ' ' + Number(p.price).toFixed(2);
    } else {
        out.value = name ? 'No price set' : '\u2014';
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
            var msg = 'Imaging order submitted: ' + study + '.';
            if (d.charge && d.price != null) {
                msg += ' ' + (d.currency || 'GHS') + ' ' + Number(d.price).toFixed(2) + ' added to the bill.';
            }
            showAlert(msg, 'success');
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
    populateRadCrRequisitions();
}

/* ================= TABS ================= */
var RAD_TABS = ['worklist', 'pending', 'scheduled', 'inprogress', 'reported', 'verified', 'dispatched', 'cancelled'];

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
                    + '<td>' + radStatusBadge(r) + radCrInfo(r) + '</td>'
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
        out.push('<button class="rad-btn-outline" onclick="openRadView(' + r.id + ')">View</button>');
        return out.join(' ');
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
                    + '<td>' + radStatusBadge(r) + radCrInfo(r) + '</td>'
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
    setCount('cCancelled', radData.filter(function (r) { return r.status === 'cancelled'; }).length);
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

    /* Cancelled - studies stopped with a recorded reason. Kept out of the live
       worklist but visible here so the reason and who actioned it stay auditable. */
    setPane('radPaneCancelled', radWorklistTable(
        radData.filter(function (r) { return r.status === 'cancelled'; }).filter(radPassFilter), q,
        q || 'No studies have been cancelled - use Cancel or Reschedule above when a study cannot go ahead.'
    ));
}

function setCount(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; }
function setPane(id, html) { var el = document.getElementById(id); if (el) el.innerHTML = html; }

/* ================= CANCEL / RESCHEDULE (with reasons) =================
   Toggle reveals a panel that cancels or reschedules an outstanding study
   (pending / scheduled / in_progress) with a reason. Reschedule also needs the
   new date/time; the backend stores the reason and moves the study to that
   slot so it reappears on the worklist. */
function radCrToggle(open) {
    var divider = document.getElementById('radCrToggle');
    var body = document.getElementById('radCrBody');
    if (!divider || !body) return;
    if (typeof open !== 'boolean') open = (body.style.display === 'none' || !body.style.display);
    body.style.display = open ? 'block' : 'none';
    divider.classList.toggle('open', open);
    divider.setAttribute('aria-expanded', open ? 'true' : 'false');
    var hint = divider.querySelector('.dhi-hint');
    if (hint) hint.textContent = open ? '- press to hide' : '- toggle or press to view';
    if (open) populateRadCrRequisitions();
}

function radCrToggleAction() {
    var action = document.getElementById('radCrAction');
    var whenField = document.getElementById('radCrWhenField');
    var whenInput = document.getElementById('radCrWhen');
    if (!action || !whenField) return;
    var isResched = action.value === 'reschedule';
    whenField.style.display = isResched ? 'flex' : 'none';
    if (whenInput) whenInput.required = isResched;
}

function populateRadCrRequisitions() {
    var sel = document.getElementById('radCrRequisition');
    if (!sel) return;
    var keep = sel.value;
    var rows = (radData || []).filter(function (r) {
        return r.status === 'pending' || r.status === 'scheduled' || r.status === 'in_progress';
    });
    sel.innerHTML = '<option value="">-- Select Study --</option>'
        + rows.map(function (r) {
            var slot = r.scheduled_at ? ' | ' + fmtRadTime(r.scheduled_at) : '';
            return '<option value="' + r.id + '">RAD ' + escHtml(r.id) + ' - '
                + escHtml(r.patient_name || '') + ' (' + escHtml(r.exam_type || '') + ')' + escHtml(slot) + '</option>';
        }).join('');
    if (keep && rows.some(function (r) { return String(r.id) === String(keep); })) sel.value = keep;
}

async function handleRadCancelReschedule(e) {
    e.preventDefault();
    var id = document.getElementById('radCrRequisition').value;
    var action = document.getElementById('radCrAction').value;
    var reasonSel = document.getElementById('radCrReason').value;
    var notes = document.getElementById('radCrNotes').value.trim();
    var when = document.getElementById('radCrWhen').value;

    if (!id) { showAlert('Select the study to cancel or reschedule.', 'error'); return; }
    if (!action) { showAlert('Choose Cancel or Reschedule.', 'error'); return; }
    if (!reasonSel) { showAlert('Select a reason.', 'error'); return; }
    if (action === 'reschedule' && !when) { showAlert('Enter the new date and time.', 'error'); return; }

    var reason = reasonSel + (notes ? ' - ' + notes : '');
    var payload = { id: id, reason: reason };
    if (action === 'reschedule') payload.scheduled_at = when;

    try {
        var r = await fetch('/hms/backend/api/radiology.php?action=' + (action === 'cancel' ? 'cancel' : 'reschedule'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        var d = await r.json();
        if (d.success) {
            showAlert(action === 'cancel'
                ? 'Study RAD ' + id + ' cancelled. Reason recorded: ' + reason
                : 'Study RAD ' + id + ' rescheduled to ' + when.replace('T', ' ') + '.', 'success');
            e.target.reset();
            radCrToggleAction();
            await loadRadRequests();
        } else {
            showAlert(d.error || 'Action failed.', 'error');
        }
    } catch (err) {
        console.error('Radiology cancel/reschedule error:', err);
        showAlert('Network error. Please try again.', 'error');
    }
}

/* Small note under a study's status: the cancellation reason, or the slot a
   study was moved to. */
function radCrInfo(r) {
    if (r.status === 'cancelled' && r.status_reason) {
        return '<span class="rad-cr-info"><b>Cancelled:</b> ' + escHtml(r.status_reason) + '</span>';
    }
    if (r.rescheduled_to) {
        return '<span class="rad-cr-info" style="color:#b45309;"><b>Rescheduled ' + escHtml(fmtRadTime(r.rescheduled_to)) + ':</b> '
            + (r.status_reason ? escHtml(r.status_reason) : '') + '</span>';
    }
    return '';
}

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
        + (r.clinical_indication ? '<br><b>Indication:</b> ' + escHtml(r.clinical_indication) : '')
        + (r.status_reason ? '<br><b>' + (r.status === 'cancelled' ? 'Cancellation reason' : 'Reschedule reason') + ':</b> ' + escHtml(r.status_reason) : '')
        + (r.rescheduled_to ? ' &nbsp;|&nbsp; <b>Moved to:</b> ' + fmtRadTime(r.rescheduled_to) : '');

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
