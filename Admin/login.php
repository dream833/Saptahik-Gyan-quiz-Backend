<?php
require_once __DIR__ . "/../utils/api_config.php";

// Handle login form submission via admin_login.php API
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Use the configured ADMIN_API_URL from api_config.php
        $apiUrl = ADMIN_API_URL . 'admin_login.php';

        // Prepare JSON payload
        $payload = json_encode([
            'email' => $email,
            'password' => $password
        ]);

        // Initialize cURL
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FOLLOWLOCATION => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            // On production, servers often can't reach themselves via public domain (loopback).
            // Try the raw PHP stream as a fallback.
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $payload,
                    'timeout' => 10,
                    'ignore_errors' => true
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
            $response = @file_get_contents($apiUrl, false, $context);
            if ($response === false) {
                $error = 'Connection error: ' . $curlError . '. Please contact support.';
            } else {
                $result = json_decode($response, true);
                if ($result && isset($result['status']) && $result['status'] === true) {
                    session_start();
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $result['data']['id'] ?? null;
                    $_SESSION['admin_name'] = $result['data']['name'] ?? '';
                    $_SESSION['admin_email'] = $result['data']['email'] ?? $email;
                    $_SESSION['admin_role'] = $result['data']['role'] ?? 'admin';
                    header('Location: ' . BASE_URL . 'Admin/Dashboard.php');
                    exit;
                } else {
                    $error = $result['message'] ?? 'Invalid credentials. Please try again.';
                }
            }
        } elseif ($httpCode !== 200 || empty($response)) {
            $error = 'Server error. Please try again later.';
        } else {
            $result = json_decode($response, true);

            if ($result && isset($result['status']) && $result['status'] === true) {
                // Start session and set user data from API response
                session_start();
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $result['data']['id'] ?? null;
                $_SESSION['admin_name'] = $result['data']['name'] ?? '';
                $_SESSION['admin_email'] = $result['data']['email'] ?? $email;
                $_SESSION['admin_role'] = $result['data']['role'] ?? 'admin';

                // Redirect to dashboard using absolute URL from config
                header('Location: ' . BASE_URL . 'Admin/Dashboard.php');
                exit;
            } else {
                $error = $result['message'] ?? 'Invalid credentials. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - WB Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header .icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .login-header .icon svg {
            width: 32px;
            height: 32px;
            fill: #fff;
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #6b7280;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-group .input-wrapper {
            position: relative;
        }

        .form-group .input-wrapper .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
        }

        .form-group .input-wrapper .input-icon svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px 12px 44px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            color: #1f2937;
            background: #f9fafb;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-group input:focus {
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-group input::placeholder {
            color: #9ca3af;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .form-options .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6b7280;
            cursor: pointer;
        }

        .form-options .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #667eea;
            cursor: pointer;
        }

        .form-options .forgot-link {
            font-size: 14px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .form-options .forgot-link:hover {
            color: #764ba2;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .login-footer p {
            font-size: 14px;
            color: #6b7280;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .login-footer a:hover {
            color: #764ba2;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 8px;
        }

        .error-message.show {
            display: flex;
        }

        .error-message svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            fill: #dc2626;
        }

        .success-message {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 8px;
        }

        .success-message.show {
            display: flex;
        }

        .success-message svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            fill: #16a34a;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: #6b7280;
        }

        .password-toggle svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 28px 24px;
            }

            .login-header h1 {
                font-size: 22px;
            }

            .form-options {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <h1>Admin Login</h1>
            <p>Sign in to access the admin dashboard</p>
        </div>

        <div class="error-message<?= !empty($error) ? ' show' : '' ?>" id="errorMessage">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            <span id="errorText"><?= htmlspecialchars($error ?? 'Invalid credentials. Please try again.') ?></span>
        </div>

        <div class="success-message" id="successMessage">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <span id="successText">Login successful! Redirecting...</span>
        </div>

        <form id="loginForm" method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </span>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                    </span>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                    <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                        <svg viewBox="0 0 24 24" id="eyeIcon"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    Remember me
                </label>
                <a href="#" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                Sign In
            </button>
        </form>

      
    </div>

    <script>
        // Password visibility toggle
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        const eyeIcon = document.getElementById('eyeIcon');

        passwordToggle.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'text') {
                eyeIcon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
            } else {
                eyeIcon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/><path d="M3.27 2.5L2 3.77 6.64 8.41C5.14 9.62 4.09 11.21 4 13c.09 1.79 1.14 3.38 2.64 4.59C8.14 18.8 10 19.5 12 19.5c1.47 0 2.87-.36 4.09-.97l4.18 4.18 1.27-1.27L3.27 2.5zM12 15c-1.66 0-3-1.34-3-3 0-.65.18-1.25.45-1.77l4.32 4.32c-.52.27-1.12.45-1.77.45zM8.45 5.91C9.57 5.21 10.78 4.75 12 4.75c5 0 9.27 3.11 11 7.25-.46 1.16-1.18 2.22-2.11 3.12l-1.5-1.5c.38-.5.68-1.05.88-1.62-1.73-4.39-6-7.5-11-7.5-1.05 0-2.07.17-3.05.47l-1.77-1.76z"/>';
            }
        });

        // Form submission with validation
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        const successMessage = document.getElementById('successMessage');
        const successText = document.getElementById('successText');

        loginForm.addEventListener('submit', function(e) {
            // Reset messages
            errorMessage.classList.remove('show');
            successMessage.classList.remove('show');

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            // Client-side validation
            if (!email || !password) {
                e.preventDefault();
                showError('Please fill in all fields.');
                return;
            }

            if (!isValidEmail(email)) {
                e.preventDefault();
                showError('Please enter a valid email address.');
                return;
            }

            // Show loading state
            loginBtn.disabled = true;
            loginBtn.textContent = 'Signing in...';

            // Allow form to submit naturally to PHP for processing & redirect
        });

        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function showError(message) {
            errorText.textContent = message;
            errorMessage.classList.add('show');
            successMessage.classList.remove('show');
        }

        function showSuccess(message) {
            successText.textContent = message;
            successMessage.classList.add('show');
            errorMessage.classList.remove('show');
        }
    </script>
</body>
</html>
