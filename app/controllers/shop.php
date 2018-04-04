<?php
use Biaoye\Model\Products;
use Biaoye\Model\Users;

$app->get('/shop/info/{id:\d+}', function ($id) use ($app) {
    $userInfo = Users::findFirst($id);

    $product = Products::find("uid=" . $id . " and status != 3");
    $productNum = count($product);
    $productCategory = [];
    if ($productNum > 0) {
        foreach($product as $value) {
            $productCategory[$value->status][] = $value;
        }
    }

    return [
        'name' => $userInfo->name,
        'logo' => $userInfo->logo,
        'money' => $userInfo->money,
        'productCategory' => $productCategory,
        'productNum' => $productNum,
    ];
});


$app->get('/shop/edit/{id:\d+}', function ($id) use ($app) {
    $userInfo = Users::findFirst($id);

    return [
        'name' => $userInfo->name,
        'logo' => $userInfo->logo,
        'desc' => $userInfo->desc
    ];
});