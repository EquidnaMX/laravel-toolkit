<?php

/**
 * Exception for HTTP 429 Too Many Requests responses (429 Too Many Requests).
 *
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 */

namespace Equidna\Toolkit\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Equidna\Toolkit\Helpers\ResponseHelper;
use Exception;
use Throwable;

class TooManyRequestsException extends Exception
{
    /**
     * TooManyRequestsException constructor.
     *
     * @param string $message Exception message (default: 'Too Many Requests').
     * @param Throwable|null $previous Previous exception for chaining.
     * @param array $errors Optional array of error details.
     */
    public function __construct(string $message = 'Too Many Requests', ?Throwable $previous = null, private ?array $errors = null)
    {
        parent::__construct($message, 429, $previous);
    }

    /**
     * Report the exception to the log.
     *
     * @return void
     */
    public function report(): void
    {
        Log::error('TooManyRequestsException: ' . $this->getMessage(), [
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'errors' => $this->errors
        ]);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @return RedirectResponse|JsonResponse
     */
    public function render(): RedirectResponse|JsonResponse
    {
        return ResponseHelper::tooManyRequests(
            message: $this->message,
            errors: $this->errors ?? [$this->message],
        );
    }
}
