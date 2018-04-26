<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\AgentMoneyList;

// 镖师列表
$app->get('/v1/app/manager/agent/list', function () use ($app) {
    $id = $app->util->getAgentId($app);
    return Agent::find([
        "conditions" => "manager_id=" . $id . " and status=1",
        "columns" => 'id as agent_id, realname, phone',
        "order" => 'id asc',
    ])->toArray();
});


$app->get('/v1/app/manager/agent/job/list', function () use ($app) {
    $id = $app->util->getAgentId($app);
    return Agent::find([
        "conditions" => "manager_id=" . $id . " and status=1",
        "columns" => 'id as agent_id, realname, phone',
        "order" => 'id asc',
    ])->toArray();
});