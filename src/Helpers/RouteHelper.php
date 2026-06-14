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

use Equidna\Toolkit\Contracts\RequestResolverInterface;
use Equidna\Toolkit\Contracts\RouteDetectorInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Offers utilities for identifying the current request channel or format.
 */
class RouteHelper
{
    private static ?RouteDetectorInterface $detector = null;

    private static ?RequestResolverInterface $requestResolver = null;

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
        $request = self::getRequest();

        if (is_null($request)) {
            return false;
        }

        $detector = self::getDetector();

        return !($detector->isApi($request) || $detector->isHook($request) || $detector->isIoT($request));
    }

    /**
     * Determines whether the request targets an API-prefixed route.
     *
     * @return bool  True when the first segment matches an API pattern.
     */
    public static function isApi(): bool
    {
        $request = self::getRequest();

        if (is_null($request)) {
            return false;
        }

        return self::getDetector()->isApi($request);
    }

    /**
     * Determines whether the request hits the hooks namespace.
     *
     * @return bool  True when the URI matches the hooks wildcard.
     */
    public static function isHook(): bool
    {
        $request = self::getRequest();

        if (is_null($request)) {
            return false;
        }

        return self::getDetector()->isHook($request);
    }

    /**
     * Determines whether the request targets the IoT namespace.
     *
     * @return bool  True when the URI begins with iot/.
     */
    public static function isIoT(): bool
    {
        $request = self::getRequest();

        if (is_null($request)) {
            return false;
        }

        return self::getDetector()->isIoT($request);
    }

    /**
     * Determines whether the given path expression matches the request.
     *
     * @param  string $expression Glob-style expression evaluated against the request path.
     * @return bool               True when the expression matches.
     */
    public static function isExpression(string $expression): bool
    {
        return self::getRequest()?->is($expression) ?? false;
    }

    /**
     * Determines whether the request expects a JSON payload.
     *
     * @return bool  True when JSON is the desired representation.
     */
    public static function wantsJson(): bool
    {
        $request = self::getRequest();

        if (is_null($request)) {
            return false;
        }

        return self::getDetector()->wantsJson($request);
    }

    /**
     * Returns the current HTTP method name.
     *
     * @return string|null The HTTP method (GET, POST, etc.) or null when unavailable.
     */
    public static function getMethod(): ?string
    {
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
        return self::getRequest()?->isMethod($method) ?? false;
    }

    /**
     * Returns the current named route when available.
     *
     * @return string|null Route name or null when not resolved.
     */
    public static function getRouteName(): ?string
    {
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
        return Str::contains(self::getRouteName() ?: '', $name);
    }

    /**
     * Resolves the current HTTP request when available.
     *
     * @return Request|null Request instance or null when one is not bound.
     */
    private static function getRequest(): ?Request
    {
        return self::getRequestResolver()->resolve();
    }

    private static function getDetector(): RouteDetectorInterface
    {
        self::$detector ??= app()->make(RouteDetectorInterface::class);

        return self::$detector;
    }

    private static function getRequestResolver(): RequestResolverInterface
    {
        self::$requestResolver ??= app()->make(RequestResolverInterface::class);

        return self::$requestResolver;
    }
}
