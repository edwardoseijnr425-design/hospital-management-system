<div class="card">
    <div class="card-header">
        <h2>Appointment Calendar</h2>
        <button class="btn btn-primary btn-sm" id="new-appointment-btn">Schedule Appointment</button>
    </div>
    <div class="card-body">
        <div class="stats-grid" style="margin-bottom:18px;">
            <div class="stat-card">
                <div class="stat-icon primary">&#128197;</div>
                <div class="stat-info"><h3 id="stat-today">0</h3><p>Today</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">&#9201;</div>
                <div class="stat-info"><h3 id="stat-upcoming">0</h3><p>Upcoming</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">&#9989;</div>
                <div class="stat-info"><h3 id="stat-completed">0</h3><p>Completed</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon danger">&#10060;</div>
                <div class="stat-info"><h3 id="stat-cancelled">0</h3><p>Cancelled</p></div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <select id="filter-appt-status">
                    <option value="">All Statuses</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="no_show">No Show</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Appointment Date</th>
                        <th>Patient</th>
                        <th>Hospital No.</th>
                        <th>Doctor</th>
                        <th>Consultation</th>
                        <th>Visit</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="appointments-table">
                    <tr><td colspan="9" style="text-align: center;">Loading...</td></tr>
                </tbody>
            </table>
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
        document.getElementById('appointments-table').innerHTML = '<tr><td colspan="9" style="text-align:center;">Failed to load appointments</td></tr>';
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
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No appointments found</td></tr>';
        return;
    }
    tbody.innerHTML = appointmentsData.map(a => {
        const badge = a.status === 'completed' ? 'badge-success'
            : a.status === 'cancelled' || a.status === 'no_show' ? 'badge-danger'
            : a.status === 'confirmed' ? 'badge-info' : 'badge-warning';
        const consultClass = a.consultation_type === 'EMERGENCY' ? 'badge-danger'
            : a.consultation_type === 'ENT' ? 'badge-warning'
            : a.consultation_type === 'EYE' ? 'badge-info' : 'badge-success';
        const visitClass = a.visit_type === 'Review' ? 'badge-secondary' : 'badge-info';
        return `
        <tr>
            <td>${fmtDateTime(a.appointment_date)}</td>
            <td><strong>${a.patient_name || '-'}</strong></td>
            <td>${a.hospital_number || '-'}</td>
            <td>${a.doctor_name || '-'}</td>
            <td><span class="badge ${consultClass}">${a.consultation_type || 'OPD'}</span></td>
            <td><span class="badge ${visitClass}">${a.visit_type || 'New'}</span></td>
            <td>${a.reason || '-'}</td>
            <td><span class="badge ${badge}">${(a.status || '').replace('_', ' ')}</span></td>
            <td style="white-space:nowrap;">
                <button class="btn btn-sm btn-secondary" onclick="openEditAppointment(${a.id})">Edit</button>
                ${a.status === 'scheduled' || a.status === 'confirmed'
                    ? `<button class="btn btn-sm btn-success" onclick="setApptStatus(${a.id}, 'completed')">Complete</button>
                       <button class="btn btn-sm btn-warning" onclick="setApptStatus(${a.id}, 'cancelled')">Cancel</button>`
                    : ''}
                <button class="btn btn-sm btn-danger" onclick="deleteAppointment(${a.id})">Delete</button>
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