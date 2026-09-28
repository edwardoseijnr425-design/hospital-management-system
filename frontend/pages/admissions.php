<style>
    #adm-patient-results{position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #C9D4E0;border-top:none;border-radius:0 0 6px 6px;box-shadow:0 6px 14px rgba(15,45,89,.12);max-height:220px;overflow-y:auto;z-index:50}
    #adm-patient-results div{padding:9px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #EEF2F7;color:#1E293B}
    #adm-patient-results div:hover,#adm-patient-results div.sel{background:#E7F3FC;color:#0F2D59}
    #adm-patient-results .hpno{color:#0072BC;font-weight:700}
    #adm-patient-results .empty{padding:10px 12px;color:#64748B;cursor:default}
    #adm-patient-results .empty:hover{background:#fff;color:#64748B}
</style>

<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-bed-pulse"></i> Admissions Register</h2>
        <div style="display:flex;gap:8px;">
            <button class="btn btn-primary btn-sm" id="admit-patient-btn"><i class="fa-solid fa-user-plus"></i> Admit Patient</button>
            <button class="btn btn-secondary btn-sm" id="refresh-admissions-btn"><i class="fa-solid fa-rotate"></i> Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <select id="filter-adm-ward">
                    <option value="">All Wards</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filter-adm-status">
                    <option value="">All Status</option>
                    <option value="Admitted">Admitted</option>
                    <option value="Discharged">Discharged</option>
                </select>
            </div>
            <div class="form-group" style="flex:1;min-width:220px;">
                <input type="text" id="filter-adm-q" placeholder="Search patient, hospital no. or admission code...">
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Admission Code</th>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Hospital No.</th>
                        <th>Ward</th>
                        <th>Bed</th>
                        <th>Type</th>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="admissions-table">
                    <tr><td colspan="10" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Admit Patient Modal -->
<div class="modal" id="admit-modal">
    <div class="modal-content" style="max-width:640px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-plus"></i> Admit Patient</h3>
            <button class="modal-close" id="close-admit-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="admit-form">
                <div class="form-group" style="position:relative;">
                    <label for="adm-patient">Patient *</label>
                    <input type="text" id="adm-patient" placeholder="Type name or hospital number..." autocomplete="off">
                    <input type="hidden" id="adm-patient-id">
                    <div id="adm-patient-results" style="display:none;"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="adm-ward">Ward *</label>
                        <select id="adm-ward" required>
                            <option value="">Select Ward</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="adm-bed">Bed *</label>
                        <select id="adm-bed" required>
                            <option value="">Select Ward first</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="adm-date">Admission Date &amp; Time *</label>
                        <input type="datetime-local" id="adm-date" required>
                    </div>
                    <div class="form-group">
                        <label for="adm-type">Admission Type *</label>
                        <select id="adm-type" required>
                            <option value="Routine">Routine</option>
                            <option value="Emergency">Emergency</option>
                            <option value="Elective">Elective</option>
                            <option value="Transfer">Transfer</option>
                            <option value="Maternity">Maternity</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="adm-doctor">Admitting Doctor</label>
                        <input type="text" id="adm-doctor" placeholder="e.g. Dr. K. Mensah">
                    </div>
                    <div class="form-group">
                        <label for="adm-referred">Referred By</label>
                        <input type="text" id="adm-referred" placeholder="Referral source / department">
                    </div>
                </div>

                <div class="form-group">
                    <label for="adm-diagnosis">Provisional Diagnosis</label>
                    <input type="text" id="adm-diagnosis" placeholder="Reason for admission (optional)">
                </div>

                <div class="form-group">
                    <label for="adm-notes">Admission Notes</label>
                    <textarea id="adm-notes" placeholder="Additional clinical notes (optional)"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancel-admit">Cancel</button>
            <button class="btn btn-primary" id="save-admit"><i class="fa-solid fa-check"></i> Admit Patient</button>
        </div>
    </div>
</div>

<!-- Discharge Modal -->
<div class="modal" id="discharge-modal">
    <div class="modal-content" style="max-width:480px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-right-from-bracket"></i> Discharge Patient</h3>
            <button class="modal-close" id="close-discharge-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="discharge-summary" style="background:#F0F4F8;border:1px solid #DCE4EC;border-radius:6px;padding:12px;margin-bottom:16px;font-size:13px;"></div>
            <div class="form-group">
                <label for="discharge-notes">Discharge Notes</label>
                <textarea id="discharge-notes" placeholder="Summary of discharge status / instructions (optional)"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancel-discharge">Cancel</button>
            <button class="btn btn-danger" id="confirm-discharge"><i class="fa-solid fa-check"></i> Confirm Discharge</button>
        </div>
    </div>
</div>

<script>
let admissionsData = [];
let dischargeTarget = null;
let wardsList = [];

async function initAdmissions() {
    setupAdmissionListeners();
    await Promise.all([loadAdmissionWards(), loadAdmissions()]);
}

async function loadAdmissionWards() {
    try {
        const response = await fetch('/hms/backend/api/wards.php');
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load wards');

        wardsList = data.wards || [];
        const bindings = {'filter-adm-ward': '', 'adm-ward': 'Select Ward'};
        Object.keys(bindings).forEach(id => {
            const sel = document.getElementById(id);
            if (!sel) return;
            sel.innerHTML = '<option value="">' + bindings[id] + '</option>' +
                wardsList.map(w => `<option value="${w.id}">${escHtml(w.ward_name)}</option>`).join('');
        });
    } catch (error) {
        console.error('Admission wards load error:', error);
    }
}

async function loadAdmissions() {
    const tbody = document.getElementById('admissions-table');
    const wardId = document.getElementById('filter-adm-ward').value;
    const status = document.getElementById('filter-adm-status').value;
    const q = document.getElementById('filter-adm-q').value.trim();

    const params = new URLSearchParams();
    if (wardId) params.set('ward_id', wardId);
    if (status) params.set('status', status);
    if (q) params.set('q', q);

    try {
        const response = await fetch('/hms/backend/api/admissions.php?' + params.toString());
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load admissions');
        admissionsData = data.admissions || [];
        renderAdmissions(tbody);
    } catch (error) {
        console.error('Admissions load error:', error);
        tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">Failed to load admissions</td></tr>';
    }
}

function renderAdmissions(tbody) {
    if (!tbody) return;
    if (!admissionsData.length) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">No admissions found</td></tr>';
        return;
    }
    tbody.innerHTML = admissionsData.map(a => {
        const isAdmitted = a.status === 'Admitted';
        const badge = isAdmitted ? 'badge-info' : 'badge-secondary';
        const bedLabel = a.bed_number + (a.bed_type ? ` (${a.bed_type})` : '');
        const dischargeBtn = isAdmitted
            ? `<button class="btn btn-danger btn-sm" data-discharge="${a.id}"><i class="fa-solid fa-right-from-bracket"></i> Discharge</button>`
            : '<span style="color:#95A5A6;">—</span>';
        return `
        <tr>
            <td><strong style="color:#0F2D59;">${escHtml(a.admission_code)}</strong></td>
            <td>${fmtDateTime(a.admission_date)}</td>
            <td><strong>${escHtml(a.patient_name || '-')}</strong></td>
            <td>${escHtml(a.hospital_number || '-')}</td>
            <td>${escHtml(a.ward_name || '-')}</td>
            <td>${escHtml(bedLabel)}</td>
            <td><span class="badge badge-secondary">${escHtml(a.admission_type || 'Routine')}</span></td>
            <td>${escHtml(a.admitting_doctor || '-')}</td>
            <td><span class="badge ${badge}">${escHtml(a.status)}</span></td>
            <td>${dischargeBtn}</td>
        </tr>`;
    }).join('');
}

function setupAdmissionListeners() {
    const refreshBtn = document.getElementById('refresh-admissions-btn');
    if (refreshBtn) refreshBtn.addEventListener('click', loadAdmissions);

    document.getElementById('admit-patient-btn').addEventListener('click', openAdmitModal);
    document.getElementById('close-admit-modal').addEventListener('click', closeAdmitModal);
    document.getElementById('cancel-admit').addEventListener('click', closeAdmitModal);
    document.getElementById('save-admit').addEventListener('click', submitAdmission);
    document.getElementById('close-discharge-modal').addEventListener('click', closeDischargeModal);
    document.getElementById('cancel-discharge').addEventListener('click', closeDischargeModal);
    document.getElementById('confirm-discharge').addEventListener('click', submitDischarge);

    document.getElementById('filter-adm-ward').addEventListener('change', loadAdmissions);
    document.getElementById('filter-adm-status').addEventListener('change', loadAdmissions);

    let card;
    let qTimer;
    const qInput = document.getElementById('filter-adm-q');
    if (qInput) qInput.addEventListener('input', function() {
        clearTimeout(qTimer);
        qTimer = setTimeout(loadAdmissions, 400);
    });

    document.getElementById('adm-ward').addEventListener('change', function() {
        loadAvailableBeds(this.value);
    });

    // Patient search with results dropdown
    const patInput = document.getElementById('adm-patient');
    const patResults = document.getElementById('adm-patient-results');
    patInput.addEventListener('input', function() {
        clearTimeout(card);
        const query = this.value.trim();
        if (query.length < 2) {
            document.getElementById('adm-patient-id').value = '';
            patResults.style.display = 'none';
            patResults.innerHTML = '';
            return;
        }
        card = setTimeout(() => searchAdmissionPatients(query), 300);
    });
    document.addEventListener('click', function(e) {
        if (!patResults.contains(e.target) && e.target !== patInput) patResults.style.display = 'none';
    });

    // Discharge buttons (event delegation)
    const tbody = document.getElementById('admissions-table');
    tbody.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-discharge]');
        if (btn) openDischargeModal(parseInt(btn.getAttribute('data-discharge'), 10));
    });
}

function nowLocal() {
    const d = new Date();
    const p = n => ('0' + n).slice(-2);
    return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + 'T' + p(d.getHours()) + ':' + p(d.getMinutes());
}

async function searchAdmissionPatients(query) {
    const resultsBox = document.getElementById('adm-patient-results');
    try {
        const response = await fetch('/hms/backend/api/patients.php?action=search&q=' + encodeURIComponent(query) + '&limit=10');
        const data = await response.json();
        const patients = (data.success && data.patients) ? data.patients : [];
        if (!patients.length) {
            resultsBox.innerHTML = '<div class="empty">No matching patients found</div>';
            resultsBox.style.display = 'block';
            return;
        }
        resultsBox.innerHTML = patients.map((p, i) => `
            <div data-pid="${p.id}" data-name="${escHtml((p.first_name || '') + ' ' + (p.last_name || ''))}" data-hn="${escHtml(p.hospital_number || '')}">
                <span class="hpno">${escHtml((p.hospital_number || ''))}</span> &nbsp; ${escHtml((p.first_name || '') + ' ' + (p.last_name || ''))}${p.gender ? ' · ' + escHtml(p.gender) : ''}
            </div>`).join('');
        resultsBox.style.display = 'block';
        resultsBox.querySelectorAll('div[data-pid]').forEach(el => {
            el.addEventListener('click', () => {
                document.getElementById('adm-patient-id').value = el.getAttribute('data-pid');
                document.getElementById('adm-patient').value = el.getAttribute('data-hn') + ' - ' + el.getAttribute('data-name');
                resultsBox.style.display = 'none';
                resultsBox.innerHTML = '';
            });
        });
    } catch (error) {
        console.error('Patient search error:', error);
        resultsBox.style.display = 'none';
    }
}

async function loadAvailableBeds(wardId) {
    const bedSel = document.getElementById('adm-bed');
    if (!wardId) {
        bedSel.innerHTML = '<option value="">Select Ward first</option>';
        return;
    }
    bedSel.innerHTML = '<option value="">Loading beds...</option>';
    try {
        const response = await fetch('/hms/backend/api/admissions.php?action=available_beds&ward_id=' + encodeURIComponent(wardId));
        const data = await response.json();
        const beds = (data.success && data.beds) ? data.beds : [];
        if (!beds.length) {
            bedSel.innerHTML = '<option value="">No available beds</option>';
            return;
        }
        bedSel.innerHTML = beds.map(b => `<option value="${b.id}">${escHtml(b.bed_number)}${b.bed_type ? ' (' + escHtml(b.bed_type) + ')' : ''}</option>`).join('');
    } catch (error) {
        console.error('Available beds load error:', error);
        bedSel.innerHTML = '<option value="">Failed to load beds</option>';
    }
}

function openAdmitModal() {
    document.getElementById('admit-form').reset();
    document.getElementById('adm-patient-id').value = '';
    document.getElementById('adm-patient-results').style.display = 'none';
    document.getElementById('adm-date').value = nowLocal();
    document.getElementById('adm-bed').innerHTML = '<option value="">Select Ward first</option>';
    document.getElementById('adm-ward').value = '';
    document.getElementById('admit-modal').classList.add('show');
    document.getElementById('adm-patient').focus();
}

function closeAdmitModal() {
    document.getElementById('admit-modal').classList.remove('show');
}

async function submitAdmission() {
    const patientId = document.getElementById('adm-patient-id').value;
    const wardId = document.getElementById('adm-ward').value;
    const bedId = document.getElementById('adm-bed').value;
    const dateVal = document.getElementById('adm-date').value;

    if (!patientId) { showAlert('Please select a patient first', 'error'); return; }
    if (!wardId) { showAlert('Please select a ward', 'error'); return; }
    if (!bedId) { showAlert('Please select a bed', 'error'); return; }
    if (!dateVal) { showAlert('Please set the admission date', 'error'); return; }

    const saveBtn = document.getElementById('save-admit');
    saveBtn.disabled = true;

    try {
        const response = await fetch('/hms/backend/api/admissions.php?action=admit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                patient_id: patientId,
                ward_id: wardId,
                bed_id: bedId,
                admission_date: dateVal,
                admission_type: document.getElementById('adm-type').value,
                admitting_doctor: document.getElementById('adm-doctor').value,
                diagnosis: document.getElementById('adm-diagnosis').value,
                referred_by: document.getElementById('adm-referred').value,
                notes: document.getElementById('adm-notes').value
            })
        });
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Admission failed');

        showAlert('Patient admitted — ' + data.admission_code, 'success');
        closeAdmitModal();
        await loadAdmissions();
    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        saveBtn.disabled = false;
    }
}

function openDischargeModal(id) {
    const admission = admissionsData.find(a => a.id === id);
    if (!admission) return;
    dischargeTarget = admission;
    document.getElementById('discharge-summary').innerHTML =
        `<div><strong style="color:#0072BC;">PATIENT:</strong> ${escHtml(admission.patient_name || '-')} (${escHtml(admission.hospital_number || '-')})</div>
         <div><strong style="color:#0072BC;">WARD / BED:</strong> ${escHtml(admission.ward_name || '-')} · ${escHtml(admission.bed_number || '-')}</div>
         <div><strong style="color:#0072BC;">ADMITTED:</strong> ${fmtDateTime(admission.admission_date)} · ${escHtml(admission.admission_type || '')}</div>`;
    document.getElementById('discharge-notes').value = '';
    document.getElementById('discharge-modal').classList.add('show');
}

function closeDischargeModal() {
    document.getElementById('discharge-modal').classList.remove('show');
    dischargeTarget = null;
}

async function submitDischarge() {
    if (!dischargeTarget) return;
    const confirmBtn = document.getElementById('confirm-discharge');
    confirmBtn.disabled = true;
    try {
        const response = await fetch('/hms/backend/api/admissions.php?action=discharge&id=' + dischargeTarget.id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ discharge_notes: document.getElementById('discharge-notes').value })
        });
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Discharge failed');

        showAlert('Patient discharged — bed released', 'success');
        closeDischargeModal();
        await loadAdmissions();
    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        confirmBtn.disabled = false;
    }
}
</script>