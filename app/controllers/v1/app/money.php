<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;

// 余额
$app->get('/v1/app/money/remain', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $info = Agent::findFirst($id);

    if ($info) {
        return $info->money;
    }

    return 0;
});

