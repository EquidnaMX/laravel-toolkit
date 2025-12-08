<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Support;

use Equidna\Toolkit\Contracts\RouteDetectorInterface;
use Illuminate\Http\Request;

class FakeRouteDetector implements RouteDetectorInterface
{
    public function __construct(
        private bool $api = false,
        private bool $hook = false,
        private bool $iot = false,
        private bool $wantsJson = false,
    ) {
    }

    public function isApi(Request $request): bool
    {
        return $this->api;
    }

    public function isHook(Request $request): bool
    {
        return $this->hook;
    }

    public function isIoT(Request $request): bool
    {
        return $this->iot;
    }

    public function wantsJson(Request $request): bool
    {
        return $this->wantsJson;
    }
}
