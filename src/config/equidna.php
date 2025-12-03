<?php

return [
    'paginator' => [
        'page_items' => 15,
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
        'include_debug_details' => env('EQUIDNA_RESPONSES_INCLUDE_DEBUG', env('APP_DEBUG', false)),
        'redirect_allowed_headers' => [
            'Cache-Control',
            'Retry-After',
        ],
        'redirect_allowed_error_fields' => ['message'],
    ],
];
