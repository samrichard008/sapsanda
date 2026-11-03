<?php
/**
 * Admin Translations Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/includes/auth.php';
require_permission('manage_pages');

$current_page = 'translations';
$page_title = 'Multilingual Translations Manager';

$supported_languages = [
    'en' => ['name' => 'English', 'icon' => '🇬🇧', 'file' => __DIR__ . '/../includes/languages/en.php'],
    'si' => ['name' => 'සිංහල (Sinhala)', 'icon' => '🇱🇰', 'file' => __DIR__ . '/../includes/languages/si.php'],
    'ta' => ['name' => 'தமிழ் (Tamil)', 'icon' => '🇱🇰', 'file' => __DIR__ . '/../includes/languages/ta.php']
];

$active_lang = isset($_GET['lang']) && array_key_exists($_GET['lang'], $supported_languages) ? $_GET['lang'] : 'en';
$current_file = $supported_languages[$active_lang]['file'];

$success_msg = '';
$error_msg = '';

// Handle save translations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_translations'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security token invalid or expired. Please refresh and try again.';
    } else {
        $posted_translations = $_POST['trans'] ?? [];
        if (is_array($posted_translations) && !empty($posted_translations)) {
            // Load existing translations to preserve any keys not in the form
            $existing = file_exists($current_file) ? include $current_file : [];
            if (!is_array($existing)) $existing = [];

            foreach ($posted_translations as $key => $val) {
                $clean_key = trim($key);
                if (!empty($clean_key)) {
                    $existing[$clean_key] = trim($val);
                }
            }

            // Generate clean PHP file content
            $content = "<?php\n/**\n * " . $supported_languages[$active_lang]['name'] . " Language Dictionary\n * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT\n */\n\nreturn [\n";
            foreach ($existing as $k => $v) {
                $escaped_k = addcslashes($k, "'\\");
                $escaped_v = addcslashes($v, "'\\");
                $content .= "    '{$escaped_k}' => '{$escaped_v}',\n";
            }
            $content .= "];\n";

            if (file_put_contents($current_file, $content, LOCK_EX) !== false) {
                log_audit('update', 'translations', "Updated language dictionary for [{$active_lang}]");
                $success_msg = "Successfully saved translations for {$supported_languages[$active_lang]['name']}.";
            } else {
                $error_msg = "Failed to write to language file: {$current_file}. Please check directory write permissions.";
            }
        }
    }
}

// Load current dictionary
$translations = file_exists($current_file) ? include $current_file : [];
if (!is_array($translations)) $translations = [];

// Optional search filter
$search_query = trim($_GET['q'] ?? '');
if ($search_query !== '') {
    $filtered = [];
    foreach ($translations as $k => $v) {
        if (stripos($k, $search_query) !== false || stripos($v, $search_query) !== false) {
            $filtered[$k] = $v;
        }
    }
    $display_translations = $filtered;
} else {
    $display_translations = $translations;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-navy mb-1"><i class="fas fa-language text-teal me-2"></i>Translations Manager</h1>
            <p class="text-muted small mb-0">Update language strings for English, Sinhala, and Tamil across all public navigation, buttons, and sections.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?lang=<?= $active_lang ?>" target="_blank" class="btn btn-outline-teal btn-sm">
                <i class="fas fa-external-link-alt me-1"></i> Preview in <?= $supported_languages[$active_lang]['name'] ?>
            </a>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Language Selector Nav Tabs -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-6">
                    <ul class="nav nav-pills">
                        <?php foreach ($supported_languages as $code => $info): ?>
                            <li class="nav-item me-2">
                                <a class="nav-link <?= $active_lang === $code ? 'active bg-navy' : 'text-dark border' ?> px-3 py-2 fw-semibold" 
                                   href="?lang=<?= $code ?>">
                                    <span class="me-1"><?= $info['icon'] ?></span> <?= $info['name'] ?>
                                    <span class="badge bg-secondary ms-1 small" style="font-size:0.65rem;">
                                        <?= count(file_exists($info['file']) ? (include $info['file']) : []) ?> keys
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-6">
                    <form method="GET" class="d-flex justify-content-md-end gap-2">
                        <input type="hidden" name="lang" value="<?= htmlspecialchars($active_lang) ?>">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search keys or text..." value="<?= htmlspecialchars($search_query) ?>">
                            <?php if ($search_query !== ''): ?>
                                <a href="?lang=<?= $active_lang ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-navy btn-sm">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Translations Editor Form -->
    <form method="POST" action="?lang=<?= htmlspecialchars($active_lang) ?>">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="save_translations" value="1">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h6 class="mb-0 fw-bold text-navy">
                    <i class="fas fa-list-alt text-teal me-2"></i>
                    Translation Keys for: <span class="text-teal"><?= $supported_languages[$active_lang]['name'] ?></span>
                    <span class="badge bg-light text-dark ms-2"><?= count($display_translations) ?> displayed</span>
                </h6>
                <button type="submit" class="btn btn-gold btn-sm px-4 fw-bold">
                    <i class="fas fa-save me-1"></i> Save All Changes
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 320px;">Translation Key</th>
                                <th>Value in <?= $supported_languages[$active_lang]['name'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($display_translations)): ?>
                                <tr>
                                    <td colspan="2" class="text-center py-5 text-muted">
                                        <i class="fas fa-search fa-2x mb-3 text-secondary opacity-50"></i>
                                        <p class="mb-0">No translation keys found matching "<strong><?= htmlspecialchars($search_query) ?></strong>".</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($display_translations as $key => $val): ?>
                                    <tr>
                                        <td class="align-top py-3">
                                            <code class="text-navy fw-bold user-select-all"><?= htmlspecialchars($key) ?></code>
                                        </td>
                                        <td class="py-2">
                                            <?php if (strlen($val) > 100 || strpos($val, "\n") !== false): ?>
                                                <textarea name="trans[<?= htmlspecialchars($key) ?>]" class="form-control" rows="3" style="font-size:0.92rem;"><?= htmlspecialchars($val) ?></textarea>
                                            <?php else: ?>
                                                <input type="text" name="trans[<?= htmlspecialchars($key) ?>]" class="form-control" value="<?= htmlspecialchars($val) ?>" style="font-size:0.92rem;">
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small">File location: <code>includes/languages/<?= $active_lang ?>.php</code></span>
                <button type="submit" class="btn btn-gold px-4 fw-bold shadow-sm">
                    <i class="fas fa-save me-1"></i> Save All Changes
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
