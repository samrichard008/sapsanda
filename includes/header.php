<?php
/**
 * Global Header Template
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Prepare dynamic page metadata
$page_title = isset($custom_page_title) ? $custom_page_title . ' | ' . e(get_setting('site_name', ORG_NAME)) : e(get_setting('site_name', ORG_NAME)) . ' - ' . e(get_setting('slogan', 'Together We Care. Together We Protect Life.'));
$meta_desc = isset($custom_meta_desc) ? $custom_meta_desc : e(get_setting('seo_meta_description', 'Official portal of SAP SADA Social Welfare & Life Protection Unit (Reg: G A 00282904), Sri Lanka.'));
$canonical_url = BASE_URL . $_SERVER['REQUEST_URI'];
$og_image = isset($custom_og_image) ? $custom_og_image : ASSETS_URL . '/images/branding/logo.png';
$active_page = $active_page ?? 'home';

// Current query parameters for language switcher (preserving existing GET parameters)
function lang_url(string $lang_code): string {
    $params = $_GET;
    $params['lang'] = $lang_code;
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="<?= e(CURRENT_LANG) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <!-- SEO & Metadata -->
    <meta name="description" content="<?= $meta_desc ?>">
    <meta name="keywords" content="<?= e(get_setting('seo_meta_keywords', 'Sap Sada, NGO, Sri Lanka, Welfare, Life Protection')) ?>">
    <meta name="author" content="SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT">
    <link rel="canonical" href="<?= e($canonical_url) ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($canonical_url) ?>">
    <meta property="og:title" content="<?= $page_title ?>">
    <meta property="og:description" content="<?= $meta_desc ?>">
    <meta property="og:image" content="<?= e($og_image) ?>">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?= $page_title ?>">
    <meta property="twitter:description" content="<?= $meta_desc ?>">
    <meta property="twitter:image" content="<?= e($og_image) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= ASSETS_URL ?>/images/branding/logo.png">
    <link rel="apple-touch-icon" href="<?= ASSETS_URL ?>/images/branding/logo.png">

    <!-- Google Fonts: Inter, Outfit & Sri Lankan Scripts (Sinhala, Tamil) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;500;600;700;800&family=Noto+Sans+Tamil:wght@400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css?v=1.0.1">
</head>
<body lang="<?= e(CURRENT_LANG) ?>">

    <!-- 1. Top Official Contact & Language Bar -->
    <div class="top-info-bar">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <span class="reg-badge">
                    <i class="fas fa-certificate text-warning"></i> 
                    <?= __('reg_number_label') ?>: <?= e(get_setting('reg_number', ORG_REG_NO)) ?>
                </span>
                <span class="d-none d-md-inline text-white-50">|</span>
                <a href="tel:<?= preg_replace('/[^0-9+]/', '', get_setting('phone_primary', ORG_PHONE_1)) ?>" class="d-flex align-items-center gap-1">
                    <i class="fas fa-phone-alt text-cyan"></i> <?= e(get_setting('phone_primary', ORG_PHONE_1)) ?>
                </a>
                <a href="tel:<?= preg_replace('/[^0-9+]/', '', get_setting('phone_secondary', ORG_PHONE_2)) ?>" class="d-none d-lg-flex align-items-center gap-1">
                    <i class="fas fa-phone-alt text-cyan"></i> <?= e(get_setting('phone_secondary', ORG_PHONE_2)) ?>
                </a>
                <a href="mailto:<?= e(get_setting('email_official', ORG_EMAIL)) ?>" class="d-none d-md-flex align-items-center gap-1">
                    <i class="fas fa-envelope text-cyan"></i> <?= e(get_setting('email_official', ORG_EMAIL)) ?>
                </a>
            </div>

            <!-- Language Switcher -->
            <div class="d-flex align-items-center gap-1 ms-auto">
                <span class="small me-1 text-white-50 d-none d-sm-inline"><i class="fas fa-globe me-1"></i>Language:</span>
                <a href="<?= lang_url('en') ?>" class="lang-selector-btn <?= CURRENT_LANG === 'en' ? 'active' : '' ?>">English</a>
                <a href="<?= lang_url('si') ?>" class="lang-selector-btn <?= CURRENT_LANG === 'si' ? 'active' : '' ?>">සිංහල</a>
                <a href="<?= lang_url('ta') ?>" class="lang-selector-btn <?= CURRENT_LANG === 'ta' ? 'active' : '' ?>">தமிழ்</a>
            </div>
        </div>
    </div>

    <!-- 2. Main Navigation Bar -->
    <?php require_once __DIR__ . '/navigation.php'; ?>
