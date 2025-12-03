<?php

namespace Equidna\Toolkit\Services\Responses;

use Equidna\Toolkit\Contracts\ResponseStrategyInterface;

class ConsoleResponseStrategy implements ResponseStrategyInterface
{
    public function respond(
        int $status,
        string $message,
        array $errors = [],
        mixed $data = null,
        array $headers = [],
        ?string $forwardUrl = null
    ): string {
        return $message;
    }
}

