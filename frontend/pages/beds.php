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
    alert('Bed assignment to be implemented - will link to admissions');
}

function dischargeBed(bedId) {
    if (confirm('Are you sure you want to discharge this patient from the bed?')) {
        alert('Bed discharge to be implemented');
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
