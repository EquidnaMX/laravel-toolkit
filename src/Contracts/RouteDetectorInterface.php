<?php

namespace Equidna\Toolkit\Contracts;

use Illuminate\Http\Request;

/**
 * Defines route context detection capabilities.
 */
interface RouteDetectorInterface
{
    /**
     * Determine whether the request targets API routes.
     */
    public function isApi(Request $request): bool;

    /**
     * Determine whether the request targets hook routes.
     */
    public function isHook(Request $request): bool;

    /**
     * Determine whether the request targets IoT routes.
     */
    public function isIoT(Request $request): bool;

    /**
     * Determine whether the request expects a JSON payload.
     */
    public function wantsJson(Request $request): bool;
}
