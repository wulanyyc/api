<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\Product;
use Biaoye\Model\CompanyInventory;
use Biaoye\Model\AgentInventory;
use Biaoye\Model\AgentInventoryRecords;
use Phalcon\Mvc\Model\Transaction\Manager;

// 进货
$app->get('/v1/app/product/buy/list', function () use ($app) {
    $agentId = $app->util->getAgentId($app);

    $companyId = Agent::findFirst($agentId)->company_id;
    $products = CompanyInventory::find([
        "conditions" => "status=0 and num > 0 and company_id = " . $companyId,
        "columns" => 'product_id, num',
        "order" => 'id asc',
    ])->toArray();

    $ret = [];
    foreach($products as $key => $item) {
        $name = Product::findFirst($item['product_id'])->name;
        $agent = AgentInventory::findFirst([
            "conditions" => "status=0 and product_id = " . $item['product_id'] . " and agent_id=" . $agentId,
        ]);

        if ($agent) {
            $num = $agent->num;
        } else {
            $num = 0;
        }

        $ret[] = [
            'product_id' => $item['product_id'],
            'name' => $name,
            'num' => $num,
        ];
    }

    return $ret;
});


$app->post('/v1/app/product/buy/submit', function () use ($app) {
    $agentId = $app->util->getAgentId($app);
    $list = $app->request->getPost("list");

    // $app->logger->error(file_get_contents("php://input"));

    if (empty($list)) {
        throw new BusinessException(1000, '进货列表不能为空');
    }

    $info = json_decode($list, true);
    if (empty($info)) {
        throw new BusinessException(1000, '进货列表格式有误');
    }

    $buy = [];
    // 参数检测
    foreach($info as $item) {
        $item['product_id'] = intval($item['product_id']);
        $item['num'] = intval($item['num']);
        if ($item['product_id'] > 0 && $item['num'] > 0) {
            $buy[$item['product_id']] = [
                'product_id' => $item['product_id'],
                'num' => $item['num'],
            ];
        } else {
            throw new BusinessException(1000, '参数有误');
        }
    }

    $batch = $app->util->uuid();
    foreach($buy as $item) {
        $add = new AgentInventoryRecords();
        $add->operator = 1;
        $add->product_id = $item['product_id'];
        $add->status = 0;
        $add->need_num = $item['num'];
        $add->num = $item['num'];
        $add->agent_id = $agentId;
        $add->batch_id = $batch;
        $add->save();
    }

    return 1;
});

$app->get('/v1/app/product/buy/process/list', function () use ($app) {
    $agentId = $app->util->getAgentId($app);

    $ret = AgentInventoryRecords::find([
        "conditions" => "status=0 and operator=1 and agent_id = " . $agentId,
        "columns" => 'product_id, need_num, id as operator_id',
        "order" => 'id desc',
    ])->toArray();

    if (!empty($ret)) {
        foreach($ret as $key => $item) {
            $ret[$key]['name'] = Product::findFirst($item['product_id'])->name;
        }
    }

    return $ret;
});


$app->get('/v1/app/product/buy/complete/list', function () use ($app) {
    $agentId = $app->util->getAgentId($app);

    $ret = AgentInventoryRecords::find([
        "conditions" => "status=1 and operator=1 and agent_id = " . $agentId,
        "columns" => 'product_id, need_num, num, id as operator_id',
        "order" => 'id desc',
    ])->toArray();

    if (!empty($ret)) {
        foreach($ret as $key => $item) {
            $ret[$key]['name'] = Product::findFirst($item['product_id'])->name;
        }
    }

    return $ret;
});


$app->get('/v1/app/product/buy/complete/{id:\d+}/{num:\d+}', function ($id, $num) use ($app) {
    $agentId = $app->util->getAgentId($app);

    try {
        $manager = new Manager();
        $transaction = $manager->get();

        $up = AgentInventoryRecords::findFirst($id);
        $up->setTransaction($transaction);

        $up->num = $num;
        $up->status = 1;

        if (!$up->save()) {
            $transaction->rollback("add invertory_record fail");
        }

        $exsit = AgentInventory::count("agent_id=" . $up->agent_id . " and product_id=" . $up->product_id);

        if ($exsit > 0) {
            $ai = AgentInventory::findFirst("agent_id=" . $up->agent_id . " and product_id=" . $up->product_id);
            $ai->setTransaction($transaction);

            $ai->num = $ai->num + $num;
            if (!$ai->save()) {
                $transaction->rollback("update agent_inventory fail");
            }

            $transaction->commit();
        } else {
            $agentInfo = Agent::findFirst($up->agent_id);
            $add = new AgentInventory();
            $add->setTransaction($transaction);

            $add->num = $num;
            $add->product_id = $up->product_id;
            $add->agent_id = $up->agent_id;
            $add->school_id = $agentInfo->school_id;
            $add->room_id = $agentInfo->room_id;

            if (!$add->save()) {
                $transaction->rollback("add agent_inventory fail");
            }

            $transaction->commit();
        }

        return 1;
    } catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
        $msg = $e->getMessage();
        $app->logger->error("add product save fail_" . $id . '_' . $oid . ':' . $msg);
        
        throw new BusinessException(1000, '进货失败');
    }
});