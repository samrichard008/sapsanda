<?php
/**
 * News & Announcements Page
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$active_page = 'news';
$custom_page_title = 'Latest News & Official Announcements';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// Search / Category filter
$category_filter = clean_input($_GET['category'] ?? '');
$search_query = clean_input($_GET['q'] ?? '');

$sql = "
    SELECT n.*, COALESCE(nt.title, n.slug) AS localized_title, COALESCE(nt.summary, '') AS localized_summary
    FROM news n
    LEFT JOIN news_translations nt ON n.id = nt.news_id AND nt.lang = ?
    WHERE n.status = 'Published'
";
$params = [CURRENT_LANG];

if (!empty($category_filter)) {
    $sql .= " AND n.category = ?";
    $params[] = $category_filter;
}

if (!empty($search_query)) {
    $sql .= " AND (nt.title LIKE ? OR nt.summary LIKE ? OR nt.content LIKE ?)";
    $like = "%{$search_query}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY n.published_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$news_list = $stmt->fetchAll();
?>

<!-- Header Banner -->
<div class="bg-navy py-5 text-white" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-4">
        <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-bold mb-2">Media &amp; Public Information</span>
        <h1 class="display-5 fw-bold text-white mb-2"><?= __('sec_latest_news') ?></h1>
        <p class="lead text-sky-light mb-0"><?= __('sec_latest_news_sub') ?></p>
    </div>
</div>

<section class="section-padding bg-light">
    <div class="container">
        <!-- Search & Filter Bar -->
        <div class="card border-0 shadow-subtle rounded-4 p-3 mb-5 bg-white">
            <form action="news.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-0 bg-light" placeholder="Search news and press releases..." value="<?= e($search_query) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select border-0 bg-light">
                        <option value="">All Categories</option>
                        <option value="Awards & Recognition" <?= $category_filter === 'Awards & Recognition' ? 'selected' : '' ?>>Awards &amp; Recognition</option>
                        <option value="Community Impact" <?= $category_filter === 'Community Impact' ? 'selected' : '' ?>>Community Impact</option>
                        <option value="Life Protection" <?= $category_filter === 'Life Protection' ? 'selected' : '' ?>>Life Protection</option>
                        <option value="General News" <?= $category_filter === 'General News' ? 'selected' : '' ?>>General News</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-sapsada-teal w-100">Filter</button>
                </div>
            </form>
        </div>

        <?php if (!empty($news_list)): ?>
            <div class="row g-4">
                <?php foreach ($news_list as $news): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="sapsada-card">
                            <div class="card-img-wrap">
                                <span class="card-badge bg-navy"><?= e($news['category']) ?></span>
                                <img src="<?= ASSETS_URL ?>/../<?= e($news['featured_image']) ?>" alt="<?= e($news['localized_title']) ?>" loading="lazy">
                            </div>
                            <div class="card-body-sapsada">
                                <div class="text-muted small mb-2">
                                    <i class="fas fa-calendar-alt text-teal me-1"></i> <?= date('F d, Y', strtotime($news['published_at'])) ?>
                                </div>
                                <h5 class="fw-bold text-navy mb-2">
                                    <a href="news-detail.php?id=<?= $news['id'] ?>" class="text-navy text-decoration-none hover-teal">
                                        <?= e($news['localized_title']) ?>
                                    </a>
                                </h5>
                                <p class="text-secondary small mb-3 flex-grow-1">
                                    <?= e($news['localized_summary']) ?>
                                </p>
                                <a href="news-detail.php?id=<?= $news['id'] ?>" class="btn btn-sapsada-outline btn-sm w-100">
                                    <?= __('read_more') ?> &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="text-muted fs-1 mb-3"><i class="fas fa-newspaper"></i></div>
                <h4 class="text-navy fw-bold">No articles match your search criteria.</h4>
                <a href="news.php" class="btn btn-sapsada-primary btn-sm mt-3">Reset Search</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
