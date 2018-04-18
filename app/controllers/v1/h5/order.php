<?php
/**
 * 下单
 */

use Biaoye\Model\Product;
use Biaoye\Model\ProductCategory;

$app->post('/v1/h5/order/confirm', function () use ($app) {
    $params = $_POST;

    if (empty($params)) {
        throw new BusinessException(1000, '参数不能为空');
    }

    $productStr = isset($params['products']) ? $params['products'] : '';

    if (empty($productStr)) {
        throw new BusinessException(1000, '商品数据不能为空');
    }

    $products = json_decode($productStr, true);

    if (empty($products)) {
        throw new BusinessException(1000, '商品数据不能为空');
    }

    $ret = [];
    $diff = 0
    foreach($products as $item) {
        if (isset($item['id']) && isset($item['num']) && $item['id'] > 0 && $item['num'] > 0) {
            $info = Product::findFirst($item['id']);
            $ret[$item['id']]['name']  = $info->name;
            $ret[$item['id']]['title'] = $info->title;
            $ret[$item['id']]['price'] = $app->producthelper->getProductPrice($item['id']);
            $diff += $info->market_price - $ret[$item['id']]['price'];
        } else {
            throw new BusinessException(1000, '商品数据有误');
        }
    }

    return [
        
    ];
});