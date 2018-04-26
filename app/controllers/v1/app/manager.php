<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\AgentOrderSuc;
use Biaoye\Model\CustomerOrder;
use Biaoye\Model\Product;
use Biaoye\Model\AgentInventoryRecords;

// 镖师列表
$app->get('/v1/app/manager/agent/list', function () use ($app) {
    $id = $app->util->getAgentId($app);
    return Agent::find([
        "conditions" => "manager_id=" . $id . " and status=1",
        "columns" => 'id as agent_id, realname, phone',
        "order" => 'id asc',
    ])->toArray();
});


$app->get('/v1/app/manager/agent/job/list/{agentId:\d+}', function ($agentId) use ($app) {
    $id = $app->util->getAgentId($app);
    $info = Agent::findFirst($agentId);
    $date = intval($app->request->getQuery("date"));

    if (!$info) {
        raise_bad_request($app);
    }

    if ($info->manager_id != $id) {
        raise_bad_request($app);
    }

    $jobs = AgentOrderSuc::find([
        "conditions" => "agent_id=" . $agentId . " and date=" . $date,
        "columns" => 'order_id, status',
        "order" => 'status asc',
    ])->toArray();

    if (empty($jobs)) {
        return [
            'name' => '',
            'total' => 0,
            'orders' => []
        ];
    }

    $orders = [];
    foreach($jobs as $job) {
        $data = CustomerOrder::findFirst($job['order_id']);
        if (!$data) {
            continue;
        }

        $temp = [];
        $temp['order_num'] = $data->date . $data->id;
        $temp['order_id'] = $data->id;
        $temp['status'] = $job['status'];
        $temp['salary'] = $data->total_salary;

        $orders[] = $temp;
    }

    return [
        'name' => $info->realname,
        'total' => count($orders),
        'orders' => $orders,
    ];
});


$app->get('/v1/app/manager/agent/buy/complete/list', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $date = intval($app->request->getQuery("date"));

    $childs = Agent::find("manager_id=" . $id . " and status = 1")->toArray();

    if (empty($childs)) {
         return [];
    }

    $agents = [];
    $infos = [];
    foreach($childs as $child) {
        $agents[] = $child['id'];
        $infos[$child['id']] = $child;
    }

    $list = AgentInventoryRecords::find([
        "conditions" => "operator=1 and agent_id in (" . implode(',', $agents) . ") and status =1",
        "columns" => 'product_id,need_num,agent_id',
        "order" => 'id desc',
    ])->toArray();

    if (empty($list)) {
        return [];
    }

    $output = [];

    foreach($list as $item) {
        $temp = [];
        // $temp['agent_id'] = $item['agent_id'];
        $temp['need_num'] = $item['need_num'];
        $temp['agent'] = $infos[$item['agent_id']]['realname'];
        $temp['prodcut'] = Product::findFirst($item['product_id'])->name;
        $output[] = $temp;
    }

    return $output;
});


$app->get('/v1/app/manager/agent/buy/process/list', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $date = intval($app->request->getQuery("date"));

    $childs = Agent::find("manager_id=" . $id . " and status = 1")->toArray();

    if (empty($childs)) {
         return [];
    }

    $agents = [];
    $infos = [];
    foreach($childs as $child) {
        $agents[] = $child['id'];
        $infos[$child['id']] = $child;
    }

    $list = AgentInventoryRecords::find([
        "conditions" => "operator=1 and agent_id in (" . implode(',', $agents) . ") and status =0",
        "columns" => 'product_id,need_num,agent_id',
        "order" => 'id desc',
    ])->toArray();

    if (empty($list)) {
        return [];
    }

    $output = [];

    foreach($list as $item) {
        $temp = [];
        // $temp['agent_id'] = $item['agent_id'];
        $temp['need_num'] = $item['need_num'];
        $temp['agent'] = $infos[$item['agent_id']]['realname'];
        $temp['prodcut'] = Product::findFirst($item['product_id'])->name;
        $output[] = $temp;
    }

    return $output;
});