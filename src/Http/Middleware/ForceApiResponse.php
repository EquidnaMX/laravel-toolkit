<?php

/**
 * Forces legacy API consumers to negotiate JSON responses via the Accept header.
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
 * @deprecated 1.0.0 Use ForceJsonResponse instead.
 */
class ForceApiResponse
{
    /**
     * Handles an incoming request and enforces `Accept: application/json`.
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
