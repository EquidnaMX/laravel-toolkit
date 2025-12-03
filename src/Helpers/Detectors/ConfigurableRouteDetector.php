<?php

namespace Equidna\Toolkit\Helpers\Detectors;

use Equidna\Toolkit\Contracts\RouteDetectorInterface;
use Illuminate\Http\Request;

/**
 * Detects route contexts based on configurable matchers.
 */
class ConfigurableRouteDetector implements RouteDetectorInterface
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $matchers;

    /**
     * @param array<string, array<int, string>> $matchers
     */
    public function __construct(array $matchers = [])
    {
        $this->matchers = $matchers;
    }

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

    private function matches(Request $request, string $key): bool
    {
        $patterns = $this->matchers[$key] ?? [];

        if (empty($patterns)) {
            return false;
        }

        return $request->is($patterns);
    }
}
