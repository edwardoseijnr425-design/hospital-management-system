<style>
.ward-stat-card{border:1px solid #E2E8F0;border-radius:8px;padding:13px 16px;background:#fff;box-shadow:0 1px 2px rgba(15,45,89,.05);}
.ward-stat-card .ward-stat-label{font-size:11px;font-weight:800;letter-spacing:.4px;color:#64748B;text-transform:uppercase;}
.ward-stat-card .ward-stat-value{font-size:24px;font-weight:800;margin-top:4px;}
.occ-bar{display:inline-block;min-width:44px;text-align:center;font-size:11px;font-weight:700;}
</style>

<!-- ============ WARD, ROOM & BED MANAGEMENT HEADER ============ -->
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div>
            <h2 style="margin:0;color:#0F2D59;font-weight:800;">WARD, ROOM &amp; BED MANAGEMENT</h2>
            <p style="margin:3px 0 0;font-size:12px;color:#64748B;">Monitor live bed occupancy, ward capacity, and configure hospital rooms.</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="button" class="btn btn-sm" id="add-ward-btn" style="background-color:#0072BC;color:#fff;font-weight:700;padding:7px 16px;">+ Create New Ward</button>
            <button type="button" class="btn btn-sm" id="add-bed-btn" style="background-color:#80C342;color:#fff;font-weight:700;padding:7px 16px;">+ Add Bed / Room</button>
        </div>
    </div>
</div>

<!-- ============ CAPACITY OVERVIEW CARDS ============ -->
<div class="row" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;margin:0 0 16px;">
    <div class="ward-stat-card" style="border-left:4px solid #0072BC !important;">
        <div class="ward-stat-label">Total Wards</div>
        <div class="ward-stat-value" id="statTotalWards" style="color:#0F2D59;">0</div>
    </div>
    <div class="ward-stat-card" style="border-left:4px solid #80C342 !important;">
        <div class="ward-stat-label">Available Beds</div>
        <div class="ward-stat-value" id="statAvailableBeds" style="color:#2E7D32;">0</div>
    </div>
    <div class="ward-stat-card" style="border-left:4px solid #E53E3E !important;">
        <div class="ward-stat-label">Occupied Beds</div>
        <div class="ward-stat-value" id="statOccupiedBeds" style="color:#E53E3E;">0</div>
    </div>
    <div class="ward-stat-card" style="border-left:4px solid #F39C12 !important;">
        <div class="ward-stat-label">Overall Occupancy Rate</div>
        <div class="ward-stat-value" id="statOccupancyRate" style="color:#D68910;">0%</div>
    </div>
</div>

<!-- ============ WARDS LIST ============ -->
<div class="card">
    <div class="card-header">
        <h2>Ward List</h2>
        <button class="btn btn-secondary btn-sm" id="refresh-wards-btn">Refresh</button>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <select id="filter-ward-type">
                    <option value="">All Types</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Pediatric">Pediatric</option>
                    <option value="ICU">ICU</option>
                    <option value="Maternity">Maternity</option>
                    <option value="General">General</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filter-ward-status">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Full">Full</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Ward Code</th>
                        <th>Ward Name</th>
                        <th>Type</th>
                        <th>Floor</th>
                        <th>Capacity</th>
                        <th>Rooms</th>
                        <th>Beds (Avail/Total)</th>
                        <th>Occupancy</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="wards-table">
                    <tr><td colspan="10" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ CREATE WARD MODAL ============ -->
<div class="modal" id="ward-modal">
    <div class="modal-content" style="max-width:520px;">
        <div class="modal-header">
            <h3 id="ward-modal-title">Create New Hospital Ward</h3>
            <button class="modal-close" id="close-ward-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="ward-form">
                <input type="hidden" id="ward-id">
                <div class="form-group">
                    <label for="ward-code">Ward Code *</label>
                    <input type="text" id="ward-code" name="ward_code" required placeholder="e.g. WARD-FEM-01" style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label for="ward-name">Ward Name *</label>
                    <input type="text" id="ward-name" name="ward_name" required placeholder="e.g. Female Medical Ward">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="ward-type">Ward Type *</label>
                        <select id="ward-type" name="ward_type" required>
                            <option value="">Select Type</option>
                            <option value="General">General</option>
                            <option value="Male">Male Ward</option>
                            <option value="Female">Female Ward</option>
                            <option value="Pediatric">Pediatric</option>
                            <option value="ICU">Intensive Care Unit (ICU)</option>
                            <option value="Maternity">Maternity Ward</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ward-capacity">Bed Capacity *</label>
                        <input type="number" id="ward-capacity" name="capacity" min="1" value="10" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="ward-floor">Floor / Location</label>
                    <input type="text" id="ward-floor" name="floor_level" placeholder="e.g. 1st Floor, West Wing">
                </div>
                <div class="form-group">
                    <label for="ward-status">Status</label>
                    <select id="ward-status" name="status">
                        <option value="Active">Active</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Full">Full</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="ward-submit-btn" style="background-color:#0072BC;font-weight:700;">SAVE WARD</button>
                    <button type="button" class="btn btn-secondary" id="cancel-ward">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============ ADD BED / ROOM MODAL ============ -->
<div class="modal" id="bed-modal">
    <div class="modal-content" style="max-width:520px;">
        <div class="modal-header">
            <h3>Add Bed / Room</h3>
            <button class="modal-close" id="close-bed-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="bed-form">
                <div class="form-group">
                    <label for="bed-ward">Ward *</label>
                    <select id="bed-ward" required>
                        <option value="">Select Ward</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bed-room">Room (Optional)</label>
                    <select id="bed-room">
                        <option value="">-- No Room --</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="bed-number">Bed Number *</label>
                        <input type="text" id="bed-number" required placeholder="e.g. BED-FEM-02" style="text-transform:uppercase;">
                    </div>
                    <div class="form-group">
                        <label for="bed-status">Status</label>
                        <select id="bed-status">
                            <option value="Available">Available</option>
                            <option value="Occupied">Occupied</option>
                            <option value="Reserved">Reserved</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" style="background-color:#80C342;font-weight:700;color:#fff;">ADD BED</button>
                    <button type="button" class="btn btn-secondary" id="cancel-bed">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============ VIEW BEDS MODAL ============ -->
<div class="modal" id="view-beds-modal">
    <div class="modal-content" style="max-width:760px;">
        <div class="modal-header">
            <h3 id="view-beds-title">Ward Beds</h3>
            <button class="modal-close" id="close-view-beds-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group" style="max-width:340px;">
                <label for="bed-assign-patient">Assign Patient To Bed</label>
                <select id="bed-assign-patient">
                    <option value="">-- Select Patient --</option>
                </select>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Bed Number</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th>Occupant</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="view-beds-body">
                        <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============ IPD Management dashboard lives in ipd-management.php ============ -->

<script>
let wardsData = [];
let bedsData = [];
let patientsData = [];
let currentWardBeds = [];
let currentWardFrozen = false;

async function initWards() {
    setupEventListeners();
    await Promise.all([loadWardSummary(), loadWards(), loadPatients()]);
}

async function loadWardSummary() {
    try {
        const response = await fetch('/hms/backend/api/wards.php?action=summary');
        const data = await response.json();
        if (data.success) {
            document.getElementById('statTotalWards').textContent = data.summary.total_wards;
            document.getElementById('statAvailableBeds').textContent = data.summary.available_beds;
            document.getElementById('statOccupiedBeds').textContent = data.summary.occupied_beds;
            document.getElementById('statOccupancyRate').textContent = data.summary.occupancy_rate + '%';
        }
    } catch (error) {
        console.error('Ward summary load error:', error);
    }
}

async function loadWards(filters = {}) {
    try {
        const queryParams = new URLSearchParams(filters);
        const response = await fetch(`/hms/backend/api/wards.php?${queryParams}`);
        const data = await response.json();
        if (data.success) {
            wardsData = data.wards;
            renderWardsTable();
            populateWardSelects();
        }
    } catch (error) {
        console.error('Wards load error:', error);
        document.getElementById('wards-table').innerHTML =
            '<tr><td colspan="10" style="text-align:center;">Failed to load wards</td></tr>';
    }
}

function populateWardSelects() {
    const sel = document.getElementById('bed-ward');
    const current = sel.value;
    sel.innerHTML = '<option value="">Select Ward</option>' +
        wardsData.map(w => `<option value="${w.id}">${escHtml(w.ward_code)} - ${escHtml(w.ward_name)}</option>`).join('');
    if (current) sel.value = current;
}

function renderWardsTable() {
    const tbody = document.getElementById('wards-table');
    if (!wardsData || wardsData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">No wards found</td></tr>';
        return;
    }
    tbody.innerHTML = wardsData.map(ward => {
        const avail = parseInt(ward.available_beds) || 0;
        const total = parseInt(ward.total_beds) || 0;
        const occ = parseInt(ward.occupied_beds) || 0;
        const occRate = ward.occupancy_rate || 0;
        const occColor = occRate >= 90 ? '#E53E3E' : (occRate >= 60 ? '#F39C12' : '#28A745');
        const statusTag = ward.status === 'Active'
            ? '<span class="badge" style="background:#28A745;color:#fff;">ACTIVE</span>'
            : '<span class="badge" style="background:#E53E3E;color:#fff;">' + escHtml(String(ward.status).toUpperCase()) + '</span>';
        const frozen = parseInt(ward.is_frozen, 10) === 1;
        const frozenTag = frozen
            ? '<span class="badge" style="background:#0F2D59;color:#fff;margin-left:4px;" title="Ward frozen - new admissions blocked">FROZEN</span>'
            : '';
        return `
            <tr>
                <td><strong style="color:#0072BC;">${escHtml(ward.ward_code)}</strong></td>
                <td style="color:#0F2D59;font-weight:700;">${escHtml(ward.ward_name)}</td>
                <td><span class="badge" style="background:#EEF2F7;color:#0F2D59;">${escHtml(ward.ward_type)}</span></td>
                <td>${escHtml(ward.floor_level || '-')}</td>
                <td>${ward.capacity}</td>
                <td>${ward.room_count || 0}</td>
                <td><span style="color:#2E7D32;font-weight:700;">${avail}</span> / ${total}</td>
                <td><span class="occ-bar" style="color:${occColor};">${occRate}%</span></td>
                <td>${statusTag}${frozenTag}</td>
                <td style="white-space:nowrap;">
                    <button class="btn btn-sm btn-secondary" onclick="editWard(${ward.id})">Edit</button>
                    <button class="btn btn-sm btn-secondary" onclick="viewWardBeds(${ward.id}, '${escJs(ward.ward_name)}')">View Beds</button>
                    <button class="btn btn-sm" onclick="toggleFreeze(${ward.id})" style="background-color:${frozen ? '#F39C12' : '#0F2D59'};color:#fff;font-weight:700;">${frozen ? 'Unfreeze' : 'Freeze'}</button>
                </td>
            </tr>`;
    }).join('');
}

async function loadPatients() {
    try {
        const response = await fetch('/hms/backend/api/patients.php');
        const data = await response.json();
        if (data.success) {
            patientsData = data.patients || [];
            const sel = document.getElementById('bed-assign-patient');
            sel.innerHTML = '<option value="">-- Select Patient --</option>' +
                patientsData.map(p => `<option value="${p.id}">${escHtml(p.first_name + ' ' + p.last_name)} (${escHtml(p.hospital_number)})</option>`).join('');
        }
    } catch (error) {
        console.warn('Patients load error:', error);
    }
}

function openWardModal() {
    document.getElementById('ward-form').reset();
    document.getElementById('ward-id').value = '';
    document.getElementById('ward-modal-title').textContent = 'Create New Hospital Ward';
    document.getElementById('ward-submit-btn').textContent = 'SAVE WARD';
    document.getElementById('ward-modal').classList.add('show');
}

// Pre-fill the ward modal for editing an existing ward
function editWard(wardId) {
    const ward = wardsData.find(w => String(w.id) === String(wardId));
    if (!ward) {
        alert('Ward record not found');
        return;
    }
    document.getElementById('ward-form').reset();
    document.getElementById('ward-id').value = ward.id;
    document.getElementById('ward-code').value = ward.ward_code;
    document.getElementById('ward-name').value = ward.ward_name;
    document.getElementById('ward-type').value = ward.ward_type;
    document.getElementById('ward-capacity').value = ward.capacity;
    document.getElementById('ward-floor').value = ward.floor_level || '';
    document.getElementById('ward-status').value = ward.status || 'Active';
    document.getElementById('ward-modal-title').textContent = 'Edit Hospital Ward - ' + ward.ward_name;
    document.getElementById('ward-submit-btn').textContent = 'UPDATE WARD';
    document.getElementById('ward-modal').classList.add('show');
}

function closeWardModal() {
    document.getElementById('ward-modal').classList.remove('show');
}

function openBedModal() {
    document.getElementById('bed-form').reset();
    document.getElementById('bed-room').innerHTML = '<option value="">-- No Room --</option>';
    document.getElementById('bed-modal').classList.add('show');
}

function closeBedModal() {
    document.getElementById('bed-modal').classList.remove('show');
}

async function loadRoomsForWard(wardId) {
    const sel = document.getElementById('bed-room');
    sel.innerHTML = '<option value="">-- No Room --</option>';
    if (!wardId) return;
    try {
        const response = await fetch(`/hms/backend/api/wards.php?action=rooms&ward_id=${wardId}`);
        const data = await response.json();
        if (data.success) {
            data.rooms.forEach(r => {
                sel.innerHTML += `<option value="${r.id}">${escHtml(r.room_number)} (${escHtml(r.room_type)})</option>`;
            });
        }
    } catch (error) {
        console.error('Rooms load error:', error);
    }
}

async function handleWardSubmit(e) {
    e.preventDefault();
    const wardId = document.getElementById('ward-id').value;
    const payload = {
        ward_code: document.getElementById('ward-code').value.trim(),
        ward_name: document.getElementById('ward-name').value.trim(),
        ward_type: document.getElementById('ward-type').value,
        capacity: document.getElementById('ward-capacity').value,
        floor_level: document.getElementById('ward-floor').value.trim(),
        status: document.getElementById('ward-status').value
    };
    const url = wardId
        ? `/hms/backend/api/wards.php?action=update&id=${wardId}`
        : '/hms/backend/api/wards.php?action=create';
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.success) {
            closeWardModal();
            await Promise.all([loadWards(), loadWardSummary()]);
        } else {
            alert(result.error || (wardId ? 'Ward update failed' : 'Ward creation failed'));
        }
    } catch (error) {
        console.error(wardId ? 'Ward update error:' : 'Ward create error:', error);
        alert(wardId ? 'Ward update failed' : 'Ward creation failed');
    }
}

async function handleBedSubmit(e) {
    e.preventDefault();
    const payload = {
        ward_id: document.getElementById('bed-ward').value,
        room_id: document.getElementById('bed-room').value || null,
        bed_number: document.getElementById('bed-number').value.trim(),
        status: document.getElementById('bed-status').value
    };
    if (!payload.ward_id || !payload.bed_number) {
        alert('Ward and bed number are required');
        return;
    }
    try {
        const response = await fetch('/hms/backend/api/wards.php?action=create_bed', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.success) {
            closeBedModal();
            await Promise.all([loadWards(), loadWardSummary()]);
        } else {
            alert(result.error || 'Bed creation failed');
        }
    } catch (error) {
        console.error('Bed create error:', error);
        alert('Bed creation failed');
    }
}

async function toggleFreeze(wardId) {
    const ward = wardsData.find(w => String(w.id) === String(wardId));
    if (!ward) return;
    const freeze = parseInt(ward.is_frozen, 10) !== 1;
    const action = freeze ? 'Freeze' : 'Unfreeze';
    if (!confirm(action + ' ward "' + ward.ward_name + '"? Frozen wards block new bed assignments.')) return;
    try {
        const response = await fetch(`/hms/backend/api/wards.php?action=update&id=${wardId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ is_frozen: freeze ? 1 : 0 })
        });
        const result = await response.json();
        if (result.success) {
            await Promise.all([loadWards(), loadWardSummary()]);
        } else {
            alert(result.error || action + ' failed');
        }
    } catch (error) {
        console.error('Freeze toggle error:', error);
        alert(action + ' failed');
    }
}

async function viewWardBeds(wardId, wardName) {
    const ward = wardsData.find(w => String(w.id) === String(wardId));
    currentWardFrozen = ward ? parseInt(ward.is_frozen, 10) === 1 : false;
    const patientSel = document.getElementById('bed-assign-patient');
    if (patientSel) patientSel.disabled = currentWardFrozen;
    document.getElementById('view-beds-title').textContent = wardName + ' - Beds' + (currentWardFrozen ? ' (FROZEN)' : '');
    document.getElementById('view-beds-modal').classList.add('show');
    await Promise.all([loadPatients(), loadWardBeds(wardId)]);
}

async function loadWardBeds(wardId) {
    try {
        const response = await fetch(`/hms/backend/api/wards.php?action=beds&ward_id=${wardId}`);
        const data = await response.json();
        if (data.success) {
            currentWardBeds = data.beds || [];
            renderWardBeds();
        }
    } catch (error) {
        console.error('Ward beds load error:', error);
    }
}

function renderWardBeds() {
    const tbody = document.getElementById('view-beds-body');
    if (!currentWardBeds.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No beds in this ward</td></tr>';
        return;
    }
    tbody.innerHTML = currentWardBeds.map(bed => {
        const statusNorm = String(bed.status || '').toUpperCase();
        const statusTag = statusNorm === 'OCCUPIED'
            ? '<span class="badge" style="background:#E53E3E;color:#fff;">OCCUPIED</span>'
            : (statusNorm === 'RESERVED'
                ? '<span class="badge" style="background:#F39C12;color:#fff;">RESERVED</span>'
                : '<span class="badge" style="background:#28A745;color:#fff;">AVAILABLE</span>');

        const occupant = bed.patient_name ? escHtml(bed.patient_name) : '<span style="color:#94A3B8;">-</span>';

        let actionCell = '';
        if (statusNorm === 'OCCUPIED') {
            actionCell = `<button class="btn btn-sm btn-danger" onclick="releaseBed(${bed.id})">Release Bed</button>`;
        } else if (currentWardFrozen) {
            actionCell = '<span class="badge" style="background:#0F2D59;color:#fff;" title="Ward frozen - new admissions blocked">WARD FROZEN</span>';
        } else {
            actionCell = `<button class="btn btn-sm btn-primary" style="background-color:#0072BC;color:#fff;" onclick="assignBed(${bed.id})">Assign</button>`;
        }

        return `
            <tr>
                <td><strong style="color:#0F2D59;">${escHtml(bed.bed_number)}</strong></td>
                <td>${bed.room_number ? escHtml(bed.room_number) : '-'}</td>
                <td>${statusTag}</td>
                <td>${occupant}</td>
                <td style="text-align:center;">${actionCell}</td>
            </tr>`;
    }).join('');
}

async function assignBed(bedId) {
    const patientId = document.getElementById('bed-assign-patient').value;
    if (!patientId) {
        alert('Select a patient first (Assign Patient To Bed)');
        return;
    }
    try {
        const response = await fetch('/hms/backend/api/wards.php?action=assign_bed', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ bed_id: bedId, patient_id: patientId })
        });
        const result = await response.json();
        if (result.success) {
            await Promise.all([loadWardBeds(currentWardBeds.length ? currentWardBeds[0].ward_id : null), loadWards(), loadWardSummary()]);
        } else {
            alert(result.error || 'Assignment failed');
        }
    } catch (error) {
        console.error('Assign bed error:', error);
    }
}

async function releaseBed(bedId) {
    if (!confirm('Release this bed?')) return;
    try {
        const response = await fetch('/hms/backend/api/wards.php?action=release_bed', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ bed_id: bedId })
        });
        const result = await response.json();
        if (result.success) {
            await Promise.all([loadWardBeds(currentWardBeds.length ? currentWardBeds[0].ward_id : null), loadWards(), loadWardSummary()]);
        } else {
            alert(result.error || 'Release failed');
        }
    } catch (error) {
        console.error('Release bed error:', error);
    }
}

function setupEventListeners() {
    document.getElementById('add-ward-btn').addEventListener('click', openWardModal);
    document.getElementById('close-ward-modal').addEventListener('click', closeWardModal);
    document.getElementById('cancel-ward').addEventListener('click', closeWardModal);
    document.getElementById('ward-form').addEventListener('submit', handleWardSubmit);

    document.getElementById('add-bed-btn').addEventListener('click', openBedModal);
    document.getElementById('close-bed-modal').addEventListener('click', closeBedModal);
    document.getElementById('cancel-bed').addEventListener('click', closeBedModal);
    document.getElementById('bed-form').addEventListener('submit', handleBedSubmit);
    document.getElementById('bed-ward').addEventListener('change', function() {
        loadRoomsForWard(this.value);
    });

    document.getElementById('close-view-beds-modal').addEventListener('click', () => {
        document.getElementById('view-beds-modal').classList.remove('show');
    });

    document.getElementById('filter-ward-type').addEventListener('change', function() {
        loadWards({ ward_type: this.value, status: document.getElementById('filter-ward-status').value });
    });
    document.getElementById('filter-ward-status').addEventListener('change', function() {
        loadWards({ ward_type: document.getElementById('filter-ward-type').value, status: this.value });
    });
    document.getElementById('refresh-wards-btn').addEventListener('click', () => {
        loadWards();
        loadWardSummary();
    });
}

function escHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function escJs(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}
</script>