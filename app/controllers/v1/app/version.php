<?php
/**
 * 版本号
 */

// 个人中心
$app->get('/v1/app/version', function () use ($app) {
    return [
        'version' => '1.0.1',
        'link' => 'https://api.qingrongby.com/pub/app-release.apk',
    ];
});