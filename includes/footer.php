<?php
/**
 * Global Footer Template
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */
$whatsapp_clean = preg_replace('/[^0-9]/', '', get_setting('whatsapp_number', ORG_WHATSAPP));
?>
    <!-- WhatsApp Floating Action Button -->
    <a href="https://wa.me/<?= $whatsapp_clean ?>?text=<?= urlencode('Hello SAP SADA Unit, I would like to inquire about your community services.') ?>" class="whatsapp-float" target="_blank" rel="noopener noreferrer" title="<?= __('whatsapp_chat_btn') ?>">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Footer -->
    <footer class="footer-sapsada">
        <div class="container">
            <div class="row g-4">
                <!-- Col 1: Organization Identity & Mission -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="<?= ASSETS_URL ?>/images/branding/logo.png" alt="Sap Sada Logo" class="footer-logo-img">
                        <div>
                            <h5 class="text-white fw-bold mb-0">SAP SADA</h5>
                            <span class="text-cyan small fw-semibold">Social Welfare &amp; Life Protection</span>
                        </div>
                    </div>
                    <p class="text-slate-300 small mb-3">
                        <?= __('footer_about') ?>
                    </p>
                    <div class="reg-badge mb-3">
                        <i class="fas fa-shield-alt text-warning"></i>
                        <span><?= __('reg_number_label') ?>: <?= e(get_setting('reg_number', ORG_REG_NO)) ?></span>
                    </div>
                    <!-- Social Links -->
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <a href="<?= e(get_setting('facebook_url', '#')) ?>" target="_blank" class="btn btn-outline-light btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="<?= e(get_setting('youtube_url', '#')) ?>" target="_blank" class="btn btn-outline-light btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://wa.me/<?= $whatsapp_clean ?>" target="_blank" class="btn btn-outline-light btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-title"><?= __('footer_quick_links') ?></h6>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-angle-right text-cyan"></i> <?= __('nav_home') ?></a></li>
                        <li><a href="about.php"><i class="fas fa-angle-right text-cyan"></i> <?= __('nav_about') ?></a></li>
                        <li><a href="activities.php"><i class="fas fa-angle-right text-cyan"></i> <?= __('nav_activities') ?></a></li>
                        <li><a href="projects.php"><i class="fas fa-angle-right text-cyan"></i> <?= __('nav_projects') ?></a></li>
                        <li><a href="volunteers.php"><i class="fas fa-angle-right text-cyan"></i> <?= __('nav_volunteer') ?></a></li>
                        <li><a href="donate.php"><i class="fas fa-angle-right text-cyan"></i> <?= __('nav_donate') ?></a></li>
                        <li><a href="gallery.php"><i class="fas fa-angle-right text-cyan"></i> <?= __('nav_gallery') ?></a></li>
                        <li><a href="contact.php"><i class="fas fa-angle-right text-cyan"></i> <?= __('nav_contact') ?></a></li>
                    </ul>
                </div>

                <!-- Col 3: Core Activities -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-title"><?= __('sec_core_activities') ?></h6>
                    <ul class="footer-links">
                        <li><a href="activities.php?cat=Life+Protection+Awareness"><i class="fas fa-life-ring text-cyan"></i> <?= __('act_life_protection') ?></a></li>
                        <li><a href="activities.php?cat=Environmental+Activities"><i class="fas fa-leaf text-cyan"></i> <?= __('act_env_protection') ?></a></li>
                        <li><a href="activities.php?cat=Community+Development"><i class="fas fa-users text-cyan"></i> <?= __('act_community_dev') ?></a></li>
                        <li><a href="activities.php?cat=Social+Welfare+Programs"><i class="fas fa-hand-holding-heart text-cyan"></i> <?= __('act_social_welfare') ?></a></li>
                        <li><a href="activities.php?cat=Emergency+Assistance"><i class="fas fa-first-aid text-cyan"></i> <?= __('act_emergency_assist') ?></a></li>
                        <li><a href="activities.php?cat=Educational+Assistance"><i class="fas fa-graduation-cap text-cyan"></i> <?= __('act_education_support') ?></a></li>
                    </ul>
                </div>

                <!-- Col 4: Official Headquarters & Contact -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-title"><?= __('footer_contact_info') ?></h6>
                    <div class="d-flex flex-column gap-3 text-slate-300 small">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-map-marker-alt text-cyan mt-1"></i>
                            <span><?= e(get_setting('address_physical', ORG_ADDRESS)) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-phone-alt text-cyan"></i>
                            <div>
                                <a href="tel:<?= preg_replace('/[^0-9+]/', '', get_setting('phone_primary', ORG_PHONE_1)) ?>" class="text-white fw-bold">
                                    <?= e(get_setting('phone_primary', ORG_PHONE_1)) ?>
                                </a> / 
                                <a href="tel:<?= preg_replace('/[^0-9+]/', '', get_setting('phone_secondary', ORG_PHONE_2)) ?>" class="text-white fw-bold">
                                    <?= e(get_setting('phone_secondary', ORG_PHONE_2)) ?>
                                </a>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-envelope text-cyan"></i>
                            <a href="mailto:<?= e(get_setting('email_official', ORG_EMAIL)) ?>" class="text-white">
                                <?= e(get_setting('email_official', ORG_EMAIL)) ?>
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fab fa-whatsapp text-success fs-5"></i>
                            <a href="https://wa.me/<?= $whatsapp_clean ?>" target="_blank" class="text-white">
                                +94 70 726 1978 (WhatsApp Support)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom Bar -->
            <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div>
                    &copy; <?= date('Y') ?> <strong><?= e(get_setting('site_name', ORG_NAME)) ?></strong>. <?= __('footer_rights') ?>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-cyan small fw-semibold"><?= e(get_setting('slogan', 'Together We Care. Together We Protect Life.')) ?></span>
                    <a href="admin/login.php" class="text-white-50 text-decoration-none small hover-white" title="Staff Portal">
                        <i class="fas fa-lock me-1"></i> Admin
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Main JS -->
    <script src="<?= ASSETS_URL ?>/js/main.js?v=1.0.1"></script>
</body>
</html>
