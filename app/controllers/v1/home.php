<?php
/**
 * 首页数据
 */
use Biaoye\Model\Customer;
use Biaoye\Model\School;

$app->get('/v1/home/page', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);
    $info = Customer::findFirst('id=' . $customerId);
    $schoolInfo = School::findFirst('id=' . $info['school_id']);

    $list = School::find([
        'columns' => 'id, name',
        'conditions' => 'status = 0 and city=' . $schoolInfo['city']
    ])->toArray();

    return [
        'school_id' => $info['school_id'],
        'school_list' => $list,
    ]
});