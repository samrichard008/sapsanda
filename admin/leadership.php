<?php
/**
 * Admin Leadership Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$page_title = 'Leadership & Governance';
$current_page = 'leadership';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$action = clean_input($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);

$alert_msg = null;
$alert_type = 'success';

// Handle Delete
if ($action === 'delete' && $id > 0) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $db->prepare("DELETE FROM leadership WHERE id = ?")->execute([$id]);
        log_audit('Delete Leader', 'Leadership', "Deleted leader ID {$id}");
        header("Location: " . ADMIN_URL . "/leadership.php?msg=deleted");
        exit;
    }
}

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_leader'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $alert_msg = 'Security token invalid.';
        $alert_type = 'danger';
    } else {
        $name = clean_input($_POST['name'] ?? '');
        $position = clean_input($_POST['position'] ?? '');
        $bio = clean_input($_POST['bio'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');
        $email = clean_input($_POST['email'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $photo = clean_input($_POST['current_photo'] ?? 'assets/images/leadership/leader-award.jpg');

        if (!empty($_FILES['photo_file']['name'])) {
            $upload = handle_file_upload($_FILES['photo_file'], 'leadership');
            if ($upload['success']) {
                $photo = $upload['path'];
            }
        }

        if ($id > 0) {
            $stmt = $db->prepare("
                UPDATE leadership 
                SET name = ?, position = ?, bio = ?, phone = ?, email = ?, sort_order = ?, is_active = ?, photo = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $position, $bio, $phone, $email, $sort_order, $is_active, $photo, $id]);
            log_audit('Update Leader', 'Leadership', "Updated leader profile: {$name}");
            $alert_msg = 'Profile updated successfully!';
        } else {
            $stmt = $db->prepare("
                INSERT INTO leadership (name, position, bio, phone, email, sort_order, is_active, photo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $position, $bio, $phone, $email, $sort_order, $is_active, $photo]);
            log_audit('Create Leader', 'Leadership', "Added leader profile: {$name}");
            $alert_msg = 'New leader profile added!';
        }
        $action = 'list';
    }
}

// Fetch for edit
$edit_leader = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM leadership WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $edit_leader = $stmt->fetch();
}

$leaders = $db->query("SELECT * FROM leadership ORDER BY sort_order ASC, id ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-1">Leadership &amp; Management</h4>
        <small class="text-muted">Manage governing trustees, divisional heads, and rescue marshals</small>
    </div>
    <?php if ($action === 'list'): ?>
        <a href="<?= ADMIN_URL ?>/leadership.php?action=create" class="btn btn-sapsada-teal btn-sm">
            <i class="fas fa-plus me-1"></i> Add Leader Profile
        </a>
    <?php else: ?>
        <a href="<?= ADMIN_URL ?>/leadership.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Leaders
        </a>
    <?php endif; ?>
</div>

<?php if ($alert_msg): ?>
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show rounded-3" role="alert">
        <?= e($alert_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($action === 'create' || $action === 'edit'): ?>
    <div class="card border-0 shadow-card rounded-4 p-4 p-md-5 bg-white max-w-800">
        <h5 class="fw-bold text-navy mb-4"><?= $action === 'edit' ? 'Edit Leadership Profile' : 'Add New Profile' ?></h5>

        <form action="<?= ADMIN_URL ?>/leadership.php<?= $id > 0 ? '?action=edit&id='.$id : '?action=create' ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="current_photo" value="<?= e($edit_leader['photo'] ?? 'assets/images/leadership/leader-award.jpg') ?>">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="<?= e($edit_leader['name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Position / Title <span class="text-danger">*</span></label>
                    <input type="text" name="position" class="form-control" required placeholder="e.g. Director of Aquatic Safety" value="<?= e($edit_leader['position'] ?? '') ?>">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Direct Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?= e($edit_leader['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Official Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($edit_leader['email'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-navy">Biography / Responsibilities</label>
                <textarea name="bio" class="form-control" rows="4"><?= e($edit_leader['bio'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-navy">Photograph</label>
                <input type="file" name="photo_file" class="form-control" accept="image/*" data-preview="leaderPreview">
                <div class="mt-2">
                    <img id="leaderPreview" src="<?= ASSETS_URL ?>/../<?= e($edit_leader['photo'] ?? 'assets/images/leadership/leader-award.jpg') ?>" class="rounded-3 shadow-subtle border" style="width: 90px; height: 110px; object-fit: cover;">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Display Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= e($edit_leader['sort_order'] ?? 1) ?>">
                </div>
                <div class="col-md-6 d-flex align-items-center mt-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="actSw" <?= (!isset($edit_leader) || $edit_leader['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold text-navy" for="actSw">Active &amp; Visible on About Us Page</label>
                    </div>
                </div>
            </div>

            <button type="submit" name="save_leader" class="btn btn-sapsada-teal px-4">
                <i class="fas fa-save me-1"></i> Save Profile
            </button>
        </form>
    </div>

<?php else: ?>
    <div class="admin-table-card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">Photo</th>
                        <th>Name &amp; Position</th>
                        <th>Contact</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaders as $l): ?>
                        <tr>
                            <td><img src="<?= ASSETS_URL ?>/../<?= e($l['photo']) ?>" class="rounded-circle shadow-subtle border" style="width:50px; height:50px; object-fit:cover;"></td>
                            <td>
                                <div class="fw-bold text-navy"><?= e($l['name']) ?></div>
                                <span class="badge bg-light text-navy border small"><?= e($l['position']) ?></span>
                            </td>
                            <td>
                                <small class="d-block"><?= e($l['phone'] ?: 'N/A') ?></small>
                                <small class="text-muted"><?= e($l['email'] ?: 'N/A') ?></small>
                            </td>
                            <td class="fw-bold"><?= $l['sort_order'] ?></td>
                            <td><span class="badge bg-<?= $l['is_active'] ? 'success' : 'secondary' ?>"><?= $l['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                            <td class="text-end">
                                <a href="<?= ADMIN_URL ?>/leadership.php?action=edit&id=<?= $l['id'] ?>" class="btn btn-light btn-sm text-primary me-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?= ADMIN_URL ?>/leadership.php?action=delete&id=<?= $l['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-light btn-sm text-danger btn-confirm-delete" data-name="profile">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
