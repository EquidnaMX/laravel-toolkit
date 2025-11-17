# Equidna Toolkit v1.0.0

> **A modern Laravel package for multi-context, modular application development.**

Equidna Toolkit provides robust helpers, traits, middleware, and service providers to streamline development for web, API, hooks, and IoT contexts. Designed for Laravel 11 & 12, it enables unified response patterns, context-aware utilities, and advanced exception handling for professional-grade PHP projects.

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
```

[!TIP]
If you use Laravel's package auto-discovery, the service provider is registered automatically. Otherwise, add it manually to your `config/app.php`:

```php
'providers' => [
    Equidna\Toolkit\Providers\EquidnaLaravelToolkitServiceProvider::class,
]
```

## Usage Examples

### Context-Aware Responses

```php
use Equidna\Toolkit\Helpers\ResponseHelper;
// In a controller or service
return ResponseHelper::success('Operation completed', ['foo' => 'bar']);
// Returns JSON for API, redirect for web, plain text for console
```

### Composite Primary Keys in Eloquent

```php
use Equidna\Toolkit\Traits\Database\HasCompositePrimaryKey;
class UserRole extends Model {
    use HasCompositePrimaryKey;
    public function getKeyName() { return ['user_id', 'role_id']; }
}
```

### Paginate Any Data

```php
use Equidna\Toolkit\Helpers\PaginatorHelper;
$paginator = PaginatorHelper::buildPaginator($arrayOrCollection, $page, $itemsPerPage);
```

### Middleware Registration

```php
// In app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        \Equidna\Toolkit\Http\Middleware\ExcludeFromHistory::class,
    ],
];
```

## Technical Overview

**Multi-Context Request Detection**

All helpers use null-safe operators and fallback logic for Laravel context availability. Example:

```php
if (RouteHelper::wantsJson()) {
    return ResponseHelper::success('Message', $data);
}
return redirect()->with(['status' => 'ok', 'message' => 'Message']);
```

**Directory Structure**

- `Helpers/` – Context-aware utilities (null-safe, fallback patterns)
- `Http/Middleware/` – Session and response manipulation middleware
- `Exceptions/` – Custom HTTP exceptions (auto-bound)
- `Traits/Database/` – Eloquent extensions for composite keys and pagination
- `Providers/` – Auto-discovery service provider
- `config/` – Publishable config for pagination, etc.

## API Reference

### Helpers

#### RouteHelper

Static methods for request type detection and routing logic:

```php
RouteHelper::isWeb();           // Is this a web request?
RouteHelper::isApi();           // Is this an API request?
RouteHelper::isHook();          // Is this a hook request?
RouteHelper::isIoT();           // Is this an IoT request?
RouteHelper::isExpression($exp);// Custom expression match
RouteHelper::isConsole();       // Is this running in console?
RouteHelper::wantsJson();       // Should respond with JSON?
RouteHelper::getMethod();       // HTTP method
RouteHelper::isMethod('POST');  // Is this a POST request?
RouteHelper::getRouteName();    // Current route name
RouteHelper::isRouteName('foo');// Is current route 'foo'?
RouteHelper::routeContains('x');// Route name contains 'x'?
```

#### ResponseHelper

Unified error and success responses. Returns a `RedirectResponse` for web, JSON for API, plain text for console.

**Error Responses:**

```php
ResponseHelper::badRequest($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::unauthorized($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::forbidden($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::notFound($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::notAcceptable($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::conflict($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::unprocessableEntity($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::tooManyRequests($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::error($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::handleException($exception, $errors = [], $headers = [], $forwardUrl = null);
```

**Success Responses:**

```php
ResponseHelper::success($msg, $data = null, $forwardUrl = null);
ResponseHelper::created($msg, $data = null, $forwardUrl = null);
ResponseHelper::accepted($msg, $data = null, $forwardUrl = null);
ResponseHelper::noContent($msg = 'Operation completed successfully', $forwardUrl = null);
```

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

### Middleware

- **ExcludeFromHistory**: Prevents the current request from being stored in the session as the current URL.
- **ForceJsonResponse**: Forces the response to be JSON (sets `Accept: application/json`).
- **ForceApiResponse**: (Deprecated) Use `ForceJsonResponse` instead.

### Exception Classes

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

## Form Request Validation

### EquidnaFormRequest

`EquidnaFormRequest` extends Laravel's `FormRequest` to provide context-aware validation error handling. On validation failure, it throws a `BadRequestException` with a standard message and the validation errors array. This ensures API, web, and other contexts receive unified, appropriate error responses.

**Usage Example:**

```php
use Equidna\Toolkit\Http\Requests\EquidnaFormRequest;

class StoreUserRequest extends EquidnaFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email',
        ];
    }
}
```

On validation failure, a `BadRequestException` is thrown with a message and errors array, which is handled by the toolkit to provide a JSON response for API requests or a redirect with flash data for web requests, following the package's multi-context philosophy.

---

## Configuration

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
];
```

## Development

- Coding Standard: PSR-12, 4-space indent, 250-char line limit, StyleCI (laravel preset)
- Static Analysis: PHPStan (`vendor/bin/phpstan analyse`)
- PHP Version: 8.0+
- No bundled tests: Please contribute tests if you extend the package!

[!NOTE]
This package is designed for advanced Laravel projects. For questions, open an issue or PR on GitHub.

## Further Reading

- [Laravel Documentation](https://laravel.com/docs)
- [Equidna Toolkit on Packagist](https://packagist.org/packages/equidna/toolkit)

[!TIP]
For advanced integration patterns and edge cases, see the source code and open issues for real-world examples.

Equidna Toolkit provides robust helpers, traits, middleware, and service providers to streamline development for web, API, hooks, and IoT contexts. Designed for Laravel 11 & 12, it enables unified response patterns, context-aware utilities, and advanced exception handling for professional-grade PHP projects.

---

## 🚀 Key Features

- **Multi-Context Request Handling**: Seamlessly detect and respond to web, API, hook, IoT, and console requests.
- **Unified Response Helpers**: Consistent success and error responses for all contexts.
- **Advanced Exception Architecture**: Custom HTTP exceptions with automatic Laravel binding and context-aware rendering.
- **Eloquent Composite Key Support**: Effortlessly manage models with composite primary keys.
- **Configurable Pagination**: Build paginated responses from arrays or collections with minimal code.
- **Plug-and-Play Middleware**: Easily exclude requests from session history or force JSON responses.
- **Zero-Config Service Provider**: Auto-discovers and binds all package features.

---

## 📦 Installation

```bash
composer require equidna/toolkit
php artisan vendor:publish --tag=equidna:config
```

[!TIP]
If you use Laravel's package auto-discovery, the service provider is registered automatically. Otherwise, add it manually to your `config/app.php`:

```php
'providers' => [
    Equidna\Toolkit\Providers\EquidnaLaravelToolkitServiceProvider::class,
]
```

---

## 🧑‍💻 Usage Examples

### 1. Context-Aware Responses

```php
use Equidna\Toolkit\Helpers\ResponseHelper;

// In a controller or service
return ResponseHelper::success('Operation completed', ['foo' => 'bar']);
// Returns JSON for API, redirect for web, plain text for console
```

### 2. Composite Primary Keys in Eloquent

```php
use Equidna\Toolkit\Traits\Database\HasCompositePrimaryKey;

class UserRole extends Model {
    use HasCompositePrimaryKey;
    public function getKeyName() { return ['user_id', 'role_id']; }
}
```

### 3. Paginate Any Data

```php
use Equidna\Toolkit\Helpers\PaginatorHelper;

$paginator = PaginatorHelper::buildPaginator($arrayOrCollection, $page, $itemsPerPage);
```

### 4. Middleware Registration

```php
// In app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        \Equidna\Toolkit\Http\Middleware\ExcludeFromHistory::class,
    ],
];
```

---

## 🏗️ Technical Overview

### Multi-Context Request Detection

All helpers use null-safe operators and fallback logic for Laravel context availability. The core pattern is:

```php
if (RouteHelper::wantsJson()) {
    return ResponseHelper::success('Message', $data);
}
return redirect()->with(['status' => 'ok', 'message' => 'Message']);
```

### Directory Structure

- `Helpers/` – Context-aware utilities (null-safe, fallback patterns)
- `Http/Middleware/` – Session and response manipulation middleware
- `Exceptions/` – Custom HTTP exceptions (auto-bound)
- `Traits/Database/` – Eloquent extensions for composite keys and pagination
- `Providers/` – Auto-discovery service provider
- `config/` – Publishable config for pagination, etc.

---

## 🧩 API Reference

### Helpers

#### RouteHelper

Static methods for request type detection and routing logic:

```php
RouteHelper::isWeb();           // Is this a web request?
RouteHelper::isApi();           // Is this an API request?
RouteHelper::isHook();          // Is this a hook request?
RouteHelper::isIoT();           // Is this an IoT request?
RouteHelper::isExpression($exp);// Custom expression match
RouteHelper::isConsole();       // Is this running in console?
RouteHelper::wantsJson();       // Should respond with JSON?
RouteHelper::getMethod();       // HTTP method
RouteHelper::isMethod('POST');  // Is this a POST request?
RouteHelper::getRouteName();    // Current route name
RouteHelper::isRouteName('foo');// Is current route 'foo'?
RouteHelper::routeContains('x');// Route name contains 'x'?
```

#### ResponseHelper

Unified error and success responses. Returns a `RedirectResponse` for web, JSON for API, plain text for console.

**Error Responses:**

```php
ResponseHelper::badRequest($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::unauthorized($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::forbidden($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::notFound($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::notAcceptable($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::conflict($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::unprocessableEntity($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::tooManyRequests($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::error($msg, $errors = [], $headers = [], $forwardUrl = null);
ResponseHelper::handleException($exception, $errors = [], $headers = [], $forwardUrl = null);
```

**Success Responses:**

```php
ResponseHelper::success($msg, $data = null, $forwardUrl = null);
ResponseHelper::created($msg, $data = null, $forwardUrl = null);
ResponseHelper::accepted($msg, $data = null, $forwardUrl = null);
ResponseHelper::noContent($msg = 'Operation completed successfully', $forwardUrl = null);
```

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
- **ForceApiResponse**: (Deprecated) Use `ForceJsonResponse` instead.

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
];
```

---

## 🛠️ Development & Contribution

- **Coding Standard**: PSR-12, 4-space indent, 250-char line limit, StyleCI (laravel preset)
- **Static Analysis**: PHPStan (`vendor/bin/phpstan analyse`)
- **PHP Version**: 8.0+
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

Builds paginated responses from arrays or collections, using config-driven pagination length.

```php
PaginatorHelper::buildPaginator(array|Collection $data, ?int $page = null, ?int $items_per_page = null, bool $set_full_url = false): LengthAwarePaginator
PaginatorHelper::appendCleanedRequest(LengthAwarePaginator $paginator, Request $request): void
PaginatorHelper::setFullURL(LengthAwarePaginator $paginator): void
```

**Trait Usage Example:**

```php
// In your Eloquent model
use Equidna\Toolkit\Traits\Database\Paginator;

// Usage in a query scope
$results = $this->scopePaginator($query, $page, $pageName, $items_per_page, $set_full_url, $transformation);
```

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
