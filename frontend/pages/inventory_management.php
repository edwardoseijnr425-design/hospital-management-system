<div class="card">
    <div class="card-header">
        <h2>Stock Overview</h2>
        <div style="display:flex;gap:10px;">
            <button class="btn btn-primary btn-sm" id="new-item-btn">Add Item</button>
            <button class="btn btn-secondary btn-sm" id="refresh-item-btn">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="stats-grid" style="margin-bottom:18px;">
            <div class="stat-card">
                <div class="stat-icon primary">&#128230;</div>
                <div class="stat-info"><h3 id="stat-items">0</h3><p>Items</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">&#128200;</div>
                <div class="stat-info"><h3 id="stat-units">0</h3><p>Units in Stock</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">&#9888;</div>
                <div class="stat-info"><h3 id="stat-low">0</h3><p>Low Stock</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon danger">&#10060;</div>
                <div class="stat-info"><h3 id="stat-out">0</h3><p>Out of Stock</p></div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <input type="text" id="item-search" placeholder="Search by item, code, or category...">
            </div>
            <div class="form-group">
                <select id="item-filter-store">
                    <option value="">All Stores</option>
                    <option value="MEDICAL">MEDICAL</option>
                    <option value="GENERAL">GENERAL</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Store</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>In Stock</th>
                        <th>Reorder Level</th>
                        <th>Status</th>
                        <th>Request</th>
                        <th id="manage-col-head" style="display:none;">Manage</th>
                    </tr>
                </thead>
                <tbody id="inventoryStockTableBody">
                    <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Inventory Requisition Records Log</h2>
        <button class="btn btn-secondary btn-sm" id="refresh-req-btn">Refresh</button>
    </div>
    <div class="card-body">
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
            <button type="button" class="btn btn-sm btn-secondary req-filter active" data-status="ALL">ALL</button>
            <button type="button" class="btn btn-sm btn-warning req-filter" data-status="PENDING">PENDING</button>
            <button type="button" class="btn btn-sm btn-success req-filter" data-status="APPROVED">APPROVED</button>
            <button type="button" class="btn btn-sm btn-danger req-filter" data-status="REJECTED">REJECTED</button>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Req ID &amp; Date</th>
                        <th>Store Type</th>
                        <th>Department</th>
                        <th>Ward</th>
                        <th>Item Count</th>
                        <th>Status</th>
                        <th id="req-actions-head" style="display:none;">Action</th>
                    </tr>
                </thead>
                <tbody id="userRequisitionsTableBody">
                    <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Request Item Modal -->
<div class="modal" id="request-modal">
    <div class="modal-content" style="max-width:460px;">
        <div class="modal-header">
            <h3>Request Stock</h3>
            <button class="modal-close" id="close-request-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="request-form">
                <input type="hidden" id="request-item-id">
                <div class="form-group">
                    <label for="request-context">Item</label>
                    <input type="text" id="request-context" readonly style="background:#EEF2F7;">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="request-qty">Quantity *</label>
                        <input type="number" id="request-qty" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label for="request-store">Store</label>
                        <input type="text" id="request-store" readonly style="background:#EEF2F7;">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="request-dept">Department</label>
                        <select id="request-dept"><option value="">My Department</option></select>
                    </div>
                    <div class="form-group">
                        <label for="request-ward">Ward</label>
                        <select id="request-ward"><option value="">No Ward</option></select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="request-remarks">Remarks</label>
                    <textarea id="request-remarks" rows="2" placeholder="Reason for request..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                    <button type="button" class="btn btn-secondary" id="cancel-request">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal" id="item-modal">
    <div class="modal-content" style="max-width:620px;">
        <div class="modal-header">
            <h3>Add Stock Item</h3>
            <button class="modal-close" id="close-item-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="item-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-name">Item Name *</label>
                        <input type="text" id="item-name" required>
                    </div>
                    <div class="form-group">
                        <label for="item-code">Item Code *</label>
                        <input type="text" id="item-code" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-store-type">Store Type</label>
                        <select id="item-store-type">
                            <option value="MEDICAL">MEDICAL</option>
                            <option value="GENERAL">GENERAL</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="item-category">Category</label>
                        <input type="text" id="item-category" placeholder="e.g. Analgesics, Antibiotics">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-generic">Generic Name</label>
                        <input type="text" id="item-generic">
                    </div>
                    <div class="form-group">
                        <label for="item-unit">Unit</label>
                        <input type="text" id="item-unit" placeholder="e.g. tabs, ml, box">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-batch">Batch Number</label>
                        <input type="text" id="item-batch">
                    </div>
                    <div class="form-group">
                        <label for="item-expiry">Expiry Date</label>
                        <input type="date" id="item-expiry">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-qty">Quantity in Stock</label>
                        <input type="number" id="item-qty" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label for="item-reorder">Reorder Level</label>
                        <input type="number" id="item-reorder" value="10" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label for="item-location">Storage Location</label>
                    <input type="text" id="item-location" placeholder="e.g. Shelf A1">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Item</button>
                    <button type="button" class="btn btn-secondary" id="cancel-item">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal" id="restock-modal">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <h3>Restock Item</h3>
            <button class="modal-close" id="close-restock-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="restock-form">
                <input type="hidden" id="restock-item-id">
                <div class="form-group">
                    <label for="restock-context">Item</label>
                    <input type="text" id="restock-context" readonly style="background:#EEF2F7;">
                </div>
                <div class="form-group">
                    <label for="restock-qty">Quantity to Add *</label>
                    <input type="number" id="restock-qty" min="1" value="10" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Restock</button>
                    <button type="button" class="btn btn-secondary" id="cancel-restock">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let inventoryData = [];
let requisitionData = [];
let currentUserRole = null;
let currentFilterStatus = 'ALL';
// Staff roles that can manage stock (restock / delete / approve / reject)
const MANAGE_ROLES = ['super_admin', 'admin', 'pharmacy', 'it'];

async function initInventory_management() {
    setupEventListeners();
    await Promise.all([loadCurrentUser(), loadInventory(), loadInventoryStats(), loadRequisitions('ALL')]);
    loadRequestOptions();
}

// Load departments + wards for the request modal (old EHMS requisition header fields)
async function loadRequestOptions() {
    try {
        const [deptRes, wardRes] = await Promise.all([
            fetch('/hms/backend/api/users.php?action=departments'),
            fetch('/hms/backend/api/wards.php')
        ]);
        const deptData = await deptRes.json();
        if (deptData.success) {
            const deptSel = document.getElementById('request-dept');
            deptData.departments.forEach(d => {
                deptSel.innerHTML += `<option value="${d.id}">${escHtml(d.name)}</option>`;
            });
        }
        const wardData = await wardRes.json();
        if (wardData.success) {
            const wardSel = document.getElementById('request-ward');
            wardData.wards.forEach(w => {
                wardSel.innerHTML += `<option value="${w.id}">${escHtml(w.ward_name)}</option>`;
            });
        }
    } catch (error) {
        console.warn('Request options load error:', error);
    }
}

function loadCurrentUser() {
    return fetch('/hms/backend/api/users.php?action=me')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.user) currentUserRole = data.user.role;
            applyRoleUI();
        })
        .catch(err => console.error('User load error:', err));
}

function canManage() {
    return currentUserRole && (currentUserRole === 'super_admin' || MANAGE_ROLES.indexOf(currentUserRole) !== -1);
}

function applyRoleUI() {
    const manage = canManage();
    document.getElementById('manage-col-head').style.display = manage ? '' : 'none';
    document.getElementById('req-actions-head').style.display = manage ? '' : 'none';
    if (manage) loadRequisitions(currentFilterStatus);
}

async function loadInventory() {
    const store = document.getElementById('item-filter-store').value;
    const q = document.getElementById('item-search').value.trim();
    const params = new URLSearchParams();
    if (store) params.set('store_type', store);
    if (q) params.set('q', q);
    try {
        const response = await fetch(`/hms/backend/api/inventory.php?${params}`);
        const data = await response.json();
        if (data.success) {
            inventoryData = data.items || [];
            renderInventoryTable();
        } else {
            throw new Error(data.error || 'Failed to load inventory');
        }
    } catch (error) {
        console.error('Inventory load error:', error);
        document.getElementById('inventoryStockTableBody').innerHTML =
            '<tr><td colspan="8" style="text-align:center;">Failed to load inventory</td></tr>';
    }
}

async function loadInventoryStats() {
    try {
        const response = await fetch('/hms/backend/api/inventory.php?action=stats');
        const data = await response.json();
        if (data.success) {
            document.getElementById('stat-items').textContent = data.stats.total_items;
            document.getElementById('stat-units').textContent = data.stats.total_units;
            document.getElementById('stat-low').textContent = data.stats.low_stock;
            document.getElementById('stat-out').textContent = data.stats.out_of_stock;
        }
    } catch (error) { console.error('Stats load error:', error); }
}

// ---- Stock Overview rendering (old EHMS style: MEDICAL/GENERAL + IN STOCK / OUT OF STOCK) ----
function renderInventoryTable() {
    const tbody = document.getElementById('inventoryStockTableBody');
    let html = '';
    if (!inventoryData || inventoryData.length === 0) {
        html = '<tr><td colspan="8" style="text-align:center;color:#94A3B8;">No stock items available.</td></tr>';
    } else {
        inventoryData.forEach(item => {
            const isMedical = item.store_type === 'MEDICAL';
            const storeBadge = isMedical
                ? '<span class="badge" style="background:#0072BC;color:#fff;">MEDICAL</span>'
                : '<span class="badge" style="background:#80C342;color:#fff;">GENERAL</span>';

            const stockQty = parseInt(item.in_stock !== undefined ? item.in_stock : item.quantity_in_stock, 10) || 0;
            const statusBadge = stockQty > 0
                ? '<span class="badge" style="background:#28A745;font-weight:700;color:#fff;">IN STOCK</span>'
                : '<span class="badge" style="background:#DC3545;font-weight:700;color:#fff;">OUT OF STOCK</span>';

            const requestBtn = stockQty <= 0
                ? '<button type="button" class="btn btn-sm" disabled style="background:#CBD5E1;color:#fff;font-weight:700;font-size:11px;padding:3px 12px;">REQUEST</button>'
                : `<button type="button" class="btn btn-sm btn-request-item" style="background-color:#0072BC;color:#fff;font-weight:700;font-size:11px;padding:3px 12px;" onclick="openRequestModal(${item.id}, '${escJs(item.drug_name)}', '${escJs(item.store_type)}')">REQUEST</button>`;

            const manageCell = canManage()
                ? `<td style="white-space:nowrap;text-align:center;">
                     <button class="btn btn-sm btn-secondary" onclick="openRestock(${item.id}, '${escJs(item.drug_name)}')">Restock</button>
                     <button class="btn btn-sm btn-danger" onclick="deleteItem(${item.id})">Delete</button>
                   </td>`
                : '';

            html += `
              <tr>
                <td>${storeBadge}</td>
                <td class="font-weight-bold" style="color:#0F2D59;font-weight:700;">${escHtml(item.drug_name)}</td>
                <td>${escHtml(item.category || '-')}</td>
                <td>${escHtml(item.unit || '-')}</td>
                <td class="font-weight-bold" style="font-weight:700;font-size:13px;">${stockQty}</td>
                <td>${parseInt(item.reorder_level) || 0}</td>
                <td>${statusBadge}</td>
                <td class="text-center">${requestBtn}</td>
                ${manageCell}
              </tr>`;
        });
    }
    tbody.innerHTML = html;
}

// ---- Requisitions rendering (old EHMS style: PENDING / APPROVED / REJECTED tags) ----
async function loadRequisitions(filterStatus) {
    currentFilterStatus = filterStatus || 'ALL';
    const params = new URLSearchParams();
    if (currentFilterStatus !== 'ALL') params.set('status', currentFilterStatus);
    try {
        const response = await fetch(`/hms/backend/api/inventory.php?action=requisitions&${params}`);
        const data = await response.json();
        if (data.success) {
            requisitionData = data.requisitions || [];
            renderRequisitions();
        }
    } catch (error) {
        console.error('Requisitions load error:', error);
        document.getElementById('userRequisitionsTableBody').innerHTML =
            '<tr><td colspan="7" style="text-align:center;">Failed to load requisitions</td></tr>';
    }
}

function renderRequisitions() {
    const tbody = document.getElementById('userRequisitionsTableBody');
    let rows = '';
    if (!requisitionData || requisitionData.length === 0) {
        rows = '<tr><td colspan="7" style="text-align:center;color:#94A3B8;">No requisition records found.</td></tr>';
    } else {
        requisitionData.forEach(req => {
            const statusNorm = String(req.status || '').toUpperCase();
            let statusTag = '';
            if (statusNorm === 'APPROVED') {
                statusTag = '<span class="badge" style="background:#28A745;color:#fff;font-weight:700;">APPROVED</span>';
            } else if (statusNorm === 'REJECTED') {
                statusTag = '<span class="badge" style="background:#DC3545;color:#fff;font-weight:700;">REJECTED</span>';
            } else {
                statusTag = '<span class="badge" style="background:#FFC107;color:#000;font-weight:700;">PENDING</span>';
            }

            const storeBadge = req.store_type === 'MEDICAL'
                ? '<span class="badge" style="background:#0072BC;color:#fff;">MEDICAL</span>'
                : '<span class="badge" style="background:#80C342;color:#fff;">GENERAL</span>';

            const dateStr = fmtDate(req.created_at);

            const items = Array.isArray(req.items) ? req.items : [];
            const itemCount = items.length
                ? `<div style="font-weight:700;">${items.length}</div>
                   <div style="font-size:11px;color:#64748B;">${items.map(it => escHtml(it.drug_name) + ' x' + it.requested_qty).join(', ')}</div>`
                : '<span style="color:#94A3B8;">-</span>';

            let actionsCell = '';
            if (canManage() && statusNorm === 'PENDING') {
                actionsCell = `
                    <td style="white-space:nowrap;text-align:center;">
                        <button class="btn btn-sm btn-success" onclick="approveRequisition(${req.id})">Approve</button>
                        <button class="btn btn-sm btn-danger" onclick="rejectRequisition(${req.id})">Reject</button>
                    </td>`;
            } else if (canManage()) {
                actionsCell = '<td style="text-align:center;color:#94A3B8;">-</td>';
            }

            rows += `
              <tr>
                <td>
                    <div class="font-weight-bold" style="color:#0072BC;font-weight:700;">${escHtml(req.req_code)}</div>
                    <div style="font-size:11px;color:#64748B;">${dateStr}</div>
                </td>
                <td>${storeBadge}</td>
                <td>${escHtml(req.department || '-')}</td>
                <td>${escHtml(req.ward_name || '-')}</td>
                <td>${itemCount}</td>
                <td>${statusTag}</td>
                ${actionsCell}
              </tr>`;
        });
    }
    tbody.innerHTML = rows;
}

function openRequestModal(id, name, store) {
    document.getElementById('request-form').reset();
    document.getElementById('request-item-id').value = id;
    document.getElementById('request-context').value = name;
    document.getElementById('request-store').value = store || 'MEDICAL';
    document.getElementById('request-qty').value = 1;
    document.getElementById('request-modal').classList.add('show');
}

async function approveRequisition(id) {
    try {
        const response = await fetch(`/hms/backend/api/inventory.php?action=reqstatus&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: 'APPROVED' })
        });
        const result = await response.json();
        if (result.success) {
            showAlert('Requisition approved', 'success');
            await Promise.all([loadRequisitions(currentFilterStatus), loadInventory(), loadInventoryStats()]);
        } else {
            showAlert(result.error || 'Update failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}

async function rejectRequisition(id) {
    const remarks = window.prompt('Reason for rejection:');
    if (remarks === null) return;
    try {
        const response = await fetch(`/hms/backend/api/inventory.php?action=reqstatus&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: 'REJECTED', remarks: remarks })
        });
        const result = await response.json();
        if (result.success) {
            showAlert('Requisition rejected', 'success');
            await Promise.all([loadRequisitions(currentFilterStatus)]);
        } else {
            showAlert(result.error || 'Update failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}

async function deleteItem(id) {
    if (!confirm('Delete this inventory item?')) return;
    try {
        const response = await fetch(`/hms/backend/api/inventory.php?action=delete&id=${id}`, { method: 'DELETE' });
        const result = await response.json();
        if (result.success) {
            showAlert('Item deleted', 'success');
            await Promise.all([loadInventory(), loadInventoryStats()]);
        } else {
            showAlert(result.error || 'Delete failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}

function openRestock(id, name) {
    document.getElementById('restock-item-id').value = id;
    document.getElementById('restock-context').value = name;
    document.getElementById('restock-qty').value = 10;
    document.getElementById('restock-modal').classList.add('show');
}

function setupEventListeners() {
    document.getElementById('new-item-btn').addEventListener('click', () => {
        document.getElementById('item-form').reset();
        document.getElementById('item-modal').classList.add('show');
    });
    document.getElementById('close-item-modal').addEventListener('click', () =>
        document.getElementById('item-modal').classList.remove('show'));
    document.getElementById('cancel-item').addEventListener('click', () =>
        document.getElementById('item-modal').classList.remove('show'));
    document.getElementById('close-restock-modal').addEventListener('click', () =>
        document.getElementById('restock-modal').classList.remove('show'));
    document.getElementById('cancel-restock').addEventListener('click', () =>
        document.getElementById('restock-modal').classList.remove('show'));
    document.getElementById('close-request-modal').addEventListener('click', () =>
        document.getElementById('request-modal').classList.remove('show'));
    document.getElementById('cancel-request').addEventListener('click', () =>
        document.getElementById('request-modal').classList.remove('show'));
    document.getElementById('item-form').addEventListener('submit', handleItemSubmit);
    document.getElementById('restock-form').addEventListener('submit', handleRestockSubmit);
    document.getElementById('request-form').addEventListener('submit', handleRequestSubmit);
    document.getElementById('refresh-item-btn').addEventListener('click', () => {
        loadInventory();
        loadInventoryStats();
    });
    document.getElementById('refresh-req-btn').addEventListener('click', () => loadRequisitions(currentFilterStatus));
    document.getElementById('item-search').addEventListener('input', debounce(loadInventory, 300));
    document.getElementById('item-filter-store').addEventListener('change', loadInventory);
    document.querySelectorAll('.req-filter').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.req-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadRequisitions(btn.dataset.status);
        });
    });
}

async function handleItemSubmit(e) {
    e.preventDefault();
    const data = {
        drug_name: document.getElementById('item-name').value.trim(),
        drug_code: document.getElementById('item-code').value.trim(),
        store_type: document.getElementById('item-store-type').value,
        category: document.getElementById('item-category').value.trim(),
        generic_name: document.getElementById('item-generic').value.trim(),
        unit: document.getElementById('item-unit').value.trim(),
        batch_number: document.getElementById('item-batch').value.trim(),
        expiry_date: document.getElementById('item-expiry').value || null,
        quantity_in_stock: parseInt(document.getElementById('item-qty').value) || 0,
        reorder_level: parseInt(document.getElementById('item-reorder').value) || 0,
        storage_location: document.getElementById('item-location').value.trim()
    };
    if (!data.drug_name || !data.drug_code) {
        showAlert('Item name and code are required', 'error');
        return;
    }
    try {
        const response = await fetch('/hms/backend/api/inventory.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            showAlert('Stock item added', 'success');
            document.getElementById('item-modal').classList.remove('show');
            await Promise.all([loadInventory(), loadInventoryStats()]);
        } else {
            showAlert(result.error || 'Add failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}

async function handleRestockSubmit(e) {
    e.preventDefault();
    const id = document.getElementById('restock-item-id').value;
    const qty = parseInt(document.getElementById('restock-qty').value) || 0;
    if (qty < 1) {
        showAlert('Enter a quantity to add', 'error');
        return;
    }
    try {
        const response = await fetch(`/hms/backend/api/inventory.php?action=restock&id=${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ quantity: qty })
        });
        const result = await response.json();
        if (result.success) {
            showAlert(`Restocked. New stock: ${result.quantity_in_stock}`, 'success');
            document.getElementById('restock-modal').classList.remove('show');
            await Promise.all([loadInventory(), loadInventoryStats()]);
        } else {
            showAlert(result.error || 'Restock failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}

async function handleRequestSubmit(e) {
    e.preventDefault();
    const itemId = document.getElementById('request-item-id').value;
    const quantity = parseInt(document.getElementById('request-qty').value) || 0;
    if (!itemId || quantity < 1) {
        showAlert('Item and a valid quantity are required', 'error');
        return;
    }
    try {
        const response = await fetch('/hms/backend/api/inventory.php?action=request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                store_type: document.getElementById('request-store').value || 'MEDICAL',
                department_id: document.getElementById('request-dept').value || null,
                ward_id: document.getElementById('request-ward').value || null,
                item_id: [itemId],
                requested_qty: [quantity],
                remarks: document.getElementById('request-remarks').value.trim()
            })
        });
        const result = await response.json();
        if (result.success) {
            showAlert(`Requisition ${result.req_code} submitted`, 'success');
            document.getElementById('request-modal').classList.remove('show');
            await Promise.all([loadRequisitions(currentFilterStatus)]);
        } else {
            showAlert(result.error || 'Request failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
}

function escJs(s) {
    return String(s == null ? '' : s).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function debounce(fn, wait) {
    let t;
    return function (...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
    };
}
</script>