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
        $sanitizedErrors = $this->sanitizeErrors($errors);

        return redirect(
            to: $forwardUrl ?? url()->previous(),
            headers: $headers,
        )->with(
            [
                'status'  => $status,
                'message' => $message,
                'errors'  => $sanitizedErrors,
                'data'    => $data,
            ]
        )->withErrors($sanitizedErrors)
            ->withInput();
    }

    public function requiresHeaderAllowList(): bool
    {
        return true;
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

