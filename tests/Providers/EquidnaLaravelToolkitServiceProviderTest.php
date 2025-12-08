<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Providers;

use Equidna\Toolkit\Providers\EquidnaLaravelToolkitServiceProvider;
use Equidna\Toolkit\Tests\TestCase;

class EquidnaLaravelToolkitServiceProviderTest extends TestCase
{
    public function test_registers_default_response_strategies_and_validates(): void
    {
        $provider = new EquidnaLaravelToolkitServiceProvider($this->app);

        // Use the provider defaults to register strategies and then validate bindings.
        $this->invokeProtected($provider, 'registerResponseStrategies');
        $this->invokeProtected($provider, 'validateConfiguration');

        $this->assertTrue($this->app->bound('equidna.responses.json_strategy'));
        $this->assertTrue($this->app->bound('equidna.responses.redirect_strategy'));
        $this->assertTrue($this->app->bound('equidna.responses.console_strategy'));
    }

    private function invokeProtected(object $instance, string $method): mixed
    {
        $reflection = new \ReflectionClass($instance);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        return $target->invoke($instance);
    }
}
