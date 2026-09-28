<?php
// Patient Record Management - EHMS-style 3-column layout (live data).
// Server-side prologue: staff name, role and today's date for the status card.
// The shell (dashboard.php) already authenticated the user.
require_once __DIR__ . '/../../backend/config/config.php';
$__recStaff = getCurrentUserName() ?: 'STAFF';
$__recRole  = getCurrentUserRole() ?: 'STAFF';
?>
<style>
/* ============ PATIENT RECORD MANAGEMENT : CLASSIC EHMS 3-COLUMN LAYOUT ============
   Scoped under #records-page. No Bootstrap - every utility is defined locally.
   Font Awesome icons come from the shell (assets/fontawesome/css/all.min.css). */
#records-page{background:#dce7f2;color:#333;font-size:13px;min-height:100vh;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}
#records-page *,#records-page *::before,#records-page *::after{box-sizing:border-box}
#records-page .rcd-topbar{background-color:#0b5fa5;color:#fff;padding:6px 15px;display:flex;justify-content:space-between;align-items:center;font-weight:bold}
#records-page .rcd-topbar-title{font-size:15px;letter-spacing:.5px;display:flex;align-items:center;gap:8px}
#records-page .rcd-topbar-right{display:flex;align-items:center;gap:15px;flex-wrap:wrap;justify-content:flex-end}
#records-page .rcd-topbar-right > span{font-size:11px}
#records-page .rcd-nav-btn{background-color:#0088cc;color:#fff;border:none;padding:4px 10px;font-size:11px;font-weight:bold;border-radius:2px;cursor:pointer;text-decoration:none;font-family:inherit}
#records-page .rcd-nav-btn:hover{background-color:#006699}
#records-page .rcd-main{display:grid;grid-template-columns:220px 1fr 240px;gap:12px;padding:10px;max-width:100%}
#records-page .rcd-sidebar{display:flex;flex-direction:column}
#records-page .rcd-content{display:flex;flex-direction:column;gap:12px;min-width:0}
#records-page .rcd-panel{background:#fff;border:1px solid #b2c8de;border-radius:3px;overflow:hidden;margin-bottom:10px}
#records-page .rcd-content .rcd-panel{margin-bottom:0}
#records-page .rcd-panel-head{background-color:#0b5fa5;color:#fff;font-weight:bold;font-size:11px;padding:6px 10px;text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
#records-page .rcd-panel-head .rcd-head-btn{background-color:#0088cc;color:#fff;border:none;padding:4px 10px;font-size:10px;font-weight:bold;border-radius:2px;cursor:pointer;text-transform:uppercase;font-family:inherit}
#records-page .rcd-panel-head .rcd-head-btn:hover{background-color:#006699}#records-page .rcd-ql{list-style:none;background-color:#eaf1f8;margin:0;padding:0}
#records-page .rcd-ql li{border-bottom:1px solid #d0deec}
#records-page .rcd-ql li:last-child{border-bottom:none}
#records-page .rcd-ql a{display:flex;align-items:center;gap:6px;padding:6px 10px;color:#333;text-decoration:none;font-size:11px;cursor:pointer}
#records-page .rcd-ql a i{font-size:10px;color:#0b5fa5;width:12px;text-align:center}
#records-page .rcd-ql a:hover{background-color:#d2e3f3;color:#005599}
#records-page .rcd-ql-badge{color:red;font-weight:bold;margin-left:auto}
#records-page .rcd-btn-orange{background-color:#f39c12;color:#fff;border:none;width:100%;padding:8px;font-weight:bold;font-size:11px;cursor:pointer;text-transform:uppercase;border-radius:2px;margin-bottom:10px;font-family:inherit}
#records-page .rcd-btn-orange:hover{background-color:#d68910}
#records-page .rcd-status-card{background-color:#0b5fa5;color:#fff;padding:8px;border-radius:3px;font-size:10px}
#records-page .rcd-status-card p{margin-bottom:4px}
#records-page .rcd-logout-btn{background-color:#0088cc;color:#fff;border:none;width:100%;padding:4px;margin-top:6px;cursor:pointer;font-weight:bold;text-transform:uppercase;font-family:inherit;font-size:10px}
#records-page .rcd-logout-btn:hover{background-color:#006699}
#records-page .rcd-search-bar{display:flex;gap:10px;padding:10px;background-color:#f4f8fb;border-bottom:1px solid #d0deec}
#records-page .rcd-search-bar input,#records-page .rcd-search-bar select{padding:6px 8px;border:1px solid #b2c8de;border-radius:2px;font-size:12px;font-family:inherit}
#records-page .rcd-table-wrap{background-color:#fff;max-height:450px;overflow-y:auto}
#records-page .rcd-table{width:100%;border-collapse:collapse;font-size:11px}
#records-page .rcd-table th{background-color:#e6eef5;color:#222;font-weight:bold;text-align:left;padding:8px;border-bottom:2px solid #b2c8de;position:sticky;top:0;z-index:1;white-space:nowrap}
#records-page .rcd-table td{padding:8px;border-bottom:1px solid #e1e8f0;vertical-align:middle}
#records-page .rcd-table tr:nth-child(even){background-color:#f9fbfd}
#records-page .rcd-table tr:hover{background-color:#eef4fb}
#records-page .rcd-bold{font-weight:700}
#records-page .rcd-btn-view{background-color:#eef3f8;border:1px solid #b2c8de;color:#333;padding:3px 8px;font-size:10px;font-weight:bold;border-radius:2px;cursor:pointer;font-family:inherit;text-transform:uppercase}
#records-page .rcd-btn-view:hover{background-color:#dbe7f2}
#records-page .rcd-btn-visit{background-color:#0088cc;border:none;color:#fff;padding:3px 8px;font-size:10px;font-weight:bold;border-radius:2px;cursor:pointer;font-family:inherit;text-transform:uppercase}
#records-page .rcd-btn-visit:hover{background-color:#006699}
#records-page .rcd-empty{text-align:center;padding:14px;color:#8a94a6}
#records-page .rcd-footer{background-color:#0b5fa5;color:#fff;padding:6px 10px;font-size:11px;text-align:center;border-radius:3px}
#records-page .rcd-msg{list-style:none;padding:0;margin:0}
#records-page .rcd-msg li{padding:8px;border-bottom:1px solid #e1e8f0;font-size:11px}
#records-page .rcd-msg li:last-child{border-bottom:none}
#records-page .rcd-msg .rcd-msg-subject{font-weight:bold}
#records-page .rcd-msg .rcd-msg-time{color:#777;display:block;font-size:10px;margin-top:2px}
#records-page .rcd-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 14px;padding:4px 0 12px}
#records-page .rcd-info-grid .rcd-info-item{font-size:12px}
#records-page .rcd-info-grid .rcd-info-item span{display:block;font-size:10px;font-weight:bold;text-transform:uppercase;color:#666}
@media (max-width:1100px){
  #records-page .rcd-main{grid-template-columns:1fr}
  #records-page .rcd-sidebar{display:grid;grid-template-columns:1fr 1fr;gap:0 12px}
  #records-page .rcd-sidebar > .rcd-panel{margin-bottom:10px}
  #records-page .rcd-sidebar > .rcd-btn-orange{align-self:end}
}
#records-page .modal-body label{text-transform:uppercase;font-size:11px;font-weight:bold}
#records-page .rcd-visit-price{background-color:#eef3f8;color:#0b5fa5;font-weight:bold}
</style><!-- PATIENT RECORD MANAGEMENT -->
<div id="records-page">

  <!-- TOP BAR -->
  <div class="rcd-topbar">
    <div class="rcd-topbar-title"><i class="fa-solid fa-folder-open"></i> RECORDS MANAGEMENT</div>
    <div class="rcd-topbar-right">
      <span>HMS - HEALTHCARE MANAGEMENT SYSTEM</span>
      <span class="rcd-nav-btn" onclick="rcdNav('home')">HOME</span>
      <span class="rcd-nav-btn" onclick="rcdGoBack()">&lt; BACK</span>
      <span class="rcd-nav-btn" onclick="rcdNav('administrator')">PASSWORD</span>
    </div>
  </div>

  <div class="rcd-main">

    <!-- LEFT SIDEBAR -->
    <div class="rcd-sidebar">
      <div class="rcd-panel">
        <div class="rcd-panel-head">Quick Links</div>
        <ul class="rcd-ql">
          <li><a onclick="rcdNav('home')"><i class="fa-solid fa-square"></i> Control Panel</a></li>
          <li><a onclick="rcdNav('appointment_calendar')"><i class="fa-solid fa-square"></i> Appointment Calendar</a></li>
          <li><a onclick="rcdNav('messages_alerts')"><i class="fa-solid fa-square"></i> Messages &amp; Alerts <span class="rcd-ql-badge" id="rcdUnreadBadge" style="display:none;">0</span></a></li>
          <li><a onclick="rcdNav('patient_records')"><i class="fa-solid fa-square"></i> Patient Record Management</a></li>
          <li><a onclick="rcdNav('ipd_management')"><i class="fa-solid fa-square"></i> Admissions</a></li>
          <li><a onclick="rcdNav('pharmacy_management')"><i class="fa-solid fa-square"></i> Drugs Dispense</a></li>
          <li><a onclick="rcdNav('messages_alerts')"><i class="fa-solid fa-square"></i> View Alerts</a></li>
          <li><a onclick="rcdNav('investigations')"><i class="fa-solid fa-square"></i> Lab Management</a></li>
          <li><a onclick="rcdNav('accounts_management')"><i class="fa-solid fa-square"></i> Account Management</a></li>
        </ul>
      </div>

      <span class="rcd-btn-orange" onclick="rcdNav('messages_alerts')">SEND MESSAGE</span>

      <div class="rcd-panel">
        <div class="rcd-panel-head">Days Alerts</div>
        <div style="padding:10px; text-align:center;">
          <h2 style="font-size:20px; margin-bottom:5px; color:#333;">0</h2>
          <span style="font-size:10px; color:#666;">NO ALERTS TODAY</span>
        </div>
      </div>

      <div class="rcd-status-card">
        <p><strong>LOGIN STATUS</strong></p>
        <p>Logged In: <?php echo htmlspecialchars($__recStaff, ENT_QUOTES); ?></p>
        <p>User Type: <?php echo htmlspecialchars(strtoupper($__recRole), ENT_QUOTES); ?></p>
        <p>Date: <?php echo date('l, d-M-Y'); ?></p>
        <p>Clinic Name: HMS - HEALTHCARE MANAGEMENT SYSTEM</p>
        <button type="button" class="rcd-logout-btn" onclick="rcdLogout()">LOGOUT</button>
      </div>
    </div>    <!-- CENTER -->
    <div class="rcd-content">
      <div class="rcd-panel">
        <div class="rcd-panel-head">
          <span>PATIENT RECORD MANAGEMENT</span>
          <button type="button" class="rcd-head-btn" id="register-patient-btn">+ REGISTER PATIENT</button>
        </div>

        <div class="rcd-search-bar">
          <input type="text" id="search-patients" placeholder="Search by name, hospital number, or phone..." style="flex:1;">
          <select id="filter-sponsor" style="width:200px;">
            <option value="">All Sponsors</option>
          </select>
        </div>

        <div class="rcd-table-wrap">
          <table class="rcd-table">
            <thead>
              <tr>
                <th>HOSPITAL NO.</th>
                <th>NAME</th>
                <th>AGE / GENDER</th>
                <th>PHONE</th>
                <th>SPONSOR</th>
                <th>REGISTRATION DATE</th>
                <th>ACTIONS</th>
              </tr>
            </thead>
            <tbody id="records-table">
              <tr><td colspan="7" class="rcd-empty">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="rcd-footer">HMS - PATIENT RECORD MANAGEMENT &middot; live data from the hospital management system</div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="rcd-sidebar">
      <div class="rcd-panel">
        <div class="rcd-panel-head">Search</div>
        <div style="padding:8px;">
          <input type="text" id="rcdSideSearch" placeholder="By System Name / ID..." style="width:100%; padding:4px; border:1px solid #b2c8de;">
        </div>
      </div>

      <div class="rcd-panel">
        <div class="rcd-panel-head">SYSTEM MESSAGES</div>
        <ul class="rcd-msg" id="rcdSystemMessages">
          <li style="color:#8a94a6;">Loading...</li>
        </ul>
      </div>
    </div>

  </div>
</div><!-- REGISTER PATIENT MODAL -->
<div class="modal" id="patient-modal">
  <div class="modal-content" style="max-width:640px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-user-plus"></i> REGISTER PATIENT</h3>
      <button class="modal-close" id="close-patient-modal">&times;</button>
    </div>
    <div class="modal-body">
      <form id="patient-form">
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
              <option value="other">Other</option>
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
        <h4>Insurance / Sponsor</h4>
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
          <button type="submit" class="btn btn-primary">SAVE PATIENT</button>
          <button type="button" class="btn btn-secondary" id="cancel-patient">CANCEL</button>
        </div>
      </form>
    </div>
  </div>
</div><!-- NEW VISIT MODAL -->
<div class="modal" id="visit-modal">
  <div class="modal-content" style="max-width:560px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-clipboard-user"></i> NEW VISIT</h3>
      <button class="modal-close" id="close-visit-modal">&times;</button>
    </div>
    <div class="modal-body">
      <form id="visit-form">
        <input type="hidden" id="visit-patient-id">
        <div class="form-group">
          <label>PATIENT</label>
          <input type="text" id="visit-patient-display" readonly>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="visit-type">VISIT STATUS / TYPE *</label>
            <select id="visit-type" required onchange="updateRcdPrice()">
              <option value="">-- Select Visit Type --</option>
              <option value="NEW">NEW (New Patient Visit)</option>
              <option value="OLD">OLD / REVIEW (Follow-Up Visit)</option>
              <option value="SPECIALIST">SPECIALIST CONSULTATION</option>
              <option value="EMERGENCY">EMERGENCY VISIT</option>
            </select>
          </div>
          <div class="form-group">
            <label for="visit-department">DEPARTMENT *</label>
            <select id="visit-department" required>
              <option value="">-- Select Department --</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="visit-price">REGISTRATION / CONSULTATION PRICE (AUTO GENERATED)</label>
          <input type="text" id="visit-price" class="rcd-visit-price" value="" readonly>
        </div>
        <div class="form-group">
          <label for="visit-complaint">CHIEF COMPLAINT</label>
          <textarea id="visit-complaint" rows="3" placeholder="Presenting complaint..."></textarea>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">START VISIT</button>
          <button type="button" class="btn btn-secondary" id="cancel-visit">CANCEL</button>
        </div>
      </form>
    </div>
  </div>
</div><!-- PATIENT DETAILS MODAL -->
<div class="modal" id="view-modal">
  <div class="modal-content" style="max-width:640px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-id-card"></i> PATIENT DETAILS</h3>
      <button class="modal-close" id="close-view-modal">&times;</button>
    </div>
    <div class="modal-body">
      <div id="view-patient-header" style="margin-bottom:10px;"></div>
      <div class="rcd-info-grid" id="view-patient-info"></div>
      <h4 style="margin:6px 0;">RECENT VISITS</h4>
      <div class="rcd-table-wrap" style="max-height:220px;">
        <table class="rcd-table">
          <thead>
            <tr><th>VISIT NO.</th><th>TYPE</th><th>DEPARTMENT</th><th>DATE</th><th>STATUS</th></tr>
          </thead>
          <tbody id="view-patient-visits">
            <tr><td colspan="5" class="rcd-empty">Loading...</td></tr>
          </tbody>
        </table>
      </div>
      <div class="form-actions" style="margin-top:12px;">
        <button type="button" class="btn btn-primary" id="view-new-visit-btn">NEW VISIT</button>
        <button type="button" class="btn btn-secondary" id="view-close-btn">CLOSE</button>
      </div>
    </div>
  </div>
</div>

<script>var REC_STAFF = <?php echo json_encode($__recStaff); ?>;
var rcdPatients = [];
var RCD_VISIT_PRICES = { NEW: 'GHS 50.00', OLD: 'GHS 20.00', SPECIALIST: 'GHS 100.00', EMERGENCY: 'GHS 80.00' };
var RCD_VISIT_LABELS = { NEW: 'New Patient Visit', OLD: 'Follow-Up Visit', SPECIALIST: 'Specialist Consultation', EMERGENCY: 'Emergency Visit' };
var RCD_VISIT_TYPES = { NEW: 'opd', OLD: 'opd', SPECIALIST: 'opd', EMERGENCY: 'emergency' };

async function initRecords() {
    setupRcdEvents();
    loadRcdUnread();
    await Promise.all([loadRcdSponsors(), loadRcdDepartments(), loadRcdPatients(), loadRcdMessages()]);
}

function setupRcdEvents() {
    var el;
    el = document.getElementById('register-patient-btn');
    if (el) el.addEventListener('click', openRcdRegister);
    el = document.getElementById('close-patient-modal');
    if (el) el.addEventListener('click', closeRcdModal('patient-modal'));
    el = document.getElementById('cancel-patient');
    if (el) el.addEventListener('click', closeRcdModal('patient-modal'));
    el = document.getElementById('patient-form');
    if (el) el.addEventListener('submit', submitRcdPatient);
    el = document.getElementById('close-visit-modal');
    if (el) el.addEventListener('click', closeRcdModal('visit-modal'));
    el = document.getElementById('cancel-visit');
    if (el) el.addEventListener('click', closeRcdModal('visit-modal'));
    el = document.getElementById('visit-form');
    if (el) el.addEventListener('submit', submitRcdVisit);
    el = document.getElementById('close-view-modal');
    if (el) el.addEventListener('click', closeRcdModal('view-modal'));
    el = document.getElementById('view-close-btn');
    if (el) el.addEventListener('click', closeRcdModal('view-modal'));
    el = document.getElementById('view-new-visit-btn');
    if (el) el.addEventListener('click', viewVisitFromDetails);
    el = document.getElementById('search-patients');
    if (el) el.addEventListener('input', renderRcdTable);
    el = document.getElementById('rcdSideSearch');
    if (el) el.addEventListener('input', renderRcdTable);
    el = document.getElementById('filter-sponsor');
    if (el) el.addEventListener('change', renderRcdTable);
}

function closeRcdModal(id) {
    return function () { var m = document.getElementById(id); if (m) m.classList.remove('show'); };
}

/* ================= NAV ================= */
function rcdNav(key) {
    if (key === 'home') {
        if (window.navigateTo) { window.navigateTo('dashboard'); return; }
        if (window.loadPage) { window.loadPage('dashboard'); return; }
    }
    if (window.loadModuleTab) window.loadModuleTab(key);
}

function rcdGoBack() {
    if (window.history && window.history.length > 1) window.history.back();
    else rcdNav('home');
}

function rcdLogout() {
    fetch('/hms/backend/api/auth.php?action=logout', { method: 'POST' })
        .then(function () { window.location.href = '/hms/frontend/index.php'; })
        .catch(function () { window.location.href = '/hms/frontend/index.php'; });
}

async function loadRcdUnread() {
    try {
        var r = await fetch('/hms/backend/api/messages.php?action=unread_count');
        var d = await r.json();
        if (d.success) {
            var badge = document.getElementById('rcdUnreadBadge');
            if (badge) {
                badge.textContent = d.unread;
                badge.style.display = d.unread > 0 ? 'inline' : 'none';
            }
        }
    } catch (e) { console.error('Unread count error:', e); }
}/* ================= DATA LOADERS ================= */
async function loadRcdSponsors() {
    try {
        var r = await fetch('/hms/backend/api/sponsors.php?action=list');
        var d = await r.json();
        if (d.success) {
            var opts = (d.sponsors || []).map(function (s) {
                return '<option value="' + s.id + '">' + rcdEsc(s.name) + '</option>';
            }).join('');
            var filterSel = document.getElementById('filter-sponsor');
            if (filterSel) filterSel.innerHTML = '<option value="">All Sponsors</option>' + opts;
            var regSel = document.getElementById('patient-sponsor');
            if (regSel) regSel.innerHTML = '<option value="">Select Sponsor</option>' + opts;
        }
    } catch (e) { console.error('Sponsors load error:', e); }
}

var rcdDepartments = [];
async function loadRcdDepartments() {
    try {
        var r = await fetch('/hms/backend/api/users.php?action=departments');
        var d = await r.json();
        if (d.success) {
            rcdDepartments = d.departments || [];
            var sel = document.getElementById('visit-department');
            if (sel) sel.innerHTML = '<option value="">-- Select Department --</option>'
                + rcdDepartments.map(function (dep) {
                    return '<option value="' + dep.id + '">' + rcdEsc(dep.name) + '</option>';
                }).join('');
        }
    } catch (e) { console.error('Departments load error:', e); }
}

function rcdRecordsDeptId() {
    var d = rcdDepartments.find(function (x) { return /records/i.test(x.name); });
    return d ? String(d.id) : '';
}

async function loadRcdPatients() {
    try {
        var r = await fetch('/hms/backend/api/patients.php?per_page=250');
        var d = await r.json();
        rcdPatients = (d.success && d.patients) ? d.patients : [];
    } catch (e) {
        console.error('Patients load error:', e);
        rcdPatients = [];
    }
    renderRcdTable();
}

async function loadRcdMessages() {
    try {
        var r = await fetch('/hms/backend/api/messages.php?action=list&limit=5');
        var d = await r.json();
        var holder = document.getElementById('rcdSystemMessages');
        if (d.success && holder) {
            var msgs = d.messages || [];
            holder.innerHTML = msgs.length
                ? msgs.map(function (m) {
                    return '<li><span class="rcd-msg-subject">&bull; ' + rcdEsc(m.subject || '(no subject)') + '</span> - ' + rcdEsc(m.recipient_name || '')
                        + '<span class="rcd-msg-time">' + rcdDateTime(m.sent_at) + '</span></li>';
                }).join('')
                : '<li style="color:#8a94a6;">No system messages.</li>';
        }
    } catch (e) { console.error('Messages load error:', e); }
}/* ================= TABLE ================= */
function rcdFilteredPatients() {
    var q1 = (document.getElementById('search-patients').value || '').trim().toLowerCase();
    var q2 = (document.getElementById('rcdSideSearch').value || '').trim().toLowerCase();
    var sponsorId = document.getElementById('filter-sponsor').value;
    return rcdPatients.filter(function (p) {
        var hay = ((p.hospital_number || '') + ' ' + (p.first_name || '') + ' ' + (p.middle_name || '') + ' ' + (p.last_name || '') + ' ' + (p.phone || '')).toLowerCase();
        if (q1 && hay.indexOf(q1) < 0) return false;
        if (q2 && hay.indexOf(q2) < 0) return false;
        if (sponsorId && String(p.sponsor_id || '') !== String(sponsorId)) return false;
        return true;
    });
}

function renderRcdTable() {
    var tbody = document.getElementById('records-table');
    if (!tbody) return;
    var rows = rcdFilteredPatients();
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="rcd-empty">' + (rcdPatients.length ? 'No patients match the current search.' : 'No patients yet - register the first patient.') + '</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(function (p) {
        var age = rcdAge(p.date_of_birth);
        var gender = (p.gender || '').charAt(0).toUpperCase() + (p.gender || '').slice(1);
        return '<tr>' +
            '<td class="rcd-bold">' + rcdEsc(p.hospital_number) + '</td>' +
            '<td class="rcd-bold">' + rcdEsc([p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ')) + '</td>' +
            '<td>' + age + ' yrs / ' + rcdEsc(gender) + '</td>' +
            '<td>' + rcdEsc(p.phone || '-') + '</td>' +
            '<td>' + rcdEsc(p.sponsor_name || 'Cash / Private') + '</td>' +
            '<td>' + rcdDate(p.registration_date) + '</td>' +
            '<td><button class="rcd-btn-view" onclick="openRcdView(' + p.id + ')">VIEW</button> ' +
            '<button class="rcd-btn-visit" onclick="openRcdVisit(' + p.id + ')">NEW VISIT</button></td>' +
        '</tr>';
    }).join('');
}/* ================= REGISTER ================= */
function openRcdRegister() {
    var form = document.getElementById('patient-form');
    if (form) form.reset();
    var modal = document.getElementById('patient-modal');
    if (modal) modal.classList.add('show');
}

async function submitRcdPatient(e) {
    e.preventDefault();
    var data = {};
    ['first_name', 'middle_name', 'last_name', 'date_of_birth', 'gender', 'blood_group',
     'phone', 'email', 'address', 'emergency_contact_name', 'emergency_contact_phone', 'sponsor_id', 'nhia_number']
        .forEach(function (k) {
            var el = document.getElementById('patient-form').querySelector('[name="' + k + '"]');
            if (el) data[k] = el.value;
        });
    if (!data.first_name || !data.last_name || !data.date_of_birth || !data.gender) {
        showAlert('First name, last name, date of birth and gender are required.', 'error');
        return;
    }
    try {
        var r = await fetch('/hms/backend/api/patients.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var d = await r.json();
        if (d.success) {
            showAlert('Patient registered successfully - ' + (d.patient ? d.patient.hospital_number : ''), 'success');
            closeRcdModal('patient-modal')();
            await loadRcdPatients();
        } else {
            var err = d.error || (d.errors ? Object.values(d.errors)[0] : null) || 'Registration failed';
            showAlert(err, 'error');
        }
    } catch (err) {
        console.error('Patient save error:', err);
        showAlert('Network error. Please try again.', 'error');
    }
}/* ================= NEW VISIT ================= */
function openRcdVisit(patientId) {
    var p = rcdPatients.find(function (x) { return String(x.id) === String(patientId); });
    if (!p) { showAlert('Patient not found', 'error'); return; }
    var form = document.getElementById('visit-form');
    if (form) form.reset();
    document.getElementById('visit-patient-id').value = p.id;
    document.getElementById('visit-patient-display').value = [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ') + ' - ' + p.hospital_number;
    document.getElementById('visit-type').value = 'NEW';
    document.getElementById('visit-department').value = rcdRecordsDeptId();
    updateRcdPrice();
    document.getElementById('visit-modal').classList.add('show');
}

function updateRcdPrice() {
    var type = document.getElementById('visit-type').value;
    document.getElementById('visit-price').value = RCD_VISIT_PRICES[type] || 'GHS 0.00';
}

async function submitRcdVisit(e) {
    e.preventDefault();
    var patientId = document.getElementById('visit-patient-id').value;
    var type = document.getElementById('visit-type').value;
    var dept = document.getElementById('visit-department').value;
    if (!patientId) { showAlert('Select the patient first.', 'error'); return; }
    if (!type || !dept) { showAlert('Visit type and department are required.', 'error'); return; }
    var complaint = document.getElementById('visit-complaint').value.trim();
    var fullComplaint = '[' + (RCD_VISIT_LABELS[type] || type) + ']' + (complaint ? ' ' + complaint : '');
    var data = {
        patient_id: patientId,
        visit_type: RCD_VISIT_TYPES[type] || 'opd',
        department_id: dept,
        chief_complaint: fullComplaint
    };
    try {
        var r = await fetch('/hms/backend/api/visits.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var d = await r.json();
        if (d.success) {
            var v = d.visit || {};
            showAlert('Visit started - ' + (v.visit_number || ('Visit #' + d.visit_id)), 'success');
            closeRcdModal('visit-modal')();
        } else {
            var err = d.error || (d.errors ? Object.values(d.errors)[0] : null) || 'Visit creation failed';
            showAlert(err, 'error');
        }
    } catch (err) {
        console.error('Visit create error:', err);
        showAlert('Network error. Please try again.', 'error');
    }
}/* ================= PATIENT DETAILS / VIEW ================= */
function openRcdView(patientId) {
    var p = rcdPatients.find(function (x) { return String(x.id) === String(patientId); });
    if (!p) { showAlert('Patient not found', 'error'); return; }
    document.getElementById('view-patient-header').innerHTML =
        '<strong style="font-size:15px;">' + rcdEsc([p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ')) + '</strong> ' +
        '<span class="rcd-bold" style="color:#0b5fa5;">(' + rcdEsc(p.hospital_number) + ')</span>';
    document.getElementById('view-patient-info').innerHTML =
        rcdInfoItem('Gender', (p.gender || '').charAt(0).toUpperCase() + (p.gender || '').slice(1))
        + rcdInfoItem('Age', rcdAge(p.date_of_birth) + ' yrs')
        + rcdInfoItem('Date of Birth', rcdDate(p.date_of_birth))
        + rcdInfoItem('Phone', p.phone || '-')
        + rcdInfoItem('Email', p.email || '-')
        + rcdInfoItem('Blood Group', p.blood_group || '-')
        + rcdInfoItem('Sponsor', p.sponsor_name || 'Cash / Private')
        + rcdInfoItem('NHIA Number', p.nhia_number || '-')
        + rcdInfoItem('Address', p.address || '-')
        + rcdInfoItem('Registered', rcdDate(p.registration_date));
    var nb = document.getElementById('view-new-visit-btn');
    if (nb) nb.setAttribute('data-patient-id', p.id);
    document.getElementById('view-modal').classList.add('show');
    loadRcdVisits(patientId);
}

function rcdInfoItem(label, value) {
    return '<div class="rcd-info-item"><span>' + rcdEsc(label) + '</span>' + rcdEsc(value) + '</div>';
}

async function loadRcdVisits(patientId) {
    var tbody = document.getElementById('view-patient-visits');
    try {
        var r = await fetch('/hms/backend/api/visits.php?patient_id=' + patientId + '&per_page=10');
        var d = await r.json();
        if (d.success && d.visits && d.visits.length) {
            tbody.innerHTML = d.visits.map(function (v) {
                return '<tr>' +
                    '<td class="rcd-bold">' + rcdEsc(v.visit_number) + '</td>' +
                    '<td>' + rcdEsc((v.visit_type || '').toUpperCase()) + '</td>' +
                    '<td>' + rcdEsc(v.department_name || '-') + '</td>' +
                    '<td>' + rcdDateTime(v.visit_date) + '</td>' +
                    '<td>' + rcdEsc(v.status || '-') + '</td>' +
                '</tr>';
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="rcd-empty">No visits recorded yet.</td></tr>';
        }
    } catch (e) {
        console.error('Visits load error:', e);
        tbody.innerHTML = '<tr><td colspan="5" class="rcd-empty">Could not load visits.</td></tr>';
    }
}

function viewVisitFromDetails() {
    var btn = document.getElementById('view-new-visit-btn');
    var pid = btn ? btn.getAttribute('data-patient-id') : null;
    if (pid) openRcdVisit(pid);
}

/* ================= HELPERS ================= */
function rcdAge(dob) {
    if (!dob) return 0;
    var today = new Date();
    var birth = new Date(dob);
    var age = today.getFullYear() - birth.getFullYear();
    var m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
    return age < 0 ? 0 : age;
}

function rcdDate(v) {
    if (!v) return '--';
    return window.fmtDate ? window.fmtDate(v) : String(v);
}

function rcdDateTime(v) {
    if (!v) return '--';
    return window.fmtDateTime ? window.fmtDateTime(v) : String(v);
}

function rcdEsc(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
</script>