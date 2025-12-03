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

/**
 * Boots and registers the Equidna Toolkit services within Laravel applications.
 */
class EquidnaLaravelToolkitServiceProvider extends ServiceProvider
{
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
    }

    /**
     * Bind the configured route detector into the container.
     */
    protected function registerRouteDetector(): void
    {
        $this->app->singleton(
            RouteDetectorInterface::class,
            function ($app) {
                $config = config('equidna.route', []);

                $detectorClass = $config['detector'] ?? null;

                if (empty($detectorClass)) {
                    $detectorClass = ConfigurableRouteDetector::class;
                    config(['equidna.route.detector' => $detectorClass]);
                }

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
        $this->app->singleton(
            RequestResolverInterface::class,
            fn($app) => $app->make(
                $this->resolveRequestResolverClass(),
            ),
        );
    }

    /**
     * Resolve the request resolver class, injecting defaults into config when not provided.
     */
    protected function resolveRequestResolverClass(): string
    {
        $resolverClass = config('equidna.route.request_resolver');

        if (empty($resolverClass)) {
            $resolverClass = LaravelRequestResolver::class;
            config(['equidna.route.request_resolver' => $resolverClass]);
        }

        return $resolverClass;
    }

    /**
     * Bind the configured pagination strategy into the container.
     */
    protected function registerPaginationStrategy(): void
    {
        $this->app->singleton(
            PaginationStrategyInterface::class,
            function ($app) {
                $strategyClass = config('equidna.paginator.strategy');

                if (empty($strategyClass)) {
                    $strategyClass = DefaultPaginationStrategy::class;
                    config(['equidna.paginator.strategy' => $strategyClass]);
                }

                return $app->make($strategyClass);
            },
        );
    }

    /**
     * Register response strategy bindings for each execution context.
     */
    protected function registerResponseStrategies(): void
    {
        $strategies = config('equidna.responses.strategies', []);

        $strategies = array_merge(
            [
                'console' => ConsoleResponseStrategy::class,
                'json' => JsonResponseStrategy::class,
                'redirect' => RedirectResponseStrategy::class,
            ],
            array_filter($strategies),
        );

        config(['equidna.responses.strategies' => $strategies]);

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
}
