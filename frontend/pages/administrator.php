<style>
/* ============ ADMINISTRATOR CONSOLE : SYSTEM DASHBOARD-STYLE LAUNCHER ============
   Scoped under #admin-cc. The shell already provides row/col-md-6/card/d-flex,
   shadow-sm/p-3/h-100/font-weight-bold/text-uppercase/hms-module-card and
   auto-appends the dark-blue contact banner (system-footer-strip) below every
   module page, so this page never hardcodes support contact details. */

/* Container reset to guarantee edge-to-edge layout */
#admin-cc.admin-console-wrapper{
    width:100% !important;
    max-width:100% !important;
}

/* Card hover animation */
#admin-cc .admin-tile{
    background-color:#ffffff;
    transition:transform .15s ease-in-out, box-shadow .15s ease-in-out;
    border:1px solid #e3e6f0 !important;
}
#admin-cc .admin-tile:hover{
    transform:translateY(-2px);
    box-shadow:0 .5rem 1rem rgba(0,0,0,.08) !important;
}

/* Soft color background badges matching System Dashboard */
#admin-cc .icon-box{
    width:52px;
    height:52px;
    border-radius:8px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex:0 0 auto;
}
#admin-cc .bg-light-blue{background-color:#e8f1ff !important}
#admin-cc .bg-light-green{background-color:#e6f9ed !important}
#admin-cc .bg-light-yellow{background-color:#fef7e0 !important}
#admin-cc .bg-light-pink{background-color:#fde8e8 !important}
#admin-cc .bg-light-cyan{background-color:#e0f8ff !important}
#admin-cc .bg-light-purple{background-color:#f3e8ff !important}
#admin-cc .bg-light-orange{background-color:#fff0e6 !important}
#admin-cc .bg-light-gray{background-color:#f1f3f5 !important}
/* Icon stroke colors (kept from the System Dashboard tile scheme) */
#admin-cc .bg-light-blue{color:#0284C7}
#admin-cc .bg-light-green{color:#16A34A}
#admin-cc .bg-light-yellow{color:#D97706}
#admin-cc .bg-light-pink{color:#DB2777}
#admin-cc .bg-light-cyan{color:#0D9488}
#admin-cc .bg-light-purple{color:#4F46E5}
#admin-cc .bg-light-orange{color:#EA580C}
#admin-cc .bg-light-gray{color:#475569}

/* Support Footer styling (applies to the shell-appended contact banner
   while this page is loaded; removed on navigation) */
.support-footer-bar{
    background-color:#0b5ed7 !important;
    font-size:.88rem;
    letter-spacing:.3px;
}

#admin-cc .justify-content-between{justify-content:space-between}
#admin-cc .align-items-center{align-items:center}
#admin-cc .mb-3{margin-bottom:1rem}
#admin-cc .mb-0{margin:0}
#admin-cc .p-2{padding:.5rem}
#admin-cc .p-3{padding:1rem}
#admin-cc .pe-1{padding-right:.25rem}
#admin-cc .ps-2{padding-left:.5rem}
#admin-cc .bg-white{background-color:#fff}
#admin-cc .bg-primary{background-color:#0b5ed7}
#admin-cc .text-white{color:#fff}
#admin-cc .text-dark{color:#0F2D59}
#admin-cc .rounded{border-radius:8px}
#admin-cc .gap-2{gap:.5rem}
#admin-cc .gap-3{gap:1rem}
#admin-cc .small{font-size:12px}
#admin-cc .tracking-wide{letter-spacing:.5px}
#admin-cc .acc-btn{display:inline-flex;align-items:center;gap:6px;border:none;padding:5px 14px;font-size:11px;font-weight:700;border-radius:4px;color:#0b5ed7;background-color:#fff;cursor:pointer;text-decoration:none;font-family:inherit;transition:background-color .2s, color .2s;line-height:1}
#admin-cc .acc-btn:hover{background-color:#e8f1ff}
</style>

<div class="container-fluid p-3 admin-console-wrapper" id="admin-cc" style="background-color:#F8FAFC;min-height:100vh;">

  <!-- TOP HEADER BAR (blue bar + light HOME/BACK/PASSWORD buttons) -->
  <div class="d-flex justify-content-between align-items-center bg-primary p-2 rounded mb-3 shadow-sm">
    <div class="d-flex align-items-center gap-2 ps-2">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#fff;">
        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
        <circle cx="8.5" cy="7" r="4"></circle>
        <polyline points="17 11 19 13 23 9"></polyline>
      </svg>
      <h5 class="font-weight-bold text-uppercase mb-0 text-white" style="font-size:15px;letter-spacing:.5px;">Administrator Console</h5>
    </div>
    <div class="d-flex gap-2 pe-1">
      <button type="button" class="acc-btn" onclick="admCCHome()">HOME</button>
      <button type="button" class="acc-btn" onclick="admCCBack()">&lt; BACK</button>
      <button type="button" class="acc-btn" onclick="admCCPassword()">PASSWORD</button>
    </div>
  </div>

  <!-- 2-COLUMN ACTION TILE GRID -->
  <div class="row g-3">

    <!-- USER MANAGEMENT -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100 admin-tile" style="border-radius:8px;" onclick="admCCGo('users')">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-box bg-light-blue me-3 rounded">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          </div>
          <span class="font-weight-bold text-dark text-uppercase small tracking-wide">USER MANAGEMENT</span>
        </div>
      </div>
    </div>

    <!-- ADD SERVICES & CATALOG -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100 admin-tile" style="border-radius:8px;" onclick="admCCGo('prices')">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-box bg-light-green me-3 rounded">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          </div>
          <span class="font-weight-bold text-dark text-uppercase small tracking-wide">ADD SERVICES &amp; CATALOG</span>
        </div>
      </div>
    </div>

    <!-- PRICE ADJUSTMENT -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100 admin-tile" style="border-radius:8px;" onclick="admCCGo('prices')">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-box bg-light-yellow me-3 rounded">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
          </div>
          <span class="font-weight-bold text-dark text-uppercase small tracking-wide">PRICE ADJUSTMENT</span>
        </div>
      </div>
    </div>

    <!-- ADD STOCK ITEM -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100 admin-tile" style="border-radius:8px;" onclick="admCCGo('inventory_management')">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-box bg-light-pink me-3 rounded">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
          </div>
          <span class="font-weight-bold text-dark text-uppercase small tracking-wide">ADD STOCK ITEM</span>
        </div>
      </div>
    </div>

    <!-- CREATE NEW DEPARTMENT -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100 admin-tile" style="border-radius:8px;" onclick="admCCGo('departments')">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-box bg-light-cyan me-3 rounded">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
          </div>
          <span class="font-weight-bold text-dark text-uppercase small tracking-wide">CREATE NEW DEPARTMENT</span>
        </div>
      </div>
    </div>

    <!-- CREATE NEW WARD / BED -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100 admin-tile" style="border-radius:8px;" onclick="admCCGo('wards')">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-box bg-light-purple me-3 rounded">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4v16"></path><path d="M2 8h18a2 2 0 0 1 2 2v10"></path><path d="M2 17h20"></path><path d="M6 8v9"></path></svg>
          </div>
          <span class="font-weight-bold text-dark text-uppercase small tracking-wide">CREATE NEW WARD / BED</span>
        </div>
      </div>
    </div>

    <!-- ADD BED / ROOM -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100 admin-tile" style="border-radius:8px;" onclick="admCCGo('beds')">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-box bg-light-orange me-3 rounded">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v2"></path><path d="M3 13h18v4H3z"></path><path d="M3 17v3M21 17v3M7 7h10v6H7z"></path></svg>
          </div>
          <span class="font-weight-bold text-dark text-uppercase small tracking-wide">ADD BED / ROOM</span>
        </div>
      </div>
    </div>

    <!-- SYSTEM ACTIVITIES / AUDIT -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100 admin-tile" style="border-radius:8px;" onclick="admCCGo('system-activities')">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-box bg-light-gray me-3 rounded">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"></path><rect x="9" y="3" width="6" height="4" rx="1"></rect><path d="M9 12h6M9 16h6"></path></svg>
          </div>
          <span class="font-weight-bold text-dark text-uppercase small tracking-wide">SYSTEM ACTIVITIES / AUDIT</span>
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