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
/* --- NHIS eligibility + Claim Check Code verification (plain CSS shims for
      the NHIA tariff-compliance block; this project ships no Tailwind/Alpine) */
#register-page .nhis-verify-row{display:grid;grid-template-columns:1fr auto;gap:10px;align-items:stretch}
#register-page .nhis-verify-btn{white-space:nowrap;justify-content:center;min-width:190px}
#register-page .nhis-verify-btn[disabled]{opacity:.6;cursor:not-allowed}
#register-page .nhis-status{margin-top:10px;border-radius:4px;padding:10px 12px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;font-size:11px;display:none}
#register-page .nhis-status-main{display:flex;align-items:center;gap:10px;min-width:0}
#register-page .nhis-status-title{font-weight:700;margin:0}
#register-page .nhis-status-sub{margin:2px 0 0;font-size:10px;line-height:1.4}
#register-page .nhis-badge{font-size:9px;font-weight:700;color:#fff;padding:2px 7px;border-radius:2px;letter-spacing:.5px;text-transform:uppercase;white-space:nowrap}
#register-page .nhis-badge-active{background-color:#1e9e5a}
#register-page .nhis-badge-inactive{background-color:#c0392b}
#register-page .nhis-status-active{background-color:#eaf7f0;border:1px solid #a8dcc0}
#register-page .nhis-status-active .nhis-status-title{color:#14663a}
#register-page .nhis-status-active .nhis-status-sub{color:#2f7a51}
#register-page .nhis-status-inactive{background-color:#fdecea;border:1px solid #f0b4ae}
#register-page .nhis-status-inactive .nhis-status-title{color:#8e2b21}
#register-page .nhis-status-inactive .nhis-status-sub{color:#a4483c}
#register-page .nhis-ccc{display:flex;align-items:center;gap:8px;background-color:#fff;border:1px solid #a8dcc0;border-radius:3px;padding:4px 8px}
#register-page .nhis-ccc-label{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#6b7c86;white-space:nowrap}
#register-page .nhis-ccc-value{border:none;background:transparent;font-weight:800;color:#0b5fa5;font-size:12px;letter-spacing:1px;width:110px;outline:none;cursor:default}
#register-page .nhis-cash-tag{background-color:#f7dcd8;border:1px solid #e2a79f;color:#8e2b21;font-size:9px;font-weight:700;padding:3px 8px;border-radius:2px;text-transform:uppercase;white-space:nowrap}
#register-page .nhis-src-note{color:#8a6d3b;font-style:italic}
@media (max-width:768px){
  #register-page .grid-2col{grid-template-columns:1fr}
  #register-page .grid-full{grid-column:span 1}
  #register-page .nhis-verify-row{grid-template-columns:1fr}
  #register-page .nhis-verify-btn{min-width:0}
  #register-page .nhis-status{align-items:flex-start}
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
            <input type="date" id="dob" class="form-control" required onchange="calculateAge()" data-dp-no-time>
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
            <label for="occupation">Occupation</label>
            <input type="text" id="occupation" class="form-control" placeholder="e.g. Teacher, Trader, Civil Servant, Student (optional)">
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

          <!-- NHIS / NHIA eligibility verification.
               Plain JS + scoped CSS: this repo ships no Alpine.js or Tailwind.
               Data comes from backend/api/verify_nhis.php. Clicking Verify fills
               the hidden claim_check_code / nhis_expiry_date inputs, which are
               submitted with the registration form below. -->
          <div class="form-group grid-full">
            <label for="nhisNumber">NHIS / Insurance Card Number</label>
            <div class="nhis-verify-row">
              <input type="text" id="nhisNumber" class="form-control" placeholder="Enter NHIS card number, e.g. 202683952" autocomplete="off">
              <button type="button" id="btnVerifyNhis" class="btn-action btn-primary nhis-verify-btn" onclick="verifyNhis()">
                <svg viewBox="0 0 24 24" fill="white"><path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 6c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm4 10H8v-1c0-1.33 2.67-2 4-2s4 .67 4 2v1z"/></svg>
                <span id="nhisVerifyLabel">Verify NHIS &amp; Get CCC</span>
              </button>
            </div>

            <input type="hidden" id="claimCheckCode" name="claim_check_code">
            <input type="hidden" id="nhisExpiryDate" name="nhis_expiry_date">

            <!-- Active eligibility + auto-generated Claim Check Code -->
            <div class="nhis-status nhis-status-active" id="nhisActiveBox">
              <div class="nhis-status-main">
                <span class="nhis-badge nhis-badge-active">ACTIVE</span>
                <div>
                  <p class="nhis-status-title">NHIS Eligibility Confirmed</p>
                  <p class="nhis-status-sub">Valid until: <b id="nhisExpiryDisplay">—</b> <span id="nhisSourceNote" class="nhis-src-note"></span></p>
                </div>
              </div>
              <div class="nhis-ccc">
                <span class="nhis-ccc-label">Claim Check Code</span>
                <input type="text" id="claimCheckCodeDisplay" class="nhis-ccc-value" readonly>
              </div>
            </div>

            <!-- Inactive / expired eligibility -->
            <div class="nhis-status nhis-status-inactive" id="nhisInactiveBox">
              <div class="nhis-status-main">
                <span class="nhis-badge nhis-badge-inactive">INACTIVE</span>
                <div>
                  <p class="nhis-status-title">NHIS card inactive / expired</p>
                  <p class="nhis-status-sub" id="nhisInactiveMsg"></p>
                </div>
              </div>
              <span class="nhis-cash-tag">Switch to cash tariff</span>
            </div>
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
  </div>
</div>

<script>/* ================= NAV (top bar) ================= */
function rgNavHome(e) { if (e) e.preventDefault(); if (window.navigateTo) window.navigateTo('dashboard'); else if (window.loadPage) window.loadPage('dashboard'); }
function rgGoBack(e) { if (e) e.preventDefault(); if (window.history && window.history.length > 1) window.history.back(); else rgNavHome(null); }
function rgGoPassword(e) { if (e) e.preventDefault(); if (window.navigateTo) window.navigateTo('change-password'); else if (window.loadPage) window.loadPage('change-password'); }

/* ================= PAGE INIT ================= */
function initRegisterPatient() {
    generatePatientId();
    var form = document.getElementById('patientRegistrationForm');
    if (form) form.addEventListener('submit', submitPatientRegistration);
    // A changed card number invalidates a previously issued Claim Check Code.
    var nhisInput = document.getElementById('nhisNumber');
    if (nhisInput) nhisInput.addEventListener('input', clearNhisResult);
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
        clearNhisResult();
    }
}

/* ================= NHIS ELIGIBILITY & CLAIM CHECK CODE ================= */

/* Clears the verification result UI and the hidden CCC fields. */
function clearNhisResult() {
    ['nhisActiveBox', 'nhisInactiveBox'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
    ['claimCheckCode', 'nhisExpiryDate', 'claimCheckCodeDisplay', 'nhisInactiveMsg', 'nhisSourceNote'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
        if (el) el.textContent = '';
    });
    var exp = document.getElementById('nhisExpiryDisplay');
    if (exp) exp.textContent = '—';
}

/* POST the card number to verify_nhis.php and render the result. */
function verifyNhis() {
    var number = (document.getElementById('nhisNumber') || {}).value;
    if (!number || !number.trim()) {
        if (window.showAlert) showAlert('Please enter the NHIS card number first.', 'error');
        else alert('Please enter the NHIS card number first.');
        return;
    }

    var btn = document.getElementById('btnVerifyNhis');
    var label = document.getElementById('nhisVerifyLabel');
    var originalHtml = label ? label.innerHTML : '';

    if (btn) btn.disabled = true;
    if (label) label.textContent = 'Verifying...';

    var body = new FormData();
    body.append('nhis_number', number.trim());

    fetch('/hms/backend/api/verify_nhis.php', { method: 'POST', body: body })
        .then(function (res) { return res.json(); })
        .then(function (data) { renderNhisResult(data); })
        .catch(function (err) {
            console.error('NHIS verification error:', err);
            if (window.showAlert) showAlert('Verification failed. Please check the server connection.', 'error');
            else alert('Verification failed. Please check the server connection.');
        })
        .finally(function () {
            if (btn) btn.disabled = false;
            if (label) label.innerHTML = originalHtml;
        });
}

/* Renders the ACTIVE (green, shows CCC) or INACTIVE (red, cash tariff) panel. */
function renderNhisResult(data) {
    clearNhisResult();

    var ok = data && data.status === 'success' && data.claim_check_code;

    if (ok) {
        // Keep the CCC + expiry on the form so they are submitted with the registration.
        document.getElementById('claimCheckCode').value = data.claim_check_code;
        document.getElementById('claimCheckCodeDisplay').value = data.claim_check_code;
        document.getElementById('nhisExpiryDisplay').textContent = data.expiry_date || 'Not on file';
        if (data.expiry_date) document.getElementById('nhisExpiryDate').value = data.expiry_date;
        if (data.mock) {
            document.getElementById('nhisSourceNote').textContent =
                '(simulated eligibility — no matching patient record; connected to real NHIA data before billing)';
        }
        document.getElementById('nhisActiveBox').style.display = 'flex';
    } else {
        document.getElementById('nhisInactiveMsg').textContent =
            (data && data.message) || 'NHIS verification did not return an active eligibility status.';
        document.getElementById('nhisInactiveBox').style.display = 'flex';
    }
}/* ================= SUBMIT ================= */
function rgVal(id) { var el = document.getElementById(id); return el ? el.value : ''; }

async function submitPatientRegistration(e) {
    e.preventDefault();
    var required = { title: 'Title', firstName: 'First Name', lastName: 'Last Name', gender: 'Gender',
                     dob: 'Date of Birth', phone: 'Primary Mobile Number', address: 'Residential Area / Location' };
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
        occupation: rgVal('occupation').trim() || null,
        marital_status: rgVal('maritalStatus') || null,
        secondary_phone: rgVal('altPhone').trim() || null,
        phone: rgVal('phone').trim(),
        email: rgVal('email').trim() || null,
        address: rgVal('address').trim(),
        emergency_contact_name: rgVal('emergencyName').trim() || null,
        emergency_contact_phone: rgVal('emergencyPhone').trim() || null,
        nhia_number: rgVal('nhisNumber').trim() || null,
        claim_check_code: rgVal('claimCheckCode').trim() || null,
        nhis_expiry_date: rgVal('nhisExpiryDate').trim() || null
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