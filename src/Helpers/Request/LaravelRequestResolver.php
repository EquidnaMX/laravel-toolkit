<?php

namespace Equidna\Toolkit\Helpers\Request;

use Equidna\Toolkit\Contracts\RequestResolverInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;

/**
 * Resolves the current request from the Laravel container when available.
 */
class LaravelRequestResolver implements RequestResolverInterface
{
    public function __construct(private Container $container)
    {
    }

    public function resolve(): ?Request
    {
        if (!$this->container->bound('request')) {
            return null;
        }

        try {
            $request = $this->container->make('request');
        } catch (BindingResolutionException) {
            return null;
        }

        return $request instanceof Request ? $request : null;
    }
}
