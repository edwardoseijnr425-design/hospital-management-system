<?php
/**
 * Reusable NHIS status reconfirmation toolbar (SPA fragment partial).
 *
 * Include from a page fragment:
 *     <?php include __DIR__ . '/_nhis_verify_toolbar.php'; ?>
 *
 * Plain JS + scoped CSS: this project ships no Alpine.js and no Tailwind.
 * Backend: backend/api/verify_nhis.php (see that file for the response contract).
 *
 * Host pages that need the result can do either of these BEFORE the include:
 *   window.nhisToolbarOnResult = function (info) { ... };   // callback
 * or read window.nhisToolbarState after asking the user to verify:
 *   { nhisNumber, active, ccc, expiry, source, mock, message }
 */
?>
<style>
/* ============ NHIS RECONFIRMATION TOOLBAR ============
   Namespaced under .nhis-toolbar so it can be dropped into any page without
   colliding with the page's own global .form-group / .btn styles. */
.nhis-toolbar{background:#f6f9fc;border:1px solid #d3e0ec;border-radius:8px;padding:12px;margin:12px 0;font-size:12px;color:#334155}
.nhis-toolbar *,.nhis-toolbar *::before,.nhis-toolbar *::after{box-sizing:border-box}
.nhis-toolbar-head{display:flex;justify-content:space-between;align-items:center;gap:8px;border-bottom:1px solid #d3e0ec;padding-bottom:8px;margin-bottom:10px}
.nhis-toolbar-title{font-weight:700;font-size:11px;letter-spacing:.4px;text-transform:uppercase;color:#1b659d}
.nhis-toolbar-sub{font-size:10px;font-weight:600;color:#94a3b8;white-space:nowrap}
.nhis-toolbar-row{display:flex;align-items:stretch;gap:8px}
.nhis-toolbar-row input{flex:1;min-width:0;padding:8px 10px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;font-weight:600;color:#1e293b;font-size:12px;font-family:inherit}
.nhis-toolbar-row input:focus{outline:none;border-color:#1b659d;box-shadow:0 0 0 2px rgba(27,101,157,.18)}
.nhis-toolbar-btn{background:#1b659d;color:#fff;border:none;border-radius:6px;padding:8px 16px;font-weight:700;font-size:11px;letter-spacing:.4px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;font-family:inherit;transition:background-color .2s}
.nhis-toolbar-btn:hover{background:#155380}
.nhis-toolbar-btn[disabled]{opacity:.6;cursor:not-allowed}
.nhis-toolbar-btn .spinner{width:11px;height:11px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;animation:nhisTbSpin .7s linear infinite;display:none}
.nhis-toolbar-btn.is-busy .spinner{display:inline-block}
.nhis-toolbar-btn.is-busy .tick{display:none}
@keyframes nhisTbSpin{to{transform:rotate(360deg)}}
.nhis-toolbar-result{display:none;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;border-radius:6px;padding:9px 11px;margin-top:10px;font-size:11px}
.nhis-toolbar-result.is-visible{display:flex}
.nhis-toolbar-result .tb-icon{font-weight:700;font-size:13px;line-height:1}
.nhis-toolbar-result .tb-main{display:flex;align-items:center;gap:8px}
.nhis-toolbar-result .tb-title{font-weight:700;margin:0}
.nhis-toolbar-result .tb-note{margin:2px 0 0;font-size:10px;line-height:1.4}
.nhis-toolbar-active{background:#eaf7f0;border:1px solid #a8dcc0;color:#14663a}
.nhis-toolbar-active .tb-title,.nhis-toolbar-active .tb-icon{color:#14663a}
.nhis-toolbar-active .tb-note{color:#2f7a51}
.nhis-toolbar-inactive{background:#fdecea;border:1px solid #f0b4ae;color:#8e2b21}
.nhis-toolbar-inactive .tb-title,.nhis-toolbar-inactive .tb-icon{color:#8e2b21}
.nhis-toolbar-inactive .tb-note{color:#a4483c}
.nhis-toolbar-ccc{background:#fff;border:1px solid #a8dcc0;border-radius:6px;padding:4px 9px;text-align:right}
.nhis-toolbar-ccc .ccc-label{display:block;font-size:9px;letter-spacing:.4px;text-transform:uppercase;color:#94a3b8;font-weight:600}
.nhis-toolbar-ccc .ccc-value{font-weight:800;color:#1b659d;font-size:12px;letter-spacing:1px}
.nhis-toolbar-cash{background:#f7dcd8;border:1px solid #e2a79f;color:#8e2b21;font-size:10px;font-weight:700;padding:3px 8px;border-radius:4px;text-transform:uppercase;white-space:nowrap}
.nhis-toolbar-sim{color:#8a6d3b;font-style:italic}
@media (max-width:640px){.nhis-toolbar-row{flex-direction:column}.nhis-toolbar-btn{justify-content:center}}
</style>

<div class="nhis-toolbar" id="nhisToolbar">
    <div class="nhis-toolbar-head">
        <label class="nhis-toolbar-title" for="nhisToolbarInput">NHIS Status Verification &amp; Reconfirmation</label>
        <span class="nhis-toolbar-sub">NHIA Direct Link</span>
    </div>

    <div class="nhis-toolbar-row">
        <input type="text" id="nhisToolbarInput" placeholder="Enter NHIS Card Number..." autocomplete="off">
        <button type="button" id="nhisToolbarBtn" class="nhis-toolbar-btn" onclick="nhisToolbarVerify()">
            <span class="spinner"></span>
            <span class="tick">&#10004;</span>
            <span id="nhisToolbarLabel">VERIFY / RECONFIRM NHIS</span>
        </button>
    </div>

    <!-- Active: membership confirmed + Claim Check Code -->
    <div class="nhis-toolbar-result nhis-toolbar-active" id="nhisToolbarActiveBox">
        <div class="tb-main">
            <span class="tb-icon">&#10004;</span>
            <div>
                <p class="tb-title">NHIS Membership Active</p>
                <p class="tb-note">Covered for outpatient consultation &amp; prescribed drugs.
                    <span id="nhisToolbarSimNote" class="nhis-toolbar-sim"></span></p>
            </div>
        </div>
        <div class="nhis-toolbar-ccc">
            <span class="ccc-label">Claim Check Code:</span>
            <span class="ccc-value" id="nhisToolbarCcc">&mdash;</span>
        </div>
    </div>

    <!-- Inactive / expired: bill on the cash tariff -->
    <div class="nhis-toolbar-result nhis-toolbar-inactive" id="nhisToolbarInactiveBox">
        <div class="tb-main">
            <span class="tb-icon">&#10008;</span>
            <div>
                <p class="tb-title">NHIS Status: INACTIVE / EXPIRED</p>
                <p class="tb-note" id="nhisToolbarMsg">Patient card is expired or inactive. Charge as Out-of-Pocket Cash.</p>
            </div>
        </div>
        <span class="nhis-toolbar-cash">Pay Cash Tariff</span>
    </div>
</div>

<script>
/* NHIS reconfirmation toolbar. Globals are redefined on every page load, which
   is fine because runPageScripts() evals each fragment's script in the page scope. */
window.nhisToolbarState = { nhisNumber: '', active: false, ccc: '', expiry: '', source: '', mock: false, message: '' };

function nhisToolbarReset() {
    ['nhisToolbarActiveBox', 'nhisToolbarInactiveBox'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('is-visible');
    });
    var ccc = document.getElementById('nhisToolbarCcc');
    if (ccc) ccc.textContent = '\u2014';
    var note = document.getElementById('nhisToolbarSimNote');
    if (note) note.textContent = '';
    window.nhisToolbarState.active = false;
    window.nhisToolbarState.ccc = '';
    window.nhisToolbarState.expiry = '';
    window.nhisToolbarState.source = '';
    window.nhisToolbarState.mock = false;
    window.nhisToolbarState.message = '';
}

function nhisToolbarVerify() {
    var input = document.getElementById('nhisToolbarInput');
    var number = input ? input.value.trim() : '';

    if (!number) {
        if (window.showAlert) showAlert('Please enter a valid NHIS Card Number.', 'error');
        else alert('Please enter a valid NHIS Card Number.');
        return;
    }

    var btn = document.getElementById('nhisToolbarBtn');
    var label = document.getElementById('nhisToolbarLabel');

    nhisToolbarReset();
    window.nhisToolbarState.nhisNumber = number;
    if (btn) { btn.disabled = true; btn.classList.add('is-busy'); }
    if (label) label.textContent = 'Verifying...';

    var body = new FormData();
    body.append('nhis_number', number);

    fetch('/hms/backend/api/verify_nhis.php', { method: 'POST', body: body })
        .then(function (res) { return res.json(); })
        .then(function (data) { nhisToolbarRender(number, data); })
        .catch(function (err) {
            console.error('NHIS verification error:', err);
            if (window.showAlert) showAlert('Error reaching NHIS verification server.', 'error');
            else alert('Error reaching NHIS verification server.');
        })
        .finally(function () {
            if (btn) { btn.disabled = false; btn.classList.remove('is-busy'); }
            if (label) label.textContent = 'VERIFY / RECONFIRM NHIS';
        });
}

function nhisToolbarRender(number, data) {
    var state = window.nhisToolbarState;
    state.nhisNumber = number;

    // Backend contract: status === 'success' on active, 'error' otherwise.
    // ccc_code is accepted as an alias of claim_check_code.
    var ccc = data && (data.claim_check_code || data.ccc_code) || '';

    if (data && data.status === 'success' && ccc) {
        state.active = true;
        state.ccc = ccc;
        state.expiry = data.expiry_date || '';
        state.source = data.source || '';
        state.mock = !!data.mock;
        state.message = data.message || '';

        var cccEl = document.getElementById('nhisToolbarCcc');
        if (cccEl) cccEl.textContent = ccc;
        if (state.mock) {
            var note = document.getElementById('nhisToolbarSimNote');
            if (note) note.textContent = '(simulated \u2014 connect to real NHIA data before billing)';
        }
        var activeBox = document.getElementById('nhisToolbarActiveBox');
        if (activeBox) activeBox.classList.add('is-visible');
    } else {
        state.active = false;
        state.ccc = '';
        state.message = (data && data.message) || 'NHIS status could not be confirmed.';
        var msg = document.getElementById('nhisToolbarMsg');
        if (msg) msg.textContent = state.message;
        var inactiveBox = document.getElementById('nhisToolbarInactiveBox');
        if (inactiveBox) inactiveBox.classList.add('is-visible');
    }

    if (typeof window.nhisToolbarOnResult === 'function') {
        window.nhisToolbarOnResult(state);
    }
}
</script>
