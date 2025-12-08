# Security Policy

## Supported Versions
- 1.0.x (current minor) — security fixes and critical patches.
- Older versions are unsupported; upgrade to the latest 1.0.x release.

## Reporting a Vulnerability
1. Please email **gruelasjr@gmail.com** with:
   - A clear description of the issue and potential impact.
   - Steps to reproduce or a proof of concept.
   - Affected versions and environment details.
2. Do **not** open public GitHub issues for unresolved vulnerabilities.

## Response Process
- Acknowledge reports within 3 business days.
- Triage and reproduce; request more detail if needed.
- Coordinate a fix and target patch release; provide a CVE if applicable.
- Publish an advisory and changelog entry once the fix is released.

## Verification Checklist for Fixes
- Add regression tests covering the reported vector.
- Run quality gates: `composer audit --locked`, `vendor/bin/phpunit`, `vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=512M`, `vendor/bin/phpcs --standard=ruleset.xml`.

## Hardening Guidance
- Keep `composer.lock` current; run `composer audit --locked --no-dev` in CI.
- Prefer the latest Laravel LTS/patch releases and PHP 8.2/8.3.
- Ensure `paginator.page_items` is positive and response strategy classes implement the documented interfaces to avoid unsafe fallbacks.
