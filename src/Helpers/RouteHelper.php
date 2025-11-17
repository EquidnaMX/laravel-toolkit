<?php

/**
 * RouteHelper
 *
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 *
 * Provides static utility methods for request type detection and routing logic in Laravel applications.
 */

namespace Equidna\Toolkit\Helpers;

use Exception;

class RouteHelper
{
    /**
     * Determine if the application is running in the console.
     *
     * @return bool True if the application is running in the console, false otherwise.
     */
    public static function isConsole(): bool
    {
        try {
            return app()->runningInConsole();
        } catch (Exception $e) {
            // Fallback for cases where app() is not available
            return php_sapi_name() === 'cli';
        }
    }

    /**
     * Determine if the request is a web request.
     *
     * @return bool True if the request is a web request, false otherwise.
     */
    public static function isWeb(): bool
    {
        return !(self::isApi() || self::isHook() || self::isIoT() || self::isConsole());
    }


    /**
     * Determine if the request is an API request.
     *
     * @return bool True if the request is an API request, false otherwise.
     */
    public static function isApi(): bool
    {
        $firstSegment = request()?->segment(1);

        if (is_null($firstSegment)) {
            return false;
        }

        return preg_match('/^(api|.*-api|api-.*)$/i', $firstSegment) === 1;
    }

    /**
     * Determine if the request is a hook request.
     *
     * @return bool True if the request is a hook request, false otherwise.
     */
    public static function isHook(): bool
    {
        return request()?->is('hooks/*') ?? false;
    }

    /**
     * Determine if the request is an IoT request.
     *
     * @return bool True if the request is an IoT request, false otherwise.
     */
    public static function isIoT(): bool
    {
        return request()?->is('iot/*') ?? false;
    }


    /**
     * Determines if the given string is a valid expression.
     *
     * @param string $expression The string to evaluate.
     * @return bool True if the string is a valid expression, false otherwise.
     */
    public static function isExpression(string $expression): bool
    {
        return request()?->is($expression) ?? false;
    }

    /**
     * Determine if the request expects a JSON response.
     *
     * @return bool True if the request expects JSON, false otherwise.
     */
    public static function wantsJson(): bool
    {
        return self::isApi() ||
            self::isHook() ||
            self::isIoT() ||
            request()?->expectsJson();
    }

    /**
     * Get the current request method.
     *
     * @return string|null The HTTP method (GET, POST, etc.) or null if no request.
     */
    public static function getMethod(): ?string
    {
        return request()?->method();
    }

    /**
     * Check if the current request is a specific HTTP method.
     *
     * @param string $method The HTTP method to check (GET, POST, PUT, DELETE, etc.).
     * @return bool True if the request method matches, false otherwise.
     */
    public static function isMethod(string $method): bool
    {
        return request()?->isMethod($method) ?? false;
    }

    /**
     * Get the current route name.
     *
     * @return string|null The route name or null if not available.
     */
    public static function getRouteName(): ?string
    {
        return request()?->route()?->getName();
    }

    /**
     * Check if the current route has a specific name.
     *
     * @param string $name The route name to check.
     * @return bool True if the route name matches, false otherwise.
     */
    public static function isRouteName(string $name): bool
    {
        return self::getRouteName() === $name;
    }

    /**
     * Checks if the current route name contains the specified string.
     *
     * @param string $name The string to search for in the current route name.
     * @return bool Returns true if the current route name contains the specified string, false otherwise.
     */
    public static function routeContains(string $name): bool
    {
        return str_contains(self::getRouteName() ?? '', $name);
    }
}

