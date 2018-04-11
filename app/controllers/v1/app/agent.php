<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;

$app->get('/v1/app/agent/realname', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $info = Agent::findFirst('id='. $id);

    return ['status' => $info->status];
});