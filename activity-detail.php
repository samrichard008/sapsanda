<?php
/**
 * Activity Detail Page
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$db = get_db();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("
    SELECT a.*, COALESCE(at.title, a.slug) AS localized_title, COALESCE(at.description, '') AS localized_desc
    FROM activities a
    LEFT JOIN activity_translations at ON a.id = at.activity_id AND at.lang = ?
    WHERE a.id = ?
    LIMIT 1
");
$stmt->execute([CURRENT_LANG, $id]);
$activity = $stmt->fetch();

if (!$activity) {
    header("Location: activities.php");
    exit;
}

$active_page = 'activities';
$custom_page_title = e($activity['localized_title']);
$custom_meta_desc = substr(strip_tags($activity['localized_desc']), 0, 160);
$custom_og_image = ASSETS_URL . '/../' . $activity['featured_image'];

require_once __DIR__ . '/includes/header.php';

// Fetch Related Activities
$stmt_related = $db->prepare("
    SELECT a.*, COALESCE(at.title, a.slug) AS localized_title
    FROM activities a
    LEFT JOIN activity_translations at ON a.id = at.activity_id AND at.lang = ?
    WHERE a.id != ? AND a.category = ?
    LIMIT 3
");
$stmt_related->execute([CURRENT_LANG, $id, $activity['category']]);
$related = $stmt_related->fetchAll();
?>

<!-- Header Banner -->
<div class="bg-navy py-5 text-white" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="index.php" class="text-sky-light"><?= __('nav_home') ?></a></li>
                <li class="breadcrumb-item"><a href="activities.php" class="text-sky-light"><?= __('nav_activities') ?></a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page"><?= e($activity['category']) ?></li>
            </ol>
        </nav>
        <span class="badge bg-gold text-dark px-3 py-1 rounded-pill fw-bold mb-2"><?= e($activity['category']) ?></span>
        <h1 class="display-6 fw-bold text-white mb-0"><?= e($activity['localized_title']) ?></h1>
    </div>
</div>

<!-- Activity Content -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <div class="rounded-4 overflow-hidden shadow-card mb-4">
                    <img src="<?= ASSETS_URL ?>/../<?= e($activity['featured_image']) ?>" alt="<?= e($activity['localized_title']) ?>" class="img-fluid w-100" style="max-height: 480px; object-fit: cover;">
                </div>

                <!-- Info Badges -->
                <div class="d-flex flex-wrap gap-4 p-3 bg-light rounded-4 mb-4 text-secondary small">
                    <div><i class="fas fa-calendar-alt text-teal me-1"></i> <strong>Date:</strong> <?= date('F d, Y', strtotime($activity['activity_date'])) ?></div>
                    <div><i class="fas fa-map-marker-alt text-teal me-1"></i> <strong>Location:</strong> <?= e($activity['location']) ?></div>
                    <div><i class="fas fa-check-circle text-teal me-1"></i> <strong>Status:</strong> <?= ucfirst($activity['status']) ?></div>
                </div>

                <!-- Full Description -->
                <div class="cms-rendered-content text-secondary lh-lg mb-5 fs-6">
                    <?= $activity['localized_desc'] ?>
                </div>

                <!-- Share Section -->
                <div class="p-4 bg-light rounded-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <span class="fw-bold text-navy"><i class="fas fa-share-alt me-2 text-teal"></i>Share this activity:</span>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="btn btn-primary btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://wa.me/?text=<?= urlencode($activity['localized_title'] . ' ' . BASE_URL . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="btn btn-success btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Action CTA -->
                <div class="card border-0 shadow-card rounded-4 p-4 bg-navy text-white mb-4">
                    <h5 class="fw-bold text-white mb-2">Support This Activity</h5>
                    <p class="small text-sky-light mb-3">Your volunteer help or contribution allows us to conduct more operations like this in Sri Lanka.</p>
                    <a href="volunteers.php" class="btn btn-light text-navy fw-bold btn-sm mb-2"><i class="fas fa-hands-helping me-1"></i> Join As Volunteer</a>
                    <a href="donate.php" class="btn btn-sapsada-gold btn-sm"><i class="fas fa-heart me-1"></i> Donate to Cause</a>
                </div>

                <!-- Related Activities -->
                <?php if (!empty($related)): ?>
                    <div class="card border-0 shadow-card rounded-4 p-4 bg-white">
                        <h6 class="fw-bold text-navy mb-3">Related Activities</h6>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($related as $rel): ?>
                                <a href="activity-detail.php?id=<?= $rel['id'] ?>" class="d-flex gap-3 text-decoration-none text-navy align-items-center">
                                    <img src="<?= ASSETS_URL ?>/../<?= e($rel['featured_image']) ?>" class="rounded-3" style="width:65px; height:55px; object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold small lh-sm"><?= e($rel['localized_title']) ?></div>
                                        <div class="text-muted smaller"><?= date('M d, Y', strtotime($rel['activity_date'])) ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
