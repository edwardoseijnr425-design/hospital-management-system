<style>
/* ============ OPERATION THEATRE CALENDAR : PASTED DESIGN ============
   Scoped under #otc-page. The shell has no Bootstrap, so the pasted
   bg-* / dark-thead / badge utilities are declared here locally. */
#otc-page .d-flex{display:flex}
#otc-page .align-items-center{align-items:center}
#otc-page .justify-content-between{justify-content:space-between}
#otc-page .gap-2{gap:.5rem}
#otc-page .ps-2{padding-left:.5rem}
#otc-page .p-2{padding:.5rem}
#otc-page .rounded{border-radius:8px}
#otc-page .mb-3{margin-bottom:1rem}
#otc-page .mb-0{margin:0}
#otc-page .fw-bold{font-weight:700}
#otc-page .text-uppercase{text-transform:uppercase}
#otc-page .text-white{color:#fff}
#otc-page .text-dark{color:#212529}
#otc-page .text-muted{color:#6c757d}
#otc-page .text-center{text-align:center}
#otc-page .py-4{padding-top:1.5rem;padding-bottom:1.5rem}

/* Top header banner (matches the pasted blue bar) */
#otc-page .otc-banner{
    background-color:#0d6efd;color:#fff;
    display:flex;align-items:center;justify-content:space-between;
    padding:.5rem .5rem .5rem .75rem;border-radius:8px;
    box-shadow:0 .125rem .25rem rgba(0,0,0,.075);margin-bottom:1rem;
}
#otc-page .otc-banner h5{margin:0;color:#fff;font-weight:700;text-transform:uppercase;font-size:15px;letter-spacing:.5px}
#otc-page .btn-light{background:#fff;color:#0d6efd;border:1px solid #fff}
#otc-page .btn-light:hover{background:#E9ECEF}
#otc-page .btn-success{background:#198754;color:#fff;border:none}
#otc-page .btn-success:hover{filter:brightness(1.08)}

/* Main card (blue header strip + white body) */
#otc-page .otc-card{
    background:#fff;border:1px solid #E2E8F0;border-radius:8px;
    box-shadow:0 .125rem .25rem rgba(0,0,0,.075);overflow:hidden;
}
#otc-page .otc-card-head{
    background-color:#0d6efd;color:#fff;font-weight:700;
    display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;
    padding:12px 16px;
}
#otc-page .otc-card-body{padding:1rem}

/* Stats strip (kept real — same statistical backend as before) */
#otc-page .otc-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-bottom:14px}
#otc-page .otc-stat{background:#fff;border:1px solid #E2E8F0;border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:12px}
#otc-page .otc-stat b{font-size:22px;color:#0F2D59}
#otc-page .otc-stat span{font-size:10px;font-weight:700;letter-spacing:.4px;color:#64748B;text-transform:uppercase}

/* Filter row */
#otc-page .otc-filterrow{display:flex;gap:10px;align-items:center;margin-bottom:12px;flex-wrap:wrap}
#otc-page .otc-filterrow select{padding:8px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:12.5px;font-family:inherit;color:#334155;background:#fff}

/* Dark-thead table (matches the pasted thead class="table-dark") */
#otc-page .otc-table{width:100%;border-collapse:collapse;font-size:13px}
#otc-page .otc-table thead th{
    background:#212529;color:#fff;font-size:11px;font-weight:700;
    text-transform:uppercase;letter-spacing:.5px;text-align:left;
    padding:10px 12px;border-bottom:2px solid #343A40;white-space:nowrap;
}
#otc-page .otc-table tbody td{padding:9px 12px;border-bottom:1px solid #E2E8F0;vertical-align:middle}
#otc-page .otc-table tbody tr:hover{background:#F5F9FD}
#otc-page .otc-table tbody tr:last-child td{border-bottom:none}

/* Bootstrap-style badges (bg-*) used in the pasted status column */
#otc-page .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:5px;font-size:11px;font-weight:700;letter-spacing:.3px;text-transform:uppercase;white-space:nowrap}
#otc-page .bg-warning{background:#ffc107;color:#212529}
#otc-page .bg-info{background:#0dcaf0;color:#212529}
#otc-page .bg-success{background:#198754;color:#fff}
#otc-page .bg-danger{background:#dc3545;color:#fff}
#otc-page .bg-secondary{background:#6c757d;color:#fff}

/* Small action buttons (btn-sm equivalents) */
#otc-page .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;border:none;border-radius:5px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;padding:6px 12px;cursor:pointer;line-height:1;transition:background .15s,filter .15s;text-decoration:none;white-space:nowrap}
#otc-page .btn-secondary{background:#F1F5F9;color:#34495E;border:1px solid #C0C0C0}
#otc-page .btn-secondary:hover{background:#E4EAF1}
#otc-page .btn-warning{background:#ffc107;color:#212529}
#otc-page .btn-danger{background:#dc3545;color:#fff}
#otc-page .btn-outline-primary{background:#fff;color:#0d6efd;border:1px solid #0d6efd}
#otc-page .btn-outline-primary:hover{background:#F0F6FF}
#otc-page .btn-outline-info{background:#fff;color:#0dcaf0;border:1px solid #0dcaf0}
#otc-page .btn-outline-info:hover{background:#F0FBFF}
</style>
<div id="otc-page">

  <!-- TOP HEADER BANNER -->
  <div class="otc-banner">
    <div class="d-flex align-items-center gap-2 ps-2">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="16" y1="2" x2="16" y2="6"></line>
        <line x1="8" y1="2" x2="8" y2="6"></line>
        <line x1="3" y1="10" x2="21" y2="10"></line>
      </svg>
      <h5>OPERATION THEATRE CALENDAR</h5>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="#" class="btn btn-light fw-bold" onclick="otcNavHome(event)">HOME</a>
      <button class="btn btn-light fw-bold" onclick="otcGoBack(event)">&lt; BACK</button>
    </div>
  </div>

  <!-- MAIN CARD -->
  <div class="otc-card">
    <div class="otc-card-head">
      <span>BOOKED OPERATIONS &amp; PATIENT SCHEDULES</span>
      <button class="btn btn-success fw-bold" id="new-appointment-btn">+ BOOK NEW OPERATION</button>
    </div>
    <div class="otc-card-body">

      <!-- Real stats strip -->
      <div class="otc-stats">
        <div class="otc-stat"><b id="stat-today">0</b><span>Today</span></div>
        <div class="otc-stat"><b id="stat-upcoming">0</b><span>Upcoming</span></div>
        <div class="otc-stat"><b id="stat-completed">0</b><span>Completed</span></div>
        <div class="otc-stat"><b id="stat-cancelled">0</b><span>Cancelled</span></div>
      </div>

      <!-- Status filter -->
      <div class="otc-filterrow">
        <select id="filter-appt-status">
          <option value="">All Statuses</option>
          <option value="scheduled">Scheduled</option>
          <option value="confirmed">Confirmed</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
          <option value="no_show">No Show</option>
        </select>
      </div>

      <!-- Booked operations / patient schedules table -->
      <div style="overflow-x:auto;">
        <table class="otc-table">
          <thead>
            <tr>
              <th>Date &amp; Time</th>
              <th>Patient No</th>
              <th>Patient Name</th>
              <th>Doctor</th>
              <th>Consultation</th>
              <th>Visit</th>
              <th>Status</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody id="appointments-table">
            <tr><td colspan="8" class="text-center" style="padding:18px;color:#64748B;">Loading...</td></tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>

</div>

<!-- Schedule Appointment Modal -->
<div class="modal" id="appointment-modal">
    <div class="modal-content" style="max-width:620px;">
        <div class="modal-header">
            <h3>Schedule Appointment</h3>
            <button class="modal-close" id="close-appointment-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="appointment-form">
                <input type="hidden" id="appointment-id">
                <div class="form-group">
                    <label for="appt-patient">Patient *</label>
                    <select id="appt-patient" required>
                        <option value="">Select Patient</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="appt-date">Date &amp; Time *</label>
                        <input type="datetime-local" id="appt-date" required>
                    </div>
                    <div class="form-group">
                        <label for="appt-status">Status</label>
                        <select id="appt-status">
                            <option value="scheduled">Scheduled</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="appt-doctor">Doctor</label>
                        <select id="appt-doctor"><option value="">Select Doctor</option></select>
                    </div>
                    <div class="form-group">
                        <label for="appt-consultation">Consultation Type *</label>
                        <select id="appt-consultation" required>
                            <option value="OPD">OPD</option>
                            <option value="ENT">ENT</option>
                            <option value="EYE">EYE</option>
                            <option value="EMERGENCY">EMERGENCY</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="appt-visit">Visit Type *</label>
                        <select id="appt-visit" required>
                            <option value="New">New</option>
                            <option value="Review">Review</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="appt-reason">Reason / Notes</label>
                    <textarea id="appt-reason" rows="2" placeholder="Reason for appointment..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Appointment</button>
                    <button type="button" class="btn btn-secondary" id="cancel-appointment">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let appointmentsData = [];
let patientsCache = [];
let doctorsCache = [];
function otcNavHome(e){ if(e) e.preventDefault(); if(window.navigateTo) window.navigateTo('dashboard'); else if(window.loadPage) window.loadPage('dashboard'); }
function otcGoBack(e){ if(e) e.preventDefault(); if(window.goBackPage && typeof window.goBackPage==='function') window.goBackPage(); else if(window.navigateTo) window.navigateTo('dashboard'); }
async function initAppointmentCalendar() {
    setupEventListeners();
    await Promise.all([loadPatients(), loadDoctors(), loadAppointments(), loadStats()]);
}

async function loadPatients() {
    try {
        const response = await fetch('/hms/backend/api/patients.php');
        const data = await response.json();
        if (data.success) {
            patientsCache = data.patients || [];
            const sel = document.getElementById('appt-patient');
            patientsCache.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${p.first_name} ${p.last_name || ''} (${p.hospital_number})`;
                sel.appendChild(opt);
            });
        }
    } catch (error) { console.error('Patients load error:', error); }
}

async function loadDoctors() {
    try {
        const response = await fetch('/hms/backend/api/messages.php?action=recipients&type=staff');
        const data = await response.json();
        if (data.success) {
            doctorsCache = (data.recipients || []).filter(u => u.role === 'doctor' || u.role === 'super_admin' || u.role === 'admin');
            const sel = document.getElementById('appt-doctor');
            doctorsCache.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = u.name;
                sel.appendChild(opt);
            });
        }
    } catch (error) { console.error('Doctors load error:', error); }
}

async function loadAppointments() {
    const status = document.getElementById('filter-appt-status').value;
    const params = new URLSearchParams();
    if (status) params.set('status', status);
    try {
        const response = await fetch(`/hms/backend/api/appointments.php?${params}`);
        const data = await response.json();
        if (data.success) {
            appointmentsData = data.appointments || [];
            renderTable();
        }
    } catch (error) {
        console.error('Appointments load error:', error);
        document.getElementById('appointments-table').innerHTML = '<tr><td colspan="8" class="text-center" style="padding:18px;color:#C0392B;">Failed to load appointments</td></tr>';
    }
}

async function loadStats() {
    try {
        const response = await fetch('/hms/backend/api/appointments.php?action=stats');
        const data = await response.json();
        if (data.success) {
            document.getElementById('stat-today').textContent = data.stats.today;
            document.getElementById('stat-upcoming').textContent = data.stats.upcoming;
            document.getElementById('stat-completed').textContent = data.stats.completed;
            document.getElementById('stat-cancelled').textContent = data.stats.cancelled;
        }
    } catch (error) { console.error('Stats load error:', error); }
}

function renderTable() {
    const tbody = document.getElementById('appointments-table');
    if (!appointmentsData.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="padding:18px;color:#94A3B8;">No booked operations / appointments found.</td></tr>';
        return;
    }
    tbody.innerHTML = appointmentsData.map(a => {
        const badge = a.status === 'completed' ? 'bg-success'
            : a.status === 'cancelled' || a.status === 'no_show' ? 'bg-danger'
            : a.status === 'confirmed' ? 'bg-info'
            : a.status === 'scheduled' ? 'bg-warning' : 'bg-secondary';
        const consultBadge = a.consultation_type === 'EMERGENCY' ? 'bg-danger'
            : a.consultation_type === 'ENT' ? 'bg-warning'
            : a.consultation_type === 'EYE' ? 'bg-info' : 'bg-success';
        const visitBadge = a.visit_type === 'Review' ? 'bg-secondary' : 'bg-info';
        const dtText = fmtDateTime(a.appointment_date);
        const dtParts = String(dtText).split(' ');
        const datePart = dtParts[0] || '-';
        const timePart = dtParts.length > 1 ? dtParts.slice(1).join(' ') : '';
        return `
        <tr>
            <td>
                <strong>${escHtml(datePart)}</strong><br>
                <small class="text-muted">${escHtml(timePart)}</small>
            </td>
            <td><span class="badge bg-secondary">${escHtml(a.hospital_number || '-')}</span></td>
            <td>
                <strong>${escHtml(a.patient_name || '-')}</strong><br>
                <small class="text-muted">${escHtml((a.reason || '') || '-')}</small>
            </td>
            <td>${escHtml(a.doctor_name || '-')}</td>
            <td><span class="badge ${consultBadge}">${escHtml(a.consultation_type || 'OPD')}</span></td>
            <td><span class="badge ${visitBadge}">${escHtml(a.visit_type || 'New')}</span></td>
            <td><span class="badge ${badge}">${escHtml((a.status || '').replace('_', ' '))}</span></td>
            <td class="text-center" style="white-space:nowrap;">
                <button class="btn btn-outline-primary" onclick="openEditAppointment(${a.id})">Edit</button>
                ${a.status === 'scheduled' || a.status === 'confirmed'
                    ? `<button class="btn btn-success" onclick="setApptStatus(${a.id}, 'completed')">Complete</button>
                       <button class="btn btn-warning" onclick="setApptStatus(${a.id}, 'cancelled')">Cancel</button>`
                    : ''}
                <button class="btn btn-danger" onclick="deleteAppointment(${a.id})">Delete</button>
            </td>
        </tr>`;
    }).join('');
}

function openEditAppointment(id) {
    const a = appointmentsData.find(x => x.id === id);
    if (!a) return;
    document.getElementById('appointment-id').value = a.id;
    document.getElementById('appt-patient').value = a.patient_id;
    if (a.appointment_date) {
        const d = new Date(String(a.appointment_date).replace(' ', 'T'));
        const local = new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        document.getElementById('appt-date').value = local;
    }
    document.getElementById('appt-status').value = a.status;
    document.getElementById('appt-doctor').value = a.doctor_id || '';
    document.getElementById('appt-reason').value = a.reason || '';
    document.getElementById('appt-consultation').value = a.consultation_type || 'OPD';
    document.getElementById('appt-visit').value = a.visit_type || 'New';
    document.getElementById('appointment-modal').classList.add('show');
}

async function setApptStatus(id, status) {
    try {
        const response = await fetch(`/hms/backend/api/appointments.php?id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: status })
        });
        const result = await response.json();
        if (result.success) {
            showAlert(status === 'completed' ? 'Appointment completed' : 'Appointment status updated', 'success');
            await Promise.all([loadAppointments(), loadStats()]);
        } else {
            showAlert(result.error || 'Update failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}

async function deleteAppointment(id) {
    if (!confirm('Delete this appointment?')) return;
    try {
        const response = await fetch(`/hms/backend/api/appointments.php?id=${id}`, { method: 'DELETE' });
        const result = await response.json();
        if (result.success) {
            showAlert('Appointment deleted', 'success');
            await Promise.all([loadAppointments(), loadStats()]);
        } else {
            showAlert(result.error || 'Delete failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}

function setupEventListeners() {
    document.getElementById('new-appointment-btn').addEventListener('click', () => navigateTo('schedule-appointment'));
    document.getElementById('close-appointment-modal').addEventListener('click', () =>
        document.getElementById('appointment-modal').classList.remove('show'));
    document.getElementById('cancel-appointment').addEventListener('click', () =>
        document.getElementById('appointment-modal').classList.remove('show'));
    document.getElementById('appointment-form').addEventListener('submit', handleAppointmentSubmit);
    document.getElementById('filter-appt-status').addEventListener('change', loadAppointments);
}

async function handleAppointmentSubmit(e) {
    e.preventDefault();
    const id = document.getElementById('appointment-id').value;
    const data = {
        patient_id: document.getElementById('appt-patient').value,
        appointment_date: document.getElementById('appt-date').value,
        doctor_id: document.getElementById('appt-doctor').value || null,
        consultation_type: document.getElementById('appt-consultation').value,
        visit_type: document.getElementById('appt-visit').value,
        reason: document.getElementById('appt-reason').value,
        status: document.getElementById('appt-status').value
    };
    if (!data.patient_id || !data.appointment_date) {
        showAlert('Patient and date/time are required', 'error');
        return;
    }
    try {
        const url = id ? `/hms/backend/api/appointments.php?id=${id}` : '/hms/backend/api/appointments.php?action=create';
        const method = id ? 'PUT' : 'POST';
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            showAlert(id ? 'Appointment updated' : 'Appointment scheduled', 'success');
            document.getElementById('appointment-modal').classList.remove('show');
            await Promise.all([loadAppointments(), loadStats()]);
        } else {
            showAlert((result.error || result.errors && Object.values(result.errors)[0]) || 'Operation failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}
</script>