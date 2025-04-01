<?php
// Ensure CustomException is defined or imported
if (!class_exists('CustomException')) {
    class CustomException extends Exception {
        // You can add custom properties or methods here if needed
    }
}

class ApiError {
    const ERROR_CODES = [
        'VALIDATION_ERROR' => 400,
        'UNAUTHORIZED' => 401,
        'FORBIDDEN' => 403,
        'NOT_FOUND' => 404,
        'SERVER_ERROR' => 500
    ];

    public static function json($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'error' => $message,
            'code' => $code
        ]);
        exit;
    }

    public static function handle(Exception $e) {
        $code = $e instanceof CustomException ? $e->getCode() : 500;
        self::json($e->getMessage(), $code);
    }
}