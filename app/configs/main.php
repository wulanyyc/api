<?php

return [
    'db' => require(__DIR__ . "/db.php"),
    'logger' => ['path' => __DIR__ . '/../logs/app.log'],
    'redis' => require(__DIR__ . "/redis.php"),
    'mapper' => [],
    'file' => [
        'path' => __DIR__ . "/../../public/files/",
    ],
    'env' => 'env',
    'params' => require(__DIR__ . "/params.php"),
    'login_cache_time' => 259200,
];
