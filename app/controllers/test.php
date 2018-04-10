<?php
use Biaoye\Model\Agent;
use Biaoye\Model\Customer;
use Biaoye\Model\Product;
use Biaoye\Model\ProductCategory;
use Biaoye\Model\ProductTags;

// 获取短信验证码
$app->get('/test/product/add', function () use ($app) {
    for ($i = 0; $i < 10; $i++) {
        $ar = new Product();
        $ar->factory = $app->util->getNoncestr();
        $ar->category = 1;
        $ar->sub_category = rand(2,3);
        $ar->name = $app->util->getNoncestr();
        $ar->price = rand(1, 100);
        $ar->desc = $app->util->getNoncestr();
        $ar->img = "http://39.107.251.99:8080/imgs/1.png";
        $ar->img1 = "http://39.107.251.99:8080/imgs/1.png";
        $ar->img2 = "http://39.107.251.99:8080/imgs/2.png";
        $ar->img3 = "http://39.107.251.99:8080/imgs/3.png";
        $ar->status = 1;
        $ar->save();
    }

    for ($i = 0; $i < 10; $i++) {
        $ar = new Product();
        $ar->factory = $app->util->getNoncestr();
        $ar->category = 4;
        $ar->sub_category = rand(5,6);
        $ar->name = $app->util->getNoncestr();
        $ar->price = rand(1, 100);
        $ar->desc = $app->util->getNoncestr();
        $ar->img = "http://39.107.251.99:8080/imgs/2.png";
        $ar->img1 = "http://39.107.251.99:8080/imgs/1.png";
        $ar->img2 = "http://39.107.251.99:8080/imgs/2.png";
        $ar->img3 = "http://39.107.251.99:8080/imgs/3.png";
        $ar->status = 1;
        $ar->save();
    }

    return 1;
});

$app->get('/test/init/agent', function () use ($app) {
    $ar = new Agent();
    $ar->phone = "13880494109";
    $ar->sex = 0;
    $ar->school_id = 1;
    $ar->room_id = 1;
    $ar->status = 1;
    $ar->realname = '杨++';
    $ar->save();

    return 1;
});

$app->get('/test/init/category', function () use ($app) {
    $category = [
        '美味食品' => [
            '休闲食品',
            '膨化食品',
        ],
        '果汁饮料' => [
            '矿泉水',
            '碳酸饮料',
        ],
    ];

    foreach($category as $key => $value) {
        $ar = new ProductCategory();
        $ar->name = $key;
        $ar->save();
        $parent = $ar->id;

        foreach($value as $v) {
            $childAr = new ProductCategory();
            $childAr->name = $v;
            $childAr->parent_id = $parent;
            $childAr->save();
        }
    }

    return 1;
});

$app->get('/test/init/productTag', function () use ($app) {
    $tags = ['精选', '活动', '特价'];
    $ar = new ProductTags();
    

    return 1;
});

$app->get('/test/agent/add', function () use ($app) {
    for($i = 0; $i < 100; $i++) {
        $ar = new Agent();
        $ar->phone = "1" . rand(1000000000, 9999999999);
        $ar->sex = rand(0, 1);
        $ar->school_id = 1;
        $ar->room_id = 1;
        $ar->manager_id = 9;
        $ar->status = 1;
        $ar->realname = 'test' . rand(0, 100);
        $ar->save();
    }

    return 1;
});