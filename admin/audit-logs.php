<?php
/**
 * Admin Security & System Audit Logs
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/includes/auth.php';
require_permission('manage_settings');

$current_page = 'audit-logs';
$page_title = 'System & Security Audit Logs';

$db = get_db();
$success_msg = '';
$error_msg = '';

// Handle Clean Old Logs (Older than 90 days)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clean_old_logs'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security token invalid. Action aborted.';
    } else {
        $clean_stmt = $db->query("DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
        $count = $clean_stmt->rowCount();
        log_audit('clean', 'audit-logs', "Purged {$count} audit log records older than 90 days");
        $success_msg = "Successfully purged {$count} audit log records older than 90 days.";
    }
}

// Filters & Pagination
$filter_module = trim($_GET['module'] ?? '');
$search_query = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['p'] ?? 1));
$per_page = 40;
$offset = ($page - 1) * $per_page;

$where_clauses = [];
$params = [];

if ($filter_module !== '') {
    $where_clauses[] = "l.module = :module";
    $params[':module'] = $filter_module;
}

if ($search_query !== '') {
    $where_clauses[] = "(l.details LIKE :search OR l.action LIKE :search OR a.username LIKE :search OR l.ip_address LIKE :search)";
    $params[':search'] = "%{$search_query}%";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Count total matching logs
$count_stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM audit_logs l 
    LEFT JOIN admins a ON l.admin_id = a.id 
    {$where_sql}
");
$count_stmt->execute($params);
$total_records = (int)$count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Fetch matching logs
$query_sql = "
    SELECT l.*, a.username, a.full_name 
    FROM audit_logs l 
    LEFT JOIN admins a ON l.admin_id = a.id 
    {$where_sql} 
    ORDER BY l.id DESC 
    LIMIT {$per_page} OFFSET {$offset}
";
$stmt = $db->prepare($query_sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Distinct modules for filter dropdown
$modules_stmt = $db->query("SELECT DISTINCT module FROM audit_logs WHERE module IS NOT NULL AND module != '' ORDER BY module ASC");
$all_modules = $modules_stmt->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-navy mb-1"><i class="fas fa-clipboard-list text-teal me-2"></i>Security &amp; Audit Logs</h1>
            <p class="text-muted small mb-0">Immutable tracking of administrative actions, authentication attempts, content updates, and system activities.</p>
        </div>
        <div class="col-auto">
            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to clean logs older than 90 days?');" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="clean_old_logs" value="1">
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-broom me-1"></i> Purge Logs &gt; 90 Days
                </button>
            </form>
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

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Search action, details, admin, IP..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="module" class="form-select form-select-sm">
                        <option value="">All Modules</option>
                        <?php foreach ($all_modules as $mod): ?>
                            <option value="<?= htmlspecialchars($mod) ?>" <?= $filter_module === $mod ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($mod)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-navy btn-sm me-1"><i class="fas fa-filter me-1"></i> Apply Filter</button>
                    <?php if ($search_query !== '' || $filter_module !== ''): ?>
                        <a href="<?= ADMIN_URL ?>/audit-logs.php" class="btn btn-outline-secondary btn-sm">Clear</a>
                    <?php endif; ?>
                </div>
                <div class="col-md-2 text-md-end">
                    <span class="badge bg-secondary"><?= number_format($total_records) ?> Total Logs</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 150px;">Timestamp</th>
                            <th style="width: 140px;">Admin</th>
                            <th style="width: 120px;">Action</th>
                            <th style="width: 130px;">Module</th>
                            <th>Details</th>
                            <th style="width: 130px;">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-history fa-2x mb-2 text-secondary opacity-50"></i>
                                    <p class="mb-0">No audit log records found.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="text-nowrap text-muted">
                                        <i class="far fa-clock me-1"></i> <?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($log['username'])): ?>
                                            <span class="fw-bold text-navy"><?= htmlspecialchars($log['username']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">System / Unknown</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $act = strtolower($log['action']);
                                        $badge_class = 'bg-secondary';
                                        if (in_array($act, ['login', 'success'])) $badge_class = 'bg-success';
                                        elseif (in_array($act, ['logout'])) $badge_class = 'bg-info text-dark';
                                        elseif (in_array($act, ['create', 'insert', 'add'])) $badge_class = 'bg-primary';
                                        elseif (in_array($act, ['update', 'edit'])) $badge_class = 'bg-warning text-dark';
                                        elseif (in_array($act, ['delete', 'destroy', 'purge', 'clean'])) $badge_class = 'bg-danger';
                                        elseif (strpos($act, 'failed') !== false) $badge_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= htmlspecialchars(strtoupper($log['action'])) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($log['module'] ?? 'general') ?></span>
                                    </td>
                                    <td>
                                        <span class="text-break"><?= htmlspecialchars($log['details'] ?? '') ?></span>
                                    </td>
                                    <td>
                                        <code class="small text-secondary"><?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?></code>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted">Showing page <?= $page ?> of <?= $total_pages ?></span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?p=<?= $page - 1 ?>&module=<?= urlencode($filter_module) ?>&q=<?= urlencode($search_query) ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?p=<?= $i ?>&module=<?= urlencode($filter_module) ?>&q=<?= urlencode($search_query) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?p=<?= $page + 1 ?>&module=<?= urlencode($filter_module) ?>&q=<?= urlencode($search_query) ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
