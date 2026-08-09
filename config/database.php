<?php
/**
 * CampusGuardian - Database Connection (PDO Singleton)
 */

class Database {
    private static $host = 'localhost';
    private static $db_name = 'campusguardian';
    private static $username = 'root';
    private static $password = '';
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                self::$conn = new PDO($dsn, self::$username, self::$password, $options);
            } catch (PDOException $e) {
                // If database doesn't exist yet during initial setup, handle gracefully or show clean message
                die("<div style='padding:20px; font-family:sans-serif; background:#f8d7da; color:#721c24; border-radius:8px; margin:20px;'>
                    <h2>Database Connection Error</h2>
                    <p>Unable to connect to MySQL database <strong>" . self::$db_name . "</strong>.</p>
                    <p><strong>Details:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                    <p>Please ensure XAMPP MySQL is running and you have imported <code>campusguardian.sql</code> file into phpMyAdmin.</p>
                </div>");
            }
        }
        return self::$conn;
    }
}
