<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\Product;
use Biaoye\Model\CompanyInventory;
use Biaoye\Model\AgentInventory;

// 进货
$app->get('/v1/app/product/buy', function () use ($app) {
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

        $ret[$item['product_id']]['name'] = $name;
        $ret[$item['product_id']]['num'] = $num;
    }
});