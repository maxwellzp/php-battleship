<?php

declare(strict_types=1);

namespace App\Controller\RESTAPI;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse
{
    public static function success($data, $message = null): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error($message, $statusCode = 400): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }
}
