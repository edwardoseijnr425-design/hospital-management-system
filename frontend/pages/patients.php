<div class="card">
    <div class="card-header">
        <h2>Patient Management</h2>
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

<!-- Patient Identification Summary Modal (old EHMS patient bar) -->
<div class="modal" id="patient-summary-modal">
    <div class="modal-content" style="max-width:860px;">
        <div class="modal-header">
            <h3>PATIENT IDENTIFICATION</h3>
            <button class="modal-close" id="close-summary-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;">
                <div style="grid-column:span 2;border-left:3px solid #0072BC;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Patient Name</div>
                    <div style="font-size:16px;font-weight:800;color:#0F2D59;" id="patientBarName">-</div>
                </div>
                <div style="grid-column:span 2;border-left:3px solid #0072BC;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Hospital Number</div>
                    <div style="font-size:16px;font-weight:800;color:#0072BC;" id="patientBarId">-</div>
                </div>
                <div style="border-left:3px solid #80C342;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Gender</div>
                    <div style="font-size:14px;font-weight:700;" id="patientBarGender">-</div>
                </div>
                <div style="border-left:3px solid #80C342;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Age</div>
                    <div style="font-size:14px;font-weight:700;" id="patientBarAge">-</div>
                </div>
                <div style="grid-column:span 2;border-left:3px solid #F39C12;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Bed / Ward</div>
                    <div style="font-size:14px;font-weight:700;" id="patientBarBedWard">Not admitted</div>
                </div>
                <div style="border-left:3px solid #E53E3E;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Blood Pressure</div>
                    <div style="font-size:14px;font-weight:700;" id="patientBarBP">-</div>
                </div>
                <div style="border-left:3px solid #E53E3E;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Pulse</div>
                    <div style="font-size:14px;font-weight:700;" id="patientBarPulse">-</div>
                </div>
                <div style="border-left:3px solid #E53E3E;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">SpO2</div>
                    <div style="font-size:14px;font-weight:700;" id="patientBarSpO2">-</div>
                </div>
                <div style="grid-column:span 2;border-left:3px solid #9C27B0;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Allergies</div>
                    <div style="font-size:14px;" id="patientBarAllergies">None recorded</div>
                </div>
                <div style="grid-column:span 2;border-left:3px solid #0072BC;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Latest Diagnosis</div>
                    <div style="font-size:14px;" id="patientBarDiagnosis">-</div>
                </div>
                <div style="grid-column:span 2;border-left:3px solid #80C342;padding-left:8px;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.4px;color:#0F2D59;text-transform:uppercase;">Latest Prescription</div>
                    <div style="font-size:14px;" id="patientBarPrescription">-</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let patientsData = [];
let sponsorsData = [];

async function initPatients() {
    await loadSponsors();
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
    document.getElementById('close-summary-modal').addEventListener('click', () => {
        document.getElementById('patient-summary-modal').classList.remove('show');
    });
    
    document.getElementById('patient-form').addEventListener('submit', handlePatientSubmit);
    
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
    loadPatientSummary(patientId);
}

// Old EHMS patient identification bar (name / id / gender / age / bed-ward / BP / pulse / SpO2 / allergies / diagnosis / prescription)
async function loadPatientSummary(patientId) {
    try {
        const response = await fetch(`/hms/backend/api/patients.php?action=summary&id=${patientId}`);
        const data = await response.json();
        if (!data.success) {
            showAlert(data.error || 'Failed to load patient summary', 'error');
            return;
        }
        populatePatientIdentificationBar(data.patient);
        document.getElementById('patient-summary-modal').classList.add('show');
    } catch (error) {
        console.error('Patient summary load error:', error);
        showAlert('Failed to load patient summary', 'error');
    }
}

function populatePatientIdentificationBar(p) {
    const set = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = (val === null || val === undefined || val === '') ? '-' : val;
    };
    const vit = p.vitals || {};
    const has = v => (v !== null && v !== undefined && v !== '');
    set('patientBarName', p.full_name);
    set('patientBarId', p.hospital_number);
    set('patientBarGender', p.gender);
    set('patientBarAge', has(p.age) ? p.age + ' yrs' : '-');
    set('patientBarBedWard', p.bed_ward || 'Not admitted');
    set('patientBarBP', vit.blood_pressure || '-');
    set('patientBarPulse', has(vit.pulse) ? vit.pulse + ' bpm' : '-');
    set('patientBarSpO2', has(vit.spo2) ? vit.spo2 + '%' : '-');
    set('patientBarAllergies', 'None recorded');
    set('patientBarDiagnosis', p.diagnosis || 'No diagnosis recorded');
    set('patientBarPrescription', p.latest_rx || 'No prescription');
}

function createVisit(patientId) {
    // TODO: Implement visit creation
    alert('Visit creation to be implemented');
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
