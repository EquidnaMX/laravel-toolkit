# Equidna Laravel Toolkit

A Laravel 11/12 package that unifies response patterns, pagination utilities, and route detection across web, API, hook, IoT, and console entry points. The toolkit ships helpers, middleware, traits, and a service provider so host applications can enforce consistent behavior without rewriting boilerplate.

![CI status](https://github.com/EquidnaMX/laravel-toolkit/actions/workflows/ci.yml/badge.svg)

## At a Glance
- **Context-aware routing**: Detect API, hook, IoT, JSON-only, and console flows with configurable matchers.
- **Unified responses**: Generate JSON, redirects, or console output through a single helper API and swappable strategies.
- **Validation-friendly requests**: `EquidnaFormRequest` surfaces validation failures as HTTP exceptions instead of redirects.
- **Pagination utilities**: Build length-aware or cursor paginators from arrays, collections, or queries with sanitized query strings.
- **Pluggable middleware**: Force JSON, disable Debugbar, or exclude requests from session history as needed.
- **Composite key support**: `HasCompositePrimaryKey` removes boilerplate for multi-column primary keys.

## Compatibility
- **PHP:** 8.2 or 8.3 (validated in CI)
- **Laravel:** 11.x or 12.x
- **Composer:** 2.5+ recommended (for `composer audit`)

## Installation
```bash
composer require equidna/laravel-toolkit
php artisan vendor:publish --tag=equidna:config
```

Auto-discovery registers the service provider. For manual registration add to `config/app.php`:
```php
'providers' => [
    Equidna\Toolkit\Providers\EquidnaLaravelToolkitServiceProvider::class,
],
```

## Configuration
`config/equidna.php` (publish to your app) exposes the main touchpoints:

```php
return [
    'paginator' => [
        'page_items' => 15,
        'strategy' => null, // bind PaginationStrategyInterface to override
    ],
    'route' => [
        'api_matchers' => ['api*', '*-api*'],
        'hook_matchers' => ['hooks/*'],
        'iot_matchers'  => ['iot/*'],
        'json_matchers' => [],
        'detector' => null,           // RouteDetectorInterface
        'request_resolver' => null,   // RequestResolverInterface
    ],
    'responses' => [
        'allowed_headers' => ['Cache-Control', 'Retry-After'],
        'strategies' => [], // console/json/redirect => class names
    ],
];
```

- Matchers feed directly into `Request::is()`. Align them with your route prefixes (e.g., `services/api/*`).
- JSON preference is inferred from API/hook/IoT matchers, additional `json_matchers`, or `Request::expectsJson()`.
- To override behavior, either bind the related interface or set the fully qualified class in config; boot will fail fast if a class is missing or does not implement the expected interface.

### Mandatory configuration and failure modes
The service provider validates critical bindings during boot. Laravel throws an `InvalidArgumentException` when a configured class is missing or does not implement its interface (e.g., `RouteDetectorInterface`, `RequestResolverInterface`, `PaginationStrategyInterface`, `ResponseStrategyInterface`). This keeps deployments from silently misbehaving.

### Swap strategies for org-specific policies
Use container overrides or config to customize detection, pagination, or responses without editing package code:

```php
// In your application's service provider
$this->app->singleton(\Equidna\Toolkit\Contracts\RouteDetectorInterface::class, fn($app) =>
    $app->make(App\Routing\SubdomainRouteDetector::class)
);

$this->app->singleton('equidna.responses.json_strategy', fn($app) =>
    $app->make(App\Http\Responses\AuditJsonResponse::class)
);
```

## Usage
### RouteHelper
```php
use Equidna\Toolkit\Helpers\RouteHelper;

if (RouteHelper::isApi()) { /* ... */ }
if (RouteHelper::wantsJson()) { /* return an API-friendly payload */ }
```

### ResponseHelper
```php
use Equidna\Toolkit\Helpers\ResponseHelper;

// JSON for API/hook/IoT, redirect with flash for web, text for console
return ResponseHelper::success('Saved', ['id' => $model->id]);

// Custom status and headers (filtered through the allow-list for JSON)
return ResponseHelper::unprocessableEntity(
    message: 'Invalid input',
    errors: ['email' => ['Already taken']],
    headers: ['Retry-After' => '30'],
);
```

### Pagination
```php
use Equidna\Toolkit\Helpers\PaginatorHelper;

$paginator = PaginatorHelper::buildPaginator($collection, page: 2, items_per_page: 20, set_full_url: true);
PaginatorHelper::appendCleanedRequest($paginator, request());
```

Cursor and length-aware pagination helpers proxy to the configured `PaginationStrategyInterface` and accept optional transformations via `through()`.

### Composite primary keys
```php
use Equidna\Toolkit\Traits\Database\HasCompositePrimaryKey;

class UserRole extends Model
{
    use HasCompositePrimaryKey;

    public function getKeyName()
    {
        return ['user_id', 'role_id'];
    }
}
```

### Middleware
Register in your host app’s `Http\Kernel` when desired:
- `Http\Middleware\ForceJsonResponse` – forces JSON responses for matched requests.
- `Http\Middleware\ExcludeFromHistory` – skips adding requests to browser history.
- `Http\Middleware\DisableDebugbar` – disables Laravel Debugbar for the request lifecycle.

### Exceptions
HTTP-friendly exceptions (`src/Exceptions/*`) are container-bound by the service provider and render context-aware responses. Use them in controllers/services to standardize error handling (e.g., `throw new UnauthorizedException();`).

### Traits & Requests
- `Traits\Database\HasCompositePrimaryKey` – declare composite key columns via `getKeyName()`.
- `Traits\Database\Paginator` – integrates pagination helpers inside models.
- `Http\Requests\EquidnaFormRequest` – extends Laravel's `FormRequest` to emit HTTP exceptions instead of redirects on validation failure.

### Technical overview
- `Helpers/` — context-aware utilities (RouteHelper, ResponseHelper, PaginatorHelper)
- `Http/Middleware/` — ForceJsonResponse, ExcludeFromHistory, DisableDebugbar
- `Exceptions/` — custom HTTP exceptions auto-bound by the service provider
- `Traits/Database/` — HasCompositePrimaryKey, Paginator (for Eloquent)
- `Providers/` — EquidnaLaravelToolkitServiceProvider

## Development
- Coding standard: PSR-12 (`vendor/bin/phpcs --standard=ruleset.xml`).
- Static analysis: `vendor/bin/phpstan analyse -c phpstan.neon`.
- Tests: `vendor/bin/phpunit`.
- Run `composer audit --locked` before releases.

### Release hygiene
- Keep `CHANGELOG.md` updated for every user-facing change and reset the `Unreleased` section when tagging.
- Align the package version in `composer.json` with the release tag and changelog entry.
- Run audits, linters, static analysis, and tests (above) before publishing.

### PHPStan note
Running PHPStan against the library can surface `trait.unused` warnings for `Traits/Database/HasCompositePrimaryKey.php` and `Traits/Database/Paginator.php` because they are consumed by downstream apps. Options:
- Leave the warning (informational).
- Add an `ignoreErrors` rule for these messages in `phpstan.neon`.
- Add minimal unit tests or example usage files that reference the traits so PHPStan recognizes they are used.

## License & compliance
- **License:** MIT (see [`LICENSE`](LICENSE)).
- **Dependencies:** Laravel Framework, Illuminate Support, and Laravel Helpers (MIT licensed).
- **Packaging:** Ships as a Composer library with no bundled telemetry or proprietary services.
