# Routes Documentation

This package does not register routes itself; it supplies detection and response helpers consumed by host Laravel applications.

## Route Detection
- Contract: `Equidna\Toolkit\Contracts\RouteDetectorInterface` (`src/Contracts/RouteDetectorInterface.php`).
- Default: `Equidna\Toolkit\Helpers\Detectors\ConfigurableRouteDetector` using patterns from `config/equidna.php`.
- Matchers (defaults):
  - API: `api*`, `*-api*`
  - Hooks: `hooks/*`
  - IoT: `iot/*`
  - Additional JSON-only paths: `json_matchers` (empty by default; uses `Request::expectsJson()` when empty)

`RouteHelper` (`src/Helpers/RouteHelper.php`) wraps the detector and exposes helpers:
- `isApi()`, `isHook()`, `isIoT()` – match against configured patterns.
- `wantsJson()` – true for API/hook/IoT requests, JSON matchers, or when `expectsJson()` is true.
- `isWeb()` – true when none of the above contexts match and not running in console.
- `isConsole()` – true when running via CLI.
- Additional utilities: `isExpression()`, `getMethod()`, `isMethod()`, `getRouteName()`, `isRouteName()`, `routeContains()`.

## Integration Guidance
- Use `RouteHelper::wantsJson()` inside controllers or exception renderers to branch between JSON and redirect responses.
- Configure matcher arrays in `config/equidna.php` to align with your route prefixes; misaligned matchers may cause redirects on API routes or HTML on API clients.
- If using a custom detector, bind `RouteDetectorInterface` or set `route.detector` to your class. The service provider validates that the class exists and implements the contract during boot.
