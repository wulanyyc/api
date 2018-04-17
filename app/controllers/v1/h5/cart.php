<?php
/**
 * 购物车
 */

use Biaoye\Model\Product;
use Biaoye\Model\CustomerCart;
use Biaoye\Model\CustomerOrder;

$app->get('/v1/h5/cart/init', function () use ($app) {
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


// 新增
$app->get('/v1/h5/cart/new/{pid:\d+}/{num:\d+}', function ($pid, $num) use ($app) {
    $customerId = $app->util->getCustomerId($app);

    if ($num == 0 || $pid == 0) {
        throw new BusinessException(1000, '参数有误');
    }

    $cart = [];
    $cart[$pid] = [
        'id' => $pid,
        'num' => $num,
        'price' => $app->producthelper->getProductPrice($pid),
    ];

    $ar = new CustomerCart();
    $ar->customer_id = $customerId;
    $ar->cart = json_encode($cart);

    if ($ar->save()) {
        $data = [];
        $productInfo = Product::findFirst($pid);
        $data[$pid] = [
            'id' => $pid,
            'num' => $num,
            'name' => $productInfo->name,
            'img'  => $productInfo->img,
            'price' => $app->producthelper->getProductPrice($pid),
        ];

        return [
            'cart_id'  => $ar->id,
            'products' => $data,
        ];
    } else {
        throw new BusinessException(1000, '添加失败');
    }
});


// 更新购物车
$app->post('/v1/h5/cart/update/{cid:\d+}', function ($cid) use ($app) {
    $customerId = $app->util->getCustomerId($app);

    if ($cid == 0) {
        throw new BusinessException(1000, '参数有误');
    }

    $ar = CustomerCart::findFirst($cid);
    if (!$ar) {
        throw new BusinessException(1000, '未找到购物车信息');
    }

    $customerId = $app->util->getCustomerId($app);

    if ($customerId != $ar->customer_id) {
        throw new BusinessException(1000, '无操作权限');
    }

    $updateCart = $app->request->getPost("cart");
    if (empty($updateCart)) {
        throw new BusinessException(1000, '购物车信息有误');
    }

    $cartInfo = json_decode($updateCart, true);
    if (empty($cartInfo)) {
        throw new BusinessException(1000, '购物车信息有误');
    }

    $cart = [];
    foreach($cartInfo as $item) {
        if (is_int($item['id']) && is_int($item['num']) && $item['id'] > 0 && $item['num'] > 0) {
            $cart[$item['id']] = [
                'id' => $item['id'],
                'num' => $item['num'],
                'price' => $app->producthelper->getProductPrice($item['id']),
            ];
        } else {
            throw new BusinessException(1000, '参数有误');
        }
    }

    $ar->cart = json_encode($cart);

    if ($ar->save()) {
        return 1;
    } else {
        throw new BusinessException(1000, '提交失败');
    }
});


// 增加
$app->get('/v1/h5/cart/plus/{cid:\d+}/product/{pid:\d+}/{num:\d+}', function ($cid, $pid, $num) use ($app) {
    if ($num == 0) {
        throw new BusinessException(1000, '参数有误');
    }

    $cartInfo = CustomerCart::findFirst($cid);
    if (!$cartInfo) {
        throw new BusinessException(1000, '未找到购物车信息');
    }

    $customerId = $app->util->getCustomerId($app);

    if ($customerId != $cartInfo->customer_id) {
        throw new BusinessException(1000, '无操作权限');
    }

    if ($num == 0) {
        throw new BusinessException(1000, '参数有误');
    }

    $cart = json_decode($cartInfo->cart, true);
    
    if (!isset($cart[$pid])) {
        $cart[$pid] = [
            'id' => $pid,
            'num' => $num,
            'price' => $app->producthelper->getProductPrice($pid),
        ];
    } else {
        $cart[$pid]['num'] = $cart[$pid]['num'] + $num;
        $cart[$pid]['price'] = $app->producthelper->getProductPrice($pid);
    }

    $cartInfo->cart = json_encode($cart);

    if ($cartInfo->save()) {
        return 1;
    } else {
        throw new BusinessException(1000, '更新购物车失败');
    }
});

// 减少
$app->get('/v1/h5/cart/minus/{cid:\d+}/product/{pid:\d+}/{num:\d+}', function ($cid, $pid, $num) use ($app) {
    if ($num == 0) {
        throw new BusinessException(1000, '参数有误');
    }

    $cartInfo = CustomerCart::findFirst($cid);
    if (!$cartInfo) {
        throw new BusinessException(1000, '未找到购物车信息');
    }

    $customerId = $app->util->getCustomerId($app);

    if ($customerId != $cartInfo->customer_id) {
        throw new BusinessException(1000, '无操作权限');
    }

    $cart = json_decode($cartInfo->cart, true);
    
    if (isset($cart[$pid])) {
        $cart[$pid]['num'] = $cart[$pid]['num'] - $num;

        if ($cart[$pid]['num'] == 0) {
            unset($cart[$pid]);
        } else {
            $cart[$pid]['price'] = $app->producthelper->getProductPrice($pid);
        }
    }

    $cartInfo->cart = json_encode($cart);

    if ($cartInfo->save()) {
        return 1;
    } else {
        throw new BusinessException(1000, '更新购物车失败');
    }
});


// 删除单品
$app->post('/v1/h5/cart/del/{cid:\d+}', function ($cid) use ($app) {
    $cartInfo = CustomerCart::findFirst($cid);
    if (!$cartInfo) {
        throw new BusinessException(1000, '未找到购物车信息');
    }

    $ids = $app->request->getPost("ids");
    if (empty($ids)) {
        throw new BusinessException(1000, '参数有误');
    }

    $customerId = $app->util->getCustomerId($app);

    if ($customerId != $cartInfo->customer_id) {
        throw new BusinessException(1000, '无操作权限');
    }

    $cart = json_decode($cartInfo->cart, true);
    
    $pids = explode(',', $ids);

    foreach($pids as $pid) {
        if (isset($cart[$pid])) {
            unset($cart[$pid]);
        }
    }

    $cartInfo->cart = json_encode($cart);

    if ($cartInfo->save()) {
        return 1;
    } else {
        throw new BusinessException(1000, '更新购物车失败');
    }

    return 1;
});
