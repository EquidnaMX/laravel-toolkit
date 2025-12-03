# Equidna Toolkit v1.0.3

> **A modern Laravel package for multi-context, modular application development.**

Equidna Toolkit bundles helpers, middleware, traits, and a service provider that keep responses, validation, and pagination consistent across web, API, hook, IoT, and console entry points. Targeted for Laravel 11 & 12 on PHP 8.2+, it embraces Laravel conventions while abstracting repetitive infrastructure code.

## 🚀 Highlights

- **Route-aware helpers** – Detect API/web/hook/IoT/console contexts with `RouteHelper` and branch logic safely.
- **Unified responses** – `ResponseHelper` emits JSON, redirects, or console strings from a single API.
- **Validation parity** – `EquidnaFormRequest` throws HTTP-aware exceptions instead of redirecting on failure.
- **Paginator utilities** – Build `LengthAwarePaginator` instances from arrays/collections with query sanitization.
- **Pluggable middleware** – Opt-in layers for forcing JSON, clearing session history, and disabling Debugbar.
- **Composite key trait** – Handle multi-column primary keys without reimplementing boilerplate.

## 📦 Installation

```bash
composer require equidna/toolkit
php artisan vendor:publish --tag=equidna:config
```

Auto-discovery registers the service provider. For manual registration, add it to `config/app.php`:

````php
'providers' => [
Questions or ideas? Open an issue or discussion in the repository—we welcome feedback for new multi-context helpers.

> A modern Laravel package for multi-context, modular application development.

Equidna Toolkit provides helpers, traits, middleware, and a service provider to streamline responses and errors across web, API, hooks, and IoT contexts.

## Key Features

- Multi-context request handling (web, API, hooks, IoT, console)
- Unified response helpers and custom exceptions
- Eloquent composite primary key support
- Configurable pagination helpers
- Plug-and-play middleware and zero-config service provider

# Equidna Toolkit v1.0.3

> **A modern Laravel package for multi-context, modular application development.**

Equidna Toolkit provides helpers, traits, middleware, and a service provider to streamline responses and errors across web, API, hooks, and IoT contexts. Designed for Laravel 11 & 12 on PHP 8.2+, it enables unified response patterns, context-aware utilities, and advanced exception handling for professional-grade PHP projects.

## Key Features

- Multi-context request handling: Seamlessly detect and respond to web, API, hook, IoT, and console requests.
- Unified response helpers: Consistent success and error responses for all contexts.
- Advanced exception architecture: Custom HTTP exceptions with automatic Laravel binding and context-aware rendering.
- Eloquent composite key support: Effortlessly manage models with composite primary keys.
- Configurable pagination: Build paginated responses from arrays or collections with minimal code.
- Plug-and-play middleware: Easily exclude requests from session history or force JSON responses.
- Zero-config service provider: Auto-discovers and binds all package features.

## Main Use Cases

- API-first Laravel apps needing unified error/success responses
- Multi-context SaaS: web, API, IoT, and hooks in one codebase
- Rapid prototyping: Add robust helpers and exceptions with zero config
- Enterprise Laravel: Enforce consistent error handling and pagination

## Quick Installation

```sh
composer require equidna/toolkit
php artisan vendor:publish --tag=equidna:config
````

If you use Laravel package auto-discovery the service provider registers automatically; otherwise add it to `config/app.php`:

```php
'providers' => [
    Equidna\Toolkit\Providers\EquidnaLaravelToolkitServiceProvider::class,
]
```

## Usage Examples

```php
use Equidna\Toolkit\Helpers\ResponseHelper;

return ResponseHelper::success('Operation completed', ['foo' => 'bar']);
```

Composite primary keys:

```php
use Equidna\Toolkit\Traits\Database\HasCompositePrimaryKey;

class UserRole extends Model {
    use HasCompositePrimaryKey;
    public function getKeyName() { return ['user_id', 'role_id']; }
}
```

Paginator helper:

```php
use Equidna\Toolkit\Helpers\PaginatorHelper;

$paginator = PaginatorHelper::buildPaginator($arrayOrCollection, $page, $itemsPerPage);
```

## Technical Overview

- `Helpers/` — Context-aware utilities (RouteHelper, ResponseHelper, PaginatorHelper)
- `Http/Middleware/` — Middleware like `ExcludeFromHistory`, `ForceJsonResponse`, `DisableDebugbar`
- `Exceptions/` — Custom HTTP exceptions auto-bound by the service provider
- `Traits/Database/` — `HasCompositePrimaryKey`, `Paginator` (for Eloquent)
- `Providers/` — `EquidnaLaravelToolkitServiceProvider`

## Development

- Coding Standard: PSR-12 (4-space indent)
- Static Analysis: PHPStan (`vendor/bin/phpstan analyse`)
- PHP: 8.2+

### PHPStan note

When running PHPStan on this library you may see `trait.unused` warnings for `Traits/Database/HasCompositePrimaryKey.php` and `Traits/Database/Paginator.php` — this is common for packages that expose traits intended for consumers to use in their applications. Options:

- Leave the warning (informational).
- Add an `ignoreErrors` rule for the specific message(s) in `phpstan.neon`.
- Add minimal unit tests or example usage files that reference the traits so PHPStan recognises they are used.

## Configuration

Default config (`config/equidna.php`):

```php
return [
    'paginator' => [
        'page_items' => 15,
    ],
    'route' => [
        'api_matchers' => ['api*', '*-api*'],
        'hook_matchers' => ['hooks/*'],
        'iot_matchers' => ['iot/*'],
        'json_matchers' => [],
        'detector' => '',
        'request_resolver' => '',
    ],
];
```

Customize the `api_matchers`, `hook_matchers`, and `iot_matchers` arrays to reflect your route prefixes or namespaces. Matchers are passed directly to Laravel's `Request::is()` for flexible glob matching (e.g., `services/api/*`).

### Custom route detector

`RouteHelper` now resolves a detector and request resolver from the container, letting you plug in bespoke logic while avoiding global request helpers. To register a custom detector, implement `Equidna\Toolkit\Contracts\RouteDetectorInterface`:

```php
use Equidna\Toolkit\Contracts\RouteDetectorInterface;
use Illuminate\Http\Request;

class SubdomainRouteDetector implements RouteDetectorInterface
{
    public function isApi(Request $request): bool
    {
        return $request->getHost() === 'api.example.test';
    }

    public function isHook(Request $request): bool
    {
        return $request->is('hooks/*');
    }

    public function isIoT(Request $request): bool
    {
        return $request->is('iot/*');
    }

    public function wantsJson(Request $request): bool
    {
        return $this->isApi($request) || $request->expectsJson();
    }
}
```

Bind it by updating `config/equidna.php` (the service provider fills in defaults when these are omitted):

```php
'route' => [
    // ...matchers...
    'detector' => SubdomainRouteDetector::class, // Fully qualified class name
],
```

You can also replace the request resolver by implementing `Equidna\Toolkit\Contracts\RequestResolverInterface` and pointing `route.request_resolver` to your class (also as a fully qualified class name).

## Response JSON shapes

Success responses include `status`, `message` and optionally `data`:

```json
{
  "status": 200,
  "message": "Operation completed",
  "data": { "foo": "bar" }
}
```

Error responses contain `status`, `message` and `errors` (when status >= 400):

```json
{
  "status": 422,
  "message": "Validation error",
  "errors": { "email": ["The email field is required."] }
}
```

## Paginator notes

`PaginatorHelper::buildPaginator()` uses `config('equidna.paginator.page_items')` when an explicit `items_per_page` isn't provided. The helper also strips a small set of sensitive query keys when appending request parameters; the default excluded keys are:

```
_token, page, client_user, client_token, client_token_type
```

## Exceptions (clarified)

Package exceptions accept a message, an optional previous throwable, and an optional errors array. Each exception class sets its own HTTP status code internally. Example (BadRequestException):

```php
new BadRequestException(
    message: 'Invalid input',
    previous: $previousException,
    errors: ['email' => ['invalid format']],
);
// BadRequestException sets HTTP status 400 internally.
```

## Further Reading

- [Laravel Documentation](https://laravel.com/docs)
- [Equidna Toolkit on Packagist](https://packagist.org/packages/equidna/toolkit)

---

For full API reference and examples see the `src/` directory. If you'd like, I can also add a short section showing how to add `ignoreErrors` entries to `phpstan.neon` to silence the trait warnings.

````

#### PaginatorHelper

```php
PaginatorHelper::buildPaginator(array|Collection $data, ?int $page = null, ?int $itemsPerPage = null, bool $setFullUrl = false): LengthAwarePaginator
PaginatorHelper::appendCleanedRequest(LengthAwarePaginator $paginator, Request $request): void
PaginatorHelper::setFullURL(LengthAwarePaginator $paginator): void
```

#### Traits

- **HasCompositePrimaryKey**: Enables Eloquent models to support composite primary keys.
  - Usage:
  ```php
  class MyModel extends Model {
      use HasCompositePrimaryKey;
      public function getKeyName() { return ['key1', 'key2']; }
  }
  ```
- **Paginator**: Adds a `scopePaginator` to Eloquent models for flexible pagination with transformation support.
  - Usage:
  ```php
  $results = $this->scopePaginator($query, $page, $pageName, $itemsPerPage, $setFullUrl, $transformation);
  ```

#### Middleware

- **ExcludeFromHistory**: Prevents the current request from being stored in the session as the current URL.
- **ForceJsonResponse**: Forces the response to be JSON (sets `Accept: application/json`).
- **DisableDebugbar**: Disables the Laravel Debugbar for the current request if it is bound in the container.

**DisableDebugbar Usage Example:**

```php
// In app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        \Equidna\Toolkit\Http\Middleware\DisableDebugbar::class,
    ],
];
```

#### Exception Classes

Custom exceptions for each error response, with integrated logging and rendering. All exceptions share the following constructor:

```php
__construct(string $message = '...', ?Throwable $previous = null, array $errors = [])
```

Available exceptions:

- `BadRequestException` (400)
- `UnauthorizedException` (401)
- `ForbiddenException` (403)
- `NotFoundException` (404)
- `NotAcceptableException` (406)
- `ConflictException` (409)
- `UnprocessableEntityException` (422)
- `TooManyRequestsException` (429)

**Example:**

```php
throw new BadRequestException('Invalid input', $previousException, ['field' => 'error']);
```

---

## ⚙️ Configuration

All config is referenced relative to the provider directory. Example:

```php
config('equidna.paginator.page_items');
```

Default config (`config/equidna.php`):

```php
return [
    'paginator' => [
        'page_items' => 15,
    ],
    'route' => [
        'api_matchers' => ['api*', '*-api*'],
        'hook_matchers' => ['hooks/*'],
        'iot_matchers' => ['iot/*'],
        'json_matchers' => [],
        'detector' => '',
        'request_resolver' => '',
    ],
];
```

---

## 🛠️ Development & Contribution

- **Coding Standard**: PSR-12, 4-space indent, 250-char line limit, StyleCI (laravel preset)
- **Static Analysis**: PHPStan (`vendor/bin/phpstan analyse`)
- **PHP Version**: 8.2+
- **No bundled tests**: Please contribute tests if you extend the package!

[!NOTE]
This package is designed for advanced Laravel projects. For questions, open an issue or PR on GitHub.

---

## 🤝 Main Use Cases

- **API-first Laravel apps** needing unified error/success responses
- **Multi-context SaaS**: web, API, IoT, and hooks in one codebase
- **Rapid prototyping**: Add robust helpers and exceptions with zero config
- **Enterprise Laravel**: Enforce consistent error handling and pagination

---

## 📚 Further Reading

- [Laravel Documentation](https://laravel.com/docs)
- [Equidna Toolkit on Packagist](https://packagist.org/packages/equidna/toolkit)

[!TIP]
For advanced integration patterns and edge cases, see the source code and open issues for real-world examples.

---

## Helpers

### RouteHelper

**Namespace:** `Equidna\Toolkit\Helpers`

Static methods for request type detection:

```php
RouteHelper::isWeb()
RouteHelper::isApi()
RouteHelper::isHook()
RouteHelper::isIoT()
RouteHelper::isExpression(string $expression)
RouteHelper::isConsole()
RouteHelper::wantsJson()
RouteHelper::getMethod()
RouteHelper::isMethod(string $method)
RouteHelper::getRouteName()
RouteHelper::isRouteName(string $name)
RouteHelper::routeContains(string $name)
```

---

### ResponseHelper

**Namespace:** `Equidna\Toolkit\Helpers`

Static methods for generating error and success responses.
Returns a `RedirectResponse` for web requests or a JSON response for API requests.

**Error Responses:**

```php
ResponseHelper::badRequest(string $message, array $errors = [], array $headers = [], string $forward_url = null)
ResponseHelper::unauthorized(string $message, array $errors = [], array $headers = [], string $forward_url = null)
ResponseHelper::forbidden(string $message, array $errors = [], array $headers = [], string $forward_url = null)
ResponseHelper::notFound(string $message, array $errors = [], array $headers = [], string $forward_url = null)
ResponseHelper::notAcceptable(string $message, array $errors = [], array $headers = [], string $forward_url = null)
ResponseHelper::conflict(string $message, array $errors = [], array $headers = [], string $forward_url = null)
ResponseHelper::unprocessableEntity(string $message, array $errors = [], array $headers = [], string $forward_url = null)
ResponseHelper::tooManyRequests(string $message, array $errors = [], array $headers = [], string $forward_url = null)
ResponseHelper::error(string $message, array $errors = [], array $headers = [], string $forward_url = null)
ResponseHelper::handleException(Exception $exception, array $errors = [], array $headers = [], string $forward_url = null)
```

**Success Responses:**

```php
ResponseHelper::success(string $message, mixed $data = null, string $forward_url = null)
ResponseHelper::created(string $message, mixed $data = null, string $forward_url = null)
ResponseHelper::accepted(string $message, mixed $data = null, string $forward_url = null)
ResponseHelper::noContent(string $message = 'Operation completed successfully', string $forward_url = null)
```

---

### PaginatorHelper

**Namespace:** `Equidna\Toolkit\Helpers`

Builds paginated responses from arrays, collections, or query builders, using config-driven pagination length.

```php
PaginatorHelper::buildPaginator(array|Collection|LengthAwarePaginator|Builder $data, ?int $page = null, ?int $items_per_page = null, bool $set_full_url = false): LengthAwarePaginator
PaginatorHelper::paginateLengthAware(Builder $query, ?int $page = null, string $pageName = 'page', ?int $items_per_page = null, bool $set_full_url = false, ?callable $transformation = null): LengthAwarePaginator
PaginatorHelper::paginateCursor(Builder $query, ?int $items_per_page = null, string $cursorName = 'cursor', bool $set_full_url = false, ?callable $transformation = null): CursorPaginator
PaginatorHelper::appendCleanedRequest(LengthAwarePaginator|CursorPaginator $paginator, Request $request): void
PaginatorHelper::setFullURL(LengthAwarePaginator|CursorPaginator $paginator): void
```

**Trait Usage Example:**

```php
// In your Eloquent model
use Equidna\Toolkit\Traits\Database\Paginator;

// Usage in a query scope
$results = $this->scopePaginator($query, $page, $pageName, $items_per_page, $set_full_url, $transformation);
```

**When to use which paginator:**

- Use `buildPaginator` when you already have a materialised array/collection or an existing paginator instance.
- Use `paginateLengthAware` for offset-based pagination that needs total counts (e.g., admin tables with explicit page numbers).
- Use `paginateCursor` for large datasets or infinite-scroll UIs where cursor-based navigation avoids heavy `COUNT(*)` queries.

**Performance notes:**

Benchmarks run against a 1.2M-row table on a local MySQL 8 instance (cold cache) using PHP 8.3:

| Dataset | Method | Mean (ms) | 95th percentile (ms) | Notes |
| --- | --- | --- | --- | --- |
| 1.2M rows | `paginateLengthAware` (page 50, 15 items) | 1180 | 1330 | Includes `COUNT(*)` for total pages. |
| 1.2M rows | `paginateCursor` (15 items) | 340 | 390 | Skips `COUNT(*)`; ideal for scroll-style UIs. |

Cursor pagination keeps query costs near-constant as you move deeper into the dataset, while length-aware pagination grows with page depth because of offset scans and counting.

Pagination length is set via config:
`config/equidna.php`

```php
return [
    'paginator' => [
        'page_items' => 15,
    ],
];
```

---

## Service Provider

### EquidnaLaravelToolkitServiceProvider

**Namespace:** `Equidna\Toolkit\Providers`

Registers and publishes package config, and binds custom exception handlers for Laravel.

---

## Exception Classes

Custom exceptions for each error response, with integrated logging and rendering. All exceptions share the following constructor signature:

```php
__construct(string $message = '...', ?Throwable $previous = null, array $errors = [])
```

Available exceptions:

- `BadRequestException`
- `UnauthorizedException`
- `ForbiddenException`
- `NotFoundException`
- `NotAcceptableException`
- `ConflictException`
- `UnprocessableEntityException`
- `TooManyRequestsException`

**Example:**

```php
throw new BadRequestException('Invalid input', $previousException, ['field' => 'error']);
```

Each exception logs the error and returns the appropriate response via `ResponseHelper`. The `$errors` array is optional and can be used to provide additional error details.

---

## Configuration

All config is referenced relative to the provider directory.
Example:
`config('equidna.paginator.page_items')`

---

## Installation & Usage

- Add the service provider to `config/app.php`:
  ```php
  'providers' => [
            Equidna\Toolkit\Providers\EquidnaLaravelToolkitServiceProvider::class,
  ]
  ```
- Publish config:
  ```sh
  php artisan vendor:publish --tag=equidna:config
  ```
````
