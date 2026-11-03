<?php
/**
 * Advanced Photo & Video Gallery
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$active_page = 'gallery';
$custom_page_title = 'Action Photo & Video Gallery | Ground Evidence';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// Fetch Albums
$albums = [];
try {
    $stmt = $db->query("SELECT * FROM gallery_albums ORDER BY id ASC");
    $albums = $stmt->fetchAll();
} catch (Exception $e) {}

// Fetch Images with Album info
$images = [];
try {
    $stmt = $db->query("
        SELECT gi.*, ga.category, ga.title AS album_title 
        FROM gallery_images gi
        JOIN gallery_albums ga ON gi.album_id = ga.id
        ORDER BY gi.id DESC
    ");
    $images = $stmt->fetchAll();
} catch (Exception $e) {}

// Unique categories
$categories = array_unique(array_column($albums, 'category'));
?>

<!-- Header Banner -->
<div class="bg-navy py-5 text-white" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-4">
        <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-bold mb-2">Visual Transparency</span>
        <h1 class="display-5 fw-bold text-white mb-2"><?= __('sec_photo_gallery') ?></h1>
        <p class="lead text-sky-light mb-0"><?= __('sec_photo_gallery_sub') ?></p>
    </div>
</div>

<section class="section-padding bg-light">
    <div class="container">
        <!-- Category Filter Buttons -->
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-5">
            <button type="button" class="btn btn-sapsada-teal filter-btn active rounded-pill px-4 py-2" data-filter="all">
                All Media
            </button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="btn btn-white bg-white text-navy border filter-btn rounded-pill px-4 py-2 shadow-sm" data-filter="<?= e($cat) ?>">
                    <?= e($cat) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Gallery Grid -->
        <div class="row g-4" id="galleryContainer">
            <?php foreach ($images as $img): ?>
                <div class="col-lg-4 col-md-6 filter-item" data-category="<?= e($img['category']) ?>">
                    <div class="gallery-card rounded-4 shadow-card" data-lightbox="gallery" data-src="<?= ASSETS_URL ?>/../<?= e($img['image_path']) ?>" data-caption="<?= e($img['caption'] ?: $img['title']) ?>">
                        <img src="<?= ASSETS_URL ?>/../<?= e($img['image_path']) ?>" alt="<?= e($img['title']) ?>" loading="lazy">
                        <div class="gallery-overlay">
                            <span class="badge bg-gold text-dark align-self-start mb-2"><?= e($img['category']) ?></span>
                            <h5 class="fw-bold mb-1 text-white"><?= e($img['title']) ?></h5>
                            <small class="text-white-50"><?= e($img['caption']) ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($images)): ?>
            <div class="text-center py-5">
                <div class="text-muted fs-1 mb-3"><i class="fas fa-images"></i></div>
                <h4 class="text-navy fw-bold">No gallery images uploaded yet.</h4>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
