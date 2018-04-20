<?php
/**
 * 下单
 */

use Biaoye\Model\Product;
use Biaoye\Model\ProductCategory;
use Biaoye\Model\CustomerAddress;
use Biaoye\Model\CustomerOrder;
use Biaoye\Model\CustomerPay;
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

    return [
        'products' => $ret,
        'diff' => $diff,
        'express_fee' => $app->config->params->express_fee,
        'address' => $app->util->getDefaultAddress($app),
    ];
});


$app->post('/v1/h5/order/submit', function () use ($app) {
    $params = $_POST;

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

    if (empty($params['address_id']) || empty($params['coupon_ids'])) {
        throw new BusinessException(1000, '参数不足');
    }

    // pay_style: 0, 1   terminal: wap, pc, wechat
    if (empty($params['pay_style']) || empty($params['terminal'])) {
        throw new BusinessException(1000, '支付方式或终端不能为空');
    }

    $inventory = $app->data->checkInventory($app, $products);

    if (!$inventory['status']) {
        throw new BusinessException(1000, $inventory['product'] . ", 剩余" . $inventory['num'] .",库存不足");
    }

    $customerId = $app->util->getCustomerId($app);

    $priceInfo = $app->data->calculateOrderPrice($app, [
        'products' => $productStr,
        'coupon_ids' => $params['coupon_ids'],
    ]);

    $addressInfo = CustomerAddress::findFirst($params['address_id']);

    try {
        $manager = new Manager();
        $transaction = $manager->get();

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

        if ($ar->save()) {
            $transaction->rollback("save customer_order fail");
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

        if ($pay->save()) {
            $transaction->rollback("save customer_pay fail");
        }

        $ret = $app->pay->handle($app, $pay->id);

        return $ret;
    } catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
        $msg = $e->getMessage();
        $app->logger->error("order_fail:" . $msg);

        throw new BusinessException(1000, '下单失败');
    }
});
