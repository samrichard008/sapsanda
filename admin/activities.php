<?php
/**
 * Admin Activities Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$page_title = 'Activities Management';
$current_page = 'activities';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$action = clean_input($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);

$alert_msg = null;
$alert_type = 'success';

$categories = [
    'Social Welfare Programs',
    'Community Development',
    'Life Protection Awareness',
    'Emergency Support',
    'Environmental Activities',
    'Educational Assistance',
    'Health Awareness',
    'Public Awareness Campaigns',
    'Elderly Support',
    'Children\'s Welfare',
    'Family Support',
    'Special Community Projects'
];

// Handle Delete
if ($action === 'delete' && $id > 0) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $db->prepare("DELETE FROM activities WHERE id = ?")->execute([$id]);
        log_audit('Delete Activity', 'Activities', "Deleted activity ID {$id}");
        header("Location: " . ADMIN_URL . "/activities.php?msg=deleted");
        exit;
    }
}

// Handle Save (Create or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_activity'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $alert_msg = 'Security token invalid.';
        $alert_type = 'danger';
    } else {
        $category = clean_input($_POST['category'] ?? 'Social Welfare Programs');
        $title_en = clean_input($_POST['title_en'] ?? '');
        $title_si = clean_input($_POST['title_si'] ?? '');
        $title_ta = clean_input($_POST['title_ta'] ?? '');
        $desc_en = $_POST['description_en'] ?? '';
        $desc_si = $_POST['description_si'] ?? '';
        $desc_ta = $_POST['description_ta'] ?? '';
        $location = clean_input($_POST['location'] ?? '');
        $activity_date = clean_input($_POST['activity_date'] ?? date('Y-m-d'));
        $status = clean_input($_POST['status'] ?? 'active');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $featured_image = clean_input($_POST['current_featured_image'] ?? 'assets/images/activities/swim-protection.jpg');

        // Slug creation
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title_en), '-'));
        if (empty($slug)) $slug = 'activity-' . time();

        // Image upload
        if (!empty($_FILES['image_file']['name'])) {
            $upload = handle_file_upload($_FILES['image_file'], 'activities');
            if ($upload['success']) {
                $featured_image = $upload['path'];
            } else {
                $alert_msg = 'Image Upload Error: ' . $upload['error'];
                $alert_type = 'danger';
            }
        }

        if (empty($alert_msg)) {
            if ($id > 0) {
                // Update Activity
                $stmt = $db->prepare("
                    UPDATE activities 
                    SET category = ?, featured_image = ?, location = ?, activity_date = ?, status = ?, is_featured = ?
                    WHERE id = ?
                ");
                $stmt->execute([$category, $featured_image, $location, $activity_date, $status, $is_featured, $id]);

                // Update Translations
                $upsert = $db->prepare("
                    INSERT INTO activity_translations (activity_id, lang, title, description)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description)
                ");
                $upsert->execute([$id, 'en', $title_en, $desc_en]);
                if (!empty($title_si)) $upsert->execute([$id, 'si', $title_si, $desc_si]);
                if (!empty($title_ta)) $upsert->execute([$id, 'ta', $title_ta, $desc_ta]);

                log_audit('Update Activity', 'Activities', "Updated activity ID {$id}: {$title_en}");
                $alert_msg = 'Activity updated successfully!';
            } else {
                // Insert Activity
                $stmt = $db->prepare("
                    INSERT INTO activities (category, slug, featured_image, location, activity_date, status, is_featured)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$category, $slug, $featured_image, $location, $activity_date, $status, $is_featured]);
                $new_id = (int)$db->lastInsertId();

                // Insert Translations
                $ins_trans = $db->prepare("
                    INSERT INTO activity_translations (activity_id, lang, title, description)
                    VALUES (?, ?, ?, ?)
                ");
                $ins_trans->execute([$new_id, 'en', $title_en, $desc_en]);
                if (!empty($title_si)) $ins_trans->execute([$new_id, 'si', $title_si, $desc_si]);
                if (!empty($title_ta)) $ins_trans->execute([$new_id, 'ta', $title_ta, $desc_ta]);

                log_audit('Create Activity', 'Activities', "Created activity ID {$new_id}: {$title_en}");
                $alert_msg = 'New activity created successfully!';
            }
            $action = 'list';
        }
    }
}

// Fetch for edit
$edit_act = null;
$edit_trans = [];
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM activities WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $edit_act = $stmt->fetch();
    
    if ($edit_act) {
        $stmt_t = $db->prepare("SELECT * FROM activity_translations WHERE activity_id = ?");
        $stmt_t->execute([$id]);
        while ($t = $stmt_t->fetch()) {
            $edit_trans[$t['lang']] = $t;
        }
    } else {
        $action = 'list';
    }
}

// List all
$activities = $db->query("
    SELECT a.*, COALESCE(at.title, a.slug) AS display_title 
    FROM activities a
    LEFT JOIN activity_translations at ON a.id = at.activity_id AND at.lang = 'en'
    ORDER BY a.activity_date DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-1">Our Core Activities</h4>
        <small class="text-muted">Manage social welfare, water safety, and environmental operations</small>
    </div>
    <?php if ($action === 'list'): ?>
        <a href="<?= ADMIN_URL ?>/activities.php?action=create" class="btn btn-sapsada-teal btn-sm">
            <i class="fas fa-plus me-1"></i> Add Activity
        </a>
    <?php else: ?>
        <a href="<?= ADMIN_URL ?>/activities.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Activity List
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
    <div class="card border-0 shadow-card rounded-4 p-4 p-md-5 bg-white">
        <h5 class="fw-bold text-navy mb-4"><?= $action === 'edit' ? 'Edit Activity Details' : 'Create New Activity' ?></h5>

        <form action="<?= ADMIN_URL ?>/activities.php<?= $id > 0 ? '?action=edit&id='.$id : '?action=create' ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="current_featured_image" value="<?= e($edit_act['featured_image'] ?? 'assets/images/activities/swim-protection.jpg') ?>">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= (($edit_act['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-navy">Activity Date <span class="text-danger">*</span></label>
                    <input type="date" name="activity_date" class="form-control" required value="<?= e($edit_act['activity_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-navy">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (($edit_act['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active / Ongoing</option>
                        <option value="completed" <?= (($edit_act['status'] ?? '') === 'completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="upcoming" <?= (($edit_act['status'] ?? '') === 'upcoming') ? 'selected' : '' ?>>Upcoming</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-navy">Location / Venue <span class="text-danger">*</span></label>
                <input type="text" name="location" class="form-control" required placeholder="e.g. Panadura Canal / Lighthouse Beach" value="<?= e($edit_act['location'] ?? '') ?>">
            </div>

            <!-- Image Upload -->
            <div class="mb-4">
                <label class="form-label small fw-bold text-navy">Featured Image</label>
                <input type="file" name="image_file" class="form-control" accept="image/*" data-preview="actPreview">
                <div class="mt-2">
                    <img id="actPreview" src="<?= ASSETS_URL ?>/../<?= e($edit_act['featured_image'] ?? 'assets/images/activities/swim-protection.jpg') ?>" class="rounded-3 shadow-subtle border" style="max-height: 120px; object-fit: cover;">
                </div>
            </div>

            <!-- Multilingual Tabs -->
            <ul class="nav nav-tabs mb-3" id="langTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold" id="en-tab" data-bs-toggle="tab" data-bs-target="#tab-en" type="button">English (Primary)</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="si-tab" data-bs-toggle="tab" data-bs-target="#tab-si" type="button">සිංහල (Sinhala)</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="ta-tab" data-bs-toggle="tab" data-bs-target="#tab-ta" type="button">தமிழ் (Tamil)</button>
                </li>
            </ul>

            <div class="tab-content mb-4" id="langTabsContent">
                <!-- English -->
                <div class="tab-pane fade show active" id="tab-en">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Activity Title (English) <span class="text-danger">*</span></label>
                        <input type="text" name="title_en" class="form-control" required value="<?= e($edit_trans['en']['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Description (English)</label>
                        <textarea name="description_en" class="form-control" rows="5"><?= e($edit_trans['en']['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Sinhala -->
                <div class="tab-pane fade" id="tab-si">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Activity Title (සිංහල)</label>
                        <input type="text" name="title_si" class="form-control" value="<?= e($edit_trans['si']['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Description (සිංහල)</label>
                        <textarea name="description_si" class="form-control" rows="5"><?= e($edit_trans['si']['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Tamil -->
                <div class="tab-pane fade" id="tab-ta">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Activity Title (தமிழ்)</label>
                        <input type="text" name="title_ta" class="form-control" value="<?= e($edit_trans['ta']['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Description (தமிழ்)</label>
                        <textarea name="description_ta" class="form-control" rows="5"><?= e($edit_trans['ta']['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="is_featured" id="featCheck" <?= (!isset($edit_act) || $edit_act['is_featured']) ? 'checked' : '' ?>>
                <label class="form-check-label fw-bold small text-navy" for="featCheck">Display as Featured on Homepage</label>
            </div>

            <button type="submit" name="save_activity" class="btn btn-sapsada-teal px-4">
                <i class="fas fa-save me-1"></i> Save Activity
            </button>
        </form>
    </div>

<?php else: ?>
    <!-- Table -->
    <div class="admin-table-card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Title &amp; Category</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $act): ?>
                        <tr>
                            <td>
                                <img src="<?= ASSETS_URL ?>/../<?= e($act['featured_image']) ?>" class="rounded-2 shadow-subtle border" style="width: 70px; height: 50px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-bold text-navy"><?= e($act['display_title']) ?></div>
                                <span class="badge bg-light text-navy border small"><?= e($act['category']) ?></span>
                            </td>
                            <td><small><i class="fas fa-map-marker-alt text-teal me-1"></i> <?= e($act['location']) ?></small></td>
                            <td><small><?= date('M d, Y', strtotime($act['activity_date'])) ?></small></td>
                            <td><span class="badge-status status-<?= e($act['status']) ?>"><?= ucfirst($act['status']) ?></span></td>
                            <td class="text-end">
                                <a href="<?= ADMIN_URL ?>/activities.php?action=edit&id=<?= $act['id'] ?>" class="btn btn-light btn-sm text-primary me-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?= ADMIN_URL ?>/activities.php?action=delete&id=<?= $act['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-light btn-sm text-danger btn-confirm-delete" data-name="this activity">
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
