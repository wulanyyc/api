<?php
/**
 * 优惠券
 */

use Biaoye\Model\Product;
use Biaoye\Model\CustomerCart;
use Biaoye\Model\CustomerOrder;

$app->get('/v1/h5/coupon/list', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $cart = CustomerCart::findFirst([
        'conditions' => "customer_id=" . $customerId,
        'order' => 'id desc'
    ]);

    if (!$cart) {
        return [];
    }

    $data = json_decode($cart->cart, true);

    if (empty($data)){
        return [];
    }

    foreach($data as $key => $item) {
        $productInfo = Product::findFirst($item['id']);
        $data[$key]['name']  = $productInfo->name;
        $data[$key]['img']   = $productInfo->img;
        $data[$key]['price'] = $app->producthelper->getProductPrice($item['id']);
    }

    return [
        'cart_id'  => $cart->id,
        'products' => $data,
    ];
});


