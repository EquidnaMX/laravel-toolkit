<?php

namespace Equidna\Toolkit\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

interface ResponseStrategyInterface
{
    /**
     * Render the response for the current execution context.
     */
    public function respond(
        int $status,
        string $message,
        array $errors = [],
        mixed $data = null,
        array $headers = [],
        ?string $forwardUrl = null
    ): string|JsonResponse|RedirectResponse;
}

