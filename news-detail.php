<?php
/**
 * News Article Detail Page
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$db = get_db();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Increment view count
$db->prepare("UPDATE news SET views = views + 1 WHERE id = ?")->execute([$id]);

$stmt = $db->prepare("
    SELECT n.*, COALESCE(nt.title, n.slug) AS localized_title, COALESCE(nt.summary, '') AS localized_summary, COALESCE(nt.content, '') AS localized_content
    FROM news n
    LEFT JOIN news_translations nt ON n.id = nt.news_id AND nt.lang = ?
    WHERE n.id = ? AND n.status = 'Published'
    LIMIT 1
");
$stmt->execute([CURRENT_LANG, $id]);
$article = $stmt->fetch();

if (!$article) {
    header("Location: news.php");
    exit;
}

$active_page = 'news';
$custom_page_title = e($article['localized_title']);
$custom_meta_desc = e($article['localized_summary']);
$custom_og_image = ASSETS_URL . '/../' . $article['featured_image'];

require_once __DIR__ . '/includes/header.php';

// Recent News
$stmt_recent = $db->prepare("
    SELECT n.*, COALESCE(nt.title, n.slug) AS localized_title
    FROM news n
    LEFT JOIN news_translations nt ON n.id = nt.news_id AND nt.lang = ?
    WHERE n.id != ? AND n.status = 'Published'
    ORDER BY n.published_at DESC
    LIMIT 4
");
$stmt_recent->execute([CURRENT_LANG, $id]);
$recent = $stmt_recent->fetchAll();
?>

<!-- Header Banner -->
<div class="bg-navy py-5 text-white" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="index.php" class="text-sky-light"><?= __('nav_home') ?></a></li>
                <li class="breadcrumb-item"><a href="news.php" class="text-sky-light"><?= __('nav_news') ?></a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page"><?= e($article['category']) ?></li>
            </ol>
        </nav>
        <span class="badge bg-gold text-dark px-3 py-1 rounded-pill fw-bold mb-2"><?= e($article['category']) ?></span>
        <h1 class="display-6 fw-bold text-white mb-0"><?= e($article['localized_title']) ?></h1>
    </div>
</div>

<section class="section-padding bg-white">
    <div class="container">
        <div class="row g-5">
            <!-- Article Body -->
            <div class="col-lg-8">
                <div class="rounded-4 overflow-hidden shadow-card mb-4">
                    <img src="<?= ASSETS_URL ?>/../<?= e($article['featured_image']) ?>" alt="<?= e($article['localized_title']) ?>" class="img-fluid w-100" style="max-height: 480px; object-fit: cover;">
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-4 text-secondary small">
                    <div>
                        <i class="fas fa-calendar-alt text-teal me-1"></i> Published: <?= date('F d, Y', strtotime($article['published_at'])) ?>
                    </div>
                    <div>
                        <i class="fas fa-eye text-teal me-1"></i> <?= number_format($article['views']) ?> views
                    </div>
                </div>

                <div class="lead fw-normal text-navy mb-4 border-start border-4 border-teal ps-3">
                    <?= e($article['localized_summary']) ?>
                </div>

                <div class="cms-rendered-content text-secondary lh-lg mb-5 fs-6">
                    <?= $article['localized_content'] ?>
                </div>

                <!-- Share -->
                <div class="p-4 bg-light rounded-4 d-flex align-items-center justify-content-between">
                    <span class="fw-bold text-navy"><i class="fas fa-share-alt me-2 text-teal"></i>Share this article:</span>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="btn btn-primary btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://wa.me/?text=<?= urlencode($article['localized_title'] . ' ' . BASE_URL . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="btn btn-success btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-card rounded-4 p-4 bg-light mb-4">
                    <h6 class="fw-bold text-navy mb-3"><i class="fas fa-newspaper text-teal me-2"></i>Recent Announcements</h6>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($recent as $item): ?>
                            <a href="news-detail.php?id=<?= $item['id'] ?>" class="text-decoration-none text-navy d-flex gap-3 align-items-center">
                                <img src="<?= ASSETS_URL ?>/../<?= e($item['featured_image']) ?>" class="rounded-3" style="width:65px; height:55px; object-fit:cover;">
                                <div>
                                    <div class="fw-semibold small lh-sm"><?= e($item['localized_title']) ?></div>
                                    <div class="text-muted smaller"><?= date('M d, Y', strtotime($item['published_at'])) ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
