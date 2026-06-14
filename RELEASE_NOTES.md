# Release v2.0.0 "Horizon"

Released: 2026-06-13

## Summary

**Horizon** marks the package's compatibility reset to Laravel 12/13. It drops Laravel 11 support, updates the package and CI constraints to the new baseline, and keeps route resolution stable when the application container is swapped during tests or runtime.

## Highlights

- **Laravel 12/13 baseline** — The package now targets Laravel 12.x and 13.x only.
- **Container-safe route resolution** — `RouteHelper` now resolves its detector and request resolver from the current container on each call instead of reusing cached instances.
- **Updated release matrix** — Composer, CI, and maintainer guidance now align on PHP 8.2-8.5 and Laravel 12/13.
- **Expanded coverage** — Route-helper tests cover container swapping and Laravel 13 compatibility.

## Removed

- Laravel 11 support.

## Fixed

- `RouteHelper` now resolves `RouteDetectorInterface` and `RequestResolverInterface` from the active container for each call, preventing stale bindings in tests and long-lived runtime contexts.
- Route-helper tests now verify container switching and the Laravel 13 compatibility path.

## Changed

- `composer.json` now requires `laravel/framework` and `illuminate/support` at `^12.0 || ^13.0`.
- CI now validates PHP 8.2-8.5 against Laravel 12/13 instead of the previous Laravel 11/12/13 matrix.
- README compatibility notes and maintainer guidance now describe the Laravel 12/13 baseline.

For the full project history see [CHANGELOG.md](CHANGELOG.md).
For migration details see [BREAKING_CHANGES.md](BREAKING_CHANGES.md).
