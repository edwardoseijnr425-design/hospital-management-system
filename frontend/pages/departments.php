<div class="card">
    <div class="card-header">
        <h2>Departments</h2>
        <div>
            <button class="btn btn-primary btn-sm" id="add-department-btn" style="background-color:#0072BC; font-weight:700;">+ Add Department</button>
            <button class="btn btn-secondary btn-sm" id="refresh-departments-btn">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <input type="text" id="search-departments" placeholder="Search departments...">
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Head of Department</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="departments-table">
                    <tr>
                        <td colspan="7" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal" id="department-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="department-modal-title">Add New Department</h3>
            <button class="modal-close" id="close-department-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="department-form">
                <input type="hidden" id="department-id">
                <div class="form-group">
                    <label for="department-name">Department Name *</label>
                    <input type="text" id="department-name" name="name" required placeholder="e.g., Cardiology">
                </div>
                
                <div class="form-group">
                    <label for="department-code">Department Code *</label>
                    <input type="text" id="department-code" name="code" required placeholder="e.g., CARD" style="text-transform: uppercase;">
                </div>
                
                <div class="form-group">
                    <label for="department-description">Description</label>
                    <textarea id="department-description" name="description" rows="3" placeholder="Brief description of the department's role"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="department-head">Head of Department</label>
                    <select id="department-head" name="head_of_department">
                        <option value="">-- Select Head of Department --</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="department-status">Status</label>
                    <select id="department-status" name="status">
                        <option value="Active">Active</option>
                        <option value="Frozen">Frozen</option>
                        <option value="Deactivated">Deactivated</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="department-submit-btn" style="background-color:#0072BC; font-weight:700;">Save Department</button>
                    <button type="button" class="btn btn-secondary" id="cancel-department">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let departmentsData = [];
let departmentHeadsMap = {};

async function initDepartments() {
    setupEventListeners();
    loadDepartmentHeads();
    await loadDepartments();
}

async function loadDepartmentHeads() {
    try {
        const response = await fetch('/hms/backend/api/users.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('department-head');
            data.users.forEach(user => {
                departmentHeadsMap[user.id] = user.full_name;
                select.innerHTML += `<option value="${user.id}">${user.full_name} (${user.role})</option>`;
            });
        }
    } catch (error) {
        console.warn('Could not load users for head of department select:', error);
    }
}

async function loadDepartments() {
    try {
        const response = await fetch('/hms/backend/api/users.php?action=departments');
        const data = await response.json();
        
        if (data.success) {
            departmentsData = data.departments;
            renderDepartmentsTable('');
        }
    } catch (error) {
        console.error('Departments load error:', error);
        const tbody = document.getElementById('departments-table');
        if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Failed to load departments</td></tr>';
    }
}

function renderDepartmentsTable(query) {
    const tbody = document.getElementById('departments-table');
    if (!tbody) return;
    
    const q = (query || '').toLowerCase();
    const rows = departmentsData.filter(d =>
        !q || (d.name && d.name.toLowerCase().includes(q)) ||
        (d.code && d.code.toLowerCase().includes(q)) ||
        (d.description && d.description.toLowerCase().includes(q))
    );
    
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No departments found</td></tr>';
        return;
    }
    
    tbody.innerHTML = rows.map(d => {
        const status = d.status || 'Active';
        const statusTag = status === 'Frozen'
            ? '<span class="badge" style="background:#F39C12;color:#fff;">FROZEN</span>'
            : status === 'Deactivated'
                ? '<span class="badge" style="background:#DC3545;color:#fff;">DEACTIVATED</span>'
                : '<span class="badge" style="background:#28A745;color:#fff;">ACTIVE</span>';
        return `
        <tr>
            <td><strong>${d.code || '-'}</strong></td>
            <td style="color:#0F2D59; font-weight:700;">${d.name || '-'}</td>
            <td>${d.description || '-'}</td>
            <td>${departmentHeadsMap[d.head_of_department] || '-'}</td>
            <td>${statusTag}</td>
            <td>${fmtDate(d.created_at)}</td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="editDepartment(${d.id})">Edit</button>
            </td>
        </tr>`;
    }).join('');
}

function openDepartmentModal() {
    document.getElementById('department-form').reset();
    document.getElementById('department-id').value = '';
    document.getElementById('department-status').value = 'Active';
    document.getElementById('department-modal-title').textContent = 'Add New Department';
    document.getElementById('department-submit-btn').textContent = 'Save Department';
    document.getElementById('department-modal').classList.add('show');
}

// Pre-fill the modal for editing an existing department
function editDepartment(departmentId) {
    const dept = departmentsData.find(d => String(d.id) === String(departmentId));
    if (!dept) {
        showAlert('Department record not found', 'error');
        return;
    }
    document.getElementById('department-form').reset();
    document.getElementById('department-id').value = dept.id;
    document.getElementById('department-name').value = dept.name || '';
    document.getElementById('department-code').value = dept.code || '';
    document.getElementById('department-description').value = dept.description || '';
    document.getElementById('department-head').value = dept.head_of_department || '';
    document.getElementById('department-status').value = dept.status || 'Active';
    document.getElementById('department-modal-title').textContent = 'Edit Department - ' + dept.name;
    document.getElementById('department-submit-btn').textContent = 'Update Department';
    document.getElementById('department-modal').classList.add('show');
}

function closeDepartmentModal() {
    document.getElementById('department-modal').classList.remove('show');
}

async function handleDepartmentSubmit(e) {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const form = document.getElementById('department-form');
    const departmentId = document.getElementById('department-id').value;
    const isEdit = !!departmentId;
    const payload = {
        name: document.getElementById('department-name').value.trim(),
        code: document.getElementById('department-code').value.trim(),
        description: document.getElementById('department-description').value.trim(),
        head_of_department: document.getElementById('department-head').value || null,
        status: document.getElementById('department-status').value
    };
    
    try {
        const url = isEdit
            ? `/hms/backend/api/users.php?action=update_department&id=${departmentId}`
            : '/hms/backend/api/users.php?action=create_department';
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        
        if (data.success) {
            form.reset();
            closeDepartmentModal();
            showAlert(isEdit ? 'Department updated' : 'Department created', 'success');
            await loadDepartments();
        } else {
            showAlert(data.error || (isEdit ? 'Failed to update department' : 'Failed to create department'), 'error');
        }
    } catch (error) {
        console.error('Department save error:', error);
        showAlert(isEdit ? 'Failed to update department' : 'Failed to create department', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

function setupEventListeners() {
    const searchBox = document.getElementById('search-departments');
    if (searchBox) {
        searchBox.addEventListener('input', function() {
            renderDepartmentsTable(this.value);
        });
    }
    const refreshBtn = document.getElementById('refresh-departments-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', loadDepartments);
    }
    
    document.getElementById('add-department-btn').addEventListener('click', openDepartmentModal);
    document.getElementById('close-department-modal').addEventListener('click', closeDepartmentModal);
    document.getElementById('cancel-department').addEventListener('click', closeDepartmentModal);
    document.getElementById('department-form').addEventListener('submit', handleDepartmentSubmit);
}
</script>