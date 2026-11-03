<?php
/**
 * Event Detail Page
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$db = get_db();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    header("Location: events.php");
    exit;
}

$active_page = 'events';
$custom_page_title = e($event['title']);
$custom_meta_desc = substr(strip_tags($event['description']), 0, 160);
$custom_og_image = ASSETS_URL . '/../' . $event['featured_image'];

$rsvp_success = null;
$rsvp_error = null;

// Handle simple RSVP / Enquiry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rsvp'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $rsvp_error = 'Security check failed. Please refresh.';
    } else {
        $name = clean_input($_POST['name'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');
        $attendees = (int)($_POST['attendees'] ?? 1);

        if (empty($name) || empty($phone)) {
            $rsvp_error = 'Please enter your name and phone number.';
        } else {
            create_notification(
                'event_rsvp',
                'Event Attendance RSVP',
                "{$name} ({$phone}) registered {$attendees} attendee(s) for event: {$event['title']}",
                'events.php'
            );
            $rsvp_success = 'Thank you! Your registration has been received. See you at the event!';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="bg-navy py-5 text-white" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="index.php" class="text-sky-light"><?= __('nav_home') ?></a></li>
                <li class="breadcrumb-item"><a href="events.php" class="text-sky-light"><?= __('nav_news') ?></a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page"><?= date('M d, Y', strtotime($event['event_date'])) ?></li>
            </ol>
        </nav>
        <span class="badge bg-gold text-dark px-3 py-1 rounded-pill fw-bold mb-2"><?= e($event['status']) ?></span>
        <h1 class="display-6 fw-bold text-white mb-0"><?= e($event['title']) ?></h1>
    </div>
</div>

<section class="section-padding bg-white">
    <div class="container">
        <div class="row g-5">
            <!-- Event Content -->
            <div class="col-lg-8">
                <div class="rounded-4 overflow-hidden shadow-card mb-4">
                    <img src="<?= ASSETS_URL ?>/../<?= e($event['featured_image']) ?>" alt="<?= e($event['title']) ?>" class="img-fluid w-100" style="max-height: 480px; object-fit: cover;">
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <i class="fas fa-calendar-alt text-teal fs-4 mb-2"></i>
                            <div class="fw-bold small text-navy">Date</div>
                            <div class="small text-secondary"><?= date('l, M d, Y', strtotime($event['event_date'])) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <i class="fas fa-clock text-teal fs-4 mb-2"></i>
                            <div class="fw-bold small text-navy">Time</div>
                            <div class="small text-secondary"><?= date('h:i A', strtotime($event['start_time'])) ?> - <?= date('h:i A', strtotime($event['end_time'])) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <i class="fas fa-map-marker-alt text-danger fs-4 mb-2"></i>
                            <div class="fw-bold small text-navy">Venue</div>
                            <div class="small text-secondary"><?= e($event['venue']) ?></div>
                        </div>
                    </div>
                </div>

                <h4 class="fw-bold text-navy mb-3">Event Details &amp; Overview</h4>
                <div class="cms-rendered-content text-secondary lh-lg mb-5 fs-6">
                    <?= nl2br(e($event['description'])) ?>
                </div>

                <!-- Registration / RSVP Section -->
                <?php if ($event['registration_open']): ?>
                    <div class="card border-0 shadow-subtle rounded-4 p-4 p-md-5 bg-light mb-4">
                        <h5 class="fw-bold text-navy mb-2"><i class="fas fa-ticket-alt text-teal me-2"></i>Register for this Event</h5>
                        <p class="small text-muted mb-4">Admission is free. Please register in advance to assist with event logistics and seating arrangements.</p>

                        <?php if ($rsvp_success): ?>
                            <div class="alert alert-success border-0 rounded-3 mb-0"><?= e($rsvp_success) ?></div>
                        <?php else: ?>
                            <?php if ($rsvp_error): ?>
                                <div class="alert alert-danger border-0 rounded-3 mb-3"><?= e($rsvp_error) ?></div>
                            <?php endif; ?>

                            <form action="event-detail.php?id=<?= $event['id'] ?>" method="POST">
                                <?= csrf_field() ?>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold text-navy">Full Name</label>
                                        <input type="text" name="name" class="form-control" required placeholder="Your Name">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-navy">Phone / WhatsApp</label>
                                        <input type="tel" name="phone" class="form-control" required placeholder="07XXXXXXXX">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-navy">Attendees</label>
                                        <input type="number" min="1" max="10" name="attendees" class="form-control" value="1">
                                    </div>
                                </div>
                                <button type="submit" name="submit_rsvp" class="btn btn-sapsada-teal">
                                    <i class="fas fa-check me-1"></i> Confirm Attendance
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-card rounded-4 p-4 bg-navy text-white mb-4">
                    <h5 class="fw-bold text-white mb-3"><i class="fas fa-info-circle text-gold me-2"></i>Event Guidelines</h5>
                    <ul class="list-unstyled small text-sky-light d-flex flex-column gap-2 mb-0">
                        <li><i class="fas fa-check text-teal me-2"></i> Open to the general public</li>
                        <li><i class="fas fa-check text-teal me-2"></i> Certificates awarded to training attendees</li>
                        <li><i class="fas fa-check text-teal me-2"></i> Refreshments and safety gear provided</li>
                        <li><i class="fas fa-check text-teal me-2"></i> Coordinated by SAP SADA rescue marshals</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
