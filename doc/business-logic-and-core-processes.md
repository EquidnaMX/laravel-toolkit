# Business Logic & Core Processes

The toolkit focuses on cross-cutting concerns rather than domain-specific business flows.

## Core Processes

- Multi-context response orchestration
  - Detect request type via `RouteHelper`.
  - Generate JSON vs redirect/text via `ResponseHelper`.
- Pagination utilities
  - `PaginatorHelper` standardizes pagination payloads across contexts.
- Exception handling
  - Custom HTTP exceptions render context-aware responses.

## Flow: Context-Aware Response

```mermaid
flowchart TD
  A[Incoming Request] --> B{RouteHelper::wantsJson()?}
  B -->|Yes| C[ResponseHelper::generateJsonResponse]
  B -->|No| D[Redirect with flash]
  C --> E[Return JSON]
  D --> F[Return Redirect]
```

## Main Classes

- `Equidna\Toolkit\Helpers\RouteHelper` (`src/Helpers/RouteHelper.php`)
- `Equidna\Toolkit\Helpers\ResponseHelper` (`src/Helpers/ResponseHelper.php`)
- `Equidna\Toolkit\Helpers\PaginatorHelper` (`src/Helpers/PaginatorHelper.php`)
- `Equidna\Toolkit\Exceptions\*` (`src/Exceptions/*`)
- `Equidna\Toolkit\Providers\EquidnaLaravelToolkitServiceProvider` (`src/Providers/EquidnaLaravelToolkitServiceProvider.php`)
