# Routes Documentation

This package does not register routes by itself. It is intended to be consumed by a host Laravel application.

## Route Detection

- `Equidna\Toolkit\Contracts\RouteDetectorInterface` (`src/Contracts/RouteDetectorInterface.php`)
- Default implementation used in tests: `Equidna\Toolkit\Tests\Support\FakeRouteDetector` (`tests/Support/FakeRouteDetector.php`)

## Helpers

- `Equidna\Toolkit\Helpers\RouteHelper` (`src/Helpers/RouteHelper.php`) determines context:
  - API routes: `/api/*`, `/*-api/*`, `api-*/*` → JSON
  - Hook routes: `/hooks/*` → JSON
  - IoT routes: `/iot/*` → JSON
  - Web routes → redirects with flash
  - Console → plain text

Integrate these helpers inside host app routes/controllers to ensure consistent responses.
