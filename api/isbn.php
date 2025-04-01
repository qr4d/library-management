<?php
header('Content-Type: application/json'); 

require_once __DIR__ . '/../models/ApiError.php';
require_once __DIR__ . '/../models/Response.php';

try {
    // ISBN查询API
    $isbn = $_GET['isbn'] ?? '';
    if(empty($isbn)) {
        ApiError::json('请输入ISBN');
    }

    // 获取豆瓣API密钥
    $config = require_once __DIR__ . '/../config/apikey.php';
    $apiKey = $config['douban'];

    // 调用豆瓣API
    $url = "https://api.douban.com/v2/book/isbn/{$isbn}?apikey={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    
    if($error = curl_error($ch)) {
        ApiError::json('豆瓣API请求失败:' . $error);
    }
    curl_close($ch);

    // 使用统一的响应格式
    Response::success(json_decode($response, true));

} catch(Exception $e) {
    ApiError::handle($e);
}