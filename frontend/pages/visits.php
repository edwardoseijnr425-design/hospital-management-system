<div class="card">
    <div class="card-header">
        <h2>Patient Visits</h2>
        <button class="btn btn-primary btn-sm" id="new-visit-btn">New Visit</button>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <input type="text" id="search-patient-visit" placeholder="Search patient...">
            </div>
            <div class="form-group">
                <select id="filter-visit-status">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filter-visit-type">
                    <option value="">All Types</option>
                    <option value="opd">OPD</option>
                    <option value="ipd">IPD</option>
                    <option value="emergency">Emergency</option>
                </select>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Visit No.</th>
                        <th>Patient</th>
                        <th>Hospital No.</th>
                        <th>Type</th>
                        <th>Department</th>
                        <th>Date/Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="visits-table">
                    <tr>
                        <td colspan="8" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Visit Modal -->
<div class="modal" id="visit-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Visit</h3>
            <button class="modal-close" id="close-visit-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="visit-form">
                <div class="form-group">
                    <label for="visit-patient">Patient *</label>
                    <input type="text" id="visit-patient" placeholder="Search patient by name or hospital number..." required>
                    <input type="hidden" id="visit-patient-id">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="visit-type">Visit Type *</label>
                        <select id="visit-type" name="visit_type" required>
                            <option value="">Select Type</option>
                            <option value="opd">OPD (Outpatient)</option>
                            <option value="ipd">IPD (Inpatient)</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="visit-department">Department *</label>
                        <select id="visit-department" name="department_id" required>
                            <option value="">Select Department</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="visit-complaint">Chief Complaint</label>
                    <textarea id="visit-complaint" name="chief_complaint" rows="3" placeholder="Describe the patient's main complaint..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Visit</button>
                    <button type="button" class="btn btn-secondary" id="cancel-visit">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let visitsData = [];
let departmentsData = [];

async function initVisits() {
    await loadDepartments();
    await loadVisits();
    setupEventListeners();
}

async function loadDepartments() {
    try {
        const response = await fetch('/hms/backend/api/users.php?action=departments');
        const data = await response.json();
        
        if (data.success) {
            departmentsData = data.departments;
            
            const select = document.getElementById('visit-department');
            departmentsData.forEach(dept => {
                select.innerHTML += `<option value="${dept.id}">${dept.name}</option>`;
            });
        }
    } catch (error) {
        console.error('Departments load error:', error);
    }
}

async function loadVisits(filters = {}) {
    try {
        const queryParams = new URLSearchParams(filters);
        const response = await fetch(`/hms/backend/api/visits.php?${queryParams}`);
        const data = await response.json();
        
        if (data.success) {
            visitsData = data.visits;
            renderVisitsTable();
        }
    } catch (error) {
        console.error('Visits load error:', error);
    }
}

function renderVisitsTable() {
    const tbody = document.getElementById('visits-table');
    
    if (!visitsData || visitsData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No visits found</td></tr>';
        return;
    }
    
    tbody.innerHTML = visitsData.map(visit => `
        <tr>
            <td><strong>${visit.visit_number}</strong></td>
            <td>${visit.patient_name}</td>
            <td>${visit.hospital_number}</td>
            <td><span class="badge badge-info">${visit.visit_type.toUpperCase()}</span></td>
            <td>${visit.department_name}</td>
            <td>${formatDateTime(visit.visit_date)}</td>
            <td><span class="badge ${getStatusBadgeClass(visit.status)}">${visit.status}</span></td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="viewVisit(${visit.id})">View</button>
                <button class="btn btn-sm btn-primary" onclick="processVisit(${visit.id})">Process</button>
            </td>
        </tr>
    `).join('');
}

function getStatusBadgeClass(status) {
    const classes = {
        'pending': 'badge-warning',
        'in_progress': 'badge-info',
        'completed': 'badge-success',
        'cancelled': 'badge-danger'
    };
    return classes[status] || 'badge-secondary';
}

function setupEventListeners() {
    document.getElementById('new-visit-btn').addEventListener('click', () => openVisitModal());
    document.getElementById('close-visit-modal').addEventListener('click', closeVisitModal);
    document.getElementById('cancel-visit').addEventListener('click', closeVisitModal);
    
    document.getElementById('visit-form').addEventListener('submit', handleVisitSubmit);
    
    // Patient search with debounce
    let searchTimeout;
    document.getElementById('visit-patient').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value;
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => searchPatients(query), 300);
        }
    });
    
    document.getElementById('filter-visit-status').addEventListener('change', function() {
        loadVisits({ status: this.value });
    });
    
    document.getElementById('filter-visit-type').addEventListener('change', function() {
        loadVisits({ visit_type: this.value });
    });
}

async function searchPatients(query) {
    try {
        const response = await fetch(`/hms/backend/api/patients.php?action=search&q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success && data.patients.length > 0) {
            // Auto-select first result for now
            const patient = data.patients[0];
            document.getElementById('visit-patient-id').value = patient.id;
            document.getElementById('visit-patient').value = `${patient.hospital_number} - ${patient.first_name} ${patient.last_name}`;
        }
    } catch (error) {
        console.error('Patient search error:', error);
    }
}

function openVisitModal() {
    document.getElementById('visit-modal').classList.add('show');
    document.getElementById('visit-form').reset();
    document.getElementById('visit-patient-id').value = '';
}

function closeVisitModal() {
    document.getElementById('visit-modal').classList.remove('show');
}

async function handleVisitSubmit(e) {
    e.preventDefault();
    
    const patientId = document.getElementById('visit-patient-id').value;
    
    if (!patientId) {
        showAlert('Please select a patient', 'error');
        return;
    }
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.patient_id = patientId;
    
    try {
        const response = await fetch('/hms/backend/api/visits.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Visit created successfully', 'success');
            closeVisitModal();
            await loadVisits();
        } else {
            showAlert(result.error || 'Failed to create visit', 'error');
        }
    } catch (error) {
        console.error('Visit creation error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function viewVisit(visitId) {
    alert('Visit detail view to be implemented');
}

function processVisit(visitId) {
    alert('Visit processing workflow to be implemented');
}

function formatDateTime(dateString) {
    return fmtDateTime(dateString);
}
</script>
