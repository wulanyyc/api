<?php
/**
 * 购物车
 */

use Biaoye\Model\Product;
use Biaoye\Model\CustomerCart;
use Biaoye\Model\CustomerOrder;

// 购物车列表
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
        $data[$key]['price'] = $app->product->getProductPrice($item['id']);
    }

    return [
        'cart_id'  => $cart->id,
        'products' => $data,
    ];
});


// 新增购物车
$app->get('/v1/h5/cart/new/{pid:\d+}/{num:\d+}', function ($pid, $num) use ($app) {
    $customerId = $app->util->getCustomerId($app);

    if ($num == 0 || $pid == 0) {
        throw new BusinessException(1000, '参数有误');
    }

    $cartInfo = CustomerCart::findFirst("customer_id=" . $customerId);
    if ($cartInfo) {
        $cart = [];
        $temp = [
            'id' => $pid,
            'num' => $num,
        ];
        $cart[] = $temp;
        $cartInfo->cart = json_encode($cart);
        
        if ($cartInfo->save()) {
            return [
                'cart_id'  => $cartInfo->id,
            ];
        } else {
            throw new BusinessException(1000, '添加失败');
        }
    } else {
        $cart = [];
        $cart[] = [
            'id' => $pid,
            'num' => $num,
        ];

        $ar = new CustomerCart();
        $ar->customer_id = $customerId;
        $ar->cart = json_encode($cart);

        if ($ar->save()) {
            return [
                'cart_id'  => $ar->id,
            ];
        } else {
            throw new BusinessException(1000, '添加失败');
        }
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
        $item['id'] = intval($item['id']);
        $item['num'] = intval($item['num']);
        if ($item['id'] > 0 && $item['num'] > 0) {
            $cart[$item['id']] = [
                'id' => $item['id'],
                'num' => $item['num'],
            ];
        }
    }

    sort($cart);
    $ar->cart = json_encode($cart);

    if ($ar->save()) {
        return 1;
    } else {
        throw new BusinessException(1000, '提交失败');
    }
});


// 增加
$app->get('/v1/h5/cart/plus/product/{pid:\d+}/{num:\d+}', function ($pid, $num) use ($app) {
    $cid = $app->util->getCartId($app);

    if ($num == 0) {
        throw new BusinessException(1000, '参数有误');
    }

    $cartInfo = CustomerCart::findFirst($cid);
    $cart = json_decode($cartInfo->cart, true);
    
    $exsit = false;
    foreach($cart as $key => $item) {
        if ($item['id'] == $pid) {
            $cart[$key]['num'] = intval($item['num']) + $num;
            $exsit = true;
        }
    }

    if (!$exsit) {
        array_push($cart, [
            'id' => $pid,
            'num' => $num,
        ]);
    }

    $num = count($cart);
    $cartInfo->cart = json_encode($cart);

    if ($cartInfo->save()) {
        return $num;
    } else {
        throw new BusinessException(1000, '更新购物车失败');
    }
});


// 减少
$app->get('/v1/h5/cart/minus/product/{pid:\d+}/{num:\d+}', function ($pid, $num) use ($app) {
    $cid = $app->util->getCartId($app);

    if ($num == 0 || $pid == 0) {
        throw new BusinessException(1000, '参数有误');
    }

    $cartInfo = CustomerCart::findFirst($cid);
    $customerId = $app->util->getCustomerId($app);

    $cart = json_decode($cartInfo->cart, true);
    
    foreach($cart as $key => $item) {
        if ($item['id'] == $pid) {
            $cart[$key]['num'] = intval($item['num']) - $num;
            if ($cart[$key]['num'] == 0) {
                unset($cart[$key]);
            }
        }
    }

    $num = count($cart);
    $cartInfo->cart = json_encode($cart);

    if ($cartInfo->save()) {
        return $num;
    } else {
        throw new BusinessException(1000, '更新购物车失败');
    }
});


// 删除单品
$app->post('/v1/h5/cart/del', function () use ($app) {
    $cid = $app->util->getCartId($app);

    $cartInfo = CustomerCart::findFirst($cid);

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
        foreach($cart as $key => $item) {
            if ($pid == $item['id']) {
                unset($cart[$key]);
            }
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
