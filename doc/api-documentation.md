# API Documentation

This package does not define HTTP API endpoints. It provides helpers and middleware used within a host application’s controllers and routes to produce context-aware responses.

## Key Helpers Impacting API Behavior

- `Equidna\Toolkit\Helpers\RouteHelper::wantsJson()` (`src/Helpers/RouteHelper.php`): detects if the current request should respond with JSON (API, hooks, IoT) vs redirect (web) vs text (console).
- `Equidna\Toolkit\Helpers\ResponseHelper::generateResponse()` (`src/Helpers/ResponseHelper.php`): generates JSON payloads for API contexts or redirect responses with session flash data for web.

Document API endpoints in the host application’s repository. This package only influences response format and error handling through its exceptions and helpers.
