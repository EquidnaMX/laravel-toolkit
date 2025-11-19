<?php

/**
 * Exception for HTTP 400 Bad Request responses (400 Bad Request).
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

class BadRequestException extends Exception
{
    /**
     * BadRequestException constructor.
     *
     * @param string $message Exception message (default: 'Bad Request').
     * @param Throwable|null $previous Previous exception for chaining.
     * @param array $errors Optional array of error details.
     */
    public function __construct(
        string $message = 'Bad Request',
        ?Throwable $previous = null,
        private ?array $errors = null
    ) {
        parent::__construct($message, 400, $previous);
    }

    /**
     * Report the exception to the log.
     *
     * @return void
     */
    public function report(): void
    {
        Log::error('BadRequestException: ' . $this->getMessage(), [
            'code'   => $this->getCode(),
            'file'   => $this->getFile(),
            'line'   => $this->getLine(),
            'errors' => $this->errors,
        ]);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @return RedirectResponse|JsonResponse
     */
    public function render(): RedirectResponse|JsonResponse
    {
        $errors = $this->errors ?? [$this->message];

        return ResponseHelper::badRequest(
            message: $this->message,
            errors: $errors
        );
    }
}
