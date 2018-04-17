<?php
/**
 * 商品详情数据
 */

use Biaoye\Model\Product;
use Biaoye\Model\ProductCategory;

$app->get('/v1/h5/product/{id:\d+}', function ($id) use ($app) {
    $data = Product::findFirst([
        "conditons" => "id=" . $id,
        "columns" => 'name,price,market_price,title,slogan,brand,place,valid_date,province,package,weight,img1,img2,img3,sale_num,sub_category',
    ])->toArray();

    $imgs = [];

    if (!empty($data['img1'])) {
        $imgs[] = $data['img1'];
    }

    if (!empty($data['img2'])) {
        $imgs[] = $data['img2'];
    }

    if (!empty($data['img3'])) {
        $imgs[] = $data['img3'];
    }

    $data['imgs'] = $imgs;
    unset($data['img1']);
    unset($data['img2']);
    unset($data['img3']);

    $data['sub_category'] = ProductCategory::findFirst($data['sub_category'])->name;

    return $data;
});