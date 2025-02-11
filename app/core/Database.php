<?php
namespace Core;

use PDO;
use PDOException;
use config\Config;

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $host = Config::get('DB_HOST');
            $dbname = Config::get('DB_NAME');
            $user = Config::get('DB_USER');
            $pass = Config::get('DB_PASS');
            $port = Config::get('DB_PORT');

            if (!$host || !$dbname || !$user) {
                throw new PDOException("Database configuration is missing. Please check your .env file.");
            }

            $dsn = sprintf("pgsql:host=%s;port=%d;dbname=%s", $host, $port,$dbname);

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, $user, $pass, $options);

        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new PDOException("Unable to connect to the database.");
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}