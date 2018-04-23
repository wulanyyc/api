<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\AgentMoneyList;

// 余额
$app->get('/v1/app/money/remain', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $info = Agent::findFirst($id);

    if ($info) {
        return $info->money;
    }

    return 0;
});


// 收入明细
$app->get('/v1/app/money/income/list', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $date = $app->request->getQuery("date");

    if (empty($date) || strlen($date) != 8) {
        throw new BusinessException(1000, '参数有误');
    }



    return 0;
});