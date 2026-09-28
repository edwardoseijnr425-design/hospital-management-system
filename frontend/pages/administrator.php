<style>
.admin-actions-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:10px;}
.admin-action-tile{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:16px 10px;border:1px solid #E2E8F0;border-radius:8px;background:#fff;color:#0F2D59;font-family:inherit;font-size:11px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;cursor:pointer;transition:box-shadow .15s,border-color .15s,transform .1s;}
.admin-action-tile:hover{border-color:#0072BC;box-shadow:0 3px 10px rgba(15,45,89,.14);transform:translateY(-1px);}
.admin-action-tile i{font-size:22px;color:#0072BC;}
</style>

<!-- ============ ADMIN QUICK ACTIONS ============ -->
<div class="card">
    <div class="card-header">
        <h2>Admin Quick Actions</h2>
    </div>
    <div class="card-body">
        <div class="admin-actions-grid">
            <button type="button" class="admin-action-tile" onclick="adminAction('users')"><i class="fa-solid fa-users"></i><span>User Management</span></button>
            <button type="button" class="admin-action-tile" onclick="adminAction('service')"><i class="fa-solid fa-briefcase-medical"></i><span>Add Services &amp; Catalog</span></button>
            <button type="button" class="admin-action-tile" onclick="adminAction('price')"><i class="fa-solid fa-money-bill-wave"></i><span>Price Adjustment</span></button>
            <button type="button" class="admin-action-tile" onclick="adminAction('stock')"><i class="fa-solid fa-boxes-stacked"></i><span>Add Stock Item</span></button>
            <button type="button" class="admin-action-tile" onclick="adminAction('dept')"><i class="fa-solid fa-building-columns"></i><span>Create New Department</span></button>
            <button type="button" class="admin-action-tile" onclick="adminAction('ward')"><i class="fa-solid fa-bed-pulse"></i><span>Create New Ward</span></button>
            <button type="button" class="admin-action-tile" onclick="adminAction('bed')"><i class="fa-solid fa-couch"></i><span>Add Bed / Room</span></button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Administrator Console</h2>
        <button class="btn btn-secondary btn-sm" id="admin-refresh-btn">Refresh</button>
    </div>
    <div class="card-body">
        <div class="stats-grid" style="margin-bottom:18px;">
            <div class="stat-card">
                <div class="stat-icon primary">&#128101;</div>
                <div class="stat-info"><h3 id="stat-users">0</h3><p>Users</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">&#127973;</div>
                <div class="stat-info"><h3 id="stat-patients">0</h3><p>Patients</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">&#128278;</div>
                <div class="stat-info"><h3 id="stat-visits">0</h3><p>Visits (Today: <span id="stat-today-visits">0</span>)</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon danger">&#128203;</div>
                <div class="stat-info"><h3 id="stat-departments">0</h3><p>Departments</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon primary">&#128176;</div>
                <div class="stat-info"><h3 id="stat-invoices">0</h3><p>Invoices</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">&#128200;</div>
                <div class="stat-info"><h3 id="stat-wards">0</h3><p>Wards / <span id="stat-beds">0</span> Beds</p></div>
            </div>
        </div>

        <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:20px;">
            <div style="flex:1;min-width:260px;">
                <h4 style="color:#0D47A1;margin:0 0 10px;">Users by Role</h4>
                <div id="users-by-role" class="table-container" style="max-height:280px;"></div>
            </div>
            <div style="flex:1.4;min-width:320px;">
                <h4 style="color:#0D47A1;margin:0 0 10px;">Departments</h4>
                <div id="admin-departments" class="table-container" style="max-height:280px;"></div>
            </div>
        </div>

        <h4 style="color:#0D47A1;margin:0 0 10px;">Recent Activity (Audit Trail)</h4>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="admin-activity">
                    <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ ADMIN QUICK ACTION MODAL ============ -->
<div class="modal" id="admin-action-modal">
    <div class="modal-content" style="max-width:540px;">
        <div class="modal-header">
            <h3 id="admin-action-title">Action</h3>
            <button class="modal-close" id="close-admin-action-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="admin-action-form">
                <div id="admin-action-fields"></div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="admin-action-submit" style="background-color:#0072BC;font-weight:700;">Save</button>
                    <button type="button" class="btn btn-secondary" id="cancel-admin-action">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
async function initAdministrator() {
    document.getElementById('admin-refresh-btn').addEventListener('click', loadOverview);
    document.getElementById('close-admin-action-modal').addEventListener('click', closeAdminActionModal);
    document.getElementById('cancel-admin-action').addEventListener('click', closeAdminActionModal);
    document.getElementById('admin-action-form').addEventListener('submit', handleAdminActionSubmit);
    await loadOverview();
}

function escJs(s) {
    return String(s == null ? '' : s).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

async function loadOverview() {
    try {
        const response = await fetch('/hms/backend/api/system.php?action=overview');
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load overview');

        const c = data.counts || {};
        document.getElementById('stat-users').textContent = c.users || 0;
        document.getElementById('stat-patients').textContent = c.patients || 0;
        document.getElementById('stat-visits').textContent = c.visits || 0;
        document.getElementById('stat-today-visits').textContent = c.today_visits || 0;
        document.getElementById('stat-departments').textContent = c.departments || 0;
        document.getElementById('stat-invoices').textContent = c.invoices || 0;
        document.getElementById('stat-wards').textContent = c.wards || 0;
        document.getElementById('stat-beds').textContent = c.beds || 0;

        const roleBox = document.getElementById('users-by-role');
        const roles = data.users_by_role || [];
        if (!roles.length) {
            roleBox.innerHTML = '<p style="color:#64748B;">No users found</p>';
        } else {
            roleBox.innerHTML = `<table><thead><tr><th>Role</th><th>Count</th></tr></thead><tbody>
                ${roles.map(r => `<tr><td>${escJs(r.role)}</td><td><span class="badge badge-primary">${r.c}</span></td></tr>`).join('')}
            </tbody></table>`;
        }

        const deptBox = document.getElementById('admin-departments');
        const depts = data.departments || [];
        if (!depts.length) {
            deptBox.innerHTML = '<p style="color:#64748B;">No departments found</p>';
        } else {
            deptBox.innerHTML = `<table><thead><tr><th>Department</th><th>Code</th><th>Staff</th><th>Head</th></tr></thead><tbody>
                ${depts.map(d => `<tr><td>${escJs(d.name)}</td><td>${escJs(d.code)}</td><td>${d.staff_count || 0}</td><td>${escJs(d.hod)}</td></tr>`).join('')}
            </tbody></table>`;
        }

        const actBody = document.getElementById('admin-activity');
        const acts = data.recent_activity || [];
        if (!acts.length) {
            actBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No audit activity yet</td></tr>';
        } else {
            actBody.innerHTML = acts.map(a => {
                return `<tr>
                    <td>${fmtDateTime(a.created_at)}</td>
                    <td>${escJs(a.user_name)}</td>
                    <td><span class="badge badge-secondary">${escJs(a.action)}</span></td>
                    <td>${escJs(a.table_name)}</td>
                    <td>${escJs(a.ip_address)}</td>
                </tr>`;
            }).join('');
        }
    } catch (error) {
        console.error('Overview load error:', error);
        document.getElementById('admin-activity').innerHTML = '<tr><td colspan="5" style="text-align:center;">Failed to load overview</td></tr>';
    }
}

/* ================= Admin Quick Actions (old EHMS style) ================= */
let adminActionType = '';

function aEsc(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function showAdminActionModal() {
    document.getElementById('admin-action-modal').classList.add('show');
}

function closeAdminActionModal() {
    document.getElementById('admin-action-modal').classList.remove('show');
}

function adminAction(type) {
    adminActionType = type;
    const title = document.getElementById('admin-action-title');
    const fields = document.getElementById('admin-action-fields');
    const submitBtn = document.getElementById('admin-action-submit');

    // Navigation quick actions
    if (type === 'users') { if (window.loadPage) window.loadPage('users'); return; }
    if (type === 'price') { if (window.loadPage) window.loadPage('prices'); return; }

    if (type === 'service') {
        title.textContent = 'Add Services & Catalog';
        fields.innerHTML = `
            <div class="form-group">
                <label for="aa-service-type">Type *</label>
                <select id="aa-service-type" required>
                    <option value="">Select Type</option>
                    <option value="consultation">Service</option>
                    <option value="procedure">Procedure</option>
                    <option value="drug">Drug</option>
                </select>
            </div>
            <div class="form-group">
                <label for="aa-service-item">Item *</label>
                <select id="aa-service-item" required><option value="">Select Item</option></select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="aa-service-price">Price (GHS) *</label>
                    <input type="number" step="0.01" min="0" id="aa-service-price" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="aa-service-date">Effective Date *</label>
                    <input type="date" id="aa-service-date" required>
                </div>
            </div>`;
        submitBtn.textContent = 'Save Item';
        loadServiceCatalog('consultation');
        document.getElementById('aa-service-type').addEventListener('change', function () {
            loadServiceCatalog(this.value);
        });
    } else if (type === 'stock') {
        title.textContent = 'Add Stock Item';
        fields.innerHTML = `
            <div class="form-row">
                <div class="form-group">
                    <label for="aa-stock-name">Item Name *</label>
                    <input type="text" id="aa-stock-name" required placeholder="e.g. Paracetamol 500mg">
                </div>
                <div class="form-group">
                    <label for="aa-stock-code">Item Code *</label>
                    <input type="text" id="aa-stock-code" required placeholder="e.g. PARA-500" style="text-transform:uppercase;">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="aa-stock-type">Store Type</label>
                    <select id="aa-stock-type">
                        <option value="MEDICAL">Medical Store</option>
                        <option value="GENERAL">General Store</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="aa-stock-qty">Quantity In Stock</label>
                    <input type="number" min="0" id="aa-stock-qty" value="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="aa-stock-category">Category</label>
                    <input type="text" id="aa-stock-category" placeholder="e.g. Analgesic">
                </div>
                <div class="form-group">
                    <label for="aa-stock-unit">Unit</label>
                    <input type="text" id="aa-stock-unit" placeholder="e.g. Tabs">
                </div>
            </div>`;
        submitBtn.textContent = 'Add Stock';
    } else if (type === 'dept') {
        title.textContent = 'Create New Department';
        fields.innerHTML = `
            <div class="form-group">
                <label for="aa-dept-name">Department Name *</label>
                <input type="text" id="aa-dept-name" required placeholder="e.g. Cardiology">
            </div>
            <div class="form-group">
                <label for="aa-dept-code">Department Code *</label>
                <input type="text" id="aa-dept-code" required placeholder="e.g. CARD" style="text-transform:uppercase;">
            </div>
            <div class="form-group">
                <label for="aa-dept-desc">Description</label>
                <textarea id="aa-dept-desc" rows="2" placeholder="Brief description of the department"></textarea>
            </div>
            <div class="form-group">
                <label for="aa-dept-head">Head of Department</label>
                <select id="aa-dept-head"><option value="">-- Select Head --</option></select>
            </div>`;
        submitBtn.textContent = 'Save Department';
        loadAdminUsers();
    } else if (type === 'ward') {
        title.textContent = 'Create New Ward';
        fields.innerHTML = `
            <div class="form-row">
                <div class="form-group">
                    <label for="aa-ward-code">Ward Code *</label>
                    <input type="text" id="aa-ward-code" required placeholder="e.g. WARD-FEM-01" style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label for="aa-ward-type">Ward Type *</label>
                    <select id="aa-ward-type" required>
                        <option value="">Select Type</option>
                        <option value="General">General</option>
                        <option value="Male">Male Ward</option>
                        <option value="Female">Female Ward</option>
                        <option value="Pediatric">Pediatric</option>
                        <option value="ICU">Intensive Care Unit (ICU)</option>
                        <option value="Maternity">Maternity Ward</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="aa-ward-name">Ward Name *</label>
                <input type="text" id="aa-ward-name" required placeholder="e.g. Female Medical Ward">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="aa-ward-capacity">Bed Capacity *</label>
                    <input type="number" id="aa-ward-capacity" min="1" value="10" required>
                </div>
                <div class="form-group">
                    <label for="aa-ward-floor">Floor / Location</label>
                    <input type="text" id="aa-ward-floor" placeholder="e.g. 1st Floor">
                </div>
            </div>`;
        submitBtn.textContent = 'Save Ward';
    } else if (type === 'bed') {
        title.textContent = 'Add Bed / Room';
        fields.innerHTML = `
            <div class="form-group">
                <label for="aa-bed-ward">Ward *</label>
                <select id="aa-bed-ward" required><option value="">Loading...</option></select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="aa-bed-number">Bed Number *</label>
                    <input type="text" id="aa-bed-number" required placeholder="e.g. BED-FEM-02" style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label for="aa-bed-status">Status</label>
                    <select id="aa-bed-status">
                        <option value="Available">Available</option>
                        <option value="Occupied">Occupied</option>
                        <option value="Reserved">Reserved</option>
                        <option value="Maintenance">Maintenance</option>
                    </select>
                </div>
            </div>`;
        submitBtn.textContent = 'Save Bed';
        loadAdminWards();
    }

    showAdminActionModal();
}

async function loadServiceCatalog(type) {
    const sel = document.getElementById('aa-service-item');
    if (!sel) return;
    sel.innerHTML = '<option value="">Loading...</option>';
    try {
        const response = await fetch('/hms/backend/api/services.php?type=' + encodeURIComponent(type));
        const data = await response.json();
        if (data.success) {
            sel.innerHTML = '<option value="">Select Item</option>' +
                data.services.map(s => `<option value="${s.id}">${aEsc(s.name)} (${aEsc(s.code)})</option>`).join('');
        } else {
            sel.innerHTML = '<option value="">No items found</option>';
        }
    } catch (error) {
        console.error('Service catalog load error:', error);
        sel.innerHTML = '<option value="">Failed to load</option>';
    }
}

async function loadAdminWards() {
    const sel = document.getElementById('aa-bed-ward');
    if (!sel) return;
    try {
        const response = await fetch('/hms/backend/api/wards.php');
        const data = await response.json();
        if (data.success) {
            sel.innerHTML = '<option value="">Select Ward</option>' +
                data.wards.map(w => `<option value="${w.id}">${aEsc(w.ward_code)} - ${aEsc(w.ward_name)}</option>`).join('');
        }
    } catch (error) {
        console.error('Wards load error:', error);
        sel.innerHTML = '<option value="">Failed to load wards</option>';
    }
}

async function loadAdminUsers() {
    const sel = document.getElementById('aa-dept-head');
    if (!sel) return;
    try {
        const response = await fetch('/hms/backend/api/users.php?action=list');
        const data = await response.json();
        if (data.success) {
            sel.innerHTML = '<option value="">-- Select Head --</option>' +
                data.users.map(u => `<option value="${u.id}">${aEsc(u.full_name)} (${aEsc(u.role)})</option>`).join('');
        }
    } catch (error) {
        console.error('Users load error:', error);
    }
}

async function handleAdminActionSubmit(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('admin-action-submit');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    let url = '';
    let payload = {};
    try {
        switch (adminActionType) {
            case 'service':
                url = '/hms/backend/api/prices.php?action=create';
                payload = {
                    service_type: document.getElementById('aa-service-type').value,
                    service_id: document.getElementById('aa-service-item').value,
                    price: document.getElementById('aa-service-price').value,
                    currency: 'GHS',
                    effective_date: document.getElementById('aa-service-date').value
                };
                break;
            case 'stock':
                url = '/hms/backend/api/inventory.php?action=create';
                payload = {
                    drug_name: document.getElementById('aa-stock-name').value.trim(),
                    drug_code: document.getElementById('aa-stock-code').value.trim(),
                    store_type: document.getElementById('aa-stock-type').value,
                    category: document.getElementById('aa-stock-category').value.trim(),
                    quantity_in_stock: document.getElementById('aa-stock-qty').value,
                    unit: document.getElementById('aa-stock-unit').value.trim()
                };
                break;
            case 'dept':
                url = '/hms/backend/api/users.php?action=create_department';
                payload = {
                    name: document.getElementById('aa-dept-name').value.trim(),
                    code: document.getElementById('aa-dept-code').value.trim(),
                    description: document.getElementById('aa-dept-desc').value.trim(),
                    head_of_department: document.getElementById('aa-dept-head').value || null
                };
                break;
            case 'ward':
                url = '/hms/backend/api/wards.php?action=create';
                payload = {
                    ward_code: document.getElementById('aa-ward-code').value.trim(),
                    ward_name: document.getElementById('aa-ward-name').value.trim(),
                    ward_type: document.getElementById('aa-ward-type').value,
                    capacity: document.getElementById('aa-ward-capacity').value,
                    floor_level: document.getElementById('aa-ward-floor').value.trim(),
                    status: 'Active'
                };
                break;
            case 'bed':
                url = '/hms/backend/api/wards.php?action=create_bed';
                payload = {
                    ward_id: document.getElementById('aa-bed-ward').value,
                    bed_number: document.getElementById('aa-bed-number').value.trim(),
                    status: document.getElementById('aa-bed-status').value
                };
                break;
            default:
                return;
        }

        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.success) {
            closeAdminActionModal();
            showAlert('Saved successfully', 'success');
        } else {
            showAlert(result.error || (result.errors && Object.values(result.errors)[0]) || 'Save failed', 'error');
        }
    } catch (error) {
        console.error('Admin action error:', error);
        showAlert('Network error. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}
</script>