<?php

declare(strict_types=1);

final class JsonResponse
{
    public static function success(array $payload = [], int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['status' => 'success'], $payload));
        exit;
    }

    public static function error(string $message, int $statusCode = 400, array $extra = []): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['status' => 'error', 'message' => $message], $extra));
        exit;
    }
}
