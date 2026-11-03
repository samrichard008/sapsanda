<?php
/**
 * Admin Website Settings Management
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/includes/auth.php';
require_permission('manage_settings');

$current_page = 'settings';
$page_title = 'Website Settings';

$db = get_db();
$success_msg = '';
$error_msg = '';

// Handle save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security token invalid or expired. Please refresh and try again.';
    } else {
        $settings_input = $_POST['settings'] ?? [];
        if (!empty($settings_input)) {
            try {
                $db->beginTransaction();
                $stmt = $db->prepare("
                    INSERT INTO website_settings (setting_key, setting_value, setting_group) 
                    VALUES (:key, :val, :group)
                    ON DUPLICATE KEY UPDATE setting_value = :val
                ");

                // Mapping of keys to groups
                $groups = [
                    'site_name' => 'general', 'site_name_si' => 'general', 'site_name_ta' => 'general',
                    'reg_number' => 'general', 'slogan' => 'general', 'slogan_si' => 'general', 'slogan_ta' => 'general',
                    'phone_primary' => 'contact', 'phone_secondary' => 'contact', 'phone_hotline' => 'contact',
                    'email_official' => 'contact', 'whatsapp_number' => 'contact',
                    'address_physical' => 'contact', 'address_physical_si' => 'contact', 'address_physical_ta' => 'contact',
                    'google_map_embed' => 'contact',
                    'stat_people_supported' => 'impact', 'stat_community_projects' => 'impact',
                    'stat_active_volunteers' => 'impact', 'stat_awareness_campaigns' => 'impact',
                    'bank_name' => 'bank', 'bank_account_name' => 'bank', 'bank_account_no' => 'bank',
                    'bank_branch' => 'bank', 'bank_swift' => 'bank', 'bank_notice' => 'bank',
                    'facebook_url' => 'social', 'youtube_url' => 'social', 'instagram_url' => 'social',
                    'seo_meta_description' => 'seo', 'seo_meta_keywords' => 'seo'
                ];

                foreach ($settings_input as $key => $val) {
                    $group = $groups[$key] ?? 'general';
                    $stmt->execute([
                        ':key' => trim($key),
                        ':val' => trim($val),
                        ':group' => $group
                    ]);
                }

                $db->commit();
                log_audit('update', 'settings', 'Updated website configuration settings');
                $success_msg = 'Website settings updated successfully!';
            } catch (PDOException $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $error_msg = 'Failed to save settings: ' . $e->getMessage();
            }
        }
    }
}

// Fetch all current settings
$stmt = $db->query("SELECT setting_key, setting_value FROM website_settings");
$current_settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}

function s_val($key, $settings, $default = '') {
    return htmlspecialchars($settings[$key] ?? $default);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-navy mb-1"><i class="fas fa-sliders-h text-teal me-2"></i>Website &amp; Organization Settings</h1>
            <p class="text-muted small mb-0">Configure official contact info, registration details, bank accounts, impact statistics, and SEO.</p>
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

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="save_settings" value="1">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-0 border-bottom">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs card-header-tabs m-0 border-0" id="settingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-4 py-3 fw-bold border-0 text-navy" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                            <i class="fas fa-id-card text-teal me-2"></i> Organization Identity
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 py-3 fw-bold border-0 text-navy" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                            <i class="fas fa-phone-alt text-teal me-2"></i> Contact &amp; Location
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 py-3 fw-bold border-0 text-navy" id="impact-tab" data-bs-toggle="tab" data-bs-target="#impact" type="button" role="tab">
                            <i class="fas fa-chart-pie text-teal me-2"></i> Impact Counters
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 py-3 fw-bold border-0 text-navy" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab">
                            <i class="fas fa-university text-teal me-2"></i> Bank Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 py-3 fw-bold border-0 text-navy" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab">
                            <i class="fas fa-globe text-teal me-2"></i> Social &amp; SEO
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="settingsTabContent">
                    
                    <!-- TAB 1: General Identity -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Official Name (English) <span class="text-danger">*</span></label>
                                <input type="text" name="settings[site_name]" class="form-control" value="<?= s_val('site_name', $current_settings) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Official Registration Number <span class="text-danger">*</span></label>
                                <input type="text" name="settings[reg_number]" class="form-control" value="<?= s_val('reg_number', $current_settings) ?>" required>
                                <div class="form-text">e.g. G A 00282904 (Officially registered NGO in Sri Lanka)</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Official Name (Sinhala - සිංහල)</label>
                                <input type="text" name="settings[site_name_si]" class="form-control" value="<?= s_val('site_name_si', $current_settings) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Official Name (Tamil - தமிழ்)</label>
                                <input type="text" name="settings[site_name_ta]" class="form-control" value="<?= s_val('site_name_ta', $current_settings) ?>">
                            </div>
                            <div class="col-12 mt-4">
                                <hr class="text-muted">
                                <h6 class="fw-bold text-navy mb-3"><i class="fas fa-quote-left text-teal me-2"></i>Organization Slogans</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Slogan (English)</label>
                                <input type="text" name="settings[slogan]" class="form-control" value="<?= s_val('slogan', $current_settings) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Slogan (Sinhala)</label>
                                <input type="text" name="settings[slogan_si]" class="form-control" value="<?= s_val('slogan_si', $current_settings) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Slogan (Tamil)</label>
                                <input type="text" name="settings[slogan_ta]" class="form-control" value="<?= s_val('slogan_ta', $current_settings) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Contact & Location -->
                    <div class="tab-pane fade" id="contact" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Primary Telephone <span class="text-danger">*</span></label>
                                <input type="text" name="settings[phone_primary]" class="form-control" value="<?= s_val('phone_primary', $current_settings) ?>" required>
                                <div class="form-text">e.g. 070 7261978</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Secondary Telephone</label>
                                <input type="text" name="settings[phone_secondary]" class="form-control" value="<?= s_val('phone_secondary', $current_settings) ?>">
                                <div class="form-text">e.g. 072 253 2493</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Hotline</label>
                                <input type="text" name="settings[phone_hotline]" class="form-control" value="<?= s_val('phone_hotline', $current_settings) ?>">
                                <div class="form-text">e.g. 0777 26 19 78</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Official Email <span class="text-danger">*</span></label>
                                <input type="email" name="settings[email_official]" class="form-control" value="<?= s_val('email_official', $current_settings) ?>" required>
                                <div class="form-text">e.g. sapsadaunit@gmail.com</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">WhatsApp Hotline (with Country Code)</label>
                                <input type="text" name="settings[whatsapp_number]" class="form-control" value="<?= s_val('whatsapp_number', $current_settings) ?>">
                                <div class="form-text">e.g. +94707261978 (used for floating button and click-to-chat)</div>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold">Physical Address (English)</label>
                                <input type="text" name="settings[address_physical]" class="form-control" value="<?= s_val('address_physical', $current_settings) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Physical Address (Sinhala)</label>
                                <input type="text" name="settings[address_physical_si]" class="form-control" value="<?= s_val('address_physical_si', $current_settings) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Physical Address (Tamil)</label>
                                <input type="text" name="settings[address_physical_ta]" class="form-control" value="<?= s_val('address_physical_ta', $current_settings) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Google Maps Embed URL</label>
                                <input type="url" name="settings[google_map_embed]" class="form-control" value="<?= s_val('google_map_embed', $current_settings) ?>">
                                <div class="form-text">Enter embed URL or Google Maps link for the Contact Us page interactive map.</div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Impact Counters -->
                    <div class="tab-pane fade" id="impact" role="tabpanel">
                        <div class="alert alert-info border-0 shadow-sm mb-4">
                            <i class="fas fa-info-circle me-2"></i> These values are displayed as live animated impact counters across the homepage and About Us page.
                        </div>
                        <div class="row g-4">
                            <div class="col-md-3">
                                <div class="p-3 border rounded bg-light text-center">
                                    <i class="fas fa-users fa-2x text-teal mb-2"></i>
                                    <label class="form-label fw-bold d-block">People Supported</label>
                                    <input type="text" name="settings[stat_people_supported]" class="form-control text-center fw-bold text-navy" value="<?= s_val('stat_people_supported', $current_settings, '12,500+') ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded bg-light text-center">
                                    <i class="fas fa-hand-holding-heart fa-2x text-teal mb-2"></i>
                                    <label class="form-label fw-bold d-block">Community Projects</label>
                                    <input type="text" name="settings[stat_community_projects]" class="form-control text-center fw-bold text-navy" value="<?= s_val('stat_community_projects', $current_settings, '48+') ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded bg-light text-center">
                                    <i class="fas fa-hands-helping fa-2x text-teal mb-2"></i>
                                    <label class="form-label fw-bold d-block">Active Volunteers</label>
                                    <input type="text" name="settings[stat_active_volunteers]" class="form-control text-center fw-bold text-navy" value="<?= s_val('stat_active_volunteers', $current_settings, '350+') ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded bg-light text-center">
                                    <i class="fas fa-bullhorn fa-2x text-teal mb-2"></i>
                                    <label class="form-label fw-bold d-block">Awareness Campaigns</label>
                                    <input type="text" name="settings[stat_awareness_campaigns]" class="form-control text-center fw-bold text-navy" value="<?= s_val('stat_awareness_campaigns', $current_settings, '85+') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: Bank Details -->
                    <div class="tab-pane fade" id="bank" role="tabpanel">
                        <div class="alert alert-warning border-0 shadow-sm mb-4">
                            <i class="fas fa-shield-alt me-2"></i> <strong>Financial Integrity:</strong> Ensure bank account details match the organization's official bank account certified by the board of trustees.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bank Name</label>
                                <input type="text" name="settings[bank_name]" class="form-control" value="<?= s_val('bank_name', $current_settings) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Account Holder Name</label>
                                <input type="text" name="settings[bank_account_name]" class="form-control" value="<?= s_val('bank_account_name', $current_settings) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Account Number</label>
                                <input type="text" name="settings[bank_account_no]" class="form-control font-monospace" value="<?= s_val('bank_account_no', $current_settings) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Bank Branch</label>
                                <input type="text" name="settings[bank_branch]" class="form-control" value="<?= s_val('bank_branch', $current_settings) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">SWIFT / BIC Code</label>
                                <input type="text" name="settings[bank_swift]" class="form-control font-monospace" value="<?= s_val('bank_swift', $current_settings) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Official Bank Notice / Instructions</label>
                                <textarea name="settings[bank_notice]" class="form-control" rows="3"><?= s_val('bank_notice', $current_settings) ?></textarea>
                                <div class="form-text">Instructions displayed to donors regarding bank deposit receipts and confirmation slips.</div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: Social & SEO -->
                    <div class="tab-pane fade" id="seo" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><i class="fab fa-facebook text-primary me-1"></i> Facebook Page URL</label>
                                <input type="url" name="settings[facebook_url]" class="form-control" value="<?= s_val('facebook_url', $current_settings) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><i class="fab fa-youtube text-danger me-1"></i> YouTube Channel URL</label>
                                <input type="url" name="settings[youtube_url]" class="form-control" value="<?= s_val('youtube_url', $current_settings) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><i class="fab fa-instagram text-danger me-1"></i> Instagram Profile URL</label>
                                <input type="url" name="settings[instagram_url]" class="form-control" value="<?= s_val('instagram_url', $current_settings) ?>">
                            </div>
                            <div class="col-12 mt-4">
                                <hr class="text-muted">
                                <h6 class="fw-bold text-navy mb-3"><i class="fas fa-search text-teal me-2"></i>Search Engine Optimization (SEO) Defaults</h6>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Default Meta Description</label>
                                <textarea name="settings[seo_meta_description]" class="form-control" rows="3"><?= s_val('seo_meta_description', $current_settings) ?></textarea>
                                <div class="form-text">Search snippet displayed in Google search results (150-160 characters recommended).</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Default Meta Keywords</label>
                                <input type="text" name="settings[seo_meta_keywords]" class="form-control" value="<?= s_val('seo_meta_keywords', $current_settings) ?>">
                                <div class="form-text">Comma-separated keywords (e.g. Sap Sada, Life Protection, Sri Lanka NGO, Panadura Welfare).</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small"><i class="fas fa-clock me-1"></i> Last updated: <?= date('Y-m-d H:i:s') ?></span>
                <button type="submit" class="btn btn-gold px-4 fw-bold shadow-sm">
                    <i class="fas fa-save me-1"></i> Save Website Settings
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
