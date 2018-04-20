<?php
/**
 * 标签列表、分类列表、特殊列表
 */

use Biaoye\Model\Product;
use Biaoye\Model\ProductTag;
use Biaoye\Model\ProductCategory;
use Biaoye\Model\ProductTagRelation;

$app->get('/v1/h5/list/new', function () use ($app) {
    $num = 10;

    $products = $app->product->getNewProduct($app, $num);

    return [
        'title' => '新品',
        'products' => $products
    ];
});

// 标签列表：精选，活动，特价
$app->get('/v1/h5/list/tag/{id:\d+}/num/{num:\d+}/page/{page:\d+}', function ($id, $num, $page) use ($app) {
    $products = $app->product->getProductByTag($app, $id, $num, $page);

    return [
        'title' => ProductTag::findFirst($id)->name,
        'products' => $products
    ];
});


// 大类列表
$app->get('/v1/h5/list/category/{id:\d+}/num/{num:\d+}/page/{page:\d+}', function ($id, $num, $page) use ($app) {
    $products = $app->product->getProductByCategory($app, $id, $num, $page);

    return [
        'title' => ProductCategory::findFirst($id)->name,
        'products' => $products
    ];
});


// 二级分类列表
$app->get('/v1/h5/list/subcategory/{id:\d+}/num/{num:\d+}/page/{page:\d+}', function ($id, $num, $page) use ($app) {
    $products = $app->product->getProductByCategory($app, $id, $num, $page, 2);

    return [
        'title' => ProductCategory::findFirst($id)->name,
        'products' => $products
    ];
});