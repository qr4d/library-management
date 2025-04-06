<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../models/Auth.php';
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/ApiError.php';
require_once __DIR__ . '/../models/Response.php';
require_once __DIR__ . '/../models/Validator.php';

try {
    $db = Database::getInstance();
    $auth = Auth::getInstance($db->getPdo());
    $book = new Book($db->getPdo());

    // GET请求不需要权限验证，其他所有修改操作都需要登录用户权限
    if($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $auth->requireLogin();
    }

    // 处理POST请求 - 添加图书
    if($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_GET['action']) || $_GET['action'] !== 'mark')) {
        $data = json_decode(file_get_contents('php://input'), true);
        Validator::required($data, ['title']);
        
        if($book->add($data)) {
            $bookId = $db->getPdo()->lastInsertId();
            $auth->logOperation('ADD', 'book', $bookId, "添加图书:{$data['title']}");
            Response::success(['id' => $bookId]);
        }
        ApiError::json('添加失败');
    }

    // 处理标记请求
    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'mark') {
        $auth->requireLogin();
        $data = json_decode(file_get_contents('php://input'), true);
        
        Validator::required($data, ['isbn', 'type']);
        if(!in_array($data['type'], ['like', 'dislike', 'favorite'])) {
            ApiError::json('无效的标记类型');
        }

        if($data['remove'] ?? false) {
            if($book->removeMark($auth->getCurrentUser()['id'], $data['isbn'], $data['type'])) {
                Response::success();
            }
        } else {
            if($book->addMark($auth->getCurrentUser()['id'], $data['isbn'], $data['type'])) {
                Response::success();
            }
        }
        ApiError::json('操作失败');
    }

    // 处理PUT请求 - 更新图书
    if($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        Validator::required($data, ['id']);
        
        if($book->update($data)) {
            Response::success();
        }
        ApiError::json('更新失败');
    }

    // 处理GET请求 - 获取图书列表
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        $shelfId = $_GET['shelf_id'] ?? '';
        $keyword = $_GET['keyword'] ?? '';
        $page = intval($_GET['page'] ?? 1);
        $pageSize = intval($_GET['page_size'] ?? 12); // 修改这行，默认为12
        $markType = $_GET['mark_type'] ?? '';
        $userId = $auth->isLoggedIn() ? $auth->getCurrentUser()['id'] : null;
        
        try {
            $books = $book->search([
                'shelf_id' => $shelfId,
                'keyword' => $keyword,
                'page' => $page,
                'page_size' => $pageSize,
                'mark_type' => $markType,
                'user_id' => $userId
            ]);
            
            Response::success($books);
        } catch(Exception $e) {
            Response::error($e->getMessage());
        }
    }

    // 处理DELETE请求 - 删除图书
    if($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        Validator::required($data, ['id']);

        $auth->requireAdmin(); // 删除操作需要管理员权限
        
        if($book->delete($data['id'])) {
            Response::success();
        }
        ApiError::json('删除失败');
    }

} catch(Exception $e) {
    ApiError::json($e->getMessage(), 500);
}
