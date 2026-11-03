<?php
/**
 * Events Page
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$active_page = 'events';
$custom_page_title = 'Upcoming Events & Community Calendar';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

$filter = clean_input($_GET['filter'] ?? 'upcoming');

if ($filter === 'past') {
    $stmt = $db->query("SELECT * FROM events WHERE event_date < CURDATE() ORDER BY event_date DESC");
} else {
    $stmt = $db->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC");
}
$events = $stmt->fetchAll();

// If no upcoming found, fetch all
if (empty($events) && $filter === 'upcoming') {
    $stmt = $db->query("SELECT * FROM events ORDER BY event_date DESC");
    $events = $stmt->fetchAll();
}
?>

<!-- Header Banner -->
<div class="bg-navy py-5 text-white" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-4">
        <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-bold mb-2">Community Calendar</span>
        <h1 class="display-5 fw-bold text-white mb-2"><?= __('sec_upcoming_events') ?></h1>
        <p class="lead text-sky-light mb-0"><?= __('sec_upcoming_events_sub') ?></p>
    </div>
</div>

<section class="section-padding bg-light">
    <div class="container">
        <!-- Filter Pills -->
        <div class="d-flex justify-content-center gap-2 mb-5">
            <a href="events.php?filter=upcoming" class="btn <?= $filter !== 'past' ? 'btn-sapsada-teal' : 'btn-white bg-white text-navy border' ?> btn-sm rounded-pill px-4 py-2">
                Upcoming Events
            </a>
            <a href="events.php?filter=past" class="btn <?= $filter === 'past' ? 'btn-sapsada-teal' : 'btn-white bg-white text-navy border' ?> btn-sm rounded-pill px-4 py-2">
                Past Completed Events
            </a>
        </div>

        <?php if (!empty($events)): ?>
            <div class="row g-4">
                <?php foreach ($events as $ev): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="sapsada-card">
                            <div class="card-img-wrap">
                                <span class="card-badge bg-gold text-dark fw-bold"><?= date('M d, Y', strtotime($ev['event_date'])) ?></span>
                                <img src="<?= ASSETS_URL ?>/../<?= e($ev['featured_image']) ?>" alt="<?= e($ev['title']) ?>" loading="lazy">
                            </div>
                            <div class="card-body-sapsada">
                                <div class="text-teal small fw-semibold mb-2">
                                    <i class="fas fa-clock me-1"></i> <?= date('h:i A', strtotime($ev['start_time'])) ?> - <?= date('h:i A', strtotime($ev['end_time'])) ?>
                                </div>
                                <h5 class="fw-bold text-navy mb-2">
                                    <a href="event-detail.php?id=<?= $ev['id'] ?>" class="text-navy text-decoration-none hover-teal">
                                        <?= e($ev['title']) ?>
                                    </a>
                                </h5>
                                <div class="text-muted small mb-3">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i> <?= e($ev['venue']) ?>
                                </div>
                                <p class="text-secondary small mb-4 flex-grow-1">
                                    <?= substr(strip_tags($ev['description']), 0, 130) ?>...
                                </p>
                                <a href="event-detail.php?id=<?= $ev['id'] ?>" class="btn btn-sapsada-primary btn-sm w-100">
                                    <?= __('view_details') ?> &amp; Register &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="text-muted fs-1 mb-3"><i class="fas fa-calendar-times"></i></div>
                <h4 class="text-navy fw-bold">No events found for this filter.</h4>
                <a href="events.php" class="btn btn-sapsada-primary btn-sm mt-3">View All Events</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
