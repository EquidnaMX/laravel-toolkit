# Copilot Coding Agent Instructions for equidna-toolkit

## Project Overview

**equidna-toolkit** is a Laravel PHP package (v1.0.0) providing utilities for multi-context application development. It intelligently handles different request types (web, API, hooks, IoT) and provides unified response patterns across contexts.

**Core Architecture:**

- `Helpers/`: Context-aware utilities with null-safe fallback patterns
- `Http/Middleware/`: Session and response manipulation middleware
- `Exceptions/`: Custom HTTP exceptions with automatic Laravel binding
- `Traits/Database/`: Eloquent extensions for composite keys
- `Providers/`: Auto-discovery service provider with config publishing

## Critical Patterns

### Multi-Context Request Handling

The package's core philosophy centers on `RouteHelper::wantsJson()` which determines response format:

- API routes (`/api/*`, `/*-api/*`) → JSON responses
- Hook routes (`/hooks/*`) → JSON responses
- IoT routes (`/iot/*`) → JSON responses
- Web routes → Redirects with session flash data
- Console → Plain text messages

Example usage in `ResponseHelper::generateResponse()`:

```php
if (RouteHelper::wantsJson()) {
    return self::generateJsonResponse($status, $message, $errors, $data);
}
return redirect()->with(['status' => $status, 'message' => $message]);
```

### Defensive Programming Pattern

All helpers use null-safe operators and fallback logic for Laravel context availability:

```php
public static function isApi(): bool {
    $firstSegment = request()?->segment(1);
    if (is_null($firstSegment)) return false;
    return preg_match('/^(api|.*-api|api-.*)$/i', $firstSegment) === 1;
}
```

### Exception Architecture

Custom exceptions auto-register in service container and provide context-aware responses:

- Each exception (404, 401, 422, etc.) has dedicated class in `src/Exceptions/`
- Service provider binds all exceptions automatically via reflection
- Exceptions inherit Laravel's `report()` and `render()` methods for logging

## Development Workflows

**Package Installation:**

```bash
composer require equidna/toolkit
# Auto-discovery registers EquidnaLaravelToolkitServiceProvider
php artisan vendor:publish --tag=equidna:config
```

**Code Quality:**

- Uses PHPStan for static analysis: `vendor/bin/phpstan analyse`
- PSR-12 coding standard with 250-char line limit (see `ruleset.xml`)
- PHP 8.0+ requirement with null-safe operators throughout

**No Testing Infrastructure:** Package lacks tests - consider this when making changes.

## Package-Specific Conventions

### Config Management

Config published from `src/config/equidna.php` with relative pathing:

```php
$this->mergeConfigFrom(__DIR__ . '/../config/equidna.php', 'equidna');
```

### Middleware Registration Pattern

Middleware designed for manual registration in host app's `Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        \Equidna\Toolkit\Http\Middleware\ExcludeFromHistory::class,
    ]
];
```

### Composite Primary Keys

Models using `HasCompositePrimaryKey` **must** override `getKeyName()`:

```php
class UserRole extends Model {
    use HasCompositePrimaryKey;
    public function getKeyName() { return ['user_id', 'role_id']; }
}
```

## Integration Points

- **Laravel Framework**: Requires illuminate/support ^11.21|^12.0 as dev dependency
- **Auto-Discovery**: Package auto-registers via composer.json `extra.laravel.providers`
- **Config Publishing**: Uses Laravel's standard `vendor:publish` command
- **Exception Handling**: Integrates with Laravel's exception handling pipeline
