<?php
/**
 * 商品详情数据
 */

use Biaoye\Model\Product;
use Biaoye\Model\ProductCategory;

// 商品详情
$app->get('/v1/h5/product/{id:\d+}', function ($id) use ($app) {
    $data = Product::findFirst([
        "conditions" => "id=" . $id,
        "columns" => 'name,price,market_price,title,slogan,brand,place,valid_date,province,package,weight,img,img1,img2,img3,sale_num,sub_category',
    ]);

    if (!$data) {
        return [];
    }

    $data = $data->toArray();

    $imgs = [];

    if (!empty($data['img'])) {
        $imgs[] = $data['img'];
    }

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
    unset($data['img']);
    unset($data['img1']);
    unset($data['img2']);
    unset($data['img3']);

    $data['sub_category'] = ProductCategory::findFirst($data['sub_category'])->name;
    $data['coupons'] = $app->data->getValidCoupons($app);
    $data['cart_num'] = $app->util->getCartNum($app);

    return $data;
});

// 商品推荐
$app->get('/v1/h5/product/recom', function () use ($app) {
    $data = $app->product->getProductRecom($app, 4);

    return $data;
});

// 商品搜索
$app->get('/v1/h5/product/search', function () use ($app) {
    $text = $app->request->getQuery("text");
    if (empty($text)) {
        throw new BusinessException(1000, '搜索内容不能为空');
    }

    $data = $app->product->getProductSearch($app, $text);

    return $data;
});

