<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Support;

use Illuminate\Http\RedirectResponse;

class FakeRedirector
{
    public function __construct(private FakeUrlGenerator $url)
    {
    }

    public function to($path, $status = 302, $headers = [], $secure = null): FakeRedirectResponse
    {
        return new FakeRedirectResponse((string) $path, $status, (array) $headers);
    }

    public function previous($status = 302, $headers = [], $fallback = false): FakeRedirectResponse
    {
        return $this->to($this->url->previous($fallback), $status, $headers);
    }
}

class FakeRedirectResponse extends RedirectResponse
{
    public $session = [];
    public $errors = [];
    public $input = false;

    public function __construct(string $targetUrl, int $status = 302, array $headers = [])
    {
        parent::__construct($targetUrl, $status, $headers);
    }

    public function with($key, $value = null): self
    {
        $data = is_array($key) ? $key : [$key => $value];

        $this->session = array_merge($this->session, $data);

        return $this;
    }

    public function withErrors($provider, $key = 'default'): self
    {
        $errors = is_array($provider) ? $provider : [$provider];
        $this->errors = $errors;

        return $this;
    }

    public function withInput($input = true, $value = null): self
    {
        $this->input = (bool) ($value ?? $input);

        return $this;
    }
}
