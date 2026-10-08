<style>
/* ============ ACTIVE IN-PATIENTS (CENSUS) ============
   Scoped under #aip-page. The shell supplies card / card-header / card-body /
   table-container / table / btn / form-control — only the pieces it does not
   have (stat strip, badge colours, empty state) are declared here. */
#aip-page .aip-head-sub{margin:3px 0 0;font-size:12px;color:#4A7A9E;}
#aip-page .aip-controls{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
#aip-page .aip-controls select,
#aip-page .aip-controls input{padding:6px 9px;border:1px solid #C9D4E0;border-radius:4px;font-size:12px;font-family:inherit;background:#fff;}
#aip-page .aip-controls input{min-width:230px;}

#aip-page .aip-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:14px;}
#aip-page .aip-stat{border:1px solid #E2E8F0;border-radius:8px;padding:10px 14px;background:#fff;}
#aip-page .aip-stat .lbl{font-size:10px;font-weight:800;letter-spacing:.4px;color:#64748B;text-transform:uppercase;}
#aip-page .aip-stat .val{font-size:20px;font-weight:800;color:#0F2D59;margin-top:2px;}
#aip-page .aip-stat .val.new{color:#16A34A;}

#aip-page .aip-empty{text-align:center;padding:26px;color:#8A94A6;font-size:12.5px;}
#aip-page .aip-ward{font-weight:700;color:#0F2D59;}
#aip-page .aip-bed{font-weight:700;color:#0072BC;}
#aip-page .aip-name{font-weight:700;color:#1E293B;}
#aip-page .aip-hn{font-family:Consolas,'Courier New',monospace;font-weight:700;color:#0F2D59;font-size:11.5px;}
#aip-page .aip-actions{display:flex;gap:5px;flex-wrap:wrap;white-space:nowrap;}
#aip-page .aip-actions button{font-size:10px;padding:4px 8px;border-radius:3px;border:1px solid #CBD5E1;background:#fff;color:#334155;cursor:pointer;font-family:inherit;font-weight:600;display:inline-flex;align-items:center;gap:4px}
#aip-page .aip-actions button:hover{background:#F1F5F9;}
#aip-page .aip-actions button.bill{color:#0b5fa5;border-color:#9FC6E4;}
#aip-page .aip-actions button.bill:hover{background:#E7F3FC;}

#aip-page .aip-status{display:inline-block;padding:2px 8px;border-radius:10px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;background:#DCFCE7;color:#166534;}
#aip-page .aip-today{display:inline-block;margin-left:6px;padding:2px 7px;border-radius:10px;font-size:9px;font-weight:800;background:#0072BC;color:#fff;text-transform:uppercase;letter-spacing:.3px;}
</style>

<div id="aip-page">
  <div class="card">
    <div class="card-header">
      <div>
        <h2 style="margin:0;color:#0072BC;font-weight:800;">ACTIVE IN-PATIENTS</h2>
        <p class="aip-head-sub">Everyone currently admitted to a ward &amp; bed — updated live from the admissions register.</p>
      </div>
      <div class="aip-controls">
        <select id="aip-ward" aria-label="Filter by ward">
          <option value="">All Wards</option>
        </select>
        <input type="text" id="aip-search" placeholder="Search patient, hospital no. or admission code..." aria-label="Search admissions">
        <button class="btn btn-secondary btn-sm" id="aip-refresh"><i class="fa-solid fa-rotate"></i> Refresh</button>
      </div>
    </div>
    <div class="card-body">
      <div class="aip-stats">
        <div class="aip-stat"><div class="lbl">Currently Admitted</div><div class="val" id="aip-stat-total">—</div></div>
        <div class="aip-stat"><div class="lbl">Admitted Today</div><div class="val new" id="aip-stat-today">—</div></div>
        <div class="aip-stat"><div class="lbl">Wards Occupied</div><div class="val" id="aip-stat-wards">—</div></div>
        <div class="aip-stat"><div class="lbl">Beds In Use</div><div class="val" id="aip-stat-beds">—</div></div>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Admitted</th>
              <th>Hospital No.</th>
              <th>Patient</th>
              <th>Age / Sex</th>
              <th>Ward</th>
              <th>Bed</th>
              <th>Bed Type</th>
              <th>Admission Type</th>
              <th>Doctor</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="aip-table">
            <tr><td colspan="11" style="text-align:center;">Loading active in-patients...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
let aipAdmissions = [];

// NOTE: the SPA derives this name as 'init' + PascalCase(page key):
//   'active-inpatients' -> initActiveInpatients  (ONE hyphen, so only the "I"
//   is upper-cased). Renaming it silently breaks initialisation.
async function initActiveInpatients() {
    setupAipListeners();
    await loadAipWards();
    await loadActiveInpatients();
}

function escAip(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function aipFmtDateTime(v) {
    if (!v) return '—';
    const d = new Date(String(v).replace(' ', 'T'));
    if (isNaN(d)) return escAip(v);
    const pad = n => String(n).padStart(2, '0');
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}

function aipToday() {
    const n = new Date();
    const pad = x => String(x).padStart(2, '0');
    return n.getFullYear() + '-' + pad(n.getMonth() + 1) + '-' + pad(n.getDate());
}

async function loadAipWards() {
    try {
        const response = await fetch('/hms/backend/api/wards.php');
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load wards');
        const sel = document.getElementById('aip-ward');
        sel.innerHTML = '<option value="">All Wards</option>' +
            (data.wards || []).map(w => `<option value="${w.id}">${escAip(w.ward_name)}</option>`).join('');
    } catch (error) {
        console.error('Wards load error:', error);
    }
}

async function loadActiveInpatients() {
    const tbody = document.getElementById('aip-table');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">Loading active in-patients...</td></tr>';

    const params = new URLSearchParams({ status: 'ADMITTED' });
    const wardId = document.getElementById('aip-ward').value;
    const q = document.getElementById('aip-search').value.trim();
    if (wardId) params.set('ward_id', wardId);
    if (q) params.set('q', q);

    try {
        const response = await fetch('/hms/backend/api/admissions.php?' + params.toString());
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load admissions');
        aipAdmissions = data.admissions || [];
        renderAipTable();
    } catch (error) {
        console.error('Active in-patients load error:', error);
        tbody.innerHTML = '<tr><td colspan="11"><div class="aip-empty">Could not load active in-patients — ' + escAip(error.message) + '</div></td></tr>';
    }
}

function renderAipTable() {
    const tbody = document.getElementById('aip-table');
    if (!tbody) return;

    const today = aipToday();
    const total = aipAdmissions.length;
    const newly = aipAdmissions.filter(a => String(a.admission_date || '').slice(0, 10) === today).length;
    const wards = new Set(aipAdmissions.map(a => a.ward_name).filter(Boolean)).size;
    const beds = aipAdmissions.filter(a => a.bed_number).length;

    document.getElementById('aip-stat-total').textContent = total;
    document.getElementById('aip-stat-today').textContent = newly;
    document.getElementById('aip-stat-wards').textContent = wards;
    document.getElementById('aip-stat-beds').textContent = beds;

    if (!total) {
        tbody.innerHTML = '<tr><td colspan="11"><div class="aip-empty">No active in-patients' +
            ((document.getElementById('aip-ward').value || document.getElementById('aip-search').value.trim()) ? ' match this filter' : ' — no one is currently admitted') +
            '.</div></td></tr>';
        return;
    }

    tbody.innerHTML = aipAdmissions.map(a => {
        const admitDate = String(a.admission_date || '');
        const isNew = admitDate.slice(0, 10) === today;
        const bedLabel = a.bed_number ? escAip(a.bed_number) : '<span style="color:#94A3B8;">—</span>';

        return `
        <tr>
            <td>${aipFmtDateTime(a.admission_date)}${isNew ? '<span class="aip-today">New</span>' : ''}</td>
            <td><span class="aip-hn">${escAip(a.hospital_number || '—')}</span></td>
            <td><span class="aip-name">${escAip(a.patient_name || '—')}</span></td>
            <td>${a.age != null ? escAip(a.age) + ' yrs' : '—'}${a.gender ? ' / ' + escAip(a.gender) : ''}</td>
            <td><span class="aip-ward">${escAip(a.ward_name || '—')}</span></td>
            <td><span class="aip-bed">${bedLabel}</span></td>
            <td>${a.bed_type ? escAip(a.bed_type) : '<span style="color:#94A3B8;">—</span>'}</td>
            <td><span class="badge badge-secondary">${escAip(a.admission_type || 'Routine')}</span></td>
            <td>${escAip(a.admitting_doctor || '—')}</td>
            <td><span class="aip-status">${escAip(a.status || 'Admitted')}</span></td>
            <td>
                <div class="aip-actions">
                    <button type="button" data-aip-admissions title="Open the admissions register for transfer or discharge"><i class="fa-solid fa-bed-pulse"></i> Admissions</button>
                    <button type="button" class="bill" data-aip-billing title="Open in-patient billing, deposits &amp; clearance"><i class="fa-solid fa-money-bill-transfer"></i> Billing</button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function setupAipListeners() {
    const refresh = document.getElementById('aip-refresh');
    if (refresh) refresh.addEventListener('click', loadActiveInpatients);

    const wardSel = document.getElementById('aip-ward');
    if (wardSel) wardSel.addEventListener('change', loadActiveInpatients);

    // Delegate on the tbody — it lives inside this fragment, so the listener is
    // discarded with the page and never stacks up across repeat visits.
    const tbody = document.getElementById('aip-table');
    if (tbody) tbody.addEventListener('click', aipRowActions);
}

// Delegated row actions — Admissions / Billing both keep their existing
// back-navigation stack, so returning here lands on this page again.
function aipRowActions(e) {
    if (e.target.closest('[data-aip-admissions]')) {
        if (typeof navigateTo === 'function') navigateTo('admissions');
        return;
    }
    if (e.target.closest('[data-aip-billing]')) {
        if (typeof navigateTo === 'function') navigateTo('ipd-billing');
    }
}
</script>
