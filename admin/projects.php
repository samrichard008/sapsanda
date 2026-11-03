<?php
/**
 * Admin Projects Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$page_title = 'Projects Management';
$current_page = 'projects';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$action = clean_input($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);

$alert_msg = null;
$alert_type = 'success';

// Handle Delete
if ($action === 'delete' && $id > 0) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $db->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
        log_audit('Delete Project', 'Projects', "Deleted project ID {$id}");
        header("Location: " . ADMIN_URL . "/projects.php?msg=deleted");
        exit;
    }
}

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_project'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $alert_msg = 'Security token invalid.';
        $alert_type = 'danger';
    } else {
        $category = clean_input($_POST['category'] ?? 'Life Protection');
        $title_en = clean_input($_POST['title_en'] ?? '');
        $title_si = clean_input($_POST['title_si'] ?? '');
        $title_ta = clean_input($_POST['title_ta'] ?? '');
        $desc_en = $_POST['description_en'] ?? '';
        $desc_si = $_POST['description_si'] ?? '';
        $desc_ta = $_POST['description_ta'] ?? '';
        $location = clean_input($_POST['location'] ?? '');
        $start_date = clean_input($_POST['start_date'] ?? date('Y-m-d'));
        $end_date = !empty($_POST['end_date']) ? clean_input($_POST['end_date']) : null;
        $status = clean_input($_POST['status'] ?? 'Ongoing');
        $featured_image = clean_input($_POST['current_featured_image'] ?? 'assets/images/projects/canal-cleanup-1.jpg');

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title_en), '-'));
        if (empty($slug)) $slug = 'project-' . time();

        // Image upload
        if (!empty($_FILES['image_file']['name'])) {
            $upload = handle_file_upload($_FILES['image_file'], 'projects');
            if ($upload['success']) {
                $featured_image = $upload['path'];
            } else {
                $alert_msg = 'Image Upload Error: ' . $upload['error'];
                $alert_type = 'danger';
            }
        }

        if (empty($alert_msg)) {
            if ($id > 0) {
                // Update
                $stmt = $db->prepare("
                    UPDATE projects 
                    SET category = ?, featured_image = ?, location = ?, start_date = ?, end_date = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$category, $featured_image, $location, $start_date, $end_date, $status, $id]);

                $upsert = $db->prepare("
                    INSERT INTO project_translations (project_id, lang, title, description)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description)
                ");
                $upsert->execute([$id, 'en', $title_en, $desc_en]);
                if (!empty($title_si)) $upsert->execute([$id, 'si', $title_si, $desc_si]);
                if (!empty($title_ta)) $upsert->execute([$id, 'ta', $title_ta, $desc_ta]);

                log_audit('Update Project', 'Projects', "Updated project ID {$id}: {$title_en}");
                $alert_msg = 'Project updated successfully!';
            } else {
                // Insert
                $stmt = $db->prepare("
                    INSERT INTO projects (category, slug, featured_image, location, start_date, end_date, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$category, $slug, $featured_image, $location, $start_date, $end_date, $status]);
                $new_id = (int)$db->lastInsertId();

                $ins_trans = $db->prepare("
                    INSERT INTO project_translations (project_id, lang, title, description)
                    VALUES (?, ?, ?, ?)
                ");
                $ins_trans->execute([$new_id, 'en', $title_en, $desc_en]);
                if (!empty($title_si)) $ins_trans->execute([$new_id, 'si', $title_si, $desc_si]);
                if (!empty($title_ta)) $ins_trans->execute([$new_id, 'ta', $title_ta, $desc_ta]);

                log_audit('Create Project', 'Projects', "Created project ID {$new_id}: {$title_en}");
                $alert_msg = 'New project created successfully!';
            }
            $action = 'list';
        }
    }
}

// Fetch for edit
$edit_proj = null;
$edit_trans = [];
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $edit_proj = $stmt->fetch();
    
    if ($edit_proj) {
        $stmt_t = $db->prepare("SELECT * FROM project_translations WHERE project_id = ?");
        $stmt_t->execute([$id]);
        while ($t = $stmt_t->fetch()) {
            $edit_trans[$t['lang']] = $t;
        }
    } else {
        $action = 'list';
    }
}

// List all
$projects = $db->query("
    SELECT p.*, COALESCE(pt.title, p.slug) AS display_title 
    FROM projects p
    LEFT JOIN project_translations pt ON p.id = pt.project_id AND pt.lang = 'en'
    ORDER BY p.start_date DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-1">Strategic Projects</h4>
        <small class="text-muted">Manage planned, ongoing, and completed community welfare operations</small>
    </div>
    <?php if ($action === 'list'): ?>
        <a href="<?= ADMIN_URL ?>/projects.php?action=create" class="btn btn-sapsada-teal btn-sm">
            <i class="fas fa-plus me-1"></i> Add Project
        </a>
    <?php else: ?>
        <a href="<?= ADMIN_URL ?>/projects.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Projects List
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
        <h5 class="fw-bold text-navy mb-4"><?= $action === 'edit' ? 'Edit Project' : 'Create New Project' ?></h5>

        <form action="<?= ADMIN_URL ?>/projects.php<?= $id > 0 ? '?action=edit&id='.$id : '?action=create' ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="current_featured_image" value="<?= e($edit_proj['featured_image'] ?? 'assets/images/projects/canal-cleanup-1.jpg') ?>">

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-navy">Category <span class="text-danger">*</span></label>
                    <input type="text" name="category" class="form-control" required placeholder="e.g. Life Protection, Environmental" value="<?= e($edit_proj['category'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-navy">Location <span class="text-danger">*</span></label>
                    <input type="text" name="location" class="form-control" required placeholder="e.g. Western Province Coastline" value="<?= e($edit_proj['location'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-navy">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select">
                        <option value="Planned" <?= (($edit_proj['status'] ?? '') === 'Planned') ? 'selected' : '' ?>>Planned</option>
                        <option value="Ongoing" <?= (($edit_proj['status'] ?? 'Ongoing') === 'Ongoing') ? 'selected' : '' ?>>Ongoing</option>
                        <option value="Completed" <?= (($edit_proj['status'] ?? '') === 'Completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="Archived" <?= (($edit_proj['status'] ?? '') === 'Archived') ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" required value="<?= e($edit_proj['start_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">End Date (Leave blank if ongoing)</label>
                    <input type="date" name="end_date" class="form-control" value="<?= e($edit_proj['end_date'] ?? '') ?>">
                </div>
            </div>

            <!-- Image Upload -->
            <div class="mb-4">
                <label class="form-label small fw-bold text-navy">Featured Project Photo</label>
                <input type="file" name="image_file" class="form-control" accept="image/*" data-preview="projPreview">
                <div class="mt-2">
                    <img id="projPreview" src="<?= ASSETS_URL ?>/../<?= e($edit_proj['featured_image'] ?? 'assets/images/projects/canal-cleanup-1.jpg') ?>" class="rounded-3 shadow-subtle border" style="max-height: 120px; object-fit: cover;">
                </div>
            </div>

            <!-- Multilingual Tabs -->
            <ul class="nav nav-tabs mb-3" id="langTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold" id="en-tab" data-bs-toggle="tab" data-bs-target="#tab-en" type="button">English</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="si-tab" data-bs-toggle="tab" data-bs-target="#tab-si" type="button">සිංහල</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="ta-tab" data-bs-toggle="tab" data-bs-target="#tab-ta" type="button">தமிழ்</button>
                </li>
            </ul>

            <div class="tab-content mb-4" id="langTabsContent">
                <!-- English -->
                <div class="tab-pane fade show active" id="tab-en">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Project Title (English) <span class="text-danger">*</span></label>
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
                        <label class="form-label small fw-bold text-navy">Project Title (සිංහල)</label>
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
                        <label class="form-label small fw-bold text-navy">Project Title (தமிழ்)</label>
                        <input type="text" name="title_ta" class="form-control" value="<?= e($edit_trans['ta']['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Description (தமிழ்)</label>
                        <textarea name="description_ta" class="form-control" rows="5"><?= e($edit_trans['ta']['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <button type="submit" name="save_project" class="btn btn-sapsada-teal px-4">
                <i class="fas fa-save me-1"></i> Save Project
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
                        <th style="width: 80px;">Photo</th>
                        <th>Project Title &amp; Category</th>
                        <th>Location</th>
                        <th>Timeline</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $proj): ?>
                        <tr>
                            <td>
                                <img src="<?= ASSETS_URL ?>/../<?= e($proj['featured_image']) ?>" class="rounded-2 shadow-subtle border" style="width: 70px; height: 50px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-bold text-navy"><?= e($proj['display_title']) ?></div>
                                <span class="badge bg-light text-navy border small"><?= e($proj['category']) ?></span>
                            </td>
                            <td><small><i class="fas fa-map-marker-alt text-teal me-1"></i> <?= e($proj['location']) ?></small></td>
                            <td><small><?= date('M Y', strtotime($proj['start_date'])) ?> &rarr; <?= $proj['end_date'] ? date('M Y', strtotime($proj['end_date'])) : 'Present' ?></small></td>
                            <td><span class="badge-status status-<?= e($proj['status']) ?>"><?= e($proj['status']) ?></span></td>
                            <td class="text-end">
                                <a href="<?= ADMIN_URL ?>/projects.php?action=edit&id=<?= $proj['id'] ?>" class="btn btn-light btn-sm text-primary me-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?= ADMIN_URL ?>/projects.php?action=delete&id=<?= $proj['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-light btn-sm text-danger btn-confirm-delete" data-name="this project">
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
