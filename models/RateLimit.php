<?php
// 创建限流类 models/RateLimit.php
class RateLimit {
    public static function check($key, $limit = 60, $period = 60) {
        $cache = Cache::getInstance();
        $current = $cache->get($key) ?: 0;
        
        if($current >= $limit) {
            Error::json('请求过于频繁', 429);
        }
        
        $cache->set($key, $current + 1, $period);
        return true;
    }
}