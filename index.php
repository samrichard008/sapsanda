<?php
/**
 * Master Homepage
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$active_page = 'home';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// 1. Fetch Active Banners
$banners = [];
try {
    $stmt = $db->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    $banners = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback if empty
}

// 2. Fetch Core Activities (with translation)
$activities = [];
try {
    $stmt = $db->prepare("
        SELECT a.*, COALESCE(at.title, a.slug) AS localized_title, COALESCE(at.description, '') AS localized_desc
        FROM activities a
        LEFT JOIN activity_translations at ON a.id = at.activity_id AND at.lang = ?
        WHERE a.status != 'cancelled'
        ORDER BY a.is_featured DESC, a.activity_date DESC
        LIMIT 6
    ");
    $stmt->execute([CURRENT_LANG]);
    $activities = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error
}

// 3. Fetch Featured Projects (with translation)
$projects = [];
try {
    $stmt = $db->prepare("
        SELECT p.*, COALESCE(pt.title, p.slug) AS localized_title, COALESCE(pt.description, '') AS localized_desc
        FROM projects p
        LEFT JOIN project_translations pt ON p.id = pt.project_id AND pt.lang = ?
        WHERE p.status IN ('Ongoing', 'Planned')
        ORDER BY p.start_date DESC
        LIMIT 3
    ");
    $stmt->execute([CURRENT_LANG]);
    $projects = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error
}

// 4. Fetch Upcoming Events
$events = [];
try {
    $stmt = $db->query("
        SELECT * FROM events 
        WHERE status = 'Upcoming' AND event_date >= CURDATE()
        ORDER BY event_date ASC 
        LIMIT 3
    ");
    $events = $stmt->fetchAll();
    // If no future events, load latest
    if (empty($events)) {
        $stmt = $db->query("SELECT * FROM events ORDER BY event_date DESC LIMIT 3");
        $events = $stmt->fetchAll();
    }
} catch (Exception $e) {}

// 5. Fetch Latest News
$news_list = [];
try {
    $stmt = $db->prepare("
        SELECT n.*, COALESCE(nt.title, n.slug) AS localized_title, COALESCE(nt.summary, '') AS localized_summary
        FROM news n
        LEFT JOIN news_translations nt ON n.id = nt.news_id AND nt.lang = ?
        WHERE n.status = 'Published'
        ORDER BY n.published_at DESC
        LIMIT 3
    ");
    $stmt->execute([CURRENT_LANG]);
    $news_list = $stmt->fetchAll();
} catch (Exception $e) {}

// 6. Fetch Gallery Preview
$gallery_items = [];
try {
    $stmt = $db->query("
        SELECT gi.*, ga.title AS album_title 
        FROM gallery_images gi
        JOIN gallery_albums ga ON gi.album_id = ga.id
        ORDER BY gi.id DESC
        LIMIT 6
    ");
    $gallery_items = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<!-- =========================================================================
     SECTION 1: HERO BANNER SLIDER
     ========================================================================= -->
<section class="hero-slider-section">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">
        <!-- Indicators -->
        <div class="carousel-indicators">
            <?php foreach ($banners as $idx => $b): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $idx ?>" class="<?= $idx === 0 ? 'active' : '' ?>"></button>
            <?php endforeach; ?>
        </div>

        <div class="carousel-inner">
            <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $idx => $banner): ?>
                    <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                        <div class="hero-slide-item" style="background-image: url('<?= ASSETS_URL ?>/../<?= e($banner['image_path']) ?>');">
                            <div class="hero-overlay"></div>
                            <div class="container hero-content">
                                <div class="badge bg-gold text-dark fw-bold px-3 py-2 rounded-pill mb-3">
                                    <i class="fas fa-shield-alt me-1"></i> <?= __('reg_number_label') ?>: <?= e(get_setting('reg_number', ORG_REG_NO)) ?>
                                </div>
                                <h1 class="hero-headline"><?= e($banner['title']) ?></h1>
                                <p class="hero-subtext"><?= e($banner['subtitle']) ?></p>
                                <div class="d-flex flex-wrap gap-3">
                                    <a href="<?= e($banner['primary_btn_url'] ?: 'about.php') ?>" class="btn btn-sapsada-teal btn-lg">
                                        <i class="fas fa-compass me-2"></i> <?= e($banner['primary_btn_text'] ?: __('hero_btn_mission')) ?>
                                    </a>
                                    <a href="<?= e($banner['secondary_btn_url'] ?: 'volunteers.php') ?>" class="btn btn-sapsada-outline text-white border-white btn-lg">
                                        <i class="fas fa-hands-helping me-2"></i> <?= e($banner['secondary_btn_text'] ?: __('hero_btn_volunteer')) ?>
                                    </a>
                                    <a href="donate.php" class="btn btn-sapsada-gold btn-lg">
                                        <i class="fas fa-heart me-2"></i> <?= __('hero_btn_donate') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default Hero Slide -->
                <div class="carousel-item active">
                    <div class="hero-slide-item" style="background-image: url('<?= ASSETS_URL ?>/images/branding/banner-en.png');">
                        <div class="hero-overlay"></div>
                        <div class="container hero-content">
                            <h1 class="hero-headline"><?= __('hero_headline') ?></h1>
                            <p class="hero-subtext"><?= __('hero_subheading') ?></p>
                            <div class="d-flex flex-wrap gap-3">
                                <a href="about.php" class="btn btn-sapsada-teal btn-lg"><?= __('hero_btn_mission') ?></a>
                                <a href="volunteers.php" class="btn btn-sapsada-outline text-white border-white btn-lg"><?= __('hero_btn_volunteer') ?></a>
                                <a href="donate.php" class="btn btn-sapsada-gold btn-lg"><?= __('hero_btn_donate') ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    </div>
</section>

<!-- =========================================================================
     SECTION 2: WHO WE ARE (INTRODUCTION)
     ========================================================================= -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-badge"><?= __('sec_who_we_are') ?></span>
                <h2 class="section-title mb-4"><?= e(get_setting('site_name', ORG_NAME)) ?></h2>
                <p class="lead text-navy fw-semibold mb-3">
                    <?= e(get_setting('slogan', 'Together We Care. Together We Protect Life.')) ?>
                </p>
                <p class="text-secondary mb-4">
                    Headquartered at <strong>Jayabodhi Viharaya, Nalluruwa, Panadura</strong> (Registration No: <strong><?= e(get_setting('reg_number', ORG_REG_NO)) ?></strong>), our unit is a dedicated humanitarian service entity in Sri Lanka. We operate on the direct ethos of unyielding public service: protecting human lives in high-risk aquatic zones, clearing heavily choked canals to mitigate floods, and providing economic resilience to vulnerable mothers and families.
                </p>
                
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light border-start border-4 border-primary">
                            <h6 class="fw-bold mb-1 text-navy"><i class="fas fa-check-circle text-teal me-2"></i>Life Protection</h6>
                            <small class="text-muted">Aquatic rescue training, safety patrols and coastal awareness.</small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light border-start border-4 border-teal">
                            <h6 class="fw-bold mb-1 text-navy"><i class="fas fa-leaf text-teal me-2"></i>Wetland Preservation</h6>
                            <small class="text-muted">Heavy machinery dredging and plastic-free water systems.</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    <a href="about.php" class="btn btn-sapsada-primary">
                        <i class="fas fa-book-open me-2"></i> <?= __('learn_more') ?>
                    </a>
                    <a href="contact.php" class="btn btn-outline-secondary">
                        <i class="fas fa-map-marker-alt me-2"></i> <?= __('nav_contact') ?>
                    </a>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="position-relative p-2">
                    <div class="rounded-4 overflow-hidden shadow-elevated border border-4 border-white">
                        <img src="<?= ASSETS_URL ?>/images/activities/swim-protection.jpg" alt="Sap Sada Life Protection Challenge" class="img-fluid w-100" style="min-height: 380px; object-fit: cover;">
                    </div>
                    <!-- Overlaid Certificate / Reg Card -->
                    <div class="position-absolute bottom-0 start-0 m-4 p-3 bg-navy text-white rounded-3 shadow-lg d-none d-sm-flex align-items-center gap-3" style="max-width: 320px; background: rgba(5,37,74,0.92); backdrop-filter: blur(8px);">
                        <i class="fas fa-award text-gold fs-1"></i>
                        <div>
                            <div class="fw-bold small text-gold">Official Registration</div>
                            <div class="fw-extrabold text-white">G A 00282904</div>
                            <div class="small text-white-50">Democratic Socialist Republic of Sri Lanka</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================================
     SECTION 3: MISSION & VISION
     ========================================================================= -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge"><?= __('sec_mission_vision') ?></span>
            <h2 class="section-title"><?= __('sec_mission_vision_sub') ?></h2>
        </div>

        <div class="row g-4">
            <!-- Mission Card -->
            <div class="col-lg-6">
                <div class="p-5 bg-white rounded-4 shadow-card h-100 border-top border-5 border-primary position-relative">
                    <div class="d-inline-flex align-items-center justify-content-center bg-sky-light text-primary rounded-circle mb-4" style="width: 70px; height: 70px; font-size: 1.9rem;">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3 class="fw-bold text-navy mb-3"><?= __('mission_title') ?></h3>
                    <p class="text-secondary fs-5 mb-0">
                        To foster a caring, resilient, and empowered society by delivering dedicated life protection services, emergency support, environmental preservation, and social welfare programs rooted in transparency and community cooperation.
                    </p>
                </div>
            </div>

            <!-- Vision Card -->
            <div class="col-lg-6">
                <div class="p-5 bg-white rounded-4 shadow-card h-100 border-top border-5 border-teal position-relative">
                    <div class="d-inline-flex align-items-center justify-content-center bg-sky-light text-teal rounded-circle mb-4" style="width: 70px; height: 70px; font-size: 1.9rem;">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 class="fw-bold text-navy mb-3"><?= __('vision_title') ?></h3>
                    <p class="text-secondary fs-5 mb-0">
                        A thriving Sri Lanka where every human life is cherished and safeguarded, natural aquatic ecosystems are preserved, and every community thrives with dignity, solidarity, and mutual responsibility.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================================
     SECTION 4: OUR CORE ACTIVITIES
     ========================================================================= -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5">
            <div>
                <span class="section-badge"><?= __('sec_core_activities') ?></span>
                <h2 class="section-title mb-0"><?= __('sec_core_activities_sub') ?></h2>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="activities.php" class="btn btn-outline-primary">
                    <?= __('view_details') ?> <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($activities as $act): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="sapsada-card">
                        <div class="card-img-wrap">
                            <span class="card-badge"><i class="fas fa-tag me-1"></i> <?= e($act['category']) ?></span>
                            <img src="<?= ASSETS_URL ?>/../<?= e($act['featured_image']) ?>" alt="<?= e($act['localized_title']) ?>" loading="lazy">
                        </div>
                        <div class="card-body-sapsada">
                            <div class="d-flex align-items-center text-muted small mb-2 gap-3">
                                <span><i class="fas fa-map-marker-alt text-teal me-1"></i> <?= e($act['location']) ?></span>
                                <span><i class="fas fa-calendar-alt text-teal me-1"></i> <?= date('M d, Y', strtotime($act['activity_date'])) ?></span>
                            </div>
                            <h5 class="fw-bold text-navy mb-3"><?= e($act['localized_title']) ?></h5>
                            <div class="text-secondary small mb-4 flex-grow-1">
                                <?= substr(strip_tags($act['localized_desc']), 0, 130) ?>...
                            </div>
                            <a href="activity-detail.php?id=<?= $act['id'] ?>" class="btn btn-sapsada-outline btn-sm w-100">
                                <?= __('learn_more') ?> <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =========================================================================
     SECTION 5: DYNAMIC IMPACT STATISTICS
     ========================================================================= -->
<section class="section-padding stats-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge bg-white text-navy"><?= __('sec_impact_stats') ?></span>
            <h2 class="text-white fw-bold display-6"><?= __('sec_impact_stats_sub') ?></h2>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- People Supported -->
            <div class="col-lg-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon"><i class="fas fa-users"></i></div>
                    <div class="stats-number counter-value" data-target="<?= e(get_setting('stat_people_supported', '12,500+')) ?>">
                        <?= e(get_setting('stat_people_supported', '12,500+')) ?>
                    </div>
                    <div class="stats-label">People Supported</div>
                </div>
            </div>

            <!-- Community Projects -->
            <div class="col-lg-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon"><i class="fas fa-project-diagram"></i></div>
                    <div class="stats-number counter-value" data-target="<?= e(get_setting('stat_community_projects', '48+')) ?>">
                        <?= e(get_setting('stat_community_projects', '48+')) ?>
                    </div>
                    <div class="stats-label">Community Projects</div>
                </div>
            </div>

            <!-- Active Volunteers -->
            <div class="col-lg-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon"><i class="fas fa-hands-helping"></i></div>
                    <div class="stats-number counter-value" data-target="<?= e(get_setting('stat_active_volunteers', '350+')) ?>">
                        <?= e(get_setting('stat_active_volunteers', '350+')) ?>
                    </div>
                    <div class="stats-label">Active Volunteers</div>
                </div>
            </div>

            <!-- Awareness Campaigns -->
            <div class="col-lg-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon"><i class="fas fa-bullhorn"></i></div>
                    <div class="stats-number counter-value" data-target="<?= e(get_setting('stat_awareness_campaigns', '85+')) ?>">
                        <?= e(get_setting('stat_awareness_campaigns', '85+')) ?>
                    </div>
                    <div class="stats-label">Awareness Campaigns</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================================
     SECTION 6: FEATURED PROJECTS
     ========================================================================= -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5">
            <div>
                <span class="section-badge"><?= __('sec_featured_projects') ?></span>
                <h2 class="section-title mb-0"><?= __('sec_featured_projects_sub') ?></h2>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="projects.php" class="btn btn-outline-primary">
                    <?= __('view_details') ?> <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($projects as $proj): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="sapsada-card">
                        <div class="card-img-wrap">
                            <span class="card-badge bg-teal"><i class="fas fa-tasks me-1"></i> <?= e($proj['status']) ?></span>
                            <img src="<?= ASSETS_URL ?>/../<?= e($proj['featured_image']) ?>" alt="<?= e($proj['localized_title']) ?>" loading="lazy">
                        </div>
                        <div class="card-body-sapsada">
                            <div class="d-flex align-items-center text-muted small mb-2 gap-2">
                                <i class="fas fa-map-marker-alt text-teal"></i> <?= e($proj['location']) ?>
                            </div>
                            <h5 class="fw-bold text-navy mb-3"><?= e($proj['localized_title']) ?></h5>
                            <div class="text-secondary small mb-4 flex-grow-1">
                                <?= substr(strip_tags($proj['localized_desc']), 0, 120) ?>...
                            </div>
                            <a href="project-detail.php?id=<?= $proj['id'] ?>" class="btn btn-sapsada-primary btn-sm w-100">
                                <?= __('view_details') ?> <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =========================================================================
     SECTION 7: UPCOMING EVENTS & 8: LATEST NEWS (DUAL GRID)
     ========================================================================= -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row g-5">
            <!-- Events Column -->
            <div class="col-lg-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="section-badge"><?= __('sec_upcoming_events') ?></span>
                        <h3 class="fw-bold text-navy mb-0">Community Calendar</h3>
                    </div>
                    <a href="events.php" class="btn btn-sm btn-link text-teal fw-bold"><?= __('all') ?> <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="d-flex flex-column gap-3">
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $ev): ?>
                            <div class="p-3 rounded-4 bg-light border border-1 d-flex gap-3 align-items-center transition-all hover-shadow">
                                <div class="bg-navy text-white text-center p-3 rounded-3" style="min-width: 80px;">
                                    <div class="fs-4 fw-bold lh-1"><?= date('d', strtotime($ev['event_date'])) ?></div>
                                    <div class="small text-uppercase text-cyan"><?= date('M', strtotime($ev['event_date'])) ?></div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold text-navy mb-1">
                                        <a href="event-detail.php?id=<?= $ev['id'] ?>" class="text-navy text-decoration-none">
                                            <?= e($ev['title']) ?>
                                        </a>
                                    </h6>
                                    <div class="small text-muted mb-1">
                                        <i class="fas fa-map-marker-alt text-teal me-1"></i> <?= e($ev['venue']) ?> | 
                                        <i class="fas fa-clock text-teal me-1"></i> <?= date('h:i A', strtotime($ev['start_time'])) ?>
                                    </div>
                                    <a href="event-detail.php?id=<?= $ev['id'] ?>" class="small fw-bold text-teal"><?= __('view_details') ?> &rarr;</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No upcoming events scheduled right now. Check back soon!</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Latest News Column -->
            <div class="col-lg-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="section-badge"><?= __('sec_latest_news') ?></span>
                        <h3 class="fw-bold text-navy mb-0">Latest Updates</h3>
                    </div>
                    <a href="news.php" class="btn btn-sm btn-link text-teal fw-bold"><?= __('all') ?> <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="d-flex flex-column gap-3">
                    <?php foreach ($news_list as $news): ?>
                        <div class="p-3 rounded-4 bg-light border border-1 d-flex gap-3 align-items-center transition-all hover-shadow">
                            <img src="<?= ASSETS_URL ?>/../<?= e($news['featured_image']) ?>" alt="<?= e($news['localized_title']) ?>" class="rounded-3" style="width: 90px; height: 80px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <span class="badge bg-sky-light text-teal mb-1"><?= e($news['category']) ?></span>
                                <h6 class="fw-bold text-navy mb-1">
                                    <a href="news-detail.php?id=<?= $news['id'] ?>" class="text-navy text-decoration-none">
                                        <?= e($news['localized_title']) ?>
                                    </a>
                                </h6>
                                <div class="small text-muted"><?= date('M d, Y', strtotime($news['published_at'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================================
     SECTION 9 & 10: CALL TO ACTION BANNERS (VOLUNTEER & DONATE)
     ========================================================================= -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row g-4">
            <!-- Volunteer CTA -->
            <div class="col-lg-6">
                <div class="cta-banner cta-volunteer h-100 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge bg-white text-navy px-3 py-2 rounded-pill fw-bold mb-3">Join Hands</span>
                        <h3 class="display-6 fw-bold text-white mb-3"><?= __('sec_volunteer_cta') ?></h3>
                        <p class="text-white-50 mb-4"><?= __('sec_volunteer_cta_sub') ?></p>
                    </div>
                    <div>
                        <a href="volunteers.php" class="btn btn-light text-navy fw-bold px-4 py-2">
                            <i class="fas fa-user-plus me-2 text-teal"></i> <?= __('sec_volunteer_btn') ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Donation CTA -->
            <div class="col-lg-6">
                <div class="cta-banner cta-donate h-100 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-bold mb-3">Transparent Support</span>
                        <h3 class="display-6 fw-bold text-white mb-3"><?= __('sec_donation_cta') ?></h3>
                        <p class="text-white-50 mb-4"><?= __('sec_donation_cta_sub') ?></p>
                    </div>
                    <div>
                        <a href="donate.php" class="btn btn-sapsada-gold px-4 py-2">
                            <i class="fas fa-heart me-2"></i> <?= __('sec_donation_btn') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================================
     SECTION 11: PHOTO GALLERY
     ========================================================================= -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5">
            <div>
                <span class="section-badge"><?= __('sec_photo_gallery') ?></span>
                <h2 class="section-title mb-0"><?= __('sec_photo_gallery_sub') ?></h2>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="gallery.php" class="btn btn-outline-primary">
                    <?= __('view_details') ?> <i class="fas fa-images ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-3">
            <?php foreach ($gallery_items as $photo): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="gallery-card" data-lightbox="gallery" data-src="<?= ASSETS_URL ?>/../<?= e($photo['image_path']) ?>" data-caption="<?= e($photo['caption'] ?: $photo['title']) ?>">
                        <img src="<?= ASSETS_URL ?>/../<?= e($photo['image_path']) ?>" alt="<?= e($photo['title']) ?>" loading="lazy">
                        <div class="gallery-overlay">
                            <span class="badge bg-gold text-dark align-self-start mb-2"><?= e($photo['album_title']) ?></span>
                            <h6 class="fw-bold mb-1 text-white"><?= e($photo['title']) ?></h6>
                            <small class="text-white-50"><?= e($photo['caption']) ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
