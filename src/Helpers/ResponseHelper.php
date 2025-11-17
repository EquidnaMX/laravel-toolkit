<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 *
 */

namespace Equidna\Toolkit\Helpers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Equidna\Toolkit\Helpers\RouteHelper;

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
     * Generates a response based on the context (console, API, hook, or web).
     *
     * @param int $status The HTTP status code of the operation.
     * @param string $message The message to be included in the response.
     * @param array $errors An array of errors to include in the response. Default is an empty array.
     * @param mixed $data Optional data to include in API responses. Default is null.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url The URL to redirect to if applicable. Default is null.
     *
     * @return string|JsonResponse|RedirectResponse
     */
    private static function generateResponse(int $status, string $message, array $errors = [], mixed $data = null, array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        if (RouteHelper::isConsole()) {
            return $message;
        }

        if (RouteHelper::wantsJson()) {
            return self::generateJsonResponse(status: $status, message: $message, errors: $errors, data: $data, headers: $headers);
        }

        return redirect(
            to: $forward_url ?? url()->previous(),
            headers: $headers
        )->with(
            [
                'status'  => $status,
                'message' => $message,
                'errors'  => $errors,
                'data'    => $data
            ]
        )->withErrors($errors)
            ->withInput();
    }

    /**
     * Generates a JSON response with the provided data.
     *
     * @param int $status The HTTP status code.
     * @param string $message The response message.
     * @param array $errors Array of errors to include. Default is an empty array.
     * @param mixed $data Optional data to include. Default is null.
     * @param array $headers Optional headers to include. Default is an empty array.
     *
     * @return JsonResponse
     */
    private static function generateJsonResponse(int $status, string $message, array $errors = [], mixed $data = null, array $headers = []): JsonResponse
    {
        if ($status === self::HTTP_NO_CONTENT) {
            return response()->json(null, $status, $headers);
        }

        $response = [
            'status'  => $status,
            'message' => $message,
        ];

        // Add data to response if provided
        if ($data !== null) {
            $response['data'] = $data;
        }

        // Add errors to response
        if ($status >= 400) {
            $response['errors'] = $errors;
        }

        return response()
            ->json(
                $response,
                $status,
                $headers
            );
    }

    // SUCCESS RESPONSES

    /**
     * Generates a 200 OK response.
     *
     * @param string $message The success message to include in the response.
     * @param mixed $data Optional data to include in the response. Default is null.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to redirect to. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function success(string $message, mixed $data = null, array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_OK,
            message: $message,
            errors: [],
            data: $data,
            headers: $headers,
            forward_url: $forward_url
        );
    }

    /**
     * Generates a 201 Created response.
     *
     * @param string $message The success message to include in the response.
     * @param mixed $data Optional data to include in the response. Default is null.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to redirect to. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function created(string $message, mixed $data = null, array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_CREATED,
            message: $message,
            errors: [],
            data: $data,
            headers: $headers,
            forward_url: $forward_url
        );
    }

    /**
     * Generates a 202 Accepted response (for asynchronous processing).
     *
     * @param string $message The success message to include in the response.
     * @param mixed $data Optional data to include in the response. Default is null.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to redirect to. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function accepted(string $message, mixed $data = null, array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_ACCEPTED,
            message: $message,
            errors: [],
            data: $data,
            headers: $headers,
            forward_url: $forward_url
        );
    }

    /**
     * Generates a 204 No Content response (typically for successful DELETE operations).
     *
     * @param string $message The success message. Default is 'Operation completed successfully'.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to redirect to. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function noContent(string $message = 'Operation completed successfully', array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_NO_CONTENT,
            message: $message,
            errors: [],
            data: null,
            headers: $headers,
            forward_url: $forward_url
        );
    }

    // ERROR RESPONSES

    /**
     * Generates a 400 Bad Request response.
     *
     * @param string $message The message to include in the response.
     * @param array $errors An array of errors to include in the response. Default is an empty array.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to redirect to. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function badRequest(string $message, array $errors = [], array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_BAD_REQUEST,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url
        );
    }

    /**
     * Generates a 401 Unauthorized response.
     *
     * @param string $message The message to include in the response.
     * @param array $errors An array of errors to include in the response. Default is an empty array.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url The URL to forward to, if any. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function unauthorized(string $message, array $errors = [], array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_UNAUTHORIZED,
            message: $message,
            errors: $errors,
            data: null,
            forward_url: $forward_url,
            headers: $headers
        );
    }

    /**
     * Generates a 403 Forbidden response.
     *
     * @param string $message The message to include in the response.
     * @param array $errors An array of errors to include in the response. Default is an empty array.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to redirect to. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function forbidden(string $message, array $errors = [], array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_FORBIDDEN,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url
        );
    }

    /**
     * Generates a 404 Not Found response.
     *
     * @param string $message The message to include in the response.
     * @param array $errors An array of errors to include in the response. Default is an empty array.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to redirect to. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function notFound(string $message, array $errors = [], array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_NOT_FOUND,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url
        );
    }

    /**
     * Generates a 406 Not Acceptable response.
     *
     * @param string $message The message to include in the response.
     * @param array $errors An array of errors to include in the response. Default is an empty array.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to redirect to. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function notAcceptable(string $message, array $errors = [], array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_NOT_ACCEPTABLE,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url
        );
    }

    /**
     * Generates a 409 Conflict response.
     *
     * @param string $message The message to include in the response.
     * @param array $errors An array of errors to include in the response. Default is an empty array.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to redirect to. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function conflict(string $message, array $errors = [], array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_CONFLICT,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url
        );
    }

    /**
     * Generates a 422 Unprocessable Entity response.
     *
     * @param string $message The message to include in the response.
     * @param array $errors An array of validation errors. Default is an empty array.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to redirect to. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function unprocessableEntity(string $message, array $errors = [], array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_UNPROCESSABLE_ENTITY,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url
        );
    }

    /**
     * Generates a 429 Too Many Requests response.
     *
     * @param string $message The message to include in the response.
     * @param array $errors An array of errors to include in the response. Default is an empty array.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to redirect to. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function tooManyRequests(string $message, array $errors = [], array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_TOO_MANY_REQUESTS,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url
        );
    }

    /**
     * Generates an error response with a 500 status code.
     *
     * @param string $message The error message to be included in the response.
     * @param array $errors An array of errors to include in the response. Default is an empty array.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to redirect to. Default is null.
     * @return string|JsonResponse|RedirectResponse
     */
    public static function error(string $message, array $errors = [], array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
        return self::generateResponse(
            status: self::HTTP_INTERNAL_SERVER_ERROR,
            message: $message,
            errors: $errors,
            data: null,
            headers: $headers,
            forward_url: $forward_url
        );
    }

    // UTILITY METHODS

    /**
     * Handles exceptions and returns an appropriate response based on the exception code.
     *
     * @param Exception $exception The exception to handle.
     * @param array $errors Additional errors to include in the response. Default is an empty array.
     * @param array $headers Optional headers to include in the response. Default is an empty array.
     * @param string|null $forward_url Optional URL to forward to in case of an error. Default is null.
     * @return string|JsonResponse|RedirectResponse The response corresponding to the exception code.
     */
    public static function handleException(Exception $exception, array $errors = [], array $headers = [], ?string $forward_url = null): string|JsonResponse|RedirectResponse
    {
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
                    self::HTTP_INTERNAL_SERVER_ERROR
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
                forward_url: $forward_url
            ),
        };
    }
}

