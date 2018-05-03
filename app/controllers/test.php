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
use Biaoye\Model\NotifyMessage;
use Biaoye\Model\AgentMoneyList;
use Biaoye\Model\CustomerSearchHistory;

// 获取短信验证码
$app->get('/test/init/product', function () use ($app) {
    $products = [
        [
            'factory' => '康师傅',
            'category' => 1,
            'sub_category' => 2,
            'name' => '脆海带香锅牛肉',
            'price' => 3,
            'market_price' => 3.5,
            'title' => '脆海带香锅牛肉',
            'slogan' => '脆滑香嫩 来一碗牛肉面',
            'brand' => '康师傅',
            'img' => 'http://qingrongby.com/imgs/nrm.png',
        ],
        [
            'factory' => '冰红茶',
            'category' => 4,
            'sub_category' => 6,
            'name' => '康饮-500ml冰红茶',
            'price' => 4,
            'market_price' => 4.5,
            'title' => '康饮-500ml冰红茶',
            'slogan' => '去火',
            'brand' => '冰红茶',
            'img' => 'http://qingrongby.com/imgs/bhc.png'
        ],
        [
            'factory' => '冰红茶',
            'category' => 4,
            'sub_category' => 6,
            'name' => '康饮-500ml茉莉蜜茶',
            'price' => 4,
            'market_price' => 4.5,
            'title' => '脆海带香锅牛肉',
            'slogan' => '甜如初夏',
            'brand' => '冰红茶',
            'img' => 'http://qingrongby.com/imgs/mlmc.png'
        ],
        [
            'factory' => '脉动',
            'category' => 4,
            'sub_category' => 6,
            'name' => '脉动青橘600ML',
            'price' => 4,
            'market_price' => 4.5,
            'title' => '脉动青橘600ML',
            'slogan' => '来一口 精神百倍',
            'brand' => '脉动',
            'img' => 'http://qingrongby.com/imgs/mdqj.png'
        ],
        [
            'factory' => '脉动',
            'category' => 4,
            'sub_category' => 6,
            'name' => '脉动青芒600ML',
            'price' => 4,
            'market_price' => 4.5,
            'title' => '脉动青芒600ML',
            'slogan' => '来一口 精神百倍',
            'brand' => '脉动',
            'img' => 'http://qingrongby.com/imgs/mdqm.png'
        ],
        [
            'factory' => '泉利堂',
            'category' => 1,
            'sub_category' => 2,
            'name' => '泉利堂话梅条128g',
            'price' => 6,
            'market_price' => 8,
            'title' => '泉利堂话梅条128g',
            'slogan' => '酸酸甜甜 总是美',
            'brand' => '泉利堂',
            'img' => 'http://qingrongby.com/imgs/hm.png'
        ],
        [
            'factory' => '泉利堂',
            'category' => 1,
            'sub_category' => 2,
            'name' => '泉利堂咸榄丝-238g',
            'price' => 6.5,
            'market_price' => 8,
            'title' => '泉利堂咸榄丝-238g',
            'slogan' => '咸榄丝好吃 来一点',
            'brand' => '泉利堂',
            'img' => 'http://qingrongby.com/imgs/xgs.png'
        ],
        [
            'factory' => '上好佳',
            'category' => 1,
            'sub_category' => 3,
            'name' => '田园薯片（宫保鸡丁）',
            'price' => 9,
            'market_price' => 9.5,
            'title' => '田园薯片（宫保鸡丁）',
            'slogan' => '脆脆的 才好吃',
            'brand' => '上好佳',
            'img' => 'http://qingrongby.com/imgs/gbjd.png'
        ],
        [
            'factory' => '上好佳',
            'category' => 1,
            'sub_category' => 3,
            'name' => '田园薯片（烤肉味）',
            'price' => 9,
            'market_price' => 10,
            'title' => '田园薯片（烤肉味）',
            'slogan' => '脆脆的 才好吃',
            'brand' => '上好佳',
            'img' => 'http://qingrongby.com/imgs/tysp.png'
        ],
        [
            'factory' => '上好佳',
            'category' => 1,
            'sub_category' => 3,
            'name' => '鲜虾条',
            'price' => 8,
            'market_price' => 8.5,
            'title' => '鲜虾条',
            'slogan' => '脆脆的 才好吃',
            'brand' => '上好佳',
            'img' => 'http://qingrongby.com/imgs/xxt.png'
        ],
    ];

    foreach ($products as $product) {
        $ar = new Product();
        $ar->factory = $product['factory'];
        $ar->category = $product['category'];
        $ar->sub_category = $product['sub_category'];
        $ar->name = $product['name'];
        $ar->price = $product['price'];
        $ar->title = $product['title'];
        $ar->slogan = $product['slogan'];
        $ar->market_price = $product['market_price'];
        $ar->brand = $product['brand'];
        $ar->birth_date = date('Ymd', time());
        $ar->valid_date = rand(1, 365);
        $ar->province = '广东';
        $ar->place = '中国四川';
        $ar->package = '袋装';
        $ar->weight = '500g';
        $ar->img = $product['img'];
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
    $num = 100;

    for($i = 0; $i < $num; $i++) {
        $addressId = rand(1, 4);
        $sex = CustomerAddress::findFirst($addressId)->sex;
        $productPrice = rand(10, 1000);

        $ar = new CustomerOrder();
        $ar->customer_id = rand(2, 6);
        $ar->cart_id = 1;
        $ar->products = CustomerCart::findFirst($ar->cart_id)->cart;
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
        $ar->status = rand(0, 4);
        $ar->save();

        $app->redis->setex($app->config->params['get_order_prefix'] . $ar->id, 86400 * 7, 0);
    }

    return 1;
});


$app->get('/test/init/order/rob', function () use ($app) {
    for($i = 60; $i <= 70; $i++) {
        $app->redis->setex($app->config->params['get_order_prefix'] . $i, 86400 * 7, 0);
    }

    return 1;
});


$app->get('/test/order/get/{id:\d+}', function ($id) use ($app) {
    $app->redis->setex($app->config->params->get_order_prefix . $id, 86400, 1);
    return 1;
});


$app->get('/test/init/company', function () use ($app) {
    // for ($i=0; $i < 5; $i++) {
        $ar = new Company();
        $ar->name = '庆荣科技';
        $ar->parent_id = 0;
        $ar->save();
    // }

    return 1;
});


$app->get('/test/init/company/inventory', function () use ($app) {
    for ($i=0; $i < 10; $i++) {
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
        $air->need_num = rand(1, 100);
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
    for ($i=0; $i < 20; $i++) {
        $type = rand(1, 3);

        if ($type == 1) {
            $ar = new CustomerCoupon();
            $ar->type = $type;
            $ar->name = $app->util->getChar(5);
            $ar->money = rand(1, 100);
            $ar->desc = $app->util->getChar(10);
            $ar->config = json_encode([
                'days' => rand(3, 50),
                'limit_money' => rand(10, 30),
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
                'limit_money' => rand(30, 50),
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
                'limit_money' => rand(30, 50),
                'start_date' => 20180414,
                'end_date' => 20180430,
                'factory' => [$app->util->getChar()],
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

$app->get('/test/init/message', function () use ($app) {
    for ($i = 0; $i < 30; $i++) {
        $ar = new NotifyMessage();
        $ar->title = $app->util->getChar(8);
        $ar->message = $app->util->getChar(50);
        $ar->date = date('Ymd', time());
        $ar->terminal = rand(0, 1);
        $ar->save();
    }

    return 1;
});

$app->get('/test/init/money/income/list', function () use ($app) {
    for ($i = 0; $i < 100; $i++) {
        $ar = new AgentMoneyList();
        $ar->agent_id = rand(9, 14);
        $ar->money = rand(20, 50);
        $ar->operator = 0;
        $ar->order_id = rand(1, 100);
        $ar->date = date("Ymd", time());
        $ar->save();
    }

    return 1;
});

$app->get('/test/init/money/get/list', function () use ($app) {
    for ($i = 0; $i < 100; $i++) {
        $ar = new AgentMoneyList();
        $ar->agent_id = rand(9, 14);
        $ar->money = rand(1, 30);
        $ar->operator = 1;
        $ar->date = date("Ymd", time());
        $ar->save();
    }

    return 1;
});

$app->get('/test/init/search/history', function () use ($app) {
    for ($i = 0; $i < 50; $i++) {
        $ar = new CustomerSearchHistory();
        $ar->customer_id = rand(1, 3);
        $ar->search_text = $app->util->getChar(8);
        $ar->save();
    }

    return 1;
});

$app->get('/test/sms', function () use ($app) {
    $app->util->sendSms($app, [13880494109], '你的验证码为123123，5分钟内有效');

    return 1;
});
