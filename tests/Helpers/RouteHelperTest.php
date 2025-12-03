<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Helpers;

use Equidna\Toolkit\Contracts\RouteDetectorInterface;
use Equidna\Toolkit\Helpers\RouteHelper;
use Equidna\Toolkit\Tests\Support\FakeApplication;
use Equidna\Toolkit\Tests\Support\FakeRouteDetector;
use Equidna\Toolkit\Tests\TestCase;
use Illuminate\Http\Request;

class RouteHelperTest extends TestCase
{
    public function test_detects_console_mode(): void
    {
        $this->app->setRunningInConsole(true);

        $this->assertTrue(RouteHelper::isConsole());
        $this->assertFalse(RouteHelper::isApi());
        $this->assertNull(RouteHelper::getMethod());
    }

    public function test_detects_api_and_json_requests(): void
    {
        $this->app->setRunningInConsole(false);
        $this->app->instance('request', Request::create('/api/products', 'PUT'));
        $this->app->singleton(RouteDetectorInterface::class, fn() => new FakeRouteDetector(api: true, wantsJson: true));

        $this->assertTrue(RouteHelper::isApi());
        $this->assertTrue(RouteHelper::wantsJson());
        $this->assertSame('PUT', RouteHelper::getMethod());
    }

    public function test_detects_hook_and_iot_routes(): void
    {
        $this->app->setRunningInConsole(false);
        $this->app->instance('request', Request::create('/hooks/webhook', 'POST'));
        $this->app->singleton(RouteDetectorInterface::class, fn() => new FakeRouteDetector(hook: true, iot: true));

        $this->assertTrue(RouteHelper::isHook());
        $this->assertTrue(RouteHelper::isIoT());
        $this->assertFalse(RouteHelper::isWeb());
    }
}
