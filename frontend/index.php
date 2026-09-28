<?php
// EHMS — EDDIE HEALTHCARE SOLUTIONS · EDDIE HOSPITAL
// Old-system login page (EHMS teal/orange card design).
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
    <title>Log In - Eddie Health Care Solutions</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #2b2b2b;
            position: relative;
            overflow: hidden;
        }

        /* Abstract Background Elements */
        .bg-shape-1 {
            position: absolute;
            width: 450px;
            height: 450px;
            background-color: #1e938f;
            border-radius: 30px;
            transform: rotate(-15deg);
            top: 50px;
            left: 100px;
            z-index: 1;
        }

        .bg-shape-2 {
            position: absolute;
            width: 350px;
            height: 350px;
            background-color: #e09867;
            border-radius: 30px;
            transform: rotate(20deg);
            bottom: 50px;
            right: 120px;
            z-index: 1;
        }

        /* Main Container Card */
        .login-card {
            position: relative;
            z-index: 2;
            width: 85%;
            max-width: 960px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            display: flex;
            overflow: hidden;
            min-height: 520px;
        }

        /* Left Section - Medical Illustration Side */
        .card-left {
            flex: 1;
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
        }

        .card-left img {
            max-width: 100%;
            height: auto;
        }

        /* Right Section - Form Side */
        .card-right {
            flex: 1;
            padding: 50px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #fdfdfd;
        }

        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 35px;
        }

        .cross-logo {
            width: 50px;
            height: 50px;
            margin-bottom: 12px;
        }

        .login-title {
            color: #1a8b86;
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .login-form {
            width: 100%;
            max-width: 320px;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 10px 0;
            border: none;
            border-bottom: 1px solid #b0bec5;
            outline: none;
            font-size: 0.95rem;
            color: #455a64;
            background: transparent;
            transition: border-color 0.3s;
        }

        .input-group input::placeholder {
            color: #90a4ae;
        }

        .input-group input:focus,
        .input-group select:focus {
            border-bottom-color: #1e938f;
        }

        .password-toggle {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #1e938f;
            opacity: 0.7;
        }

        .password-toggle:hover {
            opacity: 1;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 35px;
        }

        .btn {
            flex: 1;
            padding: 12px 0;
            border: none;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: opacity 0.2s;
        }

        .btn-login {
            background-color: #198782;
            color: #ffffff;
        }

        .btn-cancel {
            background-color: #a80015;
            color: #ffffff;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .error-message {
            margin-top: 16px;
            padding: 10px 14px;
            border-radius: 4px;
            background: #fdecea;
            border: 1px solid #f5b5b1;
            color: #a80015;
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

        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
            }
            .card-left {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Background Deco Shapes -->
    <div class="bg-shape-1"></div>
    <div class="bg-shape-2"></div>

    <!-- Main Card -->
    <div class="login-card">

        <!-- Left Side: Illustration -->
        <div class="card-left">
            <svg width="340" height="280" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Background Decorative Foliage -->
                <path d="M120 220 C80 180, 70 120, 100 80 C120 120, 130 180, 120 220 Z" fill="#D3C0DB"/>
                <path d="M340 230 C370 190, 360 130, 330 90 C320 130, 320 190, 340 230 Z" fill="#E8BDC4"/>

                <!-- Floating Medical Icons -->
                <circle cx="200" cy="80" r="18" fill="#FADBD8"/>
                <path d="M194 80 H206 M200 74 V86" stroke="#C0392B" stroke-width="3" stroke-linecap="round"/>

                <circle cx="160" cy="110" r="14" fill="#FADBD8"/>
                <path d="M154 105 L166 115" stroke="#E74C3C" stroke-width="4" stroke-linecap="round"/>

                <!-- Stool -->
                <ellipse cx="202" cy="225" rx="18" ry="5" fill="#5D4037"/>
                <path d="M192 225 L188 280 M212 225 L216 280" stroke="#5D4037" stroke-width="3"/>

                <!-- Characters (Simplified Vectors) -->
                <!-- Patient -->
                <circle cx="190" cy="155" r="10" fill="#E0AC69"/>
                <path d="M178 180 C178 170, 202 170, 202 180 L205 220 H175 Z" fill="#E74C3C"/>

                <!-- Doctor Seated -->
                <circle cx="230" cy="150" r="10" fill="#F1C40F"/>
                <path d="M218 175 C218 165, 242 165, 242 175 L240 225 H220 Z" fill="#2C3E50"/>

                <!-- Nurse Standing -->
                <circle cx="265" cy="140" r="10" fill="#E0AC69"/>
                <path d="M253 165 C253 155, 277 155, 277 165 L275 250 H255 Z" fill="#16A085"/>
            </svg>
        </div>

        <!-- Right Side: Login Form -->
        <div class="card-right">
            <div class="logo-container">
                <!-- Custom Green/Orange Medical Cross Logo -->
                <svg class="cross-logo" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M35 15 H65 V35 H85 V65 H65 V85 H35 V65 H15 V35 H35 Z" fill="#008080" />
                    <path d="M50 15 H65 V35 H85 V50 H50 Z" fill="#F28D35" />
                </svg>
                <h1 class="login-title">Log In</h1>
            </div>

            <form class="login-form" id="login-form" autocomplete="off">
                <div class="input-group">
                    <input type="text" id="username" name="username" placeholder="Username" required autofocus>
                </div>

                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <span class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </span>
                </div>

                <div class="input-group">
                    <select name="hospital" aria-label="Hospital Name">
                        <option value="" disabled selected hidden>Hospital Name</option>
                        <option value="main" selected>Eddie Hospital</option>
                    </select>
                </div>

                <div id="login-error" class="error-message hidden"></div>

                <div class="button-group">
                    <button type="submit" class="btn btn-login">LOGIN</button>
                    <button type="button" class="btn btn-cancel" id="login-cancel-btn">CANCEL</button>
                </div>
            </form>
        </div>

    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            if (pwd.type === 'password') {
                pwd.type = 'text';
            } else {
                pwd.type = 'password';
            }
        }

        // CANCEL clears the form and any previous error message.
        document.addEventListener('DOMContentLoaded', function() {
            const cancelBtn = document.getElementById('login-cancel-btn');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    const form = document.getElementById('login-form');
                    if (form) form.reset();
                    const err = document.getElementById('login-error');
                    if (err) {
                        err.textContent = '';
                        err.classList.add('hidden');
                        err.classList.remove('show');
                    }
                });
            }
        });
    </script>

    <script src="assets/js/auth.js"></script>
</body>
</html>