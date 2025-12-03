<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Support;

class FakeRedirector
{
    public function __construct(private FakeUrlGenerator $url)
    {
    }

    public function to($path, $status = 302, $headers = [], $secure = null): FakeRedirectResponse
    {
        return new FakeRedirectResponse((string) $path, (array) $headers);
    }

    public function previous($status = 302, $headers = [], $fallback = false): FakeRedirectResponse
    {
        return $this->to($this->url->previous($fallback), $status, $headers);
    }
}

class FakeRedirectResponse
{
    public array $session = [];
    public array $errors = [];
    public bool $input = false;

    public function __construct(public string $targetUrl, public array $headers = [])
    {
    }

    public function with(array $data): self
    {
        $this->session = $data;

        return $this;
    }

    public function withErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function withInput($input = true): self
    {
        $this->input = (bool) $input;

        return $this;
    }
}
