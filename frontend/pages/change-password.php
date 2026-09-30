<?php
// Change Password - EHMS standalone style restyled to the pasted Bootstrap-5
// design (SPA fragment). Two flows: self-service change (current password
// verified) and admin username-based reset. Loaded by the shell into
// #page-content via loadPage('change-password'). Real backend:
// backend/api/auth.php (actions change_password / reset_password).
require_once __DIR__ . '/../../backend/config/config.php';
?>
<style>
/* ============ CHANGE PASSWORD : BOOTSTRAP-5 STANDALONE (SPA FRAGMENT) ============
   Scoped under #cp-page. The shell already provides container-fluid/row/
   col-md-6/card/d-flex/shadow-sm/border-0/font-weight-bold/text-uppercase
   and auto-appends the contact banner below every module page, so no support
   details are hardcoded here. Everything else matches the pasted design. */
#cp-page{background:#f8f9fa;color:#212529;font-size:14px;min-height:100vh;padding:.75rem 1rem;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}
#cp-page *,#cp-page *::before,#cp-page *::after{box-sizing:border-box}

/* ---------- Top header nav banner ---------- */
#cp-page .bg-primary{background-color:#0d6efd}
#cp-page .text-white{color:#fff}
#cp-page .text-primary{color:#0d6efd}
#cp-page .rounded{border-radius:.375rem}
#cp-page .shadow-sm{box-shadow:0 .125rem .25rem rgba(0,0,0,.075)}
#cp-page .align-items-center{align-items:center}
#cp-page .justify-content-between{justify-content:space-between}
#cp-page .justify-content-end{justify-content:flex-end}
#cp-page .gap-2{gap:.5rem}
#cp-page .gap-3{gap:1rem}
#cp-page .p-2{padding:.5rem}
#cp-page .p-4{padding:1.5rem}
#cp-page .ps-2{padding-left:.5rem}
#cp-page .pe-1{padding-right:.25rem}
#cp-page .py-2{padding-top:.5rem;padding-bottom:.5rem}
#cp-page .px-3{padding-left:1rem;padding-right:1rem}
#cp-page .px-4{padding-left:1.5rem;padding-right:1.5rem}
#cp-page .mb-0{margin-bottom:0}
#cp-page .mb-3{margin-bottom:1rem}
#cp-page .mb-4{margin-bottom:1.5rem}
#cp-page .me-1{margin-right:.25rem}
#cp-page .bg-white{background-color:#fff}
#cp-page .bg-light{background-color:#f8f9fa}
#cp-page .text-muted{color:#6c757d}
#cp-page .small{font-size:.85em}
#cp-page .fw-bold{font-weight:700}

/* ---------- Buttons ---------- */
#cp-page .btn{display:inline-flex;align-items:center;gap:6px;border:1px solid transparent;padding:.375rem .75rem;font-size:.875rem;font-weight:700;line-height:1.5;border-radius:.375rem;cursor:pointer;text-decoration:none;font-family:inherit;transition:background-color .15s ease-in-out,color .15s ease-in-out,box-shadow .15s ease-in-out}
#cp-page .btn-sm{padding:.25rem .5rem;font-size:.765625rem;border-radius:.25rem}
#cp-page .btn-primary{background-color:#0d6efd;color:#fff}
#cp-page .btn-primary:hover{background-color:#0b5ed7}
#cp-page .btn-secondary{background-color:#6c757d;color:#fff}
#cp-page .btn-secondary:hover{background-color:#5c636a}
#cp-page .btn-light{background-color:#f8f9fa;color:#0d6efd;border-color:#f8f9fa}
#cp-page .btn-light:hover{background-color:#e2e6ea;color:#0a58ca}

/* ---------- Cards ---------- */
#cp-page .card{position:relative;display:flex;flex-direction:column;min-width:0;word-wrap:break-word;background-color:#fff;background-clip:border-box;border:1px solid rgba(0,0,0,.125);border-radius:.375rem}
#cp-page .card-header{padding:.5rem 1rem;margin-bottom:0;background-color:rgba(0,0,0,.03);border-bottom:1px solid rgba(0,0,0,.125)}
#cp-page .card-header-main{background-color:#0d6efd;color:#fff}
#cp-page .card-header-sub{background-color:#0b5ed7;color:#fff}
#cp-page .card-body{flex:1 1 auto;padding:1rem}
#cp-page .border{border:1px solid #dee2e6 !important}
#cp-page .border-0{border:0 !important}
#cp-page .d-flex{display:flex}

/* ---------- Forms ---------- */
#cp-page .form-label{font-size:.85rem;font-weight:700;text-transform:uppercase;color:#495057;margin-bottom:.5rem;display:block}
#cp-page .input-group{display:flex;align-items:stretch;width:100%;position:relative}
#cp-page .input-group-text{display:flex;align-items:center;background-color:#fff;border:1px solid #ced4da;border-right:0;border-radius:.375rem 0 0 .375rem;padding:.375rem .75rem}
#cp-page .input-group-text svg{width:16px;height:16px;display:block}
#cp-page .form-control{display:block;width:100%;padding:.375rem .75rem;font-size:.875rem;font-weight:400;line-height:1.5;color:#212529;background-color:#fff;background-clip:padding-box;border:1px solid #ced4da;border-radius:.375rem;transition:border-color .15s ease-in-out,box-shadow .15s ease-in-out;font-family:inherit}
#cp-page .form-control:focus{outline:0;border-color:#86b7fe;box-shadow:0 0 0 .25rem rgba(13,110,253,.25)}
#cp-page .input-group .form-control{border-top-left-radius:0;border-bottom-left-radius:0}

/* ---------- Alerts ---------- */
#cp-page .alert{position:relative;padding:1rem;margin-bottom:1rem;border:1px solid transparent;border-radius:.375rem;font-size:.875rem}
#cp-page .alert-success{color:#0f5132;background-color:#d1e7dd;border-color:#badbcc}
#cp-page .alert-danger{color:#842029;background-color:#f8d7da;border-color:#f5c2c7}
#cp-page .alert-dismissible{padding-right:3rem}
#cp-page .btn-close{box-sizing:content-box;width:1em;height:1em;padding:1rem 1rem;position:absolute;top:0;right:0;color:#000;background:transparent;border:0;border-radius:.25rem;opacity:.5;cursor:pointer;font-size:1rem;line-height:1}

@media (max-width:640px){
  #cp-page .p-4{padding:1rem}
  #cp-page .px-4{padding-left:1rem;padding-right:1rem}
}
</style><div id="cp-page">

  <!-- TOP HEADER NAV BANNER -->
  <div class="d-flex justify-content-between align-items-center bg-primary text-white p-2 rounded mb-3 shadow-sm">
    <div class="d-flex align-items-center gap-2 ps-2">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
      </svg>
      <h5 class="fw-bold text-uppercase mb-0 text-white" style="font-size:15px;letter-spacing:.5px;">CHANGE PASSWORD</h5>
    </div>
    <div class="d-flex gap-2 pe-1">
      <a href="#" class="btn btn-sm btn-light fw-bold text-primary px-3" onclick="cpNavHome(event)">HOME</a>
      <a href="#" class="btn btn-sm btn-light fw-bold text-primary px-3" onclick="cpGoBack(event)">&lt; BACK</a>
      <a href="#" class="btn btn-sm btn-light fw-bold text-primary px-3" onclick="cpGoPassword(event)">PASSWORD</a>
    </div>
  </div>

  <!-- STATUS / ERROR NOTIFICATION MESSAGE (populated by JS) -->
  <div class="alert alert-dismissible shadow-sm" id="cpAlert" role="alert" style="display:none;">
    <span id="cpAlertMsg"></span>
    <button type="button" class="btn-close" aria-label="Close" onclick="document.getElementById('cpAlert').style.display='none';">&times;</button>
  </div>

  <!-- OUTER CONTAINER CARD -->
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header card-header-main d-flex justify-content-between align-items-center py-2 px-3">
      <span class="fw-bold text-uppercase" style="letter-spacing:.5px;font-size:15px;">CHANGE PASSWORD</span>
      <small style="color:#dbeafe;font-size:11px;">HMS - HEALTHCARE MANAGEMENT SYSTEM</small>
    </div>

    <div class="card-body p-4 bg-white">

      <!-- SECTION 1: CHANGE MY PASSWORD -->
      <div class="card border mb-4 shadow-sm">
        <div class="card-header card-header-sub py-2 px-3 fw-bold text-uppercase d-flex align-items-center" style="font-size:.9rem;letter-spacing:.4px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          CHANGE MY PASSWORD
        </div>
        <div class="card-body bg-light">
          <p class="text-muted small mb-3">Update your own logging-in password. You must enter your current password to confirm your identity before the new password is saved.</p>

          <form id="cpChangeForm" autocomplete="off">
            <!-- CURRENT PASSWORD -->
            <div class="mb-3">
              <label class="form-label" for="cpCurrent">CURRENT PASSWORD *</label>
              <div class="input-group">
                <span class="input-group-text"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></span>
                <input type="password" id="cpCurrent" class="form-control" placeholder="Enter current password" required>
              </div>
            </div>

            <!-- NEW & CONFIRM PASSWORD -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label" for="cpNew">NEW PASSWORD *</label>
                <div class="input-group">
                  <span class="input-group-text"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></span>
                  <input type="password" id="cpNew" class="form-control" placeholder="Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters" minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
                </div>
                <small class="text-muted">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="cpConfirm">CONFIRM NEW PASSWORD *</label>
                <div class="input-group">
                  <span class="input-group-text"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></span>
                  <input type="password" id="cpConfirm" class="form-control" placeholder="Re-enter new password" minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
                </div>
              </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="d-flex justify-content-end gap-2">
              <button type="reset" class="btn btn-secondary px-4 fw-bold">CANCEL</button>
              <button type="submit" class="btn btn-primary px-4 fw-bold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                SAVE PASSWORD
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- SECTION 2: RESET USER PASSWORD (ADMINISTRATOR) -->
      <div class="card border shadow-sm" id="cpResetPanel">
        <div class="card-header card-header-sub py-2 px-3 fw-bold text-uppercase d-flex align-items-center" style="font-size:.9rem;letter-spacing:.4px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          RESET USER PASSWORD (ADMINISTRATOR)
        </div>
        <div class="card-body bg-light">
          <p class="text-muted small mb-3">Administrator tool: set a new password for any user account. The user's current password is not required — the account is reset directly.</p>

          <form id="cpResetForm" autocomplete="off">
            <!-- USERNAME / USER ACCOUNT -->
            <div class="mb-3">
              <label class="form-label" for="cpUsername">USERNAME / USER ACCOUNT *</label>
              <div class="input-group">
                <span class="input-group-text"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span>
                <input type="text" id="cpUsername" class="form-control" placeholder="Enter username (e.g. admin)" required>
              </div>
            </div>

            <!-- NEW & CONFIRM RESET PASSWORD -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label" for="cpResetNew">NEW PASSWORD *</label>
                <div class="input-group">
                  <span class="input-group-text"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></span>
                  <input type="password" id="cpResetNew" class="form-control" placeholder="Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters" minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
                </div>
                <small class="text-muted">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="cpResetConfirm">CONFIRM NEW PASSWORD *</label>
                <div class="input-group">
                  <span class="input-group-text"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></span>
                  <input type="password" id="cpResetConfirm" class="form-control" placeholder="Re-enter new password" minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
                </div>
              </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="d-flex justify-content-end gap-2">
              <button type="reset" class="btn btn-secondary px-4 fw-bold">CANCEL</button>
              <button type="submit" class="btn btn-primary px-4 fw-bold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                RESET PASSWORD
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

</div>

<script>
/* ================= NAV (top bar) ================= */
function cpNavHome(e) { if (e) e.preventDefault(); if (window.navigateTo) window.navigateTo('dashboard'); else if (window.loadPage) window.loadPage('dashboard'); }
function cpGoBack(e) { if (e) e.preventDefault(); if (window.goBackPage && typeof window.goBackPage === 'function') window.goBackPage(); else if (window.navigateTo) window.navigateTo('dashboard'); }
function cpGoPassword(e) { if (e) e.preventDefault(); if (window.navigateTo) window.navigateTo('change-password'); else if (window.loadPage) window.loadPage('change-password'); }

/* ================= INLINE ALERT ================= */
function cpShowAlert(msg, type) {
    var el = document.getElementById('cpAlert');
    if (!el) return;
    el.className = 'alert alert-dismissible shadow-sm ' + (type === 'success' ? 'alert-success' : 'alert-danger');
    el.style.display = 'block';
    var span = document.getElementById('cpAlertMsg');
    if (span) span.innerHTML = msg;
}

function cpHideAlert() {
    var el = document.getElementById('cpAlert');
    if (el) el.style.display = 'none';
}

/* ================= INIT ================= */
function initChangePassword() {
    var changeForm = document.getElementById('cpChangeForm');
    var resetForm = document.getElementById('cpResetForm');
    if (changeForm) changeForm.addEventListener('submit', cpSubmitChange);
    if (resetForm) resetForm.addEventListener('submit', cpSubmitReset);
    // Prefill from the User Management page's per-row reset action (umResetPassword)
    if (window.__umResetUser) {
        var u = document.getElementById('cpUsername');
        if (u) u.value = window.__umResetUser;
        window.__umResetUser = null;
        var resetPanel = document.getElementById('cpResetPanel');
        if (resetPanel && resetPanel.scrollIntoView) resetPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (u) u.focus();
    }
}

/* ================= SELF-SERVICE CHANGE ================= */
function cpSubmitChange(e) {
    e.preventDefault();
    cpHideAlert();
    var current = document.getElementById('cpCurrent').value;
    var next = document.getElementById('cpNew').value;
    var confirm = document.getElementById('cpConfirm').value;

    if (!current || !next || !confirm) { cpShowAlert('Please fill in all fields.', 'danger'); return; }
    if (next !== confirm) { cpShowAlert('New password and confirmation password do not match.', 'danger'); return; }

    var btn = e.target.querySelector('button[type="submit"]');
    var original = btn ? btn.innerHTML : '';
    if (btn) btn.disabled = true;

    fetch('/hms/backend/api/auth.php?action=change_password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ current_password: current, new_password: next, confirm_password: confirm })
    })
    .then(function(r){ return r.json().then(function(d){ return { ok: r.ok, data: d }; }); })
    .then(function(res){
        if (btn) { btn.disabled = false; btn.innerHTML = original; }
        if (res.ok && res.data.success) {
            cpShowAlert('Your password has been updated successfully!', 'success');
            if (document.getElementById('cpChangeForm')) document.getElementById('cpChangeForm').reset();
        } else {
            cpShowAlert((res.data && res.data.error) || 'Failed to update password.', 'danger');
        }
    })
    .catch(function(err){
        if (btn) { btn.disabled = false; btn.innerHTML = original; }
        cpShowAlert('Network error: ' + err.message, 'danger');
    });
}

/* ================= ADMIN RESET ================= */
function cpSubmitReset(e) {
    e.preventDefault();
    cpHideAlert();
    var username = document.getElementById('cpUsername').value.trim();
    var next = document.getElementById('cpResetNew').value;
    var confirm = document.getElementById('cpResetConfirm').value;

    if (!username || !next || !confirm) { cpShowAlert('Please fill in all fields.', 'danger'); return; }
    if (next !== confirm) { cpShowAlert('New password and confirmation password do not match.', 'danger'); return; }

    var btn = e.target.querySelector('button[type="submit"]');
    var original = btn ? btn.innerHTML : '';
    if (btn) btn.disabled = true;

    fetch('/hms/backend/api/auth.php?action=reset_password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: username, new_password: next, confirm_password: confirm })
    })
    .then(function(r){ return r.json().then(function(d){ return { ok: r.ok, data: d }; }); })
    .then(function(res){
        if (btn) { btn.disabled = false; btn.innerHTML = original; }
        if (res.ok && res.data.success) {
            cpShowAlert('Password for user "<strong>' + (res.data.username || username) + '</strong>" has been reset successfully!', 'success');
            if (document.getElementById('cpResetForm')) document.getElementById('cpResetForm').reset();
        } else {
            cpShowAlert((res.data && res.data.error) || 'Failed to reset password.', 'danger');
        }
    })
    .catch(function(err){
        if (btn) { btn.disabled = false; btn.innerHTML = original; }
        cpShowAlert('Network error: ' + err.message, 'danger');
    });
}
</script>