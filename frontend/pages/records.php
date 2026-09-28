<div class="card">
    <div class="card-header">
        <h2>Patient Record Management</h2>
        <button class="btn btn-primary btn-sm" id="register-patient-btn">Register Patient</button>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <input type="text" id="search-patients" placeholder="Search by name, hospital number, or phone...">
            </div>
            <div class="form-group">
                <select id="filter-sponsor">
                    <option value="">All Sponsors</option>
                </select>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Hospital No.</th>
                        <th>Name</th>
                        <th>Age/Gender</th>
                        <th>Phone</th>
                        <th>Sponsor</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="patients-table">
                    <tr>
                        <td colspan="7" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Patient Registration Modal -->
<div class="modal" id="patient-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="patient-modal-title">Register Patient</h3>
            <button class="modal-close" id="close-patient-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="patient-form">
                <input type="hidden" id="patient-id">
                
                <h4>Personal Information</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient-firstname">First Name *</label>
                        <input type="text" id="patient-firstname" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="patient-middlename">Middle Name</label>
                        <input type="text" id="patient-middlename" name="middle_name">
                    </div>
                    <div class="form-group">
                        <label for="patient-lastname">Last Name *</label>
                        <input type="text" id="patient-lastname" name="last_name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient-dob">Date of Birth *</label>
                        <input type="date" id="patient-dob" name="date_of_birth" required>
                    </div>
                    <div class="form-group">
                        <label for="patient-gender">Gender *</label>
                        <select id="patient-gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="patient-bloodgroup">Blood Group</label>
                        <select id="patient-bloodgroup" name="blood_group">
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                </div>
                
                <h4>Contact Information</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient-phone">Phone</label>
                        <input type="tel" id="patient-phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="patient-email">Email</label>
                        <input type="email" id="patient-email" name="email">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="patient-address">Address</label>
                    <textarea id="patient-address" name="address" rows="2"></textarea>
                </div>
                
                <h4>Emergency Contact</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="emergency-name">Emergency Contact Name</label>
                        <input type="text" id="emergency-name" name="emergency_contact_name">
                    </div>
                    <div class="form-group">
                        <label for="emergency-phone">Emergency Contact Phone</label>
                        <input type="tel" id="emergency-phone" name="emergency_contact_phone">
                    </div>
                </div>
                
                <h4>Insurance/Sponsor</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient-sponsor">Sponsor</label>
                        <select id="patient-sponsor" name="sponsor_id">
                            <option value="">Select Sponsor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="patient-nhia">NHIA Number</label>
                        <input type="text" id="patient-nhia" name="nhia_number">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Patient</button>
                    <button type="button" class="btn btn-secondary" id="cancel-patient">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- New Visit Modal -->
<div class="modal" id="visit-modal">
    <div class="modal-content" style="max-width:560px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-door-open"></i> New Visit</h3>
            <button class="modal-close" id="close-visit-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="visit-form">
                <input type="hidden" id="visit-patient-id">
                <div class="form-group">
                    <label>Patient</label>
                    <div id="visit-patient-display" style="background:#F0F4F8;border:1px solid #DCE4EC;border-radius:6px;padding:10px 12px;font-size:13px;font-weight:700;color:#0F2D59;"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="visit-type">Visit Type *</label>
                        <select id="visit-type" required>
                            <option value="">Select Visit Type</option>
                            <option value="opd">OPD (Outpatient)</option>
                            <option value="ipd">IPD (Inpatient)</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="visit-department">Department *</label>
                        <select id="visit-department" required>
                            <option value="">Select Department</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="visit-complaint">Chief Complaint</label>
                    <textarea id="visit-complaint" rows="2" placeholder="Presenting complaint..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Start Visit</button>
                    <button type="button" class="btn btn-secondary" id="cancel-visit">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let patientsData = [];
let sponsorsData = [];

async function initRecords() {
    await loadSponsors();
    await loadDepartments();
    await loadPatients();
    setupEventListeners();
}

async function loadSponsors() {
    try {
        const response = await fetch('/hms/backend/api/sponsors.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            sponsorsData = data.sponsors;
            
            const filterSelect = document.getElementById('filter-sponsor');
            const patientSelect = document.getElementById('patient-sponsor');
            
            sponsorsData.forEach(sponsor => {
                filterSelect.innerHTML += `<option value="${sponsor.id}">${sponsor.name}</option>`;
                patientSelect.innerHTML += `<option value="${sponsor.id}">${sponsor.name}</option>`;
            });
        }
    } catch (error) {
        console.error('Sponsors load error:', error);
    }
}

async function loadPatients(filters = {}) {
    try {
        const queryParams = new URLSearchParams(filters);
        const response = await fetch(`/hms/backend/api/patients.php?${queryParams}`);
        const data = await response.json();
        
        if (data.success) {
            patientsData = data.patients;
            renderPatientsTable();
        }
    } catch (error) {
        console.error('Patients load error:', error);
    }
}

function renderPatientsTable() {
    const tbody = document.getElementById('patients-table');
    
    if (!patientsData || patientsData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No patients found</td></tr>';
        return;
    }
    
    tbody.innerHTML = patientsData.map(patient => {
        const age = calculateAge(patient.date_of_birth);
        return `
            <tr>
                <td><strong>${patient.hospital_number}</strong></td>
                <td>${patient.first_name} ${patient.middle_name ? patient.middle_name + ' ' : ''}${patient.last_name}</td>
                <td>${age} yrs / ${patient.gender}</td>
                <td>${patient.phone || '-'}</td>
                <td>${patient.sponsor_name || 'Self-Pay'}</td>
                <td>${formatDate(patient.registration_date)}</td>
                <td>
                    <button class="btn btn-sm btn-secondary" onclick="viewPatient(${patient.id})">View</button>
                    <button class="btn btn-sm btn-primary" onclick="createVisit(${patient.id})">New Visit</button>
                </td>
            </tr>
        `;
    }).join('');
}

function setupEventListeners() {
    document.getElementById('register-patient-btn').addEventListener('click', () => openPatientModal());
    document.getElementById('close-patient-modal').addEventListener('click', closePatientModal);
    document.getElementById('cancel-patient').addEventListener('click', closePatientModal);
    
    document.getElementById('patient-form').addEventListener('submit', handlePatientSubmit);
    
    document.getElementById('close-visit-modal').addEventListener('click', closeVisitModal);
    document.getElementById('cancel-visit').addEventListener('click', closeVisitModal);
    document.getElementById('visit-form').addEventListener('submit', handleVisitSubmit);
    
    document.getElementById('search-patients').addEventListener('input', debounce(function() {
        if (this.value.length >= 2) {
            loadPatients({ search: this.value });
        } else if (this.value.length === 0) {
            loadPatients();
        }
    }, 300));
    
    document.getElementById('filter-sponsor').addEventListener('change', function() {
        loadPatients({ sponsor_id: this.value });
    });
}

function openPatientModal(patient = null) {
    const modal = document.getElementById('patient-modal');
    const title = document.getElementById('patient-modal-title');
    const form = document.getElementById('patient-form');
    
    form.reset();
    document.getElementById('patient-id').value = '';
    
    if (patient) {
        title.textContent = 'Edit Patient';
        document.getElementById('patient-id').value = patient.id;
        document.getElementById('patient-firstname').value = patient.first_name;
        document.getElementById('patient-middlename').value = patient.middle_name || '';
        document.getElementById('patient-lastname').value = patient.last_name;
        document.getElementById('patient-dob').value = patient.date_of_birth;
        document.getElementById('patient-gender').value = patient.gender;
        document.getElementById('patient-bloodgroup').value = patient.blood_group || '';
        document.getElementById('patient-phone').value = patient.phone || '';
        document.getElementById('patient-email').value = patient.email || '';
        document.getElementById('patient-address').value = patient.address || '';
        document.getElementById('emergency-name').value = patient.emergency_contact_name || '';
        document.getElementById('emergency-phone').value = patient.emergency_contact_phone || '';
        document.getElementById('patient-sponsor').value = patient.sponsor_id || '';
        document.getElementById('patient-nhia').value = patient.nhia_number || '';
    } else {
        title.textContent = 'Register Patient';
    }
    
    modal.classList.add('show');
}

function closePatientModal() {
    document.getElementById('patient-modal').classList.remove('show');
}

async function handlePatientSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const patientId = document.getElementById('patient-id').value;
    
    try {
        const url = patientId 
            ? `/hms/backend/api/patients.php?id=${patientId}`
            : '/hms/backend/api/patients.php?action=create';
        
        const method = patientId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(patientId ? 'Patient updated successfully' : 'Patient registered successfully', 'success');
            closePatientModal();
            await loadPatients();
        } else {
            showAlert(result.error || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Patient save error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function viewPatient(patientId) {
    // TODO: Implement patient detail view
    alert('Patient detail view to be implemented');
}

function createVisit(patientId) {
    // Open the New Visit modal with the patient pre-selected
    const patient = patientsData.find(p => parseInt(p.id, 10) === patientId);
    if (!patient) {
        showAlert('Patient not found', 'error');
        return;
    }
    const display = `${patient.first_name} ${patient.middle_name ? patient.middle_name + ' ' : ''}${patient.last_name} — ${patient.hospital_number}`;
    document.getElementById('visit-form').reset();
    document.getElementById('visit-patient-id').value = patient.id;
    document.getElementById('visit-patient-display').textContent = display;
    document.getElementById('visit-modal').classList.add('show');
}

async function loadDepartments() {
    try {
        const response = await fetch('/hms/backend/api/users.php?action=departments');
        const data = await response.json();
        if (data.success) {
            const sel = document.getElementById('visit-department');
            (data.departments || []).forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.name;
                sel.appendChild(opt);
            });
        }
    } catch (error) {
        console.error('Departments load error:', error);
    }
}

function closeVisitModal() {
    document.getElementById('visit-modal').classList.remove('show');
}

async function handleVisitSubmit(e) {
    e.preventDefault();

    const patientId = document.getElementById('visit-patient-id').value;
    if (!patientId) {
        showAlert('Patient not selected', 'error');
        return;
    }

    const data = {
        patient_id: patientId,
        visit_type: document.getElementById('visit-type').value,
        department_id: document.getElementById('visit-department').value,
        chief_complaint: document.getElementById('visit-complaint').value
    };

    if (!data.visit_type || !data.department_id) {
        showAlert('Visit type and department are required', 'error');
        return;
    }

    try {
        const response = await fetch('/hms/backend/api/visits.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            const v = result.visit || {};
            showAlert(`Visit started — ${v.visit_number || ('Visit #' + result.visit_id)}`, 'success');
            closeVisitModal();
        } else {
            showAlert((result.error || (result.errors && Object.values(result.errors)[0])) || 'Visit creation failed', 'error');
        }
    } catch (error) {
        console.error('Visit create error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function calculateAge(dateOfBirth) {
    const today = new Date();
    const birthDate = new Date(dateOfBirth);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

function formatDate(dateString) {
    return fmtDate(dateString);
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