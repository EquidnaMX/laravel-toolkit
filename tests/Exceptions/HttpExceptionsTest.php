<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Exceptions;

use Equidna\Toolkit\Exceptions\BadRequestException;
use Equidna\Toolkit\Exceptions\ConflictException;
use Equidna\Toolkit\Exceptions\ForbiddenException;
use Equidna\Toolkit\Exceptions\NotAcceptableException;
use Equidna\Toolkit\Exceptions\NotFoundException;
use Equidna\Toolkit\Exceptions\TooManyRequestsException;
use Equidna\Toolkit\Exceptions\UnauthorizedException;
use Equidna\Toolkit\Exceptions\UnprocessableEntityException;
use Equidna\Toolkit\Helpers\ResponseHelper;
use Equidna\Toolkit\Tests\Support\FakeRouteDetector;
use Equidna\Toolkit\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;

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

    /**
     * @dataProvider httpExceptionProvider
     */
    #[DataProvider('httpExceptionProvider')]
    public function test_render_never_returns_null_errors_for_http_exceptions(
        string $exceptionClass,
        int $expectedStatus,
        string $message,
    ): void {
        $this->app->setRunningInConsole(false);
        $this->app->instance('request', Request::create('/api', 'GET'));
        $this->app->singleton(
            \Equidna\Toolkit\Contracts\RouteDetectorInterface::class,
            fn() => new FakeRouteDetector(wantsJson: true),
        );

        $response = (new $exceptionClass($message))->render();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($expectedStatus, $response->status());
        $this->assertSame([$message], $response->getData(true)['errors']);
    }

    /**
     * @return array<string, array{0: class-string, 1: int, 2: string}>
     */
    public static function httpExceptionProvider(): array
    {
        return [
            'bad request' => [BadRequestException::class, ResponseHelper::HTTP_BAD_REQUEST, 'Bad request'],
            'unauthorized' => [UnauthorizedException::class, ResponseHelper::HTTP_UNAUTHORIZED, 'Unauthorized'],
            'forbidden' => [ForbiddenException::class, ResponseHelper::HTTP_FORBIDDEN, 'Forbidden'],
            'not found' => [NotFoundException::class, ResponseHelper::HTTP_NOT_FOUND, 'Not found'],
            'not acceptable' => [NotAcceptableException::class, ResponseHelper::HTTP_NOT_ACCEPTABLE, 'Not acceptable'],
            'conflict' => [ConflictException::class, ResponseHelper::HTTP_CONFLICT, 'Conflict'],
            'unprocessable entity' => [UnprocessableEntityException::class, ResponseHelper::HTTP_UNPROCESSABLE_ENTITY, 'Validation failed'],
            'too many requests' => [TooManyRequestsException::class, ResponseHelper::HTTP_TOO_MANY_REQUESTS, 'Too many requests'],
        ];
    }
}
