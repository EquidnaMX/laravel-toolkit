# Release v2.0.1

Released: 2026-10-06

## Security

This patch excludes published vulnerable dependency versions during Composer resolution and refreshes the development lockfile to patched releases. Consumers receive these restrictions when updating the package.

- Guzzle 7.15.5 and promises 2.5.3
- PSR-7 2.13.1
- Laravel 13.35.0
- CommonMark 2.10.3
- Flysystem 3.36.0
- PHP_CodeSniffer 4.0.4

All locked dependency upgrades retain their existing major versions. Laravel 12 and 13 remain supported; consumers must use Laravel 12.69.0 or later, or Laravel 13.30.0 or later. PHP support remains 8.2–8.5.

## Validation

- Local PHPUnit: 35 tests, 143 assertions, with PHPStan, PHPCS, and Composer audit passing.
- CI passed all seven supported PHP 8.2–8.5 / Laravel 12–13 combinations, including tests, static analysis, coding standards, and dependency audits.
- Independent review found no blocking defects.

Existing PHP 8.5 test deprecation and Composer version-field recommendation remain. These changes do not establish whether deployed consuming applications are exposed to the upstream advisories.

See [PR #30](https://github.com/EquidnaMX/laravel-toolkit/pull/30) and [CHANGELOG.md](CHANGELOG.md).
