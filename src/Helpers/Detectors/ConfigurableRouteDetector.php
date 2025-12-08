<?php

namespace Equidna\Toolkit\Helpers\Detectors;

use Illuminate\Http\Request;

/**
 * Detects route contexts based on configurable matchers.
 */
class ConfigurableRouteDetector extends AbstractRouteDetector
{
    public function isApi(Request $request): bool
    {
        return $this->matches($request, 'api_matchers');
    }

    public function isHook(Request $request): bool
    {
        return $this->matches($request, 'hook_matchers');
    }

    public function isIoT(Request $request): bool
    {
        return $this->matches($request, 'iot_matchers');
    }

    public function wantsJson(Request $request): bool
    {
        if ($this->isApi($request) || $this->isHook($request) || $this->isIoT($request)) {
            return true;
        }

        return $this->matches($request, 'json_matchers') || $request->expectsJson();
    }
}
