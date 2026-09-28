<?php
// EHMS — EDDIE HEALTHCARE SOLUTIONS · EDDIE HOSPITAL
// Recovered old-system login page.
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
    <title>EHMS - EDDIE HEALTHCARE SOLUTIONS | Sign In</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="logo-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" style="width:64px;height:64px;display:inline-block;vertical-align:middle" aria-hidden="true">
                    <defs>
                        <linearGradient id="greenGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#80C342"/><stop offset="50%" stop-color="#4CAF50"/><stop offset="100%" stop-color="#1B5E20"/></linearGradient>
                        <linearGradient id="blueGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#00AEEF"/><stop offset="50%" stop-color="#0072BC"/><stop offset="100%" stop-color="#0D47A1"/></linearGradient>
                    </defs>
                    <g transform="translate(250, 200) scale(1.2)">
                        <path d="M -20 -90 C -20 -115 0 -130 25 -130 C 50 -130 70 -110 70 -85 C 70 -50 20 -20 -10 -10 C -40 0 -90 -20 -90 -50 C -90 -75 -70 -95 -45 -95 C -20 -95 -20 -90 -20 -90 Z" fill="url(#greenGrad)"/>
                        <path d="M 20 90 C 20 115 0 130 -25 130 C -50 130 -70 110 -70 85 C -70 50 -20 20 10 10 C 40 0 90 20 90 50 C 90 75 70 95 45 95 C 20 95 20 90 20 90 Z" fill="url(#blueGrad)"/>
                    </g>
                </svg>
            </div>
            <div class="auth-header">
                <div class="sys-name">EHMS</div>
                <div class="sub-line">Eddie Healthcare Solutions</div>
                <div class="divider"></div>
                <div class="clinic-line">Eddie Hospital</div>
            </div>

            <form id="login-form" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn">Sign In</button>
                <div id="login-error" class="error-message hidden"></div>
            </form>

            <div class="auth-footer">
                EHMS &copy; 2026 EDDIE HOSPITAL<br>
                Authorised personnel only.
            </div>
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
</body>
</html>