<?php
/**
 * Admin Contact Messages Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$page_title = 'Contact Inquiries';
$current_page = 'messages';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$id = (int)($_GET['id'] ?? 0);
$action = clean_input($_GET['action'] ?? 'list');

$alert_msg = null;
$alert_type = 'success';

// Handle Mark as Read / Replied
if ($action === 'mark_read' && $id > 0) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$id]);
        header("Location: " . ADMIN_URL . "/messages.php");
        exit;
    }
}

if ($action === 'mark_replied' && $id > 0) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $db->prepare("UPDATE contact_messages SET is_read = 1, replied_at = NOW() WHERE id = ?")->execute([$id]);
        $alert_msg = 'Inquiry marked as replied!';
    }
}

// Handle Delete
if ($action === 'delete' && $id > 0) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $db->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
        log_audit('Delete Contact Message', 'Messages', "Deleted message ID {$id}");
        header("Location: " . ADMIN_URL . "/messages.php?msg=deleted");
        exit;
    }
}

// Single Message Detail View
$selected_msg = null;
if ($id > 0 && $action === 'view') {
    $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $selected_msg = $stmt->fetch();
    if ($selected_msg) {
        $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$id]);
    }
}

$messages = $db->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-1">Contact Inquiries</h4>
        <small class="text-muted">Public inquiries, emergency notifications, and partnership proposals</small>
    </div>
</div>

<?php if ($alert_msg): ?>
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show rounded-3" role="alert">
        <?= e($alert_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($selected_msg): ?>
    <!-- Single Message Detail Card -->
    <div class="card border-0 shadow-card rounded-4 p-4 p-md-5 bg-white mb-4">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <div>
                <h5 class="fw-bold text-navy mb-1"><?= e($selected_msg['subject']) ?></h5>
                <span class="text-muted small">From: <strong><?= e($selected_msg['name']) ?></strong> &bull; <?= date('M d, Y h:i A', strtotime($selected_msg['created_at'])) ?></span>
            </div>
            <a href="<?= ADMIN_URL ?>/messages.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to All Messages
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <small class="text-muted d-block">Sender Name</small>
                <div class="fw-bold text-navy"><?= e($selected_msg['name']) ?></div>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Email Address</small>
                <div><a href="mailto:<?= e($selected_msg['email']) ?>" class="text-teal fw-bold"><?= e($selected_msg['email']) ?></a></div>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Telephone</small>
                <div><a href="tel:<?= preg_replace('/[^0-9+]/', '', $selected_msg['phone']) ?>" class="text-navy fw-bold"><?= e($selected_msg['phone'] ?: 'N/A') ?></a></div>
            </div>
        </div>

        <div class="p-4 bg-light rounded-4 mb-4">
            <h6 class="fw-bold text-navy mb-2">Message Content:</h6>
            <div class="text-secondary lh-lg fs-6"><?= nl2br(e($selected_msg['message'])) ?></div>
        </div>

        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <a href="mailto:<?= e($selected_msg['email']) ?>?subject=<?= urlencode('Re: ' . $selected_msg['subject']) ?>" class="btn btn-sapsada-teal btn-sm">
                    <i class="fas fa-reply me-1"></i> Reply via Email
                </a>
                <?php if (!empty($selected_msg['phone'])): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $selected_msg['phone']) ?>" target="_blank" class="btn btn-success btn-sm">
                        <i class="fab fa-whatsapp me-1"></i> Open in WhatsApp
                    </a>
                <?php endif; ?>
                <?php if (!$selected_msg['replied_at']): ?>
                    <a href="<?= ADMIN_URL ?>/messages.php?id=<?= $selected_msg['id'] ?>&action=mark_replied&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-check-double me-1"></i> Mark as Replied
                    </a>
                <?php else: ?>
                    <span class="badge bg-success small"><i class="fas fa-check"></i> Replied on <?= date('M d', strtotime($selected_msg['replied_at'])) ?></span>
                <?php endif; ?>
            </div>
            <a href="<?= ADMIN_URL ?>/messages.php?action=delete&id=<?= $selected_msg['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-outline-danger btn-sm btn-confirm-delete" data-name="this message">
                <i class="fas fa-trash-alt me-1"></i> Delete Message
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Messages Table -->
<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th style="width: 40px;"></th>
                    <th>From</th>
                    <th>Contact Info</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <tr class="<?= !$msg['is_read'] ? 'bg-light bg-opacity-50 fw-bold' : '' ?>">
                            <td>
                                <?php if (!$msg['is_read']): ?>
                                    <span class="badge rounded-pill bg-danger p-1" title="Unread">&nbsp;</span>
                                <?php else: ?>
                                    <i class="fas fa-envelope-open text-muted small"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-navy"><?= e($msg['name']) ?></td>
                            <td>
                                <div><a href="mailto:<?= e($msg['email']) ?>" class="text-decoration-none text-teal small"><?= e($msg['email']) ?></a></div>
                                <small class="text-muted"><?= e($msg['phone'] ?: 'N/A') ?></small>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/messages.php?action=view&id=<?= $msg['id'] ?>" class="text-navy text-decoration-none">
                                    <?= e($msg['subject']) ?>
                                </a>
                            </td>
                            <td><small class="text-muted"><?= time_ago($msg['created_at']) ?></small></td>
                            <td>
                                <?php if ($msg['replied_at']): ?>
                                    <span class="badge bg-success">Replied</span>
                                <?php elseif ($msg['is_read']): ?>
                                    <span class="badge bg-secondary">Read</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">New</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= ADMIN_URL ?>/messages.php?action=view&id=<?= $msg['id'] ?>" class="btn btn-light btn-sm text-primary me-1">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="<?= ADMIN_URL ?>/messages.php?action=delete&id=<?= $msg['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-light btn-sm text-danger btn-confirm-delete" data-name="message from <?= e($msg['name']) ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted">No messages received yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
