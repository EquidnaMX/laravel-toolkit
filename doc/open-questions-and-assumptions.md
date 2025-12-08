# Open Questions & Assumptions

Current state after documentation review:

- **Routes/prefixes:** Defaults are `api*`, `*-api*`, `hooks/*`, `iot/*`; adjust `json_matchers` for any additional JSON-only routes (e.g., `/webhooks/*`, `/services/api/*`).
- **Publishing:** Only `config/equidna.php` is publishable via `equidna:config`; no routes, assets, or migrations are shipped.
- **Monitoring:** Package defers to host app logging/monitoring. Consider pairing with Telescope/Horizon/Sentry in consuming apps if deeper observability is required.
- **Business logic:** Package is intentionally infrastructure-focused; domain flows remain in the host application.

If consuming teams need additional route matcher presets or built-in monitoring hooks, capture the requirement before implementing to keep compatibility guarantees clear.
