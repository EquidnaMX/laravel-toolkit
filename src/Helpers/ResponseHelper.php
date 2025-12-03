<?php

/**
 * Centralizes JSON, redirect, and console responses for every execution context.
 *
 * PHP 8.0+
 *
 * @package   Equidna\Toolkit\Helpers
 * @author    Gabriel Ruelas <gruelasjr@gmail.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://github.com/EquidnaMX/laravel-toolkit Documentation
 */

namespace Equidna\Toolkit\Helpers;

use Equidna\Toolkit\Contracts\ResponseStrategyInterface;
use Equidna\Toolkit\Services\Responses\ConsoleResponseStrategy;
use Equidna\Toolkit\Services\Responses\JsonResponseStrategy;
use Equidna\Toolkit\Services\Responses\RedirectResponseStrategy;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Provides helper responses that honor the active context (console, API, hooks, or web).
 */
class ResponseHelper
{
    // HTTP Status Code Constants for better maintainability
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_ACCEPTED = 202;
    public const HTTP_NO_CONTENT = 204;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_NOT_ACCEPTABLE = 406;
    public const HTTP_CONFLICT = 409;
    public const HTTP_UNPROCESSABLE_ENTITY = 422;
    public const HTTP_TOO_MANY_REQUESTS = 429;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * Generates a context-aware response for console, API, hook, or web flows.
     *
     * @param  int                              $status        HTTP status code delivered to the client.
     * @param  string                           $message       Human-readable response message.
     * @param  array<int|string, mixed>         $errors        Error bag keyed by attribute name.
     * @param  mixed                            $data          Optional payload attached to the response.
     * @param  array<string, string>            $headers       Additional headers appended to the response.
     * @param  string|null                      $forward_url   Destination URL for redirects in web flows.
     * @return string|JsonResponse|RedirectResponse
     */
    private static function generateResponse(
        int $status,
        string $message,
        array $errors = [],
        mixed $data = null,
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        $strategy = self::resolveStrategy();

        $sanitizedMessage = self::sanitizeMessage($status, $message);
        $sanitizedErrors  = self::sanitizeErrors($status, $errors);
        $sanitizedHeaders = self::sanitizeHeaders($headers, $strategy);

        return $strategy->respond(
            status: $status,
            message: $sanitizedMessage,
            errors: $sanitizedErrors,
            data: $data,
            headers: $sanitizedHeaders,
            forwardUrl: $forward_url,
        );
    }

    private static function resolveStrategy(): ResponseStrategyInterface
    {
        if (RouteHelper::isConsole()) {
            return new ConsoleResponseStrategy();
        }

        if (RouteHelper::wantsJson()) {
            return new JsonResponseStrategy();
        }

        return new RedirectResponseStrategy();
    }

    private static function sanitizeMessage(int $status, string $message): string
    {
        $includeDebug = config('app.debug', false);

        if ($status >= self::HTTP_INTERNAL_SERVER_ERROR && !$includeDebug) {
            return 'An unexpected error occurred.';
        }

        return $message;
    }

    private static function sanitizeErrors(int $status, array $errors): array
    {
        $includeDebug = config('app.debug', false);

        if ($status >= self::HTTP_INTERNAL_SERVER_ERROR && !$includeDebug) {
            return [];
        }

        return $errors;
    }

    private static function sanitizeHeaders(array $headers, ResponseStrategyInterface $strategy): array
    {
        $sanitized = collect($headers)
            ->filter(fn($value, $key) => is_string($key) && is_string($value))
            ->all();

        // Apply allow-list filtering if the strategy requires it
        if ($strategy->requiresHeaderAllowList()) {
            $allowed = array_map('strtolower', config('equidna.responses.redirect_allowed_headers', []));

            $sanitized = collect($sanitized)
                ->filter(function ($value, $key) use ($allowed) {
                    if (empty($allowed)) {
                        return false;
                    }

                    return in_array(strtolower((string) $key), $allowed, true);
                })
                ->all();
        }

        return $sanitized;
    }

    // SUCCESS RESPONSES

    /**
     * Returns a 200 OK response for successful synchronous operations.
     *
     * @param  string                           $message      Message communicated to the consumer.
     * @param  mixed                            $data         Optional payload (JSON body or flash data).
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function success(
        string $message,
        mixed $data = null,
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_OK,
            message: $message,
            errors: [],
            data: $data,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    /**
     * Returns a 201 Created response once a resource is persisted.
     *
     * @param  string                           $message      Message communicated to the consumer.
     * @param  mixed                            $data         Optional payload describing the resource.
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function created(
        string $message,
        mixed $data = null,
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_CREATED,
            message: $message,
            errors: [],
            data: $data,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    /**
     * Returns a 202 Accepted response when processing occurs asynchronously.
     *
     * @param  string                           $message      Message communicated to the consumer.
     * @param  mixed                            $data         Optional payload (queue reference, etc.).
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function accepted(
        string $message,
        mixed $data = null,
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_ACCEPTED,
            message: $message,
            errors: [],
            data: $data,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    /**
     * Returns a 204 No Content response for operations that succeed without payload.
     *
     * @param  string                           $message      Human-readable confirmation message.
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function noContent(
        string $message = 'Operation completed successfully',
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_NO_CONTENT,
            message: $message,
            errors: [],
            data: null,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    // ERROR RESPONSES

    /**
     * Returns a 400 Bad Request response for validation or format errors.
     *
     * @param  string                           $message      Message describing the failure.
     * @param  array<int|string, mixed>         $errors       Error details keyed by attribute name.
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function badRequest(
        string $message,
        array $errors = [],
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_BAD_REQUEST,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    /**
     * Returns a 401 Unauthorized response when authentication fails.
     *
     * @param  string                           $message      Message describing the authentication issue.
     * @param  array<int|string, mixed>         $errors       Error details keyed by attribute name.
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function unauthorized(
        string $message,
        array $errors = [],
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_UNAUTHORIZED,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    /**
     * Returns a 403 Forbidden response for authorization failures.
     *
     * @param  string                           $message      Message describing the authorization issue.
     * @param  array<int|string, mixed>         $errors       Error details keyed by attribute name.
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function forbidden(
        string $message,
        array $errors = [],
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_FORBIDDEN,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    /**
     * Returns a 404 Not Found response when a resource is missing.
     *
     * @param  string                           $message      Message describing the missing resource.
     * @param  array<int|string, mixed>         $errors       Error details keyed by attribute name.
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function notFound(
        string $message,
        array $errors = [],
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_NOT_FOUND,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    /**
     * Returns a 406 Not Acceptable response when negotiation fails.
     *
     * @param  string                           $message      Message describing the negotiation issue.
     * @param  array<int|string, mixed>         $errors       Error details keyed by attribute name.
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function notAcceptable(
        string $message,
        array $errors = [],
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_NOT_ACCEPTABLE,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    /**
     * Returns a 409 Conflict response when state changes collide.
     *
     * @param  string                           $message      Message describing the conflict condition.
     * @param  array<int|string, mixed>         $errors       Error details keyed by attribute name.
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function conflict(
        string $message,
        array $errors = [],
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_CONFLICT,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    /**
     * Returns a 422 Unprocessable Entity response for validation failures.
     *
     * @param  string                           $message      Message describing the validation issue.
     * @param  array<int|string, mixed>         $errors       Validation errors keyed by attribute name.
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function unprocessableEntity(
        string $message,
        array $errors = [],
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_UNPROCESSABLE_ENTITY,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    /**
     * Returns a 429 Too Many Requests response for throttled clients.
     *
     * @param  string                           $message      Message describing the throttling reason.
     * @param  array<int|string, mixed>         $errors       Error details keyed by attribute name.
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function tooManyRequests(
        string $message,
        array $errors = [],
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_TOO_MANY_REQUESTS,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    /**
    /**
     * Returns a 500 Internal Server Error response for unexpected failures.
     *
     * @param  string                           $message      Message describing the unexpected condition.
     * @param  array<int|string, mixed>         $errors       Error details keyed by attribute name.
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function error(
        string $message,
        array $errors = [],
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        return self::generateResponse(
            status: self::HTTP_INTERNAL_SERVER_ERROR,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url,
        );
    }

    // UTILITY METHODS

    /**
     * Maps an exception to an HTTP-aware response using the exception code when possible.
     *
     * @param  Exception                        $exception    Thrown exception captured by the caller.
     * @param  array<int|string, mixed>         $errors       Supplemental error payload.
     * @param  array<string, string>            $headers      Additional headers applied to the response.
     * @param  string|null                      $forward_url  Redirect URL for web contexts.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function handleException(
        Exception $exception,
        array $errors = [],
        array $headers = [],
        ?string $forward_url = null
    ): string|JsonResponse|RedirectResponse {
        $code    = $exception->getCode();
        $message = $exception->getMessage();

        if (
            !in_array(
                $code,
                [
                    self::HTTP_OK,
                    self::HTTP_CREATED,
                    self::HTTP_ACCEPTED,
                    self::HTTP_NO_CONTENT,
                    self::HTTP_BAD_REQUEST,
                    self::HTTP_UNAUTHORIZED,
                    self::HTTP_FORBIDDEN,
                    self::HTTP_NOT_FOUND,
                    self::HTTP_NOT_ACCEPTABLE,
                    self::HTTP_CONFLICT,
                    self::HTTP_UNPROCESSABLE_ENTITY,
                    self::HTTP_TOO_MANY_REQUESTS,
                    self::HTTP_INTERNAL_SERVER_ERROR,
                ]
            )
        ) {
            $code = self::HTTP_INTERNAL_SERVER_ERROR;
        }

        return match ($code) {
            self::HTTP_BAD_REQUEST => self::badRequest($message, $errors, $headers, $forward_url),
            self::HTTP_UNAUTHORIZED => self::unauthorized($message, $errors, $headers, $forward_url),
            self::HTTP_FORBIDDEN => self::forbidden($message, $errors, $headers, $forward_url),
            self::HTTP_NOT_FOUND => self::notFound($message, $errors, $headers, $forward_url),
            self::HTTP_NOT_ACCEPTABLE => self::notAcceptable($message, $errors, $headers, $forward_url),
            self::HTTP_CONFLICT => self::conflict($message, $errors, $headers, $forward_url),
            self::HTTP_UNPROCESSABLE_ENTITY => self::unprocessableEntity($message, $errors, $headers, $forward_url),
            self::HTTP_TOO_MANY_REQUESTS => self::tooManyRequests($message, $errors, $headers, $forward_url),
            self::HTTP_INTERNAL_SERVER_ERROR => self::error($message, $errors, $headers, $forward_url),
            default => self::error(
                message: "An unexpected error occurred. ({$code}: {$message})",
                errors: $errors,
                headers: $headers,
                forward_url: $forward_url,
            ),
        };
    }
}
