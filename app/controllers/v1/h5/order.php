<?php
/**
 * 下单
 */

use Biaoye\Model\Product;
use Biaoye\Model\ProductCategory;
use Biaoye\Model\CustomerAddress;
use Biaoye\Model\CustomerOrder;
use Biaoye\Model\CustomerPay;
use Biaoye\Model\Customer;
use Phalcon\Mvc\Model\Transaction\Manager;

$app->post('/v1/h5/order/confirm', function () use ($app) {
    $params = $_POST;

    if (empty($params)) {
        throw new BusinessException(1000, '参数不能为空');
    }

    $productStr = isset($params['products']) ? $params['products'] : '';

    if (empty($productStr)) {
        throw new BusinessException(1000, '商品数据不能为空');
    }

    $products = json_decode($productStr, true);

    if (empty($products)) {
        throw new BusinessException(1000, '商品数据不能为空');
    }

    if (!is_array($products)) {
        throw new BusinessException(1000, '商品数据格式不正确');
    }

    $ret = [];
    $diff = 0;

    foreach($products as $item) {
        if (isset($item['id']) && isset($item['num']) && $item['id'] > 0 && $item['num'] > 0) {
            $info = Product::findFirst($item['id']);
            $ret[$item['id']]['id']    = $item['id'];
            $ret[$item['id']]['name']  = $info->name;
            $ret[$item['id']]['title'] = $info->title;
            $ret[$item['id']]['slogan'] = $info->slogan;
            $ret[$item['id']]['num'] = $item['num'];
            $ret[$item['id']]['img']   = $info->img;
            $ret[$item['id']]['price'] = $app->product->getProductPrice($item['id']);
            $diff += $info->market_price - $ret[$item['id']]['price'];
        } else {
            throw new BusinessException(1000, '商品数据有误');
        }
    }

    sort($ret);

    return [
        'products' => $ret,
        'diff' => $diff,
        'express_fee' => $app->config->params->express_fee,
        'address' => $app->util->getDefaultAddress($app),
    ];
});


$app->post('/v1/h5/order/submit', function () use ($app) {
    $params = $_POST;
    $customerId = $app->util->getCustomerId($app);

    $app->logger->info("params:" . json_encode($params));

    if (empty($params)) {
        throw new BusinessException(1000, '参数不能为空');
    }

    // json字符串
    $productStr = isset($params['products']) ? $params['products'] : '';
    if (empty($productStr)) {
        throw new BusinessException(1000, '商品数据不能为空');
    }

    $products = json_decode($productStr, true);
    if (empty($products)) {
        throw new BusinessException(1000, '商品数据不能为空');
    }

    if (empty($params['address_id'])) {
        throw new BusinessException(1000, '参数不足');
    }

    // pay_style: 0, 1   terminal: wap, wechat
    if (!isset($params['pay_style']) || empty($params['terminal'])) {
        throw new BusinessException(1000, '支付方式或终端不能为空');
    }

    if ($params['terminal'] == 'wechat' && empty($params['openid'])) {
        throw new BusinessException(1000, '参数不足');
    }

    // 判断订单地址
    $addressInfo = CustomerAddress::findFirst($params['address_id']);

    if ($app->util->getSwitchFlag($app)) {
        $customerRoom = Customer::findFirst($customerId)->room_id;
        if ($customerRoom != $addressInfo->rec_room) {
            throw new BusinessException(1000, '晚上了，只配送本宿舍楼');
        }
    }

    $inventory = $app->data->checkInventory($app, $products, $customerId);

    if (!$inventory['status']) {
        throw new BusinessException(1000, $inventory['product'] . ", 剩余" . $inventory['num'] .",库存不足");
    }


    $priceInfo = $app->data->calculateOrderPrice($app, [
        'products' => $productStr,
        'coupon_ids' => $params['coupon_ids'],
    ]);

    try {
        $manager = new Manager();
        $transaction = $manager->get();

        if (empty($params['order_id'])) {
            $ar = new CustomerOrder();
            $ar->setTransaction($transaction);

            $ar->customer_id = $customerId;
            $ar->cart_id = isset($params['cart_id']) ? $params['cart_id'] : 0;
            $ar->products = $productStr;
            $ar->sex = $addressInfo->sex;
            $ar->address_id = $params['address_id'];
            $ar->express_fee = $app->config->params->express_fee;
            $ar->express_time = date('Y-m-d H:i:s', time() + $app->config->params->expect_delivery_minitue * 60);
            $ar->deliver_fee = $app->config->params->express_fee * $app->config->params->deliver_fee_rate;
            $ar->coupon_ids = isset($params['coupon_ids']) ? $params['coupon_ids'] : '';
            $ar->product_price = $priceInfo['product_price'];
            $ar->express_fee = $priceInfo['express_fee'];
            $ar->pay_money = $priceInfo['pay_money'];
            $ar->deliver_fee = $priceInfo['deliver_fee'];
            $ar->product_salary = $priceInfo['product_salary'];
            $ar->total_salary = $priceInfo['total_salary'];
            $ar->coupon_fee = $priceInfo['coupon_fee'];
            $ar->date = date('Ymd', time());

            if (!$ar->save()) {
                $transaction->rollback("save customer_order fail");
            }
        } else {
            $ar = CustomerOrder::findFirst($params['order_id']);
        }

        $pay = new CustomerPay();
        $pay->setTransaction($transaction);
        $pay->customer_id = $customerId;
        $pay->order_id = $ar->id;
        $pay->pay_type = $params['pay_style'];
        $pay->pay_money = $priceInfo['pay_money'];
        $pay->out_trade_no = uniqid() . '_' . $ar->id;
        $pay->date = date('Ymd', time());
        $pay->terminal = $params['terminal'];
        if ($params['terminal'] == 'wechat') {
            $pay->openid = $params['openid'];
        }

        if (!$pay->save()) {
            $transaction->rollback("save customer_pay fail");
        }

        $transaction->commit();

        $output = $app->pay->handle($app, $pay->id);
        // $app->logger->info("order_pay:" . $pay->id);
        $app->logger->info("order_data:" . json_encode($output));

        return $output;
    } catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
        $msg = $e->getMessage();
        $app->logger->error("order_fail:" . $msg);

        throw new BusinessException(1000, '下单失败');
    }
});


$app->get('/v1/h5/order/status', function () use ($app) {
    $out_trade_no = $app->request->getQuery("out_trade_no");

    $payInfo = CustomerPay::findFirst("out_trade_no='" . $out_trade_no . "'");

    if (!$payInfo) {
        throw new BusinessException(1000, '参数有误');
    }

    return [
        'status' => $payInfo->pay_result,
        'pay_money' => $payInfo->pay_money,
    ];
});


$app->get('/v1/h5/order/list/{status:\d+}', function ($status) use ($app) {
    $customerId = $app->util->getCustomerId($app);
    $orders = CustomerOrder::find([
        "conditions" => "customer_id=" . $customerId . " and status=" . $status,
        "order" => "id desc",
    ])->toArray();

    if (empty($orders)) {
        return [];
    }

    $ret = [];
    foreach($orders as $key => $order) {
        $productJson = $order['products'];
        $products = json_decode($productJson, true);

        $ret[$order['id']]['order_num'] = count($products);

        foreach($products as $product) {
            $productInfo = Product::findFirst($product['id']);
            $ret[$order['id']]['product'][] = [
                'name' => $productInfo->name,
                'title' => $productInfo->title,
                // 'slogan' => $productInfo->slogan,
                'package' => $productInfo->title,
                'price' => $app->product->getProductPrice($productInfo->id),
                'img' => $productInfo->img,
                'id' => $productInfo->id,
                'num' => $product['num'],
            ];
        }

        $ret[$order['id']]['total_price'] = $order['product_price'] + $order['express_fee'];
        $ret[$order['id']]['express_fee'] = $order['express_fee'];
        $ret[$order['id']]['order_id'] = $order['id'];
    }

    $output = [];
    foreach($ret as $item) {
        $output[] = $item;
    }

    return $output;
});


$app->get('/v1/h5/order/detail/{id:\d+}', function ($id) use ($app) {
    $customerId = $app->util->getCustomerId($app);
    $order = CustomerOrder::findFirst($id)->toArray();

    $products = json_decode($order['products'], true);

    foreach($products as $item) {
        $info = Product::findFirst($item['id']);
        $ret[$item['id']]['id']    = $item['id'];
        $ret[$item['id']]['name']  = $info->name;
        $ret[$item['id']]['title'] = $info->title;
        $ret[$item['id']]['slogan'] = $info->slogan;
        $ret[$item['id']]['num'] = $item['num'];
        $ret[$item['id']]['img']   = $info->img;
        $ret[$item['id']]['price'] = $app->product->getProductPrice($item['id']);
    }

    return [
        'products' => $ret,
        'express_fee' => $order['express_fee'],
        'address' => $app->util->getDefaultAddress($app),
        'coupon_fee' => $order['coupon_fee'],
        'product_price' => $order['product_price'],
        'total_price' => $order['product_price'] + $order['express_fee'],
        'pay_money' => $order['pay_money'],
        'order_status' => $order['status'], // 0: 待支付  1: 已支付  2: 已抢单 3: 已完成
    ];
});


// 取消订单
$app->get('/v1/h5/order/cancel/{id:\d+}', function ($id) use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $info = CustomerOrder::findFirst($id);
    if (!$info) {
        throw new BusinessException(1000, '单号有误');
    }

    if ($info->customer_id != $customerId || $info->status != 0) {
        throw new BusinessException(1000, '无操作权限');
    }

    $info->status = 4;

    if (!$info->save()) {
        throw new BusinessException(1000, '取消订单' . $info->id . '失败');
    }

    return 1;
});


// 删除订单
$app->get('/v1/h5/order/delete/{id:\d+}', function ($id) use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $info = CustomerOrder::findFirst($id);
    if (!$info) {
        throw new BusinessException(1000, '单号有误');
    }

    if ($info->customer_id != $customerId || $info->status != 3) {
        throw new BusinessException(1000, '无操作权限');
    }

    $info->status = 5;

    if (!$info->save()) {
        throw new BusinessException(1000, '删除订单' . $info->id . '失败');
    }

    return 1;
});
