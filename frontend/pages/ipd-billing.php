<style>
    /* ============ IPD BILLING : in-patient bills, deposits & clearance (GHS) ============
       Scoped under #ipdbill. The shell supplies card/card-body/table/btn/modal/
       form-group; only the station-specific pieces are defined here. */
    #ipdbill{--ib-green:#1E7A34;--ib-ink:#0F2D59;--ib-mute:#64748B;--ib-line:#DCE4EC}
    #ipdbill .gap-2{gap:.5rem}
    #ipdbill .flex{display:flex}
    #ipdbill .wrap{flex-wrap:wrap}
    #ipdbill .items-center{align-items:center}

    /* Green billing banner, as in the pasted design */
    #ib-banner{background:var(--ib-green);color:#fff;border-radius:5px;padding:10px 14px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
    #ib-banner h5{margin:0;font-size:13px;font-weight:800;letter-spacing:.3px;text-transform:uppercase}
    #ib-banner .ib-sub{font-size:11px;opacity:.9;margin-top:2px}
    #ib-banner .ib-nav{background:#fff;color:var(--ib-green);border:none;border-radius:4px;padding:7px 13px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
    #ib-banner .ib-nav:hover{background:#D8F0DE}

    /* Totals strip */
    #ib-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:16px}
    #ib-stats .s{background:#fff;border:1px solid var(--ib-line);border-left:4px solid #0b5fa5;border-radius:5px;padding:10px 12px}
    #ib-stats .s .lbl{font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--ib-mute)}
    #ib-stats .s .val{font-size:20px;font-weight:800;color:var(--ib-ink);line-height:1.2;margin-top:3px}
    #ib-stats .s .sub{font-size:10.5px;color:var(--ib-mute)}
    #ib-stats .s.paid{border-left-color:var(--ib-green)}
    #ib-stats .s.bal{border-left-color:#C0392B}
    #ib-stats .s.bal .val{color:#C0392B}
    #ib-stats .s.unb{border-left-color:#B9770E}
    #ib-stats .s.unb .val{color:#8A5A00}

    #ib-search{width:280px}
    #ib-table td{vertical-align:middle}
    #ib-table .ib-code{font-family:Consolas,'Courier New',monospace;font-weight:700;color:var(--ib-ink);font-size:11px}
    #ib-table .ib-name{font-weight:700;color:#1E293B}
    #ib-table .money{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}
    #ib-table .ib-total{font-weight:800;color:var(--ib-ink)}
    #ib-table .ib-paid{font-weight:700;color:var(--ib-green)}
    #ib-table .ib-bal{font-weight:800;color:#C0392B}
    #ib-table .ib-bal.clear{color:var(--ib-green)}
    #ib-table .ib-bal.none{color:#8A5A00;font-weight:600}
    #ib-table .ib-actions{display:flex;gap:5px;flex-wrap:wrap}
    #ib-table .ib-actions button{font-size:10px;padding:4px 8px;border-radius:3px;border:1px solid #CBD5E1;background:#fff;color:#334155;cursor:pointer;font-family:inherit;font-weight:600;display:inline-flex;align-items:center;gap:4px}
    #ib-table .ib-actions button.dep{background:var(--ib-green);border-color:var(--ib-green);color:#fff}
    #ib-table .ib-actions button.dep:hover{background:#155D28}
    #ib-table .ib-actions button.clr{color:#0b5fa5;border-color:#9FC6E4}
    #ib-table .ib-actions button.clr:hover{background:#E7F3FC}
    #ib-table .ib-actions button.pmt:hover{background:#F1F5F9}
    #ib-empty{padding:22px;text-align:center;color:#8A94A6;font-size:12px}

    /* Payment modal */
    #ib-pay-modal .modal-content{max-width:560px !important}
    #ib-pay-modal .ib-head{background:var(--ib-green);color:#fff;margin:-16px -16px 16px;padding:12px 16px;border-radius:4px 4px 0 0}
    #ib-pay-modal .ib-head h3{margin:0;font-size:14px;font-weight:800;letter-spacing:.3px}
    #ib-pay-modal .ib-head .ib-head-sub{font-size:11.5px;opacity:.92;margin-top:3px}
    #ib-pay-modal label{display:block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#64748B;margin-bottom:5px}
    #ib-pay-modal select,#ib-pay-modal input,#ib-pay-modal textarea{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;font-family:inherit;background:#fff;color:#222;box-sizing:border-box}
    #ib-pay-modal input[type=number]{font-weight:700;font-size:14px}
    #ib-pay-modal select:focus,#ib-pay-modal input:focus,#ib-pay-modal textarea:focus{outline:none;border-color:var(--ib-green);box-shadow:0 0 4px rgba(30,122,52,.22)}
    #ib-pay-modal .ib-amount-hint{font-size:10.5px;color:#8A94A6;margin-top:5px}
    #ib-pay-modal .ib-amount-hint.over{color:#C0392B;font-weight:700}
    #ib-pay-modal .ib-terms{border:1px solid var(--ib-line);border-radius:5px;overflow:hidden;margin-bottom:16px}
    #ib-pay-modal .ib-terms .tr{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:8px 12px;font-size:12px;border-bottom:1px solid #EEF2F7}
    #ib-pay-modal .ib-terms .tr:last-child{border-bottom:none}
    #ib-pay-modal .ib-terms .tr.total{background:#F1F5F9;font-weight:800;border-top:2px solid var(--ib-line);font-size:13px}
    #ib-pay-modal .ib-terms .tr.due{color:#C0392B}
    #ib-pay-modal .ib-terms .tr.clear{color:var(--ib-green)}
    #ib-pay-modal .ib-terms .tr b{font-variant-numeric:tabular-nums}
    #ib-pay-modal .ib-warn{background:#FEF5E0;border:1px solid #F0AD4E;border-left:4px solid #f0ad4e;color:#8A5A00;border-radius:4px;padding:9px 11px;font-size:11.5px;margin-bottom:14px;display:none}
    #ib-pay-modal .ib-warn.show{display:block}
    #ib-pay-modal .ib-warn.green{background:#E8F6EC;border-color:#7FCF97;border-left-color:var(--ib-green);color:#155D28}
    #ib-pay-modal .ib-foot-note{font-size:10.5px;color:#8A94A6;margin-top:6px}

    /* Payment history */
    #ib-history{border:1px solid var(--ib-line);border-radius:5px;overflow:hidden;margin-top:14px}
    #ib-history .hd{background:#E6EEF5;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#222;padding:7px 9px;border-bottom:2px solid #b2c8de;display:flex;justify-content:space-between;gap:8px;align-items:center}
    #ib-history .bd{max-height:190px;overflow-y:auto}
    #ib-history table{width:100%;border-collapse:collapse;font-size:11px}
    #ib-history th{background:#F1F5F9;font-size:9.5px;text-transform:uppercase;letter-spacing:.3px;color:#475569;padding:5px 8px;text-align:left;border-bottom:1px solid #E1E8F0;position:sticky;top:0}
    #ib-history td{padding:6px 8px;border-bottom:1px solid #EEF2F7;vertical-align:top}
    #ib-history .pt{font-weight:700;font-size:9.5px;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
    #ib-history .pt.dep{color:#0b5fa5}
    #ib-history .pt.clr{color:var(--ib-green)}
    #ib-history .amt{text-align:right;white-space:nowrap;font-weight:700;font-variant-numeric:tabular-nums}
    #ib-history .meta{font-size:10px;color:#8A94A6;margin-top:2px}
    #ib-history .empty{padding:14px;text-align:center;color:#8A94A6}

    @media (max-width:640px){
        #ib-banner{flex-direction:column;align-items:flex-start}
        #ib-search{width:100%}
    }
</style>

<div id="ipdbill" class="container-fluid">
    <!-- Billing banner -->
    <div id="ib-banner">
        <div>
            <h5><i class="fa-solid fa-money-bill-transfer"></i> In-Patient Billing, Deposits &amp; Clearance (GHS)</h5>
            <div class="ib-sub">Bills come from raised invoices. Recording a payment settles what was received; the patient is discharged from the Admissions page.</div>
        </div>
        <button type="button" class="ib-nav" id="ib-back-to-admissions"><i class="fa-solid fa-arrow-left"></i> Admissions</button>
    </div>

    <!-- Totals -->
    <div id="ib-stats">
        <div class="s"><div class="lbl">Total Billed</div><div class="val" id="ib-stat-billed">&mdash;</div><div class="sub">across in-patient invoices</div></div>
        <div class="s paid"><div class="lbl">Deposits &amp; Payments</div><div class="val" id="ib-stat-paid">&mdash;</div><div class="sub">money received</div></div>
        <div class="s bal"><div class="lbl">Outstanding Balance</div><div class="val" id="ib-stat-balance">&mdash;</div><div class="sub">still to be cleared</div></div>
        <div class="s unb"><div class="lbl">In-Patients</div><div class="val" id="ib-stat-patients">&mdash;</div><div class="sub">currently admitted</div></div>
    </div>

    <!-- Billing table -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-file-invoice"></i> In-Patient Accounts</h2>
            <div class="flex items-center gap-2 wrap">
                <input type="text" id="ib-search" class="form-control" placeholder="Search patient, hospital no., admission or bed..." style="padding:6px 9px;border:1px solid #C9D4E0;border-radius:4px;font-size:12px;font-family:inherit;background:#fff;">
                <button class="btn btn-secondary btn-sm" id="ib-refresh-btn"><i class="fa-solid fa-rotate"></i> Refresh</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table id="ib-table">
                    <thead>
                        <tr>
                            <th>Admission No</th>
                            <th>Patient</th>
                            <th>Ward / Bed</th>
                            <th class="money">Total Bill (GH&#8373;)</th>
                            <th class="money">Paid Deposits (GH&#8373;)</th>
                            <th class="money">Balance (GH&#8373;)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ib-table-body">
                        <tr><td colspan="7" style="text-align:center;">Loading in-patient accounts...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Record Payment / Final Clearance Modal -->
<div class="modal" id="ib-pay-modal">
    <div class="modal-content">
        <div class="ib-head">
            <h3 id="ib-pay-title">RECORD DEPOSIT</h3>
            <div class="ib-head-sub" id="ib-pay-patient">&mdash;</div>
        </div>
        <div class="modal-body">
            <div class="ib-terms">
                <div class="tr"><span>Total bill</span><b id="ib-term-billed">&mdash;</b></div>
                <div class="tr"><span>Paid to date</span><b id="ib-term-paid">&mdash;</b></div>
                <div class="tr total"><span>Balance due</span><b id="ib-term-due">&mdash;</b></div>
            </div>

            <div class="ib-warn green" id="ib-clear-note">
                <i class="fa-solid fa-circle-check"></i>
                Recording a clearance payment settles this account. The patient stays admitted until they are
                discharged from the Admissions page.
            </div>

            <div class="ib-warn" id="ib-nobill-note">
                <i class="fa-solid fa-triangle-exclamation"></i>
                No invoice has been raised for this stay yet, so the balance shows GH&#8373; 0.00. Record the bill on
                Account Management first, or this payment will be a deposit against nothing.
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label for="ib-pay-type">PAYMENT TYPE *</label>
                <select id="ib-pay-type">
                    <option value="DEPOSIT">Deposit (running payment)</option>
                    <option value="CLEARANCE_PAYMENT">Final Clearance Payment</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group" style="margin-bottom:14px;">
                    <label for="ib-pay-amount">AMOUNT (GH&#8373;) *</label>
                    <input type="number" id="ib-pay-amount" min="0.01" step="0.01" placeholder="0.00">
                    <div class="ib-amount-hint" id="ib-amount-hint">Enter the amount received.</div>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label for="ib-pay-method">PAYMENT METHOD</label>
                    <select id="ib-pay-method">
                        <option value="">Not specified</option>
                        <option value="Cash">Cash</option>
                        <option value="Mobile Money">Mobile Money</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Card">Card</option>
                        <option value="Insurance / NHIA">Insurance / NHIA</option>
                        <option value="Sponsor">Sponsor</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label for="ib-pay-invoice">APPLY TO INVOICE</label>
                <select id="ib-pay-invoice">
                    <option value="">Oldest open invoice first</option>
                </select>
                <div class="ib-foot-note">Leave on automatic to let the payment settle the stay's open invoices in order.</div>
            </div>

            <div class="form-group">
                <label for="ib-pay-notes">NOTES</label>
                <textarea id="ib-pay-notes" rows="2" placeholder="Receipt number, teller, or any remark (optional)"></textarea>
            </div>

            <div id="ib-history">
                <div class="hd">
                    <span>PAYMENTS RECORDED FOR THIS STAY</span>
                    <span id="ib-history-count">0 payments</span>
                </div>
                <div class="bd" id="ib-history-body">
                    <div class="empty">Loading...</div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="ib-cancel-pay">Close</button>
            <button class="btn btn-primary" id="ib-save-pay"><i class="fa-solid fa-check"></i> Record Deposit</button>
        </div>
    </div>
</div>

<script>
let ibRows = [];
let ibPayments = [];
let ibTarget = null;
let ibSearchTimer = null;

function gh(amount) {
    const n = Number(amount || 0);
    return 'GH\u20B5 ' + n.toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function initIpdBilling() {
    document.getElementById('ib-refresh-btn').addEventListener('click', loadIpdBilling);
    document.getElementById('ib-cancel-pay').addEventListener('click', closePayModal);
    document.getElementById('ib-save-pay').addEventListener('click', savePayment);
    document.getElementById('ib-pay-type').addEventListener('change', onPayTypeChange);
    document.getElementById('ib-pay-amount').addEventListener('input', validateAmount);

    const back = document.getElementById('ib-back-to-admissions');
    if (back) back.addEventListener('click', () => navigateTo('admissions'));

    const search = document.getElementById('ib-search');
    if (search) {
        search.addEventListener('input', () => {
            clearTimeout(ibSearchTimer);
            ibSearchTimer = setTimeout(loadIpdBilling, 300);
        });
    }

    loadIpdBilling();
}

async function loadIpdBilling() {
    const tbody = document.getElementById('ib-table-body');
    const q = document.getElementById('ib-search').value.trim();

    const params = new URLSearchParams();
    params.set('action', 'ipd_billing');
    if (q) params.set('q', q);

    try {
        const response = await fetch('/hms/backend/api/admission_payments.php?' + params.toString());
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load in-patient accounts');

        ibRows = data.rows || [];
        renderTotals(data.totals || {});
        renderIpdRows();
    } catch (error) {
        console.error('IPD billing load error:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Failed to load in-patient accounts</td></tr>';
    }
}

function renderTotals(totals) {
    setText('ib-stat-billed', gh(totals.billed));
    setText('ib-stat-paid', gh(totals.paid));
    setText('ib-stat-balance', gh(totals.balance));
    setText('ib-stat-patients', ibRows.length);
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

function renderIpdRows() {
    const tbody = document.getElementById('ib-table-body');
    if (!tbody) return;

    if (!ibRows.length) {
        tbody.innerHTML = '<tr><td colspan="7"><div id="ib-empty">No in-patient accounts found</div></td></tr>';
        return;
    }
    tbody.innerHTML = ibRows.map(r => {
        let balanceCell;
        if (r.no_bill) {
            balanceCell = '<span class="ib-bal none">No bill raised</span>';
        } else if (r.settled) {
            balanceCell = '<span class="ib-bal clear">0.00 &mdash; CLEARED</span>';
        } else {
            balanceCell = '<span class="ib-bal">' + gh(r.balance) + '</span>';
        }

        return '<tr>'
            + '<td><span class="ib-code">' + escHtml(r.admission_code) + '</span>'
                + '<div style="font-size:10.5px;color:#8A94A6;">' + fmtDateTime(r.admission_date) + '</div></td>'
            + '<td><span class="ib-name">' + escHtml(r.patient_name || '-') + '</span>'
                + '<div style="font-size:10.5px;color:#8A94A6;">' + escHtml(r.hospital_number || '-') + '</div></td>'
            + '<td>' + escHtml(r.ward_name || '-') + ' &middot; Bed ' + escHtml(r.bed_number || '-') + '</td>'
            + '<td class="money ib-total">' + gh(r.total_billed) + '</td>'
            + '<td class="money ib-paid">' + gh(r.total_paid) + '</td>'
            + '<td class="money">' + balanceCell + '</td>'
            + '<td><div class="ib-actions">'
                + '<button class="dep" data-ib-deposit="' + r.admission_id + '"><i class="fa-solid fa-plus"></i> Record Deposit</button>'
                + '<button class="clr" data-ib-clear="' + r.admission_id + '"><i class="fa-solid fa-hand-holding-dollar"></i> Final Clearance</button>'
                + '<button class="pmt" data-ib-history="' + r.admission_id + '"><i class="fa-solid fa-clock-rotate-left"></i> Payments</button>'
            + '</div></td>'
            + '</tr>';
    }).join('');
}

// Delegated clicks so re-rendering the table keeps the buttons working.
document.addEventListener('click', function (e) {
    const depBtn = e.target.closest('[data-ib-deposit]');
    if (depBtn) { openPayModal(depBtn.getAttribute('data-ib-deposit'), 'DEPOSIT'); return; }

    const clrBtn = e.target.closest('[data-ib-clear]');
    if (clrBtn) { openPayModal(clrBtn.getAttribute('data-ib-clear'), 'CLEARANCE_PAYMENT'); return; }

    const histBtn = e.target.closest('[data-ib-history]');
    if (histBtn) { openPayModal(histBtn.getAttribute('data-ib-history'), 'DEPOSIT'); }
});

async function openPayModal(admissionId, type) {
    const target = ibRows.find(r => String(r.admission_id) === String(admissionId));
    if (!target) return;
    ibTarget = target;

    document.getElementById('ib-pay-patient').textContent =
        (target.patient_name || 'Patient') + ' (' + (target.hospital_number || '-') + ')';
    document.getElementById('ib-term-billed').textContent = gh(target.total_billed);
    document.getElementById('ib-term-paid').textContent = gh(target.total_paid);
    document.getElementById('ib-term-due').textContent = gh(target.balance);

    document.getElementById('ib-pay-notes').value = '';
    document.getElementById('ib-pay-invoice').innerHTML = '<option value="">Oldest open invoice first</option>';

    document.getElementById('ib-pay-type').value = type;
    // A deposit starts blank; a clearance pre-fills the exact balance, which is
    // the figure the cashier is settling.
    document.getElementById('ib-pay-amount').value = type === 'CLEARANCE_PAYMENT' && target.balance > 0
        ? target.balance.toFixed(2) : '';

    onPayTypeChange();
    validateAmount();
    document.getElementById('ib-pay-modal').classList.add('show');
    await loadStayPayments(target.admission_id, target.patient_id);
    document.getElementById('ib-pay-amount').focus();
}

function closePayModal() {
    document.getElementById('ib-pay-modal').classList.remove('show');
    ibTarget = null;
}

function onPayTypeChange() {
    const isClearance = document.getElementById('ib-pay-type').value === 'CLEARANCE_PAYMENT';

    document.getElementById('ib-pay-title').textContent = isClearance ? 'FINAL CLEARANCE PAYMENT' : 'RECORD DEPOSIT';
    document.getElementById('ib-save-pay').innerHTML = isClearance
        ? '<i class="fa-solid fa-check"></i> Record Clearance Payment'
        : '<i class="fa-solid fa-check"></i> Record Deposit';

    document.getElementById('ib-clear-note').classList.toggle('show', isClearance);
    document.getElementById('ib-nobill-note').classList.toggle('show', !isClearance && !!ibTarget && ibTarget.no_bill);

    const due = ibTarget ? Number(ibTarget.balance) : 0;
    const dueEl = document.getElementById('ib-term-due');
    dueEl.classList.toggle('due', due > 0);
    dueEl.classList.toggle('clear', due <= 0);
}

function validateAmount() {
    if (!ibTarget) return;
    const raw = document.getElementById('ib-pay-amount').value;
    const hint = document.getElementById('ib-amount-hint');
    const amount = parseFloat(raw);
    const balance = Number(ibTarget.balance);
    const isClearance = document.getElementById('ib-pay-type').value === 'CLEARANCE_PAYMENT';

    if (!raw || isNaN(amount)) {
        hint.textContent = 'Enter the amount received.';
        hint.classList.remove('over');
        return;
    }
    if (amount > balance && balance > 0) {
        hint.textContent = 'This is more than the GH\u20B5 ' + balance.toFixed(2) + ' balance. The excess will show as a credit on the stay.';
        hint.classList.add('over');
        return;
    }
    hint.classList.remove('over');
    hint.textContent = isClearance
        ? 'Clearing GH\u20B5 ' + amount.toFixed(2) + ' settles the account.'
        : 'Leaves GH\u20B5 ' + Math.max(balance - amount, 0).toFixed(2) + ' outstanding.';
}

async function loadStayPayments(admissionId, patientId) {
    const body = document.getElementById('ib-history-body');
    const count = document.getElementById('ib-history-count');
    const invoiceSel = document.getElementById('ib-pay-invoice');
    body.innerHTML = '<div class="empty">Loading...</div>';

    try {
        const res = await fetch('/hms/backend/api/admission_payments.php?action=list&admission_id=' + admissionId);
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to load payments');

        ibPayments = data.payments || [];
        count.textContent = ibPayments.length + (ibPayments.length === 1 ? ' payment' : ' payments');

        if (!ibPayments.length) {
            body.innerHTML = '<div class="empty">No payments recorded for this stay yet.</div>';
        } else {
            body.innerHTML = '<table><thead><tr><th>Type</th><th>Method</th><th class="amt">Amount</th><th>Recorded</th></tr></thead><tbody>'
                + ibPayments.map(p =>
                    '<tr>'
                    + '<td><span class="pt ' + (p.payment_type === 'CLEARANCE_PAYMENT' ? 'clr' : 'dep') + '">'
                        + escHtml(p.payment_type === 'CLEARANCE_PAYMENT' ? 'Clearance' : 'Deposit') + '</span>'
                        + (p.invoice_number ? '<div class="meta">' + escHtml(p.invoice_number) + '</div>' : '') + '</td>'
                    + '<td>' + escHtml(p.payment_method || '—') + (p.notes ? '<div class="meta">' + escHtml(p.notes) + '</div>' : '') + '</td>'
                    + '<td class="amt">' + gh(p.amount) + '</td>'
                    + '<td><span class="meta" style="margin:0;">' + fmtDateTime(p.created_at) + '<br>' + escHtml(p.recorded_by_name || '') + '</span></td>'
                    + '</tr>'
                ).join('')
                + '</tbody></table>';
        }

        // Offer the stay's open invoices so a payment can be tied to one.
        const inv = await fetch('/hms/backend/api/invoices.php?action=list&status=pending&q=');
        const invData = await inv.json();
        const mine = (invData.invoices || []).filter(i => String(i.patient_id) === String(patientId));
        invoiceSel.innerHTML = '<option value="">Oldest open invoice first</option>'
            + mine.map(i => '<option value="' + i.id + '">' + escHtml(i.invoice_number) + ' &mdash; ' + gh(i.net_amount) + '</option>').join('');
    } catch (error) {
        body.innerHTML = '<div class="empty">Could not load payments — ' + escHtml(error.message) + '</div>';
    }
}

async function savePayment() {
    if (!ibTarget) return;
    const amount = parseFloat(document.getElementById('ib-pay-amount').value);
    if (!amount || amount <= 0) { showAlert('Enter the amount received', 'error'); return; }

    const type = document.getElementById('ib-pay-type').value;
    const isClearance = type === 'CLEARANCE_PAYMENT';
    const balance = Number(ibTarget.balance);

    if (isClearance && amount < balance) {
        const proceed = confirm('This clearance is GH\u20B5 ' + amount.toFixed(2)
            + ' but the balance is GH\u20B5 ' + balance.toFixed(2) + '.'
            + '\n\nThe account will still show GH\u20B5 ' + (balance - amount).toFixed(2) + ' outstanding. Record it anyway?');
        if (!proceed) return;
    }

    const btn = document.getElementById('ib-save-pay');
    btn.disabled = true;
    try {
        const res = await fetch('/hms/backend/api/admission_payments.php?action=record', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                admission_id: ibTarget.admission_id,
                amount: amount,
                payment_type: type,
                payment_method: document.getElementById('ib-pay-method').value,
                invoice_id: document.getElementById('ib-pay-invoice').value || null,
                notes: document.getElementById('ib-pay-notes').value.trim()
            })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not record the payment');

        showAlert((isClearance ? 'Clearance payment' : 'Deposit') + ' of ' + gh(amount) + ' recorded'
            + (data.balance > 0 ? ' — GH\u20B5 ' + data.balance.toFixed(2) + ' still outstanding' : ' — account cleared'), 'success');
        closePayModal();
        await loadIpdBilling();
    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        btn.disabled = false;
    }
}
</script>
