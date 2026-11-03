<?php
/**
 * Admin Database Backup & Disaster Recovery
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/includes/auth.php';
require_permission('manage_settings');

$db = get_db();
$current_page = 'backup';
$page_title = 'Database Backup & Recovery';

// Handle Instant Download Backup
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    if (!verify_csrf_token($_GET['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }

    // Set unlimited execution time for database dump
    set_time_limit(300);

    $tables = [];
    $stmt = $db->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $timestamp = date('Y-m-d_H-i-s');
    $filename = "sapsada_backup_{$timestamp}.sql";

    // Set download headers
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "-- ==========================================================\n";
    echo "-- SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT\n";
    echo "-- Complete Database Backup & Disaster Recovery Dump\n";
    echo "-- Organization Registration: G A 00282904\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Database: sapsada_db\n";
    echo "-- ==========================================================\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n";
    echo "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    echo "SET time_zone = '+00:00';\n";
    echo "SET NAMES utf8mb4;\n\n";

    foreach ($tables as $table) {
        // Table Structure
        echo "-- --------------------------------------------------------\n";
        echo "-- Table structure for table `{$table}`\n";
        echo "-- --------------------------------------------------------\n";
        echo "DROP TABLE IF EXISTS `{$table}`;\n";

        $create_stmt = $db->query("SHOW CREATE TABLE `{$table}`");
        $create_row = $create_stmt->fetch(PDO::FETCH_NUM);
        echo $create_row[1] . ";\n\n";

        // Table Data
        $data_stmt = $db->query("SELECT * FROM `{$table}`");
        $rows = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($rows)) {
            echo "-- Dumping data for table `{$table}`\n";
            $columns = array_keys($rows[0]);
            $col_names = array_map(function($c) { return "`{$c}`"; }, $columns);
            $col_list = implode(', ', $col_names);

            foreach ($rows as $row) {
                $values = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = $db->quote($val);
                    }
                }
                echo "INSERT INTO `{$table}` ({$col_list}) VALUES (" . implode(', ', $values) . ");\n";
            }
            echo "\n";
        }
    }

    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    echo "-- Dump completed on " . date('Y-m-d H:i:s') . "\n";

    log_audit('export', 'backup', "Generated complete database SQL backup ({$filename})");
    exit;
}

// Fetch database metrics
$tables_stmt = $db->query("SHOW TABLE STATUS");
$tables_info = $tables_stmt->fetchAll(PDO::FETCH_ASSOC);

$total_tables = count($tables_info);
$total_rows = 0;
$total_size_bytes = 0;

foreach ($tables_info as $tbl) {
    $total_rows += (int)($tbl['Rows'] ?? 0);
    $total_size_bytes += (int)($tbl['Data_length'] ?? 0) + (int)($tbl['Index_length'] ?? 0);
}
$total_size_mb = round($total_size_bytes / (1024 * 1024), 2);

// Fetch MySQL Version
$version_stmt = $db->query("SELECT VERSION()");
$db_version = $version_stmt->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-navy mb-1"><i class="fas fa-database text-teal me-2"></i>Database Backup &amp; Recovery</h1>
            <p class="text-muted small mb-0">Create instant SQL database backups, verify database integrity, and review disaster recovery guides.</p>
        </div>
        <div class="col-auto">
            <a href="<?= ADMIN_URL ?>/backup.php?action=download&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-gold btn-lg fw-bold shadow-sm">
                <i class="fas fa-download me-2"></i> Download Database Backup (.sql)
            </a>
        </div>
    </div>

    <!-- DB Metrics Overview -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-4 border-primary">
                <div class="d-flex align-items-center">
                    <div class="avatar-lg bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                        <i class="fas fa-table fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Tables</div>
                        <h4 class="fw-bold mb-0 text-navy"><?= $total_tables ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-4 border-info">
                <div class="d-flex align-items-center">
                    <div class="avatar-lg bg-info bg-opacity-10 text-info rounded p-3 me-3">
                        <i class="fas fa-list-ol fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Estimated Records</div>
                        <h4 class="fw-bold mb-0 text-navy"><?= number_format($total_rows) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center">
                    <div class="avatar-lg bg-success bg-opacity-10 text-success rounded p-3 me-3">
                        <i class="fas fa-hdd fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total DB Size</div>
                        <h4 class="fw-bold mb-0 text-navy"><?= $total_size_mb ?> MB</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-4 border-warning">
                <div class="d-flex align-items-center">
                    <div class="avatar-lg bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                        <i class="fas fa-server fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Database Engine</div>
                        <h6 class="fw-bold mb-0 text-navy text-truncate" title="<?= htmlspecialchars($db_version) ?>"><?= htmlspecialchars(explode('-', $db_version)[0]) ?></h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Details & Guides -->
    <div class="row g-4">
        <!-- Tables List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-navy"><i class="fas fa-layer-group text-teal me-2"></i>Database Schema Tables</h6>
                    <span class="badge bg-navy"><?= $total_tables ?> Tables</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Table Name</th>
                                    <th>Engine</th>
                                    <th>Collation</th>
                                    <th class="text-end">Rows</th>
                                    <th class="text-end">Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tables_info as $t): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-table text-teal me-2"></i>
                                            <span class="fw-bold text-navy"><?= htmlspecialchars($t['Name']) ?></span>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($t['Engine']) ?></span></td>
                                        <td><span class="small text-muted"><?= htmlspecialchars($t['Collation']) ?></span></td>
                                        <td class="text-end fw-semibold"><?= number_format($t['Rows']) ?></td>
                                        <td class="text-end text-muted font-monospace"><?= round(($t['Data_length'] + $t['Index_length']) / 1024, 1) ?> KB</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions & Recovery Guides -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-navy text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-shield-alt text-cyan me-2"></i>Automated Backup Notice</h6>
                </div>
                <div class="card-body p-3">
                    <p class="small text-muted mb-3">
                        The one-click backup button generates a clean, standalone, standard SQL dump with all 25 tables, schema definitions, constraints, and current data encoded in UTF-8.
                    </p>
                    <a href="<?= ADMIN_URL ?>/backup.php?action=download&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-teal w-100 fw-bold btn-sm mb-2">
                        <i class="fas fa-file-download me-1"></i> Generate SQL Backup Now
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-navy"><i class="fas fa-terminal text-teal me-2"></i>Manual Backup Commands</h6>
                </div>
                <div class="card-body p-3">
                    <h6 class="fw-bold small text-navy mb-1">Local XAMPP / Command Line:</h6>
                    <pre class="bg-dark text-cyan p-2 rounded small mb-3"><code>mysqldump -u root sapsada_db &gt; backup.sql</code></pre>

                    <h6 class="fw-bold small text-navy mb-1">cPanel / Linux Server Restore:</h6>
                    <pre class="bg-dark text-cyan p-2 rounded small mb-3"><code>mysql -u [db_user] -p [db_name] &lt; backup.sql</code></pre>

                    <h6 class="fw-bold small text-navy mb-1">phpMyAdmin Web Export:</h6>
                    <ol class="small text-muted ps-3 mb-0">
                        <li>Open <code>http://localhost/phpmyadmin</code></li>
                        <li>Select <code>sapsada_db</code> from the left sidebar</li>
                        <li>Click <strong>Export</strong> tab &gt; Select <strong>Quick</strong></li>
                        <li>Click <strong>Go</strong> to save the <code>.sql</code> file</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
