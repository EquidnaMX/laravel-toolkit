<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Support;

use Illuminate\Contracts\Routing\UrlGenerator;

class FakeUrlGenerator implements UrlGenerator
{
    public function __construct(
        private string $current = 'http://localhost/current',
        private string $previous = 'http://localhost/previous'
    ) {
    }

    public function current(): string
    {
        return $this->current;
    }

    public function previous($fallback = false): string
    {
        return $this->previous;
    }

    public function to($path, $extra = [], $secure = null): string
    {
        return (string) $path;
    }

    public function secure($path, $parameters = []): string
    {
        return (string) $path;
    }

    public function asset($path, $secure = null): string
    {
        return (string) $path;
    }

    public function route($name, $parameters = [], $absolute = true): string
    {
        return (string) $name;
    }

    public function signedRoute($name, $parameters = [], $expiration = null, $absolute = true): string
    {
        return (string) $name;
    }

    public function temporarySignedRoute($name, $expiration, $parameters = [], $absolute = true): string
    {
        return (string) $name;
    }

    public function query($path, $query = [], $extra = [], $secure = null): string
    {
        return (string) $path;
    }

    public function action($action, $parameters = [], $absolute = true): string
    {
        return is_array($action) ? implode('@', $action) : (string) $action;
    }

    public function getRootControllerNamespace(): string
    {
        return 'App\\Http\\Controllers';
    }

    public function setRootControllerNamespace($rootNamespace): static
    {
        return $this;
    }
}
