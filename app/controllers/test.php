<?php
use Biaoye\Model\Agent;
use Biaoye\Model\AgentInventory;
use Biaoye\Model\AgentInventoryRecords;
use Biaoye\Model\Customer;
use Biaoye\Model\CustomerOrder;
use Biaoye\Model\CustomerCart;
use Biaoye\Model\CustomerAddress;
use Biaoye\Model\Product;
use Biaoye\Model\ProductListSchool;
use Biaoye\Model\ProductCategory;
use Biaoye\Model\ProductTag;
use Biaoye\Model\ProductTagRelation;
use Biaoye\Model\Company;
use Biaoye\Model\CompanyInventory;
use Biaoye\Model\CustomerCoupon;
use Biaoye\Model\CustomerCouponUse;

// 获取短信验证码
$app->get('/test/init/product', function () use ($app) {
    for ($i = 0; $i < 50; $i++) {
        $ar = new Product();
        $ar->factory = $app->util->getChar();
        $ar->category = 1;
        $ar->sub_category = rand(2,3);
        $ar->name = $app->util->getChar();
        $ar->price = rand(1, 900) . '.' . rand(0, 99);
        $ar->title = $app->util->getChar();
        $ar->slogan = $app->util->getChar();
        $ar->market_price = rand(1, 900) . '.' . rand(0, 99);
        $ar->brand = '麦当劳';
        $ar->birth_date = date('Ymd', time());
        $ar->valid_date = rand(1, 365);
        $ar->province = '广东';
        $ar->place = '中国四川';
        $ar->package = '袋装';
        $ar->weight = '500g';
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

    for ($i = 0; $i < 50; $i++) {
        $ar = new Product();
        $ar->factory = $app->util->getChar();
        $ar->category = 4;
        $ar->sub_category = rand(5,6);
        $ar->name = $app->util->getChar();
        $ar->price = rand(1, 900) . '.' . rand(0, 99);
        $ar->title = $app->util->getChar();
        $ar->slogan = $app->util->getChar();
        $ar->market_price = rand(1, 900) . '.' . rand(0, 99);
        $ar->brand = '麦当劳';
        $ar->birth_date = date('Ymd', time());
        $ar->valid_date = rand(1, 365);
        $ar->province = '广东';
        $ar->place = '中国四川';
        $ar->package = '袋装';
        $ar->weight = '500g';
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
                'price' => rand(1, 100),
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
    $num = 500;

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

        $app->redis->setex($app->config->params['get_order_prefix'] . $ar->id, 86400, 0);
    }

    return 1;
});


$app->get('/test/init/order/rob', function () use ($app) {
    for($i = 60; $i < 100; $i++) {
        $app->redis->setex($app->config->params['get_order_prefix'] . $i, 86400 * 3, 0);
    }

    return 1;
});


$app->get('/test/order/get/{id:\d+}', function ($id) use ($app) {
    $app->redis->setex($app->config->params->get_order_prefix . $id, 86400, 1);
    return 1;
});


$app->get('/test/init/company', function () use ($app) {
    for ($i=0; $i < 5; $i++) {
        $ar = new Company();
        $ar->name = $app->util->getChar(5);
        $ar->parent_id = 0;
        $ar->save();
    }

    return 1;
});


$app->get('/test/init/company/inventory', function () use ($app) {
    for ($i=0; $i < 20; $i++) {
        $ar = new CompanyInventory();
        $ar->product_id = $i + 1;
        $ar->company_id = 1;
        $ar->num = rand(1, 100);
        $ar->save();
    }

    return 1;
});


$app->get('/test/init/agent/inventory', function () use ($app) {
    for($i=0; $i < 300; $i++) {
        $agentNum = Agent::count();
        $agentId = rand(9, 11);
        $batch = $app->util->uuid();

        $air = new AgentInventoryRecords();
        $air->operator = 1;
        $air->product_id = rand(1, 20);
        $air->status = 1;
        $air->num = rand(1, 100);
        $air->agent_id = $agentId;
        $air->batch_id = $batch;
        $air->save();

        $agentInfo = Agent::findFirst($agentId);

        $ai = AgentInventory::findFirst("product_id = " . $air->product_id . " and agent_id=" . $agentId);
        if (empty($ai)) {
            $ai = new AgentInventory();
            $ai->product_id = $air->product_id;
            $ai->agent_id = $agentId;
            $ai->num = $air->num;
            $ai->school_id = $agentInfo->school_id;
            $ai->room_id = $agentInfo->room_id;
            $ai->save();
        } else {
            $ai->product_id = $air->product_id;
            $ai->agent_id = $agentId;
            $ai->num = $ai->num;
            $ai->school_id = $agentInfo->school_id;
            $ai->room_id = $agentInfo->room_id;
            $ai->save();
        }
    }

    return 1;
});


$app->get('/test/init/school/inventory', function () use ($app) {
    $stats = AgentInventory::find([
        "conditions" => "status=0",
        "columns" => 'school_id, product_id, sum(num) as total',
        "group" => 'school_id,product_id',
    ])->toArray();

    foreach($stats as $item) {
        $info = Product::findFirst($item['product_id']);

        $ar = new ProductListSchool();
        $ar->school_id = $item['school_id'];
        $ar->product_id = $item['product_id'];
        $ar->category = $info->category;
        $ar->sub_category = $info->sub_category;
        $ar->name = $info->name;
        $ar->price = $info->price;
        $ar->market_price = $info->market_price;
        $ar->num = $item['total'];
        $ar->title = $info->title;
        $ar->slogan = $info->slogan;
        $ar->brand = $info->brand;
        $ar->img = $info->img;
        $ar->tags = $info->tags;
        $ar->status = 1;
        $ar->save();
    }

    return 1;
});

$app->get('/test/init/product/tag', function () use ($app) {
    $tags = ProductTagRelation::find("status=0")->toArray();

    $data = [];
    foreach($tags as $tag) {
        $data[$tag['product_id']][] = $tag['tag_id'];
    }

    foreach($data as $key => $item) {
        $data[$key] = array_unique($item);
        $data[$key] = implode(',', $data[$key]);

        $up = Product::findFirst($key);
        if ($up) {
            $up->tags = $data[$key];
            $up->save();
        }
    }

    return 1;
});

$app->get('/test/cart', function () use ($app) {
    $data = [
        [
            'id' => 1,
            'num' => 3,
        ],
        [
            'id' => 2,
            'num' => 1,
        ],
    ];

    echo json_encode($data);
});


$app->get('/test/init/coupon', function () use ($app) {
    for ($i=0; $i < 10; $i++) {
        $type = rand(1, 3);

        if ($type == 1) {
            $ar = new CustomerCoupon();
            $ar->type = $type;
            $ar->name = $app->util->getChar(5);
            $ar->money = rand(1, 100);
            $ar->desc = $app->util->getChar(10);
            $ar->config = json_encode([
                'days' => rand(3, 50)
            ]);
            $ar->save();
        }

        if ($type == 2) {
            $ar = new CustomerCoupon();
            $ar->type = $type;
            $ar->name = $app->util->getChar(5);
            $ar->money = rand(1, 10);
            $ar->desc = $app->util->getChar(10);
            $ar->config = json_encode([
                'limit_money' => rand(50, 100),
                'start_date' => 20180414,
                'end_date' => 20180430,
                'category' => [1],
            ]);
            $ar->save();
        }

        if ($type == 3) {
            $ar = new CustomerCoupon();
            $ar->type = $type;
            $ar->name = $app->util->getChar(5);
            $ar->money = rand(1, 10);
            $ar->desc = $app->util->getChar(10);
            $ar->config = json_encode([
                'limit_money' => rand(50, 100),
                'start_date' => 20180414,
                'end_date' => 20180430,
                'factory' => $app->util->getChar(),
            ]);
            $ar->save();
        }
    }

    return 1;
});


$app->get('/test/init/coupon/get/{uid:\d+}/{cid:\d+}', function ($uid, $cid) use ($app) {
    $info = CustomerCoupon::findFirst($cid);

    if ($info->type == 1) {
        $config = json_decode($info->config, true);
        $startDate = date('Ymd', time());
        $endDate = date('Ymd', time() + $config['days'] * 86400);
    }

    if ($info->type == 2 || $info->type == 3) {
        $config = json_decode($info->config, true);

        $startDate = $config['start_date'];
        $endDate   = $config['end_date'];
    }

    $ar = new CustomerCouponUse();
    $ar->customer_id = $uid;
    $ar->coupon_id = $cid;
    $ar->start_date = $startDate;
    $ar->end_date = $endDate;
    $ar->save();

    return 1;
});
