<?php
// 用于重置管理员密码的工具脚本

// 安全检查
if (!getenv('ALLOW_PASSWORD_RESET')) {
    die("错误: 未启用密码重置功能。请设置环境变量 ALLOW_PASSWORD_RESET=true 来启用。\n");
}

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
    
    // 重置完成后禁用功能
    putenv('ALLOW_PASSWORD_RESET=false');
} catch(Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}