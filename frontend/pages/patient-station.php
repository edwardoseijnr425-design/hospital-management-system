<?php
// Patient Station & Diagnostic Investigations (SPA fragment).
//
// Ported from the standalone "Patient Station & Diagnostic Investigations"
// mock (Tailwind + Alpine CDN) into this project's conventions: a page
// FRAGMENT (no <html>/<head>), plain JS + scoped CSS, no Tailwind/Alpine.
//
// It is a patient-centric diagnostics workspace: pick a patient, then read
// their combined laboratory + radiology + other/specialist investigation feed
// and raise new orders. Everything is wired to the real APIs:
//   patients.php?action=summary           -> header + latest vitals
//   invoices.php?action=patient_billing   -> amount due
//   lab.php?action=requests|results       -> laboratory findings
//   radiology.php?action=requests|results -> imaging findings + image ref
//   consultation_form.php?action=other_requests|context|save -> other tests + ordering
require_once __DIR__ . '/../../backend/config/config.php';
$__pstaStaff = getCurrentUserName() ?: 'STAFF';
?>
<style>
/* ============ PATIENT STATION (scoped under #psta-page) ============
   Plain CSS. The project ships no Tailwind/Alpine; every utility is defined
   here. Font Awesome is available. Element ids the JS consumes are namespaced
   psta* to avoid clashing with other fragments. */
#psta-page{--brand:#1b659d;--brand-dk:#155380;--ink:#0F2D59;--mute:#64748B;--line:#e2e8f0;
  --s50:#F8FAFC;--s100:#F1F5F9;--s200:#E2E8F0;--s300:#CBD5E1;--s400:#94A3B8;--s500:#64748B;--s700:#334155;--s800:#1E293B;
  background:#f0f4f8;border-radius:12px;padding:12px;color:var(--s700);font-size:12px;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}
#psta-page *,#psta-page *::before,#psta-page *::after{box-sizing:border-box}

/* ---- Top search + actions ---- */
#psta-page .psta-topbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:12px}
#psta-page .psta-search{position:relative;flex:1;min-width:240px;display:flex;align-items:center}
#psta-page .psta-search>i{position:absolute;left:11px;color:var(--s400);font-size:12px}
#psta-page .psta-search input{width:100%;padding:10px 11px 10px 32px;border:1px solid var(--s300);border-radius:10px;background:#fff;font-size:12px;font-weight:600;color:var(--s800);outline:none;font-family:inherit}
#psta-page .psta-search input:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(27,101,157,.15)}
#psta-page .psta-results{position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid var(--s300);border-radius:10px;max-height:260px;overflow:auto;z-index:60;box-shadow:0 10px 24px rgba(15,23,42,.14);display:none}
#psta-page .psta-results div{padding:9px 11px;cursor:pointer;display:flex;justify-content:space-between;gap:10px;align-items:center}
#psta-page .psta-results div:hover{background:#EFF6FF}
#psta-page .psta-results .psta-num{color:var(--brand);font-weight:800;white-space:nowrap}
#psta-page .psta-results .psta-empty{padding:12px;color:var(--s400);cursor:default;justify-content:center}
#psta-page .psta-topactions{display:flex;gap:8px;flex-wrap:wrap}
#psta-page .psta-btn{background:var(--brand);color:#fff;border:none;border-radius:10px;padding:10px 15px;font-size:11px;font-weight:800;letter-spacing:.3px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase;font-family:inherit;transition:background .15s}
#psta-page .psta-btn:hover{background:var(--brand-dk)}
#psta-page .psta-btn.ghost{background:#fff;color:var(--s700);border:1px solid var(--s300)}
#psta-page .psta-btn.ghost:hover{background:var(--s100)}
#psta-page .psta-btn:disabled{opacity:.55;cursor:not-allowed}

/* ---- Patient header ---- */
#psta-page .psta-header{background:#fff;border:1px solid var(--s200);border-radius:12px;box-shadow:0 1px 2px rgba(15,23,42,.05);padding:14px 16px;margin-bottom:12px}
#psta-page .psta-hrow{display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;align-items:flex-start}
#psta-page .psta-hleft{display:flex;gap:12px;align-items:center;min-width:0}
#psta-page .psta-avatar{width:48px;height:48px;border-radius:12px;background:#EAF2FB;color:var(--brand);display:flex;align-items:center;justify-content:center;font-size:20px;flex:0 0 auto}
#psta-page .psta-name{margin:0;font-size:16px;font-weight:800;color:var(--ink);letter-spacing:.2px}
#psta-page .psta-sub{margin:3px 0 0;font-size:11px;color:var(--mute);font-weight:600;display:flex;gap:10px;flex-wrap:wrap}
#psta-page .psta-sub b{color:var(--s700)}
#psta-page .psta-chips{display:flex;gap:8px;flex-wrap:wrap;align-items:center;justify-content:flex-end}
#psta-page .psta-chip{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.3px;text-transform:uppercase;border:1px solid}
#psta-page .psta-chip .dot{width:7px;height:7px;border-radius:50%}
#psta-page .psta-chip.on{background:#ECFDF5;color:#047857;border-color:#6EE7B7}
#psta-page .psta-chip.on .dot{background:#10B981}
#psta-page .psta-chip.off{background:#F1F5F9;color:#64748B;border-color:#CBD5E1}
#psta-page .psta-chip.off .dot{background:#94A3B8}
#psta-page .psta-chip.due{background:#FFF7ED;color:#B45309;border-color:#FCD34D}
#psta-page .psta-chip.due.clear{background:#ECFDF5;color:#047857;border-color:#6EE7B7}
#psta-page .psta-chip.info{background:#EFF6FF;color:#1D4ED8;border-color:#BFDBFE}
#psta-page .psta-vitals{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;border-top:1px dashed var(--s200);padding-top:10px}
#psta-page .psta-vital{background:var(--s50);border:1px solid var(--s200);border-radius:9px;padding:7px 11px;min-width:96px}
#psta-page .psta-vital .k{display:block;font-size:9px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;color:var(--s400);margin-bottom:2px}
#psta-page .psta-vital .v{font-size:13px;font-weight:800;color:var(--ink)}

/* ---- Tabs ---- */
#psta-page .psta-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px}
#psta-page .psta-tab{border:1px solid var(--s200);background:#fff;color:var(--s600);border-radius:999px;padding:8px 14px;font-size:11px;font-weight:800;letter-spacing:.3px;cursor:pointer;display:inline-flex;align-items:center;gap:7px;text-transform:uppercase;font-family:inherit}
#psta-page .psta-tab.active{background:var(--brand);color:#fff;border-color:var(--brand)}
#psta-page .psta-tab i{font-size:12px}

/* ---- Toolbar + filters ---- */
#psta-page .psta-toolbar{display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:10px}
#psta-page .psta-filters{display:flex;gap:6px;flex-wrap:wrap}
#psta-page .psta-filter{background:#fff;border:1px solid var(--s200);border-radius:999px;padding:6px 12px;font-size:10.5px;font-weight:800;letter-spacing:.3px;color:var(--s600);cursor:pointer;text-transform:uppercase;font-family:inherit}
#psta-page .psta-filter.active{background:#EAF2FB;border-color:var(--brand);color:var(--brand)}
#psta-page .psta-count{font-size:10.5px;font-weight:800;color:var(--s400);text-transform:uppercase;letter-spacing:.3px}

/* ---- Feed ---- */
#psta-page .psta-feed{display:flex;flex-direction:column;gap:12px}
#psta-page .psta-empty{background:#fff;border:1px dashed var(--s300);border-radius:12px;padding:28px;text-align:center;color:var(--s400);font-weight:600}
#psta-page .psta-card{background:#fff;border:1px solid var(--s200);border-radius:12px;box-shadow:0 1px 2px rgba(15,23,42,.05);overflow:hidden}
#psta-page .psta-card-head{display:flex;justify-content:space-between;gap:10px;align-items:center;padding:11px 14px;border-bottom:1px solid var(--s200);background:var(--s50);flex-wrap:wrap}
#psta-page .psta-card-head .l{display:flex;align-items:center;gap:9px;min-width:0}
#psta-page .psta-card-head .l>i{color:var(--brand);font-size:15px}
#psta-page .psta-card-title{font-size:13px;font-weight:800;color:var(--ink);margin:0}
#psta-page .psta-card-cat{font-size:9.5px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;color:var(--s400);margin:2px 0 0}
#psta-page .psta-badge{font-size:9px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;padding:2px 8px;border-radius:5px;border:1px solid;white-space:nowrap}
#psta-page .psta-badge.lab{background:#EFF6FF;color:#1D4ED8;border-color:#BFDBFE}
#psta-page .psta-badge.imaging{background:#F5F3FF;color:#6D28D9;border-color:#DDD6FE}
#psta-page .psta-badge.sono{background:#ECFEFF;color:#0E7490;border-color:#A5F3FC}
#psta-page .psta-badge.other{background:#F1F5F9;color:#475569;border-color:#CBD5E1}
#psta-page .psta-status{font-size:9.5px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;padding:3px 9px;border-radius:999px;border:1px solid}
#psta-page .psta-status.ok{background:#ECFDF5;color:#047857;border-color:#6EE7B7}
#psta-page .psta-status.draft{background:#EFF6FF;color:#1D4ED8;border-color:#BFDBFE}
#psta-page .psta-status.pend{background:#FFFBEB;color:#B45309;border-color:#FCD34D}
#psta-page .psta-status.cancel{background:#FEF2F2;color:#B91C1C;border-color:#FCA5A5}
#psta-page .psta-status.prog{background:#EEF2FF;color:#4338CA;border-color:#C7D2FE}
#psta-page .psta-card-body{padding:13px 14px}
#psta-page .psta-meta{display:flex;gap:14px;flex-wrap:wrap;font-size:10.5px;color:var(--s500);font-weight:600;margin-bottom:10px}
#psta-page .psta-meta b{color:var(--s700);font-weight:800}
#psta-page .psta-field{margin-bottom:9px}
#psta-page .psta-field:last-child{margin-bottom:0}
#psta-page .psta-field .k{font-size:9px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;color:var(--s400);margin-bottom:3px;display:block}
#psta-page .psta-field .v{font-size:12px;color:var(--s700);white-space:pre-wrap;line-height:1.5}
#psta-page .psta-field.result .v{background:#F8FAFC;border:1px solid var(--s200);border-radius:8px;padding:9px 11px;font-weight:600;color:var(--s800)}
#psta-page .psta-imgbtn{margin-top:8px;background:#F5F3FF;border:1px solid #DDD6FE;color:#6D28D9;border-radius:8px;padding:7px 12px;font-size:10.5px;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;gap:6px;font-family:inherit;text-transform:uppercase;letter-spacing:.3px}
#psta-page .psta-imgbtn:hover{background:#EDE9FE}

/* ---- Modals ---- */
#psta-page .psta-modal{position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;z-index:2000;padding:16px}
#psta-page .psta-modal.show{display:flex}
#psta-page .psta-modal-box{background:#fff;border-radius:14px;width:100%;max-width:720px;max-height:88vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 24px 60px rgba(2,6,23,.4)}
#psta-page .psta-modal-head{background:var(--brand);color:#fff;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;gap:10px}
#psta-page .psta-modal-head h3{margin:0;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;gap:8px}
#psta-page .psta-modal-head .x{background:transparent;border:none;color:#fff;font-size:20px;line-height:1;cursor:pointer}
#psta-page .psta-modal-body{padding:16px;overflow:auto}
#psta-page .psta-modal-foot{padding:12px 16px;border-top:1px solid var(--s200);display:flex;justify-content:flex-end;gap:8px}
#psta-page .psta-inv-group{border:1px solid var(--s200);border-radius:10px;padding:10px;margin-bottom:10px}
#psta-page .psta-inv-group-t{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--s500);margin-bottom:8px;display:flex;align-items:center;gap:7px}
#psta-page .psta-inv-group-t i{color:var(--brand)}
#psta-page .psta-inv-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px}
#psta-page .psta-inv-grid label{display:flex;align-items:center;gap:8px;background:var(--s50);border:1px solid var(--s200);border-radius:8px;padding:7px 9px;font-size:11.5px;font-weight:600;cursor:pointer}
#psta-page .psta-inv-grid label:hover{background:#EFF6FF}
#psta-page .psta-inv-grid input{accent-color:var(--brand);width:14px;height:14px}
#psta-page .psta-input{width:100%;padding:9px 11px;border:1px solid var(--s300);border-radius:8px;background:var(--s50);font-size:12px;font-weight:600;color:var(--s800);outline:none;font-family:inherit}
#psta-page .psta-input:focus{background:#fff;border-color:var(--brand);box-shadow:0 0 0 3px rgba(27,101,157,.15)}
#psta-page .psta-modal-img{max-width:100%;border-radius:10px;border:1px solid var(--s200);display:block}
#psta-page .psta-imgref{font-family:Consolas,monospace;font-size:11.5px;background:var(--s50);border:1px dashed var(--s300);border-radius:8px;padding:10px;word-break:break-all;color:var(--s600)}
@media (max-width:760px){
  #psta-page .psta-inv-grid{grid-template-columns:1fr}
  #psta-page .psta-hrow{flex-direction:column}
  #psta-page .psta-chips{justify-content:flex-start}
}
</style>

<div id="psta-page">
  <div class="psta-wrap">

    <!-- Top search + actions -->
    <div class="psta-topbar">
      <div class="psta-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="pstaSearch" placeholder="Search patient by name or hospital number to open their station..." autocomplete="off">
        <div class="psta-results" id="pstaResults"></div>
      </div>
      <div class="psta-topactions">
        <button type="button" class="psta-btn" id="pstaOrderBtn" onclick="pstaOpenOrder()" disabled><i class="fa-solid fa-plus"></i> Order Tests</button>
        <button type="button" class="psta-btn ghost" id="pstaRefreshBtn" onclick="pstaRefresh()" disabled><i class="fa-solid fa-rotate"></i> Refresh</button>
      </div>
    </div>

    <!-- Patient header -->
    <div class="psta-header" id="pstaHeader" style="display:none">
      <div class="psta-hrow">
        <div class="psta-hleft">
          <div class="psta-avatar"><i class="fa-solid fa-user-injured"></i></div>
          <div>
            <h2 class="psta-name" id="pstaName">Patient</h2>
            <p class="psta-sub">
              <span>ID: <b id="pstaHospNo">--</b></span>
              <span id="pstaGenderAge">--</span>
              <span>NHIS: <b id="pstaNhis">--</b></span>
            </p>
          </div>
        </div>
        <div class="psta-chips" id="pstaChips"></div>
      </div>
      <div class="psta-vitals" id="pstaVitals"></div>
    </div>

    <!-- Tabs -->
    <div class="psta-tabs" id="pstaTabs">
      <button type="button" class="psta-tab active" data-psta-tab="investigations" onclick="pstaSwitchTab('investigations')">
        <i class="fa-solid fa-flask-vial"></i> Diagnostic Investigations
      </button>
    </div>

    <!-- Filters -->
    <div class="psta-toolbar">
      <div class="psta-filters" id="pstaFilters"></div>
      <span class="psta-count" id="pstaCount"></span>
    </div>

    <!-- Feed -->
    <div class="psta-feed" id="pstaFeed">
      <div class="psta-empty">Search for a patient above to load their diagnostic investigations.</div>
    </div>
  </div>
</div>

<!-- Order tests modal -->
<div class="psta-modal" id="pstaOrderModal">
  <div class="psta-modal-box">
    <div class="psta-modal-head">
      <h3><i class="fa-solid fa-vial-circle-check"></i> Order Investigations &amp; Diagnostic Tests</h3>
      <button type="button" class="x" onclick="pstaCloseModal('pstaOrderModal')">&times;</button>
    </div>
    <div class="psta-modal-body" id="pstaOrderBody">
      <div class="psta-empty" style="padding:18px;">Loading investigation catalogue...</div>
    </div>
    <div class="psta-modal-foot">
      <button type="button" class="psta-btn ghost" onclick="pstaCloseModal('pstaOrderModal')">Cancel</button>
      <button type="button" class="psta-btn" id="pstaOrderSaveBtn" onclick="pstaSubmitOrder()"><i class="fa-solid fa-floppy-disk"></i> Save Requests</button>
    </div>
  </div>
</div>

<!-- Image viewer modal -->
<div class="psta-modal" id="pstaImageModal">
  <div class="psta-modal-box">
    <div class="psta-modal-head">
      <h3><i class="fa-solid fa-x-ray"></i> Imaging / Attachment</h3>
      <button type="button" class="x" onclick="pstaCloseModal('pstaImageModal')">&times;</button>
    </div>
    <div class="psta-modal-body" id="pstaImageBody"></div>
  </div>
</div>

<script>
/* ================= PATIENT STATION ================= */
var pstaPatient = null;      // summary.patient for the loaded chart
var pstaBilling = null;      // patient_billing payload
var pstaFeed = [];           // normalised investigation cards
var pstaFilter = 'all';
var pstaCatalogue = null;    // consultation_form context (lazy, for ordering)
var pstaSearchTimer = null;
var pstaOrdering = false;

var PSTA_MODALITIES = [
    { kind: 'lab',        label: 'Laboratory',  groupLabel: 'Laboratory Tests',        icon: 'fa-flask-vial' },
    { kind: 'radiology',  label: 'Radiology',   groupLabel: 'Radiology / X-Ray',       icon: 'fa-x-ray' },
    { kind: 'ultrasound', label: 'Ultrasound',  groupLabel: 'Ultrasound / Sonography', icon: 'fa-wave-square' },
    { kind: 'other',      label: 'Other',       groupLabel: 'Other / Specialist',      icon: 'fa-stethoscope' }
];

function pstaEsc(s) { return (window.escHtml ? escHtml(s) : String(s == null ? '' : s)); }

function pstaInit() {
    var input = document.getElementById('pstaSearch');
    if (input) {
        input.addEventListener('input', pstaOnSearchInput);
        input.addEventListener('blur', function () { setTimeout(pstaHideResults, 150); });
    }
    pstaRenderFilters();
    // A host page (Lab Management / Patient Records) can seed a patient before
    // navigating here: window.patientStationSeed = { patientId: N }.
    var seed = window.patientStationSeed;
    if (seed && seed.patientId) {
        window.patientStationSeed = null;
        pstaLoadPatient(parseInt(seed.patientId, 10));
    }
    // Cross-station handoff from the unified patient folder banner.
    var pfCtx = (typeof window.hmsConsumePatientContext === 'function') ? window.hmsConsumePatientContext() : null;
    if (pfCtx && pfCtx.patientId) {
        pstaLoadPatient(parseInt(pfCtx.patientId, 10));
    }
}

/* Mount the system-wide Patient Master Folder banner into the shell host so
   the diagnostics workspace shares the exact same patient header as every
   other clinical station. */
function pstaMountFolder(pid) {
    var host = document.getElementById('hmsPatientFolder');
    if (window.HMSPatientFolder && host && pid) {
        window.HMSPatientFolder.mount(host, pid, { station: 'patient' });
    }
}

/* ---------- patient search ---------- */
function pstaOnSearchInput() {
    var input = document.getElementById('pstaSearch');
    if (pstaSearchTimer) clearTimeout(pstaSearchTimer);
    pstaSearchTimer = setTimeout(function () { pstaDoSearch(input ? input.value : ''); }, 300);
}

function pstaDoSearch(q) {
    var box = document.getElementById('pstaResults');
    if (!box) return;
    if (!q || q.trim().length < 2) { pstaHideResults(); return; }
    fetch('/hms/backend/api/patients.php?action=search&q=' + encodeURIComponent(q.trim()) + '&limit=8')
        .then(function (r) { return r.json(); })
        .then(function (d) {
            var patients = (d && d.patients) || [];
            if (!patients.length) {
                box.innerHTML = '<div class="psta-empty">No matching patients found</div>';
                box.style.display = 'block';
                return;
            }
            box.innerHTML = patients.map(function (p) {
                var name = [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ');
                return '<div data-psta-pid="' + p.id + '"><span>' + pstaEsc(name) + '</span>'
                    + '<span class="psta-num">' + pstaEsc(p.hospital_number) + '</span></div>';
            }).join('');
            box.querySelectorAll('[data-psta-pid]').forEach(function (el) {
                el.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    var pid = el.getAttribute('data-psta-pid');
                    var nm = el.querySelector('span') ? el.querySelector('span').textContent : 'Patient';
                    var num = el.querySelector('.psta-num') ? el.querySelector('.psta-num').textContent : '';
                    var search = document.getElementById('pstaSearch');
                    if (search) search.value = nm + (num ? ' (' + num + ')' : '');
                    pstaHideResults();
                    pstaLoadPatient(parseInt(pid, 10));
                });
            });
            box.style.display = 'block';
        })
        .catch(function () { pstaHideResults(); });
}

function pstaHideResults() {
    var box = document.getElementById('pstaResults');
    if (box) box.style.display = 'none';
}

/* ---------- load a patient ---------- */
async function pstaLoadPatient(pid) {
    if (!pid) return;
    pstaHideResults();
    var feed = document.getElementById('pstaFeed');
    if (feed) feed.innerHTML = '<div class="psta-empty">Loading diagnostic investigations...</div>';
    try {
        var base = '/hms/backend/api/';
        var res = await Promise.all([
            fetch(base + 'patients.php?action=summary&id=' + pid).then(function (r) { return r.json(); }).catch(function () { return { success: false }; }),
            fetch(base + 'invoices.php?action=patient_billing&patient_id=' + pid).then(function (r) { return r.json(); }).catch(function () { return { success: false }; }),
            fetch(base + 'lab.php?action=requests').then(function (r) { return r.json(); }).catch(function () { return {}; }),
            fetch(base + 'lab.php?action=results&patient_id=' + pid).then(function (r) { return r.json(); }).catch(function () { return {}; }),
            fetch(base + 'radiology.php?action=requests').then(function (r) { return r.json(); }).catch(function () { return {}; }),
            fetch(base + 'radiology.php?action=results&patient_id=' + pid).then(function (r) { return r.json(); }).catch(function () { return {}; }),
            fetch(base + 'consultation_form.php?action=other_requests').then(function (r) { return r.json(); }).catch(function () { return {}; })
        ]);
        var summary = res[0], billing = res[1], labReq = res[2], labRes = res[3], radReq = res[4], radRes = res[5], othReq = res[6];

        if (!summary || !summary.success || !summary.patient) {
            if (feed) feed.innerHTML = '<div class="psta-empty">Could not load this patient.</div>';
            return;
        }
        pstaPatient = summary.patient;
        pstaBilling = (billing && billing.success) ? billing : null;
        pstaCatalogue = null;

        var hospNo = pstaPatient.hospital_number;
        pstaFeed = pstaBuildFeed(pid, hospNo, labReq, labRes, radReq, radRes, othReq);

        pstaRenderHeader();
        pstaRenderFeed();
        pstaEnableActions(true);
        pstaMountFolder(pid);
    } catch (e) {
        console.error('Patient Station load error:', e);
        if (feed) feed.innerHTML = '<div class="psta-empty">Failed to load investigations.</div>';
    }
}

function pstaEnableActions(on) {
    ['pstaOrderBtn', 'pstaRefreshBtn'].forEach(function (id) {
        var b = document.getElementById(id);
        if (b) b.disabled = !on;
    });
}

function pstaRefresh() {
    if (pstaPatient) pstaLoadPatient(pstaPatient.patient_id);
}

/* ---------- feed assembly ---------- */
function pstaBuildFeed(pid, hospNo, labReq, labRes, radReq, radRes, othReq) {
    var items = [];

    var labResultsByReq = {};
    ((labRes && labRes.results) || []).forEach(function (r) { labResultsByReq[r.lab_request_id] = r; });
    ((labReq && labReq.requests) || []).filter(function (r) {
        return String(r.patient_id) === String(pid);
    }).forEach(function (r) {
        var res = labResultsByReq[r.id];
        items.push({
            kind: 'lab', badge: 'LAB', badgeCls: 'lab', icon: 'fa-flask-vial',
            refId: 'LAB-' + r.id, title: r.test_type || 'Laboratory Test',
            category: 'Laboratory', requestedBy: r.doctor_name || '--',
            date: r.requested_at, urgency: r.urgency,
            statusKey: res ? (res.status === 'draft' ? 'draft' : 'ok')
                           : pstaReqStatusKey(r.status),
            statusLabel: res ? (res.status === 'final' || res.status === 'amended' ? 'Verified' : 'Draft')
                             : pstaReqStatusLabel(r.status),
            resultsText: res ? res.results : '', normalRange: res ? res.normal_range : '',
            interpretation: res ? res.interpretation : '',
            completedAt: res ? res.completed_at : '', verifiedBy: res ? res.verified_name : ''
        });
    });

    var radResultsByReq = {};
    ((radRes && radRes.results) || []).forEach(function (r) { radResultsByReq[r.radiology_request_id] = r; });
    ((radReq && radReq.requests) || []).filter(function (r) {
        return String(r.hospital_number || '') === String(hospNo || '');
    }).forEach(function (r) {
        var res = radResultsByReq[r.id];
        var sono = /ultrasound|sonograph|obstetric|pelvic/i.test(String(r.exam_type || '') + ' ' + String(r.body_part || ''));
        items.push({
            kind: sono ? 'ultrasound' : 'radiology',
            badge: sono ? 'SONO' : 'IMAGING', badgeCls: sono ? 'sono' : 'imaging',
            icon: sono ? 'fa-wave-square' : 'fa-x-ray',
            refId: (sono ? 'SONO-' : 'RAD-') + r.id,
            title: (r.exam_type || 'Imaging Study') + (r.body_part ? ' / ' + r.body_part : ''),
            category: sono ? 'Ultrasound / Sonography' : 'Radiology / X-Ray',
            requestedBy: r.doctor_name || '--', date: r.requested_at, urgency: r.urgency,
            statusKey: res ? (res.status === 'draft' ? 'draft' : 'ok') : pstaReqStatusKey(r.status),
            statusLabel: res ? (res.status === 'final' || res.status === 'amended' ? 'Verified' : 'Draft')
                             : pstaReqStatusLabel(r.status),
            resultsText: res ? res.findings : '', normalRange: '',
            interpretation: res ? res.impression : '',
            completedAt: res ? res.completed_at : '', verifiedBy: res ? res.verified_name : '',
            imagePath: res ? res.image_path : ''
        });
    });

    ((othReq && othReq.requests) || []).filter(function (r) {
        return String(r.hospital_number || '') === String(hospNo || '');
    }).forEach(function (r) {
        items.push({
            kind: 'other', badge: 'OTHER', badgeCls: 'other', icon: 'fa-stethoscope',
            refId: 'OTH-' + r.id, title: r.request_type || 'Specialist Test',
            category: 'Other / Specialist', requestedBy: r.doctor_name || '--',
            date: r.requested_at, urgency: null,
            statusKey: pstaReqStatusKey(r.status), statusLabel: pstaReqStatusLabel(r.status),
            resultsText: '', normalRange: '', interpretation: r.notes || '',
            completedAt: '', verifiedBy: ''
        });
    });

    items.sort(function (a, b) { return String(b.date || '').localeCompare(String(a.date || '')); });
    return items;
}

function pstaReqStatusKey(s) {
    s = String(s || '').toLowerCase();
    if (s === 'completed') return 'ok';
    if (s === 'in_progress') return 'prog';
    if (s === 'cancelled') return 'cancel';
    return 'pend';
}
function pstaReqStatusLabel(s) {
    s = String(s || 'pending').replace(/_/g, ' ');
    return s.replace(/\b\w/g, function (c) { return c.toUpperCase(); });
}

/* ---------- header render ---------- */
function pstaRenderHeader() {
    var p = pstaPatient;
    if (!p) return;
    document.getElementById('pstaHeader').style.display = 'block';
    document.getElementById('pstaName').textContent = p.full_name || 'Patient';
    document.getElementById('pstaHospNo').textContent = p.hospital_number || '--';
    document.getElementById('pstaGenderAge').textContent = (p.gender || '--') + ' | ' + (p.age != null ? p.age + ' Yrs' : '-- Yrs');
    document.getElementById('pstaNhis').textContent = p.nhia_number || '--';

    var chips = [];
    // summary returns the *latest* admission whatever its status, so admission
    // only counts as active when that row is still 'Admitted'.
    var admitted = !!(p.admission && p.admission.status === 'Admitted');
    var active = (parseInt(p.is_active, 10) === 1) || admitted;
    chips.push('<span class="psta-chip ' + (active ? 'on' : 'off') + '"><span class="dot"></span>' + (admitted ? 'Active Admission' : (active ? 'Active Patient' : 'Inactive')) + '</span>');
    if (p.bed_ward) chips.push('<span class="psta-chip info">' + pstaEsc(p.bed_ward) + '</span>');
    var due = (pstaBilling && pstaBilling.outstanding != null) ? Number(pstaBilling.outstanding) : 0;
    chips.push('<span class="psta-chip due' + (due > 0 ? '' : ' clear') + '">GH\u20B5 ' + due.toFixed(2) + ' Due</span>');
    chips.push('<span class="psta-chip ' + (p.nhis_active ? 'on' : 'off') + '"><span class="dot"></span>NHIS ' + (p.nhis_active ? 'Active' : 'Inactive') + '</span>');
    document.getElementById('pstaChips').innerHTML = chips.join('');

    var v = p.vitals || {};
    var vitals = [
        ['Blood Pressure', v.blood_pressure || '--'],
        ['Pulse', v.pulse != null ? v.pulse + ' bpm' : '--'],
        ['Temperature', v.temperature != null ? v.temperature + ' \u00B0C' : '--'],
        ['SpO\u2082', v.spo2 != null ? v.spo2 + '%' : '--']
    ];
    document.getElementById('pstaVitals').innerHTML = vitals.map(function (row) {
        return '<div class="psta-vital"><span class="k">' + row[0] + '</span><span class="v">' + pstaEsc(String(row[1])) + '</span></div>';
    }).join('');
}

/* ---------- filters + feed render ---------- */
function pstaRenderFilters() {
    var host = document.getElementById('pstaFilters');
    if (!host) return;
    var defs = [{ kind: 'all', label: 'All' }].concat(PSTA_MODALITIES.map(function (m) { return { kind: m.kind, label: m.label }; }));
    host.innerHTML = defs.map(function (d) {
        return '<button type="button" class="psta-filter' + (pstaFilter === d.kind ? ' active' : '') + '" data-psta-filter="' + d.kind + '" onclick="pstaSetFilter(\'' + d.kind + '\')">' + d.label + '</button>';
    }).join('');
}

function pstaSetFilter(kind) {
    pstaFilter = kind;
    pstaRenderFilters();
    pstaRenderFeed();
}

function pstaSwitchTab(tab) {
    document.querySelectorAll('#pstaTabs .psta-tab').forEach(function (b) {
        b.classList.toggle('active', b.getAttribute('data-psta-tab') === tab);
    });
}

function pstaRenderFeed() {
    var host = document.getElementById('pstaFeed');
    if (!host) return;
    var list = pstaFeed.filter(function (x) { return pstaFilter === 'all' || x.kind === pstaFilter; });
    var count = document.getElementById('pstaCount');
    if (count) count.textContent = list.length + ' record' + (list.length === 1 ? '' : 's');
    if (!pstaPatient) {
        host.innerHTML = '<div class="psta-empty">Search for a patient above to load their diagnostic investigations.</div>';
        return;
    }
    if (!list.length) {
        host.innerHTML = '<div class="psta-empty">No investigations recorded for this patient' + (pstaFilter !== 'all' ? ' in this modality' : '') + ' yet.</div>';
        return;
    }
    host.innerHTML = list.map(pstaCard).join('');
}

function pstaCard(x) {
    var when = x.completedAt || x.date;
    var meta = '<div class="psta-meta">'
        + '<span>Ref: <b>' + pstaEsc(x.refId) + '</b></span>'
        + '<span>Requested by: <b>' + pstaEsc(x.requestedBy) + '</b></span>'
        + (when ? '<span>Date: <b>' + pstaEsc(pstaFmt(when)) + '</b></span>' : '')
        + (x.urgency && x.urgency !== 'routine' ? '<span>' + pstaEsc(String(x.urgency).toUpperCase()) + '</span>' : '')
        + (x.verifiedBy ? '<span>Verified by: <b>' + pstaEsc(x.verifiedBy) + '</b></span>' : '')
        + '</div>';

    var body = meta;
    if (x.resultsText) body += '<div class="psta-field result"><span class="k">Results / Findings</span><span class="v">' + pstaEsc(x.resultsText) + '</span></div>';
    if (x.normalRange) body += '<div class="psta-field"><span class="k">Reference Range</span><span class="v">' + pstaEsc(x.normalRange) + '</span></div>';
    if (x.interpretation) body += '<div class="psta-field"><span class="k">' + (x.kind === 'radiology' || x.kind === 'ultrasound' ? 'Impression' : 'Interpretation') + '</span><span class="v">' + pstaEsc(x.interpretation) + '</span></div>';
    if (!x.resultsText && !x.interpretation) body += '<div class="psta-field"><span class="k">Status</span><span class="v">Awaiting result.</span></div>';
    if (x.imagePath) body += '<button type="button" class="psta-imgbtn" onclick="pstaOpenImage(\'' + pstaJs(x.imagePath) + '\')"><i class="fa-solid fa-image"></i> View Image / Attachment</button>';

    return '<div class="psta-card">'
        + '<div class="psta-card-head">'
            + '<div class="l"><i class="fa-solid ' + x.icon + '"></i>'
                + '<div><h4 class="psta-card-title">' + pstaEsc(x.title) + '</h4>'
                + '<p class="psta-card-cat">' + pstaEsc(x.category) + '</p></div>'
            + '</div>'
            + '<div style="display:flex;gap:8px;align-items:center;">'
                + '<span class="psta-badge ' + x.badgeCls + '">' + pstaEsc(x.badge) + '</span>'
                + '<span class="psta-status ' + x.statusKey + '">' + pstaEsc(x.statusLabel) + '</span>'
            + '</div>'
        + '</div>'
        + '<div class="psta-card-body">' + body + '</div>'
    + '</div>';
}

function pstaFmt(v) {
    if (window.fmtDateTime) return fmtDateTime(v);
    return String(v == null ? '' : v);
}
function pstaJs(s) {
    return String(s == null ? '' : s).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

/* ---------- order modal ---------- */
async function pstaOpenOrder() {
    if (!pstaPatient) return;
    var box = document.getElementById('pstaOrderBody');
    document.getElementById('pstaOrderModal').classList.add('show');
    if (box) box.innerHTML = '<div class="psta-empty" style="padding:18px;">Loading investigation catalogue...</div>';
    try {
        if (!pstaCatalogue) {
            var r = await fetch('/hms/backend/api/consultation_form.php?action=context&patient_id=' + pstaPatient.patient_id);
            var d = await r.json();
            if (!d.success) throw new Error(d.error || 'Failed to load');
            pstaCatalogue = d;
        }
        pstaRenderOrderCatalogue();
    } catch (e) {
        console.error('Catalogue error:', e);
        if (box) box.innerHTML = '<div class="psta-empty" style="padding:18px;color:#C0392B;">Could not load the investigation catalogue.</div>';
    }
}

function pstaRenderOrderCatalogue() {
    var box = document.getElementById('pstaOrderBody');
    if (!box) return;
    var inv = (pstaCatalogue && pstaCatalogue.investigations) ? pstaCatalogue.investigations : [];
    if (!inv.length) { box.innerHTML = '<div class="psta-empty" style="padding:18px;">No investigations are configured.</div>'; return; }
    var groups = {};
    PSTA_MODALITIES.forEach(function (m) { groups[m.kind] = []; });
    inv.forEach(function (i) { (groups[i.kind] = groups[i.kind] || []).push(i); });

    box.innerHTML = PSTA_MODALITIES.map(function (m) {
        var items = groups[m.kind] || [];
        var isOther = m.kind === 'other';
        if (!items.length && !isOther) return '';
        var grid = items.map(function (i) {
            return '<label><input type="checkbox" class="psta-inv-check" data-psta-grp="' + m.kind + '" value="' + pstaEsc(i.key) + '"><span>' + pstaEsc(i.label) + '</span></label>';
        }).join('');
        return '<div class="psta-inv-group">'
            + '<div class="psta-inv-group-t"><i class="fa-solid ' + m.icon + '"></i> ' + pstaEsc(m.groupLabel) + '</div>'
            + (grid ? '<div class="psta-inv-grid">' + grid + '</div>' : '<div class="psta-field"><span class="v">No presets - type below.</span></div>')
            + (isOther ? '<div style="margin-top:9px;"><input type="text" id="pstaOtherText" class="psta-input" placeholder="Or type any other test / procedure not listed above..."></div>' : '')
            + '</div>';
    }).join('');
}

async function pstaSubmitOrder() {
    if (pstaOrdering || !pstaPatient || !pstaCatalogue) return;
    var keys = [];
    document.querySelectorAll('#pstaOrderModal .psta-inv-check').forEach(function (c) { if (c.checked) keys.push(c.value); });
    var otherText = ((document.getElementById('pstaOtherText') || {}).value || '').trim();
    if (!keys.length && !otherText) { showAlert('Tick a test - or type one under Other - before saving.', 'error'); return; }

    var payload = {
        patient_id: pstaPatient.patient_id,
        diagnosis_status: 'New',
        presenting_complaints: '',
        doctor_notes: 'Investigations ordered from the Patient Station.',
        provisional_diagnosis: '',
        medication_notes: '',
        allergies: (pstaCatalogue.patient && pstaCatalogue.patient.allergies) ? pstaCatalogue.patient.allergies : '',
        investigations: keys,
        other_requests: otherText ? [otherText] : [],
        prescriptions: [],
        save_as_draft: false
    };
    pstaOrdering = true;
    var btn = document.getElementById('pstaOrderSaveBtn');
    if (btn) { btn.disabled = true; }
    try {
        var r = await fetch('/hms/backend/api/consultation_form.php?action=save', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
        });
        var d = await r.json();
        if (!d.success) throw new Error(d.error || 'Could not save.');
        pstaCloseModal('pstaOrderModal');
        showAlert('Investigation order sent to the laboratory / imaging desk.', 'success');
        pstaLoadPatient(pstaPatient.patient_id);
    } catch (e) {
        showAlert(e.message, 'error');
    } finally {
        pstaOrdering = false;
        if (btn) btn.disabled = false;
    }
}

/* ---------- image modal ---------- */
function pstaOpenImage(path) {
    var body = document.getElementById('pstaImageBody');
    if (body) {
        var looksLikeUrl = /^(https?:)?\/\//i.test(path) || /^\/|\.(png|jpe?g|gif|webp|bmp|pdf)$/i.test(path);
        body.innerHTML = (looksLikeUrl ? '<img class="psta-modal-img" src="' + pstaEsc(path) + '" alt="Imaging attachment" onerror="this.style.display=\'none\'">' : '')
            + '<div style="margin-top:' + (looksLikeUrl ? '10px' : '0') + ';"><span class="k" style="font-size:9px;font-weight:800;text-transform:uppercase;color:#94A3B8;">Image reference</span>'
            + '<div class="psta-imgref">' + pstaEsc(path) + '</div></div>';
    }
    document.getElementById('pstaImageModal').classList.add('show');
}

function pstaCloseModal(id) {
    var m = document.getElementById(id);
    if (m) m.classList.remove('show');
}

/* The SPA shell derives its init hook from the page key: for the fragment
   'patient-station' it calls window.initPatientStation(). Publish that alias. */
window.initPatientStation = function () { pstaInit(); };

/* Esc closes whichever modal is open. Bound once — the fragment is re-evaluated
   on every visit, so without this guard listeners would accumulate. */
if (!window.__pstaEscBound) {
    window.__pstaEscBound = true;
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { pstaCloseModal('pstaOrderModal'); pstaCloseModal('pstaImageModal'); }
    });
}
</script>
