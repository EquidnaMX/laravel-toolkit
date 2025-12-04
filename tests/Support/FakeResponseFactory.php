<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Support;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;

class FakeResponseFactory implements ResponseFactory
{
    public function make($content = '', $status = 200, array $headers = [])
    {
        return new JsonResponse($content, $status, $headers);
    }

    public function noContent($status = 204, array $headers = [])
    {
        return new JsonResponse(null, $status, $headers);
    }

    public function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        return new JsonResponse($data, $status, $headers, $options);
    }

    public function jsonp($callback, $data = [], $status = 200, array $headers = [], $options = 0)
    {
        return new JsonResponse(['callback' => $callback, 'data' => $data], $status, $headers, $options);
    }

    public function view($view = null, $data = [], $status = 200, array $headers = [])
    {
        return new JsonResponse(['view' => $view, 'data' => $data], $status, $headers);
    }

    public function stream($callback, $status = 200, array $headers = [])
    {
        return $this->json(['streamed' => true], $status, $headers);
    }

    public function streamDownload($callback, $name = null, array $headers = [], $disposition = 'attachment')
    {
        return $this->json(['download' => $name], 200, $headers);
    }

    public function download($file, $name = null, array $headers = [], $disposition = 'attachment')
    {
        return $this->json(['download' => $name ?? $file], 200, $headers);
    }

    public function file($file, array $headers = [])
    {
        return $this->json(['file' => $file], 200, $headers);
    }

    public function redirectTo($path, $status = 302, $headers = [], $secure = null)
    {
        return $this->json(['redirect' => $path, 'secure' => $secure], $status, (array) $headers);
    }

    public function redirectToRoute($route, $parameters = [], $status = 302, $headers = [])
    {
        return $this->json(['route' => $route, 'parameters' => $parameters], $status, $headers);
    }

    public function redirectToAction($action, $parameters = [], $status = 302, $headers = [])
    {
        return $this->json(['action' => $action, 'parameters' => $parameters], $status, $headers);
    }

    public function redirectGuest($path, $status = 302, $headers = [], $secure = null)
    {
        return $this->json(['redirect' => $path, 'secure' => $secure, 'guest' => true], $status, (array) $headers);
    }

    public function redirectToIntended($default = '/', $status = 302, $headers = [], $secure = null)
    {
        return $this->json(['redirect' => $default, 'secure' => $secure, 'intended' => true], $status, (array) $headers);
    }
}
