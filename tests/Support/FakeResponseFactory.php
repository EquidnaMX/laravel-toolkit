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

    public function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        return new JsonResponse($data, $status, $headers, $options);
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
}
