<style>
/* Scoped styles for the patient picker in the Assign Bed modal. */
#assign-bed-modal .assign-results{margin-top:6px;max-height:190px;overflow-y:auto;border:1px solid #dfe6ee;border-radius:6px;background:#fff;}
#assign-bed-modal .assign-results:empty{display:none;}
#assign-bed-modal .assign-result{padding:8px 10px;font-size:12px;cursor:pointer;border-bottom:1px solid #f0f4f8;color:#334155;}
#assign-bed-modal .assign-result:last-child{border-bottom:none;}
#assign-bed-modal .assign-result:hover{background:#eef6fd;}
#assign-bed-modal .assign-hn{font-size:11px;color:#64748b;margin-left:4px;}
#assign-bed-modal .assign-empty{padding:8px 10px;font-size:12px;color:#94a3b8;}
</style>
<div class="card">
    <div class="card-header">
        <h2>Bed Management</h2>
        <button class="btn btn-primary btn-sm" id="add-bed-btn">Add Bed</button>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <select id="filter-bed-ward">
                    <option value="">All Wards</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filter-bed-type">
                    <option value="">All Types</option>
                    <option value="standard">Standard</option>
                    <option value="deluxe">Deluxe</option>
                    <option value="icu">ICU</option>
                    <option value="pediatric">Pediatric</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filter-bed-status">
                    <option value="">All Status</option>
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="reserved">Reserved</option>
                </select>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Bed Number</th>
                        <th>Ward</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Current Patient</th>
                        <th>Admission Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="beds-table">
                    <tr>
                        <td colspan="7" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bed Modal -->
<div class="modal" id="bed-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="bed-modal-title">Add Bed</h3>
            <button class="modal-close" id="close-bed-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="bed-form">
                <input type="hidden" id="bed-id">
                
                <div class="form-group">
                    <label for="bed-ward">Ward *</label>
                    <select id="bed-ward" name="ward_id" required>
                        <option value="">Select Ward</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="bed-number">Bed Number *</label>
                    <input type="text" id="bed-number" name="bed_number" required placeholder="e.g., B001">
                </div>
                
                <div class="form-group">
                    <label for="bed-type">Bed Type *</label>
                    <select id="bed-type" name="bed_type" required>
                        <option value="">Select Type</option>
                        <option value="standard">Standard</option>
                        <option value="deluxe">Deluxe</option>
                        <option value="icu">ICU</option>
                        <option value="pediatric">Pediatric</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Bed</button>
                    <button type="button" class="btn btn-secondary" id="cancel-bed">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Bed Modal -->
<div class="modal" id="assign-bed-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Assign Patient to <span id="assign-bed-label">Bed</span></h3>
            <button class="modal-close" id="close-assign-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="assign-bed-form">
                <input type="hidden" id="assign-bed-id">
                <div class="form-group">
                    <label for="assign-patient-search">Search Patient *</label>
                    <input type="text" id="assign-patient-search" placeholder="Type a name, hospital number or phone...">
                    <div id="assign-patient-results" class="assign-results"></div>
                </div>
                <div class="form-group">
                    <label>Selected Patient</label>
                    <input type="text" id="assign-patient-name" readonly placeholder="No patient selected">
                    <input type="hidden" id="assign-patient-id">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Assign Bed</button>
                    <button type="button" class="btn btn-secondary" id="cancel-assign">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let bedsData = [];
let wardsData = [];

async function initBeds() {
    await loadWards();
    await loadBeds();
    setupEventListeners();
}

async function loadWards() {
    try {
        const response = await fetch('/hms/backend/api/wards.php');
        const data = await response.json();
        
        if (data.success) {
            wardsData = data.wards;
            
            const filterSelect = document.getElementById('filter-bed-ward');
            const bedSelect = document.getElementById('bed-ward');
            
            wardsData.forEach(ward => {
                filterSelect.innerHTML += `<option value="${ward.id}">${ward.name} (${ward.code})</option>`;
                bedSelect.innerHTML += `<option value="${ward.id}">${ward.name} (${ward.code})</option>`;
            });
        }
    } catch (error) {
        console.error('Wards load error:', error);
    }
}

async function loadBeds(filters = {}) {
    try {
        const queryParams = new URLSearchParams(filters);
        const response = await fetch(`/hms/backend/api/beds.php?${queryParams}`);
        const data = await response.json();
        
        if (data.success) {
            bedsData = data.beds;
            renderBedsTable();
        }
    } catch (error) {
        console.error('Beds load error:', error);
    }
}

function renderBedsTable() {
    const tbody = document.getElementById('beds-table');
    
    if (!bedsData || bedsData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No beds found</td></tr>';
        return;
    }
    
    tbody.innerHTML = bedsData.map(bed => `
        <tr>
            <td><strong>${bed.bed_number}</strong></td>
            <td>${bed.ward_name}</td>
            <td><span class="badge badge-info">${bed.bed_type}</span></td>
            <td><span class="badge ${getBedStatusBadgeClass(bed.status)}">${bed.status}</span></td>
            <td>${bed.current_patient || '-'}</td>
            <td>${bed.admission_date ? formatDate(bed.admission_date) : '-'}</td>
            <td>
                ${bed.status === 'available' ? 
                    `<button class="btn btn-sm btn-primary" onclick="assignBed(${bed.id})">Assign</button>` :
                    `<button class="btn btn-sm btn-warning" onclick="dischargeBed(${bed.id})">Discharge</button>`
                }
                <button class="btn btn-sm btn-secondary" onclick="editBed(${bed.id})">Edit</button>
            </td>
        </tr>
    `).join('');
}

function getBedStatusBadgeClass(status) {
    const classes = {
        'available': 'badge-success',
        'occupied': 'badge-danger',
        'maintenance': 'badge-warning',
        'reserved': 'badge-info'
    };
    return classes[status] || 'badge-secondary';
}

function setupEventListeners() {
    document.getElementById('add-bed-btn').addEventListener('click', () => openBedModal());
    document.getElementById('close-bed-modal').addEventListener('click', closeBedModal);
    document.getElementById('cancel-bed').addEventListener('click', closeBedModal);
    
    document.getElementById('bed-form').addEventListener('submit', handleBedSubmit);
    document.getElementById('close-assign-modal').addEventListener('click', function () {
        document.getElementById('assign-bed-modal').classList.remove('show');
    });
    document.getElementById('cancel-assign').addEventListener('click', function () {
        document.getElementById('assign-bed-modal').classList.remove('show');
    });
    document.getElementById('assign-bed-form').addEventListener('submit', handleAssignSubmit);
    const assignSearch = document.getElementById('assign-patient-search');
    if (assignSearch) {
        assignSearch.addEventListener('input', function () { searchAssignPatients(this.value.trim()); });
    }
    const assignResults = document.getElementById('assign-patient-results');
    if (assignResults) {
        assignResults.addEventListener('click', function (e) {
            const row = e.target.closest('.assign-result');
            if (!row) return;
            document.getElementById('assign-patient-id').value = row.dataset.id;
            document.getElementById('assign-patient-name').value = row.dataset.name;
            assignResults.innerHTML = '';
            document.getElementById('assign-patient-search').value = row.dataset.name;
        });
    }
    
    document.getElementById('filter-bed-ward').addEventListener('change', function() {
        loadBeds({ ward_id: this.value });
    });
    
    document.getElementById('filter-bed-type').addEventListener('change', function() {
        loadBeds({ bed_type: this.value });
    });
    
    document.getElementById('filter-bed-status').addEventListener('change', function() {
        loadBeds({ status: this.value });
    });
}

function openBedModal(bed = null) {
    const modal = document.getElementById('bed-modal');
    const title = document.getElementById('bed-modal-title');
    const form = document.getElementById('bed-form');
    
    form.reset();
    document.getElementById('bed-id').value = '';
    
    if (bed) {
        title.textContent = 'Edit Bed';
        document.getElementById('bed-id').value = bed.id;
        document.getElementById('bed-ward').value = bed.ward_id;
        document.getElementById('bed-number').value = bed.bed_number;
        document.getElementById('bed-type').value = bed.bed_type;
    } else {
        title.textContent = 'Add Bed';
    }
    
    modal.classList.add('show');
}

function closeBedModal() {
    document.getElementById('bed-modal').classList.remove('show');
}

async function handleBedSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const bedId = document.getElementById('bed-id').value;
    
    try {
        const url = bedId 
            ? `/hms/backend/api/beds.php?id=${bedId}`
            : '/hms/backend/api/beds.php?action=create';
        
        const method = bedId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(bedId ? 'Bed updated' : 'Bed created', 'success');
            closeBedModal();
            await loadBeds();
        } else {
            showAlert(result.error || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Bed save error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function assignBed(bedId) {
    const bed = bedsData.find(b => b.id === bedId);
    const modal = document.getElementById('assign-bed-modal');
    if (!modal) return;

    document.getElementById('assign-bed-id').value = bedId;
    document.getElementById('assign-bed-label').textContent = bed ? bed.bed_number : 'Bed';
    document.getElementById('assign-patient-search').value = '';
    document.getElementById('assign-patient-results').innerHTML = '';
    document.getElementById('assign-patient-name').value = '';
    document.getElementById('assign-patient-id').value = '';

    modal.classList.add('show');
    document.getElementById('assign-patient-search').focus();
}

function searchAssignPatients(query) {
    const box = document.getElementById('assign-patient-results');
    if (!box) return;
    if (!query || query.length < 2) { box.innerHTML = ''; return; }

    fetch('/hms/backend/api/patients.php?action=search&q=' + encodeURIComponent(query) + '&limit=10')
        .then(r => r.json())
        .then(data => {
            const patients = (data && data.patients) || [];
            if (!patients.length) { box.innerHTML = '<div class="assign-empty">No matching patients</div>'; return; }
            box.innerHTML = patients.map(p => {
                const name = [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ');
                return '<div class="assign-result" data-id="' + p.id + '" data-name="' + name.replace(/"/g, '&quot;') + '">' +
                    '<strong>' + name + '</strong>' +
                    (p.hospital_number ? ' <span class="assign-hn">' + p.hospital_number + '</span>' : '') +
                    '</div>';
            }).join('');
        })
        .catch(err => { console.error('Patient search error:', err); box.innerHTML = '<div class="assign-empty">Search failed</div>'; });
}

async function handleAssignSubmit(e) {
    e.preventDefault();
    const bedId = document.getElementById('assign-bed-id').value;
    const patientId = document.getElementById('assign-patient-id').value;
    if (!patientId) { showAlert('Please select a patient to assign.', 'error'); return; }

    try {
        const response = await fetch('/hms/backend/api/beds.php?action=assign', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ bed_id: bedId, patient_id: patientId })
        });
        const result = await response.json();
        if (result.success) {
            showAlert(result.message || 'Bed assigned', 'success');
            document.getElementById('assign-bed-modal').classList.remove('show');
            await loadBeds();
        } else {
            showAlert((result.errors && Object.values(result.errors).join(', ')) || result.error || 'Assignment failed', 'error');
        }
    } catch (error) {
        console.error('Bed assignment error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

async function dischargeBed(bedId) {
    if (!confirm('Are you sure you want to discharge this patient from the bed?')) return;

    try {
        const response = await fetch('/hms/backend/api/beds.php?action=release', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ bed_id: bedId })
        });
        const result = await response.json();
        if (result.success) {
            showAlert(result.message || 'Bed released', 'success');
            await loadBeds();
        } else {
            showAlert((result.errors && Object.values(result.errors).join(', ')) || result.error || 'Discharge failed', 'error');
        }
    } catch (error) {
        console.error('Bed discharge error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function editBed(bedId) {
    const bed = bedsData.find(b => b.id === bedId);
    if (bed) {
        openBedModal(bed);
    }
}

function formatDate(dateString) {
    return fmtDate(dateString);
}
</script>
