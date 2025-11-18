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
