<?php
// 修改 models/Database.php
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct($config) {
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $this->pdo = new PDO($dsn, $config['username'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public static function getInstance() {
        if(self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    public function getPdo() {
        return $this->pdo;
    }

    public function transaction($callback) {
        try {
            $this->pdo->beginTransaction();
            $result = $callback($this->pdo);
            $this->pdo->commit();
            return $result;
        } catch(Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}