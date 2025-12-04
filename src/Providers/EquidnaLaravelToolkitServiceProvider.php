<?php

/**
 * Registers Equidna Toolkit bindings and publishable resources for Laravel hosts.
 *
 * PHP 8.0+
 *
 * @package   Equidna\Toolkit\Providers
 * @author    Gabriel Ruelas <gruelasjr@gmail.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://github.com/EquidnaMX/laravel-toolkit Documentation
 */

namespace Equidna\Toolkit\Providers;

use Equidna\Toolkit\Exceptions\BadRequestException;
use Equidna\Toolkit\Exceptions\ConflictException;
use Equidna\Toolkit\Exceptions\ForbiddenException;
use Equidna\Toolkit\Contracts\PaginationStrategyInterface;
use Equidna\Toolkit\Contracts\RequestResolverInterface;
use Equidna\Toolkit\Contracts\RouteDetectorInterface;
use Equidna\Toolkit\Contracts\ResponseStrategyInterface;
use Equidna\Toolkit\Helpers\Detectors\ConfigurableRouteDetector;
use Equidna\Toolkit\Helpers\Request\LaravelRequestResolver;
use Equidna\Toolkit\Services\Pagination\DefaultPaginationStrategy;
use Equidna\Toolkit\Services\Responses\ConsoleResponseStrategy;
use Equidna\Toolkit\Services\Responses\JsonResponseStrategy;
use Equidna\Toolkit\Services\Responses\RedirectResponseStrategy;
use Equidna\Toolkit\Exceptions\NotAcceptableException;
use Equidna\Toolkit\Exceptions\NotFoundException;
use Equidna\Toolkit\Exceptions\TooManyRequestsException;
use Equidna\Toolkit\Exceptions\UnauthorizedException;
use Equidna\Toolkit\Exceptions\UnprocessableEntityException;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

/**
 * Boots and registers the Equidna Toolkit services within Laravel applications.
 */
class EquidnaLaravelToolkitServiceProvider extends ServiceProvider
{
    /**
     * Resolved response strategy classes for validation.
     *
     * @var array<string, class-string<ResponseStrategyInterface>>
     */
    private array $responseStrategies = [];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerRequestResolver();
        $this->registerRouteDetector();
        $this->registerPaginationStrategy();
        $this->registerResponseStrategies();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishConfig();
        $this->registerExceptionHandlers();
        $this->validateConfiguration();
    }

    /**
     * Bind the configured route detector into the container.
     */
    protected function registerRouteDetector(): void
    {
        if ($this->app->bound(RouteDetectorInterface::class)) {
            return;
        }

        $this->app->singleton(
            RouteDetectorInterface::class,
            function ($app) {
                $config = config('equidna.route', []);

                $detectorClass = $config['detector'] ?? ConfigurableRouteDetector::class;

                return $app->make(
                    $detectorClass,
                    [
                        'matchers' => [
                            'api_matchers' => $config['api_matchers'] ?? [],
                            'hook_matchers' => $config['hook_matchers'] ?? [],
                            'iot_matchers' => $config['iot_matchers'] ?? [],
                            'json_matchers' => $config['json_matchers'] ?? [],
                        ],
                    ],
                );
            },
        );
    }

    /**
     * Bind the configured request resolver into the container.
     */
    protected function registerRequestResolver(): void
    {
        if ($this->app->bound(RequestResolverInterface::class)) {
            return;
        }

        $this->app->singleton(
            RequestResolverInterface::class,
            function ($app) {
                $resolverClass = config('equidna.route.request_resolver') ?: LaravelRequestResolver::class;

                return $app->make($resolverClass);
            },
        );
    }

    /**
     * Bind the configured pagination strategy into the container.
     */
    protected function registerPaginationStrategy(): void
    {
        if ($this->app->bound(PaginationStrategyInterface::class)) {
            return;
        }

        $this->app->singleton(
            PaginationStrategyInterface::class,
            function ($app) {
                $strategyClass = config('equidna.paginator.strategy') ?: DefaultPaginationStrategy::class;

                return $app->make($strategyClass);
            },
        );
    }

    /**
     * Register response strategy bindings for each execution context.
     */
    protected function registerResponseStrategies(): void
    {
        $strategies = array_replace(
            [
                'console' => ConsoleResponseStrategy::class,
                'json' => JsonResponseStrategy::class,
                'redirect' => RedirectResponseStrategy::class,
            ],
            array_filter(config('equidna.responses.strategies', [])),
        );

        $this->responseStrategies = $strategies;

        foreach ($strategies as $key => $class) {
            $this->app->singleton(
                "equidna.responses.{$key}_strategy",
                fn($app) => $app->make($class),
            );
        }
    }

    /**
     * Register custom exception handlers for the package.
     *
     * @return void
     */
    protected function registerExceptionHandlers(): void
    {
        $exceptions = [
            BadRequestException::class,
            UnauthorizedException::class,
            ForbiddenException::class,
            NotFoundException::class,
            NotAcceptableException::class,
            ConflictException::class,
            UnprocessableEntityException::class,
            TooManyRequestsException::class,
        ];

        foreach ($exceptions as $exception) {
            $this->app->bind(
                $exception,
                fn() => new $exception(),
            );
        }
    }

    /**
     * Merge package configuration with the application's config.
     *
     * @return void
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__ . '/../config/equidna.php',
            key: 'equidna',
        );
    }

    /**
     * Publish the package configuration file to the application's config path.
     *
     * @return void
     */
    protected function publishConfig(): void
    {
        $this->publishes(
            paths: [
                __DIR__ . '/../config/equidna.php' => config_path('equidna.php'),
            ],
            groups: 'equidna:config',
        );
    }

    /**
     * Validate critical configuration values and fail fast when misconfigured.
     */
    protected function validateConfiguration(): void
    {
        $this->assertServiceBinding(RouteDetectorInterface::class, 'Route detector');
        $this->assertServiceBinding(RequestResolverInterface::class, 'Request resolver');
        $this->assertServiceBinding(PaginationStrategyInterface::class, 'Pagination strategy');

        foreach ($this->responseStrategies as $key => $class) {
            $this->assertClassConfig(
                $class,
                ResponseStrategyInterface::class,
                "equidna.responses.strategies.{$key}",
            );
        }
    }

    /**
     * Ensure a configured class exists and implements the expected interface.
     */
    protected function assertClassConfig(?string $class, string $interface, string $configKey): void
    {
        if (empty($class)) {
            throw new InvalidArgumentException("Configuration '{$configKey}' must specify a class implementing {$interface}.");
        }

        if (!class_exists($class)) {
            throw new InvalidArgumentException("Configuration '{$configKey}' references missing class {$class}.");
        }

        if (!is_a($class, $interface, true)) {
            throw new InvalidArgumentException("Configuration '{$configKey}' must implement {$interface}.");
        }
    }

    /**
     * Ensure a bound service resolves and satisfies the expected interface.
     */
    protected function assertServiceBinding(string $abstract, string $label): void
    {
        if (! $this->app->bound($abstract)) {
            throw new InvalidArgumentException("{$label} binding for {$abstract} is required.");
        }

        $instance = $this->app->make($abstract);

        if (! $instance instanceof $abstract) {
            throw new InvalidArgumentException("{$label} binding for {$abstract} must implement the expected interface.");
        }
    }
}
