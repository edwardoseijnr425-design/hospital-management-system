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
/* ============ ADD / EDIT USER MODAL (scoped) ============ */
#um-page .modal{display:none;position:fixed;inset:0;z-index:2000;background:rgba(15,45,89,.55);align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto}
#um-page .modal.show{display:flex}
#um-page .modal-content{background:#fff;border-radius:8px;width:min(560px,100%);box-shadow:0 12px 34px rgba(0,0,0,.25);overflow:hidden}
#um-page .modal-header{background-color:#0b5fa5;color:#fff;padding:13px 18px;display:flex;justify-content:space-between;align-items:center}
#um-page .modal-header h3{margin:0;font-size:14px;text-transform:uppercase;letter-spacing:.4px}
#um-page .modal-close{background:none;border:none;color:#fff;font-size:22px;line-height:1;cursor:pointer;padding:0 4px}
#um-page .modal-close:hover{color:#FFD54F}
#um-page .modal-body{padding:18px 20px;background:#F8FAFC}
#um-page .modal-body .form-group{margin-bottom:14px}
#um-page .modal-body label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#64748b;margin-bottom:5px}
#um-page .modal-body input,#um-page .modal-body select{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;background-color:#fff;color:#222;font-family:inherit;box-sizing:border-box}
#um-page .modal-body input:focus,#um-page .modal-body select:focus{outline:none;border-color:#0b5fa5;box-shadow:0 0 4px rgba(11,95,165,.25)}
#um-page .form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px;padding-top:14px;border-top:1px solid #e1e8f0}
#um-page .btn{border:none;border-radius:3px;cursor:pointer;font-weight:bold;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:8px 18px;font-family:inherit;display:inline-flex;align-items:center;gap:6px}
#um-page .btn-primary{background-color:#0072BC;color:#fff}
#um-page .btn-primary:hover{filter:brightness(1.08)}
#um-page .btn-secondary{background:#F1F5F9;color:#34495E;border:1px solid #C0C0C0}
#um-page .btn-secondary:hover{background:#E2E8F0}
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

<!-- User Modal -->
<div class="modal" id="user-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="user-modal-title">Add User</h3>
            <button class="modal-close" id="close-user-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="user-form">
                <input type="hidden" id="user-id">
                
                <div class="form-group">
                    <label for="user-fullname">Full Name *</label>
                    <input type="text" id="user-fullname" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="user-username">Username *</label>
                    <input type="text" id="user-username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="user-email">Email</label>
                    <input type="email" id="user-email" name="email">
                </div>
                
                <div class="form-group">
                    <label for="user-password">Password *</label>
                    <input type="password" id="user-password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="user-role">Role *</label>
                    <select id="user-role" name="role" required>
                        <option value="">Select Role</option>
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
                    <label for="user-department">Department</label>
                    <select id="user-department" name="department_id">
                        <option value="">Select Department</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save User</button>
                    <button type="button" class="btn btn-secondary" id="cancel-user">Cancel</button>
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

function openUserModal(user = null) {
    const modal = document.getElementById('user-modal');
    const title = document.getElementById('user-modal-title');
    const form = document.getElementById('user-form');
    
    form.reset();
    document.getElementById('user-id').value = '';
    
    if (user) {
        title.textContent = 'Edit User';
        document.getElementById('user-id').value = user.id;
        document.getElementById('user-fullname').value = user.full_name;
        document.getElementById('user-username').value = user.username;
        document.getElementById('user-email').value = user.email || '';
        document.getElementById('user-role').value = user.role;
        document.getElementById('user-department').value = user.department_id || '';
        document.getElementById('user-password').required = false;
    } else {
        title.textContent = 'Add User';
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
