<style>
/* ================= VITAL SIGNS CARD UI : LOCAL BOOTSTRAP-STYLE UTILITIES =================
   Scoped under #vitals-page because the shell (dashboard.php) has no Bootstrap
   dependency and flattens plain .card inside .main-content-card-wrapper. */
#vitals-page .container-fluid{width:100%;padding-right:calc(1rem*.5);padding-left:calc(1rem*.5);margin-right:auto;margin-left:auto;box-sizing:border-box}
#vitals-page .bg-light{background-color:#F4F6F9 !important}
#vitals-page .card{position:relative;display:flex;flex-direction:column;min-width:0;word-wrap:break-word;background-color:#fff;background-clip:border-box;border:1px solid rgba(15,45,89,.08);border-radius:8px}
#vitals-page .card-header{padding:10px 16px;background-color:#fff;border-bottom:1px solid #E2E8F0;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
#vitals-page .card-body{flex:1 1 auto;padding:1rem 1rem 1.25rem}
#vitals-page .border-0{border:0 !important}
#vitals-page .shadow-sm{box-shadow:0 .125rem .25rem rgba(0,0,0,.075) !important}
#vitals-page .mb-4{margin-bottom:1.5rem !important}
#vitals-page .mb-3{margin-bottom:1rem !important}
#vitals-page .mb-2{margin-bottom:.5rem !important}
#vitals-page .mb-1{margin-bottom:.25rem !important}
#vitals-page .mb-0{margin-bottom:0 !important}
#vitals-page .me-1{margin-right:.25rem !important}
#vitals-page .py-2{padding-top:.5rem !important;padding-bottom:.5rem !important}
#vitals-page .py-0{padding-top:0 !important;padding-bottom:0 !important}
#vitals-page .pt-2{padding-top:.5rem !important}
#vitals-page .px-3{padding-left:1rem !important;padding-right:1rem !important}
#vitals-page .px-2{padding-left:.5rem !important;padding-right:.5rem !important}
#vitals-page .px-1{padding-left:.25rem !important;padding-right:.25rem !important}
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
#vitals-page .align-items-end{align-items:flex-end}
#vitals-page .align-items-center{align-items:center}
#vitals-page .justify-content-between{justify-content:space-between}
#vitals-page .flex-wrap{flex-wrap:wrap}
#vitals-page .gap-2{gap:.5rem}
#vitals-page .gap-5{gap:3rem}
#vitals-page .d-flex{display:flex}
#vitals-page .form-label{display:inline-block;margin-bottom:.5rem;font-family:inherit}
#vitals-page .form-select{display:block;width:100%;padding:.375rem .75rem;font-size:12px;line-height:1.5;color:#334155;background-color:#fff;border:1px solid #D1D5DB;border-radius:4px}
#vitals-page .form-select-sm{padding:.25rem .5rem;font-size:12px}
#vitals-page .text-center{text-align:center}
#vitals-page .text-uppercase{text-transform:uppercase}
#vitals-page .font-weight-bold{font-weight:700}
#vitals-page .text-dark{color:#0F172A !important}
#vitals-page .text-muted{color:#64748B !important}
#vitals-page .text-secondary{color:#64748B !important}
#vitals-page .text-white{color:#fff !important}
#vitals-page .bg-white{background-color:#fff !important}
#vitals-page .bg-dark{background-color:#1E293B !important}
#vitals-page .bg-secondary{background-color:#64748B !important}
#vitals-page .bg-warning{background-color:#F59E0B !important}
#vitals-page .bg-info{background-color:#0EA5E9 !important}
#vitals-page .badge{display:inline-flex;align-items:center;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:700;line-height:1.4;white-space:nowrap}
#vitals-page .border{border:1px solid #E2E8F0 !important}
#vitals-page .border-top{border-top:1px solid #E2E8F0 !important}
#vitals-page .border-bottom{border-bottom:1px solid #E2E8F0 !important}
#vitals-page .border-secondary-subtle{border-color:#E2E8F0 !important}
#vitals-page .rounded{border-radius:.375rem !important}
#vitals-page .table{width:100%;margin-bottom:1rem;color:#334155;border-collapse:collapse;font-size:12px}
#vitals-page .table-bordered{border:1px solid #E2E8F0}
#vitals-page .table-bordered > tbody > tr > td{border:1px solid #E2E8F0;padding:8px 10px;vertical-align:middle}
#vitals-page .table-responsive{overflow-x:auto}
#vitals-page .align-middle{vertical-align:middle}
#vitals-page .btn-light{background-color:#fff;color:#334155;border:1px solid #E2E8F0}
#vitals-page .btn-outline-primary{background-color:#fff;color:#0072BC;border:1px solid #0072BC}
#vitals-page .btn-outline-primary:hover{background-color:#0072BC;color:#fff}
</style>

<div class="container-fluid p-3 bg-light" id="vitals-page">

  <!-- TOP FILTER BAR (EXCLUDING DEPARTMENT & PATIENT INPUT FIELDS) -->
  <div class="card border-0 shadow-sm p-3 mb-4" style="border-radius: 8px;">
    <form id="vitalsFilterForm" class="row g-3 align-items-end" onsubmit="return false;">

      <!-- 1. Type Of Ward -->
      <div class="col-md-3">
        <label for="cboTypeWard" class="form-label font-weight-bold" style="font-size: 12px; color: #333;">Type Of Ward</label>
        <select class="form-select form-select-sm" id="cboTypeWard" style="border-radius: 4px; font-size: 12px;">
          <option value="By Ward" selected>By Ward</option>
          <option value="By Speciality">By Speciality</option>
        </select>
      </div>

      <!-- 2. Wards -->
      <div class="col-md-4">
        <label for="cboWards" class="form-label font-weight-bold" style="font-size: 12px; color: #333;">Wards</label>
        <select class="form-select form-select-sm" id="cboWards" style="border-radius: 4px; font-size: 12px;">
          <option value="Male Emergency Ward" selected>Male Emergency Ward</option>
          <option value="Female Emergency Ward">Female Emergency Ward</option>
          <option value="Children Ward">Children Ward</option>
          <option value="Maternity Ward">Maternity Ward</option>
        </select>
      </div>

      <!-- 3. Search & Action Buttons -->
      <div class="col-md-5 d-flex gap-2">
        <button type="button" class="btn btn-sm btn-primary px-3 font-weight-bold" onclick="filterVitalsByWard()" style="background-color: #0072BC; border: none; font-size: 12px;">
          Search
        </button>
        <button type="button" class="btn btn-sm btn-success px-3" style="background-color: #22C55E; border: none;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        </button>
      </div>

    </form>
  </div>

  <!-- HEADER COUNTERS -->
  <div class="text-center mb-3">
    <h5 class="font-weight-bold text-uppercase mb-2" style="letter-spacing: 0.5px; color: #1E293B;">WARD VITAL SIGNS DETAILS</h5>
    <div class="d-flex justify-content-center gap-5" style="font-size: 14px;">
      <span><strong>Total Patient(S):</strong> <span class="badge bg-secondary">1</span></span>
      <span><strong>Partial Discharged Patient(S):</strong> <span class="badge bg-secondary">0</span></span>
    </div>
  </div>

  <!-- PATIENT CLINICAL VITAL CARD CONTAINER -->
  <div class="card border border-secondary-subtle shadow-sm mb-3" style="border-radius: 8px;">

    <!-- Ward Banner Header -->
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2">
      <div class="d-flex align-items-center gap-2">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0072BC" stroke-width="2"><path d="M2 4v16"></path><path d="M2 8h18a2 2 0 0 1 2 2v10"></path><path d="M2 17h20"></path><path d="M6 8v9"></path></svg>
        <span class="font-weight-bold text-dark" style="font-size: 14px;">Male Emergency Ward</span>
      </div>
      <span class="text-muted" style="font-size: 11px;">No Shift Available</span>
    </div>

    <div class="card-body p-3">

      <!-- Patient Banner -->
      <div class="d-flex flex-wrap justify-content-between align-items-center p-2 mb-3 rounded" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-dark">BED 1</span>
          <span class="badge bg-warning text-dark">0</span>
          <span class="text-muted" style="font-size: 12px;">(Currently Not On Oxygen)</span>
        </div>

        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-info text-white" style="font-size: 11px;">Blood Donation :- NA</span>
          <h6 class="mb-0 font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 15px;">FREDUA AGYEMANG</h6>
          <button class="btn btn-sm btn-info text-white py-0 px-2" style="font-size: 11px;">i</button>
          <button class="btn btn-sm btn-primary py-0 px-2" style="font-size: 11px; background-color: #0072BC;">AM</button>
        </div>
      </div>

      <!-- Current Medications Row -->
      <div class="mb-3">
        <label class="font-weight-bold text-secondary mb-1" style="font-size: 11px;">Today's Medications :</label>
        <div class="table-responsive">
          <table class="table table-bordered align-middle mb-0" style="font-size: 12px;">
            <tbody>
              <tr>
                <td style="width: 65%;">
                  <strong>ARTEMETHER + LUMEFANTRINE (24'S)</strong>
                  <span class="text-muted">[ARTEMETHER + LUMEFANTRINE (24'S) | 20MG+120MG | TABLET]</span>
                </td>
                <td class="text-center" style="width: 15%;">4 Tabs</td>
                <td class="text-center" style="width: 15%;">1-0-1</td>
                <td class="text-center" style="width: 5%;">
                  <button class="btn btn-sm btn-outline-primary py-0 px-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Clinical Notes Section -->
      <div class="p-2 mb-3 rounded" style="background-color: #F1F5F9; border-left: 4px solid #0072BC; font-size: 11px; color: #334155;">
        <span class="badge bg-info text-white me-1">N</span>
        <strong>Notes :</strong> SITUATION A 28-YEAR-OLD MALE IN THE EMERGENCY DEPARTMENT, BEING MANAGED AS A CASE OF MALARIA IN A KNOWN SCGD WITH GENOTYPE SS. ASSESSMENT VITALS: BP 114/60, HR 90, O2 SAT 95% ON INO2. GEN: ILL LOOKING, FAIRLY-HYDRATED, IN MILD RESPIRATORY DISTRESS.
      </div>

      <!-- Services & Department Lines -->
      <div class="border-top pt-2 mb-3" style="font-size: 11px; color: #64748B;">
        <p class="mb-1"><strong>Services :</strong> --</p>
        <p class="mb-0"><strong>Directorate / Departments :</strong> Emergency Medicine</p>
      </div>

      <!-- CLINICAL ACTION BUTTON TOOLBAR -->
      <div class="d-flex flex-wrap gap-2 pt-2 border-top">
        <button class="btn btn-sm btn-light border shadow-sm px-2" title="Record Vitals" onclick="openRecordVitalsModal()"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0072BC" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></button>
        <button class="btn btn-sm btn-light border shadow-sm px-2" title="Heart Rate History"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg></button>
        <button class="btn btn-sm btn-light border shadow-sm px-2" title="Search Medical History"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></button>
        <button class="btn btn-sm btn-light border shadow-sm px-2" title="Patient Chart Summary"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg></button>
        <button class="btn btn-sm btn-light border shadow-sm px-2" title="Lab Results"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><path d="M10 2v7.51L4.53 17.92A2 2 0 0 0 6.24 21h11.52a2 2 0 0 0 1.71-3.08L14 9.51V2"></path></svg></button>
        <button class="btn btn-sm btn-light border shadow-sm px-2" title="Nursing Notes"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="M9 12h6"></path><path d="M9 16h6"></path></svg></button>
        <button class="btn btn-sm btn-light border shadow-sm px-2" title="Medication Orders"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#06B6D4" stroke-width="2"><rect x="6" y="7" width="12" height="14" rx="2"></rect><line x1="12" y1="11" x2="12" y2="17"></line></svg></button>
        <button class="btn btn-sm btn-light border shadow-sm px-2" title="Vitals Graph View"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg></button>
        <button class="btn btn-sm btn-light border shadow-sm px-2" title="Print Vitals Summary"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg></button>
      </div>

    </div>
  </div>

</div>

<!-- Vitals Modal -->
<div class="modal" id="vitals-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Record Vital Signs</h3>
            <button class="modal-close" id="close-vitals-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="vitals-form">
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
                    <button type="submit" class="btn btn-primary">Save Vitals</button>
                    <button type="button" class="btn btn-secondary" id="cancel-vitals">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let vitalsData = [];

async function initVitals() {
    await loadActiveVisits();
    await loadVitals();
    setupEventListeners();
}

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

async function loadVitals(filters = {}) {
    try {
        const queryParams = new URLSearchParams(filters);
        const response = await fetch(`/hms/backend/api/vitals.php?${queryParams}`);
        const data = await response.json();

        if (data.success) {
            vitalsData = data.vitals;
            renderVitalsTable();
        }
    } catch (error) {
        console.error('Vitals load error:', error);
    }
}

function renderVitalsTable() {
    const tbody = document.getElementById('vitals-table');

    if (!tbody) return;

    if (!vitalsData || vitalsData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align: center;">No vital signs recorded</td></tr>';
        return;
    }

    tbody.innerHTML = vitalsData.map(vital => `
        <tr>
            <td>${formatDateTime(vital.recorded_at)}</td>
            <td>${vital.patient_name}</td>
            <td>${vital.hospital_number}</td>
            <td>${vital.temperature || '-'}</td>
            <td>${vital.blood_pressure_systolic && vital.blood_pressure_diastolic ?
                vital.blood_pressure_systolic + '/' + vital.blood_pressure_diastolic : '-'}</td>
            <td>${vital.heart_rate || '-'}</td>
            <td>${vital.respiratory_rate || '-'}</td>
            <td>${vital.oxygen_saturation || '-'}</td>
            <td>${vital.weight || '-'}</td>
            <td>${vital.nurse_name}</td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="viewVitals(${vital.id})">View</button>
            </td>
        </tr>
    `).join('');
}

function setupEventListeners() {
    const newVitalsBtn = document.getElementById('new-vitals-btn');
    if (newVitalsBtn) newVitalsBtn.addEventListener('click', () => openVitalsModal());
    const closeBtn = document.getElementById('close-vitals-modal');
    if (closeBtn) closeBtn.addEventListener('click', closeVitalsModal);
    const cancelBtn = document.getElementById('cancel-vitals');
    if (cancelBtn) cancelBtn.addEventListener('click', closeVitalsModal);

    const form = document.getElementById('vitals-form');
    if (form) form.addEventListener('submit', handleVitalsSubmit);

    const dateFilter = document.getElementById('filter-vitals-date');
    if (dateFilter) dateFilter.addEventListener('change', function() {
        loadVitals({ date_filter: this.value });
    });
}

function openVitalsModal() {
    document.getElementById('vitals-modal').classList.add('show');
    document.getElementById('vitals-form').reset();
}

function openRecordVitalsModal() {
    openVitalsModal();
}

function closeVitalsModal() {
    document.getElementById('vitals-modal').classList.remove('show');
}

function filterVitalsByWard() {
    const type = document.getElementById('cboTypeWard');
    const ward = document.getElementById('cboWards');
    loadVitals({
        type_filter: type ? type.value : '',
        ward_filter: ward ? ward.value : ''
    });
}

async function handleVitalsSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('/hms/backend/api/vitals.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showAlert('Vital signs recorded successfully', 'success');
            closeVitalsModal();
            await loadVitals();
        } else {
            showAlert(result.error || 'Failed to record vitals', 'error');
        }
    } catch (error) {
        console.error('Vitals save error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function viewVitals(vitalId) {
    const vital = vitalsData.find(v => v.id === vitalId);
    if (vital) {
        alert(`Vital Signs Details:\n\nTemperature: ${vital.temperature || 'N/A'}°C\nBP: ${vital.blood_pressure_systolic || 'N/A'}/${vital.blood_pressure_diastolic || 'N/A'} mmHg\nHeart Rate: ${vital.heart_rate || 'N/A'} bpm\nRespiratory Rate: ${vital.respiratory_rate || 'N/A'} bpm\nSpO2: ${vital.oxygen_saturation || 'N/A'}%\nWeight: ${vital.weight || 'N/A'} kg\nHeight: ${vital.height || 'N/A'} cm\nBMI: ${vital.bmi || 'N/A'}\nNotes: ${vital.notes || 'None'}`);
    }
}

function formatDateTime(dateString) {
    return fmtDateTime(dateString);
}
</script>