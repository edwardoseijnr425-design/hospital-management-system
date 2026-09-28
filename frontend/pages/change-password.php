<?php
// Change Password - EHMS standalone style (SPA fragment).
// Two flows: self-service change (current password verified) and admin
// username-based reset. Loaded by the shell into #page-content via
// loadPage('change-password'). Real backend: backend/api/auth.php
// (actions change_password / reset_password).
require_once __DIR__ . '/../../backend/config/config.php';
?>
<style>
/* ============ CHANGE PASSWORD : EHMS STANDALONE ============
   Scoped under #cp-page. No Bootstrap - utilities defined here.
   FontAwesome (loaded by the shell) used for action-button icons. */
#cp-page{background:#dce7f2;color:#333;font-size:13px;min-height:100vh;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;padding-bottom:30px}
#cp-page *,#cp-page *::before,#cp-page *::after{box-sizing:border-box}
#cp-page .top-bar{background-color:#0b5fa5;color:#fff;padding:8px 20px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 5px rgba(0,0,0,0.15);flex-wrap:wrap;gap:8px}
#cp-page .top-bar .title-group{display:flex;align-items:center;gap:10px}
#cp-page .top-bar .title-group svg{width:24px;height:24px;fill:#ffffff}
#cp-page .top-bar .title{font-size:16px;font-weight:bold;letter-spacing:0.5px;text-transform:uppercase}
#cp-page .top-bar .right-nav{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
#cp-page .top-bar .hospital-tag{font-size:11px;color:#d1e5f7;margin-right:15px;font-weight:500}
#cp-page .top-bar .btn-nav{background-color:#0088cc;color:#fff;border:none;padding:5px 12px;font-size:11px;font-weight:bold;border-radius:3px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background-color 0.2s;font-family:inherit}
#cp-page .top-bar .btn-nav:hover{background-color:#006699}
#cp-page .form-wrapper{max-width:760px;margin:20px auto;padding:0 15px;display:flex;flex-direction:column;gap:18px}
#cp-page .panel-box{background:#fff;border:1px solid #b2c8de;border-radius:4px;overflow:hidden;box-shadow:0 3px 8px rgba(0,0,0,0.06)}
#cp-page .panel-header{background-color:#0b5fa5;color:#fff;font-weight:bold;font-size:12px;padding:10px 15px;text-transform:uppercase;display:flex;align-items:center;gap:8px}
#cp-page .panel-header svg{width:16px;height:16px;fill:#ffffff}
#cp-page .panel-body{padding:18px 22px;background-color:#f8fafc}
#cp-page .form-section-title{font-size:12px;font-weight:bold;color:#0b5fa5;border-bottom:2px solid #0b5fa5;padding-bottom:4px;margin-bottom:15px;text-transform:uppercase}
#cp-page .grid-2col{display:grid;grid-template-columns:1fr 1fr;gap:15px 25px}
#cp-page .grid-full{grid-column:span 2}
#cp-page .form-group{display:flex;flex-direction:column;gap:5px}
#cp-page .form-group label{font-weight:600;color:#333;font-size:11px;text-transform:uppercase;letter-spacing:0.3px}
#cp-page .form-group label .required{color:#e74c3c;font-weight:bold}
#cp-page .input-wrapper{position:relative;display:flex;align-items:center}
#cp-page .input-wrapper svg{position:absolute;left:10px;width:15px;height:15px;fill:#0b5fa5;pointer-events:none}
#cp-page .input-wrapper input{padding-left:34px}
#cp-page .form-control{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;transition:border-color 0.2s, box-shadow 0.2s;font-family:inherit}
#cp-page .form-control:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,0.25)}
#cp-page .form-actions{margin-top:20px;padding-top:15px;border-top:1px solid #e1e8f0;display:flex;justify-content:flex-end;gap:12px;align-items:center}
#cp-page .btn-action{border:none;padding:8px 20px;font-weight:bold;font-size:12px;border-radius:3px;cursor:pointer;display:inline-flex;align-items:center;gap:7px;text-transform:uppercase;letter-spacing:0.5px;font-family:inherit;text-decoration:none}
#cp-page .btn-action i{font-size:13px}
#cp-page .btn-primary{background-color:#0b5fa5;color:#fff}
#cp-page .btn-primary:hover{background-color:#004080}
#cp-page .btn-secondary{background-color:#7f8c8d;color:#fff}
#cp-page .btn-secondary:hover{background-color:#636e72}
#cp-page .hint-note{font-size:11px;color:#64748b;margin-top:4px;line-height:1.5}
#cp-page .info-bar{background:#eef5fb;border-left:4px solid #0b5fa5;padding:10px 14px;border-radius:3px;font-size:12px;color:#0c4a75;margin-bottom:16px;line-height:1.5}
@media (max-width:640px){
  #cp-page .grid-2col{grid-template-columns:1fr}
  #cp-page .grid-full{grid-column:span 1}
}
</style><div id="cp-page">

  <!-- TOP BAR (HMS - HEALTHCARE MANAGEMENT SYSTEM branding) -->
  <div class="top-bar">
    <div class="title-group">
      <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
      <div class="title">CHANGE PASSWORD</div>
    </div>
    <div class="right-nav">
      <span class="hospital-tag">HMS - HEALTHCARE MANAGEMENT SYSTEM</span>
      <a href="#" class="btn-nav" onclick="cpNavHome(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>HOME</a>
      <a href="#" class="btn-nav" onclick="cpGoBack(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>BACK</a>
      <a href="#" class="btn-nav" onclick="cpGoPassword(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>PASSWORD</a>
    </div>
  </div>

  <div class="form-wrapper">

    <!-- ============ SELF-SERVICE : CHANGE MY PASSWORD ============ -->
    <div class="panel-box">
      <div class="panel-header">
        <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
        Change My Password
      </div>
      <div class="panel-body">
        <div class="info-bar">
          Update your own login password. You must enter your current password to confirm your identity before the new password is saved.
        </div>
        <form id="cpChangeForm" autocomplete="off">
          <div class="grid-2col">
            <div class="form-group grid-full">
              <label for="cpCurrent">Current Password <span class="required">*</span></label>
              <div class="input-wrapper">
                <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                <input type="password" id="cpCurrent" class="form-control" placeholder="Enter current password" required>
              </div>
            </div>
            <div class="form-group">
              <label for="cpNew">New Password <span class="required">*</span></label>
              <div class="input-wrapper">
                <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2z"/></svg>
                <input type="password" id="cpNew" class="form-control" placeholder="Minimum 8 characters" required>
              </div>
              <div class="hint-note">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters.</div>
            </div>
            <div class="form-group">
              <label for="cpConfirm">Confirm New Password <span class="required">*</span></label>
              <div class="input-wrapper">
                <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2z"/></svg>
                <input type="password" id="cpConfirm" class="form-control" placeholder="Re-enter new password" required>
              </div>
            </div>
          </div>
          <div class="form-actions">
            <button type="button" class="btn-action btn-secondary" onclick="cpCancel()"><i class="fa fa-undo"></i> CANCEL</button>
            <button type="submit" class="btn-action btn-primary"><i class="fa fa-lock"></i> SAVE PASSWORD</button>
          </div>
        </form>
      </div>
    </div>

    <!-- ============ ADMIN : RESET USER PASSWORD ============ -->
    <div class="panel-box">
      <div class="panel-header">
        <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
        Reset User Password (Administrator)
      </div>
      <div class="panel-body">
        <div class="info-bar">
          Administrator tool: set a new password for any user account. The user's current password is not required — the account is reset directly.
        </div>
        <form id="cpResetForm" autocomplete="off">
          <div class="grid-2col">
            <div class="form-group grid-full">
              <label for="cpUsername">Username / User Account <span class="required">*</span></label>
              <div class="input-wrapper">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                <input type="text" id="cpUsername" class="form-control" placeholder="e.g. admin" required>
              </div>
            </div>
            <div class="form-group">
              <label for="cpResetNew">New Password <span class="required">*</span></label>
              <div class="input-wrapper">
                <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2z"/></svg>
                <input type="password" id="cpResetNew" class="form-control" placeholder="Minimum 8 characters" required>
              </div>
              <div class="hint-note">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters.</div>
            </div>
            <div class="form-group">
              <label for="cpResetConfirm">Confirm New Password <span class="required">*</span></label>
              <div class="input-wrapper">
                <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2z"/></svg>
                <input type="password" id="cpResetConfirm" class="form-control" placeholder="Re-enter new password" required>
              </div>
            </div>
          </div>
          <div class="form-actions">
            <button type="button" class="btn-action btn-secondary" onclick="cpCancel()"><i class="fa fa-undo"></i> CANCEL</button>
            <button type="submit" class="btn-action btn-primary"><i class="fa fa-calendar-check"></i> RESET PASSWORD</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<script>
/* ================= NAV (top bar) ================= */
function cpNavHome(e) { if (e) e.preventDefault(); if (window.navigateTo) window.navigateTo('dashboard'); else if (window.loadPage) window.loadPage('dashboard'); }
function cpGoBack(e) { if (e) e.preventDefault(); if (window.goBackPage && typeof window.goBackPage === 'function') window.goBackPage(); else if (window.navigateTo) window.navigateTo('dashboard'); }
function cpGoPassword(e) { if (e) e.preventDefault(); if (window.navigateTo) window.navigateTo('change-password'); else if (window.loadPage) window.loadPage('change-password'); }
function cpCancel() { if (window.goBackPage && typeof window.goBackPage === 'function') window.goBackPage(); else if (window.navigateTo) window.navigateTo('dashboard'); }

/* ================= INIT ================= */
function initChangePassword() {
    var changeForm = document.getElementById('cpChangeForm');
    var resetForm = document.getElementById('cpResetForm');
    if (changeForm) changeForm.addEventListener('submit', cpSubmitChange);
    if (resetForm) resetForm.addEventListener('submit', cpSubmitReset);
}

/* ================= SELF-SERVICE CHANGE ================= */
function cpSubmitChange(e) {
    e.preventDefault();
    var current = document.getElementById('cpCurrent').value;
    var next = document.getElementById('cpNew').value;
    var confirm = document.getElementById('cpConfirm').value;

    if (!current || !next || !confirm) { showAlert('Please fill in all fields.', 'error'); return; }
    if (next !== confirm) { showAlert('New password and confirmation password do not match.', 'error'); return; }

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
        if (res.ok && res.data.success) {
            showAlert('Password updated successfully.', 'success');
            if (btn) { btn.disabled = false; btn.innerHTML = original; }
            cpChangeForm.reset();
            if (window.navigateTo) window.navigateTo('dashboard');
        } else {
            showAlert((res.data && res.data.error) || 'Failed to update password.', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = original; }
        }
    })
    .catch(function(err){
        showAlert('Network error: ' + err.message, 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = original; }
    });
}

/* ================= ADMIN RESET ================= */
function cpSubmitReset(e) {
    e.preventDefault();
    var username = document.getElementById('cpUsername').value.trim();
    var next = document.getElementById('cpResetNew').value;
    var confirm = document.getElementById('cpResetConfirm').value;

    if (!username || !next || !confirm) { showAlert('Please fill in all fields.', 'error'); return; }
    if (next !== confirm) { showAlert('New password and confirmation password do not match.', 'error'); return; }

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
        if (res.ok && res.data.success) {
            showAlert('Password reset successfully for "' + res.data.username + '".', 'success');
            if (btn) { btn.disabled = false; btn.innerHTML = original; }
            cpResetForm.reset();
        } else {
            showAlert((res.data && res.data.error) || 'Failed to reset password.', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = original; }
        }
    })
    .catch(function(err){
        showAlert('Network error: ' + err.message, 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = original; }
    });
}
</script>