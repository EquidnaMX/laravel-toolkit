<?php

namespace Equidna\Toolkit\Helpers\Detectors;

use Equidna\Toolkit\Contracts\RouteDetectorInterface;
use Illuminate\Http\Request;

abstract class AbstractRouteDetector implements RouteDetectorInterface
{
    /**
     * @param array<string, array<int, string>> $matchers
     */
    public function __construct(protected array $matchers = [])
    {
    }

    public function wantsJson(Request $request): bool
    {
        if ($this->isApi($request) || $this->isHook($request) || $this->isIoT($request)) {
            return true;
        }

        return $this->matches($request, 'json_matchers') || $request->expectsJson();
    }

    /**
     * @param array<int, string> $default
     */
    protected function patterns(string $key, array $default = []): array
    {
        return $this->matchers[$key] ?? $default;
    }

    protected function matches(Request $request, string $key): bool
    {
        $patterns = $this->patterns($key);

        if (empty($patterns)) {
            return false;
        }

        return $request->is($patterns);
    }
}
