# Open Questions & Assumptions

## Deployment

- Assumption: No migrations, routes, or assets are published by the package.
- Assumption: Config publish tag is `equidna:config` per project instructions.

## API & Routes

- Assumption: Package does not auto-register any routes; host app defines them.
- Question: Are there recommended route prefixes beyond `/api/*`, `/hooks/*`, `/iot/*` for detection?

## Monitoring

- Assumption: Package relies on host app logging/monitoring; no channels are added.

## Business Logic

- Assumption: Toolkit aims at response formatting and utilities, not domain flows.

If any assumption is incorrect, please update this document and the relevant details in other docs.
