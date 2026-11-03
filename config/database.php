<?php
/**
 * Database Connection Manager (PDO)
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Log and show clean message in production, or detail in development
                error_log("Database Connection Error: " . $e->getMessage());
                if (defined('APP_ENV') && APP_ENV === 'development') {
                    die("Database Connection Failed: " . htmlspecialchars($e->getMessage()));
                } else {
                    die("A database connection error occurred. Please contact the administrator.");
                }
            }
        }

        return self::$instance;
    }
}

/**
 * Global helper function to get PDO database handle
 */
function get_db(): PDO {
    return Database::getInstance();
}
