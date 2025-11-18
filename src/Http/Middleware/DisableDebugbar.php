<?php

namespace Equidna\Toolkit\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

/*This middleware disables the Laravel Debugbar for the current request if it is bound in the container.
 * This is useful for production environments where you do not want to expose debugging information.
 */

class DisableDebugbar
{
    /**
     * Disable Laravel Debugbar for the current request if it is bound in the container.
     *
     * @param Request $request Incoming HTTP request instance
     * @param Closure $next Next middleware closure
     * @return mixed Response from next middleware
     */
    public function handle(Request $request, Closure $next)
    {
        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }
        return $next($request);
    }
}
