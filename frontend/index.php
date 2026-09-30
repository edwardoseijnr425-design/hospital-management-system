<?php
// EHMS — EDDIE HEALTHCARE SOLUTIONS · EDDIE HOSPITAL
// Login page (modern single-card portal design).
session_start();

// Already logged in? Straight to the dashboard shell.
if (isset($_SESSION['user_id'])) {
    header('Location: /hms/frontend/dashboard.php');
    exit;
}

// Support contact details shown on the "forgot password" panel and the login
// footer. Generic placeholders by default; override locally via the gitignored
// file backend/config/local-contact.php (may define $contactEmail /
// $contactPhone). Personal contact details are NEVER committed to this public
// repository — keep it that way.
$contactEmail = 'support@example.com';
$contactPhone = '+233 00 000 0000';
$__localContactConfig = __DIR__ . '/../backend/config/local-contact.php';
if (is_file($__localContactConfig)) { include $__localContactConfig; }
unset($__localContactConfig);
$contactEmail = htmlspecialchars($contactEmail, ENT_QUOTES);
$contactPhone = htmlspecialchars($contactPhone, ENT_QUOTES);

// Roles the users table actually accepts, with readable labels. This list used
// to offer "receptionist" — which is not a role in this system — while omitting
// seven that are, so a user hunting for their account had no matching option.
//
// The keys mirror USER_ROLES in backend/config/config.php. They are repeated
// here rather than imported because this page deliberately does not include
// config.php: that would switch on display_errors on the sign-in screen and
// pull in the database layer for a page that needs neither.
//
// The selection is advisory only — login reads users.role from the database and
// never trusts what is posted here, so it grants nothing.
$roleLabels = [
    'super_admin' => 'Super Administrator',
    'admin'       => 'System Administrator',
    'doctor'      => 'Doctor / Physician',
    'nurse'       => 'Nurse / Clinician',
    'it'          => 'IT Support',
    'records'     => 'Health Records',
    'pharmacy'    => 'Pharmacy',
    'lab'         => 'Laboratory',
    'radiology'   => 'Radiology',
    'account'     => 'Accounts',
    'revenue'     => 'Revenue',
];
define('USER_ROLE_LABELS', $roleLabels);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Eddie Health Care Solutions</title>
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8fafc;
            padding: 16px;
        }

        /* Main Card */
        .login-card {
            max-width: 448px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        /* Header Banner */
        .card-header {
            background-color: #0f766e;
            padding: 24px;
            text-align: center;
            color: #ffffff;
            position: relative;
        }

        .card-header .hosp-badge {
            position: absolute;
            top: 16px;
            left: 16px;
            background: rgba(15, 118, 110, 0.6);
            padding: 8px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .card-header .hosp-badge i {
            font-size: 1.25rem;
            color: #ffffff;
        }

        .card-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .card-header p {
            color: #ccfbf1;
            font-size: 0.875rem;
            margin-top: 4px;
        }

        /* Form Body */
        .card-body {
            padding: 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group:last-of-type {
            margin-bottom: 0;
        }

        .field-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 8px;
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            inset: 0 auto 0 0;
            display: flex;
            align-items: center;
            padding-left: 16px;
            color: #94a3b8;
            pointer-events: none;
        }

        .field-wrap input,
        .field-wrap select {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            color: #334155;
            font-size: 0.95rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .field-wrap input::placeholder {
            color: #94a3b8;
        }

        .field-wrap input:focus,
        .field-wrap select:focus {
            border-color: transparent;
            box-shadow: 0 0 0 2px #14b8a6;
            background-color: #ffffff;
        }

        /* Remember row */
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.875rem;
            margin-top: 4px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            color: #475569;
            cursor: pointer;
        }

        .remember-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            accent-color: #0d9488;
            margin-right: 8px;
            cursor: pointer;
        }

        .remember-label input[type="checkbox"]:focus {
            outline: 2px solid #14b8a6;
            outline-offset: 2px;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            background-color: #0d9488;
            color: #ffffff;
            font-weight: 600;
            padding: 12px;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(15, 23, 42, 0.1);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
            margin-top: 20px;
        }

        .btn-submit:hover {
            background-color: #0f766e;
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.15);
        }

        .btn-submit:active {
            background-color: #115e59;
        }

        /* Error message */
        .error-message {
            margin-top: 16px;
            padding: 10px 14px;
            border-radius: 8px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .error-message.hidden {
            display: none;
        }

        /* Header illustration (doctor, patient & nurse) */
        .hms-illustration {
            display: block;
            width: 240px;
            max-width: 100%;
            height: auto;
            margin: 0 auto 14px;
        }

        /* Password reveal toggle */
        .pw-toggle {
            position: absolute;
            inset: 0 0 0 auto;
            width: 44px;
            border: none;
            background: none;
            padding: 0;
            color: #94a3b8;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0 8px 8px 0;
            font-family: inherit;
            font-size: 0.9rem;
        }

        .pw-toggle:hover {
            color: #0f766e;
        }

        .pw-toggle:focus-visible {
            outline: 2px solid #14b8a6;
            outline-offset: -2px;
        }

        .field-wrap.has-toggle input {
            padding-right: 48px;
        }

        /* Forgot-password link */
        .forgot-link {
            background: none;
            border: none;
            padding: 0;
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #0d9488;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .forgot-link:hover {
            color: #0f766e;
        }

        .forgot-link:focus-visible {
            outline: 2px solid #14b8a6;
            outline-offset: 2px;
            border-radius: 2px;
        }

        /* Admin-assisted password reset panel */
        .reset-panel {
            margin-top: 16px;
            border: 1px solid #99f6e4;
            border-left: 4px solid #0d9488;
            border-radius: 8px;
            background-color: #f0fdfa;
            padding: 16px;
            font-size: 0.8125rem;
            color: #115e59;
            display: none;
        }

        .reset-panel.show {
            display: block;
        }

        .reset-panel h3 {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .reset-panel p {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .reset-panel ul {
            margin: 0 0 10px 18px;
            line-height: 1.7;
        }

        .reset-panel .reset-contact {
            border-top: 1px dashed #99f6e4;
            padding-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .reset-panel .reset-contact a {
            color: #0f766e;
            font-weight: 600;
            word-break: break-word;
        }

        .reset-close {
            background: none;
            border: none;
            padding: 0;
            color: #0d9488;
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
        }

        /* Footer Note */
        .card-footer {
            background-color: #f8fafc;
            padding: 16px 32px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .support-strip {
            margin-bottom: 6px;
            color: #64748b;
        }

        .support-strip a {
            color: #0d9488;
            font-weight: 600;
            text-decoration: none;
        }

        .support-strip a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Header Banner -->
        <div class="card-header">
            <!-- Illustration: doctor, patient & nurse. Inline SVG so the portal
                 has no binary image dependency to install or go missing. -->
            <svg class="hms-illustration" viewBox="0 0 240 104" role="img" aria-label="Illustration of a doctor attending to a patient in bed, with a nurse">
                <rect x="1" y="1" width="238" height="102" rx="12" fill="rgba(255,255,255,0.08)"/>

                <!-- Doctor (left): coat, stethoscope -->
                <g>
                    <circle cx="44" cy="28" r="11" fill="#ffffff"/>
                    <path d="M24 94c0-15 9-26 20-26s20 11 20 26z" fill="rgba(255,255,255,0.20)" stroke="#ffffff" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M37 68l7 9 7-9" fill="none" stroke="#5eead4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M33 72v6a11 11 0 0 0 22 0v-6" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="55" cy="80" r="3.5" fill="#5eead4"/>
                </g>

                <!-- Patient (centre): in bed -->
                <g>
                    <rect x="84" y="34" width="20" height="11" rx="5" fill="rgba(255,255,255,0.28)"/>
                    <circle cx="107" cy="40" r="9" fill="#ffffff"/>
                    <path d="M117 40h45a7 7 0 0 1 7 7v9h-52z" fill="rgba(255,255,255,0.20)" stroke="#ffffff" stroke-width="2" stroke-linejoin="round"/>
                    <rect x="84" y="56" width="85" height="7" rx="2" fill="rgba(255,255,255,0.14)" stroke="#ffffff" stroke-width="2"/>
                    <path d="M89 63v16M164 63v16" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                </g>

                <!-- Nurse (right): cap with cross, uniform -->
                <g>
                    <path d="M184 22a11 11 0 0 1 22 0z" fill="#5eead4"/>
                    <circle cx="195" cy="30" r="11" fill="#ffffff"/>
                    <path d="M175 94c0-15 9-26 20-26s20 11 20 26z" fill="rgba(255,255,255,0.20)" stroke="#ffffff" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M195 76v12M189 82h12" stroke="#5eead4" stroke-width="3" stroke-linecap="round"/>
                </g>
            </svg>
            <h1>Eddie Health Care Solutions</h1>
            <p>System Dashboard</p>
        </div>

        <!-- Form Body -->
        <div class="card-body">
            <form id="login-form" autocomplete="on" class="space-y-5" novalidate>

                <!-- Username / Staff ID Field -->
                <div class="form-group">
                    <label class="field-label" for="username">Username</label>
                    <div class="field-wrap">
                        <span class="field-icon"><i class="fa-solid fa-user"></i></span>
                        <input type="text" id="username" name="username" required placeholder="e.g. DR_EDDIE_01" autocomplete="username" autofocus>
                    </div>
                </div>

                <!-- Password Field, with a reveal toggle -->
                <div class="form-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-wrap has-toggle">
                        <span class="field-icon"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" id="password" name="password" required placeholder="••••••••" autocomplete="current-password">
                        <button type="button" class="pw-toggle" id="pw-toggle" aria-label="Show password" aria-pressed="false">
                            <i class="fa-solid fa-eye" id="pw-toggle-icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Role Selection -->
                <div class="form-group">
                    <label class="field-label" for="role">Select User Role</label>
                    <div class="field-wrap">
                        <span class="field-icon"><i class="fa-solid fa-user-doctor"></i></span>
                        <select id="role" name="role">
                            <option value="" selected>Select User Role</option>
                            <?php foreach (USER_ROLE_LABELS as $roleValue => $roleLabel): ?>
                                <option value="<?php echo htmlspecialchars($roleValue, ENT_QUOTES); ?>"><?php echo htmlspecialchars($roleLabel, ENT_QUOTES); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Remember Me + Forgot Password -->
                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" id="remember">
                        Remember Me
                    </label>
                    <button type="button" class="forgot-link" id="forgot-btn" aria-expanded="false" aria-controls="reset-panel">Forgot Password?</button>
                </div>

                <div id="login-error" class="error-message hidden"></div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    <span>Login</span>
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>

                <!-- Admin-assisted password reset. There is no self-service reset
                     link: auth.php?action=reset_password is super_admin/admin only
                     and no SMTP is configured, so a reset email would silently
                     never arrive. Staff reset each other through User Management. -->
                <div class="reset-panel" id="reset-panel" role="region" aria-label="Password reset help">
                    <h3><i class="fa-solid fa-key"></i> Password Reset</h3>
                    <p>Passwords are reset by a system administrator — there is no self-service reset link.</p>
                    <ul>
                        <li>Ask your administrator for the <strong>User Management</strong> page.</li>
                        <li>They set a new password using <strong>Reset Password</strong>.</li>
                        <li>They must also confirm your account is <strong>active</strong>.</li>
                    </ul>
                    <div class="reset-contact">
                        <span><i class="fa-solid fa-envelope"></i> Email: <a href="mailto:<?php echo $contactEmail; ?>"><?php echo $contactEmail; ?></a></span>
                        <span><i class="fa-solid fa-phone"></i> Phone: <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contactPhone); ?>"><?php echo $contactPhone; ?></a></span>
                    </div>
                    <button type="button" class="reset-close" id="reset-close">Close</button>
                </div>
            </form>
        </div>

        <!-- Footer Note -->
        <div class="card-footer">
            <div class="support-strip">
                HMS Support: Email:
                <a href="mailto:<?php echo $contactEmail; ?>"><?php echo $contactEmail; ?></a>
                &nbsp;|&nbsp; Phone:
                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contactPhone); ?>"><?php echo $contactPhone; ?></a>
            </div>
            Authorized Personnel Only &bull; Protected by SSL Encryption
        </div>
    </div>

    <script>
        // "Remember Me" persists the username (never the password).
        (function() {
            var remember = document.getElementById('remember');
            var userField = document.getElementById('username');

            if (localStorage.getItem('hms_remember') === '1' && userField) {
                userField.value = localStorage.getItem('hms_username') || '';
                if (remember) remember.checked = true;
            }

            var form = document.getElementById('login-form');
            if (form) {
                form.addEventListener('submit', function() {
                    if (remember && remember.checked) {
                        localStorage.setItem('hms_remember', '1');
                        localStorage.setItem('hms_username', userField.value.trim());
                    } else {
                        localStorage.removeItem('hms_remember');
                        localStorage.removeItem('hms_username');
                    }
                });
            }
        })();

        // Password reveal toggle.
        (function() {
            var btn = document.getElementById('pw-toggle');
            var icon = document.getElementById('pw-toggle-icon');
            var field = document.getElementById('password');
            if (!btn || !icon || !field) return;

            btn.addEventListener('click', function() {
                var show = field.type === 'password';
                field.type = show ? 'text' : 'password';
                icon.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                // Keep the caret where they left it rather than jumping to the end.
                var len = field.value.length;
                field.focus();
                try { field.setSelectionRange(len, len); } catch (e) { /* not all types support it */ }
            });
        })();

        // Forgot-password panel: reveals the admin-assisted reset steps.
        (function() {
            var btn = document.getElementById('forgot-btn');
            var panel = document.getElementById('reset-panel');
            var close = document.getElementById('reset-close');
            if (!btn || !panel) return;

            function setOpen(open) {
                panel.classList.toggle('show', open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) panel.scrollIntoView({ block: 'nearest' });
            }

            btn.addEventListener('click', function() {
                setOpen(!panel.classList.contains('show'));
            });
            if (close) close.addEventListener('click', function() {
                setOpen(false);
                btn.focus();
            });
        })();
    </script>

    <script src="assets/js/auth.js"></script>
</body>
</html>