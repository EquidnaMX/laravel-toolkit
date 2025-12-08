<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Support;

use Illuminate\Container\Container;

class FakeApplication extends Container
{
    private bool $runningInConsole = false;

    public function setRunningInConsole(bool $runningInConsole): void
    {
        $this->runningInConsole = $runningInConsole;
    }

    public function runningInConsole(): bool
    {
        return $this->runningInConsole;
    }
}
