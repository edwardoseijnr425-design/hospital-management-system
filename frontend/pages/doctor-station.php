<?php
// Doctor Station — clinical workspace for admitted patients.
// Layout mirrors the Nurses Station (vitals.php): ward filter panel, WARD DETAILS
// bar, then one card per in-patient. Each card carries the six-tab clinical
// workspace (Doctor Notes, Vitals, Prescriptions, Nurse Notes, Treatment,
// Investigations). Doctor write actions are preserved:
//   * Doctor Notes tab  -> clinical_notes.php (OPD / IPD / Discharge Summary)
//   * Prescriptions tab -> consultation_form.php (drug orders)
//   * Investigations tab-> consultation_form.php (lab + radiology requests)
// The shell (dashboard.php) already authenticated the session.
require_once __DIR__ . '/../../backend/config/config.php';
$__clinicStaff = getCurrentUserName() ?: 'Staff';
?>
<style>
/* ================= DOCTOR STATION WORKSPACE (scoped under #docstation) =================
   Plain CSS. The project ships no Bootstrap/Alpine/Tailwind; the shell supplies a
   few utility shims but everything the station needs is defined here. */
#docstation,#docstation *{box-sizing:border-box}
#docstation{--ds-red:#C0392B;--ds-blue:#1B659D;--ds-ink:#0F2D59;--ds-mute:#64748B;--ds-line:#E2E8F0;--ds-bg:#F4F7F9}
#docstation{font-family:inherit;color:#334155}
#docstation .ds-wrap{padding:0}

/* ---------- Top filter panel ---------- */
#docstation .ds-filter{background:#fff;border:1px solid var(--ds-line);border-radius:10px;box-shadow:0 1px 2px rgba(15,45,89,.06);padding:14px;margin-bottom:14px}
#docstation .ds-grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
#docstation .ds-field{min-width:0}
#docstation .ds-label{display:block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#64748B;margin-bottom:5px}
#docstation .ds-select,#docstation .ds-input{width:100%;padding:8px 10px;border:1px solid #CBD5E1;border-radius:8px;background:#F8FAFC;font-size:13px;font-weight:600;color:#334155;font-family:inherit;outline:none}
#docstation .ds-select:focus,#docstation .ds-input:focus{border-color:var(--ds-blue);box-shadow:0 0 0 2px rgba(27,101,157,.18)}
#docstation .ds-actions-row{display:flex;justify-content:flex-end;gap:8px;margin-top:12px}
#docstation .ds-btn{border:1px solid transparent;border-radius:8px;padding:8px 18px;font-size:12px;font-weight:800;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase}
#docstation .ds-btn:disabled{opacity:.55;cursor:not-allowed}
#docstation .ds-btn.light{background:#F1F5F9;color:#334155;border-color:#E2E8F0}
#docstation .ds-btn.light:hover{background:#E2E8F0}
#docstation .ds-btn.primary{background:var(--ds-blue);color:#fff}
#docstation .ds-btn.primary:hover{background:#155380}
#docstation .ds-btn.red{background:var(--ds-red);color:#fff}
#docstation .ds-btn.red:hover{background:#96281B}
#docstation .ds-btn.green{background:#15803D;color:#fff}
#docstation .ds-btn.green:hover{background:#12682f}
#docstation .ds-btn.outline{background:#fff;color:var(--ds-blue);border-color:var(--ds-blue)}
#docstation .ds-btn.outline:hover{background:#F1F8FD}
#docstation .ds-btn.sm{padding:6px 12px;font-size:11px}

/* ---------- WARD DETAILS bar ---------- */
#docstation .ds-ward-summary{text-align:center;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#475569;font-size:11px;margin:4px 0 12px}
#docstation .ds-ward-summary strong{color:var(--ds-blue)}
#docstation .ds-ward-summary .muted{color:#1E293B}

/* ---------- Card ---------- */
#docstation .ds-card{background:#fff;border:1px solid var(--ds-line);border-radius:12px;box-shadow:0 1px 3px rgba(15,45,89,.08);margin-bottom:18px;overflow:hidden}
#docstation .ds-card-pad{padding:14px}

/* Ward + quick actions bar */
#docstation .ds-wardbar{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;background:#fff;border:1px solid var(--ds-line);border-radius:10px;box-shadow:0 1px 2px rgba(15,45,89,.06);padding:12px 14px;margin-bottom:12px}
#docstation .ds-wardbar .left{display:flex;align-items:center;gap:8px}
#docstation .ds-wardbar .left i{color:var(--ds-blue);font-size:15px}
#docstation .ds-wardbar h3{margin:0;font-size:14px;font-weight:800;color:#1E293B}
#docstation .ds-bedchip{background:#E2E8F0;color:#334155;font-weight:800;font-size:10px;padding:3px 8px;border-radius:4px;text-transform:uppercase;letter-spacing:.4px}
#docstation .ds-wardbar .right{display:flex;gap:8px;flex-wrap:wrap}

/* Red STATION MANAGEMENT banner */
#docstation .ds-banner{background:var(--ds-red);color:#fff;border-radius:10px;padding:12px 14px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px}
#docstation .ds-banner h3{margin:0;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;gap:8px}
#docstation .ds-banner h3 i{color:#FCD34D}
#docstation .ds-banner .sub{font-size:10.5px;color:#FEE2E2;margin-top:3px}
#docstation .ds-banner .hn{background:rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.35);border-radius:8px;padding:5px 11px;font-size:12px;font-weight:800;display:inline-flex;align-items:center;gap:6px;white-space:nowrap}

/* Patient details card */
#docstation .ds-patient{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;background:#fff;border:1px solid var(--ds-line);border-radius:10px;box-shadow:0 1px 2px rgba(15,45,89,.06);padding:14px;margin-bottom:12px}
#docstation .ds-patient .who{display:flex;align-items:center;gap:12px}
#docstation .ds-avatar{width:44px;height:44px;border-radius:50%;background:var(--ds-blue);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex:0 0 auto;box-shadow:0 1px 2px rgba(15,45,89,.25)}
#docstation .ds-patient .nm{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
#docstation .ds-patient .nm h4{margin:0;font-size:14px;font-weight:800;text-transform:uppercase;color:#1E293B}
#docstation .ds-idchip{background:var(--ds-blue);color:#fff;font-size:10px;font-weight:800;padding:2px 8px;border-radius:4px;letter-spacing:.3px}
#docstation .ds-admchip{background:#F1F5F9;color:#475569;border:1px solid #CBD5E1;font-size:10px;font-weight:800;padding:2px 9px;border-radius:999px;text-transform:uppercase;letter-spacing:.3px}
#docstation .ds-admchip.discharged{background:#DCFCE7;color:#15803D;border-color:#86EFAC}
#docstation .ds-admchip.died{background:#FEE2E2;color:#B91C1C;border-color:#FCA5A5}
#docstation .ds-admchip.absconded{background:#FEF3C7;color:#B45309;border-color:#FCD34D}
#docstation .ds-patient .demo{font-size:11px;color:#64748B;margin-top:3px}
#docstation .ds-patient .demo b{color:#334155}
#docstation .ds-link{color:var(--ds-blue);font-weight:700;font-size:11px;text-decoration:none;cursor:pointer;display:inline-block;margin-top:3px}
#docstation .ds-link:hover{text-decoration:underline}
#docstation .ds-allergy{display:inline-flex;align-items:center;gap:5px;background:#FEF5E0;border:1px solid #F0AD4E;color:#8A5A00;font-size:10.5px;font-weight:800;padding:4px 10px;border-radius:6px}

/* Latest vitals tiles */
#docstation .ds-vitals{background:#fff;border:1px solid var(--ds-line);border-radius:10px;box-shadow:0 1px 2px rgba(15,45,89,.06);padding:14px;margin-bottom:12px}
#docstation .ds-vitals .head{display:flex;justify-content:space-between;align-items:center;font-size:11px;color:#94A3B8;margin-bottom:10px}
#docstation .ds-vitals .head .ttl{font-weight:800;text-transform:uppercase;color:var(--ds-blue);display:flex;align-items:center;gap:6px}
#docstation .ds-tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
#docstation .ds-tile{background:#F8FAFC;border:1px solid var(--ds-line);border-radius:8px;padding:10px;text-align:center}
#docstation .ds-tile .k{display:block;font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#94A3B8}
#docstation .ds-tile .v{font-size:14px;font-weight:800;color:#1E293B}
#docstation .ds-tile.none .v{color:#B6BECB;font-weight:600;font-size:11.5px}

/* ---------- Clinical workspace tabs ---------- */
#docstation .ds-tabs{display:flex;flex-wrap:wrap;gap:6px;border-bottom:1px solid var(--ds-line);padding-bottom:10px;margin-bottom:14px}
#docstation .ds-tab{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid transparent;border-radius:8px;background:#F1F5F9;color:#334155;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:background-color .15s,color .15s,box-shadow .15s}
#docstation .ds-tab:hover{background:#E2E8F0}
#docstation .ds-tab.active{background:var(--ds-blue);color:#fff;box-shadow:0 2px 6px rgba(15,45,89,.25)}
#docstation .ds-tab i{font-size:12px}
#docstation .ds-panel{display:none}
#docstation .ds-panel.active{display:block}

/* Panels content */
#docstation .ds-sec{border:1px solid var(--ds-line);border-radius:10px;padding:14px;background:#fff}
#docstation .ds-sec-head{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;border-bottom:1px solid var(--ds-line);padding-bottom:9px;margin-bottom:12px}
#docstation .ds-sec-head h4{margin:0;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--ds-blue);display:flex;align-items:center;gap:7px}
#docstation .ds-sec-head .hint{font-size:10.5px;color:#94A3B8;font-weight:600}

#docstation textarea,#docstation input[type=text],#docstation input[type=number],#docstation select{width:100%;padding:8px 10px;border:1px solid #CBD5E1;border-radius:8px;font-size:12.5px;font-family:inherit;background:#fff;color:#1E293B;outline:none}
#docstation textarea{resize:vertical;min-height:90px;line-height:1.5}
#docstation textarea:focus,#docstation input:focus,#docstation select:focus{border-color:var(--ds-blue);box-shadow:0 0 0 2px rgba(27,101,157,.15)}
#docstation .ds-fld{margin-bottom:10px}
#docstation .ds-fld:last-child{margin-bottom:0}
#docstation .ds-fld label{display:block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#64748B;margin-bottom:4px}
#docstation .ds-row2{display:grid;grid-template-columns:1.6fr 1fr;gap:10px}

/* Tables */
#docstation .ds-table-wrap{overflow-x:auto;border:1px solid var(--ds-line);border-radius:8px}
#docstation table.ds-table{width:100%;border-collapse:collapse;font-size:12px}
#docstation table.ds-table th{background:#F1F5F9;color:#475569;font-size:10px;text-transform:uppercase;letter-spacing:.4px;text-align:left;padding:8px 10px;border-bottom:1px solid var(--ds-line);white-space:nowrap}
#docstation table.ds-table td{padding:8px 10px;border-bottom:1px solid #EEF2F7;vertical-align:middle;color:#334155}
#docstation table.ds-table tr:last-child td{border-bottom:none}
#docstation .ds-pill{display:inline-block;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:800;border:1px solid}
#docstation .ds-pill.ok{background:#DCFCE7;color:#15803D;border-color:#86EFAC}
#docstation .ds-pill.warn{background:#FEF3C7;color:#B45309;border-color:#FCD34D}
#docstation .ds-pill.bad{background:#FEE2E2;color:#B91C1C;border-color:#FCA5A5}
#docstation .ds-pill.info{background:#E0F2FE;color:#0369A1;border-color:#7DD3FC}

/* Clinical notes feed (tab 1) */
#docstation .ds-note-item{background:#EFF6FF;border:1px solid #BFDBFE;border-radius:8px;padding:10px;margin-bottom:8px}
#docstation .ds-note-item .top{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px}
#docstation .ds-note-item .who{font-weight:800;color:#1D4ED8;font-size:11.5px}
#docstation .ds-note-item .when{font-size:10.5px;color:#94A3B8}
#docstation .ds-note-item .txt{white-space:pre-wrap;color:#334155;font-size:12px;line-height:1.5}
#docstation .ds-note-item .rm{background:#fff;border:1px solid #F0B7B2;color:#C0392B;border-radius:4px;font-size:10px;font-weight:800;padding:3px 8px;cursor:pointer;font-family:inherit}
#docstation .ds-note-item .rm:hover{background:#FDECEA}
#docstation .ds-empty{color:#94A3B8;font-size:12px;padding:10px;text-align:center}

/* Prescription builder */
#docstation .ds-rx-add{display:flex;gap:8px}
#docstation .ds-rx-add input{flex:1}
#docstation .ds-rx-add .ds-btn{flex:0 0 auto}
#docstation .ds-rx-hint{font-size:10.5px;color:#94A3B8;margin-top:6px}
#docstation .ds-rx-lines{margin-top:10px;display:flex;flex-direction:column;gap:8px}
#docstation .ds-rx-line{border:1px solid var(--ds-line);border-radius:8px;padding:9px;background:#F8FAFC}
#docstation .ds-rx-line .top{display:flex;align-items:center;gap:8px}
#docstation .ds-rx-line .top b{font-size:12.5px;color:#1E293B}
#docstation .ds-rx-line .top .gen{font-size:10.5px;color:#64748B}
#docstation .ds-rx-line .top .rm{margin-left:auto;background:#fff;border:1px solid #F0B7B2;color:#C0392B;border-radius:4px;font-size:10px;font-weight:800;padding:3px 8px;cursor:pointer;font-family:inherit}
#docstation .ds-rx-line .flds{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:8px}
#docstation .ds-rx-line .flds label{font-size:9.5px}
#docstation .ds-rx-line .flds input{padding:6px 8px;font-size:11.5px}
#docstation .ds-rx-empty{font-size:11.5px;color:#94A3B8;text-align:center;padding:12px;border:1px dashed var(--ds-line);border-radius:8px}

/* Investigations catalogue */
#docstation .ds-inv{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}
#docstation .ds-inv label{display:flex;align-items:center;gap:8px;background:#F8FAFC;border:1px solid var(--ds-line);border-radius:8px;padding:8px 10px;font-size:12px;color:#334155;font-weight:600;cursor:pointer;text-transform:none;letter-spacing:0}
#docstation .ds-inv label:hover{background:#F1F5F9}
#docstation .ds-inv label .kind{margin-left:auto;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;padding:1px 6px;border-radius:3px;background:#E2E8F0;color:#475569}
#docstation .ds-inv input:checked + span + .kind{background:var(--ds-blue);color:#fff}

/* Footer */
#docstation .ds-footer{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;padding:10px 14px;border-top:1px solid var(--ds-line);background:#fff}
#docstation .ds-footer .log{font-size:11px;color:#64748B}
#docstation .ds-footer .btns{display:flex;gap:8px;flex-wrap:wrap}

/* Modal (Medical Records) — the shell has a .modal base, we scope our extra bits */
#docstation-modal{position:fixed;inset:0;background:rgba(15,23,42,.5);display:none;align-items:center;justify-content:center;z-index:2000;padding:16px}
#docstation-modal.show{display:flex}
#docstation-modal .dm-box{background:#fff;border-radius:12px;max-width:760px;width:100%;max-height:88vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 50px rgba(2,6,23,.35)}
#docstation-modal .dm-head{background:var(--ds-red);color:#fff;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;gap:10px}
#docstation-modal .dm-head h3{margin:0;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.4px}
#docstation-modal .dm-head .x{background:transparent;border:none;color:#fff;font-size:20px;cursor:pointer;line-height:1}
#docstation-modal .dm-body{padding:16px;overflow-y:auto}
#docstation-modal .dm-foot{padding:12px 16px;border-top:1px solid var(--ds-line);text-align:right}
/* The modal sits outside #docstation, so re-declare the tokens and the small set
   of station classes it reuses (buttons, note cards, section heads, empty state). */
#docstation-modal{--ds-blue:#1B659D;--ds-line:#E2E8F0}
#docstation-modal .ds-btn{border:1px solid #E2E8F0;border-radius:8px;padding:8px 18px;font-size:12px;font-weight:800;letter-spacing:.3px;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase;background:#F1F5F9;color:#334155}
#docstation-modal .ds-btn:hover{background:#E2E8F0}
#docstation-modal .ds-note-item{background:#EFF6FF;border:1px solid #BFDBFE;border-radius:8px;padding:10px;margin-bottom:8px}
#docstation-modal .ds-note-item .top{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px}
#docstation-modal .ds-note-item .who{font-weight:800;color:#1D4ED8;font-size:11.5px}
#docstation-modal .ds-note-item .when{font-size:10.5px;color:#94A3B8}
#docstation-modal .ds-note-item .txt{white-space:pre-wrap;color:#334155;font-size:12px;line-height:1.5}
#docstation-modal .ds-empty{color:#94A3B8;font-size:12px;padding:10px;text-align:center}
#docstation-modal .ds-note-sec{display:flex;flex-direction:column;gap:2px;margin:0 0 8px;padding-bottom:6px;border-bottom:1px dashed #D9E7F8}
#docstation-modal .ds-note-sec:last-child{border-bottom:none;margin-bottom:0}
#docstation-modal .ds-note-sec .k{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#1D4ED8}
#docstation-modal .ds-note-sec .v{color:#334155;font-size:12px;white-space:pre-wrap;line-height:1.5}
#docstation-modal .ds-note-diag{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;background:#fff;border:1px solid #E2E8F0;border-radius:8px;padding:9px;margin-top:8px}
#docstation-modal .ds-note-diag .k{display:block;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#94A3B8;margin-bottom:2px}
#docstation-modal .ds-note-diag .k.blue{color:#1B659D}
#docstation-modal .ds-note-diag .v{font-weight:700;color:#1E293B;font-size:12px}
#docstation-modal .ds-status-badge{display:inline-block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;padding:2px 9px;border-radius:6px;border:1px solid}
#docstation-modal .ds-status-badge.new{background:#ECFDF5;color:#047857;border-color:#6EE7B7}
#docstation-modal .ds-status-badge.old{background:#EFF6FF;color:#1D4ED8;border-color:#BFDBFE}
#docstation-modal .ds-status-badge.recurring{background:#FFFBEB;color:#B45309;border-color:#FCD34D}
#docstation-modal .ds-status-badge.cured{background:#F0FDFA;color:#0F766E;border-color:#5EEAD4}
#docstation-modal .ds-discharge-badge{display:inline-block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;padding:2px 9px;border-radius:6px;border:1px solid}
#docstation-modal .ds-discharge-badge.continue{background:#EFF6FF;color:#1D4ED8;border-color:#BFDBFE}
#docstation-modal .ds-discharge-badge.ready{background:#FFFBEB;color:#B45309;border-color:#FCD34D}
#docstation-modal .ds-discharge-badge.fit{background:#ECFDF5;color:#047857;border-color:#6EE7B7}
#docstation-modal .ds-discharge-badge.referred{background:#FEF2F2;color:#B91C1C;border-color:#FCA5A5}
#docstation-modal .ds-sec-head{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;border-bottom:1px solid var(--ds-line);padding-bottom:9px;margin-bottom:12px}
#docstation-modal .ds-sec-head h4{margin:0;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--ds-blue);display:flex;align-items:center;gap:7px}
#docstation .ds-save-state{font-size:10.5px;color:#15803D;display:none;align-items:center;gap:5px}
#docstation .ds-save-state.show{display:inline-flex}

@media (max-width:900px){
  #docstation .ds-grid2{grid-template-columns:1fr}
  #docstation .ds-tiles{grid-template-columns:repeat(2,1fr)}
  #docstation .ds-row2{grid-template-columns:1fr}
  #docstation .ds-inv{grid-template-columns:1fr}
  #docstation .ds-inv-card .ds-inv{grid-template-columns:1fr}
  #docstation .ds-rx-line .flds{grid-template-columns:1fr 1fr}
  #docstation .ds-consult-grid{grid-template-columns:1fr}
  #docstation .ds-consult-grid.ds-diagnosis{grid-template-columns:1fr}
  #docstation .ds-note-diag{grid-template-columns:1fr}
}
@media (max-width:640px){
  #docstation .ds-tiles{grid-template-columns:1fr 1fr}
}

/* ---- EXPANDED CLINICAL CONSULTATION FORM + PROFILE STATUS (snippet integration) ---- */
/* ACTIVE / INACTIVE patient status badge with a live pulse dot */
#docstation .ds-status-chip{display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:999px;font-size:9.5px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;border:1px solid}
#docstation .ds-status-chip .dot{width:7px;height:7px;border-radius:50%;display:inline-block;flex:0 0 auto}
#docstation .ds-status-chip.on{background:#ECFDF5;color:#047857;border-color:#6EE7B7}
#docstation .ds-status-chip.on .dot{background:#10B981;animation:dsPulse 1.4s ease-in-out infinite}
#docstation .ds-status-chip.off{background:#F1F5F9;color:#64748B;border-color:#CBD5E1}
#docstation .ds-status-chip.off .dot{background:#94A3B8}
@keyframes dsPulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.45;transform:scale(.8)}}
/* Right column of the patient profile header (allergy chip + refill button) */
#docstation .ds-patient-right{display:flex;flex-direction:column;align-items:flex-start;gap:8px}
/* Two-column consultation form rows */
#docstation .ds-consult-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
/* Allergies / drug reactions is highlighted as a warning-style field */
#docstation .ds-fld.warn textarea{background:#FFFBF5;border-color:#F0AD4E}
#docstation .ds-fld.warn label{color:#B45309}
/* Diagnosis row (provisional / confirmed / status) on a shaded panel */
#docstation .ds-consult-grid.ds-diagnosis{grid-template-columns:repeat(3,1fr);background:#F8FAFC;border:1px solid var(--ds-line);border-radius:12px;padding:14px;margin-bottom:10px}
#docstation .ds-consult-grid.ds-diagnosis .ds-fld{margin-bottom:0}
#docstation .ds-consult-grid.ds-diagnosis label{color:var(--ds-blue)}
/* Test Requested Results (lab & radiology) — a tinted single-field panel */
#docstation .ds-consult-grid.ds-lab{grid-template-columns:1fr;background:#EFF6FF;border:1px solid #BFDBFE;border-radius:12px;padding:14px;margin-bottom:10px}
#docstation .ds-consult-grid.ds-lab .ds-fld{margin-bottom:0}
#docstation .ds-consult-grid.ds-lab label{color:var(--ds-blue)}
#docstation .ds-consult-grid.ds-lab textarea{background:#fff}
/* Finalize visit / discharge process — an amber "decision" panel */
#docstation .ds-consult-grid.ds-finalize{background:#FFFBEB;border:1px solid #FCD34D;border-radius:12px;padding:14px;margin-bottom:10px}
#docstation .ds-consult-grid.ds-finalize .ds-fld{margin-bottom:0}
#docstation .ds-consult-grid.ds-finalize label{color:#92400E}
/* Structured rendering of a saved labelled clinical entry */
#docstation .ds-note-sec{display:flex;flex-direction:column;gap:2px;margin:0 0 8px;padding-bottom:6px;border-bottom:1px dashed #D9E7F8}
#docstation .ds-note-sec:last-child{border-bottom:none;margin-bottom:0}
#docstation .ds-note-sec .k{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#1D4ED8}
#docstation .ds-note-sec .v{color:#334155;font-size:12px;white-space:pre-wrap;line-height:1.5}
/* Diagnosis block inside a saved entry (provisional / confirmed / status / discharge) */
#docstation .ds-note-diag{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;background:#fff;border:1px solid var(--ds-line);border-radius:8px;padding:9px;margin-top:8px}
#docstation .ds-note-diag .k{display:block;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#94A3B8;margin-bottom:2px}
#docstation .ds-note-diag .k.blue{color:var(--ds-blue)}
#docstation .ds-note-diag .v{font-weight:700;color:#1E293B;font-size:12px}
#docstation .ds-status-badge{display:inline-block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;padding:2px 9px;border-radius:6px;border:1px solid}
#docstation .ds-status-badge.new{background:#ECFDF5;color:#047857;border-color:#6EE7B7}
#docstation .ds-status-badge.old{background:#EFF6FF;color:#1D4ED8;border-color:#BFDBFE}
#docstation .ds-status-badge.recurring{background:#FFFBEB;color:#B45309;border-color:#FCD34D}
#docstation .ds-status-badge.cured{background:#F0FDFA;color:#0F766E;border-color:#5EEAD4}
/* Discharge process badge shown beside the diagnosis status in a saved entry */
#docstation .ds-discharge-badge{display:inline-block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;padding:2px 9px;border-radius:6px;border:1px solid}
#docstation .ds-discharge-badge.continue{background:#EFF6FF;color:#1D4ED8;border-color:#BFDBFE}
#docstation .ds-discharge-badge.ready{background:#FFFBEB;color:#B45309;border-color:#FCD34D}
#docstation .ds-discharge-badge.fit{background:#ECFDF5;color:#047857;border-color:#6EE7B7}
#docstation .ds-discharge-badge.referred{background:#FEF2F2;color:#B91C1C;border-color:#FCA5A5}
/* Clinical record header: collapsible title + sort / collapse tools */
#docstation .ds-rec-head{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;border-bottom:1px solid var(--ds-line);padding-bottom:6px;margin-bottom:10px}
#docstation .ds-rec-title{display:flex;align-items:center;gap:7px;cursor:pointer;user-select:none}
#docstation .ds-rec-title i{color:var(--ds-blue);font-size:13px}
#docstation .ds-rec-title h4{margin:0;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--ds-blue);display:flex;align-items:center;gap:8px}
#docstation .ds-rec-count{background:#F1F5F9;color:#475569;font-size:10px;font-weight:800;padding:1px 8px;border-radius:999px}
#docstation .ds-rec-hint{font-size:10px;font-weight:700;color:#94A3B8;text-transform:none;letter-spacing:0}
#docstation .ds-rec-tools{display:flex;align-items:center;gap:6px}
#docstation .ds-rec-sort{background:#F1F5F9;border:1px solid var(--ds-line);color:#334155;border-radius:8px;padding:5px 10px;font-size:10.5px;font-weight:800;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:5px}
#docstation .ds-rec-sort:hover{background:#E2E8F0}
#docstation .ds-rec-sort i{color:var(--ds-blue)}
#docstation .ds-rec-sort.asc i{color:#94A3B8}
#docstation .ds-rec-chev{background:transparent;border:none;color:#94A3B8;cursor:pointer;font-size:13px;padding:3px 5px;line-height:1}
#docstation .ds-rec-chev:hover{color:#475569}
#docstation .ds-rec-list.hidden{display:none}
/* ---- INVESTIGATION MODALITY CARDS (Request Test) ----
   Plain-CSS port of the "Investigations & Diagnostics" workspace: a master
   Show All / Hide All bar plus one collapsible card per modality. The project
   ships no Alpine.js/Tailwind, so the reveal toggles are a `.collapsed` class
   driven by plain JS (dsToggleModCard / dsShowAllMods). */
#docstation .ds-inv-bar{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:8px;background:#fff;border:1px solid var(--ds-line);border-radius:10px;padding:10px 12px;margin-bottom:10px}
#docstation .ds-inv-bar .bar-title{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#0F2D59}
#docstation .ds-inv-bar .bar-title i{color:var(--ds-blue);font-size:14px}
#docstation .ds-inv-bar .bar-btns{display:flex;flex-wrap:wrap;gap:8px}
#docstation .ds-inv-bar-btn{background:#F1F5F9;border:1px solid #E2E8F0;color:#334155;border-radius:8px;padding:7px 12px;font-size:11px;font-weight:800;font-family:inherit;cursor:pointer;display:inline-flex;align-items:center;gap:6px}
#docstation .ds-inv-bar-btn:hover{background:#E2E8F0}
#docstation .ds-inv-bar-btn i{color:var(--ds-blue)}
#docstation .ds-inv-bar-btn.hide i{color:var(--ds-red)}
#docstation .ds-inv-card{background:#fff;border:1px solid var(--ds-line);border-radius:12px;overflow:hidden;transition:border-color .15s ease}
#docstation .ds-inv-card + .ds-inv-card{margin-top:10px}
#docstation .ds-inv-card.on{border-color:var(--ds-blue)}
#docstation .ds-inv-card-head{display:flex;justify-content:space-between;align-items:center;gap:10px;background:#F8FAFC;border-bottom:1px solid var(--ds-line);padding:9px 12px;cursor:pointer;user-select:none}
#docstation .ds-inv-card.collapsed .ds-inv-card-head{border-bottom-color:transparent}
#docstation .ds-inv-card-head .left{display:flex;align-items:center;gap:8px;min-width:0}
#docstation .ds-inv-card-head .left>i{color:var(--ds-blue);font-size:13px}
#docstation .ds-inv-card-head h5{margin:0;font-size:11.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#1E293B}
#docstation .ds-inv-card-head .mod-check{accent-color:var(--ds-blue);width:15px;height:15px;cursor:pointer;flex:0 0 auto}
#docstation .ds-inv-card-head .mod-check:disabled{opacity:.4;cursor:not-allowed}
#docstation .ds-inv-card-head .mod-cnt{margin-left:2px;font-size:9px;font-weight:800;background:#E2E8F0;color:#475569;border-radius:999px;padding:1px 8px;min-width:22px;text-align:center}
#docstation .ds-inv-card-head.on .mod-cnt{background:var(--ds-blue);color:#fff}
#docstation .ds-inv-card-head .right{display:flex;align-items:center;gap:8px}
#docstation .ds-inv-card-head .vis-badge{font-size:9.5px;font-weight:800;padding:2px 8px;border-radius:6px;background:#EFF6FF;color:var(--ds-blue);border:1px solid #BFDBFE;text-transform:uppercase;letter-spacing:.3px}
#docstation .ds-inv-card.collapsed .ds-inv-card-head .vis-badge{background:#F1F5F9;color:#64748B;border-color:#CBD5E1}
#docstation .ds-inv-card-head .chev{color:#94A3B8;font-size:12px;transition:transform .15s ease}
#docstation .ds-inv-card.collapsed .ds-inv-card-head .chev{transform:rotate(180deg)}
#docstation .ds-inv-card-body{padding:12px}
#docstation .ds-inv-card.collapsed .ds-inv-card-body{display:none}
#docstation .ds-other-spec{display:flex;gap:8px;margin-top:10px;align-items:center}
#docstation .ds-other-spec .ds-input{flex:1}
</style>

<div id="docstation" class="ds-wrap">

  <!-- 1. TOP WARD / PATIENT SEARCH FILTER PANEL -->
  <div class="ds-filter">
    <div class="ds-grid2">
      <div class="ds-field">
        <label class="ds-label" for="dsWardType">Type Of Ward</label>
        <select class="ds-select" id="dsWardType">
          <option value="By Ward" selected>By Ward</option>
        </select>
      </div>
      <div class="ds-field">
        <label class="ds-label" for="dsWard">Wards</label>
        <select class="ds-select" id="dsWard">
          <option value="">-- All Wards --</option>
        </select>
      </div>
    </div>

    <div class="ds-field" style="margin-top:12px;">
      <label class="ds-label" for="dsSearch">Search Patient (IPD / Wards)</label>
      <input type="text" class="ds-input" id="dsSearch" placeholder="Search by name, hospital number or bed — e.g. HMS202683952 or BED-MED-01">
    </div>

    <div class="ds-actions-row">
      <button type="button" class="ds-btn light" id="dsClearBtn">Clear</button>
      <button type="button" class="ds-btn primary" id="dsFindBtn">Find Patient</button>
    </div>
  </div>

  <!-- 2. WARD SUMMARY BAR -->
  <div class="ds-ward-summary">
    Ward Details &bull; Total Patient(s): <strong id="dsTotalPatients">0</strong>
    &nbsp;|&nbsp; Partial Discharged Patient(s): <span class="muted" id="dsPartialDischarged">0</span>
  </div>

  <!-- 3. PATIENT CARDS -->
  <div id="dsCards">
    <div class="ds-card"><div class="ds-card-pad" style="text-align:center;color:#64748B;font-size:13px;">Loading admitted patients…</div></div>
  </div>
</div>

<!-- MEDICAL RECORDS MODAL -->
<div id="docstation-modal">
  <div class="dm-box">
    <div class="dm-head">
      <h3><i class="fa-solid fa-file-medical"></i> Medical Records</h3>
      <button type="button" class="x" onclick="dsCloseModal()">&times;</button>
    </div>
    <div class="dm-body" id="dsModalBody"></div>
    <div class="dm-foot"><button type="button" class="ds-btn light" onclick="dsCloseModal()">Close</button></div>
  </div>
</div>

<script>
var DS_STAFF = <?php echo json_encode($__clinicStaff); ?>;

var dsWards = [];
var dsInpatients = [];        // rows from clinical_notes.php?action=inpatients
var dsContext = {};           // pid -> { summary, latestVisit, vitals, admission }
var dsNotesCache = {};        // pid -> clinical notes
var dsMedsCache = {};         // pid -> prescriptions
var dsInvCache = {};          // pid -> investigations rows
var dsConsultCache = {};      // pid -> consultation_form context (lazy)
var dsRx = {};                // pid -> [drug lines]
var dsDrugs = {};             // pid -> [stocked drugs]
var dsNotesUi = {};           // pid -> { open: bool, desc: bool } clinical-record view state
var dsSearchTimer = null;

var DS_NOTE_LABELS = {
    OPD_NOTE: 'OPD Consultation Entry',
    IPD_NOTE: 'IPD Daily Clinical Round Note',
    PROGRESS_NOTE: 'Progress Report Note',
    DISCHARGE_SUMMARY: 'Discharge Summary & Discharge Process'
};

/* Human labels shown in the entry-type select (same keys as DS_NOTE_LABELS). */
var DS_ENTRY_LABELS = {
    OPD_NOTE: 'OPD Consultation Entry',
    IPD_NOTE: 'IPD Daily Clinical Round Note',
    PROGRESS_NOTE: 'Progress Report Note',
    DISCHARGE_SUMMARY: 'Discharge Summary & Discharge Process'
};

/* The structured consultation fields on the expanded Doctor Notes form. Each
   entry maps a form field id (dsNote_<pid>_<key>) to the uppercase section
   label used inside the saved clinical note, so the record can be re-parsed
   when a doctor taps "Refill Previous Visit Entries". */
var DS_NOTE_FIELDS = [
    { key: 'principal_complaints', label: 'PRINCIPAL COMPLAINTS',             plain: 'Principal Complaints' },
    { key: 'on_direct_question',   label: 'ON DIRECT QUESTION',               plain: 'On Direct Question' },
    { key: 'previous_history',     label: 'PREVIOUS HISTORY / MEDICAL HISTORY', plain: 'Previous History / Medical History' },
    { key: 'allergies',            label: 'ALLERGIES / DRUG REACTIONS',       plain: 'Allergies / Drug Reactions' },
    { key: 'examinations',         label: 'EXAMINATIONS (PHYSICAL AND SYSTEMIC)', plain: 'Examinations (Physical & Systemic)' },
    { key: 'progress_notes',       label: 'PROGRESS REPORT NOTES',            plain: 'Progress Report Notes' },
    { key: 'test_requested_results', label: 'TEST REQUESTED RESULTS (LAB AND RADIOLOGY)', plain: 'Test Requested Results (Lab & Radiology)' },
    { key: 'provisional_diagnosis', label: 'PROVISIONAL DIAGNOSIS',           plain: 'Provisional Diagnosis', control: 'input' },
    { key: 'confirmed_diagnosis',   label: 'CONFIRMED DIAGNOSIS',             plain: 'Confirmed Diagnosis',   control: 'input' },
    { key: 'diagnosis_status',      label: 'DIAGNOSIS STATUS',                plain: 'Diagnosis Status',      control: 'select', defaultValue: 'NEW',
      options: [
          { value: 'NEW',       label: 'NEW' },
          { value: 'OLD',       label: 'OLD' },
          { value: 'RECURRING', label: 'RECURRING' },
          { value: 'CURED',     label: 'CURED' }
      ] },
    { key: 'finalize_visit',        label: 'FINALIZE VISIT INSTRUCTIONS',     plain: 'Finalize Visit Instructions', control: 'input' },
    { key: 'discharge_process',     label: 'DISCHARGE PROCESS / STATUS',      plain: 'Discharge Process / Status', control: 'select', defaultValue: 'CONTINUE_ADMISSION',
      options: [
          { value: 'CONTINUE_ADMISSION',  label: 'Continue Admission (Active Stay)' },
          { value: 'READY_FOR_DISCHARGE', label: 'Ready for Discharge (Finalize Bill)' },
          { value: 'DISCHARGED_FIT',      label: 'Discharged - Fit for Home' },
          { value: 'REFERRED_OUT',        label: 'Referred Out to Tertiary Facility' }
      ] }
];

/* The diagnosis fields are grouped into their own panel when a saved entry is
   rendered, with the status shown as a coloured badge. */
var DS_DIAG_KEYS = ['provisional_diagnosis', 'confirmed_diagnosis', 'diagnosis_status'];
var DS_STATUS_CLASS = { NEW: 'new', OLD: 'old', RECURRING: 'recurring', CURED: 'cured' };

/* The discharge process shares the diagnosis panel in a saved entry, rendered
   as a colour-coded badge beside the diagnosis status. */
var DS_DISCHARGE_KEY = 'discharge_process';
var DS_DISCHARGE_LABELS = {
    CONTINUE_ADMISSION:  'Continue Admission (Active Stay)',
    READY_FOR_DISCHARGE: 'Ready for Discharge (Finalize Bill)',
    DISCHARGED_FIT:      'Discharged - Fit for Home',
    REFERRED_OUT:        'Referred Out to Tertiary Facility'
};
var DS_DISCHARGE_CLASS = {
    CONTINUE_ADMISSION: 'continue', READY_FOR_DISCHARGE: 'ready',
    DISCHARGED_FIT: 'fit', REFERRED_OUT: 'referred'
};

/* Modality tick-box row on the Request Test block. `kind` matches the kind tag
   on the investigation catalogue, so the master boxes and the grouped checklists
   stay in lock-step. */
var DS_INV_MODALITIES = [
    { kind: 'lab',        label: 'Lab',                     groupLabel: 'Laboratory Tests',        icon: 'fa-flask-vial', tag: 'LAB' },
    { kind: 'radiology',  label: 'Radiology',               groupLabel: 'Radiology / X-Ray',       icon: 'fa-x-ray',      tag: 'IMAGING' },
    { kind: 'ultrasound', label: 'Ultrasound / Sonography', groupLabel: 'Ultrasound / Sonography', icon: 'fa-wave-square', tag: 'SONO' },
    { kind: 'other',      label: 'Other',                   groupLabel: 'Other / Specialist',      icon: 'fa-stethoscope', tag: 'OTHER' }
];

function initDoctorStation() {
    var findBtn = document.getElementById('dsFindBtn');
    var clearBtn = document.getElementById('dsClearBtn');
    var wardSel = document.getElementById('dsWard');
    var search = document.getElementById('dsSearch');

    if (findBtn) findBtn.addEventListener('click', loadDoctorStation);
    if (clearBtn) clearBtn.addEventListener('click', function () {
        document.getElementById('dsWard').value = '';
        document.getElementById('dsSearch').value = '';
        loadDoctorStation();
    });
    if (wardSel) wardSel.addEventListener('change', loadDoctorStation);
    if (search) {
        search.addEventListener('input', function () {
            clearTimeout(dsSearchTimer);
            dsSearchTimer = setTimeout(loadDoctorStation, 300);
        });
        search.addEventListener('keydown', function (e) { if (e.key === 'Enter') loadDoctorStation(); });
    }

    loadWards().then(loadDoctorStation).then(function () {
        // Cross-station handoff: "Route to Doctor" from the patient folder
        // banner lands here; open the folder for that patient.
        var pfCtx = (typeof window.hmsConsumePatientContext === 'function') ? window.hmsConsumePatientContext() : null;
        if (pfCtx && pfCtx.patientId) dsOpenFolder(pfCtx.patientId);
    });
}

/* ============================ WARDS ============================ */
async function loadWards() {
    try {
        var res = await fetch('/hms/backend/api/wards.php');
        var data = await res.json();
        dsWards = (data.success && data.wards) ? data.wards : [];
        var sel = document.getElementById('dsWard');
        if (!sel) return;
        sel.innerHTML = '<option value="">-- All Wards --</option>' +
            dsWards.map(function (w) { return '<option value="' + w.id + '">' + escHtml(w.ward_name) + '</option>'; }).join('');
    } catch (e) {
        console.error('Wards load error:', e);
    }
}

function dsWardName(wardId) {
    var w = dsWards.filter(function (x) { return String(x.id) === String(wardId); })[0];
    return w ? w.ward_name : '';
}

/* ============================ IN-PATIENT LIST ============================ */
async function loadDoctorStation() {
    var container = document.getElementById('dsCards');
    if (!container) return;
    container.innerHTML = '<div class="ds-card"><div class="ds-card-pad" style="text-align:center;color:#64748B;font-size:13px;">Searching wards… please wait.</div></div>';

    var wardId = document.getElementById('dsWard').value;
    var q = document.getElementById('dsSearch').value.trim();

    var params = new URLSearchParams();
    params.set('action', 'inpatients');
    if (q) params.set('q', q);

    try {
        var res = await fetch('/hms/backend/api/clinical_notes.php?' + params.toString());
        var data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to load admitted patients');
        dsInpatients = data.inpatients || [];
    } catch (e) {
        console.error('Doctor station load error:', e);
        container.innerHTML = '<div class="ds-card"><div class="ds-card-pad" style="text-align:center;color:#C0392B;font-size:13px;">Failed to load admitted patients. Please try again.</div></div>';
        dsUpdateCounters(0, 0);
        return;
    }

    // Ward filter is applied client-side: the in-patient feed already carries ward_name.
    if (wardId) {
        var wanted = dsWardName(wardId);
        dsInpatients = dsInpatients.filter(function (p) { return String(p.ward_name) === String(wanted); });
    }

    await renderCards();
}

async function renderCards() {
    var container = document.getElementById('dsCards');
    dsContext = {}; dsNotesCache = {}; dsMedsCache = {}; dsInvCache = {}; dsConsultCache = {}; dsRx = {}; dsDrugs = {}; dsNotesUi = {};

    if (!dsInpatients.length) {
        container.innerHTML = '<div class="ds-card"><div class="ds-card-pad" style="text-align:center;color:#64748B;font-size:13px;">No patients currently admitted in the selected ward.</div></div>';
        dsUpdateCounters(0, 0);
        return;
    }

    var html = [];
    var ctxList = [];
    for (var i = 0; i < dsInpatients.length; i++) {
        var row = dsInpatients[i];
        var ctx = await buildPatientContext(row);
        dsContext[row.patient_id] = ctx;
        ctxList.push(ctx);
        html.push(renderPatientCard(ctx));
    }
    container.innerHTML = html.join('');

    var total = ctxList.length;
    var partial = ctxList.filter(function (c) { return Number(c.row.summary_count) > 0; }).length;
    dsUpdateCounters(total, partial);

    ctxList.forEach(fillClinicalData);
}

async function buildPatientContext(row) {
    var pid = row.patient_id;
    var summary = null;
    var visits = [];
    var vitalsList = [];

    try {
        var r = await fetch('/hms/backend/api/patients.php?action=summary&id=' + pid);
        var d = await r.json();
        if (d.success) summary = d.patient;
    } catch (e) { console.error('Patient summary error:', e); }

    try {
        var r2 = await fetch('/hms/backend/api/visits.php?patient_id=' + pid);
        var d2 = await r2.json();
        if (d2.success) visits = d2.visits || [];
    } catch (e) { console.error('Patient visits error:', e); }

    var latestVisit = visits[0] || null;
    if (latestVisit) {
        try {
            var r3 = await fetch('/hms/backend/api/vitals.php?visit_id=' + latestVisit.id);
            var d3 = await r3.json();
            if (d3.success) vitalsList = d3.vitals || [];
        } catch (e) { console.error('Patient vitals error:', e); }
    }

    return { row: row, summary: summary, latestVisit: latestVisit, vitals: vitalsList };
}

function dsUpdateCounters(total, partial) {
    setDsText('dsTotalPatients', total);
    setDsText('dsPartialDischarged', partial);
}

function setDsText(id, value) {
    var el = document.getElementById(id);
    if (el) el.textContent = value;
}

/* ============================ CARD RENDERING ============================ */
function initialsOf(name) {
    var parts = String(name || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '?';
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function plausibleVital(v, field) {
    if (!v) return null;
    var val = v[field];
    if (val === null || val === undefined || val === '') return null;
    val = Number(val);
    if (isNaN(val)) return null;
    var bounds = { temperature: [30, 45], heart_rate: [20, 260], oxygen_saturation: [40, 100],
                   blood_pressure_systolic: [40, 320], blood_pressure_diastolic: [20, 200] };
    var b = bounds[field];
    if (b && (val < b[0] || val > b[1])) return null;
    return val;
}

function latestVitalTiles(readings) {
    var v = (readings && readings.length) ? readings[0] : null;
    var sys = plausibleVital(v, 'blood_pressure_systolic');
    var dia = plausibleVital(v, 'blood_pressure_diastolic');
    var bp = (sys !== null && dia !== null && dia < sys) ? sys + '/' + dia : '';
    var pulse = plausibleVital(v, 'heart_rate');
    var temp = plausibleVital(v, 'temperature');
    var spo2 = plausibleVital(v, 'oxygen_saturation');

    function tile(k, text) {
        return '<div class="ds-tile' + (text ? '' : ' none') + '"><span class="k">' + escHtml(k) + '</span>'
            + '<span class="v">' + escHtml(text || 'Not recorded') + '</span></div>';
    }
    return '<div class="ds-tiles">'
        + tile('Blood Pressure', bp)
        + tile('Pulse Rate', pulse !== null ? pulse + ' bpm' : '')
        + tile('Temperature', temp !== null ? temp + ' °C' : '')
        + tile('SpO2', spo2 !== null ? spo2 + '%' : '')
        + '</div>';
}

function dischargeBadgeHtml(adm) {
    if (!adm) return '<span class="ds-admchip">No Admission Record</span>';
    var outcome = String(adm.discharge_outcome || '').trim();
    var status = String(adm.status || '').trim();
    var isDischarged = status.toLowerCase() === 'discharged' || !!outcome;
    var cls = '', label = 'Active Admission';
    if (isDischarged) {
        var o = outcome.toLowerCase();
        if (/die|death|deceased|expire/.test(o)) { cls = 'died'; label = outcome || 'Died'; }
        else if (/abscond|absent|escape|left against/.test(o)) { cls = 'absconded'; label = outcome || 'Absconded'; }
        else { cls = 'discharged'; label = outcome || 'Discharged'; }
    }
    return '<span class="ds-admchip ' + cls + '">' + escHtml(label) + '</span>';
}

/* ACTIVE / INACTIVE patient status badge shown next to the ID chip. A live
   admission (or a patient with no admission record yet) reads ACTIVE, with a
   pulsing green dot; anything discharged/inactive reads INACTIVE. */
function dsStatusChip(adm, isActive) {
    var status = String((adm && adm.status) || '').toLowerCase();
    var active = (isActive === undefined || isActive === 1)
        && (status === 'admitted' || status === '' || !adm);
    return active
        ? '<span class="ds-status-chip on"><span class="dot"></span>Patient Status: Active</span>'
        : '<span class="ds-status-chip off"><span class="dot"></span>Patient Status: Inactive</span>';
}

function renderPatientCard(ctx) {
    var row = ctx.row;
    var s = ctx.summary || {};
    var pid = row.patient_id;
    var readings = ctx.vitals || [];
    var adm = s.admission || { status: 'Admitted' };

    var demoLine = escHtml(s.gender || row.gender || '--') + ' | '
        + ((s.age != null ? s.age : row.age) != null ? escHtml(s.age != null ? s.age : row.age) + ' Yrs' : '--')
        + ' | Genotype: ' + escHtml(s.genotype || '--') + ' | Blood Group: ' + escHtml(s.blood_group || '--')
        + (s.nhia_number ? ' | NHIS No: ' + escHtml(s.nhia_number) : '');

    var allergy = (s.allergies && String(s.allergies).trim())
        ? '<span class="ds-allergy"><i class="fa-solid fa-triangle-exclamation"></i> Allergy: ' + escHtml(s.allergies) + '</span>'
        : '';

    return ''
    + '<div class="ds-card" data-ds-card="' + pid + '">'
      + '<div class="ds-card-pad">'

        // Ward + quick actions bar
        + '<div class="ds-wardbar">'
            + '<div class="left"><i class="fa-solid fa-bed"></i>'
                + '<h3>' + escHtml(row.ward_name || 'Ward') + '</h3>'
                + '<span class="ds-bedchip">Bed ' + escHtml(row.bed_number || '--') + '</span></div>'
            + '<div class="right">'
                + '<button type="button" class="ds-btn outline sm" onclick="dsTransfer()"><i class="fa-solid fa-right-left"></i> Change Bed / Transfer Patient</button>'
                + '<button type="button" class="ds-btn outline sm" onclick="dsOpenMedicalRecords(' + pid + ')"><i class="fa-solid fa-file-medical"></i> Medical Records</button>'
                + '<button type="button" class="ds-btn outline sm" onclick="dsOpenFolder(' + pid + ')"><i class="fa-solid fa-folder-open"></i> Patient Folder</button>'
            + '</div>'
        + '</div>'

        // Red banner
        + '<div class="ds-banner">'
            + '<div><h3><i class="fa-regular fa-star"></i> Station Management — Doctor Clinical Workflow</h3>'
                + '<div class="sub">Clinical entries, round notes, consultations and discharge summaries for this patient.</div></div>'
            + '<div class="hn"><i class="fa-solid fa-hospital-user"></i> ' + escHtml(row.hospital_number || '--') + '</div>'
        + '</div>'

        // Patient details card
        + '<div class="ds-patient">'
            + '<div class="who">'
                + '<div class="ds-avatar"><i class="fa-solid fa-user"></i></div>'
                + '<div>'
                    + '<div class="nm"><h4>' + escHtml(row.patient_name || 'PATIENT') + '</h4>'
                        + '<span class="ds-idchip">ID: ' + escHtml(row.hospital_number || '--') + '</span>'
                        + dsStatusChip(adm, s.is_active) + dischargeBadgeHtml(adm) + '</div>'
                    + '<div class="demo">' + demoLine + '</div>'
                    + '<a class="ds-link" onclick="dsGoAdmissions()">Discharge from Admissions</a>'
                + '</div>'
            + '</div>'
            + '<div class="ds-patient-right">'
                + allergy
                + '<button type="button" class="ds-btn outline sm" onclick="dsRefillPrevious(' + pid + ')"><i class="fa-solid fa-rotate-left"></i> Refill Previous Visit Entries</button>'
            + '</div>'
        + '</div>'

        // Latest vitals
        + '<div class="ds-vitals">'
            + '<div class="head"><span class="ttl"><i class="fa-solid fa-heart-pulse"></i> Latest Vitals</span>'
                + '<span>Most recent reading</span></div>'
            + latestVitalTiles(readings)
        + '</div>'

        // Clinical workspace
        + '<div class="ds-workspace" id="dsWorkspace_' + pid + '">'
            + '<div class="ds-tabs" role="tablist" aria-label="Clinical sections">'
                + dsTab(pid, 'doctor_notes', 'fa-user-doctor', 'Doctor Notes &amp; Clinical Entries', true)
                + dsTab(pid, 'vitals', 'fa-heart-pulse', 'Vital Signs Readings', false)
                + dsTab(pid, 'prescriptions', 'fa-pills', 'Prescribed Medication', false)
                + dsTab(pid, 'nurse_notes', 'fa-notes-medical', 'Nurses / Midwives Notes', false)
                + dsTab(pid, 'treatment', 'fa-file-prescription', 'Treatment Sheet', false)
                + dsTab(pid, 'investigations', 'fa-flask-vial', 'Investigations Requested', false)
            + '</div>'

            // Panel 1 — Doctor Notes & Clinical Entries (WRITE)
            + '<div class="ds-panel active" id="dsPanel_' + pid + '_doctor_notes" data-ds-panel="doctor_notes" role="tabpanel">'
                + '<div class="ds-sec">'
                    + '<div class="ds-sec-head"><h4><i class="fa-solid fa-pen-to-square"></i> Doctor Clinical Round Notes &amp; Discharge Summary</h4>'
                        + '<span class="hint">Saved against this stay under your name</span></div>'
                    + '<div class="ds-fld">'
                        + '<label for="dsNoteType_' + pid + '">ENTRY TYPE *</label>'
                        + '<select id="dsNoteType_' + pid + '">'
                            + '<option value="IPD_NOTE" selected>IPD Daily Clinical Round Note</option>'
                            + '<option value="OPD_NOTE">OPD Consultation Entry</option>'
                            + '<option value="PROGRESS_NOTE">Progress Report Note</option>'
                            + '<option value="DISCHARGE_SUMMARY">Discharge Summary &amp; Discharge Process</option>'
                        + '</select></div>'
                    + '<div class="ds-consult-grid">'
                        + '<div>' + dsNoteFieldHtml(pid, 'principal_complaints', {
                            required: true,
                            placeholder: 'Record the patient\u2019s primary symptoms, duration and main presentation...'
                        }) + '</div>'
                        + '<div>' + dsNoteFieldHtml(pid, 'on_direct_question', {
                            placeholder: 'Document targeted questions asked, e.g. fever, chills, cough, chest pain, nausea...'
                        }) + '</div>'
                    + '</div>'
                    + '<div class="ds-consult-grid">'
                        + '<div>' + dsNoteFieldHtml(pid, 'previous_history', {
                            placeholder: 'Past medical, surgical, chronic illnesses or family history...'
                        }) + '</div>'
                        + '<div>' + dsNoteFieldHtml(pid, 'allergies', {
                            warn: true,
                            value: s.allergies || '',
                            placeholder: 'e.g. Penicillin, Sulfa drugs, NSAIDs, food or environmental allergies...'
                        }) + '</div>'
                    + '</div>'
                    + '<div class="ds-consult-grid">'
                        + '<div>' + dsNoteFieldHtml(pid, 'examinations', {
                            required: true,
                            placeholder: 'General, cardiovascular, respiratory, abdominal examination findings...'
                        }) + '</div>'
                        + '<div>' + dsNoteFieldHtml(pid, 'progress_notes', {
                            required: true,
                            placeholder: 'Enter clinical progression, response to treatment, round observations...'
                        }) + '</div>'
                    + '</div>'
                    + '<div class="ds-consult-grid ds-lab">'
                        + '<div>' + dsNoteFieldHtml(pid, 'test_requested_results', {
                            placeholder: 'Summary of lab / x-ray test results reviewed during consultation...'
                        }) + '</div>'
                    + '</div>'
                    + '<div class="ds-consult-grid ds-diagnosis">'
                        + '<div>' + dsNoteFieldHtml(pid, 'provisional_diagnosis', {
                            placeholder: 'e.g. Suspected Malaria / Acute Febrile Illness'
                        }) + '</div>'
                        + '<div>' + dsNoteFieldHtml(pid, 'confirmed_diagnosis', {
                            placeholder: 'e.g. Plasmodium Falciparum Malaria'
                        }) + '</div>'
                        + '<div>' + dsNoteFieldHtml(pid, 'diagnosis_status', {
                            required: true
                        }) + '</div>'
                    + '</div>'
                    + '<div class="ds-consult-grid ds-finalize">'
                        + '<div>' + dsNoteFieldHtml(pid, 'finalize_visit', {
                            placeholder: 'e.g. Outpatient review in 1 week / Routine follow-up scheduled'
                        }) + '</div>'
                        + '<div>' + dsNoteFieldHtml(pid, 'discharge_process', {}) + '</div>'
                    + '</div>'
                    + '<div style="text-align:right;"><button type="button" class="ds-btn primary" onclick="dsSaveDoctorEntry(' + pid + ')"><i class="fa-solid fa-floppy-disk"></i> Save Doctor Entry</button></div>'
                    + '<div style="border-top:1px dashed var(--ds-line);margin:14px 0 12px;"></div>'
                    + '<div class="ds-rec-head">'
                        + '<div class="ds-rec-title" onclick="dsToggleNotes(' + pid + ')">'
                            + '<i class="fa-solid fa-clock-rotate-left"></i>'
                            + '<h4><span>Clinical Record For This Stay</span>'
                                + '<span class="ds-rec-count" id="dsNotesCount_' + pid + '">0</span>'
                                + '<span class="ds-rec-hint" id="dsNotesHint_' + pid + '"></span></h4>'
                        + '</div>'
                        + '<div class="ds-rec-tools">'
                            + '<button type="button" class="ds-rec-sort" id="dsNotesSort_' + pid + '" onclick="dsToggleNotesSort(' + pid + ')">'
                                + '<i class="fa-solid fa-arrow-down-9-1"></i><span>Sorted: Newest First (DESC)</span></button>'
                            + '<button type="button" class="ds-rec-chev" id="dsNotesChev_' + pid + '" onclick="dsToggleNotes(' + pid + ')" aria-label="Collapse clinical record">'
                                + '<i class="fa-solid fa-chevron-up"></i></button>'
                        + '</div>'
                    + '</div>'
                    + '<div id="dsNotesList_' + pid + '" class="ds-rec-list"><div class="ds-empty">Loading clinical record…</div></div>'
                + '</div>'
            + '</div>'

            // Panel 2 — Vitals (READ)
            + '<div class="ds-panel" id="dsPanel_' + pid + '_vitals" data-ds-panel="vitals" role="tabpanel">'
                + '<div class="ds-sec">'
                    + '<div class="ds-sec-head"><h4><i class="fa-solid fa-heart-pulse"></i> Vital Signs Readings Log</h4>'
                        + '<span class="hint">Recorded by nursing</span></div>'
                    + '<div class="ds-table-wrap"><table class="ds-table"><thead><tr>'
                        + '<th>Date / Time</th><th>Temp (°C)</th><th>BP (mmHg)</th><th>HR (bpm)</th><th>RR (bpm)</th><th>SpO2 (%)</th><th>Weight (kg)</th><th>Nurse</th>'
                        + '</tr></thead><tbody id="dsVitalsBody_' + pid + '">' + dsVitalsRows(readings) + '</tbody></table></div>'
                + '</div>'
            + '</div>'

            // Panel 3 — Prescriptions (READ + WRITE)
            + '<div class="ds-panel" id="dsPanel_' + pid + '_prescriptions" data-ds-panel="prescriptions" role="tabpanel">'
                + '<div class="ds-sec">'
                    + '<div class="ds-sec-head"><h4><i class="fa-solid fa-pills"></i> Prescribed Medication</h4>'
                        + '<span class="hint">Only stocked drugs can be ordered</span></div>'
                    + '<div class="ds-table-wrap" style="margin-bottom:14px;"><table class="ds-table"><thead><tr>'
                        + '<th>Drug Name &amp; Strength</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Status</th>'
                        + '</tr></thead><tbody id="dsMedsBody_' + pid + '"><tr><td colspan="5" class="ds-empty">Loading prescriptions…</td></tr></tbody></table></div>'
                    + '<div class="ds-fld"><label for="dsDrugSearch_' + pid + '">+ Prescribe Drug</label>'
                        + '<div class="ds-rx-add">'
                            + '<input type="text" id="dsDrugSearch_' + pid + '" list="dsDrugList_' + pid + '" placeholder="Search stocked drugs by name or code…" autocomplete="off">'
                            + '<datalist id="dsDrugList_' + pid + '"></datalist>'
                            + '<button type="button" class="ds-btn primary sm" onclick="dsAddDrug(' + pid + ')"><i class="fa-solid fa-plus"></i> Add</button>'
                        + '</div>'
                        + '<div class="ds-rx-hint">Each drug added here creates a prescription the pharmacy can dispense.</div>'
                    + '</div>'
                    + '<div class="ds-rx-lines" id="dsRxLines_' + pid + '"></div>'
                    + '<div style="text-align:right;margin-top:12px;"><button type="button" class="ds-btn green" onclick="dsSavePrescription(' + pid + ')"><i class="fa-solid fa-floppy-disk"></i> Save Prescription</button></div>'
                + '</div>'
            + '</div>'

            // Panel 4 — Nurse notes (READ)
            + '<div class="ds-panel" id="dsPanel_' + pid + '_nurse_notes" data-ds-panel="nurse_notes" role="tabpanel">'
                + '<div class="ds-sec">'
                    + '<div class="ds-sec-head"><h4><i class="fa-solid fa-notes-medical"></i> Nurses / Midwives Shift Notes</h4>'
                        + '<span class="hint">Recorded at the Nurses Station</span></div>'
                    + '<div id="dsNurseList_' + pid + '"></div>'
                + '</div>'
            + '</div>'

            // Panel 5 — Treatment sheet (READ)
            + '<div class="ds-panel" id="dsPanel_' + pid + '_treatment" data-ds-panel="treatment" role="tabpanel">'
                + '<div class="ds-sec">'
                    + '<div class="ds-sec-head"><h4><i class="fa-solid fa-file-prescription"></i> Inpatient Treatment Sheet</h4>'
                        + '<span class="hint">Recorded at the Nurses Station</span></div>'
                    + '<div class="ds-table-wrap"><table class="ds-table"><thead><tr>'
                        + '<th>Date / Time</th><th>Treatment / Procedure</th><th>Notes</th><th>Staff</th><th>Status</th>'
                        + '</tr></thead><tbody id="dsTreatBody_' + pid + '"><tr><td colspan="5" class="ds-empty">No treatments logged yet.</td></tr></tbody></table></div>'
                + '</div>'
            + '</div>'

            // Panel 6 — Investigations (READ + WRITE)
            + '<div class="ds-panel" id="dsPanel_' + pid + '_investigations" data-ds-panel="investigations" role="tabpanel">'
                + '<div class="ds-sec">'
                    + '<div class="ds-sec-head"><h4><i class="fa-solid fa-flask-vial"></i> Laboratory &amp; Radiology Investigations Requested</h4>'
                        + '<span class="hint">Ordered from the Doctor Station</span></div>'
                    + '<div class="ds-table-wrap" style="margin-bottom:14px;"><table class="ds-table"><thead><tr>'
                        + '<th>Date Requested</th><th>Investigation / Test</th><th>Category</th><th>Ordered By</th><th>Result Status</th>'
                        + '</tr></thead><tbody id="dsInvBody_' + pid + '"><tr><td colspan="5" class="ds-empty">Loading investigations…</td></tr></tbody></table></div>'
                    + '<div class="ds-fld"><label>+ Request Test — Show a modality card, then tick the tests</label>'
                        + '<div class="ds-inv-bar" id="dsModRow_' + pid + '"></div>'
                        + '<div class="ds-inv-cards" id="dsInvCat_' + pid + '"><div class="ds-empty">Loading investigation catalogue…</div></div></div>'
                    + '<div style="text-align:right;margin-top:12px;"><button type="button" class="ds-btn green" onclick="dsSaveInvestigations(' + pid + ')"><i class="fa-solid fa-floppy-disk"></i> Save Requests</button></div>'
                + '</div>'
            + '</div>'

        + '</div>' // /workspace

      + '</div>' // /card-pad

      // Footer
      + '<div class="ds-footer">'
          + '<span class="log">Logging as: <strong>' + escHtml(DS_STAFF) + '</strong> &bull; Last saved: <span id="dsLastSaved_' + pid + '">--</span>'
              + ' <span class="ds-save-state" id="dsSaved_' + pid + '"><i class="fa-solid fa-circle-check"></i> Saved</span></span>'
          + '<div class="btns">'
              + '<button type="button" class="ds-btn light" onclick="dsSaveDraft(' + pid + ')">Save To Draft</button>'
              + '<button type="button" class="ds-btn green" onclick="dsSaveOrUpdate(' + pid + ')">Save Or Update</button>'
          + '</div>'
      + '</div>'

    + '</div>';
}

function dsTab(pid, section, icon, label, active) {
    return '<button type="button" class="ds-tab' + (active ? ' active' : '') + '" data-ds-tab="' + section + '"'
        + ' role="tab" aria-selected="' + (active ? 'true' : 'false') + '"'
        + ' onclick="switchDsTab(' + pid + ', \'' + section + '\')">'
        + '<i class="fa-solid ' + icon + '"></i><span>' + label + '</span></button>';
}

function dsVitalsRows(readings) {
    if (!readings || !readings.length) {
        return '<tr><td colspan="8" class="ds-empty">No vital signs recorded yet.</td></tr>';
    }
    return readings.map(function (v) {
        var bp = (v.blood_pressure_systolic && v.blood_pressure_diastolic)
            ? v.blood_pressure_systolic + '/' + v.blood_pressure_diastolic : '--';
        return '<tr>'
            + '<td><strong>' + escHtml(fmtDateTime(v.recorded_at)) + '</strong></td>'
            + '<td>' + escHtml(v.temperature != null ? v.temperature : '--') + '</td>'
            + '<td>' + escHtml(bp) + '</td>'
            + '<td>' + escHtml(v.heart_rate != null ? v.heart_rate : '--') + '</td>'
            + '<td>' + escHtml(v.respiratory_rate != null ? v.respiratory_rate : '--') + '</td>'
            + '<td>' + escHtml(v.oxygen_saturation != null ? v.oxygen_saturation + '%' : '--') + '</td>'
            + '<td>' + escHtml(v.weight != null ? v.weight : '--') + '</td>'
            + '<td>' + escHtml(v.nurse_name || '--') + '</td>'
            + '</tr>';
    }).join('');
}

/* ============================ TAB SWITCH ============================ */
function switchDsTab(pid, section) {
    var ws = document.getElementById('dsWorkspace_' + pid);
    if (!ws) return;
    ws.querySelectorAll('.ds-tab').forEach(function (btn) {
        var on = btn.getAttribute('data-ds-tab') === section;
        btn.classList.toggle('active', on);
        btn.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    ws.querySelectorAll('.ds-panel').forEach(function (panel) {
        panel.classList.toggle('active', panel.getAttribute('data-ds-panel') === section);
    });
    // Lazy-load the write context the first time a doctor-write tab is opened.
    if (section === 'prescriptions' || section === 'investigations') ensureConsultContext(pid);
}

/* ============================ FILL CLINICAL DATA ============================ */
function fillClinicalData(ctx) {
    var pid = ctx.row.patient_id;
    loadClinicalNotes(pid);
    if (ctx.latestVisit) loadPrescriptions(ctx.latestVisit.id, pid);
    else renderMedications(pid);
    loadInvestigations(pid, ctx.row.hospital_number);
    renderNurseNotes(pid);
    renderTreatments(pid);
    renderSavedStamp(pid);
}

/* ---------- Expanded consultation form (snippet-driven, plain JS) ---------- */

// Field lookup by key.
function dsNoteField(key) {
    var found = null;
    DS_NOTE_FIELDS.forEach(function (f) { if (f.key === key) found = f; });
    return found;
}

// One textarea field for the consultation form. Textareas are tagged by id
// (dsNote_<pid>_<key>) so composition and refill stay data-driven; rows for the
// truncated snippet simply register more DS_NOTE_FIELDS entries.
function dsNoteFieldHtml(pid, key, opts) {
    opts = opts || {};
    var f = dsNoteField(key);
    if (!f) return '';
    var id = 'dsNote_' + pid + '_' + f.key;
    var control = opts.control || f.control || 'textarea';
    var val = opts.value != null ? opts.value : (f.defaultValue != null ? f.defaultValue : '');
    var field;
    if (control === 'select') {
        var choices = opts.options || f.options || [];
        field = '<select id="' + id + '">' + choices.map(function (o) {
            return '<option value="' + escHtml(o.value) + '"'
                + (String(o.value) === String(val) ? ' selected' : '') + '>'
                + escHtml(o.label) + '</option>';
        }).join('') + '</select>';
    } else if (control === 'input') {
        field = '<input type="text" id="' + id + '" value="' + escHtml(val) + '"'
            + ' placeholder="' + escHtml(opts.placeholder || '') + '">';
    } else {
        field = '<textarea id="' + id + '" rows="3" placeholder="' + escHtml(opts.placeholder || '') + '">'
            + (opts.value ? escHtml(opts.value) : '')
            + '</textarea>';
    }
    return '<div class="ds-fld' + (opts.warn ? ' warn' : '') + '">'
        + '<label for="' + id + '">' + escHtml(f.plain)
        + (opts.required ? ' <span style="color:#C0392B;">*</span>' : '') + '</label>'
        + field + '</div>';
}

function dsReadNoteFields(pid) {
    var out = {};
    DS_NOTE_FIELDS.forEach(function (f) {
        var el = document.getElementById('dsNote_' + pid + '_' + f.key);
        out[f.key] = el ? el.value.trim() : '';
    });
    return out;
}

function dsHasNoteContent(pid) {
    var fields = dsReadNoteFields(pid);
    return DS_NOTE_FIELDS.some(function (f) {
        // The status select always holds a value, so it does not count as
        // clinical content for the "nothing entered" guard.
        if ((f.control || 'textarea') === 'select') return false;
        return !!fields[f.key];
    });
}

function dsClearNoteFields(pid) {
    DS_NOTE_FIELDS.forEach(function (f) {
        var el = document.getElementById('dsNote_' + pid + '_' + f.key);
        if (!el) return;
        if ((f.control || 'textarea') === 'select') {
            // Selects reset to their default (e.g. NEW / CONTINUE_ADMISSION)
            // rather than a blank option after a save.
            el.value = f.defaultValue != null
                ? f.defaultValue
                : ((f.options && f.options[0]) ? f.options[0].value : '');
        } else {
            el.value = '';
        }
    });
}

// Build the labelled clinical-note block from the form fields. Only sections
// with content are written; the entry type heads the block.
function dsComposeClinicalNote(pid, type) {
    var fields = dsReadNoteFields(pid);
    var lines = [];
    lines.push('ENTRY TYPE: ' + (DS_ENTRY_LABELS[type] || type));
    DS_NOTE_FIELDS.forEach(function (f) {
        if (fields[f.key]) lines.push(f.label + ':\n' + fields[f.key]);
    });
    return lines.join('\n\n');
}

// Reverse of the above: split a saved block into its labelled sections so the
// record renders neatly and "Refill" can pull a previous entry back into the
// form. Old free-text notes (no headers) come through as { raw: ... }.
function dsParseNoteBlock(text) {
    var sections = {};
    if (!text) return sections;
    var lines = String(text).split(/\r?\n/);
    var current = null, buf = [];
    lines.forEach(function (line) {
        var m = line.match(/^([A-Z][A-Z0-9 /()._-]+):\s*(.*)$/);
        var key = null;
        if (m) {
            var label = m[1].trim();
            DS_NOTE_FIELDS.forEach(function (f) { if (f.label === label) key = f.key; });
            if (!key && label === 'ENTRY TYPE') key = 'entry_type';
        }
        if (key) {
            if (current) sections[current] = buf.join('\n').trim();
            current = key;
            buf = m[2] ? [m[2]] : [];
        } else {
            buf.push(line);
        }
    });
    if (current) {
        sections[current] = buf.join('\n').trim();
    } else {
        // No known header found - plain legacy note.
        sections.raw = text.trim();
    }
    return sections;
}

function dsRenderNoteText(text) {
    var parts = dsParseNoteBlock(text);
    if (parts.raw) return '<div class="txt">' + escHtml(parts.raw) + '</div>';
    var html = '';
    if (parts.entry_type) {
        html += '<div class="ds-note-sec"><span class="k">Entry Type</span><span class="v">' + escHtml(parts.entry_type) + '</span></div>';
    }
    DS_NOTE_FIELDS.forEach(function (f) {
        // The diagnosis fields and discharge process are grouped into their own
        // panel below, not rendered as plain sections.
        if (DS_DIAG_KEYS.indexOf(f.key) !== -1) return;
        if (f.key === DS_DISCHARGE_KEY) return;
        if (parts[f.key]) {
            html += '<div class="ds-note-sec"><span class="k">' + escHtml(f.plain) + '</span><div class="v">' + escHtml(parts[f.key]) + '</div></div>';
        }
    });
    var hasDiag = DS_DIAG_KEYS.some(function (k) { return !!parts[k]; })
        || !!parts[DS_DISCHARGE_KEY];
    if (hasDiag) {
        html += '<div class="ds-note-diag">'
            + dsDiagCell('Provisional Diagnosis', parts.provisional_diagnosis, false)
            + dsDiagCell('Confirmed Diagnosis', parts.confirmed_diagnosis, false)
            + dsDiagCell('Diagnosis Status', parts.diagnosis_status, true)
            + (parts[DS_DISCHARGE_KEY] ? dsDischargeCell(parts[DS_DISCHARGE_KEY]) : '')
            + '</div>';
    }
    return '<div class="txt">' + html + '</div>';
}

// Cell of the saved-entry diagnosis panel showing the discharge process as a
// colour-coded badge. The raw select value is mapped back to its friendly label.
function dsDischargeCell(value) {
    var code = String(value || '').toUpperCase();
    return '<div><span class="k">Discharge Process</span>'
        + (value
            ? '<span class="ds-discharge-badge ' + (DS_DISCHARGE_CLASS[code] || 'continue') + '">'
                + escHtml(DS_DISCHARGE_LABELS[code] || value) + '</span>'
            : '<span class="v">&mdash;</span>')
        + '</div>';
}

// One cell of the saved-entry diagnosis panel. The status renders as a
// colour-coded badge; the two diagnoses render as plain text.
function dsDiagCell(label, value, isStatus) {
    var inner;
    if (isStatus) {
        inner = value
            ? '<span class="ds-status-badge ' + (DS_STATUS_CLASS[String(value).toUpperCase()] || 'old') + '">' + escHtml(value) + '</span>'
            : '<span class="v">&mdash;</span>';
    } else {
        inner = '<span class="v">' + escHtml(value || '\u2014') + '</span>';
    }
    return '<div><span class="k' + (isStatus ? '' : ' blue') + '">' + escHtml(label) + '</span>' + inner + '</div>';
}

// Fill the consultation form with the most recent previous clinical entry:
// its labelled sections populate the fields (allergies fall back to the
// patient's stored allergies when the note has none).
function dsRefillPrevious(pid) {
    var list = dsNotesCache[pid] || [];
    if (!list.length) {
        showAlert('No previous clinical entries to refill from.', 'info');
        return;
    }
    var prev = list[0];
    var parts = dsParseNoteBlock(prev.clinical_note);
    var filled = 0;
    (DS_NOTE_FIELDS).forEach(function (f) {
        var el = document.getElementById('dsNote_' + pid + '_' + f.key);
        if (!el || !parts[f.key]) return;
        el.value = parts[f.key];
        filled++;
    });
    if (parts.entry_type && DS_ENTRY_LABELS[prev.note_type]) {
        var t = document.getElementById('dsNoteType_' + pid);
        if (t) t.value = prev.note_type;
    }
    if (!filled && !parts.raw) {
        showAlert('The last entry has no structured fields to refill.', 'info');
        return;
    }
    showAlert('Previous entry refilled into the form.', 'success');
}

/* ---------- Clinical notes (tab 1) ---------- */
async function loadClinicalNotes(pid) {
    try {
        var r = await fetch('/hms/backend/api/clinical_notes.php?action=list&patient_id=' + pid);
        var d = await r.json();
        dsNotesCache[pid] = (d.success && d.notes) ? d.notes : [];
    } catch (e) {
        console.error('Clinical notes load error:', e);
        dsNotesCache[pid] = [];
    }
    renderClinicalNotes(pid);
}

/* View state for the collapsible, sortable clinical record (defaults: open,
   newest first — matching the API's own DESC ordering). */
function dsNotesState(pid) {
    if (!dsNotesUi[pid]) dsNotesUi[pid] = { open: true, desc: true };
    return dsNotesUi[pid];
}

function dsToggleNotes(pid) {
    var st = dsNotesState(pid);
    st.open = !st.open;
    renderClinicalNotes(pid);
}

function dsToggleNotesSort(pid) {
    var st = dsNotesState(pid);
    st.desc = !st.desc;
    renderClinicalNotes(pid);
}

function renderClinicalNotes(pid) {
    var holder = document.getElementById('dsNotesList_' + pid);
    if (!holder) return;
    var st = dsNotesState(pid);

    // Sort a copy so the cache keeps the API's own order for "refill previous".
    var list = (dsNotesCache[pid] || []).slice();
    list.sort(function (a, b) {
        var ta = String(a.created_at || ''), tb = String(b.created_at || '');
        if (ta === tb) return (Number(b.id) || 0) - (Number(a.id) || 0);
        return ta < tb ? -1 : 1;
    });
    if (st.desc) list.reverse();

    // Header reflects the current state.
    var hint = document.getElementById('dsNotesHint_' + pid);
    if (hint) hint.textContent = st.open ? 'Click to Hide' : 'Click to Expand';
    var count = document.getElementById('dsNotesCount_' + pid);
    if (count) count.textContent = list.length;
    var sortBtn = document.getElementById('dsNotesSort_' + pid);
    if (sortBtn) {
        sortBtn.classList.toggle('asc', !st.desc);
        var sic = sortBtn.querySelector('i');
        if (sic) sic.className = 'fa-solid ' + (st.desc ? 'fa-arrow-down-9-1' : 'fa-arrow-up-1-9');
        var slbl = sortBtn.querySelector('span');
        if (slbl) slbl.textContent = st.desc ? 'Sorted: Newest First (DESC)' : 'Sorted: Oldest First (ASC)';
    }
    var chev = document.getElementById('dsNotesChev_' + pid);
    if (chev) {
        var cic = chev.querySelector('i');
        if (cic) cic.className = 'fa-solid ' + (st.open ? 'fa-chevron-up' : 'fa-chevron-down');
    }
    holder.classList.toggle('hidden', !st.open);

    if (!list.length) {
        holder.innerHTML = '<div class="ds-empty">No clinical entries recorded for this stay yet.</div>';
        return;
    }
    holder.innerHTML = list.map(function (n) {
        return '<div class="ds-note-item">'
            + '<div class="top"><span class="who">' + escHtml(n.doctor_name || 'Doctor')
                + ' <span style="font-weight:400;color:#64748B;">&middot; ' + escHtml(DS_NOTE_LABELS[n.note_type] || n.note_type) + '</span></span>'
                + '<span class="when">' + escHtml(fmtDateTime(n.created_at)) + '</span></div>'
            + dsRenderNoteText(n.clinical_note)
            + '<div style="text-align:right;margin-top:6px;"><button type="button" class="rm" onclick="dsDeleteNote(' + n.id + ',' + pid + ')">Delete</button></div>'
            + '</div>';
    }).join('');
}

async function dsSaveDoctorEntry(pid) {
    var row = dsInpatients.filter(function (p) { return String(p.patient_id) === String(pid); })[0];
    if (!row) return;
    if (!dsHasNoteContent(pid)) { showAlert('Enter at least one clinical field before saving.', 'error'); return; }
    var type = document.getElementById('dsNoteType_' + pid).value;
    var text = dsComposeClinicalNote(pid, type);

    try {
        var res = await fetch('/hms/backend/api/clinical_notes.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                patient_id: pid,
                admission_id: row.admission_id,
                note_type: type,
                clinical_note: text
            })
        });
        var data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not save the entry.');

        // Allergies are a standing patient fact: when the field differs from
        // what the record holds, sync it. Non-fatal if the sync fails.
        var summary = (dsContext[pid] || {}).summary || {};
        var storedAllergy = String(summary.allergies || '') === '' ? '' : String(summary.allergies);
        var newAllergy = dsReadNoteFields(pid).allergies;
        if (newAllergy !== storedAllergy) {
            try {
                await fetch('/hms/backend/api/patients.php?id=' + pid, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ allergies: newAllergy || '' })
                });
            } catch (e) { console.error('Allergy sync error:', e); }
        }

        dsClearNoteFields(pid);
        showAlert((DS_NOTE_LABELS[type] || 'Clinical entry') + ' saved.', 'success');
        markSaved(pid);
        await loadClinicalNotes(pid);
    } catch (e) {
        showAlert(e.message, 'error');
    }
}

async function dsDeleteNote(id, pid) {
    if (!confirm('Delete this clinical entry?\n\nThe record cannot be recovered.')) return;
    try {
        var res = await fetch('/hms/backend/api/clinical_notes.php?action=delete&id=' + id, { method: 'DELETE' });
        var data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not delete the entry.');
        showAlert('Clinical entry deleted.', 'success');
        await loadClinicalNotes(pid);
    } catch (e) {
        showAlert(e.message, 'error');
    }
}

/* ---------- Prescriptions (tab 3) ---------- */
async function loadPrescriptions(visitId, pid) {
    try {
        var r = await fetch('/hms/backend/api/prescriptions.php?visit_id=' + visitId);
        var d = await r.json();
        dsMedsCache[pid] = (d.success && d.prescriptions) ? d.prescriptions : [];
    } catch (e) {
        console.error('Prescriptions load error:', e);
        dsMedsCache[pid] = [];
    }
    renderMedications(pid);
}

function renderMedications(pid) {
    var tbody = document.getElementById('dsMedsBody_' + pid);
    if (!tbody) return;
    var list = dsMedsCache[pid] || [];
    if (!list.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="ds-empty">No prescriptions recorded for the latest visit yet.</td></tr>';
        return;
    }
    tbody.innerHTML = list.map(function (rx) {
        var st = String(rx.status || 'pending');
        var cls = /dispens|issued/i.test(st) ? 'ok' : (/cancel/i.test(st) ? 'bad' : 'warn');
        return '<tr>'
            + '<td><strong>' + escHtml(rx.drug_name || '--') + '</strong></td>'
            + '<td>' + escHtml(rx.dosage || '--') + '</td>'
            + '<td>' + escHtml(rx.frequency || '--') + '</td>'
            + '<td>' + escHtml(rx.duration || '--') + '</td>'
            + '<td><span class="ds-pill ' + cls + '">' + escHtml(st) + '</span></td>'
            + '</tr>';
    }).join('');
}

async function ensureConsultContext(pid) {
    if (dsConsultCache[pid]) { renderInvCatalogue(pid); return dsConsultCache[pid]; }
    var holder = document.getElementById('dsInvCat_' + pid);
    if (holder) holder.innerHTML = '<div class="ds-empty">Loading investigation catalogue…</div>';
    try {
        var r = await fetch('/hms/backend/api/consultation_form.php?action=context&patient_id=' + pid);
        var d = await r.json();
        if (!d.success) throw new Error(d.error || 'Failed to load');
        dsConsultCache[pid] = d;
    } catch (e) {
        console.error('Consultation context error:', e);
        if (holder) holder.innerHTML = '<div class="ds-empty" style="color:#C0392B;">Could not load the investigation catalogue.</div>';
        return null;
    }
    renderInvCatalogue(pid);
    return dsConsultCache[pid];
}

function renderInvCatalogue(pid) {
    var holder = document.getElementById('dsInvCat_' + pid);
    var modRow = document.getElementById('dsModRow_' + pid);
    if (!holder) return;
    var ctx = dsConsultCache[pid];
    var inv = (ctx && ctx.investigations) ? ctx.investigations : [];
    if (!inv.length) {
        holder.innerHTML = '<div class="ds-empty">No investigations are configured.</div>';
        if (modRow) modRow.innerHTML = '';
        return;
    }

    // Sort the catalogue under its modality (kind).
    var groups = {};
    DS_INV_MODALITIES.forEach(function (m) { groups[m.kind] = []; });
    inv.forEach(function (i) {
        if (!groups[i.kind]) groups[i.kind] = [];
        groups[i.kind].push(i);
    });

    // Master control bar — reveal or collapse every modality card at once.
    // (Replaces the old flat master tick row; the per-modality "select all"
    // checkbox now lives in each card header instead.)
    if (modRow) {
        modRow.innerHTML =
            '<div class="bar-title"><i class="fa-solid fa-flask-vial"></i><span>Request Investigations &amp; Diagnostic Tests</span></div>'
            + '<div class="bar-btns">'
                + '<button type="button" class="ds-inv-bar-btn" onclick="dsShowAllMods(' + pid + ',true)"><i class="fa-solid fa-eye"></i><span>Show All Modalities</span></button>'
                + '<button type="button" class="ds-inv-bar-btn hide" onclick="dsShowAllMods(' + pid + ',false)"><i class="fa-solid fa-eye-slash"></i><span>Hide All</span></button>'
            + '</div>';
    }

    // One collapsible card per modality. The Other card also carries a
    // free-text box so the doctor can request anything not preset.
    holder.innerHTML = DS_INV_MODALITIES.map(function (m) {
        var items = groups[m.kind] || [];
        var isOther = m.kind === 'other';
        if (!items.length && !isOther) return '';
        var body = items.map(function (i) {
            return '<label><input type="checkbox" class="ds-inv-check" data-ds-inv="' + pid + '" data-ds-grp="' + m.kind + '" value="' + escHtml(i.key) + '">'
                + '<span>' + escHtml(i.label) + '</span>'
                + '<span class="kind">' + escHtml(m.tag) + '</span></label>';
        }).join('');
        if (!items.length && isOther) {
            body = '<div class="ds-empty" style="padding:10px;">No presets — type any procedure below.</div>';
        }
        return '<div class="ds-inv-card" id="dsMod_' + pid + '_' + m.kind + '" data-ds-grp-block="' + m.kind + '">'
            + '<div class="ds-inv-card-head" onclick="dsToggleModCard(' + pid + ',\'' + m.kind + '\')">'
                + '<div class="left">'
                    + '<input type="checkbox" class="mod-check ds-mod-check" id="dsModCheck_' + pid + '_' + m.kind + '" data-ds-mod="' + m.kind + '" onclick="event.stopPropagation()" onchange="dsToggleMod(' + pid + ',\'' + m.kind + '\',this.checked)">'
                    + '<i class="fa-solid ' + m.icon + '"></i>'
                    + '<h5>' + escHtml(m.groupLabel) + '</h5>'
                    + '<span class="mod-cnt" id="dsModCnt_' + pid + '_' + m.kind + '">' + items.length + '</span>'
                + '</div>'
                + '<div class="right">'
                    + '<span class="vis-badge" id="dsModVis_' + pid + '_' + m.kind + '">Visible</span>'
                    + '<i class="fa-solid fa-chevron-up chev"></i>'
                + '</div>'
            + '</div>'
            + '<div class="ds-inv-card-body">'
                + '<div class="ds-inv">' + body + '</div>'
                + (isOther
                    ? '<div class="ds-other-spec"><input type="text" id="dsOtherText_' + pid + '" class="ds-input" placeholder="Or type any other test / procedure not listed above…"></div>'
                    : '')
            + '</div>'
            + '</div>';
    }).join('');

    dsSyncModMasters(pid);
    if (!holder.getAttribute('data-ds-inv-bound')) {
        holder.setAttribute('data-ds-inv-bound', '1');
        holder.addEventListener('change', function (e) {
            if (e.target && e.target.classList && e.target.classList.contains('ds-inv-check')) {
                dsSyncModMasters(pid);
            }
        });
    }
}

// Collapse / reveal a single modality card. The card head click toggles it.
function dsToggleModCard(pid, kind) {
    var card = document.getElementById('dsMod_' + pid + '_' + kind);
    if (!card) return;
    dsSetCardVis(pid, kind, card.classList.contains('collapsed'));
}

// Apply the reveal state to a card, keeping its Visible / Hidden badge honest.
function dsSetCardVis(pid, kind, visible) {
    var card = document.getElementById('dsMod_' + pid + '_' + kind);
    if (!card) return;
    card.classList.toggle('collapsed', !visible);
    var badge = document.getElementById('dsModVis_' + pid + '_' + kind);
    if (badge) badge.textContent = visible ? 'Visible' : 'Hidden';
}

// Master reveal / collapse for every modality card at once.
function dsShowAllMods(pid, show) {
    DS_INV_MODALITIES.forEach(function (m) { dsSetCardVis(pid, m.kind, show); });
}

// Master tick toggles every test underneath that modality.
function dsToggleMod(pid, kind, checked) {
    document.querySelectorAll('#dsInvCat_' + pid + ' [data-ds-grp="' + kind + '"]').forEach(function (c) {
        c.checked = !!checked;
    });
    dsSyncModMasters(pid);
}

// Keep the master state truthful: checked when ALL tests in its group are
// checked (master with one test behaves like that test), lit up whenever any
// are ticked, and the count badge shows selected / total.
function dsSyncModMasters(pid) {
    DS_INV_MODALITIES.forEach(function (m) {
        var card = document.getElementById('dsMod_' + pid + '_' + m.kind);
        if (!card) return; // modality absent from the catalogue
        var cnt = document.getElementById('dsModCnt_' + pid + '_' + m.kind);
        var master = document.getElementById('dsModCheck_' + pid + '_' + m.kind);
        var checks = document.querySelectorAll('#dsInvCat_' + pid + ' [data-ds-grp="' + m.kind + '"]');
        var total = checks.length, on = 0;
        checks.forEach(function (c) { if (c.checked) on++; });
        if (cnt) cnt.textContent = on ? (on + '/' + total) : String(total);
        if (master) { master.checked = total > 0 && on === total; master.disabled = total === 0; }
        card.classList.toggle('on', !!on);
    });
}

async function dsAddDrug(pid) {
    var input = document.getElementById('dsDrugSearch_' + pid);
    if (!input) return;
    var term = input.value.trim();
    if (!term) { showAlert('Type or pick a drug to add.', 'error'); return; }

    var list = dsDrugs[pid] || [];
    var lower = term.toLowerCase();
    var drug = list.filter(function (d) {
        return String(d.drug_name || '').toLowerCase() === lower || String(d.drug_code || '').toLowerCase() === lower;
    })[0];

    if (!drug) {
        try {
            var r = await fetch('/hms/backend/api/consultation_form.php?action=drugs&q=' + encodeURIComponent(term));
            var d = await r.json();
            var found = (d && d.drugs) ? d.drugs : [];
            dsDrugs[pid] = found;
            var opts = document.getElementById('dsDrugList_' + pid);
            if (opts) opts.innerHTML = found.map(function (x) { return '<option value="' + escHtml(x.drug_name) + '">'; }).join('');
            drug = found.filter(function (x) {
                return String(x.drug_name).toLowerCase() === lower || String(x.drug_code).toLowerCase() === lower;
            })[0];
        } catch (e) { console.error('Drug search error:', e); }
    }

    if (!drug) {
        showAlert('"' + term + '" is not a stocked drug. Pick one from the list.', 'error');
        return;
    }

    dsRx[pid] = dsRx[pid] || [];
    if (dsRx[pid].some(function (l) { return l.drug_id === drug.id; })) {
        showAlert(drug.drug_name + ' is already on this prescription.', 'error');
        return;
    }
    dsRx[pid].push({
        drug_id: drug.id, drug_name: drug.drug_name, generic_name: drug.generic_name,
        dosage: '', frequency: '', duration: '', quantity: 1, instructions: ''
    });
    input.value = '';
    renderRxLines(pid);
}

function renderRxLines(pid) {
    var host = document.getElementById('dsRxLines_' + pid);
    if (!host) return;
    var lines = dsRx[pid] || [];
    if (!lines.length) {
        host.innerHTML = '<div class="ds-rx-empty">No drugs added yet.</div>';
        return;
    }
    host.innerHTML = lines.map(function (l, i) {
        return '<div class="ds-rx-line">'
            + '<div class="top"><b>' + escHtml(l.drug_name) + '</b>'
                + (l.generic_name && String(l.generic_name).toLowerCase() !== String(l.drug_name).toLowerCase()
                    ? '<span class="gen">' + escHtml(l.generic_name) + '</span>' : '')
                + '<button type="button" class="rm" onclick="dsRemoveDrug(' + pid + ',' + i + ')">Remove</button></div>'
            + '<div class="flds">'
                + '<div><label>Dosage</label><input type="text" data-ds-rx="dosage" data-ds-i="' + i + '" value="' + escHtml(l.dosage) + '" placeholder="e.g. 1g"></div>'
                + '<div><label>Frequency</label><input type="text" data-ds-rx="frequency" data-ds-i="' + i + '" value="' + escHtml(l.frequency) + '" placeholder="e.g. TDS"></div>'
                + '<div><label>Duration</label><input type="text" data-ds-rx="duration" data-ds-i="' + i + '" value="' + escHtml(l.duration) + '" placeholder="e.g. 5 days"></div>'
                + '<div><label>Quantity</label><input type="number" min="1" step="1" data-ds-rx="quantity" data-ds-i="' + i + '" value="' + escHtml(l.quantity == null ? 1 : l.quantity) + '"></div>'
            + '</div></div>';
    }).join('');
}

function dsRemoveDrug(pid, i) {
    if (dsRx[pid]) { dsRx[pid].splice(i, 1); renderRxLines(pid); }
}

function dsCollectRx(pid) {
    var lines = dsRx[pid] || [];
    document.querySelectorAll('#dsRxLines_' + pid + ' [data-ds-rx]').forEach(function (el) {
        var i = Number(el.getAttribute('data-ds-i'));
        if (lines[i]) lines[i][el.getAttribute('data-ds-rx')] = el.value.trim();
    });
    return lines.map(function (l) {
        return {
            drug_id: l.drug_id, dosage: l.dosage, frequency: l.frequency,
            duration: l.duration, quantity: Number(l.quantity) || 1, instructions: l.instructions || ''
        };
    });
}

async function dsSavePrescription(pid) {
    var ctx = await ensureConsultContext(pid);
    if (!ctx) { showAlert('Could not load the patient context.', 'error'); return; }
    var lines = dsCollectRx(pid);
    if (!lines.length) { showAlert('Add at least one drug before saving.', 'error'); return; }

    var paid = dsPostConsultation(pid, ctx, {
        diagnosis_status: 'New',
        medication_notes: 'Prescription issued from the Doctor Station.',
        prescriptions: lines
    });
    await paid;
}

/* ---------- Investigations (tab 6) ---------- */
async function loadInvestigations(pid, hospitalNumber) {
    var tbody = document.getElementById('dsInvBody_' + pid);
    if (!tbody || !hospitalNumber) return;
    var rows = [];
    try {
        var labP = fetch('/hms/backend/api/lab.php?action=requests').then(function (r) { return r.json(); }).catch(function () { return { success: false }; });
        var radP = fetch('/hms/backend/api/radiology.php?action=requests').then(function (r) { return r.json(); }).catch(function () { return { success: false }; });
        var othP = fetch('/hms/backend/api/consultation_form.php?action=other_requests').then(function (r) { return r.json(); }).catch(function () { return { success: false }; });
        var out = await Promise.all([labP, radP, othP]);
        var lab = out[0], rad = out[1], oth = out[2];
        if (lab.success && lab.requests) {
            lab.requests.filter(function (x) { return x.hospital_number === hospitalNumber; }).forEach(function (x) {
                rows.push({ kind: 'Laboratory', test: x.test_type, when: x.requested_at, by: x.doctor_name || '--', status: x.status, result_status: x.result_status });
            });
        }
        if (rad.success && rad.requests) {
            rad.requests.filter(function (x) { return x.hospital_number === hospitalNumber; }).forEach(function (x) {
                var test = x.exam_type + (x.body_part ? ' / ' + x.body_part : '');
                rows.push({ kind: 'Radiology', test: test, when: x.requested_at, by: x.doctor_name || '--', status: x.status, result_status: x.result_status });
            });
        }
        if (oth.success && oth.requests) {
            oth.requests.filter(function (x) { return x.hospital_number === hospitalNumber; }).forEach(function (x) {
                rows.push({ kind: 'Other', test: x.request_type, when: x.requested_at, by: x.doctor_name || '--', status: x.status, result_status: null });
            });
        }
    } catch (e) { console.error('Investigations load error:', e); }
    dsInvCache[pid] = rows;
    renderInvestigations(pid);
}

function invStatusBadge(x) {
    if (String(x.status || '').toLowerCase() === 'cancelled') return '<span class="ds-pill bad">Cancelled</span>';
    var rs = String(x.result_status || '').toLowerCase();
    if (rs === 'final' || rs === 'amended') return '<span class="ds-pill ok">Result Available</span>';
    if (rs === 'draft') return '<span class="ds-pill info">Result Drafted</span>';
    var label = String(x.status || 'pending').replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    return '<span class="ds-pill warn">' + escHtml(label) + '</span>';
}

function renderInvestigations(pid) {
    var tbody = document.getElementById('dsInvBody_' + pid);
    if (!tbody) return;
    var list = dsInvCache[pid] || [];
    if (!list.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="ds-empty">No investigations requested for this patient yet.</td></tr>';
        return;
    }
    tbody.innerHTML = list.slice().sort(function (a, b) {
        return String(b.when || '').localeCompare(String(a.when || ''));
    }).map(function (x) {
        return '<tr>'
            + '<td><strong>' + escHtml(fmtDateTime(x.when)) + '</strong></td>'
            + '<td>' + escHtml(x.test) + '</td>'
            + '<td>' + escHtml(x.kind) + '</td>'
            + '<td>' + escHtml(x.by) + '</td>'
            + '<td>' + invStatusBadge(x) + '</td>'
            + '</tr>';
    }).join('');
}

async function dsSaveInvestigations(pid) {
    var ctx = await ensureConsultContext(pid);
    if (!ctx) { showAlert('Could not load the patient context.', 'error'); return; }
    var keys = [];
    document.querySelectorAll('#dsInvCat_' + pid + ' .ds-inv-check').forEach(function (c) { if (c.checked) keys.push(c.value); });
    var otherText = ((document.getElementById('dsOtherText_' + pid) || {}).value || '').trim();
    if (!keys.length && !otherText) {
        showAlert('Tick a test — or type one under Other — before saving.', 'error');
        return;
    }
    var payload = {
        diagnosis_status: 'New',
        doctor_notes: 'Investigations requested from the Doctor Station.',
        investigations: keys
    };
    if (otherText) payload.other_requests = [otherText];
    await dsPostConsultation(pid, ctx, payload);
}

/* ---------- Shared consultation POST (prescriptions + investigations) ---------- */
async function dsPostConsultation(pid, ctx, overrides) {
    var allergies = (ctx.patient && ctx.patient.allergies) ? ctx.patient.allergies : '';
    var payload = {
        patient_id: pid,
        diagnosis_status: overrides.diagnosis_status || 'New',
        presenting_complaints: '',
        doctor_notes: overrides.doctor_notes || '',
        provisional_diagnosis: '',
        medication_notes: overrides.medication_notes || '',
        allergies: allergies,
        investigations: overrides.investigations || [],
        other_requests: overrides.other_requests || [],
        prescriptions: overrides.prescriptions || [],
        save_as_draft: false
    };
    try {
        var res = await fetch('/hms/backend/api/consultation_form.php?action=save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        var data = await res.json();
        if (!data.success) throw new Error(data.error || 'Could not save.');
        var parts = [];
        if (data.created) {
            if (data.created.prescriptions) parts.push(data.created.prescriptions + ' prescription' + (data.created.prescriptions === 1 ? '' : 's'));
            if (data.created.lab) parts.push(data.created.lab + ' lab request' + (data.created.lab === 1 ? '' : 's'));
            if (data.created.radiology) parts.push(data.created.radiology + ' imaging request' + (data.created.radiology === 1 ? '' : 's'));
            if (data.created.other) parts.push(data.created.other + ' other request' + (data.created.other === 1 ? '' : 's'));
        }
        showAlert('Saved' + (parts.length ? ' \u00B7 ' + parts.join(' \u00B7 ') : '') + '.', 'success');
        markSaved(pid);
        if (overrides.prescriptions) { dsRx[pid] = []; renderRxLines(pid); }
        if (overrides.investigations || overrides.other_requests) {
            var cat = document.getElementById('dsInvCat_' + pid);
            if (cat) cat.querySelectorAll('.ds-inv-check').forEach(function (c) { c.checked = false; });
            var oth = document.getElementById('dsOtherText_' + pid);
            if (oth) oth.value = '';
            dsSyncModMasters(pid);
        }
        var row = dsInpatients.filter(function (p) { return String(p.patient_id) === String(pid); })[0];
        if (row) loadInvestigations(pid, row.hospital_number);
    } catch (e) {
        showAlert(e.message, 'error');
    }
}

/* ---------- Nurse notes (read-only from the Nurses Station browser sheet) ---------- */
function sheetGet(kind, pid) {
    try { var raw = localStorage.getItem('hms:' + kind + '-' + pid); return raw ? JSON.parse(raw) : []; }
    catch (e) { return []; }
}

function renderNurseNotes(pid) {
    var holder = document.getElementById('dsNurseList_' + pid);
    if (!holder) return;
    var notes = sheetGet('nurse-notes', pid);
    if (!notes.length) {
        holder.innerHTML = '<div class="ds-empty">No nurse notes recorded yet.</div>';
        return;
    }
    holder.innerHTML = notes.slice().reverse().map(function (n) {
        return '<div style="background:#F1F5F9;border:1px solid #E2E8F0;border-radius:8px;padding:10px;margin-bottom:8px;font-size:12px;">'
            + '<div style="white-space:pre-wrap;color:#334155;">' + escHtml(n.text) + '</div>'
            + '<div style="font-size:10.5px;color:#94A3B8;margin-top:4px;">&mdash; ' + escHtml(n.by || 'Staff') + ' &middot; ' + escHtml(fmtDateTime(n.at)) + '</div>'
            + '</div>';
    }).join('');
}

/* ---------- Treatment sheet (read-only from the Nurses Station browser sheet) ---------- */
function renderTreatments(pid) {
    var tbody = document.getElementById('dsTreatBody_' + pid);
    if (!tbody) return;
    var list = sheetGet('treatments', pid);
    if (!list.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="ds-empty">No treatments logged yet.</td></tr>';
        return;
    }
    tbody.innerHTML = list.slice().reverse().map(function (t) {
        var cls = t.status === 'Done' ? 'ok' : (t.status === 'Failed' ? 'bad' : 'warn');
        return '<tr>'
            + '<td><strong>' + escHtml(fmtDateTime(t.at)) + '</strong></td>'
            + '<td><strong>' + escHtml(t.treatment) + '</strong></td>'
            + '<td style="white-space:pre-wrap;">' + escHtml(t.notes || '--') + '</td>'
            + '<td>' + escHtml(t.by || '--') + '</td>'
            + '<td><span class="ds-pill ' + cls + '">' + escHtml(t.status || 'Pending') + '</span></td>'
            + '</tr>';
    }).join('');
}

/* ============================ DRAFT / SAVE OR UPDATE (footer) ============================ */
function renderSavedStamp(pid) {
    var draft = dsReadDraft(pid);
    var el = document.getElementById('dsLastSaved_' + pid);
    if (el && draft && draft.savedAt) el.textContent = fmtDateTime(draft.savedAt);
}

function dsDraftKey(pid) { return 'hms:docstation-draft-' + pid; }

function dsReadDraft(pid) {
    try { var raw = localStorage.getItem(dsDraftKey(pid)); return raw ? JSON.parse(raw) : null; }
    catch (e) { return null; }
}

function dsSaveDraft(pid) {
    var payload = {
        noteType: (document.getElementById('dsNoteType_' + pid) || {}).value || 'IPD_NOTE',
        noteFields: dsReadNoteFields(pid),
        rx: dsCollectRx(pid),
        savedAt: new Date().toISOString()
    };
    try { localStorage.setItem(dsDraftKey(pid), JSON.stringify(payload)); } catch (e) {}
    var el = document.getElementById('dsLastSaved_' + pid);
    if (el) el.textContent = fmtDateTime(payload.savedAt);
    showAlert('Draft saved in this browser. Click Save Or Update to finalise.', 'info');
}

async function dsSaveOrUpdate(pid) {
    var noteContent = dsHasNoteContent(pid);
    var lines = dsCollectedLinesExist(pid);
    var invCount = document.querySelectorAll('#dsInvCat_' + pid + ' .ds-inv-check:checked').length;
    var otherText = ((document.getElementById('dsOtherText_' + pid) || {}).value || '').trim() !== '';

    if (!noteContent && !lines && !invCount && !otherText) {
        showAlert('Nothing to save — write a clinical entry, prescribe a drug or tick an investigation.', 'error');
        return;
    }
    if (noteContent) await dsSaveDoctorEntry(pid);
    if (lines) await dsSavePrescription(pid);
    if (invCount || otherText) await dsSaveInvestigations(pid);
    try { localStorage.removeItem(dsDraftKey(pid)); } catch (e) {}
}

function dsCollectedLinesExist(pid) {
    return (dsRx[pid] || []).length > 0;
}

function markSaved(pid) {
    var el = document.getElementById('dsSaved_' + pid);
    if (!el) return;
    el.classList.add('show');
    setTimeout(function () { el.classList.remove('show'); }, 3500);
}

/* ============================ PATIENT FOLDER ============================ */
/* Mount the system-wide Patient Master Folder banner (shared across every
   clinical station) for one patient card and scroll it into view. */
function dsOpenFolder(pid) {
    var host = document.getElementById('hmsPatientFolder');
    if (!window.HMSPatientFolder || !host) return;
    window.HMSPatientFolder.mount(host, pid, { station: 'doctor' });
    try { host.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    catch (e) { if (host.scrollIntoView) host.scrollIntoView(); }
}

/* ============================ MEDICAL RECORDS MODAL ============================ */
function dsOpenMedicalRecords(pid) {
    var row = dsInpatients.filter(function (p) { return String(p.patient_id) === String(pid); })[0];
    var notes = dsNotesCache[pid] || [];
    var body = document.getElementById('dsModalBody');

    var head = row
        ? '<p style="font-weight:800;color:#0F2D59;margin:0 0 4px;">' + escHtml(row.patient_name) + ' &middot; ' + escHtml(row.hospital_number || '--') + '</p>'
          + '<p style="font-size:11px;color:#64748B;margin:0 0 14px;">' + escHtml(row.ward_name || '') + ' &middot; Bed ' + escHtml(row.bed_number || '--')
          + (row.diagnosis ? ' &middot; Diagnosis: ' + escHtml(row.diagnosis) : '') + '</p>'
        : '';

    var list = notes.length
        ? notes.map(function (n) {
            return '<div class="ds-note-item">'
                + '<div class="top"><span class="who">' + escHtml(DS_NOTE_LABELS[n.note_type] || n.note_type) + ' &middot; ' + escHtml(n.doctor_name || 'Doctor') + '</span>'
                + '<span class="when">' + escHtml(fmtDateTime(n.created_at)) + '</span></div>'
                + dsRenderNoteText(n.clinical_note) + '</div>';
        }).join('')
        : '<div class="ds-empty">No clinical entries recorded for this stay yet.</div>';

    body.innerHTML = head + '<div class="ds-sec-head"><h4><i class="fa-solid fa-file-lines"></i> Clinical Entries</h4></div>' + list;
    document.getElementById('docstation-modal').classList.add('show');
}

function dsCloseModal() {
    document.getElementById('docstation-modal').classList.remove('show');
}

/* ============================ NAVIGATION HELPERS ============================ */
function dsGoAdmissions() {
    if (window.navigateTo) { window.navigateTo('admissions'); return; }
    if (window.loadModuleTab) { window.loadModuleTab('active_inpatients'); return; }
}

function dsTransfer() {
    // Bed state is owned by the Admissions Register; send the user there.
    dsGoAdmissions();
}

/* ============================ HELPERS ============================ */
function escHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function fmtDateTime(dateString) {
    if (!dateString) return '--';
    var d = new Date(dateString);
    if (isNaN(d.getTime())) return String(dateString);
    var p = function (n) { return String(n).padStart(2, '0'); };
    return p(d.getDate()) + '/' + p(d.getMonth() + 1) + '/' + d.getFullYear() + ' ' + p(d.getHours()) + ':' + p(d.getMinutes());
}
</script>
