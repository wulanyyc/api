<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\AgentOrderSuc;
use Biaoye\Model\CustomerOrder;


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
            'total' => 0,
            'orders' => []
        ];
    }

    $orders = [];
    foreach($jobs as $job) {
        $data = CustomerOrder::findFirst($job->order_id);
        if (!$data) {
            continue;
        }

        $temp = [];
        $temp['order_num'] = $data->date . $data->id;
        $temp['order_id'] = $data->id;
        $temp['status'] = $job->status;
        $temp['salary'] = $data->total_salary;

        $orders[] = $temp;
    }

    return [
        'total' => count($orders),
        'orders' => $orders,
    ];
});