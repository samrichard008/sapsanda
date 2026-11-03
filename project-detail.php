<?php
/**
 * Project Detail Page
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$db = get_db();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("
    SELECT p.*, COALESCE(pt.title, p.slug) AS localized_title, COALESCE(pt.description, '') AS localized_desc
    FROM projects p
    LEFT JOIN project_translations pt ON p.id = pt.project_id AND pt.lang = ?
    WHERE p.id = ?
    LIMIT 1
");
$stmt->execute([CURRENT_LANG, $id]);
$project = $stmt->fetch();

if (!$project) {
    header("Location: projects.php");
    exit;
}

$active_page = 'projects';
$custom_page_title = e($project['localized_title']);
$custom_meta_desc = substr(strip_tags($project['localized_desc']), 0, 160);
$custom_og_image = ASSETS_URL . '/../' . $project['featured_image'];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="bg-navy py-5 text-white" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="index.php" class="text-sky-light"><?= __('nav_home') ?></a></li>
                <li class="breadcrumb-item"><a href="projects.php" class="text-sky-light"><?= __('nav_projects') ?></a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page"><?= e($project['status']) ?></li>
            </ol>
        </nav>
        <span class="badge bg-gold text-dark px-3 py-1 rounded-pill fw-bold mb-2"><?= e($project['category']) ?></span>
        <h1 class="display-6 fw-bold text-white mb-0"><?= e($project['localized_title']) ?></h1>
    </div>
</div>

<!-- Project Content -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <div class="rounded-4 overflow-hidden shadow-card mb-4">
                    <img src="<?= ASSETS_URL ?>/../<?= e($project['featured_image']) ?>" alt="<?= e($project['localized_title']) ?>" class="img-fluid w-100" style="max-height: 480px; object-fit: cover;">
                </div>

                <!-- Info Badges -->
                <div class="d-flex flex-wrap gap-4 p-3 bg-light rounded-4 mb-4 text-secondary small">
                    <div><i class="fas fa-play-circle text-teal me-1"></i> <strong>Start Date:</strong> <?= date('F d, Y', strtotime($project['start_date'])) ?></div>
                    <div><i class="fas fa-flag-checkered text-teal me-1"></i> <strong>End Date:</strong> <?= $project['end_date'] ? date('F d, Y', strtotime($project['end_date'])) : 'Ongoing Initiative' ?></div>
                    <div><i class="fas fa-map-marker-alt text-teal me-1"></i> <strong>Location:</strong> <?= e($project['location']) ?></div>
                    <div><i class="fas fa-info-circle text-teal me-1"></i> <strong>Status:</strong> <span class="badge bg-teal"><?= e($project['status']) ?></span></div>
                </div>

                <!-- Full Project Description -->
                <div class="cms-rendered-content text-secondary lh-lg mb-5 fs-6">
                    <?= $project['localized_desc'] ?>
                </div>

                <!-- Share Section -->
                <div class="p-4 bg-light rounded-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <span class="fw-bold text-navy"><i class="fas fa-share-alt me-2 text-teal"></i>Share this project:</span>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="btn btn-primary btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://wa.me/?text=<?= urlencode($project['localized_title'] . ' ' . BASE_URL . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="btn btn-success btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-card rounded-4 p-4 bg-light mb-4">
                    <h5 class="fw-bold text-navy mb-3"><i class="fas fa-hand-holding-heart text-teal me-2"></i>Contribute to Project</h5>
                    <p class="small text-secondary mb-3">Community projects rely on public cooperation and dedicated field volunteers. Join us in making this initiative a success.</p>
                    <a href="volunteers.php" class="btn btn-sapsada-teal w-100 btn-sm mb-2"><i class="fas fa-hands-helping me-1"></i> Volunteer on this Project</a>
                    <a href="donate.php" class="btn btn-sapsada-gold w-100 btn-sm"><i class="fas fa-heart me-1"></i> Support Financially</a>
                </div>

                <div class="card border-0 shadow-card rounded-4 p-4 bg-white">
                    <h6 class="fw-bold text-navy mb-3">Project Governance</h6>
                    <ul class="list-unstyled small text-secondary d-flex flex-column gap-2 mb-0">
                        <li><i class="fas fa-check text-success me-2"></i> Environmental safety protocols</li>
                        <li><i class="fas fa-check text-success me-2"></i> Public consultation &amp; local approval</li>
                        <li><i class="fas fa-check text-success me-2"></i> Regular progress reporting</li>
                        <li><i class="fas fa-check text-success me-2"></i> Community oversight committee</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
