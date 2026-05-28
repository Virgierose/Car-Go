<?php

// Guard against double-inclusion (e.g. if any file still requires this directly)
if (class_exists('Database', false)) {
    return;
}

class Database {

    private static ?Database $instance = null;
    private mysqli $connection;

    private string $host     = 'localhost';
    private string $username = 'u970217706_cargo';
    private string $password = 'a4b3c2d1_GMP';
    private string $database = 'u970217706_cargo';
    private int    $port     = 3306;

    private function __construct() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->connection = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database,
                $this->port
            );
            $this->connection->set_charset('utf8mb4');

        } catch (mysqli_sql_exception $e) {
            $code = $e->getCode();

            $messages = [
                1045 => "Access denied — wrong username or password for MySQL root user.",
                1049 => "Database 'cargo_db' does not exist — please import cargo_db.sql first.",
                2002 => "Cannot connect to MySQL — make sure MySQL is running in XAMPP Control Panel.",
            ];

            $friendly = $messages[$code] ?? 'DB Error [' . $code . ']: ' . $e->getMessage();
            error_log('Database connection failed: ' . $e->getMessage());

            die('
                <div style="font-family:monospace;background:#1e1e1e;color:#f48771;
                            padding:2rem;margin:2rem;border-left:4px solid #f48771;">
                    <strong>Database Connection Failed</strong><br><br>
                    ' . htmlspecialchars($friendly) . '<br><br>
                    <span style="color:#9cdcfe;">Checklist:</span><br>
                    &nbsp;1. XAMPP → MySQL is <strong>Running</strong> (green)<br>
                    &nbsp;2. Database name is exactly <strong>cargo_db</strong> in phpMyAdmin<br>
                    &nbsp;3. Username is <strong>root</strong>, password is <strong>empty</strong> (XAMPP default)<br>
                    &nbsp;4. You imported <strong>cargo_db.sql</strong> via phpMyAdmin → Import
                </div>
            ');
        }
    }

    public static function getInstance(): mysqli {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }

    private function __clone() {}
}