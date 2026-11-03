<?php
/**
 * Admin Index Redirect
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/../config/config.php';

header("Location: " . ADMIN_URL . "/dashboard.php");
exit;
