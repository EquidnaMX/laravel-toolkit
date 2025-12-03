<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Support;

class FakeUrlGenerator
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
}
