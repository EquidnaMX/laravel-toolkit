<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Helpers;

use Equidna\Toolkit\Contracts\RequestResolverInterface;
use Equidna\Toolkit\Contracts\RouteDetectorInterface;
use Equidna\Toolkit\Helpers\RouteHelper;
use Equidna\Toolkit\Helpers\Request\LaravelRequestResolver;
use Equidna\Toolkit\Tests\Support\FakeApplication;
use Equidna\Toolkit\Tests\Support\FakeRouteDetector;
use Equidna\Toolkit\Tests\TestCase;
use Illuminate\Container\Container;
use Illuminate\Http\Request;

class RouteHelperTest extends TestCase
{
    public function test_detects_console_mode(): void
    {
        $this->app->setRunningInConsole(true);

        $this->assertTrue(RouteHelper::isConsole());
        $this->assertFalse(RouteHelper::isApi());
        $this->assertFalse(RouteHelper::isHook());
        $this->assertFalse(RouteHelper::isIoT());
        $this->assertFalse(RouteHelper::isWeb());
        $this->assertFalse(RouteHelper::isExpression('api/*'));
        $this->assertFalse(RouteHelper::wantsJson());
        $this->assertNull(RouteHelper::getMethod());
        $this->assertFalse(RouteHelper::isMethod('GET'));
        $this->assertNull(RouteHelper::getRouteName());
        $this->assertFalse(RouteHelper::routeContains('users'));
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

    public function test_resolves_detector_and_request_resolver_from_current_container(): void
    {
        $this->app->setRunningInConsole(false);
        $this->app->instance('request', Request::create('/api/products', 'PUT'));
        $this->app->singleton(RouteDetectorInterface::class, fn() => new FakeRouteDetector(api: true, wantsJson: true));

        $this->assertTrue(RouteHelper::isApi());
        $this->assertTrue(RouteHelper::wantsJson());

        $nextApp = new FakeApplication();
        $nextApp->setRunningInConsole(false);
        $nextApp->instance('request', Request::create('/web/products', 'GET'));
        $nextApp->singleton(RouteDetectorInterface::class, fn() => new FakeRouteDetector());
        $nextApp->singleton(
            RequestResolverInterface::class,
            fn($app) => new LaravelRequestResolver($app),
        );

        Container::setInstance($nextApp);

        $this->assertFalse(RouteHelper::isApi());
        $this->assertFalse(RouteHelper::wantsJson());
        $this->assertSame('GET', RouteHelper::getMethod());
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

    public function test_route_helpers_resolve_name_and_expressions(): void
    {
        $this->app->setRunningInConsole(false);

        $request = Request::create('/admin/users', 'GET');
        $request->setRouteResolver(fn() => new class () {
            public function getName(): string
            {
                return 'admin.users.index';
            }
        });

        $this->app->instance('request', $request);
        $this->app->singleton(RouteDetectorInterface::class, fn() => new FakeRouteDetector());

        $this->assertTrue(RouteHelper::isRouteName('admin.users.index'));
        $this->assertTrue(RouteHelper::routeContains('users'));
        $this->assertTrue(RouteHelper::isExpression('admin/*'));
        $this->assertSame('GET', RouteHelper::getMethod());
    }

    public function test_http_helpers_use_bound_request_even_when_application_runs_in_console(): void
    {
        $this->app->setRunningInConsole(true);

        $request = Request::create('/api/products', 'PUT');
        $request->setRouteResolver(fn() => new class () {
            public function getName(): string
            {
                return 'api.products.update';
            }
        });

        $this->app->instance('request', $request);

        $this->assertTrue(RouteHelper::isConsole());
        $this->assertTrue(RouteHelper::isApi());
        $this->assertTrue(RouteHelper::wantsJson());
        $this->assertTrue(RouteHelper::isExpression('api/*'));
        $this->assertSame('PUT', RouteHelper::getMethod());
        $this->assertTrue(RouteHelper::isMethod('PUT'));
        $this->assertSame('api.products.update', RouteHelper::getRouteName());
        $this->assertTrue(RouteHelper::routeContains('products'));
        $this->assertFalse(RouteHelper::isWeb());
    }

    public function test_route_matchers_include_roots_and_avoid_api_prefix_false_positives(): void
    {
        $this->app->setRunningInConsole(false);

        foreach (['/api', '/api/products', '/foo-api', '/foo-api/users'] as $path) {
            $this->app->instance('request', Request::create($path, 'GET'));

            $this->assertTrue(RouteHelper::isApi(), "{$path} should be detected as API.");
            $this->assertTrue(RouteHelper::wantsJson(), "{$path} should want JSON.");
        }

        foreach (['/apiary', '/apiproducts', '/foo-apiary'] as $path) {
            $this->app->instance('request', Request::create($path, 'GET'));

            $this->assertFalse(RouteHelper::isApi(), "{$path} should not be detected as API.");
            $this->assertFalse(RouteHelper::wantsJson(), "{$path} should not want JSON by route matcher.");
        }
    }

    public function test_hook_and_iot_matchers_include_root_paths(): void
    {
        $this->app->setRunningInConsole(false);

        foreach (['/hooks', '/hooks/webhook'] as $path) {
            $this->app->instance('request', Request::create($path, 'POST'));

            $this->assertTrue(RouteHelper::isHook(), "{$path} should be detected as hook.");
            $this->assertTrue(RouteHelper::wantsJson(), "{$path} should want JSON.");
            $this->assertFalse(RouteHelper::isWeb(), "{$path} should not be detected as web.");
        }

        foreach (['/iot', '/iot/devices'] as $path) {
            $this->app->instance('request', Request::create($path, 'POST'));

            $this->assertTrue(RouteHelper::isIoT(), "{$path} should be detected as IoT.");
            $this->assertTrue(RouteHelper::wantsJson(), "{$path} should want JSON.");
            $this->assertFalse(RouteHelper::isWeb(), "{$path} should not be detected as web.");
        }
    }
}
