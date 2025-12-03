# Tests Documentation

## Framework

- PHPUnit (see `phpunit.xml`).

## How to Run

```powershell
./vendor/bin/phpunit
```

## Structure

- `tests/Unit/**` exists with coverage for helpers and exceptions:
  - `tests/Exceptions/HttpExceptionsTest.php`
  - `tests/Helpers/PaginatorHelperTest.php`
  - `tests/Helpers/ResponseHelperTest.php`
  - `tests/Helpers/RouteHelperTest.php`
  - Support fakes under `tests/Support/*` (e.g., `FakeResponseFactory`, `FakeRouteDetector`).
- Base: `tests/TestCase.php`.

## Coverage Overview

- Strong focus on helpers (pagination, response generation, route detection) and custom HTTP exceptions.
- No feature/integration tests in this package.

## Adding New Unit Tests

- Place under `tests/Unit/**` following the Agent Testing Scope rules.
- One SUT per file: `{ClassName}Test.php`.
- Isolate collaborators using fakes/mocks.
- Keep tests deterministic and fast (< 100ms).

This package has no migrations or controllers, so tests remain unit-level.
