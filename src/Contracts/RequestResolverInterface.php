<?php

namespace Equidna\Toolkit\Contracts;

use Illuminate\Http\Request;

/**
 * Resolves the current HTTP request without relying on global helpers.
 */
interface RequestResolverInterface
{
    /**
     * Resolve the active request instance if available.
     */
    public function resolve(): ?Request;
}
