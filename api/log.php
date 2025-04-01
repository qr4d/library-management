<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../models/Auth.php';
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Error.php';
require_once __DIR__ . '/../models/Response.php';

$config = require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
        $config['username'], 
        $config['password']
    );

    $auth = Auth::getInstance($pdo);

    // 检查管理员权限
    if(!$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => '需要管理员权限']);
        exit;
    }

    // 获取操作日志列表
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query('
            SELECT l.*, u.username 
            FROM operation_log l
            LEFT JOIN user u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT 100
        ');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}