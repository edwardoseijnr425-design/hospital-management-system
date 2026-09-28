<div class="card">
    <div class="card-header">
        <h2>Consultations</h2>
        <button class="btn btn-primary btn-sm" id="new-consultation-btn">New Consultation</button>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <select id="filter-consultation-status">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filter-consultation-type">
                    <option value="">All Types</option>
                    <option value="new">New</option>
                    <option value="follow_up">Follow-up</option>
                    <option value="emergency">Emergency</option>
                </select>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Hospital No.</th>
                        <th>Type</th>
                        <th>Doctor</th>
                        <th>Diagnosis</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="consultations-table">
                    <tr>
                        <td colspan="8" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Consultation Modal -->
<div class="modal" id="consultation-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="consultation-modal-title">New Consultation</h3>
            <button class="modal-close" id="close-consultation-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="consultation-form">
                <input type="hidden" id="consultation-id">
                
                <div class="form-group">
                    <label for="consultation-visit">Visit *</label>
                    <select id="consultation-visit" name="visit_id" required>
                        <option value="">Select Visit</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="consultation-type">Consultation Type *</label>
                    <select id="consultation-type" name="consultation_type" required>
                        <option value="">Select Type</option>
                        <option value="new">New</option>
                        <option value="follow_up">Follow-up</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="consultation-diagnosis">Diagnosis</label>
                    <textarea id="consultation-diagnosis" name="diagnosis" rows="3" placeholder="Enter diagnosis..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="consultation-notes">Clinical Notes</label>
                    <textarea id="consultation-notes" name="notes" rows="5" placeholder="Enter detailed clinical notes..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Consultation</button>
                    <button type="button" class="btn btn-secondary" id="cancel-consultation">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let consultationsData = [];

async function initConsultations() {
    await loadActiveVisits();
    await loadConsultations();
    setupEventListeners();
}

async function loadActiveVisits() {
    try {
        const response = await fetch('/hms/backend/api/visits.php?action=active');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('consultation-visit');
            data.visits.forEach(visit => {
                select.innerHTML += `<option value="${visit.id}">${visit.visit_number} - ${visit.patient_name}</option>`;
            });
        }
    } catch (error) {
        console.error('Active visits load error:', error);
    }
}

async function loadConsultations(filters = {}) {
    try {
        const queryParams = new URLSearchParams(filters);
        const response = await fetch(`/hms/backend/api/consultations.php?${queryParams}`);
        const data = await response.json();
        
        if (data.success) {
            consultationsData = data.consultations;
            renderConsultationsTable();
        }
    } catch (error) {
        console.error('Consultations load error:', error);
    }
}

function renderConsultationsTable() {
    const tbody = document.getElementById('consultations-table');
    
    if (!consultationsData || consultationsData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No consultations found</td></tr>';
        return;
    }
    
    tbody.innerHTML = consultationsData.map(consult => `
        <tr>
            <td>${formatDate(consult.consultation_date)}</td>
            <td>${consult.patient_name}</td>
            <td>${consult.hospital_number}</td>
            <td><span class="badge badge-info">${consult.consultation_type}</span></td>
            <td>${consult.doctor_name}</td>
            <td>${consult.diagnosis || '-'}</td>
            <td><span class="badge ${getStatusBadgeClass(consult.status)}">${consult.status}</span></td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="viewConsultation(${consult.id})">View</button>
                <button class="btn btn-sm btn-primary" onclick="editConsultation(${consult.id})">Edit</button>
            </td>
        </tr>
    `).join('');
}

function getStatusBadgeClass(status) {
    const classes = {
        'pending': 'badge-warning',
        'in_progress': 'badge-info',
        'completed': 'badge-success'
    };
    return classes[status] || 'badge-secondary';
}

function setupEventListeners() {
    document.getElementById('new-consultation-btn').addEventListener('click', () => openConsultationModal());
    document.getElementById('close-consultation-modal').addEventListener('click', closeConsultationModal);
    document.getElementById('cancel-consultation').addEventListener('click', closeConsultationModal);
    
    document.getElementById('consultation-form').addEventListener('submit', handleConsultationSubmit);
    
    document.getElementById('filter-consultation-status').addEventListener('change', function() {
        loadConsultations({ status: this.value });
    });
    
    document.getElementById('filter-consultation-type').addEventListener('change', function() {
        loadConsultations({ consultation_type: this.value });
    });
}

function openConsultationModal(consultation = null) {
    const modal = document.getElementById('consultation-modal');
    const title = document.getElementById('consultation-modal-title');
    const form = document.getElementById('consultation-form');
    
    form.reset();
    document.getElementById('consultation-id').value = '';
    
    if (consultation) {
        title.textContent = 'Edit Consultation';
        document.getElementById('consultation-id').value = consultation.id;
        document.getElementById('consultation-visit').value = consultation.visit_id;
        document.getElementById('consultation-type').value = consultation.consultation_type;
        document.getElementById('consultation-diagnosis').value = consultation.diagnosis || '';
        document.getElementById('consultation-notes').value = consultation.notes || '';
    } else {
        title.textContent = 'New Consultation';
    }
    
    modal.classList.add('show');
}

function closeConsultationModal() {
    document.getElementById('consultation-modal').classList.remove('show');
}

async function handleConsultationSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const consultationId = document.getElementById('consultation-id').value;
    
    try {
        const url = consultationId 
            ? `/hms/backend/api/consultations.php?id=${consultationId}`
            : '/hms/backend/api/consultations.php?action=create';
        
        const method = consultationId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(consultationId ? 'Consultation updated' : 'Consultation created', 'success');
            closeConsultationModal();
            await loadConsultations();
        } else {
            showAlert(result.error || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Consultation save error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function viewConsultation(consultationId) {
    alert('Consultation detail view to be implemented');
}

function editConsultation(consultationId) {
    const consultation = consultationsData.find(c => c.id === consultationId);
    if (consultation) {
        openConsultationModal(consultation);
    }
}

function formatDate(dateString) {
    return fmtDate(dateString);
}
</script>
