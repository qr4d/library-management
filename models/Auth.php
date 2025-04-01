<?php
class Auth {
    private static $instance = null;
    private $pdo;
    private $user = null;
    
    private function __construct($pdo) {
        $this->pdo = $pdo;
        $this->checkSession();
    }
    
    public static function getInstance($pdo) {
        if(self::$instance === null) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }
    
    private function checkSession() {
        if(isset($_SESSION['user_id'])) {
            $stmt = $this->pdo->prepare('SELECT * FROM user WHERE id = ? AND status = 1');
            $stmt->execute([$_SESSION['user_id']]);
            $this->user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    public function isLoggedIn() {
        return $this->user !== null;
    }
    
    public function isAdmin() {
        return $this->isLoggedIn() && $this->user['role'] >= 1;
    }
    
    public function isUserAdmin() {
        return $this->isLoggedIn() && $this->user['role'] == 2;
    }

    public function getCurrentUser() {
        return $this->user;
    }
    
    public function logOperation($action, $targetType, $targetId, $detail = '') {
        if(!$this->isLoggedIn()) return false;
        
        $stmt = $this->pdo->prepare('
            INSERT INTO operation_log (user_id, action, target_type, target_id, detail)
            VALUES (?, ?, ?, ?, ?)
        ');
        return $stmt->execute([
            $this->user['id'],
            $action,
            $targetType,
            $targetId,
            $detail
        ]);
    }

    public function requireAdmin() {
        if(!$this->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => '需要管理员权限']);
            exit;
        }
    }

    public function requireUserAdmin() {
        if(!$this->isUserAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => '需要用户管理员权限']);
            exit;
        }
    }

    public function requireLogin() {
        if(!$this->isLoggedIn()) {
            http_response_code(403);
            echo json_encode(['error' => '请先登录']);
            exit;
        }
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare('
            SELECT * FROM user 
            WHERE username = ? AND status = 1
        ');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $this->user = $user;
            $this->logOperation('LOGIN', 'user', $user['id'], '用户登录');
            return true;
        }
        return false;
    }

    public function logout() {
        if($this->isLoggedIn()) {
            $this->logOperation('LOGOUT', 'user', $this->user['id'], '用户退出');
        }
        $this->user = null;
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }
        session_destroy();
    }
}