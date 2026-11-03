<?php
/**
 * Admin Gallery Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$page_title = 'Gallery & Albums';
$current_page = 'gallery';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$action = clean_input($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);

$alert_msg = null;
$alert_type = 'success';

$gallery_categories = [
    'Social Welfare Activities',
    'Community Events',
    'Public Awareness',
    'Projects',
    'Volunteer Activities',
    'Organization Events',
    'Life Protection'
];

// Handle Image Delete
if ($action === 'delete_img' && $id > 0) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $db->prepare("DELETE FROM gallery_images WHERE id = ?")->execute([$id]);
        log_audit('Delete Gallery Image', 'Gallery', "Deleted image ID {$id}");
        header("Location: " . ADMIN_URL . "/gallery.php?msg=deleted");
        exit;
    }
}

// Handle Add Image Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $alert_msg = 'Security token invalid.';
        $alert_type = 'danger';
    } else {
        $album_id = (int)$_POST['album_id'];
        $title = clean_input($_POST['title'] ?? '');
        $caption = clean_input($_POST['caption'] ?? '');
        $video_url = clean_input($_POST['video_url'] ?? '');

        if (!empty($_FILES['image_file']['name'])) {
            $upload = handle_file_upload($_FILES['image_file'], 'gallery');
            if ($upload['success']) {
                $stmt = $db->prepare("INSERT INTO gallery_images (album_id, title, image_path, video_url, caption) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$album_id, $title, $upload['path'], $video_url, $caption]);
                log_audit('Upload Gallery Image', 'Gallery', "Uploaded image {$title} to album {$album_id}");
                $alert_msg = 'Image uploaded successfully!';
            } else {
                $alert_msg = 'Upload Error: ' . $upload['error'];
                $alert_type = 'danger';
            }
        } else {
            $alert_msg = 'Please select an image file to upload.';
            $alert_type = 'danger';
        }
    }
}

// Handle New Album
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_album'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $album_title = clean_input($_POST['album_title'] ?? '');
        $album_cat = clean_input($_POST['category'] ?? 'Social Welfare Activities');
        $album_desc = clean_input($_POST['description'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $album_title), '-')) . '-' . time();

        if (!empty($album_title)) {
            $stmt = $db->prepare("INSERT INTO gallery_albums (title, slug, category, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$album_title, $slug, $album_cat, $album_desc]);
            log_audit('Create Album', 'Gallery', "Created album {$album_title}");
            $alert_msg = 'New album created successfully!';
        }
    }
}

$albums = $db->query("SELECT * FROM gallery_albums ORDER BY id ASC")->fetchAll();
$images = $db->query("
    SELECT gi.*, ga.title as album_title, ga.category
    FROM gallery_images gi
    JOIN gallery_albums ga ON gi.album_id = ga.id
    ORDER BY gi.id DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-1">Photo &amp; Video Gallery</h4>
        <small class="text-muted">Manage albums and upload high-resolution evidence of community operations</small>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newAlbumModal">
            <i class="fas fa-folder-plus me-1"></i> New Album
        </button>
        <button type="button" class="btn btn-sapsada-teal btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-cloud-upload-alt me-1"></i> Upload Photo
        </button>
    </div>
</div>

<?php if ($alert_msg): ?>
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show rounded-3" role="alert">
        <?= e($alert_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Albums Cards Overview -->
<div class="row g-3 mb-4">
    <?php foreach ($albums as $alb): ?>
        <?php
            $count = (int)$db->query("SELECT COUNT(*) FROM gallery_images WHERE album_id = {$alb['id']}")->fetchColumn();
        ?>
        <div class="col-md-3 col-sm-6">
            <div class="p-3 bg-white rounded-3 shadow-subtle border">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge bg-sky-light text-teal small"><?= e($alb['category']) ?></span>
                    <span class="badge bg-light text-muted border"><?= $count ?> Photos</span>
                </div>
                <h6 class="fw-bold text-navy mb-1 text-truncate"><?= e($alb['title']) ?></h6>
                <small class="text-muted text-truncate d-block"><?= e($alb['description'] ?: 'General Album') ?></small>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Uploaded Photos Grid -->
<div class="admin-table-card p-4">
    <h6 class="fw-bold text-navy mb-3">All Uploaded Photos &amp; Media</h6>

    <div class="row g-3">
        <?php foreach ($images as $img): ?>
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card h-100 border shadow-subtle rounded-3 overflow-hidden">
                    <img src="<?= ASSETS_URL ?>/../<?= e($img['image_path']) ?>" class="card-img-top" style="height:160px; object-fit:cover;">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            <span class="badge bg-light text-navy border small mb-1"><?= e($img['album_title']) ?></span>
                            <div class="fw-bold text-navy small text-truncate"><?= e($img['title']) ?></div>
                            <small class="text-muted text-truncate d-block"><?= e($img['caption']) ?></small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                            <small class="text-muted"><?= date('M d, Y', strtotime($img['created_at'])) ?></small>
                            <a href="<?= ADMIN_URL ?>/gallery.php?action=delete_img&id=<?= $img['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-outline-danger btn-sm p-1 px-2 btn-confirm-delete" data-name="photo">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal: New Album -->
<div class="modal fade" id="newAlbumModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-navy">Create New Gallery Album</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= ADMIN_URL ?>/gallery.php" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Album Title <span class="text-danger">*</span></label>
                        <input type="text" name="album_title" class="form-control" required placeholder="e.g. Flood Rescue Drills 2026">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Category</label>
                        <select name="category" class="form-select">
                            <?php foreach ($gallery_categories as $cat): ?>
                                <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="submit" name="create_album" class="btn btn-sapsada-teal">Create Album</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Upload Photo -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-navy">Upload Photo to Gallery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= ADMIN_URL ?>/gallery.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Select Album <span class="text-danger">*</span></label>
                        <select name="album_id" class="form-select" required>
                            <?php foreach ($albums as $alb): ?>
                                <option value="<?= $alb['id'] ?>"><?= e($alb['title']) ?> (<?= e($alb['category']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Photo Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Volunteers loading canal debris">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Image File (JPG, PNG, WebP) <span class="text-danger">*</span></label>
                        <input type="file" name="image_file" class="form-control" required accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">Caption / Details</label>
                        <input type="text" name="caption" class="form-control" placeholder="Optional brief caption">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-navy">YouTube Video URL (Optional)</label>
                        <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="submit" name="upload_image" class="btn btn-sapsada-teal">Upload Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
