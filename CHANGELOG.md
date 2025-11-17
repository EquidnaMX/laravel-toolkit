# Changelog

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
