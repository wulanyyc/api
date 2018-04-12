<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\Product;
use Biaoye\Model\ProductTagRelation;

$app->get('/v1/h5/list/new', function () use ($app) {
    $num = 6;

    $info = Product::find([
        'conditions' => 'status = 1',
        'columns' => 'id,name,title,price,img',
        'limit' => $num,
        'order' => 'id desc'
    ])->toArray();

    return $info;
});

// 标签列表：精选，活动，特价
$app->get('/v1/h5/list/tag/{id:\d+}', function ($id) use ($app) {
    $pids = ProductTagRelation::find([
        'conditions' => 'status=0 and tag_id = ' . $id,
        'columns' => 'product_id',
    ])->toArray();

    if (empty($pids)) return [];

    $pidList = [];
    foreach($pids as $value) {
        $pidList[] = $value['product_id'];
    }

    $info = Product::find([
        'conditions' => 'status = 1 and id in (' . implode(',', $pidList) . ')',
        'columns' => 'id,name,title,price,img',
        'order' => 'id desc'
    ])->toArray();

    return $info;
});


// 大类列表
$app->get('/v1/h5/list/category/{id:\d+}/num/{num:\d+}/page/{page:\d+}', function ($id, $num, $page) use ($app) {
    $info = Product::find([
        'conditions' => 'status=1 and category=' . $id,
        'columns' => 'id,name,title,price,img',
        'limit' => $num,
        'offset' => ($page - 1) * $num,
        'order' => 'id desc'
    ])->toArray();

    return $info;
});


// 二级分类列表
$app->get('/v1/h5/list/subcategory/{id:\d+}/num/{num:\d+}/page/{page:\d+}', function ($id, $num, $page) use ($app) {
    $info = Product::find([
        'conditions' => 'status=1 and sub_category=' . $id,
        'columns' => 'id,name,title,price,img',
        'limit' => $num,
        'offset' => ($page - 1) * $num,
        'order' => 'id desc'
    ])->toArray();

    return $info;
});