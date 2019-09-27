<?php

return [
    'default' => 'extensions',
    'disks' => [
        'extensions' => [
            'driver' => 'local',
            'root' => base_path('extensions/'),
        ],
        'zips' => [
            'driver' => 'local',
            'root' => storage_path('zips/'),
        ],
        'repositories' => [
            'driver' => 'local',
            'root' => storage_path('repositories/'),
        ],
        'languages' => [
            'driver' => 'local',
            'root' => base_path('languages/'),
        ],
        'root' => [
            'driver' => 'local',
            'root' => base_path(),
        ],
        'stubs' => [
            'driver' => 'local',
            'root' => base_path('stubs/'),
        ],
    ],
];
