<?php
/**
 * Admin User Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/includes/auth.php';
require_permission('manage_users');

$current_page = 'users';
$page_title = 'Admin Users Management';

$db = get_db();
$success_msg = '';
$error_msg = '';

$current_admin_id = $_SESSION['admin_id'] ?? 0;

// Fetch all roles
$roles_stmt = $db->query("SELECT id, role_name FROM admin_roles ORDER BY id ASC");
$roles = $roles_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Actions (Add, Edit, Delete)
$action = $_GET['action'] ?? 'list';
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle Delete
if ($action === 'delete' && $edit_id > 0) {
    if (!verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $error_msg = 'Security token invalid. Action aborted.';
    } elseif ($edit_id === $current_admin_id) {
        $error_msg = 'You cannot delete your own logged-in account.';
    } else {
        $del_stmt = $db->prepare("DELETE FROM admins WHERE id = ?");
        $del_stmt->execute([$edit_id]);
        log_audit('delete', 'users', "Deleted admin user ID #{$edit_id}");
        header('Location: ' . ADMIN_URL . '/users.php?msg=deleted');
        exit;
    }
}

// Handle Form Submissions (Create or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security token invalid or expired. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $role_id = (int)($_POST['role_id'] ?? 2);
        $status = $_POST['status'] ?? 'active';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email) || empty($full_name)) {
            $error_msg = 'Username, email, and full name are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = 'Please provide a valid email address.';
        } else {
            // Check username/email uniqueness
            $check_sql = "SELECT id FROM admins WHERE (username = ? OR email = ?)" . ($edit_id > 0 ? " AND id != ?" : "");
            $params = $edit_id > 0 ? [$username, $email, $edit_id] : [$username, $email];
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->execute($params);

            if ($check_stmt->fetch()) {
                $error_msg = 'A user with this username or email already exists.';
            } else {
                if ($edit_id > 0) {
                    // Update user
                    // Prevent self-deactivation or self-role change
                    if ($edit_id === $current_admin_id) {
                        $status = 'active';
                        $role_id = $_SESSION['admin_role_id'] ?? 1;
                    }

                    if (!empty($password)) {
                        if (strlen($password) < 8) {
                            $error_msg = 'Password must be at least 8 characters long.';
                        } else {
                            $hash = password_hash($password, PASSWORD_BCRYPT);
                            $upd_stmt = $db->prepare("
                                UPDATE admins 
                                SET username = ?, email = ?, full_name = ?, role_id = ?, status = ?, password_hash = ?
                                WHERE id = ?
                            ");
                            $upd_stmt->execute([$username, $email, $full_name, $role_id, $status, $hash, $edit_id]);
                            log_audit('update', 'users', "Updated admin user #{$edit_id} ({$username}) including password reset");
                            header('Location: ' . ADMIN_URL . '/users.php?msg=updated');
                            exit;
                        }
                    } else {
                        $upd_stmt = $db->prepare("
                            UPDATE admins 
                            SET username = ?, email = ?, full_name = ?, role_id = ?, status = ?
                            WHERE id = ?
                        ");
                        $upd_stmt->execute([$username, $email, $full_name, $role_id, $status, $edit_id]);
                        log_audit('update', 'users', "Updated admin user #{$edit_id} ({$username})");
                        header('Location: ' . ADMIN_URL . '/users.php?msg=updated');
                        exit;
                    }
                } else {
                    // Create new user
                    if (empty($password) || strlen($password) < 8) {
                        $error_msg = 'Password must be at least 8 characters long for new user.';
                    } else {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $ins_stmt = $db->prepare("
                            INSERT INTO admins (username, email, password_hash, full_name, role_id, status)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $ins_stmt->execute([$username, $email, $hash, $full_name, $role_id, $status]);
                        $new_id = $db->lastInsertId();
                        log_audit('create', 'users', "Created new admin user #{$new_id} ({$username})");
                        header('Location: ' . ADMIN_URL . '/users.php?msg=created');
                        exit;
                    }
                }
            }
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $success_msg = 'New administrator user created successfully!';
    if ($_GET['msg'] === 'updated') $success_msg = 'Administrator details updated successfully!';
    if ($_GET['msg'] === 'deleted') $success_msg = 'Administrator user removed successfully!';
}

// Fetch user for editing if action is edit
$user_to_edit = null;
if ($action === 'edit' && $edit_id > 0) {
    $u_stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
    $u_stmt->execute([$edit_id]);
    $user_to_edit = $u_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user_to_edit) {
        $action = 'list';
        $error_msg = 'Admin user not found.';
    }
}

// Fetch all users for listing
$users_stmt = $db->query("
    SELECT a.*, r.role_name 
    FROM admins a 
    JOIN admin_roles r ON a.role_id = r.id 
    ORDER BY a.id ASC
");
$all_users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-navy mb-1"><i class="fas fa-users-cog text-teal me-2"></i>Admin Users Management</h1>
            <p class="text-muted small mb-0">Manage authorized staff accounts, roles, access levels, and security credentials.</p>
        </div>
        <div class="col-auto">
            <?php if ($action !== 'list'): ?>
                <a href="<?= ADMIN_URL ?>/users.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to User List
                </a>
            <?php else: ?>
                <a href="<?= ADMIN_URL ?>/users.php?action=add" class="btn btn-gold btn-sm fw-bold">
                    <i class="fas fa-user-plus me-1"></i> Add New Admin
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add / Edit Form Card -->
        <div class="card border-0 shadow-sm mb-4" style="max-width: 800px;">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-navy">
                    <i class="fas <?= $action === 'edit' ? 'fa-user-edit' : 'fa-user-plus' ?> text-teal me-2"></i>
                    <?= $action === 'edit' ? 'Edit Admin User: ' . htmlspecialchars($user_to_edit['username']) : 'Create New Administrator' ?>
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user_to_edit['username'] ?? '') ?>" required <?= $action === 'edit' ? 'readonly' : '' ?>>
                            <div class="form-text">Used to sign in. Alphanumeric only.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Official Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user_to_edit['email'] ?? '') ?>" required>
                            <div class="form-text">For account notifications &amp; recovery.</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user_to_edit['full_name'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" <?= ($action === 'edit' && $user_to_edit['id'] === $current_admin_id) ? 'disabled' : '' ?>>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= (isset($user_to_edit['role_id']) && $user_to_edit['role_id'] == $r['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['role_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($action === 'edit' && $user_to_edit['id'] === $current_admin_id): ?>
                                <input type="hidden" name="role_id" value="<?= $user_to_edit['role_id'] ?>">
                                <div class="form-text text-muted">You cannot change your own role.</div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Account Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" <?= ($action === 'edit' && $user_to_edit['id'] === $current_admin_id) ? 'disabled' : '' ?>>
                                <option value="active" <?= (isset($user_to_edit['status']) && $user_to_edit['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= (isset($user_to_edit['status']) && $user_to_edit['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                <option value="suspended" <?= (isset($user_to_edit['status']) && $user_to_edit['status'] === 'suspended') ? 'selected' : '' ?>>Suspended</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">
                                <?= $action === 'edit' ? 'Reset Password (Leave blank to keep unchanged)' : 'Password <span class="text-danger">*</span>' ?>
                            </label>
                            <input type="password" name="password" class="form-control" <?= $action === 'add' ? 'required' : '' ?> minlength="8" placeholder="Minimum 8 characters">
                            <div class="form-text">Must be at least 8 characters. Uses strong bcrypt hashing.</div>
                        </div>

                        <div class="col-12 mt-4 pt-2 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-navy px-4 fw-bold">
                                <i class="fas fa-save me-1"></i> <?= $action === 'edit' ? 'Update User' : 'Create User' ?>
                            </button>
                            <a href="<?= ADMIN_URL ?>/users.php" class="btn btn-outline-secondary px-3">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- User List Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-navy"><i class="fas fa-users text-teal me-2"></i>Active Administrator Accounts</h6>
            <span class="badge bg-navy"><?= count($all_users) ?> Accounts</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_users as $u): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-navy text-white rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="width:36px; height:36px; font-size:0.85rem;">
                                            <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-navy"><?= htmlspecialchars($u['full_name']) ?></div>
                                            <div class="text-muted small">@<?= htmlspecialchars($u['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= htmlspecialchars($u['email']) ?></span>
                                </td>
                                <td>
                                    <?php if ($u['role_name'] === 'Super Admin'): ?>
                                        <span class="badge bg-danger text-white"><i class="fas fa-shield-alt me-1"></i> Super Admin</span>
                                    <?php elseif ($u['role_name'] === 'Editor'): ?>
                                        <span class="badge bg-info text-dark"><i class="fas fa-edit me-1"></i> Editor</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary text-white"><?= htmlspecialchars($u['role_name']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['status'] === 'active'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">Active</span>
                                    <?php elseif ($u['status'] === 'inactive'): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">Inactive</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="small text-muted">
                                        <?= $u['last_login'] ? date('M d, Y H:i', strtotime($u['last_login'])) : 'Never' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= ADMIN_URL ?>/users.php?action=edit&id=<?= $u['id'] ?>" class="btn btn-outline-navy btn-sm me-1" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($u['id'] !== $current_admin_id): ?>
                                        <a href="<?= ADMIN_URL ?>/users.php?action=delete&id=<?= $u['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" 
                                           class="btn btn-outline-danger btn-sm" 
                                           onclick="return confirm('Are you sure you want to permanently delete administrator <?= htmlspecialchars($u['username']) ?>?');" 
                                           title="Delete User">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
