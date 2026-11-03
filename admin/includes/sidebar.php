<?php
/**
 * Admin Sidebar Navigation
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <img src="<?= ASSETS_URL ?>/images/branding/logo.png" alt="SAP SADA Emblem" class="sidebar-logo">
        <div>
            <div class="fw-bold text-white small lh-1">SAP SADA</div>
            <div class="text-cyan small fw-semibold" style="font-size:0.7rem;">Admin Console</div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="sidebar-heading">Core</li>
        <li>
            <a href="<?= ADMIN_URL ?>/dashboard.php" class="<?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> <span>Dashboard</span>
            </a>
        </li>

        <li class="sidebar-heading">Content &amp; Media</li>
        <li>
            <a href="<?= ADMIN_URL ?>/banners.php" class="<?= $current_page === 'banners' ? 'active' : '' ?>">
                <i class="fas fa-images"></i> <span>Hero Banners</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/pages.php" class="<?= $current_page === 'pages' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i> <span>Pages &amp; About Us</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/translations.php" class="<?= $current_page === 'translations' ? 'active' : '' ?>">
                <i class="fas fa-language"></i> <span>Translations (EN/SI/TA)</span>
            </a>
        </li>

        <li class="sidebar-heading">Operations</li>
        <li>
            <a href="<?= ADMIN_URL ?>/activities.php" class="<?= $current_page === 'activities' ? 'active' : '' ?>">
                <i class="fas fa-life-ring"></i> <span>Activities</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/projects.php" class="<?= $current_page === 'projects' ? 'active' : '' ?>">
                <i class="fas fa-tasks"></i> <span>Projects</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/volunteers.php" class="<?= $current_page === 'volunteers' ? 'active' : '' ?>">
                <i class="fas fa-hands-helping"></i> <span>Volunteers</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/donations.php" class="<?= $current_page === 'donations' ? 'active' : '' ?>">
                <i class="fas fa-donate"></i> <span>Donations &amp; Slips</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/news.php" class="<?= $current_page === 'news' ? 'active' : '' ?>">
                <i class="fas fa-newspaper"></i> <span>News Articles</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/events.php" class="<?= $current_page === 'events' ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i> <span>Events</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/gallery.php" class="<?= $current_page === 'gallery' ? 'active' : '' ?>">
                <i class="fas fa-photo-video"></i> <span>Gallery &amp; Albums</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/leadership.php" class="<?= $current_page === 'leadership' ? 'active' : '' ?>">
                <i class="fas fa-user-tie"></i> <span>Leadership</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/messages.php" class="<?= $current_page === 'messages' ? 'active' : '' ?>">
                <i class="fas fa-envelope-open-text"></i> <span>Contact Messages</span>
            </a>
        </li>

        <li class="sidebar-heading">System &amp; Security</li>
        <li>
            <a href="<?= ADMIN_URL ?>/settings.php" class="<?= $current_page === 'settings' ? 'active' : '' ?>">
                <i class="fas fa-sliders-h"></i> <span>Website Settings</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/users.php" class="<?= $current_page === 'users' ? 'active' : '' ?>">
                <i class="fas fa-users-cog"></i> <span>Admin Users</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/audit-logs.php" class="<?= $current_page === 'audit-logs' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i> <span>Audit Logs</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/backup.php" class="<?= $current_page === 'backup' ? 'active' : '' ?>">
                <i class="fas fa-database"></i> <span>Database Backup</span>
            </a>
        </li>
    </ul>

    <div class="p-3 border-top border-white border-opacity-10">
        <a href="<?= ADMIN_URL ?>/logout.php" class="btn btn-outline-danger btn-sm w-100">
            <i class="fas fa-sign-out-alt me-1"></i> Sign Out
        </a>
    </div>
</aside>
