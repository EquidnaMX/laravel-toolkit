<?php

namespace Equidna\Toolkit\Services\Responses;

use Equidna\Toolkit\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;

class JsonResponseStrategy extends AbstractResponseStrategy
{
    public function respond(
        int $status,
        string $message,
        array $errors = [],
        mixed $data = null,
        array $headers = [],
        ?string $forwardUrl = null
    ): JsonResponse {
        if ($status === ResponseHelper::HTTP_NO_CONTENT) {
            return response()->json(null, $status, $headers);
        }

        $response = [
            'status'  => $status,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($status >= ResponseHelper::HTTP_BAD_REQUEST && !empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status, $headers);
    }
}
