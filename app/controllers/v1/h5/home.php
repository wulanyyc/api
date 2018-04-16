<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Customer;
use Biaoye\Model\School;
use Biaoye\Model\Product;

$app->get('/v1/h5/home/page', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);
    $info = Customer::findFirst('id=' . $customerId);
    $schoolInfo = School::findFirst('id=' . $info->school_id);

    $jinxuan = $app->producthelper->getHomeProductByTag($app, $customerId, 1, 3);
    $huodong = $app->producthelper->getHomeProductByTag($app, $customerId, 2, 3);
    $tejia   = $app->producthelper->getHomeProductByTag($app, $customerId, 3, 4);

    $lunbo = [
        [
            'img'  => 'http://39.107.251.99:8080/imgs/1.png',
            'link' => '/activity/1.html',
        ],
        [
            'img'  => 'http://39.107.251.99:8080/imgs/2.png',
            'link' => '/activity/2.html',
        ],
        [
            'img'  => 'http://39.107.251.99:8080/imgs/3.png',
            'link' => '/activity/3.html',
        ],
    ];

    return [
        'school_id' => $schoolInfo->id,
        'school_name' => $schoolInfo->name,
        'lunbo' => $lunbo,
        'jinxuan' => $jinxuan,
        'huodong' => $huodong,
        'tejia' => $tejia,
    ];
});