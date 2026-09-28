<?php
// Patient Registration - standalone EHMS-style registration form (SPA fragment).
// Loaded by the shell (dashboard.php) into #page-content via loadPage('register-patient').
require_once __DIR__ . '/../../backend/config/config.php';
?>
<style>
/* ============ PATIENT REGISTRATION : EHMS STANDALONE FORM ============
   Scoped under #register-page. No Bootstrap - all utilities defined here.
   Inline SVG icons used to match the wireframe; Font Awesome also available. */
#register-page{background:#dce7f2;color:#333;font-size:13px;min-height:100vh;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;padding-bottom:30px}
#register-page *,#register-page *::before,#register-page *::after{box-sizing:border-box}
#register-page .top-bar{background-color:#0b5fa5;color:#fff;padding:8px 20px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 5px rgba(0,0,0,0.15)}
#register-page .top-bar .title-group{display:flex;align-items:center;gap:10px}
#register-page .top-bar .title-group svg{width:24px;height:24px;fill:#ffffff}
#register-page .top-bar .title{font-size:16px;font-weight:bold;letter-spacing:0.5px;text-transform:uppercase}
#register-page .top-bar .right-nav{display:flex;align-items:center;gap:10px}
#register-page .top-bar .hospital-tag{font-size:11px;color:#d1e5f7;margin-right:15px;font-weight:500}
#register-page .top-bar .btn-nav{background-color:#0088cc;color:#fff;border:none;padding:5px 12px;font-size:11px;font-weight:bold;border-radius:3px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background-color 0.2s;font-family:inherit}
#register-page .top-bar .btn-nav:hover{background-color:#006699}
#register-page .form-wrapper{max-width:960px;margin:20px auto;padding:0 15px}
#register-page .panel-box{background:#fff;border:1px solid #b2c8de;border-radius:4px;overflow:hidden;box-shadow:0 3px 8px rgba(0,0,0,0.06)}
#register-page .panel-header{background-color:#0b5fa5;color:#fff;font-weight:bold;font-size:12px;padding:10px 15px;text-transform:uppercase;display:flex;align-items:center;gap:8px}
#register-page .panel-header svg{width:16px;height:16px;fill:#ffffff}#register-page .patient-form{padding:20px 25px;background-color:#f8fafc}
#register-page .form-section-title{font-size:12px;font-weight:bold;color:#0b5fa5;border-bottom:2px solid #0b5fa5;padding-bottom:4px;margin-bottom:15px;text-transform:uppercase}
#register-page .grid-2col{display:grid;grid-template-columns:1fr 1fr;gap:15px 25px}
#register-page .grid-full{grid-column:span 2}
#register-page .form-group{display:flex;flex-direction:column;gap:5px}
#register-page .form-group label{font-weight:600;color:#333;font-size:11px;text-transform:uppercase;letter-spacing:0.3px}
#register-page .form-group label .required{color:#e74c3c;font-weight:bold}
#register-page .input-wrapper{position:relative;display:flex;align-items:center}
#register-page .form-control{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;transition:border-color 0.2s, box-shadow 0.2s;font-family:inherit}
#register-page .form-control:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,0.25)}
#register-page .id-field{background-color:#eef5fb;font-weight:bold;color:#0b5fa5;letter-spacing:1px;padding-right:40px}
#register-page .btn-reload-id{position:absolute;right:5px;background:none;border:none;cursor:pointer;color:#0b5fa5;padding:4px;display:flex;align-items:center;justify-content:center;border-radius:3px}
#register-page .btn-reload-id:hover{background-color:#d0e2f3}
#register-page .btn-reload-id svg{width:16px;height:16px;fill:#0b5fa5}
#register-page .badge-auto{font-size:9px;background-color:#27ae60;color:#fff;padding:2px 6px;border-radius:2px;margin-left:6px;vertical-align:middle;font-weight:normal}
#register-page .form-actions{margin-top:25px;padding-top:15px;border-top:1px solid #e1e8f0;display:flex;justify-content:flex-end;gap:12px}
#register-page .btn-action{border:none;padding:8px 20px;font-weight:bold;font-size:12px;border-radius:3px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase;letter-spacing:0.5px;font-family:inherit}
#register-page .btn-action svg{width:14px;height:14px}
#register-page .btn-primary{background-color:#0b5fa5;color:#fff}
#register-page .btn-primary:hover{background-color:#004080}
#register-page .btn-secondary{background-color:#7f8c8d;color:#fff}
#register-page .btn-secondary:hover{background-color:#636e72}
#register-page .footer-bar{margin-top:15px;background-color:#0b5fa5;color:#fff;padding:7px 12px;font-size:11px;text-align:center;border-radius:3px}
@media (max-width:768px){
  #register-page .grid-2col{grid-template-columns:1fr}
  #register-page .grid-full{grid-column:span 1}
}
</style><div id="register-page">

  <!-- TOP BAR -->
  <div class="top-bar">
    <div class="title-group">
      <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-1.99.9-1.99 2L3 19c0 1.1.89 2 1.99 2H19c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 11h-4v4h-4v-4H6v-4h4V6h4v4h4v4z"/></svg>
      <div class="title">PATIENT REGISTRATION</div>
    </div>
    <div class="right-nav">
      <span class="hospital-tag">HMS - HEALTHCARE MANAGEMENT SYSTEM</span>
      <a href="#" class="btn-nav" onclick="rgNavHome(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>HOME</a>
      <a href="#" class="btn-nav" onclick="rgGoBack(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>BACK</a>
      <a href="#" class="btn-nav" onclick="rgGoPassword(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>PASSWORD</a>
    </div>
  </div>

  <!-- STANDALONE REGISTRATION FORM -->
  <div class="form-wrapper">
    <div class="panel-box">
      <div class="panel-header">
        <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        Personal Information Field
      </div>

      <form class="patient-form" id="patientRegistrationForm">

        <div class="form-section-title">Patient Identification &amp; Bio-Data</div>

        <div class="grid-2col">

          <div class="form-group">
            <label for="patientId">Patient ID <span class="required">*</span><span class="badge-auto">AUTO-GENERATED</span></label>
            <div class="input-wrapper">
              <input type="text" id="patientId" class="form-control id-field" readonly required>
              <button type="button" class="btn-reload-id" onclick="generatePatientId()" title="Regenerate Patient ID">
                <svg viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="title">Title <span class="required">*</span></label>
            <select id="title" class="form-control" required>
              <option value="">-- Select Title --</option>
              <option value="Mr">Mr.</option>
              <option value="Mrs">Mrs.</option>
              <option value="Ms">Ms.</option>
              <option value="Dr">Dr.</option>
              <option value="Rev">Rev.</option>
              <option value="Master">Master</option>
            </select>
          </div>          <div class="form-group">
            <label for="firstName">First Name <span class="required">*</span></label>
            <input type="text" id="firstName" class="form-control" placeholder="Enter first name" required>
          </div>

          <div class="form-group">
            <label for="lastName">Last Name / Surname <span class="required">*</span></label>
            <input type="text" id="lastName" class="form-control" placeholder="Enter last name" required>
          </div>

          <div class="form-group">
            <label for="otherNames">Other Names</label>
            <input type="text" id="otherNames" class="form-control" placeholder="Middle name(s)">
          </div>

          <div class="form-group">
            <label for="gender">Gender <span class="required">*</span></label>
            <select id="gender" class="form-control" required>
              <option value="">-- Select Gender --</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div class="form-group">
            <label for="dob">Date of Birth <span class="required">*</span></label>
            <input type="date" id="dob" class="form-control" required onchange="calculateAge()">
          </div>

          <div class="form-group">
            <label for="age">Age (Years)</label>
            <input type="number" id="age" class="form-control" placeholder="Auto-calculated or enter age">
          </div>

          <div class="form-group">
            <label for="maritalStatus">Marital Status</label>
            <select id="maritalStatus" class="form-control">
              <option value="">-- Select Status --</option>
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Divorced">Divorced</option>
              <option value="Widowed">Widowed</option>
            </select>
          </div>

          <div class="form-group">
            <label for="occupation">Occupation <span class="required">*</span></label>
            <input type="text" id="occupation" class="form-control" placeholder="e.g. Teacher, Trader, Civil Servant, Student" required>
          </div>

        </div>

        <div class="form-section-title" style="margin-top:20px;">Contact &amp; Location Details</div>

        <div class="grid-2col">

          <div class="form-group">
            <label for="phone">Primary Mobile Number <span class="required">*</span></label>
            <input type="tel" id="phone" class="form-control" placeholder="e.g. 024XXXXXXX" required>
          </div>

          <div class="form-group">
            <label for="altPhone">Secondary Phone Number</label>
            <input type="tel" id="altPhone" class="form-control" placeholder="Optional phone number">
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" class="form-control" placeholder="e.g. patient@gmail.com">
          </div>

          <div class="form-group">
            <label for="address">Residential Area / Location <span class="required">*</span></label>
            <input type="text" id="address" class="form-control" placeholder="e.g. Madina, Accra" required>
          </div>          <div class="form-group">
            <label for="emergencyName">Emergency Contact Person</label>
            <input type="text" id="emergencyName" class="form-control" placeholder="Full name of next of kin">
          </div>

          <div class="form-group">
            <label for="emergencyPhone">Emergency Contact Phone</label>
            <input type="tel" id="emergencyPhone" class="form-control" placeholder="Next of kin phone number">
          </div>

          <div class="form-group grid-full">
            <label for="nhisNumber">NHIS / Insurance Card Number</label>
            <input type="text" id="nhisNumber" class="form-control" placeholder="Enter NHIS card number if applicable">
          </div>

        </div>

        <div class="form-actions">
          <button type="button" class="btn-action btn-secondary" onclick="resetForm()">
            <svg viewBox="0 0 24 24" fill="white"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>
            Reset Form
          </button>

          <button type="submit" class="btn-action btn-primary">
            <svg viewBox="0 0 24 24" fill="white"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
            Register Patient
          </button>
        </div>

      </form>
    </div>

    <div class="footer-bar">HMS Support: support@example.com | Phone: +233 00 000 0000</div>
  </div>
</div>

<script>/* ================= NAV (top bar) ================= */
function rgNavHome(e) { if (e) e.preventDefault(); if (window.navigateTo) window.navigateTo('dashboard'); else if (window.loadPage) window.loadPage('dashboard'); }
function rgGoBack(e) { if (e) e.preventDefault(); if (window.history && window.history.length > 1) window.history.back(); else rgNavHome(null); }
function rgGoPassword(e) { if (e) e.preventDefault(); if (window.loadModuleTab) window.loadModuleTab('administrator'); }

/* ================= PAGE INIT ================= */
function initRegisterPatient() {
    generatePatientId();
    var form = document.getElementById('patientRegistrationForm');
    if (form) form.addEventListener('submit', submitPatientRegistration);
}

/* Auto Patient ID Generator - HMS + current year + 5 random digits (matches system format) */
function generatePatientId() {
    var year = new Date().getFullYear();
    var randomNum = Math.floor(10000 + Math.random() * 90000);
    document.getElementById('patientId').value = 'HMS' + year + randomNum;
}

/* Auto Age Calculator based on Date of Birth */
function calculateAge() {
    var dobVal = document.getElementById('dob').value;
    if (!dobVal) return;
    var dob = new Date(dobVal);
    var today = new Date();
    var age = today.getFullYear() - dob.getFullYear();
    var monthDiff = today.getMonth() - dob.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) { age--; }
    if (age >= 0) { document.getElementById('age').value = age; }
}

/* Form Reset Handler */
function resetForm() {
    if (confirm('Are you sure you want to clear all entered fields?')) {
        document.getElementById('patientRegistrationForm').reset();
        generatePatientId();
    }
}/* ================= SUBMIT ================= */
function rgVal(id) { var el = document.getElementById(id); return el ? el.value : ''; }

async function submitPatientRegistration(e) {
    e.preventDefault();
    var required = { title: 'Title', firstName: 'First Name', lastName: 'Last Name', gender: 'Gender',
                     dob: 'Date of Birth', occupation: 'Occupation', phone: 'Primary Mobile Number', address: 'Residential Area / Location' };
    var missing = [];
    for (var k in required) {
        if (required.hasOwnProperty(k) && !rgVal(k).trim()) { missing.push(required[k]); }
    }
    if (missing.length) {
        if (window.showAlert) showAlert('Please complete: ' + missing.join(', '), 'error');
        return;
    }

    var data = {
        first_name: rgVal('firstName').trim(),
        middle_name: rgVal('otherNames').trim() || null,
        last_name: rgVal('lastName').trim(),
        date_of_birth: rgVal('dob'),
        gender: rgVal('gender'),
        title: rgVal('title'),
        occupation: rgVal('occupation').trim(),
        marital_status: rgVal('maritalStatus') || null,
        secondary_phone: rgVal('altPhone').trim() || null,
        phone: rgVal('phone').trim(),
        email: rgVal('email').trim() || null,
        address: rgVal('address').trim(),
        emergency_contact_name: rgVal('emergencyName').trim() || null,
        emergency_contact_phone: rgVal('emergencyPhone').trim() || null,
        nhia_number: rgVal('nhisNumber').trim() || null
    };

    try {
        var r = await fetch('/hms/backend/api/patients.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var d = await r.json();
        if (d.success) {
            var p = d.patient || {};
            var fullName = [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ');
            if (window.showAlert) showAlert('Patient Registered Successfully - ' + (p.hospital_number || '') + ' (' + fullName + ')', 'success');
            document.getElementById('patientRegistrationForm').reset();
            generatePatientId();
        } else {
            var err = d.error || (d.errors ? Object.values(d.errors)[0] : null) || 'Registration failed';
            if (window.showAlert) showAlert(err, 'error');
        }
    } catch (err) {
        console.error('Patient registration error:', err);
        if (window.showAlert) showAlert('Network error. Please try again.', 'error');
    }
}
</script>