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


$app->post('/open/sms/app/vcode', function () use ($app) {
    $phone = $app->request->getPost("phone");
    $code  = $app->request->getPost("code");
    $inviteCode = $app->request->getPost("invite_code");

    if (empty($phone) || empty($code)) {
        throw new BusinessException(1000, '参数不正确');
    }

    $key = $phone . '_smscode';
    $vcode = $app->redis->get($key);

    if (!empty($vcode) && $vcode == $code) {
        // TODO 验证邀请码是否合法
        // $exsit = Agent::findFirst("phone = " . $inviteCode . " and manager_flag = 1")->count();

        // if ($exsit == 0) {
        //     throw new Exception(1000, '邀请码有误');
        // }

        $token = uniqid();

        $app->redis->setex($phone . '_token', $app->config->login_cache_time, $token);

        $app->redis->hmset($token, [
            'phone' => $phone,
            'invite_code' => $inviteCode
        ]);

        $app->redis->expire($token, $app->config->login_cache_time);

        return [
            'token' => $token
        ];
    } else {
        throw new BusinessException(1000, '验证码有误');
    }
});


$app->post('/open/login', function () use ($app) {
    $phone = $app->request->getPost("phone");
    $code  = $app->request->getPost("code");

    if (empty($phone) || empty($code)) {
        throw new BusinessException(1000, '参数不正确');
    }

    $key = $phone . '_smscode';
    $vcode = $app->redis->get($key);

    if (!empty($vcode) && $vcode == $code) {
        // 阻止多台同时登陆
        $exsitToken = $app->redis->get($phone . '_token');
        if ($exsitToken) {
            $app->redis->del($exsitToken);
        }

        $token = uniqid();

        $app->redis->setex($phone . '_token', $app->config->login_cache_time, $token);
        
        $app->redis->hmset($token, ['phone' => $phone]);
        $app->redis->expire($token, 86400);

        return [
            'token' => $token
        ];
    } else {
        throw new BusinessException(1000, '验证码有误');
    }
});