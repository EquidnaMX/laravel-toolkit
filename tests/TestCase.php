<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests;

use Equidna\Toolkit\Contracts\PaginationStrategyInterface;
use Equidna\Toolkit\Contracts\RequestResolverInterface;
use Equidna\Toolkit\Contracts\RouteDetectorInterface;
use Equidna\Toolkit\Helpers\Detectors\ConfigurableRouteDetector;
use Equidna\Toolkit\Helpers\Request\LaravelRequestResolver;
use Equidna\Toolkit\Services\Pagination\DefaultPaginationStrategy;
use Equidna\Toolkit\Tests\Support\FakeApplication;
use Equidna\Toolkit\Tests\Support\FakeRedirector;
use Equidna\Toolkit\Tests\Support\FakeResponseFactory;
use Equidna\Toolkit\Tests\Support\FakeUrlGenerator;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionClass;

abstract class TestCase extends BaseTestCase
{
    protected FakeApplication $app;

    protected ConfigRepository $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new FakeApplication();
        Container::setInstance($this->app);

        $this->config = new ConfigRepository([
            'app' => ['debug' => false],
            'equidna' => require __DIR__ . '/../src/config/equidna.php',
        ]);

        $this->app->instance('config', $this->config);
        $this->app->alias('config', ConfigRepository::class);

        $this->bindRoutingUtilities();

        $this->app->singleton(
            RouteDetectorInterface::class,
            fn() => new ConfigurableRouteDetector($this->config->get('equidna.route')),
        );

        $this->app->singleton(
            RequestResolverInterface::class,
            fn($app) => new LaravelRequestResolver($app),
        );

        $this->app->singleton(
            PaginationStrategyInterface::class,
            DefaultPaginationStrategy::class,
        );
    }

    protected function tearDown(): void
    {
        $this->resetRouteHelperState();
        Container::setInstance(null);

        parent::tearDown();
    }

    protected function bindRoutingUtilities(?FakeUrlGenerator $url = null): void
    {
        $urlGenerator = $url ?? new FakeUrlGenerator();

        $this->app->instance('url', $urlGenerator);
        $this->app->instance('redirect', new FakeRedirector($urlGenerator));
        $this->app->instance('Illuminate\\Contracts\\Routing\\ResponseFactory', new FakeResponseFactory());
        $this->app->alias('Illuminate\\Contracts\\Routing\\ResponseFactory', 'response');
    }

    protected function resetRouteHelperState(): void
    {
        $reflection = new ReflectionClass('Equidna\\Toolkit\\Helpers\\RouteHelper');

        foreach (['detector', 'requestResolver'] as $property) {
            $prop = $reflection->getProperty($property);
            $prop->setAccessible(true);
            $prop->setValue(null, null);
        }
    }
}
