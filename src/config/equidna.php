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
];
