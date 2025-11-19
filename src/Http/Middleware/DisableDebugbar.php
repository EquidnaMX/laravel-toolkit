<?php

/**
 * Disables the Laravel Debugbar per-request when present in the container.
 * PHP 8.0+
 * @package   Equidna\Toolkit\Http\Middleware
 * @author    Gabriel Ruelas <gruelasjr@gmail.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://laravel.com/docs/12.x/middleware#middleware-and-responses Documentation
 */

namespace Equidna\Toolkit\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

/**
 * Ensures the Debugbar stays disabled on environments where it may exist.
 */
class DisableDebugbar
{
    /**
     * Disables Debugbar when bound and continues the middleware pipeline.
     *
     * @param  Request $request Incoming HTTP request instance.
     * @param  Closure $next    Next middleware closure.
     * @return mixed            Response from the subsequent middleware.
     */
    public function handle(
        Request $request,
        Closure $next,
    ): mixed {
        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }

        return $next($request);
    }
}
