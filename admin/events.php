<?php
/**
 * Admin Events Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$page_title = 'Community Events Management';
$current_page = 'events';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$action = clean_input($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);

$alert_msg = null;
$alert_type = 'success';

// Handle Delete
if ($action === 'delete' && $id > 0) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $db->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);
        log_audit('Delete Event', 'Events', "Deleted event ID {$id}");
        header("Location: " . ADMIN_URL . "/events.php?msg=deleted");
        exit;
    }
}

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_event'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $alert_msg = 'Security token invalid.';
        $alert_type = 'danger';
    } else {
        $title = clean_input($_POST['title'] ?? '');
        $description = $_POST['description'] ?? '';
        $event_date = clean_input($_POST['event_date'] ?? date('Y-m-d'));
        $start_time = clean_input($_POST['start_time'] ?? '09:00:00');
        $end_time = clean_input($_POST['end_time'] ?? '12:00:00');
        $venue = clean_input($_POST['venue'] ?? '');
        $status = clean_input($_POST['status'] ?? 'Upcoming');
        $registration_open = isset($_POST['registration_open']) ? 1 : 0;
        $featured_image = clean_input($_POST['current_featured_image'] ?? 'assets/images/activities/swim-protection.jpg');

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        if (empty($slug)) $slug = 'event-' . time();

        if (!empty($_FILES['image_file']['name'])) {
            $upload = handle_file_upload($_FILES['image_file'], 'events');
            if ($upload['success']) {
                $featured_image = $upload['path'];
            }
        }

        if ($id > 0) {
            $stmt = $db->prepare("
                UPDATE events 
                SET title = ?, description = ?, event_date = ?, start_time = ?, end_time = ?, venue = ?, featured_image = ?, registration_open = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $description, $event_date, $start_time, $end_time, $venue, $featured_image, $registration_open, $status, $id]);
            log_audit('Update Event', 'Events', "Updated event ID {$id}: {$title}");
            $alert_msg = 'Event updated successfully!';
        } else {
            $stmt = $db->prepare("
                INSERT INTO events (title, slug, description, event_date, start_time, end_time, venue, featured_image, registration_open, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $slug, $description, $event_date, $start_time, $end_time, $venue, $featured_image, $registration_open, $status]);
            log_audit('Create Event', 'Events', "Created event: {$title}");
            $alert_msg = 'Event scheduled successfully!';
        }
        $action = 'list';
    }
}

// Fetch for Edit
$edit_event = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $edit_event = $stmt->fetch();
    if (!$edit_event) $action = 'list';
}

$events = $db->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-1">Community Events</h4>
        <small class="text-muted">Schedule volunteer workshops, beach cleanups, and welfare distributions</small>
    </div>
    <?php if ($action === 'list'): ?>
        <a href="<?= ADMIN_URL ?>/events.php?action=create" class="btn btn-sapsada-teal btn-sm">
            <i class="fas fa-plus me-1"></i> Add Event
        </a>
    <?php else: ?>
        <a href="<?= ADMIN_URL ?>/events.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Events
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
        <h5 class="fw-bold text-navy mb-4"><?= $action === 'edit' ? 'Edit Event' : 'Schedule New Event' ?></h5>

        <form action="<?= ADMIN_URL ?>/events.php<?= $id > 0 ? '?action=edit&id='.$id : '?action=create' ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="current_featured_image" value="<?= e($edit_event['featured_image'] ?? 'assets/images/activities/swim-protection.jpg') ?>">

            <div class="mb-3">
                <label class="form-label small fw-bold text-navy">Event Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required value="<?= e($edit_event['title'] ?? '') ?>">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-navy">Event Date <span class="text-danger">*</span></label>
                    <input type="date" name="event_date" class="form-control" required value="<?= e($edit_event['event_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-navy">Start Time</label>
                    <input type="time" name="start_time" class="form-control" required value="<?= e($edit_event['start_time'] ?? '09:00') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-navy">End Time</label>
                    <input type="time" name="end_time" class="form-control" required value="<?= e($edit_event['end_time'] ?? '13:00') ?>">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label small fw-bold text-navy">Venue / Address <span class="text-danger">*</span></label>
                    <input type="text" name="venue" class="form-control" required placeholder="e.g. Jayabodhi Community Hall, Nalluruwa" value="<?= e($edit_event['venue'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-navy">Status</label>
                    <select name="status" class="form-select">
                        <option value="Upcoming" <?= (($edit_event['status'] ?? '') === 'Upcoming') ? 'selected' : '' ?>>Upcoming</option>
                        <option value="Completed" <?= (($edit_event['status'] ?? '') === 'Completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancelled" <?= (($edit_event['status'] ?? '') === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-navy">Event Details &amp; Registration Instructions</label>
                <textarea name="description" class="form-control" rows="5"><?= e($edit_event['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-navy">Featured Photo</label>
                <input type="file" name="image_file" class="form-control" accept="image/*" data-preview="evPreview">
                <div class="mt-2">
                    <img id="evPreview" src="<?= ASSETS_URL ?>/../<?= e($edit_event['featured_image'] ?? 'assets/images/activities/swim-protection.jpg') ?>" class="rounded-3 shadow-subtle border" style="max-height: 120px; object-fit: cover;">
                </div>
            </div>

            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="registration_open" id="regSwitch" <?= (!isset($edit_event) || $edit_event['registration_open']) ? 'checked' : '' ?>>
                <label class="form-check-label small fw-bold text-navy" for="regSwitch">Allow Public Registration / RSVP on website</label>
            </div>

            <button type="submit" name="save_event" class="btn btn-sapsada-teal px-4">
                <i class="fas fa-save me-1"></i> Save Event
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
                        <th>Event Title</th>
                        <th>Date &amp; Time</th>
                        <th>Venue</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): ?>
                        <tr>
                            <td><img src="<?= ASSETS_URL ?>/../<?= e($ev['featured_image']) ?>" class="rounded-2 shadow-subtle border" style="width:70px; height:50px; object-fit:cover;"></td>
                            <td>
                                <div class="fw-bold text-navy"><?= e($ev['title']) ?></div>
                                <small class="text-muted"><?= $ev['registration_open'] ? '<span class="text-success"><i class="fas fa-check"></i> RSVP Open</span>' : 'RSVP Closed' ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold text-navy"><?= date('M d, Y', strtotime($ev['event_date'])) ?></div>
                                <small class="text-muted"><?= date('h:i A', strtotime($ev['start_time'])) ?> - <?= date('h:i A', strtotime($ev['end_time'])) ?></small>
                            </td>
                            <td><small><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= e($ev['venue']) ?></small></td>
                            <td><span class="badge-status status-<?= e($ev['status']) ?>"><?= e($ev['status']) ?></span></td>
                            <td class="text-end">
                                <a href="<?= ADMIN_URL ?>/events.php?action=edit&id=<?= $ev['id'] ?>" class="btn btn-light btn-sm text-primary me-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?= ADMIN_URL ?>/events.php?action=delete&id=<?= $ev['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-light btn-sm text-danger btn-confirm-delete" data-name="this event">
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
