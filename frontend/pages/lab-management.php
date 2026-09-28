<?php
// Laboratory Dashboard (EHMS-style 3-column layout).
// Server-side prologue: resolve the logged-in staff name, role, and today's date
// for the login-status card. The shell (dashboard.php) already authenticated.
require_once __DIR__ . '/../../backend/config/config.php';
$__labStaff = getCurrentUserName() ?: 'STAFF';
$__labRole  = getCurrentUserRole() ?: 'STAFF';
?>
<style>
/* ================= LAB DASHBOARD : CLASSIC EHMS 3-COLUMN LAYOUT =================
   Scoped under #lab-page. The shell (dashboard.php) has no Bootstrap dependency,
   so every utility below is defined locally. Font Awesome icons are available
   because the shell already loads frontend/assets/fontawesome/css/all.min.css. */
#lab-page{background:#dce7f2;color:#333;font-size:13px;min-height:100vh;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}
#lab-page *,#lab-page *::before,#lab-page *::after{box-sizing:border-box}
#lab-page .lab-topbar{background-color:#0b5fa5;color:#fff;padding:6px 15px;display:flex;justify-content:space-between;align-items:center;font-weight:bold}
#lab-page .lab-topbar-title{font-size:15px;letter-spacing:.5px}
#lab-page .lab-topbar-right{display:flex;align-items:center;gap:15px;flex-wrap:wrap;justify-content:flex-end}
#lab-page .lab-topbar-right > span{font-size:11px}
#lab-page .lab-nav-btn{background-color:#0088cc;color:#fff;border:none;padding:4px 10px;font-size:11px;font-weight:bold;border-radius:2px;cursor:pointer;text-decoration:none;font-family:inherit}
#lab-page .lab-nav-btn:hover{background-color:#006699}
#lab-page .lab-main{display:grid;grid-template-columns:220px 1fr 240px;gap:12px;padding:10px;max-width:100%}
#lab-page .lab-sidebar,.lab-content .lab-panel{display:flex;flex-direction:column}
#lab-page .lab-sidebar{display:flex;flex-direction:column}
#lab-page .lab-content{display:flex;flex-direction:column;gap:12px;min-width:0}
#lab-page .lab-panel{background:#fff;border:1px solid #b2c8de;border-radius:3px;overflow:hidden;margin-bottom:10px}
#lab-page .lab-content .lab-panel{margin-bottom:0}
#lab-page .lab-panel-head{background-color:#0b5fa5;color:#fff;font-weight:bold;font-size:11px;padding:6px 10px;text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
#lab-page .lab-panel-head .lab-head-link{color:#FFD166;font-size:10px;text-transform:uppercase;cursor:pointer;text-decoration:underline;font-weight:bold}
#lab-page .lab-panel-head .lab-head-link:hover{color:#fff}
#lab-page .lab-ql{list-style:none;background-color:#eaf1f8;margin:0;padding:0}
#lab-page .lab-ql li{border-bottom:1px solid #d0deec}
#lab-page .lab-ql li:last-child{border-bottom:none}
#lab-page .lab-ql a{display:flex;align-items:center;gap:6px;padding:6px 10px;color:#333;text-decoration:none;font-size:11px;cursor:pointer}
#lab-page .lab-ql a i{font-size:10px;color:#0b5fa5;width:12px;text-align:center}
#lab-page .lab-ql a:hover{background-color:#d2e3f3;color:#005599}
#lab-page .lab-ql-badge{color:red;font-weight:bold;margin-left:auto}
#lab-page .lab-btn-orange{background-color:#f39c12;color:#fff;border:none;width:100%;padding:8px;font-weight:bold;font-size:11px;cursor:pointer;text-transform:uppercase;border-radius:2px;margin-bottom:10px;font-family:inherit}
#lab-page .lab-btn-orange:hover{background-color:#d68910}
#lab-page .lab-status-card{background-color:#0b5fa5;color:#fff;padding:8px;border-radius:3px;font-size:10px}
#lab-page .lab-status-card p{margin-bottom:4px}
#lab-page .lab-logout-btn{background-color:#0088cc;color:#fff;border:none;width:100%;padding:4px;margin-top:6px;cursor:pointer;font-weight:bold;text-transform:uppercase;font-family:inherit;font-size:10px}
#lab-page .lab-logout-btn:hover{background-color:#006699}
#lab-page .lab-form-grid{display:grid;grid-template-columns:150px 1fr;gap:8px 12px;padding:12px;align-items:center;background-color:#f8fafc}
#lab-page .lab-form-grid label{font-weight:bold;color:#222;font-size:12px}
#lab-page .lab-form-grid input,#lab-page .lab-form-grid select,#lab-page .lab-form-grid textarea{width:100%;padding:6px 8px;border:1px solid #b2c8de;border-radius:2px;font-size:12px;background-color:#fff;font-family:inherit}
#lab-page .lab-form-grid textarea{resize:vertical;min-height:60px}
#lab-page .lab-form-grid input[readonly]{background-color:#eef3f8;color:#555}
#lab-page .lab-form-actions{grid-column:span 2;display:flex;justify-content:flex-end;gap:10px;margin-top:5px}
#lab-page .lab-btn-submit{background-color:#0b5fa5;color:#fff;border:none;padding:6px 16px;font-weight:bold;font-size:11px;border-radius:2px;cursor:pointer;font-family:inherit;text-transform:uppercase}
#lab-page .lab-btn-submit:hover{background-color:#004080}
#lab-page .lab-btn-submit:disabled{opacity:.6;cursor:not-allowed}
#lab-page .lab-btn-outline{background-color:#fff;color:#0b5fa5;border:1px solid #b2c8de;padding:3px 10px;font-weight:bold;font-size:10px;border-radius:2px;cursor:pointer;font-family:inherit}
#lab-page .lab-btn-outline:hover{background-color:#e2edf7}
#lab-page .lab-btn-success{background-color:#27ae60;color:#fff;border:none;padding:3px 10px;font-weight:bold;font-size:10px;border-radius:2px;cursor:pointer;font-family:inherit}
#lab-page .lab-btn-success:hover{background-color:#1e8c4d}
#lab-page .lab-table-wrap{background-color:#fff;max-height:320px;overflow-y:auto}
#lab-page .lab-table{width:100%;border-collapse:collapse;font-size:11px}
#lab-page .lab-table th{background-color:#e6eef5;color:#222;font-weight:bold;text-align:left;padding:8px;border-bottom:2px solid #b2c8de;position:sticky;top:0;z-index:1;white-space:nowrap}
#lab-page .lab-table td{padding:7px 8px;border-bottom:1px solid #e1e8f0;vertical-align:middle}
#lab-page .lab-table tr:nth-child(even){background-color:#f9fbfd}
#lab-page .lab-table tr:hover{background-color:#eef4fb}
#lab-page .lab-badge{padding:2px 6px;border-radius:2px;font-weight:bold;font-size:10px}
#lab-page .lab-status-sent{color:#27ae60;background:rgba(39,174,96,.12)}
#lab-page .lab-status-abs{color:#e67e22;background:rgba(230,126,34,.12)}
#lab-page .lab-status-not{color:#e74c3c;background:rgba(231,76,60,.12)}
#lab-page .lab-status-info{color:#0b5fa5;background:rgba(11,95,165,.12)}
#lab-page .lab-empty{text-align:center;padding:14px;color:#8a94a6}
#lab-page .lab-footer{background-color:#0b5fa5;color:#fff;padding:6px 10px;font-size:11px;text-align:center;border-radius:3px}
#lab-page .lab-mis{display:flex;flex-direction:column;gap:8px;padding:10px;background:#fff}
#lab-page .lab-mis-btn{display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #b2c8de;border-radius:3px;background-color:#f4f8fb;text-decoration:none;color:#222;font-weight:bold;font-size:11px;transition:background .2s;cursor:pointer}
#lab-page .lab-mis-btn:hover{background-color:#e2edf7}
#lab-page .lab-mis-btn.active{background-color:#d2e3f3;border-color:#0b5fa5}
#lab-page .lab-mis-btn i{font-size:18px;width:24px;text-align:center}
#lab-page .lab-ico-dispatched{color:#34495e}
#lab-page .lab-ico-dhims{color:#f39c12}
#lab-page .lab-ico-ready{color:#27ae60}
#lab-page .lab-ico-notready{color:#e74c3c}
@media (max-width:1100px){
  #lab-page .lab-main{grid-template-columns:1fr}
  #lab-page .lab-sidebar{display:grid;grid-template-columns:1fr 1fr;gap:0 12px}
  #lab-page .lab-sidebar > .lab-panel{margin-bottom:10px}
  #lab-page .lab-sidebar > .lab-btn-orange{align-self:end}
}
</style>

<!-- LAB DASHBOARD -->
<div id="lab-page">

  <!-- TOP BAR -->
  <div class="lab-topbar">
    <div class="lab-topbar-title">EHMS LAB DASHBOARD</div>
    <div class="lab-topbar-right">
      <span>HMS - HEALTHCARE MANAGEMENT SYSTEM</span>
      <span class="lab-nav-btn" onclick="labGoHome()">HOME</span>
      <span class="lab-nav-btn" onclick="labGoBack()">&lt; BACK</span>
      <span class="lab-nav-btn" onclick="labGoPassword()">PASSWORD</span>
    </div>
  </div>

  <!-- MAIN 3-COLUMN GRID -->
  <div class="lab-main">

    <!-- ======================= LEFT SIDEBAR ======================= -->
    <div class="lab-sidebar">
      <div class="lab-panel">
        <div class="lab-panel-head">Quick Links</div>
        <ul class="lab-ql">
          <li><a onclick="labNav('home')"><i class="fa-solid fa-square"></i> Control Panel</a></li>
          <li><a onclick="labNav('appointment_calendar')"><i class="fa-solid fa-square"></i> Appointment Calendar</a></li>
          <li><a onclick="labNav('messages_alerts')"><i class="fa-solid fa-square"></i> Messages &amp; Alerts <span class="lab-ql-badge" id="labUnreadBadge" style="display:none;">0</span></a></li>
          <li><a onclick="labNav('patient_records')"><i class="fa-solid fa-square"></i> Patient Record Management</a></li>
          <li><a onclick="labNav('radiology')"><i class="fa-solid fa-square"></i> IPD / Ward Details</a></li>
          <li><a onclick="labNav('ipd_management')"><i class="fa-solid fa-square"></i> Admissions</a></li>
          <li><a onclick="labNav('pharmacy_management')"><i class="fa-solid fa-square"></i> Drugs Dispense</a></li>
          <li><a onclick="labNav('investigations')"><i class="fa-solid fa-square"></i> Lab Management</a></li>
          <li><a onclick="labNav('accounts_management')"><i class="fa-solid fa-square"></i> Account Management</a></li>
        </ul>
      </div>

      <span class="lab-btn-orange" onclick="labNav('messages_alerts')">SEND MESSAGE</span>

      <div class="lab-panel">
        <div class="lab-panel-head">Days Alerts</div>
        <div style="padding:10px; text-align:center;">
          <h2 style="font-size:20px; margin-bottom:5px; color:#333;">0</h2>
          <span style="font-size:10px; color:#666;">NO ALERTS TODAY</span>
        </div>
      </div>

      <div class="lab-status-card">
        <p><strong>LOGIN STATUS</strong></p>
        <p>Logged In: <?php echo htmlspecialchars($__labStaff, ENT_QUOTES); ?></p>
        <p>User Type: <?php echo htmlspecialchars(strtoupper($__labRole), ENT_QUOTES); ?></p>
        <p>Date: <?php echo date('l, d-M-Y'); ?></p>
        <p>Clinic Name: HMS - HEALTHCARE MANAGEMENT SYSTEM</p>
        <button type="button" class="lab-logout-btn" onclick="labLogout()">LOGOUT</button>
      </div>
    </div>

    <!-- ======================= CENTER CONTENT ======================= -->
    <div class="lab-content">

      <!-- ENTRIES FORM -->
      <div class="lab-panel">
        <div class="lab-panel-head">Entries Form</div>
        <form class="lab-form-grid" id="labEntryForm">
          <label for="entryPatient">Patient ID or Name</label>
          <select id="entryPatient" required>
            <option value="">-- Select Visit --</option>
          </select>

          <label for="labType">Lab Type</label>
          <select id="labType" onchange="updateLabPrice()">
            <option value="">Select Lab Type --</option>
            <option value="haematology">Haematology</option>
            <option value="biochemistry">Biochemistry</option>
            <option value="microbiology">Microbiology</option>
            <option value="parasitology">Parasitology</option>
          </select>

          <label for="labToDone">Lab to be Done</label>
          <input type="text" id="labToDone" placeholder="e.g. Full Blood Count (FBC)">

          <label for="price">Price</label>
          <input type="text" id="price" placeholder="Price Auto-generated" readonly>

          <div class="lab-form-actions">
            <button type="submit" class="lab-btn-submit">Submit Request</button>
          </div>
        </form>
      </div>

      <!-- ALL LABS REQUESTED BY THE DOCTOR / MIS VIEWS -->
      <div class="lab-panel">
        <div class="lab-panel-head">
          <span id="labMainTitle">ALL LABS REQUESTED BY THE DOCTOR</span>
          <a class="lab-head-link" id="labViewAllLink" style="display:none;" onclick="setLabView('requested')">View All Requests</a>
        </div>
        <div class="lab-table-wrap">
          <div id="labMainView"><div class="lab-empty">Loading...</div></div>
        </div>
      </div>

      <div class="lab-footer">HMS - LAB DASHBOARD &middot; live data from the hospital management system</div>
    </div>

    <!-- ======================= RIGHT SIDEBAR ======================= -->
    <div class="lab-sidebar">
      <div class="lab-panel">
        <div class="lab-panel-head">Search</div>
        <div style="padding:8px;">
          <input type="text" id="labGlobalSearch" placeholder="By System Name / ID..." style="width:100%; padding:4px; border:1px solid #b2c8de;" oninput="renderLabMain()">
        </div>
      </div>

      <div class="lab-panel">
        <div class="lab-panel-head">Quick Access for MIS Reports</div>
        <div class="lab-mis">
          <a class="lab-mis-btn" id="misDispatched" onclick="setLabView('dispatched')">
            <i class="fa-solid fa-envelope lab-ico-dispatched"></i>
            <span>All Labs Dispatched (<span id="labDispCount">0</span>)</span>
          </a>
          <a class="lab-mis-btn" onclick="labNav('mis')">
            <i class="fa-solid fa-clipboard-list lab-ico-dhims"></i>
            <span>DHIMS Report</span>
          </a>
          <a class="lab-mis-btn" id="misReady" onclick="setLabView('ready')">
            <i class="fa-solid fa-check lab-ico-ready"></i>
            <span>All Ready Labs (<span id="labReadyCount">0</span>)</span>
          </a>
          <a class="lab-mis-btn" id="misNotready" onclick="setLabView('notready')">
            <i class="fa-solid fa-xmark lab-ico-notready"></i>
            <span>Not Ready Labs (<span id="labNotReadyCount">0</span>)</span>
          </a>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Lab Result Entry Modal -->
<div class="modal" id="lab-result-modal">
    <div class="modal-content" style="max-width:640px;">
        <div class="modal-header">
            <h3>Lab Result Entry</h3>
            <button class="modal-close" id="close-lab-result-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="labResultEntryForm">
                <div class="form-group">
                    <label for="entryRequisition">Requisition *</label>
                    <select id="entryRequisition">
                        <option value="">-- Awaiting requisitions --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="entrySpecimen">Specimen Type</label>
                    <input type="text" id="entrySpecimen" placeholder="e.g. Venous Blood (EDTA Tube)">
                </div>
                <div class="form-group">
                    <label for="entryTechnician">Lab Technician / Staff</label>
                    <input type="text" id="entryTechnician" value="<?php echo htmlspecialchars($__labStaff, ENT_QUOTES); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="entryResults">Test Results *</label>
                    <textarea id="entryResults" rows="3" placeholder="e.g. Hb: 12.5 g/dL, WBC: 11.2 x10^3/µL, MP: Positive (+), Platelets: 240 x10^3/µL..."></textarea>
                </div>
                <div class="form-group">
                    <label for="entryNormalRange">Normal / Reference Range</label>
                    <textarea id="entryNormalRange" rows="2" placeholder="e.g. Hb 12.0-16.0 g/dL, WBC 4.0-10.0 x10^3/µL"></textarea>
                </div>
                <div class="form-group">
                    <label for="entryInterpretation">Pathologist / Technician Remarks</label>
                    <textarea id="entryInterpretation" rows="2" placeholder="Clinical notes or observations..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-secondary" data-save="draft">Save Draft</button>
                    <button type="submit" class="btn btn-primary" data-save="final" id="entryMarkReadyBtn">Mark as Ready &amp; Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var CURRENT_LAB_STAFF = <?php echo json_encode($__labStaff); ?>;
var labData = [];
var labResultsMap = {};
var visitsCache = [];
var DISPATCH_KEY = 'hms:lab-dispatched';
var LAB_PRICES = { haematology: 'GHS 150.00', biochemistry: 'GHS 220.00', microbiology: 'GHS 180.00', parasitology: 'GHS 90.00' };
var LAB_VIEW = 'requested';

async function initLabManagement() {
    setupLabEvents();
    loadUnreadCount();
    await Promise.all([loadVisits(), loadLabRequests()]);
}

function setupLabEvents() {
    var entryForm = document.getElementById('labEntryForm');
    if (entryForm) entryForm.addEventListener('submit', handleLabEntrySubmit);
    var closeRes = document.getElementById('close-lab-result-modal');
    if (closeRes) closeRes.addEventListener('click', closeResultModal);
    var entryForm2 = document.getElementById('labResultEntryForm');
    if (entryForm2) entryForm2.addEventListener('submit', handleEntrySubmit);
}

/* ================= TOP BAR / NAV ================= */
function labNav(key) {
    if (key === 'home') {
        if (window.navigateTo) { window.navigateTo('dashboard'); return; }
        if (window.loadPage) { window.loadPage('dashboard'); return; }
    }
    if (key === 'mis' && window.loadModuleTab) { window.loadModuleTab('mis'); return; }
    if (window.loadModuleTab) window.loadModuleTab(key);
}

function labGoHome() { labNav('home'); }

function labGoBack() {
    if (window.history && window.history.length > 1) window.history.back();
    else labNav('home');
}

function labGoPassword() {
    if (window.navigateTo) window.navigateTo('change-password');
    else if (window.loadPage) window.loadPage('change-password');
}

function labLogout() {
    fetch('/hms/backend/api/auth.php?action=logout', { method: 'POST' })
        .then(function () { window.location.href = '/hms/frontend/index.php'; })
        .catch(function () { window.location.href = '/hms/frontend/index.php'; });
}

async function loadUnreadCount() {
    try {
        var r = await fetch('/hms/backend/api/messages.php?action=unread_count');
        var d = await r.json();
        if (d.success) {
            var badge = document.getElementById('labUnreadBadge');
            if (badge) {
                badge.textContent = d.unread;
                badge.style.display = d.unread > 0 ? 'inline' : 'none';
            }
        }
    } catch (e) { console.error('Unread count error:', e); }
}

/* ================= ENTRIES FORM (Lab Request) ================= */
async function loadVisits() {
    try {
        var r = await fetch('/hms/backend/api/visits.php');
        var d = await r.json();
        if (d.success) visitsCache = d.visits || [];
    } catch (e) {
        console.error('Visits load error:', e);
        visitsCache = [];
    }
    var sel = document.getElementById('entryPatient');
    sel.innerHTML = '<option value="">-- Select Visit --</option>'
        + visitsCache.map(function (v) {
            return '<option value="' + v.id + '">' + escHtml(v.visit_number + ' — ' + v.patient_name + ' (' + v.hospital_number + ')') + '</option>';
        }).join('');
}

function updateLabPrice() {
    var type = document.getElementById('labType').value;
    document.getElementById('price').value = LAB_PRICES[type] || '';
}

async function handleLabEntrySubmit(e) {
    e.preventDefault();
    var visitId = document.getElementById('entryPatient').value;
    var testType = document.getElementById('labToDone').value.trim();
    var labType = document.getElementById('labType').value;
    if (!visitId) { showAlert('Select the patient / visit first.', 'error'); return; }
    if (!testType) { showAlert('Enter the lab test to be done.', 'error'); return; }

    var data = {
        visit_id: visitId,
        test_type: testType,
        test_description: labType ? 'Category: ' + labType.charAt(0).toUpperCase() + labType.slice(1) : '',
        urgency: 'routine'
    };
    try {
        var r = await fetch('/hms/backend/api/lab.php?action=request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var d = await r.json();
        if (d.success) {
            showAlert('Lab request submitted.', 'success');
            document.getElementById('labEntryForm').reset();
            document.getElementById('price').value = '';
            setLabView('requested');
            await loadLabRequests();
        } else {
            showAlert(d.error || 'Request failed', 'error');
        }
    } catch (e) {
        console.error('Lab request error:', e);
        showAlert('Network error. Please try again.', 'error');
    }
}

/* ================= LAB DATA / VIEWS ================= */
async function loadLabRequests() {
    try {
        var r = await fetch('/hms/backend/api/lab.php?action=requests');
        var d = await r.json();
        labData = (d.success && d.requests) ? d.requests : [];
    } catch (e) {
        console.error('Lab load error:', e);
        labData = [];
    }
    await loadLabResults();
    renderLabCounts();
    renderLabMain();
    populateEntryRequisition();
}

async function loadLabResults() {
    labResultsMap = {};
    try {
        var r = await fetch('/hms/backend/api/lab.php?action=results');
        var d = await r.json();
        if (d.success) {
            (d.results || []).forEach(function (res) {
                if (res.lab_request_id !== null && res.lab_request_id !== undefined) {
                    labResultsMap[String(res.lab_request_id)] = res;
                }
            });
        }
    } catch (e) { console.error('Lab results load error:', e); }
}

function renderLabCounts() {
    var notReady = labData.filter(function (r) { return r.status === 'pending' || r.status === 'in_progress'; }).length;
    var ready = labData.filter(function (r) { return r.status === 'completed'; }).length;
    document.getElementById('labNotReadyCount').textContent = notReady;
    document.getElementById('labReadyCount').textContent = ready;
    document.getElementById('labDispCount').textContent = readDispatched().length;
}

function labStatusBadge(status) {
    if (status === 'completed') return '<span class="lab-badge lab-status-sent">Ready</span>';
    if (status === 'in_progress') return '<span class="lab-badge lab-status-sent">Sent</span>';
    if (status === 'cancelled') return '<span class="lab-badge lab-status-abs">ABS</span>';
    return '<span class="lab-badge lab-status-not">Not Ready</span>';
}

function labFilteredRows() {
    var q = (document.getElementById('labGlobalSearch').value || '').trim().toLowerCase();
    if (!q) return labData;
    return labData.filter(function (r) {
        return String(r.patient_name || '').toLowerCase().indexOf(q) >= 0
            || String(r.hospital_number || '').toLowerCase().indexOf(q) >= 0
            || String(r.test_type || '').toLowerCase().indexOf(q) >= 0
            || String(r.id || '').indexOf(q) >= 0;
    });
}

function setLabView(view) {
    LAB_VIEW = view;
    var valid = ['requested', 'ready', 'notready', 'dispatched'];
    if (valid.indexOf(view) === -1) view = 'requested';
    document.getElementById('misDispatched').classList.toggle('active', view === 'dispatched');
    document.getElementById('misReady').classList.toggle('active', view === 'ready');
    document.getElementById('misNotready').classList.toggle('active', view === 'notready');
    var titles = {
        requested: 'ALL LABS REQUESTED BY THE DOCTOR',
        ready: 'READY & VERIFIED LABS',
        notready: 'NOT READY / IN-PROGRESS LABS',
        dispatched: 'DISPATCHED REPORTS HISTORY'
    };
    document.getElementById('labMainTitle').textContent = titles[view];
    document.getElementById('labViewAllLink').style.display = view === 'requested' ? 'none' : 'inline';
    renderLabMain();
}

function renderLabMain() {
    var holder = document.getElementById('labMainView');
    if (!holder) return;
    var q = (document.getElementById('labGlobalSearch').value || '').trim().toLowerCase();
    var filter = function (rows) {
        if (!q) return rows;
        return rows.filter(function (r) {
            return String(r.patient_name || '').toLowerCase().indexOf(q) >= 0
                || String(r.hospital_number || '').toLowerCase().indexOf(q) >= 0
                || String(r.test_type || '').toLowerCase().indexOf(q) >= 0
                || String(r.id || '').indexOf(q) >= 0;
        });
    };

    if (LAB_VIEW === 'requested') {
        var rows = filter(labData);
        holder.innerHTML = '<table class="lab-table"><thead><tr>' +
            '<th>Req. ID</th><th>Patient Name</th><th>Lab Test</th><th>Doctor</th><th>Request Date</th><th>Status</th>' +
            '</tr></thead><tbody>' + (rows.length
                ? rows.map(function (r) {
                    var action = '';
                    if (r.status === 'pending' || r.status === 'in_progress') {
                        action = '<button class="lab-btn-outline" style="margin-left:4px;" onclick="openEntryFor(' + r.id + ')">Process</button>';
                    } else if (r.status === 'completed') {
                        action = '<button class="lab-btn-success" style="margin-left:4px;" onclick="dispatchReport(' + r.id + ')">Dispatch</button>';
                    }
                    return '<tr>' +
                        '<td class="fw-bold">#LAB-' + escHtml(r.id) + '</td>' +
                        '<td class="fw-bold">' + escHtml(r.patient_name || '--') + '</td>' +
                        '<td>' + escHtml(r.test_type || '--') + '</td>' +
                        '<td>' + escHtml(r.doctor_name || '--') + '</td>' +
                        '<td>' + fmtLabTime(r.requested_at) + '</td>' +
                        '<td>' + labStatusBadge(r.status) + action + '</td>' +
                    '</tr>';
                }).join('')
                : '<tr><td colspan="6" class="lab-empty">' + (q ? 'No requests match "' + escHtml(q) + '".' : 'No lab requests yet — use the Entries Form above to add one.') + '</td></tr>') +
            '</tbody></table>';
        return;
    }

    if (LAB_VIEW === 'notready') {
        var nrows = filter(labData.filter(function (r) { return r.status === 'pending' || r.status === 'in_progress'; }));
        holder.innerHTML = '<table class="lab-table"><thead><tr>' +
            '<th>Req. ID</th><th>Patient</th><th>Lab Test</th><th>Request Date</th><th>Status</th><th>Action</th>' +
            '</tr></thead><tbody>' + (nrows.length
                ? nrows.map(function (r) {
                    return '<tr>' +
                        '<td class="fw-bold">#LAB-' + escHtml(r.id) + '</td>' +
                        '<td class="fw-bold">' + escHtml(r.patient_name || '--') + '</td>' +
                        '<td>' + escHtml(r.test_type || '--') + '</td>' +
                        '<td>' + fmtLabTime(r.requested_at) + '</td>' +
                        '<td>' + labStatusBadge(r.status) + '</td>' +
                        '<td><button class="lab-btn-outline" onclick="openEntryFor(' + r.id + ')">Enter Results</button></td>' +
                    '</tr>';
                }).join('')
                : '<tr><td colspan="6" class="lab-empty">No in-progress / not-ready labs.</td></tr>') +
            '</tbody></table>';
        return;
    }

    if (LAB_VIEW === 'ready') {
        var dispatched = readDispatched();
        var rrows = filter(labData.filter(function (r) {
            return r.status === 'completed' && !dispatched.some(function (d) { return d.id === String(r.id); });
        }));
        holder.innerHTML = '<table class="lab-table"><thead><tr>' +
            '<th>Req. ID</th><th>Patient</th><th>Lab Test</th><th>Result</th><th>Verified / Completed</th><th>Action</th>' +
            '</tr></thead><tbody>' + (rrows.length
                ? rrows.map(function (r) {
                    var labRes = labResultsMap[String(r.id)] || null;
                    var resultText = labRes ? String(labRes.results || '--') : String(r.result_status || '--');
                    var doneAt = labRes && labRes.completed_at ? fmtLabTime(labRes.completed_at) : fmtLabTime(r.requested_at);
                    return '<tr>' +
                        '<td class="fw-bold">#LAB-' + escHtml(r.id) + '</td>' +
                        '<td class="fw-bold">' + escHtml(r.patient_name || '--') + '</td>' +
                        '<td>' + escHtml(r.test_type || '--') + '</td>' +
                        '<td style="max-width:240px;white-space:pre-wrap;">' + escHtml(resultText) + '</td>' +
                        '<td>' + (labRes && labRes.verified_name ? escHtml(labRes.verified_name) + '<br>' : '') + doneAt + '</td>' +
                        '<td><button class="lab-btn-success" onclick="dispatchReport(' + r.id + ')">Dispatch Report</button></td>' +
                    '</tr>';
                }).join('')
                : '<tr><td colspan="6" class="lab-empty">No ready & verified labs.</td></tr>') +
            '</tbody></table>';
        return;
    }

    /* dispatched */
    var dlist = readDispatched();
    var drows = dlist.map(function (d) {
        var r = labData.find(function (x) { return String(x.id) === d.id; }) || null;
        return '<tr>' +
            '<td class="fw-bold">#DSP-' + escHtml(d.id) + '</td>' +
            '<td class="fw-bold">' + escHtml(r ? r.patient_name : '--') + '</td>' +
            '<td>' + escHtml(r ? r.test_type : '--') + '</td>' +
            '<td>' + (d.at ? fmtLabTime(d.at) : '--') + '</td>' +
            '<td><button class="lab-btn-outline" onclick="printReport(' + (r ? r.id : 0) + ')">Print PDF</button></td>' +
        '</tr>';
    }).join('');
    holder.innerHTML = '<table class="lab-table"><thead><tr>' +
        '<th>Dispatch ID</th><th>Patient</th><th>Lab Test</th><th>Dispatch Date</th><th>Report</th>' +
        '</tr></thead><tbody>' + (drows || '<tr><td colspan="5" class="lab-empty">No dispatched reports yet — dispatch a ready lab above.</td></tr>') +
        '</tbody></table>';
}

/* ================= RESULT ENTRY ================= */
function populateEntryRequisition() {
    var sel = document.getElementById('entryRequisition');
    if (!sel) return;
    var keep = sel.value;
    var rows = labData.filter(function (r) { return r.status === 'pending' || r.status === 'in_progress'; });
    sel.innerHTML = rows.length
        ? rows.map(function (r) { return '<option value="' + r.id + '">#LAB-' + r.id + ' — ' + escHtml(r.patient_name) + ' (' + escHtml(r.test_type) + ')</option>'; }).join('')
        : '<option value="">-- Awaiting requisitions --</option>';
    if (keep && rows.some(function (r) { return String(r.id) === String(keep); })) sel.value = keep;
}

function openEntryFor(requestId) {
    populateEntryRequisition();
    var sel = document.getElementById('entryRequisition');
    if (sel.options.length && Array.from(sel.options).some(function (o) { return String(o.value) === String(requestId); })) {
        sel.value = String(requestId);
    }
    document.getElementById('labResultEntryForm').reset();
    document.getElementById('lab-result-modal').classList.add('show');
}

function closeResultModal() {
    document.getElementById('lab-result-modal').classList.remove('show');
}

async function handleEntrySubmit(e) {
    e.preventDefault();
    var reqId = document.getElementById('entryRequisition').value;
    if (!reqId) {
        showAlert('Select a requisition first — there are no pending lab requests to record results for.', 'error');
        return;
    }
    var saveAs = e.submitter && e.submitter.getAttribute ? (e.submitter.getAttribute('data-save') || 'final') : 'final';
    var results = document.getElementById('entryResults').value.trim();
    var spec = document.getElementById('entrySpecimen').value.trim();
    if (spec) results = results ? 'Specimen: ' + spec + '\n' + results : 'Specimen: ' + spec;
    if (!results) {
        showAlert('Enter the result values before saving.', 'error');
        return;
    }
    var data = {
        lab_request_id: reqId,
        results: results,
        normal_range: document.getElementById('entryNormalRange').value.trim(),
        interpretation: document.getElementById('entryInterpretation').value.trim(),
        status: saveAs
    };
    try {
        var r = await fetch('/hms/backend/api/lab.php?action=result', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var d = await r.json();
        if (d.success) {
            showAlert(saveAs === 'final' ? 'Result saved and marked as READY.' : 'Result saved as draft.', 'success');
            closeResultModal();
            setLabView(saveAs === 'final' ? 'ready' : 'notready');
            await loadLabRequests();
        } else {
            showAlert(d.error || 'Save failed', 'error');
        }
    } catch (e) {
        console.error('Entry save error:', e);
        showAlert('Network error. Please try again.', 'error');
    }
}

/* ================= DISPATCH (browser-local log; backend has no 'dispatched' status yet) ================= */
function readDispatched() {
    try {
        var raw = localStorage.getItem(DISPATCH_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch (e) { return []; }
}

function dispatchReport(requestId) {
    var list = readDispatched();
    if (!list.some(function (d) { return String(d.id) === String(requestId); })) {
        list.push({ id: String(requestId), at: new Date().toISOString() });
        try { localStorage.setItem(DISPATCH_KEY, JSON.stringify(list)); } catch (e) {}
    }
    showAlert('Report marked as dispatched (browser dispatch log).', 'success');
    renderLabCounts();
    if (LAB_VIEW === 'ready') setLabView('dispatched');
    loadLabRequests();
}

function printReport(requestId) {
    var r = labData.find(function (x) { return String(x.id) === String(requestId); });
    var lines = [
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

/* ================= HELPERS ================= */
function fmtLabTime(v) {
    return window.fmtDateTime ? window.fmtDateTime(v) : (v || '--');
}

function escHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
</script>