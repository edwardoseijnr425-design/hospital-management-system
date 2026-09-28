<div class="card">
    <div class="card-header">
        <h2>User Management</h2>
        <button class="btn btn-primary btn-sm" id="add-user-btn">Add User</button>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <input type="text" id="search-users" placeholder="Search users...">
            </div>
            <div class="form-group">
                <select id="filter-role">
                    <option value="">All Roles</option>
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
                <select id="filter-department">
                    <option value="">All Departments</option>
                </select>
            </div>
        </div>
        
        <div class="table-container">
            <table>
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
                    <tr>
                        <td colspan="6" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
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
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No users found</td></tr>';
        return;
    }
    
    tbody.innerHTML = usersData.map(user => `
        <tr>
            <td>${user.full_name}</td>
            <td>${user.username}</td>
            <td><span class="badge badge-info">${user.role}</span></td>
            <td>${user.department_name || '-'}</td>
            <td><span class="badge ${user.is_active ? 'badge-success' : 'badge-danger'}">${user.is_active ? 'Active' : 'Inactive'}</span></td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="editUser(${user.id})">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">Delete</button>
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
</script>
