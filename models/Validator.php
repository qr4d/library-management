<?php
// 创建验证类 models/Validator.php
class Validator {
    public static function required($data, $fields) {
        foreach($fields as $field) {
            if(!isset($data[$field]) || empty($data[$field])) {
                ApiError::json("{$field} 不能为空");
            }
        }
    }
    
    public static function numeric($value, $field) {
        if(!is_numeric($value)) {
            ApiError::json("{$field} 必须是数字");
        }
    }

    public static function validateShelfName($name) {
        if (empty($name)) {
            throw new InvalidArgumentException('Shelf name cannot be empty.');
        }
        if (strlen($name) > 255) {
            throw new InvalidArgumentException('Shelf name cannot exceed 255 characters.');
        }
    }

    public static function validateShelfId($id) {
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            throw new InvalidArgumentException('Invalid shelf ID.');
        }
    }

    public static function validate($data, $rules) {
        $errors = [];
        
        foreach($rules as $field => $rule) {
            if(isset($rule['required']) && $rule['required']) {
                if(!isset($data[$field]) || empty($data[$field])) {
                    $errors[$field] = "{$field} 不能为空";
                    continue;
                }
            }
            
            if(isset($data[$field])) {
                if(isset($rule['min']) && strlen($data[$field]) < $rule['min']) {
                    $errors[$field] = "{$field} 长度不能小于 {$rule['min']}";
                }
                
                if(isset($rule['max']) && strlen($data[$field]) > $rule['max']) {
                    $errors[$field] = "{$field} 长度不能大于 {$rule['max']}";
                }
                
                if(isset($rule['pattern']) && !preg_match($rule['pattern'], $data[$field])) {
                    $errors[$field] = "{$field} 格式不正确";
                }
            }
        }
        
        if(!empty($errors)) {
            ApiError::json(['validation_errors' => $errors]);
        }
    }
}