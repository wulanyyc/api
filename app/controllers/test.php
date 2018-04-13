<?php
use Biaoye\Model\Agent;
use Biaoye\Model\Customer;
use Biaoye\Model\CustomerOrder;
use Biaoye\Model\CustomerCart;
use Biaoye\Model\CustomerAddress;
use Biaoye\Model\Product;
use Biaoye\Model\ProductCategory;
use Biaoye\Model\ProductTag;
use Biaoye\Model\ProductTagRelation;

// 获取短信验证码
$app->get('/test/product/add', function () use ($app) {
    for ($i = 0; $i < 10; $i++) {
        $ar = new Product();
        $ar->factory = $app->util->getChar();
        $ar->category = 1;
        $ar->sub_category = rand(2,3);
        $ar->name = $app->util->getChar();
        $ar->price = rand(1, 900) . '.' . rand(0, 99);
        $ar->title = $app->util->getChar();
        $ar->slogan = $app->util->getChar();
        $ar->img = "http://39.107.251.99:8080/imgs/1.png";
        $ar->img1 = "http://39.107.251.99:8080/imgs/1.png";
        $ar->img2 = "http://39.107.251.99:8080/imgs/2.png";
        $ar->img3 = "http://39.107.251.99:8080/imgs/3.png";
        $ar->status = 1;
        $ar->save();


        $relation = new ProductTagRelation();
        $relation->product_id = $ar->id;
        $relation->tag_id = rand(1,3);
        $relation->save();
    }

    for ($i = 0; $i < 10; $i++) {
        $ar = new Product();
        $ar->factory = $app->util->getChar();
        $ar->category = 4;
        $ar->sub_category = rand(5,6);
        $ar->name = $app->util->getChar();
        $ar->price = rand(1, 900) . '.' . rand(0, 99);
        $ar->title = $app->util->getChar();
        $ar->slogan = $app->util->getChar();
        $ar->img = "http://39.107.251.99:8080/imgs/2.png";
        $ar->img1 = "http://39.107.251.99:8080/imgs/1.png";
        $ar->img2 = "http://39.107.251.99:8080/imgs/2.png";
        $ar->img3 = "http://39.107.251.99:8080/imgs/3.png";
        $ar->status = 1;
        $ar->save();

        $relation = new ProductTagRelation();
        $relation->product_id = $ar->id;
        $relation->tag_id = rand(1,3);
        $relation->save();
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
            $childAr->img = "http://39.107.251.99:8080/imgs/2.png";
            $childAr->save();
        }
    }

    return 1;
});


$app->get('/test/init/product/tag', function () use ($app) {
    $tags = ['精选', '活动', '特价'];
    foreach($tags as $value) {
        $ar = new ProductTag();
        $ar->name = $value;
        $ar->save();
    }

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


$app->get('/test/init/cart', function () use ($app) {
    $cid = 1;
    $num = 20;

    $temp = [];
    for($m = 0; $m < 10; $m++) {
        $cart = [];
        for($i = 0; $i < 5; $i++) {
            $item = [
                'id' => rand(1, 20),
                'num' => rand(1, 5),
            ];
            $cart[$item['id']] = $item;
        }

        $temp[] = $cart;
    }

    $tempNum = count($temp) - 1;

    for($j=0; $j < $num; $j++) {
        $rand = rand(0, $tempNum);
        $ar = new CustomerCart();
        $ar->customer_id = $cid;
        $ar->cart = json_encode($temp[$rand]);
        $ar->save();
    }

    return 1;
});


$app->get('/test/init/address', function () use ($app) {
    $cid = 1;
    $num = 10;

    for($j=0; $j < $num; $j++) {
        $ar = new CustomerAddress();
        $ar->customer_id = $cid;
        $ar->rec_name = $app->util->getChar(3);
        $ar->rec_phone = 1 . rand(1000000000, 9999999999);
        $ar->rec_school = 1;
        $ar->rec_room = 1;
        $ar->sex = rand(0, 1);
        $ar->rec_detail = $app->util->getChar();
        $ar->save();
    }

    return 1;
});


$app->get('/test/init/order', function () use ($app) {
    $cid = 1;
    $num = 100;

    for($i = 0; $i < $num; $i++) {
        $addressId = rand(1, 10);
        $sex = CustomerAddress::findFirst($addressId)->sex;
        $productPrice = rand(10, 1000);

        $ar = new CustomerOrder();
        $ar->customer_id = $cid;
        $ar->cart_id = rand(1, 20);
        $ar->address_id = $addressId;
        $ar->rec_sex = $sex;
        $ar->product_price = $productPrice;
        $ar->pay_money = $productPrice;
        $ar->express_fee = $app->config->params->express_fee;
        $ar->express_time = date('Y-m-d H:i:s', time() + 1800);
        $ar->deliver_fee = $app->config->params->deliver_fee_rate * $app->config->params->express_fee;
        $ar->product_salary = $productPrice * $app->config->params->order_salary_rate;
        $ar->total_salary = $ar->product_salary + $ar->deliver_fee;
        $ar->date = date('Ymd', time());
        $ar->status = 1;
        $ar->save();
    }

    return 1;
});


$app->get('/test/order/get/{id:\d+}', function ($id) use ($app) {
    $app->redis->setex($app->config->params->get_order_prefix . $id, 86400, 1);
    return 1;
});


