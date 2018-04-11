<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Product;

$app->get('/v1/h5/list/new', function () use ($app) {
    $num = 6;

    $info = Product::find([
        'conditions' => 'status=0',
        'columns' => 'id,name,title,price,img',
        'limit' => $num,
        'order' => 'id desc'
    ]);

    if (!empty($info)) {
        return $info->toArray();
    } else {
        return [];
    }
});

$app->get('/v1/h5/list/new', function () use ($app) {
    $num = 6;

    $info = Product::find([
        'conditions' => 'status=0',
        'columns' => 'id,name,title,price,img',
        'limit' => $num,
        'order' => 'id desc'
    ]);

    if (!empty($info)) {
        return $info->toArray();
    } else {
        return [];
    }
});

$app->get('/v1/h5/list/category/{id:\d+}/num/{num:\d+}', function ($id, $num) use ($app) {
    $info = Product::find([
        'conditions' => 'status=0 and category=' . $id,
        'columns' => 'id,name,title,price,img',
        'limit' => $num,
        'order' => 'id desc'
    ]);

    if (!empty($info)) {
        return $info->toArray();
    } else {
        return [];
    }
});

$app->get('/v1/h5/list/subcategory/{id:\d+}/num/{num:\d+}', function ($id, $num) use ($app) {
    $info = Product::find([
        'conditions' => 'status=0 and sub_category=' . $id,
        'columns' => 'id,name,title,price,img',
        'limit' => $num,
        'order' => 'id desc'
    ]);

    if (!empty($info)) {
        return $info->toArray();
    } else {
        return [];
    }
});