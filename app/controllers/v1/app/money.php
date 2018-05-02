<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\AgentMoneyList;
use Biaoye\Model\CustomerOrder;
use Phalcon\Mvc\Model\Transaction\Manager;

// 余额
$app->get('/v1/app/money/remain', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $info = Agent::findFirst($id);

    if ($info) {
        return $info->money;
    }

    return 0;
});


// 卡片信息
$app->get('/v1/app/money/card/info', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $ar = Agent::findFirst($id);

    return [
        'card_bank' => $ar->card_bank,
        'card_name' => $ar->card_name,
        'card_num' => $ar->card_num,
    ];
});

// 添加卡片
$app->post('/v1/app/money/edit/card', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $name = $app->request->getPost("card_name");
    $num = $app->request->getPost("card_num");
    $bank = $app->request->getPost("card_bank");

    if (empty($name) || empty($num) || empty($bank)) {
        throw new BusinessException(1000, '参数有误');
    }

    $ar = Agent::findFirst($id);
    $ar->card_name = $name;
    $ar->card_num = $num;
    $ar->card_bank = $bank;

    if (!$ar->save()) {
        throw new BusinessException(1000, '添加失败');
    }

    return 1;
});

// 收入明细
$app->get('/v1/app/money/income/list', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $date = $app->request->getQuery("date");

    if (empty($date) || strlen($date) != 8) {
        throw new BusinessException(1000, '参数有误');
    }

    $total = AgentMoneyList::count([
        "conditions" => "agent_id=" . $id . " and operator=0 and date=" . $date,
        "column" => 'money',
    ]);

    $list = AgentMoneyList::find([
        "conditions" => "agent_id=" . $id . " and operator=0 and date=" . $date,
        "columns" => 'money, order_id',
        "order" => 'id desc',
    ])->toArray();

    if (!empty($list)) {
        foreach($list as $key => $item) {
            $orderInfo = CustomerOrder::findFirst($item['order_id']);
            $createTime = $orderInfo->create_time;

            $timestamp = strtotime($createTime);
            $month = intval(date('m', $timestamp));
            $day = intval(date('d', $timestamp));

            $time = date('H:i', $timestamp);
            $list[$key]['date'] = $month . '月' . $day . '日';
            $list[$key]['time'] = $time;
            $list[$key]['order_num'] = date('Ymd', strtotime($createTime)) . $item['order_id'];
        }
    }

    return [
        'total' => empty($total) ? 0 : $total,
        'list' => $list,
    ];
});

// 提现
$app->get('/v1/app/money/get/page', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $info = Agent::findFirst($id);

    return [
        'bank' => $info->card_bank,
        'card_suffix' => substr($info->card_num, -4),
        'money' => $info->money,
    ];
});


// 提现
$app->post('/v1/app/money/get', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $currentMoney = Agent::findFirst($id)->money;
    $money = $app->request->getPost("money");

    if (empty($money)) {
        throw new BusinessException(1000, '提现金额不能为空');
    }

    if ($money > $currentMoney) {
        throw new BusinessException(1000, '提现金额大于余额');
    }

    try {
        $manager = new Manager();
        $transaction = $manager->get();

        $ar = new AgentMoneyList();
        $ar->setTransaction($transaction);
        $ar->agent_id = $id;
        $ar->money = $money;
        $ar->operator = 1;
        $ar->date = date('Ymd', time());
        if (!$ar->save()) {
            $transaction->rollback("save agent_money_list fail");
        }

        $up = Agent::findFirst($id);
        $up->money = $up->money - $money;
        if (!$up->save()) {
            $transaction->rollback("save agent money fail");
        }
        $transaction->commit();

        return 1;
    } catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
        $msg = $e->getMessage();
        $app->logger->error("rob job db save fail_" . $id . '_' . $oid . ':' . $msg);
        
        throw new BusinessException(1000, '抢单失败了');
    }
});

// 提现明细
$app->get('/v1/app/money/get/list', function () use ($app) {
    $id = $app->util->getAgentId($app);

    $list = AgentMoneyList::find([
        "conditions" => "agent_id=" . $id . " and operator=1",
        "columns" => 'money, create_time',
        "order" => 'id desc',
    ])->toArray();

    if (!empty($list)) {
        $agentInfo = Agent::findFirst($id);
        foreach($list as $key => $item) {
            $createTime = $item['create_time'];

            $timestamp = strtotime($createTime);
            $month = intval(date('m', $timestamp));
            $day = intval(date('d', $timestamp));

            $time = date('H:i', $timestamp);
            $list[$key]['date'] = $month . '月' . $day . '日';
            $list[$key]['time'] = $time;
            $list[$key]['title'] = $agentInfo->card_bank . "(" . substr($agentInfo->card_num, 0, -4) . ")";
        }
    }

    return $list;
});