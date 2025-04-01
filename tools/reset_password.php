<?php
// 用于重置管理员密码的工具脚本
$config = require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
        $config['username'], 
        $config['password']
    );
    
    $username = 'admin';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare('UPDATE user SET password = ? WHERE username = ?');
    $stmt->execute([$hash, $username]);
    
    echo "密码重置成功!\n";
    echo "用户名: $username\n";
    echo "密码: $password\n";
    echo "哈希值: $hash\n";
} catch(Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}