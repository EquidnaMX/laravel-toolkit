<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Http\Middleware;

use Equidna\Toolkit\Http\Middleware\ForceJsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;

class ForceJsonResponseTest extends TestCase
{
    public function test_it_sets_accept_header_to_application_json(): void
    {
        $middleware = new ForceJsonResponse();
        $request = Request::create(
            uri: '/',
            method: 'GET',
            parameters: [],
            cookies: [],
            files: [],
            server: ['HTTP_ACCEPT' => 'text/html'],
        );

        $response = $middleware->handle(
            request: $request,
            next: fn(Request $handledRequest) => new Response(
                content: $handledRequest->header('Accept'),
            ),
        );

        $this->assertSame('application/json', $request->header('Accept'));
        $this->assertSame('application/json', $response->getContent());
    }
}
