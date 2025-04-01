<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../models/Auth.php';
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Shelf.php';
require_once __DIR__ . '/../models/ApiError.php';
require_once __DIR__ . '/../models/Response.php';
require_once __DIR__ . '/../models/Validator.php';

try {
    $db = Database::getInstance();
    $auth = Auth::getInstance($db->getPdo());
    $shelf = new Shelf($db->getPdo());

    // GET请求不需要权限验证，其他所有修改操作都需要管理员权限
    if($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $auth->requireAdmin();
    }
    
    // 处理GET请求 - 获取书架列表
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        Response::success($shelf->getAll());
        exit;
    }

    // 处理POST请求 - 新增书架
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        Validator::validateShelfName($name);
        
        $shelfId = $shelf->add($name);
        Response::success(['id' => $shelfId]);
        
        // 记录操作日志
        $auth->logOperation('ADD', 'shelf', $shelfId, "添加书架:{$name}");
        exit;
    }

    // 处理DELETE请求 - 删除书架
    if($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? '';
        $action = $data['action'] ?? 'delete';
        $targetShelfId = $data['target_shelf_id'] ?? null;
        
        Validator::validateShelfId($id);
        
        // 开启事务
        $db->getPdo()->beginTransaction();
        try {
            // 获取书架名称用于日志
            $shelfName = $shelf->getNameById($id);

            if($action === 'move' && !empty($targetShelfId)) {
                // 转移书籍到目标书架
                $shelf->moveBooks($id, $targetShelfId);
            } else {
                // 删除该书架下的所有图书
                $shelf->deleteBooks($id);
            }
            
            // 删除书架
            $shelf->delete($id);
            
            $db->getPdo()->commit();
            Response::success();

            // 记录操作日志
            $auth->logOperation('DELETE', 'shelf', $id, "删除书架:{$shelfName}");
            exit;
        } catch(Exception $e) {
            $db->getPdo()->rollBack();
            throw $e;
        }
    }

} catch(Exception $e) {
    ApiError::handle($e);
}