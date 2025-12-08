# Tests Documentation

## Framework & Tools
- PHPUnit (see `phpunit.xml`).
- Optional static analysis: `vendor/bin/phpstan analyse -c phpstan.neon`.
- Coding standards: `vendor/bin/phpcs --standard=ruleset.xml`.

## How to Run
```bash
composer install
./vendor/bin/phpunit
```

## Structure
- `tests/Exceptions/HttpExceptionsTest.php`
- `tests/Helpers/PaginatorHelperTest.php`
- `tests/Helpers/ResponseHelperTest.php`
- `tests/Helpers/RouteHelperTest.php`
- Support fakes under `tests/Support/*` (e.g., `FakeRouteDetector`, `FakeResponseFactory`).
- Base bootstrap: `tests/TestCase.php` (provides a minimal application container for helper tests).

## Coverage Overview
- Focus on helpers (pagination, response generation, route detection) and custom HTTP exceptions.
- No feature/integration tests in this package; host apps should layer integration coverage over their usage of the toolkit.

## Adding New Unit Tests
- Create files under `tests/*` aligned with the feature under test.
- Prefer one SUT per file (`{ClassName}Test.php`).
- Isolate collaborators using fakes/mocks from `tests/Support` or bespoke stubs.
- Keep tests deterministic and fast (<100ms) to preserve package CI speed.
