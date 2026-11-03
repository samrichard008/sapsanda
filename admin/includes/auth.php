<?php
/**
 * Admin Authentication & Session Guard
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || empty($_SESSION['admin_id'])) {
    $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '');
    header("Location: " . ADMIN_URL . "/login.php?redirect=" . $redirect);
    exit;
}

// Fetch current logged in admin
function current_admin(): array {
    static $admin = null;
    if ($admin === null) {
        $db = get_db();
        $stmt = $db->prepare("
            SELECT a.*, r.role_name 
            FROM admins a
            JOIN admin_roles r ON a.role_id = r.id
            WHERE a.id = ? AND a.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        if (!$admin) {
            // Suspended or deleted
            session_destroy();
            header("Location: " . ADMIN_URL . "/login.php?error=inactive");
            exit;
        }
    }
    return $admin;
}

// Check admin module permissions
function has_permission(string $module): bool {
    $admin = current_admin();
    if ($admin['role_name'] === 'Super Admin') {
        return true;
    }
    $db = get_db();
    $stmt = $db->prepare("
        SELECT can_view, can_edit FROM permissions 
        WHERE role_id = ? AND (module = ? OR module = 'all')
        LIMIT 1
    ");
    $stmt->execute([$admin['role_id'], $module]);
    $perm = $stmt->fetch();
    return $perm && ($perm['can_view'] == 1 || $perm['can_edit'] == 1);
}
