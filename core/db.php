<?php
/**
 * Database Connection & Query Helpers
 */

class DB {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

function db() {
    return DB::getInstance()->getConnection();
}

function query($sql, $params = []) {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function fetchOne($sql, $params = []) {
    return query($sql, $params)->fetch();
}

function fetchAll($sql, $params = []) {
    return query($sql, $params)->fetchAll();
}

function execute($sql, $params = []) {
    return query($sql, $params)->rowCount();
}

function lastInsertId() {
    return db()->lastInsertId();
}

function beginTransaction() {
    return db()->beginTransaction();
}

function commit() {
    return db()->commit();
}

function rollback() {
    return db()->rollBack();
}
