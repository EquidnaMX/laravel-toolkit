# Deployment Instructions

This is a Laravel package, not a standalone application. It integrates into host Laravel apps.

## System Requirements

- PHP: ^8.2
- Laravel: ^11.21 or ^12.0
- Extensions: typical Laravel requirements (mbstring, openssl, pdo, tokenizer, xml). No additional extensions required specifically by this package.
- Databases/Cache/Queue: Not mandated; package is storage-agnostic.

## Environment Configuration

No package-specific `.env` variables are required. Host application may configure behavior through the published config file:

- Publish: `php artisan vendor:publish --tag=equidna:config`
- Config path in host app: `config/equidna.php`

## Initial Setup in Host App

```powershell
composer require equidna/laravel-toolkit
php artisan vendor:publish --tag=equidna:config
```

Optional middleware registration (example): add `Equidna\Toolkit\Http\Middleware\ExcludeFromHistory` to `App\Http\Kernel` under the `web` group.

## Deployment Workflow (Host App)

- Pull latest code
- `composer install --no-dev` (production) or `composer install` (development)
- Configure and cache: `php artisan config:cache`; optionally `route:cache` if applicable
- Restart workers if using queues

## Assumptions

- Package does not define routes, controllers, or Artisan commands. It provides helpers and middleware only.
- No migrations or assets to build are included.

See `doc/open-questions-and-assumptions.md` for any uncertainties.
