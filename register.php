<?php
session_start();
require_once 'models/Auth.php';
$config = require_once 'config/database.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    try {
        if(empty($username) || empty($password)) {
            throw new Exception('用户名和密码不能为空');
        }
        
        if($password !== $confirmPassword) {
            throw new Exception('两次输入的密码不一致');
        }
        
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
            $config['username'], 
            $config['password']
        );
        
        // 检查用户名是否已存在
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM user WHERE username = ?');
        $stmt->execute([$username]);
        if($stmt->fetchColumn() > 0) {
            throw new Exception('用户名已被使用');
        }
        
        // 创建新用户
        $stmt = $pdo->prepare('INSERT INTO user (username, password, role, status) VALUES (?, ?, 0, 1)');
        $stmt->execute([
            $username,
            password_hash($password, PASSWORD_DEFAULT)
        ]);
        
        $success = '注册成功，请登录';
        
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - 家庭图书管理系统</title>
    <link href="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-M/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title text-center mb-4">用户注册</h4>
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if($success): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                                <br>
                                <a href="login.php" class="alert-link">点击这里登录</a>
                            </div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">用户名</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">密码</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">确认密码</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">注册</button>
                            <div class="text-center">
                                已有账号？<a href="login.php">立即登录</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>