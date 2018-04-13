<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\AgentOrderSuc;
use Biaoye\Model\AgentOrderList;
use Biaoye\Model\CustomerOrder;
use Biaoye\Model\CustomerCart;
use Biaoye\Model\Product;

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
        "columns" => 'id as order_id, address_id, total_salary as salary',
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


$app->get('/v1/app/agent/rob/job/{oid:\d+}', function ($oid) use ($app) {
    $id = $app->util->getAgentId($app);

    $key = $app->config->params->get_order_prefix . $oid;

    if (!$app->redis->exists($key)) {
        throw new BusinessException(1000, '该单已过期');
    }

    $num = $app->redis->get($key);
    if ($num < 1) {
        // 抢单redis事务
        $app->redis->watch($key);
        $app->redis->multi();
        $app->redis->set($key, $num + 1);
        $robStatus = $app->redis->exec();

        if ($robStatus) {
            $ar = new AgentOrderSuc();
            $ar->agent_id = $id;
            $ar->order_id = $oid;

            if ($ar->save()) {
                AgentOrderList::addRecord($id, $oid, 1);
                $co = CustomerOrder::findFirst($oid);
                $co->status = 2;
                $co->save();

                return $ar->id;
            } else {
                AgentOrderList::addRecord($id, $oid);
                throw new BusinessException(1000, '该单已被抢');
            }
        } else {
            AgentOrderList::addRecord($id, $oid);
            throw new BusinessException(1000, '抢单失败');
        }
    } else {
        AgentOrderList::addRecord($id, $oid);
        throw new BusinessException(1000, '该单已被抢');
    }
});


$app->get('/v1/app/agent/job/detail/{oid}', function ($oid) use ($app) {
    $id = $app->util->getAgentId($app);
    $sex = Agent::findFirst($id)->sex;

    $data = CustomerOrder::findFirst([
        'conditions' => 'id = ' . $oid,
        'columns' => 'id as order_id,express_fee,express_time,product_price,pay_money,deliver_fee,product_salary,total_salary,cart_id,address_id'
    ])->toArray();

    if (empty($data)) {
        throw new BusinessException(1000, '未查到该订单号信息');
    }

    $data['express_time'] = date("Y:m", strtotime($data['express_time']));
    $data['address'] = $app->util->getAddressInfo($app, $data['address_id']);
    $data['order_num'] = date('Ymd') . $data['order_id'];
    $data['products'] = CustomerCart::getCart($data['cart_id']);

    return $data;
});