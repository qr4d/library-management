<?php
header('Content-Type: application/json');

// 修改为正确的 ApiError 类引用
require_once __DIR__ . '/../models/ApiError.php';
require_once __DIR__ . '/../models/Response.php';

try {
    $keyword = $_GET['q'] ?? '';
    if(empty($keyword)) {
        ApiError::json('请输入搜索关键词');
    }

    $config = require_once __DIR__ . '/../config/apikey.php';
    $apiKey = $config['douban'];
    $url = "https://api.douban.com/v2/book/search?apikey={$apiKey}&q=" . urlencode($keyword);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    
    if($error = curl_error($ch)) {
        ApiError::json('豆瓣API请求失败:' . $error);
    }
    curl_close($ch);

    Response::success(json_decode($response, true));

} catch(Exception $e) {
    ApiError::handle($e);
}