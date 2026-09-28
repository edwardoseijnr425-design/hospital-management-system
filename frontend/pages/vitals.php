<?php
// Vital Signs / Ward Details view with the Clinical Patient Care & Treatment Sheet card.
// Small server-side prologue: resolve the logged-in staff name for signatures and
// the "Logging as" footer. The shell (dashboard.php) already authenticated the session.
require_once __DIR__ . '/../../backend/config/config.php';
$__clinicStaff = getCurrentUserName() ?: 'Staff';
?>
<style>
/* ================= VITAL SIGNS / WARD DETAILS CARD UI : LOCAL BOOTSTRAP-STYLE UTILITIES =================
   Scoped under #vitals-page because the shell (dashboard.php) has no Bootstrap
   dependency and flattens plain .card inside .main-content-card-wrapper. */
#vitals-page .container-fluid{width:100%;padding-right:calc(1rem*.5);padding-left:calc(1rem*.5);margin-right:auto;margin-left:auto;box-sizing:border-box}
#vitals-page .p-3{padding:1rem !important}
#vitals-page .card{position:relative;display:flex;flex-direction:column;min-width:0;word-wrap:break-word;background-color:#fff;background-clip:border-box;border:1px solid rgba(15,45,89,.08);border-radius:8px}
#vitals-page .card-header{padding:10px 16px;background-color:#fff;border-bottom:1px solid #E2E8F0;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
#vitals-page .card-body{flex:1 1 auto;padding:1rem 1rem 1.25rem}
#vitals-page .card-footer{padding:.75rem 1rem;background-color:#fff;border-top:1px solid #E2E8F0;display:flex;align-items:center}
#vitals-page .border-0{border:0 !important}
#vitals-page .shadow-sm{box-shadow:0 .125rem .25rem rgba(0,0,0,.075) !important}
#vitals-page .shadow-none{box-shadow:none !important}
#vitals-page .mb-4{margin-bottom:1.5rem !important}
#vitals-page .mb-3{margin-bottom:1rem !important}
#vitals-page .mb-2{margin-bottom:.5rem !important}
#vitals-page .mb-1{margin-bottom:.25rem !important}
#vitals-page .mb-0{margin-bottom:0 !important}
#vitals-page .me-1{margin-right:.25rem !important}
#vitals-page .me-3{margin-right:1rem !important}
#vitals-page .ms-1{margin-left:.25rem !important}
#vitals-page .ms-2{margin-left:.5rem !important}
#vitals-page .mt-2{margin-top:.5rem !important}
#vitals-page .mt-3{margin-top:1rem !important}
#vitals-page .mt-4{margin-top:1.5rem !important}
#vitals-page .m-0{margin:0 !important}
#vitals-page .py-2{padding-top:.5rem !important;padding-bottom:.5rem !important}
#vitals-page .py-0{padding-top:0 !important;padding-bottom:0 !important}
#vitals-page .pt-2{padding-top:.5rem !important}
#vitals-page .px-3{padding-left:1rem !important;padding-right:1rem !important}
#vitals-page .px-2{padding-left:.5rem !important;padding-right:.5rem !important}
#vitals-page .px-1{padding-left:.25rem !important;padding-right:.25rem !important}
#vitals-page .px-4{padding-left:1.5rem !important;padding-right:1.5rem !important}
#vitals-page .p-2{padding:.5rem !important}
#vitals-page .p-4{padding:1.5rem !important}
#vitals-page .badge{display:inline-block;padding:.35em .6em;font-size:.75em;font-weight:700;line-height:1;text-align:center;white-space:nowrap;vertical-align:baseline;border-radius:.375rem}
#vitals-page .row{display:flex;flex-wrap:wrap;margin-right:calc(1rem * -.5);margin-left:calc(1rem * -.5);box-sizing:border-box}
#vitals-page .row > [class*="col-"]{padding-right:calc(1rem*.5);padding-left:calc(1rem*.5);box-sizing:border-box}
#vitals-page .col-md-3{flex:0 0 100%;max-width:100%}
#vitals-page .col-md-4{flex:0 0 100%;max-width:100%}
#vitals-page .col-md-5{flex:0 0 100%;max-width:100%}
@media (min-width:768px){
  #vitals-page .col-md-3{flex:0 0 25%;max-width:25%}
  #vitals-page .col-md-4{flex:0 0 33.3333%;max-width:33.3333%}
  #vitals-page .col-md-5{flex:0 0 41.6667%;max-width:41.6667%}
}
#vitals-page .w-100{width:100% !important}
#vitals-page .align-items-end{align-items:flex-end}
#vitals-page .align-items-center{align-items:center}
#vitals-page .justify-content-between{justify-content:space-between}
#vitals-page .justify-content-end{justify-content:flex-end}
#vitals-page .justify-content-center{justify-content:center}
#vitals-page .flex-wrap{flex-wrap:wrap}
#vitals-page .gap-2{gap:.5rem}
#vitals-page .gap-3{gap:1rem}
#vitals-page .gap-4{gap:1.5rem}
#vitals-page .gap-5{gap:3rem}
#vitals-page .d-flex{display:flex}
#vitals-page .d-block{display:block}
#vitals-page .form-label{display:inline-block;margin-bottom:.5rem;font-family:inherit}
#vitals-page .form-select{display:block;width:100%;padding:.375rem .75rem;font-size:12px;line-height:1.5;color:#334155;background-color:#fff;border:1px solid #CBD5E1;border-radius:4px}
#vitals-page .form-select-sm{padding:.25rem .5rem;font-size:12px}
#vitals-page .form-control{display:block;width:100%;padding:.375rem .6rem;font-size:12px;line-height:1.5;color:#334155;background-color:#fff;border:1px solid #CBD5E1;border-radius:4px;box-sizing:border-box;font-family:inherit}
#vitals-page .form-control-sm{padding:.25rem .5rem;font-size:12px}
#vitals-page .text-center{text-align:center}
#vitals-page .text-uppercase{text-transform:uppercase}
#vitals-page .font-weight-bold{font-weight:700}
#vitals-page .fw-bold{font-weight:700}
#vitals-page .text-dark{color:#0F172A !important}
#vitals-page .text-muted{color:#64748B !important}
#vitals-page .text-secondary{color:#64748B !important}
#vitals-page .text-white{color:#fff !important}
#vitals-page .text-primary{color:#0072BC !important}
#vitals-page .text-success{color:#198754 !important}
#vitals-page .text-danger{color:#DC2626 !important}
#vitals-page .text-info{color:#0EA5E9 !important}
#vitals-page .fs-5{font-size:1.25rem !important}
#vitals-page .bg-white{background-color:#fff !important}
#vitals-page .bg-dark{background-color:#1E293B !important}
#vitals-page .bg-secondary{background-color:#64748B !important}
#vitals-page .bg-primary{background-color:#0072BC !important}
#vitals-page .bg-success{background-color:#198754 !important}
#vitals-page .bg-danger{background-color:#DC2626 !important}
#vitals-page .bg-warning{background-color:#F59E0B !important}
#vitals-page .bg-info{background-color:#0EA5E9 !important}
#vitals-page .bg-success-subtle{background-color:#D1E7DD !important}
#vitals-page .bg-primary-subtle{background-color:#CFE2FF !important}
#vitals-page .border{border:1px solid #E2E8F0 !important}
#vitals-page .border-top{border-top:1px solid #E2E8F0 !important}
#vitals-page .border-bottom{border-bottom:1px solid #E2E8F0 !important}
#vitals-page .border-info{border-color:#0EA5E9 !important}
#vitals-page .border-secondary-subtle{border-color:#E2E8F0 !important}
#vitals-page .border-success{border-color:#198754 !important}
#vitals-page .border-primary{border-color:#0072BC !important}
#vitals-page .rounded{border-radius:.375rem !important}
#vitals-page .rounded-circle{border-radius:50% !important}
#vitals-page .table{width:100%;margin-bottom:1rem;color:#334155;border-collapse:collapse;font-size:12px}
#vitals-page .table-bordered{border:1px solid #E2E8F0}
#vitals-page .table-bordered > thead > tr > th{border:1px solid #E2E8F0;padding:8px 10px;vertical-align:middle}
#vitals-page .table-bordered > tbody > tr > td{border:1px solid #E2E8F0;padding:8px 10px;vertical-align:middle}
#vitals-page .table-striped > tbody > tr:nth-of-type(odd){background-color:rgba(15,45,89,.03)}
#vitals-page .table-responsive{overflow-x:auto}
#vitals-page .align-middle{vertical-align:middle}
#vitals-page .btn-light{background-color:#fff;color:#334155;border:1px solid #E2E8F0}
#vitals-page .btn-success{background-color:#198754;border:1px solid #198754;color:#fff}
#vitals-page .btn-success:hover{background-color:#157347;border-color:#157347;color:#fff}
#vitals-page .btn-outline-primary{background-color:#fff;color:#0072BC;border:1px solid #0072BC}
#vitals-page .btn-outline-primary:hover{background-color:#0072BC;color:#fff}
#vitals-page .btn-outline-info{background-color:#fff;color:#0EA5E9;border:1px solid #0EA5E9}
#vitals-page .btn-outline-info:hover{background-color:#0EA5E9;color:#fff}
#vitals-page .btn-outline-secondary{background-color:#fff;color:#475569;border:1px solid #CBD5E1}
#vitals-page .btn-outline-secondary:hover{background-color:#E2E8F0;color:#0F172A}
</style>

<div class="container-fluid p-3" id="vitals-page" style="background-color: #F8FAFC; min-height: 100vh;">

  <!-- TOP FILTER HEADER PANEL -->
  <div class="card border-0 shadow-sm p-3 mb-4" style="border-radius: 8px; background: #FFFFFF;">
    <div class="row g-3 align-items-end">

      <!-- Type of Ward Dropdown -->
      <div class="col-md-4">
        <label class="form-label font-weight-bold text-uppercase mb-1" style="font-size: 11px; color: #1E293B;">Type Of Ward</label>
        <select class="form-select form-select-sm shadow-none" id="selWardType" style="border-color: #CBD5E1; border-radius: 4px; font-size: 13px;">
          <option value="By Ward" selected>By Ward</option>
          <option value="By Specialty">By Specialty</option>
        </select>
      </div>

      <!-- Wards Selection (populated from the wards API) -->
      <div class="col-md-5">
        <label class="form-label font-weight-bold text-uppercase mb-1" style="font-size: 11px; color: #1E293B;">Wards</label>
        <select class="form-select form-select-sm shadow-none" id="selWard" style="border-color: #CBD5E1; border-radius: 4px; font-size: 13px;">
          <option value="">-- All Wards --</option>
        </select>
      </div>

      <!-- Search Trigger Button -->
      <div class="col-md-3 d-flex gap-2">
        <button type="button" class="btn btn-sm btn-primary w-100 font-weight-bold px-3" onclick="filterVitalsByWard()" style="background-color: #0072BC; border: none; border-radius: 4px; height: 31px;">
          Search
        </button>
      </div>

    </div>
  </div>

  <!-- MAIN WARD TITLE & SUMMARY COUNTERS -->
  <div class="text-center mb-3">
    <h5 class="font-weight-bold text-uppercase mb-2" style="letter-spacing: 1px; color: #0F2D59;">WARD DETAILS</h5>
    <div class="d-flex justify-content-center gap-5 text-dark font-weight-bold" style="font-size: 14px;">
      <span>Total Patient(S): <span class="text-primary fs-5 ms-1" id="statTotalPatients">0</span></span>
      <span>Partial Discharged Patient(S): <span class="text-muted fs-5 ms-1" id="statPartialDischarged" title="No partial-discharge flag exists in the admissions schema yet">0</span></span>
    </div>
  </div>

  <!-- PATIENT CLINICAL CARE & TREATMENT SHEET CARDS (populated by JS — one per patient) -->
  <div id="wardCardsContainer">
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 8px; border: 1px solid #E2E8F0 !important;">
      <div class="card-body p-4 text-center" style="color:#64748B; font-size:13px;">Loading ward details...</div>
    </div>
  </div>

</div>

<!-- Vitals Modal (Record / Edit) -->
<div class="modal" id="vitals-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="vitals-modal-title">Record Vital Signs</h3>
            <button class="modal-close" id="close-vitals-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="vitals-form">
                <input type="hidden" id="editing-vitals-id">

                <!-- Auto-draft banner -->
                <div id="vitals-draft-banner" style="display:none;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;background:#FEF3C7;border:1px solid #F59E0B;border-radius:6px;padding:8px 12px;margin-bottom:12px;font-size:12px;color:#92400E;">
                    <span id="vitals-draft-info"></span>
                    <span style="white-space:nowrap;">
                        <button type="button" onclick="restoreDraft()" style="background:#0072BC;border:1px solid #0072BC;color:#fff;border-radius:4px;font-size:12px;font-weight:700;padding:4px 10px;cursor:pointer;">Restore</button>
                        <button type="button" onclick="discardDraft()" style="background:#fff;border:1px solid #D1D5DB;color:#334155;border-radius:4px;font-size:12px;font-weight:700;padding:4px 10px;cursor:pointer;margin-left:6px;">Discard</button>
                    </span>
                </div>

                <div class="form-group">
                    <label for="vitals-visit">Visit *</label>
                    <select id="vitals-visit" name="visit_id" required>
                        <option value="">Select Visit</option>
                    </select>
                </div>

                <h4>Vital Signs</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="vitals-temp">Temperature (°C)</label>
                        <input type="number" step="0.1" id="vitals-temp" name="temperature" placeholder="36.5">
                    </div>
                    <div class="form-group">
                        <label for="vitals-bp-sys">BP Systolic (mmHg)</label>
                        <input type="number" id="vitals-bp-sys" name="blood_pressure_systolic" placeholder="120">
                    </div>
                    <div class="form-group">
                        <label for="vitals-bp-dia">BP Diastolic (mmHg)</label>
                        <input type="number" id="vitals-bp-dia" name="blood_pressure_diastolic" placeholder="80">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="vitals-hr">Heart Rate (bpm)</label>
                        <input type="number" id="vitals-hr" name="heart_rate" placeholder="72">
                    </div>
                    <div class="form-group">
                        <label for="vitals-rr">Respiratory Rate (bpm)</label>
                        <input type="number" id="vitals-rr" name="respiratory_rate" placeholder="16">
                    </div>
                    <div class="form-group">
                        <label for="vitals-spo2">SpO2 (%)</label>
                        <input type="number" id="vitals-spo2" name="oxygen_saturation" placeholder="98">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="vitals-weight">Weight (kg)</label>
                        <input type="number" step="0.1" id="vitals-weight" name="weight" placeholder="70">
                    </div>
                    <div class="form-group">
                        <label for="vitals-height">Height (cm)</label>
                        <input type="number" step="0.1" id="vitals-height" name="height" placeholder="170">
                    </div>
                </div>

                <div class="form-group">
                    <label for="vitals-notes">Notes</label>
                    <textarea id="vitals-notes" name="notes" rows="2" placeholder="Additional observations..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="vitals-submit-btn">Save Vitals</button>
                    <button type="button" class="btn btn-secondary" id="cancel-vitals">Cancel</button>
                </div>
                <div id="vitals-save-status" style="margin-top:10px;font-size:11px;color:#64748B;">Draft auto-save enabled — in-progress entries are kept if the page or tab closes unexpectedly.</div>
            </form>
        </div>
    </div>
</div>

<!-- Vitals History Modal (history + edit existing records) -->
<div class="modal" id="vitals-history-modal">
    <div class="modal-content" style="max-width:820px;">
        <div class="modal-header">
            <h3 id="vitals-history-title">Vitals History</h3>
            <button class="modal-close" id="close-vitals-history-modal">&times;</button>
        </div>
        <div class="modal-body">
            <p id="vitals-history-sub" style="margin:0 0 12px;font-size:12px;color:#64748B;"></p>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr style="background:#F1F5F9;color:#0F2D59;">
                            <th style="padding:8px 10px;border:1px solid #E2E8F0;text-align:left;">Date</th>
                            <th style="padding:8px 10px;border:1px solid #E2E8F0;text-align:left;">Temp</th>
                            <th style="padding:8px 10px;border:1px solid #E2E8F0;text-align:left;">BP</th>
                            <th style="padding:8px 10px;border:1px solid #E2E8F0;text-align:left;">HR</th>
                            <th style="padding:8px 10px;border:1px solid #E2E8F0;text-align:left;">RR</th>
                            <th style="padding:8px 10px;border:1px solid #E2E8F0;text-align:left;">SpO2</th>
                            <th style="padding:8px 10px;border:1px solid #E2E8F0;text-align:left;">Weight</th>
                            <th style="padding:8px 10px;border:1px solid #E2E8F0;text-align:left;">Notes</th>
                            <th style="padding:8px 10px;border:1px solid #E2E8F0;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="vitals-history-body">
                        <tr><td colspan="9" style="text-align:center;padding:14px;color:#64748B;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="form-actions" style="margin-top:14px;">
                <button type="button" class="btn btn-secondary" id="cancel-vitals-history">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Bed Modal (Change Bed / Transfer Patient) -->
<div class="modal" id="transfer-bed-modal">
    <div class="modal-content" style="max-width:520px;">
        <div class="modal-header">
            <h3>Change Bed / Transfer Patient</h3>
            <button class="modal-close" id="close-transfer-bed-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="transfer-bed-form">
                <input type="hidden" id="transfer-patient-id">
                <p id="transfer-patient-info" style="margin:0 0 12px;font-size:12px;color:#64748B;"></p>
                <div class="form-group">
                    <label for="transfer-ward">Destination Ward *</label>
                    <select id="transfer-ward" required>
                        <option value="">Select Ward</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="transfer-bed">Available Bed *</label>
                    <select id="transfer-bed" required>
                        <option value="">Select a ward first</option>
                    </select>
                </div>
                <p style="margin:0 0 12px;font-size:11px;color:#94A3B8;">Only beds currently marked <strong>Available</strong> are listed. The patient's previous bed is freed automatically after the transfer.</p>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="confirm-transfer-btn">Transfer Patient</button>
                    <button type="button" class="btn btn-secondary" id="cancel-transfer-bed">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Treatment Log Modal -->
<div class="modal" id="add-treatment-modal">
    <div class="modal-content" style="max-width:520px;">
        <div class="modal-header">
            <h3>Log Treatment</h3>
            <button class="modal-close" id="close-add-treatment-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="add-treatment-form">
                <input type="hidden" id="treatment-patient-id">
                <div class="form-group">
                    <label for="treatment-name">Treatment / Procedure *</label>
                    <input type="text" id="treatment-name" placeholder="e.g. IV fluids, wound dressing, oxygen therapy" required>
                </div>
                <div class="form-group">
                    <label for="treatment-notes">Staff Notes</label>
                    <textarea id="treatment-notes" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label for="treatment-status">Status</label>
                    <select id="treatment-status">
                        <option value="Done">Done</option>
                        <option value="Pending">Pending</option>
                        <option value="Failed">Failed</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">+ Log Treatment</button>
                    <button type="button" class="btn btn-secondary" id="cancel-add-treatment">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var CURRENT_VITALS_STAFF = <?php echo json_encode($__clinicStaff); ?>;
var wardsData = [];
var patientContext = {};   // patient_id -> { bed, summary, latestVisit, visits, vitals }
var medsCache = {};        // patient_id -> prescription list
var draftTimer = null;
const DRAFT_KEY = 'hms:vitals-draft';

async function initVitals() {
    setupEventListeners();
    await Promise.all([loadWards(), loadActiveVisits()]);
    await filterVitalsByWard();
    setupDraftAutosave();
    checkDraftOnLoad();
}

/* ============================ WARDS ============================ */
async function loadWards() {
    try {
        const response = await fetch('/hms/backend/api/wards.php');
        const data = await response.json();
        wardsData = data.success ? (data.wards || []) : [];
        const sel = document.getElementById('selWard');
        const previous = sel.value;
        sel.innerHTML = '<option value="">-- All Wards --</option>' +
            wardsData.map(w => `<option value="${w.id}">${escHtml(w.ward_name)}</option>`).join('');
        if (previous && wardsData.some(w => String(w.id) === String(previous))) sel.value = previous;
    } catch (error) {
        console.error('Wards load error:', error);
    }
}

function getWardName(wardId) {
    const w = wardsData.find(x => String(x.id) === String(wardId));
    return w ? w.ward_name : 'the selected ward';
}

/* ============================ ACTIVE VISITS (modal dropdown) ============================ */
async function loadActiveVisits() {
    try {
        const response = await fetch('/hms/backend/api/visits.php?action=active');
        const data = await response.json();
        if (data.success) {
            const select = document.getElementById('vitals-visit');
            data.visits.forEach(visit => {
                select.innerHTML += `<option value="${visit.id}">${visit.visit_number} - ${visit.patient_name}</option>`;
            });
        }
    } catch (error) {
        console.error('Active visits load error:', error);
    }
}

function ensureVisitOption(visit, selectIt) {
    const sel = document.getElementById('vitals-visit');
    if (!sel || !visit) return;
    let exists = false;
    for (const opt of sel.options) {
        if (String(opt.value) === String(visit.id)) { exists = true; break; }
    }
    if (!exists) {
        const opt = document.createElement('option');
        opt.value = visit.id;
        opt.textContent = `${visit.visit_number || ''} - ${visit.patient_name || ''}`.trim() || ('Visit #' + visit.id);
        sel.appendChild(opt);
    }
    if (selectIt) sel.value = String(visit.id);
}

/* ============================ FILTER / RENDER ============================ */
async function filterVitalsByWard() {
    const wardId = document.getElementById('selWard').value;
    const container = document.getElementById('wardCardsContainer');
    container.innerHTML = '<div class="card border-0 shadow-sm mb-4" style="border-radius:8px;border:1px solid #E2E8F0 !important;"><div class="card-body p-4 text-center" style="color:#64748B;font-size:13px;">Searching ward... please wait.</div></div>';
    try {
        const beds = await fetchOccupiedBeds(wardId);
        await renderWardCards(beds, wardId);
    } catch (error) {
        console.error('Filter vitals error:', error);
        container.innerHTML = '<div class="card border-0 shadow-sm mb-4" style="border-radius:8px;border:1px solid #E2E8F0 !important;"><div class="card-body p-4 text-center" style="color:#C0392B;font-size:13px;">Failed to load ward data. Please try again.</div></div>';
        updateCounters(0, 0);
    }
}

async function fetchOccupiedBeds(wardId) {
    const qs = wardId ? `?status=Occupied&ward_id=${encodeURIComponent(wardId)}` : '?status=Occupied';
    const response = await fetch('/hms/backend/api/beds.php' + qs);
    const data = await response.json();
    return (data.success && data.beds) ? data.beds : [];
}

async function renderWardCards(beds, selectedWardId) {
    const container = document.getElementById('wardCardsContainer');
    patientContext = {};
    medsCache = {};
    let totalPatients = 0;

    if (!beds.length) {
        container.innerHTML = `<div class="card border-0 shadow-sm mb-4" style="border-radius:8px;border:1px solid #E2E8F0 !important;">
            <div class="card-body p-4 text-center" style="color:#64748B;font-size:13px;">
                No patients currently admitted in ${escHtml(selectedWardId ? getWardName(selectedWardId) : 'the selected wards')}.
            </div></div>`;
        updateCounters(0, 0);
        return;
    }

    const cardsHTML = [];
    const rendered = [];
    for (const bed of beds) {
        const ctx = await buildPatientContext(bed);
        if (bed.current_patient_id != null) patientContext[bed.current_patient_id] = ctx;
        totalPatients++;
        rendered.push(ctx);
        cardsHTML.push(renderPatientCard(ctx));
    }

    container.innerHTML = cardsHTML.join('');
    updateCounters(totalPatients, 0);
    rendered.forEach(fillClinicalData);
}

async function buildPatientContext(bed) {
    const pid = bed.current_patient_id;
    let summary = null;
    let visits = [];
    let vitalsList = [];
    try {
        const r = await fetch('/hms/backend/api/patients.php?action=summary&id=' + pid);
        const d = await r.json();
        if (d.success) summary = d.patient;
    } catch (e) { console.error('Patient summary error:', e); }
    try {
        const r = await fetch('/hms/backend/api/visits.php?patient_id=' + pid);
        const d = await r.json();
        if (d.success) visits = d.visits || [];
    } catch (e) { console.error('Patient visits error:', e); }
    const latestVisit = visits[0] || null;
    if (latestVisit) {
        try {
            const r = await fetch('/hms/backend/api/vitals.php?visit_id=' + latestVisit.id);
            const d = await r.json();
            if (d.success) vitalsList = d.vitals || [];
        } catch (e) { console.error('Patient vitals error:', e); }
    }
    return { bed: bed, summary: summary, visits: visits, latestVisit: latestVisit, vitals: vitalsList };
}

/* ============================================================
   CLINICAL PATIENT CARE & TREATMENT SHEET CARD (per patient)
   ============================================================ */
function renderPatientCard(ctx) {
    const bed = ctx.bed;
    const s = ctx.summary || {};
    const pid = bed.current_patient_id;
    const readings = ctx.vitals || [];
    const visit = ctx.latestVisit;

    const gridRows = readings.length
        ? readings.map(v => `
            <tr>
                <td class="font-weight-bold">${escHtml(formatDateTime(v.recorded_at))}</td>
                <td><span class="badge bg-success-subtle text-success border border-success px-2 py-1">${v.temperature != null ? v.temperature : '--'}</span></td>
                <td><span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1">${v.blood_pressure_systolic && v.blood_pressure_diastolic ? v.blood_pressure_systolic + '/' + v.blood_pressure_diastolic : '--'}</span></td>
                <td>${v.heart_rate != null ? v.heart_rate : '--'}</td>
                <td>${v.respiratory_rate != null ? v.respiratory_rate : '--'}</td>
                <td>${v.oxygen_saturation != null ? v.oxygen_saturation + '%' : '--'}</td>
                <td>${v.weight != null ? v.weight : '--'}</td>
                <td class="font-weight-bold text-secondary">${escHtml(v.nurse_name || '--')}</td>
            </tr>`).join('')
        : '<tr><td colspan="8" style="color:#94A3B8;padding:12px;">No vital signs recorded yet — click + RECORD VITALS to add the first reading.</td></tr>';

    const demoLine = `${escHtml(s.gender || '--')} | ${s.age != null ? escHtml(s.age) + ' Yrs' : '--'} | Genotype: -- | Blood Group: ${escHtml(s.blood_group || '--')}`;

    return `
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 8px; border: 1px solid #CBD5E1 !important;">

        <!-- CARD HEADER: WARD TITLE & PATIENT CONTROLS -->
        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0072BC" stroke-width="2"><path d="M2 4v16"></path><path d="M2 8h18a2 2 0 0 1 2 2v10"></path><path d="M2 17h20"></path><path d="M6 8v9"></path></svg>
                <span class="font-weight-bold text-dark" style="font-size: 14px;">${escHtml(bed.ward_name || 'Ward')}</span>
                <span class="badge bg-secondary ms-2">BED ${escHtml(bed.bed_number)}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 font-weight-bold px-2 py-1" style="font-size: 11px;" onclick="openTransferModal(${pid})">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
                    Change Bed / Transfer Patient
                </button>
                <button class="btn btn-sm btn-outline-info text-dark d-flex align-items-center gap-1 font-weight-bold px-2 py-1" style="font-size: 11px;" onclick="window.loadModuleTab && window.loadModuleTab('patient_records')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    Medical Records
                </button>
            </div>
        </div>

        <div class="card-body p-3">

            <!-- PATIENT ID & NAME HEADER WITH SVG AVATAR -->
            <div class="p-3 mb-3 rounded d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #F1F5F9; border: 1px solid #CBD5E1;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px; background-color: #0F2D59; flex-shrink: 0;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h6 class="mb-0 font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 16px;">${escHtml(s.full_name || bed.patient_name || 'PATIENT')}</h6>
                            <span class="badge bg-primary px-2" style="font-size: 10px;">ID: ${escHtml(s.hospital_number || '--')}</span>
                        </div>
                        <small class="text-muted">${demoLine}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-info text-white font-weight-bold" style="font-size: 11px;">Blood Donation :- NA</span>
                    <span class="badge bg-warning text-dark font-weight-bold" style="font-size: 11px;">(Currently Not On Oxygen)</span>
                </div>
            </div>

            <!-- VITAL SIGNS READINGS -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2 flex-wrap">
                    <h6 class="font-weight-bold text-uppercase mb-0 d-flex align-items-center gap-2" style="color: #0F2D59; font-size: 13px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0072BC" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                        Vital Signs Readings
                    </h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-light border px-3 font-weight-bold" style="font-size: 12px; color: #0072BC;" onclick="openVitalsHistory(${pid}, 'history')">Vitals History</button>
                        <button class="btn btn-sm btn-primary font-weight-bold px-3" onclick="openRecordVitalsModal(${pid})" style="background-color: #0072BC; border-radius: 4px;">+ RECORD VITALS</button>
                    </div>
                </div>
                <div class="table-responsive rounded border">
                    <table class="table table-bordered mb-0 text-center align-middle" style="font-size: 12px;">
                        <thead class="text-uppercase" style="background-color: #E2E8F0; color: #0F2D59;">
                            <tr>
                                <th>DATE / TIME</th>
                                <th>TEMP (°C)</th>
                                <th>BP (MMHG)</th>
                                <th>HR (BPM)</th>
                                <th>RR (BPM)</th>
                                <th>SPO2 (%)</th>
                                <th>WEIGHT (KG)</th>
                                <th>NURSE</th>
                            </tr>
                        </thead>
                        <tbody>${gridRows}</tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 1: PRESCRIBED MEDICATION -->
            <div class="mb-4">
                <h6 class="font-weight-bold text-uppercase mb-2 d-flex align-items-center gap-2" style="color: #0F2D59; font-size: 13px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2"><rect x="6" y="7" width="12" height="14" rx="2"></rect><path d="M9 3h6v4H9z"></path></svg>
                    Prescribed Medication
                </h6>
                <div class="table-responsive rounded border">
                    <table class="table table-bordered mb-0 align-middle" style="font-size: 12px;">
                        <thead class="bg-light text-uppercase" style="font-size: 11px;">
                            <tr>
                                <th>DRUG NAME &amp; STRENGTH</th>
                                <th>DOSAGE</th>
                                <th>FREQUENCY</th>
                                <th>DURATION</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody id="medsBody_${pid}">
                            <tr><td colspan="5" style="text-align:center;padding:12px;color:#94A3B8;">Loading prescriptions...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 2: NURSES / MIDWIVES NOTES -->
            <div class="mb-4">
                <h6 class="font-weight-bold text-uppercase mb-2 d-flex align-items-center gap-2" style="color: #0F2D59; font-size: 13px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0EA5E9" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    Nurses / Midwives Notes
                </h6>
                <div class="d-flex gap-2 mb-2 align-items-end flex-wrap">
                    <div style="flex:1;min-width:260px;">
                        <textarea id="txtNurseNotes_${pid}" class="form-control form-control-sm" rows="2" placeholder="Add a nurse / midwife note for this patient..."></textarea>
                    </div>
                    <button class="btn btn-sm btn-primary font-weight-bold px-3" style="background-color:#0072BC;" onclick="saveNurseNote(${pid})">Save Note Entry</button>
                </div>
                <div id="nurseNotesList_${pid}">
                    <p style="color:#94A3B8;font-size:12px;margin:0;">No nurse notes recorded yet.</p>
                </div>
            </div>

            <!-- SECTION 3: TREATMENT SHEET -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2 flex-wrap">
                    <h6 class="font-weight-bold text-uppercase mb-0 d-flex align-items-center gap-2" style="color: #0F2D59; font-size: 13px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        Treatment Sheet
                    </h6>
                    <button class="btn btn-sm btn-outline-primary font-weight-bold px-3 py-1" style="font-size: 11px;" onclick="openAddTreatment(${pid})">+ Log Treatment</button>
                </div>
                <div class="table-responsive rounded border">
                    <table class="table table-striped table-bordered mb-0 align-middle" style="font-size: 12px;">
                        <thead class="text-uppercase" style="background-color:#E2E8F0;font-size:11px;">
                            <tr>
                                <th>DATE / TIME</th>
                                <th>TREATMENT / PROCEDURE</th>
                                <th>NOTES</th>
                                <th>STAFF</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody id="treatmentsList_${pid}">
                            <tr><td colspan="5" style="text-align:center;padding:12px;color:#94A3B8;">No treatments logged yet — click + Log Treatment to add the first entry.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 4: BILLING SUMMARY -->
            <div class="mb-4">
                <h6 class="font-weight-bold text-uppercase mb-2 d-flex align-items-center gap-2" style="color: #0F2D59; font-size: 13px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#B45309" stroke-width="2"><path d="M20 6H9a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1z"></path><path d="M6 9H4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2"></path></svg>
                    Billing Summary
                </h6>
                <div class="table-responsive rounded border mb-2">
                    <table class="table table-bordered mb-0 align-middle" style="font-size: 12px;">
                        <thead class="bg-light text-uppercase" style="font-size: 11px;">
                            <tr>
                                <th>INVOICE NO</th>
                                <th>DATE</th>
                                <th>STATUS</th>
                                <th class="text-end">NET (GHS)</th>
                            </tr>
                        </thead>
                        <tbody id="billingBody_${pid}">
                            <tr><td colspan="4" style="text-align:center;padding:12px;color:#94A3B8;">Loading billing...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive rounded border mb-2">
                    <table class="table table-bordered mb-0 align-middle" style="font-size: 12px;">
                        <thead class="text-uppercase" style="background-color:#E2E8F0;font-size:11px;">
                            <tr>
                                <th>ITEM</th>
                                <th>DESCRIPTION</th>
                                <th>QTY</th>
                                <th>UNIT (GHS)</th>
                                <th class="text-end">TOTAL (GHS)</th>
                            </tr>
                        </thead>
                        <tbody id="billingItemsBody_${pid}">
                            <tr><td colspan="5" style="text-align:center;padding:12px;color:#94A3B8;">Loading billing items...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="billingTotals_${pid}" class="p-2 rounded d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color:#F8FAFC;border:1px solid #E2E8F0;font-size:12px;">
                    <span class="text-muted">Auto-generated from the patient's invoices &amp; billing items</span>
                    <span class="font-weight-bold text-danger">Fee To Be Paid: <span class="text-success" id="billingFee_${pid}">GHS 0.00</span></span>
                </div>
            </div>

            ${visit ? '' : '<p style="font-size:11px;color:#B45309;">No active visit on record — medication and billing sections may be empty.</p>'}

        </div>

        <!-- CARD FOOTER: SAVE ACTIONS -->
        <div class="card-footer bg-white border-top p-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span style="font-size: 11px; color: #64748B;">Logging as: <strong>${escHtml(CURRENT_VITALS_STAFF)}</strong> · Last saved: <span id="lastSaved_${pid}">--</span></span>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary font-weight-bold px-3" style="font-size: 12px;" onclick="saveSheet(${pid}, 'draft')">Save to Draft</button>
                <button class="btn btn-sm btn-success font-weight-bold px-4" style="font-size: 12px;" onclick="saveSheet(${pid}, 'final')">Save or Update</button>
            </div>
        </div>

    </div>`;
}

/* ---------- Async fill-in of section data (meds + billing) ---------- */
function fillClinicalData(ctx) {
    const pid = ctx.bed.current_patient_id;
    if (ctx.latestVisit) loadPrescriptions(ctx.latestVisit.id, pid);
    if (ctx.summary && ctx.summary.hospital_number) loadBilling(ctx.summary.hospital_number, pid);
    renderNurseNotes(pid);
    renderTreatments(pid);
    renderLastSaved(pid);
}

/* ============================ PRESCRIBED MEDICATION ============================ */
async function loadPrescriptions(visitId, pid) {
    const tbody = document.getElementById('medsBody_' + pid);
    if (!tbody) return;
    try {
        const r = await fetch('/hms/backend/api/prescriptions.php?visit_id=' + visitId);
        const d = await r.json();
        medsCache[pid] = (d.success && d.prescriptions) ? d.prescriptions : [];
    } catch (e) {
        console.error('Prescriptions load error:', e);
        medsCache[pid] = [];
    }
    renderMedications(pid);
}

function readRxAdmin(pid) {
    try { const raw = localStorage.getItem('hms:rx-admin-' + pid); return raw ? JSON.parse(raw) : []; }
    catch (e) { return []; }
}

function renderMedications(pid) {
    const tbody = document.getElementById('medsBody_' + pid);
    if (!tbody) return;
    const list = medsCache[pid] || [];
    if (!list.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:12px;color:#94A3B8;">No prescriptions recorded for the latest visit yet.</td></tr>';
        return;
    }
    const administered = readRxAdmin(pid);
    tbody.innerHTML = list.map(rx => {
        const given = administered.some(x => String(x) === String(rx.id));
        const baseStatus = rx.status === 'dispensed' ? 'dispensed' : (rx.status === 'cancelled' ? 'cancelled' : 'pending');
        const effective = given ? 'given' : baseStatus;
        const badge = effective === 'given' || effective === 'dispensed'
            ? '<span class="badge bg-success">Administered</span>'
            : effective === 'cancelled'
                ? '<span class="badge bg-danger">Cancelled</span>'
                : '<span class="badge bg-warning text-dark">Pending</span>';
        const action = effective === 'pending'
            ? `<button class="btn btn-sm btn-outline-primary py-0 px-2 font-weight-bold" style="font-size:11px;" onclick="giveDose(${pid}, ${rx.id})">Give Dose</button>`
            : '';
        return '<tr>' +
            '<td class="fw-bold">' + escHtml(rx.drug_name || '--') +
                (rx.generic_name ? ' <span class="text-muted" style="font-weight:400;">(' + escHtml(rx.generic_name) + ')</span>' : '') +
                (rx.doctor_name ? '<br><small class="text-muted">Dr. ' + escHtml(rx.doctor_name) + '</small>' : '') +
            '</td>' +
            '<td>' + escHtml(rx.dosage || '--') + '</td>' +
            '<td>' + escHtml(rx.frequency || '--') + '</td>' +
            '<td>' + escHtml(rx.duration || '--') + '</td>' +
            '<td>' + badge + ' ' + action + '</td>' +
        '</tr>';
    }).join('');
}

function giveDose(pid, rxId) {
    const list = readRxAdmin(pid);
    if (!list.some(x => String(x) === String(rxId))) list.push(String(rxId));
    try { localStorage.setItem('hms:rx-admin-' + pid, JSON.stringify(list)); } catch (e) {}
    showAlert('Dose marked as administered (browser log).', 'success');
    renderMedications(pid);
}

/* ============================ NURSES / MIDWIVES NOTES ============================ */
function sheetGet(kind, pid) {
    try { const raw = localStorage.getItem('hms:' + kind + '-' + pid); return raw ? JSON.parse(raw) : []; }
    catch (e) { return []; }
}

function sheetSet(kind, pid, arr) {
    try { localStorage.setItem('hms:' + kind + '-' + pid, JSON.stringify(arr)); } catch (e) {}
}

function saveNurseNote(pid) {
    const ta = document.getElementById('txtNurseNotes_' + pid);
    if (!ta) return;
    const text = ta.value.trim();
    if (!text) { showAlert('Type a note before saving.', 'error'); return; }
    const notes = sheetGet('nurse-notes', pid);
    notes.push({ text: text, by: CURRENT_VITALS_STAFF, at: new Date().toISOString() });
    sheetSet('nurse-notes', pid, notes);
    ta.value = '';
    renderNurseNotes(pid);
    showAlert('Note entry saved.', 'success');
}

function renderNurseNotes(pid) {
    const holder = document.getElementById('nurseNotesList_' + pid);
    if (!holder) return;
    const notes = sheetGet('nurse-notes', pid);
    if (!notes.length) {
        holder.innerHTML = '<p style="color:#94A3B8;font-size:12px;margin:0;">No nurse notes recorded yet.</p>';
        return;
    }
    holder.innerHTML = notes.slice().reverse().map(n => `
        <div class="p-2 mb-2 rounded" style="background:#F1F5F9;border:1px solid #E2E8F0;font-size:12px;">
            <div style="white-space:pre-wrap;color:#334155;">${escHtml(n.text)}</div>
            <div class="text-muted" style="font-size:11px;margin-top:4px;">— ${escHtml(n.by || 'Staff')} · ${escHtml(fmtDateTime(n.at))}</div>
        </div>`).join('');
}

/* ============================ TREATMENT SHEET ============================ */
function openAddTreatment(pid) {
    document.getElementById('treatment-patient-id').value = pid;
    document.getElementById('add-treatment-form').reset();
    document.getElementById('add-treatment-modal').classList.add('show');
}

function closeAddTreatment() {
    document.getElementById('add-treatment-modal').classList.remove('show');
}

async function handleAddTreatmentSubmit(e) {
    e.preventDefault();
    const pid = document.getElementById('treatment-patient-id').value;
    const name = document.getElementById('treatment-name').value.trim();
    if (!pid || !name) { showAlert('Treatment name is required.', 'error'); return; }
    const list = sheetGet('treatments', pid);
    list.push({
        treatment: name,
        notes: document.getElementById('treatment-notes').value.trim(),
        status: document.getElementById('treatment-status').value,
        by: CURRENT_VITALS_STAFF,
        at: new Date().toISOString()
    });
    sheetSet('treatments', pid, list);
    closeAddTreatment();
    renderTreatments(pid);
    showAlert('Treatment logged on the sheet.', 'success');
}

function renderTreatments(pid) {
    const tbody = document.getElementById('treatmentsList_' + pid);
    if (!tbody) return;
    const list = sheetGet('treatments', pid);
    if (!list.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:12px;color:#94A3B8;">No treatments logged yet — click + Log Treatment to add the first entry.</td></tr>';
        return;
    }
    const statusBadge = s => s === 'Done'
        ? '<span class="badge bg-success">Done</span>'
        : s === 'Failed'
            ? '<span class="badge bg-danger">Failed</span>'
            : '<span class="badge bg-warning text-dark">' + escHtml(s || 'Pending') + '</span>';
    tbody.innerHTML = list.slice().reverse().map(t => `
        <tr>
            <td class="fw-bold">${escHtml(fmtDateTime(t.at))}</td>
            <td class="fw-bold">${escHtml(t.treatment)}</td>
            <td style="white-space:pre-wrap;">${escHtml(t.notes || '--')}</td>
            <td>${escHtml(t.by || '--')}</td>
            <td>${statusBadge(t.status)}</td>
        </tr>`).join('');
}

/* ============================ BILLING SUMMARY ============================ */
async function loadBilling(hospitalNumber, pid) {
    const body = document.getElementById('billingBody_' + pid);
    const itemsBody = document.getElementById('billingItemsBody_' + pid);
    const feeEl = document.getElementById('billingFee_' + pid);
    if (!body || !itemsBody || !hospitalNumber) return;
    try {
        const r = await fetch('/hms/backend/api/invoices.php?q=' + encodeURIComponent(hospitalNumber));
        const d = await r.json();
        const invoices = (d.success && d.invoices) ? d.invoices : [];
        if (!invoices.length) {
            body.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:12px;color:#94A3B8;">No invoices yet for this patient.</td></tr>';
            itemsBody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:12px;color:#94A3B8;">No billing items yet.</td></tr>';
            if (feeEl) feeEl.textContent = 'GHS 0.00';
            return;
        }
        const details = await Promise.all(invoices.map(inv =>
            fetch('/hms/backend/api/invoices.php?action=detail&id=' + inv.id)
                .then(x => x.json())
                .then(dd => (dd.success ? dd.items : []))
                .catch(() => [])
        ));

        const statusBadge = s => s === 'paid'
            ? '<span class="badge bg-success">PAID</span>'
            : s === 'pending' || s === 'partial'
                ? '<span class="badge bg-warning text-dark">' + escHtml(String(s).toUpperCase()) + '</span>'
                : s === 'cancelled'
                    ? '<span class="badge bg-danger">CANCELLED</span>'
                    : '<span class="badge bg-secondary">' + escHtml(String(s || '').toUpperCase()) + '</span>';

        body.innerHTML = invoices.map(inv => `
            <tr>
                <td class="fw-bold text-primary">${escHtml(inv.invoice_number)}</td>
                <td>${escHtml(fmtDateTime(inv.created_at))}</td>
                <td>${statusBadge(inv.status)}</td>
                <td class="text-end fw-bold">GHS ${Number(inv.net_amount || 0).toFixed(2)}</td>
            </tr>`).join('');

        let paid = 0, due = 0;
        invoices.forEach(inv => {
            const net = Number(inv.net_amount || 0);
            if (inv.status === 'paid') paid += net;
            else if (inv.status === 'pending' || inv.status === 'partial') due += net;
        });

        const allItems = [];
        invoices.forEach((inv, idx) => (details[idx] || []).forEach(it => {
            allItems.push({ invoice: inv.invoice_number, it: it });
        }));
        itemsBody.innerHTML = allItems.length
            ? allItems.map(row => `
                <tr>
                    <td class="fw-bold">${escHtml(String(row.it.item_type || '').replace('_', ' ').toUpperCase())}</td>
                    <td>${escHtml(row.it.description || '--')}<br><small class="text-muted">${escHtml(row.invoice)}</small></td>
                    <td>${escHtml(row.it.quantity != null ? row.it.quantity : '1')}</td>
                    <td>GHS ${Number(row.it.unit_price || 0).toFixed(2)}</td>
                    <td class="text-end fw-bold">GHS ${Number(row.it.total_price || 0).toFixed(2)}</td>
                </tr>`).join('')
            : '<tr><td colspan="5" style="text-align:center;padding:12px;color:#94A3B8;">No line items on these invoices.</td></tr>';

        if (feeEl) feeEl.textContent = 'GHS ' + due.toFixed(2);
    } catch (e) {
        console.error('Billing load error:', e);
        body.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:12px;color:#C0392B;">Failed to load billing.</td></tr>';
    }
}

/* ============================ CHANGE BED / TRANSFER PATIENT ============================ */
function openTransferModal(pid) {
    const ctx = patientContext[pid];
    if (!ctx) return;
    document.getElementById('transfer-patient-id').value = pid;
    document.getElementById('transfer-patient-info').textContent =
        'Transferring: ' + (ctx.summary ? ctx.summary.full_name : ctx.bed.patient_name) +
        ' · Current: ' + (ctx.bed.ward_name || 'Ward') + ' / Bed ' + ctx.bed.bed_number;
    const wardSel = document.getElementById('transfer-ward');
    wardSel.innerHTML = '<option value="">Select Ward</option>' +
        wardsData.map(w => `<option value="${w.id}">${escHtml(w.ward_name)}</option>`).join('');
    document.getElementById('transfer-bed').innerHTML = '<option value="">Select a ward first</option>';
    document.getElementById('transfer-bed-modal').classList.add('show');
}

async function loadTransferBeds() {
    const wardId = document.getElementById('transfer-ward').value;
    const bedSel = document.getElementById('transfer-bed');
    const pid = document.getElementById('transfer-patient-id').value;
    const ctx = patientContext[pid];
    if (!wardId) { bedSel.innerHTML = '<option value="">Select a ward first</option>'; return; }
    bedSel.innerHTML = '<option value="">Loading beds...</option>';
    try {
        const r = await fetch('/hms/backend/api/beds.php?status=Available&ward_id=' + encodeURIComponent(wardId));
        const d = await r.json();
        const beds = (d.success && d.beds) ? d.beds : [];
        const currentBedId = ctx ? ctx.bed.id : null;
        const opts = beds.filter(b => String(b.id) !== String(currentBedId))
            .map(b => `<option value="${b.id}">Bed ${escHtml(b.bed_number)} — ${escHtml(b.ward_name)}</option>`).join('');
        bedSel.innerHTML = opts
            ? '<option value="">Select available bed</option>' + opts
            : '<option value="">No beds available in this ward</option>';
    } catch (e) {
        console.error('Transfer beds load error:', e);
        bedSel.innerHTML = '<option value="">Failed to load beds</option>';
    }
}

async function handleTransferSubmit(e) {
    e.preventDefault();
    const pid = document.getElementById('transfer-patient-id').value;
    const bedId = document.getElementById('transfer-bed').value;
    if (!pid || !bedId) { showAlert('Select a destination bed first.', 'error'); return; }
    const btn = document.getElementById('confirm-transfer-btn');
    btn.disabled = true;
    btn.textContent = 'Transferring...';
    try {
        const r = await fetch('/hms/backend/api/beds.php?action=assign', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ bed_id: bedId, patient_id: pid })
        });
        const d = await r.json();
        if (d.success) {
            showAlert('Patient transferred to the new bed successfully.', 'success');
            document.getElementById('transfer-bed-modal').classList.remove('show');
            await filterVitalsByWard();
        } else {
            showAlert(d.error || 'Transfer failed', 'error');
        }
    } catch (e2) {
        console.error('Transfer error:', e2);
        showAlert('Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Transfer Patient';
    }
}

/* ============================ SAVE TO DRAFT / SAVE OR UPDATE ============================ */
function saveSheet(pid, mode) {
    const ta = document.getElementById('txtNurseNotes_' + pid);
    if (ta && ta.value.trim()) {
        const notes = sheetGet('nurse-notes', pid);
        notes.push({ text: ta.value.trim(), by: CURRENT_VITALS_STAFF, at: new Date().toISOString() });
        sheetSet('nurse-notes', pid, notes);
        ta.value = '';
    }
    const key = 'hms:sheet-' + (mode === 'final' ? 'final' : 'draft') + '-' + pid;
    const payload = {
        notes: sheetGet('nurse-notes', pid),
        treatments: sheetGet('treatments', pid),
        medsAdmin: sheetGet('rx-admin', pid),
        savedBy: CURRENT_VITALS_STAFF,
        at: new Date().toISOString()
    };
    try { localStorage.setItem(key, JSON.stringify(payload)); } catch (e) {}
    renderNurseNotes(pid);
    renderLastSaved(pid);
    if (mode === 'final') {
        showAlert('Treatment sheet saved & updated — logged by ' + CURRENT_VITALS_STAFF + '.', 'success');
    } else {
        showAlert('Sheet saved as draft (this browser). Click Save or Update to finalise.', 'info');
    }
}

function renderLastSaved(pid) {
    const el = document.getElementById('lastSaved_' + pid);
    if (!el) return;
    let at = null;
    try {
        const raw = localStorage.getItem('hms:sheet-final-' + pid);
        if (raw) at = JSON.parse(raw).at;
    } catch (e) {}
    el.textContent = at ? fmtDateTime(at) : '--';
}

function updateCounters(totalPatients, partialDischarged) {
    document.getElementById('statTotalPatients').textContent = totalPatients;
    document.getElementById('statPartialDischarged').textContent = partialDischarged;
}

function gotoLabModule() {
    if (window.loadModuleTab) window.loadModuleTab('investigations');
}

/* ============================ MODAL: RECORD / EDIT VITALS ============================ */
function openVitalsModal() {
    const form = document.getElementById('vitals-form');
    form.reset();
    document.getElementById('editing-vitals-id').value = '';
    document.getElementById('vitals-modal-title').textContent = 'Record Vital Signs';
    document.getElementById('vitals-submit-btn').textContent = 'Save Vitals';
    document.getElementById('vitals-save-status').textContent = 'Draft auto-save enabled — in-progress entries are kept if the page or tab closes unexpectedly.';
    checkDraftBanner();
    document.getElementById('vitals-modal').classList.add('show');
}

function openRecordVitalsModal(pid) {
    openVitalsModal();
    if (pid != null && patientContext[pid] && patientContext[pid].latestVisit) {
        ensureVisitOption(patientContext[pid].latestVisit, true);
    }
}

function closeVitalsModal() {
    document.getElementById('vitals-modal').classList.remove('show');
}

async function openVitalsHistory(pid, mode) {
    const ctx = patientContext[pid];
    if (!ctx || !ctx.latestVisit) {
        showAlert('No active visit found for this patient', 'error');
        return;
    }
    const visit = ctx.latestVisit;
    const head = (mode === 'graph') ? 'Vitals Trend' : 'Vitals History';
    document.getElementById('vitals-history-title').textContent = head + ' — ' + (ctx.summary ? ctx.summary.full_name : ctx.bed.patient_name);
    document.getElementById('vitals-history-sub').textContent = 'Visit ' + visit.visit_number;
    document.getElementById('vitals-history-modal').classList.add('show');
    const tbody = document.getElementById('vitals-history-body');
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:14px;color:#64748B;">Loading vitals...</td></tr>';
    try {
        const r = await fetch('/hms/backend/api/vitals.php?visit_id=' + visit.id);
        const d = await r.json();
        const rows = (d.success && d.vitals) ? d.vitals : [];
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:14px;color:#94A3B8;">No vital signs recorded for this visit yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(v => `
            <tr>
                <td style="padding:6px 8px;border:1px solid #E2E8F0;white-space:nowrap;">${escHtml(formatDateTime(v.recorded_at))}</td>
                <td style="padding:6px 8px;border:1px solid #E2E8F0;">${v.temperature != null ? v.temperature + '°C' : '-'}</td>
                <td style="padding:6px 8px;border:1px solid #E2E8F0;">${v.blood_pressure_systolic && v.blood_pressure_diastolic ? v.blood_pressure_systolic + '/' + v.blood_pressure_diastolic : '-'}</td>
                <td style="padding:6px 8px;border:1px solid #E2E8F0;">${v.heart_rate || '-'}</td>
                <td style="padding:6px 8px;border:1px solid #E2E8F0;">${v.respiratory_rate || '-'}</td>
                <td style="padding:6px 8px;border:1px solid #E2E8F0;">${v.oxygen_saturation != null ? v.oxygen_saturation + '%' : '-'}</td>
                <td style="padding:6px 8px;border:1px solid #E2E8F0;">${v.weight != null ? v.weight + 'kg' : '-'}</td>
                <td style="padding:6px 8px;border:1px solid #E2E8F0;">${escHtml(v.notes || '')}</td>
                <td style="padding:6px 8px;border:1px solid #E2E8F0;text-align:center;">
                    <button class="btn btn-sm btn-primary" style="background-color:#0072BC;color:#fff;font-size:11px;padding:3px 10px;border-radius:4px;border:none;cursor:pointer;" onclick="editVitals(${v.id}, ${pid})">Edit</button>
                </td>
            </tr>`).join('');
    } catch (error) {
        console.error('Vitals history error:', error);
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:14px;color:#C0392B;">Failed to load vitals history.</td></tr>';
    }
}

async function editVitals(vitalId, pid) {
    const ctx = patientContext[pid];
    if (!ctx || !ctx.latestVisit) {
        showAlert('Patient visit context not found', 'error');
        return;
    }
    let vital = null;
    try {
        const r = await fetch('/hms/backend/api/vitals.php?visit_id=' + ctx.latestVisit.id);
        const d = await r.json();
        if (d.success && d.vitals) vital = d.vitals.find(v => String(v.id) === String(vitalId));
    } catch (e) { console.error('Vitals fetch for edit error:', e); }
    if (!vital) {
        showAlert('Vital record not found', 'error');
        return;
    }

    document.getElementById('vitals-history-modal').classList.remove('show');

    openVitalsModal();
    ensureVisitOption({ id: vital.visit_id, visit_number: vital.visit_number, patient_name: vital.patient_name }, true);
    document.getElementById('vitals-visit').value = String(vital.visit_id);
    document.getElementById('vitals-temp').value = vital.temperature != null ? vital.temperature : '';
    document.getElementById('vitals-bp-sys').value = vital.blood_pressure_systolic != null ? vital.blood_pressure_systolic : '';
    document.getElementById('vitals-bp-dia').value = vital.blood_pressure_diastolic != null ? vital.blood_pressure_diastolic : '';
    document.getElementById('vitals-hr').value = vital.heart_rate != null ? vital.heart_rate : '';
    document.getElementById('vitals-rr').value = vital.respiratory_rate != null ? vital.respiratory_rate : '';
    document.getElementById('vitals-spo2').value = vital.oxygen_saturation != null ? vital.oxygen_saturation : '';
    document.getElementById('vitals-weight').value = vital.weight != null ? vital.weight : '';
    document.getElementById('vitals-height').value = vital.height != null ? vital.height : '';
    document.getElementById('vitals-notes').value = vital.notes || '';
    document.getElementById('editing-vitals-id').value = vital.id;
    document.getElementById('vitals-modal-title').textContent = 'Update Vital Signs';
    document.getElementById('vitals-submit-btn').textContent = 'Update Vitals';
    document.getElementById('vitals-save-status').textContent = 'Editing record #' + vital.id + ' — changes update the existing entry.';
    document.getElementById('vitals-draft-banner').style.display = 'none';
}

/* ============================ AUTO-DRAFT (localStorage) ============================ */
const DRAFT_FIELDS = {
    visit_id: 'vitals-visit',
    temperature: 'vitals-temp',
    blood_pressure_systolic: 'vitals-bp-sys',
    blood_pressure_diastolic: 'vitals-bp-dia',
    heart_rate: 'vitals-hr',
    respiratory_rate: 'vitals-rr',
    oxygen_saturation: 'vitals-spo2',
    weight: 'vitals-weight',
    height: 'vitals-height',
    notes: 'vitals-notes'
};

function setupDraftAutosave() {
    Object.values(DRAFT_FIELDS).forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', scheduleDraftSave);
        el.addEventListener('change', scheduleDraftSave);
    });
}

function scheduleDraftSave() {
    clearTimeout(draftTimer);
    draftTimer = setTimeout(saveDraft, 400);
}

function saveDraft() {
    const data = {};
    Object.entries(DRAFT_FIELDS).forEach(([key, id]) => {
        const el = document.getElementById(id);
        if (el) data[key] = el.value;
    });
    const visitSel = document.getElementById('vitals-visit');
    if (visitSel && visitSel.selectedOptions && visitSel.selectedOptions.length) {
        data.patient_name = visitSel.selectedOptions[0].text;
    }
    data.savedAt = new Date().toISOString();
    if (Object.keys(data).every(k => k === 'savedAt' || data[k] === '')) return; // nothing typed yet
    try { localStorage.setItem(DRAFT_KEY, JSON.stringify(data)); } catch (e) { console.warn('Draft save failed:', e); }
    const st = document.getElementById('vitals-save-status');
    if (st) st.textContent = 'Draft auto-saved ' + new Date().toLocaleTimeString() + ' — safe to close the page.';
}

function readDraft() {
    try {
        const raw = localStorage.getItem(DRAFT_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch (e) { return null; }
}

function checkDraftBanner() {
    const draft = readDraft();
    const banner = document.getElementById('vitals-draft-banner');
    if (!banner) return;
    if (draft) {
        banner.style.display = 'flex';
        document.getElementById('vitals-draft-info').textContent =
            'Unsaved draft from ' + formatTime(draft.savedAt) + (draft.patient_name ? ' — ' + draft.patient_name : '') + '.';
    } else {
        banner.style.display = 'none';
    }
}

function restoreDraft() {
    const draft = readDraft();
    if (!draft) return;
    Object.entries(DRAFT_FIELDS).forEach(([key, id]) => {
        const el = document.getElementById(id);
        if (!el) return;
        if (key === 'visit_id') {
            if (draft[key]) ensureVisitOption({ id: draft[key], visit_number: '', patient_name: draft.patient_name || '' }, true);
        } else if (draft[key] != null && draft[key] !== '') {
            el.value = draft[key];
        }
    });
    checkDraftBanner();
    showAlert('Draft restored. Review the values and Save.', 'success');
}

function discardDraft() {
    discardDraftSilently();
    showAlert('Draft discarded.', 'info');
}

function discardDraftSilently() {
    try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
    const banner = document.getElementById('vitals-draft-banner');
    if (banner) banner.style.display = 'none';
    const st = document.getElementById('vitals-save-status');
    if (st) st.textContent = 'Draft auto-save enabled — in-progress entries are kept if the page or tab closes unexpectedly.';
}

function checkDraftOnLoad() {
    const draft = readDraft();
    if (draft) {
        showAlert('An unsaved vitals draft from ' + formatTime(draft.savedAt) + ' was found. Click Record Vitals to restore it.', 'warning');
    }
}

function formatTime(iso) {
    if (!iso) return 'earlier';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return 'earlier';
    return d.toLocaleString();
}

/* ============================ SAVE / UPDATE SUBMIT (vitals) ============================ */
async function handleVitalsSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const editingId = document.getElementById('editing-vitals-id').value;
    const url = editingId ? `/hms/backend/api/vitals.php?id=${editingId}` : '/hms/backend/api/vitals.php?action=create';
    const method = editingId ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();

        if (result.success) {
            showAlert(editingId ? 'Vital signs updated successfully' : 'Vital signs recorded successfully', 'success');
            discardDraftSilently();
            closeVitalsModal();
            await filterVitalsByWard();
        } else {
            showAlert(result.error || 'Failed to save vitals', 'error');
        }
    } catch (error) {
        console.error('Vitals save error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

/* ============================ EVENT LISTENERS ============================ */
function setupEventListeners() {
    const closeVitals = document.getElementById('close-vitals-modal');
    if (closeVitals) closeVitals.addEventListener('click', closeVitalsModal);
    const cancelVitals = document.getElementById('cancel-vitals');
    if (cancelVitals) cancelVitals.addEventListener('click', closeVitalsModal);
    const form = document.getElementById('vitals-form');
    if (form) form.addEventListener('submit', handleVitalsSubmit);

    const closeHistory = document.getElementById('close-vitals-history-modal');
    if (closeHistory) closeHistory.addEventListener('click', () => document.getElementById('vitals-history-modal').classList.remove('show'));
    const cancelHistory = document.getElementById('cancel-vitals-history');
    if (cancelHistory) cancelHistory.addEventListener('click', () => document.getElementById('vitals-history-modal').classList.remove('show'));

    const closeTransfer = document.getElementById('close-transfer-bed-modal');
    if (closeTransfer) closeTransfer.addEventListener('click', () => document.getElementById('transfer-bed-modal').classList.remove('show'));
    const cancelTransfer = document.getElementById('cancel-transfer-bed');
    if (cancelTransfer) cancelTransfer.addEventListener('click', () => document.getElementById('transfer-bed-modal').classList.remove('show'));
    const transferWard = document.getElementById('transfer-ward');
    if (transferWard) transferWard.addEventListener('change', loadTransferBeds);
    const transferForm = document.getElementById('transfer-bed-form');
    if (transferForm) transferForm.addEventListener('submit', handleTransferSubmit);

    const closeTreatment = document.getElementById('close-add-treatment-modal');
    if (closeTreatment) closeTreatment.addEventListener('click', closeAddTreatment);
    const cancelTreatment = document.getElementById('cancel-add-treatment');
    if (cancelTreatment) cancelTreatment.addEventListener('click', closeAddTreatment);
    const treatmentForm = document.getElementById('add-treatment-form');
    if (treatmentForm) treatmentForm.addEventListener('submit', handleAddTreatmentSubmit);
}

/* ============================ HELPERS ============================ */
function escHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function escJs(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

function fmtDateTime(dateString) {
    if (!dateString) return '--';
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return String(dateString);
    const p = n => String(n).padStart(2, '0');
    return p(d.getDate()) + '/' + p(d.getMonth() + 1) + '/' + d.getFullYear() + ' ' + p(d.getHours()) + ':' + p(d.getMinutes());
}

function formatDateTime(dateString) {
    return fmtDateTime(dateString);
}
</script>