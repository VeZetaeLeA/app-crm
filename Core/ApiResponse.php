<?php
namespace Core;

/**
 * RF-10: Estandarización de respuestas de la API
 */
class ApiResponse
{
    public static function success(array $data = [], string $message = 'Operación exitosa', int $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');

        echo json_encode([
            'success'   => true,
            'status'    => $code,
            'message'   => $message,
            'data'      => $data,
            'timestamp' => date('c'),
            'meta'      => [
                'requestId' => App::$requestId ?? 'N/A'
            ]
        ]);
        exit;
    }

    public static function error(string $message, int $code = 400, array $errors = [])
    {
        http_response_code($code);
        header('Content-Type: application/json');

        echo json_encode([
            'success'   => false,
            'status'    => $code,
            'message'   => $message,
            'errors'    => $errors,
            'timestamp' => date('c'),
            'meta'      => [
                'requestId' => App::$requestId ?? 'N/A'
            ]
        ]);
        exit;
    }
}
