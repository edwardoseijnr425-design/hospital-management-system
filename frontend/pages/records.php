<style>
/* ============ RECORDS : NEW VISIT / SCHEDULE APPOINTMENT FULL PAGE ============
   Scoped under #nv-page. No Bootstrap - all utilities defined here so the
   existing records list card (which uses the shell's .card shims) is unaffected. */
#nv-page{background:#dce7f2;color:#333;font-size:13px;min-height:100vh;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;padding-bottom:30px}
#nv-page *,#nv-page *::before,#nv-page *::after{box-sizing:border-box}
#nv-page .top-bar{background-color:#0b5fa5;color:#fff;padding:8px 20px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 5px rgba(0,0,0,0.15)}
#nv-page .top-bar .title-group{display:flex;align-items:center;gap:10px}
#nv-page .top-bar .title-group svg{width:24px;height:24px;fill:#ffffff}
#nv-page .top-bar .title{font-size:16px;font-weight:bold;letter-spacing:0.5px;text-transform:uppercase}
#nv-page .top-bar .right-nav{display:flex;align-items:center;gap:10px}
#nv-page .top-bar .hospital-tag{font-size:11px;color:#d1e5f7;margin-right:15px;font-weight:500}
#nv-page .top-bar .btn-nav{background-color:#0088cc;color:#fff;border:none;padding:5px 12px;font-size:11px;font-weight:bold;border-radius:3px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background-color 0.2s;font-family:inherit}
#nv-page .top-bar .btn-nav:hover{background-color:#006699}
#nv-page .form-wrapper{max-width:960px;margin:20px auto;padding:0 15px}
#nv-page .panel-box{background:#fff;border:1px solid #b2c8de;border-radius:4px;overflow:hidden;box-shadow:0 3px 8px rgba(0,0,0,0.06)}
#nv-page .panel-header{background-color:#0b5fa5;color:#fff;font-weight:bold;font-size:12px;padding:10px 15px;text-transform:uppercase;display:flex;align-items:center;gap:8px}
#nv-page .panel-header svg{width:16px;height:16px;fill:#ffffff}
#nv-page .appointment-form{padding:20px 25px;background-color:#f8fafc}
#nv-page .form-section-title{font-size:12px;font-weight:bold;color:#0b5fa5;border-bottom:2px solid #0b5fa5;padding-bottom:4px;margin-bottom:15px;text-transform:uppercase}
#nv-page .grid-2col{display:grid;grid-template-columns:1fr 1fr;gap:15px 25px}
#nv-page .grid-full{grid-column:span 2}
#nv-page .form-group{display:flex;flex-direction:column;gap:5px}
#nv-page .form-group label{font-weight:600;color:#333;font-size:11px;text-transform:uppercase;letter-spacing:0.3px}
#nv-page .form-group label .required{color:#e74c3c;font-weight:bold}
#nv-page .input-wrapper{position:relative;display:flex;align-items:center}
#nv-page .form-control{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;transition:border-color 0.2s, box-shadow 0.2s;font-family:inherit}
#nv-page .form-control:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,0.25)}
#nv-page textarea.form-control{resize:vertical;min-height:70px;font-family:inherit}
#nv-page .price-field{background-color:#e8f8f5;font-weight:bold;color:#27ae60;font-size:14px;letter-spacing:0.5px}
#nv-page .badge-auto{font-size:9px;background-color:#27ae60;color:#fff;padding:2px 6px;border-radius:2px;margin-left:6px;vertical-align:middle;font-weight:normal}
#nv-page .date-scheduling-card{background:#fff;border:1px solid #c0d4e8;border-left:4px solid #0b5fa5;border-radius:4px;padding:14px 16px;display:grid;grid-template-columns:1fr 1fr;gap:15px;align-items:start}
#nv-page .date-input-container{display:flex;flex-direction:column;gap:6px}
#nv-page .date-input-label,#nv-page .time-slots-container>label{font-weight:600;color:#333;font-size:11px;text-transform:uppercase;letter-spacing:0.3px}
#nv-page .date-input-container label .required,#nv-page .time-slots-container label .required{color:#e74c3c;font-weight:bold}
#nv-page .date-input-wrapper{position:relative;display:flex;align-items:center}
#nv-page .date-input-wrapper svg{position:absolute;left:10px;width:16px;height:16px;fill:#0b5fa5;pointer-events:none}
#nv-page .date-input-wrapper input[type="date"]{padding-left:34px;font-weight:600;color:#0b5fa5;cursor:pointer}
#nv-page .formal-date-badge{background-color:#eef5fb;border:1px dashed #0b5fa5;padding:6px 10px;border-radius:3px;font-size:11px;font-weight:bold;color:#0b5fa5;display:flex;align-items:center;gap:6px}
#nv-page .formal-date-badge svg{width:14px;height:14px;fill:#0b5fa5}
#nv-page .time-slots-container{display:flex;flex-direction:column;gap:6px}
#nv-page .time-chips-group{display:flex;gap:6px;flex-wrap:wrap}
#nv-page .time-chip{background-color:#f0f4f8;border:1px solid #b2c8de;padding:5px 9px;border-radius:3px;font-size:10px;font-weight:bold;color:#444;cursor:pointer;transition:all 0.2s;font-family:inherit}
#nv-page .time-chip:hover{background-color:#d1e3f3;color:#0b5fa5}
#nv-page .time-chip.active{background-color:#0b5fa5;color:#fff;border-color:#0b5fa5}
#nv-page input[type="time"].form-control{font-weight:600;color:#0b5fa5}
#nv-page .nv-search-wrap{position:relative}
#nv-page .nv-results{position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid #b2c8de;border-radius:3px;max-height:220px;overflow:auto;z-index:50;box-shadow:0 4px 10px rgba(0,0,0,0.12);display:none}
#nv-page .nv-results div{padding:8px 10px;cursor:pointer;font-size:12px;display:flex;justify-content:space-between;align-items:center;gap:10px}
#nv-page .nv-results div:hover{background:#eef5fb}
#nv-page .nv-results .nv-num{color:#0b5fa5;font-weight:700;letter-spacing:0.3px;white-space:nowrap}
#nv-page .nv-results .nv-empty{padding:10px;color:#888;cursor:default;justify-content:center}
#nv-page .form-actions{margin-top:25px;padding-top:15px;border-top:1px solid #e1e8f0;display:flex;justify-content:flex-end;gap:12px}
#nv-page .btn-action{border:none;padding:8px 20px;font-weight:bold;font-size:12px;border-radius:3px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase;letter-spacing:0.5px;font-family:inherit}
#nv-page .btn-action svg{width:14px;height:14px}
#nv-page .btn-primary{background-color:#0b5fa5;color:#fff}
#nv-page .btn-primary:hover{background-color:#004080}
#nv-page .btn-secondary{background-color:#7f8c8d;color:#fff}
#nv-page .btn-secondary:hover{background-color:#636e72}
#nv-page .quick-hint{color:#888;font-size:11px;margin-top:2px}
@media (max-width:768px){
  #nv-page .grid-2col,#nv-page .date-scheduling-card{grid-template-columns:1fr}
  #nv-page .grid-full{grid-column:span 1}
}
</style>
<div id="patientRecordsListContainer">
<div class="card">
    <div class="card-header">
        <h2>Patient Record Management</h2>
        <button class="btn btn-primary btn-sm" id="register-patient-btn">Register Patient</button>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <input type="text" id="search-patients" placeholder="Search by name, hospital number, or phone...">
            </div>
            <div class="form-group">
                <select id="filter-sponsor">
                    <option value="">All Sponsors</option>
                </select>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Hospital No.</th>
                        <th>Name</th>
                        <th>Age/Gender</th>
                        <th>Phone</th>
                        <th>Sponsor</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="patients-table">
                    <tr>
                        <td colspan="7" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<!-- Patient Registration Modal -->
<div class="modal" id="patient-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="patient-modal-title">Register Patient</h3>
            <button class="modal-close" id="close-patient-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="patient-form">
                <input type="hidden" id="patient-id">
                
                <h4>Personal Information</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient-firstname">First Name *</label>
                        <input type="text" id="patient-firstname" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="patient-middlename">Middle Name</label>
                        <input type="text" id="patient-middlename" name="middle_name">
                    </div>
                    <div class="form-group">
                        <label for="patient-lastname">Last Name *</label>
                        <input type="text" id="patient-lastname" name="last_name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient-dob">Date of Birth *</label>
                        <input type="date" id="patient-dob" name="date_of_birth" required>
                    </div>
                    <div class="form-group">
                        <label for="patient-gender">Gender *</label>
                        <select id="patient-gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="patient-bloodgroup">Blood Group</label>
                        <select id="patient-bloodgroup" name="blood_group">
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                </div>
                
                <h4>Contact Information</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient-phone">Phone</label>
                        <input type="tel" id="patient-phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="patient-email">Email</label>
                        <input type="email" id="patient-email" name="email">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="patient-address">Address</label>
                    <textarea id="patient-address" name="address" rows="2"></textarea>
                </div>
                
                <h4>Emergency Contact</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="emergency-name">Emergency Contact Name</label>
                        <input type="text" id="emergency-name" name="emergency_contact_name">
                    </div>
                    <div class="form-group">
                        <label for="emergency-phone">Emergency Contact Phone</label>
                        <input type="tel" id="emergency-phone" name="emergency_contact_phone">
                    </div>
                </div>
                
                <h4>Insurance/Sponsor</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient-sponsor">Sponsor</label>
                        <select id="patient-sponsor" name="sponsor_id">
                            <option value="">Select Sponsor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="patient-nhia">NHIA Number</label>
                        <input type="text" id="patient-nhia" name="nhia_number">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Patient</button>
                    <button type="button" class="btn btn-secondary" id="cancel-patient">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- New Visit Modal -->
<div class="modal" id="visit-modal">
    <div class="modal-content" style="max-width:560px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-door-open"></i> New Visit</h3>
            <button class="modal-close" id="close-visit-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="visit-form">
                <input type="hidden" id="visit-patient-id">
                <div class="form-group">
                    <label>Patient</label>
                    <div id="visit-patient-display" style="background:#F0F4F8;border:1px solid #DCE4EC;border-radius:6px;padding:10px 12px;font-size:13px;font-weight:700;color:#0F2D59;"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="visit-type">Visit Type *</label>
                        <select id="visit-type" required>
                            <option value="">Select Visit Type</option>
                            <option value="opd">OPD (Outpatient)</option>
                            <option value="ipd">IPD (Inpatient)</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="visit-department">Department *</label>
                        <select id="visit-department" required>
                            <option value="">Select Department</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="visit-complaint">Chief Complaint</label>
                    <textarea id="visit-complaint" rows="2" placeholder="Presenting complaint..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Start Visit</button>
                    <button type="button" class="btn btn-secondary" id="cancel-visit">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Patient Identification Summary Modal (old EHMS patient bar) -->
<div class="modal" id="patient-summary-modal">
    <div class="modal-content" style="max-width:860px;">
        <div class="modal-header">
            <h3>PATIENT IDENTIFICATION</h3>
            <button class="modal-close" id="close-summary-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;">
                <div style="grid-column:span 2;border-left:3px solid #0072BC;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Patient Name</div>
                    <div style="font-size:16px;font-weight:800;color:#0F2D59;" id="patientBarName">-</div>
                </div>
                <div style="grid-column:span 2;border-left:3px solid #0072BC;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Hospital Number</div>
                    <div style="font-size:16px;font-weight:800;color:#0072BC;" id="patientBarId">-</div>
                </div>
                <div style="border-left:3px solid #80C342;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Gender</div>
                    <div style="font-size:14px;font-weight:700;" id="patientBarGender">-</div>
                </div>
                <div style="border-left:3px solid #80C342;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Age</div>
                    <div style="font-size:14px;font-weight:700;" id="patientBarAge">-</div>
                </div>
                <div style="grid-column:span 2;border-left:3px solid #F39C12;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Bed / Ward</div>
                    <div style="font-size:14px;font-weight:700;" id="patientBarBedWard">Not admitted</div>
                </div>
                <div style="border-left:3px solid #E53E3E;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Blood Pressure</div>
                    <div style="font-size:14px;font-weight:700;" id="patientBarBP">-</div>
                </div>
                <div style="border-left:3px solid #E53E3E;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Pulse</div>
                    <div style="font-size:14px;font-weight:700;" id="patientBarPulse">-</div>
                </div>
                <div style="border-left:3px solid #E53E3E;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">SpO2</div>
                    <div style="font-size:14px;font-weight:700;" id="patientBarSpO2">-</div>
                </div>
                <div style="grid-column:span 2;border-left:3px solid #9C27B0;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Allergies</div>
                    <div style="font-size:14px;" id="patientBarAllergies">None recorded</div>
                </div>
                <div style="grid-column:span 2;border-left:3px solid #0072BC;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Latest Diagnosis</div>
                    <div style="font-size:14px;" id="patientBarDiagnosis">-</div>
                </div>
                <div style="grid-column:span 2;border-left:3px solid #80C342;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Latest Prescription</div>
                    <div style="font-size:14px;" id="patientBarPrescription">-</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FULL SCHEDULE APPOINTMENT / NEW VISIT CONTAINER (Image 2) -->
<div id="fullNewVisitContainer" style="display:none;">
<div id="nv-page">

  <!-- TOP BAR -->
  <div class="top-bar">
    <div class="title-group">
      <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5z"/></svg>
      <div class="title">New Visit &amp; Schedule Appointment</div>
    </div>
    <div class="right-nav">
      <span class="hospital-tag">HMS - HEALTHCARE MANAGEMENT SYSTEM</span>
      <a href="#" class="btn-nav" onclick="nvNavHome(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>HOME</a>
      <a href="#" class="btn-nav" onclick="nvCloseFullPage(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>BACK</a>
      <a href="#" class="btn-nav" onclick="nvGoPassword(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>PASSWORD</a>
    </div>
  </div>

  <!-- APPOINTMENT BOOKING FORM -->
  <div class="form-wrapper">
    <div class="panel-box">
      <div class="panel-header">
        <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
        Appointment Booking &amp; Consultation Pricing
      </div>

      <form class="appointment-form" id="nvForm">

        <div class="form-section-title">Patient Identification &amp; Consultation Type</div>

        <div class="grid-2col">

          <div class="form-group">
            <label for="nvPatientSearch">Patient ID or Name <span class="required">*</span></label>
            <div class="input-wrapper nv-search-wrap">
              <input type="text" id="nvPatientSearch" class="form-control" placeholder="Type Patient ID (e.g. HMS202600001) or Name" autocomplete="off" required>
              <input type="hidden" id="nvPatientId">
              <div class="nv-results" id="nvSearchResults"></div>
            </div>
            <div class="quick-hint">Auto-filled from the selected patient &mdash; type to change.</div>
          </div>

          <div class="form-group">
            <label for="nvCategory">Consultation Category <span class="required">*</span></label>
            <select id="nvCategory" class="form-control" required onchange="nvCalcPrice()">
              <option value="new">New Patient Consultation</option>
              <option value="returning" selected>Old / Returning Patient Consultation</option>
            </select>
          </div>

        </div>

        <div class="form-section-title" style="margin-top:20px;">Clinical Specialty &amp; Attending Doctor</div>

        <div class="grid-2col">

          <div class="form-group">
            <label for="nvVisitType">Visit / Specialty Type <span class="required">*</span></label>
            <select id="nvVisitType" class="form-control" required onchange="nvCalcPrice()">
              <option value="general">General Outpatient (OPD)</option>
              <option value="specialist">Specialist Consultation (Gynaecology, ENT, Cardiology)</option>
              <option value="emergency">Emergency / Urgent Care</option>
              <option value="followup">Routine Follow-Up / Results Review</option>
              <option value="pediatric">Pediatric Care Consultation</option>
              <option value="dental">Dental &amp; Oral Care</option>
            </select>
          </div>

          <div class="form-group">
            <label for="nvDoctor">Attending Doctor / Medical Team <span class="required">*</span></label>
            <select id="nvDoctor" class="form-control" required>
              <option value="">-- Select Doctor or Medical Team --</option>
            </select>
          </div>

        </div>

        <div class="form-section-title" style="margin-top:20px;">Appointment Schedule (Date &amp; Time)</div>

        <div class="date-scheduling-card">

          <!-- DATE PICKER COLUMN -->
          <div class="date-input-container">
            <label class="date-input-label" for="nvDate">Select Appointment Date <span class="required">*</span></label>
            <div class="date-input-wrapper">
              <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
              <input type="date" id="nvDate" class="form-control" required onchange="nvUpdateFormalDate()">
            </div>
            <div class="formal-date-badge" id="nvFormalDateBadge">
              <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
              <span id="nvFormalDateText">-- select date --</span>
            </div>
          </div>

          <!-- TIME SLOT COLUMN -->
          <div class="time-slots-container">
            <label for="nvTime">Select Preferred Time Slot <span class="required">*</span></label>
            <div class="time-chips-group">
              <button type="button" class="time-chip" data-time="08:30" onclick="nvSelectTimeChip('08:30', this)">08:30 AM</button>
              <button type="button" class="time-chip active" data-time="10:00" onclick="nvSelectTimeChip('10:00', this)">10:00 AM</button>
              <button type="button" class="time-chip" data-time="11:30" onclick="nvSelectTimeChip('11:30', this)">11:30 AM</button>
              <button type="button" class="time-chip" data-time="14:00" onclick="nvSelectTimeChip('14:00', this)">02:00 PM</button>
              <button type="button" class="time-chip" data-time="15:30" onclick="nvSelectTimeChip('15:30', this)">03:30 PM</button>
            </div>
            <input type="time" id="nvTime" class="form-control" value="10:00" required style="margin-top:4px;font-weight:600;color:#0b5fa5;">
          </div>

        </div>

        <div class="form-section-title" style="margin-top:20px;">Consultation Pricing &amp; Notes</div>

        <div class="grid-2col">

          <div class="form-group grid-full">
            <label for="nvPrice">Generated Consultation Fee (GHS) <span class="badge-auto">AUTO CALCULATED</span></label>
            <input type="text" id="nvPrice" class="form-control price-field" readonly required value="GH₵ 50.00">
          </div>

          <div class="form-group grid-full">
            <label for="nvNotes">Reason for Visit / Clinical Notes</label>
            <textarea id="nvNotes" class="form-control" rows="2" placeholder="Enter brief symptoms or purpose of appointment..."></textarea>
          </div>

        </div>

        <div class="form-actions">
          <button type="button" class="btn-action btn-secondary" onclick="nvResetForm()">
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
  </div>
</div>
</div>

<script>
let patientsData = [];
let sponsorsData = [];

async function initRecords() {
    await loadSponsors();
    await loadDepartments();
    await loadPatients();
    setupEventListeners();
}

async function loadSponsors() {
    try {
        const response = await fetch('/hms/backend/api/sponsors.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            sponsorsData = data.sponsors;
            
            const filterSelect = document.getElementById('filter-sponsor');
            const patientSelect = document.getElementById('patient-sponsor');
            
            sponsorsData.forEach(sponsor => {
                filterSelect.innerHTML += `<option value="${sponsor.id}">${sponsor.name}</option>`;
                patientSelect.innerHTML += `<option value="${sponsor.id}">${sponsor.name}</option>`;
            });
        }
    } catch (error) {
        console.error('Sponsors load error:', error);
    }
}

async function loadPatients(filters = {}) {
    try {
        const queryParams = new URLSearchParams(filters);
        const response = await fetch(`/hms/backend/api/patients.php?${queryParams}`);
        const data = await response.json();
        
        if (data.success) {
            patientsData = data.patients;
            renderPatientsTable();
        }
    } catch (error) {
        console.error('Patients load error:', error);
    }
}

function renderPatientsTable() {
    const tbody = document.getElementById('patients-table');
    
    if (!patientsData || patientsData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No patients found</td></tr>';
        return;
    }
    
    tbody.innerHTML = patientsData.map(patient => {
        const age = calculateAge(patient.date_of_birth);
        return `
            <tr>
                <td><strong>${patient.hospital_number}</strong></td>
                <td>${patient.first_name} ${patient.middle_name ? patient.middle_name + ' ' : ''}${patient.last_name}</td>
                <td>${age} yrs / ${patient.gender}</td>
                <td>${patient.phone || '-'}</td>
                <td>${patient.sponsor_name || 'Self-Pay'}</td>
                <td>${formatDate(patient.registration_date)}</td>
                <td>
                    <button class="btn btn-sm btn-secondary" onclick="viewPatient(${patient.id})">View</button>
                    <button class="btn btn-sm btn-primary" onclick="openFullNewVisitPage(${patient.id})">New Visit</button>
                </td>
            </tr>
        `;
    }).join('');
}

function setupEventListeners() {
    document.getElementById('register-patient-btn').addEventListener('click', () => navigateTo('register-patient'));
    document.getElementById('close-patient-modal').addEventListener('click', closePatientModal);
    document.getElementById('cancel-patient').addEventListener('click', closePatientModal);
    
    document.getElementById('patient-form').addEventListener('submit', handlePatientSubmit);
    
    document.getElementById('close-visit-modal').addEventListener('click', closeVisitModal);
    document.getElementById('cancel-visit').addEventListener('click', closeVisitModal);
    document.getElementById('visit-form').addEventListener('submit', handleVisitSubmit);
    
    document.getElementById('close-summary-modal').addEventListener('click', closeSummaryModal);
    document.getElementById('patient-summary-modal').addEventListener('click', (e) => {
        if (e.target === document.getElementById('patient-summary-modal')) closeSummaryModal();
    });
    
    document.getElementById('nvForm').addEventListener('submit', nvSubmit);
    
    var nvSearchInput = document.getElementById('nvPatientSearch');
    if (nvSearchInput) {
        nvSearchInput.addEventListener('input', nvOnSearchInput);
        nvSearchInput.addEventListener('blur', nvHideResults);
    }
    
    document.getElementById('search-patients').addEventListener('input', debounce(function() {
        if (this.value.length >= 2) {
            loadPatients({ search: this.value });
        } else if (this.value.length === 0) {
            loadPatients();
        }
    }, 300));
    
    document.getElementById('filter-sponsor').addEventListener('change', function() {
        loadPatients({ sponsor_id: this.value });
    });
}

function openPatientModal(patient = null) {
    const modal = document.getElementById('patient-modal');
    const title = document.getElementById('patient-modal-title');
    const form = document.getElementById('patient-form');
    
    form.reset();
    document.getElementById('patient-id').value = '';
    
    if (patient) {
        title.textContent = 'Edit Patient';
        document.getElementById('patient-id').value = patient.id;
        document.getElementById('patient-firstname').value = patient.first_name;
        document.getElementById('patient-middlename').value = patient.middle_name || '';
        document.getElementById('patient-lastname').value = patient.last_name;
        document.getElementById('patient-dob').value = patient.date_of_birth;
        document.getElementById('patient-gender').value = patient.gender;
        document.getElementById('patient-bloodgroup').value = patient.blood_group || '';
        document.getElementById('patient-phone').value = patient.phone || '';
        document.getElementById('patient-email').value = patient.email || '';
        document.getElementById('patient-address').value = patient.address || '';
        document.getElementById('emergency-name').value = patient.emergency_contact_name || '';
        document.getElementById('emergency-phone').value = patient.emergency_contact_phone || '';
        document.getElementById('patient-sponsor').value = patient.sponsor_id || '';
        document.getElementById('patient-nhia').value = patient.nhia_number || '';
    } else {
        title.textContent = 'Register Patient';
    }
    
    modal.classList.add('show');
}

function closePatientModal() {
    document.getElementById('patient-modal').classList.remove('show');
}

async function handlePatientSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const patientId = document.getElementById('patient-id').value;
    
    try {
        const url = patientId 
            ? `/hms/backend/api/patients.php?id=${patientId}`
            : '/hms/backend/api/patients.php?action=create';
        
        const method = patientId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(patientId ? 'Patient updated successfully' : 'Patient registered successfully', 'success');
            closePatientModal();
            await loadPatients();
        } else {
            showAlert(result.error || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Patient save error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function viewPatient(patientId) {
    loadPatientSummary(patientId);
}

// Patient identification summary (old EHMS patient bar): demographics + bed/ward + latest vitals/diagnosis/prescription
async function loadPatientSummary(patientId) {
    try {
        const response = await fetch(`/hms/backend/api/patients.php?action=summary&id=${patientId}`);
        const data = await response.json();
        if (!data.success) {
            showAlert(data.error || 'Failed to load patient summary', 'error');
            return;
        }
        populatePatientIdentificationBar(data.patient);
        document.getElementById('patient-summary-modal').classList.add('show');
    } catch (error) {
        console.error('Patient summary load error:', error);
        showAlert('Failed to load patient summary', 'error');
    }
}

function populatePatientIdentificationBar(p) {
    const set = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = (val === null || val === undefined || val === '') ? '-' : val;
    };
    const vit = p.vitals || {};
    const has = v => (v !== null && v !== undefined && v !== '');
    set('patientBarName', p.full_name);
    set('patientBarId', p.hospital_number);
    set('patientBarGender', p.gender);
    set('patientBarAge', has(p.age) ? p.age + ' yrs' : '-');
    set('patientBarBedWard', p.bed_ward || 'Not admitted');
    set('patientBarBP', vit.blood_pressure || '-');
    set('patientBarPulse', has(vit.pulse) ? vit.pulse + ' bpm' : '-');
    set('patientBarSpO2', has(vit.spo2) ? vit.spo2 + '%' : '-');
    set('patientBarAllergies', 'None recorded');
    set('patientBarDiagnosis', p.diagnosis || 'No diagnosis recorded');
    set('patientBarPrescription', p.latest_rx || 'No prescription');
}

function closeSummaryModal() {
    document.getElementById('patient-summary-modal').classList.remove('show');
}

function createVisit(patientId) {
    // Open the New Visit modal with the patient pre-selected
    const patient = patientsData.find(p => parseInt(p.id, 10) === patientId);
    if (!patient) {
        showAlert('Patient not found', 'error');
        return;
    }
    const display = `${patient.first_name} ${patient.middle_name ? patient.middle_name + ' ' : ''}${patient.last_name} — ${patient.hospital_number}`;
    document.getElementById('visit-form').reset();
    document.getElementById('visit-patient-id').value = patient.id;
    document.getElementById('visit-patient-display').textContent = display;
    document.getElementById('visit-modal').classList.add('show');
}

async function loadDepartments() {
    try {
        const response = await fetch('/hms/backend/api/users.php?action=departments');
        const data = await response.json();
        if (data.success) {
            const sel = document.getElementById('visit-department');
            (data.departments || []).forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.name;
                sel.appendChild(opt);
            });
        }
    } catch (error) {
        console.error('Departments load error:', error);
    }
}

function closeVisitModal() {
    document.getElementById('visit-modal').classList.remove('show');
}

async function handleVisitSubmit(e) {
    e.preventDefault();

    const patientId = document.getElementById('visit-patient-id').value;
    if (!patientId) {
        showAlert('Patient not selected', 'error');
        return;
    }

    const data = {
        patient_id: patientId,
        visit_type: document.getElementById('visit-type').value,
        department_id: document.getElementById('visit-department').value,
        chief_complaint: document.getElementById('visit-complaint').value
    };

    if (!data.visit_type || !data.department_id) {
        showAlert('Visit type and department are required', 'error');
        return;
    }

    try {
        const response = await fetch('/hms/backend/api/visits.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            const v = result.visit || {};
            showAlert(`Visit started — ${v.visit_number || ('Visit #' + result.visit_id)}`, 'success');
            closeVisitModal();
        } else {
            showAlert((result.error || (result.errors && Object.values(result.errors)[0])) || 'Visit creation failed', 'error');
        }
    } catch (error) {
        console.error('Visit create error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function calculateAge(dateOfBirth) {
    const today = new Date();
    const birthDate = new Date(dateOfBirth);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

function formatDate(dateString) {
    return fmtDate(dateString);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/* ================= NEW VISIT / SCHEDULE APPOINTMENT ROUTER (Image 2) ================= */

/* ---- Open the full page from the NEW VISIT button, auto-filling the patient ---- */
function openFullNewVisitPage(patientId) {
    var patient = patientsData.find(p => parseInt(p.id, 10) === parseInt(patientId, 10));
    if (!patient) { showAlert('Patient not found', 'error'); return; }

    var name = [patient.first_name, patient.middle_name, patient.last_name].filter(Boolean).join(' ');
    document.getElementById('nvPatientSearch').value = name + ' — ' + patient.hospital_number;
    document.getElementById('nvPatientId').value = patient.id;

    // Defaults for the rest of the form
    document.getElementById('nvCategory').value = 'returning';
    document.getElementById('nvVisitType').value = 'general';
    document.getElementById('nvDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('nvTime').value = '10:00';
    document.getElementById('nvNotes').value = '';
    document.getElementById('nvDoctor').value = '';

    var chips = document.querySelectorAll('#nv-page .time-chip');
    for (var i = 0; i < chips.length; i++) {
        chips[i].classList.toggle('active', chips[i].getAttribute('data-time') === '10:00');
    }
    nvUpdateFormalDate();
    nvCalcPrice();
    nvLoadReference();

    document.getElementById('patientRecordsListContainer').style.display = 'none';
    document.getElementById('fullNewVisitContainer').style.display = 'block';
    document.getElementById('fullNewVisitContainer').scrollIntoView();
}

/* ---- Close the full page, back to the records list ---- */
function nvCloseFullPage(e) {
    if (e) e.preventDefault();
    document.getElementById('fullNewVisitContainer').style.display = 'none';
    document.getElementById('patientRecordsListContainer').style.display = '';
}

function nvNavHome(e) {
    if (e) e.preventDefault();
    if (window.navigateTo) window.navigateTo('dashboard');
    else if (window.loadPage) window.loadPage('dashboard');
}

function nvGoPassword(e) {
    if (e) e.preventDefault();
    if (window.loadModuleTab) window.loadModuleTab('administrator');
}

function nvVal(id) { var el = document.getElementById(id); return el ? el.value : ''; }

/* ---- Formal date display ---- */
function nvUpdateFormalDate() {
    var dateVal = document.getElementById('nvDate').value;
    if (!dateVal) return;
    var dateObj = new Date(dateVal);
    var formalString = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('nvFormalDateText').innerText = formalString;
}

/* ---- Time slot chips ---- */
function nvSelectTimeChip(timeVal, chipEl) {
    document.getElementById('nvTime').value = timeVal;
    var chips = document.querySelectorAll('#nv-page .time-chip');
    for (var i = 0; i < chips.length; i++) { chips[i].classList.remove('active'); }
    chipEl.classList.add('active');
}

/* ---- Auto-calculated consultation fee (display only) ---- */
var nvBasePrices = { general: 50, specialist: 120, emergency: 150, followup: 30, pediatric: 80, dental: 100 };
var nvNewPatientAddon = 30;

function nvCalcPrice() {
    var category = document.getElementById('nvCategory').value;
    var visitType = document.getElementById('nvVisitType').value;
    var baseFee = nvBasePrices[visitType] || 50;
    if (category === 'new') { baseFee += nvNewPatientAddon; }
    document.getElementById('nvPrice').value = 'GH₵ ' + baseFee.toFixed(2);
}

/* ---- Reset ---- */
function nvResetForm() {
    if (confirm('Reset the New Visit form?')) {
        document.getElementById('nvPatientSearch').value = '';
        document.getElementById('nvPatientId').value = '';
        document.getElementById('nvCategory').value = 'returning';
        document.getElementById('nvVisitType').value = 'general';
        document.getElementById('nvNotes').value = '';
        document.getElementById('nvDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('nvTime').value = '10:00';
        var chips = document.querySelectorAll('#nv-page .time-chip');
        for (var i = 0; i < chips.length; i++) {
            chips[i].classList.toggle('active', chips[i].getAttribute('data-time') === '10:00');
        }
        nvUpdateFormalDate();
        nvCalcPrice();
    }
}

/* ---- Patient typeahead (Patient ID or Name) ---- */
var nvSearchTimer = null;

function nvOnSearchInput() {
    document.getElementById('nvPatientId').value = '';
    if (nvSearchTimer) clearTimeout(nvSearchTimer);
    nvSearchTimer = setTimeout(function(){ nvDoSearch(document.getElementById('nvPatientSearch').value); }, 300);
}

function nvDoSearch(q) {
    if (!q || q.trim().length < 2) { nvHideResults(); return; }
    fetch('/hms/backend/api/patients.php?action=search&q=' + encodeURIComponent(q.trim()) + '&limit=8')
        .then(function(r){ return r.json(); })
        .then(function(d){
            var resultsBox = document.getElementById('nvSearchResults');
            var patients = (d && d.patients) || [];
            resultsBox.innerHTML = '';
            if (!patients.length) {
                var empty = document.createElement('div');
                empty.className = 'nv-empty';
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
                numSpan.className = 'nv-num';
                numSpan.textContent = p.hospital_number;
                item.appendChild(nameSpan);
                item.appendChild(numSpan);
                item.addEventListener('mousedown', function(ev){
                    ev.preventDefault();
                    nvSelectPatient(p);
                });
                resultsBox.appendChild(item);
            });
            resultsBox.style.display = 'block';
        })
        .catch(function(){ nvHideResults(); });
}

function nvSelectPatient(p) {
    var name = [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ');
    document.getElementById('nvPatientSearch').value = name + ' — ' + p.hospital_number;
    document.getElementById('nvPatientId').value = p.id;
    nvHideResults();
}

function nvHideResults() {
    var resultsBox = document.getElementById('nvSearchResults');
    if (resultsBox) resultsBox.style.display = 'none';
}

/* ---- Medical teams + system doctors (any doctor can take charge) ---- */
var nvTeams = {};
function nvLoadReference() {
    var doctorSelect = document.getElementById('nvDoctor');
    if (!doctorSelect) return;
    var placeholder = doctorSelect.options[0];
    doctorSelect.innerHTML = '';
    if (placeholder) doctorSelect.appendChild(placeholder);

    fetch('/hms/backend/api/medicalteams.php?action=list')
        .then(function(r){ return r.json(); })
        .then(function(d){
            var teams = (d && d.medical_teams) || [];
            var teamGroup = document.createElement('optgroup');
            teamGroup.label = '-- MEDICAL TEAMS --';
            teams.forEach(function(team){
                nvTeams[team.id] = { name: team.name, department_id: team.department_id };
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
        .catch(function(){ /* ignore */ });

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

/* ---- Submit: create the appointment via the live API ---- */
async function nvSubmit(e) {
    e.preventDefault();
    var missing = [];
    if (!nvVal('nvPatientId')) missing.push('Patient (ID or Name)');
    if (!nvVal('nvDoctor')) missing.push('Attending Doctor / Medical Team');
    if (!nvVal('nvDate')) missing.push('Appointment Date');
    if (!nvVal('nvTime')) missing.push('Preferred Time Slot');
    if (missing.length) { showAlert('Please complete: ' + missing.join(', '), 'error'); return; }

    var doctorValue = nvVal('nvDoctor');
    var departmentId = null, doctorId = null;
    if (doctorValue.indexOf('team:') === 0) {
        var teamId = parseInt(doctorValue.split(':')[1], 10);
        var team = nvTeams[teamId];
        if (team && team.department_id) { departmentId = parseInt(team.department_id, 10); }
    } else if (doctorValue.indexOf('doc:') === 0) {
        doctorId = parseInt(doctorValue.split(':')[1], 10);
    }

    var data = {
        patient_id: parseInt(nvVal('nvPatientId'), 10),
        appointment_date: nvVal('nvDate') + ' ' + nvVal('nvTime') + ':00',
        department_id: departmentId,
        doctor_id: doctorId,
        reason: nvVal('nvNotes').trim(),
        consultation_type: nvVal('nvVisitType').toUpperCase(),
        visit_type: nvVal('nvCategory') === 'new' ? 'New' : 'Review'
    };

    try {
        var r = await fetch('/hms/backend/api/appointments.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var d = await r.json();
        if (d.success) {
            var formatted = document.getElementById('nvFormalDateText').innerText;
            showAlert('Appointment / Visit Scheduled Successfully - ' + formatted + ' at ' + nvVal('nvTime'), 'success');
            nvCloseFullPage();
        } else {
            var err = d.error || (d.errors ? Object.values(d.errors)[0] : null) || 'Failed to schedule appointment';
            showAlert(err, 'error');
        }
    } catch (err) {
        console.error('Appointment scheduling error:', err);
        showAlert('Network error. Please try again.', 'error');
    }
}
</script>