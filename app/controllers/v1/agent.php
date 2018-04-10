<?php
use Biaoye\Model\Agent;

$app->post('/v1/agent/login', function () use ($app) {
    return ['Hello' => 'World!'];
});


$app->post('/v1/agent/reg', function () use ($app) {
    $params = $_POST;

    $token = $app->util->getToken($app);
    $phone = $app->redis->hget($token, 'phone');
    $inviteCode = $app->redis->hget($token, 'invite_code');

    $exsit = Agent::count("phone = " . $phone);

    if ($exsit) {
        throw new BusinessException(1000, '该手机号已注册过');
    }

    $info = Agent::findFirst('phone = ' . $inviteCode);
    $managerId = $info->id;

    $ar = new Agent();
    $ar->phone = $phone;
    $ar->sex = $params['sex'];
    $ar->school_id = $params['school_id'];
    $ar->room_id = $params['room_id'];
    $ar->invite_code = $inviteCode;
    $ar->manager_id = $managerId;

    if ($ar->save()) {
        $token = $app->util->getToken($app);
        $app->redis->hmset($token, ['agent_id' => $ar->id]);

        return 1;
    } else {
        throw new BusinessException(1000, '注册失败，请联系客服');
    }
});