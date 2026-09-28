<div class="card">
    <div class="card-header">
        <h2>Service Prices Management</h2>
        <button class="btn btn-primary btn-sm" id="add-price-btn">Add Price</button>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <select id="filter-price-type">
                    <option value="">All Types</option>
                    <option value="consultation">Consultation</option>
                    <option value="procedure">Procedure</option>
                    <option value="drug">Drug</option>
                    <option value="lab_test">Lab Test</option>
                    <option value="radiology">Radiology</option>
                    <option value="bed">Bed</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filter-price-status">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Service Type</th>
                        <th>Service Name</th>
                        <th>Price</th>
                        <th>Currency</th>
                        <th>Effective Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="prices-table">
                    <tr>
                        <td colspan="7" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Price Modal -->
<div class="modal" id="price-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="price-modal-title">Add Service Price</h3>
            <button class="modal-close" id="close-price-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="price-form">
                <input type="hidden" id="price-id">
                
                <div class="form-group">
                    <label for="price-type">Service Type *</label>
                    <select id="price-type" name="service_type" required>
                        <option value="">Select Type</option>
                        <option value="consultation">Consultation</option>
                        <option value="procedure">Procedure</option>
                        <option value="drug">Drug</option>
                        <option value="lab_test">Lab Test</option>
                        <option value="radiology">Radiology</option>
                        <option value="bed">Bed</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price-service">Service *</label>
                    <select id="price-service" name="service_id" required>
                        <option value="">Select Service</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price-amount">Price *</label>
                        <input type="number" step="0.01" id="price-amount" name="price" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label for="price-currency">Currency</label>
                        <select id="price-currency" name="currency">
                            <option value="GHS">GHS</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="price-effective">Effective Date *</label>
                    <input type="date" id="price-effective" name="effective_date" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Price</button>
                    <button type="button" class="btn btn-secondary" id="cancel-price">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let pricesData = [];

async function initPrices() {
    await loadServices();
    await loadPrices();
    setupEventListeners();
}

async function loadServices() {
    // Load services based on type selection
    document.getElementById('price-type').addEventListener('change', async function() {
        const type = this.value;
        const select = document.getElementById('price-service');
        select.innerHTML = '<option value="">Select Service</option>';
        
        if (!type) return;
        
        try {
            const response = await fetch(`/hms/backend/api/services.php?type=${type}`);
            const data = await response.json();
            
            if (data.success) {
                data.services.forEach(service => {
                    select.innerHTML += `<option value="${service.id}">${service.name} (${service.code})</option>`;
                });
            }
        } catch (error) {
            console.error('Services load error:', error);
        }
    });
}

async function loadPrices(filters = {}) {
    try {
        const queryParams = new URLSearchParams(filters);
        const response = await fetch(`/hms/backend/api/prices.php?${queryParams}`);
        const data = await response.json();
        
        if (data.success) {
            pricesData = data.prices;
            renderPricesTable();
        }
    } catch (error) {
        console.error('Prices load error:', error);
    }
}

function renderPricesTable() {
    const tbody = document.getElementById('prices-table');
    
    if (!pricesData || pricesData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No prices found</td></tr>';
        return;
    }
    
    tbody.innerHTML = pricesData.map(price => `
        <tr>
            <td><span class="badge badge-info">${price.service_type}</span></td>
            <td>${price.service_name}</td>
            <td><strong>${formatCurrency(price.price)}</strong></td>
            <td>${price.currency}</td>
            <td>${formatDate(price.effective_date)}</td>
            <td><span class="badge ${price.is_active ? 'badge-success' : 'badge-danger'}">${price.is_active ? 'Active' : 'Inactive'}</span></td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="editPrice(${price.id})">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deletePrice(${price.id})">Delete</button>
            </td>
        </tr>
    `).join('');
}

function setupEventListeners() {
    document.getElementById('add-price-btn').addEventListener('click', () => openPriceModal());
    document.getElementById('close-price-modal').addEventListener('click', closePriceModal);
    document.getElementById('cancel-price').addEventListener('click', closePriceModal);
    
    document.getElementById('price-form').addEventListener('submit', handlePriceSubmit);
    
    document.getElementById('filter-price-type').addEventListener('change', function() {
        loadPrices({ service_type: this.value });
    });
    
    document.getElementById('filter-price-status').addEventListener('change', function() {
        loadPrices({ is_active: this.value });
    });
}

function openPriceModal(price = null) {
    const modal = document.getElementById('price-modal');
    const title = document.getElementById('price-modal-title');
    const form = document.getElementById('price-form');
    
    form.reset();
    document.getElementById('price-id').value = '';
    document.getElementById('price-effective').value = new Date().toISOString().split('T')[0];
    
    if (price) {
        title.textContent = 'Edit Price';
        document.getElementById('price-id').value = price.id;
        document.getElementById('price-type').value = price.service_type;
        document.getElementById('price-amount').value = price.price;
        document.getElementById('price-currency').value = price.currency;
        document.getElementById('price-effective').value = price.effective_date;
        
        // Trigger service load
        document.getElementById('price-type').dispatchEvent(new Event('change'));
        
        // Set service after load
        setTimeout(() => {
            document.getElementById('price-service').value = price.service_id;
        }, 100);
    } else {
        title.textContent = 'Add Service Price';
    }
    
    modal.classList.add('show');
}

function closePriceModal() {
    document.getElementById('price-modal').classList.remove('show');
}

async function handlePriceSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const priceId = document.getElementById('price-id').value;
    
    try {
        const url = priceId 
            ? `/hms/backend/api/prices.php?id=${priceId}`
            : '/hms/backend/api/prices.php?action=create';
        
        const method = priceId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(priceId ? 'Price updated' : 'Price created', 'success');
            closePriceModal();
            await loadPrices();
        } else {
            showAlert(result.error || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Price save error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function editPrice(priceId) {
    const price = pricesData.find(p => p.id === priceId);
    if (price) {
        openPriceModal(price);
    }
}

async function deletePrice(priceId) {
    if (!confirm('Are you sure you want to delete this price?')) {
        return;
    }
    
    try {
        const response = await fetch(`/hms/backend/api/prices.php?id=${priceId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Price deleted successfully', 'success');
            await loadPrices();
        } else {
            showAlert(result.error || 'Deletion failed', 'error');
        }
    } catch (error) {
        console.error('Price delete error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2);
}

function formatDate(dateString) {
    return fmtDate(dateString);
}
</script>
