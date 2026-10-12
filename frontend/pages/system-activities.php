<div id="sysact-page">
<style>
/* System Activities — scoped styles (slate / rounded look, matches the module mockup). */
#sysact-page{background:#f4f7f9;color:#334155;font-size:12px;box-sizing:border-box;padding:14px;border-radius:12px;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
#sysact-page *,#sysact-page *::before,#sysact-page *::after{box-sizing:border-box;}
#sysact-page .sa-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 2px rgba(15,23,42,.05);}
#sysact-page .sa-toolbar{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;padding:16px;}
#sysact-page .sa-title{margin:0;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#1b659d;display:flex;align-items:center;gap:8px;}
#sysact-page .sa-sub{margin:3px 0 0;font-size:11px;color:#64748b;}
#sysact-page .sa-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
#sysact-page .sa-btn{border:1px solid #e2e8f0;background:#f1f5f9;color:#334155;border-radius:8px;padding:8px 13px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;transition:background .15s,color .15s;}
#sysact-page .sa-btn:hover{background:#e2e8f0;}
#sysact-page .sa-btn i{font-size:12px;}
#sysact-page .sa-btn.is-on,
#sysact-page .sa-btn.primary{background:#1b659d;color:#fff;border-color:#1b659d;}
#sysact-page .sa-btn.is-on:hover,
#sysact-page .sa-btn.primary:hover{background:#155380;border-color:#155380;}
#sysact-page .sa-btn.green{background:#059669;color:#fff;border-color:#059669;}
#sysact-page .sa-btn.green:hover{background:#047857;border-color:#047857;}
#sysact-page .sa-filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(165px,1fr));gap:10px 12px;border:1px solid #e2e8f0;border-radius:12px;padding:14px;background:#fff;margin-top:12px;box-shadow:0 1px 2px rgba(15,23,42,.05);}
#sysact-page .sa-filters label{display:block;font-size:10px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;}
#sysact-page .sa-filters input,
#sysact-page .sa-filters select{width:100%;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px;background:#f8fafc;color:#334155;font-family:inherit;}
#sysact-page .sa-filters input:focus,
#sysact-page .sa-filters select:focus{outline:none;border-color:#1b659d;background:#fff;box-shadow:0 0 0 2px rgba(27,101,157,.18);}
#sysact-page .sa-summary{display:flex;justify-content:space-between;align-items:center;background:#e8f2fb;border:1px solid #cfe3f5;border-radius:12px;padding:10px 14px;margin-top:12px;flex-wrap:wrap;gap:6px;}
#sysact-page .sa-summary .total{font-size:13px;font-weight:800;color:#1b659d;display:flex;align-items:center;gap:7px;}
#sysact-page .sa-summary .range{font-size:12px;color:#64748b;}
#sysact-page .sa-table-wrap{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 2px rgba(15,23,42,.05);overflow:hidden;margin-top:12px;}
#sysact-page .sa-table-wrap .scroller{overflow-x:auto;}
#sysact-page table{width:100%;border-collapse:collapse;font-size:12px;}
#sysact-page thead th{background:#f1f5f9;color:#475569;font-weight:700;text-align:left;text-transform:uppercase;font-size:10px;letter-spacing:.5px;padding:12px;border-bottom:1px solid #e2e8f0;white-space:nowrap;}
#sysact-page tbody td{padding:12px;border-bottom:1px solid #f1f5f9;color:#334155;vertical-align:middle;}
#sysact-page tbody tr:last-child td{border-bottom:none;}
#sysact-page tbody tr:hover{background:#f8fafc;}
#sysact-page .sa-badge{display:inline-block;padding:3px 9px;border-radius:6px;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:.4px;}
#sysact-page .sa-badge.login{color:#047857;background:#d1fae5;}
#sysact-page .sa-badge.logout{color:#475569;background:#e2e8f0;}
#sysact-page .sa-badge.create{color:#1d4ed8;background:#dbeafe;}
#sysact-page .sa-badge.update{color:#b45309;background:#fef3c7;}
#sysact-page .sa-badge.delete{color:#be123c;background:#ffe4e6;}
#sysact-page .sa-badge.view{color:#475569;background:#f1f5f9;}
#sysact-page .sa-badge.print{color:#4338ca;background:#e0e7ff;}
#sysact-page .sa-badge.default{color:#475569;background:#f1f5f9;}
#sysact-page .sa-view-btn{background:#f1f5f9;border:1px solid #e2e8f0;color:#334155;border-radius:7px;padding:5px 12px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;cursor:pointer;font-family:inherit;}
#sysact-page .sa-view-btn:hover{background:#e2e8f0;}
#sysact-page .sa-empty{text-align:center;color:#94a3b8;padding:28px 16px;font-weight:500;}
#sysact-page .modal{display:none;position:fixed;inset:0;z-index:2000;background:rgba(15,23,42,.55);align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto;}
#sysact-page .modal.show{display:flex;}
#sysact-page .modal-content{background:#fff;border-radius:12px;width:min(640px,100%);box-shadow:0 12px 34px rgba(15,23,42,.25);overflow:hidden;margin:0;}
#sysact-page .modal-header{background:#1b659d;color:#fff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;gap:10px;}
#sysact-page .modal-header h3{margin:0;font-size:13px;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:8px;}
#sysact-page .modal-close{background:none;border:none;color:#fff;font-size:22px;line-height:1;cursor:pointer;padding:0 4px;font-family:inherit;}
#sysact-page .modal-close:hover{color:#fde68a;}
#sysact-page .modal-body{padding:18px 20px;background:#f8fafc;}
#sysact-page .sa-detail-meta{display:grid;grid-template-columns:1fr 1fr;gap:8px 14px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px;font-size:12px;color:#334155;}
#sysact-page .sa-detail-label{font-size:10px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.5px;margin:14px 0 6px;}
#sysact-page pre{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px;font-size:11px;max-height:180px;overflow:auto;white-space:pre-wrap;word-break:break-word;margin:0;color:#334155;}
@media (max-width:640px){ #sysact-page .sa-detail-meta{grid-template-columns:1fr;} }
</style>

<!-- Top filter toolbar -->
<div class="sa-card">
    <div class="sa-toolbar">
        <div>
            <h3 class="sa-title"><i class="fa-solid fa-calendar-day"></i> <span id="audit-heading">System Activities — Today Only</span></h3>
            <p class="sa-sub">Showing logs filtered by date and time in descending order.</p>
        </div>
        <div class="sa-actions">
            <button type="button" id="audit-today-toggle" class="sa-btn is-on" title="Toggle between today's activities and all records">
                <i class="fa-solid fa-filter"></i> <span id="audit-today-label">Showing: Today Only</span>
            </button>
            <button type="button" class="sa-btn primary" id="run-report-btn"><i class="fa-solid fa-play"></i> Run Report</button>
            <button type="button" class="sa-btn green" id="export-audit-csv-btn"><i class="fa-solid fa-file-csv"></i> Export CSV</button>
            <button type="button" class="sa-btn" id="print-audit-btn"><i class="fa-solid fa-print"></i> Print</button>
            <button type="button" class="sa-btn" id="refresh-audit-btn"><i class="fa-solid fa-rotate-right"></i> Refresh</button>
        </div>
    </div>
</div>

<!-- Report filter bar -->
<div class="sa-filters">
    <div>
        <label for="audit-date-from">From Date</label>
        <input type="date" id="audit-date-from" data-dp-no-time>
    </div>
    <div>
        <label for="audit-date-to">To Date</label>
        <input type="date" id="audit-date-to" data-dp-no-time>
    </div>
    <div>
        <label for="audit-action">Action</label>
        <select id="audit-action">
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
        <label for="audit-table-filter">Table</label>
        <select id="audit-table-filter">
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
        <label for="audit-user">User</label>
        <select id="audit-user">
            <option value="">All Users</option>
        </select>
    </div>
    <div>
        <label for="audit-q">Keyword</label>
        <input type="text" id="audit-q" placeholder="Action, table, user, IP...">
    </div>
</div>

<!-- Report summary -->
<div id="audit-summary" class="sa-summary">
    <div class="total"><i class="fa-solid fa-list-check"></i> <span id="audit-total-label">0</span> SYSTEM ACTIVITIES FOUND</div>
    <div class="range" id="audit-range-label">Loading...</div>
</div>

<!-- Data table -->
<div class="sa-table-wrap">
    <div class="scroller">
        <table>
            <thead>
                <tr>
                    <th>Date / Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Table</th>
                    <th>Record ID</th>
                    <th>IP Address</th>
                    <th style="text-align:center;">Details</th>
                </tr>
            </thead>
            <tbody id="audit-table">
                <tr><td colspan="7" class="sa-empty">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Activity Detail Modal -->
<div class="modal" id="audit-detail-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-magnifying-glass"></i> Activity Detail</h3>
            <button class="modal-close" id="close-audit-detail">&times;</button>
        </div>
        <div class="modal-body">
            <div id="audit-detail-meta" class="sa-detail-meta"></div>
            <div class="sa-detail-label">Old Values</div>
            <pre id="audit-old-values">-</pre>
            <div class="sa-detail-label">New Values</div>
            <pre id="audit-new-values">-</pre>
        </div>
    </div>
</div>
</div>

<script>
let auditData = [];
let auditTotal = 0;
let auditTodayOnly = true;
let auditSyncing = false;

async function initSystemActivities() {
    syncAuditTodayInputs();
    updateAuditToggleUI();
    setupEventListeners();
    await loadAudit();
}

/* Today Only / All Records toggle ------------------------------------------ */
function auditTodayISO() {
    const d = new Date();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return d.getFullYear() + '-' + m + '-' + day;
}

function auditTodayLabelText() {
    return new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

/* Set both date fields and refresh any custom date-picker display.
   The picker only re-reads the input on a change event, so we dispatch one;
   auditSyncing stops the change handler from re-flipping the toggle. */
function setAuditDates(fromVal, toVal) {
    auditSyncing = true;
    const from = document.getElementById('audit-date-from');
    const to = document.getElementById('audit-date-to');
    if (from) { from.value = fromVal; from.dispatchEvent(new Event('change', { bubbles: true })); }
    if (to) { to.value = toVal; to.dispatchEvent(new Event('change', { bubbles: true })); }
    auditSyncing = false;
}

function syncAuditTodayInputs() {
    if (auditTodayOnly) {
        const t = auditTodayISO();
        setAuditDates(t, t);
    }
}

function onAuditDatesChanged() {
    if (auditSyncing) return;
    const t = auditTodayISO();
    const f = document.getElementById('audit-date-from').value;
    const to = document.getElementById('audit-date-to').value;
    const isToday = (f === t && to === t);
    if (auditTodayOnly !== isToday) {
        auditTodayOnly = isToday;
        updateAuditToggleUI();
    }
}

function updateAuditToggleUI() {
    const btn = document.getElementById('audit-today-toggle');
    const lbl = document.getElementById('audit-today-label');
    const head = document.getElementById('audit-heading');
    if (btn) btn.classList.toggle('is-on', auditTodayOnly);
    if (lbl) lbl.textContent = auditTodayOnly ? 'Showing: Today Only' : 'Showing: All Records';
    if (head) head.textContent = auditTodayOnly
        ? ('System Activities — Today Only (' + auditTodayLabelText() + ')')
        : 'System Activities — All Records';
}

function toggleAuditToday() {
    auditTodayOnly = !auditTodayOnly;
    if (auditTodayOnly) {
        const t = auditTodayISO();
        setAuditDates(t, t);
    } else {
        // Switching to "All Records" clears the quick date window.
        setAuditDates('', '');
    }
    updateAuditToggleUI();
    loadAudit();
}

function getAuditFilter() {
    return {
        user_id: document.getElementById('audit-user').value,
        action: document.getElementById('audit-action').value,
        table_name: document.getElementById('audit-table-filter').value,
        date_from: document.getElementById('audit-date-from').value,
        date_to: document.getElementById('audit-date-to').value,
        q: document.getElementById('audit-q').value.trim()
    };
}

async function loadAudit() {
    const tbody = document.getElementById('audit-table');
    if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="sa-empty">Running report...</td></tr>';
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
        if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="sa-empty">Failed to run report</td></tr>';
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

function auditBadgeClass(action) {
    switch (String(action || '').toUpperCase()) {
        case 'CREATE': return 'sa-badge create';
        case 'LOGIN': return 'sa-badge login';
        case 'LOGOUT': return 'sa-badge logout';
        case 'UPDATE': return 'sa-badge update';
        case 'DELETE': return 'sa-badge delete';
        case 'VIEW': return 'sa-badge view';
        case 'PRINT': return 'sa-badge print';
        default: return 'sa-badge default';
    }
}

function renderAuditReport() {
    const tbody = document.getElementById('audit-table');
    if (!tbody) return;

    const f = getAuditFilter();
    const parts = [];
    if (auditTodayOnly) parts.push('Today only (' + auditTodayLabelText() + ')');
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
        tbody.innerHTML = '<tr><td colspan="7" class="sa-empty">No system activities match the report filters</td></tr>';
        return;
    }

    tbody.innerHTML = auditData.map(log => {
        const dt = fmtDateTime(log.created_at);
        return `
        <tr>
            <td>${dt}</td>
            <td>${escHtml(log.full_name || ('User #' + (log.user_id || '?')))}</td>
            <td><span class="${auditBadgeClass(log.action)}">${escHtml(log.action)}</span></td>
            <td>${escHtml(log.table_name)}</td>
            <td>${log.record_id ?? '-'}</td>
            <td>${escHtml(log.ip_address || '-')}</td>
            <td style="text-align:center;"><button class="sa-view-btn audit-detail-btn" data-id="${log.id}">View</button></td>
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
        <div><strong style="color:#1b659d;">User:</strong> ${escHtml(log.full_name || ('User #' + (log.user_id || '?')))}</div>
        <div><strong style="color:#1b659d;">Action:</strong> ${escHtml(log.action)}</div>
        <div><strong style="color:#1b659d;">Table:</strong> ${escHtml(log.table_name)}</div>
        <div><strong style="color:#1b659d;">Record ID:</strong> ${log.record_id ?? '-'}</div>
        <div><strong style="color:#1b659d;">Date:</strong> ${dt}</div>
        <div><strong style="color:#1b659d;">IP Address:</strong> ${escHtml(log.ip_address || '-')}</div>`;
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
    document.getElementById('audit-today-toggle').addEventListener('click', toggleAuditToday);
    document.getElementById('run-report-btn').addEventListener('click', loadAudit);
    document.getElementById('refresh-audit-btn').addEventListener('click', loadAudit);
    document.getElementById('export-audit-csv-btn').addEventListener('click', exportAuditCSV);
    document.getElementById('print-audit-btn').addEventListener('click', printAuditReport);
    document.getElementById('audit-date-from').addEventListener('change', onAuditDatesChanged);
    document.getElementById('audit-date-to').addEventListener('change', onAuditDatesChanged);
    document.getElementById('close-audit-detail').addEventListener('click', () => {
        document.getElementById('audit-detail-modal').classList.remove('show');
    });
    document.getElementById('audit-q').addEventListener('keydown', e => {
        if (e.key === 'Enter') loadAudit();
    });
}
</script>
