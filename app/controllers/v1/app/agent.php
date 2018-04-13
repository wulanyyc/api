<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\AgentOrderSuc;
use Biaoye\Model\AgentOrderList;
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

    $key = $app->config->params->get_order_prefix . $oid;

    if (!$app->redis->exsits($key)) {
        throw new BusinessException(1000, '该单已过期');
    }

    $getNum = $app->redis->get($key);
    if ($getNum == 1) {
        $app->redis->incr($key);
        $exsit = AgentOrderSuc::count("order_id=" . $oid);
        if ($exsit > 0) {
            $list = new AgentOrderList();
            $list->agent_id = $id;
            $list->order_id = $oid;
            $list->save();
            throw new BusinessException(1000, '该单已被抢');
        }

        $ar = new AgentOrderSuc();
        $ar->agent_id = $id;
        $ar->order_id = $oid;

        if ($ar->save()) {
            $list = new AgentOrderList();
            $list->agent_id = $id;
            $list->order_id = $oid;
            $list->status = 1;
            $list->save();

            return $ar->id;
        } else {
            $list = new AgentOrderList();
            $list->agent_id = $id;
            $list->order_id = $oid;
            $list->save();
            throw new BusinessException(1000, '该单已被抢');
        }
    } else {
        $app->redis->incr($key);
        $list = new AgentOrderList();
        $list->agent_id = $id;
        $list->order_id = $oid;
        $list->save();
        throw new BusinessException(1000, '该单已被抢');
    }
});