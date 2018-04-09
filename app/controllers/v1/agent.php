<?php
use Biaoye\Model\Agent;

$app->post('/v1/agent/login', function () use ($app) {
    return ['Hello' => 'World!'];
});


$app->post('/v1/agent/apply', function () use ($app) {
    $params = $_POST;

    $exsit = Agent::count("phone = " . $params['phone']);

    if ($exsit) {
        throw new BusinessException(1000, '该手机号已注册过');
    }

    $info = Agent::findFirst('phone = ' . $params['invite_code'] . ' and manager_flag = 1');
    if ($info->id) {
        $managerId = $info->id;
    } else {
        $managerId = 0;
    }

    $ar = new Agent();
    $ar->phone = $params['phone'];
    $ar->sex = $params['sex'];
    $ar->school_id = $params['school_id'];
    $ar->room_id = $params['room_id'];
    $ar->invite_code = $params['invite_code'];
    $ar->manager_id = $managerId;

    if ($ar->save()) {
        return 1;
    } else {
        throw new BusinessException(1000, '注册失败，请联系客服');
    }
});