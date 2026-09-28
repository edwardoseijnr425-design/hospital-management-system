<style>
/* ============ ADMINISTRATOR CONSOLE : SYSTEM DASHBOARD-STYLE LAUNCHER ============
   Scoped under #admin-cc. The shell already provides row/col-md-6/card/d-flex,
   border-0/shadow-sm/p-3/h-100/font-weight-bold/text-uppercase/hms-module-card
   and auto-appends the dark-blue contact banner below every module page. */
#admin-cc .justify-content-between{justify-content:space-between}
#admin-cc .mb-3{margin-bottom:1rem}
#admin-cc .p-2{padding:.5rem}
#admin-cc .bg-white{background-color:#fff}
#admin-cc .rounded{border-radius:8px}
#admin-cc .border{border:1px solid #E2E8F0}
#admin-cc .gap-2{gap:.5rem}
#admin-cc .gap-3{gap:1rem}
#admin-cc .d-block{display:block}
#admin-cc .acc-btn{display:inline-flex;align-items:center;gap:6px;border:none;padding:5px 14px;font-size:11px;font-weight:700;border-radius:4px;color:#fff;cursor:pointer;text-decoration:none;font-family:inherit;transition:background-color .2s}
#admin-cc .acc-btn-blue{background-color:#0072BC}
#admin-cc .acc-btn-blue:hover{background-color:#0b5fa5}
#admin-cc .acc-btn-info{background-color:#0EA5E9}
#admin-cc .acc-btn-info:hover{background-color:#0284C7}
#admin-cc .acc-icon-box{width:50px;height:50px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex:0 0 auto}
</style>

<div class="container-fluid p-3" id="admin-cc" style="background-color:#F8FAFC;min-height:100vh;">

  <!-- TOP TITLE & ROUTING HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-white rounded shadow-sm border">
    <h5 class="font-weight-bold text-uppercase" style="margin:0;color:#0F2D59;font-size:16px;letter-spacing:.5px;">Administrator Console</h5>
    <div class="d-flex gap-2">
      <button type="button" class="acc-btn acc-btn-blue" onclick="admCCHome()">HOME</button>
      <button type="button" class="acc-btn acc-btn-blue" onclick="admCCBack()">&larr; BACK</button>
      <button type="button" class="acc-btn acc-btn-info" onclick="admCCPassword()">PASSWORD</button>
    </div>
  </div>

  <!-- 2-COLUMN SPACIOUS MODULE GRID (matching System Dashboard card style) -->
  <div class="row g-3">

    <!-- CARD 1: USER MANAGEMENT -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded hms-module-card" style="border:1px solid #E2E8F0 !important;cursor:pointer;" onclick="admCCGo('users')">
        <div class="d-flex align-items-center gap-3">
          <div class="acc-icon-box" style="background-color:#E0F2FE;color:#0284C7;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          </div>
          <div class="flex-grow-1 text-center">
            <span class="font-weight-bold text-uppercase d-block" style="color:#0F2D59;font-size:13px;letter-spacing:.5px;">USER MANAGEMENT</span>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD 2: ADD SERVICES & CATALOG -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded hms-module-card" style="border:1px solid #E2E8F0 !important;cursor:pointer;" onclick="admCCGo('prices')">
        <div class="d-flex align-items-center gap-3">
          <div class="acc-icon-box" style="background-color:#DCFCE7;color:#16A34A;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"></path></svg>
          </div>
          <div class="flex-grow-1 text-center">
            <span class="font-weight-bold text-uppercase d-block" style="color:#0F2D59;font-size:13px;letter-spacing:.5px;">ADD SERVICES &amp; CATALOG</span>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD 3: PRICE ADJUSTMENT -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded hms-module-card" style="border:1px solid #E2E8F0 !important;cursor:pointer;" onclick="admCCGo('prices')">
        <div class="d-flex align-items-center gap-3">
          <div class="acc-icon-box" style="background-color:#FEF3C7;color:#D97706;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
          </div>
          <div class="flex-grow-1 text-center">
            <span class="font-weight-bold text-uppercase d-block" style="color:#0F2D59;font-size:13px;letter-spacing:.5px;">PRICE ADJUSTMENT</span>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD 4: INVENTORY / STOCK ITEM -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded hms-module-card" style="border:1px solid #E2E8F0 !important;cursor:pointer;" onclick="admCCGo('inventory_management')">
        <div class="d-flex align-items-center gap-3">
          <div class="acc-icon-box" style="background-color:#FCE7F3;color:#DB2777;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
          </div>
          <div class="flex-grow-1 text-center">
            <span class="font-weight-bold text-uppercase d-block" style="color:#0F2D59;font-size:13px;letter-spacing:.5px;">ADD STOCK ITEM</span>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD 5: CREATE NEW DEPARTMENT -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded hms-module-card" style="border:1px solid #E2E8F0 !important;cursor:pointer;" onclick="admCCGo('departments')">
        <div class="d-flex align-items-center gap-3">
          <div class="acc-icon-box" style="background-color:#CCFBF1;color:#0D9488;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path></svg>
          </div>
          <div class="flex-grow-1 text-center">
            <span class="font-weight-bold text-uppercase d-block" style="color:#0F2D59;font-size:13px;letter-spacing:.5px;">CREATE NEW DEPARTMENT</span>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD 6: CREATE NEW WARD / BED -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded hms-module-card" style="border:1px solid #E2E8F0 !important;cursor:pointer;" onclick="admCCGo('wards')">
        <div class="d-flex align-items-center gap-3">
          <div class="acc-icon-box" style="background-color:#E0E7FF;color:#4F46E5;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16M2 8h18a2 2 0 0 1 2 2v10M2 17h20"></path></svg>
          </div>
          <div class="flex-grow-1 text-center">
            <span class="font-weight-bold text-uppercase d-block" style="color:#0F2D59;font-size:13px;letter-spacing:.5px;">CREATE NEW WARD / BED</span>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD 7: ADD BED / ROOM -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded hms-module-card" style="border:1px solid #E2E8F0 !important;cursor:pointer;" onclick="admCCGo('beds')">
        <div class="d-flex align-items-center gap-3">
          <div class="acc-icon-box" style="background-color:#FFEDD5;color:#EA580C;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v2"></path><path d="M3 13h18v4H3z"></path><path d="M3 17v3M21 17v3M7 7h10v6H7z"></path></svg>
          </div>
          <div class="flex-grow-1 text-center">
            <span class="font-weight-bold text-uppercase d-block" style="color:#0F2D59;font-size:13px;letter-spacing:.5px;">ADD BED / ROOM</span>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD 8: SYSTEM ACTIVITIES / AUDIT -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded hms-module-card" style="border:1px solid #E2E8F0 !important;cursor:pointer;" onclick="admCCGo('system-activities')">
        <div class="d-flex align-items-center gap-3">
          <div class="acc-icon-box" style="background-color:#F1F5F9;color:#475569;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"></path><rect x="9" y="3" width="6" height="4" rx="1"></rect><path d="M9 12h6M9 16h6"></path></svg>
          </div>
          <div class="flex-grow-1 text-center">
            <span class="font-weight-bold text-uppercase d-block" style="color:#0F2D59;font-size:13px;letter-spacing:.5px;">SYSTEM ACTIVITIES / AUDIT</span>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
/* Launcher page - no data init required. */
function initAdministrator() {}

function admCCGo(page, e) {
    if (e && e.preventDefault) e.preventDefault();
    if (window.loadPage) window.loadPage(page);
    else if (window.navigateTo) window.navigateTo(page);
}

function admCCHome() { admCCGo('dashboard'); }

function admCCBack() {
    if (window.history && window.history.length > 1) window.history.back();
    else admCCHome();
}

function admCCPassword() { admCCGo('account-management'); }
</script>