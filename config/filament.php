<?php

return [
    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

    'broadcasting' => [
        'enabled' => env('FILAMENT_BROADCASTING_ENABLED', false),
        'echo' => [
            'broadcaster' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'cluster' => env('PUSHER_APP_CLUSTER'),
        ],
    ],
];
