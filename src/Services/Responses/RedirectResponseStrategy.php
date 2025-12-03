<?php

namespace Equidna\Toolkit\Services\Responses;

use Equidna\Toolkit\Contracts\ResponseStrategyInterface;
use Illuminate\Http\RedirectResponse;

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
                'errors'  => $errors,
                'data'    => $data,
            ]
        )->withErrors($errors)
            ->withInput();
    }

    public function requiresHeaderAllowList(): bool
    {
        return true;        
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
}

