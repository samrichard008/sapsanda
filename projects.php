<?php
/**
 * Projects Overview Page
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$active_page = 'projects';
$custom_page_title = 'Our Projects | Strategic Welfare Initiatives';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// Filter status: all, Ongoing, Planned, Completed
$status_filter = clean_input($_GET['status'] ?? 'all');
$valid_statuses = ['Ongoing', 'Planned', 'Completed'];

$sql = "
    SELECT p.*, COALESCE(pt.title, p.slug) AS localized_title, COALESCE(pt.description, '') AS localized_desc
    FROM projects p
    LEFT JOIN project_translations pt ON p.id = pt.project_id AND pt.lang = ?
    WHERE p.status != 'Archived'
";
$params = [CURRENT_LANG];

if ($status_filter !== 'all' && in_array($status_filter, $valid_statuses)) {
    $sql .= " AND p.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY p.start_date DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();
?>

<!-- Header Banner -->
<div class="bg-navy py-5 text-white" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-4">
        <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-bold mb-2">Sustainable Solutions</span>
        <h1 class="display-5 fw-bold text-white mb-2"><?= __('sec_featured_projects') ?></h1>
        <p class="lead text-sky-light mb-0"><?= __('sec_featured_projects_sub') ?></p>
    </div>
</div>

<!-- Main Projects Section -->
<section class="section-padding bg-light">
    <div class="container">
        <!-- Status Filter Tabs -->
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-5">
            <a href="projects.php" class="btn <?= $status_filter === 'all' ? 'btn-sapsada-teal' : 'btn-white bg-white text-navy border' ?> btn-sm rounded-pill px-4 py-2 shadow-sm">
                All Projects
            </a>
            <a href="projects.php?status=Ongoing" class="btn <?= $status_filter === 'Ongoing' ? 'btn-sapsada-teal' : 'btn-white bg-white text-navy border' ?> btn-sm rounded-pill px-4 py-2 shadow-sm">
                <i class="fas fa-spinner fa-spin me-1"></i> Ongoing
            </a>
            <a href="projects.php?status=Planned" class="btn <?= $status_filter === 'Planned' ? 'btn-sapsada-teal' : 'btn-white bg-white text-navy border' ?> btn-sm rounded-pill px-4 py-2 shadow-sm">
                <i class="fas fa-calendar-check me-1"></i> Planned
            </a>
            <a href="projects.php?status=Completed" class="btn <?= $status_filter === 'Completed' ? 'btn-sapsada-teal' : 'btn-white bg-white text-navy border' ?> btn-sm rounded-pill px-4 py-2 shadow-sm">
                <i class="fas fa-check-circle me-1"></i> Completed
            </a>
        </div>

        <?php if (!empty($projects)): ?>
            <div class="row g-4">
                <?php foreach ($projects as $proj): ?>
                    <?php 
                        $badge_class = 'bg-primary';
                        if ($proj['status'] === 'Ongoing') $badge_class = 'bg-teal';
                        elseif ($proj['status'] === 'Completed') $badge_class = 'bg-success';
                        elseif ($proj['status'] === 'Planned') $badge_class = 'bg-warning text-dark';
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="sapsada-card">
                            <div class="card-img-wrap">
                                <span class="card-badge <?= $badge_class ?>"><i class="fas fa-circle-notch me-1"></i> <?= e($proj['status']) ?></span>
                                <img src="<?= ASSETS_URL ?>/../<?= e($proj['featured_image']) ?>" alt="<?= e($proj['localized_title']) ?>" loading="lazy">
                            </div>
                            <div class="card-body-sapsada">
                                <div class="d-flex align-items-center text-muted small mb-2 gap-2">
                                    <i class="fas fa-map-marker-alt text-teal"></i> <?= e($proj['location']) ?>
                                </div>
                                <h5 class="fw-bold text-navy mb-3">
                                    <a href="project-detail.php?id=<?= $proj['id'] ?>" class="text-navy text-decoration-none hover-teal">
                                        <?= e($proj['localized_title']) ?>
                                    </a>
                                </h5>
                                <div class="text-secondary small mb-4 flex-grow-1">
                                    <?= substr(strip_tags($proj['localized_desc']), 0, 140) ?>...
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i> <?= date('Y', strtotime($proj['start_date'])) ?></small>
                                    <a href="project-detail.php?id=<?= $proj['id'] ?>" class="btn btn-sapsada-primary btn-sm">
                                        <?= __('view_details') ?> <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="text-muted fs-1 mb-3"><i class="fas fa-tasks"></i></div>
                <h4 class="text-navy fw-bold">No projects found with status "<?= e($status_filter) ?>".</h4>
                <a href="projects.php" class="btn btn-sapsada-primary btn-sm mt-3">View All Projects</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
