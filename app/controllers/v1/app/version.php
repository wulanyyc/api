<?php
/**
 * 版本号
 */

// 个人中心
$app->get('/v1/app/version', function () use ($app) {
    return [
        'version' => '1.0.0',
        'link' => 'http://api.qingrongby.com/test.apk',
    ];
});