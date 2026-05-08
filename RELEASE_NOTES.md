# Release v1.0.5 "Sentinel"

Released: 2026-05-07

## Summary

**Sentinel** is a focused reliability patch that enforces a non-null contract on `UnprocessableEntityException` JSON responses. Before this release it was possible for the `errors` field in an API error response to be `null` when no explicit error list was provided to the exception. This release guarantees that `errors` is always a non-empty array, making API error handling more predictable for consumers.

## Highlights

- **Non-null errors guarantee** — `UnprocessableEntityException::render()` now defaults the `errors` field to `[exception message]` when no errors are supplied.
- **Expanded test coverage** — A new `httpExceptionProvider` data provider validates multiple HTTP exception classes at once, strengthening the exception test suite.

## Fixed

- `UnprocessableEntityException::render()` returned `"errors": null` when instantiated without an explicit errors array. It now returns `"errors": ["<message>"]` in that case.

## Added

- Unit tests with `httpExceptionProvider` data provider covering all main HTTP exception classes (`BadRequestException`, `UnauthorizedException`, `ForbiddenException`, `NotFoundException`, `NotAcceptableException`, `ConflictException`, `UnprocessableEntityException`, `TooManyRequestsException`).

## No Breaking Changes

This is a fully backward-compatible patch. No public API signatures changed.

---

For the full project history see [CHANGELOG.md](CHANGELOG.md).
