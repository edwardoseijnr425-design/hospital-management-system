<style>
/* ============ REVENUE VIEW TOGGLE (Account Management) ============
   Swaps between the "Revenue by Area" panel and the "Total Money Paid"
   panel. Scoped under .am-view-toggle so it can't clash with other pages. */
.am-view-toggle{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 14px;padding-bottom:12px;border-bottom:1px solid #E2E8F0}
.am-toggle{font-family:inherit;font-size:11.5px;font-weight:700;letter-spacing:.3px;text-transform:uppercase;padding:8px 15px;border:1px solid #CBD5E1;border-radius:6px;background:#fff;color:#475569;cursor:pointer;display:inline-flex;align-items:center;gap:7px;transition:background .15s,border-color .15s,color .15s}
.am-toggle:hover{background:#F1F5F9;border-color:#9FC6E4;color:#0F2D59}
.am-toggle.active{background:#0072BC;border-color:#0072BC;color:#fff;box-shadow:0 2px 6px rgba(0,114,188,.25)}
</style>

<div class="card">
    <div class="card-header">
        <h2>Account Management</h2>
        <div style="display:flex;gap:10px;">
            <button class="btn btn-primary btn-sm" id="new-invoice-btn">New Invoice</button>
            <button class="btn btn-secondary btn-sm" id="refresh-invoice-btn">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="stats-grid" style="margin-bottom:18px;">
            <div class="stat-card">
                <div class="stat-icon success">&#128176;</div>
                <div class="stat-info"><h3 id="stat-revenue">0.00</h3><p>Collected (GHS)</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">&#9203;</div>
                <div class="stat-info"><h3 id="stat-pending">0</h3><p>Pending Invoices</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon danger">&#128181;</div>
                <div class="stat-info"><h3 id="stat-outstanding">0.00</h3><p>Outstanding (GHS)</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon primary">&#9989;</div>
                <div class="stat-info"><h3 id="stat-paid">0</h3><p>Paid</p></div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <input type="text" id="invoice-search" placeholder="Search by invoice no., patient, or hospital no...">
            </div>
            <div class="form-group">
                <select id="invoice-filter-status">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="pending">Pending</option>
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Invoice No.</th>
                        <th>Patient</th>
                        <th>Hospital No.</th>
                        <th>Visit</th>
                        <th>Sponsor</th>
                        <th>Net Amount</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="invoices-table">
                    <tr><td colspan="9" style="text-align: center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- VIEW TOGGLE : Revenue by Area  <->  Total Money Paid -->
<div class="am-view-toggle" role="tablist" aria-label="Revenue view">
    <button type="button" class="am-toggle active" data-am-view="areas" role="tab" aria-selected="true">
        <i class="fa-solid fa-chart-pie"></i> Toggle to view: Revenue by Area
    </button>
    <button type="button" class="am-toggle" data-am-view="paid" role="tab" aria-selected="false">
        <i class="fa-solid fa-money-bill-wave"></i> Toggle to view: Total Money Paid
    </button>
</div>

<!-- Revenue by Area -->
<div class="card" id="am-areas-card">
    <div class="card-header">
        <h2>Revenue by Area</h2>
        <button class="btn btn-secondary btn-sm" id="refresh-areas-btn">Refresh</button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Area</th>
                        <th>Transactions</th>
                        <th>Items Sold</th>
                        <th>Revenue (GHS)</th>
                        <th>Share</th>
                    </tr>
                </thead>
                <tbody id="areas-table">
                    <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Transactions & Money Paid Log -->
<div class="card" id="am-paid-card" style="display:none;">
    <div class="card-header">
        <h2>Transactions &amp; Money Paid Log</h2>
        <div style="display:flex;gap:8px;align-items:center;">
            <button class="btn btn-sm" id="export-csv-btn" style="background-color:#0F2D59;color:#fff;font-weight:700;">Export CSV</button>
            <button class="btn btn-sm" id="print-trans-btn" style="background-color:#80C342;color:#fff;font-weight:700;">Print</button>
            <button class="btn btn-secondary btn-sm" id="refresh-trans-btn">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label for="trans-date-from">From</label>
                <input type="date" id="trans-date-from">
            </div>
            <div class="form-group">
                <label for="trans-date-to">To</label>
                <input type="date" id="trans-date-to">
            </div>
            <div class="form-group" style="flex:2;min-width:220px;">
                <input type="text" id="trans-search" placeholder="Search invoice no., patient, or hospital no...">
            </div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;background:#EAF4FC;border:1px solid #BBDDF3;border-radius:8px;padding:12px 16px;margin-bottom:14px;">
            <strong style="color:#0F2D59;">TOTAL MONEY PAID</strong>
            <span style="font-size:22px;font-weight:800;color:#0072BC;" id="trans-total-paid">GHS 0.00</span>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice No.</th>
                        <th>Patient</th>
                        <th>Hospital No.</th>
                        <th>Areas</th>
                        <th>Items</th>
                        <th>Amount Paid</th>
                        <th>Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="transactions-table">
                    <tr><td colspan="9" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Invoice Modal -->
<div class="modal" id="invoice-modal">
    <div class="modal-content" style="max-width:640px;">
        <div class="modal-header">
            <h3>New Invoice</h3>
            <button class="modal-close" id="close-invoice-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="invoice-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="inv-patient">Patient *</label>
                        <select id="inv-patient" required><option value="">Select Patient</option></select>
                    </div>
                    <div class="form-group">
                        <label for="inv-status">Status</label>
                        <select id="inv-status">
                            <option value="pending">Pending</option>
                            <option value="draft">Draft</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>

                <h4>Line Items</h4>
                <div id="invoice-items">
                    <div class="form-row inv-item-row">
                        <div class="form-group" style="flex:2;min-width:180px;">
                            <label>Description</label>
                            <input type="text" class="inv-desc" placeholder="e.g. Consultation fee">
                        </div>
                        <div class="form-group" style="flex:1;min-width:80px;">
                            <label>Type</label>
                            <select class="inv-type">
                                <option value="consultation">Consultation</option>
                                <option value="procedure">Procedure</option>
                                <option value="drug">Drug</option>
                                <option value="lab_test">Lab Test</option>
                                <option value="radiology">Radiology</option>
                                <option value="bed">Bed</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex:0 0 70px;">
                            <label>Qty</label>
                            <input type="number" class="inv-qty" value="1" min="1">
                        </div>
                        <div class="form-group" style="flex:0 0 110px;">
                            <label>Unit Price</label>
                            <input type="number" class="inv-price" step="0.01" min="0" value="0">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="add-inv-item" style="margin-bottom:12px;">+ Add Item</button>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Invoice</button>
                    <button type="button" class="btn btn-secondary" id="cancel-invoice">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Invoice Detail Modal -->
<div class="modal" id="invoice-detail-modal">
    <div class="modal-content" style="max-width:620px;">
        <div class="modal-header">
            <h3>Invoice Details</h3>
            <button class="modal-close" id="close-invoice-detail-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="invoice-detail-body" style="color:#64748B;font-size:13px;">Loading...</div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal" id="payment-modal">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <h3>Record Payment</h3>
            <button class="modal-close" id="close-payment-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display:flex;justify-content:space-between;align-items:center;background:#EEF2F7;border-radius:8px;padding:12px 14px;margin-bottom:14px;">
                <div>
                    <div style="font-size:11px;font-weight:800;color:#0F2D59;text-transform:uppercase;">Invoice</div>
                    <div style="font-weight:700;color:#0072BC;" id="payment-invoice-no">-</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px;font-weight:800;color:#0F2D59;text-transform:uppercase;">Amount</div>
                    <div style="font-size:18px;font-weight:800;color:#0F2D59;" id="payment-invoice-amount">GHS 0.00</div>
                </div>
            </div>
            <div class="form-group">
                <label for="payment-method">Payment Method *</label>
                <select id="payment-method">
                    <option value="Cash">Cash</option>
                    <option value="Mobile Money">Mobile Money</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Card">Card</option>
                    <option value="NHIA / Insurance">NHIA / Insurance</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" id="confirm-payment-btn">Confirm Payment</button>
                <button type="button" class="btn btn-secondary" id="cancel-payment">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
let invoiceData = [];
let areaData = [];
let areaTotals = { revenue: 0, transactions: 0 };
let transactionsData = [];
let transTotalPaid = 0;
let pendingPaidInvoiceId = null;

async function initAccountManagement() {
    setupEventListeners();
    setupRevenueViewToggle();
    await Promise.all([loadPatients(), loadInvoices(), loadInvoiceStats(), loadRevenueAreas(), loadTransactions()]);
}

/* Revenue view toggle: shows exactly one of the two summary panels
   (Revenue by Area / Total Money Paid). Both datasets are already fetched by
   initAccountManagement, so switching is purely presentational — flipping
   back never needs a refetch, and the hidden panel's filters keep their state. */
function setupRevenueViewToggle() {
    document.querySelectorAll('.am-toggle').forEach(btn => {
        btn.addEventListener('click', () => setRevenueView(btn.dataset.amView));
    });
    setRevenueView('areas');
}

function setRevenueView(view) {
    const active = view === 'paid' ? 'paid' : 'areas';
    const areas = document.getElementById('am-areas-card');
    const paid = document.getElementById('am-paid-card');
    if (areas) areas.style.display = active === 'areas' ? '' : 'none';
    if (paid) paid.style.display = active === 'paid' ? '' : 'none';
    document.querySelectorAll('.am-toggle').forEach(btn => {
        const on = btn.dataset.amView === active;
        btn.classList.toggle('active', on);
        btn.setAttribute('aria-selected', on ? 'true' : 'false');
    });
}

async function loadPatients() {
    try {
        const response = await fetch('/hms/backend/api/patients.php');
        const data = await response.json();
        if (data.success) {
            const sel = document.getElementById('inv-patient');
            (data.patients || []).forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${p.first_name} ${p.last_name || ''} (${p.hospital_number})`;
                sel.appendChild(opt);
            });
        }
    } catch (error) { console.error('Patients load error:', error); }
}

async function loadInvoices() {
    const status = document.getElementById('invoice-filter-status').value;
    const q = document.getElementById('invoice-search').value.trim();
    const params = new URLSearchParams();
    if (status) params.set('status', status);
    if (q) params.set('q', q);
    try {
        const response = await fetch(`/hms/backend/api/invoices.php?${params}`);
        const data = await response.json();
        if (data.success) {
            invoiceData = data.invoices || [];
            renderInvoiceTable();
        } else {
            throw new Error(data.error || 'Failed to load invoices');
        }
    } catch (error) {
        console.error('Invoices load error:', error);
        document.getElementById('invoices-table').innerHTML = '<tr><td colspan="9" style="text-align:center;">Failed to load invoices</td></tr>';
    }
}

async function loadInvoiceStats() {
    try {
        const response = await fetch('/hms/backend/api/invoices.php?action=stats');
        const data = await response.json();
        if (data.success) {
            document.getElementById('stat-revenue').textContent = Number(data.stats.total_revenue || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
            document.getElementById('stat-pending').textContent = data.stats.pending;
            document.getElementById('stat-outstanding').textContent = Number(data.stats.outstanding || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
            document.getElementById('stat-paid').textContent = data.stats.paid_count;
        }
    } catch (error) { console.error('Stats load error:', error); }
}

// Revenue split by billing area (money paid -> where it came from)
async function loadRevenueAreas() {
    const df = document.getElementById('trans-date-from').value;
    const dt = document.getElementById('trans-date-to').value;
    const params = new URLSearchParams();
    if (df) params.set('date_from', df);
    if (dt) params.set('date_to', dt);
    try {
        const response = await fetch(`/hms/backend/api/invoices.php?action=areas&${params}`);
        const data = await response.json();
        if (data.success) {
            areaData = data.areas || [];
            areaTotals = data.totals || { revenue: 0, transactions: 0 };
            renderAreasTable();
        }
    } catch (error) { console.error('Areas load error:', error); }
}

function renderAreasTable() {
    const tbody = document.getElementById('areas-table');
    if (!tbody) return;
    if (!areaData.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No paid transactions recorded yet</td></tr>';
        return;
    }
    const total = Number(areaTotals.revenue) || 0;
    tbody.innerHTML = areaData.map(a => {
        const rev = Number(a.revenue) || 0;
        const pct = total > 0 ? Math.round((rev / total) * 100) : 0;
        return `
        <tr>
            <td><span class="badge" style="background:#EEF2F7;color:#0F2D59;font-weight:700;">${escHtml(a.label)}</span></td>
            <td>${a.transactions}</td>
            <td>${a.items_sold}</td>
            <td><strong>GHS ${rev.toLocaleString(undefined, { minimumFractionDigits: 2 })}</strong></td>
            <td>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="flex:1;max-width:130px;background:#EEF2F7;border-radius:6px;height:8px;overflow:hidden;">
                        <div style="width:${pct}%;height:100%;background:#0072BC;"></div>
                    </div>
                    <span style="font-size:11px;font-weight:700;color:#0F2D59;">${pct}%</span>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// Money paid log (paid invoices)
async function loadTransactions() {
    const df = document.getElementById('trans-date-from').value;
    const dt = document.getElementById('trans-date-to').value;
    const q = document.getElementById('trans-search').value.trim();
    const params = new URLSearchParams();
    if (df) params.set('date_from', df);
    if (dt) params.set('date_to', dt);
    if (q) params.set('q', q);
    try {
        const response = await fetch(`/hms/backend/api/invoices.php?action=transactions&${params}`);
        const data = await response.json();
        if (data.success) {
            transactionsData = data.transactions || [];
            transTotalPaid = Number(data.total_paid) || 0;
            renderTransactionsTable();
        }
    } catch (error) {
        console.error('Transactions load error:', error);
        document.getElementById('transactions-table').innerHTML = '<tr><td colspan="9" style="text-align:center;">Failed to load transactions</td></tr>';
    }
}

function renderTransactionsTable() {
    const tbody = document.getElementById('transactions-table');
    const totalEl = document.getElementById('trans-total-paid');
    const fmt = n => Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
    if (totalEl) totalEl.textContent = 'GHS ' + fmt(transTotalPaid);
    if (!transactionsData.length) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">No paid transactions found</td></tr>';
        return;
    }
    tbody.innerHTML = transactionsData.map(t => {
        const dateStr = fmtDate(t.updated_at);
        return `
        <tr>
            <td>${dateStr}</td>
            <td><strong style="color:#0072BC;">${escHtml(t.invoice_number)}</strong></td>
            <td>${escHtml(t.patient_name)}</td>
            <td>${escHtml(t.hospital_number)}</td>
            <td>${escHtml(t.areas || '-')}</td>
            <td>${t.item_count}</td>
            <td><strong>GHS ${fmt(t.net_amount)}</strong></td>
            <td><span class="badge" style="background:#EEF2F7;color:#0F2D59;">${escHtml(t.payment_method || '-')}</span></td>
            <td><span class="badge badge-success">PAID</span></td>
        </tr>`;
    }).join('');
}

function renderInvoiceTable() {
    const tbody = document.getElementById('invoices-table');
    if (!invoiceData.length) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No invoices found</td></tr>';
        return;
    }
    tbody.innerHTML = invoiceData.map(i => {
        const badge = i.status === 'paid' ? 'badge-success'
            : i.status === 'cancelled' ? 'badge-danger'
            : i.status === 'partial' ? 'badge-warning'
            : i.status === 'draft' ? 'badge-secondary' : 'badge-info';
        return `
        <tr>
            <td><strong>${i.invoice_number || '-'}</strong></td>
            <td>${i.patient_name || '-'}</td>
            <td>${i.hospital_number || '-'}</td>
            <td>${i.visit_number || '-'}</td>
            <td>${i.sponsor_name || 'Self-Pay'}</td>
            <td><strong>GHS ${Number(i.net_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}</strong></td>
            <td><span class="badge ${badge}">${i.status || '-'}</span></td>
            <td>${i.item_count || 0}</td>
            <td style="white-space:nowrap;">
                <button class="btn btn-sm btn-secondary" onclick="openInvoiceDetail(${i.id})">View</button>
                ${i.status === 'pending' || i.status === 'partial' || i.status === 'draft'
                    ? `<button class="btn btn-sm btn-success" onclick="openPaymentModal(${i.id}, '${escJs(i.invoice_number)}', ${Number(i.net_amount) || 0})">Mark Paid</button>`
                    : ''}
                ${i.status === 'pending' || i.status === 'partial'
                    ? `<button class="btn btn-sm btn-warning" onclick="setInvoiceStatus(${i.id}, 'cancelled')">Cancel</button>`
                    : ''}
            </td>
        </tr>`;
    }).join('');
}

async function openInvoiceDetail(id) {
    const body = document.getElementById('invoice-detail-body');
    body.innerHTML = '<span class="spinner"></span>Loading...';
    document.getElementById('invoice-detail-modal').classList.add('show');
    try {
        const response = await fetch(`/hms/backend/api/invoices.php?action=detail&id=${id}`);
        const data = await response.json();
        if (data.success) {
            const inv = data.invoice;
            const items = data.items || [];
            body.innerHTML = `
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid #E0E6ED;padding-bottom:10px;margin-bottom:12px;">
                    <div>
                        <strong style="color:#0D47A1;font-size:15px;">${inv.invoice_number}</strong><br>
                        <span>${inv.patient_first} ${inv.patient_last}</span>
                    </div>
                    <div style="text-align:right;">
                        <span class="badge ${inv.status === 'paid' ? 'badge-success' : 'badge-warning'}">${inv.status}</span>
                    </div>
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
                    <thead>
                        <tr style="background:#EBF0F6;color:#0D47A1;text-transform:uppercase;font-size:11px;">
                            <th style="padding:8px;text-align:left;">Description</th>
                            <th style="padding:8px;text-align:left;">Type</th>
                            <th style="padding:8px;text-align:right;">Qty</th>
                            <th style="padding:8px;text-align:right;">Unit</th>
                            <th style="padding:8px;text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${items.map(it => `<tr style="border-bottom:1px solid #EEF2F7;">
                            <td style="padding:8px;">${it.description || '-'}</td>
                            <td style="padding:8px;">${it.item_type}</td>
                            <td style="padding:8px;text-align:right;">${it.quantity}</td>
                            <td style="padding:8px;text-align:right;">${Number(it.unit_price).toLocaleString(undefined,{minimumFractionDigits:2})}</td>
                            <td style="padding:8px;text-align:right;"><strong>${Number(it.total_price).toLocaleString(undefined,{minimumFractionDigits:2})}</strong></td>
                        </tr>`).join('')}
                    </tbody>
                </table>
                <div style="margin-top:12px;text-align:right;font-size:13px;">
                    <div>Total: <strong>GHS ${Number(inv.total_amount).toLocaleString(undefined,{minimumFractionDigits:2})}</strong></div>
                    <div>Discount: -GHS ${Number(inv.discount_amount).toLocaleString(undefined,{minimumFractionDigits:2})}</div>
                    <div>Tax: +GHS ${Number(inv.tax_amount).toLocaleString(undefined,{minimumFractionDigits:2})}</div>
                    <div style="font-size:16px;color:#0D47A1;margin-top:4px;">Net: <strong>GHS ${Number(inv.net_amount).toLocaleString(undefined,{minimumFractionDigits:2})}</strong></div>
                </div>`;
        } else {
            body.innerHTML = `<div class="alert alert-error">${data.error || 'Failed to load invoice'}</div>`;
        }
    } catch (error) {
        body.innerHTML = '<div class="alert alert-error">Network error</div>';
    }
}

async function setInvoiceStatus(id, status, paymentMethod) {
    if (status === 'cancelled' && !confirm('Cancel this invoice?')) return;
    try {
        const body = { status: status };
        if (paymentMethod) body.payment_method = paymentMethod;
        const response = await fetch(`/hms/backend/api/invoices.php?action=status&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        const result = await response.json();
        if (result.success) {
            showAlert(status === 'paid' ? 'Invoice marked as paid' : 'Invoice status updated', 'success');
            await Promise.all([loadInvoices(), loadInvoiceStats(), loadRevenueAreas(), loadTransactions()]);
        } else {
            showAlert(result.error || 'Update failed', 'error');
        }
    } catch (error) { console.error(error); showAlert('Network error', 'error'); }
}

function openPaymentModal(id, invoiceNumber, amount) {
    pendingPaidInvoiceId = id;
    document.getElementById('payment-invoice-no').textContent = invoiceNumber;
    document.getElementById('payment-invoice-amount').textContent = 'GHS ' + Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
    document.getElementById('payment-method').value = 'Cash';
    document.getElementById('payment-modal').classList.add('show');
}

function closePaymentModal() {
    pendingPaidInvoiceId = null;
    document.getElementById('payment-modal').classList.remove('show');
}

function addItemRow() {
    const container = document.getElementById('invoice-items');
    const row = document.createElement('div');
    row.className = 'form-row inv-item-row';
    row.innerHTML = `
        <div class="form-group" style="flex:2;min-width:180px;">
            <label>Description</label>
            <input type="text" class="inv-desc" placeholder="e.g. Consultation fee">
        </div>
        <div class="form-group" style="flex:1;min-width:80px;">
            <label>Type</label>
            <select class="inv-type">
                <option value="consultation">Consultation</option>
                <option value="procedure">Procedure</option>
                <option value="drug">Drug</option>
                <option value="lab_test">Lab Test</option>
                <option value="radiology">Radiology</option>
                <option value="bed">Bed</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="form-group" style="flex:0 0 70px;">
            <label>Qty</label>
            <input type="number" class="inv-qty" value="1" min="1">
        </div>
        <div class="form-group" style="flex:0 0 110px;">
            <label>Unit Price</label>
            <input type="number" class="inv-price" step="0.01" min="0" value="0">
        </div>
        <div style="align-self:flex-end;margin-bottom:16px;">
            <button type="button" class="btn btn-sm btn-danger inv-remove-row">&times;</button>
        </div>`;
    container.appendChild(row);
    row.querySelector('.inv-remove-row').addEventListener('click', () => row.remove());
}

function exportTransactionsCSV() {
    if (!transactionsData.length) { showAlert('Nothing to export', 'error'); return; }
    const c = v => '"' + String(v === null || v === undefined ? '' : v).replace(/"/g, '""') + '"';
    const rows = [['Date', 'Invoice No.', 'Patient', 'Hospital No.', 'Areas', 'Items', 'Amount Paid (GHS)', 'Method']];
    transactionsData.forEach(t => {
        rows.push([
            fmtDate(t.updated_at),
            t.invoice_number, t.patient_name, t.hospital_number,
            t.areas || '', t.item_count, Number(t.net_amount || 0).toFixed(2), t.payment_method || ''
        ]);
    });
    const csv = rows.map(r => r.map(c).join(',')).join('\r\n');
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'transactions-paid-log.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => URL.revokeObjectURL(a.href), 1000);
}

function printTransactions() {
    const w = window.open('', '_blank', 'width=900,height=700');
    if (!w) { showAlert('Pop-up blocked — allow pop-ups to print', 'error'); return; }
    const fmt = n => Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
    const rowsHtml = transactionsData.map(t => {
        return `
        <tr>
            <td>${fmtDate(t.updated_at)}</td>
            <td>${String(t.invoice_number || '').replace(/</g, '&lt;')}</td>
            <td>${String(t.patient_name || '').replace(/</g, '&lt;')}</td>
            <td>${String(t.hospital_number || '').replace(/</g, '&lt;')}</td>
            <td>${String(t.areas || '').replace(/</g, '&lt;')}</td>
            <td style="text-align:right;">${t.item_count}</td>
            <td style="text-align:right;">GHS ${fmt(t.net_amount)}</td>
            <td>${String(t.payment_method || '-').replace(/</g, '&lt;')}</td>
        </tr>`;
    }).join('');
    w.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>Transactions & Money Paid Log</title>
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
<h2>Transactions &amp; Money Paid Log</h2>
<div class="sub">Printed ${fmtDateTime(new Date())}</div>
<div class="total">TOTAL MONEY PAID: GHS ${fmt(transTotalPaid)} (${transactionsData.length} transactions)</div>
<table><thead><tr><th>Date</th><th>Invoice No.</th><th>Patient</th><th>Hospital No.</th><th>Areas</th><th>Items</th><th>Amount</th><th>Method</th></tr></thead>
<tbody>${rowsHtml}</tbody></table>
</body></html>`);
    w.document.close();
    w.focus();
    setTimeout(() => { w.print(); }, 350);
}

function setupEventListeners() {
    document.getElementById('new-invoice-btn').addEventListener('click', () => {
        document.getElementById('invoice-form').reset();
        document.getElementById('invoice-items').innerHTML = '';
        addItemRow();
        document.getElementById('invoice-modal').classList.add('show');
    });
    document.getElementById('close-invoice-modal').addEventListener('click', () =>
        document.getElementById('invoice-modal').classList.remove('show'));
    document.getElementById('cancel-invoice').addEventListener('click', () =>
        document.getElementById('invoice-modal').classList.remove('show'));
    document.getElementById('close-invoice-detail-modal').addEventListener('click', () =>
        document.getElementById('invoice-detail-modal').classList.remove('show'));
    document.getElementById('invoice-form').addEventListener('submit', handleInvoiceSubmit);
    document.getElementById('add-inv-item').addEventListener('click', addItemRow);
    document.getElementById('refresh-invoice-btn').addEventListener('click', () => {
        loadInvoices();
        loadInvoiceStats();
    });
    document.getElementById('invoice-search').addEventListener('input', debounce(loadInvoices, 300));
    document.getElementById('invoice-filter-status').addEventListener('change', loadInvoices);

    document.getElementById('refresh-areas-btn').addEventListener('click', loadRevenueAreas);
    document.getElementById('refresh-trans-btn').addEventListener('click', () => {
        loadTransactions();
        loadRevenueAreas();
    });
    document.getElementById('trans-date-from').addEventListener('change', () => {
        loadTransactions();
        loadRevenueAreas();
    });
    document.getElementById('trans-date-to').addEventListener('change', () => {
        loadTransactions();
        loadRevenueAreas();
    });
    document.getElementById('trans-search').addEventListener('input', debounce(loadTransactions, 300));

    document.getElementById('export-csv-btn').addEventListener('click', exportTransactionsCSV);
    document.getElementById('print-trans-btn').addEventListener('click', printTransactions);
    document.getElementById('close-payment-modal').addEventListener('click', closePaymentModal);
    document.getElementById('cancel-payment').addEventListener('click', closePaymentModal);
    document.getElementById('confirm-payment-btn').addEventListener('click', async () => {
        if (!pendingPaidInvoiceId) return;
        const method = document.getElementById('payment-method').value;
        await setInvoiceStatus(pendingPaidInvoiceId, 'paid', method);
        closePaymentModal();
    });
}

async function handleInvoiceSubmit(e) {
    e.preventDefault();
    const patientId = document.getElementById('inv-patient').value;
    if (!patientId) {
        showAlert('Please select a patient', 'error');
        return;
    }
    const itemRows = document.querySelectorAll('.inv-item-row');
    const items = [];
    itemRows.forEach(row => {
        const desc = row.querySelector('.inv-desc').value.trim();
        const price = parseFloat(row.querySelector('.inv-price').value) || 0;
        const qty = parseInt(row.querySelector('.inv-qty').value) || 1;
        if (desc && price > 0) {
            items.push({
                description: desc,
                item_type: row.querySelector('.inv-type').value,
                quantity: qty,
                unit_price: price
            });
        }
    });
    if (!items.length) {
        showAlert('Add at least one line item with a description and price', 'error');
        return;
    }
    try {
        const response = await fetch('/hms/backend/api/invoices.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                patient_id: patientId,
                status: document.getElementById('inv-status').value,
                items: items
            })
        });
        const result = await response.json();
        if (result.success) {
            showAlert(`Invoice ${result.invoice_number} created`, 'success');
            document.getElementById('invoice-modal').classList.remove('show');
            await Promise.all([loadInvoices(), loadInvoiceStats(), loadRevenueAreas(), loadTransactions()]);
        } else {
            showAlert(result.error || 'Invoice creation failed', 'error');
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

function escHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
</script>