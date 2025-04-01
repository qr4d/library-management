<?php
class Cache {
    private static $instance = null;
    private $cachePath;
    
    private function __construct() {
        $this->cachePath = __DIR__ . '/../cache/';
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
    }
    
    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get($key) {
        $filename = $this->getCacheFile($key);
        if (!file_exists($filename)) {
            return null;
        }
        
        $content = file_get_contents($filename);
        if (!$content) {
            return null;
        }
        
        $data = unserialize($content);
        if (!$data || !isset($data['expires']) || !isset($data['value'])) {
            return null;
        }
        
        // 检查是否过期
        if ($data['expires'] < time()) {
            unlink($filename);
            return null;
        }
        
        return $data['value'];
    }
    
    public function set($key, $value, $expires = 3600) {
        $filename = $this->getCacheFile($key);
        $data = serialize([
            'expires' => time() + $expires,
            'value' => $value
        ]);
        
        return file_put_contents($filename, $data);
    }
    
    private function getCacheFile($key) {
        return $this->cachePath . md5($key) . '.cache';
    }
    
    public function clear($key = null) {
        if ($key) {
            $filename = $this->getCacheFile($key);
            if (file_exists($filename)) {
                return unlink($filename);
            }
            return true;
        }
        
        // 清除所有缓存
        $files = glob($this->cachePath . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
}