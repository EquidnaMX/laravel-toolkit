<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Helpers;

use Equidna\Toolkit\Helpers\ResponseHelper;
use Equidna\Toolkit\Tests\Support\FakeRedirectResponse;
use Equidna\Toolkit\Tests\Support\FakeRouteDetector;
use Equidna\Toolkit\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponseHelperTest extends TestCase
{
    public function test_console_strategy_returns_text_block(): void
    {
        $this->app->setRunningInConsole(true);

        $response = ResponseHelper::success('Console hello', ['foo' => 'bar']);

        $this->assertIsString($response);
        $this->assertStringContainsString('[200] Console hello', $response);
        $this->assertStringContainsString('Data:', $response);
    }

    public function test_json_strategy_honors_status_and_payload(): void
    {
        $this->app->setRunningInConsole(false);
        $this->app->instance('request', Request::create('/api/example', 'POST'));
        $this->app->singleton(
            \Equidna\Toolkit\Contracts\RouteDetectorInterface::class,
            fn() => new FakeRouteDetector(api: true, wantsJson: true),
        );

        $response = ResponseHelper::created('Created', ['id' => 10]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(ResponseHelper::HTTP_CREATED, $response->status());
        $this->assertSame([
            'status' => ResponseHelper::HTTP_CREATED,
            'message' => 'Created',
            'data' => ['id' => 10],
        ], $response->getData(true));
    }

    public function test_redirect_strategy_filters_headers(): void
    {
        $this->app->setRunningInConsole(false);
        $request = Request::create('/web/example', 'GET');
        $this->app->instance('request', $request);
        $this->config->set('equidna.responses.allowed_headers', ['Retry-After']);
        $this->app->singleton(
            \Equidna\Toolkit\Contracts\RouteDetectorInterface::class,
            fn() => new FakeRouteDetector(),
        );

        $response = ResponseHelper::forbidden(
            message: 'Blocked',
            errors: [
                'message' => 'Not allowed',
                'debug' => 'trace',
            ],
            headers: [
                'Retry-After' => '30',
                'X-Debug' => 'secret',
            ],
            forward_url: 'https://example.com/login',
        );

        $this->assertInstanceOf(FakeRedirectResponse::class, $response);
        $this->assertSame('https://example.com/login', $response->targetUrl);
        $this->assertSame([
            'status' => ResponseHelper::HTTP_FORBIDDEN,
            'message' => 'Blocked',
            'errors' => [
                'message' => 'Not allowed',
                'debug' => 'trace',
            ],
            'data' => null,
        ], $response->session);
        $this->assertSame([
            'message' => 'Not allowed',
            'debug' => 'trace',
        ], $response->errors);
        $this->assertTrue($response->input);
        $this->assertSame(['Retry-After' => '30'], $response->headers);
    }

    public function test_handle_exception_maps_to_configured_status(): void
    {
        $this->app->setRunningInConsole(false);
        $this->app->instance('request', Request::create('/api/resource', 'GET'));
        $this->app->singleton(
            \Equidna\Toolkit\Contracts\RouteDetectorInterface::class,
            fn() => new FakeRouteDetector(wantsJson: true),
        );

        $response = ResponseHelper::handleException(new \Exception('Missing', ResponseHelper::HTTP_NOT_FOUND));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(ResponseHelper::HTTP_NOT_FOUND, $response->status());
        $this->assertSame([
            'status' => ResponseHelper::HTTP_NOT_FOUND,
            'message' => 'Missing',
            'errors' => [],
        ], $response->getData(true));
    }

    public function test_internal_error_hides_message_when_debug_disabled(): void
    {
        $this->app->setRunningInConsole(true);
        $this->config->set('app.debug', false);

        $response = ResponseHelper::error('Sensitive details', ['trace' => 'stack']);

        $this->assertStringContainsString('[500] An unexpected error occurred.', $response);
        $this->assertStringNotContainsString('Sensitive details', $response);
        $this->assertStringNotContainsString('trace', $response);
    }
}
