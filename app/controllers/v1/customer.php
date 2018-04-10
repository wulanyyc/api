<?php
/**
 * 商城客户
 */

use Biaoye\Model\Customer;

$app->post('/v1/customer/reg', function () use ($app) {
    $params = $_POST;

    $exsit = Customer::count("phone = " . $params['phone']);

    if ($exsit) {
        throw new BusinessException(1000, '该手机号已注册过');
    }

    $ar = new Customer();
    $ar->phone = $params['phone'];
    $ar->school_id = $params['school_id'];
    $ar->invite_code = $params['invite_code'];

    if ($ar->save()) {
        $token = $app->util->getToken($app);
        $app->redis->hmset($token, ['customer_id' => $ar->id]);

        return 1;
    } else {
        throw new BusinessException(1000, '注册失败，请联系客服');
    }
});