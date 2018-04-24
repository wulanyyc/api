<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\AgentMoneyList;

// 个人中心
$app->get('/v1/app/stats/agent', function () use ($app) {
    $id = $app->util->getAgentId($app);
    $info = Agent::findFirst('id='. $id);

    // 当日收入
    $date = date('Ymd', time());
    $num = AgentMoneyList::count([
        "conditions" => "date=" . $date . " and agent_id=" . $id . " and operator=0",
    ]);

    $income = AgentMoneyList::sum([
        "conditions" => "date=" . $date . " and agent_id=" . $id . " and operator=0",
        "column" => "money",
    ]);

    $num = empty($num) ? 0 : $num;
    $income = empty($income) ? 0: $income;


    // 当月收入
    $monthDate = date('Ym01', time());
    $monthNum = AgentMoneyList::count([
        "conditions" => "date >=" . $monthDate . " and agent_id=" . $id . " and operator=0",
    ]);

    $monthIncome = AgentMoneyList::sum([
        "conditions" => "date >=" . $monthDate . " and agent_id=" . $id . " and operator=0",
        "column" => "money",
    ]);

    $monthNum = empty($monthNum) ? 0 : $monthNum;
    $monthIncome = empty($monthIncome) ? 0: $monthIncome;

    $sex = $info->sex;

    return [
        'sex' => $sex,
        'day_num' => $num,
        'day_income' => $income,
        'month_num' => $monthNum,
        'month_income' => $monthIncome,
    ];
});