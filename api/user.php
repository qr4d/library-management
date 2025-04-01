<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../models/Auth.php';

$config = require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
        $config['username'], 
        $config['password']
    );

    $auth = Auth::getInstance($pdo);

    // 所有用户管理操作都需要用户管理员权限
    if($_SERVER['REQUEST_METHOD'] !== 'GET') {
        if(!$auth->isUserAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => '需要用户管理员权限']);
            exit;
        }
    }

    // 获取用户列表
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        if(!$auth->isUserAdmin()) {
            http_response_code(403);
            exit;
        }
        
        $stmt = $pdo->query('SELECT id, username, role, status, created_at FROM user');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // 添加新用户
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if(empty($data['username']) || empty($data['password'])) {
            throw new Exception('用户名和密码不能为空');
        }
        
        $stmt = $pdo->prepare('INSERT INTO user (username, password, role) VALUES (?, ?, ?)');
        $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 0
        ]);
        
        echo json_encode(['success' => true]);
        exit;
    }

    // 修改用户信息
    if($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if(empty($data['id'])) {
            throw new Exception('缺少用户ID');
        }
        
        $updates = [];
        $params = [];
        
        if(isset($data['password'])) {
            $updates[] = 'password = ?';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if(isset($data['role'])) {
            $updates[] = 'role = ?';
            $params[] = $data['role'];
        }
        
        if(isset($data['status'])) {
            $updates[] = 'status = ?';
            $params[] = $data['status'];
        }
        
        if(!empty($updates)) {
            $params[] = $data['id'];
            $stmt = $pdo->prepare('UPDATE user SET ' . implode(', ', $updates) . ' WHERE id = ?');
            $stmt->execute($params);
        }
        
        echo json_encode(['success' => true]);
        exit;
    }

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}