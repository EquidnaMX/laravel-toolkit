# Deployment Instructions

This is a Laravel package, not a standalone application. Deploy it as part of your host Laravel project.

## System Requirements
- PHP: ^8.2
- Laravel: ^11.21 or ^12.0
- Extensions: standard Laravel stack (mbstring, openssl, pdo, tokenizer, xml). No additional extensions required.
- Database/Cache/Queue: not mandated; package utilities are storage-agnostic.

## Environment & Configuration
- No package-specific `.env` variables are required.
- Publish configuration in the host app: `php artisan vendor:publish --tag=equidna:config`.
- Key settings in `config/equidna.php`:
  - `route.*_matchers` for context detection.
  - `responses.allowed_headers` and `responses.strategies` for response handling.
  - `paginator.page_items` and optional `paginator.strategy` override.
- Boot validation: if any configured class cannot be resolved or fails to implement the expected interface, the service provider throws an `InvalidArgumentException` during application startup. Address configuration before deploying.

## Initial Setup in Host App
```bash
composer require equidna/laravel-toolkit
php artisan vendor:publish --tag=equidna:config
```

Optional middleware registration (examples):
- `Equidna\Toolkit\Http\Middleware\ForceJsonResponse` in the `api` or custom middleware groups.
- `Equidna\Toolkit\Http\Middleware\ExcludeFromHistory` under `web` routes to avoid session history buildup.
- `Equidna\Toolkit\Http\Middleware\DisableDebugbar` to enforce Debugbar off for sensitive routes.

## Deployment Workflow (Host App)
- Pull latest code.
- `composer install --no-dev` (production) or `composer install` (development).
- Cache configuration: `php artisan config:cache`; add `route:cache` if applicable.
- Restart queue workers after deployment if they consume toolkit helpers.

## Assumptions
- Package does not define routes, controllers, migrations, or assets; only configuration and PHP classes are published.
- Monitoring/logging relies on the host application's configuration; no channels are added.
