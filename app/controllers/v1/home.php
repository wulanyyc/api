<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Customer;
use Biaoye\Model\School;
use Biaoye\Model\Product;

$app->get('/v1/home/page', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);
    $info = Customer::findFirst('id=' . $customerId);
    $schoolInfo = School::findFirst('id=' . $info->school_id);

    $list = School::find([
        'columns' => 'id, name',
        'conditions' => 'status = 0 and city=' . $schoolInfo->city
    ])->toArray();

    $jinxuan = Product::getProductByTag(1, 3, true);
    $huodong = Product::getProductByTag(2, 3);
    $tejia = Product::getProductByTag(3, 4);

    return [
        'school_id' => $info->school_id,
        'school_list' => $list,
        'jinxuan' => $jinxuan,
        'huodong' => $huodong,
        'tejia' => $tejia,
    ];
});