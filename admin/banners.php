<?php
/**
 * Admin Banner Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$page_title = 'Hero Banners Management';
$current_page = 'banners';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$action = clean_input($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);

$alert_msg = null;
$alert_type = 'success';

// Handle Delete
if ($action === 'delete' && $id > 0) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $db->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);
        log_audit('Delete Banner', 'Banners', "Deleted banner ID: {$id}");
        header("Location: " . ADMIN_URL . "/banners.php?msg=deleted");
        exit;
    }
}

// Handle Form Submission (Create or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_banner'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $alert_msg = 'Security token invalid. Please try again.';
        $alert_type = 'danger';
    } else {
        $title = clean_input($_POST['title'] ?? '');
        $subtitle = clean_input($_POST['subtitle'] ?? '');
        $primary_btn_text = clean_input($_POST['primary_btn_text'] ?? 'Discover Our Mission');
        $primary_btn_url = clean_input($_POST['primary_btn_url'] ?? 'about.php');
        $secondary_btn_text = clean_input($_POST['secondary_btn_text'] ?? 'Become a Volunteer');
        $secondary_btn_url = clean_input($_POST['secondary_btn_url'] ?? 'volunteers.php');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $image_path = clean_input($_POST['current_image_path'] ?? 'assets/images/branding/banner-en.png');

        // File upload check
        if (!empty($_FILES['banner_file']['name'])) {
            $upload = handle_file_upload($_FILES['banner_file'], 'banners');
            if ($upload['success']) {
                $image_path = $upload['path'];
            } else {
                $alert_msg = 'Image Upload Error: ' . $upload['error'];
                $alert_type = 'danger';
            }
        }

        if (empty($alert_msg)) {
            if ($id > 0) {
                // Update
                $stmt = $db->prepare("
                    UPDATE banners 
                    SET title = ?, subtitle = ?, image_path = ?, primary_btn_text = ?, primary_btn_url = ?, 
                        secondary_btn_text = ?, secondary_btn_url = ?, sort_order = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $subtitle, $image_path, $primary_btn_text, $primary_btn_url, $secondary_btn_text, $secondary_btn_url, $sort_order, $is_active, $id]);
                log_audit('Update Banner', 'Banners', "Updated banner ID {$id}: {$title}");
                $alert_msg = 'Banner updated successfully!';
            } else {
                // Insert
                $stmt = $db->prepare("
                    INSERT INTO banners (title, subtitle, image_path, primary_btn_text, primary_btn_url, secondary_btn_text, secondary_btn_url, sort_order, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $subtitle, $image_path, $primary_btn_text, $primary_btn_url, $secondary_btn_text, $secondary_btn_url, $sort_order, $is_active]);
                log_audit('Create Banner', 'Banners', "Created new banner: {$title}");
                $alert_msg = 'New banner created successfully!';
            }
            $action = 'list';
        }
    }
}

// Fetch banner for edit
$edit_banner = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM banners WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $edit_banner = $stmt->fetch();
    if (!$edit_banner) $action = 'list';
}

// List all banners
$banners = $db->query("SELECT * FROM banners ORDER BY sort_order ASC, id ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-1">Hero Banner Slides</h4>
        <small class="text-muted">Manage homepage top slider slides, images, and call-to-action buttons</small>
    </div>
    <?php if ($action === 'list'): ?>
        <a href="<?= ADMIN_URL ?>/banners.php?action=create" class="btn btn-sapsada-teal btn-sm">
            <i class="fas fa-plus me-1"></i> Add New Slide
        </a>
    <?php else: ?>
        <a href="<?= ADMIN_URL ?>/banners.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Banner List
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
    <!-- Form Card -->
    <div class="card border-0 shadow-card rounded-4 p-4 p-md-5 bg-white max-w-800 mx-auto">
        <h5 class="fw-bold text-navy mb-4"><?= $action === 'edit' ? 'Edit Banner Slide' : 'Add New Banner Slide' ?></h5>
        
        <form action="<?= ADMIN_URL ?>/banners.php<?= $id > 0 ? '?action=edit&id='.$id : '?action=create' ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="current_image_path" value="<?= e($edit_banner['image_path'] ?? 'assets/images/branding/banner-en.png') ?>">

            <div class="mb-3">
                <label class="form-label small fw-bold text-navy">Headline / Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required value="<?= e($edit_banner['title'] ?? '') ?>" placeholder="e.g. Together We Care. Together We Protect Life.">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-navy">Subtitle / Description <span class="text-danger">*</span></label>
                <textarea name="subtitle" class="form-control" rows="2" required placeholder="Slide description"><?= e($edit_banner['subtitle'] ?? '') ?></textarea>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Primary Button Text</label>
                    <input type="text" name="primary_btn_text" class="form-control" value="<?= e($edit_banner['primary_btn_text'] ?? 'Discover Our Mission') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Primary Button Link</label>
                    <input type="text" name="primary_btn_url" class="form-control" value="<?= e($edit_banner['primary_btn_url'] ?? 'about.php') ?>">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Secondary Button Text</label>
                    <input type="text" name="secondary_btn_text" class="form-control" value="<?= e($edit_banner['secondary_btn_text'] ?? 'Become a Volunteer') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Secondary Button Link</label>
                    <input type="text" name="secondary_btn_url" class="form-control" value="<?= e($edit_banner['secondary_btn_url'] ?? 'volunteers.php') ?>">
                </div>
            </div>

            <!-- Image Upload -->
            <div class="mb-4">
                <label class="form-label small fw-bold text-navy">Banner Image</label>
                <input type="file" name="banner_file" class="form-control" accept="image/*" data-preview="bannerPreview">
                <small class="text-muted">Recommended: 1920x800 high-resolution photograph (Max 5MB)</small>
                
                <div class="mt-2">
                    <img id="bannerPreview" src="<?= ASSETS_URL ?>/../<?= e($edit_banner['image_path'] ?? 'assets/images/branding/banner-en.png') ?>" alt="Preview" class="rounded-3 shadow-subtle border" style="max-height: 140px; width: auto; object-fit: cover;">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Display Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= e($edit_banner['sort_order'] ?? 1) ?>">
                </div>
                <div class="col-md-6 d-flex align-items-center mt-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch" <?= (!isset($edit_banner) || $edit_banner['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold text-navy" for="activeSwitch">Active on Homepage</label>
                    </div>
                </div>
            </div>

            <button type="submit" name="save_banner" class="btn btn-sapsada-teal px-4">
                <i class="fas fa-save me-1"></i> Save Banner Slide
            </button>
        </form>
    </div>

<?php else: ?>
    <!-- Table Card -->
    <div class="admin-table-card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">Order</th>
                        <th style="width: 140px;">Image</th>
                        <th>Headline &amp; Subtitle</th>
                        <th>Buttons</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($banners as $b): ?>
                        <tr>
                            <td class="fw-bold text-center"><?= $b['sort_order'] ?></td>
                            <td>
                                <img src="<?= ASSETS_URL ?>/../<?= e($b['image_path']) ?>" class="rounded-2 shadow-subtle border" style="width: 100px; height: 50px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-bold text-navy"><?= e($b['title']) ?></div>
                                <small class="text-muted text-truncate d-block" style="max-width: 380px;"><?= e($b['subtitle']) ?></small>
                            </td>
                            <td>
                                <small class="badge bg-light text-navy border me-1"><?= e($b['primary_btn_text']) ?></small>
                                <small class="badge bg-light text-secondary border"><?= e($b['secondary_btn_text']) ?></small>
                            </td>
                            <td>
                                <?php if ($b['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Hidden</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= ADMIN_URL ?>/banners.php?action=edit&id=<?= $b['id'] ?>" class="btn btn-light btn-sm text-primary me-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?= ADMIN_URL ?>/banners.php?action=delete&id=<?= $b['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-light btn-sm text-danger btn-confirm-delete" data-name="this banner slide">
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
