<?php
/**
 * Admin Layout Header
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/auth.php';

$current_admin = current_admin();
$db = get_db();

// Fetch unread notifications count
$unread_notif_count = 0;
$unread_notifications = [];
try {
    $stmt = $db->query("SELECT * FROM notifications WHERE is_read = 0 ORDER BY id DESC LIMIT 5");
    $unread_notifications = $stmt->fetchAll();
    $unread_notif_count = count($unread_notifications);
} catch (Exception $e) {}

$admin_title = isset($page_title) ? $page_title . ' | SAP SADA Admin' : 'SAP SADA Admin Console';
$current_page = $current_page ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($admin_title) ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= ASSETS_URL ?>/images/branding/logo.png">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Noto+Sans+Sinhala:wght@400;600;700&family=Noto+Sans+Tamil:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Admin Stylesheet -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css?v=1.0.1">
</head>
<body class="admin-body">

    <!-- 1. Admin Sidebar Navigation -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- 2. Main Content Wrapper -->
    <div class="admin-main-wrapper">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-light d-lg-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-none d-sm-block">
                    <h5 class="fw-bold mb-0 text-navy"><?= e($page_title ?? 'Dashboard') ?></h5>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- View Public Website Link -->
                <a href="<?= BASE_URL ?>/index.php" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-none d-md-inline-flex align-items-center gap-1">
                    <i class="fas fa-external-link-alt text-teal"></i> View Website
                </a>

                <!-- Notifications Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light rounded-circle position-relative p-2" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="width:40px; height:40px;">
                        <i class="fas fa-bell text-secondary"></i>
                        <?php if ($unread_notif_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.65rem;">
                                <?= $unread_notif_count ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-card border-0 rounded-3 p-2" style="width: 320px;" aria-labelledby="notifDropdown">
                        <li class="d-flex justify-content-between align-items-center px-2 py-1 mb-2 border-bottom">
                            <span class="fw-bold small text-navy">Notifications</span>
                            <span class="badge bg-light text-muted"><?= $unread_notif_count ?> Unread</span>
                        </li>
                        <?php if (!empty($unread_notifications)): ?>
                            <?php foreach ($unread_notifications as $notif): ?>
                                <li>
                                    <a class="dropdown-item rounded-2 p-2 small" href="<?= ADMIN_URL ?>/<?= e($notif['link'] ?: 'dashboard.php') ?>">
                                        <div class="fw-semibold text-navy"><?= e($notif['title']) ?></div>
                                        <div class="text-muted smaller"><?= e($notif['message']) ?></div>
                                        <div class="text-secondary smaller mt-1"><?= time_ago($notif['created_at']) ?></div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-center py-3 text-muted small">No new notifications</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Admin Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2 p-1 pe-3 rounded-pill border" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center fw-bold" style="width:34px; height:34px; font-size:0.85rem;">
                            <?= strtoupper(substr($current_admin['username'], 0, 1)) ?>
                        </div>
                        <div class="text-start d-none d-sm-block">
                            <div class="fw-bold small text-navy lh-1"><?= e($current_admin['username']) ?></div>
                            <small class="text-muted text-uppercase" style="font-size: 0.68rem;"><?= e($current_admin['role_name']) ?></small>
                        </div>
                        <i class="fas fa-chevron-down small text-muted ms-1"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-card border-0 rounded-3 p-2" aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item py-2 small" href="<?= ADMIN_URL ?>/settings.php"><i class="fas fa-cog me-2 text-teal"></i>System Settings</a></li>
                        <li><a class="dropdown-item py-2 small" href="<?= ADMIN_URL ?>/users.php"><i class="fas fa-users-cog me-2 text-primary"></i>Admin Users</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 small text-danger" href="<?= ADMIN_URL ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Admin Content Body Start -->
        <main class="admin-content-body">
