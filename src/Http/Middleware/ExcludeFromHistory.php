<?php

/**
 * Removes the `_previous` session entry so sensitive routes stay out of history.
 * PHP 8.0+
 * @package   Equidna\Toolkit\Http\Middleware
 * @author    Gabriel Ruelas <gruelasjr@gmail.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://laravel.com/docs/12.x/middleware#middleware-and-responses Documentation
 */

namespace Equidna\Toolkit\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Exception;

/**
 * Ensures browser history ignores the current request by clearing session state.
 */
class ExcludeFromHistory
{
    /**
     * Clears the `_previous` session value before passing the request downstream.
     *
     * @param  Request $request Incoming HTTP request.
     * @param  Closure $next    Next middleware in the pipeline.
     * @return mixed            Response from the following middleware.
     */
    public function handle(
        Request $request,
        Closure $next,
    ): mixed {
        try {
            $request->session()->forget('_previous');
        } catch (Exception) {
            // Intentionally ignored when the session store is unavailable.
        }

        return $next($request);
    }
}
