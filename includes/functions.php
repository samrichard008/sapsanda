<?php
/**
 * Core Helper Functions
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Global cache for website settings and translations
$GLOBALS['sapsada_settings_cache'] = null;
$GLOBALS['sapsada_lang_dictionary'] = [];

/**
 * Escapes HTML output (XSS protection)
 */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize text input
 */
function clean_input(?string $data): string {
    if ($data === null) return '';
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

/**
 * Translation helper function
 */
function __(string $key, ?string $lang = null): string {
    global $sapsada_lang_dictionary;
    $target_lang = $lang ?: CURRENT_LANG;
    
    // Load dictionary if not already loaded
    if (!isset($sapsada_lang_dictionary[$target_lang])) {
        $lang_file = INCLUDES_PATH . "/languages/{$target_lang}.php";
        if (file_exists($lang_file)) {
            $sapsada_lang_dictionary[$target_lang] = require $lang_file;
        } else {
            // Fallback to English
            $fallback_file = INCLUDES_PATH . "/languages/en.php";
            $sapsada_lang_dictionary[$target_lang] = file_exists($fallback_file) ? require $fallback_file : [];
        }
    }

    if (isset($sapsada_lang_dictionary[$target_lang][$key])) {
        return $sapsada_lang_dictionary[$target_lang][$key];
    }

    // Secondary fallback to English if missing in current language
    if ($target_lang !== 'en') {
        if (!isset($sapsada_lang_dictionary['en'])) {
            $en_file = INCLUDES_PATH . "/languages/en.php";
            $sapsada_lang_dictionary['en'] = file_exists($en_file) ? require $en_file : [];
        }
        if (isset($sapsada_lang_dictionary['en'][$key])) {
            return $sapsada_lang_dictionary['en'][$key];
        }
    }

    return $key;
}

/**
 * Fetch a website setting with in-memory caching
 */
function get_setting(string $key, string $default = ''): string {
    global $sapsada_settings_cache;
    
    if ($sapsada_settings_cache === null) {
        $sapsada_settings_cache = [];
        try {
            $db = get_db();
            $stmt = $db->query("SELECT setting_key, setting_value FROM website_settings");
            while ($row = $stmt->fetch()) {
                $sapsada_settings_cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            // Fallback if table doesn't exist yet
            return $default;
        }
    }

    // Language-aware fallback for settings like site_name, slogan, address
    if (CURRENT_LANG !== 'en') {
        $localized_key = "{$key}_" . CURRENT_LANG;
        if (!empty($sapsada_settings_cache[$localized_key])) {
            return $sapsada_settings_cache[$localized_key];
        }
    }

    return $sapsada_settings_cache[$key] ?? $default;
}

/**
 * Generate CSRF Token
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output hidden CSRF form input
 */
function csrf_field(): string {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Validate CSRF Token
 */
function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate unique volunteer reference number (e.g., SAP-VOL-2026-1042)
 */
function generate_volunteer_reference(): string {
    $year = date('Y');
    $rand = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    return "SAP-VOL-{$year}-{$rand}";
}

/**
 * Get list of all 25 Sri Lankan Districts
 */
function get_sri_lanka_districts(): array {
    return [
        'Colombo', 'Gampaha', 'Kalutara',
        'Kandy', 'Matale', 'Nuwara Eliya',
        'Galle', 'Matara', 'Hambantota',
        'Jaffna', 'Kilinochchi', 'Mannar', 'Vavuniya', 'Mullaitivu',
        'Batticaloa', 'Ampara', 'Trincomalee',
        'Kurunegala', 'Puttalam',
        'Anuradhapura', 'Polonnaruwa',
        'Badulla', 'Monaragala',
        'Ratnapura', 'Kegalle'
    ];
}

/**
 * Log administrative audit action
 */
function log_audit(string $action, string $module, ?string $details = null, ?int $admin_id = null): void {
    try {
        $db = get_db();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $admin = $admin_id ?: ($_SESSION['admin_id'] ?? null);
        
        $stmt = $db->prepare("INSERT INTO audit_logs (admin_id, action, module, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$admin, $action, $module, $details, $ip]);
    } catch (Exception $e) {
        error_log("Failed to log audit: " . $e->getMessage());
    }
}

/**
 * Create an admin notification
 */
function create_notification(string $type, string $title, string $message, ?string $link = null): void {
    try {
        $db = get_db();
        $stmt = $db->prepare("INSERT INTO notifications (type, title, message, link, is_read) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$type, $title, $message, $link]);
    } catch (Exception $e) {
        error_log("Failed to create notification: " . $e->getMessage());
    }
}

/**
 * Secure file upload handler
 * @return array ['success' => bool, 'path' => string, 'error' => string]
 */
function handle_file_upload(array $file, string $target_subfolder, array $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'], int $max_bytes = 5242880): array {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Invalid file parameter.'];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'error' => 'No file was uploaded.'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'error' => 'Exceeded file size limit.'];
        default:
            return ['success' => false, 'error' => 'Unknown upload error occurred.'];
    }

    if ($file['size'] > $max_bytes) {
        return ['success' => false, 'error' => 'File size exceeds 5MB limit.'];
    }

    // Verify MIME Type securely
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed_mimes)) {
        return ['success' => false, 'error' => 'Invalid file format. Only JPG, PNG, and WebP are allowed.'];
    }

    $ext_map = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];
    $ext = $ext_map[$mime] ?? 'jpg';

    $target_dir = UPLOADS_PATH . DIRECTORY_SEPARATOR . trim($target_subfolder, '/\\');
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $filename = sprintf('%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(6)), $ext);
    $destination = $target_dir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to move uploaded file.'];
    }

    // Relative path for database storage and web display
    $relative_path = 'uploads/' . trim($target_subfolder, '/') . '/' . $filename;
    return ['success' => true, 'path' => $relative_path, 'filename' => $filename];
}

/**
 * Format relative time (e.g. "2 hours ago")
 */
function time_ago(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return round($diff / 60) . ' mins ago';
    if ($diff < 86400) return round($diff / 3600) . ' hours ago';
    if ($diff < 604800) return round($diff / 86400) . ' days ago';
    return date('M d, Y', $time);
}
