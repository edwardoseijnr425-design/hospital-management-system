<div class="card">
    <div class="card-header">
        <div>
            <h2><i class="fa-solid fa-chart-line" style="color:#0072BC;margin-right:8px;"></i>System Activities Report</h2>
            <p style="margin:3px 0 0;font-size:12px;color:#64748B;">Run and search the system activities trail — filter by date range, action, table, user or keyword.</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <button class="btn btn-sm" id="run-report-btn" style="background-color:#0F2D59;color:#fff;font-weight:700;"><i class="fa-solid fa-play"></i> Run Report</button>
            <button class="btn btn-sm" id="export-audit-csv-btn" style="background-color:#80C342;color:#fff;font-weight:700;"><i class="fa-solid fa-file-csv"></i> Export CSV</button>
            <button class="btn btn-sm" id="print-audit-btn" style="background-color:#0072BC;color:#fff;font-weight:700;"><i class="fa-solid fa-print"></i> Print</button>
            <button class="btn btn-secondary btn-sm" id="refresh-audit-btn"><i class="fa-solid fa-rotate-right"></i> Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <!-- Report filter bar -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(165px,1fr));gap:10px 12px;border:1px solid #E2E8F0;border-radius:8px;padding:14px;background:#F8FAFC;margin-bottom:12px;">
            <div>
                <label style="display:block;font-size:11px;font-weight:800;color:#0F2D59;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">From Date</label>
                <input type="date" id="audit-date-from" style="width:100%;">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:800;color:#0F2D59;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">To Date</label>
                <input type="date" id="audit-date-to" style="width:100%;">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:800;color:#0F2D59;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">Action</label>
                <select id="audit-action" style="width:100%;">
                    <option value="">All Actions</option>
                    <option value="LOGIN">LOGIN</option>
                    <option value="LOGOUT">LOGOUT</option>
                    <option value="CREATE">CREATE</option>
                    <option value="UPDATE">UPDATE</option>
                    <option value="DELETE">DELETE</option>
                    <option value="VIEW">VIEW</option>
                    <option value="PRINT">PRINT</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:800;color:#0F2D59;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">Table</label>
                <select id="audit-table" style="width:100%;">
                    <option value="">All Tables</option>
                    <option value="users">Users</option>
                    <option value="patient_registrations">Patients</option>
                    <option value="patient_visits">Visits</option>
                    <option value="wards">Wards</option>
                    <option value="rooms">Rooms</option>
                    <option value="beds">Beds</option>
                    <option value="admissions">Admissions</option>
                    <option value="sponsors">Sponsors</option>
                    <option value="service_prices">Prices</option>
                    <option value="consultations">Consultations</option>
                    <option value="lab_results">Lab Results</option>
                    <option value="radiology_results">Radiology Results</option>
                    <option value="invoices">Invoices</option>
                    <option value="inventory_requisitions">Inventory Requisitions</option>
                    <option value="appointments">Appointments</option>
                    <option value="messages">Messages</option>
                    <option value="departments">Departments</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:800;color:#0F2D59;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">User</label>
                <select id="audit-user" style="width:100%;">
                    <option value="">All Users</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:800;color:#0F2D59;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">Keyword</label>
                <input type="text" id="audit-q" placeholder="Action, table, user, IP..." style="width:100%;box-sizing:border-box;">
            </div>
        </div>

        <!-- Report summary -->
        <div id="audit-summary" style="display:flex;justify-content:space-between;align-items:center;background:#EAF4FC;border:1px solid #BBDDF3;border-radius:8px;padding:10px 14px;margin-bottom:12px;flex-wrap:wrap;gap:6px;">
            <div style="font-size:13px;font-weight:800;color:#0F2D59;"><span id="audit-total-label">0</span> SYSTEM ACTIVITIES FOUND</div>
            <div style="font-size:12px;color:#64748B;" id="audit-range-label">Loading...</div>
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
                        <th>IP Address</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody id="audit-table">
                    <tr>
                        <td colspan="7" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Activity Detail Modal -->
<div class="modal" id="audit-detail-modal">
    <div class="modal-content" style="max-width:640px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-magnifying-glass" style="color:#0072BC;margin-right:6px;"></i>Activity Detail</h3>
            <button class="modal-close" id="close-audit-detail">&times;</button>
        </div>
        <div class="modal-body">
            <div id="audit-detail-meta" style="display:grid;grid-template-columns:1fr 1fr;gap:8px 14px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px 14px;font-size:12px;color:#334155;"></div>
            <div style="margin-top:14px;">
                <div style="font-size:11px;font-weight:800;color:#0F2D59;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Old Values</div>
                <pre id="audit-old-values" style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:6px;padding:10px;font-size:11px;max-height:180px;overflow:auto;white-space:pre-wrap;word-break:break-word;margin:0;color:#334155;">-</pre>
            </div>
            <div style="margin-top:14px;">
                <div style="font-size:11px;font-weight:800;color:#0F2D59;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">New Values</div>
                <pre id="audit-new-values" style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:6px;padding:10px;font-size:11px;max-height:180px;overflow:auto;white-space:pre-wrap;word-break:break-word;margin:0;color:#334155;">-</pre>
            </div>
        </div>
    </div>
</div>

<script>
let auditData = [];
let auditTotal = 0;

async function initSystemActivities() {
    setupEventListeners();
    await loadAudit();
}

function getAuditFilter() {
    return {
        user_id: document.getElementById('audit-user').value,
        action: document.getElementById('audit-action').value,
        table_name: document.getElementById('audit-table').value,
        date_from: document.getElementById('audit-date-from').value,
        date_to: document.getElementById('audit-date-to').value,
        q: document.getElementById('audit-q').value.trim()
    };
}

async function loadAudit() {
    const tbody = document.getElementById('audit-table');
    if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Running report...</td></tr>';
    try {
        const f = getAuditFilter();
        const params = new URLSearchParams({ action: 'list', per_page: '500' });
        if (f.user_id) params.set('user_id', f.user_id);
        if (f.action) params.set('filter_action', f.action);
        if (f.table_name) params.set('table_name', f.table_name);
        if (f.date_from) params.set('date_from', f.date_from);
        if (f.date_to) params.set('date_to', f.date_to + ' 23:59:59');
        if (f.q) params.set('q', f.q);

        const response = await fetch('/hms/backend/api/audit.php?' + params.toString());
        const data = await response.json();

        if (data.success) {
            auditData = data.audit_logs || [];
            auditTotal = data.total ?? auditData.length;
            populateUserFilter();
            renderAuditReport();
        } else {
            throw new Error(data.error || 'Report failed');
        }
    } catch (error) {
        console.error('Audit report error:', error);
        if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Failed to run report</td></tr>';
        document.getElementById('audit-total-label').textContent = '0';
        document.getElementById('audit-range-label').textContent = 'Error loading report';
    }
}

function populateUserFilter() {
    const sel = document.getElementById('audit-user');
    if (!sel) return;
    const kept = sel.value;
    const users = new Map();
    auditData.forEach(log => {
        if (log.user_id && !users.has(log.user_id)) {
            users.set(log.user_id, log.full_name || ('User #' + log.user_id));
        }
    });
    const opts = ['<option value="">All Users</option>'];
    [...users.entries()].sort((a, b) => a[1].localeCompare(b[1])).forEach(([id, name]) => {
        opts.push(`<option value="${id}">${escHtml(name)}</option>`);
    });
    sel.innerHTML = opts.join('');
    sel.value = kept;
}

function renderAuditReport() {
    const tbody = document.getElementById('audit-table');
    if (!tbody) return;

    const f = getAuditFilter();
    const parts = [];
    if (f.date_from) parts.push('From ' + fmtDate(f.date_from));
    if (f.date_to) parts.push('To ' + fmtDate(f.date_to));
    if (f.action) parts.push('Action: ' + f.action);
    if (f.table_name) parts.push('Table: ' + f.table_name);
    if (f.user_id) {
        const opt = document.querySelector('#audit-user option[value="' + f.user_id + '"]');
        parts.push('User: ' + (opt ? opt.textContent : 'User #' + f.user_id));
    }
    if (f.q) parts.push('Keyword: "' + f.q + '"');
    document.getElementById('audit-total-label').textContent = auditTotal;
    document.getElementById('audit-range-label').textContent = parts.length ? parts.join(' · ') : 'All dates · No filters';

    if (!auditData.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No system activities match the report filters</td></tr>';
        return;
    }

    tbody.innerHTML = auditData.map(log => {
        const dt = fmtDateTime(log.created_at);
        const badgeClass = (log.action === 'CREATE' || log.action === 'LOGIN') ? 'badge-success'
            : (log.action === 'UPDATE') ? 'badge-info'
            : (log.action === 'DELETE') ? 'badge-danger' : 'badge-secondary';
        return `
        <tr>
            <td>${dt}</td>
            <td>${escHtml(log.full_name || ('User #' + (log.user_id || '?')))}</td>
            <td><span class="badge ${badgeClass}">${escHtml(log.action)}</span></td>
            <td>${escHtml(log.table_name)}</td>
            <td>${log.record_id ?? '-'}</td>
            <td>${escHtml(log.ip_address || '-')}</td>
            <td><button class="btn btn-secondary btn-sm audit-detail-btn" data-id="${log.id}">View</button></td>
        </tr>`;
    }).join('');

    tbody.querySelectorAll('.audit-detail-btn').forEach(btn => {
        btn.addEventListener('click', () => openAuditDetail(parseInt(btn.dataset.id, 10)));
    });
}

function openAuditDetail(id) {
    const log = auditData.find(l => l.id === id);
    if (!log) return;
    const dt = fmtDateTime(log.created_at);
    document.getElementById('audit-detail-meta').innerHTML = `
        <div><strong style="color:#0F2D59;">User:</strong> ${escHtml(log.full_name || ('User #' + (log.user_id || '?')))}</div>
        <div><strong style="color:#0F2D59;">Action:</strong> ${escHtml(log.action)}</div>
        <div><strong style="color:#0F2D59;">Table:</strong> ${escHtml(log.table_name)}</div>
        <div><strong style="color:#0F2D59;">Record ID:</strong> ${log.record_id ?? '-'}</div>
        <div><strong style="color:#0F2D59;">Date:</strong> ${dt}</div>
        <div><strong style="color:#0F2D59;">IP Address:</strong> ${escHtml(log.ip_address || '-')}</div>`;
    document.getElementById('audit-old-values').textContent = prettyJson(log.old_values);
    document.getElementById('audit-new-values').textContent = prettyJson(log.new_values);
    document.getElementById('audit-detail-modal').classList.add('show');
}

function prettyJson(value) {
    if (value === null || value === undefined || value === '') return '-';
    if (typeof value === 'string') {
        try {
            const parsed = JSON.parse(value);
            if (parsed && typeof parsed === 'object') return JSON.stringify(parsed, null, 2);
            return value;
        } catch (e) {
            return value;
        }
    }
    return JSON.stringify(value, null, 2);
}

function exportAuditCSV() {
    if (!auditData.length) { showAlert('Nothing to export', 'error'); return; }
    const c = v => '"' + String(v === null || v === undefined ? '' : v).replace(/"/g, '""') + '"';
    const rows = [['Date/Time', 'User', 'Action', 'Table', 'Record ID', 'IP Address']];
    auditData.forEach(log => {
        rows.push([
            fmtDateTime(log.created_at),
            log.full_name || ('User #' + (log.user_id || '')),
            log.action, log.table_name, log.record_id ?? '', log.ip_address || ''
        ]);
    });
    const csv = rows.map(r => r.map(c).join(',')).join('\r\n');
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'system-activities-report.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => URL.revokeObjectURL(a.href), 1000);
}

function printAuditReport() {
    const w = window.open('', '_blank', 'width=900,height=700');
    if (!w) { showAlert('Pop-up blocked — allow pop-ups to print', 'error'); return; }
    const esc = s => String(s === null || s === undefined ? '' : s).replace(/</g, '&lt;');
    const rowsHtml = auditData.map(log => {
        return `
        <tr>
            <td>${esc(fmtDateTime(log.created_at))}</td>
            <td>${esc(log.full_name || ('User #' + (log.user_id || '')))}</td>
            <td>${esc(log.action)}</td>
            <td>${esc(log.table_name)}</td>
            <td>${log.record_id ?? ''}</td>
            <td>${esc(log.ip_address || '')}</td>
        </tr>`;
    }).join('');
    w.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>System Activities Report</title>
<style>
  body{font-family:'Segoe UI',Arial,sans-serif;color:#0F2D59;padding:24px;}
  h2{margin:0 0 4px;font-size:20px;color:#0F2D59;}
  .sub{color:#64748B;font-size:12px;margin-bottom:16px;}
  .total{background:#EAF4FC;border:1px solid #BBDDF3;border-radius:6px;padding:10px 14px;font-size:15px;font-weight:700;margin-bottom:14px;}
  table{width:100%;border-collapse:collapse;font-size:12px;}
  th{background:#0F2D59;color:#fff;padding:8px;text-align:left;font-size:11px;text-transform:uppercase;}
  td{padding:7px 8px;border-bottom:1px solid #E2E8F0;}
  tr:nth-child(even){background:#F8FAFC;}
</style></head><body>
<h2>System Activities Report</h2>
<div class="sub">${esc(document.getElementById('audit-range-label').textContent)} · Printed ${fmtDateTime(new Date())}</div>
<div class="total">${auditTotal} SYSTEM ACTIVITIES FOUND</div>
<table><thead><tr><th>Date/Time</th><th>User</th><th>Action</th><th>Table</th><th>Record ID</th><th>IP Address</th></tr></thead>
<tbody>${rowsHtml}</tbody></table>
</body></html>`);
    w.document.close();
    w.focus();
    setTimeout(() => { w.print(); }, 350);
}

function setupEventListeners() {
    document.getElementById('run-report-btn').addEventListener('click', loadAudit);
    document.getElementById('refresh-audit-btn').addEventListener('click', loadAudit);
    document.getElementById('export-audit-csv-btn').addEventListener('click', exportAuditCSV);
    document.getElementById('print-audit-btn').addEventListener('click', printAuditReport);
    document.getElementById('close-audit-detail').addEventListener('click', () => {
        document.getElementById('audit-detail-modal').classList.remove('show');
    });
    document.getElementById('audit-q').addEventListener('keydown', e => {
        if (e.key === 'Enter') loadAudit();
    });
}
</script>