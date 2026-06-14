<?php

return [
    'paginator' => [
        'page_items' => 15,
        // Leave null to use the toolkit default or bind PaginationStrategyInterface to override.
        'strategy' => null,
    ],
    'route' => [
        'api_matchers' => ['api', 'api/*', '*-api', '*-api/*'],
        'hook_matchers' => ['hooks', 'hooks/*'],
        'iot_matchers' => ['iot', 'iot/*'],
        'json_matchers' => [],
        // Leave null to use the toolkit defaults or bind interfaces to override.
        'detector' => null,
        'request_resolver' => null,
    ],
    'responses' => [
        'allowed_headers' => [
            'Cache-Control',
            'Retry-After',
        ],
        // Specify response strategy classes per context, or leave empty to use defaults.
        'strategies' => [],
    ],
];
