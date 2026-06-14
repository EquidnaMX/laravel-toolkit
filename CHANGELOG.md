# Changelog

## [Unreleased]

_No changes yet._

## [1.0.6] - 2026-06-13

### Fixed

- `RouteHelper` methods (`isApi`, `isHook`, `isIoT`, `isWeb`, `wantsJson`, `isExpression`, `getMethod`, `isMethod`, `getRouteName`, `routeContains`, `getMethod`) no longer short-circuit to `false`/`null` when `isConsole()` returns `true`. Console commands that bind a request object to the container now receive correct route-context results instead of always getting the console-fallback value.
- `AbstractRouteDetector::patterns()` always returns an array fallback (empty array instead of an optionally-provided default), eliminating a subtle inconsistency when no matchers are configured.

### Changed

- Default `api_matchers` in `equidna.php` updated from `['api*', '*-api*']` to `['api', 'api/*', '*-api', '*-api/*']`. The previous glob patterns could match unrelated paths (e.g. `/apiary`); the new patterns require an exact prefix or a slash separator.
- Default `hook_matchers` updated from `['hooks/*']` to `['hooks', 'hooks/*']` to also match the bare `/hooks` root path.
- Default `iot_matchers` updated from `['iot/*']` to `['iot', 'iot/*']` to also match the bare `/iot` root path.

### Added

- Expanded `RouteHelperTest` coverage: console-bound requests, root-path matcher assertions for `api`, `hooks`, and `iot`, and regression tests for prefix false-positives.

## [1.0.5] - 2026-05-07

### Fixed

- `UnprocessableEntityException::render()` now returns a non-null `errors` array in its JSON response. When `$this->errors` is `null`, it defaults to an array containing the exception message, ensuring the `errors` field is always an array in API error responses.

### Added

- Unit tests and a `httpExceptionProvider` data provider covering multiple HTTP exception classes to assert that all of them return a properly structured JSON response with an `errors` array.

## [1.0.4] - 2025-12-05

### Fixed

- Resolved response strategy resolution so default bindings work without custom configuration and added configuration guardrails with `ConfigurationException`.
- Validated paginator page-size configuration to require positive integers and improved request query appending.

### Added

- Tests covering service provider response strategy wiring/validation and configuration guards for pagination and response helpers.
- Enterprise readiness and quality-gate guidance in the README.
- Security policy in `SECURITY.md`.

## Maintainer guidance

### Changelog maintenance

- Record every user-facing change under `Unreleased` using `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, or `Security` subsections.
- Keep entries concise, action-oriented, and scoped to the package consumer (avoid internal-only noise).
- When cutting a release, copy the `Unreleased` section into a new versioned heading (e.g., `## [1.0.4] - YYYY-MM-DD`) and reset `Unreleased` to `_No changes yet._`.

### Versioning policy

- Follows Semantic Versioning: MAJOR for breaking changes, MINOR for new functionality (backwards compatible), PATCH for fixes and documentation-only releases.
- Target PHP 8.2+ and Laravel 11/12; raise the major version if compatibility requirements change.
- Each release must align `composer.json`'s `version` field, tags, and changelog entry.

### Release checklist

- Update `CHANGELOG.md` and ensure the `Unreleased` section is empty before tagging.
- Bump the package version in `composer.json` and confirm the tag matches the changelog version.
- Verify documentation and README examples reflect current APIs and supported PHP/Laravel versions.
- Run quality gates: `composer audit --locked`, `composer audit --locked --no-dev`, `vendor/bin/phpunit`, `vendor/bin/phpstan analyse -c phpstan.neon`, and `vendor/bin/phpcs --standard=ruleset.xml`.

## [1.0.3] - 2025-11-19

### Changed

- Documentation: Clarified PHPStan guidance in `README.md` regarding `trait.unused` findings and suggested suppression or test/usage examples. (No functional changes)
- Bumped package version to `1.0.3` to prepare the release.

## [1.0.2] - 2025-11-18

### Fixed (1.0.2)

- Ensured all repository files use UTF-8 encoding without BOM (including recent changes).
- Bumped package version to `1.0.2` in `composer.json` for release.
- No functional changes.

## [1.0.1] - 2025-11-17

### Fixed (1.0.1)

- Bumped package version to `1.0.1` in `composer.json`.
- Removed UTF-8 BOMs and normalized line endings to LF across the repository (non-vendor files).
- No functional changes.

## [1.0.0] - 2025-11-17

### Highlights (1.0.0)

- First stable release with refreshed installation docs and internal instructions.
- Updated package metadata and README to reflect the 1.0.0 milestone.

### Changed (1.0.0)

- Renamed the package service provider to `EquidnaLaravelToolkitServiceProvider` for clearer auto-discovery intent.

## [0.6.5] - 2025-07-28

### Added (0.6.5)

- Introduced `EquidnaLaravelToolkitServiceProvider` for the Equidna Toolkit Laravel package.
- Automatic merging and publishing of package configuration (`equidna.php`) using `registerConfig()` and `publishConfig()`.
- Registration of custom HTTP exception handlers:
  - `BadRequestException`
  - `UnauthorizedException`
  - `ForbiddenException`
  - `NotFoundException`
  - `NotAcceptableException`
  - `ConflictException`
  - `UnprocessableEntityException`
  - `TooManyRequestsException`
- PSR-12 style compliance, 4-space indentation, and PHPDoc documentation for all methods.

### Changed (0.6.5)

- None (initial release).

### Fixed (0.6.5)

- None (initial release).
