<?php

return [
    'env' => 'env',
    'mapper' => [],
    'login_cache_time' => 259200,
    'db' => require(__DIR__ . "/db.php"),
    'logger' => [
        'path' => __DIR__ . '/../logs/app.log'
    ],
    'redis'  => require(__DIR__ . "/redis.php"),
    'params' => require(__DIR__ . "/params.php"),
    'wxpay'  => require(__DIR__ . "/paywx.php"),
    'alipay' => require(__DIR__ . "/payali.php"),
];
