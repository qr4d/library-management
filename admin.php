<?php
session_start();
require_once 'models/Auth.php';
$config = require_once 'config/database.php';

$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
    $config['username'], 
    $config['password']
);

$auth = Auth::getInstance($pdo);

// 基础管理功能需要普通管理员权限
if(!$auth->isAdmin()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统管理</title>
    <link href="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-M/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">系统管理</a>
            <div class="navbar-nav">
                <?php if($auth->isUserAdmin()): ?>
                <a class="nav-link" href="#" data-page="users">用户管理</a>
                <?php endif; ?>
                <a class="nav-link" href="#" data-page="logs">操作日志</a>
                <a class="nav-link" href="index.php">返回首页</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div id="page-content"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://lf9-cdn-tos.bytecdntp.com/cdn/expire-1-M/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script src="js/components/modal.js?v=<?php echo time(); ?>"></script>
    <script src="js/components/toast.js?v=<?php echo time(); ?>"></script>
    <script src="js/admin.js?v=<?php echo time(); ?>"></script>
</body>
</html>