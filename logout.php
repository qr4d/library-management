<?php
session_start();

// 清除所有会话变量
$_SESSION = array();

// 销毁会话 cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// 销毁会话
session_destroy();

// 记录退出日志
require_once 'models/Auth.php';
$config = require_once 'config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
        $config['username'], 
        $config['password']
    );

    $auth = Auth::getInstance($pdo);
    if($auth->getCurrentUser()) {
        $auth->logOperation('LOGOUT', 'user', $auth->getCurrentUser()['id'], '用户退出登录');
    }
} catch(Exception $e) {
    // 忽略日志记录错误
}

// 重定向到登录页面
header('Location: /');
exit;