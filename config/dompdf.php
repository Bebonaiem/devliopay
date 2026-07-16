<?php

return [
    'show_warnings' => env('DOMPDF_SHOW_WARNINGS', false),
    'orientation' => 'portrait',
    'defines' => [
        'font_dir' => storage_path('fonts'),
        'font_cache' => storage_path('fonts'),
        'temp_dir' => sys_get_temp_dir(),
        'chroot' => realpath(base_path()),
        'enable_font_subsetting' => false,
        'enable_remote' => env('DOMPDF_ENABLE_REMOTE', true),
        'default_paper_size' => 'a4',
        'dpi' => 96,
        'enable_html5_parser' => true,
    ],
];
