<?php

if (class_exists('Database', false)) {
    return;
}

class Database {

    private static ?Database $instance = null;
    private mysqli $connection;

    private string $host     = 'localhost';
    private string $username = 'u970217706_cargo';
    private string $password = 'gmp_Cargo3';
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
                1045 => "Access denied — wrong username or password.",
                1049 => "Database does not exist.",
                2002 => "Cannot connect to MySQL server.",
            ];

            $friendly = $messages[$code] ?? 'DB Error [' . $code . ']: ' . $e->getMessage();
            error_log('Database connection failed: ' . $e->getMessage());

            die('<div style="font-family:monospace;background:#1e1e1e;color:#f48771;padding:2rem;margin:2rem;border-left:4px solid #f48771;">
                <strong>Database Connection Failed</strong><br><br>' . htmlspecialchars($friendly) . '
            </div>');
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