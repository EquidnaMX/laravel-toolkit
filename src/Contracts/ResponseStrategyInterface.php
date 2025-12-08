<?php

namespace Equidna\Toolkit\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

interface ResponseStrategyInterface
{
    /**
     * Render the response for the current execution context using the strategy pattern.
     *
     * The concrete implementation will return a response type appropriate for the context:
     * - API/Hook/IoT: JsonResponse
     * - Web: RedirectResponse
     * - Console: string
     *
     * @param int $status      HTTP status code for the response (e.g., 200, 404, 422).
     * @param string $message  Human-readable message describing the result.
     * @param array $errors    List of error details, if any (default: empty array).
     * @param mixed $data      Additional data to include in the response (default: null).
     * @param array $headers   Extra HTTP headers to send with the response (default: empty array).
     * @param string|null $forwardUrl Optional URL to redirect to (web context only).
     *
     * @return string|JsonResponse|RedirectResponse
     *         Response type depends on execution context; see above.
     */
    public function respond(
        int $status,
        string $message,
        array $errors = [],
        mixed $data = null,
        array $headers = [],
        ?string $forwardUrl = null
    ): string|JsonResponse|RedirectResponse;

    /**
     * Determine if this strategy requires allow-list filtering for response headers.
     *
     * @return bool True if headers should be filtered by an allow-list, false otherwise.
     */
    public function requiresHeaderAllowList(): bool;
}
