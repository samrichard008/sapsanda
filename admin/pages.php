<?php
/**
 * Admin CMS Pages Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$page_title = 'Page Content & About Us';
$current_page = 'pages';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$slug = clean_input($_GET['slug'] ?? 'about-us');
$lang = clean_input($_GET['lang'] ?? 'en');

$alert_msg = null;
$alert_type = 'success';

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_page'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $alert_msg = 'Security token invalid.';
        $alert_type = 'danger';
    } else {
        $page_id = (int)$_POST['page_id'];
        $title = clean_input($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';

        // Upsert into page_translations
        $stmt = $db->prepare("
            INSERT INTO page_translations (page_id, lang, title, content)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content)
        ");
        $stmt->execute([$page_id, $lang, $title, $content]);

        log_audit('Update CMS Page', 'Pages', "Updated page {$slug} for language {$lang}");
        $alert_msg = 'Page content saved successfully!';
    }
}

// Fetch Page Record
$stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    $page = ['id' => 1, 'slug' => 'about-us', 'title' => 'About Us'];
}

// Fetch Translation Content
$stmt = $db->prepare("SELECT * FROM page_translations WHERE page_id = ? AND lang = ? LIMIT 1");
$stmt->execute([$page['id'], $lang]);
$trans = $stmt->fetch();

// All available pages
$all_pages = $db->query("SELECT * FROM pages ORDER BY id ASC")->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-1">Page Content Management</h4>
        <small class="text-muted">Manage multilingual content for About Us, Mission, Vision, and Legal Registration</small>
    </div>
</div>

<?php if ($alert_msg): ?>
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show rounded-3" role="alert">
        <?= e($alert_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tabs: Page Selector & Language Selector -->
<div class="card border-0 shadow-subtle rounded-4 p-3 mb-4 bg-white">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Page Tabs -->
        <div class="nav nav-pills">
            <?php foreach ($all_pages as $p): ?>
                <a href="<?= ADMIN_URL ?>/pages.php?slug=<?= urlencode($p['slug']) ?>&lang=<?= urlencode($lang) ?>" class="nav-link <?= $slug === $p['slug'] ? 'active bg-navy' : 'text-navy' ?> rounded-pill px-3 py-2">
                    <?= e($p['title']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Language Switcher -->
        <div class="btn-group">
            <a href="<?= ADMIN_URL ?>/pages.php?slug=<?= urlencode($slug) ?>&lang=en" class="btn btn-sm <?= $lang === 'en' ? 'btn-teal' : 'btn-outline-secondary' ?>">English</a>
            <a href="<?= ADMIN_URL ?>/pages.php?slug=<?= urlencode($slug) ?>&lang=si" class="btn btn-sm <?= $lang === 'si' ? 'btn-teal' : 'btn-outline-secondary' ?>">සිංහල</a>
            <a href="<?= ADMIN_URL ?>/pages.php?slug=<?= urlencode($slug) ?>&lang=ta" class="btn btn-sm <?= $lang === 'ta' ? 'btn-teal' : 'btn-outline-secondary' ?>">தமிழ்</a>
        </div>
    </div>
</div>

<!-- Editor Card -->
<div class="card border-0 shadow-card rounded-4 p-4 p-md-5 bg-white">
    <form action="<?= ADMIN_URL ?>/pages.php?slug=<?= urlencode($slug) ?>&lang=<?= urlencode($lang) ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="page_id" value="<?= $page['id'] ?>">

        <div class="mb-4">
            <label class="form-label small fw-bold text-navy">Page Section Title (<?= strtoupper($lang) ?>) <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control form-control-lg fw-bold" required value="<?= e($trans['title'] ?? $page['title']) ?>">
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold text-navy">Section Content (HTML &amp; Text Supported) <span class="text-danger">*</span></label>
            <textarea name="content" class="form-control font-monospace" rows="14" required style="font-size: 0.95rem;"><?= e($trans['content'] ?? '') ?></textarea>
            <small class="text-muted">You can use standard HTML tags like &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, etc.</small>
        </div>

        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <span class="badge bg-light text-muted border">Editing: <?= e($page['slug']) ?> [<?= strtoupper($lang) ?>]</span>
            <button type="submit" name="save_page" class="btn btn-sapsada-teal px-4">
                <i class="fas fa-save me-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
