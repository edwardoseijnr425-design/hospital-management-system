<div class="card">
    <div class="card-header">
        <h2>Audit Trail</h2>
        <button class="btn btn-secondary btn-sm" id="refresh-audit-btn">Refresh</button>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <input type="text" id="filter-audit-action" placeholder="Filter by action (e.g. LOGIN, CREATE, UPDATE)">
            </div>
            <div class="form-group">
                <select id="filter-audit-table">
                    <option value="">All Tables</option>
                    <option value="users">Users</option>
                    <option value="patient_registrations">Patients</option>
                    <option value="patient_visits">Visits</option>
                    <option value="wards">Wards</option>
                    <option value="beds">Beds</option>
                    <option value="sponsors">Sponsors</option>
                    <option value="service_prices">Prices</option>
                    <option value="consultations">Consultations</option>
                    <option value="lab_results">Lab Results</option>
                    <option value="radiology_results">Radiology Results</option>
                </select>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Record ID</th>
                    </tr>
                </thead>
                <tbody id="audit-table">
                    <tr>
                        <td colspan="5" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let auditData = [];

async function initAudit() {
    setupEventListeners();
    await loadAudit();
}

async function loadAudit() {
    try {
        const response = await fetch('/hms/backend/api/audit.php?action=list&per_page=100');
        const data = await response.json();
        
        if (data.success) {
            auditData = data.audit_logs;
            renderAuditTable('', '');
        }
    } catch (error) {
        console.error('Audit load error:', error);
        const tbody = document.getElementById('audit-table');
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Failed to load audit trail</td></tr>';
    }
}

function renderAuditTable(actionFilter, tableFilter) {
    const tbody = document.getElementById('audit-table');
    if (!tbody) return;
    
    const a = (actionFilter || '').toLowerCase();
    const t = tableFilter || '';
    const rows = auditData.filter(log =>
        (!a || (log.action && log.action.toLowerCase().includes(a))) &&
        (!t || log.table_name === t)
    );
    
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No audit records found</td></tr>';
        return;
    }
    
    tbody.innerHTML = rows.map(log => {
        const dt = fmtDateTime(log.created_at);
        const badgeClass = (log.action === 'CREATE' || log.action === 'LOGIN') ? 'badge-success'
            : (log.action === 'UPDATE') ? 'badge-info'
            : (log.action === 'DELETE') ? 'badge-danger' : 'badge-secondary';
        return `
        <tr>
            <td>${dt}</td>
            <td>${log.full_name || 'User #' + (log.user_id || '?')}</td>
            <td><span class="badge ${badgeClass}">${log.action}</span></td>
            <td>${log.table_name}</td>
            <td>${log.record_id ?? '-'}</td>
        </tr>
    `}).join('');
}

function setupEventListeners() {
    const actionBox = document.getElementById('filter-audit-action');
    if (actionBox) {
        actionBox.addEventListener('input', function() {
            renderAuditTable(this.value, document.getElementById('filter-audit-table').value);
        });
    }
    const tableSel = document.getElementById('filter-audit-table');
    if (tableSel) {
        tableSel.addEventListener('change', function() {
            renderAuditTable(document.getElementById('filter-audit-action').value, this.value);
        });
    }
    const refreshBtn = document.getElementById('refresh-audit-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', loadAudit);
    }
}
</script>