<div class="card">
    <div class="card-header">
        <h2>Vital Signs</h2>
        <button class="btn btn-primary btn-sm" id="new-vitals-btn">Record Vitals</button>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <input type="text" id="search-vitals-patient" placeholder="Search patient...">
            </div>
            <div class="form-group">
                <select id="filter-vitals-date">
                    <option value="">All Dates</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Patient</th>
                        <th>Hospital No.</th>
                        <th>Temp (°C)</th>
                        <th>BP (mmHg)</th>
                        <th>HR (bpm)</th>
                        <th>RR (bpm)</th>
                        <th>SpO2 (%)</th>
                        <th>Weight (kg)</th>
                        <th>Nurse</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="vitals-table">
                    <tr>
                        <td colspan="10" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Vitals Modal -->
<div class="modal" id="vitals-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Record Vital Signs</h3>
            <button class="modal-close" id="close-vitals-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="vitals-form">
                <div class="form-group">
                    <label for="vitals-visit">Visit *</label>
                    <select id="vitals-visit" name="visit_id" required>
                        <option value="">Select Visit</option>
                    </select>
                </div>
                
                <h4>Vital Signs</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="vitals-temp">Temperature (°C)</label>
                        <input type="number" step="0.1" id="vitals-temp" name="temperature" placeholder="36.5">
                    </div>
                    <div class="form-group">
                        <label for="vitals-bp-sys">BP Systolic (mmHg)</label>
                        <input type="number" id="vitals-bp-sys" name="blood_pressure_systolic" placeholder="120">
                    </div>
                    <div class="form-group">
                        <label for="vitals-bp-dia">BP Diastolic (mmHg)</label>
                        <input type="number" id="vitals-bp-dia" name="blood_pressure_diastolic" placeholder="80">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="vitals-hr">Heart Rate (bpm)</label>
                        <input type="number" id="vitals-hr" name="heart_rate" placeholder="72">
                    </div>
                    <div class="form-group">
                        <label for="vitals-rr">Respiratory Rate (bpm)</label>
                        <input type="number" id="vitals-rr" name="respiratory_rate" placeholder="16">
                    </div>
                    <div class="form-group">
                        <label for="vitals-spo2">SpO2 (%)</label>
                        <input type="number" id="vitals-spo2" name="oxygen_saturation" placeholder="98">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="vitals-weight">Weight (kg)</label>
                        <input type="number" step="0.1" id="vitals-weight" name="weight" placeholder="70">
                    </div>
                    <div class="form-group">
                        <label for="vitals-height">Height (cm)</label>
                        <input type="number" step="0.1" id="vitals-height" name="height" placeholder="170">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="vitals-notes">Notes</label>
                    <textarea id="vitals-notes" name="notes" rows="2" placeholder="Additional observations..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Vitals</button>
                    <button type="button" class="btn btn-secondary" id="cancel-vitals">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let vitalsData = [];

async function initVitals() {
    await loadActiveVisits();
    await loadVitals();
    setupEventListeners();
}

async function loadActiveVisits() {
    try {
        const response = await fetch('/hms/backend/api/visits.php?action=active');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('vitals-visit');
            data.visits.forEach(visit => {
                select.innerHTML += `<option value="${visit.id}">${visit.visit_number} - ${visit.patient_name}</option>`;
            });
        }
    } catch (error) {
        console.error('Active visits load error:', error);
    }
}

async function loadVitals(filters = {}) {
    try {
        const queryParams = new URLSearchParams(filters);
        const response = await fetch(`/hms/backend/api/vitals.php?${queryParams}`);
        const data = await response.json();
        
        if (data.success) {
            vitalsData = data.vitals;
            renderVitalsTable();
        }
    } catch (error) {
        console.error('Vitals load error:', error);
    }
}

function renderVitalsTable() {
    const tbody = document.getElementById('vitals-table');
    
    if (!vitalsData || vitalsData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align: center;">No vital signs recorded</td></tr>';
        return;
    }
    
    tbody.innerHTML = vitalsData.map(vital => `
        <tr>
            <td>${formatDateTime(vital.recorded_at)}</td>
            <td>${vital.patient_name}</td>
            <td>${vital.hospital_number}</td>
            <td>${vital.temperature || '-'}</td>
            <td>${vital.blood_pressure_systolic && vital.blood_pressure_diastolic ? 
                vital.blood_pressure_systolic + '/' + vital.blood_pressure_diastolic : '-'}</td>
            <td>${vital.heart_rate || '-'}</td>
            <td>${vital.respiratory_rate || '-'}</td>
            <td>${vital.oxygen_saturation || '-'}</td>
            <td>${vital.weight || '-'}</td>
            <td>${vital.nurse_name}</td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="viewVitals(${vital.id})">View</button>
            </td>
        </tr>
    `).join('');
}

function setupEventListeners() {
    document.getElementById('new-vitals-btn').addEventListener('click', () => openVitalsModal());
    document.getElementById('close-vitals-modal').addEventListener('click', closeVitalsModal);
    document.getElementById('cancel-vitals').addEventListener('click', closeVitalsModal);
    
    document.getElementById('vitals-form').addEventListener('submit', handleVitalsSubmit);
    
    document.getElementById('filter-vitals-date').addEventListener('change', function() {
        loadVitals({ date_filter: this.value });
    });
}

function openVitalsModal() {
    document.getElementById('vitals-modal').classList.add('show');
    document.getElementById('vitals-form').reset();
}

function closeVitalsModal() {
    document.getElementById('vitals-modal').classList.remove('show');
}

async function handleVitalsSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('/hms/backend/api/vitals.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Vital signs recorded successfully', 'success');
            closeVitalsModal();
            await loadVitals();
        } else {
            showAlert(result.error || 'Failed to record vitals', 'error');
        }
    } catch (error) {
        console.error('Vitals save error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function viewVitals(vitalId) {
    const vital = vitalsData.find(v => v.id === vitalId);
    if (vital) {
        alert(`Vital Signs Details:\n\nTemperature: ${vital.temperature || 'N/A'}°C\nBP: ${vital.blood_pressure_systolic || 'N/A'}/${vital.blood_pressure_diastolic || 'N/A'} mmHg\nHeart Rate: ${vital.heart_rate || 'N/A'} bpm\nRespiratory Rate: ${vital.respiratory_rate || 'N/A'} bpm\nSpO2: ${vital.oxygen_saturation || 'N/A'}%\nWeight: ${vital.weight || 'N/A'} kg\nHeight: ${vital.height || 'N/A'} cm\nBMI: ${vital.bmi || 'N/A'}\nNotes: ${vital.notes || 'None'}`);
    }
}

function formatDateTime(dateString) {
    return fmtDateTime(dateString);
}
</script>
