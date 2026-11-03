<?php
/**
 * Web Installer & Environment Verification Wizard
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 * Registration Number: G A 00282904
 */

$lock_file = __DIR__ . '/config/installed.lock';
$is_locked = file_exists($lock_file);

// Check requirements
$requirements = [
    'PHP Version (>= 8.0)' => [
        'status' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'current' => PHP_VERSION,
        'required' => '>= 8.0.0'
    ],
    'PDO Extension' => [
        'status' => extension_loaded('pdo'),
        'current' => extension_loaded('pdo') ? 'Loaded' : 'Missing',
        'required' => 'Required'
    ],
    'PDO MySQL Driver' => [
        'status' => extension_loaded('pdo_mysql'),
        'current' => extension_loaded('pdo_mysql') ? 'Loaded' : 'Missing',
        'required' => 'Required'
    ],
    'Mbstring Extension' => [
        'status' => extension_loaded('mbstring'),
        'current' => extension_loaded('mbstring') ? 'Loaded' : 'Missing',
        'required' => 'Required'
    ],
    'FileInfo Extension (Uploads)' => [
        'status' => extension_loaded('fileinfo'),
        'current' => extension_loaded('fileinfo') ? 'Loaded' : 'Missing',
        'required' => 'Required'
    ],
    'GD / Image Library' => [
        'status' => extension_loaded('gd'),
        'current' => extension_loaded('gd') ? 'Loaded' : 'Missing',
        'required' => 'Required'
    ],
    'OpenSSL Extension' => [
        'status' => extension_loaded('openssl'),
        'current' => extension_loaded('openssl') ? 'Loaded' : 'Missing',
        'required' => 'Required'
    ]
];

$write_checks = [
    'config' => is_writable(__DIR__ . '/config'),
    'uploads' => is_writable(__DIR__ . '/uploads'),
    'uploads/activities' => is_writable(__DIR__ . '/uploads/activities'),
    'uploads/projects' => is_writable(__DIR__ . '/uploads/projects'),
    'uploads/donations' => is_writable(__DIR__ . '/uploads/donations'),
    'uploads/gallery' => is_writable(__DIR__ . '/uploads/gallery')
];

$all_reqs_met = true;
foreach ($requirements as $r) {
    if (!$r['status']) $all_reqs_met = false;
}
foreach ($write_checks as $w) {
    if (!$w) $all_reqs_met = false;
}

$db_status = null;
$db_error = null;
$tables_count = 0;

// Try database connection using config.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $db = get_db();
    $db_status = true;
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $tables_count = count($tables);
} catch (Exception $e) {
    $db_status = false;
    $db_error = $e->getMessage();
}

// Handle Auto-Install / Migration
$install_msg = '';
$install_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'install_db') {
        $sql_file = __DIR__ . '/database.sql';
        if (!file_exists($sql_file)) {
            $install_err = 'database.sql file not found in root directory.';
        } else {
            try {
                $sql = file_get_contents($sql_file);
                $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
                $db->exec($sql);
                file_put_contents($lock_file, "Installed on " . date('Y-m-d H:i:s'));
                $is_locked = true;
                $install_msg = 'Database imported and initialized successfully! All 25 tables are active.';
                $tables_count = 25;
            } catch (Exception $e) {
                $install_err = 'Failed to import database: ' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'lock_installer') {
        file_put_contents($lock_file, "Locked on " . date('Y-m-d H:i:s'));
        $is_locked = true;
        $install_msg = 'Installer locked for security.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Installer &amp; Health Check | SAP SADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-deep: #05254a;
            --teal-ocean: #008fa0;
            --gold-accent: #f0a500;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #05254a 0%, #031528 100%);
            min-height: 100vh;
            color: #212529;
        }
        .text-navy { color: var(--navy-deep); }
        .text-teal { color: var(--teal-ocean); }
        .btn-navy { background-color: var(--navy-deep); color: #fff; }
        .btn-navy:hover { background-color: #031830; color: #fff; }
        .btn-gold { background-color: var(--gold-accent); color: var(--navy-deep); font-weight: 700; }
        .btn-gold:hover { background-color: #d89400; color: var(--navy-deep); }
    </style>
</head>
<body class="py-5">

<div class="container" style="max-width: 900px;">
    <!-- Logo & Title -->
    <div class="text-center text-white mb-4">
        <img src="assets/images/branding/logo.png" alt="SAP SADA Emblem" style="max-height: 90px;" class="mb-3">
        <h2 class="fw-bold mb-1">SAP SADA SOCIAL WELFARE &amp; LIFE PROTECTION UNIT</h2>
        <div class="text-info small fw-semibold">Reg. No: G A 00282904 | Panadura, Sri Lanka</div>
        <p class="text-white-50 small mt-2">Automated Server Environment Verification &amp; One-Click Installer</p>
    </div>

    <?php if ($install_msg): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($install_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($install_err): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($install_err) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-navy">
                <i class="fas fa-server text-teal me-2"></i> System Health &amp; Deployment Readiness
            </h5>
            <?php if ($is_locked): ?>
                <span class="badge bg-success"><i class="fas fa-lock me-1"></i> Installation Locked</span>
            <?php else: ?>
                <span class="badge bg-warning text-dark"><i class="fas fa-lock-open me-1"></i> Setup Mode Active</span>
            <?php endif; ?>
        </div>

        <div class="card-body p-4">
            <!-- 1. PHP Requirements -->
            <h6 class="fw-bold text-navy mb-3"><i class="fab fa-php text-primary me-2"></i> 1. PHP Server Environment</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Prerequisite</th>
                            <th>Required</th>
                            <th>Current Server</th>
                            <th class="text-center" style="width: 100px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requirements as $name => $req): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($name) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($req['required']) ?></td>
                                <td><?= htmlspecialchars($req['current']) ?></td>
                                <td class="text-center">
                                    <?php if ($req['status']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Pass</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fas fa-times"></i> Fail</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- 2. Write Permissions -->
            <h6 class="fw-bold text-navy mb-3"><i class="fas fa-folder-open text-warning me-2"></i> 2. File &amp; Directory Permissions</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Directory</th>
                            <th>Purpose</th>
                            <th class="text-center" style="width: 100px;">Permission</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($write_checks as $dir => $writable): ?>
                            <tr>
                                <td><code>/<?= htmlspecialchars($dir) ?></code></td>
                                <td class="text-muted small">
                                    <?= strpos($dir, 'config') !== false ? 'Configuration files and setup lock' : 'Public user uploads & media files' ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($writable): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Writable</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fas fa-times"></i> Read-only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- 3. Database Status -->
            <h6 class="fw-bold text-navy mb-3"><i class="fas fa-database text-teal me-2"></i> 3. Database Connectivity &amp; Tables</h6>
            <div class="p-3 rounded mb-4 <?= $db_status ? 'bg-light border' : 'bg-danger bg-opacity-10 border border-danger' ?>">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold text-navy">
                            Database: <code><?= DB_NAME ?></code> @ <code><?= DB_HOST ?>:<?= DB_PORT ?></code>
                        </div>
                        <div class="small text-muted">
                            <?php if ($db_status): ?>
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i> Connected successfully.</span>
                                <strong><?= $tables_count ?></strong> tables detected in database.
                            <?php else: ?>
                                <span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Connection failed: <?= htmlspecialchars($db_error) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <?php if ($db_status && $tables_count >= 25): ?>
                            <span class="badge bg-success p-2"><i class="fas fa-check-double me-1"></i> Fully Populated (25 Tables)</span>
                        <?php elseif ($db_status): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="install_db">
                                <button type="submit" class="btn btn-gold btn-sm" onclick="return confirm('Do you want to import database.sql now?');">
                                    <i class="fas fa-bolt me-1"></i> Initialize Database Now
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 4. Default Credentials Notice -->
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                <i class="fas fa-key fa-2x text-teal me-3"></i>
                <div>
                    <strong class="d-block text-navy">Default Super Admin Credentials:</strong>
                    <span class="font-monospace small">Username: <strong>admin</strong> | Password: <strong>Admin@SapSada2026!</strong></span>
                    <div class="small text-muted">You can modify these credentials any time inside the Admin Console &gt; Admin Users.</div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center pt-3 border-top">
                <div class="d-flex gap-2">
                    <a href="index.php" class="btn btn-navy fw-bold px-4">
                        <i class="fas fa-globe me-2"></i> Visit Website
                    </a>
                    <a href="admin/login.php" class="btn btn-teal text-white fw-bold px-4" style="background-color: var(--teal-ocean);">
                        <i class="fas fa-lock me-2"></i> Admin Login
                    </a>
                </div>

                <?php if (!$is_locked): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="lock_installer">
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-shield-alt me-1"></i> Lock Installer
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="text-center text-white-50 small mt-4">
        &copy; <?= date('Y') ?> SAP SADA SOCIAL WELFARE &amp; LIFE PROTECTION UNIT (G A 00282904). All Rights Reserved.
    </div>
</div>

</body>
</html>
