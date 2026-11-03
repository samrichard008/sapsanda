<?php
/**
 * Admin Secure Login Portal
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: " . ADMIN_URL . "/dashboard.php");
    exit;
}

$error_msg = null;
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$db = get_db();

// Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security validation failed. Please reload and try again.';
    } else {
        $username_email = clean_input($_POST['username_email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username_email) || empty($password)) {
            $error_msg = 'Please enter both your Username/Email and Password.';
        } else {
            // Check lockout
            $stmt = $db->prepare("
                SELECT * FROM admins 
                WHERE (username = ? OR email = ?) 
                LIMIT 1
            ");
            $stmt->execute([$username_email, $username_email]);
            $admin = $stmt->fetch();

            if ($admin) {
                // Check if currently locked out
                if ($admin['lockout_until'] && strtotime($admin['lockout_until']) > time()) {
                    $remaining = ceil((strtotime($admin['lockout_until']) - time()) / 60);
                    $error_msg = "Account is temporarily locked due to consecutive failed attempts. Please try again in {$remaining} minute(s).";
                } elseif ($admin['status'] !== 'active') {
                    $error_msg = 'Your administrative account has been deactivated or suspended.';
                } else {
                    // Verify Password
                    if (password_verify($password, $admin['password_hash'])) {
                        // Reset failed attempts & update login timestamp
                        $db->prepare("
                            UPDATE admins 
                            SET failed_attempts = 0, lockout_until = NULL, last_login = NOW() 
                            WHERE id = ?
                        ")->execute([$admin['id']]);

                        // Prevent session fixation
                        session_regenerate_id(true);

                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_username'] = $admin['username'];
                        $_SESSION['admin_name'] = $admin['full_name'];
                        $_SESSION['admin_role_id'] = $admin['role_id'];

                        log_audit('Admin Login', 'Auth', "User {$admin['username']} logged in successfully.", $admin['id']);

                        $redirect = !empty($_GET['redirect']) ? urldecode($_GET['redirect']) : ADMIN_URL . '/dashboard.php';
                        header("Location: " . $redirect);
                        exit;
                    } else {
                        // Increment failed attempts
                        $attempts = $admin['failed_attempts'] + 1;
                        $lockout = null;
                        if ($attempts >= 5) {
                            $lockout = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                            $error_msg = 'Too many failed login attempts. Account locked for 15 minutes.';
                        } else {
                            $rem = 5 - $attempts;
                            $error_msg = "Invalid credentials. You have {$rem} attempt(s) remaining before temporary lockout.";
                        }

                        $db->prepare("
                            UPDATE admins 
                            SET failed_attempts = ?, lockout_until = ? 
                            WHERE id = ?
                        ")->execute([$attempts, $lockout, $admin['id']]);

                        log_audit('Failed Login Attempt', 'Auth', "Failed login attempt for user {$username_email} from IP {$ip_address}.");
                    }
                }
            } else {
                $error_msg = 'Invalid username or password.';
                log_audit('Unknown User Login Attempt', 'Auth', "Attempted login with unknown username '{$username_email}' from IP {$ip_address}.");
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
    <title>Admin Authentication | <?= e(get_setting('site_name', ORG_NAME)) ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= ASSETS_URL ?>/images/branding/logo.png">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #031730 0%, #05254a 50%, #0a3663 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .login-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 35px 25px 25px;
            text-align: center;
        }
        .login-logo {
            height: 75px;
            width: auto;
            object-fit: contain;
            margin-bottom: 12px;
        }
        .btn-login {
            background: linear-gradient(135deg, #05254a 0%, #008fa0 100%);
            color: #ffffff;
            font-weight: 700;
            padding: 12px;
            border-radius: 10px;
            border: none;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #008fa0 0%, #05254a 100%);
            color: #ffffff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <img src="<?= ASSETS_URL ?>/images/branding/logo.png" alt="SAP SADA Emblem" class="login-logo">
            <h4 class="fw-bold text-navy mb-1" style="color: #05254a;">SAP SADA UNIT</h4>
            <div class="small text-muted fw-semibold">Administrative Management Console</div>
            <span class="badge bg-light text-secondary border mt-2">Registration: G A 00282904</span>
        </div>

        <div class="p-4 p-sm-5">
            <?php if ($error_msg): ?>
                <div class="alert alert-danger border-0 small rounded-3 p-3 mb-4">
                    <i class="fas fa-exclamation-circle me-1"></i> <?= e($error_msg) ?>
                </div>
            <?php endif; ?>

            <form action="login.php<?= !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Username or Official Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" name="username_email" class="form-control border-start-0 bg-light" required placeholder="admin" value="<?= e($_POST['username_email'] ?? '') ?>" autocomplete="username">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 bg-light" required placeholder="••••••••••••" autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" name="login_submit" class="btn btn-login w-100 shadow-sm">
                    <i class="fas fa-sign-in-alt me-2"></i> Authenticate &amp; Enter
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top">
                <a href="<?= BASE_URL ?>/index.php" class="small text-muted text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Return to Public Website
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
