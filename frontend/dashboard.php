<?php
require_once __DIR__ . '/../backend/config/config.php';
requireLogin();
$userName = htmlspecialchars(getCurrentUserName() ?: 'EDWARD OSEI POKU');
$userRole = htmlspecialchars(getCurrentUserRole() ?: 'ADMIN');
$displayName = strtoupper($userName);
$displayRole = strtoupper($userRole);
// Session login timestamp (set in auth.php on login; falls back to page-render time)
$loginTimestamp = isset($_SESSION['login_time']) ? (int)$_SESSION['login_time'] : time();
// e.g. "Tuesday, 15/09/2026 15:29:16" (full weekday + date + exact system time)
$sinceTimestamp = date('l, d/m/Y H:i:s', $loginTimestamp);
// Elapsed session duration rendered before JS ticks take over
$elapsedSession = gmdate('H:i:s', max(0, time() - $loginTimestamp));
$clinicName = 'EDDIE HOSPITAL';
// New HealthCare cross brand logo (green + blue interlocking ribbon) — embedded inline vector SVG
// across the system header, sidebar headers and login/splash pages. The UI theme is derived from its
// green gradient (#80C342 / #4CAF50 / #1B5E20) and blue gradient (#00AEEF / #0072BC / #0D47A1).
function hcBrandLogo($px = 45) {
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" class="hms-brand-logo" style="width:' . $px . 'px;height:' . $px . 'px;display:inline-block;vertical-align:middle" aria-hidden="true">
  <defs>
    <linearGradient id="greenGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#80C342"/><stop offset="50%" stop-color="#4CAF50"/><stop offset="100%" stop-color="#1B5E20"/></linearGradient>
    <linearGradient id="blueGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#00AEEF"/><stop offset="50%" stop-color="#0072BC"/><stop offset="100%" stop-color="#0D47A1"/></linearGradient>
  </defs>
  <g transform="translate(250, 200) scale(1.2)">
    <path d="M -20 -90 C -20 -115 0 -130 25 -130 C 50 -130 70 -110 70 -85 C 70 -50 20 -20 -10 -10 C -40 0 -90 -20 -90 -50 C -90 -75 -70 -95 -45 -95 C -20 -95 -20 -90 -20 -90 Z" fill="url(#greenGrad)"/>
    <path d="M 20 90 C 20 115 0 130 -25 130 C -50 130 -70 110 -70 85 C -70 50 -20 20 10 10 C 40 0 90 20 90 50 C 90 75 70 95 45 95 C 20 95 20 90 20 90 Z" fill="url(#blueGrad)"/>
  </g>
</svg>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EHMS - Eddie Healthcare Solutions</title>
    <link rel="stylesheet" href="assets/css/date-picker.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background-color:#EBF0F5 !important;color:#212529;line-height:1.5;font-size:14px}

        /* ---------- TOP HEADER & BRANDING ---------- */
        .hms-topbar{
            height:58px;
            min-height:58px;
            background:#FFFFFF;
            border-bottom:3px solid #0072BC;
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:0 18px;
            gap:16px;
        }
        .topbar-left{display:flex;align-items:center;gap:12px;min-width:0}
        .hms-brand-logo{display:inline-block;vertical-align:middle;flex-shrink:0;filter:drop-shadow(0 1px 2px rgba(0,0,0,.15))}
        .hms-topbar h1{color:#0D47A1;font-size:18px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;white-space:nowrap;margin:0}
        .topbar-right{display:flex;align-items:center;gap:16px;flex-shrink:0}
        .brand-text{display:flex;flex-direction:column;align-items:flex-end;line-height:1.25}
        .brand-text strong{color:#0D47A1;font-size:15px;font-weight:800;letter-spacing:.6px;text-transform:uppercase}
        .brand-text span{font-size:10px;color:#7F8C9B;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
        .user-chip{font-size:12px;font-weight:600;color:#2C3E50;white-space:nowrap}
        .topbar-actions{display:flex;align-items:center;gap:8px;flex-shrink:0}
        .btn-header{background:#0072BC;color:#fff;border:none;padding:7px 16px;border-radius:3px;font-weight:700;font-size:12px;cursor:pointer;text-transform:uppercase;letter-spacing:.4px;transition:background .2s}
        .btn-header:hover{background:#0D47A1}
        .menu-toggle{display:none;flex-direction:column;gap:4px;background:none;border:none;cursor:pointer;padding:6px}
        .menu-toggle span{width:20px;height:2px;background:#0072BC}

        /* ---------- 3-COLUMN BODY ---------- */
        .hms-body{display:grid;grid-template-columns:250px minmax(0,1fr) 290px;align-items:stretch;min-height:calc(100vh - 58px)}

        /* ================= LEFT SIDEBAR : QUICK LINKS ================= */
        .sidebar-left{background:#fff;border-right:1px solid #C0C0C0;display:flex;flex-direction:column;min-width:0}
        .side-left-header{background:linear-gradient(135deg,#0072BC,#0D47A1);color:#fff;padding:14px 16px}
        .side-brand-row{display:flex;align-items:center;gap:10px}
        .side-left-header .side-title{font-size:15px;font-weight:800;letter-spacing:.4px;line-height:1.2;text-transform:uppercase}
        .side-left-header p{font-size:8.5px;color:#CFE6F5;text-transform:uppercase;letter-spacing:.7px;margin-top:5px;line-height:1.5}
        .side-nav{list-style:none;padding:8px 0}
        .side-nav a{display:flex;align-items:center;gap:10px;padding:9px 14px 9px 16px;color:#2C3E50;text-decoration:none;font-size:13px;border-left:3px solid transparent;transition:background .15s,border-color .15s}
        .side-nav a::before{content:'';width:8px;height:8px;border-radius:2px;background:#9FC4DE;flex-shrink:0;transition:background .15s}
        .side-nav a:hover{background:#F1F6FB;border-left-color:#0072BC;color:#0D47A1}
        .side-nav a:hover::before{background:#0072BC}
        .side-nav a.active{background-color:#EAEAEA;border-left-color:#0D47A1;color:#0D47A1;font-weight:bold}
        .side-nav a.active::before{background:#0D47A1}
        .side-nav a.dl-quick-link::before{display:none}
        .side-nav a.dl-quick-link svg{flex-shrink:0}
        .sidebar-action{padding:12px 14px 4px}
        .btn-send{width:100%;background:linear-gradient(180deg,#F39C12,#E67E22);color:#fff;border:none;padding:10px 12px;border-radius:3px;font-weight:700;font-size:13px;cursor:pointer;text-transform:uppercase;letter-spacing:.3px;transition:filter .15s}
        .btn-send:hover{filter:brightness(1.08)}
        .widget{margin:8px 14px;border-radius:3px;overflow:hidden;border:1px solid #C0C0C0}
        .widget-title{padding:8px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#fff;background:rgba(255,255,255,.07);border-bottom:1px solid rgba(255,255,255,.14)}
        .widget-days{background:#0D47A1;color:#fff}
        .widget-days .widget-body{padding:10px 12px;font-size:12px;color:#C9DDF0;line-height:1.6}
        .widget-days .widget-body span.alert-tag{display:inline-block;background:#E74C3C;color:#fff;font-size:9px;font-weight:700;padding:1px 6px;border-radius:8px;margin-right:6px;text-transform:uppercase;letter-spacing:.4px}
        .widget-login{background:#0D47A1;color:#fff}
        .widget-login .widget-body{padding:10px 12px 12px}
        .status-row{display:flex;justify-content:space-between;gap:8px;padding:3px 0;border-bottom:1px solid rgba(255,255,255,.09);font-size:11px}
        .status-row:last-of-type{border-bottom:none}
        .status-label{color:#8FB3D1;white-space:nowrap;font-size:10.5px}
        .status-value{color:#fff;font-weight:600;text-align:right;word-break:break-word;font-size:10.5px}
        .logout-btn{width:100%;background:#0072BC;color:#fff;border:none;padding:8px 12px;border-radius:3px;font-weight:700;font-size:12px;cursor:pointer;text-transform:uppercase;letter-spacing:.4px;margin-top:10px;transition:background .2s}
        .logout-btn:hover{background:#0D47A1}

        /* ================= CENTER WORKSPACE : MODULE GRID ================= */
        .main-content{padding:16px;background-color:#EBF0F5 !important;min-width:0;display:flex;flex-direction:column}
        .dashboard-fill{min-height:calc(100vh - 90px);display:flex;flex-direction:column}
        .central-dash{flex:1;display:flex;flex-direction:column;gap:12px;min-width:0}

        /* System Support Footer Bar */
        .hms-system-footer-bar{display:flex;align-items:center;gap:10px;background:linear-gradient(135deg,#0D47A1,#1565C0);border:1px solid #0B3E8F;border-radius:8px;padding:12px 16px;margin:0 14px 6px;box-shadow:0 2px 6px rgba(13,71,161,.22);color:#EAF2FC;font-size:13px;line-height:1.4}
        .hms-system-footer-bar strong{color:#FFD54F;font-weight:800}
        .main-content-card-wrapper .hms-system-footer-bar{margin:14px 0 0}
        .main-content-card-wrapper .system-footer-strip{margin:14px 0 0}
        .page-slot{width:100%;display:block;flex:1}
        .main-content-card-wrapper{background:#FFFFFF;border:1px solid #C0C0C0;border-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,0.06);padding:16px}

        .module-grid{
            display:grid;
            grid-template-columns:repeat(2, 1fr);
            gap:16px;
            padding:12px 16px;
            width:100%;
            align-content:start;
        }

        /* -------- Module card : matching Administrator-view dimensions -------- */
        .module-card{
            background-color:#FFFFFF;
            border:1px solid rgba(0,0,0,0.06);
            border-radius:6px;
            display:flex;
            align-items:center;
            justify-content:flex-start;
            padding:18px 24px;
            min-height:95px;
            cursor:pointer;
            text-decoration:none !important;
            box-shadow:0 2px 8px rgba(0,0,0,0.04);
            transition:all 0.2s ease-in-out;
        }
        .module-card:hover,
        .module-card.dragging{
            background-color:#FFFFFF;
            border-color:#0072BC;
            box-shadow:0 8px 20px rgba(15,45,89,0.12);
            transform:translateY(-3px);
            cursor:pointer;
        }

        /* Left icon box : 52px flex container, clean inline SVG */
        .module-icon{
            width:52px;
            min-width:52px;
            height:52px;
            display:flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
            margin-right:12px;
        }
        .module-icon svg{width:52px;height:52px;display:block}

        /* Card text label : matching Administrator-view typography */
        .module-title{
            flex:1;
            color:#0D47A1;
            font-weight:700;
            font-size:14px;
            text-transform:uppercase;
            text-align:center;
            letter-spacing:.5px;
            line-height:1.3;
            padding:0 4px;
        }

        /* ================= SYSTEM PORTAL : TWO-STATION DASHBOARD ================= */
        /* ================= UNIFORM DASHBOARD GRID : BOOTSTRAP-STYLE UTILITIES =================
           The shell has no Bootstrap dependency, so the refactored dashboard grid declares the
           minimal set of Bootstrap 5 helper classes it uses (row/col grid, flex, spacing). */
        .container-fluid{width:100%;padding-right:calc(var(--bs-gutter-x,1rem)*.5);padding-left:calc(var(--bs-gutter-x,1rem)*.5);margin-right:auto;margin-left:auto}
        .p-3{padding:1rem}
        .row{--bs-gutter-x:1rem;--bs-gutter-y:1rem;display:flex;flex-wrap:wrap;margin-top:calc(var(--bs-gutter-y)*-1);margin-right:calc(var(--bs-gutter-x)*-.5);margin-left:calc(var(--bs-gutter-x)*-.5);box-sizing:border-box}
        .row > *{box-sizing:border-box;flex-shrink:0;width:100%;max-width:100%;padding-right:calc(var(--bs-gutter-x)*.5);padding-left:calc(var(--bs-gutter-x)*.5);margin-top:var(--bs-gutter-y)}
        .col-md-6{flex:0 0 100%;width:100%;max-width:100%}
        @media (min-width:768px){.col-md-6{flex:0 0 50%;max-width:50%}}
        .card{position:relative;display:flex;flex-direction:column;min-width:0;word-wrap:break-word;background-color:#fff;background-clip:border-box}
        .border-0{border:0 !important}
        .shadow-sm{box-shadow:0 0.125rem 0.25rem rgba(0,0,0,.075) !important}
        .h-100{height:100%}
        .d-flex{display:flex}
        .align-items-center{align-items:center}
        .justify-content-center{justify-content:center}
        .me-3{margin-right:1rem}
        .me-4{margin-right:1.5rem}
        .text-center{text-align:center}
        .flex-grow-1{flex-grow:1}
        .font-weight-bold{font-weight:700}
        .text-uppercase{text-transform:uppercase}

        /* Uniform module card : white, 8px radius, subtle border, soft shadow */
        .hms-module-card{transition:border-color .2s ease-in-out,box-shadow .2s ease-in-out,transform .2s ease-in-out}
        .hms-module-card:hover,
        .hms-module-card.dragging{
            box-shadow:0 8px 20px rgba(15,45,89,0.12);
            transform:translateY(-3px);
        }

        .system-dashboard-container{
            display:flex;
            flex-direction:column;
            gap:20px;
            width:100%;
        }

        /* Station section header (old EHMS portal look : graded bar + uppercase title) */
        .sys-station-header{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:14px;
            width:100%;
            padding:12px 18px;
            border-radius:8px;
            box-shadow:0 2px 8px rgba(0,0,0,0.08);
        }
        .sys-station-header.station-doctor{
            background:linear-gradient(135deg,#0D47A1,#0072BC);
            border:1px solid #0B3E8F;
        }
        .sys-station-header.station-nurse{
            background:linear-gradient(135deg,#1B5E20,#4CAF50);
            border:1px solid #14501D;
        }
        .sys-station-title{
            display:flex;
            align-items:center;
            gap:12px;
            color:#FFFFFF;
            font-weight:800;
            font-size:16px;
            text-transform:uppercase;
            letter-spacing:1.2px;
            line-height:1.2;
        }
        .sys-station-title i{font-size:20px;color:#FFD54F}
        .sys-station-sub{
            color:rgba(255,255,255,.85);
            font-size:11px;
            font-weight:600;
            text-transform:uppercase;
            letter-spacing:.8px;
            text-align:right;
            line-height:1.3;
        }

        /* Station module grid : 2-column, same rhythm as the module grid */
        .sys-station-grid{
            display:grid;
            grid-template-columns:repeat(2, 1fr);
            gap:16px;
            width:100%;
            align-content:start;
        }

        /* Station module card : matching Administrator-view dimensions (old EHMS sys-card) */
        .sys-card{
            background-color:#FFFFFF;
            border:1px solid rgba(0,0,0,0.06);
            border-radius:6px;
            display:flex;
            align-items:center;
            justify-content:flex-start;
            padding:18px 24px;
            min-height:95px;
            cursor:pointer;
            text-decoration:none !important;
            box-shadow:0 2px 8px rgba(0,0,0,0.04);
            transition:all 0.2s ease-in-out;
        }
        .sys-card:hover,
        .sys-card.dragging{
            background-color:#FFFFFF;
            border-color:#0072BC;
            box-shadow:0 8px 20px rgba(15,45,89,0.12);
            transform:translateY(-3px);
            cursor:pointer;
        }

        /* Left icon box : 52px rounded box with a Font Awesome icon */
        .sys-card-icon{
            width:52px;
            min-width:52px;
            height:52px;
            display:flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
            margin-right:12px;
            border-radius:8px;
            background:linear-gradient(135deg,#E3F2FD,#BBDEFB);
            color:#0D47A1;
            font-size:24px;
        }
        .sys-card-icon .fa-solid{font-size:24px}

        /* Card text label : matching Administrator-view typography */
        .sys-card-title{
            flex:1;
            color:#0D47A1;
            font-weight:700;
            font-size:14px;
            text-transform:uppercase;
            text-align:center;
            letter-spacing:.5px;
            line-height:1.3;
            padding:0 4px;
        }

        /* System footer contact strip (old EHMS portal contact bar) */
        .system-footer-strip{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            flex-wrap:wrap;
            background:linear-gradient(135deg,#0D47A1,#1565C0);
            border:1px solid #0B3E8F;
            border-radius:8px;
            padding:12px 16px;
            margin:4px 0 6px;
            box-shadow:0 2px 6px rgba(13,71,161,.22);
            color:#EAF2FC;
            font-size:13px;
            line-height:1.4;
            width:100%;
        }
        .system-footer-strip strong{color:#FFD54F;font-weight:800}

        /* ================= RIGHT SIDEBAR : PATIENT SEARCH ================= */
        .sidebar-right{background:#fff;border-left:1px solid #C0C0C0;display:flex;flex-direction:column;min-width:0}
        .side-right-header{background:linear-gradient(135deg,#0072BC,#0D47A1);color:#fff;padding:12px 14px}
        .side-right-header h2{font-size:13px;font-weight:800;letter-spacing:.4px;text-transform:uppercase}
        .search-fields{padding:12px 14px;display:grid;gap:9px}
        .search-row{display:flex;align-items:center;gap:6px}
        .search-row label{width:104px;font-size:11px;color:#34495E;font-weight:600;flex-shrink:0}
        .search-row input{flex:1;min-width:0;padding:6px 8px;border:1px solid #C0C0C0;border-radius:3px;font-size:12px;outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit}
        .search-row input:focus{border-color:#0072BC;box-shadow:0 0 0 2px rgba(0,114,188,.15)}
        .btn-search{width:28px;height:28px;flex-shrink:0;border:1px solid #C0C0C0;background:#F4F7FB;border-radius:3px;color:#34495E;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s,color .15s,border-color .15s}
        .btn-search:hover{background:#0D47A1;color:#fff;border-color:#0D47A1}

        /* Search results panel */
        .search-results-panel{border-top:1px solid #E0E6ED;background:#F8FAFC;max-height:calc(100vh - 520px);overflow-y:auto}
        .results-header{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#EBF0F6;font-size:12px;font-weight:700;color:#34495E;border-bottom:1px solid #E0E6ED;position:sticky;top:0;z-index:1}
        .results-close{width:22px;height:22px;border:none;background:#C0392B;color:#fff;border-radius:50%;font-size:14px;line-height:22px;text-align:center;cursor:pointer;transition:background .15s}
        .results-close:hover{background:#96281B}
        .results-list{padding:0}
        .result-item{padding:10px 14px;border-bottom:1px solid #E8EDF2;cursor:pointer;transition:background .12s}
        .result-item:hover{background:#E8F4FD}
        .result-item:last-child{border-bottom:none}
        .result-name{font-size:13px;font-weight:700;color:#0D47A1;margin-bottom:3px}
        .result-meta{font-size:11px;color:#666;line-height:1.5}
        .result-badge{display:inline-block;background:#E8F4FD;color:#0D47A1;font-size:10px;padding:1px 6px;border-radius:3px;font-weight:600;margin-left:4px}
        .result-actions{margin-top:6px;display:flex;gap:6px}
        .result-view-btn{border:none;border-radius:4px;background:#0072BC;color:#fff;font-size:10px;font-weight:800;padding:4px 10px;cursor:pointer;letter-spacing:.3px;text-transform:uppercase}
        .result-view-btn:hover{background:#005A94}
        .result-no-results{padding:20px;text-align:center;color:#999;font-size:12px}
        .result-loading{padding:14px;text-align:center;color:#666;font-size:12px}
        .result-loading .spinner{display:inline-block;width:16px;height:16px;border:2px solid #C0C0C0;border-top-color:#0D47A1;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;margin-right:6px}
        @keyframes spin{to{transform:rotate(360deg)}}

        /* Patient profile modal */
        .pp-banner{display:flex;align-items:center;gap:16px;padding:22px 24px;background:linear-gradient(135deg,#0D47A1,#0072BC);color:#fff}
        .pp-avatar{width:64px;height:64px;min-width:64px;border-radius:50%;background:rgba(255,255,255,.18);border:2px solid rgba(255,255,255,.55);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;text-transform:uppercase}
        .pp-head{display:flex;flex-direction:column;gap:3px;min-width:0}
        .pp-name{font-size:19px;font-weight:800;letter-spacing:.3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .pp-sub{font-size:12px;color:rgba(255,255,255,.85)}
        .pp-hn-badge{align-self:flex-start;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.5);color:#fff;font-size:11px;font-weight:700;letter-spacing:.5px;padding:2px 10px;border-radius:999px}
        .pp-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:#E8EDF2}
        .pp-field{background:#fff;padding:12px 14px;min-height:56px}
        .pp-field span{display:block;font-size:9.5px;font-weight:800;letter-spacing:.6px;color:#8FA1B3;text-transform:uppercase;margin-bottom:4px}
        .pp-field b{font-size:13px;font-weight:700;color:#0F2D59;word-break:break-word}
        @media(max-width:720px){.pp-grid{grid-template-columns:1fr 1fr}.pp-banner{flex-wrap:wrap}}
        .btn-search svg{width:14px;height:14px;display:block}
        .side-actions{display:flex;gap:8px;padding:10px 14px}
        .btn-navy{flex:1;background:#0D47A1;color:#fff;border:none;padding:8px 10px;border-radius:3px;font-weight:700;font-size:12px;cursor:pointer;text-transform:uppercase;letter-spacing:.3px;transition:background .15s}
        .btn-navy:hover{background:#1A4D8E}
        .server-msgs{margin:6px 14px 14px;border:1px solid #C0C0C0;border-radius:3px;overflow:hidden}
        .server-msgs .widget-title{background:#F2F6FB;color:#0D47A1;border-bottom:1px solid #C0C0C0;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:8px 12px}
        .msg-item{padding:8px 12px;border-bottom:1px solid #EEF2F6;font-size:12px;color:#4A5A6A}
        .msg-item:last-child{border-bottom:none}
        .msg-item time{display:block;font-size:10px;color:#8FA1B3;margin-top:2px}
        .msg-item .msg-dot{display:inline-block;width:7px;height:7px;border-radius:50%;margin-right:7px;vertical-align:1px}
        .days-alert-count{display:inline-block;background:#E74C3C;color:#fff;font-size:10px;font-weight:800;min-width:18px;height:18px;line-height:18px;text-align:center;border-radius:9px;padding:0 5px;margin-left:6px}
        .alert-open-btn{border:1px solid #C0C0C0;border-radius:4px;background:#fff;color:#0D47A1;font-size:11px;font-weight:700;cursor:pointer;padding:5px 10px;font-family:inherit}
        .alert-open-btn:hover{background:#EEF5FB}

        /* ================= SEND MESSAGE MODAL ================= */
        #sendMessageModal{display:none;position:fixed;inset:0;z-index:1070;background:rgba(11,29,58,.55);align-items:center;justify-content:center;padding:24px 16px;overflow-y:auto}
        #sendMessageModal.show{display:flex}
        #sendMessageModal .modal-dialog{width:min(820px,100%);margin:auto;max-height:92vh}
        #sendMessageModal .modal-content{background:#fff;border-radius:10px;overflow:hidden;border:none;box-shadow:0 10px 30px rgba(0,0,0,.2);display:flex;flex-direction:column;max-height:92vh}
        #sendMessageModal .modal-header{color:#fff;padding:15px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-shrink:0}
        #sendMessageModal .modal-title{font-size:16px;font-weight:800;margin:0;display:flex;align-items:center;gap:8px;letter-spacing:.4px;text-transform:uppercase;color:#fff}
        #sendMessageModal .modal-title svg{flex-shrink:0}
        #sendMessageModal .close{border:none;background:none;color:#fff;font-size:26px;line-height:1;opacity:.85;cursor:pointer;padding:0 4px}
        #sendMessageModal .close:hover{opacity:1}
        #sendMessageModal .modal-body{padding:20px;background-color:#F8FAFC;overflow-y:auto;flex:1}
        #sendMessageModal .form-group{margin-bottom:16px}
        #sendMessageModal .form-group label{display:block;color:#0F2D59;font-size:12px;letter-spacing:.5px;margin-bottom:6px;font-weight:700}
        #sendMessageModal .form-control,#sendMessageModal .custom-select{width:100%;height:42px;padding:9px 34px 9px 12px;border:1px solid #C9D4E0;border-radius:6px;font-size:13px;font-family:inherit;outline:none;background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2364748B'/%3E%3C/svg%3E") no-repeat right 12px center;appearance:none;-webkit-appearance:none;cursor:pointer;transition:border-color .15s,box-shadow .15s;box-sizing:border-box;font-weight:600;color:#212529}
        #sendMessageModal textarea.form-control{height:auto;min-height:120px;resize:vertical;line-height:1.5;font-weight:400;cursor:text}
        #sendMessageModal input.form-control{background:#fff;cursor:text}
        #sendMessageModal .form-control:focus,#sendMessageModal .custom-select:focus{border-color:#0072BC;box-shadow:0 0 0 3px rgba(0,114,188,.15)}
        #sendMessageModal .custom-select:disabled{background-color:#E9EEF4;cursor:not-allowed}
        /* Two-column subject / channel row */
        #sendMessageModal .row{display:flex;gap:14px;flex-wrap:wrap;margin:0}
        #sendMessageModal .row > .form-group{margin-bottom:16px}
        #sendMessageModal .col-md-8{flex:2 1 260px;padding:0}
        #sendMessageModal .col-md-4{flex:1 1 140px;padding:0}
        @media (max-width:640px){#sendMessageModal .row{flex-direction:column;gap:0}}
        /* Recipient type radio pills (Bootstrap-style markup, custom styling) */
        #sendMessageModal .custom-control{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border:1.5px solid #C9D4E0;border-radius:6px;background:#fff;cursor:pointer;font-weight:600;font-size:13px;color:#34495E;margin:0 0 0 0;transition:border-color .15s,background .15s,box-shadow .15s}
        #sendMessageModal .custom-control:hover{border-color:#0072BC}
        #sendMessageModal .custom-control.active{border-color:#0D47A1;background:#EEF5FB;box-shadow:0 0 0 3px rgba(13,71,161,.10)}
        #sendMessageModal .custom-control .custom-control-input{appearance:none;-webkit-appearance:none;width:16px;height:16px;border:2px solid #98A9BA;border-radius:50%;margin:0;cursor:pointer;position:relative;flex-shrink:0;transition:border-color .15s}
        #sendMessageModal .custom-control .custom-control-input:checked{border-color:#0D47A1}
        #sendMessageModal .custom-control .custom-control-input:checked::after{content:'';position:absolute;inset:2px;border-radius:50%;background:#0D47A1}
        #sendMessageModal .custom-control .custom-control-label{cursor:pointer;user-select:none;font-weight:700}
        /* Footer buttons (spec: secondary cancel + orange send) */
        #sendMessageModal .modal-footer{display:flex;justify-content:flex-end;align-items:center;gap:10px;padding:12px 20px;flex-shrink:0}
        #sendMessageModal .btn{border:none;border-radius:6px;padding:10px 20px;font-size:12.5px;font-weight:700;cursor:pointer;text-transform:uppercase;letter-spacing:.4px;transition:background .15s,filter .15s;font-family:inherit}
        #sendMessageModal .btn-secondary{background:#F1F5F9;color:#34495E;border:1px solid #C0C0C0}
        #sendMessageModal .btn-secondary:hover{background:#E2E8F0}
        #sendMessageModal .btn-warning{background-color:#F39C12;color:#fff;min-width:130px}
        #sendMessageModal .btn-warning:hover{filter:brightness(1.08)}
        #sendMessageModal .btn-warning:disabled{opacity:.55;cursor:not-allowed;filter:none}
        #sendMessageModal .msg-loading{display:inline-flex;align-items:center;justify-content:center;gap:8px;color:#fff;font-size:12.5px;font-weight:700}
        #sendMessageModal .msg-spinner{width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:msgSpin .7s linear infinite;display:inline-block}
        @keyframes msgSpin{to{transform:rotate(360deg)}}
        #sendMessageModal .msg-feedback{margin-top:14px;display:none;padding:10px 14px;border-radius:6px;font-size:12.5px;font-weight:600}
        #sendMessageModal .msg-feedback.ok{display:block;background:#E9F7EF;border:1px solid #A9DFBF;color:#1E8449}
        #sendMessageModal .msg-feedback.err{display:block;background:#FDEDEC;border:1px solid #F5B7B1;color:#C0392B}

        /* ================= PATIENT PROFILE MODAL ================= */
        #patientProfileModal{display:none;position:fixed;inset:0;z-index:1100;background:rgba(11,29,58,.55);align-items:center;justify-content:center;padding:24px 16px;overflow-y:auto}
        #patientProfileModal.show{display:flex}
        #patientProfileModal .modal-dialog{width:min(760px,100%);margin:auto;max-height:92vh}
        #patientProfileModal .modal-content{background:#fff;border-radius:10px;overflow:hidden;border:none;box-shadow:0 10px 30px rgba(0,0,0,.2);display:flex;flex-direction:column;max-height:92vh;width:100%}
        #patientProfileModal .modal-header{color:#fff;padding:15px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-shrink:0}
        #patientProfileModal .modal-title{font-size:16px;font-weight:800;margin:0;display:flex;align-items:center;gap:8px;letter-spacing:.4px;text-transform:uppercase}
        #patientProfileModal .close{border:none;background:none;color:#fff;font-size:26px;line-height:1;opacity:.85;cursor:pointer;padding:0 4px}
        #patientProfileModal .close:hover{opacity:1}
        #patientProfileModal .modal-body{padding:0;background-color:#F8FAFC;overflow-y:auto;flex:1}
        #patientProfileModal .modal-footer{display:flex;justify-content:flex-end;align-items:center;gap:10px;padding:12px 20px;flex-shrink:0}
        #patientProfileModal .btn{border:none;border-radius:6px;padding:10px 20px;font-size:12.5px;font-weight:700;cursor:pointer;text-transform:uppercase;letter-spacing:.4px;transition:background .15s,filter .15s;font-family:inherit}
        #patientProfileModal .btn-primary{background-color:#0072BC;color:#fff}
        #patientProfileModal .btn-primary:hover{filter:brightness(1.08)}
        #patientProfileModal .btn-secondary{background:#F1F5F9;color:#34495E;border:1px solid #C0C0C0}
        #patientProfileModal .btn-secondary:hover{background:#E2E8F0}

        /* ---------- RESPONSIVE ---------- */
        @media (max-width:1180px){
            .hms-body{grid-template-columns:230px minmax(0,1fr)}
            .sidebar-right{grid-column:1/-1;border-left:none;border-top:1px solid #C0C0C0}
        }
        @media (max-width:860px){
            .hms-body{grid-template-columns:1fr}
            .sidebar-left{display:none}
            .sidebar-left.open{display:flex;border-right:none;border-bottom:1px solid #C0C0C0}
            .menu-toggle{display:flex}
            .hms-topbar{padding:0 14px}
        }
        @media (max-width:600px){
            .module-grid{grid-template-columns:1fr; padding:10px 12px}
            .module-card{min-height:82px; padding:14px 18px}
            .module-title{font-size:13px}
            .sys-station-grid{grid-template-columns:1fr; padding:0}
            .sys-card{min-height:82px; padding:14px 18px}
            .sys-card-title{font-size:13px}
            .sys-station-header{flex-direction:column; align-items:flex-start; gap:4px; padding:12px 14px}
            .sys-station-sub{text-align:left}
        }
    </style>
</head>
<body>

<header class="hms-topbar">
    <div class="topbar-left">
        <button class="menu-toggle" id="menu-toggle" aria-label="Toggle menu"><span></span><span></span><span></span></button>
        <span title="Eddie HealthCare Solutions - EHMS"><?php echo hcBrandLogo(42); ?></span>
        <h1 id="page-title">SYSTEM DASHBOARD</h1>
    </div>
    <div class="topbar-right">
        <div class="header-branding-right" style="text-align: right; display: flex; flex-direction: column; align-items: flex-end;">
            <span style="color: #0D47A1; font-weight: 900; font-size: 22px; letter-spacing: 0.5px; line-height: 1;">
                EHMS
            </span>
            <span style="color: #64748B; font-weight: 500; font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase; margin-top: 2px;">
                EDDIE HEALTHCARE SOLUTIONS
            </span>
        </div>
        <div class="topbar-actions">
            <button type="button" class="btn-header" id="top-home-btn">Home</button>
            <button type="button" class="btn-header" id="top-back-btn" title="Back to previous page / Dashboard">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width:13px;height:13px;vertical-align:-2px;margin-right:5px"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back
            </button>
            <button type="button" class="btn-header" id="top-password-btn">Password</button>
        </div>
    </div>
</header>

<div class="hms-body">

    <!-- ============ LEFT SIDEBAR : QUICK LINKS ============ -->
    <aside class="sidebar-left" id="sidebar-left">
        <div class="side-left-header">
            <div class="side-brand-row">
                <?php echo hcBrandLogo(36); ?>
                <div>
                    <div class="side-title">Quick Links</div>
                    <p>EHMS<br>EDDIE HEALTHCARE SOLUTIONS</p>
                </div>
            </div>
        </div>

        <nav aria-label="Quick Links" class="side-nav">
            <a href="#" class="nav-link active" data-page="dashboard">Control Panel</a>
            <a href="#" class="nav-link" data-page="appointment-calendar">Appointment Calendar</a>
            <a href="#" class="nav-link" data-page="messages">Messages &amp; Alerts <span class="nav-msg-badge" id="msgs-nav-badge" style="display:none">0</span></a>
            <a href="#" class="nav-link" data-page="records">Patient Record Management</a>
            <a href="#" class="nav-link" data-page="admissions">Admissions</a>
            <a href="#" class="nav-link" data-page="prices">Drugs Dispense</a>
            <a href="#" class="nav-link" data-page="reports">View Alerts</a>
            <a href="#" class="nav-link dl-quick-link" data-page="dhims-report" style="font-size: 12px; font-weight: 500;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" flex-shrink="0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span>DHIMS Report</span>
                <span style="background:#F59E0B;color:#7C2D12;font-size:9px;font-weight:700;padding:1px 6px;border-radius:8px;margin-left:auto;">QUICK</span>
            </a>
            <a href="#" class="nav-link" data-page="lab-management">Lab Management</a>
            <a href="#" class="nav-link" data-page="account-management">Account Management</a>
            <a href="#" class="nav-link" data-page="users">User Management</a>
            <a href="#" class="nav-link" data-page="administrator">Administrator</a>
            <a href="#" class="nav-link" data-page="wards">Departments</a>
            <a href="#" class="nav-link" data-page="system-activities">System Activities</a>
        </nav>

        <div class="sidebar-action">
            <button type="button" class="btn-send" id="send-message-btn">Send Message</button>
        </div>

        <div class="widget widget-days">
            <div class="widget-title">Days Alerts</div>
            <div class="widget-body" id="days-alert-body">
                <div class="days-alert-num" id="days-alert-num">0</div>
                <div class="days-alert-label" id="days-alert-label">messages / alerts today</div>
                <button type="button" class="alert-open-btn" id="alert-open-msgs" style="display:none">Open Messages →</button>
            </div>
        </div>

        <div class="widget widget-login">
            <div class="widget-title">Login Status</div>
            <div class="widget-body">
                <div class="status-row"><span class="status-label">Login As</span><span class="status-value"><?php echo $displayName; ?></span></div>
                <div class="status-row"><span class="status-label">User Type</span><span class="status-value"><?php echo $displayRole; ?></span></div>
                <div class="status-row"><span class="status-label">Logout Timer</span><span class="status-value" id="lblSessionTimer" data-login-time="<?php echo $loginTimestamp; ?>"><?php echo $elapsedSession; ?></span></div>
                <div class="status-row"><span class="status-label">Since</span><span class="status-value"><?php echo $sinceTimestamp; ?></span></div>
                <div class="status-row"><span class="status-label">Clinic Name</span><span class="status-value"><?php echo $clinicName; ?></span></div>
                <button type="button" class="logout-btn" id="sidebar-logout-btn">Logout</button>
            </div>
        </div>
    </aside>

    <!-- ============ CENTER WORKSPACE : MODULE CARDS (2-COLUMN GRID) ============ -->
    <main class="main-content">
        <div class="dashboard-fill">
            <div class="central-dash" id="page-content">
                <!-- SYSTEM DASHBOARD GRID CONTAINER -->
                <div class="container-fluid p-3">

                    <!-- 14-MODULE 2-COLUMN UNIFORM GRID -->
                    <div class="row g-3">

                        <!-- 1. APPOINTMENT CALENDAR -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('appointment_calendar')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(0, 114, 188, 0.08); border-radius: 6px;">
                                        <!-- Calendar Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0072BC" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><text x="8" y="18" font-size="7" font-weight="bold" fill="#0072BC" stroke="none">15</text></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">APPOINTMENT CALENDAR</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. PATIENT RECORDS -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('patient_records')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(0, 114, 188, 0.08); border-radius: 6px;">
                                        <!-- Patient Folder Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0072BC" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path><line x1="12" y1="11" x2="12" y2="17"></line><line x1="9" y1="14" x2="15" y2="14"></line></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">PATIENT RECORDS</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. ADMINISTRATOR -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('administrator')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(55, 65, 81, 0.08); border-radius: 6px;">
                                        <!-- PC Admin Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">ADMINISTRATOR</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. ACCOUNTS MANAGEMENT -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('accounts_management')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.08); border-radius: 6px;">
                                        <!-- Accounts Chart Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line><polyline points="4 6 9 2 15 8 20 2"></polyline></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">ACCOUNTS MANAGEMENT</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5. INVESTIGATIONS -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('investigations')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(217, 119, 6, 0.08); border-radius: 6px;">
                                        <!-- Test Tubes Lab Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><path d="M10 2v7.51L4.53 17.92A2 2 0 0 0 6.24 21h11.52a2 2 0 0 0 1.71-3.08L14 9.51V2"></path><line x1="8.5" y1="2" x2="15.5" y2="2"></line></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">INVESTIGATIONS</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 6. PHARMACY MANAGEMENT -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('pharmacy_management')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(239, 68, 68, 0.08); border-radius: 6px;">
                                        <!-- Pharmacy Bottle & Cross Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2"><rect x="6" y="7" width="12" height="14" rx="2"></rect><path d="M9 3h6v4H9z"></path><line x1="12" y1="11" x2="12" y2="17"></line><line x1="9" y1="14" x2="15" y2="14"></line></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">PHARMACY MANAGEMENT</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 7. MIS -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('mis')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(0, 114, 188, 0.08); border-radius: 6px;">
                                        <!-- MIS Bar Chart Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0072BC" stroke-width="2"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">MIS</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 8. DHIMS REPORT -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('dhims_report')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: #E0F2FE; border-radius: 6px;">
                                        <!-- DHIMS Chart/Document Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">DHIMS REPORT</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 9. IPD MANAGEMENT -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('ipd_management')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(15, 45, 89, 0.08); border-radius: 6px;">
                                        <!-- ID Card Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0F2D59" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"></rect><circle cx="9" cy="10" r="2"></circle><line x1="15" y1="8" x2="17" y2="8"></line><line x1="15" y1="12" x2="17" y2="12"></line><path d="M6 16a3 3 0 0 1 6 0"></path></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">IPD MANAGEMENT</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 10. DEPARTMENTS -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('departments')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(0, 114, 188, 0.08); border-radius: 6px;">
                                        <!-- Document Clipboard Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0072BC" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><line x1="9" y1="12" x2="15" y2="12"></line><line x1="9" y1="16" x2="15" y2="16"></line></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">DEPARTMENTS</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 11. SYSTEM ACTIVITIES -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('system_activities')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(0, 114, 188, 0.08); border-radius: 6px;">
                                        <!-- Checklist Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0072BC" stroke-width="2"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">SYSTEM ACTIVITIES</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 12. NHIA CLAIM -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('nhia_claim')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.08); border-radius: 6px;">
                                        <!-- NHIA Shield/Home Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">NHIA CLAIM</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 13. RADIOLOGY -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('radiology')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.08); border-radius: 6px;">
                                        <!-- Monitor / Screen Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">RADIOLOGY</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 14. MESSAGES & ALERTS -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('messages_alerts')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.08); border-radius: 6px;">
                                        <!-- Message Bubble Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path><circle cx="17" cy="7" r="2" fill="#EF4444" stroke="none"></circle></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">MESSAGES &amp; ALERTS</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 15. INVENTORY MANAGEMENT -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100 hms-module-card" style="border-radius: 8px; background: #FFFFFF; cursor: pointer;" onclick="loadModuleTab('inventory_management')">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.08); border-radius: 6px;">
                                        <!-- Inventory Box Icon -->
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                    </div>
                                    <div class="text-center flex-grow-1 me-4">
                                        <span class="font-weight-bold text-uppercase" style="color: #0F2D59; font-size: 13px; letter-spacing: 0.5px;">INVENTORY MANAGEMENT</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- System Footer Strip (old EHMS portal contact bar) -->
                <div class="system-footer-strip">
                    Email: <strong>eddie.kay@gmail.com</strong> | Phone: <strong>0547 49 74 02</strong>
                </div>
            </div>
        </div>
    </main>

    <!-- ============ RIGHT SIDEBAR : PATIENT SEARCH ============ -->
    <aside class="sidebar-right">
        <div class="side-right-header">
            <div class="side-brand-row">
                <?php echo hcBrandLogo(34); ?>
                <h2>Patient Search</h2>
            </div>
        </div>

        <div class="search-fields">
            <div class="search-row">
                <label for="p-name">By Patient Name</label>
                <input id="p-name" type="text" placeholder="Full name">
                <button type="button" class="btn-search" title="Search" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </div>
            <div class="search-row">
                <label for="p-no">By Patient No.</label>
                <input id="p-no" type="text" placeholder="Patient number">
                <button type="button" class="btn-search" title="Search" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </div>
            <div class="search-row">
                <label for="p-nhis">By NHIS Number</label>
                <input id="p-nhis" type="text" placeholder="NHIS number">
                <button type="button" class="btn-search" title="Search" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </div>
            <div class="search-row">
                <label for="p-area">By Area</label>
                <input id="p-area" type="text" placeholder="Residential area">
                <button type="button" class="btn-search" title="Search" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </div>
            <div class="search-row">
                <label for="p-mobile">By Mobile</label>
                <input id="p-mobile" type="text" placeholder="Mobile number">
                <button type="button" class="btn-search" title="Search" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </div>
            <div class="search-row">
                <label for="p-moh">By MOH No</label>
                <input id="p-moh" type="text" placeholder="MOH number">
                <button type="button" class="btn-search" title="Search" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </div>
            <div class="search-row">
                <label for="p-contact">By Contact Name</label>
                <input id="p-contact" type="text" placeholder="Contact">
                <button type="button" class="btn-search" title="Search" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </div>
            <div class="search-row">
                <label for="p-cno">By Contact Number</label>
                <input id="p-cno" type="text" placeholder="Contact number">
                <button type="button" class="btn-search" title="Search" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </div>
            <div class="search-row">
                <label for="p-cemail">By Contact Email</label>
                <input id="p-cemail" type="text" placeholder="Contact email">
                <button type="button" class="btn-search" title="Search" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </div>
            <div class="search-row">
                <label for="p-tag">By Patient Tag</label>
                <input id="p-tag" type="text" placeholder="Tag">
                <button type="button" class="btn-search" title="Search" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </div>
        </div>

        <!-- Search Results Panel -->
        <div class="search-results-panel" id="patientSearchResults" style="display:none">
            <div class="results-header">
                <span id="resultsCount">0</span> patient(s) found
                <button type="button" class="results-close" id="clearSearchResults" title="Clear">&times;</button>
            </div>
            <div class="results-list" id="resultsList"></div>
        </div>

        <div class="side-actions">
            <button type="button" class="btn-navy">Support</button>
            <button type="button" class="btn-navy">Help</button>
        </div>

        <div class="server-msgs">
            <div class="widget-title">System Messages</div>
            <div id="system-msgs-body">
                <div class="msg-item"><span class="msg-dot" style="background:#95A5A6;"></span>Loading system messages…</div>
            </div>
            <button type="button" class="alert-open-btn" id="sys-msgs-open" style="display:none;margin:10px 12px;width:calc(100% - 24px);text-align:center">Open Messages &amp; Alerts →</button>
        </div>

        </aside>

</div>

<!-- ============ SEND MESSAGE MODAL (SIDEBAR "SEND MESSAGE" QUICK ACTION) ============ -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 10px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">

            <!-- Modal Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #0D47A1, #0072BC); color: white; padding: 15px 20px;">
                <h5 class="modal-title font-weight-bold" style="font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    SEND PATIENT / USER MESSAGE
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form id="sendMessageForm" autocomplete="off" novalidate>
                <div class="modal-body" style="padding: 20px; background-color: #F8FAFC;">

                    <!-- Recipient Type Switcher -->
                    <div class="form-group mb-3">
                        <label class="font-weight-bold" style="color: #0F2D59; font-size: 12px; letter-spacing: 0.5px;">RECIPIENT TYPE *</label>
                        <div style="display: flex; gap: 14px; flex-wrap: wrap; margin-top: 4px;">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="typePatient" name="recipient_type" value="patient" class="custom-control-input" checked>
                                <label class="custom-control-label font-weight-bold" for="typePatient">Patient</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="typeStaff" name="recipient_type" value="staff" class="custom-control-input">
                                <label class="custom-control-label font-weight-bold" for="typeStaff">System User / Staff</label>
                            </div>
                        </div>
                    </div>

                    <!-- Select Recipient Dropdown (Searchable Select2 / Standard Select) -->
                    <div class="form-group mb-3">
                        <label for="selectRecipient" class="font-weight-bold" style="color: #0F2D59; font-size: 12px;">SELECT PATIENT OR USER *</label>
                        <div class="recipient-search-wrap" style="position: relative;">
                            <input type="text" id="msgSearchBox" class="form-control" placeholder="Search by name, hospital number, role or department..." autocomplete="off" style="height: 40px; border-radius: 6px 6px 0 0; border-bottom: none; padding-right: 34px;">
                            <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #64748B; pointer-events: none; display: flex;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                            </span>
                        </div>
                        <select id="selectRecipient" name="recipient_id" class="form-control custom-select" required style="height: 42px; border-radius: 0 0 6px 6px; font-weight: 600;">
                            <option value="">-- Loading Patients/Users... --</option>
                        </select>
                    </div>

                    <!-- Optional Patient Context Tag -->
                    <div class="form-group mb-3" id="patientContextGroup">
                        <label for="patientContext" class="font-weight-bold" style="color: #0F2D59; font-size: 12px;">REGARDING PATIENT (OPTIONAL CONTEXT)</label>
                        <select id="patientContext" name="patient_context_id" class="form-control custom-select" style="height: 42px; border-radius: 6px;">
                            <option value="">-- Select Related Patient (if applicable) --</option>
                        </select>
                    </div>

                    <!-- Message Subject & Channel -->
                    <div class="row">
                        <div class="col-md-8 form-group mb-3">
                            <label for="msgSubject" class="font-weight-bold" style="color: #0F2D59; font-size: 12px;">SUBJECT / TITLE *</label>
                            <input type="text" id="msgSubject" name="subject" class="form-control" placeholder="e.g. Lab Results Ready / Appointment Reminder" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label for="msgChannel" class="font-weight-bold" style="color: #0F2D59; font-size: 12px;">CHANNEL *</label>
                            <select id="msgChannel" name="channel" class="form-control custom-select">
                                <option value="internal">Internal System Notice</option>
                                <option value="sms">SMS Alert</option>
                                <option value="email">Email Notification</option>
                            </select>
                        </div>
                    </div>

                    <!-- Message Content Textarea -->
                    <div class="form-group mb-2">
                        <label for="msgContent" class="font-weight-bold" style="color: #0F2D59; font-size: 12px;">MESSAGE BODY *</label>
                        <textarea id="msgContent" name="message" class="form-control" rows="4" placeholder="Type your message or clinical instructions here..." required></textarea>
                    </div>

                    <!-- Feedback -->
                    <div class="msg-feedback" id="msgFeedback"></div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer" style="background-color: #EDF2F7; padding: 12px 20px;">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning font-weight-bold text-white" id="msgSendBtn" style="background-color: #F39C12; border: none; min-width: 130px;">
                        SEND MESSAGE
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ============ PATIENT PROFILE MODAL (SEARCH RESULTS "VIEW") ============ -->
<div class="modal fade" id="patientProfileModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 10px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">

            <!-- Modal Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #0D47A1, #0072BC); color: white; padding: 15px 20px;">
                <h5 class="modal-title font-weight-bold" style="font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    PATIENT PROFILE
                </h5>
                <button type="button" class="close text-white" id="closePatientProfileBtn" aria-label="Close" style="opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" style="padding: 0; background-color: #F8FAFC;">

                <!-- Profile banner -->
                <div class="pp-banner">
                    <div class="pp-avatar" id="pp-avatar">—</div>
                    <div class="pp-head">
                        <div class="pp-name" id="pp-name">—</div>
                        <div class="pp-sub" id="pp-sub">—</div>
                        <span class="pp-hn-badge" id="pp-hn">—</span>
                    </div>
                </div>

                <!-- Details grid -->
                <div class="pp-grid">
                    <div class="pp-field"><span>DATE OF BIRTH</span><b id="pp-dob">—</b></div>
                    <div class="pp-field"><span>AGE / GENDER</span><b id="pp-agegender">—</b></div>
                    <div class="pp-field"><span>BLOOD GROUP</span><b id="pp-blood">—</b></div>
                    <div class="pp-field"><span>PHONE</span><b id="pp-phone">—</b></div>
                    <div class="pp-field"><span>EMAIL</span><b id="pp-email">—</b></div>
                    <div class="pp-field"><span>RESIDENTIAL ADDRESS</span><b id="pp-address">—</b></div>
                    <div class="pp-field"><span>NHIS NUMBER</span><b id="pp-nhis">—</b></div>
                    <div class="pp-field"><span>GHA / MOH NUMBER</span><b id="pp-gha">—</b></div>
                    <div class="pp-field"><span>SPONSOR</span><b id="pp-sponsor">—</b></div>
                    <div class="pp-field"><span>EMERGENCY CONTACT</span><b id="pp-emergency">—</b></div>
                    <div class="pp-field"><span>REGISTERED ON</span><b id="pp-registered">—</b></div>
                    <div class="pp-field"><span>RECORD STATUS</span><b id="pp-status">—</b></div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="padding: 12px 20px; background: #fff; border-top: 1px solid #EDF1F5;">
                <button type="button" class="btn btn-primary font-weight-bold" id="pp-go-records" style="background-color:#0072BC;border:none;display:none">Open Patient Records →</button>
                <button type="button" class="btn btn-secondary font-weight-bold" id="pp-close-btn">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/date-picker.js"></script>
<script>
/* Server login time (epoch ms) injected by PHP — consumed by initGMTDashboardTimer() */
window.SERVER_GMT_LOGIN_TIME = <?php echo $loginTimestamp * 1000; ?>;
/* ============ UNIFORM FORMAL DATE FORMATTING ============ */
var HMS_MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
function hmsPad2(n){ return n < 10 ? '0' + n : '' + n; }
function hmsToDate(v){
    if (v instanceof Date) return v;
    if (v === null || v === undefined || v === '') return null;
    var d = (String(v).indexOf('T') >= 0) ? new Date(String(v)) : new Date(String(v).replace(' ', 'T'));
    return isNaN(d) ? null : d;
}
function fmtDate(v){
    var d = hmsToDate(v);
    if (!d) return (v === null || v === undefined || v === '') ? '-' : String(v);
    return hmsPad2(d.getDate()) + '-' + HMS_MONTHS[d.getMonth()] + '-' + d.getFullYear();
}
function fmtDateTime(v){
    var d = hmsToDate(v);
    if (!d) return (v === null || v === undefined || v === '') ? '-' : String(v);
    return fmtDate(d) + ' ' + hmsPad2(d.getHours()) + ':' + hmsPad2(d.getMinutes());
}
window.fmtDate = fmtDate;
window.fmtDateTime = fmtDateTime;
(function(){
    // Mobile menu toggle (left sidebar)
    var menuToggle=document.getElementById('menu-toggle');
    var sidebar=document.getElementById('sidebar-left');
    if(menuToggle&&sidebar){
        menuToggle.addEventListener('click',function(){ sidebar.classList.toggle('open'); });
    }
    // Logout
    function doLogout(){
        fetch('../backend/api/auth.php?action=logout',{method:'POST'}).then(function(){window.location.href='index.php';}).catch(function(){window.location.href='index.php';});
    }
    var lb=document.getElementById('logout-btn');
    var sb=document.getElementById('sidebar-logout-btn');
    if(lb) lb.addEventListener('click',doLogout);
    if(sb) sb.addEventListener('click',doLogout);
    // Top-right header actions : HOME / BACK / PASSWORD
    var homeBtn=document.getElementById('top-home-btn');
    var backBtn=document.getElementById('top-back-btn');
    var pwdBtn=document.getElementById('top-password-btn');
    if(homeBtn) homeBtn.addEventListener('click',function(){ navigateTo('dashboard'); });
    // EHMS back navigation : SPA history stack -> browser history -> dashboard fallback
    function goBackPage(){
        if(pageStack.length){
            backNav=true;
            loadPage(pageStack.pop());
            return;
        }
        if(window.history.length>1 && document.referrer!==''){
            window.history.back();
            return;
        }
        navigateTo('dashboard');
    }
    window.goBackPage=goBackPage; /* exposed so module page topbars can reuse it */
    if(backBtn) backBtn.addEventListener('click',goBackPage);
    /* Delegated support for any #btnBackNavigation / .btn-back-nav element */
    document.addEventListener('click',function(e){
        var t=(e.target && e.target.closest)?e.target.closest('#btnBackNavigation, .btn-back-nav'):null;
        if(t){ e.preventDefault(); goBackPage(); }
    });
    if(pwdBtn) pwdBtn.addEventListener('click',function(){ navigateTo('users'); });
    // Login duration timer : counts UP from the session login timestamp (currentTime - loginTime)
    var timerEl=document.getElementById('login-timer');
    if(timerEl){
        var rawLogin=parseInt(timerEl.getAttribute('data-login-time'),10);
        var loginTime=isNaN(rawLogin) ? Date.now() : rawLogin*1000;
        var lastTick=null;
        function renderTimer(){
            var total=Math.max(0,Math.floor((Date.now()-loginTime)/1000));
            var h=Math.floor(total/3600),m=Math.floor((total%3600)/60),s=total%60;
            var out=(h<10?'0'+h:h)+':'+(m<10?'0'+m:m)+':'+(s<10?'0'+s:s);
            if(out!==lastTick){ timerEl.textContent=out; lastTick=out; }
        }
        renderTimer();
        setInterval(renderTimer,1000);
    }
    // SPA navigation
    var navLinks=document.querySelectorAll('.side-nav .nav-link');
    var pageTitle=document.getElementById('page-title');
    var pageContent=document.getElementById('page-content');
    var initialGridHTML=pageContent ? pageContent.innerHTML : '';
    // System Support Contact Banner — also appended to sub-module pages (bottom of workspace)
    var supportBannerEl=document.querySelector('.hms-system-footer-bar') || document.querySelector('.system-footer-strip');
    var supportBannerHTML=supportBannerEl ? supportBannerEl.outerHTML : '';
    // SPA page-history stack used by the header BACK button: returns to the
    // interface that was open before the currently loaded page, else Dashboard.
    var pageStack=[];
    var currentPage='dashboard';
    var backNav=false;
    function formatTitle(p){return p.replace(/-/g,' ').replace(/\b\w/g,function(c){return c.toUpperCase();});}
    // Execute <script> blocks injected from fetched pages (innerHTML does not run them)
    function runPageScripts(root){
        var nodes=root.querySelectorAll('script');
        for(var i=0;i<nodes.length;i++){
            (function(scr){
                if(scr.src){
                    var s=document.createElement('script');
                    s.src=scr.src;
                    scr.parentNode.replaceChild(s,scr);
                }else{
                    try{ (0,eval)(scr.textContent); }catch(err){ console.error('Page script error:',err); }
                    scr.parentNode.removeChild(scr);
                }
            })(nodes[i]);
        }
    }
    function loadPage(page, linkEl){
        if(page!==currentPage){
            if(!backNav && currentPage!=='dashboard') pageStack.push(currentPage);
            currentPage=page;
            backNav=false;
        }
        navLinks.forEach(function(l){l.classList.remove('active');});
        if(linkEl) linkEl.classList.add('active');
        if(sidebar) sidebar.classList.remove('open');
        if(page==='dashboard'){
            if(pageTitle) pageTitle.textContent='SYSTEM DASHBOARD';
            if(pageContent){ pageContent.className='central-dash'; pageContent.innerHTML=initialGridHTML; }
            return;
        }
        if(pageContent) pageContent.className='page-slot';
        if(pageTitle) pageTitle.textContent=formatTitle(page).toUpperCase();
        if(pageContent) pageContent.innerHTML='<div style="background:#fff;border:1px solid #C0C0C0;border-radius:3px;padding:28px;text-align:center;color:#6c757d">Loading '+formatTitle(page)+'...</div>';
        fetch('pages/'+page+'.php').then(function(r){ if(!r.ok) throw new Error('Status '+r.status); return r.text();}).then(function(html){
            if(pageContent) pageContent.innerHTML='<div class="main-content-card-wrapper">'+html+supportBannerHTML+'</div>';
            runPageScripts(pageContent);
            var initFn=window['init'+page.charAt(0).toUpperCase()+page.slice(1).replace(/-([a-z])/g,function(_,c){return c.toUpperCase();})];
            if(typeof initFn==='function') initFn();
            if(window.initCustomDatePickers) initCustomDatePickers(pageContent);
        }).catch(function(err){
            if(pageContent) pageContent.innerHTML='<div style="background:#fff;border:1px solid #C0C0C0;border-radius:3px;padding:24px;"><p style="color:#C0392B;font-weight:700">Failed to load '+formatTitle(page)+': '+err.message+'</p></div>';
        });
    }
    window.loadPage=loadPage; /* expose for navigateTo fallback — pages without a sidebar link */
    /* Module-card router : maps uniform grid module ids to the SPA page keys and navigates.
       Kept global so inline onclick="loadModuleTab(...)" hooks on the dashboard grid work. */
    window.loadModuleTab=function(moduleId){
        var map={
            appointment_calendar:'appointment-calendar',
            patient_records:'records',
            administrator:'administrator',
            accounts_management:'account-management',
            investigations:'lab-management',
            pharmacy_management:'prices',
            mis:'reports',
            dhims_report:'dhims-report',
            ipd_management:'ipd-management',
            departments:'departments',
            system_activities:'system-activities',
            nhia_claim:'sponsors',
            radiology:'vitals',
            messages_alerts:'messages',
            inventory_management:'inventory_management'
        };
        var page=map[moduleId] || moduleId;
        var target=document.querySelector('.side-nav .nav-link[data-page="'+page+'"]');
        loadPage(page, target || null);
    };
    navLinks.forEach(function(link){
        link.addEventListener('click',function(e){
            e.preventDefault();
            loadPage(this.getAttribute('data-page'), this);
        });
    });
    // Grab/drag visual state (teal highlight, white background maintained)
    document.addEventListener('mousedown', function(e){
        var c = e.target.closest('.module-card, .sys-card, .dashboard-card');
        if (c) c.classList.add('dragging');
    });
    document.addEventListener('mouseup', function(e){
        var c = e.target.closest('.module-card, .sys-card, .dashboard-card');
        if (c) c.classList.remove('dragging');
    });
    // Module cards, lab cards & admin buttons drive the same SPA navigation
    document.addEventListener('click',function(e){
        var card=e.target.closest('.module-card, .sys-card, .dashboard-card, .admin-btn');
        if(card && card.getAttribute('data-page')){
            e.preventDefault();
            var pg=card.getAttribute('data-page');
            var target=document.querySelector('.side-nav .nav-link[data-page="'+pg+'"]');
            loadPage(pg, target || null);
        }
    });
    // ================= SEND MESSAGE MODAL (orange SEND MESSAGE quick action) =================
    var sendModal=document.getElementById('sendMessageModal');
    var sendForm=document.getElementById('sendMessageForm');
    var selectRecipient=document.getElementById('selectRecipient');
    var msgSearchBox=document.getElementById('msgSearchBox');
    var patientContext=document.getElementById('patientContext');
    var msgSubject=document.getElementById('msgSubject');
    var msgChannel=document.getElementById('msgChannel');
    var msgContent=document.getElementById('msgContent');
    var msgSendBtn=document.getElementById('msgSendBtn');
    var msgFeedback=document.getElementById('msgFeedback');
    var recipientCache={patient:[],staff:[]};
    var currentRecipientType='patient';

    function showMsgFeedback(msg,ok){
        if(!msgFeedback) return;
        msgFeedback.textContent=msg||'';
        msgFeedback.className='msg-feedback '+(ok?'ok':'err');
    }
    function clearMsgFeedback(){ if(msgFeedback){ msgFeedback.textContent=''; msgFeedback.className='msg-feedback'; } }
    function setMsgLoading(on){
        if(!msgSendBtn) return;
        if(on){ msgSendBtn.disabled=true; msgSendBtn.innerHTML='<span class="msg-loading"><span class="msg-spinner"></span> Sending…</span>'; }
        else{ msgSendBtn.disabled=false; msgSendBtn.innerHTML='SEND MESSAGE'; }
    }
    function markRadio(type){
        document.querySelectorAll('#sendMessageModal input[name="recipient_type"]').forEach(function(r){
            var box=r.closest('.custom-control');
            if(box){ (r.value===type) ? box.classList.add('active') : box.classList.remove('active'); }
        });
    }
    function buildCombined(){
        var all=[];
        (recipientCache.patient||[]).forEach(function(r){
            var name=r.name||r.label||'';
            all.push({
                id:r.id,
                _type:'patient',
                label:(r.hospital_number||'P'+r.id)+' — '+name,
                searchText:((r.hospital_number||'')+' '+name+' '+(r.label||'')).toLowerCase()
            });
        });
        (recipientCache.staff||[]).forEach(function(r){
            var name=r.name||r.label||'';
            all.push({
                id:r.id,
                _type:'staff',
                label:name,
                searchText:(name+' '+(r.role||'')+' '+(r.department||'')+' '+(r.username||'')+' '+(r.label||'')).toLowerCase()
            });
        });
        return all;
    }
    var allRecipients=[];
    var recipientsLoaded=false;
    var mixedView=false;
    function populateSelect(view){
        if(!selectRecipient) return;
        selectRecipient.innerHTML='';
        if(!recipientsLoaded){
            var ph=document.createElement('option');
            ph.value=''; ph.textContent='-- Loading Patients/Users... --';
            selectRecipient.appendChild(ph);
            selectRecipient.disabled=true;
            if(msgSearchBox) msgSearchBox.disabled=true;
            return;
        }
        var ph=document.createElement('option');
        ph.value='';
        if(view && view.length>0) ph.textContent='— Select a recipient —';
        else if(msgSearchBox && msgSearchBox.value.trim()!=='') ph.textContent='No matches in the whole system for "'+msgSearchBox.value.trim()+'"';
        else ph.textContent='— No recipients found —';
        selectRecipient.appendChild(ph);
        (view||[]).forEach(function(r){
            var o=document.createElement('option');
            o.value=r.id; o._type=r._type;
            o.textContent=(mixedView ? (r._type==='patient'?'[PATIENT] ':'[STAFF] ') : '')+r.label;
            selectRecipient.appendChild(o);
        });
        selectRecipient.disabled=!(view && view.length>0);
        if(msgSearchBox) msgSearchBox.disabled=false;
    }
    function renderRecipients(){
        if(!selectRecipient) return;
        var q=msgSearchBox ? msgSearchBox.value.trim().toLowerCase() : '';
        var view;
        if(q!==''){
            mixedView=true;
            view=allRecipients.filter(function(r){ return r.searchText.indexOf(q)!==-1; });
        }else{
            mixedView=false;
            view=allRecipients.filter(function(r){ return r._type===currentRecipientType; });
        }
        populateSelect(view);
    }
    function populatePatientContext(list){
        if(!patientContext) return;
        patientContext.innerHTML='';
        var ph=document.createElement('option');
        ph.value=''; ph.textContent='-- Select Related Patient (if applicable) --';
        patientContext.appendChild(ph);
        (list||[]).forEach(function(r){
            var o=document.createElement('option');
            o.value=r.id; o.textContent=(r.hospital_number||'P'+r.id)+' — '+(r.name||r.label);
            patientContext.appendChild(o);
        });
    }
    function loadRecipients(force){
        if(!force && recipientsLoaded){
            renderRecipients();
            return;
        }
        if(selectRecipient){
            selectRecipient.disabled=true;
            selectRecipient.innerHTML='';
            var ph=document.createElement('option');
            ph.value=''; ph.textContent='-- Loading Patients/Users... --';
            selectRecipient.appendChild(ph);
            if(msgSearchBox) msgSearchBox.disabled=true;
        }
        Promise.all([
            fetch('../backend/api/messages.php?action=recipients&type=patient').then(function(r){ if(!r.ok) throw new Error('Status '+r.status); return r.json(); }),
            fetch('../backend/api/messages.php?action=recipients&type=staff').then(function(r){ if(!r.ok) throw new Error('Status '+r.status); return r.json(); })
        ]).then(function(res){
            if(!res[0] || !res[0].success || !res[1] || !res[1].success) throw new Error('Failed to load recipients');
            recipientCache.patient=res[0].recipients||[];
            recipientCache.staff=res[1].recipients||[];
            allRecipients=buildCombined();
            recipientsLoaded=true;
            renderRecipients();
            clearMsgFeedback();
        }).catch(function(err){
            showMsgFeedback('Could not load recipients: '+err.message,'err');
        });
    }
    function loadPatientContext(){
        if(!patientContext) return;
        if(recipientCache.patient && recipientCache.patient.length>0){ populatePatientContext(recipientCache.patient); return; }
        fetch('../backend/api/messages.php?action=recipients&type=patient')
            .then(function(r){ if(!r.ok) throw new Error('Status '+r.status); return r.json(); })
            .then(function(data){
                if(!data || !data.success) throw new Error((data&&data.error)||'Failed to load patients');
                if(recipientCache.patient && recipientCache.patient.length===0) recipientCache.patient=data.recipients||[];
                populatePatientContext(recipientCache.patient);
            })
            .catch(function(){ /* optional context — silent fail */ });
    }
    function openSendMessageModal(){
        if(!sendModal) return;
        sendForm.reset();
        var def=document.getElementById('typePatient');
        if(def){ def.checked=true; }
        currentRecipientType='patient';
        markRadio('patient');
        if(msgSearchBox){ msgSearchBox.value=''; }
        clearMsgFeedback();
        setMsgLoading(false);
        sendModal.classList.add('show');
        document.body.style.overflow='hidden';
        loadRecipients(true);
        loadPatientContext();
        setTimeout(function(){ if(msgSearchBox && !msgSearchBox.disabled) msgSearchBox.focus(); else if(selectRecipient && !selectRecipient.disabled) selectRecipient.focus(); }, 50);
    }
    function closeSendMessageModal(){
        if(!sendModal) return;
        sendModal.classList.remove('show');
        document.body.style.overflow='';
        setMsgLoading(false);
    }
    window.openSendMessageModal=openSendMessageModal;
    window.closeSendMessageModal=closeSendMessageModal;

    // Orange SEND MESSAGE quick action opens the modal
    var sendBtn=document.getElementById('send-message-btn');
    if(sendBtn){ sendBtn.addEventListener('click',function(e){ e.preventDefault(); openSendMessageModal(); }); }

    // Recipient type switch repopulates the dropdown
    document.querySelectorAll('#sendMessageModal input[name="recipient_type"]').forEach(function(r){
        r.addEventListener('change',function(){
            currentRecipientType=r.value;
            markRadio(currentRecipientType);
            if(msgSearchBox){ msgSearchBox.value=''; }
            renderRecipients();
            clearMsgFeedback();
        });
    });
    // Live search filter across the whole system (patients + users/staff)
    if(msgSearchBox){
        msgSearchBox.addEventListener('input',function(){ renderRecipients(); });
    }
    // Close: data-dismiss buttons, backdrop click, Escape
    if(sendModal){
        sendModal.querySelectorAll('[data-dismiss="modal"]').forEach(function(b){
            b.addEventListener('click',function(e){ e.preventDefault(); closeSendMessageModal(); });
        });
        sendModal.addEventListener('click',function(e){
            if(e.target===sendModal || (e.target.classList && e.target.classList.contains('modal-dialog'))){
                closeSendMessageModal();
            }
        });
        document.addEventListener('keydown',function(e){
            if(e.key==='Escape' && sendModal.classList.contains('show')) closeSendMessageModal();
        });
    }
    // Submit: validate + persist via backend/api/messages.php
    if(sendForm){
        sendForm.addEventListener('submit',function(e){
            e.preventDefault();
            if(!selectRecipient || !selectRecipient.value){ showMsgFeedback('Please select a recipient.','err'); return; }
            var subject=msgSubject ? msgSubject.value.trim() : '';
            var message=msgContent ? msgContent.value.trim() : '';
            if(subject===''){ showMsgFeedback('Please enter a subject.','err'); if(msgSubject) msgSubject.focus(); return; }
            if(message===''){ showMsgFeedback('Please enter a message.','err'); if(msgContent) msgContent.focus(); return; }
            setMsgLoading(true);
            clearMsgFeedback();
            var channel=msgChannel ? (msgChannel.value||'internal') : 'internal';
            var contextId=patientContext ? patientContext.value : '';
            var selOption=selectRecipient.selectedOptions && selectRecipient.selectedOptions[0];
            var selType=(selOption && selOption._type) ? selOption._type : currentRecipientType;
            var payload={
                recipient_type:selType,
                recipient_id:selectRecipient.value,
                subject:subject,
                message:message,
                channel:channel,
                patient_context_id:contextId || null
            };
            fetch('../backend/api/messages.php?action=send',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify(payload)
            }).then(function(r){ return r.json(); }).then(function(data){
                setMsgLoading(false);
                if(data && data.success){
                    showMsgFeedback('Message sent successfully.','ok');
                    sendForm.reset();
                    if(selectRecipient) selectRecipient.selectedIndex=0;
                    if(patientContext) patientContext.selectedIndex=0;
                    if(msgChannel) msgChannel.value='internal';
                    if(msgSearchBox){ msgSearchBox.value=''; renderRecipients(); }
                    setTimeout(function(){ closeSendMessageModal(); clearMsgFeedback(); }, 1400);
                }else{
                    showMsgFeedback((data&&data.error)||'Failed to send message.','err');
                }
            }).catch(function(err){
                setMsgLoading(false);
                showMsgFeedback('Network error: '+err.message,'err');
            });
        });
    }
    // ================= DAYS ALERTS (LEFT COUNT) + SYSTEM MESSAGES FEED (RIGHT) =================
    function escHtml(s){ var d=document.createElement('div'); d.textContent=(s==null?'':String(s)); return d.innerHTML; }
    function openMessagesPage(){
        var l=document.querySelector('.side-nav .nav-link[data-page="messages"]');
        loadPage('messages', l || null);
    }
    var daysAlertNum=document.getElementById('days-alert-num');
    var daysAlertLabel=document.getElementById('days-alert-label');
    var msgsNavBadge=document.getElementById('msgs-nav-badge');
    var alertOpenBtn=document.getElementById('alert-open-msgs');
    var sysMsgsBody=document.getElementById('system-msgs-body');
    var sysMsgsOpen=document.getElementById('sys-msgs-open');
    function loadAlerts(){
        fetch('../backend/api/messages.php?action=list&limit=100')
            .then(function(r){ if(!r.ok) throw new Error('Status '+r.status); return r.json(); })
            .then(function(data){
                if(!data || !data.success) throw new Error((data&&data.error)||'Failed to load alerts');
                var msgs=data.messages||[];
                var today=new Date(); today.setHours(0,0,0,0);
                var todayMsgs=0;
                msgs.forEach(function(m){
                    var d=new Date(String(m.sent_at).replace(' ','T'));
                    if(d>=today) todayMsgs++;
                });
                var total=msgs.length;
                if(msgsNavBadge){
                    msgsNavBadge.textContent=total;
                    msgsNavBadge.style.display=total>0?'inline-block':'none';
                }
                if(daysAlertNum) daysAlertNum.textContent=todayMsgs;
                if(daysAlertLabel) daysAlertLabel.textContent=(todayMsgs===1?'message alert today':(todayMsgs===0?'no alerts today':'messages / alerts today'));
                if(alertOpenBtn) alertOpenBtn.style.display=total>0?'inline-block':'none';
                renderSystemMsgs(msgs);
            })
            .catch(function(err){
                if(daysAlertLabel) daysAlertLabel.textContent='Alert count unavailable';
                if(sysMsgsBody) sysMsgsBody.innerHTML='<div class="msg-item"><span class="msg-dot" style="background:#95A5A6;"></span>Could not load system messages: '+escHtml(err.message)+'</div>';
            });
    }
    function renderSystemMsgs(msgs){
        if(!sysMsgsBody) return;
        if(!msgs || msgs.length===0){
            sysMsgsBody.innerHTML='<div class="msg-item"><span class="msg-dot" style="background:#95A5A6;"></span>No system messages yet.</div>';
            return;
        }
        sysMsgsBody.innerHTML='';
        msgs.slice(0,6).forEach(function(m){
            var col=(m.channel==='sms')?'#E67E22':(m.channel==='email')?'#2980B9':'#2ECC71';
            var ts=fmtDateTime(m.sent_at);
            var item=document.createElement('div');
            item.className='msg-item';
            item.innerHTML='<span class="msg-dot" style="background:'+col+';"></span>'+escHtml(m.subject)+' → '+escHtml(m.recipient_name)+'<time>'+ts+'</time>';
            sysMsgsBody.appendChild(item);
        });
        if(sysMsgsOpen) sysMsgsOpen.style.display='inline-block';
    }
    if(alertOpenBtn) alertOpenBtn.addEventListener('click',openMessagesPage);
    if(sysMsgsOpen) sysMsgsOpen.addEventListener('click',openMessagesPage);
    loadAlerts();
})();
/**
 * Synchronize live session timer and GMT time display
 */
function initGMTDashboardTimer() {
  const loginEpochMs = window.SERVER_GMT_LOGIN_TIME || Date.now();

  function updateTimers() {
    // Current GMT Time in epoch ms
    const nowUtcEpochMs = Date.now();
    
    // Calculate elapsed session seconds
    let diffSeconds = Math.floor((nowUtcEpochMs - loginEpochMs) / 1000);
    if (diffSeconds < 0) diffSeconds = 0;

    const hours = String(Math.floor(diffSeconds / 3600)).padStart(2, '0');
    const minutes = String(Math.floor((diffSeconds % 3600) / 60)).padStart(2, '0');
    const seconds = String(diffSeconds % 60).padStart(2, '0');

    // Update Session Elapsed Counter
    const timerElem = document.getElementById('lblSessionTimer');
    if (timerElem) {
      timerElem.innerText = `${hours}:${minutes}:${seconds}`;
    }
  }

  // Initial call and set 1-second interval
  updateTimers();
  setInterval(updateTimers, 1000);
}

document.addEventListener('DOMContentLoaded', initGMTDashboardTimer);
function navigateTo(page){
    var l=document.querySelector('.side-nav .nav-link[data-page="'+page+'"]');
    if(l){ l.click(); return; }
    if(typeof window.loadPage==='function') window.loadPage(page);
}
/* Global feedback toast used by SPA page scripts (showAlert) — self-contained,
   no Bootstrap dependency; floats above modals so saves/errors stay visible. */
function showAlert(message,type){
    type=type||'info';
    var colors={success:['#28A745','#E9F9EF'],error:['#DC3545','#FDEBEC'],danger:['#DC3545','#FDEBEC'],warning:['#B9770E','#FEF5E0'],info:['#0072BC','#E7F3FC']};
    var c=colors[type]||colors.info;
    var box=document.createElement('div');
    box.textContent=message;
    box.style.cssText='position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:2200;max-width:min(640px,92vw);padding:11px 20px;border:1px solid '+c[0]+';border-radius:6px;background:'+c[1]+';color:'+c[0]+';font-size:13px;font-weight:700;box-shadow:0 4px 14px rgba(0,0,0,.18);font-family:inherit;';
    document.body.appendChild(box);
    setTimeout(function(){ if(box.parentNode) box.parentNode.removeChild(box); },5000);
}
// ================= PATIENT SEARCH (RIGHT SIDEBAR) =================
(function(){
    var FIELD_KEYS={
        'p-name':'name',
        'p-no':'patient_no',
        'p-nhis':'nhis',
        'p-area':'area',
        'p-mobile':'mobile',
        'p-moh':'moh',
        'p-contact':'contact_name',
        'p-cno':'contact_number',
        'p-cemail':'contact_email',
        'p-tag':''
    };
    var resultsPanel=document.getElementById('patientSearchResults');
    var resultsList=document.getElementById('resultsList');
    var resultsCount=document.getElementById('resultsCount');
    var searchTimer=null;
    var lastSearchPatients=[]; // patients from the most recent search (for View Profile)

    function getPatientName(p){ return [p.first_name,p.middle_name,p.last_name].filter(Boolean).join(' '); }
    function esc(s){ var d=document.createElement('div'); d.textContent=(s==null?'':String(s)); return d.innerHTML; }

    function runSearch(field,query,showLoading){
        query=(query||'').trim();
        if(!query){ if(resultsPanel) resultsPanel.style.display='none'; return; }
        if(!resultsPanel || !resultsList) return;
        resultsPanel.style.display='block';
        if(showLoading){ resultsList.innerHTML='<div class="result-loading"><span class="spinner"></span>Searching…</div>'; }
        fetch('../backend/api/patients.php?action=search&q='+encodeURIComponent(query)+'&field='+encodeURIComponent(field||'')+'&limit=15')
            .then(function(r){ if(!r.ok) throw new Error('Status '+r.status); return r.json(); })
            .then(function(data){
                if(!data || !data.success) throw new Error((data&&data.error)||'Search failed');
                var patients=data.patients||[];
                lastSearchPatients=patients;
                if(resultsCount) resultsCount.textContent=patients.length;
                if(!patients.length){
                    resultsList.innerHTML='<div class="result-no-results">No patient matches “'+esc(query)+'”.</div>';
                    return;
                }
                resultsList.innerHTML=patients.map(function(p){
                    var name=getPatientName(p);
                    return '<div class="result-item" data-pid="'+p.id+'">'+
                        '<div class="result-name">'+esc(name)+
                            (p.hospital_number?'<span class="result-badge">'+esc(p.hospital_number)+'</span>':'')+
                        '</div>'+
                        '<div class="result-meta">'+
                            (p.phone?'📞 '+esc(p.phone):'')+
                            (p.nhia_number?' &nbsp;·&nbsp; NHIS '+esc(p.nhia_number):'')+
                            (p.sponsor_name?' &nbsp;·&nbsp; '+esc(p.sponsor_name):'')+
                        '</div>'+
                        '<div class="result-meta" style="display:none" data-detail="1">'+
                            (p.date_of_birth?'DOB: '+esc(p.date_of_birth):'')+
                            (p.gender?' &nbsp;·&nbsp; '+esc(p.gender.charAt(0).toUpperCase()+p.gender.slice(1)):'')+
                            (p.gha_number?' &nbsp;·&nbsp; GHA '+esc(p.gha_number):'')+
                            '<br>'+(p.address?'📍 '+esc(p.address):'')+
                            (p.email?'<br>✉ '+esc(p.email):'')+
                            (p.emergency_contact_name?'<br>Emergency: '+esc(p.emergency_contact_name)+(p.emergency_contact_phone?' ('+esc(p.emergency_contact_phone)+')':''):'')+
                        '</div>'+
                        '<div class="result-actions">'+
                            '<button type="button" class="result-view-btn" data-view="'+p.id+'">View Profile</button>'+
                        '</div>'+
                    '</div>';
                }).join('');
            })
            .catch(function(err){
                resultsList.innerHTML='<div class="result-no-results">Error: '+esc(err.message)+'</div>';
            });
    }

    document.querySelectorAll('.search-row').forEach(function(row){
        var input=row.querySelector('input');
        var btn=row.querySelector('.btn-search');
        if(!input) return;
        var key=FIELD_KEYS[input.id]||'';
        function trigger(){ runSearch(key,input.value,true); }
        if(btn) btn.addEventListener('click',trigger);
        input.addEventListener('keydown',function(e){
            if(e.key==='Enter'){ e.preventDefault(); trigger(); }
        });
        input.addEventListener('input',function(){
            clearTimeout(searchTimer);
            searchTimer=setTimeout(function(){ runSearch(key,input.value,false); },500);
        });
    });

    var clearBtn=document.getElementById('clearSearchResults');
    if(clearBtn) clearBtn.addEventListener('click',function(){
        if(resultsPanel) resultsPanel.style.display='none';
        if(resultsList) resultsList.innerHTML='';
        document.querySelectorAll('.search-fields input').forEach(function(i){ i.value=''; });
    });

    if(resultsList){
        resultsList.addEventListener('click',function(e){
            var viewBtn=e.target.closest('.result-view-btn');
            if(viewBtn){
                e.stopPropagation();
                openPatientProfile(parseInt(viewBtn.getAttribute('data-view'),10));
                return;
            }
            var item=e.target.closest('.result-item');
            if(!item) return;
            var detail=item.querySelector('[data-detail]');
            if(detail) detail.style.display=(detail.style.display==='block')?'none':'block';
        });
    }

    /* ================= PATIENT PROFILE MODAL ================= */
    var profileModal=document.getElementById('patientProfileModal');
    function fillPp(id,value){
        var el=document.getElementById(id);
        if(el) el.textContent=(value==null||value==='')?'—':String(value);
    }
    function calcAge(dob){
        if(!dob) return '';
        var b=new Date(String(dob).replace(' ','T')||dob+'T00:00:00');
        if(isNaN(b.getTime())) return '';
        var today=new Date();
        var age=today.getFullYear()-b.getFullYear();
        var m=today.getMonth()-b.getMonth();
        if(m<0||(m===0&&today.getDate()<b.getDate())) age--;
        return age;
    }
    function openPatientProfile(pid){
        if(!profileModal) return;
        var p=null;
        for(var i=0;i<lastSearchPatients.length;i++){
            if(parseInt(lastSearchPatients[i].id,10)===pid){ p=lastSearchPatients[i]; break; }
        }
        if(!p){ showAlert('Patient details not found. Please search again.','error'); return; }
        var name=getPatientName(p);
        var age=calcAge(p.date_of_birth);
        fillPp('pp-avatar',(p.first_name||'?').charAt(0));
        fillPp('pp-name',name);
        fillPp('pp-sub',(p.sponsor_name||'Self-Pay')+(p.phone?' · '+p.phone:''));
        fillPp('pp-hn',p.hospital_number||('P'+p.id));
        fillPp('pp-dob',p.date_of_birth?fmtDate(p.date_of_birth):null);
        fillPp('pp-agegender',(age!==''?age+' yrs / ':'')+(p.gender?p.gender.charAt(0).toUpperCase()+p.gender.slice(1):''));
        fillPp('pp-blood',p.blood_group);
        fillPp('pp-phone',p.phone);
        fillPp('pp-email',p.email);
        fillPp('pp-address',p.address);
        fillPp('pp-nhis',p.nhia_number);
        fillPp('pp-gha',p.gha_number);
        fillPp('pp-sponsor',p.sponsor_name||'Self-Pay');
        fillPp('pp-emergency',(p.emergency_contact_name||'')+(p.emergency_contact_phone?' ('+p.emergency_contact_phone+')':''));
        fillPp('pp-registered',p.registration_date?fmtDate(p.registration_date):null);
        fillPp('pp-status',p.is_active==='0'||p.is_active===0?'Frozen':'Active');
        var goBtn=document.getElementById('pp-go-records');
        if(goBtn && typeof navigateTo==='function'){
            goBtn.onclick=function(){ closePatientProfile(); navigateTo('records'); };
            goBtn.style.display='';
        }
        profileModal.classList.add('show');
        document.body.style.overflow='hidden';
    }
    function closePatientProfile(){
        if(!profileModal) return;
        profileModal.classList.remove('show');
        document.body.style.overflow='';
    }
    window.openPatientProfile=openPatientProfile;
    window.closePatientProfile=closePatientProfile;
    var ppCloseBtn=document.getElementById('closePatientProfileBtn');
    var ppClose2=document.getElementById('pp-close-btn');
    if(ppCloseBtn) ppCloseBtn.addEventListener('click',closePatientProfile);
    if(ppClose2) ppClose2.addEventListener('click',closePatientProfile);
    if(profileModal){
        profileModal.addEventListener('click',function(e){
            if(e.target===profileModal || (e.target.classList && e.target.classList.contains('modal-dialog'))) closePatientProfile();
        });
        document.addEventListener('keydown',function(e){
            if(e.key==='Escape' && profileModal.classList.contains('show')) closePatientProfile();
        });
    }
})();
</script>
</body>
</html>
