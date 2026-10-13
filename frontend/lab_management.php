<?php
// EHMS — Laboratory Management (standalone lab-technician workspace).
//
// This is the landing screen for users whose role is 'lab'. The SPA shell
// (frontend/dashboard.php) redirects lab users here so they only ever see the
// laboratory workspace; every other role keeps the normal dashboard, and the
// same lab worklist also lives inside the dashboard as
// frontend/pages/lab-management.php.
//
// It reuses the existing Laboratory API (backend/api/lab.php), Visits API
// (visits.php) and Messages API (messages.php), so there is no parallel data
// path — this page is just a different face on the same records.
require_once __DIR__ . '/../backend/config/config.php';
requireLogin();

// Only the laboratory role (super_admin bypasses via hasRole) may open this
// screen; anyone else is sent back to the dashboard.
if (!hasRole(['lab'])) {
    header('Location: dashboard.php');
    exit;
}

$labStaff     = getCurrentUserName() ?: 'LAB STAFF';
$labRole      = getCurrentUserRole() ?: 'lab';
$labLoginTime = isset($_SESSION['login_time']) ? (int)$_SESSION['login_time'] : time();
$labSince     = date('l, d/m/Y H:i:s', $labLoginTime);
$labElapsed   = gmdate('H:i:s', max(0, time() - $labLoginTime));
$labClinic    = 'EDDIE HOSPITAL';

// Support contact details: generic placeholders, overridable locally via the
// gitignored backend/config/local-contact.php. Personal details are never
// committed to this public repository.
$contactEmail = 'support@example.com';
$contactPhone = '+233 00 000 0000';
$__localContactConfig = __DIR__ . '/../backend/config/local-contact.php';
if (is_file($__localContactConfig)) { include $__localContactConfig; }
unset($__localContactConfig);

// ---- Laboratory investigation catalogue -------------------------------------
// Mirrors the catalogue in frontend/pages/lab-management.php so the two entry
// points present the same tests and prices. Edit prices in both places (or
// move this to backend/includes if it needs to grow).
$labCategories = [
    'hematology'   => 'Hematology',
    'parasitology' => 'Parasitology & Serology',
    'biochemistry' => 'Biochemistry',
    'microbiology' => 'Microbiology',
];
$labCatalog = [
    'hematology' => [
        ['fbc', 'Full Blood Count (FBC)', 50.00],
        ['hb', 'Hemoglobin (Hb) Level', 20.00],
        ['blood_group', 'Blood Grouping & Rh Factor', 30.00],
        ['sickling', 'Sickling Test / Hb Electrophoresis', 25.00],
        ['esr', 'Erythrocyte Sedimentation Rate (ESR)', 30.00],
    ],
    'parasitology' => [
        ['malaria_rdt', 'Malaria RDT', 15.00],
        ['malaria_mps', 'Malaria Microscopy (MPS)', 25.00],
        ['widal', 'Widal Test (Typhoid)', 30.00],
        ['hbsag', 'Hepatitis B Screening (HBsAg)', 35.00],
        ['hiv', 'HIV Screening Test', 20.00],
        ['vdrl', 'Syphilis (VDRL/RPR)', 20.00],
    ],
    'biochemistry' => [
        ['fbs', 'Fasting Blood Sugar (FBS)', 15.00],
        ['rbs', 'Random Blood Sugar (RBS)', 15.00],
        ['lft', 'Liver Function Tests (LFTs)', 120.00],
        ['kft', 'Kidney Function Tests (KFTs)', 130.00],
        ['lipid', 'Lipid Profile', 110.00],
    ],
    'microbiology' => [
        ['urine_re', 'Urine Routine Examination', 20.00],
        ['urine_cs', 'Urine Culture & Sensitivity', 80.00],
        ['stool_re', 'Stool Routine Examination', 20.00],
        ['hvs_cs', 'High Vaginal Swab (HVS) C&S', 90.00],
    ],
];
$labParamData = [];
foreach ($labCatalog as $cat => $items) {
    $labParamData[$cat] = [];
    foreach ($items as $it) {
        $labParamData[$cat][] = ['name' => $it[1], 'code' => $it[0], 'price' => number_format($it[2], 2, '.', '')];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lab Management — EHMS</title>
<link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
<style>
:root{
  --brand:#1b659d; --brand-dk:#155380; --rose:#E11D48;
  --s50:#F8FAFC; --s100:#F1F5F9; --s200:#E2E8F0; --s300:#CBD5E1;
  --s400:#94A3B8; --s500:#64748B; --s600:#475569; --s700:#334155; --s800:#1E293B;
}
*{box-sizing:border-box}
body{margin:0;background:#E7EDF3;color:var(--s700);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;font-size:12px;line-height:1.45}

/* ---- TOP HEADER ---- */
.lab-top{background:var(--brand);color:#fff;padding:9px 16px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;box-shadow:0 1px 3px rgba(15,23,42,.2)}
.lab-top .brand{display:flex;align-items:center;gap:9px}
.lab-top .brand i{font-size:18px}
.lab-top h1{margin:0;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:1px}
.lab-top .nav{display:flex;align-items:center;gap:8px;font-size:11px;flex-wrap:wrap}
.lab-top .nav .sys{color:#BFDBFE;font-weight:700;text-transform:uppercase}
.topbtn{background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.22);color:#fff;border-radius:7px;padding:5px 11px;font-size:11px;font-weight:700;cursor:pointer;text-decoration:none;text-transform:uppercase;letter-spacing:.3px;font-family:inherit}
.topbtn:hover{background:rgba(255,255,255,.2)}
.topbtn.danger{background:var(--rose);border-color:transparent}
.topbtn.danger:hover{background:#BE123C}

/* ---- THREE-COLUMN SHELL ---- */
.lab-shell{display:flex;gap:12px;padding:12px;max-width:1920px;margin:0 auto;align-items:flex-start}
.lab-rail{width:224px;flex:0 0 224px;display:flex;flex-direction:column;gap:12px}
.lab-rail.right{width:244px;flex:0 0 244px}
.lab-main{flex:1 1 auto;min-width:0;display:flex;flex-direction:column;gap:12px}
@media (max-width:1180px){.lab-shell{flex-wrap:wrap}.lab-rail,.lab-rail.right{width:100%;flex:1 1 100%}}

/* ---- RAIL CARDS ---- */
.railcard{background:#fff;border:1px solid var(--s200);border-radius:12px;overflow:hidden;box-shadow:0 1px 2px rgba(15,23,42,.05)}
.railcard.brand{background:var(--brand);border-color:var(--brand);color:#fff}
.rail-head{font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;padding:10px 12px;display:flex;align-items:center;gap:7px}
.railcard.brand .rail-head{border-bottom:1px solid rgba(255,255,255,.25)}
.railcard .rail-body{padding:10px 12px}
.qlink{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:7px 10px;border-radius:7px;color:#E2E8F0;text-decoration:none;font-weight:600;font-size:11px;cursor:pointer;border:none;background:none;font-family:inherit;width:100%;text-align:left}
.qlink:hover{background:rgba(255,255,255,.12);color:#fff}
.qlink.active{background:rgba(255,255,255,.2);color:#fff;font-weight:800;border-left:3px solid #FBBF24}
.qlink .qb{background:var(--rose);color:#fff;font-size:9px;font-weight:800;border-radius:999px;padding:1px 6px}
.btn-send{width:100%;background:#F59E0B;color:#fff;border:none;border-radius:11px;padding:9px 12px;font-weight:800;font-size:11px;text-transform:uppercase;letter-spacing:.5px;cursor:pointer;font-family:inherit}
.btn-send:hover{background:#D97706}
.alerts-num{font-size:22px;font-weight:800;line-height:1.1}
.alerts-label{font-size:9.5px;font-weight:700;text-transform:uppercase;color:#BFDBFE;margin-top:2px}
.alerts-btn{margin-top:8px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.22);color:#fff;border-radius:7px;padding:4px 9px;font-size:10px;font-weight:700;cursor:pointer;font-family:inherit}
.alerts-btn:hover{background:rgba(255,255,255,.2)}
.status-row{display:flex;justify-content:space-between;gap:8px;padding:3px 0;font-size:10px}
.status-row .sl{color:#BFDBFE}
.status-row .sv{font-weight:700;color:#fff;text-align:right}
.status-tag{background:#FBBF24;color:#1E293B;border-radius:4px;padding:1px 6px;font-size:9px;font-weight:800}
.status-logout{display:block;text-align:center;background:var(--rose);color:#fff;font-weight:700;text-decoration:none;border-radius:7px;padding:6px;margin-top:9px;border:none;width:100%;cursor:pointer;font-family:inherit;font-size:11px;text-transform:uppercase;letter-spacing:.4px}
.status-logout:hover{background:#BE123C}

/* ---- BANNER ---- */
.banner{background:var(--brand);color:#fff;border-radius:12px;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;box-shadow:0 1px 3px rgba(15,23,42,.15)}
.banner .bl{display:flex;align-items:center;gap:11px}
.banner .bl i{font-size:18px;color:#BFDBFE}
.banner h2{margin:0;font-size:12.5px;font-weight:800;text-transform:uppercase;letter-spacing:.5px}
.banner p{margin:2px 0 0;font-size:10.5px;color:#BFDBFE}
.banner .br{display:flex;align-items:center;gap:9px;flex-wrap:wrap}
.live{background:rgba(16,185,129,.18);color:#A7F3D0;border:1px solid rgba(52,211,153,.35);border-radius:999px;font-size:9.5px;font-weight:800;padding:5px 11px;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase}
.live .dot{width:7px;height:7px;border-radius:50%;background:#34D399;animation:pulse 1.6s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.35}}

/* ---- STAT TILES ---- */
.tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
@media (max-width:1100px){.tiles{grid-template-columns:repeat(2,1fr)}}
@media (max-width:640px){.tiles{grid-template-columns:1fr}}
.tile{background:#fff;border:1px solid var(--s200);border-radius:12px;padding:12px 14px;display:flex;justify-content:space-between;align-items:center;gap:12px;box-shadow:0 1px 2px rgba(15,23,42,.05);cursor:pointer;text-align:left;font-family:inherit;transition:border-color .15s,box-shadow .15s}
.tile:hover{border-color:var(--brand);box-shadow:0 5px 14px rgba(15,23,42,.09)}
.tile .tt{font-size:9.5px;font-weight:800;color:var(--s400);text-transform:uppercase;letter-spacing:.4px}
.tile .tn{font-size:16px;font-weight:800;color:var(--brand)}
.tile .tn .u{font-size:10px;font-weight:600;color:var(--s500)}
.tile .ts{font-size:10px;color:var(--s500)}
.tile .ti{width:44px;height:44px;border-radius:11px;display:inline-flex;align-items:center;justify-content:center;font-size:17px;flex:0 0 auto}
.ti.blue{background:#EFF6FF;color:var(--brand);border:1px solid #BFDBFE}
.ti.green{background:#ECFDF5;color:#059669;border:1px solid #A7F3D0}
.ti.amber{background:#FFFBEB;color:#D97706;border:1px solid #FDE68A}
.ti.slate{background:var(--s100);color:var(--s600);border:1px solid var(--s200)}

/* ---- PANELS ---- */
.panel{background:#fff;border:1px solid var(--s200);border-radius:12px;overflow:hidden;box-shadow:0 1px 2px rgba(15,23,42,.05)}
.panel-head{background:var(--brand);color:#fff;font-weight:800;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:11px 15px;display:flex;align-items:center;gap:8px;justify-content:space-between;flex-wrap:wrap}

/* ---- TECHNICIAN FORM ---- */
.form-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:12px 16px;padding:15px 16px}
.field{display:flex;flex-direction:column;gap:5px}
.field label{font-size:10px;font-weight:800;color:var(--s600);text-transform:uppercase;letter-spacing:.4px}
.field label .req{color:#E74C3C}
.field select,.field input,.field textarea{width:100%;padding:9px 11px;border:1px solid var(--s300);border-radius:8px;background:var(--s50);color:var(--s800);font-size:12px;font-weight:600;font-family:inherit}
.field textarea{font-weight:500;resize:vertical}
.field select:focus,.field input:focus,.field textarea:focus{outline:none;border-color:var(--brand);background:#fff;box-shadow:0 0 0 3px rgba(27,101,157,.15)}
.field input[readonly]{background:var(--s100);color:var(--brand);font-weight:800}
.span-2{grid-column:span 2}.span-3{grid-column:span 3}.span-4{grid-column:span 4}.span-6{grid-column:span 6}
.form-actions{grid-column:span 6;display:flex;justify-content:space-between;gap:10px;align-items:center;padding-top:13px;border-top:1px solid var(--s200);flex-wrap:wrap}
@media (max-width:820px){.form-grid{grid-template-columns:1fr}.span-2,.span-3,.span-4,.span-6,.form-actions{grid-column:span 1}}

/* ---- BUTTONS ---- */
.btn{border:none;border-radius:8px;padding:9px 16px;font-weight:800;font-size:11px;text-transform:uppercase;letter-spacing:.4px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px}
.btn-primary{background:var(--brand);color:#fff}.btn-primary:hover{background:var(--brand-dk)}
.btn-rose{background:#FFF1F2;color:#BE123C;border:1px solid #FECDD3}.btn-rose:hover{background:#FFE4E6}
.btn-success{background:#059669;color:#fff}.btn-success:hover{background:#047857}
.btn-ghost{background:#fff;color:var(--brand);border:1px solid var(--s300)}.btn-ghost:hover{background:var(--s100)}
.btn-sm{padding:5px 11px;font-size:10px}

/* ---- CANCEL / RESCHEDULE ---- */
.cr-divider{height:2px;background:#FECDD3;border-radius:2px;position:relative;cursor:pointer;margin:0 16px 12px;user-select:none;transition:background .2s}
.cr-divider:hover{background:#FDA4AF}
.cr-divider span{position:absolute;top:-10px;left:6px;background:var(--rose);color:#fff;font-size:10px;font-weight:800;padding:4px 12px;border-radius:999px;text-transform:uppercase;letter-spacing:.5px}
.cr-divider .caret{display:inline-block;margin-left:8px;font-size:9px;font-style:normal;transition:transform .25s}
.cr-divider.open .caret{transform:rotate(180deg)}
#crBody .form-grid{background:#FFF1F2;border-top:1px solid #FECDD3}
.cr-hint{grid-column:span 6;font-size:10.5px;color:#92400E;background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px;padding:9px 11px;margin:0}
@media (max-width:820px){.cr-hint{grid-column:span 1}}

/* ---- TABS + TABLE ---- */
.tabs{display:flex;flex-wrap:wrap;background:var(--s800)}
.tab{background:transparent;border:none;border-right:1px solid rgba(255,255,255,.12);color:#CBD5E1;font-family:inherit;font-weight:800;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;padding:11px 15px;cursor:pointer;transition:background .15s,color .15s}
.tab:last-child{border-right:none}
.tab:hover{background:rgba(255,255,255,.08);color:#fff}
.tab.active{background:var(--brand);color:#fff}
.toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:9px 13px;background:var(--s50);border-bottom:1px solid var(--s200)}
.toolbar .search{position:relative;flex:1 1 240px;min-width:200px}
.toolbar .search i{position:absolute;left:9px;top:8px;color:var(--s400);font-size:12px}
.toolbar .search input{width:100%;padding:7px 9px 7px 28px;border:1px solid var(--s300);border-radius:8px;font-size:12px;font-family:inherit}
.toolbar .search input:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 3px rgba(27,101,157,.15)}
.counts{font-size:10px;color:var(--s500);font-weight:600;display:flex;gap:10px;flex-wrap:wrap}
.counts b{color:var(--s800)}
.table-wrap{max-height:520px;overflow:auto;background:#fff}
table.lab-table{width:100%;border-collapse:collapse;font-size:11px}
table.lab-table th{position:sticky;top:0;z-index:1;background:var(--s100);color:var(--s600);text-align:left;font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;padding:9px 10px;border-bottom:1px solid var(--s200);white-space:nowrap}
table.lab-table td{padding:9px 10px;border-bottom:1px solid var(--s100);vertical-align:middle}
table.lab-table tbody tr:hover{background:var(--s50)}
.first-col{padding-left:14px}
.act-col{text-align:center;white-space:nowrap}
.empty{text-align:center;color:var(--s400);padding:22px}
.pane{display:none}.pane.active{display:block}
.badge{display:inline-block;padding:2px 9px;border-radius:999px;font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.3px}
.b-ready{color:#047857;background:#ECFDF5}
.b-prog{color:var(--brand);background:#EFF6FF}
.b-pend{color:#B91C1C;background:#FEF2F2}
.b-cancel{color:#B45309;background:#FFFBEB}
.cr-note{font-size:9.5px;max-width:190px;white-space:normal;margin-top:3px}
.cr-note.warn{color:#B9770E}.cr-note.bad{color:#B23B3B}
.muted{color:#8a94a6}
.cell-strong{font-weight:700}
.cell-result{max-width:240px;white-space:pre-wrap}

/* ---- MODALS ---- */
.modal{display:none;position:fixed;inset:0;z-index:2000;background:rgba(15,23,42,.55);align-items:flex-start;justify-content:center;padding:40px 16px;overflow:auto}
.modal.show{display:flex}
.modal-box{background:#fff;border-radius:14px;width:min(620px,100%);overflow:hidden;box-shadow:0 20px 44px rgba(15,23,42,.3)}
.modal-head{background:var(--brand);color:#fff;padding:13px 16px;display:flex;justify-content:space-between;align-items:center;font-weight:800;font-size:12px;text-transform:uppercase;letter-spacing:.4px}
.modal-head .x{background:none;border:none;color:#fff;font-size:22px;line-height:1;cursor:pointer}
.modal-head .x:hover{color:#FDE68A}
.modal-body{padding:16px 18px;background:var(--s50)}
.modal-body .field{margin-bottom:12px}
.modal-foot{display:flex;justify-content:flex-end;gap:9px;padding:13px 18px;border-top:1px solid var(--s200);background:#fff;flex-wrap:wrap}
@media (max-width:560px){.modal{padding:16px 10px}}

/* ---- RIGHT RAIL ---- */
.rail-label{display:block;font-size:9.5px;font-weight:800;color:var(--s500);text-transform:uppercase;letter-spacing:.4px;margin:0 0 4px}
.rail-input{width:100%;padding:7px 9px;border:1px solid var(--s300);border-radius:7px;background:var(--s50);font-size:11px;font-family:inherit}
.rail-input:focus{outline:none;border-color:var(--brand);background:#fff;box-shadow:0 0 0 3px rgba(27,101,157,.15)}
.rail-field{margin-bottom:9px}
.msg{display:block;width:100%;text-align:left;background:#fff;border:1px solid var(--s200);border-radius:9px;padding:9px 10px;margin-bottom:8px;cursor:pointer;font-family:inherit}
.msg:hover{border-color:var(--brand)}
.msg.unread{border-left:3px solid var(--brand);background:#F8FBFF}
.msg .mh{display:flex;justify-content:space-between;gap:8px;font-size:9.5px;color:var(--s500);font-weight:700}
.msg .ms{font-size:11px;font-weight:800;color:var(--s800);margin-top:2px}
.msg .mb{font-size:10px;color:var(--s500);margin-top:2px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}

/* ---- SUPPORT FOOTER + TOASTS ---- */
.support{background:var(--brand);color:#fff;border-radius:12px;padding:11px 14px;text-align:center;font-size:11px;font-weight:600}
.support a{color:#FCD34D;font-weight:800;text-decoration:underline}
#labToast{position:fixed;right:18px;bottom:18px;z-index:3000;display:flex;flex-direction:column;gap:8px;max-width:340px}
.toast{background:var(--s800);color:#fff;border-radius:9px;padding:10px 14px;font-size:11.5px;font-weight:600;box-shadow:0 8px 24px rgba(15,23,42,.35);border-left:4px solid var(--brand)}
.toast.success{border-left-color:#10B981}.toast.error{border-left-color:#EF4444}.toast.info{border-left-color:#3B82F6}
</style>
</head>
<body>

<!-- ================= TOP HEADER ================= -->
<header class="lab-top">
  <div class="brand">
    <i class="fa-solid fa-flask-vial"></i>
    <h1>Lab Management</h1>
  </div>
  <div class="nav">
    <span class="sys">EHMS &bull; Eddie Healthcare Solutions</span>
    <a class="topbtn" href="dashboard.php">Home</a>
    <button class="topbtn" type="button" onclick="history.back()">&larr; Back</button>
    <button class="topbtn danger" type="button" onclick="labLogout()">Logout</button>
  </div>
</header>

<div class="lab-shell">

  <!-- ================= LEFT RAIL ================= -->
  <aside class="lab-rail">

    <div class="railcard brand">
      <div class="rail-head"><i class="fa-solid fa-list-check"></i> Quick Links</div>
      <div class="rail-body" style="padding-top:6px">
        <button type="button" class="qlink active" onclick="labGoSection('labFormPanel')">Technician Form</button>
        <button type="button" class="qlink" onclick="labCardTab('doctors')">Doctor Requests</button>
        <button type="button" class="qlink" onclick="labCardTab('pending')">
          <span>Pending Labs</span><span class="qb" id="qlPendingBadge" style="display:none">0</span>
        </button>
        <button type="button" class="qlink" onclick="labCardTab('ready')">
          <span>Ready Labs</span><span class="qb" id="qlReadyBadge" style="display:none">0</span>
        </button>
        <button type="button" class="qlink" onclick="labCardTab('notready')">Not Ready Labs</button>
        <button type="button" class="qlink" onclick="labCardTab('dispatched')">Dispatched Labs</button>
        <button type="button" class="qlink" onclick="labGoSection('crSection')">Cancel / Reschedule</button>
        <button type="button" class="qlink" onclick="labGoSection('sysMessages')">Messages &amp; Alerts</button>
      </div>
    </div>

    <button type="button" class="btn-send" onclick="openSendModal()">Send Message</button>

    <div class="railcard brand">
      <div class="rail-body">
        <div class="alerts-num" id="daysAlertNum">0</div>
        <div class="alerts-label" id="daysAlertLabel">No alerts today</div>
        <button type="button" class="alerts-btn" onclick="labGoSection('sysMessages')">Open Messages &rarr;</button>
      </div>
    </div>

    <div class="railcard brand">
      <div class="rail-head"><i class="fa-solid fa-shield-halved"></i> Login Status</div>
      <div class="rail-body">
        <div class="status-row"><span class="sl">Logged As:</span><span class="sv"><?php echo htmlspecialchars(strtoupper($labStaff), ENT_QUOTES); ?></span></div>
        <div class="status-row"><span class="sl">User Type:</span><span class="sv"><span class="status-tag"><?php echo htmlspecialchars(strtoupper($labRole), ENT_QUOTES); ?></span></span></div>
        <div class="status-row"><span class="sl">Logout Timer:</span><span class="sv" id="labSessionTimer" data-login-time="<?php echo (int)$labLoginTime; ?>"><?php echo htmlspecialchars($labElapsed, ENT_QUOTES); ?></span></div>
        <div class="status-row"><span class="sl">Since:</span><span class="sv"><?php echo htmlspecialchars($labSince, ENT_QUOTES); ?></span></div>
        <div class="status-row"><span class="sl">Clinic Name:</span><span class="sv"><?php echo htmlspecialchars($labClinic, ENT_QUOTES); ?></span></div>
        <button type="button" class="status-logout" onclick="labLogout()">Logout</button>
      </div>
    </div>

  </aside>

  <!-- ================= CENTER WORKSPACE ================= -->
  <main class="lab-main">

    <div class="banner">
      <div class="bl">
        <i class="fa-solid fa-vials"></i>
        <div>
          <h2>Laboratory Management &amp; Investigation Module</h2>
          <p>Lab Technician Workspace &bull; Process Specimen, Verify Results &amp; Dispatch Reports</p>
        </div>
      </div>
      <div class="br">
        <span class="live"><span class="dot"></span> Live Auto-Refresh</span>
        <button type="button" class="topbtn" onclick="labRefresh()"><i class="fa-solid fa-arrows-rotate"></i> Refresh</button>
      </div>
    </div>

    <div class="tiles">
      <button type="button" class="tile" onclick="labGoSection('labFormPanel')">
        <span>
          <span class="tt">Sample Management</span>
          <span class="tn"><b id="tileSample">0</b> <span class="u">Logged</span></span>
          <span class="ts">Register &amp; process patient specimen</span>
        </span>
        <span class="ti blue"><i class="fa-solid fa-vial"></i></span>
      </button>

      <button type="button" class="tile" onclick="labCardTab('ready')">
        <span>
          <span class="tt">Lab Verification &amp; Ready Labs</span>
          <span class="tn"><b id="tileReady">0</b> <span class="u">Ready</span></span>
          <span class="ts">Verify completed tests &amp; authorize results</span>
        </span>
        <span class="ti green"><i class="fa-solid fa-circle-check"></i></span>
      </button>

      <button type="button" class="tile" onclick="labCardTab('pending')">
        <span>
          <span class="tt">Pending Tests</span>
          <span class="tn"><b id="tilePending">0</b> <span class="u">Queued</span></span>
          <span class="ts">View pending lab test requests</span>
        </span>
        <span class="ti amber"><i class="fa-solid fa-clock-rotate-left"></i></span>
      </button>

      <button type="button" class="tile" onclick="labCardTab('dispatched')">
        <span>
          <span class="tt">Report Dispatched</span>
          <span class="tn"><b id="tileDispatched">0</b> <span class="u">Reports</span></span>
          <span class="ts">Access dispatched lab records</span>
        </span>
        <span class="ti slate"><i class="fa-solid fa-envelope-circle-check"></i></span>
      </button>
    </div>

    <!-- TECHNICIAN FORM -->
    <div class="panel" id="labFormPanel">
      <div class="panel-head"><span><i class="fa-solid fa-pen-to-square"></i> Lab Technician Form - Process Investigation</span></div>
      <form class="form-grid" id="labEntryForm">
        <div class="field span-3">
          <label for="entryPatient">Patient ID / Name <span class="req">*</span></label>
          <select id="entryPatient" required><option value="">-- Select Patient --</option></select>
        </div>
        <div class="field span-3">
          <label for="labTypeSelect">Lab Type <span class="req">*</span></label>
          <select id="labTypeSelect" onchange="updateLabParameters()" required>
            <option value="">-- Select Lab Category --</option>
            <?php foreach ($labCategories as $key => $label): ?>
              <option value="<?php echo htmlspecialchars($key, ENT_QUOTES); ?>"><?php echo htmlspecialchars($label, ENT_QUOTES); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field span-4">
          <label for="labParameterSelect">Lab Parameter / Test to be Done <span class="req">*</span></label>
          <select id="labParameterSelect" onchange="updateLabPrice()" required>
            <option value="">-- Select Lab Category First --</option>
          </select>
        </div>
        <div class="field span-2">
          <label for="labPriceInput">Price (GHS)</label>
          <input type="text" id="labPriceInput" placeholder="GHS 0.00" readonly>
        </div>
        <div class="form-actions">
          <button type="button" class="btn btn-rose" onclick="labCrToggle()">Cancel or Reschedule Investigation</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save &amp; Submit Test</button>
        </div>
      </form>

      <!-- CANCEL / RESCHEDULE -->
      <div id="crSection">
        <div class="cr-divider" id="crToggleDivider" role="button" tabindex="0" aria-expanded="false" aria-controls="crBody" onclick="labCrToggle()">
          <span>Cancel or Reschedule Investigation <b id="crHint" style="font-weight:600;text-transform:none">- toggle to view</b><i class="caret">&#9660;</i></span>
        </div>
        <div id="crBody" style="display:none">
          <form class="form-grid" id="labCrForm">
            <div class="field span-3">
              <label for="crRequisition">Investigation / Requisition <span class="req">*</span></label>
              <select id="crRequisition" required><option value="">-- Select Requisition --</option></select>
            </div>
            <div class="field span-3">
              <label for="crAction">Action <span class="req">*</span></label>
              <select id="crAction" onchange="labCrToggleAction()" required>
                <option value="">-- Select Action --</option>
                <option value="cancel">Cancel Test</option>
                <option value="reschedule">Reschedule Test</option>
              </select>
            </div>
            <div class="field span-3" id="crWhenField" style="display:none">
              <label for="crWhen">New Date &amp; Time <span class="req">*</span></label>
              <input type="datetime-local" id="crWhen">
            </div>
            <div class="field span-3">
              <label for="crReason">Reason <span class="req">*</span></label>
              <select id="crReason" required>
                <option value="">-- Select Reason --</option>
                <option>Patient not ready / not on bed</option>
                <option>Sample not collected</option>
                <option>Insufficient sample</option>
                <option>Sample haemolysed / clotted</option>
                <option>Machine / analyser fault</option>
                <option>Reagent unavailable</option>
                <option>Patient deferred / requested later</option>
                <option>Duplicate request</option>
                <option>Wrong test ordered</option>
                <option>Other (state in details)</option>
              </select>
            </div>
            <div class="field span-6">
              <label for="crNotes">Details / Notes</label>
              <input type="text" id="crNotes" placeholder="Optional details, e.g. collected in the wrong tube, patient fasting not observed...">
            </div>
            <p class="cr-hint">Cancelling stops the test and records the reason against the request. Rescheduling keeps the request open and returns it to the pending worklist on the new date.</p>
            <div class="form-actions">
              <span></span>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-xmark"></i> Submit Cancel / Reschedule</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- WORKLIST -->
    <div class="panel" id="labWorklist">
      <div class="tabs" id="labTabs">
        <button type="button" class="tab active" data-view="doctors" onclick="labSwitchTab('doctors')">Doctor Requests</button>
        <button type="button" class="tab" data-view="pending" onclick="labSwitchTab('pending')">Pending Labs</button>
        <button type="button" class="tab" data-view="ready" onclick="labSwitchTab('ready')">Ready Labs</button>
        <button type="button" class="tab" data-view="notready" onclick="labSwitchTab('notready')">Not Ready Labs</button>
        <button type="button" class="tab" data-view="dispatched" onclick="labSwitchTab('dispatched')">Dispatched Labs</button>
      </div>
      <div class="toolbar">
        <div class="search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="labSearch" placeholder="By Patient / Test / Req. ID..." oninput="renderLabPanes()">
        </div>
        <div class="counts">
          <span>All: <b id="cAll">0</b></span> |
          <span>Pending: <b id="cPending">0</b></span> |
          <span>In Progress: <b id="cNotReady">0</b></span> |
          <span>Ready: <b id="cReady">0</b></span> |
          <span>Dispatched: <b id="cDispatched">0</b></span>
        </div>
      </div>
      <div class="table-wrap">
        <div class="pane active" id="paneDoctors"><div class="empty">Loading...</div></div>
        <div class="pane" id="panePending"></div>
        <div class="pane" id="paneReady"></div>
        <div class="pane" id="paneNotReady"></div>
        <div class="pane" id="paneDispatched"></div>
      </div>
    </div>

    <div class="support">
      HMS Support: Email: <a href="mailto:<?php echo htmlspecialchars($contactEmail, ENT_QUOTES); ?>"><?php echo htmlspecialchars($contactEmail, ENT_QUOTES); ?></a>
      | Phone: <strong><?php echo htmlspecialchars($contactPhone, ENT_QUOTES); ?></strong>
    </div>

  </main>

  <!-- ================= RIGHT RAIL ================= -->
  <aside class="lab-rail right">

    <div class="panel">
      <div class="panel-head" style="justify-content:center"><span><i class="fa-solid fa-magnifying-glass"></i> Patient Search</span></div>
      <div class="rail-body" style="padding:11px 12px">
        <div class="rail-field">
          <label class="rail-label" for="railName">By Patient Name</label>
          <input class="rail-input" type="text" id="railName" placeholder="Full name" oninput="renderLabPanes()">
        </div>
        <div class="rail-field">
          <label class="rail-label" for="railNo">By Patient No.</label>
          <input class="rail-input" type="text" id="railNo" placeholder="Patient number" oninput="renderLabPanes()">
        </div>
        <button type="button" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center" onclick="labClearSearch()">Clear Search</button>
      </div>
    </div>

    <div class="panel" id="sysMessages">
      <div class="panel-head"><span><i class="fa-solid fa-bell"></i> System Messages</span><button type="button" class="topbtn" onclick="loadMessages()">Refresh</button></div>
      <div class="rail-body" id="msgList" style="padding:11px 12px"><div class="empty" style="padding:12px">Loading...</div></div>
    </div>

  </aside>

</div>

<!-- ================= PROCESS / RESULT-ENTRY MODAL ================= -->
<div class="modal" id="processModal">
  <div class="modal-box">
    <div class="modal-head"><span>Lab Result Entry</span><button type="button" class="x" onclick="closeProcessModal()">&times;</button></div>
    <form id="processForm">
      <div class="modal-body">
        <div class="field">
          <label for="prRequisition">Requisition *</label>
          <select id="prRequisition" required><option value="">-- Awaiting requisitions --</option></select>
        </div>
        <div class="field">
          <label for="prSpecimen">Specimen Type</label>
          <input type="text" id="prSpecimen" placeholder="e.g. Venous Blood (EDTA Tube)">
        </div>
        <div class="field">
          <label for="prTechnician">Lab Technician / Staff</label>
          <input type="text" id="prTechnician" value="<?php echo htmlspecialchars($labStaff, ENT_QUOTES); ?>" readonly>
        </div>
        <div class="field">
          <label for="prResults">Test Results *</label>
          <textarea id="prResults" rows="3" placeholder="e.g. Hb: 12.5 g/dL, WBC: 11.2 x10^3/uL, MP: Positive (+)"></textarea>
        </div>
        <div class="field">
          <label for="prNormalRange">Normal / Reference Range</label>
          <textarea id="prNormalRange" rows="2" placeholder="e.g. Hb 12.0-16.0 g/dL, WBC 4.0-10.0 x10^3/uL"></textarea>
        </div>
        <div class="field">
          <label for="prInterpretation">Pathologist / Technician Remarks</label>
          <textarea id="prInterpretation" rows="2" placeholder="Clinical notes or observations..."></textarea>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" onclick="closeProcessModal()">Cancel</button>
        <button type="submit" class="btn btn-ghost" data-save="draft">Save Draft</button>
        <button type="submit" class="btn btn-primary" data-save="final">Mark Ready &amp; Save</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= SEND MESSAGE MODAL ================= -->
<div class="modal" id="sendModal">
  <div class="modal-box">
    <div class="modal-head"><span>Send Message</span><button type="button" class="x" onclick="closeSendModal()">&times;</button></div>
    <form id="sendForm">
      <div class="modal-body">
        <div class="field">
          <label for="sendType">Recipient Type *</label>
          <select id="sendType" onchange="loadRecipients()" required>
            <option value="staff">Staff</option>
            <option value="patient">Patient</option>
          </select>
        </div>
        <div class="field">
          <label for="sendRecipient">Recipient *</label>
          <select id="sendRecipient" required><option value="">-- Loading --</option></select>
        </div>
        <div class="field">
          <label for="sendSubject">Subject *</label>
          <input type="text" id="sendSubject" placeholder="Message subject" required>
        </div>
        <div class="field">
          <label for="sendChannel">Channel</label>
          <select id="sendChannel">
            <option value="internal">Internal</option>
            <option value="sms">SMS</option>
            <option value="email">Email</option>
          </select>
        </div>
        <div class="field">
          <label for="sendBody">Message *</label>
          <textarea id="sendBody" rows="4" placeholder="Type your message..." required></textarea>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" onclick="closeSendModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Send</button>
      </div>
    </form>
  </div>
</div>

<div id="labToast"></div>

<script>
var LAB_CAT_LABELS = <?php echo json_encode($labCategories); ?>;
var LAB_PARAM_DATA = <?php echo json_encode($labParamData); ?>;
var DISPATCH_KEY = 'hms:lab-dispatched';
var API = '/hms/backend/api';

var labData = [];
var labResultsMap = {};
var visitsCache = [];
var knownReqIds = {};
var LAB_VIEW = 'doctors';
var labPollTimer = null;
var POLL_INTERVAL_MS = 20000;
var LAB_VIEWS = ['doctors', 'pending', 'ready', 'notready', 'dispatched'];

/* ================= BOOT ================= */
document.addEventListener('DOMContentLoaded', function () {
    setupLabEvents();
    loadVisits();
    loadLabRequests();
    loadMessages();
    startSessionTimer();
    labPollTimer = setInterval(livePoll, POLL_INTERVAL_MS);
});

function setupLabEvents() {
    var entry = document.getElementById('labEntryForm');
    if (entry) entry.addEventListener('submit', handleLabEntrySubmit);
    var proc = document.getElementById('processForm');
    if (proc) proc.addEventListener('submit', handleProcessSubmit);
    var cr = document.getElementById('labCrForm');
    if (cr) cr.addEventListener('submit', handleCrSubmit);
    var send = document.getElementById('sendForm');
    if (send) send.addEventListener('submit', handleSendSubmit);
    var divider = document.getElementById('crToggleDivider');
    if (divider) divider.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') { e.preventDefault(); labCrToggle(); }
    });
}

/* ================= NAV / SCROLL ================= */
function labGoSection(id) {
    var el = document.getElementById(id);
    if (el && el.scrollIntoView) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
function labCardTab(view) { labSwitchTab(view); labGoSection('labWorklist'); }
function labClearSearch() {
    var ids = ['labSearch', 'railName', 'railNo'];
    ids.forEach(function (id) { var el = document.getElementById(id); if (el) el.value = ''; });
    renderLabPanes();
}

/* ================= SESSION TIMER ================= */
function startSessionTimer() {
    var el = document.getElementById('labSessionTimer');
    if (!el) return;
    var loginTime = parseInt(el.getAttribute('data-login-time'), 10) || Math.floor(Date.now() / 1000);
    var tick = function () {
        var s = Math.max(0, Math.floor(Date.now() / 1000) - loginTime);
        var h = String(Math.floor(s / 3600)).padStart(2, '0');
        var m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
        var sec = String(s % 60).padStart(2, '0');
        el.textContent = h + ':' + m + ':' + sec;
    };
    tick();
    setInterval(tick, 1000);
}

function labLogout() {
    fetch(API + '/auth.php?action=logout', { method: 'POST' })
        .catch(function () {})
        .then(function () { window.location.href = 'index.php'; });
}

/* ================= TOASTS ================= */
function toast(msg, type) {
    var box = document.getElementById('labToast');
    if (!box) return;
    var el = document.createElement('div');
    el.className = 'toast ' + (type || 'info');
    el.textContent = msg;
    box.appendChild(el);
    setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 4500);
}

/* ================= VISITS / PATIENT SELECT ================= */
async function loadVisits() {
    try {
        var r = await fetch(API + '/visits.php');
        var d = await r.json();
        if (d.success) visitsCache = d.visits || [];
    } catch (e) { console.error('Visits load error:', e); visitsCache = []; }
    renderVisitSelect();
}

function renderVisitSelect() {
    var sel = document.getElementById('entryPatient');
    if (!sel) return;
    var keep = sel.value;
    sel.innerHTML = '<option value="">-- Select Patient --</option>'
        + visitsCache.map(function (v) {
            return '<option value="' + v.id + '">' + escHtml(v.visit_number + ' - ' + v.patient_name + ' (' + v.hospital_number + ')') + '</option>';
        }).join('');
    if (keep) sel.value = keep;
}

/* ================= PARAMETERS / PRICE ================= */
function updateLabParameters() {
    var paramSel = document.getElementById('labParameterSelect');
    var cat = document.getElementById('labTypeSelect').value;
    if (!paramSel) return;
    paramSel.innerHTML = '<option value="">-- Select Lab Parameter --</option>';
    document.getElementById('labPriceInput').value = '';
    if (cat && LAB_PARAM_DATA[cat]) {
        LAB_PARAM_DATA[cat].forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = item.name;
            opt.setAttribute('data-code', item.code);
            opt.setAttribute('data-price', item.price);
            opt.textContent = item.name + ' - GHS ' + item.price;
            paramSel.appendChild(opt);
        });
    }
}

function updateLabPrice() {
    var sel = document.getElementById('labParameterSelect');
    var opt = sel && sel.options[sel.selectedIndex];
    document.getElementById('labPriceInput').value = 'GHS ' + (opt && opt.getAttribute('data-price') ? opt.getAttribute('data-price') : '0.00');
}

async function handleLabEntrySubmit(e) {
    e.preventDefault();
    var visitId = document.getElementById('entryPatient').value;
    var cat = document.getElementById('labTypeSelect').value;
    var sel = document.getElementById('labParameterSelect');
    var opt = sel.options[sel.selectedIndex];
    if (!visitId) { toast('Select the patient / visit first.', 'error'); return; }
    if (!cat) { toast('Select the lab category.', 'error'); return; }
    if (!opt || !opt.value) { toast('Select the lab parameter / test to be done.', 'error'); return; }

    var price = opt.getAttribute('data-price') || '0.00';
    var code = opt.getAttribute('data-code') || '';
    var label = LAB_CAT_LABELS[cat] || cat;
    var data = {
        visit_id: visitId,
        test_type: opt.value,
        test_description: 'Category: ' + label + (code ? ' (' + code + ')' : '') + ' | Price: GHS ' + price,
        urgency: 'routine'
    };
    try {
        var r = await fetch(API + '/lab.php?action=request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var d = await r.json();
        if (d.success) {
            toast('Test saved & submitted: ' + opt.value + ' - GHS ' + price + '.', 'success');
            document.getElementById('labEntryForm').reset();
            document.getElementById('labPriceInput').value = '';
            labSwitchTab('doctors');
            await loadLabRequests();
        } else {
            toast(d.error || 'Submit failed', 'error');
        }
    } catch (err) {
        console.error('Lab request error:', err);
        toast('Network error. Please try again.', 'error');
    }
}

/* ================= DATA ================= */
async function labRefresh() {
    try {
        await Promise.all([loadVisits(), loadLabRequests(), loadMessages()]);
        toast('Lab data refreshed.', 'success');
    } catch (e) { toast('Refresh failed - please try again.', 'error'); }
}

async function livePoll() {
    try {
        var r = await fetch(API + '/lab.php?action=requests');
        var d = await r.json();
        if (d && d.success) {
            var reqs = d.requests || [];
            var fresh = reqs.filter(function (x) { return !knownReqIds[String(x.id)]; });
            reqs.forEach(function (x) { knownReqIds[String(x.id)] = true; });
            labData = reqs;
            await loadLabResults();
            renderLabPanes();
            populateCrRequisitions();
            if (fresh.length) toast('New lab request: ' + (fresh[0].patient_name || '--') + ' - ' + (fresh[0].test_type || '--'), 'info');
        }
    } catch (e) { console.error('Live refresh error:', e); }
}

async function loadLabRequests() {
    try {
        var r = await fetch(API + '/lab.php?action=requests');
        var d = await r.json();
        labData = (d.success && d.requests) ? d.requests : [];
        (labData || []).forEach(function (x) { knownReqIds[String(x.id)] = true; });
    } catch (e) { console.error('Lab load error:', e); labData = []; }
    await loadLabResults();
    renderLabPanes();
    populateCrRequisitions();
}

async function loadLabResults() {
    labResultsMap = {};
    try {
        var r = await fetch(API + '/lab.php?action=results');
        var d = await r.json();
        if (d.success) {
            (d.results || []).forEach(function (res) {
                if (res.lab_request_id !== null && res.lab_request_id !== undefined) {
                    labResultsMap[String(res.lab_request_id)] = res;
                }
            });
        }
    } catch (e) { console.error('Lab results load error:', e); }
}

/* ================= TABS ================= */
function labSwitchTab(view) {
    if (LAB_VIEWS.indexOf(view) === -1) view = 'doctors';
    LAB_VIEW = view;
    LAB_VIEWS.forEach(function (t) {
        var btn = document.querySelector('.tab[data-view="' + t + '"]');
        var pane = document.getElementById('pane' + t.charAt(0).toUpperCase() + t.slice(1));
        if (btn) btn.classList.toggle('active', t === view);
        if (pane) pane.classList.toggle('active', t === view);
    });
    renderLabPanes();
}

/* ================= FILTERS / RENDER ================= */
function readFilters() {
    var s = (val('labSearch') || '').trim().toLowerCase();
    var nm = (val('railName') || '').trim().toLowerCase();
    var no = (val('railNo') || '').trim().toLowerCase();
    return { s: s, nm: nm, no: no };
}

function matchesFilter(r, f) {
    var name = String(r.patient_name || '').toLowerCase();
    var hn = String(r.hospital_number || '').toLowerCase();
    var test = String(r.test_type || '').toLowerCase();
    var id = String(r.id || '');
    if (f.s && !(name.indexOf(f.s) >= 0 || hn.indexOf(f.s) >= 0 || test.indexOf(f.s) >= 0 || id.indexOf(f.s) >= 0)) return false;
    if (f.nm && name.indexOf(f.nm) < 0) return false;
    if (f.no && hn.indexOf(f.no) < 0) return false;
    return true;
}

function labStatusBadge(status) {
    if (status === 'completed') return '<span class="badge b-ready">Ready</span>';
    if (status === 'in_progress') return '<span class="badge b-prog">In Progress</span>';
    if (status === 'cancelled') return '<span class="badge b-cancel">Cancelled</span>';
    return '<span class="badge b-pend">Pending</span>';
}

function crNote(r) {
    if (r.status === 'cancelled' && r.status_reason) {
        return '<div class="cr-note bad">' + escHtml(r.status_reason) + '</div>';
    }
    if (r.rescheduled_to) {
        return '<div class="cr-note warn">Rescheduled: ' + fmtTime(r.rescheduled_to)
            + (r.status_reason ? ' - ' + escHtml(r.status_reason) : '') + '</div>';
    }
    return '';
}

function requestTable(rows, cols, actionCell, emptyMsg) {
    return '<table class="lab-table"><thead><tr>' + cols + '</tr></thead><tbody>' + (rows.length
        ? rows.map(function (r) { return '<tr>' + actionCell(r) + '</tr>'; }).join('')
        : '<tr><td colspan="7" class="empty">' + escHtml(emptyMsg) + '</td></tr>') + '</tbody></table>';
}

function processBtn(r) {
    return '<td class="act-col"><button class="btn btn-primary btn-sm" onclick="openProcessModal(' + r.id + ')">Process Test</button></td>';
}
function dispatchBtn(r) {
    return '<td class="act-col"><button class="btn btn-success btn-sm" onclick="dispatchReport(' + r.id + ')">Dispatch</button></td>';
}

function renderLabPanes() {
    if (!document.getElementById('labTabs')) return;
    var f = readFilters();
    var dispatched = readDispatched();
    var ready = labData.filter(function (r) {
        return r.status === 'completed' && !dispatched.some(function (d) { return d.id === String(r.id); });
    });
    var pending = labData.filter(function (r) { return r.status === 'pending'; });
    var notReady = labData.filter(function (r) { return r.status === 'in_progress'; });

    /* counts */
    setCount('cAll', labData.length);
    setCount('cPending', pending.length);
    setCount('cNotReady', notReady.length);
    setCount('cReady', ready.length);
    setCount('cDispatched', dispatched.length);

    var headBase = '<th class="first-col">Req ID</th><th>Patient ID/Name</th><th>Lab Test Requested</th><th>Doctor</th><th>Date &amp; Time</th><th>Status</th><th class="act-col">Action</th>';
    var rowBase = function (r) {
        return '<td class="first-col cell-strong">LAB ' + escHtml(r.id) + '</td>'
            + '<td class="cell-strong">' + escHtml((r.patient_name || '--') + (r.hospital_number ? ' (' + r.hospital_number + ')' : '')) + '</td>'
            + '<td>' + escHtml(r.test_type || '--') + '</td>'
            + '<td>' + escHtml(r.doctor_name || '--') + '</td>'
            + '<td>' + fmtTime(r.requested_at) + '</td>'
            + '<td>' + labStatusBadge(r.status) + crNote(r) + '</td>';
    };

    /* Doctor Requests */
    var all = labData.filter(function (r) { return matchesFilter(r, f); });
    setPane('paneDoctors', requestTable(all, headBase, function (r) {
        var action;
        if (r.status === 'pending' || r.status === 'in_progress') action = processBtn(r);
        else if (r.status === 'completed') action = dispatchBtn(r);
        else action = '<td class="act-col muted">-</td>';
        return rowBase(r) + action;
    }, all.length ? '' : 'No lab requests yet - use the technician form above to add one.'));

    /* Pending Labs */
    var pendRows = pending.filter(function (r) { return matchesFilter(r, f); });
    setPane('panePending', requestTable(pendRows, headBase, function (r) { return rowBase(r) + processBtn(r); },
        'Nothing pending - all requested tests have been processed.'));

    /* Not Ready Labs */
    var nrRows = notReady.filter(function (r) { return matchesFilter(r, f); });
    setPane('paneNotReady', requestTable(nrRows, headBase, function (r) { return rowBase(r) + processBtn(r); },
        'No test is currently under processing.'));

    /* Ready Labs */
    var readyRows = ready.filter(function (r) { return matchesFilter(r, f); });
    var readyHead = '<th class="first-col">Req ID</th><th>Patient ID/Name</th><th>Lab Test</th><th>Result</th><th>Verified / Completed</th><th>Status</th><th class="act-col">Action</th>';
    setPane('paneReady', requestTable(readyRows, readyHead, function (r) {
        var res = labResultsMap[String(r.id)] || null;
        var resultText = res ? String(res.results || '--') : String(r.result_status || '--');
        var doneAt = res && res.completed_at ? fmtTime(res.completed_at) : fmtTime(r.requested_at);
        return '<td class="first-col cell-strong">LAB ' + escHtml(r.id) + '</td>'
            + '<td class="cell-strong">' + escHtml(r.patient_name || '--') + '</td>'
            + '<td>' + escHtml(r.test_type || '--') + '</td>'
            + '<td class="cell-result">' + escHtml(resultText) + '</td>'
            + '<td>' + (res && res.verified_name ? escHtml(res.verified_name) + '<br>' : '') + doneAt + '</td>'
            + '<td>' + labStatusBadge(r.status) + '</td>'
            + '<td class="act-col"><button class="btn btn-success btn-sm" onclick="dispatchReport(' + r.id + ')">Dispatch Report</button></td>';
    }, 'No verified results are ready for dispatch yet.'));

    /* Dispatched Labs */
    var dHead = '<th class="first-col">Dispatch ID</th><th>Patient Name</th><th>Lab Test</th><th>Dispatch Date</th><th class="act-col">Report</th>';
    var dRows = dispatched;
    var dHtml = '<table class="lab-table"><thead><tr>' + dHead + '</tr></thead><tbody>' + (dRows.length
        ? dRows.map(function (d) {
            var r = labData.find(function (x) { return String(x.id) === d.id; }) || null;
            return '<tr>'
                + '<td class="first-col cell-strong">DSP ' + escHtml(d.id) + '</td>'
                + '<td class="cell-strong">' + escHtml(r ? r.patient_name : '--') + '</td>'
                + '<td>' + escHtml(r ? r.test_type : '--') + '</td>'
                + '<td>' + (d.at ? fmtTime(d.at) : '--') + '</td>'
                + '<td class="act-col"><button class="btn btn-ghost btn-sm" onclick="printReport(' + (r ? r.id : 0) + ')">Print PDF</button></td>'
                + '</tr>';
        }).join('')
        : '<tr><td colspan="5" class="empty">No dispatched reports yet - dispatch a ready lab above.</td></tr>') + '</tbody></table>';
    setPane('paneDispatched', dHtml);
}

function setCount(id, n) {
    var el = document.getElementById(id);
    if (el) el.textContent = n;
    var tile = { cAll: 'tileSample', cReady: 'tileReady', cPending: 'tilePending', cDispatched: 'tileDispatched' }[id];
    var t = tile ? document.getElementById(tile) : null;
    if (t) t.textContent = n;
    var badge = { cPending: 'qlPendingBadge', cReady: 'qlReadyBadge' }[id];
    var b = badge ? document.getElementById(badge) : null;
    if (b) { b.textContent = n; b.style.display = n > 0 ? 'inline-block' : 'none'; }
}

function setPane(id, html) { var el = document.getElementById(id); if (el) el.innerHTML = html; }

/* ================= PROCESS / RESULT ENTRY ================= */
function populateRequisitions(selectId, placeholder) {
    var sel = document.getElementById(selectId);
    if (!sel) return;
    var keep = sel.value;
    var rows = labData.filter(function (r) { return r.status === 'pending' || r.status === 'in_progress'; });
    sel.innerHTML = '<option value="">' + placeholder + '</option>'
        + rows.map(function (r) {
            return '<option value="' + r.id + '">LAB ' + escHtml(r.id) + ' - ' + escHtml(r.patient_name || '')
                + ' (' + escHtml(r.test_type || '') + ')</option>';
        }).join('');
    if (keep && rows.some(function (r) { return String(r.id) === String(keep); })) sel.value = keep;
}

function openProcessModal(requestId) {
    populateRequisitions('prRequisition', '-- Select Requisition --');
    var sel = document.getElementById('prRequisition');
    if (sel && requestId && Array.from(sel.options).some(function (o) { return String(o.value) === String(requestId); })) {
        sel.value = String(requestId);
    }
    document.getElementById('processForm').reset();
    document.getElementById('prTechnician').value = <?php echo json_encode($labStaff); ?>;
    document.getElementById('processModal').classList.add('show');
}
function closeProcessModal() { document.getElementById('processModal').classList.remove('show'); }

async function handleProcessSubmit(e) {
    e.preventDefault();
    var reqId = document.getElementById('prRequisition').value;
    if (!reqId) { toast('Select a requisition first.', 'error'); return; }
    var saveAs = e.submitter && e.submitter.getAttribute ? (e.submitter.getAttribute('data-save') || 'final') : 'final';
    var spec = document.getElementById('prSpecimen').value.trim();
    var results = document.getElementById('prResults').value.trim();
    if (spec) results = results ? 'Specimen: ' + spec + '\n' + results : 'Specimen: ' + spec;
    if (!results) { toast('Enter the result values before saving.', 'error'); return; }

    try {
        var r = await fetch(API + '/lab.php?action=result', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                lab_request_id: reqId,
                results: results,
                normal_range: document.getElementById('prNormalRange').value.trim(),
                interpretation: document.getElementById('prInterpretation').value.trim(),
                status: saveAs
            })
        });
        var d = await r.json();
        if (d.success) {
            toast(saveAs === 'final' ? 'Result saved and marked as READY.' : 'Result saved as draft.', 'success');
            closeProcessModal();
            labSwitchTab(saveAs === 'final' ? 'ready' : 'notready');
            await loadLabRequests();
        } else {
            toast(d.error || 'Save failed', 'error');
        }
    } catch (err) {
        console.error('Entry save error:', err);
        toast('Network error. Please try again.', 'error');
    }
}

/* ================= CANCEL / RESCHEDULE ================= */
function labCrToggle() {
    var divider = document.getElementById('crToggleDivider');
    var body = document.getElementById('crBody');
    if (!divider || !body) return;
    var open = (body.style.display === 'none' || !body.style.display);
    body.style.display = open ? 'block' : 'none';
    divider.classList.toggle('open', open);
    divider.setAttribute('aria-expanded', open ? 'true' : 'false');
    var hint = document.getElementById('crHint');
    if (hint) hint.textContent = open ? '- press to hide' : '- toggle to view';
    if (open) labGoSection('crSection');
}

function labCrToggleAction() {
    var action = document.getElementById('crAction').value;
    var whenField = document.getElementById('crWhenField');
    var whenInput = document.getElementById('crWhen');
    var isResched = action === 'reschedule';
    whenField.style.display = isResched ? 'flex' : 'none';
    whenInput.required = isResched;
}

function populateCrRequisitions() { populateRequisitions('crRequisition', '-- Select Requisition --'); }

async function handleCrSubmit(e) {
    e.preventDefault();
    var id = document.getElementById('crRequisition').value;
    var action = document.getElementById('crAction').value;
    var reasonSel = document.getElementById('crReason').value;
    var notes = document.getElementById('crNotes').value.trim();
    var when = document.getElementById('crWhen').value;

    if (!id) { toast('Select the requisition to cancel or reschedule.', 'error'); return; }
    if (!action) { toast('Choose Cancel or Reschedule.', 'error'); return; }
    if (!reasonSel) { toast('Select a reason.', 'error'); return; }
    if (action === 'reschedule' && !when) { toast('Enter the new date and time.', 'error'); return; }

    var reason = reasonSel + (notes ? ' - ' + notes : '');
    var payload = { id: id, reason: reason };
    if (action === 'reschedule') payload.scheduled_at = when;

    try {
        var r = await fetch(API + '/lab.php?action=' + (action === 'cancel' ? 'cancel' : 'reschedule'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        var d = await r.json();
        if (d.success) {
            toast(action === 'cancel' ? 'Test cancelled. Reason recorded.' : 'Test rescheduled to ' + when.replace('T', ' ') + '.', 'success');
            e.target.reset();
            labCrToggleAction();
            await loadLabRequests();
        } else {
            toast(d.error || 'Action failed.', 'error');
        }
    } catch (err) {
        console.error('Cancel/reschedule error:', err);
        toast('Network error. Please try again.', 'error');
    }
}

/* ================= DISPATCH ================= */
function readDispatched() {
    try { var raw = localStorage.getItem(DISPATCH_KEY); return raw ? JSON.parse(raw) : []; }
    catch (e) { return []; }
}

function dispatchReport(requestId) {
    var list = readDispatched();
    if (!list.some(function (d) { return String(d.id) === String(requestId); })) {
        list.push({ id: String(requestId), at: new Date().toISOString() });
        try { localStorage.setItem(DISPATCH_KEY, JSON.stringify(list)); } catch (e) {}
    }
    toast('Report marked as dispatched.', 'success');
    renderLabPanes();
    labSwitchTab('dispatched');
}

function printReport(requestId) {
    var r = labData.find(function (x) { return String(x.id) === String(requestId); });
    toast('Report LAB ' + (r ? r.id : '') + ' queued for printing - use Ctrl+P to export the PDF.', 'info');
}

/* ================= MESSAGES ================= */
var labMessages = [];

async function loadMessages() {
    var box = document.getElementById('msgList');
    try {
        var res = await Promise.all([
            fetch(API + '/messages.php?action=list&limit=6').then(function (r) { return r.json(); }),
            fetch(API + '/messages.php?action=unread_count').then(function (r) { return r.json(); })
        ]);
        labMessages = (res[0].success && res[0].messages) ? res[0].messages : [];
        setAlerts(res[1] && res[1].success ? Number(res[1].unread) : 0);
        if (!box) return;
        box.innerHTML = labMessages.length
            ? labMessages.map(function (m, i) {
                return '<button type="button" class="msg' + (Number(m.is_read) === 0 ? ' unread' : '') + '" onclick="showMessage(' + i + ')">'
                    + '<span class="mh"><span>' + escHtml(m.sender_name || 'System') + '</span><span>' + fmtTime(m.sent_at) + '</span></span>'
                    + '<span class="ms">' + escHtml(m.subject || '(no subject)') + '</span>'
                    + '<span class="mb">' + escHtml(m.message || '') + '</span>'
                    + '</button>';
            }).join('')
            : '<div class="empty" style="padding:12px">No messages yet.</div>';
    } catch (e) {
        console.error('Messages load error:', e);
        if (box) box.innerHTML = '<div class="empty" style="padding:12px">Could not load messages.</div>';
    }
}

function showMessage(i) {
    var m = labMessages[i];
    if (!m) return;
    toast((m.subject || '(no subject)') + ' — ' + (m.message || ''), 'info');
}

function setAlerts(unread) {
    var n = document.getElementById('daysAlertNum');
    var l = document.getElementById('daysAlertLabel');
    if (n) n.textContent = unread;
    if (l) l.textContent = (unread === 0) ? 'No alerts today' : (unread + ' unread alert' + (unread === 1 ? '' : 's'));
}

/* ================= SEND MESSAGE ================= */
function openSendModal() {
    document.getElementById('sendModal').classList.add('show');
    loadRecipients();
}
function closeSendModal() { document.getElementById('sendModal').classList.remove('show'); }

async function loadRecipients() {
    var type = document.getElementById('sendType').value;
    var sel = document.getElementById('sendRecipient');
    if (!sel) return;
    sel.innerHTML = '<option value="">-- Loading --</option>';
    try {
        var r = await fetch(API + '/messages.php?action=recipients&type=' + encodeURIComponent(type));
        var d = await r.json();
        var list = (d.success && d.recipients) ? d.recipients : [];
        sel.innerHTML = '<option value="">-- Select Recipient --</option>'
            + list.map(function (x) {
                var label = x.name + (x.hospital_number ? ' (' + x.hospital_number + ')' : (x.role ? ' (' + x.role + ')' : ''));
                return '<option value="' + x.id + '">' + escHtml(label) + '</option>';
            }).join('');
    } catch (e) {
        console.error('Recipients load error:', e);
        sel.innerHTML = '<option value="">-- Could not load --</option>';
    }
}

async function handleSendSubmit(e) {
    e.preventDefault();
    var payload = {
        recipient_type: document.getElementById('sendType').value,
        recipient_id: document.getElementById('sendRecipient').value,
        subject: document.getElementById('sendSubject').value.trim(),
        message: document.getElementById('sendBody').value.trim(),
        channel: document.getElementById('sendChannel').value
    };
    if (!payload.recipient_id || !payload.subject || !payload.message) {
        toast('Recipient, subject and message are required.', 'error');
        return;
    }
    try {
        var r = await fetch(API + '/messages.php?action=send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        var d = await r.json();
        if (d.success) {
            toast('Message sent.', 'success');
            e.target.reset();
            closeSendModal();
            loadMessages();
        } else {
            toast(d.error || 'Could not send message.', 'error');
        }
    } catch (err) {
        console.error('Send message error:', err);
        toast('Network error. Please try again.', 'error');
    }
}

/* ================= HELPERS ================= */
function val(id) { var el = document.getElementById(id); return el ? el.value : ''; }

function fmtTime(v) {
    if (!v) return '--';
    var d = new Date(String(v).replace(' ', 'T'));
    if (isNaN(d.getTime())) return String(v);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    return pad(d.getDate()) + '-' + months[d.getMonth()] + '-' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}

function escHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
</script>
</body>
</html>
