<?php

$app->post('/open/sms/code', function () use ($app) {
    $phone = $app->request->getPost("phone");
    if (empty($phone)) {
        throw new BusinessException(1000, '手机号不能为空');
    }

    $key = $phone . '_smscode';
    if ($app->redis->get($key)) {
        throw new BusinessException(1000, '已发送，请查看短信');
    } else {
        $code = rand(100000, 999999);
        // TODO send sms

        $app->redis->setex($key, 600, $code);
        return $code;
    }
});


$app->post('/open/sms/vcode', function () use ($app) {
    $phone = $app->request->getPost("phone");
    $code  = $app->request->getPost("code");

    if (empty($phone) || empty($code)) {
        throw new BusinessException(1000, '参数不正确');
    }

    $key = $phone . '_smscode';
    $vcode = $app->redis->get($key);

    if (!empty($vcode) && $vcode == $code) {
        return 1;
    } else {
        throw new BusinessException(1000, '验证码有误');
    }
});