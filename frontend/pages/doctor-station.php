<style>
    /* ============ DOCTOR STATION : OPD/IPD notes & discharge summaries ============
       Scoped under #docstation. The shell supplies card/card-body/table/btn/
       modal/form-group, so only the station-specific pieces are defined here. */
    #docstation{--ds-red:#C0392B;--ds-ink:#0F2D59;--ds-mute:#64748B;--ds-line:#DCE4EC}
    #docstation .mb-2{margin-bottom:.5rem}
    #docstation .mb-3{margin-bottom:1rem}
    #docstation .mb-4{margin-bottom:1.5rem}
    #docstation .gap-2{gap:.5rem}
    #docstation .flex{display:flex}
    #docstation .wrap{flex-wrap:wrap}
    #docstation .items-center{align-items:center}
    #docstation .justify-between{justify-content:space-between}
    #docstation .text-muted{color:var(--ds-mute)}
    #docstation .fw-bold{font-weight:700}
    #docstation .text-uppercase{text-transform:uppercase}
    #docstation .small{font-size:12px}
    #docstation .container-fluid{padding:0}

    /* Red station banner, as in the pasted design */
    #ds-banner{background:var(--ds-red);color:#fff;border-radius:5px;padding:10px 14px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
    #ds-banner h5{margin:0;font-size:13px;font-weight:800;letter-spacing:.3px;text-transform:uppercase}
    #ds-banner .ds-sub{font-size:11px;opacity:.9;margin-top:2px}
    #ds-banner .ds-nav{background:#fff;color:var(--ds-red);border:none;border-radius:4px;padding:7px 13px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
    #ds-banner .ds-nav:hover{background:#F8D7D3}

    /* Stat strip: in-patients, notes written, summaries awaiting discharge */
    #ds-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:16px}
    #ds-stats .s{background:#fff;border:1px solid var(--ds-line);border-left:4px solid var(--ds-red);border-radius:5px;padding:10px 12px}
    #ds-stats .s .lbl{font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--ds-mute)}
    #ds-stats .s .val{font-size:21px;font-weight:800;color:var(--ds-ink);line-height:1.2;margin-top:3px}
    #ds-stats .s .sub{font-size:10.5px;color:var(--ds-mute)}
    #ds-stats .s.notes{border-left-color:#0b5fa5}
    #ds-stats .s.sum{border-left-color:#B9770E}
    #ds-stats .s.dis{border-left-color:#1E7A34}

    #ds-search{width:280px}
    #ds-table td{vertical-align:middle}
    #ds-table .ds-code{font-family:Consolas,'Courier New',monospace;font-weight:700;color:var(--ds-ink);font-size:11px}
    #ds-table .ds-name{font-weight:700;color:#1E293B}
    #ds-table .ds-note-count{font-size:10.5px;color:var(--ds-mute);margin-top:2px}
    #ds-table .ds-actions{display:flex;gap:5px;flex-wrap:wrap}
    #ds-table .ds-actions button{font-size:10px;padding:4px 8px;border-radius:3px;border:1px solid #CBD5E1;background:#fff;color:#334155;cursor:pointer;font-family:inherit;font-weight:600;display:inline-flex;align-items:center;gap:4px}
    #ds-table .ds-actions button.primary{background:var(--ds-red);border-color:var(--ds-red);color:#fff}
    #ds-table .ds-actions button.primary:hover{background:#96281B}
    #ds-table .ds-actions button:hover{background:#F1F5F9}
    #ds-table .ds-actions button.primary:hover{background:#96281B}
    #ds-table .ds-actions button.view:hover{background:#E7F3FC}
    #ds-empty{padding:22px;text-align:center;color:#8A94A6;font-size:12px}

    /* Entry modal */
    #ds-note-modal .modal-content{max-width:720px !important}
    #ds-note-modal .ds-head{background:var(--ds-red);color:#fff;margin:-16px -16px 16px;padding:12px 16px;border-radius:4px 4px 0 0}
    #ds-note-modal .ds-head h3{margin:0;font-size:14px;font-weight:800;letter-spacing:.3px}
    #ds-note-modal .ds-head .ds-head-sub{font-size:11.5px;opacity:.92;margin-top:3px}
    #ds-note-modal label{display:block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#64748B;margin-bottom:5px}
    #ds-note-modal select,#ds-note-modal textarea{width:100%;padding:8px 10px;border:1px solid #b2c8de;border-radius:3px;font-size:12px;font-family:inherit;background:#fff;color:#222;box-sizing:border-box}
    #ds-note-modal textarea{resize:vertical;min-height:130px;line-height:1.5}
    #ds-note-modal select:focus,#ds-note-modal textarea:focus{outline:none;border-color:var(--ds-red);box-shadow:0 0 4px rgba(192,57,43,.22)}
    #ds-note-modal .ds-type-hint{font-size:10.5px;color:#8A94A6;margin-top:5px}
    #ds-note-modal .ds-patient-bar{display:flex;justify-content:space-between;align-items:center;gap:10px;background:#F1F5F9;border:1px solid #C9D4E0;border-radius:4px;padding:9px 12px;margin-bottom:14px;font-size:12px;color:#334155;flex-wrap:wrap}
    #ds-note-modal .ds-patient-bar b{color:#0b5fa5}
    #ds-note-modal .ds-warn{background:#FEF5E0;border:1px solid #F0AD4E;border-left:4px solid #f0ad4e;color:#8A5A00;border-radius:4px;padding:9px 11px;font-size:11.5px;margin-bottom:14px;display:none}
    #ds-note-modal .ds-warn.show{display:block}
    #ds-note-modal .ds-summary-only{display:none}
    #ds-note-modal .ds-summary-only.show{display:block}
    #ds-note-modal .ds-foot-note{font-size:10.5px;color:#8A94A6;margin-top:6px}

    /* Notes history drawer inside the modal */
    #ds-history{border:1px solid var(--ds-line);border-radius:5px;overflow:hidden;margin-top:14px}
    #ds-history .hd{background:#E6EEF5;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#222;padding:7px 9px;border-bottom:2px solid #b2c8de;display:flex;justify-content:space-between;gap:8px;align-items:center}
    #ds-history .bd{max-height:200px;overflow-y:auto}
    #ds-history table{width:100%;border-collapse:collapse;font-size:11px}
    #ds-history th{background:#F1F5F9;font-size:9.5px;text-transform:uppercase;letter-spacing:.3px;color:#475569;padding:5px 8px;text-align:left;border-bottom:1px solid #E1E8F0;position:sticky;top:0}
    #ds-history td{padding:6px 8px;border-bottom:1px solid #EEF2F7;vertical-align:top}
    #ds-history .nt{font-weight:700;font-size:9.5px;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
    #ds-history .nt.opd{color:#0b5fa5}
    #ds-history .nt.ipd{color:#1E7A34}
    #ds-history .nt.dsum{color:#B9770E}
    #ds-history .txt{color:#334155;white-space:pre-wrap;line-height:1.45;max-height:96px;overflow-y:auto}
    #ds-history .meta{font-size:10px;color:#8A94A6;margin-top:3px;white-space:nowrap}
    #ds-history .rm{background:#fff;border:1px solid #F0B7B2;color:#C0392B;border-radius:3px;font-size:9.5px;font-weight:700;padding:3px 6px;cursor:pointer;font-family:inherit;white-space:nowrap}
    #ds-history .rm:hover{background:#FDECEA}
    #ds-history .empty{padding:14px;text-align:center;color:#8A94A6}

    @media (max-width:640px){
        #ds-banner{flex-direction:column;align-items:flex-start}
        #ds-search{width:100%}
    }
</style>

<div id="docstation" class="container-fluid">
    <!-- Station banner -->
    <div id="ds-banner">
        <div>
            <h5><i class="fa-solid fa-user-doctor"></i> Doctor Station &mdash; OPD/IPD Notes &amp; Discharge Summaries</h5>
            <div class="ds-sub">Clinical entries are written against the patient's stay and appear on the discharge record.</div>
        </div>
        <button type="button" class="ds-nav" id="ds-back-to-admissions"><i class="fa-solid fa-arrow-left"></i> Admissions</button>
    </div>

    <!-- Stats -->
    <div id="ds-stats">
        <div class="s"><div class="lbl">In-Patients</div><div class="val" id="ds-stat-inpatients">&mdash;</div><div class="sub">currently admitted</div></div>
        <div class="s notes"><div class="lbl">Round Notes Written</div><div class="val" id="ds-stat-notes">&mdash;</div><div class="sub">IPD daily notes</div></div>
        <div class="s sum"><div class="lbl">Summaries Awaiting</div><div class="val" id="ds-stat-summaries">&mdash;</div><div class="sub">no discharge summary yet</div></div>
        <div class="s dis"><div class="lbl">Stays With Summary</div><div class="val" id="ds-stat-done">&mdash;</div><div class="sub">ready for discharge</div></div>
    </div>

    <!-- In-patient list -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-stethoscope"></i> Current In-Patients &mdash; Clinical Entries</h2>
            <div class="flex items-center gap-2 wrap">
                <input type="text" id="ds-search" class="form-control" placeholder="Search patient, hospital no., ward or bed..." style="padding:6px 9px;border:1px solid #C9D4E0;border-radius:4px;font-size:12px;font-family:inherit;background:#fff;">
                <select id="ds-filter-entry" style="padding:6px 9px;border:1px solid #C9D4E0;border-radius:4px;font-size:12px;font-family:inherit;background:#fff;">
                    <option value="ALL">All Entries</option>
                    <option value="OPD_NOTE">OPD Consultation Note</option>
                    <option value="IPD_NOTE">IPD Daily Round Note</option>
                    <option value="DISCHARGE_SUMMARY">Discharge Summary</option>
                </select>
                <button class="btn btn-secondary btn-sm" id="ds-refresh-btn"><i class="fa-solid fa-rotate"></i> Refresh</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table id="ds-table">
                    <thead>
                        <tr>
                            <th>Hospital No.</th>
                            <th>Patient</th>
                            <th>Ward / Bed</th>
                            <th>Admitted</th>
                            <th>Diagnosis</th>
                            <th>Clinical Record</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ds-table-body">
                        <tr><td colspan="7" style="text-align:center;">Loading in-patients...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Clinical Entry Modal -->
<div class="modal" id="ds-note-modal">
    <div class="modal-content">
        <div class="ds-head">
            <h3>CLINICAL ENTRY</h3>
            <div class="ds-head-sub" id="ds-note-patient">&mdash;</div>
        </div>
        <div class="modal-body">
            <div class="ds-patient-bar">
                <span id="ds-bar-admission">&mdash;</span>
                <span id="ds-bar-bed">&mdash;</span>
            </div>

            <div class="ds-warn" id="ds-summary-warn">
                <i class="fa-solid fa-triangle-exclamation"></i>
                A discharge summary already exists for this stay. Saving another entry of this type is blocked &mdash;
                the patient still has to be discharged from the Admissions page.
            </div>

            <div class="form-group mb-3">
                <label for="ds-note-type">ENTRY TYPE *</label>
                <select id="ds-note-type">
                    <option value="OPD_NOTE">OPD Consultation Note</option>
                    <option value="IPD_NOTE" selected>IPD Daily Clinical Round Note</option>
                    <option value="DISCHARGE_SUMMARY">Discharge Summary</option>
                </select>
                <div class="ds-type-hint" id="ds-type-hint">Daily progress note for this admission. Appears on the patient's clinical record.</div>
            </div>

            <div class="form-group mb-2">
                <label for="ds-note-text">CLINICAL NOTES / RECOMMENDATIONS *</label>
                <textarea id="ds-note-text" placeholder="Enter clinical observations, diagnosis, treatment plan, or discharge summary..."></textarea>
                <div class="ds-foot-note">Saved against admission <span id="ds-foot-code">—</span> under your name, with a timestamp.</div>
            </div>

            <div id="ds-history">
                <div class="hd">
                    <span>CLINICAL RECORD FOR THIS STAY</span>
                    <span id="ds-history-count">0 entries</span>
                </div>
                <div class="bd" id="ds-history-body">
                    <div class="empty">Loading...</div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="ds-cancel-note">Close</button>
            <button class="btn btn-primary" id="ds-save-note"><i class="fa-solid fa-check"></i> Save Clinical Entry</button>
        </div>
    </div>
</div>

<script>
let dsInpatients = [];
let dsNotes = [];
let dsTarget = null;
let dsSearchTimer = null;

const DS_TYPE_LABELS = {
    OPD_NOTE: 'OPD Consultation Note',
    IPD_NOTE: 'IPD Daily Round Note',
    DISCHARGE_SUMMARY: 'Discharge Summary'
};
const DS_TYPE_HINTS = {
    OPD_NOTE: 'Outpatient consultation note for this patient. Stored with the visit when one is linked.',
    IPD_NOTE: 'Daily progress note for this admission. Appears on the patient’s clinical record.',
    DISCHARGE_SUMMARY: 'Written on the admission as its discharge summary. The patient is still discharged from the Admissions page, not here.'
};

function initDoctorStation() {
    document.getElementById('ds-refresh-btn').addEventListener('click', loadInpatients);
    document.getElementById('ds-cancel-note').addEventListener('click', closeNoteModal);
    document.getElementById('ds-save-note').addEventListener('click', saveClinicalNote);
    document.getElementById('ds-note-type').addEventListener('change', onNoteTypeChange);

    const back = document.getElementById('ds-back-to-admissions');
    if (back) back.addEventListener('click', () => navigateTo('admissions'));

    const filter = document.getElementById('ds-filter-entry');
    if (filter) filter.addEventListener('change', renderInpatients);

    const search = document.getElementById('ds-search');
    if (search) {
        search.addEventListener('input', () => {
            clearTimeout(dsSearchTimer);
            dsSearchTimer = setTimeout(loadInpatients, 300);
        });
    }

    loadInpatients();
}

async function loadInpatients() {
    const tbody = document.getElementById('ds-table-body');
    const q = document.getElementById('ds-search').value.trim();

    const params = new URLSearchParams();
    params.set('action', 'inpatients');
    if (q) params.set('q', q);

    try {
        const response = await fetch('/hms/backend/api/clinical_notes.php?' + params.toString());
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load in-patients');

        dsInpatients = data.inpatients || [];
        renderInpatients();
        renderStationStats();
    } catch (error) {
        console.error('Doctor station load error:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Failed to load in-patients</td></tr>';
    }
}

function renderInpatients() {
    const tbody = document.getElementById('ds-table-body');
    if (!tbody) return;

    // The entry-type filter narrows what each stay shows in its record column;
    // a stay with no entry of that type is hidden rather than shown empty.
    const filter = document.getElementById('ds-filter-entry').value;
    if (filter === 'ALL') {
        tbody.innerHTML = dsInpatients.length
            ? dsInpatients.map(inpatientRow).join('')
            : '<tr><td colspan="7"><div id="ds-empty">No in-patients found</div></td></tr>';
        return;
    }
    // A type-specific filter needs the notes per stay, so they are fetched once.
    loadNotesForType(filter);
}

function inpatientRow(p) {
    const notes = dsNotes.filter(n => String(n.admission_id) === String(p.admission_id));
    const record = clinicalRecordCell(p, notes);
    return '<tr>'
        + '<td><span class="ds-code">' + escHtml(p.hospital_number || '-') + '</span></td>'
        + '<td><span class="ds-name">' + escHtml(p.patient_name || '-') + '</span>'
            + '<div class="ds-note-count">' + escHtml(p.gender || '') + (p.age !== null && p.age !== undefined ? ' &middot; ' + escHtml(p.age) + ' yrs' : '') + '</div></td>'
        + '<td>' + escHtml(p.ward_name || '-') + ' &middot; Bed ' + escHtml(p.bed_number || '-') + '</td>'
        + '<td>' + fmtDateTime(p.admission_date) + '<div class="ds-note-count">' + escHtml(p.admission_type || '') + '</div></td>'
        + '<td>' + (p.diagnosis ? escHtml(p.diagnosis) : '<span style="color:#95A5A6;">—</span>') + '</td>'
        + record
        + '<td><div class="ds-actions">'
            + '<button class="primary" data-ds-open="' + p.admission_id + '"><i class="fa-solid fa-plus"></i> Add Note / Discharge Summary</button>'
            + '<button class="view" data-ds-history="' + p.admission_id + '"><i class="fa-solid fa-clock-rotate-left"></i> Record</button>'
        + '</div></td>'
        + '</tr>';
}

function clinicalRecordCell(p, notes) {
    if (!notes.length) {
        return '<td><span style="color:#95A5A6;">No entries yet</span></td>';
    }
    const tagFor = t => t === 'DISCHARGE_SUMMARY' ? 'dsum' : (t === 'IPD_NOTE' ? 'ipd' : 'opd');
    const items = notes.slice(0, 3).map(n =>
        '<div class="nt ' + tagFor(n.note_type) + '">' + escHtml(DS_TYPE_LABELS[n.note_type] || n.note_type) + '</div>'
        + '<div class="txt" style="font-size:10.5px;color:#475569;max-height:34px;overflow:hidden;white-space:pre-wrap;">' + escHtml(n.clinical_note) + '</div>'
        + '<div class="meta">' + fmtDateTime(n.created_at) + ' &middot; ' + escHtml(n.doctor_name || '') + '</div>'
    ).join('<div style="height:6px"></div>');
    const more = notes.length > 3 ? '<div class="meta">+ ' + (notes.length - 3) + ' more entr' + (notes.length - 3 === 1 ? 'y' : 'ies') + '</div>' : '';
    return '<td>' + items + more + '</td>';
}

function renderStationStats() {
    let notes = 0, awaiting = 0, done = 0;
    dsInpatients.forEach(p => {
        if (Number(p.ipd_note_count) > 0) notes++;
        if (Number(p.summary_count) > 0) done++; else awaiting++;
    });
    setText('ds-stat-inpatients', dsInpatients.length);
    setText('ds-stat-notes', notes);
    setText('ds-stat-summaries', awaiting);
    setText('ds-stat-done', done);
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

async function loadNotesForType(type) {
    const tbody = document.getElementById('ds-table-body');
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Loading entries...</td></tr>';
    try {
        const response = await fetch('/hms/backend/api/clinical_notes.php?action=list&note_type=' + type);
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load entries');
        dsNotes = data.notes || [];

        const wanted = dsInpatients.filter(p => dsNotes.some(n => String(n.admission_id) === String(p.admission_id)));
        tbody.innerHTML = wanted.length
            ? wanted.map(inpatientRow).join('')
            : '<tr><td colspan="7"><div id="ds-empty">No in-patients with a ' + escHtml(DS_TYPE_LABELS[type] || type) + ' yet</div></td></tr>';
    } catch (error) {
        console.error('Doctor station notes error:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Failed to load entries</td></tr>';
    }
}

// Delegated clicks so re-rendering the table keeps the buttons working.
document.addEventListener('click', function (e) {
    const openBtn = e.target.closest('[data-ds-open]');
    if (openBtn) { openNoteModal(openBtn.getAttribute('data-ds-open'), true); return; }

    const histBtn = e.target.closest('[data-ds-history]');
    if (histBtn) { openNoteModal(histBtn.getAttribute('data-ds-history'), false); return; }

    const rmBtn = e.target.closest('[data-ds-delete-note]');
    if (rmBtn) { deleteClinicalNote(rmBtn.getAttribute('data-ds-delete-note')); }
});

async function openNoteModal(admissionId, startNewEntry) {
    const target = dsInpatients.find(p => String(p.admission_id) === String(admissionId));
    if (!target) return;
    dsTarget = target;

    document.getElementById('ds-note-patient').textContent =
        (target.patient_name || 'Patient') + ' (' + (target.hospital_number || '-') + ')';
    document.getElementById('ds-bar-admission').innerHTML =
        '<b>ADMISSION:</b> ' + escHtml(target.admission_code || '-');
    document.getElementById('ds-bar-bed').innerHTML =
        '<b>WARD / BED:</b> ' + escHtml(target.ward_name || '-') + ' &middot; Bed ' + escHtml(target.bed_number || '-');
    document.getElementById('ds-foot-code').textContent = target.admission_code || '—';

    if (startNewEntry) {
        document.getElementById('ds-note-text').value = '';
        // A round note is the safe default: it is the one type always accepted
        // for a live stay, whereas a stay that already has a summary would have
        // its second summary refused by the API.
        document.getElementById('ds-note-type').value = 'IPD_NOTE';
    }
    onNoteTypeChange();

    document.getElementById('ds-note-modal').classList.add('show');
    await loadStayNotes(target.admission_id);
    if (startNewEntry) document.getElementById('ds-note-text').focus();
}

function closeNoteModal() {
    document.getElementById('ds-note-modal').classList.remove('show');
    dsTarget = null;
}

function onNoteTypeChange() {
    const type = document.getElementById('ds-note-type').value;
    const hint = document.getElementById('ds-type-hint');
    if (hint) hint.textContent = DS_TYPE_HINTS[type] || '';

    const warn = document.getElementById('ds-summary-warn');
    if (warn) warn.classList.toggle('show', type === 'DISCHARGE_SUMMARY' && dsTarget && Number(dsTarget.summary_count) > 0);
}

async function loadStayNotes(admissionId) {
    const body = document.getElementById('ds-history-body');
    const count = document.getElementById('ds-history-count');
    body.innerHTML = '<div class="empty">Loading...</div>';
    try {
        const response = await fetch('/hms/backend/api/clinical_notes.php?action=list&admission_id=' + admissionId);
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load the clinical record');

        const notes = data.notes || [];
        count.textContent = notes.length + (notes.length === 1 ? ' entry' : ' entries');

        if (!notes.length) {
            body.innerHTML = '<div class="empty">No clinical entries recorded for this stay yet.</div>';
            return;
        }
        const tagFor = t => t === 'DISCHARGE_SUMMARY' ? 'dsum' : (t === 'IPD_NOTE' ? 'ipd' : 'opd');
        body.innerHTML = '<table><thead><tr><th>Type</th><th>Entry</th><th>Recorded</th><th></th></tr></thead><tbody>'
            + notes.map(n =>
                '<tr>'
                + '<td><span class="nt ' + tagFor(n.note_type) + '">' + escHtml(DS_TYPE_LABELS[n.note_type] || n.note_type) + '</span></td>'
                + '<td><div class="txt">' + escHtml(n.clinical_note) + '</div></td>'
                + '<td><span class="meta" style="margin:0;">' + fmtDateTime(n.created_at) + '<br>' + escHtml(n.doctor_name || '') + '</span></td>'
                + '<td><button class="rm" data-ds-delete-note="' + n.id + '">Delete</button></td>'
                + '</tr>'
            ).join('')
            + '</tbody></table>';
    } catch (error) {
        body.innerHTML = '<div class="empty">Could not load the clinical record — ' + escHtml(error.message) + '</div>';
    }
}

async function saveClinicalNote() {
    if (!dsTarget) return;
    const text = document.getElementById('ds-note-text').value.trim();
    if (!text) { showAlert('Enter the clinical notes before saving', 'error'); return; }

    const type = document.getElementById('ds-note-type').value;
    const btn = document.getElementById('ds-save-note');
    btn.disabled = true;
    try {
        const res = await fetch('/hms/backend/api/clinical_notes.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                patient_id: dsTarget.patient_id,
                admission_id: dsTarget.admission_id,
                note_type: type,
                clinical_note: text
            })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not save the entry');

        showAlert((DS_TYPE_LABELS[type] || 'Clinical entry') + ' saved', 'success');
        document.getElementById('ds-note-text').value = '';
        closeNoteModal();
        await loadInpatients();
    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        btn.disabled = false;
    }
}

async function deleteClinicalNote(id) {
    if (!confirm('Delete this clinical entry?\n\nThe record cannot be recovered.')) return;
    try {
        const res = await fetch('/hms/backend/api/clinical_notes.php?action=delete&id=' + id, { method: 'DELETE' });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not delete the entry');

        showAlert('Clinical entry deleted', 'success');
        if (dsTarget) await loadStayNotes(dsTarget.admission_id);
        await loadInpatients();
    } catch (error) {
        showAlert(error.message, 'error');
    }
}
</script>
