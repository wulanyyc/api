<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\Product;
use Biaoye\Model\CompanyInventory;
use Biaoye\Model\AgentInventory;
use Biaoye\Model\AgentInventoryRecords;
use Biaoye\Model\ProductListSchool;
use Phalcon\Mvc\Model\Transaction\Manager;

// 进货
$app->get('/v1/app/product/buy/list', function () use ($app) {
    $agentId = $app->util->getAgentId($app);

    $agentInfo = Agent::findFirst($agentId);

    // 校代进货列表
    if ($agentInfo->manager_flag == 1) {
        $companyId = $agentInfo->company_id;
        $products = CompanyInventory::find([
            "conditions" => "status=0 and num > 0 and company_id = " . $companyId,
            "columns" => 'product_id, num',
            "order" => 'id asc',
        ])->toArray();
    } else {
        // 代理进货列表
        $parent = $agentInfo->manager_id;
        $products = AgentInventory::find([
            "conditions" => "status=0 and num > 0 and agent_id=" . $parent,
            "columns" => 'product_id, num',
            "order" => 'id asc',
        ]);
    }

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
        $add->num = 0;
        $add->agent_id = $agentId;
        $add->batch_id = $batch;
        $add->date = date('Ymd', time());
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
    $date = $app->request->getQuery("date");

    if (empty($date)) {
        throw new BusinessException(1000, '参数有误');
    }

    $ret = AgentInventoryRecords::find([
        "conditions" => "status=1 and operator=1 and agent_id = " . $agentId . " and date=" . $date,
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
    $agentInfo = Agent::findFirst($agentId);

    $checkStatus = AgentInventoryRecords::findFirst($id)->status;

    if ($checkStatus == 1) {
        throw new BusinessException(1000, '进货已完成');
    }

    try {
        $manager = new Manager();
        $transaction = $manager->get();

        // 代理库存调整
        $up = AgentInventoryRecords::findFirst($id);
        $up->setTransaction($transaction);

        $up->num = $num;
        $up->status = 1;
        $up->date = date('Ymd', time());

        if (!$up->save()) {
            $transaction->rollback("add invertory_record fail");
        }

        // 库存数据调整
        $exsit = AgentInventory::count("agent_id=" . $up->agent_id . " and product_id=" . $up->product_id);

        if ($exsit > 0) {
            $ai = AgentInventory::findFirst("agent_id=" . $up->agent_id . " and product_id=" . $up->product_id);
            $ai->setTransaction($transaction);

            $ai->num = $ai->num + $num;
            if (!$ai->save()) {
                $transaction->rollback("update agent_inventory fail");
            }
        } else {
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
        }

        // 学校库存调整
        $schoolExsit = ProductListSchool::count("school_id=" . $agentInfo->school_id . " and product_id=" . $up->product_id);

        if ($schoolExsit > 0) {
            $pls = ProductListSchool::findFirst("school_id=" . $agentInfo->school_id . " and product_id=" . $up->product_id);
            $pls->setTransaction($transaction);

            $pls->num = $pls->num + $num;
            if (!$pls->save()) {
                $transaction->rollback("update agent_inventory fail");
            }

            $transaction->commit();
        } else {
            $productInfo = Product::findFirst($up->product_id);

            $pls = new ProductListSchool();
            $pls->setTransaction($transaction);

            $pls->num = $num;
            $pls->product_id = $up->product_id;
            $pls->school_id = $agentInfo->school_id;
            $pls->category = $productInfo->category;
            $pls->sub_category = $productInfo->sub_category;
            $pls->name = $productInfo->name;
            $pls->price = $productInfo->price;
            $pls->market_price = $productInfo->market_price;
            $pls->title = $productInfo->title;
            $pls->slogan = $productInfo->slogan;
            $pls->brand = $productInfo->brand;
            $pls->img = $productInfo->img;
            $pls->tags = $productInfo->tags;
            $pls->status = 1;

            if (!$pls->save()) {
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