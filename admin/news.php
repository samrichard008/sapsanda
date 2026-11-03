<?php
/**
 * Admin News & Press Releases Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$page_title = 'News & Press Management';
$current_page = 'news';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$action = clean_input($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);

$alert_msg = null;
$alert_type = 'success';

// Handle Delete
if ($action === 'delete' && $id > 0) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $db->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
        log_audit('Delete News', 'News', "Deleted article ID {$id}");
        header("Location: " . ADMIN_URL . "/news.php?msg=deleted");
        exit;
    }
}

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_news'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $alert_msg = 'Security token invalid.';
        $alert_type = 'danger';
    } else {
        $category = clean_input($_POST['category'] ?? 'General News');
        $title_en = clean_input($_POST['title_en'] ?? '');
        $title_si = clean_input($_POST['title_si'] ?? '');
        $title_ta = clean_input($_POST['title_ta'] ?? '');
        $summary_en = clean_input($_POST['summary_en'] ?? '');
        $summary_si = clean_input($_POST['summary_si'] ?? '');
        $summary_ta = clean_input($_POST['summary_ta'] ?? '');
        $content_en = $_POST['content_en'] ?? '';
        $content_si = $_POST['content_si'] ?? '';
        $content_ta = $_POST['content_ta'] ?? '';
        $published_at = clean_input($_POST['published_at'] ?? date('Y-m-d'));
        $status = clean_input($_POST['status'] ?? 'Published');
        $featured_image = clean_input($_POST['current_featured_image'] ?? 'assets/images/activities/awards-ceremony.jpg');

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title_en), '-'));
        if (empty($slug)) $slug = 'news-' . time();

        if (!empty($_FILES['image_file']['name'])) {
            $upload = handle_file_upload($_FILES['image_file'], 'news');
            if ($upload['success']) {
                $featured_image = $upload['path'];
            }
        }

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE news SET category = ?, featured_image = ?, published_at = ?, status = ? WHERE id = ?");
            $stmt->execute([$category, $featured_image, $published_at, $status, $id]);

            $upsert = $db->prepare("
                INSERT INTO news_translations (news_id, lang, title, summary, content)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), content = VALUES(content)
            ");
            $upsert->execute([$id, 'en', $title_en, $summary_en, $content_en]);
            if (!empty($title_si)) $upsert->execute([$id, 'si', $title_si, $summary_si, $content_si]);
            if (!empty($title_ta)) $upsert->execute([$id, 'ta', $title_ta, $summary_ta, $content_ta]);

            log_audit('Update News', 'News', "Updated news ID {$id}: {$title_en}");
            $alert_msg = 'News article updated successfully!';
        } else {
            $stmt = $db->prepare("
                INSERT INTO news (category, slug, featured_image, published_at, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$category, $slug, $featured_image, $published_at, $status]);
            $new_id = (int)$db->lastInsertId();

            $ins = $db->prepare("INSERT INTO news_translations (news_id, lang, title, summary, content) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$new_id, 'en', $title_en, $summary_en, $content_en]);
            if (!empty($title_si)) $ins->execute([$new_id, 'si', $title_si, $summary_si, $content_si]);
            if (!empty($title_ta)) $ins->execute([$new_id, 'ta', $title_ta, $summary_ta, $content_ta]);

            log_audit('Create News', 'News', "Created news ID {$new_id}: {$title_en}");
            $alert_msg = 'News article published successfully!';
        }
        $action = 'list';
    }
}

// Fetch for Edit
$edit_news = null;
$edit_trans = [];
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM news WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $edit_news = $stmt->fetch();
    if ($edit_news) {
        $stmt_t = $db->prepare("SELECT * FROM news_translations WHERE news_id = ?");
        $stmt_t->execute([$id]);
        while ($t = $stmt_t->fetch()) {
            $edit_trans[$t['lang']] = $t;
        }
    } else {
        $action = 'list';
    }
}

$articles = $db->query("
    SELECT n.*, COALESCE(nt.title, n.slug) AS display_title 
    FROM news n
    LEFT JOIN news_translations nt ON n.id = nt.news_id AND nt.lang = 'en'
    ORDER BY n.published_at DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-1">News Articles &amp; Press</h4>
        <small class="text-muted">Publish press releases, official announcements, and awards coverage</small>
    </div>
    <?php if ($action === 'list'): ?>
        <a href="<?= ADMIN_URL ?>/news.php?action=create" class="btn btn-sapsada-teal btn-sm">
            <i class="fas fa-plus me-1"></i> Add Article
        </a>
    <?php else: ?>
        <a href="<?= ADMIN_URL ?>/news.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Articles
        </a>
    <?php endif; ?>
</div>

<?php if ($alert_msg): ?>
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show rounded-3" role="alert">
        <?= e($alert_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($action === 'create' || $action === 'edit'): ?>
    <div class="card border-0 shadow-card rounded-4 p-4 p-md-5 bg-white">
        <h5 class="fw-bold text-navy mb-4"><?= $action === 'edit' ? 'Edit News Article' : 'Publish New Article' ?></h5>

        <form action="<?= ADMIN_URL ?>/news.php<?= $id > 0 ? '?action=edit&id='.$id : '?action=create' ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="current_featured_image" value="<?= e($edit_news['featured_image'] ?? 'assets/images/activities/awards-ceremony.jpg') ?>">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-navy">Category</label>
                    <input type="text" name="category" class="form-control" required placeholder="e.g. Awards & Recognition, Community Impact" value="<?= e($edit_news['category'] ?? 'General News') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-navy">Publish Date</label>
                    <input type="date" name="published_at" class="form-control" required value="<?= e($edit_news['published_at'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-navy">Status</label>
                    <select name="status" class="form-select">
                        <option value="Published" <?= (($edit_news['status'] ?? '') === 'Published') ? 'selected' : '' ?>>Published</option>
                        <option value="Draft" <?= (($edit_news['status'] ?? '') === 'Draft') ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-navy">Featured Photo</label>
                <input type="file" name="image_file" class="form-control" accept="image/*" data-preview="newsPreview">
                <div class="mt-2">
                    <img id="newsPreview" src="<?= ASSETS_URL ?>/../<?= e($edit_news['featured_image'] ?? 'assets/images/activities/awards-ceremony.jpg') ?>" class="rounded-3 shadow-subtle border" style="max-height: 120px; object-fit: cover;">
                </div>
            </div>

            <!-- Multilingual Tabs -->
            <ul class="nav nav-tabs mb-3" id="newsLangTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold" id="n-en-tab" data-bs-toggle="tab" data-bs-target="#n-tab-en" type="button">English</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="n-si-tab" data-bs-toggle="tab" data-bs-target="#n-tab-si" type="button">සිංහල</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="n-ta-tab" data-bs-toggle="tab" data-bs-target="#n-tab-ta" type="button">தமிழ்</button>
                </li>
            </ul>

            <div class="tab-content mb-4" id="newsLangTabsContent">
                <!-- English -->
                <div class="tab-pane fade show active" id="n-tab-en">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Article Title (English) <span class="text-danger">*</span></label>
                        <input type="text" name="title_en" class="form-control" required value="<?= e($edit_trans['en']['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Summary / Lead Paragraph (English)</label>
                        <textarea name="summary_en" class="form-control" rows="2"><?= e($edit_trans['en']['summary'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Full Article Content (English)</label>
                        <textarea name="content_en" class="form-control" rows="6"><?= e($edit_trans['en']['content'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Sinhala -->
                <div class="tab-pane fade" id="n-tab-si">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Article Title (සිංහල)</label>
                        <input type="text" name="title_si" class="form-control" value="<?= e($edit_trans['si']['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Summary (සිංහල)</label>
                        <textarea name="summary_si" class="form-control" rows="2"><?= e($edit_trans['si']['summary'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Full Content (සිංහල)</label>
                        <textarea name="content_si" class="form-control" rows="6"><?= e($edit_trans['si']['content'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Tamil -->
                <div class="tab-pane fade" id="n-tab-ta">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Article Title (தமிழ்)</label>
                        <input type="text" name="title_ta" class="form-control" value="<?= e($edit_trans['ta']['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Summary (தமிழ்)</label>
                        <textarea name="summary_ta" class="form-control" rows="2"><?= e($edit_trans['ta']['summary'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Full Content (தமிழ்)</label>
                        <textarea name="content_ta" class="form-control" rows="6"><?= e($edit_trans['ta']['content'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <button type="submit" name="save_news" class="btn btn-sapsada-teal px-4">
                <i class="fas fa-save me-1"></i> Save Article
            </button>
        </form>
    </div>

<?php else: ?>
    <!-- Table -->
    <div class="admin-table-card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">Photo</th>
                        <th>Title &amp; Category</th>
                        <th>Publish Date</th>
                        <th>Views</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $art): ?>
                        <tr>
                            <td><img src="<?= ASSETS_URL ?>/../<?= e($art['featured_image']) ?>" class="rounded-2 shadow-subtle border" style="width:70px; height:50px; object-fit:cover;"></td>
                            <td>
                                <div class="fw-bold text-navy"><?= e($art['display_title']) ?></div>
                                <span class="badge bg-light text-navy border small"><?= e($art['category']) ?></span>
                            </td>
                            <td><small><?= date('M d, Y', strtotime($art['published_at'])) ?></small></td>
                            <td><small><i class="fas fa-eye text-teal me-1"></i> <?= $art['views'] ?></small></td>
                            <td><span class="badge bg-<?= $art['status'] === 'Published' ? 'success' : 'secondary' ?>"><?= e($art['status']) ?></span></td>
                            <td class="text-end">
                                <a href="<?= ADMIN_URL ?>/news.php?action=edit&id=<?= $art['id'] ?>" class="btn btn-light btn-sm text-primary me-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?= ADMIN_URL ?>/news.php?action=delete&id=<?= $art['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-light btn-sm text-danger btn-confirm-delete" data-name="this news article">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
