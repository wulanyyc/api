<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Agent;
use Biaoye\Model\Product;
use Biaoye\Model\CompanyInventory;

// 进货
$app->get('/v1/app/product/buy', function () use ($app) {
    CompanyInventory::find([
        "conditions" => "sex=" . $sex . " and status=1",
        "columns" => 'id as order_id, address_id, total_salary as salary',
        "order" => 'id asc',
        "limit" => 50,
    ])->toArray();
});