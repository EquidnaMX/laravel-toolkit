<?php

namespace Equidna\Toolkit\Services\Responses;

use Equidna\Toolkit\Contracts\ResponseStrategyInterface;
use Illuminate\Http\JsonResponse;

class JsonResponseStrategy implements ResponseStrategyInterface
{
    public function respond(
        int $status,
        string $message,
        array $errors = [],
        mixed $data = null,
        array $headers = [],
        ?string $forwardUrl = null
    ): JsonResponse {
        if ($status === 204) {
            return response()->json(null, $status, $headers);
        }

        $response = [
            'status'  => $status,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($status >= 400 && !empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status, $headers);
    }
}

