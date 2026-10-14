/* ============================================================================
   Patient Master Folder Banner — shared, system-wide patient header.
   Ported from the pasted Tailwind/Alpine "System-Wide Patient Master Folder
   Banner" mock into plain JS (the project ships no Tailwind/Alpine), wired to
   the live APIs:

     GET  patients.php?action=summary&id=N          -> folder demographics
     GET  invoices.php?action=patient_billing&...    -> outstanding dues
     POST patients.php?action=pregnancy              -> save an ANC record
     POST patients.php?action=mortality              -> save a mortality record

   Public API:
     HMSPatientFolder.mount(host, patientId[, opts])
     HMSPatientFolder.refresh(host)
     HMSPatientFolder.clear(host)
     HMSPatientFolder.get(host, 'patientId'|'data'|'billing')

   opts: { showBilling:true, onClose:fn }
   ============================================================================ */
window.HMSPatientFolder = (function () {
    'use strict';

    var API = '/hms/backend/api';
    var HKEY = '__hmsPatientFolder';

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = (s === null || s === undefined) ? '' : String(s);
        return d.innerHTML;
    }
    function val(v) {
        if (v === null || v === undefined || v === '') return '\u2014';
        return esc(v);
    }
    function money(n) {
        var num = Number(n || 0);
        return 'GH\u20B5 ' + num.toFixed(2);
    }
    function fmtD(v) {
        if (!v) return null;
        if (window.fmtDate) return window.fmtDate(v);
        return String(v).substring(0, 10);
    }
    function toLocalInput(v) {
        // 'YYYY-MM-DD HH:MM:SS' -> 'YYYY-MM-DDTHH:MM' for datetime-local.
        if (!v) return '';
        var s = String(v).replace(' ', 'T');
        return s.length >= 16 ? s.substring(0, 16) : s;
    }
    function attr(s) { return esc(s).replace(/"/g, '&quot;'); }

    function hostOf(ref) {
        if (!ref) return null;
        return (typeof ref === 'string') ? document.querySelector(ref) : ref;
    }

    function getJson(url) {
        return fetch(url, { credentials: 'same-origin' })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); });
    }
    function postJson(url, body) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }).then(function (r) {
            return r.json().catch(function () { return { success: r.ok }; })
                .then(function (j) { if (!r.ok && !j.error) j.error = 'HTTP ' + r.status; return j; });
        });
    }

    function isFemale(data) { return String((data && data.gender) || '').toLowerCase().indexOf('female') === 0; }
    function isNHIS(data) { return data && data.payment_type === 'NHIS'; }

    function rowHTML(label, valueHTML) {
        return '<div class="hms-pf-row"><span class="k">' + esc(label) + '</span><span class="v">' + valueHTML + '</span></div>';
    }

    function render(state) {
        var p = state.data || {};
        var b = state.billing || null;
        var due = (b && b.outstanding != null) ? Number(b.outstanding) : 0;

        var initials = (p.first_name || p.full_name || '?').charAt(0);

        /* ---- status pills ---- */
        var pills = '';
        pills += '<span class="hms-pf-pill id">' + val(p.hospital_number || ('P' + p.patient_id)) + '</span>';
        if (isNHIS(p)) {
            var nhisOn = !!p.nhis_active;
            pills += '<span class="hms-pf-pill ' + (nhisOn ? 'nhis-on' : 'nhis-off') + '">' +
                (nhisOn ? '<span class="hms-pf-dot"></span>' : '') +
                'NHIS' + (p.nhia_number ? ' (' + esc(p.nhia_number) + ')' : '') +
                ' \u2014 ' + (nhisOn ? 'ACTIVE' : 'INACTIVE') + '</span>';
        } else {
            pills += '<span class="hms-pf-pill selfpay">SELF PAY (CASH)</span>';
        }
        pills += '<span class="hms-pf-pill type">Type: ' + val(p.patient_type) + '</span>';
        if (p.is_deceased) pills += '<span class="hms-pf-pill deceased">\u2020 DECEASED</span>';

        /* ---- demographics grid ---- */
        var ageTxt = p.age_text ? val(p.age_text) : (p.age != null ? esc(p.age) + ' Yrs' : '\u2014');
        var left = [
            rowHTML('Name', '<span class="v strong">' + val(p.full_name) + '</span>'),
            rowHTML('Gender', val(p.gender)),
            rowHTML('Age', ageTxt),
            rowHTML('Marital Status', val(p.marital_status)),
            rowHTML('Contact', '<b>' + val(p.phone) + '</b>'),
            rowHTML('Occupation', val(p.occupation)),
            rowHTML('WhatsApp Number', val(p.whatsapp))
        ].join('');
        var right = [
            rowHTML('Patient No.', '<span class="v brand">' + val(p.hospital_number || ('P' + p.patient_id)) + '</span>'),
            rowHTML('Type', '<b>' + val(p.patient_type) + '</b>'),
            rowHTML('Date of Birth', val(fmtD(p.date_of_birth))),
            rowHTML('Register Date', val(fmtD(p.registration_date))),
            rowHTML('Address', '<b>' + val(p.address) + '</b>'),
            rowHTML('Email', val(p.email)),
            rowHTML('Nationality', '<b>' + val(p.nationality) + '</b>'),
            rowHTML('Blood Group', val(p.blood_group) + (p.allergies ? ' \u00b7 <span style="color:#BE123C;font-weight:800">Allergies: ' + esc(p.allergies) + '</span>' : ''))
        ].join('');

        /* ---- special clinical actions ---- */
        var pregChecked = state.showPreg ? ' checked' : '';
        var deadChecked = state.showDeath ? ' checked' : '';
        var pregBox = isFemale(p)
            ? '<label class="hms-pf-check preg"><input type="checkbox" data-pf-preg-toggle' + pregChecked + '> ' +
              'Tick if Pregnant (Open Pregnancy Form)</label>'
            : '';
        var actionsBar =
            '<div class="hms-pf-actions-bar">' +
                '<span class="lead"><i class="fa-solid fa-notes-medical"></i> Special Clinical Actions</span>' +
                pregBox +
                '<label class="hms-pf-check dead"><input type="checkbox" data-pf-dead-toggle' + deadChecked + '> ' +
                'Tick if Deceased / Died (Open Mortality Entry)</label>' +
            '</div>';

        /* ---- pregnancy form ---- */
        var pg = p.pregnancy || {};
        var pregForm = isFemale(p)
            ? '<div class="hms-pf-form preg" data-pf-preg-form' + (state.showPreg ? '' : ' hidden') + '>' +
                '<div class="hms-pf-form-head"><span><i class="fa-solid fa-baby"></i> Pregnancy &amp; Maternity Registration</span>' +
                '<span>ANC Record</span></div>' +
                '<div class="hms-pf-form-grid">' +
                    field('Gestational Age (Weeks)', '<input type="number" min="0" max="45" data-pf-preg-age value="' + (pg.gestational_age != null ? esc(pg.gestational_age) : '') + '" placeholder="e.g. 24">') +
                    field('Last Menstrual Period (LMP)', '<input type="date" data-pf-preg-lmp value="' + attr(pg.lmp_date ? String(pg.lmp_date).substring(0, 10) : '') + '">') +
                    field('Expected Date of Delivery (EDD)', '<input type="date" data-pf-preg-edd value="' + attr(pg.edd_date ? String(pg.edd_date).substring(0, 10) : '') + '">') +
                '</div>' +
                (p.is_pregnant && pg.recorded_at ? '<div class="hms-pf-note">Current record opened ' + esc(fmtD(pg.recorded_at)) + '.</div>' : '') +
                '<div class="hms-pf-form-foot"><button type="button" class="hms-pf-btn" data-pf-preg-save>' +
                '<i class="fa-solid fa-floppy-disk"></i> Save Pregnancy Record</button></div>' +
              '</div>'
            : '';

        /* ---- mortality form ---- */
        var dt = p.death || {};
        var deadForm =
            '<div class="hms-pf-form dead" data-pf-dead-form' + (state.showDeath ? '' : ' hidden') + '>' +
                '<div class="hms-pf-form-head"><span><i class="fa-solid fa-skull"></i> Deceased / Mortality Registration</span>' +
                '<span>Certify Death</span></div>' +
                '<div class="hms-pf-form-grid">' +
                    field('Date &amp; Time of Death', '<input type="datetime-local" data-pf-dead-when value="' + attr(toLocalInput(dt.death_datetime)) + '">') +
                    field('Primary Cause of Death', '<input type="text" data-pf-dead-cause value="' + attr(dt.cause_primary || '') + '" placeholder="e.g. Respiratory failure">') +
                    field('Certifying Doctor', '<input type="text" data-pf-dead-doctor value="' + attr(dt.certifying_doctor || '') + '" placeholder="Dr. Name">') +
                '</div>' +
                (p.is_deceased && dt.recorded_at ? '<div class="hms-pf-note">Recorded ' + esc(fmtD(dt.recorded_at)) + '.</div>' : '') +
                '<div class="hms-pf-form-foot"><button type="button" class="hms-pf-btn" data-pf-dead-save>' +
                '<i class="fa-solid fa-floppy-disk"></i> Register Mortality Entry</button></div>' +
              '</div>';

        var folder =
            '<div class="hms-pf-folder" data-pf-folder' + (state.showFolder ? '' : ' hidden') + '>' +
                '<div class="hms-pf-grid">' +
                    '<div>' + left + '</div>' +
                    '<div>' + right + '</div>' +
                '</div>' +
                actionsBar + pregForm + deadForm +
            '</div>';

        return '' +
            '<div class="hms-pf-stack">' +
                '<div class="hms-pf-bar">' +
                    '<div class="hms-pf-idwrap">' +
                        '<div class="hms-pf-avatar">' + esc(initials) + '</div>' +
                        '<h3 class="hms-pf-name">' + val(p.full_name) + '</h3>' +
                        pills +
                    '</div>' +
                    '<div class="hms-pf-actions">' +
                        '<span class="hms-pf-dues' + (due > 0 ? ' owed' : '') + '">' + money(due) + ' Due</span>' +
                        '<button type="button" class="hms-pf-btn ghost" data-pf-toggle>' +
                            '<i class="fa-solid ' + (state.showFolder ? 'fa-chevron-up' : 'fa-chevron-down') + '"></i>' +
                            '<span data-pf-toggle-label>' + (state.showFolder ? 'Hide Profile' : 'Show Profile') + '</span>' +
                        '</button>' +
                        '<button type="button" class="hms-pf-close" data-pf-close title="Close patient folder">&times;</button>' +
                    '</div>' +
                '</div>' +
                folder +
            '</div>';
    }

    function field(label, inputHTML) {
        return '<div class="hms-pf-field"><label>' + label + '</label>' + inputHTML + '</div>';
    }

    function paint(host) {
        var st = host[HKEY];
        if (!st || !st.data) return;
        host.innerHTML = '<div class="hms-pf">' + render(st) + '</div>';
        bind(host);
    }

    function bind(host) {
        var st = host[HKEY];
        var root = host.querySelector('.hms-pf');
        if (!root) return;

        function qa(sel) { return root.querySelector(sel); }

        var toggle = qa('[data-pf-toggle]');
        if (toggle) toggle.addEventListener('click', function () {
            st.showFolder = !st.showFolder;
            paint(host);
        });

        var close = qa('[data-pf-close]');
        if (close) close.addEventListener('click', function () {
            var cb = st.opts && st.opts.onClose;
            clear(host);
            if (typeof cb === 'function') cb(st.patientId);
        });

        var pregToggle = qa('[data-pf-preg-toggle]');
        if (pregToggle) pregToggle.addEventListener('change', function () {
            st.showPreg = pregToggle.checked;
            var f = qa('[data-pf-preg-form]');
            if (f) f.hidden = !st.showPreg;
        });

        var deadToggle = qa('[data-pf-dead-toggle]');
        if (deadToggle) deadToggle.addEventListener('change', function () {
            st.showDeath = deadToggle.checked;
            var f = qa('[data-pf-dead-form]');
            if (f) f.hidden = !st.showDeath;
        });

        var pregSave = qa('[data-pf-preg-save]');
        if (pregSave) pregSave.addEventListener('click', function () {
            var g = qa('[data-pf-preg-age]');
            var l = qa('[data-pf-preg-lmp]');
            var e = qa('[data-pf-preg-edd]');
            var body = {
                patient_id: st.patientId,
                gestational_age: g && g.value !== '' ? parseInt(g.value, 10) : null,
                lmp_date: l && l.value ? l.value : null,
                edd_date: e && e.value ? e.value : null
            };
            pregSave.disabled = true;
            postJson(API + '/patients.php?action=pregnancy', body).then(function (j) {
                pregSave.disabled = false;
                if (j && j.success) {
                    if (window.showAlert) showAlert('Pregnancy record saved.', 'success');
                    load(host, st.patientId, st.opts);
                } else if (window.showAlert) {
                    showAlert((j && j.error) || 'Could not save pregnancy record.', 'error');
                }
            }).catch(function () {
                pregSave.disabled = false;
                if (window.showAlert) showAlert('Network error saving pregnancy record.', 'error');
            });
        });

        var deadSave = qa('[data-pf-dead-save]');
        if (deadSave) deadSave.addEventListener('click', function () {
            var w = qa('[data-pf-dead-when]');
            var c = qa('[data-pf-dead-cause]');
            var d = qa('[data-pf-dead-doctor]');
            var body = {
                patient_id: st.patientId,
                death_datetime: w && w.value ? w.value : null,
                cause_primary: c ? c.value : '',
                certifying_doctor: d ? d.value : ''
            };
            if (!body.cause_primary || !body.cause_primary.trim()) {
                if (window.showAlert) showAlert('Primary cause of death is required.', 'error');
                return;
            }
            deadSave.disabled = true;
            postJson(API + '/patients.php?action=mortality', body).then(function (j) {
                deadSave.disabled = false;
                if (j && j.success) {
                    if (window.showAlert) showAlert('Mortality entry registered.', 'success');
                    load(host, st.patientId, st.opts);
                } else if (window.showAlert) {
                    showAlert((j && j.error) || 'Could not register mortality entry.', 'error');
                }
            }).catch(function () {
                deadSave.disabled = false;
                if (window.showAlert) showAlert('Network error registering mortality entry.', 'error');
            });
        });
    }

    function load(host, patientId, opts) {
        if (!host) return;
        opts = opts || {};
        var st = host[HKEY] || (host[HKEY] = {
            patientId: null, data: null, billing: null,
            showFolder: true, showPreg: false, showDeath: false, opts: {}
        });
        st.patientId = patientId;
        st.opts = opts;

        host.innerHTML = '<div class="hms-pf"><div class="hms-pf-stack"><div class="hms-pf-empty">Loading patient folder\u2026</div></div></div>';

        getJson(API + '/patients.php?action=summary&id=' + encodeURIComponent(patientId))
            .then(function (summary) {
                if (!summary || !summary.success || !summary.patient) throw new Error('not found');
                st.data = summary.patient;
                st.showFolder = true;
                // Re-open the relevant form when a record already exists.
                st.showPreg = !!st.data.is_pregnant;
                st.showDeath = !!st.data.is_deceased;
                if (opts.showBilling === false) { st.billing = null; paint(host); return; }
                return getJson(API + '/invoices.php?action=patient_billing&patient_id=' + encodeURIComponent(patientId))
                    .then(function (b) { st.billing = (b && b.success) ? b : null; })
                    .catch(function () { st.billing = null; })
                    .then(function () { paint(host); });
            })
            .catch(function () {
                host.innerHTML = '<div class="hms-pf"><div class="hms-pf-stack">' +
                    '<div class="hms-pf-empty">Could not load the patient folder. Close and search again.</div></div></div>';
            });
    }

    function clear(host) {
        host = hostOf(host);
        if (!host) return;
        host[HKEY] = null;
        host.innerHTML = '';
    }

    function refresh(host) {
        host = hostOf(host);
        if (!host || !host[HKEY] || !host[HKEY].patientId) return;
        load(host, host[HKEY].patientId, host[HKEY].opts);
    }

    function get(host, key) {
        host = hostOf(host);
        if (!host || !host[HKEY]) return null;
        return host[HKEY][key];
    }

    return {
        mount: function (host, patientId, opts) { load(hostOf(host), patientId, opts); },
        refresh: refresh,
        clear: clear,
        get: get
    };
})();
