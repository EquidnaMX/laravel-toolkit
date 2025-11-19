<?php

/**
 * Forces downstream responses to negotiate JSON by setting the Accept header.
 * PHP 8.0+
 * @package   Equidna\Toolkit\Http\Middleware
 * @author    Gabriel Ruelas <gruelasjr@gmail.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://laravel.com/docs/12.x/middleware#middleware-and-responses Documentation
 */

namespace Equidna\Toolkit\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Closure;

/**
 * Ensures every request downstream of the middleware expects JSON responses.
 */
class ForceJsonResponse
{
    /**
     * Handles an incoming request while enforcing JSON response negotiation.
     *
     * @param  Request $request Incoming HTTP request.
     * @param  Closure $next    Next middleware in the pipeline.
     * @return Response         Response returned by the next middleware.
     */
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
