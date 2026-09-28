<?php
// Laboratory Management / Investigations view.
// Small server-side prologue: resolve the logged-in staff name so the lab entry
// form can pre-fill the technician field. The shell (dashboard.php) already
// authenticated the session; we only read the name here.
require_once __DIR__ . '/../../backend/config/config.php';
$__labStaff = getCurrentUserName() ?: 'Staff';
?>
<style>
/* ================= LABORATORY MANAGEMENT : LOCAL BOOTSTRAP-STYLE UTILITIES =================
   Scoped under #lab-page because the shell (dashboard.php) has no Bootstrap
   dependency. Tabs are driven by vanilla JS (switchToLabTab), not bootstrap.Tab. */
#lab-page .container-fluid{width:100%;padding-right:calc(1rem*.5);padding-left:calc(1rem*.5);margin-right:auto;margin-left:auto;box-sizing:border-box}
#lab-page .p-1{padding:.25rem !important}
#lab-page .p-2{padding:.5rem !important}
#lab-page .p-3{padding:1rem !important}
#lab-page .p-4{padding:1.5rem !important}
#lab-page .px-2{padding-left:.5rem !important;padding-right:.5rem !important}
#lab-page .px-3{padding-left:1rem !important;padding-right:1rem !important}
#lab-page .px-4{padding-left:1.5rem !important;padding-right:1.5rem !important}
#lab-page .py-1{padding-top:.25rem !important;padding-bottom:.25rem !important}
#lab-page .py-0{padding-top:0 !important;padding-bottom:0 !important}
#lab-page .py-2{padding-top:.5rem !important;padding-bottom:.5rem !important}
#lab-page .m-0{margin:0 !important}
#lab-page .mb-1{margin-bottom:.25rem !important}
#lab-page .mb-2{margin-bottom:.5rem !important}
#lab-page .mb-3{margin-bottom:1rem !important}
#lab-page .mb-0{margin-bottom:0 !important}
#lab-page .mb-4{margin-bottom:1.5rem !important}
#lab-page .me-1{margin-right:.25rem !important}
#lab-page .me-3{margin-right:1rem !important}
#lab-page .ms-1{margin-left:.25rem !important}
#lab-page .mt-3{margin-top:1rem !important}
#lab-page .mt-4{margin-top:1.5rem !important}
#lab-page .d-flex{display:flex}
#lab-page .d-block{display:block}
#lab-page .flex-wrap{flex-wrap:wrap}
#lab-page .align-items-center{align-items:center}
#lab-page .align-items-end{align-items:flex-end}
#lab-page .justify-content-between{justify-content:space-between}
#lab-page .justify-content-end{justify-content:flex-end}
#lab-page .justify-content-center{justify-content:center}
#lab-page .gap-2{gap:.5rem}
#lab-page .gap-3{gap:1rem}
#lab-page .gap-4{gap:1.5rem}
#lab-page .text-center{text-align:center}
#lab-page .text-uppercase{text-transform:uppercase}
#lab-page .font-weight-bold{font-weight:700}
#lab-page .fw-bold{font-weight:700}
#lab-page .h-100{height:100%}
#lab-page .text-muted{color:#64748B !important}
#lab-page .text-dark{color:#0F172A !important}
#lab-page .text-white{color:#fff !important}
#lab-page .text-primary{color:#0072BC !important}
#lab-page .text-success{color:#16A34A !important}
#lab-page .text-warning{color:#B45309 !important}
#lab-page .text-danger{color:#DC2626 !important}
#lab-page .bg-white{background-color:#fff !important}
#lab-page .bg-light{background-color:#F1F5F9 !important}
#lab-page .bg-primary{background-color:#0072BC !important}
#lab-page .bg-secondary{background-color:#64748B !important}
#lab-page .bg-success{background-color:#16A34A !important}
#lab-page .bg-danger{background-color:#DC2626 !important}
#lab-page .bg-warning{background-color:#F59E0B !important}
#lab-page .bg-info{background-color:#0EA5E9 !important}
#lab-page .card{position:relative;display:flex;flex-direction:column;min-width:0;word-wrap:break-word;background-color:#fff;background-clip:border-box;border:1px solid #E2E8F0;border-radius:8px}
#lab-page .border{border:1px solid #E2E8F0 !important}
#lab-page .border-top{border-top:1px solid #E2E8F0 !important}
#lab-page .border-bottom{border-bottom:1px solid #E2E8F0 !important}
#lab-page .border-bottom-0{border-bottom:0 !important}
#lab-page .border-0{border:0 !important}
#lab-page .rounded{border-radius:.375rem !important}
#lab-page .rounded-circle{border-radius:50% !important}
#lab-page .shadow-sm{box-shadow:0 .125rem .25rem rgba(15,45,89,.08) !important}
#lab-page .badge{display:inline-block;padding:.35em .6em;font-size:.73em;font-weight:700;line-height:1;text-align:center;white-space:nowrap;vertical-align:baseline;border-radius:.375rem}
#lab-page .badge-secondary{background-color:#64748B;color:#fff}
#lab-page .badge-primary{background-color:#0072BC;color:#fff}
#lab-page .badge-success{background-color:#16A34A;color:#fff}
#lab-page .badge-warning{background-color:#F59E0B;color:#fff}
#lab-page .badge-danger{background-color:#DC2626;color:#fff}
#lab-page .badge-info{background-color:#0EA5E9;color:#fff}
#lab-page .table{width:100%;margin-bottom:0;color:#334155;border-collapse:collapse;font-size:12.5px}
#lab-page .table-bordered{border:1px solid #E2E8F0}
#lab-page .table-bordered > thead > tr > th,
#lab-page .table-bordered > tbody > tr > td{border:1px solid #E2E8F0;padding:8px 10px;vertical-align:middle}
#lab-page .table-striped > tbody > tr:nth-of-type(odd){background-color:rgba(15,45,89,.03)}
#lab-page .table-hover > tbody > tr:hover{background-color:#EFF6FF}
#lab-page .table thead th{background-color:#F1F5F9;color:#475569;letter-spacing:.3px;white-space:nowrap}
#lab-page .table-responsive{overflow-x:auto}
#lab-page .align-middle{vertical-align:middle}
#lab-page .form-label{display:inline-block;margin-bottom:.35rem;font-family:inherit}
#lab-page .form-select,#lab-page .form-control{display:block;width:100%;padding:.375rem .6rem;font-size:12px;line-height:1.5;color:#334155;background-color:#fff;border:1px solid #CBD5E1;border-radius:4px;box-sizing:border-box;font-family:inherit}
#lab-page .form-select-sm,#lab-page .form-control-sm{padding:.25rem .5rem;font-size:12px}
#lab-page .btn-outline-primary{background-color:#fff;color:#0072BC;border:1px solid #0072BC}
#lab-page .btn-outline-primary:hover{background-color:#0072BC;color:#fff}
#lab-page .btn-outline-secondary{background-color:#fff;color:#475569;border:1px solid #CBD5E1}
#lab-page .btn-outline-secondary:hover{background-color:#E2E8F0;color:#0F172A}
#lab-page .row{display:flex;flex-wrap:wrap;margin-right:calc(1rem * -.5);margin-left:calc(1rem * -.5);box-sizing:border-box}
#lab-page .row > [class*="col-"]{padding-right:calc(1rem*.5);padding-left:calc(1rem*.5);box-sizing:border-box}
#lab-page .col-md-3{flex:0 0 100%;max-width:100%}
#lab-page .col-md-4{flex:0 0 100%;max-width:100%}
#lab-page .col-md-6{flex:0 0 100%;max-width:100%}
@media (min-width:768px){
  #lab-page .col-md-3{flex:0 0 25%;max-width:25%}
  #lab-page .col-md-4{flex:0 0 33.3333%;max-width:33.3333%}
  #lab-page .col-md-6{flex:0 0 50%;max-width:50%}
}
@media (min-width:992px){
  #lab-page .col-lg-4{flex:0 0 33.3333%;max-width:33.3333%}
}
#lab-page .g-3{margin:0}
#lab-page .nav-tabs{display:flex;flex-wrap:wrap;gap:2px;list-style:none;padding:0;margin:0 0 16px;border-bottom:2px solid #E2E8F0}
#lab-page .nav-item{list-style:none}
#lab-page .nav-link{display:inline-block;padding:9px 15px;font-size:12px;font-weight:600;text-transform:uppercase;text-decoration:none;color:#475569;background:transparent;border:1px solid transparent;border-bottom:none;border-radius:6px 6px 0 0;cursor:pointer;font-family:inherit}
#lab-page .nav-link:hover{color:#0072BC}
#lab-page .nav-link.active{color:#0072BC;background:#fff;border-color:#E2E8F0;border-bottom:2px solid #0072BC;position:relative;top:1px}
#lab-page .tab-pane{display:none}
#lab-page .tab-pane.show{display:block}
#lab-page .lab-portal-card{transition:box-shadow .15s ease,transform .15s ease}
#lab-page .lab-portal-card:hover{box-shadow:0 .5rem 1.25rem rgba(15,45,89,.14) !important;transform:translateY(-2px)}
</style>

<div class="container-fluid p-3" id="lab-page" style="background-color: #F8FAFC; min-height: 100vh;">

  <!-- TOP TITLE HEADER -->
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 p-3 bg-white shadow-sm rounded border">
    <div class="d-flex align-items-center gap-2">
      <div class="p-2 rounded" style="background: rgba(217, 119, 6, 0.1);">
        <!-- Lab Tubes Icon -->
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><path d="M10 2v7.51L4.53 17.92A2 2 0 0 0 6.24 21h11.52a2 2 0 0 0 1.71-3.08L14 9.51V2"></path><line x1="8.5" y1="2" x2="15.5" y2="2"></line></svg>
      </div>
      <div>
        <h5 class="m-0 font-weight-bold text-uppercase" style="color: #0F2D59;">LABORATORY MANAGEMENT</h5>
        <small class="text-muted">Manage lab requests, test entries, sample tracking, and dispatched reports</small>
      </div>
    </div>

    <!-- STATS COUNTERS -->
    <div class="d-flex gap-3 text-center">
      <div class="px-3 py-1 rounded bg-light border">
        <span class="d-block text-muted" style="font-size: 10px; font-weight: 700;">REQUESTED</span>
        <span class="fw-bold text-primary" style="font-size: 14px;" id="statLabRequested">0</span>
      </div>
      <div class="px-3 py-1 rounded bg-light border">
        <span class="d-block text-muted" style="font-size: 10px; font-weight: 700;">NOT READY</span>
        <span class="fw-bold text-warning" style="font-size: 14px;" id="statLabNotReady">0</span>
      </div>
      <div class="px-3 py-1 rounded bg-light border">
        <span class="d-block text-muted" style="font-size: 10px; font-weight: 700;">READY</span>
        <span class="fw-bold text-success" style="font-size: 14px;" id="statLabReady">0</span>
      </div>
    </div>
  </div>

  <!-- MODULE NAV TABS (vanilla JS tabs — no Bootstrap dependency) -->
  <ul class="nav nav-tabs" id="labTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active text-uppercase px-3 py-2" id="grid-tab" data-target="tab-grid" type="button" role="tab" onclick="switchToLabTab(this.id)">Overview Grid</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link text-uppercase px-3 py-2" id="requested-tab" data-target="tab-requested" type="button" role="tab" onclick="switchToLabTab(this.id)">Requested Labs <span class="badge badge-primary ms-1" id="reqTabBadge">0</span></button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link text-uppercase px-3 py-2" id="entry-tab" data-target="tab-entry" type="button" role="tab" onclick="switchToLabTab(this.id)">Staff Entry Form</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link text-uppercase px-3 py-2" id="pending-tab" data-target="tab-pending" type="button" role="tab" onclick="switchToLabTab(this.id)">Not Ready (<span id="pendingTabBadge">0</span>)</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link text-uppercase px-3 py-2" id="ready-tab" data-target="tab-ready" type="button" role="tab" onclick="switchToLabTab(this.id)">Ready Labs (<span id="readyTabBadge">0</span>)</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link text-uppercase px-3 py-2" id="dispatched-tab" data-target="tab-dispatched" type="button" role="tab" onclick="switchToLabTab(this.id)">Dispatched Labs</button>
    </li>
  </ul>

  <!-- TAB CONTENT PANELS -->
  <div class="tab-content" id="labTabsContent">

    <!-- TAB 0: MAIN DASHBOARD GRID -->
    <div class="tab-pane show" id="tab-grid" role="tabpanel">
      <div class="row g-3">

        <!-- CARD 1: SAMPLE MANAGEMENT -->
        <div class="col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm p-3 h-100 lab-portal-card" style="border-radius: 8px; cursor: pointer;" onclick="switchToLabTab('entry-tab')">
            <div class="d-flex align-items-center">
              <div class="p-3 me-3 rounded" style="background: rgba(217, 119, 6, 0.1);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><path d="M10 2v7.51L4.53 17.92A2 2 0 0 0 6.24 21h11.52a2 2 0 0 0 1.71-3.08L14 9.51V2"></path><line x1="8.5" y1="2" x2="15.5" y2="2"></line></svg>
              </div>
              <div>
                <h6 class="fw-bold mb-1" style="color: #0F2D59;">SAMPLE MANAGEMENT</h6>
                <small class="text-muted">Specimen collection, barcodes, &amp; log tracking</small>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD 2: PENDING / NOT READY TESTS -->
        <div class="col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm p-3 h-100 lab-portal-card" style="border-radius: 8px; cursor: pointer;" onclick="switchToLabTab('pending-tab')">
            <div class="d-flex align-items-center">
              <div class="p-3 me-3 rounded" style="background: rgba(239, 68, 68, 0.1);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              </div>
              <div>
                <h6 class="fw-bold mb-1" style="color: #0F2D59;">PENDING / NOT READY</h6>
                <small class="text-muted">Work in progress &amp; processing queue</small>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD 3: LAB VERIFICATION -->
        <div class="col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm p-3 h-100 lab-portal-card" style="border-radius: 8px; cursor: pointer;" onclick="switchToLabTab('ready-tab')">
            <div class="d-flex align-items-center">
              <div class="p-3 me-3 rounded" style="background: rgba(16, 185, 129, 0.1);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
              </div>
              <div>
                <h6 class="fw-bold mb-1" style="color: #0F2D59;">LAB VERIFICATION &amp; READY</h6>
                <small class="text-muted">Review, approve, and finalize test outcomes</small>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD 4: REPORT DISPATCHED -->
        <div class="col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm p-3 h-100 lab-portal-card" style="border-radius: 8px; cursor: pointer;" onclick="switchToLabTab('dispatched-tab')">
            <div class="d-flex align-items-center">
              <div class="p-3 me-3 rounded" style="background: rgba(0, 114, 188, 0.1);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0072BC" stroke-width="2"><path d="M22 2L11 13"></path><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
              </div>
              <div>
                <h6 class="fw-bold mb-1" style="color: #0F2D59;">REPORT DISPATCHED</h6>
                <small class="text-muted">Sent to ward/doctor &amp; patient printed logs</small>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD 5: DOCTOR REQUESTS -->
        <div class="col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm p-3 h-100 lab-portal-card" style="border-radius: 8px; cursor: pointer;" onclick="switchToLabTab('requested-tab')">
            <div class="d-flex align-items-center">
              <div class="p-3 me-3 rounded" style="background: rgba(15, 45, 89, 0.1);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0F2D59" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
              </div>
              <div>
                <h6 class="fw-bold mb-1" style="color: #0F2D59;">DOCTOR REQUESTS</h6>
                <small class="text-muted">Incoming lab requisitions from OPD/IPD</small>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- TAB 1: ALL LABS REQUESTED BY DOCTOR -->
    <div class="tab-pane" id="tab-requested" role="tabpanel">
      <div class="card border-0 shadow-sm p-3 bg-white rounded">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
          <h6 class="fw-bold mb-0" style="color: #0F2D59;">DOCTOR REQUISITIONS QUEUE</h6>
          <div class="d-flex gap-2 align-items-center">
            <input type="text" id="labRequisitionSearch" class="form-control form-control-sm" style="width: 250px;" placeholder="Search by patient or hospital no...">
            <button type="button" class="btn btn-sm btn-primary" id="new-lab-request-btn" style="background-color:#0072BC;">+ New Request</button>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle" style="font-size: 13px;">
            <thead class="text-uppercase" style="font-size: 11px;">
              <tr>
                <th>REQ ID</th>
                <th>DATE/TIME</th>
                <th>PATIENT NAME</th>
                <th>HOSPITAL NO</th>
                <th>TEST REQUESTED</th>
                <th>REQUESTING DOCTOR</th>
                <th>URGENCY</th>
                <th>STATUS</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody id="reqTableBody">
              <tr><td colspan="9" style="text-align:center;padding:14px;color:#94A3B8;">Loading requisitions...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 2: LAB RESULT ENTRY FORM FOR STAFF -->
    <div class="tab-pane" id="tab-entry" role="tabpanel">
      <div class="card border-0 shadow-sm p-4 bg-white rounded">
        <h6 class="fw-bold mb-3" style="color: #0F2D59;">STAFF LAB RESULT ENTRY FORM</h6>
        <form id="labResultEntryForm">
          <div class="row mb-3">
            <div class="col-md-4 mb-2">
              <label class="form-label font-weight-bold" style="font-size: 12px;">Select Patient / Requisition</label>
              <select class="form-select form-select-sm" id="entryRequisition">
                <option value="">-- Awaiting requisitions --</option>
              </select>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label font-weight-bold" style="font-size: 12px;">Specimen Type</label>
              <input type="text" class="form-control form-control-sm" id="entrySpecimen" placeholder="e.g. Venous Blood (EDTA Tube)">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label font-weight-bold" style="font-size: 12px;">Lab Technician / Staff</label>
              <input type="text" class="form-control form-control-sm" id="entryTechnician" value="<?php echo htmlspecialchars($__labStaff, ENT_QUOTES); ?>" readonly>
            </div>
          </div>

          <h6 class="fw-bold text-muted mb-2" style="font-size: 12px;">RESULT VALUES &amp; INTERPRETATION</h6>
          <div class="row mb-3">
            <div class="col-md-6 mb-2">
              <label class="form-label font-weight-bold" style="font-size: 12px;">Test Results *</label>
              <textarea class="form-control form-control-sm" id="entryResults" rows="4" placeholder="e.g. Hb: 12.5 g/dL, WBC: 11.2 x10^3/µL, MP: Positive (+), Platelets: 240 x10^3/µL..."></textarea>
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label font-weight-bold" style="font-size: 12px;">Normal / Reference Range</label>
              <textarea class="form-control form-control-sm" id="entryNormalRange" rows="2" placeholder="e.g. Hb 12.0–16.0 g/dL, WBC 4.0–10.0 x10^3/µL"></textarea>
              <label class="form-label font-weight-bold mt-3" style="font-size: 12px;">Pathologist / Technician Remarks</label>
              <textarea class="form-control form-control-sm" id="entryInterpretation" rows="2" placeholder="Clinical notes or observations..."></textarea>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-sm btn-secondary px-3" data-save="draft">Save Draft</button>
            <button type="submit" class="btn btn-sm btn-success px-4" data-save="final" style="background-color: #10B981;">Mark as Ready &amp; Save</button>
          </div>
        </form>
      </div>
    </div>

    <!-- TAB 3: NOT READY LABS -->
    <div class="tab-pane" id="tab-pending" role="tabpanel">
      <div class="card border-0 shadow-sm p-3 bg-white rounded">
        <h6 class="fw-bold mb-3" style="color: #B45309;">IN-PROGRESS / NOT READY LABS</h6>
        <div class="table-responsive">
          <table class="table table-striped align-middle" style="font-size: 13px;">
            <thead class="text-uppercase" style="font-size: 11px;">
              <tr>
                <th>REQ ID</th>
                <th>PATIENT</th>
                <th>TESTS</th>
                <th>REQUESTED AT</th>
                <th>STATUS</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody id="pendingTableBody">
              <tr><td colspan="6" style="text-align:center;padding:14px;color:#94A3B8;">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 4: READY LABS -->
    <div class="tab-pane" id="tab-ready" role="tabpanel">
      <div class="card border-0 shadow-sm p-3 bg-white rounded">
        <h6 class="fw-bold mb-3" style="color: #16A34A;">READY &amp; VERIFIED LABS</h6>
        <div class="table-responsive">
          <table class="table table-hover align-middle" style="font-size: 13px;">
            <thead class="text-uppercase" style="font-size: 11px;">
              <tr>
                <th>REQ ID</th>
                <th>PATIENT</th>
                <th>TEST</th>
                <th>RESULT</th>
                <th>VERIFIED / COMPLETED</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody id="readyTableBody">
              <tr><td colspan="6" style="text-align:center;padding:14px;color:#94A3B8;">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 5: DISPATCHED LABS -->
    <div class="tab-pane" id="tab-dispatched" role="tabpanel">
      <div class="card border-0 shadow-sm p-3 bg-white rounded">
        <h6 class="fw-bold mb-1" style="color: #0072BC;">DISPATCHED REPORTS HISTORY</h6>
        <p class="text-muted m-0 mb-2" style="font-size: 11px;">Note: the current backend has no "dispatched" lab status yet, so this dispatch log is tracked in this browser only (per workstation).</p>
        <div class="table-responsive">
          <table class="table table-bordered align-middle" style="font-size: 13px;">
            <thead class="text-uppercase" style="font-size: 11px;">
              <tr>
                <th>DISPATCH ID</th>
                <th>PATIENT</th>
                <th>TEST</th>
                <th>DISPATCH DATE</th>
                <th>REPORT</th>
              </tr>
            </thead>
            <tbody id="dispatchedTableBody">
              <tr><td colspan="5" style="text-align:center;padding:14px;color:#94A3B8;">No dispatched reports yet.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

</div>

<!-- New Lab Request Modal -->
<div class="modal" id="lab-request-modal">
    <div class="modal-content" style="max-width:560px;">
        <div class="modal-header">
            <h3>New Lab Request</h3>
            <button class="modal-close" id="close-lab-request-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="lab-request-form">
                <div class="form-group">
                    <label for="lab-visit">Visit *</label>
                    <select id="lab-visit" required>
                        <option value="">Select Visit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="lab-test-type">Test Type *</label>
                    <input type="text" id="lab-test-type" placeholder="e.g. Full Blood Count, Malaria RDT, Urine Analysis" required>
                </div>
                <div class="form-group">
                    <label for="lab-test-description">Description / Clinical Notes</label>
                    <textarea id="lab-test-description" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label for="lab-urgency">Urgency</label>
                    <select id="lab-urgency">
                        <option value="routine">Routine</option>
                        <option value="urgent">Urgent</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                    <button type="button" class="btn btn-secondary" id="cancel-lab-request">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var CURRENT_LAB_STAFF = <?php echo json_encode($__labStaff); ?>;
var labData = [];
var visitsCache = [];
var DISPATCH_KEY = 'hms:lab-dispatched';
var labResultsMap = {};

async function initLabManagement() {
    setupEventListeners();
    await Promise.all([loadVisits(), loadLabRequests()]);
}

function setupEventListeners() {
    var newBtn = document.getElementById('new-lab-request-btn');
    if (newBtn) newBtn.addEventListener('click', function () {
        document.getElementById('lab-request-form').reset();
        document.getElementById('lab-request-modal').classList.add('show');
    });
    var closeReq = document.getElementById('close-lab-request-modal');
    if (closeReq) closeReq.addEventListener('click', function () { document.getElementById('lab-request-modal').classList.remove('show'); });
    var cancelReq = document.getElementById('cancel-lab-request');
    if (cancelReq) cancelReq.addEventListener('click', function () { document.getElementById('lab-request-modal').classList.remove('show'); });
    var reqForm = document.getElementById('lab-request-form');
    if (reqForm) reqForm.addEventListener('submit', handleLabRequestSubmit);
    var entryForm = document.getElementById('labResultEntryForm');
    if (entryForm) entryForm.addEventListener('submit', handleEntrySubmit);
    var search = document.getElementById('labRequisitionSearch');
    if (search) search.addEventListener('input', debounce(applySearch, 250));
}

async function loadVisits() {
    try {
        const response = await fetch('/hms/backend/api/visits.php');
        const data = await response.json();
        if (data.success) {
            visitsCache = data.visits || [];
            const sel = document.getElementById('lab-visit');
            sel.innerHTML = '<option value="">Select Visit</option>';
            visitsCache.forEach(function (v) {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.visit_number + ' — ' + v.patient_name + ' (' + v.hospital_number + ')';
                sel.appendChild(opt);
            });
        }
    } catch (error) { console.error('Visits load error:', error); }
}

async function loadLabRequests() {
    try {
        const response = await fetch('/hms/backend/api/lab.php?action=requests');
        const data = await response.json();
        if (data.success) {
            labData = data.requests || [];
        } else {
            throw new Error(data.error || 'Failed to load lab requests');
        }
    } catch (error) {
        console.error('Lab load error:', error);
        labData = [];
    }
    await loadLabResults();
    renderAll();
}

async function loadLabResults() {
    labResultsMap = {};
    try {
        const response = await fetch('/hms/backend/api/lab.php?action=results');
        const data = await response.json();
        if (data.success) {
            (data.results || []).forEach(function (res) {
                if (res.lab_request_id !== null && res.lab_request_id !== undefined) {
                    labResultsMap[String(res.lab_request_id)] = res;
                }
            });
        }
    } catch (error) {
        console.error('Lab results load error:', error);
    }
}

function renderAll() {
    renderStats();
    renderRequested();
    renderPending();
    renderReady();
    renderDispatched();
    populateEntryRequisition();
}

function renderStats() {
    const notReady = labData.filter(function (r) { return r.status === 'pending' || r.status === 'in_progress'; }).length;
    const ready = labData.filter(function (r) { return r.status === 'completed'; }).length;
    document.getElementById('statLabRequested').textContent = labData.length;
    document.getElementById('statLabNotReady').textContent = notReady;
    document.getElementById('statLabReady').textContent = ready;
    document.getElementById('reqTabBadge').textContent = labData.length;
    document.getElementById('pendingTabBadge').textContent = notReady;
    document.getElementById('readyTabBadge').textContent = ready;
}

function fmtLabTime(v) {
    return window.fmtDateTime ? window.fmtDateTime(v) : (v || '--');
}

function renderRequested() {
    const tbody = document.getElementById('reqTableBody');
    if (!labData.length) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:14px;color:#94A3B8;">No lab requests yet — use "+ New Request" to add one.</td></tr>';
        return;
    }
    const q = (document.getElementById('labRequisitionSearch').value || '').trim().toLowerCase();
    const rows = q
        ? labData.filter(function (r) { return String(r.patient_name || '').toLowerCase().indexOf(q) >= 0 || String(r.hospital_number || '').toLowerCase().indexOf(q) >= 0; })
        : labData;
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:14px;color:#94A3B8;">No requisitions match "' + escHtml(q) + '".</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(function (r) {
        const urgencyBadge = r.urgency === 'emergency' ? 'badge-danger' : r.urgency === 'urgent' ? 'badge-warning' : 'badge-secondary';
        const statusBadge = r.status === 'completed' ? 'badge-success' : r.status === 'cancelled' ? 'badge-danger' : r.status === 'in_progress' ? 'badge-info' : 'badge-warning';
        return '<tr>' +
            '<td class="fw-bold text-primary">#LAB-' + (r.id || '') + '</td>' +
            '<td>' + fmtLabTime(r.requested_at) + '</td>' +
            '<td class="fw-bold">' + escHtml(r.patient_name || '--') + '</td>' +
            '<td>' + escHtml(r.hospital_number || '--') + '</td>' +
            '<td><span class="badge badge-secondary">' + escHtml(r.test_type || '--') + '</span></td>' +
            '<td>' + escHtml(r.doctor_name || '--') + '</td>' +
            '<td><span class="badge ' + urgencyBadge + '">' + escHtml((r.urgency || 'routine').toUpperCase()) + '</span></td>' +
            '<td><span class="badge ' + statusBadge + '">' + escHtml(String(r.status || '').replace('_', ' ')) + '</span></td>' +
            '<td>' +
                (r.status === 'pending' || r.status === 'in_progress'
                    ? '<button class="btn btn-sm btn-primary py-1 px-2" style="font-size:11px;background:#0072BC;" onclick="openEntryFor(' + r.id + ')">Process Sample</button>'
                    : '<span class="badge badge-info">' + escHtml(r.result_status || 'result saved') + '</span>') +
            '</td>' +
        '</tr>';
    }).join('');
}

function applySearch() { renderRequested(); }

function renderPending() {
    const tbody = document.getElementById('pendingTableBody');
    const rows = labData.filter(function (r) { return r.status === 'pending' || r.status === 'in_progress'; });
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:14px;color:#94A3B8;">No in-progress / not-ready labs.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(function (r) {
        return '<tr>' +
            '<td class="fw-bold">#LAB-' + (r.id || '') + '</td>' +
            '<td class="fw-bold">' + escHtml(r.patient_name || '--') + '</td>' +
            '<td>' + escHtml(r.test_type || '--') + '</td>' +
            '<td>' + fmtLabTime(r.requested_at) + '</td>' +
            '<td><span class="badge badge-warning">' + escHtml(String(r.status || 'pending').replace('_', ' ')) + '</span></td>' +
            '<td><button class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px;" onclick="openEntryFor(' + r.id + ')">Enter Results</button></td>' +
        '</tr>';
    }).join('');
}

function renderReady() {
    const tbody = document.getElementById('readyTableBody');
    const dispatched = readDispatched();
    const rows = labData.filter(function (r) {
        return r.status === 'completed' && !dispatched.some(function (d) { return d.id === String(r.id); });
    });
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:14px;color:#94A3B8;">No ready & verified labs.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(function (r) {
        const labRes = labResultsMap[String(r.id)] || null;
        const resultText = labRes ? String(labRes.results || '--') : String(r.result_status || '--');
        const doneAt = labRes && labRes.completed_at ? fmtLabTime(labRes.completed_at) : fmtLabTime(r.requested_at);
        return '<tr>' +
            '<td class="fw-bold text-success">#LAB-' + (r.id || '') + '</td>' +
            '<td class="fw-bold">' + escHtml(r.patient_name || '--') + '</td>' +
            '<td>' + escHtml(r.test_type || '--') + '</td>' +
            '<td style="max-width:280px;white-space:pre-wrap;">' + escHtml(resultText) + '</td>' +
            '<td>' + (labRes && labRes.verified_name ? escHtml(labRes.verified_name) + '<br>' : '') + doneAt + '</td>' +
            '<td><button class="btn btn-sm btn-success py-1 px-3" style="font-size:11px;" onclick="dispatchReport(' + r.id + ')">Dispatch Report</button></td>' +
        '</tr>';
    }).join('');
}

function renderDispatched() {
    const tbody = document.getElementById('dispatchedTableBody');
    const list = readDispatched();
    const rows = list.map(function (d) {
        const r = labData.find(function (x) { return String(x.id) === d.id; }) || null;
        return '<tr>' +
            '<td class="fw-bold">#DSP-' + escHtml(d.id) + '</td>' +
            '<td class="fw-bold">' + escHtml(r ? r.patient_name : '--') + '</td>' +
            '<td>' + escHtml(r ? r.test_type : '--') + '</td>' +
            '<td>' + (d.at ? fmtLabTime(d.at) : '--') + '</td>' +
            '<td><button class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:11px;" onclick="printReport(' + (r ? r.id : 0) + ')">Print PDF</button></td>' +
        '</tr>';
    }).join('');
    tbody.innerHTML = rows || '<tr><td colspan="5" style="text-align:center;padding:14px;color:#94A3B8;">No dispatched reports yet — dispatch a ready lab above.</td></tr>';
}

function populateEntryRequisition() {
    const sel = document.getElementById('entryRequisition');
    const keep = sel.value;
    const rows = labData.filter(function (r) { return r.status === 'pending' || r.status === 'in_progress'; });
    sel.innerHTML = rows.length
        ? rows.map(function (r) { return '<option value="' + r.id + '">#LAB-' + r.id + ' — ' + escHtml(r.patient_name) + ' (' + escHtml(r.test_type) + ')</option>'; }).join('')
        : '<option value="">-- Awaiting requisitions --</option>';
    if (keep && rows.some(function (r) { return String(r.id) === String(keep); })) sel.value = keep;
}

function openEntryFor(requestId) {
    populateEntryRequisition();
    const sel = document.getElementById('entryRequisition');
    if (sel.options.length && Array.from(sel.options).some(function (o) { return String(o.value) === String(requestId); })) {
        sel.value = String(requestId);
    }
    switchToLabTab('entry-tab');
}

async function handleEntrySubmit(e) {
    e.preventDefault();
    const reqId = document.getElementById('entryRequisition').value;
    if (!reqId) {
        showAlert('Select a requisition first — there are no pending lab requests to record results for.', 'error');
        return;
    }
    const saveAs = e.submitter && e.submitter.getAttribute ? (e.submitter.getAttribute('data-save') || 'final') : 'final';
    let results = document.getElementById('entryResults').value.trim();
    const spec = document.getElementById('entrySpecimen').value.trim();
    if (spec) results = results ? 'Specimen: ' + spec + '\n' + results : 'Specimen: ' + spec;
    if (!results) {
        showAlert('Enter the result values before saving.', 'error');
        return;
    }
    const data = {
        lab_request_id: reqId,
        results: results,
        normal_range: document.getElementById('entryNormalRange').value.trim(),
        interpretation: document.getElementById('entryInterpretation').value.trim(),
        status: saveAs
    };
    try {
        const response = await fetch('/hms/backend/api/lab.php?action=result', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            showAlert(saveAs === 'final' ? 'Result saved and marked as READY.' : 'Result saved as draft.', 'success');
            document.getElementById('entryResults').value = '';
            document.getElementById('entryInterpretation').value = '';
            document.getElementById('entryNormalRange').value = '';
            await loadLabRequests();
        } else {
            showAlert(result.error || 'Save failed', 'error');
        }
    } catch (error) {
        console.error('Entry save error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

async function handleLabRequestSubmit(e) {
    e.preventDefault();
    const data = {
        visit_id: document.getElementById('lab-visit').value,
        test_type: document.getElementById('lab-test-type').value.trim(),
        test_description: document.getElementById('lab-test-description').value.trim(),
        urgency: document.getElementById('lab-urgency').value
    };
    if (!data.visit_id || !data.test_type) {
        showAlert('Visit and test type are required', 'error');
        return;
    }
    try {
        const response = await fetch('/hms/backend/api/lab.php?action=request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            showAlert('Lab request submitted', 'success');
            document.getElementById('lab-request-modal').classList.remove('show');
            document.getElementById('lab-request-form').reset();
            await loadLabRequests();
        } else {
            showAlert(result.error || 'Request failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}

/* ---- Dispatch (browser-local log; backend has no 'dispatched' status yet) ---- */
function readDispatched() {
    try {
        const raw = localStorage.getItem(DISPATCH_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch (e) { return []; }
}

function dispatchReport(requestId) {
    const list = readDispatched();
    if (!list.some(function (d) { return String(d.id) === String(requestId); })) {
        list.push({ id: String(requestId), at: new Date().toISOString() });
        try { localStorage.setItem(DISPATCH_KEY, JSON.stringify(list)); } catch (e) {}
    }
    showAlert('Report marked as dispatched (browser dispatch log).', 'success');
    renderAll();
}

function printReport(requestId) {
    const r = labData.find(function (x) { return String(x.id) === String(requestId); });
    const lines = [
        'LABORATORY REPORT',
        '=================',
        'Request   : #LAB-' + (r ? r.id : ''),
        'Patient   : ' + (r ? r.patient_name : ''),
        'Hospital  : ' + (r ? r.hospital_number : ''),
        'Test      : ' + (r ? r.test_type : ''),
        'Status    : DISPATCHED',
        '',
        'Use your browser print dialog (Ctrl+P) to produce this report as a PDF.'
    ];
    showAlert('Report #LAB-' + (r ? r.id : '') + ' queued for printing — use Ctrl+P to export the PDF.', 'info');
    console.log(lines.join('\n'));
}

/* ---- Vanilla tab switcher (replaces bootstrap.Tab) ---- */
function switchToLabTab(triggerId) {
    var btn = document.getElementById(triggerId);
    var targetId = btn ? btn.getAttribute('data-target') : null;
    if (!targetId) return;
    document.querySelectorAll('#lab-page .nav-tabs .nav-link').forEach(function (b) {
        b.classList.toggle('active', b === btn);
    });
    document.querySelectorAll('#lab-page .tab-pane').forEach(function (p) {
        var show = p.id === targetId;
        p.classList.toggle('show', show);
    });
}

function debounce(fn, wait) {
    var t;
    return function () {
        var args = arguments;
        var ctx = this;
        clearTimeout(t);
        t = setTimeout(function () { fn.apply(ctx, args); }, wait);
    };
}

function escHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
</script>