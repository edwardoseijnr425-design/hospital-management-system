<?php
// Schedule Appointment - standalone EHMS-style appointment form (SPA fragment).
// Loaded by the shell (dashboard.php) into #page-content via loadPage('schedule-appointment').
require_once __DIR__ . '/../../backend/config/config.php';
?>
<style>
/* ============ SCHEDULE APPOINTMENT : EHMS STANDALONE FORM ============
   Scoped under #schedule-page. No Bootstrap - all utilities defined here.
   Inline SVG icons to match the wireframe; Font Awesome also available. */
#schedule-page{background:#dce7f2;color:#333;font-size:13px;min-height:100vh;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;padding-bottom:30px}
#schedule-page *,#schedule-page *::before,#schedule-page *::after{box-sizing:border-box}
#schedule-page .top-bar{background-color:#0b5fa5;color:#fff;padding:8px 20px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 5px rgba(0,0,0,0.15)}
#schedule-page .top-bar .title-group{display:flex;align-items:center;gap:10px}
#schedule-page .top-bar .title-group svg{width:24px;height:24px;fill:#ffffff}
#schedule-page .top-bar .title{font-size:16px;font-weight:bold;letter-spacing:0.5px;text-transform:uppercase}
#schedule-page .top-bar .right-nav{display:flex;align-items:center;gap:10px}
#schedule-page .top-bar .hospital-tag{font-size:11px;color:#d1e5f7;margin-right:15px;font-weight:500}
#schedule-page .top-bar .btn-nav{background-color:#0088cc;color:#fff;border:none;padding:5px 12px;font-size:11px;font-weight:bold;border-radius:3px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background-color 0.2s;font-family:inherit}
#schedule-page .top-bar .btn-nav:hover{background-color:#006699}
#schedule-page .form-wrapper{max-width:960px;margin:20px auto;padding:0 15px}
#schedule-page .panel-box{background:#fff;border:1px solid #b2c8de;border-radius:4px;overflow:hidden;box-shadow:0 3px 8px rgba(0,0,0,0.06)}
#schedule-page .panel-header{background-color:#0b5fa5;color:#fff;font-weight:bold;font-size:12px;padding:10px 15px;text-transform:uppercase;display:flex;align-items:center;gap:8px}
#schedule-page .panel-header svg{width:16px;height:16px;fill:#ffffff}#schedule-page .appointment-form{padding:20px 25px;background-color:#f8fafc}
#schedule-page .form-section-title{font-size:12px;font-weight:bold;color:#0b5fa5;border-bottom:2px solid #0b5fa5;padding-bottom:4px;margin-bottom:15px;text-transform:uppercase}
#schedule-page .grid-2col{display:grid;grid-template-columns:1fr 1fr;gap:15px 25px}
#schedule-page .grid-full{grid-column:span 2}
#schedule-page .form-group{display:flex;flex-direction:column;gap:5px}
#schedule-page .form-group label{font-weight:600;color:#333;font-size:11px;text-transform:uppercase;letter-spacing:0.3px}
#schedule-page .form-group label .required{color:#e74c3c;font-weight:bold}
#schedule-page .input-wrapper{position:relative;display:flex;align-items:center}
#schedule-page .form-control{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;transition:border-color 0.2s, box-shadow 0.2s;font-family:inherit}
#schedule-page .form-control:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,0.25)}
#schedule-page .price-field{background-color:#e8f8f5;font-weight:bold;color:#27ae60;font-size:14px;letter-spacing:0.5px}
#schedule-page .badge-auto{font-size:9px;background-color:#27ae60;color:#fff;padding:2px 6px;border-radius:2px;margin-left:6px;vertical-align:middle;font-weight:normal}
#schedule-page textarea.form-control{resize:vertical;min-height:70px;font-family:inherit}
#schedule-page .sa-search-wrap{position:relative}
#schedule-page .sa-results{position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid #b2c8de;border-radius:3px;max-height:220px;overflow:auto;z-index:50;box-shadow:0 4px 10px rgba(0,0,0,0.12);display:none}
#schedule-page .sa-results div{padding:8px 10px;cursor:pointer;font-size:12px;display:flex;justify-content:space-between;align-items:center;gap:10px}
#schedule-page .sa-results div:hover{background:#eef5fb}
#schedule-page .sa-results .sa-num{color:#0b5fa5;font-weight:700;letter-spacing:0.3px;white-space:nowrap}
#schedule-page .sa-results .sa-empty{padding:10px;color:#888;cursor:default;justify-content:center}
#schedule-page .form-actions{margin-top:25px;padding-top:15px;border-top:1px solid #e1e8f0;display:flex;justify-content:flex-end;gap:12px}
#schedule-page .btn-action{border:none;padding:8px 20px;font-weight:bold;font-size:12px;border-radius:3px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase;letter-spacing:0.5px;font-family:inherit}
#schedule-page .btn-action svg{width:14px;height:14px}
#schedule-page .btn-primary{background-color:#0b5fa5;color:#fff}
#schedule-page .btn-primary:hover{background-color:#004080}
#schedule-page .btn-secondary{background-color:#7f8c8d;color:#fff}
#schedule-page .btn-secondary:hover{background-color:#636e72}
#schedule-page .footer-bar{margin-top:15px;background-color:#0b5fa5;color:#fff;padding:7px 12px;font-size:11px;text-align:center;border-radius:3px}
@media (max-width:768px){
  #schedule-page .grid-2col{grid-template-columns:1fr}
  #schedule-page .grid-full{grid-column:span 1}
}
</style><div id="schedule-page">

  <!-- TOP BAR -->
  <div class="top-bar">
    <div class="title-group">
      <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5z"/></svg>
      <div class="title">SCHEDULE APPOINTMENT</div>
    </div>
    <div class="right-nav">
      <span class="hospital-tag">HMS - HEALTHCARE MANAGEMENT SYSTEM</span>
      <a href="#" class="btn-nav" onclick="saNavHome(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>HOME</a>
      <a href="#" class="btn-nav" onclick="saGoBack(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>BACK</a>
      <a href="#" class="btn-nav" onclick="saGoPassword(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>PASSWORD</a>
    </div>
  </div>

  <!-- APPOINTMENT BOOKING FORM -->
  <div class="form-wrapper">
    <div class="panel-box">
      <div class="panel-header">
        <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
        Appointment Booking &amp; Consultation Pricing
      </div>

      <form class="appointment-form" id="appointmentForm">

        <div class="form-section-title">Patient Search &amp; Type</div>

        <div class="grid-2col">

          <div class="form-group">
            <label for="saPatientSearch">Patient ID or Name <span class="required">*</span></label>
            <div class="input-wrapper sa-search-wrap">
              <input type="text" id="saPatientSearch" class="form-control" placeholder="Type Patient ID (e.g. HMS202600001) or Name" autocomplete="off" required>
              <input type="hidden" id="saPatientId">
              <div class="sa-results" id="saSearchResults"></div>
            </div>
          </div>

          <div class="form-group">
            <label for="saCategory">Consultation Category <span class="required">*</span></label>
            <select id="saCategory" class="form-control" required onchange="saCalcPrice()">
              <option value="new">New Patient Consultation</option>
              <option value="returning" selected>Old / Returning Patient Consultation</option>
            </select>
          </div>

        </div>        <div class="form-section-title" style="margin-top:20px;">Clinical Visit &amp; Attending Doctors</div>

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
              <option value="">-- Select Medical Team or Doctor --</option>
            </select>
          </div>

          <div class="form-group">
            <label for="saDate">Appointment Date <span class="required">*</span></label>
            <input type="date" id="saDate" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="saTime">Preferred Time Slot <span class="required">*</span></label>
            <input type="time" id="saTime" class="form-control" required>
          </div>

        </div>        <div class="form-section-title" style="margin-top:20px;">Consultation Pricing Calculation</div>

        <div class="grid-2col">

          <div class="form-group grid-full">
            <label for="saPrice">Generated Consultation Fee (GHS) <span class="badge-auto">AUTO CALCULATED</span></label>
            <input type="text" id="saPrice" class="form-control price-field" readonly required value="GH₵ 50.00">
          </div>

          <div class="form-group grid-full">
            <label for="saNotes">Reason for Visit / Symptoms / Notes</label>
            <textarea id="saNotes" class="form-control" placeholder="Enter brief symptoms or purpose of appointment..."></textarea>
          </div>

        </div>

        <div class="form-actions">
          <button type="button" class="btn-action btn-secondary" onclick="saResetAppointment()">
            <svg viewBox="0 0 24 24" fill="white"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>
            Reset
          </button>

          <button type="submit" class="btn-action btn-primary">
            <svg viewBox="0 0 24 24" fill="white"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
            Save Appointment
          </button>
        </div>

      </form>
    </div>

    <div class="footer-bar">HMS Support: support@example.com | Phone: +233 00 000 0000</div>
  </div>
</div>

<script>/* ================= NAV (top bar) ================= */
function saNavHome(e) { if (e) e.preventDefault(); if (window.navigateTo) window.navigateTo('dashboard'); else if (window.loadPage) window.loadPage('dashboard'); }
function saGoBack(e) { if (e) e.preventDefault(); if (window.history && window.history.length > 1) window.history.back(); else saNavHome(null); }
function saGoPassword(e) { if (e) e.preventDefault(); if (window.loadModuleTab) window.loadModuleTab('administrator'); }

/* ================= PAGE INIT ================= */
function initScheduleAppointment() {
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('saDate').value = today;
    saCalcPrice();
    saLoadReference();
    var form = document.getElementById('appointmentForm');
    if (form) form.addEventListener('submit', saSubmitAppointment);
    var searchInput = document.getElementById('saPatientSearch');
    if (searchInput) {
        searchInput.addEventListener('input', saOnSearchInput);
        searchInput.addEventListener('blur', saHideResults);
    }
}

/* ================= AUTO PRICE CALCULATION ================= */
var saBasePrices = { general: 50, specialist: 120, emergency: 150, followup: 30, pediatric: 80, dental: 100 };
var saNewPatientAddon = 30;

function saCalcPrice() {
    var category = document.getElementById('saCategory').value;
    var visitType = document.getElementById('saVisitType').value;
    var baseFee = saBasePrices[visitType] || 50;
    if (category === 'new') { baseFee += saNewPatientAddon; }
    document.getElementById('saPrice').value = 'GH₵ ' + baseFee.toFixed(2);
}

/* ================= RESET ================= */
function saResetAppointment() {
    if (confirm('Reset appointment form?')) {
        document.getElementById('appointmentForm').reset();
        var today = new Date().toISOString().split('T')[0];
        document.getElementById('saDate').value = today;
        document.getElementById('saPatientId').value = '';
        saCalcPrice();
    }
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
}/* ================= DEPARTMENTS + REAL DOCTORS ================= */
function saLoadReference() {
    var doctorSelect = document.getElementById('saDoctor');
    if (!doctorSelect) return;
    var placeholder = doctorSelect.options[0];
    doctorSelect.innerHTML = '';
    if (placeholder) doctorSelect.appendChild(placeholder);

    fetch('/hms/backend/api/users.php?action=departments')
        .then(function(r){ return r.json(); })
        .then(function(d){
            var departments = (d && d.departments) || [];
            if (!departments.length) return;
            var optGroup = document.createElement('optgroup');
            optGroup.label = '-- TEAM DOCTORS / DEPARTMENTS --';
            departments.forEach(function(dep){
                var opt = document.createElement('option');
                opt.value = 'dept:' + dep.id;
                opt.textContent = dep.name + ' Department';
                optGroup.appendChild(opt);
            });
            doctorSelect.appendChild(optGroup);
        })
        .catch(function(){ /* ignore - teams unavailable */ });

    fetch('/hms/backend/api/users.php?action=list&role=doctor')
        .then(function(r){ return r.json(); })
        .then(function(d){
            var doctors = (d && d.users) || [];
            var optGroup = document.createElement('optgroup');
            optGroup.label = '-- ALL INDIVIDUAL DOCTORS --';
            if (doctors.length) {
                doctors.forEach(function(doc){
                    var opt = document.createElement('option');
                    opt.value = 'doc:' + doc.id;
                    opt.textContent = doc.full_name || ('Doctor #' + doc.id);
                    optGroup.appendChild(opt);
                });
            } else {
                var noneOpt = document.createElement('option');
                noneOpt.disabled = true;
                noneOpt.textContent = 'No individual doctors registered yet';
                optGroup.appendChild(noneOpt);
            }
            doctorSelect.appendChild(optGroup);
        })
        .catch(function(){
            var optGroup = document.createElement('optgroup');
            optGroup.label = '-- ALL INDIVIDUAL DOCTORS --';
            var noneOpt = document.createElement('option');
            noneOpt.disabled = true;
            noneOpt.textContent = 'No individual doctors registered yet';
            optGroup.appendChild(noneOpt);
            doctorSelect.appendChild(optGroup);
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
    if (doctorValue.indexOf('dept:') === 0) { departmentId = parseInt(doctorValue.split(':')[1], 10); }
    else if (doctorValue.indexOf('doc:') === 0) { doctorId = parseInt(doctorValue.split(':')[1], 10); }

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
            if (window.showAlert) showAlert('Appointment Scheduled Successfully - ' + saVal('saDate') + ' at ' + saVal('saTime'), 'success');
            document.getElementById('appointmentForm').reset();
            var today = new Date().toISOString().split('T')[0];
            document.getElementById('saDate').value = today;
            document.getElementById('saPatientId').value = '';
            saCalcPrice();
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