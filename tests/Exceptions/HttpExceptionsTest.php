<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Exceptions;

use Equidna\Toolkit\Exceptions\BadRequestException;
use Equidna\Toolkit\Exceptions\ForbiddenException;
use Equidna\Toolkit\Exceptions\NotFoundException;
use Equidna\Toolkit\Helpers\ResponseHelper;
use Equidna\Toolkit\Tests\Support\FakeRouteDetector;
use Equidna\Toolkit\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HttpExceptionsTest extends TestCase
{
    public function test_renders_redirect_response_for_web(): void
    {
        $this->app->setRunningInConsole(false);
        $this->app->instance('request', Request::create('/web', 'GET'));
        $this->app->singleton(
            \Equidna\Toolkit\Contracts\RouteDetectorInterface::class,
            fn() => new FakeRouteDetector(),
        );

        $response = (new ForbiddenException('Nope'))->render();

        $this->assertSame(ResponseHelper::HTTP_FORBIDDEN, $response->session['status']);
    }

    public function test_renders_json_for_api_calls(): void
    {
        $this->app->setRunningInConsole(false);
        $this->app->instance('request', Request::create('/api', 'GET'));
        $this->app->singleton(
            \Equidna\Toolkit\Contracts\RouteDetectorInterface::class,
            fn() => new FakeRouteDetector(wantsJson: true),
        );

        $response = (new NotFoundException('Missing'))->render();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(ResponseHelper::HTTP_NOT_FOUND, $response->status());
    }

    public function test_renders_text_for_console(): void
    {
        $this->app->setRunningInConsole(true);

        $response = (new BadRequestException('Console bad'))->render();

        $this->assertIsString($response);
        $this->assertStringContainsString('[400] Console bad', $response);
    }
}
