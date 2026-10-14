<?php
// IPD Management & Admission History — consolidated in-patient workspace.
//
// Ported from the pasted Tailwind/Alpine "IPD MANAGEMENT & ADMISSION HISTORY
// REPORTS" mockup into the project's plain CSS/JS fragment style, wired to the
// live APIs instead of the mockup's demo dataset:
//   * Admission History  -> admissions.php                    (register + filters)
//   * Ward Management     -> admissions.php?action=wards        (real bed counts)
//   * Discharge Summaries -> admissions.php?status=DISCHARGED    (outcome / final dx)
//   * Billing Ledger      -> invoices.php?action=patient_billing
// The shell (dashboard.php) already authenticated the session and supplies the
// .modal / .show modal styles plus the page title bar, so this file is only the
// workspace body.
require_once __DIR__ . '/../../backend/config/config.php';
$__ipdUser = getCurrentUserName() ?: 'Staff';
?>
<style>
/* ============ IPD MANAGEMENT & ADMISSION HISTORY (scoped under #ipd-workspace) ============
   Plain CSS — the project ships no Tailwind/Alpine. Colours follow the pasted
   mockup (#1b659d primary on a soft #f0f4f8 canvas). */
#ipd-workspace,#ipd-workspace *{box-sizing:border-box}
#ipd-workspace{--ipd-blue:#1B659D;--ipd-blue-dark:#155380;--ipd-ink:#0F2D59;--ipd-line:#DCE4EC;--ipd-mute:#64748B;--ipd-bg:#F0F4F8;font-family:inherit;color:#334155;font-size:12px}
#ipd-workspace .ipdw-stack{display:flex;flex-direction:column;gap:14px}

/* ---------- Patient header banner ---------- */
#ipd-workspace .ipdw-banner{background:#fff;border:1px solid var(--ipd-line);border-radius:12px;box-shadow:0 1px 3px rgba(15,45,89,.08);padding:14px 16px;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px}
#ipd-workspace .ipdw-banner-left{display:flex;align-items:center;gap:14px;min-width:0}
#ipd-workspace .ipdw-avatar{width:46px;height:46px;flex:0 0 auto;border-radius:50%;background:var(--ipd-blue);color:#fff;display:flex;align-items:center;justify-content:center}
#ipd-workspace .ipdw-avatar svg{width:22px;height:22px}
#ipd-workspace .ipdw-name{font-size:16px;font-weight:800;text-transform:uppercase;color:var(--ipd-ink);letter-spacing:.3px;margin:0}
#ipd-workspace .ipdw-meta{display:flex;flex-wrap:wrap;gap:6px 10px;align-items:center;margin-top:4px;font-size:11px;color:var(--ipd-mute)}
#ipd-workspace .ipdw-meta b{color:#33526F}
#ipd-workspace .ipdw-banner-right{display:flex;flex-wrap:wrap;align-items:center;gap:10px}

/* Status / bill pills */
#ipd-workspace .ipdw-pill{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:9.5px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;white-space:nowrap}
#ipd-workspace .ipdw-pill.admitted{background:#E8F1FF;color:#155380}
#ipd-workspace .ipdw-pill.discharged{background:#E6F9ED;color:#15803D}
#ipd-workspace .ipdw-pill.due{background:#FDECEA;color:#C0392B}
#ipd-workspace .ipdw-pill.clear{background:#E6F9ED;color:#15803D}
#ipd-workspace .ipdw-pill.muted{background:#F1F5F9;color:#475569}

/* ---------- Buttons ---------- */
#ipd-workspace .ipdw-btn{border:1px solid transparent;border-radius:8px;padding:7px 13px;font-size:11px;font-weight:800;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase;white-space:nowrap}
#ipd-workspace .ipdw-btn.primary{background:var(--ipd-blue);color:#fff}
#ipd-workspace .ipdw-btn.primary:hover{background:var(--ipd-blue-dark)}
#ipd-workspace .ipdw-btn.light{background:#F1F5F9;color:#334155;border-color:var(--ipd-line)}
#ipd-workspace .ipdw-btn.light:hover{background:#E2E8F0}
#ipd-workspace .ipdw-btn.outline{background:#fff;color:var(--ipd-blue);border-color:var(--ipd-blue)}
#ipd-workspace .ipdw-btn.outline:hover{background:#F1F8FD}
#ipd-workspace .ipdw-btn.danger{background:#C0392B;color:#fff}
#ipd-workspace .ipdw-btn.danger:hover{background:#96281B}
#ipd-workspace .ipdw-btn.sm{padding:5px 9px;font-size:10px}

/* ---------- Tabs ---------- */
#ipd-workspace .ipdw-tabs{background:#fff;border:1px solid var(--ipd-line);border-radius:12px;box-shadow:0 1px 3px rgba(15,45,89,.08);padding:5px;display:flex;flex-wrap:wrap;gap:4px}
#ipd-workspace .ipdw-tab{border:none;background:transparent;border-radius:8px;padding:9px 15px;font-size:11px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;color:var(--ipd-mute);cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px}
#ipd-workspace .ipdw-tab:hover{background:#F1F5F9;color:var(--ipd-ink)}
#ipd-workspace .ipdw-tab.active{background:var(--ipd-blue);color:#fff}
#ipd-workspace .ipdw-tab svg{width:15px;height:15px}

/* ---------- Cards / panels ---------- */
#ipd-workspace .ipdw-card{background:#fff;border:1px solid var(--ipd-line);border-radius:12px;box-shadow:0 1px 3px rgba(15,45,89,.08);overflow:hidden}
#ipd-workspace .ipdw-card-head{padding:12px 16px;border-bottom:1px solid #EEF2F7;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px}
#ipd-workspace .ipdw-card-head h3{margin:0;font-size:12.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--ipd-blue);display:flex;align-items:center;gap:8px}
#ipd-workspace .ipdw-card-body{padding:14px 16px}

/* Filter row */
#ipd-workspace .ipdw-filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px}
#ipd-workspace .ipdw-field label{display:block;font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--ipd-mute);margin-bottom:5px}
#ipd-workspace .ipdw-input,#ipd-workspace .ipdw-select{width:100%;padding:8px 10px;border:1px solid #CBD5E1;border-radius:8px;background:#F8FAFC;font-size:12px;font-weight:600;color:#334155;font-family:inherit;outline:none}
#ipd-workspace .ipdw-input:focus,#ipd-workspace .ipdw-select:focus{border-color:var(--ipd-blue);box-shadow:0 0 0 2px rgba(27,101,157,.18);background:#fff}

/* Tables */
#ipd-workspace .ipdw-table-wrap{overflow-x:auto}
#ipd-workspace table.ipdw-table{width:100%;border-collapse:collapse;font-size:11.5px}
#ipd-workspace table.ipdw-table th{background:#E6EEF5;color:#33526F;font-size:9.5px;text-transform:uppercase;letter-spacing:.4px;text-align:left;padding:8px 10px;border-bottom:2px solid #B2C8DE;white-space:nowrap}
#ipd-workspace table.ipdw-table td{padding:8px 10px;border-bottom:1px solid #EEF2F7;vertical-align:top}
#ipd-workspace table.ipdw-table tbody tr:hover{background:#F7FBFF}
#ipd-workspace table.ipdw-table tbody tr.selected{background:#EAF3FB}
#ipd-workspace table.ipdw-table .ctr{text-align:center}
#ipd-workspace table.ipdw-table .num{text-align:right;white-space:nowrap}
#ipd-workspace table.ipdw-table .empty{text-align:center;padding:20px;color:#8A94A6}
#ipd-workspace .ipdw-code{font-family:Consolas,'Courier New',monospace;font-weight:700;color:var(--ipd-ink);font-size:11px}
#ipd-workspace .ipdw-sub{display:block;color:#94A3B8;font-size:10px;margin-top:2px}
#ipd-workspace .ipdw-actions{display:flex;gap:5px;flex-wrap:wrap}

/* Ward grid */
#ipd-workspace .ipdw-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:14px}
#ipd-workspace .ipdw-stat{background:#F8FAFC;border:1px solid var(--ipd-line);border-left:4px solid var(--ipd-blue);border-radius:8px;padding:10px 12px}
#ipd-workspace .ipdw-stat .lbl{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--ipd-mute)}
#ipd-workspace .ipdw-stat .val{font-size:21px;font-weight:800;color:var(--ipd-ink);line-height:1.2;margin-top:3px}
#ipd-workspace .ipdw-stat.occ{border-left-color:#C0392B}
#ipd-workspace .ipdw-stat.free{border-left-color:#15803D}
#ipd-workspace .ipdw-ward-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px}
#ipd-workspace .ipdw-ward{border:1px solid var(--ipd-line);border-radius:10px;background:#fff;padding:12px 13px}
#ipd-workspace .ipdw-ward .nm{font-weight:800;color:var(--ipd-ink);font-size:13px;line-height:1.25}
#ipd-workspace .ipdw-ward .cd{font-size:10.5px;color:var(--ipd-mute);margin-top:2px}
#ipd-workspace .ipdw-ward .tags{display:flex;gap:5px;flex-wrap:wrap;margin-top:7px}
#ipd-workspace .ipdw-ward .tag{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;background:#E6EEF5;color:#33526F;border-radius:4px;padding:3px 6px}
#ipd-workspace .ipdw-ward .bar{height:8px;border-radius:4px;background:#E2E8F0;overflow:hidden;margin-top:9px}
#ipd-workspace .ipdw-ward .bar i{display:block;height:100%;background:var(--ipd-blue)}
#ipd-workspace .ipdw-ward .bar i.full{background:#C0392B}
#ipd-workspace .ipdw-ward .figs{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:9px;text-align:center;font-size:10px;color:var(--ipd-mute)}
#ipd-workspace .ipdw-ward .figs b{display:block;font-size:15px;color:#1E293B;margin-top:1px}
#ipd-workspace .ipdw-ward .figs b.warn{color:#C0392B}

/* Billing summary */
#ipd-workspace .ipdw-bill-sec{background:#F1F5F9;color:#475569;font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;padding:6px 10px;border-bottom:1px solid #E1E8F0}
#ipd-workspace .ipdw-bill-foot{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;background:#F8FAFC;border-top:1px solid #E1E8F0;padding:10px 12px;font-size:11.5px;color:var(--ipd-mute)}
#ipd-workspace .ipdw-bill-foot .due{color:#C0392B;font-weight:800;font-size:13px}
#ipd-workspace .ipdw-bill-foot .clear{color:#15803D;font-weight:800}
#ipd-workspace .ipdw-stat-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.3px}
#ipd-workspace .ipdw-stat-badge.paid{background:#DCFCE7;color:#166534}
#ipd-workspace .ipdw-stat-badge.pending{background:#FEF3C7;color:#92400E}
#ipd-workspace .ipdw-stat-badge.partial{background:#FEF3C7;color:#92400E}
#ipd-workspace .ipdw-stat-badge.draft{background:#E2E8F0;color:#475569}
#ipd-workspace .ipdw-stat-badge.cancelled{background:#FEE2E2;color:#991B1B}

/* Detail blocks shared by the two modals (kept inside #ipd-workspace scope via .ipdw-) */
#ipd-workspace .ipdw-kv{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px}
#ipd-workspace .ipdw-kv .k{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--ipd-mute)}
#ipd-workspace .ipdw-kv .v{font-size:12px;color:#1E293B;margin-top:2px;word-break:break-word}
#ipd-workspace .ipdw-block{background:#fff;border:1px solid #E2E8F0;border-radius:8px;padding:10px 12px}
#ipd-workspace .ipdw-block strong{display:block;color:var(--ipd-blue);font-size:11px;margin-bottom:4px}
#ipd-workspace .ipdw-block p{margin:0;color:#334155;line-height:1.45;white-space:pre-wrap;font-size:11.5px}
#ipd-workspace .ipdw-mini-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px}
#ipd-workspace .ipdw-mini-tab{border:1px solid var(--ipd-line);background:#fff;border-radius:999px;padding:5px 12px;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;color:var(--ipd-mute);cursor:pointer;font-family:inherit}
#ipd-workspace .ipdw-mini-tab.active{background:var(--ipd-ink);border-color:var(--ipd-ink);color:#fff}

/* IPD module quick-access tiles (preserved from the previous dashboard page —
   this grid is the only in-app route to wards, admissions, the doctor/nursing
   stations, in-patient billing and reports) */
#ipd-workspace .ipdw-modgrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:12px}
#ipd-workspace .ipdw-tile{display:flex;align-items:center;gap:12px;border:1px solid var(--ipd-line);border-radius:10px;background:#fff;padding:12px 14px;cursor:pointer;transition:transform .15s ease,box-shadow .15s ease;text-decoration:none}
#ipd-workspace .ipdw-tile:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(15,45,89,.10)}
#ipd-workspace .ipdw-tile .icon{width:44px;height:44px;flex:0 0 auto;border-radius:9px;display:flex;align-items:center;justify-content:center}
#ipd-workspace .ipdw-tile .icon svg{width:22px;height:22px}
#ipd-workspace .ipdw-tile h6{margin:0;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--ipd-ink);line-height:1.25}
#ipd-workspace .ipdw-tile small{color:var(--ipd-mute);font-size:11px;line-height:1.3}
#ipd-workspace .ipdw-badge{display:inline-block;vertical-align:middle;margin-left:6px;padding:1px 7px;border-radius:10px;background:var(--ipd-blue);color:#fff;font-size:9px;font-weight:800;letter-spacing:.3px;text-transform:uppercase;white-space:nowrap}
#ipd-workspace .ipdw-c-primary{background:#e8f1ff;color:#0d6efd}
#ipd-workspace .ipdw-c-info{background:#e0f8ff;color:#0891b2}
#ipd-workspace .ipdw-c-success{background:#e6f9ed;color:#16a34a}
#ipd-workspace .ipdw-c-secondary{background:#f1f3f5;color:#475569}
#ipd-workspace .ipdw-c-warning{background:#fef7e0;color:#d97706}
#ipd-workspace .ipdw-c-danger{background:#fde8e8;color:#dc2626}

@media (max-width:640px){
    #ipd-workspace .ipdw-banner{align-items:flex-start}
    #ipd-workspace .ipdw-banner-right{width:100%}
}
</style>
<div id="ipd-workspace">
  <div class="ipdw-stack">

    <!-- ================= IPD MODULE QUICK ACCESS (navigation hub) ================= -->
    <div class="ipdw-card">
      <div class="ipdw-card-head">
        <h3>IPD Management Dashboard</h3>
        <span style="font-size:11px;color:#64748B;">Quick access to every in-patient module</span>
      </div>
      <div class="ipdw-card-body">
        <div class="ipdw-modgrid" id="ipdw-mod-grid">
          <div style="grid-column:1/-1;text-align:center;color:#94A3B8;padding:18px 0;">Loading IPD modules...</div>
        </div>
      </div>
    </div>

    <!-- ================= PATIENT HEADER BANNER (outstanding-bill badge) ================= -->
    <div class="ipdw-banner" id="ipdw-banner">
      <div class="ipdw-banner-left">
        <div class="ipdw-avatar" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </div>
        <div style="min-width:0;">
          <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
            <h3 class="ipdw-name" id="ipdw-banner-name">NO PATIENT SELECTED</h3>
            <span class="ipdw-pill muted" id="ipdw-banner-status">—</span>
          </div>
          <div class="ipdw-meta" id="ipdw-banner-meta">
            <span>Select an admission below to load the patient workspace.</span>
          </div>
        </div>
      </div>
      <div class="ipdw-banner-right">
        <span class="ipdw-pill muted" id="ipdw-banner-bill">BILL: —</span>
        <button class="ipdw-btn outline" id="ipdw-banner-details" type="button">View Details</button>
        <button class="ipdw-btn primary" id="ipdw-banner-card" type="button">Discharge Card</button>
      </div>
    </div>

    <!-- ================= TABS ================= -->
    <div class="ipdw-tabs" role="tablist">
      <button class="ipdw-tab active" data-tab="admission_history" type="button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v4H3z"></path><path d="M5 7v14"></path><path d="M19 7v14"></path><path d="M9 11h6"></path><path d="M9 15h6"></path></svg>
        Admission History
      </button>
      <button class="ipdw-tab" data-tab="ward_management" type="button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4v16"></path><path d="M2 8h18a2 2 0 0 1 2 2v10"></path><path d="M2 17h20"></path><path d="M6 8v9"></path></svg>
        Ward Management
      </button>
      <button class="ipdw-tab" data-tab="discharge_summaries" type="button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="9" y1="15" x2="15" y2="15"></line></svg>
        Discharge Summaries
      </button>
      <button class="ipdw-tab" data-tab="billing_ledger" type="button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
        Billing Ledger
      </button>
    </div>

    <!-- ================= PANEL: ADMISSION HISTORY ================= -->
    <div class="ipdw-card ipdw-panel" data-panel="admission_history">
      <div class="ipdw-card-head">
        <h3>Admission History &amp; Register</h3>
        <div style="display:flex;gap:8px;align-items:center;">
          <button class="ipdw-btn light" id="ipdw-refresh-admissions" type="button">Refresh</button>
        </div>
      </div>
      <div class="ipdw-card-body">
        <div class="ipdw-filters" style="margin-bottom:14px;">
          <div class="ipdw-field" style="grid-column:span 2;min-width:240px;">
            <label for="ipdw-f-q">Search</label>
            <input class="ipdw-input" type="text" id="ipdw-f-q" placeholder="Patient, hospital no. or admission code...">
          </div>
          <div class="ipdw-field">
            <label for="ipdw-f-ward">Ward</label>
            <select class="ipdw-select" id="ipdw-f-ward"><option value="">All Wards</option></select>
          </div>
          <div class="ipdw-field">
            <label for="ipdw-f-status">Status</label>
            <select class="ipdw-select" id="ipdw-f-status">
              <option value="">All Status</option>
              <option value="Admitted">Admitted</option>
              <option value="Discharged">Discharged</option>
            </select>
          </div>
        </div>
        <div class="ipdw-table-wrap">
          <table class="ipdw-table">
            <thead>
              <tr>
                <th>Admission No.</th>
                <th>Patient</th>
                <th>Hospital No.</th>
                <th>Ward / Bed</th>
                <th>Admitted</th>
                <th>Discharged</th>
                <th>Doctor</th>
                <th>Status</th>
                <th>Outstanding</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="ipdw-adm-tbody">
              <tr><td class="empty" colspan="10">Loading admissions...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ================= PANEL: WARD MANAGEMENT ================= -->
    <div class="ipdw-card ipdw-panel" data-panel="ward_management" style="display:none;">
      <div class="ipdw-card-head">
        <h3>Ward Occupancy &amp; Bed Allocation</h3>
        <button class="ipdw-btn light" id="ipdw-refresh-wards" type="button">Refresh</button>
      </div>
      <div class="ipdw-card-body">
        <div class="ipdw-stats" id="ipdw-ward-stats"></div>
        <div class="ipdw-ward-grid" id="ipdw-ward-grid">
          <div class="empty" style="grid-column:1/-1;text-align:center;padding:22px;color:#8A94A6;">Loading ward occupancy...</div>
        </div>
      </div>
    </div>

    <!-- ================= PANEL: DISCHARGE SUMMARIES ================= -->
    <div class="ipdw-card ipdw-panel" data-panel="discharge_summaries" style="display:none;">
      <div class="ipdw-card-head">
        <h3>Discharge Summaries</h3>
        <button class="ipdw-btn light" id="ipdw-refresh-discharges" type="button">Refresh</button>
      </div>
      <div class="ipdw-card-body">
        <div class="ipdw-table-wrap">
          <table class="ipdw-table">
            <thead>
              <tr>
                <th>Admission No.</th>
                <th>Patient</th>
                <th>Ward / Bed</th>
                <th>Discharged</th>
                <th>Outcome</th>
                <th>Final Diagnosis</th>
                <th>Follow-up</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="ipdw-discharge-tbody">
              <tr><td class="empty" colspan="8">Loading discharge summaries...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ================= PANEL: BILLING LEDGER ================= -->
    <div class="ipdw-card ipdw-panel" data-panel="billing_ledger" style="display:none;">
      <div class="ipdw-card-head">
        <h3>In-Patient Billing Ledger</h3>
        <div style="display:flex;gap:8px;align-items:center;">
          <select class="ipdw-select" id="ipdw-bill-patient" style="min-width:220px;"><option value="">Select a patient...</option></select>
          <button class="ipdw-btn light" id="ipdw-refresh-billing" type="button">Refresh</button>
        </div>
      </div>
      <div class="ipdw-card-body" id="ipdw-billing-body">
        <div class="empty" style="text-align:center;padding:22px;color:#8A94A6;">Select a patient above to view their billing ledger.</div>
      </div>
    </div>

  </div>

<!-- ================= MODAL: IN-PATIENT DETAILS ================= -->
<div class="modal" id="ipdw-details-modal">
  <div class="modal-content" style="max-width:820px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-hospital-user"></i> In-Patient Details</h3>
      <button class="modal-close" id="ipdw-details-close">&times;</button>
    </div>
    <div class="modal-body" id="ipdw-details-body"></div>
    <div class="modal-footer">
      <button class="btn btn-secondary" id="ipdw-details-dismiss">Close</button>
    </div>
  </div>
</div>

<!-- ================= MODAL: DISCHARGE CARD (printable) ================= -->
<div class="modal" id="ipdw-card-modal">
  <div class="modal-content" style="max-width:820px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-file-medical"></i> Discharge Card</h3>
      <button class="modal-close" id="ipdw-card-close">&times;</button>
    </div>
    <div class="modal-body" id="ipdw-card-body"></div>
    <div class="modal-footer">
      <button class="btn btn-secondary" id="ipdw-card-dismiss">Close</button>
      <button class="btn btn-primary" id="ipdw-card-print"><i class="fa-solid fa-print"></i> Print / Export</button>
    </div>
  </div>
</div>
</div>

<script>
/* =====================================================================
   IPD Management & Admission History workspace.
   The shell runs this block through (0,eval), so `let` bindings live only
   for this page's functions (closures) and function declarations become
   global — matching the pattern used by the other page fragments.
   ===================================================================== */
let ipdAdmissions = [];
let ipdWards = [];
let ipdWardTotals = null;
let ipdSelected = null;
let ipdBilling = {};          // patient_id -> {invoices, items, outstanding, state}
let ipdBillPatientId = '';
let ipdQTimer = null;

/* IPD module tiles — quick access to the other in-patient pages. */
const IPD_TILES = [
    { label: 'WARDS, ROOMS & BED STATUS', sub: 'Real-time occupancy and bed allocation status', icon: 'bed',       color: 'ipdw-c-primary',   page: 'wards' },
    { label: 'CURRENT PATIENT ACCESS',    sub: 'Access newly admitted or active in-patients',   icon: 'user',      color: 'ipdw-c-info',      page: 'active-inpatients', badge: 'badge-census' },
    { label: 'ADMIT PATIENT',             sub: 'Admit patient directly to a ward and bed',      icon: 'user-plus', color: 'ipdw-c-success',   page: 'admissions', intent: 'admit', badge: 'badge-freebeds' },
    { label: 'DRAFT ADMISSIONS',          sub: 'In-progress admissions pending finalization',   icon: 'file',      color: 'ipdw-c-secondary', page: 'admissions', intent: 'drafts', badge: 'badge-drafts' },
    { label: 'NURSING STATION',           sub: 'Vitals, admissions and ward observations',      icon: 'activity',  color: 'ipdw-c-warning',   page: 'vitals' },
    { label: 'DOCTOR STATION',            sub: 'OPD/IPD doctor notes, clinical entries & discharge summary', icon: 'doctor', color: 'ipdw-c-danger', page: 'doctor-station' },
    { label: 'BILLING MANAGEMENT',        sub: 'In-patient billing, deposits, & clearance',     icon: 'card',      color: 'ipdw-c-success',   page: 'ipd-billing' },
    { label: 'REPORTS',                   sub: 'All IPD records, ward admission history & discharges', icon: 'chart', color: 'ipdw-c-info',   page: 'admissions' }
];

const IPD_ICONS = {
    'bed': '<path d="M2 4v16"></path><path d="M2 8h18a2 2 0 0 1 2 2v10"></path><path d="M2 17h20"></path><path d="M6 8v9"></path>',
    'user': '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
    'user-plus': '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="17" y1="11" x2="23" y2="11"></line>',
    'file': '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line>',
    'activity': '<path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>',
    'doctor': '<circle cx="12" cy="7" r="4"></circle><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"></path>',
    'card': '<rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line>',
    'chart': '<line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line>'
};

function initIpdManagement() {
    bindIpdListeners();
    switchIpdTab('admission_history');
    loadIpdTiles();
    Promise.all([loadIpdAdmissions(), loadIpdWards()]);
}

function loadIpdTiles() {
    const grid = document.getElementById('ipdw-mod-grid');
    if (!grid) return;
    grid.innerHTML = IPD_TILES.map(function (c) {
        return '<div class="ipdw-tile" data-page="' + c.page + '"' + (c.intent ? ' data-intent="' + c.intent + '"' : '') + '>' +
            '<div class="icon ' + c.color + '"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + (IPD_ICONS[c.icon] || '') + '</svg></div>' +
            '<div style="min-width:0;"><h6>' + c.label + (c.badge ? ' <span class="ipdw-badge" id="' + c.badge + '" style="display:none;"></span>' : '') + '</h6>' +
            '<small>' + c.sub + '</small></div>' +
        '</div>';
    }).join('');
    grid.querySelectorAll('.ipdw-tile').forEach(function (card) {
        card.addEventListener('click', function () {
            if (typeof window.loadPage === 'function') {
                window.loadPage(card.getAttribute('data-page'), null, { intent: card.getAttribute('data-intent') || null });
            }
        });
    });
    loadIpdBadges();
}

function setIpdBadge(id, value, suffix) {
    const el = document.getElementById(id);
    if (!el) return;
    if (value === null || value === undefined) { el.style.display = 'none'; return; }
    el.textContent = suffix ? value + ' ' + suffix : String(value);
    el.style.display = 'inline-block';
}

async function loadIpdBadges() {
    setIpdBadge('badge-drafts', null);
    setIpdBadge('badge-census', null);
    try {
        const res = await Promise.all([
            fetch('/hms/backend/api/draft_admissions.php?status=DRAFT').then(function (r) { return r.json(); }).catch(function () { return null; }),
            fetch('/hms/backend/api/admissions.php?status=ADMITTED').then(function (r) { return r.json(); }).catch(function () { return null; })
        ]);
        if (res[0] && res[0].success) setIpdBadge('badge-drafts', (res[0].drafts || []).length, 'pending');
        if (res[1] && res[1].success) setIpdBadge('badge-census', (res[1].admissions || []).length, 'active');
    } catch (err) {
        console.error('IPD tile badge load error:', err);
    }
}

function bindIpdListeners() {
    // Tabs
    document.querySelectorAll('#ipd-workspace .ipdw-tab').forEach(function (b) {
        b.addEventListener('click', function () { switchIpdTab(this.getAttribute('data-tab')); });
    });

    // Admission filters (debounced search)
    const q = document.getElementById('ipdw-f-q');
    if (q) q.addEventListener('input', function () {
        clearTimeout(ipdQTimer);
        ipdQTimer = setTimeout(loadIpdAdmissions, 350);
    });
    ['ipdw-f-ward', 'ipdw-f-status'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', loadIpdAdmissions);
    });

    const refreshAdm = document.getElementById('ipdw-refresh-admissions');
    if (refreshAdm) refreshAdm.addEventListener('click', loadIpdAdmissions);
    const refreshWards = document.getElementById('ipdw-refresh-wards');
    if (refreshWards) refreshWards.addEventListener('click', loadIpdWards);
    const refreshDis = document.getElementById('ipdw-refresh-discharges');
    if (refreshDis) refreshDis.addEventListener('click', loadIpdDischargeSummaries);
    const refreshBill = document.getElementById('ipdw-refresh-billing');
    if (refreshBill) refreshBill.addEventListener('click', function () {
        if (ipdBillPatientId) loadIpdBilling(ipdBillPatientId, true);
    });

    // Banner actions
    const detailsBtn = document.getElementById('ipdw-banner-details');
    if (detailsBtn) detailsBtn.addEventListener('click', function () {
        if (ipdSelected) openIpdDetails(ipdSelected.id);
    });
    const cardBtn = document.getElementById('ipdw-banner-card');
    if (cardBtn) cardBtn.addEventListener('click', function () {
        if (ipdSelected) openIpdDischargeCard(ipdSelected.id);
    });

    // Billing patient picker
    const billSel = document.getElementById('ipdw-bill-patient');
    if (billSel) billSel.addEventListener('change', function () {
        ipdBillPatientId = this.value;
        if (ipdBillPatientId) loadIpdBilling(ipdBillPatientId, true);
        else renderIpdBillingEmpty();
    });

    // Admission table actions (event delegation)
    const tbody = document.getElementById('ipdw-adm-tbody');
    if (tbody) tbody.addEventListener('click', function (e) {
        const details = e.target.closest('[data-details]');
        if (details) { openIpdDetails(parseInt(details.getAttribute('data-details'), 10)); return; }
        const card = e.target.closest('[data-card]');
        if (card) { openIpdDischargeCard(parseInt(card.getAttribute('data-card'), 10)); return; }
        const bill = e.target.closest('[data-bill]');
        if (bill) { switchIpdTab('billing_ledger'); selectIpdBillPatient(bill.getAttribute('data-bill')); return; }
        const row = e.target.closest('tr[data-admission]');
        if (row) selectIpdAdmission(parseInt(row.getAttribute('data-admission'), 10));
    });

    // Discharge table actions
    const disTbody = document.getElementById('ipdw-discharge-tbody');
    if (disTbody) disTbody.addEventListener('click', function (e) {
        const card = e.target.closest('[data-card]');
        if (card) { openIpdDischargeCard(parseInt(card.getAttribute('data-card'), 10)); return; }
        const row = e.target.closest('tr[data-admission]');
        if (row) selectIpdAdmission(parseInt(row.getAttribute('data-admission'), 10));
    });

    // Modals
    const dc = document.getElementById('ipdw-details-close');
    if (dc) dc.addEventListener('click', function () { closeIpdModal('ipdw-details-modal'); });
    const dd = document.getElementById('ipdw-details-dismiss');
    if (dd) dd.addEventListener('click', function () { closeIpdModal('ipdw-details-modal'); });
    const cc = document.getElementById('ipdw-card-close');
    if (cc) cc.addEventListener('click', function () { closeIpdModal('ipdw-card-modal'); });
    const cd = document.getElementById('ipdw-card-dismiss');
    if (cd) cd.addEventListener('click', function () { closeIpdModal('ipdw-card-modal'); });
    const cp = document.getElementById('ipdw-card-print');
    if (cp) cp.addEventListener('click', printIpdCard);
}

function switchIpdTab(tab) {
    document.querySelectorAll('#ipd-workspace .ipdw-tab').forEach(function (b) {
        b.classList.toggle('active', b.getAttribute('data-tab') === tab);
    });
    document.querySelectorAll('#ipd-workspace .ipdw-panel').forEach(function (p) {
        p.style.display = (p.getAttribute('data-panel') === tab) ? '' : 'none';
    });
    if (tab === 'ward_management' && !ipdWards.length) loadIpdWards();
    if (tab === 'discharge_summaries') loadIpdDischargeSummaries();
    if (tab === 'billing_ledger' && ipdBillPatientId) loadIpdBilling(ipdBillPatientId);
}

/* ---------------- Admission History ---------------- */
async function loadIpdAdmissions() {
    const tbody = document.getElementById('ipdw-adm-tbody');
    const params = new URLSearchParams();
    const ward = document.getElementById('ipdw-f-ward');
    const status = document.getElementById('ipdw-f-status');
    const q = document.getElementById('ipdw-f-q');
    if (ward && ward.value) params.set('ward_id', ward.value);
    if (status && status.value) params.set('status', status.value);
    if (q && q.value.trim()) params.set('q', q.value.trim());

    try {
        const res = await fetch('/hms/backend/api/admissions.php?' + params.toString());
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed');
        ipdAdmissions = data.admissions || [];
        renderIpdAdmissions(tbody);
        populateIpdBillPatients();
        if (!ipdSelected && ipdAdmissions.length) {
            // Default the banner to the newest active in-patient, else the newest row.
            const active = ipdAdmissions.find(function (a) { return a.status === 'Admitted'; });
            selectIpdAdmission((active || ipdAdmissions[0]).id, true);
        }
        hydrateIpdOutstanding();
    } catch (err) {
        console.error('IPD admissions load error:', err);
        if (tbody) tbody.innerHTML = '<tr><td class="empty" colspan="10">Failed to load admissions</td></tr>';
    }
}

function renderIpdAdmissions(tbody) {
    if (!tbody) return;
    if (!ipdAdmissions.length) {
        tbody.innerHTML = '<tr><td class="empty" colspan="10">No admissions found</td></tr>';
        return;
    }
    tbody.innerHTML = ipdAdmissions.map(function (a) {
        const admitted = a.status === 'Admitted';
        const bedLabel = a.bed_number ? (a.bed_number + (a.bed_type ? ' (' + a.bed_type + ')' : '')) : '-';
        const dischargeCell = admitted
            ? '<span style="color:#15803D;font-weight:700;">ACTIVE (IN-PATIENT)</span>'
            : (a.discharged_at ? fmtIpdDateTime(a.discharged_at) : '—');
        return '<tr data-admission="' + a.id + '"' + (ipdSelected && ipdSelected.id === a.id ? ' class="selected"' : '') + '>' +
            '<td><span class="ipdw-code">' + escIpd(a.admission_code) + '</span></td>' +
            '<td><strong style="color:#0F2D59;">' + escIpd(a.patient_name || '-') + '</strong>' +
                '<span class="ipdw-sub">' + escIpd(a.gender || '') + (a.age !== null && a.age !== undefined ? ' / ' + escIpd(a.age) + ' Yrs' : '') + '</span></td>' +
            '<td>' + escIpd(a.hospital_number || '-') + '</td>' +
            '<td>' + escIpd((a.ward_name || '-') + (a.bed_number ? ' / ' + bedLabel : '')) + '</td>' +
            '<td>' + fmtIpdDate(a.admission_date) + '</td>' +
            '<td>' + dischargeCell + '</td>' +
            '<td>' + escIpd(a.admitting_doctor || '-') + '</td>' +
            '<td><span class="ipdw-pill ' + (admitted ? 'admitted' : 'discharged') + '">' + escIpd(a.status) + '</span></td>' +
            '<td class="num"><span class="ipdw-due" data-pid="' + a.patient_id + '">…</span></td>' +
            '<td><div class="ipdw-actions">' +
                '<button class="ipdw-btn light sm" type="button" data-details="' + a.id + '">Details</button>' +
                '<button class="ipdw-btn outline sm" type="button" data-card="' + a.id + '">Card</button>' +
                '<button class="ipdw-btn light sm" type="button" data-bill="' + a.patient_id + '">Bill</button>' +
            '</div></td>' +
        '</tr>';
    }).join('');
}

/* Outstanding balance per patient — fetched once per distinct patient and
   cached, then written into every matching row + the banner. */
async function hydrateIpdOutstanding() {
    const pids = [];
    ipdAdmissions.forEach(function (a) {
        if (pids.indexOf(a.patient_id) === -1 && pids.length < 60) pids.push(a.patient_id);
    });
    await Promise.all(pids.map(function (pid) { return loadIpdBilling(pid, false); }));
    document.querySelectorAll('#ipd-workspace .ipdw-due[data-pid]').forEach(function (el) {
        const b = ipdBilling[el.getAttribute('data-pid')];
        el.innerHTML = ipdDueHtml(b);
    });
}

function ipdDueHtml(b) {
    if (!b || b.state === 'loading') return '<span style="color:#94A3B8;">…</span>';
    if (b.state === 'error') return '<span style="color:#94A3B8;">n/a</span>';
    const due = Number(b.outstanding || 0);
    return due > 0
        ? '<span style="color:#C0392B;font-weight:800;">' + fmtIpdMoney(due) + ' Due</span>'
        : '<span style="color:#15803D;font-weight:700;">Cleared</span>';
}

/* ---------------- Ward Management ---------------- */
async function loadIpdWards() {
    try {
        const res = await fetch('/hms/backend/api/admissions.php?action=wards');
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed');
        ipdWards = data.wards || [];
        ipdWardTotals = data.totals || null;
        renderIpdWards();
    } catch (err) {
        console.error('IPD wards load error:', err);
        const grid = document.getElementById('ipdw-ward-grid');
        if (grid) grid.innerHTML = '<div class="empty" style="grid-column:1/-1;text-align:center;padding:22px;color:#8A94A6;">Failed to load ward occupancy</div>';
    }
}

function renderIpdWards() {
    const stats = document.getElementById('ipdw-ward-stats');
    const grid = document.getElementById('ipdw-ward-grid');
    if (!grid) return;

    const totals = ipdWardTotals || { beds_total: 0, beds_occupied: 0, beds_free: 0, patients: 0 };
    setIpdBadge('badge-freebeds', totals.beds_free, 'free');
    if (stats) {
        stats.innerHTML =
            ipdStatHtml('Total Beds', totals.beds_total, '') +
            ipdStatHtml('Occupied', totals.beds_occupied, 'occ') +
            ipdStatHtml('Available', totals.beds_free, 'free') +
            ipdStatHtml('In-Patients', totals.patients, '');
    }

    if (!ipdWards.length) {
        grid.innerHTML = '<div class="empty" style="grid-column:1/-1;text-align:center;padding:22px;color:#8A94A6;">No wards found</div>';
        return;
    }

    grid.innerHTML = ipdWards.map(function (w) {
        const pct = Number(w.occupancy_pct || 0);
        const full = pct >= 100;
        return '<div class="ipdw-ward">' +
            '<div class="nm">' + escIpd(w.ward_name) + '</div>' +
            '<div class="cd">' + escIpd(w.ward_code || '') + (w.floor_level ? ' · Floor ' + escIpd(w.floor_level) : '') + '</div>' +
            '<div class="tags"><span class="tag">' + escIpd(w.ward_type || 'General') + '</span><span class="tag">Capacity ' + escIpd(w.capacity || 0) + '</span></div>' +
            '<div class="bar"><i class="' + (full ? 'full' : '') + '" style="width:' + Math.min(100, pct) + '%"></i></div>' +
            '<div class="figs">' +
                '<div>Occupied<b class="' + (pct >= 80 ? 'warn' : '') + '">' + (w.beds_occupied || 0) + '</b></div>' +
                '<div>Free<b>' + (w.beds_free || 0) + '</b></div>' +
                '<div>Patients<b>' + (w.patients_admitted || 0) + '</b></div>' +
            '</div>' +
        '</div>';
    }).join('');
}

function ipdStatHtml(label, value, cls) {
    return '<div class="ipdw-stat ' + (cls || '') + '"><div class="lbl">' + label + '</div><div class="val">' + (value || 0) + '</div></div>';
}

/* ---------------- Discharge Summaries ---------------- */
async function loadIpdDischargeSummaries() {
    const tbody = document.getElementById('ipdw-discharge-tbody');
    if (!tbody) return;
    try {
        const res = await fetch('/hms/backend/api/admissions.php?status=Discharged');
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed');
        const rows = data.admissions || [];
        if (!rows.length) {
            tbody.innerHTML = '<tr><td class="empty" colspan="8">No discharge summaries recorded yet</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (a) {
            return '<tr data-admission="' + a.id + '">' +
                '<td><span class="ipdw-code">' + escIpd(a.admission_code) + '</span></td>' +
                '<td><strong style="color:#0F2D59;">' + escIpd(a.patient_name || '-') + '</strong><span class="ipdw-sub">' + escIpd(a.hospital_number || '') + '</span></td>' +
                '<td>' + escIpd((a.ward_name || '-') + (a.bed_number ? ' / ' + a.bed_number : '')) + '</td>' +
                '<td>' + fmtIpdDateTime(a.discharged_at) + '</td>' +
                '<td>' + (a.discharge_outcome ? '<span class="ipdw-pill admitted">' + escIpd(a.discharge_outcome) + '</span>' : '—') + '</td>' +
                '<td>' + escIpd(a.final_diagnosis || a.diagnosis || '—') + '</td>' +
                '<td>' + escIpd(a.follow_up_date || '—') + '</td>' +
                '<td><div class="ipdw-actions"><button class="ipdw-btn outline sm" type="button" data-card="' + a.id + '">Discharge Card</button></div></td>' +
            '</tr>';
        }).join('');
    } catch (err) {
        console.error('IPD discharge load error:', err);
        tbody.innerHTML = '<tr><td class="empty" colspan="8">Failed to load discharge summaries</td></tr>';
    }
}

/* ---------------- Billing Ledger ---------------- */
function populateIpdBillPatients() {
    const sel = document.getElementById('ipdw-bill-patient');
    if (!sel) return;
    const seen = {};
    const opts = [];
    ipdAdmissions.forEach(function (a) {
        if (seen[a.patient_id]) return;
        seen[a.patient_id] = true;
        opts.push('<option value="' + a.patient_id + '">' + escIpd(a.hospital_number || '') + ' — ' + escIpd(a.patient_name || '') + '</option>');
    });
    sel.innerHTML = '<option value="">Select a patient...</option>' + opts.join('');
    if (ipdBillPatientId) sel.value = ipdBillPatientId;
}

function selectIpdBillPatient(pid) {
    ipdBillPatientId = String(pid);
    const sel = document.getElementById('ipdw-bill-patient');
    if (sel) sel.value = ipdBillPatientId;
    loadIpdBilling(ipdBillPatientId, true);
}

/* forceRefresh = true bypasses the cache (Refresh button / patient change).
   Otherwise a cached result is reused — but the ledger is always repainted when
   the patient is the one currently shown in the Billing Ledger tab, so switching
   back to that tab never shows a blank panel. */
async function loadIpdBilling(patientId, forceRefresh) {
    patientId = String(patientId);
    if (!patientId) return;

    const isLedgerPatient = (ipdBillPatientId === patientId);
    const cached = ipdBilling[patientId];
    if (isLedgerPatient) renderIpdBilling(patientId);

    if (cached && cached.loaded && !forceRefresh) {
        updateIpdBannerBill();
        return;
    }

    ipdBilling[patientId] = { state: 'loading' };
    if (isLedgerPatient) renderIpdBilling(patientId);

    try {
        const res = await fetch('/hms/backend/api/invoices.php?action=patient_billing&patient_id=' + encodeURIComponent(patientId));
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed');
        ipdBilling[patientId] = {
            invoices: data.invoices || [],
            items: data.items || [],
            outstanding: data.outstanding || 0,
            state: 'ok',
            loaded: true
        };
    } catch (err) {
        console.error('IPD billing load error:', err);
        ipdBilling[patientId] = { invoices: [], items: [], outstanding: 0, state: 'error', loaded: true };
    }

    updateIpdDueCells(patientId);
    updateIpdBannerBill();
    if (ipdBillPatientId === patientId) renderIpdBilling(patientId);
}

function updateIpdDueCells(patientId) {
    document.querySelectorAll('#ipd-workspace .ipdw-due[data-pid="' + patientId + '"]').forEach(function (el) {
        el.innerHTML = ipdDueHtml(ipdBilling[patientId]);
    });
}

function renderIpdBillingEmpty() {
    const body = document.getElementById('ipdw-billing-body');
    if (body) body.innerHTML = '<div class="empty" style="text-align:center;padding:22px;color:#8A94A6;">Select a patient above to view their billing ledger.</div>';
}

function renderIpdBilling(patientId) {
    const body = document.getElementById('ipdw-billing-body');
    if (!body) return;
    const b = ipdBilling[String(patientId)];
    if (!b || b.state === 'loading') {
        body.innerHTML = '<div class="empty" style="text-align:center;padding:22px;color:#8A94A6;">Loading billing ledger...</div>';
        return;
    }
    if (b.state === 'error') {
        body.innerHTML = '<div class="empty" style="text-align:center;padding:22px;color:#8A94A6;">Failed to load billing ledger.</div>';
        return;
    }

    const invRows = (b.invoices || []).map(function (i) {
        const st = String(i.status || '').toLowerCase();
        return '<tr>' +
            '<td><span class="ipdw-code">' + escIpd(i.invoice_number) + '</span></td>' +
            '<td>' + fmtIpdDate(i.created_at) + '</td>' +
            '<td>' + (i.visit_id ? '#' + escIpd(i.visit_id) : '—') + '</td>' +
            '<td class="num">' + fmtIpdMoney(i.total_amount) + '</td>' +
            '<td class="num">' + fmtIpdMoney(i.discount_amount) + '</td>' +
            '<td class="num"><strong>' + fmtIpdMoney(i.net_amount) + '</strong></td>' +
            '<td class="ctr"><span class="ipdw-stat-badge ' + escIpd(st) + '">' + escIpd(i.status) + '</span></td>' +
        '</tr>';
    }).join('');

    const itemRows = (b.items || []).map(function (it) {
        return '<tr>' +
            '<td><span class="ipdw-sub" style="margin:0;">' + escIpd(it.item_type || it.category || 'Charge') + '</span></td>' +
            '<td>' + escIpd(it.description || '-') + '</td>' +
            '<td class="num">' + escIpd(it.quantity || 1) + '</td>' +
            '<td class="num">' + fmtIpdMoney(it.unit_price) + '</td>' +
            '<td class="num"><strong>' + fmtIpdMoney(it.total_price) + '</strong></td>' +
        '</tr>';
    }).join('');

    const due = Number(b.outstanding || 0);
    body.innerHTML =
        '<div class="ipdw-card" style="margin-bottom:14px;">' +
            '<div class="ipdw-bill-sec">Invoices</div>' +
            '<div class="ipdw-table-wrap"><table class="ipdw-table"><thead><tr>' +
                '<th>Invoice No.</th><th>Date</th><th>Visit</th><th class="num">Total</th><th class="num">Discount</th><th class="num">Net</th><th class="ctr">Status</th>' +
            '</tr></thead><tbody>' + (invRows || '<tr><td class="empty" colspan="7">No invoices on record</td></tr>') + '</tbody></table></div>' +
        '</div>' +
        '<div class="ipdw-card">' +
            '<div class="ipdw-bill-sec">Itemised Charges</div>' +
            '<div class="ipdw-table-wrap"><table class="ipdw-table"><thead><tr>' +
                '<th>Category</th><th>Description</th><th class="num">Qty</th><th class="num">Unit</th><th class="num">Amount</th>' +
            '</tr></thead><tbody>' + (itemRows || '<tr><td class="empty" colspan="5">No itemised charges</td></tr>') + '</tbody></table></div>' +
            '<div class="ipdw-bill-foot"><span>Auto-generated from the patient\'s invoices &amp; billing items</span>' +
                '<span>' + (due > 0 ? 'Fee To Be Paid: <span class="due">' + fmtIpdMoney(due) + '</span>' : '<span class="clear">Fully Settled</span>') + '</span></div>' +
        '</div>';
}

/* ---------------- Selection + banner ---------------- */
function selectIpdAdmission(id, silent) {
    const a = ipdAdmissions.find(function (x) { return x.id === id; });
    if (!a) return;
    ipdSelected = a;
    ipdBillPatientId = String(a.patient_id);
    const billSel = document.getElementById('ipdw-bill-patient');
    if (billSel) billSel.value = ipdBillPatientId;

    document.querySelectorAll('#ipd-workspace tr[data-admission]').forEach(function (tr) {
        tr.classList.toggle('selected', parseInt(tr.getAttribute('data-admission'), 10) === id);
    });

    // Banner
    const name = document.getElementById('ipdw-banner-name');
    const status = document.getElementById('ipdw-banner-status');
    const meta = document.getElementById('ipdw-banner-meta');
    if (name) name.textContent = a.patient_name || 'UNKNOWN PATIENT';
    if (status) {
        status.textContent = a.status;
        status.className = 'ipdw-pill ' + (a.status === 'Admitted' ? 'admitted' : 'discharged');
    }
    if (meta) {
        meta.innerHTML =
            '<span><b>Hospital No:</b> ' + escIpd(a.hospital_number || '—') + '</span>' +
            '<span><b>Gender/Age:</b> ' + escIpd(a.gender || '—') + ' / ' + escIpd(a.age !== null && a.age !== undefined ? a.age + ' Yrs' : '—') + '</span>' +
            '<span><b>Ward:</b> ' + escIpd(a.ward_name || '—') + (a.bed_number ? ' (Bed ' + escIpd(a.bed_number) + ')' : '') + '</span>' +
            '<span><b>Admitted:</b> ' + fmtIpdDate(a.admission_date) + '</span>' +
            '<span><b>Doctor:</b> ' + escIpd(a.admitting_doctor || '—') + '</span>';
    }
    updateIpdBannerBill();
    if (!silent && ipdBillPatientId) loadIpdBilling(ipdBillPatientId, true);
}

function updateIpdBannerBill() {
    const el = document.getElementById('ipdw-banner-bill');
    if (!el) return;
    if (!ipdSelected) { el.textContent = 'BILL: —'; el.className = 'ipdw-pill muted'; return; }
    const b = ipdBilling[String(ipdSelected.patient_id)];
    if (!b || b.state === 'loading') { el.textContent = 'BILL: …'; el.className = 'ipdw-pill muted'; return; }
    if (b.state === 'error') { el.textContent = 'BILL: n/a'; el.className = 'ipdw-pill muted'; return; }
    const due = Number(b.outstanding || 0);
    if (due > 0) { el.textContent = 'OUTSTANDING: ' + fmtIpdMoney(due) + ' DUE'; el.className = 'ipdw-pill due'; }
    else { el.textContent = 'BILL SETTLED'; el.className = 'ipdw-pill clear'; }
}

/* ---------------- Modals ---------------- */
function openIpdDetails(id) {
    const a = ipdAdmissions.find(function (x) { return x.id === id; }) || ipdSelected;
    if (!a) return;
    const body = document.getElementById('ipdw-details-body');
    if (!body) return;
    body.innerHTML =
        '<div class="ipdw-kv" style="margin-bottom:14px;">' +
            ipdKv('Patient', a.patient_name || '—') +
            ipdKv('Hospital No.', a.hospital_number || '—') +
            ipdKv('Gender / Age', (a.gender || '—') + ' / ' + (a.age !== null && a.age !== undefined ? a.age + ' Yrs' : '—')) +
            ipdKv('Admission No.', a.admission_code || '—') +
            ipdKv('Ward / Bed', (a.ward_name || '—') + (a.bed_number ? ' / ' + a.bed_number : '')) +
            ipdKv('Admission Type', a.admission_type || 'Routine') +
            ipdKv('Admitted', fmtIpdDateTime(a.admission_date)) +
            ipdKv('Attending Doctor', a.admitting_doctor || '—') +
            ipdKv('Status', a.status || '—') +
        '</div>' +
        '<div class="ipdw-block" style="margin-bottom:10px;"><strong>Provisional Diagnosis / Reason for Admission</strong><p>' + escIpd(a.diagnosis || 'No admission diagnosis recorded.') + '</p></div>' +
        '<div class="ipdw-block" style="margin-bottom:10px;"><strong>Admission Notes</strong><p>' + escIpd(a.notes || 'No admission notes recorded.') + '</p></div>' +
        '<div class="ipdw-block"><strong>Discharge Summary</strong><p>' + escIpd(a.discharge_notes || 'Not discharged.') + '</p></div>';
    openIpdModal('ipdw-details-modal');
}

function openIpdDischargeCard(id) {
    const a = ipdAdmissions.find(function (x) { return x.id === id; }) || ipdSelected;
    if (!a) return;
    const body = document.getElementById('ipdw-card-body');
    if (!body) return;
    const bill = ipdBilling[String(a.patient_id)];
    const due = bill && bill.state === 'ok' ? Number(bill.outstanding || 0) : null;

    body.innerHTML =
        '<div style="border:1px solid #DCE4EC;border-radius:10px;overflow:hidden;">' +
            '<div style="background:#1B659D;color:#fff;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">' +
                '<div><div style="font-weight:800;letter-spacing:.5px;">EHMS — DISCHARGE CARD</div>' +
                '<div style="font-size:10px;opacity:.85;">Eddie Healthcare Solutions · In-Patient Services</div></div>' +
                '<div style="text-align:right;"><div style="font-family:Consolas,monospace;font-weight:700;">' + escIpd(a.admission_code) + '</div>' +
                '<div style="font-size:10px;opacity:.85;">' + escIpd(a.status) + '</div></div>' +
            '</div>' +
            '<div style="padding:14px 16px;">' +
                '<div class="ipdw-kv" style="margin-bottom:12px;">' +
                    ipdKv('Patient', a.patient_name || '—') +
                    ipdKv('Hospital No.', a.hospital_number || '—') +
                    ipdKv('Gender / Age', (a.gender || '—') + ' / ' + (a.age !== null && a.age !== undefined ? a.age + ' Yrs' : '—')) +
                    ipdKv('Ward / Bed', (a.ward_name || '—') + (a.bed_number ? ' / ' + a.bed_number : '')) +
                    ipdKv('Admitted', fmtIpdDateTime(a.admission_date)) +
                    ipdKv('Discharged', a.discharged_at ? fmtIpdDateTime(a.discharged_at) : 'ACTIVE (IN-PATIENT)') +
                '</div>' +
                '<div class="ipdw-block" style="margin-bottom:10px;"><strong>Provisional Diagnosis</strong><p>' + escIpd(a.diagnosis || '—') + '</p></div>' +
                '<div class="ipdw-block" style="margin-bottom:10px;"><strong>Confirmed / Final Diagnosis</strong><p>' + escIpd(a.final_diagnosis || '—') + '</p></div>' +
                '<div class="ipdw-block" style="margin-bottom:10px;"><strong>Discharge Outcome</strong><p>' + escIpd(a.discharge_outcome || '—') + '</p></div>' +
                '<div class="ipdw-block" style="margin-bottom:10px;"><strong>Discharge Notes / Treatment Summary</strong><p>' + escIpd(a.discharge_notes || '—') + '</p></div>' +
                '<div class="ipdw-kv">' +
                    ipdKv('Follow-up Date', a.follow_up_date || '—') +
                    ipdKv('Attending Doctor', a.admitting_doctor || '—') +
                    ipdKv('Outstanding Balance', due === null ? '—' : (due > 0 ? fmtIpdMoney(due) : 'Fully Settled')) +
                '</div>' +
            '</div>' +
        '</div>';
    openIpdModal('ipdw-card-modal');
}

function ipdKv(k, v) {
    return '<div><div class="k">' + escIpd(k) + '</div><div class="v">' + escIpd(v) + '</div></div>';
}

function openIpdModal(id) { const el = document.getElementById(id); if (el) el.classList.add('show'); }
function closeIpdModal(id) { const el = document.getElementById(id); if (el) el.classList.remove('show'); }

function printIpdCard() {
    const body = document.getElementById('ipdw-card-body');
    if (!body) return;
    const w = window.open('', '_blank', 'width=900,height=1000');
    if (!w) { alert('Allow pop-ups to print the discharge card.'); return; }
    w.document.write('<html><head><title>EHMS Discharge Card</title>' +
        '<style>body{font-family:Arial,Helvetica,sans-serif;color:#334155;font-size:12px;padding:24px}' +
        '.ipdw-kv{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}' +
        '.ipdw-kv .k{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#64748B}' +
        '.ipdw-kv .v{font-size:12px;color:#1E293B;margin-top:2px}' +
        '.ipdw-block{background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:10px 12px;margin:10px 0}' +
        '.ipdw-block strong{display:block;color:#1B659D;font-size:11px;margin-bottom:4px}' +
        '.ipdw-block p{margin:0;white-space:pre-wrap;line-height:1.45}</style></head><body>' +
        body.innerHTML + '</body></html>');
    w.document.close();
    w.focus();
    setTimeout(function () { w.print(); }, 250);
}

/* ---------------- Helpers ---------------- */
function escIpd(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
function fmtIpdDate(v) {
    if (!v) return '—';
    const d = new Date(String(v).replace(' ', 'T'));
    if (isNaN(d.getTime())) return escIpd(v);
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}
function fmtIpdDateTime(v) {
    if (!v) return '—';
    const d = new Date(String(v).replace(' ', 'T'));
    if (isNaN(d.getTime())) return escIpd(v);
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' +
        d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
}
function fmtIpdMoney(v) {
    const n = Number(v || 0);
    return 'GHS ' + n.toFixed(2);
}
</script>
