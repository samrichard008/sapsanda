<?php
/**
 * About Us Page
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$active_page = 'about';
$custom_page_title = 'About Us | Overview, Leadership & Mission';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// 1. Fetch CMS Page Content for 'about-us'
$about_content = '';
try {
    $stmt = $db->prepare("
        SELECT pt.content 
        FROM pages p
        JOIN page_translations pt ON p.id = pt.page_id
        WHERE p.slug = 'about-us' AND pt.lang = ?
        LIMIT 1
    ");
    $stmt->execute([CURRENT_LANG]);
    $res = $stmt->fetch();
    $about_content = $res ? $res['content'] : '';
} catch (Exception $e) {}

// 2. Fetch Leadership Members
$leaders = [];
try {
    $stmt = $db->query("SELECT * FROM leadership WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    $leaders = $stmt->fetchAll();
} catch (Exception $e) {}

// 3. Fetch Testimonials
$testimonials = [];
try {
    $stmt = $db->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id DESC LIMIT 3");
    $testimonials = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<!-- Page Header Banner -->
<div class="bg-navy py-5 text-white position-relative" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-4">
        <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-bold mb-2">
            <i class="fas fa-shield-alt me-1"></i> Registration No: <?= e(get_setting('reg_number', ORG_REG_NO)) ?>
        </span>
        <h1 class="display-5 fw-bold text-white mb-2"><?= __('about_title') ?></h1>
        <p class="lead text-sky-light mb-0"><?= __('about_subtitle') ?></p>
    </div>
</div>

<!-- Section 1: Overview & Content -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-7">
                <span class="section-badge"><?= __('sec_who_we_are') ?></span>
                <h2 class="section-title mb-4"><?= e(get_setting('site_name', ORG_NAME)) ?></h2>
                <div class="cms-rendered-content text-secondary lh-lg">
                    <?php if (!empty($about_content)): ?>
                        <?= $about_content ?>
                    <?php else: ?>
                        <p>SAP SADA SOCIAL WELFARE &amp; LIFE PROTECTION UNIT (Registration: G A 00282904) is a non-governmental organization in Sri Lanka committed to the preservation of human life, proactive aquatic safety, and community resilience.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-card rounded-4 p-4 bg-light border-top border-5 border-primary">
                    <h5 class="fw-bold text-navy mb-3">
                        <i class="fas fa-certificate text-gold me-2"></i> <?= __('reg_info_title') ?>
                    </h5>
                    <ul class="list-unstyled d-flex flex-column gap-3 text-secondary small mb-4">
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <strong>Official Name:</strong> 
                            <span class="text-end text-navy fw-semibold">SAP SADA Social Welfare &amp; Life Protection Unit</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <strong>Registration No:</strong> 
                            <span class="badge bg-primary text-white fs-6">G A 00282904</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <strong>Country:</strong> 
                            <span>Sri Lanka</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <strong>Headquarters:</strong> 
                            <span class="text-end">Jayabodhi Viharaya, Nalluruwa, Panadura</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <strong>Hotline:</strong> 
                            <span>0777 26 19 78 / 070 7261978</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <strong>Official Email:</strong> 
                            <span>sapsadaunit@gmail.com</span>
                        </li>
                    </ul>
                    <a href="contact.php" class="btn btn-sapsada-primary w-100 btn-sm">
                        <i class="fas fa-envelope me-1"></i> Contact Headquarters
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Mission, Vision & Core Values -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="p-4 bg-white rounded-4 shadow-subtle border-start border-4 border-primary h-100">
                    <h4 class="fw-bold text-navy mb-2"><i class="fas fa-compass text-primary me-2"></i><?= __('mission_title') ?></h4>
                    <p class="text-secondary mb-0">
                        To foster a caring, resilient, and empowered society by delivering dedicated life protection services, emergency support, environmental preservation, and social welfare programs rooted in transparency and community cooperation.
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-4 bg-white rounded-4 shadow-subtle border-start border-4 border-teal h-100">
                    <h4 class="fw-bold text-navy mb-2"><i class="fas fa-eye text-teal me-2"></i><?= __('vision_title') ?></h4>
                    <p class="text-secondary mb-0">
                        A thriving Sri Lanka where every human life is cherished and safeguarded, natural aquatic ecosystems are preserved, and every community thrives with dignity, solidarity, and mutual responsibility.
                    </p>
                </div>
            </div>
        </div>

        <!-- Core Values Grid -->
        <div class="text-center mb-4">
            <h3 class="fw-bold text-navy"><?= __('core_values_title') ?></h3>
            <p class="text-muted"><?= __('core_values_desc') ?></p>
        </div>
        <div class="row g-4">
            <div class="col-md-4 col-sm-6">
                <div class="p-4 bg-white rounded-4 shadow-subtle text-center h-100">
                    <div class="text-teal fs-2 mb-2"><i class="fas fa-heart"></i></div>
                    <h5 class="fw-bold text-navy">Humanity &amp; Care</h5>
                    <p class="small text-secondary mb-0">Placing compassion at the heart of our service to vulnerable families, children, and elders.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="p-4 bg-white rounded-4 shadow-subtle text-center h-100">
                    <div class="text-primary fs-2 mb-2"><i class="fas fa-life-ring"></i></div>
                    <h5 class="fw-bold text-navy">Life Protection</h5>
                    <p class="small text-secondary mb-0">Active dedication to drowning prevention, rescue readiness, and crisis intervention.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="p-4 bg-white rounded-4 shadow-subtle text-center h-100">
                    <div class="text-teal fs-2 mb-2"><i class="fas fa-balance-scale"></i></div>
                    <h5 class="fw-bold text-navy">Transparency &amp; Trust</h5>
                    <p class="small text-secondary mb-0">Uncompromising accountability in fund management, volunteer safety, and public communications.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="p-4 bg-white rounded-4 shadow-subtle text-center h-100">
                    <div class="text-gold fs-2 mb-2"><i class="fas fa-seedling"></i></div>
                    <h5 class="fw-bold text-navy">Environmental Defense</h5>
                    <p class="small text-secondary mb-0">Restoring drainage canals, defending wetland ecosystems, and advancing plastic-free stewardship.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="p-4 bg-white rounded-4 shadow-subtle text-center h-100">
                    <div class="text-cyan fs-2 mb-2"><i class="fas fa-users"></i></div>
                    <h5 class="fw-bold text-navy">Community Participation</h5>
                    <p class="small text-secondary mb-0">Building grassroots leadership and volunteer involvement for lasting sustainable change.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="p-4 bg-white rounded-4 shadow-subtle text-center h-100">
                    <div class="text-navy fs-2 mb-2"><i class="fas fa-award"></i></div>
                    <h5 class="fw-bold text-navy">Excellence &amp; Integrity</h5>
                    <p class="small text-secondary mb-0">Maintaining ethical standards and rigorous execution across all welfare initiatives.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 3: Leadership & Management -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge"><?= __('leadership_title') ?></span>
            <h2 class="section-title"><?= __('leadership_sub') ?></h2>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($leaders as $leader): ?>
                <div class="col-lg-5 col-md-6">
                    <div class="sapsada-card p-4 d-flex flex-column flex-sm-row align-items-center gap-4">
                        <img src="<?= ASSETS_URL ?>/../<?= e($leader['photo']) ?>" alt="<?= e($leader['name']) ?>" class="rounded-4 shadow-subtle" style="width: 140px; height: 160px; object-fit: cover;">
                        <div class="text-center text-sm-start">
                            <span class="badge bg-sky-light text-teal mb-1"><?= e($leader['position']) ?></span>
                            <h5 class="fw-bold text-navy mb-1"><?= e($leader['name']) ?></h5>
                            <p class="small text-secondary mb-3"><?= e($leader['bio']) ?></p>
                            <div class="d-flex align-items-center justify-content-center justify-content-sm-start gap-2">
                                <?php if (!empty($leader['phone'])): ?>
                                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $leader['phone']) ?>" class="btn btn-outline-secondary btn-sm" title="Call">
                                        <i class="fas fa-phone-alt"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($leader['email'])): ?>
                                    <a href="mailto:<?= e($leader['email']) ?>" class="btn btn-outline-secondary btn-sm" title="Email">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Section 4: Organizational Structure Diagram -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge"><?= __('org_structure_title') ?></span>
            <h2 class="section-title">Functional Operating Framework</h2>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="p-4 p-md-5 bg-white rounded-4 shadow-card">
                    <!-- Framework Hierarchy -->
                    <div class="text-center mb-4">
                        <div class="d-inline-block bg-navy text-white px-4 py-2 rounded-3 fw-bold shadow-sm">
                            <i class="fas fa-sitemap me-2"></i> Executive Governing Council &amp; Trustees
                        </div>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <div style="width: 2px; height: 30px; background: var(--color-teal);"></div>
                    </div>
                    <div class="row g-3 text-center">
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded-3 border border-1 h-100">
                                <i class="fas fa-life-ring text-teal fs-4 mb-2"></i>
                                <h6 class="fw-bold mb-1">Life Protection Division</h6>
                                <small class="text-muted">Water rescue drills, patrol &amp; safety stations</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded-3 border border-1 h-100">
                                <i class="fas fa-hands-helping text-primary fs-4 mb-2"></i>
                                <h6 class="fw-bold mb-1">Social Welfare Division</h6>
                                <small class="text-muted">Family aid, children welfare, elderly support</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded-3 border border-1 h-100">
                                <i class="fas fa-leaf text-success fs-4 mb-2"></i>
                                <h6 class="fw-bold mb-1">Environmental Division</h6>
                                <small class="text-muted">Canal cleaning, dredging &amp; plastic-free drives</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded-3 border border-1 h-100">
                                <i class="fas fa-users-cog text-warning fs-4 mb-2"></i>
                                <h6 class="fw-bold mb-1">Volunteer &amp; Community Corps</h6>
                                <small class="text-muted">Field volunteers, logistics &amp; emergency relief</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
