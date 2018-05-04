<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Customer;
use Biaoye\Model\School;
use Biaoye\Model\Product;
use Biaoye\Model\Agent;
use Biaoye\Model\AgentInventory;

$app->get('/v1/h5/home/page', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);
    $info = Customer::findFirst('id=' . $customerId);
    $schoolInfo = School::findFirst('id=' . $info->school_id);

    $jinxuan = $app->product->getHomeProductByTag($app, $customerId, 1, 3);
    $huodong = $app->product->getHomeProductByTag($app, $customerId, 2, 3);
    $tejia   = $app->product->getHomeProductByTag($app, $customerId, 3, 4);

    $lunbo = [
        [
            'img'  => 'https://api.qingrongby.com/imgs/1.png',
            'link' => '/activity/1.html',
        ],
        [
            'img'  => 'https://api.qingrongby.com/imgs/2.png',
            'link' => '/activity/2.html',
        ],
        [
            'img'  => 'https://api.qingrongby.com/imgs/3.png',
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

// 检查商城是否有效
$app->get('/v1/h5/home/check', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);
    $info = Customer::findFirst($customerId);
    $school = $info->school_id;
    $room = $info->room_id;

    $flag = $app->util->getSwitchFlag($app);

    // 切换标志
    if ($flag) {
        $exsit = Agent::count([
            "conditions" => "manager_flag=0 and work_flag = 1 and status=1 and room_id = " . $room,
        ]);

        if ($exsit > 0) {
            return true;
        }
    } else {
        $exsit = Agent::count([
            "conditions" => "manager_flag=0 and work_flag = 1 and status=1 and school_id = " . $school,
        ]);

        if ($exsit > 0) {
            return true;
        }
    }

    return false;
});