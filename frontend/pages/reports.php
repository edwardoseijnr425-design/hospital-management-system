<?php
// Reports (MIS) - consolidated reporting page: DHIMS Report & Data Export Console
// on top, standard report generator below. SPA fragment loaded by the shell.
require_once __DIR__ . '/../../backend/config/config.php';
?>
<style>
/* ============ MIS / REPORTS : DHIMS REPORT & DATA EXPORT CONSOLE ============
   Scoped under #dhims-cc. All data is real (dhims.php monthly aggregates,
   system overview counts, live departments). Contact banner is auto-appended
   by the shell - never duplicate personal contact details here. */
#dhims-cc .ctrl-row{display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap}
#dhims-cc .form-group{display:flex;flex-direction:column;gap:5px}
#dhims-cc .form-group label{font-weight:600;color:#333;font-size:11px;text-transform:uppercase;letter-spacing:.3px}
#dhims-cc .form-control{padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;font-family:inherit}
#dhims-cc .form-control:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#dhims-cc .btn-action{border:none;padding:8px 18px;font-weight:bold;font-size:12px;border-radius:3px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase;letter-spacing:.5px;font-family:inherit}
#dhims-cc .btn-action svg{width:14px;height:14px;fill:#fff}
#dhims-cc .btn-primary{background-color:#0b5fa5;color:#fff}
#dhims-cc .btn-primary:hover{background-color:#004080}
#dhims-cc .btn-export{background-color:#0284C7;color:#fff}
#dhims-cc .btn-export:hover{background-color:#0369a1}
#dhims-cc .banner{margin:0 0 18px;padding:14px 18px;background:linear-gradient(135deg,#0b5fa5,#0D47A1);color:#fff;border-radius:4px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
#dhims-cc .banner .bt{font-size:15px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;display:flex;align-items:center;gap:10px}
#dhims-cc .banner .bt svg{width:22px;height:22px;fill:#fff}
#dhims-cc .banner .bs{font-size:11px;color:#d1e5f7;font-weight:600}
#dhims-cc .filter-card{background:#fff;border:1px solid #b2c8de;border-radius:4px;padding:16px 18px;box-shadow:0 3px 8px rgba(0,0,0,.06);margin-bottom:18px}
#dhims-cc .filter-title{font-size:12px;font-weight:bold;color:#0b5fa5;border-bottom:2px solid #0b5fa5;padding-bottom:4px;margin-bottom:14px;text-transform:uppercase}
#dhims-cc .kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px}
#dhims-cc .kpi-card{background:#fff;border:1px solid #b2c8de;border-radius:4px;padding:12px 14px;display:flex;align-items:center;gap:10px}
#dhims-cc .kpi-icon{width:40px;height:40px;border-radius:6px;display:flex;align-items:center;justify-content:center;flex:0 0 auto;background:#E0F2FE;color:#0284C7}
#dhims-cc .kpi-icon svg{width:20px;height:20px;fill:currentColor}
#dhims-cc .kpi-info{min-width:0}
#dhims-cc .kpi-num{font-size:20px;font-weight:800;color:#0F2D59;line-height:1.1}
#dhims-cc .kpi-label{font-size:9.5px;text-transform:uppercase;letter-spacing:.4px;color:#64748b;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#dhims-cc .cat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px}
#dhims-cc .cat-card{background:#fff;border:1px solid #E2E8F0;border-radius:6px;padding:14px 16px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:box-shadow .2s,transform .2s}
#dhims-cc .cat-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.1);transform:translateY(-2px)}
#dhims-cc .cat-icon{width:46px;height:46px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex:0 0 auto}
#dhims-cc .cat-icon svg{width:24px;height:24px}
#dhims-cc .cat-card h6{margin:0;font-size:13px;font-weight:800;color:#0F2D59;letter-spacing:.3px}
#dhims-cc .cat-card small{font-size:10.5px;color:#94a3b8;display:block;margin-top:2px;line-height:1.4}
#dhims-cc .panel-box{background:#fff;border:1px solid #b2c8de;border-radius:4px;overflow:hidden;box-shadow:0 3px 8px rgba(0,0,0,.06);margin-bottom:18px}
#dhims-cc .panel-header{background-color:#0b5fa5;color:#fff;font-weight:bold;font-size:12px;padding:10px 15px;text-transform:uppercase;display:flex;align-items:center;gap:8px;justify-content:space-between}
#dhims-cc .panel-header .ph-l{display:flex;align-items:center;gap:8px}
#dhims-cc .panel-header svg{width:16px;height:16px;fill:#fff}
#dhims-cc .panel-body{padding:16px 18px;background-color:#f8fafc}
#dhims-cc .table-container{overflow-x:auto}
#dhims-cc .data-table{width:100%;border-collapse:collapse;font-size:12px;background:#fff}
#dhims-cc .data-table th{background:#0F2D59;color:#fff;text-align:left;padding:7px 10px;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
#dhims-cc .data-table td{padding:7px 10px;border-bottom:1px solid #e2e8f0;color:#333}
#dhims-cc .data-table tr:nth-child(even) td{background:#f1f5f9}
#dhims-cc .data-table td.num,#dhims-cc .data-table th.num{text-align:right;font-variant-numeric:tabular-nums}
#dhims-cc .data-table .total-row td{background:#e0f2fe;font-weight:700;color:#0F2D59}
#dhims-cc .pill{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;text-transform:uppercase}
#dhims-cc .pill-opd{background:#e0f2fe;color:#0284C7}
#dhims-cc .pill-ipd{background:#fef3c7;color:#d97706}
#dhims-cc .pill-emergency{background:#fee2e2;color:#dc2626}
#dhims-cc .pill-nhia{background:#dcfce7;color:#16a34a}
#dhims-cc .empty-note{padding:14px;text-align:center;color:#94a3b8;font-style:italic;font-size:12px}
#dhims-cc .mt15{margin-top:15px}
#dhims-cc .misdhims-divider{height:2px;background:#c0d4e8;margin:26px 0 20px;border-radius:2px;position:relative}
#dhims-cc .misdhims-divider span{position:absolute;top:-9px;left:16px;background:#0b5fa5;color:#fff;font-size:10px;font-weight:700;padding:3px 12px;border-radius:10px;text-transform:uppercase;letter-spacing:.5px}

/* ---- Monthly indicators: Edit control, reported-figure markers, dialog ---- */
#dhims-cc .dhi-edit-btn{background:#fff;color:#0b5fa5;border:1px solid #b2c8de;border-radius:3px;padding:4px 10px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:5px}
#dhims-cc .dhi-edit-btn svg{width:12px;height:12px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#dhims-cc .dhi-edit-btn:hover{background:#e2edf7}
#dhims-cc .dhi-edit-btn.is-ovr{background:#FEF5E0;border-color:#f0ad4e;color:#B9770E}
#dhims-cc .dhi-edit-btn.is-ovr:hover{background:#FDEBC8}
#dhims-cc .ovr-tag{display:inline-block;margin-top:3px;background:#FEF5E0;color:#B9770E;border:1px solid #f0ad4e;border-radius:3px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;padding:1px 6px;cursor:help}
#dhims-cc .ovr-sub{text-transform:none;letter-spacing:0;font-weight:600;color:#8A5A00;background:none;border:none;padding:1px 0 0}
#dhims-cc .ovr-badge{background:#E9F9EF;color:#1E7A34;border:1px solid #b7e4c6;border-radius:999px;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;padding:3px 9px;white-space:nowrap}
#dhims-cc .ovr-badge.has-ovr{background:#FEF5E0;color:#B9770E;border-color:#f0ad4e}
#dhims-cc .data-table td.ctr{text-align:center;white-space:nowrap}
#dhi-override-modal{display:none;position:fixed;inset:0;z-index:2200;background:rgba(15,45,89,.55);align-items:center;justify-content:center;padding:24px 16px;overflow-y:auto}
#dhi-override-modal.show{display:flex}
#dhi-override-modal .modal-content{background:#fff;border-radius:8px;width:100%;box-shadow:0 12px 34px rgba(0,0,0,.25);overflow:hidden}
#dhi-override-modal .modal-header{background-color:#0b5fa5;color:#fff;padding:13px 18px;display:flex;justify-content:space-between;align-items:center;gap:10px}
#dhi-override-modal .modal-header h3{margin:0;font-size:13.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;gap:8px;min-width:0}
#dhi-override-modal .modal-header h3 span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#dhi-override-modal .modal-header svg{width:17px;height:17px;fill:none;stroke:#fff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
#dhi-override-modal .modal-close{background:none;border:none;color:#fff;font-size:22px;line-height:1;cursor:pointer;padding:0 4px;flex-shrink:0}
#dhi-override-modal .modal-close:hover{color:#FFD54F}
#dhi-override-modal .modal-body{padding:18px 20px;background:#F8FAFC}
#dhi-override-modal .ovr-computed{display:flex;justify-content:space-between;align-items:center;background:#E6EEF5;border:1px solid #b2c8de;border-radius:4px;padding:9px 12px;margin-bottom:14px;font-size:11.5px;color:#334155}
#dhi-override-modal .ovr-computed b{color:#0F2D59;font-size:14px}
#dhi-override-modal .form-group{margin-bottom:14px}
#dhi-override-modal label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#64748b;margin-bottom:5px}
#dhi-override-modal input,#dhi-override-modal textarea{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background:#fff;color:#222;font-family:inherit;box-sizing:border-box}
#dhi-override-modal textarea{resize:vertical}
#dhi-override-modal input:focus,#dhi-override-modal textarea:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#dhi-override-modal .ovr-hint{margin:0 0 8px;font-size:11px;color:#475569}
#dhi-override-modal .ovr-note{margin:0;font-size:10.5px;color:#94a3b8;line-height:1.5}
#dhi-override-modal .form-actions{display:flex;justify-content:flex-end;gap:10px;padding:13px 20px;background:#F1F5F9;border-top:1px solid #e1e8f0}
#dhi-override-modal .btn{border:none;border-radius:3px;cursor:pointer;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:8px 16px;font-family:inherit;display:inline-flex;align-items:center;gap:6px}
#dhi-override-modal .btn-primary{background-color:#0072BC;color:#fff}
#dhi-override-modal .btn-primary:hover{filter:brightness(1.08)}
#dhi-override-modal .btn-primary:disabled{opacity:.6;cursor:not-allowed}
#dhi-override-modal .btn-secondary{background:#F1F5F9;color:#34495E;border:1px solid #C0C0C0}
#dhi-override-modal .btn-secondary:hover{background:#E2E8F0}
@media (max-width:900px){
  #dhims-cc .kpi-grid{grid-template-columns:repeat(2,1fr)}
  #dhims-cc .cat-grid{grid-template-columns:1fr}
}
@media (max-width:600px){
  #dhims-cc .kpi-grid{grid-template-columns:1fr}
}
</style>

<div id="dhims-cc">

  <!-- DHIMS REPORT & DATA EXPORT CONSOLE BANNER -->
  <div class="banner">
    <div class="bt">
      <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      DHIMS Report &amp; Data Export Console
    </div>
    <span class="bs" id="dhiMonthBadge"></span>
  </div>

  <!-- QUICK DHIMS EXPORT FILTER -->
  <div class="filter-card">
    <div class="filter-title">⚡ Quick DHIMS Export Filter</div>
    <div class="ctrl-row">
      <div class="form-group">
        <label for="dhiMonth">Reporting Period</label>
        <input type="month" id="dhiMonth" class="form-control" style="width:190px;">
      </div>
      <div class="form-group">
        <label for="dhiDept">Department / Clinic</label>
        <select id="dhiDept" class="form-control" style="width:230px;">
          <option value="">All Departments (OPD + IPD)</option>
        </select>
      </div>
      <div class="form-group">
        <label for="dhiFormat">Export Format</label>
        <select id="dhiFormat" class="form-control" style="width:220px;">
          <option value="csv">DHIMS2 Compatible (CSV/Excel)</option>
          <option value="csv_section">PDF Summary Report</option>
          <option value="xml">XML Data Exchange Format</option>
        </select>
      </div>
      <button type="button" class="btn-action btn-primary" onclick="dhiLoad()"><svg viewBox="0 0 24 24"><path d="M21 3H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h5v2h8v-2h5c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 14H3V5h18v12z"/></svg>Generate DHIMS Report</button>
      <button type="button" class="btn-action btn-export" onclick="dhiExportAll()"><svg viewBox="0 0 24 24"><path d="M12 3v10.55l-2.94-2.94-1.41 1.41L12 16.41l4.35-4.35-1.41-1.41L12 13.55V3z"/><path d="M19 13v6H5v-6H3v6c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-6h-2z"/></svg>DHIMS2 Export</button>
    </div>
    <div class="empty-note" id="dhiStatusNote" style="display:none"></div>
  </div>

  <!-- KPI STAT CARDS -->
  <div class="kpi-grid" id="dhiKpiGrid">
    <div class="kpi-card"><div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div><div class="kpi-info"><div class="kpi-num" id="dhiKpiPatients">–</div><div class="kpi-label">Total Patients</div></div></div>
    <div class="kpi-card"><div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg></div><div class="kpi-info"><div class="kpi-num" id="dhiKpiVisits">–</div><div class="kpi-label">Total Visits</div></div></div>
    <div class="kpi-card"><div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M12 7V2L4 12h6v5l8-10h-6z"/></svg></div><div class="kpi-info"><div class="kpi-num" id="dhiKpiToday">–</div><div class="kpi-label">Visits Today</div></div></div>
    <div class="kpi-card"><div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-3 12l-3-1.5L11 14l-3-1.5V8l1.5 1.5L13 8l3 1.5V11l-1.5 1.5L17 14z"/></svg></div><div class="kpi-info"><div class="kpi-num" id="dhiKpiCons">–</div><div class="kpi-label">Consultations</div></div></div>
    <div class="kpi-card"><div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M16 4h-2V2h-4v2H8c-1.1 0-2 .9-2 2v.18C6.5 8.23 8 10.14 8 12c0 1.86-1.5 3.77-2 5.82V18c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2v-.18C16.5 15.77 15 13.86 15 12c0-1.86 1.5-3.77 2-5.82V6c0-1.1-.9-2-2-2z"/></svg></div><div class="kpi-info"><div class="kpi-num" id="dhiKpiDepts">–</div><div class="kpi-label">Departments</div></div></div>
    <div class="kpi-card"><div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M2 4v16h20V4H2zm18 14H4V10h16v8zm0-10H4V6h16v2z"/></svg></div><div class="kpi-info"><div class="kpi-num" id="dhiKpiWards">–</div><div class="kpi-label">Wards / Beds</div></div></div>
    <div class="kpi-card"><div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6C4.9 2 4.01 2.9 4.01 4L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg></div><div class="kpi-info"><div class="kpi-num" id="dhiKpiInvoices">–</div><div class="kpi-label">Invoices</div></div></div>
    <div class="kpi-card"><div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></div><div class="kpi-info"><div class="kpi-num" id="dhiKpiMonthVisits">–</div><div class="kpi-label">Month Visits</div></div></div>
  </div>

  <!-- DHIMS REPORT CATEGORIES GRID -->
  <div class="cat-grid">
    <div class="cat-card" onclick="dhiScrollTo('dhiDiseasePanel')">
      <div class="cat-icon" style="background-color:#DCFCE7;color:#16A34A;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
      <div><h6>OPD Morbidity Returns</h6><small>Monthly outpatient disease classification &amp; surveillance</small></div>
    </div>
    <div class="cat-card" onclick="dhiScrollTo('dhiOpdIpdPanel')">
      <div class="cat-icon" style="background-color:#E0F2FE;color:#0284C7;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v2"/><path d="M3 13h18v4H3z"/><path d="M3 17v3M21 17v3M7 7h10v6H7z"/></svg></div>
      <div><h6>IPD &amp; Inpatient Summary</h6><small>Admissions, inpatient bed usage &amp; visit streams</small></div>
    </div>
    <div class="cat-card" onclick="dhiScrollTo('dhiMonthlyPanel')">
      <div class="cat-icon" style="background-color:#FCE7F3;color:#DB2777;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3z"/><path d="M8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3z"/><path d="M8 14c-2.33 0-7 1.17-7 3.5V19h6v-2.5c0-.85.33-1.65.87-2.33-.57-.11-1.18-.17-1.87-.17z"/><path d="M16 14c-.69 0-1.3.06-1.87.17A3.97 3.97 0 0 1 15 16.5V19h6v-1.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>
      <div><h6>Maternal &amp; Child Health (RCH)</h6><small>Registrations, gender split &amp; NHIA insured coverage</small></div>
    </div>
  </div>

  <!-- MONTHLY AGGREGATED DATA -->
  <div class="panel-box" id="dhiMonthlyPanel">
    <div class="panel-header">
      <div class="ph-l"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>Monthly Aggregated Data</div>
      <div style="display:flex;align-items:center;gap:8px;">
        <span class="ovr-badge" id="dhiOverrideBadge">all figures live</span>
        <button type="button" class="btn-action btn-export" style="padding:4px 10px;font-size:10px;" onclick="dhiExportMonthly()">
          <svg viewBox="0 0 24 24" style="width:12px;height:12px;fill:#fff;vertical-align:-2px;margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>CSV
        </button>
      </div>
    </div>
    <div class="panel-body">
      <div class="table-container" id="dhiMonthlyTable"><div class="empty-note">Select a month and click Generate DHIMS Report.</div></div>
    </div>
  </div>

  <!-- DISEASE SURVEILLANCE -->
  <div class="panel-box" id="dhiDiseasePanel">
    <div class="panel-header">
      <div class="ph-l"><svg viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.9 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/></svg>Disease Surveillance</div>
      <button type="button" class="btn-action btn-export" style="padding:4px 10px;font-size:10px;" onclick="dhiExportDiseases()">CSV</button>
    </div>
    <div class="panel-body">
      <div class="table-container" id="dhiDiseaseTable"><div class="empty-note">Select a month and click Generate DHIMS Report.</div></div>
    </div>
  </div>

  <!-- OPD / IPD STATISTICS -->
  <div class="panel-box" id="dhiOpdIpdPanel">
    <div class="panel-header">
      <div class="ph-l"><svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>OPD / IPD Statistics</div>
      <button type="button" class="btn-action btn-export" style="padding:4px 10px;font-size:10px;" onclick="dhiExportOpdIpd()">CSV</button>
    </div>
    <div class="panel-body">
      <div class="table-container" id="dhiOpdIpdTable"><div class="empty-note">Select a month and click Generate DHIMS Report.</div></div>
    </div>
  </div>

  <!-- NHIA / DHIMS2 EXPORT TOOLS -->
  <div class="panel-box" id="dhiNhiaPanel">
    <div class="panel-header">
      <div class="ph-l"><svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-1 14.59l-3.67-3.67 1.41-1.41L11 12.76l4.26-4.26 1.41 1.41L11 15.59z"/></svg>NHIA Claims &amp; DHIMS2 Export Tools</div>
    </div>
    <div class="panel-body">
      <div class="table-container" id="dhiNhiaTable"><div class="empty-note">Select a month and click Generate DHIMS Report.</div></div>
      <div class="ctrl-row mt15">
        <button type="button" class="btn-action btn-export" onclick="dhiExportNhia()"><svg viewBox="0 0 24 24"><path d="M12 3v10.55l-2.94-2.94-1.41 1.41L12 16.41l4.35-4.35-1.41-1.41L12 13.55V3z"/><path d="M19 13v6H5v-6H3v6c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-6h-2z"/></svg>Export NHIA Claims CSV</button>
        <button type="button" class="btn-action btn-export" onclick="dhiExportDiseases()"><svg viewBox="0 0 24 24"><path d="M12 3v10.55l-2.94-2.94-1.41 1.41L12 16.41l4.35-4.35-1.41-1.41L12 13.55V3z"/><path d="M19 13v6H5v-6H3v6c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-6h-2z"/></svg>Export Disease CSV</button>
        <button type="button" class="btn-action btn-chart" style="background-color:#7f8c8d;color:#fff;" onclick="dhiExportAll()"><svg viewBox="0 0 24 24"><path d="M12 3v10.55l-2.94-2.94-1.41 1.41L12 16.41l4.35-4.35-1.41-1.41L12 13.55V3z"/><path d="M19 13v6H5v-6H3v6c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-6h-2z"/></svg>Export All (DHIMS2)</button>
      </div>
    </div>
  </div>

  <!-- REPORTED FIGURE DIALOG -->
  <div class="modal" id="dhi-override-modal">
    <div class="modal-content" style="max-width:520px;">
      <div class="modal-header">
        <h3><svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg><span id="dhi-ovr-title">Indicator</span></h3>
        <button class="modal-close" onclick="closeDhiOverride()">&times;</button>
      </div>
      <div class="modal-body">
        <div class="ovr-computed">
          <span>Live value from the records</span>
          <b id="dhi-ovr-computed">0</b>
        </div>
        <div class="form-group">
          <label for="dhi-ovr-value" id="dhi-ovr-label">Reported value</label>
          <input type="number" id="dhi-ovr-value" step="0.01" min="0">
        </div>
        <div class="form-group">
          <label for="dhi-ovr-reason">Reason for the reported figure</label>
          <textarea id="dhi-ovr-reason" rows="2" maxlength="255" placeholder="e.g. Figure confirmed with the district health information officer"></textarea>
        </div>
        <p class="ovr-hint" id="dhi-ovr-hint"></p>
        <p class="ovr-note">The reported figure is stored with your name and the reason in the audit trail. The live value is always kept alongside it, and the original records are never changed.</p>
      </div>
      <div class="form-actions">
        <button type="button" class="btn btn-secondary" id="dhi-ovr-clear">Clear Figure</button>
        <button type="button" class="btn btn-primary" id="dhi-ovr-save">Save Figure</button>
      </div>
    </div>
  </div>

  <div class="misdhims-divider"><span>Standard Reports</span></div>

  <!-- STANDARD REPORT GENERATOR -->
  <div class="card">
    <div class="card-header">
        <h2>Reports</h2>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label for="report-type">Report Type</label>
                <select id="report-type">
                    <option value="">Select Report Type</option>
                    <option value="patient_registrations">Patient Registrations</option>
                    <option value="visits_summary">Visits Summary</option>
                    <option value="consultations_summary">Consultations Summary</option>
                    <option value="department_stats">Department Statistics</option>
                    <option value="revenue_summary">Revenue Summary</option>
                    <option value="bed_occupancy">Bed Occupancy</option>
                    <option value="lab_summary">Laboratory Summary</option>
                    <option value="audit_log">Audit Log</option>
                </select>
            </div>
            <div class="form-group">
                <label for="report-date-from">From Date</label>
                <input type="date" id="report-date-from">
            </div>
            <div class="form-group">
                <label for="report-date-to">To Date</label>
                <input type="date" id="report-date-to">
            </div>
        </div>
        
        <div class="form-actions">
            <button class="btn btn-primary" id="generate-report-btn">Generate Report</button>
            <button class="btn btn-secondary" id="export-report-btn">Export CSV</button>
        </div>
    </div>
</div>

<div class="card" id="report-results-card" style="display: none;">
    <div class="card-header">
        <h2 id="report-title">Report Results</h2>
    </div>
    <div class="card-body">
        <div id="report-summary"></div>
        <div class="table-container" id="report-table-container">
            <table id="report-table">
                <!-- Report data will be loaded here -->
            </table>
        </div>
    </div>
</div>

</div>

<script>
async function initReports() {
    setupEventListeners();
    
    // Set default date range (current month)
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('report-date-from').value = firstDay.toISOString().split('T')[0];
    document.getElementById('report-date-to').value = today.toISOString().split('T')[0];

    dhiInit();
}

/* ============ DHIMS REPORT CONSOLE LOGIC ============ */
var dhiData = null;      // cached dhims.php monthly payload
var dhiOverview = null;  // cached system overview counts

function dhiInit(){
    var now = new Date();
    var m = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0');
    document.getElementById('dhiMonth').value = m;

    // Reported-figure dialog (the monthly indicator Edit control)
    document.getElementById('dhi-ovr-save').addEventListener('click', dhiSaveIndicator);
    document.getElementById('dhi-ovr-clear').addEventListener('click', function(){
        var key = document.getElementById('dhi-override-modal').dataset.key;
        if(key) dhiClearIndicator(key);
    });

    dhiLoadDepartments();
}

function dhiLoadDepartments(){
    return fetch('/hms/backend/api/users.php?action=departments').then(function(r){ return r.json(); }).then(function(data){
        var sel = document.getElementById('dhiDept');
        if(data && data.success && data.departments){
            data.departments.forEach(function(d){
                var o = document.createElement('option');
                o.value = d.id;
                o.textContent = d.name;
                sel.appendChild(o);
            });
        }
        return dhiLoad();
    }).catch(function(){ return dhiLoad(); });
}

function dhiMonthName(ym){
    var p = ym.split('-');
    var d = new Date(parseInt(p[0],10), parseInt(p[1],10)-1, 1);
    return d.toLocaleString('en-US',{month:'long', year:'numeric'});
}

function dhiScrollTo(id){
    var el = document.getElementById(id);
    if(el) el.scrollIntoView({behavior:'smooth', block:'start'});
}

function dhiLoad(){
    var m = document.getElementById('dhiMonth').value || '';
    var dept = document.getElementById('dhiDept').value || '';
    if(!m){ showAlert('Please select a reporting period','error'); return; }
    document.getElementById('dhiMonthBadge').textContent = dhiMonthName(m);
    var note = document.getElementById('dhiStatusNote');
    note.style.display='block';
    note.textContent='Loading DHIMS data for '+dhiMonthName(m)+' ...';
    var url = '/hms/backend/api/dhims.php?action=monthly&month='+encodeURIComponent(m);
    if(dept) url += '&department_id='+encodeURIComponent(dept);
    Promise.all([
        fetch(url).then(function(r){ return r.json(); }),
        fetch('/hms/backend/api/system.php?action=overview').then(function(r){ return r.json(); })
    ]).then(function(res){
        var data = res[0];
        dhiOverview = res[1];
        if(!data || !data.success){ note.textContent = (data && data.error) ? data.error : 'Failed to load DHIMS data'; return; }
        dhiData = data;
        note.style.display='none';
        dhiRenderKpis();
        dhiRenderMonthly();
        dhiRenderDiseases();
        dhiRenderOpdIpd();
        dhiRenderNhia();
        if(window.showAlert) showAlert('DHIMS report loaded for '+data.month_label,'success');
    }).catch(function(err){
        note.textContent = 'Network error: '+err.message;
        if(window.showAlert) showAlert('Failed to load DHIMS report','error');
    });
}

/* ---------- KPI CARDS (from system overview) ---------- */
function dhiRenderKpis(){
    var c = (dhiOverview && dhiOverview.counts) ? dhiOverview.counts : {};
    var s = dhiData ? dhiData.summary : {};
    var map = {
        dhiKpiPatients: c.patients, dhiKpiVisits: c.visits, dhiKpiToday: c.today_visits,
        dhiKpiCons: c.consultations, dhiKpiDepts: c.departments,
        dhiKpiWards: (c.wards!=null ? c.wards : '–') + ' / ' + (c.beds!=null ? c.beds : '–'),
        dhiKpiInvoices: c.invoices, dhiKpiMonthVisits: s.visits
    };
    Object.keys(map).forEach(function(id){
        var v = map[id];
        var el = document.getElementById(id);
        if(el) el.textContent = (v===null||v===undefined)? '–' : v;
    });
}

/* ---------- MONTHLY AGGREGATED DATA ----------
   Each row is described once (label + summary key + formatting) and rendered
   from that list, so the table, the Edit control and the CSV export can never
   drift apart. `dhiOverrides` holds the manually reported figures for the
   month, keyed by the same summary key. */
var DHI_ROWS = [
    {key:'registrations',           label:'Patient Registrations',                  indent:0, kind:'count'},
    {key:'registrations_male',      label:'Male Registrations',                     indent:1, kind:'count'},
    {key:'registrations_female',    label:'Female Registrations',                   indent:1, kind:'count'},
    {key:'registrations_insured',   label:'Insured (NHIA / Sponsor) Registrations', indent:1, kind:'count'},
    {key:'visits',                  label:'Total Patient Visits',                   indent:0, kind:'count'},
    {key:'visits_opd',              label:'OPD Visits',                             indent:1, kind:'count'},
    {key:'visits_ipd',              label:'IPD Visits',                             indent:1, kind:'count'},
    {key:'visits_emergency',        label:'Emergency Visits',                       indent:1, kind:'count'},
    {key:'consultations',           label:'Consultations (Total)',                  indent:0, kind:'count'},
    {key:'consultations_completed', label:'Completed Consultations',               indent:1, kind:'count'},
    {key:'invoices',                label:'Invoices Issued',                        indent:0, kind:'count'},
    {key:'revenue',                 label:'Gross Revenue',                          indent:0, kind:'money'},
    {key:'revenue_paid',            label:'Paid Revenue',                           indent:1, kind:'money'},
    {key:'revenue_outstanding',     label:'Outstanding Revenue',                    indent:1, kind:'money', total:true}
];
var dhiOverrides = {};

function dhiFmt(v, kind){
    if(kind === 'money'){
        return 'GH&#8373; ' + Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
    }
    return String(Number(v||0).toLocaleString());
}

function dhiRenderMonthly(){
    var s = dhiData ? dhiData.summary : null;
    var wrap = document.getElementById('dhiMonthlyTable');
    if(!s){ wrap.innerHTML='<div class="empty-note">No data for this month.</div>'; return; }

    dhiOverrides = (dhiData && dhiData.overrides) ? dhiData.overrides : {};

    var rows = DHI_ROWS.map(function(def){
        var o = dhiOverrides[def.key];
        var label = (def.indent ? '<span style="color:#64748B;">— </span>' : '') + def.label;

        var valueCell = dhiFmt(o ? o.value : s[def.key], def.kind);
        if(o){
            // A reported figure always says so, and keeps the live count
            // alongside it so the difference is never hidden.
            valueCell = '<strong>'+valueCell+'</strong>'
                + '<div class="ovr-tag" title="'+dhiEsc(o.reason||'Manual correction')+'">reported'
                + '<div class="ovr-sub">computed: '+dhiFmt(s[def.key], def.kind)+'</div></div>';
        }

        var action = '<button class="dhi-edit-btn'+(o?' is-ovr':'')+'"'
            + ' onclick="dhiEditIndicator(\''+dhiEsc(def.key)+'\')"'
            + ' title="'+(o ? 'Edit the reported figure' : 'Report a figure for this indicator')+'">'
            + '<svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>'
            + ' Edit</button>';

        return '<tr'+(def.total?' class="total-row"':'')+'>'
            + '<td>'+label+'</td>'
            + '<td class="num">'+valueCell+'</td>'
            + '<td class="ctr">'+action+'</td>'
            + '</tr>';
    }).join('');

    wrap.innerHTML = '<table class="data-table"><thead><tr>'
        + '<th>Indicator</th><th class="num" style="width:180px;">Value</th><th class="ctr" style="width:110px;">Action</th>'
        + '</tr></thead><tbody>'+rows+'</tbody></table>';

    var count = Object.keys(dhiOverrides).length;
    var badge = document.getElementById('dhiOverrideBadge');
    if(badge){
        badge.textContent = count ? count + ' reported figure' + (count===1?'':'s') : 'all figures live';
        badge.className = 'ovr-badge' + (count ? ' has-ovr' : '');
    }
}

/* ---------- EDIT A REPORTED FIGURE ---------- */
function dhiEditIndicator(key){
    if(!dhiData){ showAlert('Generate the DHIMS report first','error'); return; }
    var def = null;
    for(var i=0;i<DHI_ROWS.length;i++){ if(DHI_ROWS[i].key === key){ def = DHI_ROWS[i]; break; } }
    if(!def) return;

    var s = dhiData.summary;
    var existing = dhiOverrides[key];
    var computed = s[key];

    var modal = document.getElementById('dhi-override-modal');
    document.getElementById('dhi-ovr-title').textContent = def.label;
    document.getElementById('dhi-ovr-computed').textContent = dhiFmt(computed, def.kind).replace(/&#8373;/g, 'GH¢');
    document.getElementById('dhi-ovr-label').textContent = 'Reported ' + def.label + (def.kind==='money' ? ' (GH¢)' : '');
    document.getElementById('dhi-ovr-value').value = existing ? existing.value : computed;
    document.getElementById('dhi-ovr-reason').value = existing ? (existing.reason||'') : '';
    document.getElementById('dhi-ovr-hint').textContent = existing
        ? 'Currently reported: ' + dhiFmt(existing.value, def.kind).replace(/&#8373;/g, 'GH¢')
          + ' — saved by ' + (existing.updated_by||'system') + '.'
        : 'This is the live value computed from the records.';

    var clearBtn = document.getElementById('dhi-ovr-clear');
    clearBtn.style.display = existing ? 'inline-flex' : 'none';

    // Remember which indicator the dialog is editing, for the save handler.
    modal.dataset.key = key;
    modal.classList.add('show');
    document.getElementById('dhi-ovr-value').focus();
}

function closeDhiOverride(){
    document.getElementById('dhi-override-modal').classList.remove('show');
}

async function dhiSaveIndicator(){
    if(!dhiData) return;
    var key = document.getElementById('dhi-override-modal').dataset.key;
    if(!key) return;
    var value = document.getElementById('dhi-ovr-value').value.trim();
    var reason = document.getElementById('dhi-ovr-reason').value.trim();

    if(value === ''){ showAlert('Enter the figure to report','error'); return; }
    if(isNaN(Number(value))){ showAlert('The figure must be a number','error'); return; }
    if(!reason){ showAlert('Give a reason for the reported figure — it is stored in the audit trail','error'); return; }

    var btn = document.getElementById('dhi-ovr-save');
    btn.disabled = true;
    try{
        var res = await fetch('/hms/backend/api/dhims.php?action=set_override', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ indicator_key:key, month:dhiData.month, value:Number(value), reason:reason })
        });
        var data = await res.json();
        if(!data.success) throw new Error(data.error || 'Failed to save');

        closeDhiOverride();
        showAlert('Reported figure saved for ' + dhiData.month_label,'success');
        await dhiReload();
    }catch(e){
        showAlert(e.message,'error');
    }finally{
        btn.disabled = false;
    }
}

async function dhiClearIndicator(key){
    if(!dhiData) return;
    try{
        var res = await fetch('/hms/backend/api/dhims.php?action=clear_override', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ indicator_key:key, month:dhiData.month })
        });
        var data = await res.json();
        if(!data.success) throw new Error(data.error || 'Failed to clear');

        closeDhiOverride();
        showAlert('Reported figure cleared — the live value is shown again','success');
        await dhiReload();
    }catch(e){
        showAlert(e.message,'error');
    }
}

/* Re-run the current month/department selection without touching the inputs. */
async function dhiReload(){
    var m = document.getElementById('dhiMonth').value;
    var dept = document.getElementById('dhiDept').value;
    var url = '/hms/backend/api/dhims.php?action=monthly&month='+encodeURIComponent(m);
    if(dept) url += '&department_id='+encodeURIComponent(dept);
    var data = await fetch(url).then(function(r){ return r.json(); });
    if(!data || !data.success) throw new Error((data && data.error) || 'Failed to reload');
    dhiData = data;
    dhiRenderKpis();
    dhiRenderMonthly();
    dhiRenderDiseases();
    dhiRenderOpdIpd();
    dhiRenderNhia();
}

/* ---------- DISEASE SURVEILLANCE ---------- */
function dhiRenderDiseases(){
    var list = (dhiData && dhiData.diagnoses) ? dhiData.diagnoses : [];
    var wrap = document.getElementById('dhiDiseaseTable');
    if(!list.length){ wrap.innerHTML='<div class="empty-note">No diagnoses recorded in the selected month.</div>'; return; }
    var rows = list.map(function(d){
        return '<tr><td>'+dhiEsc(d.diagnosis)+'</td><td class="num">'+d.counts+'</td></tr>';
    }).join('');
    wrap.innerHTML = '<table class="data-table"><thead><tr><th style="width:60%">Diagnosis</th><th class="num">Cases</th></tr></thead><tbody>'+rows+'</tbody></table>';
}

/* ---------- OPD / IPD STATISTICS ---------- */
function dhiRenderOpdIpd(){
    var s = dhiData ? dhiData.summary : null;
    var vbs = (dhiData && dhiData.visits_by_status) ? dhiData.visits_by_status : [];
    var wrap = document.getElementById('dhiOpdIpdTable');
    if(!s){ wrap.innerHTML='<div class="empty-note">No data for this month.</div>'; return; }
    var rows = '';
    rows += '<tr><td><span class="pill pill-opd">OPD</span> Outpatient Department</td><td class="num">'+s.visits_opd+'</td></tr>';
    rows += '<tr><td><span class="pill pill-ipd">IPD</span> Inpatient Department</td><td class="num">'+s.visits_ipd+'</td></tr>';
    rows += '<tr><td><span class="pill pill-emergency">EMG</span> Emergency / Urgent Care</td><td class="num">'+s.visits_emergency+'</td></tr>';
    rows += '<tr class="total-row"><td>Total Visits</td><td class="num">'+s.visits+'</td></tr>';
    vbs.forEach(function(v){
        rows += '<tr><td>Status: '+dhiEsc(v.status)+'</td><td class="num">'+v.total+'</td></tr>';
    });
    wrap.innerHTML = '<table class="data-table"><thead><tr><th>Visit Stream</th><th class="num">Count</th></tr></thead><tbody>'+rows+'</tbody></table>';
}

/* ---------- NHIA / DHIMS2 ---------- */
function dhiRenderNhia(){
    var list = (dhiData && dhiData.sponsor_claims) ? dhiData.sponsor_claims : [];
    var wrap = document.getElementById('dhiNhiaTable');
    if(!list.length){ wrap.innerHTML='<div class="empty-note">No NHIA / sponsor claims in the selected month.</div>'; return; }
    var rows = list.map(function(c){
        return '<tr><td><span class="pill pill-nhia">NHIA</span> '+dhiEsc(c.sponsor)+'</td><td class="num">'+c.invoices+'</td><td class="num">'+Number(c.amount).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})+'</td></tr>';
    }).join('');
    wrap.innerHTML = '<table class="data-table"><thead><tr><th>Sponsor / Payer</th><th class="num">Claims</th><th class="num">Amount (GH&#8373;)</th></tr></thead><tbody>'+rows+'</tbody></table>';
}

/* ---------- CSV / XML EXPORTS (real data only) ---------- */
function dhiDownload(name, header, rows){
    var lines = [header.join(',')];
    rows.forEach(function(r){
        lines.push(r.map(function(v){
            var str = (v===null||v===undefined)? '' : String(v);
            return '"'+str.replace(/"/g,'""')+'"';
        }).join(','));
    });
    var blob = new Blob([lines.join('\r\n')], {type:'text/csv;charset=utf-8;'});
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = name + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(a.href);
    if(window.showAlert) showAlert('Exported '+name+'.csv','success');
}

function dhiFormat() {
    return document.getElementById('dhiFormat').value || 'csv';
}

function dhiExportAll(){
    if(!dhiData){ showAlert('Generate a DHIMS report first','error'); return; }
    dhiExportMonthly();
    dhiExportDiseases();
    dhiExportOpdIpd();
    dhiExportNhia();
}

function dhiExportMonthly(){
    if(!dhiData){ showAlert('Generate a DHIMS report first','error'); return; }
    var s = dhiData.summary;
    var ovr = (dhiData.overrides) ? dhiData.overrides : {};
    dhiOverrides = ovr;

    // Built from DHI_ROWS so the export always matches the table. Each row
    // carries the live value and the source, so a reader of the CSV can tell
    // a manual correction from a computed count.
    var rows = [['Reporting Month', dhiData.month_label, '', '']];
    DHI_ROWS.forEach(function(def){
        var o = ovr[def.key];
        rows.push([
            (def.indent ? '  ' : '') + def.label,
            dhiCsvNumber(o ? o.value : s[def.key], def.kind),
            dhiCsvNumber(s[def.key], def.kind),
            o ? 'reported manually (' + (o.updated_by || 'unknown') + ')' : 'computed'
        ]);
    });

    dhiDownload('dhims_monthly_'+dhiData.month,
        ['Indicator','Value','Computed From Records','Source'], rows);
}

/* Plain numeric text for CSV: money to 2dp, counts as whole numbers. */
function dhiCsvNumber(v, kind){
    var n = Number(v||0);
    return kind === 'money' ? n.toFixed(2) : String(n);
}

function dhiExportDiseases(){
    if(!dhiData){ showAlert('Generate a DHIMS report first','error'); return; }
    var rows = dhiData.diagnoses.map(function(d){ return [d.diagnosis, d.counts]; });
    rows.unshift(['Total Cases', rows.reduce(function(a,r){return a+Number(r[1]||0);},0)]);
    dhiDownload('dhims_diseases_'+dhiData.month, ['Diagnosis','Cases'], rows);
}

function dhiExportOpdIpd(){
    if(!dhiData){ showAlert('Generate a DHIMS report first','error'); return; }
    var s = dhiData.summary;
    var rows = [['OPD', s.visits_opd], ['IPD', s.visits_ipd], ['Emergency', s.visits_emergency], ['Total', s.visits]];
    dhiData.visits_by_status.forEach(function(v){ rows.push(['Status: '+v.status, v.total]); });
    dhiDownload('dhims_opd_ipd_'+dhiData.month, ['Visit Stream','Count'], rows);
}

function dhiExportNhia(){
    if(!dhiData){ showAlert('Generate a DHIMS report first','error'); return; }
    var rows = dhiData.sponsor_claims.map(function(c){ return [c.sponsor, c.invoices, c.amount]; });
    dhiDownload('dhims_nhia_claims_'+dhiData.month, ['Sponsor / Payer','Claims','Amount'], rows);
}

function dhiEsc(v){
    if(v === null || v === undefined) return '';
    return String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ============ STANDARD REPORT GENERATOR (existing behavior) ============ */
function setupEventListeners() {
    document.getElementById('generate-report-btn').addEventListener('click', generateReport);
    document.getElementById('export-report-btn').addEventListener('click', exportReport);
}

async function generateReport() {
    const reportType = document.getElementById('report-type').value;
    const dateFrom = document.getElementById('report-date-from').value;
    const dateTo = document.getElementById('report-date-to').value;
    
    if (!reportType) {
        showAlert('Please select a report type', 'error');
        return;
    }
    
    if (!dateFrom || !dateTo) {
        showAlert('Please select date range', 'error');
        return;
    }
    
    try {
        const response = await fetch(`/hms/backend/api/reports.php?type=${reportType}&date_from=${dateFrom}&date_to=${dateTo}`);
        const data = await response.json();
        
        if (data.success) {
            displayReport(data);
        } else {
            showAlert(data.error || 'Failed to generate report', 'error');
        }
    } catch (error) {
        console.error('Report generation error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function displayReport(data) {
    const reportCard = document.getElementById('report-results-card');
    const reportTitle = document.getElementById('report-title');
    const reportSummary = document.getElementById('report-summary');
    const reportTable = document.getElementById('report-table');
    
    reportCard.style.display = 'block';
    reportTitle.textContent = data.report_title || 'Report Results';
    
    // Display summary if available
    if (data.summary) {
        reportSummary.innerHTML = '<div class="stats-grid">' + 
            Object.entries(data.summary).map(([key, value]) => `
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3>${value}</h3>
                        <p>${formatKey(key)}</p>
                    </div>
                </div>
            `).join('') + 
        '</div>';
    } else {
        reportSummary.innerHTML = '';
    }
    
    // Display table data
    if (data.data && data.data.length > 0) {
        const headers = Object.keys(data.data[0]);
        
        reportTable.innerHTML = `
            <thead>
                <tr>
                    ${headers.map(header => `<th>${formatKey(header)}</th>`).join('')}
                </tr>
            </thead>
            <tbody>
                ${data.data.map(row => `
                    <tr>
                        ${headers.map(header => `<td>${row[header] || '-'}</td>`).join('')}
                    </tr>
                `).join('')}
            </tbody>
        `;
    } else {
        reportTable.innerHTML = '<tbody><tr><td colspan="100%" style="text-align: center;">No data available for this report</td></tr></tbody>';
    }
}

function formatKey(key) {
    return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function exportReport() {
    const reportTable = document.getElementById('report-table');
    const reportTitle = document.getElementById('report-title').textContent;
    
    if (!reportTable || reportTable.rows.length === 0) {
        showAlert('No report data to export', 'error');
        return;
    }
    
    let csv = [];
    
    // Add headers
    const headers = [];
    for (let i = 0; i < reportTable.rows[0].cells.length; i++) {
        headers.push(reportTable.rows[0].cells[i].textContent);
    }
    csv.push(headers.join(','));
    
    // Add data rows
    for (let i = 1; i < reportTable.rows.length; i++) {
        const row = [];
        for (let j = 0; j < reportTable.rows[i].cells.length; j++) {
            row.push(reportTable.rows[i].cells[j].textContent);
        }
        csv.push(row.join(','));
    }
    
    // Create download link
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = `${reportTitle.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.csv`;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>