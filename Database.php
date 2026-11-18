<?php
/**
 * Database.php
 * Handles the MySQL connection using PDO.
 * Implemented using the Singleton design pattern (OOP concept)
 * so that only one connection instance is used throughout the app.
 */

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    // ---- Update these to match your XAMPP/WAMP MySQL setup ----
    private string $host = "localhost";
    private string $dbName = "jersey_system";
    private string $username = "root";
    private string $password = "";
    // -------------------------------------------------------------

    private function __construct()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    // Prevent cloning of the instance
    private function __clone() {}

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
