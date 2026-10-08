<style>
/* ============ IPD MANAGEMENT DASHBOARD : SYSTEM DASHBOARD-STYLE GRID ============
   Scoped under #ipd-page. The shell already provides card/card-body/row/
   col-md-6/font-weight-bold/text-uppercase and auto-appends the contact banner
   below every module page. Tile look follows the pasted IPD main-grid design
   (soft pastel icon badges, hover lift) matching the System Dashboard tiles. */
#ipd-page .mb-3{margin-bottom:1rem}
#ipd-page .mb-0{margin:0}
#ipd-page .p-2{padding:.5rem}
#ipd-page .p-3{padding:1rem}
#ipd-page .gap-3{gap:1rem}
#ipd-page .rounded{border-radius:8px}
#ipd-page .text-dark{color:#0F2D59}
#ipd-page .text-primary{color:#0d6efd}
#ipd-page .text-muted{color:#64748B}
#ipd-page .align-items-center{align-items:center}
#ipd-page .d-flex{display:flex}

/* Tile hover animation + border matching the pasted design */
#ipd-page .ipd-tile{
    background-color:#ffffff;
    transition:transform .15s ease-in-out, box-shadow .15s ease-in-out;
    border:1px solid #e3e6f0 !important;
    cursor:pointer;
    text-decoration:none;
}
#ipd-page .ipd-tile:hover{
    transform:translateY(-2px);
    box-shadow:0 .5rem 1rem rgba(0,0,0,.08) !important;
}

/* Soft color background badges matching System Dashboard */
#ipd-page .icon-box{
    width:50px;
    height:50px;
    border-radius:8px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex:0 0 auto;
}
#ipd-page .bg-primary-subtle{background-color:#e8f1ff !important;color:#0d6efd}
#ipd-page .bg-info-subtle{background-color:#e0f8ff !important;color:#0891b2}
#ipd-page .bg-success-subtle{background-color:#e6f9ed !important;color:#16a34a}
#ipd-page .bg-secondary-subtle{background-color:#f1f3f5 !important;color:#475569}
#ipd-page .bg-warning-subtle{background-color:#fef7e0 !important;color:#d97706}
#ipd-page .bg-danger-subtle{background-color:#fde8e8 !important;color:#dc2626}

/* Active (shown) state for the WARDS, ROOMS & BED STATUS tile */
#ipd-page .ipd-tile.active{
    border-color:#0072BC !important;
    box-shadow:0 0 0 2px rgba(0,114,188,.22) !important;
}

/* Live count badge shown inside a tile heading (filled in by loadIpdBadges) */
#ipd-page .ipd-badge{
    display:inline-block;
    vertical-align:middle;
    margin-left:6px;
    padding:2px 7px;
    border-radius:10px;
    background:#0072BC;
    color:#fff;
    font-size:9.5px;
    font-weight:800;
    letter-spacing:.3px;
    text-transform:uppercase;
    white-space:nowrap;
}

/* Bed status panel mini-stats */
.stat-mini{border:1px solid #E2E8F0;border-radius:8px;padding:10px 14px;background:#fff;}
.stat-mini .stat-mini-label{font-size:10px;font-weight:800;letter-spacing:.4px;color:#64748B;text-transform:uppercase;}
.stat-mini .stat-mini-value{font-size:20px;font-weight:800;color:#0F2D59;margin-top:2px;}
</style><div id="ipd-page">

  <!-- ============ IPD MANAGEMENT DASHBOARD ============ -->
  <div class="card">
    <div class="card-header">
      <div>
        <h2 style="margin:0;color:#0072BC;font-weight:800;">IPD MANAGEMENT DASHBOARD</h2>
        <p style="margin:3px 0 0;font-size:12px;color:#4A7A9E;">Quick access to every in-patient module — click a tile to open that section, or click WARDS, ROOMS &amp; BED STATUS to view the bed status panel below.</p>
      </div>
    </div>
    <div class="card-body">
      <!-- 2-COLUMN MODULE GRID (matches the pasted IPD main grid) -->
      <div class="row row-cols-1 row-cols-md-2 g-3 mb-3" id="ipd-grid">
        <div class="col-12" style="grid-column:1/-1;text-align:center;color:#94A3B8;padding:18px 0;">Loading IPD modules...</div>
      </div>
    </div>
  </div>

  <!-- ============ WARD / BED / STATUS (shown when the WARDS, ROOMS & BED STATUS tile is clicked) ============ -->
  <div class="card" id="bed-status-section" style="display:none;">
    <div class="card-header">
      <div>
        <h2 style="margin:0;color:#0072BC;font-weight:800;"><i class="fa-solid fa-bed" style="margin-right:8px;"></i>WARD · BED · STATUS</h2>
        <p style="margin:3px 0 0;font-size:12px;color:#64748B;">Bed occupancy across all wards — click “WARDS, ROOMS &amp; BED STATUS” above to toggle this panel.</p>
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <select id="filter-ward-admission" style="min-width:170px;">
          <option value="">All Wards</option>
        </select>
        <button class="btn btn-secondary btn-sm" id="refresh-beds-btn">Refresh</button>
      </div>
    </div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:14px;">
        <div class="stat-mini"><div class="stat-mini-label">Total Beds</div><div class="stat-mini-value" id="statBedsTotal">0</div></div>
        <div class="stat-mini"><div class="stat-mini-label">Occupied</div><div class="stat-mini-value" id="statBedsOccupied" style="color:#E53E3E;">0</div></div>
        <div class="stat-mini"><div class="stat-mini-label">Available</div><div class="stat-mini-value" id="statBedsAvailable" style="color:#2E7D32;">0</div></div>
      </div>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Ward</th>
              <th>Bed No.</th>
              <th>Room</th>
              <th>Type</th>
              <th>Status</th>
              <th>Current Patient</th>
            </tr>
          </thead>
          <tbody id="beds-table">
            <tr><td colspan="6" style="text-align:center;">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<script>
let bedsData = [];

/* IPD module grid: real SPA routing keys (WARDS tile toggles the bed-status
   panel instead of navigating; the rest call loadPage). DRAFT ADMISSIONS and
   REPORTS both land on the Admissions page — the draft section and the
   admissions/discharge history respectively — while DOCTOR STATION and BILLING
   MANAGEMENT have pages of their own. */
const IPD_TILES = [
    { label: 'WARDS, ROOMS & BED STATUS', sub: 'Real-time occupancy and bed allocation status', icon: 'bed',    color: 'bg-primary-subtle text-primary',   page: 'wards' },
    { label: 'CURRENT PATIENT ACCESS',    sub: 'Access newly admitted or active in-patients',   icon: 'user',    color: 'bg-info-subtle text-info',        page: 'active-inpatients', badge: 'badge-census' },
    { label: 'ADMIT PATIENT',             sub: 'Admit patient directly to a ward and bed',      icon: 'user-plus', color: 'bg-success-subtle text-success', page: 'admissions', intent: 'admit', badge: 'badge-freebeds' },
    { label: 'DRAFT ADMISSIONS',          sub: 'In-progress admissions pending finalization',   icon: 'file',    color: 'bg-secondary-subtle text-secondary', page: 'admissions', intent: 'drafts', badge: 'badge-drafts' },
    { label: 'NURSING STATION',           sub: 'Admit patient directly to a ward & bed only', icon: 'activity', color: 'bg-warning-subtle text-warning', page: 'vitals' },
    { label: 'DOCTOR STATION',            sub: 'OPD/IPD doctor notes, clinical entries & discharge summary', icon: 'doctor',  color: 'bg-danger-subtle text-danger',      page: 'doctor-station' },
    { label: 'BILLING MANAGEMENT',        sub: 'In-patient billing, deposits, & clearance',     icon: 'card',    color: 'bg-success-subtle text-success', page: 'ipd-billing' },
    { label: 'REPORTS',                   sub: 'All IPD management records, ward admission history & discharges', icon: 'chart',  color: 'bg-info-subtle text-info',        page: 'admissions' }
];

const IPD_ICONS = {
    'bed': '<path d="M2 4v16"></path><path d="M2 8h18a2 2 0 0 1 2 2v10"></path><path d="M2 17h20"></path><path d="M6 8v9"></path>',
    'user': '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
    'user-plus': '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="17" y1="11" x2="23" y2="11"></line>',
    'file': '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line>',
    'activity': '<path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>',
    'doctor': '<circle cx="12" cy="7" r="4"></circle><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"></path>',
    'card': '<rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line>',
    'chart': '<line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line>'
};

async function initIpdManagement() {
    setupEventListeners();
    await Promise.all([loadWards(), loadBeds(''), loadIpdTiles()]);
}

function loadIpdTiles() {
    const grid = document.getElementById('ipd-grid');
    if (!grid) return;
    grid.innerHTML = IPD_TILES.map(c => `
        <div class="col-md-6">
          <div class="card border-0 shadow-sm h-100 p-2 ipd-tile" data-page="${c.page}"${c.intent ? ' data-intent="' + c.intent + '"' : ''}>
            <div class="card-body d-flex align-items-center gap-3 p-2">
              <div class="icon-box ${c.color} p-3 rounded">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${IPD_ICONS[c.icon] || ''}</svg>
              </div>
              <div>
                <h6 class="font-weight-bold text-uppercase mb-0" style="${c.page === 'patients' ? 'color:#0d6efd;' : 'color:#0F2D59;'}font-size:.9rem;letter-spacing:.5px;">${c.label}${c.badge ? ' <span class="ipd-badge" id="' + c.badge + '" style="display:none;"></span>' : ''}</h6>
                ${c.sub ? '<small class="text-muted" style="font-size:.78rem;">' + c.sub + '</small>' : ''}
              </div>
            </div>
          </div>
        </div>`).join('');
    grid.querySelectorAll('.ipd-tile').forEach(card => {
        card.addEventListener('click', (e) => {
            e.preventDefault();
            if (card.dataset.page === 'wards') {
                toggleBedStatusSection();
                return;
            }
            if (typeof window.loadPage === 'function') {
                window.loadPage(card.dataset.page, null, { intent: card.dataset.intent || null });
            }
        });
    });
    loadIpdBadges();
}

/* Live count badges on the tiles. Free beds are derived from bedsData (already
   fetched by loadBeds) so no extra request is made for that one. */
function setBadge(id, value, suffix) {
    const el = document.getElementById(id);
    if (!el) return;
    if (value === null || value === undefined) { el.style.display = 'none'; return; }
    el.textContent = suffix ? value + ' ' + suffix : String(value);
    el.style.display = 'inline-block';
}

async function loadIpdBadges() {
    setBadge('badge-drafts', null);
    setBadge('badge-census', null);
    try {
        const [draftsRes, censusRes] = await Promise.all([
            fetch('/hms/backend/api/draft_admissions.php?status=DRAFT'),
            fetch('/hms/backend/api/admissions.php?status=ADMITTED')
        ]);
        const drafts = await draftsRes.json();
        const census = await censusRes.json();
        if (drafts && drafts.success) setBadge('badge-drafts', (drafts.drafts || []).length, 'pending');
        if (census && census.success) setBadge('badge-census', (census.admissions || []).length, 'active');
    } catch (error) {
        console.error('IPD tile badge load error:', error);
    }
}

function toggleBedStatusSection() {
    const section = document.getElementById('bed-status-section');
    if (!section) return;
    const isHidden = section.style.display === 'none' || !section.style.display;
    section.style.display = isHidden ? 'block' : 'none';
    const tile = document.querySelector('.ipd-tile[data-page="wards"]');
    if (tile) tile.classList.toggle('active', isHidden);
    if (isHidden) {
        loadBeds(document.getElementById('filter-ward-admission').value);
        setTimeout(() => { section.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 80);
    }
}

async function loadWards() {
    try {
        const response = await fetch('/hms/backend/api/wards.php');
        const data = await response.json();
        if (data.success) {
            const select = document.getElementById('filter-ward-admission');
            select.innerHTML = '<option value="">All Wards</option>' +
                data.wards.map(w => `<option value="${w.id}">${escHtml(w.ward_name)}</option>`).join('');
        }
    } catch (error) {
        console.error('Wards load error:', error);
    }
}

async function loadBeds(wardId = '') {
    try {
        const url = wardId
            ? '/hms/backend/api/beds.php?ward_id=' + encodeURIComponent(wardId)
            : '/hms/backend/api/beds.php';
        const response = await fetch(url);
        const data = await response.json();
        if (data.success) {
            bedsData = data.beds || [];
            renderBedsTable();
        }
    } catch (error) {
        console.error('Beds load error:', error);
        const tbody = document.getElementById('beds-table');
        if (tbody) tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Failed to load beds</td></tr>';
    }
}

function renderBedsTable() {
    const tbody = document.getElementById('beds-table');
    if (!tbody) return;

    const total = bedsData.length;
    const occupied = bedsData.filter(b => String(b.status || '').toUpperCase() === 'OCCUPIED').length;
    setBadge('badge-freebeds', total - occupied, 'free');
    document.getElementById('statBedsTotal').textContent = total;
    document.getElementById('statBedsOccupied').textContent = occupied;
    document.getElementById('statBedsAvailable').textContent = total - occupied;

    if (!bedsData.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No beds found</td></tr>';
        return;
    }

    tbody.innerHTML = bedsData.map(bed => {
        const statusNorm = String(bed.status || '').toUpperCase();
        let statusTag = '';
        if (statusNorm === 'OCCUPIED') statusTag = '<span class="badge" style="background:#DC3545;color:#fff;font-weight:700;">OCCUPIED</span>';
        else if (statusNorm === 'RESERVED') statusTag = '<span class="badge" style="background:#F39C12;color:#fff;font-weight:700;">RESERVED</span>';
        else if (statusNorm === 'MAINTENANCE') statusTag = '<span class="badge" style="background:#6C757D;color:#fff;font-weight:700;">MAINTENANCE</span>';
        else statusTag = '<span class="badge" style="background:#28A745;color:#fff;font-weight:700;">AVAILABLE</span>';

        const typeBadge = bed.room_type
            ? `<span class="badge" style="background:#EEF2F7;color:#0F2D59;">${escHtml(bed.room_type)}</span>`
            : '<span style="color:#94A3B8;">-</span>';

        return `
        <tr>
            <td><strong style="color:#0F2D59;">${escHtml(bed.ward_name || '-')}</strong></td>
            <td><strong style="color:#0072BC;">${escHtml(bed.bed_number || '-')}</strong></td>
            <td>${escHtml(bed.room_number || '-')}</td>
            <td>${typeBadge}</td>
            <td>${statusTag}</td>
            <td>${bed.patient_name ? escHtml(bed.patient_name) : '<span style="color:#94A3B8;">-</span>'}</td>
        </tr>`;
    }).join('');
}

function setupEventListeners() {
    const wardSel = document.getElementById('filter-ward-admission');
    if (wardSel) {
        wardSel.addEventListener('change', function() {
            loadBeds(this.value);
        });
    }
    const refreshBtn = document.getElementById('refresh-beds-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            loadBeds(document.getElementById('filter-ward-admission').value);
        });
    }
}

function escHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
</script>