<?php
use Biaoye\Model\Agent;
use Biaoye\Model\Customer;

// 获取短信验证码
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

        $app->redis->setex($key, 60, $code);
        return $code;
    }
});

// 验证app验证码
$app->post('/open/sms/app/vcode', function () use ($app) {
    $phone = $app->request->getPost("phone");
    $code  = $app->request->getPost("code");
    $inviteCode = $app->request->getPost("invite_code");

    if (empty($phone) || empty($code) || empty($inviteCode)) {
        throw new BusinessException(1000, '参数不正确');
    }

    if ($inviteCode == $phone) {
        throw new BusinessException(1000, '手机号和邀请码不能相同');
    }

    $key = $phone . '_smscode';
    $vcode = $app->redis->get($key);

    if (!empty($vcode) && $vcode == $code) {
        // TODO 验证邀请码是否合法
        $exsit = Agent::count("phone = " . $inviteCode . " and manager_flag = 1 and status = 1");

        if ($exsit == 0) {
            throw new Exception(1000, '邀请码有误，请检查');
        }

        $token = $app->util->uuid();

        $app->redis->setex($phone . '_token', $app->config->login_cache_time, $token);

        $app->redis->hmset($token, [
            'phone' => $phone
        ]);

        $app->redis->expire($token, $app->config->login_cache_time);

        return [
            'token' => $token
        ];
    } else {
        throw new BusinessException(1000, '验证码有误');
    }
});

// 验证H5商城验证码
$app->post('/open/sms/h5/vcode', function () use ($app) {
    $phone = $app->request->getPost("phone");
    $code  = $app->request->getPost("code");

    if (empty($phone) || empty($code)) {
        throw new BusinessException(1000, '参数不正确');
    }

    $key = $phone . '_smscode';
    $vcode = $app->redis->get($key);

    if (!empty($vcode) && $vcode == $code) {
        $token = $app->util->uuid();

        $app->redis->setex($phone . '_token', $app->config->login_cache_time, $token);

        $app->redis->hmset($token, [
            'phone' => $phone
        ]);

        $app->redis->expire($token, $app->config->login_cache_time);

        return [
            'token' => $token
        ];
    } else {
        throw new BusinessException(1000, '验证码有误');
    }
});

// app登陆
$app->post('/open/app/login', function () use ($app) {
    $phone = $app->request->getPost("phone");
    $code  = $app->request->getPost("code");

    if (empty($phone) || empty($code)) {
        throw new BusinessException(1000, '参数不正确');
    }

    $key = $phone . '_smscode';
    $vcode = $app->redis->get($key);

    if (!empty($vcode) && $vcode == $code) {
        $info = Agent::findFirst('phone=' . $phone . " and status = 1");
        if (empty($info)) {
            throw new BusinessException(1000, '该手机号还未注册或审核中');
        }

        // 阻止多台同时登陆
        $exsitToken = $app->redis->get($phone . '_agent_token');
        if ($exsitToken) {
            $app->redis->del($exsitToken);
        }

        $token = $app->util->uuid();
        $app->redis->setex($phone . '_agent_token', $app->config->login_cache_time, $token);

        $app->redis->hmset($token, ['phone' => $phone, 'agent_id' => $info->id]);
        $app->redis->expire($token, 86400);

        return [
            'token' => $token,
            'manager_flag' => $info->manager_flag,
            'realname' => $info->realname,
        ];
    } else {
        throw new BusinessException(1000, '验证码有误');
    }
});

// h5商城登陆
$app->post('/open/h5/login', function () use ($app) {
    $phone = $app->request->getPost("phone");
    $code  = $app->request->getPost("code");

    if (empty($phone) || empty($code)) {
        throw new BusinessException(1000, '参数不正确');
    }

    $key = $phone . '_smscode';
    $vcode = $app->redis->get($key);

    if (!empty($vcode) && $vcode == $code) {
        $info = Customer::findFirst('phone=' . $phone);
        if (empty($info)) {
            throw new BusinessException(1000, '该手机号还未注册');
        }

        // 阻止多台同时登陆
        $exsitToken = $app->redis->get($phone . '_customer_token');
        if ($exsitToken) {
            $app->redis->del($exsitToken);
        }

        $token = $app->util->uuid();
        $app->redis->setex($phone . '_customer_token', $app->config->login_cache_time, $token);

        $app->redis->hmset($token, ['phone' => $phone, 'customer_id' => $info->id]);
        $app->redis->expire($token, 86400);

        return [
            'token' => $token
        ];
    } else {
        throw new BusinessException(1000, '验证码有误');
    }
});