<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\Product;
use Biaoye\Model\CompanyInventory;
use Biaoye\Model\AgentInventory;
use Biaoye\Model\AgentInventoryRecords;

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
            'id' => $item['product_id'],
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
        throw new BusinessException(1000, '进货列表不能为空');
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

    return $ret;
});


$app->get('/v1/app/product/buy/complete/{id:\d+}/{num:\d+}', function ($id, $num) use ($app) {
    $agentId = $app->util->getAgentId($app);

    $up = AgentInventoryRecords::findFirst($id);
    if ($num > $up->need_num) {
        throw new BusinessException(1000, '数量不能超过进货单数量');
    }

    $up->num = $num;
    $up->status = 1;

    if ($up->save()) {
        return 1;
    } else {
        throw new BusinessException(1000, '设置失败');
    }
});