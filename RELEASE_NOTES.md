# Release v1.0.6 "Meridian"

Released: 2026-06-13

## Summary

**Meridian** is a precision patch that corrects route detection behavior in console contexts and tightens the default URL matcher patterns to eliminate false positives. Route helper methods previously short-circuited to `false`/`null` whenever `isConsole()` returned `true`, preventing console commands from using a bound request for route context. Matcher patterns for `api`, `hooks`, and `iot` were also too broad, allowing unrelated paths (e.g. `/apiary`) to match.

## Highlights

- **Console-aware route detection** — Route helper methods no longer force `false` in console context; they now delegate to the request resolver, enabling console commands with a bound request to receive accurate results.
- **Precise default matchers** — `api_matchers`, `hook_matchers`, and `iot_matchers` updated to exact-prefix patterns, eliminating false positives from overlapping route names.
- **Root-path matching** — `/hooks` and `/iot` root paths are now matched in addition to their sub-paths.
- **Expanded test coverage** — New `RouteHelperTest` cases cover console-bound requests, root-path assertions, and prefix false-positive regressions.

## Fixed

- `RouteHelper` methods (`isApi`, `isHook`, `isIoT`, `isWeb`, `wantsJson`, `isExpression`, `getMethod`, `isMethod`, `getRouteName`, `routeContains`) no longer return early with a console-fallback value when `isConsole()` is `true`. Console commands with a request object bound to the container now receive correct route-context values.
- `AbstractRouteDetector::patterns()` now always returns an empty array when a key is missing, removing an inconsistency caused by the removed optional `$default` parameter.

## Changed

- `equidna.php` → `route.api_matchers` default changed from `['api*', '*-api*']` to `['api', 'api/*', '*-api', '*-api/*']`. Prevents paths like `/apiary` or `/rapid-api-gateway` from being incorrectly classified as API routes.
- `equidna.php` → `route.hook_matchers` default changed from `['hooks/*']` to `['hooks', 'hooks/*']` to also match the bare `/hooks` path.
- `equidna.php` → `route.iot_matchers` default changed from `['iot/*']` to `['iot', 'iot/*']` to also match the bare `/iot` path.

## Added

- `RouteHelperTest`: new test cases for console-bound requests, root-path matchers (`/api`, `/hooks`, `/iot`), and prefix false-positive regressions.

## No Breaking Changes

This is a fully backward-compatible patch. No public API signatures changed.

> **Note for upgraders:** If you have published `equidna.php` to your application's `config/` directory, the new default matcher patterns are **not** applied automatically. Review and update your `api_matchers`, `hook_matchers`, and `iot_matchers` entries to benefit from the improved patterns.

---

For the full project history see [CHANGELOG.md](CHANGELOG.md).
