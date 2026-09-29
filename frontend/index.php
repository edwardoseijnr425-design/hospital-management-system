<?php
// EHMS — EDDIE HEALTHCARE SOLUTIONS · EDDIE HOSPITAL
// Login page (modern single-card portal design).
session_start();

// Already logged in? Straight to the dashboard shell.
if (isset($_SESSION['user_id'])) {
    header('Location: /hms/frontend/dashboard.php');
    exit;
}
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

        /* Footer Note */
        .card-footer {
            background-color: #f8fafc;
            padding: 16px 32px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 0.75rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Header Banner -->
        <div class="card-header">
            <div class="hosp-badge">
                <i class="fa-solid fa-hospital"></i>
            </div>
            <h1>Eddie Health Care</h1>
            <p>Hospital Management System Portal</p>
        </div>

        <!-- Form Body -->
        <div class="card-body">
            <form id="login-form" autocomplete="on" class="space-y-5" novalidate>

                <!-- Role Selection -->
                <div class="form-group">
                    <label class="field-label" for="role">Select User Role</label>
                    <div class="field-wrap">
                        <span class="field-icon"><i class="fa-solid fa-user-doctor"></i></span>
                        <select id="role" name="role">
                            <option value="" selected>Select User Role</option>
                            <option value="doctor">Doctor / Physician</option>
                            <option value="nurse">Nurse / Clinician</option>
                            <option value="receptionist">Reception / Front Desk</option>
                            <option value="admin">System Administrator</option>
                        </select>
                    </div>
                </div>

                <!-- Username / Staff ID Field -->
                <div class="form-group">
                    <label class="field-label" for="username">Username or Staff ID</label>
                    <div class="field-wrap">
                        <span class="field-icon"><i class="fa-solid fa-user"></i></span>
                        <input type="text" id="username" name="username" required placeholder="e.g. DR_EDDIE_01" autocomplete="username" autofocus>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-wrap">
                        <span class="field-icon"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" id="password" name="password" required placeholder="••••••••" autocomplete="current-password">
                    </div>
                </div>

                <!-- Remember This Device -->
                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" id="remember">
                        Remember this device
                    </label>
                </div>

                <div id="login-error" class="error-message hidden"></div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    <span>Secure Sign In</span>
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>
        </div>

        <!-- Footer Note -->
        <div class="card-footer">
            Authorized Personnel Only &bull; Protected by SSL Encryption
        </div>
    </div>

    <script>
        // "Remember this device" persists the username (never the password).
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
    </script>

    <script src="assets/js/auth.js"></script>
</body>
</html>