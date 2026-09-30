<style>
/* ============ SYSTEM CONFIGURATION & CATALOG MANAGEMENT : LAUNCHER GRID ============
   Scoped under #sc-page. The shell already provides card/card-body/row/d-flex/
   font-weight-bold/text-uppercase and auto-appends the contact banner below every
   module page. Tile look follows the pasted grid (soft pastel icon badges + hover
   lift) — the anchor tiles are rendered as clickable cards that call loadPage. */
#sc-page .mb-0{margin:0}
#sc-page .mb-1{margin-bottom:.25rem}
#sc-page .mb-3{margin-bottom:1rem}
#sc-page .mb-4{margin-bottom:1.5rem}
#sc-page .p-2{padding:.5rem}
#sc-page .p-3{padding:1rem}
#sc-page .pe-1{padding-right:.25rem}
#sc-page .ps-2{padding-left:.5rem}
#sc-page .gap-2{gap:.5rem}
#sc-page .gap-3{gap:1rem}
#sc-page .rounded{border-radius:8px}
#sc-page .text-dark{color:#0F2D59}
#sc-page .text-muted{color:#64748B}
#sc-page .align-items-center{align-items:center}
#sc-page .d-flex{display:flex}
#sc-page .bg-white{background-color:#fff}
#sc-page .bg-primary{background-color:#0b5ed7}
#sc-page .text-white{color:#fff}
#sc-page .shadow-sm{box-shadow:0 1px 2px rgba(0,0,0,.05)}

/* Tile hover + border matching the pasted design */
#sc-page .sc-tile{
    background-color:#ffffff;
    transition:transform .15s ease-in-out, box-shadow .15s ease-in-out;
    border:1px solid #e3e6f0 !important;
    cursor:pointer;
    text-decoration:none;
}
#sc-page .sc-tile:hover{
    transform:translateY(-2px);
    box-shadow:0 .5rem 1rem rgba(0,0,0,.08) !important;
}

/* Soft color background badges matching the pasted grid */
#sc-page .icon-box{
    width:50px;
    height:50px;
    border-radius:8px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex:0 0 auto;
}
#sc-page .bg-success-subtle{background-color:#e6f9ed !important;color:#16a34a}
#sc-page .bg-danger-subtle{background-color:#fde8e8 !important;color:#dc2626}
#sc-page .bg-warning-subtle{background-color:#fef7e0 !important;color:#d97706}
#sc-page .bg-info-subtle{background-color:#e0f8ff !important;color:#0891b2}
#sc-page .bg-primary-subtle{background-color:#e8f1ff !important;color:#0d6efd}

#sc-page .sc-btn{display:inline-flex;align-items:center;gap:6px;border:none;padding:5px 14px;font-size:11px;font-weight:700;border-radius:4px;color:#0b5ed7;background-color:#fff;cursor:pointer;text-decoration:none;font-family:inherit;transition:background-color .2s, color .2s;line-height:1}
#sc-page .sc-btn:hover{background-color:#e8f1ff}
</style>

<div class="container-fluid p-3" id="sc-page" style="background-color:#F8FAFC;min-height:100vh;">

  <!-- TOP HEADER BAR (blue bar + light HOME/BACK buttons) -->
  <div class="d-flex justify-content-between align-items-center bg-primary p-2 rounded mb-4 shadow-sm">
    <div class="d-flex align-items-center gap-2 ps-2">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#fff;">
        <circle cx="12" cy="12" r="3"></circle>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
      </svg>
      <h5 class="font-weight-bold text-uppercase mb-0 text-white" style="font-size:15px;letter-spacing:.5px;">System Configuration &amp; Catalog</h5>
    </div>
    <div class="d-flex gap-2 pe-1">
      <button type="button" class="sc-btn" onclick="scNavHome()">HOME</button>
      <button type="button" class="sc-btn" onclick="scGoBack()">&lt; BACK</button>
    </div>
  </div>

  <!-- 5-TILE LAUNCHER GRID (matches the pasted SYSTEM CONFIGURATION & CATALOG MANAGEMENT GRID) -->
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-4">

    <!-- 1. ADD SERVICES & CATALOG -->
    <div class="col">
      <div class="card border-0 shadow-sm h-100 p-2 sc-tile" data-page="prices" style="border-radius:8px;border:1px solid #e3e6f0;">
        <div class="card-body d-flex align-items-center gap-3 p-2">
          <div class="p-3 bg-success-subtle text-success rounded d-flex align-items-center justify-content-center icon-box" style="width:50px;height:50px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </div>
          <div>
            <h6 class="fw-bold text-dark text-uppercase mb-0" style="font-size:.9rem;letter-spacing:.5px;">ADD SERVICES &amp; CATALOG</h6>
            <small class="text-muted" style="font-size:.78rem;">Add, edit, or update system services &amp; catalog pricing</small>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. ADD STOCK ITEM -->
    <div class="col">
      <div class="card border-0 shadow-sm h-100 p-2 sc-tile" data-page="inventory_management" style="border-radius:8px;border:1px solid #e3e6f0;">
        <div class="card-body d-flex align-items-center gap-3 p-2">
          <div class="p-3 bg-danger-subtle text-danger rounded d-flex align-items-center justify-content-center icon-box" style="width:50px;height:50px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
              <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
              <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
          </div>
          <div>
            <h6 class="fw-bold text-dark text-uppercase mb-0" style="font-size:.9rem;letter-spacing:.5px;">ADD STOCK ITEM</h6>
            <small class="text-muted" style="font-size:.78rem;">Add, edit, or update available &amp; unavailable stock</small>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. STOCK REPORT -->
    <div class="col">
      <div class="card border-0 shadow-sm h-100 p-2 sc-tile" data-page="inventory_management" style="border-radius:8px;border:1px solid #e3e6f0;">
        <div class="card-body d-flex align-items-center gap-3 p-2">
          <div class="p-3 bg-warning-subtle text-warning rounded d-flex align-items-center justify-content-center icon-box" style="width:50px;height:50px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="20" x2="18" y2="10"></line>
              <line x1="12" y1="20" x2="12" y2="4"></line>
              <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
          </div>
          <div>
            <h6 class="fw-bold text-dark text-uppercase mb-0" style="font-size:.9rem;letter-spacing:.5px;">STOCK REPORT</h6>
            <small class="text-muted" style="font-size:.78rem;">View &amp; record stock reports and user-requested items</small>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. CREATE NEW DEPARTMENT -->
    <div class="col">
      <div class="card border-0 shadow-sm h-100 p-2 sc-tile" data-page="departments" style="border-radius:8px;border:1px solid #e3e6f0;">
        <div class="card-body d-flex align-items-center gap-3 p-2">
          <div class="p-3 bg-info-subtle text-info rounded d-flex align-items-center justify-content-center icon-box" style="width:50px;height:50px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
              <line x1="8" y1="21" x2="16" y2="21"></line>
              <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
          </div>
          <div>
            <h6 class="fw-bold text-dark text-uppercase mb-0" style="font-size:.9rem;letter-spacing:.5px;">CREATE NEW DEPARTMENT</h6>
            <small class="text-muted" style="font-size:.78rem;">Add, edit, or update hospital departments</small>
          </div>
        </div>
      </div>
    </div>

    <!-- 5. CREATE NEW WARD / BED -->
    <div class="col">
      <div class="card border-0 shadow-sm h-100 p-2 sc-tile" data-page="wards" style="border-radius:8px;border:1px solid #e3e6f0;">
        <div class="card-body d-flex align-items-center gap-3 p-2">
          <div class="p-3 bg-primary-subtle text-primary rounded d-flex align-items-center justify-content-center icon-box" style="width:50px;height:50px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M2 4v16"></path>
              <path d="M2 8h18a2 2 0 0 1 2 2v10"></path>
              <path d="M2 17h20"></path>
              <path d="M6 8v9"></path>
            </svg>
          </div>
          <div>
            <h6 class="fw-bold text-dark text-uppercase mb-0" style="font-size:.9rem;letter-spacing:.5px;">CREATE NEW WARD / BED</h6>
            <small class="text-muted" style="font-size:.78rem;">Create, edit, or update hospital wards &amp; beds</small>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
/* Launcher page - no data init required. */
function initSystemConfiguration() {}

function scGo(page) {
    if (window.loadPage) window.loadPage(page);
    else if (window.navigateTo) window.navigateTo(page);
}

function scNavHome() { scGo('dashboard'); }

function scGoBack() {
    if (window.history && window.history.length > 1) window.history.back();
    else scNavHome();
}

document.addEventListener('click', function(e) {
    var tile = e.target.closest('.sc-tile');
    if (!tile) return;
    e.preventDefault();
    if (tile.dataset.page) scGo(tile.dataset.page);
});
</script>