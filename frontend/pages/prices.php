<style>
/* ============ SERVICE PRICES : ADD/EDIT PRICE MODAL (PASTED DESIGN) ============
   Scoped under #price-modal. The shell provides .modal/.modal-content/
   .modal-header/.modal-close/.modal-body/.modal-footer/.form-group/.form-row/.btn. */
#price-modal .modal-content{max-width:560px}
#price-modal .modal-header{background:linear-gradient(135deg,#0D47A1,#0072BC);color:#fff;padding:14px 20px}
#price-modal .modal-header h3{color:#fff;font-size:15px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;margin:0}
#price-modal .modal-body{padding:18px 20px;background:#fff}
#price-modal .modal-footer{background:#F8FAFC;padding:12px 20px;justify-content:flex-end;gap:10px}
#price-modal .modal-footer .btn{padding:8px 18px;font-size:12px;font-weight:800;border-radius:5px}
#price-modal .modal-footer .btn-primary{background:#0072BC;color:#fff;border:1px solid #0072BC}
#price-modal .modal-footer .btn-primary:hover{background:#0D47A1}
#price-modal .modal-footer .btn-secondary{background:#fff;color:#475569;border:1px solid #CBD5E1}
#price-modal .modal-footer .btn-secondary:hover{background:#E2E8F0;color:#0F172A}
#price-modal .form-group label{color:#1E293B;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px}
#price-modal .input-group{display:flex;align-items:stretch}
#price-modal .input-group .input-group-text{background:#EDF2F7;border:1px solid #C9D4E0;border-right:none;border-radius:6px 0 0 6px;padding:0 12px;display:flex;align-items:center;font-weight:800;font-size:13px;color:#0F2D59;flex:0 0 auto}
#price-modal .input-group input{border-radius:0 6px 6px 0}
#price-modal .copay-box{border:1px solid #E2E8F0;border-radius:8px;background:#F8FAFC;padding:12px 14px;margin-bottom:16px}
#price-modal .copay-box label{display:block;color:#0d6efd;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;margin:0 0 2px}
#price-modal .copay-box small{display:block;color:#64748B;font-size:11px;margin-bottom:8px}
#price-modal .copay-box .input-group-text{color:#0d6efd}
</style>
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
                        <th>Top-up / Co-pay</th>
                        <th>Currency</th>
                        <th>Effective Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="prices-table">
                    <tr>
                        <td colspan="8" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Service Price Modal -->
<div class="modal" id="price-modal">
    <div class="modal-content" style="max-width:560px;">
        <div class="modal-header">
            <h3 id="price-modal-title">Add Service Price</h3>
            <button class="modal-close" id="close-price-modal">&times;</button>
        </div>
        <form id="price-form">
            <div class="modal-body">
                <input type="hidden" id="price-id">

                <!-- SERVICE TYPE -->
                <div class="form-group">
                    <label for="price-type">Service Type <span class="text-danger">*</span></label>
                    <select id="price-type" name="service_type" required>
                        <option value="" selected disabled>Select Type</option>
                        <option value="consultation">OPD Consultation</option>
                        <option value="procedure">Operation Theatre Procedure</option>
                        <option value="drug">Pharmacy Medication</option>
                        <option value="lab_test">Laboratory Test</option>
                        <option value="radiology">Radiology</option>
                        <option value="other">Other Service</option>
                    </select>
                </div>

                <!-- SERVICE -->
                <div class="form-group">
                    <label for="price-service">Service <span class="text-danger">*</span></label>
                    <select id="price-service" name="service_id" required>
                        <option value="">Select Service</option>
                    </select>
                </div>

                <!-- PRICE & CURRENCY -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="price-amount">Standard Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">GH₵</span>
                            <input type="number" step="0.01" min="0" id="price-amount" name="price" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="price-currency">Currency</label>
                        <select id="price-currency" name="currency" style="background-color:#F1F5F9;">
                            <option value="GHS" selected>GHS (GH₵)</option>
                        </select>
                    </div>
                </div>

                <!-- TOP-UP / CO-PAYMENT (INSURED PATIENTS) -->
                <div class="copay-box">
                    <label for="copay-amount">Top-up / Co-payment (Insured Patients)</label>
                    <small>Out-of-pocket amount an insured patient must pay for this service.</small>
                    <div class="input-group">
                        <span class="input-group-text">GH₵</span>
                        <input type="number" step="0.01" min="0" id="copay-amount" name="copay_amount" class="font-weight-bold" placeholder="0.00" value="0.00">
                    </div>
                </div>

                <!-- EFFECTIVE DATE -->
                <div class="form-group">
                    <label for="price-effective">Effective Date <span class="text-danger">*</span></label>
                    <input type="date" id="price-effective" name="effective_date" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <!-- MODAL FOOTER -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancel-price">CANCEL</button>
                <button type="submit" class="btn btn-primary">SAVE PRICE</button>
            </div>
        </form>
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
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No prices found</td></tr>';
        return;
    }
    
    tbody.innerHTML = pricesData.map(price => `
        <tr>
            <td><span class="badge badge-info">${price.service_type}</span></td>
            <td>${price.service_name}</td>
            <td><strong>${formatCurrency(price.price)}</strong></td>
            <td>${formatCurrency(price.copay_amount)}</td>
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
    document.getElementById('copay-amount').value = '0.00';
    
    if (price) {
        title.textContent = 'Edit Price';
        document.getElementById('price-id').value = price.id;
        document.getElementById('price-type').value = price.service_type;
        document.getElementById('price-amount').value = price.price;
        document.getElementById('price-currency').value = price.currency;
        document.getElementById('price-effective').value = price.effective_date;
        document.getElementById('copay-amount').value = price.copay_amount ?? '0.00';
        
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
