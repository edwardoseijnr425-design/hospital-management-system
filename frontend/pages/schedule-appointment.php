<?php
// Schedule Appointment - standalone EHMS-style appointment form (SPA fragment).
// Loaded by the shell (dashboard.php) into #page-content via loadPage('schedule-appointment').
require_once __DIR__ . '/../../backend/config/config.php';
?>
<style>
/* ============ SCHEDULE APPOINTMENT : rounded workspace form ============
   Scoped under #schedule-page. No Bootstrap/Tailwind - all utilities defined
   here. Form ids/JS hooks (saPatientSearch, saDate, saTime, saPrice, ...) are
   preserved so the booking + auto-pricing logic is unchanged. */
#schedule-page{
    --brand:#1b659d; --brand-dk:#155380;
    --s50:#F8FAFC; --s100:#F1F5F9; --s200:#E2E8F0; --s300:#CBD5E1;
    --s400:#94A3B8; --s500:#64748B; --s600:#475569; --s700:#334155; --s800:#1E293B;
    background:var(--s50);border-radius:12px;padding:14px;color:var(--s700);font-size:12px;
    font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
}
#schedule-page *,#schedule-page *::before,#schedule-page *::after{box-sizing:border-box}

/* Form shell */
#schedule-page .sa-form-wrapper{max-width:960px;margin:0 auto}
#schedule-page .sa-panel{background:#fff;border:1px solid var(--s200);border-radius:12px;overflow:hidden;box-shadow:0 1px 2px rgba(15,23,42,.05)}
#schedule-page .sa-panel-header{background:var(--brand);color:#fff;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
#schedule-page .sa-panel-title{font-weight:800;font-size:12px;letter-spacing:.4px;text-transform:uppercase;display:inline-flex;align-items:center;gap:8px}
#schedule-page .sa-panel-title i{font-size:14px}
#schedule-page .sa-panel-tag{font-size:10px;color:#BFDBFE;font-weight:400}
#schedule-page .appointment-form{padding:18px 20px;background:#fff}

/* Sections, grid & fields */
#schedule-page .form-section-title{font-size:11px;font-weight:800;color:var(--brand);border-bottom:1px solid var(--s200);padding-bottom:6px;margin-bottom:14px;text-transform:uppercase;letter-spacing:.4px}
#schedule-page .form-section-title.second{margin-top:20px}
#schedule-page .grid-2col{display:grid;grid-template-columns:1fr 1fr;gap:14px 20px}
#schedule-page .grid-full{grid-column:span 2}
#schedule-page .form-group{display:flex;flex-direction:column;gap:5px}
#schedule-page .form-group.notes{margin-top:20px}
#schedule-page .form-group label{font-weight:700;color:var(--s600);font-size:10px;text-transform:uppercase;letter-spacing:.4px}
#schedule-page .form-group label .required{color:#E74C3C;font-weight:800}
#schedule-page .sa-hint{font-size:10px;color:var(--s400);margin:4px 0 0}
#schedule-page .input-wrapper{position:relative;display:flex;align-items:center}
#schedule-page .form-control{width:100%;padding:9px 11px;border:1px solid var(--s300);border-radius:8px;font-size:12px;background:var(--s50);color:var(--s800);font-weight:600;transition:border-color .2s,box-shadow .2s;font-family:inherit}
#schedule-page .form-control:focus{outline:none;border-color:var(--brand);background:#fff;box-shadow:0 0 0 3px rgba(27,101,157,.15)}
#schedule-page textarea.form-control{resize:vertical;min-height:64px;font-weight:400}

/* Date & time scheduling card */
#schedule-page .date-scheduling-card{background:var(--s50);border:1px solid var(--s200);border-radius:10px;padding:14px 16px;display:grid;grid-template-columns:1fr 1fr;gap:14px 20px;align-items:start}
#schedule-page .date-input-container{display:flex;flex-direction:column;gap:6px}
#schedule-page .date-input-label,#schedule-page .time-slots-container>label{font-weight:700;color:var(--s600);font-size:10px;text-transform:uppercase;letter-spacing:.4px}
#schedule-page .date-input-container label .required,#schedule-page .time-slots-container label .required{color:#E74C3C;font-weight:800}
#schedule-page .formal-date-badge{background:#EFF6FF;border:1px dashed var(--brand);padding:6px 10px;border-radius:8px;font-size:11px;font-weight:700;color:var(--brand);display:flex;align-items:center;gap:6px}
#schedule-page .time-slots-container{display:flex;flex-direction:column;gap:6px}
#schedule-page .time-chips-group{display:flex;gap:6px;flex-wrap:wrap}
#schedule-page .time-chip{background:var(--s100);border:1px solid var(--s200);padding:6px 11px;border-radius:8px;font-size:11px;font-weight:700;color:var(--s700);cursor:pointer;transition:background .2s,color .2s,box-shadow .2s;font-family:inherit}
#schedule-page .time-chip:hover{background:var(--s200)}
#schedule-page .time-chip.active{background:var(--brand);color:#fff;border-color:var(--brand);box-shadow:0 2px 6px rgba(27,101,157,.35)}
#schedule-page input[type="time"].form-control{font-weight:600;color:var(--brand)}

/* Consultation fee banner (auto-calculated; reflects the NHIS outcome) */
#schedule-page .sa-fee-banner{background:#ECFDF5;border:1px solid #A7F3D0;border-radius:10px;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:14px;font-weight:800;color:#065F46;font-size:14px}
#schedule-page .sa-fee-banner.is-cash{background:#FFF1F2;border-color:#FECDD3;color:#9F1239}
#schedule-page .sa-fee-label{text-transform:uppercase;letter-spacing:.4px}
#schedule-page .sa-fee-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
#schedule-page .sa-fee-note{font-size:10px;font-weight:700;color:#047857;text-transform:uppercase;letter-spacing:.3px}
#schedule-page .sa-fee-note.is-cash{color:#BE123C}
#schedule-page .sa-fee-value{font-size:16px;font-weight:800}

/* Patient search results */
#schedule-page .sa-search-wrap{position:relative}
#schedule-page .sa-results{position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid var(--s300);border-radius:8px;max-height:220px;overflow:auto;z-index:50;box-shadow:0 8px 20px rgba(15,23,42,.12);display:none}
#schedule-page .sa-results div{padding:8px 10px;cursor:pointer;font-size:12px;display:flex;justify-content:space-between;align-items:center;gap:10px}
#schedule-page .sa-results div:hover{background:#EFF6FF}
#schedule-page .sa-results .sa-num{color:var(--brand);font-weight:700;letter-spacing:.3px;white-space:nowrap}
#schedule-page .sa-results .sa-empty{padding:10px;color:var(--s400);cursor:default;justify-content:center}

/* Actions */
#schedule-page .form-actions{margin-top:20px;padding-top:14px;border-top:1px solid var(--s200);display:flex;justify-content:flex-end;gap:10px}
#schedule-page .btn-action{border:none;padding:10px 20px;font-weight:800;font-size:11px;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase;letter-spacing:.4px;font-family:inherit;transition:background .2s}
#schedule-page .btn-primary{background:var(--brand);color:#fff;box-shadow:0 1px 2px rgba(27,101,157,.25)}
#schedule-page .btn-primary:hover{background:var(--brand-dk)}
#schedule-page .btn-secondary{background:var(--s100);color:var(--s700);border:1px solid var(--s300)}
#schedule-page .btn-secondary:hover{background:var(--s200)}

@media (max-width:768px){
  #schedule-page .grid-2col,#schedule-page .date-scheduling-card{grid-template-columns:1fr}
  #schedule-page .grid-full{grid-column:span 1}
}
</style><div id="schedule-page">

  <!-- APPOINTMENT BOOKING FORM -->
  <div class="sa-form-wrapper">
    <div class="sa-panel">
      <div class="sa-panel-header">
        <span class="sa-panel-title"><i class="fa-solid fa-calendar-plus"></i> Appointment Booking &amp; Consultation Pricing</span>
        <span class="sa-panel-tag">EHMS &mdash; Healthcare Management System</span>
      </div>

      <form class="appointment-form" id="appointmentForm">

        <div class="form-section-title">Patient Identification &amp; Consultation Type</div>

        <div class="grid-2col">

          <div class="form-group">
            <label for="saPatientSearch">Patient ID or Name <span class="required">*</span></label>
            <div class="input-wrapper sa-search-wrap">
              <input type="text" id="saPatientSearch" class="form-control" placeholder="Type Patient ID (e.g. HMS202600001) or Name" autocomplete="off" required>
              <input type="hidden" id="saPatientId">
              <div class="sa-results" id="saSearchResults"></div>
            </div>
            <p class="sa-hint">Auto-filled from selected patient &mdash; type to change.</p>
          </div>

          <div class="form-group">
            <label for="saCategory">Consultation Category <span class="required">*</span></label>
            <select id="saCategory" class="form-control" required onchange="saCalcPrice()">
              <option value="new">New Patient Consultation</option>
              <option value="returning" selected>Old / Returning Patient Consultation</option>
            </select>
          </div>

        </div>

        <!-- 2. NHIS status verification & reconfirmation (shared toolbar) -->
        <?php include __DIR__ . '/_nhis_verify_toolbar.php'; ?>

        <div class="form-section-title second">Clinical Specialty &amp; Attending Doctor</div>

        <div class="grid-2col">

          <div class="form-group">
            <label for="saVisitType">Visit / Specialty Type <span class="required">*</span></label>
            <select id="saVisitType" class="form-control" required onchange="saCalcPrice()">
              <option value="general">General Outpatient (OPD)</option>
              <option value="specialist">Specialist Consultation (Gynaecology, ENT, Cardiology)</option>
              <option value="emergency">Emergency / Urgent Care</option>
              <option value="followup">Routine Follow-Up / Results Review</option>
              <option value="pediatric">Pediatric Care Consultation</option>
              <option value="dental">Dental &amp; Oral Care</option>
            </select>
          </div>

          <div class="form-group">
            <label for="saDoctor">Attending Doctor / Medical Team <span class="required">*</span></label>
            <select id="saDoctor" class="form-control" required>
              <option value="">-- Select Doctor or Medical Team --</option>
            </select>
          </div>

        </div>

        <div class="form-section-title second">Appointment Schedule (Date &amp; Time)</div>

        <div class="date-scheduling-card">

          <!-- DATE PICKER COLUMN -->
          <div class="date-input-container">
            <label class="date-input-label" for="saDate">Select Appointment Date <span class="required">*</span></label>
            <input type="date" id="saDate" class="form-control" required onchange="saUpdateFormalDate()">
            <div class="formal-date-badge" id="saFormalDateBadge">
              <i class="fa-solid fa-circle-check"></i>
              <span id="saFormalDateText">-- select date --</span>
            </div>
          </div>

          <!-- TIME SLOT COLUMN -->
          <div class="time-slots-container">
            <label for="saTime">Select Preferred Time Slot <span class="required">*</span></label>
            <div class="time-chips-group">
              <button type="button" class="time-chip" data-time="08:30" onclick="saSelectTimeChip('08:30', this)">08:30 AM</button>
              <button type="button" class="time-chip active" data-time="10:00" onclick="saSelectTimeChip('10:00', this)">10:00 AM</button>
              <button type="button" class="time-chip" data-time="11:30" onclick="saSelectTimeChip('11:30', this)">11:30 AM</button>
              <button type="button" class="time-chip" data-time="14:00" onclick="saSelectTimeChip('14:00', this)">02:00 PM</button>
              <button type="button" class="time-chip" data-time="15:30" onclick="saSelectTimeChip('15:30', this)">03:30 PM</button>
            </div>
            <input type="time" id="saTime" class="form-control" value="10:00" required style="margin-top:4px;">
          </div>

        </div>

        <div class="form-section-title second">Consultation Pricing &amp; Notes</div>

        <div class="sa-fee-banner" id="saFeeBanner">
          <span class="sa-fee-label">Consultation Tariff:</span>
          <span class="sa-fee-right">
            <span class="sa-fee-note" id="saFeeNote"></span>
            <span class="sa-fee-value" id="saFeeValue">GH₵ 50.00</span>
          </span>
        </div>
        <input type="hidden" id="saPrice" value="GH₵ 50.00">

        <div class="form-group notes">
          <label for="saNotes">Reason for Visit / Clinical Notes</label>
          <textarea id="saNotes" class="form-control" rows="2" placeholder="Enter brief symptoms or purpose of appointment..."></textarea>
        </div>

        <div class="form-actions">
          <button type="button" class="btn-action btn-secondary" onclick="saGoBack(event)">CANCEL</button>

          <button type="submit" class="btn-action btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> SAVE APPOINTMENT
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<script>/* ================= NAV (top bar) ================= */
function saNavHome(e) { if (e) e.preventDefault(); if (window.navigateTo) window.navigateTo('dashboard'); else if (window.loadPage) window.loadPage('dashboard'); }
function saGoBack(e) { if (e) e.preventDefault(); if (window.history && window.history.length > 1) window.history.back(); else saNavHome(null); }
function saGoPassword(e) { if (e) e.preventDefault(); if (window.navigateTo) window.navigateTo('change-password'); else if (window.loadPage) window.loadPage('change-password'); }

/* ================= PAGE INIT ================= */
function initScheduleAppointment() {
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('saDate').value = today;
    saUpdateFormalDate();
    saCalcPrice();
    saLoadReference();

    // NHIS reconfirmation toolbar: refresh the fee banner whenever a verification
    // completes, and clear any stale result from a previous visit to this page.
    window.nhisToolbarOnResult = function () { saUpdateFeeBanner(); };
    if (window.nhisToolbarReset) { nhisToolbarReset(); }
    saUpdateFeeBanner();

    var form = document.getElementById('appointmentForm');
    if (form) form.addEventListener('submit', saSubmitAppointment);
    var searchInput = document.getElementById('saPatientSearch');
    if (searchInput) {
        searchInput.addEventListener('input', saOnSearchInput);
        searchInput.addEventListener('blur', saHideResults);
    }
}

/* ================= FORMAL DATE DISPLAY ================= */
function saUpdateFormalDate() {
    var dateVal = document.getElementById('saDate').value;
    if (!dateVal) return;
    var dateObj = new Date(dateVal);
    var formalString = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('saFormalDateText').innerText = formalString;
}

/* ================= TIME CHIP SELECTOR ================= */
function saSelectTimeChip(timeVal, chipEl) {
    document.getElementById('saTime').value = timeVal;
    var chips = document.querySelectorAll('#schedule-page .time-chip');
    for (var i = 0; i < chips.length; i++) { chips[i].classList.remove('active'); }
    chipEl.classList.add('active');
}

/* ================= AUTO PRICE CALCULATION ================= */
var saBasePrices = { general: 50, specialist: 120, emergency: 150, followup: 30, pediatric: 80, dental: 100 };
var saNewPatientAddon = 30;

function saCalcPrice() {
    var category = document.getElementById('saCategory').value;
    var visitType = document.getElementById('saVisitType').value;
    var baseFee = saBasePrices[visitType] || 50;
    if (category === 'new') { baseFee += saNewPatientAddon; }
    var feeText = 'GH₵ ' + baseFee.toFixed(2);
    document.getElementById('saPrice').value = feeText;
    var feeValue = document.getElementById('saFeeValue');
    if (feeValue) feeValue.textContent = feeText;
    saUpdateFeeBanner();
}

/* Reflect the NHIS reconfirmation outcome in the fee banner. Display only:
   the server-side tariff (appointments.php) is unchanged. An active card reads
   as NHIS-covered, an inactive/expired card as cash, and an unverified state
   shows no tariff note at all. */
function saUpdateFeeBanner() {
    var banner = document.getElementById('saFeeBanner');
    var note = document.getElementById('saFeeNote');
    if (!banner || !note) return;
    var state = window.nhisToolbarState || {};
    if (state.active) {
        note.textContent = 'NHIS Covered \u2014 standard consultation tariff';
        note.className = 'sa-fee-note';
        banner.classList.remove('is-cash');
    } else if (state.nhisNumber && state.message) {
        note.textContent = 'Cash Rate Applied \u2014 NHIS inactive/expired';
        note.className = 'sa-fee-note is-cash';
        banner.classList.add('is-cash');
    } else {
        note.textContent = '';
        note.className = 'sa-fee-note';
        banner.classList.remove('is-cash');
    }
}

/* ================= RESET ================= */
function saResetAppointment() {
    if (confirm('Reset appointment form?')) { saResetSilent(); }
}

function saResetSilent() {
    document.getElementById('appointmentForm').reset();
    document.getElementById('saPatientId').value = '';
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('saDate').value = today;
    document.getElementById('saTime').value = '10:00';
    var chips = document.querySelectorAll('#schedule-page .time-chip');
    for (var i = 0; i < chips.length; i++) {
        chips[i].classList.toggle('active', chips[i].getAttribute('data-time') === '10:00');
    }
    saUpdateFormalDate();
    if (window.nhisToolbarReset) { nhisToolbarReset(); }
    saCalcPrice();
}

/* ================= PATIENT SEARCH / TYPING ================= */
var saSearchTimer = null;

function saOnSearchInput() {
    var input = document.getElementById('saPatientSearch');
    document.getElementById('saPatientId').value = '';
    if (saSearchTimer) clearTimeout(saSearchTimer);
    saSearchTimer = setTimeout(function(){ saDoSearch(input.value); }, 300);
}

function saDoSearch(q) {
    var resultsBox = document.getElementById('saSearchResults');
    if (!q || q.trim().length < 2) { saHideResults(); return; }
    fetch('/hms/backend/api/patients.php?action=search&q=' + encodeURIComponent(q.trim()) + '&limit=8')
        .then(function(r){ return r.json(); })
        .then(function(d){
            var patients = (d && d.patients) || [];
            resultsBox.innerHTML = '';
            if (!patients.length) {
                var empty = document.createElement('div');
                empty.className = 'sa-empty';
                empty.textContent = 'No matching patients found';
                resultsBox.appendChild(empty);
                resultsBox.style.display = 'block';
                return;
            }
            patients.forEach(function(p){
                var item = document.createElement('div');
                var name = [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ');
                var nameSpan = document.createElement('span');
                nameSpan.textContent = name;
                var numSpan = document.createElement('span');
                numSpan.className = 'sa-num';
                numSpan.textContent = p.hospital_number;
                item.appendChild(nameSpan);
                item.appendChild(numSpan);
                item.addEventListener('mousedown', function(ev){
                    ev.preventDefault();
                    saSelectPatient(p);
                });
                resultsBox.appendChild(item);
            });
            resultsBox.style.display = 'block';
        })
        .catch(function(){ saHideResults(); });
}

function saSelectPatient(p) {
    var name = [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ');
    document.getElementById('saPatientSearch').value = name + ' (' + p.hospital_number + ')';
    document.getElementById('saPatientId').value = p.id;
    saHideResults();
}

function saHideResults() {
    var resultsBox = document.getElementById('saSearchResults');
    if (resultsBox) resultsBox.style.display = 'none';
}/* ================= MEDICAL TEAMS + SYSTEM DOCTORS ================= */
var saTeams = {};
function saLoadReference() {
    var doctorSelect = document.getElementById('saDoctor');
    if (!doctorSelect) return;
    var placeholder = doctorSelect.options[0];
    doctorSelect.innerHTML = '';
    if (placeholder) doctorSelect.appendChild(placeholder);

    /* ---- Medical teams (duty/clinical teams) ---- */
    fetch('/hms/backend/api/medicalteams.php?action=list')
        .then(function(r){ return r.json(); })
        .then(function(d){
            var teams = (d && d.medical_teams) || [];
            var teamGroup = document.createElement('optgroup');
            teamGroup.label = '-- MEDICAL TEAMS --';
            teams.forEach(function(team){
                saTeams[team.id] = { name: team.name, department_id: team.department_id };
                var opt = document.createElement('option');
                opt.value = 'team:' + team.id;
                opt.textContent = team.name;
                teamGroup.appendChild(opt);
            });
            if (!teams.length) {
                var noneOpt = document.createElement('option');
                noneOpt.disabled = true;
                noneOpt.textContent = 'No medical teams registered yet';
                teamGroup.appendChild(noneOpt);
            }
            doctorSelect.appendChild(teamGroup);
        })
        .catch(function(){ /* ignore - teams unavailable */ });

    /* ---- Doctors from system users (any doctor can take charge) ---- */
    fetch('/hms/backend/api/users.php?action=list&role=doctor')
        .then(function(r){ return r.json(); })
        .then(function(d){
            var doctors = (d && d.users) || [];
            var docGroup = document.createElement('optgroup');
            docGroup.label = '-- SYSTEM DOCTORS --';
            if (doctors.length) {
                doctors.forEach(function(doc){
                    var opt = document.createElement('option');
                    opt.value = 'doc:' + doc.id;
                    opt.textContent = doc.full_name || ('Doctor #' + doc.id);
                    docGroup.appendChild(opt);
                });
            } else {
                var noneOpt = document.createElement('option');
                noneOpt.disabled = true;
                noneOpt.textContent = 'No doctor accounts yet - any doctor can take charge';
                docGroup.appendChild(noneOpt);
            }
            doctorSelect.appendChild(docGroup);
        })
        .catch(function(){
            var docGroup = document.createElement('optgroup');
            docGroup.label = '-- SYSTEM DOCTORS --';
            var noneOpt = document.createElement('option');
            noneOpt.disabled = true;
            noneOpt.textContent = 'No doctor accounts yet - any doctor can take charge';
            docGroup.appendChild(noneOpt);
            doctorSelect.appendChild(docGroup);
        });
}

/* ================= SUBMIT ================= */
function saVal(id) { var el = document.getElementById(id); return el ? el.value : ''; }

async function saSubmitAppointment(e) {
    e.preventDefault();
    var missing = [];
    if (!saVal('saPatientId')) missing.push('Patient');
    if (!saVal('saDoctor')) missing.push('Attending Doctor / Medical Team');
    if (!saVal('saDate')) missing.push('Appointment Date');
    if (!saVal('saTime')) missing.push('Preferred Time Slot');
    if (missing.length) {
        if (window.showAlert) showAlert('Please complete: ' + missing.join(', '), 'error');
        return;
    }

    var doctorValue = saVal('saDoctor');
    var departmentId = null, doctorId = null;
    if (doctorValue.indexOf('team:') === 0) {
        var teamId = parseInt(doctorValue.split(':')[1], 10);
        var team = saTeams[teamId];
        if (team && team.department_id) { departmentId = parseInt(team.department_id, 10); }
    } else if (doctorValue.indexOf('doc:') === 0) {
        doctorId = parseInt(doctorValue.split(':')[1], 10);
    }

    var data = {
        patient_id: parseInt(saVal('saPatientId'), 10),
        appointment_date: saVal('saDate') + ' ' + saVal('saTime') + ':00',
        department_id: departmentId,
        doctor_id: doctorId,
        reason: saVal('saNotes').trim(),
        consultation_type: saVal('saVisitType').toUpperCase(),
        visit_type: saVal('saCategory') === 'new' ? 'New' : 'Review'
    };

    try {
        var r = await fetch('/hms/backend/api/appointments.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var d = await r.json();
        if (d.success) {
            var formalDate = document.getElementById('saFormalDateText').innerText;
            if (window.showAlert) showAlert('Appointment Scheduled Successfully - ' + formalDate + ' at ' + saVal('saTime'), 'success');
            saResetSilent();
        } else {
            var err = d.error || (d.errors ? Object.values(d.errors)[0] : null) || 'Failed to schedule appointment';
            if (window.showAlert) showAlert(err, 'error');
        }
    } catch (err) {
        console.error('Appointment scheduling error:', err);
        if (window.showAlert) showAlert('Network error. Please try again.', 'error');
    }
}
</script>