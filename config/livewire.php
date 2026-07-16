<?php

return [
    'class_namespace' => 'App\\Livewire',
    'view_path' => resource_path('views/livewire'),
    'layout' => 'layouts.app',
    'asset_url' => null,
    'app_url' => env('APP_URL'),
    'middleware_group' => 'web',
    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TEMP_UPLOAD_DISK', 'local'),
        'rules' => ['required', 'file', 'max:12288'],
        'directory' => 'livewire-tmp',
    ],
];
