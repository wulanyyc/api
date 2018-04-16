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
use Phalcon\Mvc\Model\Transaction\Manager;

// 实名状态
$app->get('/v1/app/agent/realname', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $info = Agent::findFirst('id='. $id);

    return ['status' => $info->status, 'name' => $info->realname];
});

$app->get('/v1/app/agent/work/start', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $up = Agent::findFirst($id);
    $up->work_flag = 1;
    $up->save();

    return 1;
});

$app->get('/v1/app/agent/work/stop', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $up = Agent::findFirst($id);
    $up->work_flag = 0;
    $up->save();

    return 1;
});

// 抢单列表
$app->get('/v1/app/agent/job', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $sex = Agent::findFirst($id)->sex;

    //TODO 细化
    $data = CustomerOrder::find([
        "conditions" => "sex=" . $sex . " and status=1",
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

// 抢单
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
            try {
                $manager = new Manager();
                $transaction = $manager->get();

                $ar = new AgentOrderSuc();
                $ar->setTransaction($transaction);

                $ar->agent_id = $id;
                $ar->order_id = $oid;

                if (!$ar->save()) {
                    $transaction->rollback("save agent_order_suc fail");
                }

                $co = CustomerOrder::findFirst($oid);
                $co->setTransaction($transaction);
                $co->status = 2;

                if (!$co->save()) {
                    $transaction->rollback("save customer_order fail");
                }

                $transaction->commit();
            } catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
                AgentOrderList::addRecord($id, $oid);

                $msg = $e->getMessage();
                $app->logger->error("rob job db save fail_" . $id . '_' . $oid . ':' . $msg);
                
                throw new BusinessException(1000, '抢单失败了');
            }

            AgentOrderList::addRecord($id, $oid, 1);

            return 1;
        } else {
            AgentOrderList::addRecord($id, $oid);
            throw new BusinessException(1000, '抢单失败');
        }
    } else {
        AgentOrderList::addRecord($id, $oid);
        throw new BusinessException(1000, '该单已被抢');
    }
});

// 抢单详情
$app->get('/v1/app/agent/job/detail/{oid:\d+}', function ($oid) use ($app) {
    $id = $app->util->getAgentId($app);
    $sex = Agent::findFirst($id)->sex;

    $data = CustomerOrder::findFirst([
        'conditions' => 'id = ' . $oid,
        'columns' => 'id as order_id,express_fee,express_time,product_price,pay_money,deliver_fee,product_salary,total_salary,cart_id,address_id'
    ])->toArray();

    if (empty($data)) {
        throw new BusinessException(1000, '未查到该订单号信息');
    }

    $data['express_time_tiny'] = date("H:i", strtotime($data['express_time']));
    $data['address'] = $app->util->getAddressInfo($app, $data['address_id']);
    $data['order_num'] = date('Ymd') . $data['order_id'];
    $data['products'] = CustomerCart::getCart($data['cart_id']);

    return $data;
});

// 抢单成功后，处理列表
$app->get('/v1/app/agent/job/process', function () use ($app) {
    $id = $app->util->getAgentId($app);

    $orderList = AgentOrderSuc::find([
        "conditions" => "agent_id=" . $id . " and status=0",
        "columns" => 'order_id',
    ])->toArray();

    if (empty($orderList)) {
        return [];
    }

    $orderIds = [];
    foreach($orderList as $item) {
        $orderIds[] = $item['order_id'];
    }

    $data = CustomerOrder::find([
        "conditions" => "status=2 and id in (" . implode(',', $orderIds) . ")" ,
        "columns" => 'id as order_id, address_id, total_salary as salary,express_time',
        "order" => 'id asc',
        "limit" => 50,
    ])->toArray();

    if (empty($data)) {
        return [];
    }

    foreach($data as $key => $value) {
        $data[$key]['address'] = $app->util->getAddressInfo($app, $value['address_id']);
        $data[$key]['express_time'] = date("H:i", strtotime($data[$key]['express_time']));
    }

    return $data;
});

// 订单处理完成
$app->get('/v1/app/agent/job/complete/{oid:\d+}', function ($oid) use ($app) {
    $id = $app->util->getAgentId($app);

    // TODO 更新库存

    // TODO 收入调整

    try {
        $manager = new Manager();
        $transaction = $manager->get();

        $ar = AgentOrderSuc::findFirst("agent_id=" . $id . " and order_id=" . $oid);
        $ar->setTransaction($transaction);
        $ar->status = 1;

        if (!$ar->save()) {
            $transaction->rollback("save agent_order_suc complete fail");
        }

        $co = CustomerOrder::findFirst($oid);
        $co->setTransaction($transaction);
        $co->status = 3;

        if (!$co->save()) {
            $transaction->rollback("save customer_order complete fail");
        }

        $transaction->commit();
    } catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
        $msg = $e->getMessage();
        $app->logger->error("complete job db save fail_" . $id . '_' . $oid . ':' . $msg);
        
        throw new BusinessException(1000, '设置失败');
    }

    return 1;
});