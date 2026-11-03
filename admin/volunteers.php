<?php
/**
 * Admin Volunteer Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$page_title = 'Volunteer Applications';
$current_page = 'volunteers';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$districts = get_sri_lanka_districts();

$alert_msg = null;
$alert_type = 'success';

// Handle Status Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $alert_msg = 'Security check failed.';
        $alert_type = 'danger';
    } else {
        $vol_id = (int)$_POST['volunteer_id'];
        $new_status = clean_input($_POST['status'] ?? 'Pending');
        $valid_statuses = ['Pending', 'Approved', 'Rejected', 'Contacted'];

        if (in_array($new_status, $valid_statuses)) {
            $db->prepare("UPDATE volunteers SET status = ? WHERE id = ?")->execute([$new_status, $vol_id]);
            log_audit('Update Volunteer Status', 'Volunteers', "Changed status of volunteer ID {$vol_id} to {$new_status}");
            $alert_msg = "Volunteer status updated to {$new_status}!";
        }
    }
}

// Handle Add Internal Note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $vol_id = (int)$_POST['volunteer_id'];
        $note = clean_input($_POST['note'] ?? '');
        if (!empty($note)) {
            $stmt = $db->prepare("INSERT INTO volunteer_notes (volunteer_id, admin_id, note) VALUES (?, ?, ?)");
            $stmt->execute([$vol_id, $_SESSION['admin_id'], $note]);
            $alert_msg = 'Internal note recorded successfully!';
        }
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $del_id = (int)$_GET['id'];
        $db->prepare("DELETE FROM volunteers WHERE id = ?")->execute([$del_id]);
        log_audit('Delete Volunteer', 'Volunteers', "Deleted volunteer ID {$del_id}");
        header("Location: " . ADMIN_URL . "/volunteers.php?msg=deleted");
        exit;
    }
}

// Filter and Search parameters
$search = clean_input($_GET['search'] ?? '');
$filter_district = clean_input($_GET['district'] ?? '');
$filter_status = clean_input($_GET['status'] ?? '');

$sql = "SELECT * FROM volunteers WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ? OR reference_no LIKE ? OR nic_passport LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
}

if (!empty($filter_district)) {
    $sql .= " AND district = ?";
    $params[] = $filter_district;
}

if (!empty($filter_status)) {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
}

$sql .= " ORDER BY id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$volunteers = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-1">Volunteer Applicants</h4>
        <small class="text-muted">Review, filter, verify credentials, and manage community volunteer applications</small>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= ADMIN_URL ?>/export-volunteers.php?district=<?= urlencode($filter_district) ?>&status=<?= urlencode($filter_status) ?>" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-csv me-1"></i> Export Filtered CSV
        </a>
    </div>
</div>

<?php if ($alert_msg): ?>
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show rounded-3" role="alert">
        <?= e($alert_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filter & Search Bar -->
<div class="card border-0 shadow-subtle rounded-4 p-3 mb-4 bg-white">
    <form action="<?= ADMIN_URL ?>/volunteers.php" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control bg-light border-0" placeholder="Search name, phone, NIC, Ref..." value="<?= e($search) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="district" class="form-select form-select-sm bg-light border-0">
                <option value="">All 25 Districts</option>
                <?php foreach ($districts as $dist): ?>
                    <option value="<?= e($dist) ?>" <?= $filter_district === $dist ? 'selected' : '' ?>><?= e($dist) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm bg-light border-0">
                <option value="">All Statuses</option>
                <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Approved" <?= $filter_status === 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Contacted" <?= $filter_status === 'Contacted' ? 'selected' : '' ?>>Contacted</option>
                <option value="Rejected" <?= $filter_status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sapsada-teal btn-sm w-100">Filter</button>
            <a href="<?= ADMIN_URL ?>/volunteers.php" class="btn btn-light btn-sm" title="Clear Filters"><i class="fas fa-times"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Ref. No</th>
                    <th>Full Name</th>
                    <th>Contact</th>
                    <th>District</th>
                    <th>Skills / Profession</th>
                    <th>Status</th>
                    <th class="text-end">Manage</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($volunteers)): ?>
                    <?php foreach ($volunteers as $v): ?>
                        <tr>
                            <td><span class="badge bg-light text-navy border fw-bold"><?= e($v['reference_no']) ?></span></td>
                            <td>
                                <div class="fw-bold text-navy"><?= e($v['full_name']) ?></div>
                                <small class="text-muted"><?= e($v['gender']) ?> &bull; NIC: <?= e($v['nic_passport'] ?: 'N/A') ?></small>
                            </td>
                            <td>
                                <div><a href="tel:<?= preg_replace('/[^0-9+]/', '', $v['phone']) ?>" class="text-navy fw-semibold text-decoration-none"><?= e($v['phone']) ?></a></div>
                                <small class="text-muted"><?= e($v['email']) ?></small>
                            </td>
                            <td><span class="badge bg-light text-secondary border"><?= e($v['district']) ?></span></td>
                            <td>
                                <div class="small fw-semibold text-truncate" style="max-width: 180px;"><?= e($v['occupation'] ?: 'General Volunteer') ?></div>
                                <small class="text-muted text-truncate d-block" style="max-width: 180px;"><?= e($v['skills'] ?: 'N/A') ?></small>
                            </td>
                            <td>
                                <form action="<?= ADMIN_URL ?>/volunteers.php" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="volunteer_id" value="<?= $v['id'] ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <select name="status" class="form-select form-select-sm border-0 shadow-none fw-bold badge-status status-<?= e($v['status']) ?>" onchange="this.form.submit()">
                                        <option value="Pending" <?= $v['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Approved" <?= $v['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                        <option value="Contacted" <?= $v['status'] === 'Contacted' ? 'selected' : '' ?>>Contacted</option>
                                        <option value="Rejected" <?= $v['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                    </select>
                                </form>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-light btn-sm text-primary me-1" data-bs-toggle="modal" data-bs-target="#volModal<?= $v['id'] ?>" title="View Details & Notes">
                                    <i class="fas fa-eye"></i> Details
                                </button>
                                <a href="<?= ADMIN_URL ?>/volunteers.php?action=delete&id=<?= $v['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-light btn-sm text-danger btn-confirm-delete" data-name="applicant <?= e($v['full_name']) ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal for Volunteer Details & Admin Notes -->
                        <div class="modal fade" id="volModal<?= $v['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                    <div class="modal-header border-bottom bg-light">
                                        <div>
                                            <h5 class="modal-title fw-bold text-navy"><?= e($v['full_name']) ?></h5>
                                            <span class="badge bg-navy text-white"><?= e($v['reference_no']) ?></span>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Telephone / WhatsApp</small>
                                                <div class="fw-bold text-navy"><?= e($v['phone']) ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Email Address</small>
                                                <div class="fw-bold text-navy"><?= e($v['email']) ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">District / Address</small>
                                                <div class="fw-bold text-navy"><?= e($v['district']) ?> &bull; <?= e($v['address']) ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Availability</small>
                                                <div class="fw-bold text-navy"><?= e($v['availability'] ?: 'Flexible') ?></div>
                                            </div>
                                            <div class="col-12">
                                                <small class="text-muted d-block">Skills &amp; Capabilities</small>
                                                <div class="p-2 bg-light rounded-3"><?= e($v['skills'] ?: 'None specified') ?></div>
                                            </div>
                                            <div class="col-12">
                                                <small class="text-muted d-block">Areas of Interest</small>
                                                <div class="p-2 bg-light rounded-3"><?= e($v['areas_of_interest'] ?: 'General Welfare') ?></div>
                                            </div>
                                            <div class="col-12">
                                                <small class="text-muted d-block">Prior Voluntary Experience</small>
                                                <div class="p-2 bg-light rounded-3"><?= e($v['previous_experience'] ?: 'None specified') ?></div>
                                            </div>
                                            <div class="col-12">
                                                <small class="text-muted d-block">Applicant Motivation Message</small>
                                                <div class="p-2 bg-light rounded-3"><?= e($v['message'] ?: 'N/A') ?></div>
                                            </div>
                                        </div>

                                        <!-- Admin Notes Section -->
                                        <hr>
                                        <h6 class="fw-bold text-navy mb-3"><i class="fas fa-sticky-note text-warning me-2"></i>Internal Admin Notes</h6>
                                        <?php
                                            $stmt_n = $db->prepare("
                                                SELECT vn.*, a.username 
                                                FROM volunteer_notes vn
                                                JOIN admins a ON vn.admin_id = a.id
                                                WHERE vn.volunteer_id = ?
                                                ORDER BY vn.created_at DESC
                                            ");
                                            $stmt_n->execute([$v['id']]);
                                            $notes = $stmt_n->fetchAll();
                                        ?>
                                        <div class="d-flex flex-column gap-2 mb-3">
                                            <?php foreach ($notes as $n): ?>
                                                <div class="p-2 bg-light rounded-3 border-start border-3 border-warning small">
                                                    <div><?= e($n['note']) ?></div>
                                                    <small class="text-muted">— <?= e($n['username']) ?> on <?= date('M d, Y h:i A', strtotime($n['created_at'])) ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <form action="<?= ADMIN_URL ?>/volunteers.php" method="POST">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="volunteer_id" value="<?= $v['id'] ?>">
                                            <div class="input-group">
                                                <input type="text" name="note" class="form-control" required placeholder="Add private admin remarks...">
                                                <button type="submit" name="add_note" class="btn btn-warning fw-bold">Add Note</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted">No volunteers found matching your query.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
