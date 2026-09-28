<div class="card">
    <div class="card-header">
        <h2>Sponsors Management</h2>
        <button class="btn btn-primary btn-sm" id="add-sponsor-btn">Add Sponsor</button>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <select id="filter-sponsor-type">
                    <option value="">All Types</option>
                    <option value="nhia">NHIA</option>
                    <option value="private_insurance">Private Insurance</option>
                    <option value="corporate">Corporate</option>
                    <option value="individual">Individual</option>
                    <option value="government">Government</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filter-sponsor-status">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filter-nhia-status">
                    <option value="">NHIA Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>NHIA Status</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="sponsors-table">
                    <tr>
                        <td colspan="9" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sponsor Modal -->
<div class="modal" id="sponsor-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="sponsor-modal-title">Add Sponsor</h3>
            <button class="modal-close" id="close-sponsor-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="sponsor-form">
                <input type="hidden" id="sponsor-id">
                
                <div class="form-group">
                    <label for="sponsor-code">Sponsor Code *</label>
                    <input type="text" id="sponsor-code" name="code" required placeholder="e.g., NHIA001">
                </div>
                
                <div class="form-group">
                    <label for="sponsor-name">Sponsor Name *</label>
                    <input type="text" id="sponsor-name" name="name" required placeholder="e.g., National Health Insurance Authority">
                </div>
                
                <div class="form-group">
                    <label for="sponsor-type">Type *</label>
                    <select id="sponsor-type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="nhia">NHIA</option>
                        <option value="private_insurance">Private Insurance</option>
                        <option value="corporate">Corporate</option>
                        <option value="individual">Individual</option>
                        <option value="government">Government</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sponsor-contact">Contact Person</label>
                        <input type="text" id="sponsor-contact" name="contact_person">
                    </div>
                    <div class="form-group">
                        <label for="sponsor-phone">Phone</label>
                        <input type="tel" id="sponsor-phone" name="phone">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sponsor-email">Email</label>
                        <input type="email" id="sponsor-email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="sponsor-nhia-status">NHIA Status</label>
                        <select id="sponsor-nhia-status" name="nhia_status">
                            <option value="">Not Applicable</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="sponsor-expiry">Expiry Date</label>
                    <input type="date" id="sponsor-expiry" name="expiry_date">
                </div>
                
                <div class="form-group">
                    <label for="sponsor-address">Address</label>
                    <textarea id="sponsor-address" name="address" rows="2"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Sponsor</button>
                    <button type="button" class="btn btn-secondary" id="cancel-sponsor">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let sponsorsData = [];

async function initSponsors() {
    await loadSponsors();
    setupEventListeners();
}

async function loadSponsors(filters = {}) {
    try {
        const queryParams = new URLSearchParams(filters);
        const response = await fetch(`/hms/backend/api/sponsors.php?action=list&${queryParams}`);
        const data = await response.json();
        
        if (data.success) {
            sponsorsData = data.sponsors;
            renderSponsorsTable();
        }
    } catch (error) {
        console.error('Sponsors load error:', error);
    }
}

function renderSponsorsTable() {
    const tbody = document.getElementById('sponsors-table');
    
    if (!sponsorsData || sponsorsData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No sponsors found</td></tr>';
        return;
    }
    
    tbody.innerHTML = sponsorsData.map(sponsor => `
        <tr>
            <td><strong>${sponsor.code}</strong></td>
            <td>${sponsor.name}</td>
            <td><span class="badge badge-info">${sponsor.type}</span></td>
            <td>${sponsor.nhia_status ? `<span class="badge ${getNHIAStatusBadgeClass(sponsor.nhia_status)}">${sponsor.nhia_status}</span>` : '-'}</td>
            <td>${sponsor.contact_person || '-'}</td>
            <td>${sponsor.phone || '-'}</td>
            <td>${sponsor.expiry_date ? formatDate(sponsor.expiry_date) : '-'}</td>
            <td><span class="badge ${sponsor.is_active ? 'badge-success' : 'badge-danger'}">${sponsor.is_active ? 'Active' : 'Inactive'}</span></td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="editSponsor(${sponsor.id})">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteSponsor(${sponsor.id})">Delete</button>
            </td>
        </tr>
    `).join('');
}

function getNHIAStatusBadgeClass(status) {
    const classes = {
        'active': 'badge-success',
        'inactive': 'badge-warning',
        'suspended': 'badge-danger'
    };
    return classes[status] || 'badge-secondary';
}

function setupEventListeners() {
    document.getElementById('add-sponsor-btn').addEventListener('click', () => openSponsorModal());
    document.getElementById('close-sponsor-modal').addEventListener('click', closeSponsorModal);
    document.getElementById('cancel-sponsor').addEventListener('click', closeSponsorModal);
    
    document.getElementById('sponsor-form').addEventListener('submit', handleSponsorSubmit);
    
    document.getElementById('filter-sponsor-type').addEventListener('change', function() {
        loadSponsors({ type: this.value });
    });
    
    document.getElementById('filter-sponsor-status').addEventListener('change', function() {
        loadSponsors({ is_active: this.value });
    });
    
    document.getElementById('filter-nhia-status').addEventListener('change', function() {
        loadSponsors({ nhia_status: this.value });
    });
}

function openSponsorModal(sponsor = null) {
    const modal = document.getElementById('sponsor-modal');
    const title = document.getElementById('sponsor-modal-title');
    const form = document.getElementById('sponsor-form');
    
    form.reset();
    document.getElementById('sponsor-id').value = '';
    
    if (sponsor) {
        title.textContent = 'Edit Sponsor';
        document.getElementById('sponsor-id').value = sponsor.id;
        document.getElementById('sponsor-code').value = sponsor.code;
        document.getElementById('sponsor-name').value = sponsor.name;
        document.getElementById('sponsor-type').value = sponsor.type;
        document.getElementById('sponsor-contact').value = sponsor.contact_person || '';
        document.getElementById('sponsor-phone').value = sponsor.phone || '';
        document.getElementById('sponsor-email').value = sponsor.email || '';
        document.getElementById('sponsor-nhia-status').value = sponsor.nhia_status || '';
        document.getElementById('sponsor-expiry').value = sponsor.expiry_date || '';
        document.getElementById('sponsor-address').value = sponsor.address || '';
    } else {
        title.textContent = 'Add Sponsor';
    }
    
    modal.classList.add('show');
}

function closeSponsorModal() {
    document.getElementById('sponsor-modal').classList.remove('show');
}

async function handleSponsorSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const sponsorId = document.getElementById('sponsor-id').value;
    
    try {
        const url = sponsorId 
            ? `/hms/backend/api/sponsors.php?id=${sponsorId}`
            : '/hms/backend/api/sponsors.php?action=create';
        
        const method = sponsorId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(sponsorId ? 'Sponsor updated' : 'Sponsor created', 'success');
            closeSponsorModal();
            await loadSponsors();
        } else {
            showAlert(result.error || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Sponsor save error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function editSponsor(sponsorId) {
    const sponsor = sponsorsData.find(s => s.id === sponsorId);
    if (sponsor) {
        openSponsorModal(sponsor);
    }
}

async function deleteSponsor(sponsorId) {
    if (!confirm('Are you sure you want to delete this sponsor?')) {
        return;
    }
    
    try {
        const response = await fetch(`/hms/backend/api/sponsors.php?id=${sponsorId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Sponsor deleted successfully', 'success');
            await loadSponsors();
        } else {
            showAlert(result.error || 'Deletion failed', 'error');
        }
    } catch (error) {
        console.error('Sponsor delete error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function formatDate(dateString) {
    return fmtDate(dateString);
}
</script>
