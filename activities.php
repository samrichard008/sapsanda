<?php
/**
 * Activities Page
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$active_page = 'activities';
$custom_page_title = 'Our Activities & Welfare Operations';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// Selected category from GET parameter
$selected_cat = clean_input($_GET['cat'] ?? 'all');

// Categories array
$categories = [
    'all' => 'All Activities',
    'Life Protection Awareness' => 'Life Protection Awareness',
    'Environmental Activities' => 'Environmental Activities',
    'Community Development' => 'Community Development',
    'Social Welfare Programs' => 'Social Welfare Programs',
    'Emergency Support' => 'Emergency Support',
    'Educational Assistance' => 'Educational Assistance',
    'Health Awareness' => 'Health Awareness',
    'Public Awareness Campaigns' => 'Public Awareness Campaigns',
    'Elderly Support' => 'Elderly Support',
    'Children\'s Welfare' => 'Children\'s Welfare',
    'Family Support' => 'Family Support',
    'Special Community Projects' => 'Special Community Projects'
];

// Query
$sql = "
    SELECT a.*, COALESCE(at.title, a.slug) AS localized_title, COALESCE(at.description, '') AS localized_desc
    FROM activities a
    LEFT JOIN activity_translations at ON a.id = at.activity_id AND at.lang = ?
    WHERE a.status != 'cancelled'
";
$params = [CURRENT_LANG];

if ($selected_cat !== 'all' && isset($categories[$selected_cat])) {
    $sql .= " AND a.category = ?";
    $params[] = $selected_cat;
}

$sql .= " ORDER BY a.activity_date DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll();
?>

<!-- Header Banner -->
<div class="bg-navy py-5 text-white" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-4">
        <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-bold mb-2">On The Ground</span>
        <h1 class="display-5 fw-bold text-white mb-2"><?= __('sec_core_activities') ?></h1>
        <p class="lead text-sky-light mb-0"><?= __('sec_core_activities_sub') ?></p>
    </div>
</div>

<!-- Main Activities Section -->
<section class="section-padding bg-light">
    <div class="container">
        <!-- Category Filter Pills -->
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-5">
            <?php foreach ($categories as $key => $label): ?>
                <a href="activities.php?cat=<?= urlencode($key) ?>" class="btn <?= $selected_cat === $key ? 'btn-sapsada-teal' : 'btn-white bg-white text-navy border' ?> btn-sm rounded-pill px-3 py-2 shadow-sm">
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($activities)): ?>
            <div class="row g-4">
                <?php foreach ($activities as $act): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="sapsada-card">
                            <div class="card-img-wrap">
                                <span class="card-badge bg-navy"><i class="fas fa-tag me-1 text-gold"></i> <?= e($act['category']) ?></span>
                                <img src="<?= ASSETS_URL ?>/../<?= e($act['featured_image']) ?>" alt="<?= e($act['localized_title']) ?>" loading="lazy">
                            </div>
                            <div class="card-body-sapsada">
                                <div class="d-flex align-items-center text-muted small mb-2 gap-3">
                                    <span><i class="fas fa-map-marker-alt text-teal me-1"></i> <?= e($act['location']) ?></span>
                                    <span><i class="fas fa-calendar-alt text-teal me-1"></i> <?= date('M d, Y', strtotime($act['activity_date'])) ?></span>
                                </div>
                                <h5 class="fw-bold text-navy mb-3">
                                    <a href="activity-detail.php?id=<?= $act['id'] ?>" class="text-navy text-decoration-none hover-teal">
                                        <?= e($act['localized_title']) ?>
                                    </a>
                                </h5>
                                <div class="text-secondary small mb-4 flex-grow-1">
                                    <?= substr(strip_tags($act['localized_desc']), 0, 140) ?>...
                                </div>
                                <a href="activity-detail.php?id=<?= $act['id'] ?>" class="btn btn-sapsada-outline btn-sm w-100">
                                    <?= __('learn_more') ?> <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="text-muted fs-1 mb-3"><i class="fas fa-clipboard-list"></i></div>
                <h4 class="text-navy fw-bold">No activities found in this category.</h4>
                <p class="text-secondary">Please check back soon or browse all activities.</p>
                <a href="activities.php" class="btn btn-sapsada-primary btn-sm">View All Activities</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
