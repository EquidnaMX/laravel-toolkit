<?php

namespace Equidna\Toolkit\Services\Responses;

use Equidna\Toolkit\Contracts\ResponseStrategyInterface;

abstract class AbstractResponseStrategy implements ResponseStrategyInterface
{
    public function __construct(protected bool $requiresHeaderAllowList = false)
    {
    }

    public function requiresHeaderAllowList(): bool
    {
        return $this->requiresHeaderAllowList;
    }
}

