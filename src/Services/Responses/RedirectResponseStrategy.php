<?php

namespace Equidna\Toolkit\Services\Responses;

use Equidna\Toolkit\Contracts\ResponseStrategyInterface;
use Illuminate\Http\RedirectResponse;
use Stringable;

class RedirectResponseStrategy implements ResponseStrategyInterface
{
    public function respond(
        int $status,
        string $message,
        array $errors = [],
        mixed $data = null,
        array $headers = [],
        ?string $forwardUrl = null
    ): RedirectResponse {
        return redirect(
            to: $forwardUrl ?? url()->previous(),
            headers: $this->sanitizeHeaders($headers),
        )->with(
            [
                'status'  => $status,
                'message' => $message,
                'errors'  => $this->sanitizeErrors($errors),
                'data'    => $data,
            ]
        )->withErrors($this->sanitizeErrors($errors))
            ->withInput();
    }

    private function sanitizeHeaders(array $headers): array
    {
        $allowed = array_map('strtolower', config('equidna.responses.redirect_allowed_headers', []));

        return collect($headers)
            ->filter(fn($value, $key) => is_string($key) && is_string($value))
            ->filter(function ($value, $key) use ($allowed) {
                if (empty($allowed)) {
                    return false;
                }

                return in_array(strtolower((string) $key), $allowed, true);
            })
            ->all();
    }

    private function sanitizeErrors(array $errors): array
    {
        $allowedKeys = config('equidna.responses.redirect_allowed_error_fields', []);

        $filterError = function ($value) {
            if (is_scalar($value) || $value instanceof Stringable || (is_object($value) && method_exists($value, '__toString'))) {
                return (string) $value;
            }

            if (is_array($value)) {
                return array_values(array_map(
                    fn($item) => (string) $item,
                    array_filter($value, fn($item) => is_scalar($item) || (is_object($item) && method_exists($item, '__toString')))
                ));
            }

            return null;
        };

        $sanitized = [];

        foreach ($errors as $key => $value) {
            if (!empty($allowedKeys) && !in_array($key, $allowedKeys, true)) {
                continue;
            }

            $cleaned = $filterError($value);

            if ($cleaned !== null) {
                $sanitized[$key] = $cleaned;
            }
        }

        return $sanitized;
    }
}

