<style>
/* ============ USER MANAGEMENT : EHMS STANDALONE ============
   Scoped under #um-page. No Bootstrap — utilities defined here.
   Font Awesome (loaded by the shell) is used for the action icons. */
#um-page{background:#dce7f2;color:#333;font-size:13px;min-height:100vh;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;padding:0 0 30px}
#um-page *,#um-page *::before,#um-page *::after{box-sizing:border-box}
#um-page .um-topbar{background-color:#0b5fa5;color:#fff;padding:8px 20px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 5px rgba(0,0,0,0.15);flex-wrap:wrap;gap:8px}
#um-page .um-topbar .title-group{display:flex;align-items:center;gap:10px}
#um-page .um-topbar .title-group svg{width:24px;height:24px;fill:#ffffff}
#um-page .um-topbar .title{font-size:16px;font-weight:bold;letter-spacing:0.5px;text-transform:uppercase}
#um-page .um-topbar .right-nav{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
#um-page .um-topbar .hospital-tag{font-size:11px;color:#d1e5f7;margin-right:15px;font-weight:500}
#um-page .um-topbar .btn-nav{background-color:#0088cc;color:#fff;border:none;padding:5px 12px;font-size:11px;font-weight:bold;border-radius:3px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background-color 0.2s;font-family:inherit}
#um-page .um-topbar .btn-nav:hover{background-color:#006699}
#um-page .um-wrap{max-width:1000px;margin:20px auto;padding:0 15px}
#um-page .um-panel{background:#fff;border:1px solid #b2c8de;border-radius:4px;overflow:hidden;box-shadow:0 3px 8px rgba(0,0,0,0.06)}
#um-page .um-panel-head{background-color:#0b5fa5;color:#fff;padding:10px 15px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
#um-page .um-panel-head .um-title{font-weight:bold;font-size:13px;text-transform:uppercase;letter-spacing:0.4px;display:flex;align-items:center;gap:8px}
#um-page .um-panel-head .um-title i{color:#FFD54F}
#um-page .um-panel-head .um-actions{display:flex;gap:8px;flex-wrap:wrap}
#um-page .um-btn{border:none;border-radius:3px;cursor:pointer;font-weight:bold;font-size:11px;text-transform:uppercase;letter-spacing:0.4px;display:inline-flex;align-items:center;gap:6px;padding:7px 14px;font-family:inherit;transition:filter .15s}
#um-page .um-btn i{font-size:12px}
#um-page .um-btn-warning{background-color:#F39C12;color:#fff}
#um-page .um-btn-warning:hover{filter:brightness(1.08)}
#um-page .um-btn-primary{background-color:#0072BC;color:#fff}
#um-page .um-btn-primary:hover{filter:brightness(1.08)}
#um-page .um-toolbar{display:flex;gap:12px;flex-wrap:wrap;padding:14px 16px;border-bottom:1px solid #e1e8f0;background-color:#f8fafc}
#um-page .um-toolbar .um-field{flex:1 1 200px;min-width:180px}
#um-page .um-toolbar label{display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;margin-bottom:4px}
#um-page .um-toolbar input[type="text"],#um-page .um-toolbar select{width:100%;padding:7px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;font-family:inherit}
#um-page .um-toolbar input:focus,#um-page .um-toolbar select:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,0.25)}
#um-page .um-table-wrap{overflow-x:auto}
#um-page .um-table{width:100%;border-collapse:collapse;font-size:12px}
#um-page .um-table th{background-color:#e6eef5;color:#222;font-weight:bold;text-align:left;padding:9px 10px;border-bottom:2px solid #b2c8de;white-space:nowrap;text-transform:uppercase;font-size:11px;letter-spacing:0.3px}
#um-page .um-table td{padding:8px 10px;border-bottom:1px solid #e1e8f0;vertical-align:middle}
#um-page .um-table tbody tr:hover{background-color:#eef4fb}
#um-page .um-table .um-empty{text-align:center;color:#8a94a6;padding:16px}
#um-page .um-badge{display:inline-block;padding:2px 8px;border-radius:3px;font-weight:bold;font-size:10px;text-transform:uppercase;letter-spacing:0.3px}
#um-page .um-badge-role{background:#E8F4FD;color:#0D47A1}
#um-page .um-badge-active{background:#E9F9EF;color:#1E7A34}
#um-page .um-badge-inactive{background:#FDEBEC;color:#B23B3B}
#um-page .um-row-actions{display:flex;gap:6px;align-items:center;flex-wrap:wrap}
#um-page .um-a-btn{border:1px solid #b2c8de;background:#fff;color:#0b5fa5;border-radius:3px;padding:4px 10px;font-weight:bold;font-size:10px;cursor:pointer;display:inline-flex;align-items:center;gap:5px;font-family:inherit;text-transform:uppercase;text-decoration:none;transition:background .15s}
#um-page .um-a-btn:hover{background:#e2edf7}
#um-page .um-a-btn.danger{border-color:#f5c6cb;color:#B23B3B}
#um-page .um-a-btn.danger:hover{background:#FDEBEC}
#um-page .um-a-btn.warn{border-color:#f8d7a3;color:#B9770E}
#um-page .um-a-btn.warn:hover{background:#FEF5E0}
#um-page .um-a-btn i{font-size:11px}
/* ============ ADD / EDIT USER MODAL (scoped to #user-modal) ============ */
#user-modal{display:none;position:fixed;inset:0;z-index:2100;background:rgba(11,29,58,.55);align-items:center;justify-content:center;padding:24px 16px;overflow-y:auto}
#user-modal.show{display:flex}
#user-modal .modal-container{background:#fff;border-radius:10px;width:min(580px,100%);max-height:92vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 14px 40px rgba(0,0,0,.28)}
#user-modal .modal-header{background:linear-gradient(135deg,#0b3d66,#0b5fa5);color:#fff;padding:15px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-shrink:0}
#user-modal .modal-header span{font-size:15px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:9px}
#user-modal .modal-header span svg{width:17px;height:17px;fill:none;stroke:#fff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
#user-modal .modal-close-btn{background:transparent;border:none;color:#fff;font-size:24px;line-height:1;cursor:pointer;opacity:.85;padding:0 4px}
#user-modal .modal-close-btn:hover{opacity:1}
#user-modal .modal-body{padding:20px 22px;background:#F8FAFC;overflow-y:auto;flex:1}
#user-modal .form-group{margin-bottom:14px;min-width:0}
#user-modal .form-row{display:flex;gap:14px}
#user-modal .form-row .form-group{flex:1}
#user-modal label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#64748b;margin-bottom:5px}
#user-modal .form-control{width:100%;padding:9px 11px;border:1px solid #b2c8de;border-radius:4px;font-size:13px;background:#fff;color:#222;font-family:inherit;box-sizing:border-box}
#user-modal .form-control:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#user-modal .form-control[readonly]{background:#eef3f8;color:#555}
#user-modal .modern-date-wrapper{max-width:100%}
#user-modal .modern-date-input-group{display:flex;align-items:center;gap:8px;border:1px solid #b2c8de;border-radius:4px;background:#fff;padding:9px 11px}
#user-modal .modern-date-input-group svg{flex-shrink:0;color:#0b5fa5}
#user-modal .modern-date-value{font-size:13px;color:#222;font-weight:600}
#user-modal .signature-upload-box{margin-top:2px}
#user-modal .signature-dropzone{border:2px dashed #b2c8de;border-radius:6px;background:#fff;padding:18px 14px;display:flex;align-items:center;justify-content:center;gap:10px;color:#64748b;font-size:12px;cursor:pointer;text-align:center;transition:border-color .15s,background .15s}
#user-modal .signature-dropzone:hover{border-color:#0b5fa5;background:#eef4fb}
#user-modal .signature-dropzone svg{color:#0b5fa5;flex-shrink:0}
#user-modal .sig-file-name{margin-top:7px;font-size:12px;color:#1E7A34;display:none;font-weight:600}
#user-modal .modal-footer-btns{display:flex;justify-content:flex-end;gap:10px;margin-top:18px;padding-top:14px;border-top:1px solid #e1e8f0}
#user-modal .btn-save{background:#0b5fa5;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:800;font-size:12px;text-transform:uppercase;letter-spacing:.4px;padding:10px 22px;font-family:inherit;display:inline-flex;align-items:center;gap:6px}
#user-modal .btn-save:hover{background:#094d83}
#user-modal .btn-cancel{background:#fff;color:#34495E;border:1px solid #C0C0C0;border-radius:4px;cursor:pointer;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.4px;padding:10px 22px;font-family:inherit}
#user-modal .btn-cancel:hover{background:#F1F5F9}
@media (max-width:560px){#user-modal .form-row{flex-direction:column;gap:0}}
@media (max-width:640px){#um-page .um-topbar .hospital-tag{display:none}}
</style>

<div id="um-page">

  <!-- TOP BAR (HMS - HEALTHCARE MANAGEMENT SYSTEM branding) -->
  <div class="um-topbar">
    <div class="title-group">
      <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
      <div class="title">User Management</div>
    </div>
    <div class="right-nav">
      <span class="hospital-tag">HMS - HEALTHCARE MANAGEMENT SYSTEM</span>
      <a href="#" class="btn-nav" onclick="umNavHome(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>HOME</a>
      <a href="#" class="btn-nav" onclick="umGoBack(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>BACK</a>
      <a href="#" class="btn-nav" onclick="umGoPassword(event)"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>PASSWORD</a>
    </div>
  </div>

  <div class="um-wrap">

    <!-- USER MANAGEMENT PANEL -->
    <div class="um-panel">
      <div class="um-panel-head">
        <div class="um-title"><i class="fa fa-users"></i> User Management</div>
        <div class="um-actions">
          <button type="button" class="um-btn um-btn-warning" onclick="umManagePasswords()">
            <i class="fa fa-key"></i> Manage Passwords
          </button>
          <button type="button" class="um-btn um-btn-primary" id="add-user-btn">
            <i class="fa fa-user-plus"></i> Add User
          </button>
        </div>
      </div>

      <!-- SEARCH & FILTERS -->
      <div class="um-toolbar">
        <div class="um-field">
          <label for="search-users">Search</label>
          <input type="text" id="search-users" placeholder="Search users...">
        </div>
        <div class="um-field">
          <label for="filter-role">Role</label>
          <select id="filter-role">
            <option value="">All Roles</option>
            <option value="super_admin">Super Admin</option>
            <option value="admin">Admin</option>
            <option value="doctor">Doctor</option>
            <option value="nurse">Nurse</option>
            <option value="pharmacy">Pharmacy</option>
            <option value="lab">Laboratory</option>
            <option value="radiology">Radiology</option>
            <option value="records">Records</option>
            <option value="account">Account</option>
            <option value="revenue">Revenue</option>
            <option value="it">IT</option>
          </select>
        </div>
        <div class="um-field">
          <label for="filter-department">Department</label>
          <select id="filter-department">
            <option value="">All Departments</option>
          </select>
        </div>
      </div>

      <!-- USER TABLE -->
      <div class="um-table-wrap">
        <table class="um-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Username</th>
              <th>Role</th>
              <th>Department</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="users-table">
            <tr><td colspan="6" class="um-empty">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- ADD / EDIT USER MODAL -->
<div class="modal-overlay" id="user-modal">
  <div class="modal-container">
    <div class="modal-header">
      <span id="user-modal-title">
        <svg viewBox="0 0 24 24"><path d="M19 5v14H5V5h14zm0-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-3 6h-2v2h2v2h-2v2h-2v-2H8v2H6v-4h4V9h4V7h4v4z" fill="none" stroke="#fff" stroke-width="1.5"/></svg>
        <span id="user-modal-title-text">ADD USER</span>
      </span>
      <button class="modal-close-btn" id="close-user-modal">&times;</button>
    </div>

    <div class="modal-body">
      <form id="user-form">
        <input type="hidden" id="user-id">

        <div class="form-group">
          <label>FULL NAME *</label>
          <input type="text" class="form-control" id="user-fullname" name="full_name" placeholder="Enter full name" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>STAFF ID / REGISTRATION NO.</label>
            <input type="text" class="form-control" id="user-staff-id" name="staff_id" placeholder="e.g. STF-2026-001">
          </div>
          <div class="form-group">
            <label>CONTACT NUMBER</label>
            <input type="tel" class="form-control" id="user-phone" name="phone" placeholder="e.g. +233 54 000 0000">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>USERNAME *</label>
            <input type="text" class="form-control" id="user-username" name="username" placeholder="Enter username" required>
          </div>
          <div class="form-group">
            <label>EMAIL</label>
            <input type="email" class="form-control" id="user-email" name="email" placeholder="Enter email address">
          </div>
        </div>

        <div class="form-group">
          <label>PASSWORD *</label>
          <input type="password" class="form-control" id="user-password" name="password" placeholder="Enter temporary password" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>ROLE *</label>
            <select class="form-control" id="user-role" name="role" required>
              <option value="">Select Role</option>
              <option value="super_admin">Super Admin</option>
              <option value="admin">Admin</option>
              <option value="doctor">Doctor</option>
              <option value="nurse">Nurse</option>
              <option value="pharmacy">Pharmacy</option>
              <option value="lab">Laboratory</option>
              <option value="radiology">Radiology</option>
              <option value="records">Records</option>
              <option value="account">Account</option>
              <option value="revenue">Revenue</option>
              <option value="it">IT</option>
            </select>
          </div>
          <div class="form-group">
            <label>DEPARTMENT</label>
            <select class="form-control" id="user-department" name="department_id">
              <option value="">Select Department</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>ACCOUNT STATUS</label>
            <select class="form-control" id="user-status" name="is_active">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div class="form-group">
            <label>REGISTRATION DATE &amp; TIME</label>
            <div class="modern-date-wrapper">
              <div class="modern-date-input-group">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span class="modern-date-value" id="user-reg-date">&mdash;</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Signature Upload (visual only) -->
        <div class="form-group">
          <label>SIGNATURE (Digital Upload)</label>
          <div class="signature-upload-box">
            <input type="file" id="sigFile" accept="image/*" style="display:none;">
            <div class="signature-dropzone" onclick="document.getElementById('sigFile').click()">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
              <span>Click to upload signature image (PNG/JPG)</span>
            </div>
            <div class="sig-file-name" id="sig-file-name"></div>
          </div>
        </div>

        <div class="modal-footer-btns">
          <button type="submit" class="btn-save">SAVE USER</button>
          <button type="button" class="btn-cancel" id="cancel-user">CANCEL</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let usersData = [];
let departmentsData = [];

async function initUsers() {
    await loadDepartments();
    await loadUsers();
    setupEventListeners();
}

async function loadDepartments() {
    try {
        const response = await fetch('/hms/backend/api/users.php?action=departments');
        const data = await response.json();
        
        if (data.success) {
            departmentsData = data.departments;
            
            // Populate department dropdowns
            const filterSelect = document.getElementById('filter-department');
            const userSelect = document.getElementById('user-department');
            
            departmentsData.forEach(dept => {
                filterSelect.innerHTML += `<option value="${dept.id}">${dept.name}</option>`;
                userSelect.innerHTML += `<option value="${dept.id}">${dept.name}</option>`;
            });
        }
    } catch (error) {
        console.error('Departments load error:', error);
    }
}

async function loadUsers(filters = {}) {
    try {
        const queryParams = new URLSearchParams({ action: 'list', ...filters });
        const response = await fetch(`/hms/backend/api/users.php?${queryParams}`);
        const data = await response.json();
        
        if (data.success) {
            usersData = data.users;
            renderUsersTable();
        }
    } catch (error) {
        console.error('Users load error:', error);
    }
}

function renderUsersTable() {
    const tbody = document.getElementById('users-table');
    
    if (!usersData || usersData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="um-empty">No users found</td></tr>';
        return;
    }
    
    tbody.innerHTML = usersData.map(user => `
        <tr>
            <td>${user.full_name}</td>
            <td>${user.username}</td>
            <td><span class="um-badge um-badge-role">${user.role || '-'}</span></td>
            <td>${user.department_name || '-'}</td>
            <td><span class="um-badge ${user.is_active ? 'um-badge-active' : 'um-badge-inactive'}">${user.is_active ? 'Active' : 'Inactive'}</span></td>
            <td>
                <div class="um-row-actions">
                    <button type="button" class="um-a-btn" onclick="editUser(${user.id})"><i class="fa fa-pen"></i> Edit</button>
                    <button type="button" class="um-a-btn warn" title="Reset Password" onclick="umResetPassword('${user.username}')"><i class="fa fa-key"></i></button>
                    <button type="button" class="um-a-btn danger" onclick="deleteUser(${user.id})"><i class="fa fa-trash"></i></button>
                </div>
            </td>
        </tr>
    `).join('');
}

function setupEventListeners() {
    document.getElementById('add-user-btn').addEventListener('click', () => openUserModal());
    document.getElementById('close-user-modal').addEventListener('click', closeUserModal);
    document.getElementById('cancel-user').addEventListener('click', closeUserModal);
    
    document.getElementById('user-form').addEventListener('submit', handleUserSubmit);
    
    document.getElementById('sigFile').addEventListener('change', function() {
        const label = document.getElementById('sig-file-name');
        if (this.files && this.files.length) {
            label.textContent = '\u2713 ' + this.files[0].name;
            label.style.display = 'block';
        } else {
            label.textContent = '';
            label.style.display = 'none';
        }
    });
    
    document.getElementById('search-users').addEventListener('input', debounce(function() {
        loadUsers({ search: this.value });
    }, 300));
    
    document.getElementById('filter-role').addEventListener('change', function() {
        loadUsers({ role: this.value });
    });
    
    document.getElementById('filter-department').addEventListener('change', function() {
        loadUsers({ department_id: this.value });
    });
}

function formatRegDateTime(dt) {
    if (!dt) return '\u2014';
    const d = new Date(dt);
    if (isNaN(d.getTime())) return '\u2014';
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const pad = n => String(n).padStart(2, '0');
    return pad(d.getDate()) + '-' + months[d.getMonth()] + '-' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}

function openUserModal(user = null) {
    const modal = document.getElementById('user-modal');
    const titleText = document.getElementById('user-modal-title-text');
    const form = document.getElementById('user-form');
    
    form.reset();
    document.getElementById('user-id').value = '';
    document.getElementById('sig-file-name').style.display = 'none';
    document.getElementById('sig-file-name').textContent = '';
    document.getElementById('sigFile').value = '';
    
    // Registration date & time: now for a new user, created_at when editing
    document.getElementById('user-reg-date').textContent = formatRegDateTime(
        user ? (user.created_at || new Date()) : new Date()
    );
    
    if (user) {
        titleText.textContent = 'Edit User';
        document.getElementById('user-id').value = user.id;
        document.getElementById('user-fullname').value = user.full_name;
        document.getElementById('user-username').value = user.username;
        document.getElementById('user-email').value = user.email || '';
        document.getElementById('user-role').value = user.role;
        document.getElementById('user-department').value = user.department_id || '';
        document.getElementById('user-status').value = user.is_active ? '1' : '0';
        document.getElementById('user-staff-id').value = user.profile_staff_id || user.staff_id || '';
        document.getElementById('user-phone').value = user.profile_phone || '';
        document.getElementById('user-password').required = false;
    } else {
        titleText.textContent = 'Add User';
        document.getElementById('user-password').required = true;
    }
    
    modal.classList.add('show');
}

function closeUserModal() {
    document.getElementById('user-modal').classList.remove('show');
}

async function handleUserSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const userId = document.getElementById('user-id').value;
    
    if (!data.password) {
        delete data.password;
    }
    
    // Profile fields (staff_id, phone) live in user_profiles — nest them so the
    // backend routes them to createProfile/updateProfile.
    const profile = {};
    if (data.staff_id !== undefined) {
        profile.staff_id = data.staff_id.trim() !== '' ? data.staff_id.trim() : null;
    }
    if (data.phone !== undefined) {
        profile.phone = data.phone.trim() !== '' ? data.phone.trim() : null;
    }
    delete data.staff_id;
    delete data.phone;
    if (Object.keys(profile).length) {
        data.profile = profile;
    }
    
    try {
        const url = userId 
            ? `/hms/backend/api/users.php?id=${userId}`
            : '/hms/backend/api/users.php?action=create';
        
        const method = userId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(userId ? 'User updated successfully' : 'User created successfully', 'success');
            closeUserModal();
            await loadUsers();
        } else {
            showAlert(result.error || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('User save error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function editUser(userId) {
    const user = usersData.find(u => u.id === userId);
    if (user) {
        openUserModal(user);
    }
}

async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }
    
    try {
        const response = await fetch(`/hms/backend/api/users.php?id=${userId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('User deleted successfully', 'success');
            await loadUsers();
        } else {
            showAlert(result.error || 'Deletion failed', 'error');
        }
    } catch (error) {
        console.error('User delete error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/* ================= NAV (top bar) ================= */
function umNavHome(e) {
    if (e) e.preventDefault();
    if (window.navigateTo) window.navigateTo('dashboard');
    else if (window.loadPage) window.loadPage('dashboard');
}
function umGoBack(e) {
    if (e) e.preventDefault();
    if (window.goBackPage && typeof window.goBackPage === 'function') window.goBackPage();
    else if (window.navigateTo) window.navigateTo('dashboard');
}
function umGoPassword(e) {
    if (e) e.preventDefault();
    if (window.navigateTo) window.navigateTo('change-password');
    else if (window.loadPage) window.loadPage('change-password');
}
/* MANAGE PASSWORDS header button — routes to the Change Password console */
function umManagePasswords() {
    umGoPassword(null);
}
/* Per-row reset: remember the username, then open the Change Password console
   so its admin Reset panel can prefill the account. */
function umResetPassword(username) {
    window.__umResetUser = username || '';
    umGoPassword(null);
}
</script>
