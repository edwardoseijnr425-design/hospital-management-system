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
#vitals-page .ms-1{margin-left:.25rem !important}
#vitals-page .ms-2{margin-left:.5rem !important}
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
#vitals-page .flex-wrap{flex-wrap:wrap}
#vitals-page .gap-2{gap:.5rem}
#vitals-page .gap-4{gap:1.5rem}
#vitals-page .gap-5{gap:3rem}
#vitals-page .d-flex{display:flex}
#vitals-page .form-label{display:inline-block;margin-bottom:.5rem;font-family:inherit}
#vitals-page .form-select{display:block;width:100%;padding:.375rem .75rem;font-size:12px;line-height:1.5;color:#334155;background-color:#fff;border:1px solid #CBD5E1;border-radius:4px}
#vitals-page .form-select-sm{padding:.25rem .5rem;font-size:12px}
#vitals-page .text-center{text-align:center}
#vitals-page .text-uppercase{text-transform:uppercase}
#vitals-page .font-weight-bold{font-weight:700}
#vitals-page .text-dark{color:#0F172A !important}
#vitals-page .text-muted{color:#64748B !important}
#vitals-page .text-secondary{color:#64748B !important}
#vitals-page .text-white{color:#fff !important}
#vitals-page .text-primary{color:#0072BC !important}
#vitals-page .text-success{color:#198754 !important}
#vitals-page .fs-5{font-size:1.25rem !important}
#vitals-page .bg-white{background-color:#fff !important}
#vitals-page .bg-dark{background-color:#1E293B !important}
#vitals-page .bg-secondary{background-color:#64748B !important}
#vitals-page .bg-warning{background-color:#F59E0B !important}
#vitals-page .bg-info{background-color:#0EA5E9 !important}
#vitals-page .bg-success-subtle{background-color:#D1E7DD !important}
#vitals-page .bg-primary-subtle{background-color:#CFE2FF !important}
#vitals-page .border{border:1px solid #E2E8F0 !important}
#vitals-page .border-top{border-top:1px solid #E2E8F0 !important}
#vitals-page .border-bottom{border-bottom:1px solid #E2E8F0 !important}
#vitals-page .border-secondary-subtle{border-color:#E2E8F0 !important}
#vitals-page .border-success{border-color:#198754 !important}
#vitals-page .border-primary{border-color:#0072BC !important}
#vitals-page .rounded{border-radius:.375rem !important}
#vitals-page .rounded-circle{border-radius:50% !important}
#vitals-page .table{width:100%;margin-bottom:1rem;color:#334155;border-collapse:collapse;font-size:12px}
#vitals-page .table-bordered{border:1px solid #E2E8F0}
#vitals-page .table-bordered > thead > tr > th{border:1px solid #E2E8F0;padding:8px 10px;vertical-align:middle}
#vitals-page .table-bordered > tbody > tr > td{border:1px solid #E2E8F0;padding:8px 10px;vertical-align:middle}
#vitals-page .table-responsive{overflow-x:auto}
#vitals-page .align-middle{vertical-align:middle}
#vitals-page .btn-light{background-color:#fff;color:#334155;border:1px solid #E2E8F0}
#vitals-page .btn-outline-primary{background-color:#fff;color:#0072BC;border:1px solid #0072BC}
#vitals-page .btn-outline-primary:hover{background-color:#0072BC;color:#fff}
</style>

<div class="container-fluid p-3" id="vitals-page" style="background-color: #F8FAFC; min-height: 100vh;">

  <!-- TOP FILTER HEADER PANEL (NO DEPARTMENT, NO PATIENT SELECTOR, NO LOCK BUTTON) -->
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

  <!-- PATIENT VITAL SIGNS CARDS (populated by JS — one card per patient, matches the Image 1 structure) -->
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

<!-- Patient Info Modal (medical summary / nursing notes / medication orders) -->
<div class="modal" id="patient-info-modal">
    <div class="modal-content" style="max-width:560px;">
        <div class="modal-header">
            <h3 id="patient-info-title">Patient Information</h3>
            <button class="modal-close" id="close-patient-info-modal">&times;</button>
        </div>
        <div class="modal-body" id="patient-info-body"></div>
        <div class="modal-footer" style="padding:12px 20px;border-top:1px solid #E2E8F0;text-align:right;">
            <button type="button" class="btn btn-secondary" id="cancel-patient-info">Close</button>
        </div>
    </div>
</div>

<script>
let wardsData = [];
let patientContext = {};   // patient_id -> { bed, summary, latestVisit, visits, vitals }
let draftTimer = null;
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
    for (const bed of beds) {
        const ctx = await buildPatientContext(bed);
        if (bed.current_patient_id != null) patientContext[bed.current_patient_id] = ctx;
        totalPatients++;
        cardsHTML.push(renderPatientCard(ctx));
    }

    container.innerHTML = cardsHTML.join('');
    updateCounters(totalPatients, 0);
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

/* Image-1 style patient card: ward header -> patient bar -> vitals readings grid -> record footer */
function renderPatientCard(ctx) {
    const bed = ctx.bed;
    const s = ctx.summary || {};
    const pid = bed.current_patient_id;
    const readings = ctx.vitals || [];

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

    return `
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 8px; border: 1px solid #E2E8F0 !important;">

        <!-- CARD WARD TITLE HEADER BAR -->
        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0072BC" stroke-width="2"><path d="M2 4v16"></path><path d="M2 8h18a2 2 0 0 1 2 2v10"></path><path d="M2 17h20"></path><path d="M6 8v9"></path></svg>
                <span class="font-weight-bold text-dark" style="font-size: 14px;">${escHtml(bed.ward_name || 'Ward')}</span>
            </div>
            <span class="text-muted" style="font-size: 11px;">No Shift Available.</span>
        </div>

        <!-- CARD BODY DETAILS -->
        <div class="card-body p-3">

            <!-- PATIENT HEADER BAR -->
            <div class="p-2 mb-3 rounded d-flex justify-content-between align-items-center flex-wrap" style="background-color: #F1F5F9; border: 1px solid #CBD5E1;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary font-weight-bold" style="font-size: 11px;">BED ${escHtml(bed.bed_number)}</span>
                    <span class="badge rounded-circle bg-warning text-dark font-weight-bold" style="width: 22px; height: 22px; line-height: 14px;">${readings.length}</span>
                    <span class="text-muted" style="font-size: 12px;">(Currently Not On Oxygen)</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-info text-white font-weight-bold" style="font-size: 11px;">Blood Group : ${escHtml(s.blood_group || 'NA')}</span>
                    <span class="font-weight-bold text-uppercase ms-2" style="font-size: 15px; color: #0F2D59;">${escHtml(s.full_name || bed.patient_name || 'PATIENT')}</span>
                    <button class="btn btn-sm btn-info text-white py-0 px-2 font-weight-bold" style="font-size: 11px;" title="Patient Medical Summary" onclick="openPatientSummary(${pid})">i</button>
                    <button class="btn btn-sm btn-primary py-0 px-2 font-weight-bold" style="font-size: 11px; background-color: #0072BC;" title="Record Vitals" onclick="openRecordVitalsModal(${pid})">AM</button>
                </div>
            </div>

            <!-- VITAL SIGNS READINGS GRID -->
            <div class="table-responsive rounded border mb-3">
                <table class="table table-bordered mb-0 text-center" style="font-size: 12px;">
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

        <!-- CARD FOOTER RECORD VITAL BUTTON -->
        <div class="card-footer bg-white border-top p-2 d-flex justify-content-end gap-2">
            <button class="btn btn-sm btn-light border px-3 font-weight-bold" style="font-size: 12px; color: #0072BC;" onclick="openVitalsHistory(${pid}, 'history')">Vitals History</button>
            <button class="btn btn-sm btn-primary font-weight-bold px-4" onclick="openRecordVitalsModal(${pid})" style="background-color: #0072BC; border-radius: 4px;">+ RECORD VITALS</button>
        </div>

    </div>`;
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

/* ============================ PATIENT INFO MODALS ============================ */
function openPatientSummary(pid) { openPatientInfo(pid, 'summary'); }
function openNursingNotes(pid) { openPatientInfo(pid, 'notes'); }
function openMedicationOrders(pid) { openPatientInfo(pid, 'meds'); }

function openPatientInfo(pid, mode) {
    const ctx = patientContext[pid];
    if (!ctx) return;
    const s = ctx.summary || {};
    let title = 'Patient Information';
    let html = '';
    if (mode === 'notes') {
        title = 'Nursing / Clinical Notes';
        const notes = ((s.vitals && s.vitals.notes) || s.consultation_notes || '').trim();
        html = notes
            ? '<p style="white-space:pre-wrap;font-size:13px;color:#334155;">' + escHtml(notes) + '</p>'
            : '<p style="color:#94A3B8;font-size:13px;">No clinical notes recorded yet.</p>';
    } else if (mode === 'meds') {
        title = 'Medication Orders';
        html = s.latest_rx
            ? '<p style="font-size:13px;color:#334155;"><strong>' + escHtml(s.latest_rx) + '</strong></p><p style="font-size:12px;color:#64748B;">Most recently prescribed medication.</p>'
            : '<p style="color:#94A3B8;font-size:13px;">No medication orders recorded yet.</p>';
    } else {
        title = 'Patient Medical Summary';
        html = '<div style="font-size:12px;color:#334155;line-height:1.8;">' +
            '<p><strong>Name :</strong> ' + escHtml(s.full_name || '--') + '</p>' +
            '<p><strong>Hospital No :</strong> ' + escHtml(s.hospital_number || '--') + '</p>' +
            '<p><strong>Age / Gender :</strong> ' + (s.age != null ? escHtml(s.age) + ' yrs' : '--') + ' / ' + escHtml(s.gender || '--') + '</p>' +
            '<p><strong>Blood Group :</strong> ' + escHtml(s.blood_group || '--') + '</p>' +
            '<p><strong>Bed / Ward :</strong> ' + escHtml(s.bed || '--') + ' / ' + escHtml(s.ward || '--') + '</p>' +
            '<p><strong>Diagnosis :</strong> ' + escHtml(s.diagnosis || '--') + '</p>' +
            '<p style="margin-bottom:2px;"><strong>Latest Vitals :</strong></p>' +
            '<p style="margin:0 0 10px 14px;">' + vitalsLine(s.vitals) + '</p>' +
            '<p><strong>Latest Medication :</strong> ' + escHtml(s.latest_rx || 'None') + '</p>' +
            '</div>';
    }
    document.getElementById('patient-info-title').textContent = title;
    document.getElementById('patient-info-body').innerHTML = html;
    document.getElementById('patient-info-modal').classList.add('show');
}

function vitalsLine(v) {
    if (!v) return 'No vitals recorded yet.';
    const when = v.recorded_at ? ' <span style="color:#94A3B8;">(' + escHtml(fmtDateTime(v.recorded_at)) + ')</span>' : '';
    return 'Temp ' + (v.temperature != null ? v.temperature + '°C' : '--') +
        ' · BP ' + escHtml(v.blood_pressure || '--') +
        ' · Pulse ' + (v.pulse != null ? escHtml(v.pulse) : '--') +
        ' · SpO2 ' + (v.spo2 != null ? escHtml(v.spo2) + '%' : '--') +
        ' · RR ' + (v.respiratory_rate != null ? escHtml(v.respiratory_rate) : '--') + when;
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

/* ============================ SAVE / UPDATE SUBMIT ============================ */
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

    const closeInfo = document.getElementById('close-patient-info-modal');
    if (closeInfo) closeInfo.addEventListener('click', () => document.getElementById('patient-info-modal').classList.remove('show'));
    const cancelInfo = document.getElementById('cancel-patient-info');
    if (cancelInfo) cancelInfo.addEventListener('click', () => document.getElementById('patient-info-modal').classList.remove('show'));
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