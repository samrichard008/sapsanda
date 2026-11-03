<?php
/**
 * Navigation Bar Template
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */
?>
<nav class="navbar navbar-expand-xl navbar-sapsada">
    <div class="container">
        <!-- Logo & Brand Name -->
        <a class="navbar-brand d-flex align-items-center gap-3" href="index.php">
            <img src="<?= ASSETS_URL ?>/images/branding/logo.png" alt="Sap Sada Emblem" class="brand-logo-img">
            <div class="brand-text-container">
                <span class="brand-title">SAP SADA</span>
                <span class="brand-subtitle">Welfare &amp; Life Protection Unit</span>
            </div>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars fs-3 text-navy"></i>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-xl-0 align-items-xl-center">
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'home' ? 'active' : '' ?>" href="index.php"><?= __('nav_home') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'about' ? 'active' : '' ?>" href="about.php"><?= __('nav_about') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'activities' ? 'active' : '' ?>" href="activities.php"><?= __('nav_activities') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'projects' ? 'active' : '' ?>" href="projects.php"><?= __('nav_projects') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'volunteers' ? 'active' : '' ?>" href="volunteers.php"><?= __('nav_volunteer') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'donate' ? 'active' : '' ?>" href="donate.php"><?= __('nav_donate') ?></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($active_page, ['news', 'events']) ? 'active' : '' ?>" href="#" id="newsEventsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= __('nav_news') ?>
                    </a>
                    <ul class="dropdown-menu border-0 shadow-lg rounded-3" aria-labelledby="newsEventsDropdown">
                        <li><a class="dropdown-item py-2" href="news.php"><i class="fas fa-newspaper me-2 text-teal"></i>Latest News</a></li>
                        <li><a class="dropdown-item py-2" href="events.php"><i class="fas fa-calendar-alt me-2 text-primary"></i>Upcoming Events</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'gallery' ? 'active' : '' ?>" href="gallery.php"><?= __('nav_gallery') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'contact' ? 'active' : '' ?>" href="contact.php"><?= __('nav_contact') ?></a>
                </li>
            </ul>

            <!-- CTA Action Buttons -->
            <div class="d-flex align-items-center gap-2 ms-xl-3 mt-3 mt-xl-0">
                <a href="volunteers.php" class="btn btn-sapsada-outline btn-sm">
                    <i class="fas fa-hands-helping me-1"></i> <?= __('nav_volunteer_btn') ?>
                </a>
                <a href="donate.php" class="btn btn-sapsada-gold btn-sm">
                    <i class="fas fa-heart me-1"></i> <?= __('nav_donate_btn') ?>
                </a>
            </div>
        </div>
    </div>
</nav>
