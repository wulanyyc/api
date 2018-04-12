<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\AgentOrder;
use Biaoye\Model\CustomerOrder;

$app->get('/v1/app/agent/realname', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $info = Agent::findFirst('id='. $id);

    return ['status' => $info->status];
});


$app->get('/v1/app/agent/job', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $sex = Agent::findFirst($id)->sex;

    $data = CustomerOrder::find([
        "conditons" => "sex=" . $sex . " and status=1",
        "columns" => 'id as order_id, address_id',
        "order" => 'id asc',
        "limit" => 50,
    ])->toArray();

    if (empty($data)) {
        return [];
    }

    foreach($data as $key => $value) {
        $data[$key]['address'] = $app->util->getAddressInfo($app, $value['address_id']);
    }

    return $data;
});


$app->get('/v1/app/agent/get/job/{oid:\d+}', function ($oid) use ($app) {
    $id = $app->util->getAgentId($app);

    $exsit = AgentOrder::find("order_id=" . $oid)->toArray();
    if ($exsit) {
        throw new BusinessException(1000, '该单已被抢');
    }

    $ar = new AgentOrder();
    $ar->agent_id = $id;
    $ar->order_id = $oid;

    if ($ar->save()) {
        return $ar->id;
    } else {
        throw new BusinessException(1000, '该单已被抢');
    }

    return $data;
});