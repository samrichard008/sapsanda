<?php
/**
 * Admin Donations & Campaigns Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$page_title = 'Donations & Campaigns';
$current_page = 'donations';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$tab = clean_input($_GET['tab'] ?? 'campaigns');
$action = clean_input($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);

$alert_msg = null;
$alert_type = 'success';

// Handle Slip Status Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_slip_status'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $slip_id = (int)$_POST['slip_id'];
        $status = clean_input($_POST['status'] ?? 'Pending');
        $db->prepare("UPDATE donation_enquiries SET status = ? WHERE id = ?")->execute([$status, $slip_id]);
        log_audit('Update Donation Slip Status', 'Donations', "Set status to {$status} for slip ID {$slip_id}");
        $alert_msg = "Donation notification marked as {$status}!";
    }
}

// Handle Campaign Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_campaign'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $alert_msg = 'Security token invalid.';
        $alert_type = 'danger';
    } else {
        $title = clean_input($_POST['title'] ?? '');
        $description = $_POST['description'] ?? '';
        $target_amount = (float)($_POST['target_amount'] ?? 0);
        $raised_amount = (float)($_POST['raised_amount'] ?? 0);
        $status = clean_input($_POST['status'] ?? 'active');
        $image_path = clean_input($_POST['current_image_path'] ?? 'assets/images/activities/swim-protection.jpg');

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        if (empty($slug)) $slug = 'campaign-' . time();

        if (!empty($_FILES['image_file']['name'])) {
            $upload = handle_file_upload($_FILES['image_file'], 'donations');
            if ($upload['success']) {
                $image_path = $upload['path'];
            }
        }

        if ($id > 0) {
            $stmt = $db->prepare("
                UPDATE donation_campaigns 
                SET title = ?, description = ?, target_amount = ?, raised_amount = ?, status = ?, image_path = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $description, $target_amount, $raised_amount, $status, $image_path, $id]);
            log_audit('Update Campaign', 'Donations', "Updated campaign {$title}");
            $alert_msg = 'Campaign updated successfully!';
        } else {
            $stmt = $db->prepare("
                INSERT INTO donation_campaigns (title, slug, description, target_amount, raised_amount, image_path, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $slug, $description, $target_amount, $raised_amount, $image_path, $status]);
            log_audit('Create Campaign', 'Donations', "Created campaign {$title}");
            $alert_msg = 'Campaign created successfully!';
        }
        $action = 'list';
    }
}

// Handle Campaign Delete
if ($action === 'delete_camp' && $id > 0) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $db->prepare("DELETE FROM donation_campaigns WHERE id = ?")->execute([$id]);
        log_audit('Delete Campaign', 'Donations', "Deleted campaign ID {$id}");
        header("Location: " . ADMIN_URL . "/donations.php?tab=campaigns&msg=deleted");
        exit;
    }
}

// Fetch Campaign for edit
$edit_camp = null;
if ($action === 'edit_camp' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM donation_campaigns WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $edit_camp = $stmt->fetch();
}

$campaigns = $db->query("SELECT * FROM donation_campaigns ORDER BY id ASC")->fetchAll();
$slips = $db->query("
    SELECT de.*, dc.title AS campaign_title 
    FROM donation_enquiries de
    LEFT JOIN donation_campaigns dc ON de.campaign_id = dc.id
    ORDER BY de.id DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-1">Donations &amp; Welfare Funds</h4>
        <small class="text-muted">Manage active campaigns and review public bank transfer notifications</small>
    </div>
    <?php if ($tab === 'campaigns' && $action === 'list'): ?>
        <a href="<?= ADMIN_URL ?>/donations.php?tab=campaigns&action=create_camp" class="btn btn-sapsada-teal btn-sm">
            <i class="fas fa-plus me-1"></i> Add Campaign
        </a>
    <?php endif; ?>
</div>

<?php if ($alert_msg): ?>
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show rounded-3" role="alert">
        <?= e($alert_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tabs -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a href="<?= ADMIN_URL ?>/donations.php?tab=campaigns" class="nav-link <?= $tab === 'campaigns' ? 'active bg-navy' : 'text-navy' ?> rounded-pill px-4">
            <i class="fas fa-hand-holding-usd me-1"></i> Campaigns (<?= count($campaigns) ?>)
        </a>
    </li>
    <li class="nav-item">
        <a href="<?= ADMIN_URL ?>/donations.php?tab=slips" class="nav-link <?= $tab === 'slips' ? 'active bg-navy' : 'text-navy' ?> rounded-pill px-4">
            <i class="fas fa-receipt me-1"></i> Bank Slip Notifications (<?= count($slips) ?>)
        </a>
    </li>
</ul>

<?php if ($tab === 'campaigns'): ?>
    <?php if ($action === 'create_camp' || $action === 'edit_camp'): ?>
        <!-- Form -->
        <div class="card border-0 shadow-card rounded-4 p-4 p-md-5 bg-white max-w-800">
            <h5 class="fw-bold text-navy mb-4"><?= $action === 'edit_camp' ? 'Edit Campaign' : 'Create Campaign' ?></h5>
            <form action="<?= ADMIN_URL ?>/donations.php?tab=campaigns" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="current_image_path" value="<?= e($edit_camp['image_path'] ?? 'assets/images/activities/swim-protection.jpg') ?>">

                <div class="mb-3">
                    <label class="form-label small fw-bold text-navy">Campaign Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required value="<?= e($edit_camp['title'] ?? '') ?>">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-navy">Target Amount (LKR)</label>
                        <input type="number" step="0.01" name="target_amount" class="form-control" value="<?= e($edit_camp['target_amount'] ?? 500000) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-navy">Raised Amount (LKR)</label>
                        <input type="number" step="0.01" name="raised_amount" class="form-control" value="<?= e($edit_camp['raised_amount'] ?? 0) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-navy">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= e($edit_camp['description'] ?? '') ?></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-navy">Campaign Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= (($edit_camp['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="completed" <?= (($edit_camp['status'] ?? '') === 'completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="paused" <?= (($edit_camp['status'] ?? '') === 'paused') ? 'selected' : '' ?>>Paused</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-navy">Campaign Photo</label>
                        <input type="file" name="image_file" class="form-control" accept="image/*">
                    </div>
                </div>

                <button type="submit" name="save_campaign" class="btn btn-sapsada-teal px-4">
                    <i class="fas fa-save me-1"></i> Save Campaign
                </button>
            </form>
        </div>
    <?php else: ?>
        <!-- Campaigns Table -->
        <div class="admin-table-card">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Campaign Title</th>
                            <th>Target Amount</th>
                            <th>Raised Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campaigns as $camp): ?>
                            <tr>
                                <td><img src="<?= ASSETS_URL ?>/../<?= e($camp['image_path']) ?>" class="rounded-2 shadow-subtle border" style="width:70px; height:50px; object-fit:cover;"></td>
                                <td>
                                    <div class="fw-bold text-navy"><?= e($camp['title']) ?></div>
                                    <small class="text-muted text-truncate d-block" style="max-width:320px;"><?= e($camp['description']) ?></small>
                                </td>
                                <td class="fw-semibold">LKR <?= number_format($camp['target_amount']) ?></td>
                                <td class="fw-bold text-teal">LKR <?= number_format($camp['raised_amount']) ?></td>
                                <td><span class="badge-status status-<?= e($camp['status']) ?>"><?= ucfirst($camp['status']) ?></span></td>
                                <td class="text-end">
                                    <a href="<?= ADMIN_URL ?>/donations.php?tab=campaigns&action=edit_camp&id=<?= $camp['id'] ?>" class="btn btn-light btn-sm text-primary me-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="<?= ADMIN_URL ?>/donations.php?tab=campaigns&action=delete_camp&id=<?= $camp['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-light btn-sm text-danger btn-confirm-delete" data-name="this campaign">
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

<?php else: ?>
    <!-- Bank Slip Notifications Table -->
    <div class="admin-table-card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Donor Name</th>
                        <th>Contact</th>
                        <th>Fund Allocated</th>
                        <th>Amount Declared</th>
                        <th>Bank Ref</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($slips)): ?>
                        <?php foreach ($slips as $slip): ?>
                            <tr>
                                <td class="fw-bold text-navy"><?= e($slip['donor_name']) ?></td>
                                <td>
                                    <div><?= e($slip['phone']) ?></div>
                                    <small class="text-muted"><?= e($slip['email']) ?></small>
                                </td>
                                <td><small class="badge bg-light text-navy border"><?= e($slip['campaign_title'] ?: 'General Welfare') ?></small></td>
                                <td class="fw-bold text-teal">LKR <?= number_format($slip['amount_declared'] ?? 0) ?></td>
                                <td><span class="badge bg-light text-secondary border"><?= e($slip['transfer_reference'] ?: 'None') ?></span></td>
                                <td>
                                    <form action="<?= ADMIN_URL ?>/donations.php?tab=slips" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="slip_id" value="<?= $slip['id'] ?>">
                                        <input type="hidden" name="update_slip_status" value="1">
                                        <select name="status" class="form-select form-select-sm border-0 badge-status status-<?= e($slip['status']) ?>" onchange="this.form.submit()">
                                            <option value="Pending" <?= $slip['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Verified" <?= $slip['status'] === 'Verified' ? 'selected' : '' ?>>Verified</option>
                                            <option value="Cancelled" <?= $slip['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td><small class="text-muted"><?= date('M d, Y', strtotime($slip['created_at'])) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No donation notifications recorded.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
