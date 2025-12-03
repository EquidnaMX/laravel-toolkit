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
        $lines = ["[{$status}] {$message}"];

        if (!empty($errors)) {
            $lines[] = 'Errors:';
            $lines[] = $this->stringifyPayload($errors);
        }

        if ($data !== null) {
            $lines[] = 'Data:';
            $lines[] = $this->stringifyPayload($data);
        }

        if (!empty($headers)) {
            $lines[] = 'Headers:';
            $lines[] = $this->stringifyPayload($headers);
        }

        if ($forwardUrl !== null) {
            $lines[] = "Forward: {$forwardUrl}";
        }

        return implode(PHP_EOL, $lines);
    }

    private function stringifyPayload(mixed $payload): string
    {
        if (is_scalar($payload)) {
            return (string) $payload;
        }

        $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $encoded === false ? '[unserializable payload]' : $encoded;
    }
}

