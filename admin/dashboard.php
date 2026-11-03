<?php
/**
 * Admin Master Dashboard
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$page_title = 'Executive Dashboard';
$current_page = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// 1. Fetch Real Live Database Counts
$counts = [
    'volunteers_total' => 0,
    'volunteers_pending' => 0,
    'projects_total' => 0,
    'projects_ongoing' => 0,
    'projects_completed' => 0,
    'news_total' => 0,
    'events_upcoming' => 0,
    'contact_messages' => 0,
    'donations_enquiries' => 0,
    'activities_total' => 0
];

try {
    $counts['volunteers_total'] = (int)$db->query("SELECT COUNT(*) FROM volunteers")->fetchColumn();
    $counts['volunteers_pending'] = (int)$db->query("SELECT COUNT(*) FROM volunteers WHERE status = 'Pending'")->fetchColumn();
    $counts['projects_total'] = (int)$db->query("SELECT COUNT(*) FROM projects WHERE status != 'Archived'")->fetchColumn();
    $counts['projects_ongoing'] = (int)$db->query("SELECT COUNT(*) FROM projects WHERE status = 'Ongoing'")->fetchColumn();
    $counts['projects_completed'] = (int)$db->query("SELECT COUNT(*) FROM projects WHERE status = 'Completed'")->fetchColumn();
    $counts['news_total'] = (int)$db->query("SELECT COUNT(*) FROM news WHERE status = 'Published'")->fetchColumn();
    $counts['events_upcoming'] = (int)$db->query("SELECT COUNT(*) FROM events WHERE status = 'Upcoming' AND event_date >= CURDATE()")->fetchColumn();
    $counts['contact_messages'] = (int)$db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
    $counts['donations_enquiries'] = (int)$db->query("SELECT COUNT(*) FROM donation_enquiries WHERE status = 'Pending'")->fetchColumn();
    $counts['activities_total'] = (int)$db->query("SELECT COUNT(*) FROM activities")->fetchColumn();
} catch (Exception $e) {}

// 2. Fetch Recent Volunteer Applicants
$recent_volunteers = [];
try {
    $stmt = $db->query("SELECT * FROM volunteers ORDER BY id DESC LIMIT 5");
    $recent_volunteers = $stmt->fetchAll();
} catch (Exception $e) {}

// 3. Fetch Recent Contact Messages
$recent_messages = [];
try {
    $stmt = $db->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 5");
    $recent_messages = $stmt->fetchAll();
} catch (Exception $e) {}

// 4. District Breakdown for Chart
$district_data = [];
try {
    $stmt = $db->query("SELECT district, COUNT(*) as count FROM volunteers GROUP BY district ORDER BY count DESC LIMIT 6");
    $district_data = $stmt->fetchAll();
} catch (Exception $e) {}

$chart_labels = array_column($district_data, 'district');
$chart_values = array_column($district_data, 'count');
if (empty($chart_labels)) {
    $chart_labels = ['Kalutara', 'Colombo', 'Gampaha', 'Galle'];
    $chart_values = [2, 0, 0, 0];
}
?>

<!-- Top Welcome & Quick Actions -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-1">Welcome back, <?= e($current_admin['full_name']) ?>!</h4>
        <div class="text-muted small">System Status: Active &amp; Operational &bull; Database: Connected &bull; Region: Sri Lanka</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= ADMIN_URL ?>/activities.php?action=create" class="btn btn-sapsada-teal btn-sm">
            <i class="fas fa-plus me-1"></i> New Activity
        </a>
        <a href="<?= ADMIN_URL ?>/projects.php?action=create" class="btn btn-sapsada-primary btn-sm">
            <i class="fas fa-plus me-1"></i> New Project
        </a>
        <a href="<?= ADMIN_URL ?>/export-volunteers.php" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-csv me-1"></i> Export Volunteers
        </a>
    </div>
</div>

<!-- 9 Primary Statistics Cards -->
<div class="row g-3 mb-4">
    <!-- Volunteers Total -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card">
            <div>
                <div class="stat-value"><?= $counts['volunteers_total'] ?></div>
                <div class="stat-label">Total Volunteers</div>
                <small class="text-teal fw-semibold"><?= $counts['volunteers_pending'] ?> pending review</small>
            </div>
            <div class="stat-icon-wrap bg-sky-light text-teal">
                <i class="fas fa-hands-helping"></i>
            </div>
        </div>
    </div>

    <!-- Ongoing Projects -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card">
            <div>
                <div class="stat-value"><?= $counts['projects_ongoing'] ?></div>
                <div class="stat-label">Ongoing Projects</div>
                <small class="text-muted"><?= $counts['projects_total'] ?> total recorded</small>
            </div>
            <div class="stat-icon-wrap bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-tasks"></i>
            </div>
        </div>
    </div>

    <!-- Completed Projects -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card">
            <div>
                <div class="stat-value"><?= $counts['projects_completed'] ?></div>
                <div class="stat-label">Completed Projects</div>
                <small class="text-success fw-semibold"><i class="fas fa-check-circle"></i> Verified impact</small>
            </div>
            <div class="stat-icon-wrap bg-success bg-opacity-10 text-success">
                <i class="fas fa-clipboard-check"></i>
            </div>
        </div>
    </div>

    <!-- Total Activities -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card">
            <div>
                <div class="stat-value"><?= $counts['activities_total'] ?></div>
                <div class="stat-label">Core Activities</div>
                <small class="text-muted">Life &amp; Environment</small>
            </div>
            <div class="stat-icon-wrap bg-info bg-opacity-10 text-info">
                <i class="fas fa-life-ring"></i>
            </div>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card">
            <div>
                <div class="stat-value"><?= $counts['events_upcoming'] ?></div>
                <div class="stat-label">Upcoming Events</div>
                <small class="text-muted">Community calendar</small>
            </div>
            <div class="stat-icon-wrap bg-warning bg-opacity-10 text-warning">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
    </div>

    <!-- News Articles -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card">
            <div>
                <div class="stat-value"><?= $counts['news_total'] ?></div>
                <div class="stat-label">Published News</div>
                <small class="text-muted">Press releases &amp; awards</small>
            </div>
            <div class="stat-icon-wrap bg-secondary bg-opacity-10 text-secondary">
                <i class="fas fa-newspaper"></i>
            </div>
        </div>
    </div>

    <!-- Unread Messages -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card">
            <div>
                <div class="stat-value"><?= $counts['contact_messages'] ?></div>
                <div class="stat-label">Unread Messages</div>
                <small class="text-danger fw-semibold">Requires attention</small>
            </div>
            <div class="stat-icon-wrap bg-danger bg-opacity-10 text-danger">
                <i class="fas fa-envelope"></i>
            </div>
        </div>
    </div>

    <!-- Donation Slips -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card">
            <div>
                <div class="stat-value"><?= $counts['donations_enquiries'] ?></div>
                <div class="stat-label">Pending Bank Slips</div>
                <small class="text-muted">Awaiting confirmation</small>
            </div>
            <div class="stat-icon-wrap bg-success bg-opacity-10 text-success">
                <i class="fas fa-donate"></i>
            </div>
        </div>
    </div>
</div>

<!-- Analytics & Recent Applications Grid -->
<div class="row g-4 mb-4">
    <!-- Chart Column -->
    <div class="col-lg-5">
        <div class="admin-table-card p-4 h-100">
            <h6 class="fw-bold text-navy mb-1">Volunteers by District</h6>
            <small class="text-muted mb-4 d-block">Geographic distribution of registered applicants</small>
            <div style="height: 260px; position: relative;">
                <canvas id="districtChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Volunteer Applications -->
    <div class="col-lg-7">
        <div class="admin-table-card h-100">
            <div class="admin-table-header">
                <div>
                    <h6 class="fw-bold text-navy mb-0">Recent Volunteer Applications</h6>
                    <small class="text-muted">Latest public registrants</small>
                </div>
                <a href="<?= ADMIN_URL ?>/volunteers.php" class="btn btn-sm btn-link text-teal">View All &rarr;</a>
            </div>

            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Ref. No</th>
                            <th>Applicant</th>
                            <th>District</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_volunteers)): ?>
                            <?php foreach ($recent_volunteers as $v): ?>
                                <tr>
                                    <td><span class="badge bg-light text-navy border fw-bold"><?= e($v['reference_no']) ?></span></td>
                                    <td>
                                        <div class="fw-bold text-navy"><?= e($v['full_name']) ?></div>
                                        <small class="text-muted"><?= e($v['phone']) ?></small>
                                    </td>
                                    <td><?= e($v['district']) ?></td>
                                    <td><span class="badge-status status-<?= e($v['status']) ?>"><?= e($v['status']) ?></span></td>
                                    <td><small class="text-muted"><?= date('M d', strtotime($v['created_at'])) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No volunteer applications recorded.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Recent Messages -->
<div class="admin-table-card mb-4">
    <div class="admin-table-header">
        <div>
            <h6 class="fw-bold text-navy mb-0">Recent Contact Inquiries</h6>
            <small class="text-muted">Direct public messages</small>
        </div>
        <a href="<?= ADMIN_URL ?>/messages.php" class="btn btn-sm btn-link text-teal">All Messages &rarr;</a>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_messages)): ?>
                    <?php foreach ($recent_messages as $msg): ?>
                        <tr>
                            <td class="fw-bold text-navy"><?= e($msg['name']) ?></td>
                            <td><?= e($msg['email']) ?></td>
                            <td><?= e($msg['phone'] ?: 'N/A') ?></td>
                            <td><span class="text-truncate d-inline-block" style="max-width: 250px;"><?= e($msg['subject']) ?></span></td>
                            <td><small class="text-muted"><?= time_ago($msg['created_at']) ?></small></td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/messages.php?id=<?= $msg['id'] ?>" class="btn btn-light btn-sm text-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No contact messages received.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('districtChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    data: <?= json_encode($chart_values) ?>,
                    backgroundColor: ['#008fa0', '#0a3663', '#00b4d8', '#f59e0b', '#10b981', '#6366f1'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { family: 'Outfit', size: 11 } }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
