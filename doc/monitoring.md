# Monitoring

This package does not introduce logging channels or monitoring tools. It operates within the host app’s logging/monitoring setup.

## Recommended Setup in Host Apps

- Use default Laravel logging (`config/logging.php`) with daily logs and error level alerts.
- Consider adopting:
  - Telescope for debugging
  - Horizon if using queues
  - Sentry/Bugsnag/New Relic for error/APM monitoring

## Suggested Metrics

- Error rates from custom exceptions (`Equidna\Toolkit\Exceptions\*`).
- Response format mismatches (unexpected HTML vs JSON) when integrating helpers.
- Queue failure rates (if helpers are used within jobs).

## Troubleshooting Tips

- If API routes return redirects instead of JSON, verify `RouteHelper::wantsJson()` usage and route prefixes.
- Ensure middleware registration aligns with host app expectations.
