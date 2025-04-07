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
$currentUser = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>个人图书管理</title>
    <link href="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-M/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">个人图书管理</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-page="search">全网搜索</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-page="all-books">我的图书</a>
                    </li>
                    <?php if($auth->isAdmin()): ?> <!-- 改为 isAdmin -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="quick-add">快速录入</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="shelf">书架管理</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php" onclick="window.location.href='admin.php'; return false;">系统管理</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if($auth->isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($currentUser['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="logout.php">退出登录</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php" onclick="window.location.href='login.php'; return false;">登录</a>
                    </li>
                <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div id="searchResults" class="row"></div>
        <div id="page-content"></div>
    </div>
    <footer class="footer">
        <div class="container">
            <p>
                Copyright &copy; <?php echo date('Y'); ?> 个人图书管理系统 All Rights Reserved. 
                <!--a href="https://github.com/qr4d/library-management" target="_blank" class="text-decoration-none text-muted">
                    <i class="bi bi-github"></i> GitHub
                </a-->
                <!-- 添加 GitHub Star 按钮 -->
                <iframe
                    src="https://ghbtns.com/github-btn.html?user=qr4d&repo=library-management&type=star&count=true"
                    frameborder="0"
                    scrolling="0"
                    width="100"
                    height="20"
                    title="GitHub"
                    style="vertical-align: bottom; margin-left: 8px;"
                ></iframe>
            </p>
        </div>
    </footer>
    <script src="https://lf9-cdn-tos.bytecdntp.com/cdn/expire-1-M/bootstrap/5.1.3/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/components/toast.js?v=<?php echo time(); ?>"></script>
    <script src="js/components/modal.js?v=<?php echo time(); ?>"></script>
    <script src="js/router.js?v=<?php echo time(); ?>"></script>
    <script src="js/pages/search.js?v=<?php echo time(); ?>"></script>
    <script src="js/pages/quickAdd.js?v=<?php echo time(); ?>"></script>
    <script src="js/pages/shelf.js?v=<?php echo time(); ?>"></script>
    <script src="js/pages/books.js?v=<?php echo time(); ?>"></script>
    <script src="js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        window.isLoggedIn = <?php echo $auth->isLoggedIn() ? 'true' : 'false'; ?>;
        window.isAdmin = <?php echo $auth->isAdmin() ? 'true' : 'false'; ?>;
    </script>
</body>
</html>