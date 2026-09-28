<div class="card">
    <div class="card-header">
        <h2>Lab Management</h2>
        <div style="display:flex;gap:10px;">
            <button class="btn btn-primary btn-sm" id="new-lab-request-btn">New Lab Request</button>
            <button class="btn btn-secondary btn-sm" id="refresh-lab-btn">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="stats-grid" style="margin-bottom:18px;">
            <div class="stat-card">
                <div class="stat-icon warning">&#9203;</div>
                <div class="stat-info"><h3 id="stat-lab-pending">0</h3><p>Pending</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon primary">&#128269;</div>
                <div class="stat-info"><h3 id="stat-lab-progress">0</h3><p>In Progress</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">&#9989;</div>
                <div class="stat-info"><h3 id="stat-lab-completed">0</h3><p>Completed</p></div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <input type="text" id="lab-search" placeholder="Search by patient name or hospital number...">
            </div>
            <div class="form-group">
                <select id="lab-filter-status">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Requested At</th>
                        <th>Patient</th>
                        <th>Hospital No.</th>
                        <th>Test Type</th>
                        <th>Urgency</th>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="lab-table">
                    <tr><td colspan="8" style="text-align: center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Lab Request Modal -->
<div class="modal" id="lab-request-modal">
    <div class="modal-content" style="max-width:560px;">
        <div class="modal-header">
            <h3>New Lab Request</h3>
            <button class="modal-close" id="close-lab-request-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="lab-request-form">
                <div class="form-group">
                    <label for="lab-visit">Visit *</label>
                    <select id="lab-visit" required>
                        <option value="">Select Visit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="lab-test-type">Test Type *</label>
                    <input type="text" id="lab-test-type" placeholder="e.g. Full Blood Count, Malaria RDT, Urine Analysis" required>
                </div>
                <div class="form-group">
                    <label for="lab-test-description">Description / Clinical Notes</label>
                    <textarea id="lab-test-description" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label for="lab-urgency">Urgency</label>
                    <select id="lab-urgency">
                        <option value="routine">Routine</option>
                        <option value="urgent">Urgent</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                    <button type="button" class="btn btn-secondary" id="cancel-lab-request">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Result Modal -->
<div class="modal" id="lab-result-modal">
    <div class="modal-content" style="max-width:560px;">
        <div class="modal-header">
            <h3>Record Lab Result</h3>
            <button class="modal-close" id="close-lab-result-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="lab-result-form">
                <input type="hidden" id="lab-result-request-id">
                <div class="form-group">
                    <label for="lab-result-context">Request</label>
                    <input type="text" id="lab-result-context" readonly style="background:#EEF2F7;">
                </div>
                <div class="form-group">
                    <label for="lab-results">Results *</label>
                    <textarea id="lab-results" rows="4" required placeholder="e.g. Hb: 12.5 g/dL, WBC: 6.4 x10^9/L..."></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="lab-normal-range">Normal Range</label>
                        <input type="text" id="lab-normal-range">
                    </div>
                    <div class="form-group">
                        <label for="lab-result-status">Status</label>
                        <select id="lab-result-status">
                            <option value="final">Final</option>
                            <option value="draft">Draft</option>
                            <option value="amended">Amended</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="lab-interpretation">Interpretation</label>
                    <textarea id="lab-interpretation" rows="2"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Result</button>
                    <button type="button" class="btn btn-secondary" id="cancel-lab-result">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let labData = [];
let visitsCache = [];

async function initLabManagement() {
    setupEventListeners();
    await Promise.all([loadVisits(), loadLabRequests(), loadLabStats()]);
}

async function loadVisits() {
    try {
        const response = await fetch('/hms/backend/api/visits.php');
        const data = await response.json();
        if (data.success) {
            visitsCache = data.visits || [];
            const sel = document.getElementById('lab-visit');
            visitsCache.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = `${v.visit_number} — ${v.patient_name} (${v.hospital_number})`;
                sel.appendChild(opt);
            });
        }
    } catch (error) { console.error('Visits load error:', error); }
}

async function loadLabRequests() {
    const status = document.getElementById('lab-filter-status').value;
    const q = document.getElementById('lab-search').value.trim();
    const params = new URLSearchParams();
    if (status) params.set('status', status);
    if (q) params.set('q', q);
    try {
        const response = await fetch(`/hms/backend/api/lab.php?action=requests&${params}`);
        const data = await response.json();
        if (data.success) {
            labData = data.requests || [];
            renderLabTable();
        } else {
            throw new Error(data.error || 'Failed to load lab requests');
        }
    } catch (error) {
        console.error('Lab load error:', error);
        document.getElementById('lab-table').innerHTML = '<tr><td colspan="8" style="text-align:center;">Failed to load lab requests</td></tr>';
    }
}

async function loadLabStats() {
    try {
        const response = await fetch('/hms/backend/api/lab.php?action=stats');
        const data = await response.json();
        if (data.success) {
            document.getElementById('stat-lab-pending').textContent = data.stats.pending;
            document.getElementById('stat-lab-progress').textContent = data.stats.in_progress;
            document.getElementById('stat-lab-completed').textContent = data.stats.completed;
        }
    } catch (error) { console.error('Stats load error:', error); }
}

function renderLabTable() {
    const tbody = document.getElementById('lab-table');
    if (!labData.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No lab requests found</td></tr>';
        return;
    }
    tbody.innerHTML = labData.map(r => {
        const badge = r.status === 'completed' ? 'badge-success'
            : r.status === 'cancelled' ? 'badge-danger'
            : r.status === 'in_progress' ? 'badge-info' : 'badge-warning';
        const urgencyBadge = r.urgency === 'emergency' ? 'badge-danger'
            : r.urgency === 'urgent' ? 'badge-warning' : 'badge-secondary';
        return `
        <tr>
            <td>${fmtDateTime(r.requested_at)}</td>
            <td><strong>${r.patient_name || '-'}</strong></td>
            <td>${r.hospital_number || '-'}</td>
            <td>${r.test_type || '-'}</td>
            <td><span class="badge ${urgencyBadge}">${r.urgency || 'routine'}</span></td>
            <td>${r.doctor_name || '-'}</td>
            <td><span class="badge ${badge}">${(r.status || '').replace('_', ' ')}</span></td>
            <td style="white-space:nowrap;">
                ${r.status === 'pending' || r.status === 'in_progress'
                    ? `<button class="btn btn-sm btn-primary" onclick="openResultModal(${r.id}, '${escJs(r.test_type)}', '${escJs(r.patient_name)}')">Record Result</button>`
                    : `<span class="badge badge-secondary">${r.result_status || 'no result'}</span>`}
            </td>
        </tr>`;
    }).join('');
}

function escJs(s) {
    return String(s == null ? '' : s).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function openResultModal(requestId, testType, patientName) {
    document.getElementById('lab-result-form').reset();
    document.getElementById('lab-result-request-id').value = requestId;
    document.getElementById('lab-result-context').value = `${testType} — ${patientName}`;
    document.getElementById('lab-result-modal').classList.add('show');
}

function setupEventListeners() {
    document.getElementById('new-lab-request-btn').addEventListener('click', () => {
        document.getElementById('lab-request-form').reset();
        document.getElementById('lab-request-modal').classList.add('show');
    });
    document.getElementById('close-lab-request-modal').addEventListener('click', () =>
        document.getElementById('lab-request-modal').classList.remove('show'));
    document.getElementById('cancel-lab-request').addEventListener('click', () =>
        document.getElementById('lab-request-modal').classList.remove('show'));
    document.getElementById('close-lab-result-modal').addEventListener('click', () =>
        document.getElementById('lab-result-modal').classList.remove('show'));
    document.getElementById('cancel-lab-result').addEventListener('click', () =>
        document.getElementById('lab-result-modal').classList.remove('show'));
    document.getElementById('lab-request-form').addEventListener('submit', handleLabRequestSubmit);
    document.getElementById('lab-result-form').addEventListener('submit', handleLabResultSubmit);
    document.getElementById('refresh-lab-btn').addEventListener('click', () => {
        loadLabRequests();
        loadLabStats();
    });
    document.getElementById('lab-search').addEventListener('input', debounce(loadLabRequests, 300));
    document.getElementById('lab-filter-status').addEventListener('change', loadLabRequests);
}

async function handleLabRequestSubmit(e) {
    e.preventDefault();
    const data = {
        visit_id: document.getElementById('lab-visit').value,
        test_type: document.getElementById('lab-test-type').value.trim(),
        test_description: document.getElementById('lab-test-description').value.trim(),
        urgency: document.getElementById('lab-urgency').value
    };
    if (!data.visit_id || !data.test_type) {
        showAlert('Visit and test type are required', 'error');
        return;
    }
    try {
        const response = await fetch('/hms/backend/api/lab.php?action=request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            showAlert('Lab request submitted', 'success');
            document.getElementById('lab-request-modal').classList.remove('show');
            await Promise.all([loadLabRequests(), loadLabStats()]);
        } else {
            showAlert(result.error || 'Request failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}

async function handleLabResultSubmit(e) {
    e.preventDefault();
    const requestId = document.getElementById('lab-result-request-id').value;
    const data = {
        lab_request_id: requestId,
        results: document.getElementById('lab-results').value.trim(),
        normal_range: document.getElementById('lab-normal-range').value.trim(),
        interpretation: document.getElementById('lab-interpretation').value.trim(),
        status: document.getElementById('lab-result-status').value
    };
    if (!data.results) {
        showAlert('Results are required', 'error');
        return;
    }
    try {
        const response = await fetch('/hms/backend/api/lab.php?action=result', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            showAlert('Lab result saved', 'success');
            document.getElementById('lab-result-modal').classList.remove('show');
            await Promise.all([loadLabRequests(), loadLabStats()]);
        } else {
            showAlert(result.error || 'Save failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}

function debounce(fn, wait) {
    let t;
    return function (...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
    };
}
</script>