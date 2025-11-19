<?php

/**
 * Provides request-context helpers to determine routing intent and response type.
 * PHP 8.0+
 * @package   Equidna\Toolkit\Helpers
 * @author    Gabriel Ruelas <gruelasjr@gmail.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://github.com/EquidnaMX/laravel-toolkit Documentation
 */

namespace Equidna\Toolkit\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;

/**
 * Offers utilities for identifying the current request channel or format.
 */
class RouteHelper
{
    /**
     * Determines whether the application executes within the console.
     *
     * @return bool  True when the Laravel application runs via CLI.
     */
    public static function isConsole(): bool
    {
        try {
            return app()->runningInConsole();
        } catch (Exception $e) {
            return php_sapi_name() === 'cli';
        }
    }

    /**
     * Determines whether the current request should be handled as web traffic.
     *
     * @return bool  True when none of the API-focused channels apply.
     */
    public static function isWeb(): bool
    {
        return !(self::isApi() || self::isHook() || self::isIoT() || self::isConsole());
    }

    /**
     * Determines whether the request targets an API-prefixed route.
     *
     * @return bool  True when the first segment matches an API pattern.
     */
    public static function isApi(): bool
    {
        if (self::isConsole()) {
            return false;
        }

        $firstSegment = self::getRequest()?->segment(1);

        if (is_null($firstSegment)) {
            return false;
        }

        return preg_match('/^(api|.*-api|api-.*)$/i', $firstSegment) === 1;
    }

    /**
     * Determines whether the request hits the hooks namespace.
     *
     * @return bool  True when the URI matches the hooks wildcard.
     */
    public static function isHook(): bool
    {
        if (self::isConsole()) {
            return false;
        }

        return self::getRequest()?->is('hooks/*') ?? false;
    }

    /**
     * Determines whether the request targets the IoT namespace.
     *
     * @return bool  True when the URI begins with iot/.
     */
    public static function isIoT(): bool
    {
        if (self::isConsole()) {
            return false;
        }

        return self::getRequest()?->is('iot/*') ?? false;
    }

    /**
     * Determines whether the given path expression matches the request.
     *
     * @param  string $expression Glob-style expression evaluated against the request path.
     * @return bool               True when the expression matches.
     */
    public static function isExpression(string $expression): bool
    {
        if (self::isConsole()) {
            return false;
        }

        return self::getRequest()?->is($expression) ?? false;
    }

    /**
     * Determines whether the request expects a JSON payload.
     *
     * @return bool  True when JSON is the desired representation.
     */
    public static function wantsJson(): bool
    {
        if (self::isConsole()) {
            return false;
        }

        return self::isApi()
            || self::isHook()
            || self::isIoT()
            || (self::getRequest()?->expectsJson() ?? false);
    }

    /**
     * Returns the current HTTP method name.
     *
     * @return string|null The HTTP method (GET, POST, etc.) or null when unavailable.
     */
    public static function getMethod(): ?string
    {
        if (self::isConsole()) {
            return null;
        }

        return self::getRequest()?->method();
    }

    /**
     * Checks whether the request method matches the provided verb.
     *
     * @param  string $method HTTP verb to compare against the request method.
     * @return bool           True when the method matches.
     */
    public static function isMethod(string $method): bool
    {
        if (self::isConsole()) {
            return false;
        }

        return self::getRequest()?->isMethod($method) ?? false;
    }

    /**
     * Returns the current named route when available.
     *
     * @return string|null Route name or null when not resolved.
     */
    public static function getRouteName(): ?string
    {
        if (self::isConsole()) {
            return null;
        }

        $route = self::getRequest()?->route();

        return $route ? $route->getName() : null;
    }

    /**
     * Determines whether the current route name matches the given value.
     *
     * @param  string $name Route name to compare.
     * @return bool         True when the current route name equals the provided name.
     */
    public static function isRouteName(string $name): bool
    {
        return self::getRouteName() === $name;
    }

    /**
     * Determines whether the current route name contains the provided substring.
     *
     * @param  string $name Substring to look for in the route name.
     * @return bool         True when the route name contains the substring.
     */
    public static function routeContains(string $name): bool
    {
        if (self::isConsole()) {
            return false;
        }

        return Str::contains(self::getRouteName() ?: '', $name);
    }

    /**
     * Resolves the current HTTP request when available.
     *
     * @return Request|null Request instance or null when one is not bound.
     */
    private static function getRequest(): ?Request
    {
        if (!app()->bound('request')) {
            return null;
        }

        $request = request();

        return $request instanceof Request ? $request : null;
    }
}
