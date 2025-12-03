<?php

return [
    'paginator' => [
        'page_items' => 15,
        'strategy' => '',
    ],
    'route' => [
        'api_matchers' => ['api*', '*-api*'],
        'hook_matchers' => ['hooks/*'],
        'iot_matchers' => ['iot/*'],
        'json_matchers' => [],
        'detector' => '',
        'request_resolver' => '',
    ],
    'responses' => [
        'allowed_headers' => [
            'Cache-Control',
            'Retry-After',
        ],
        'strategies' => [
            'console' => '',
            'json' => '',
            'redirect' => '',
        ],
    ],
];
