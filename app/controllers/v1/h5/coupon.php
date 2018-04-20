<?php
/**
 * 优惠券
 */

use Biaoye\Model\CustomerCoupon;
use Biaoye\Model\CustomerCouponUse;

$app->post('/v1/h5/coupon/list', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);
    $products = $app->request->getPost("products");

    if (empty($products)) {
         return [];
    }

    $coupons = CustomerCouponUse::find([
        'conditions' => "customer_id=" . $customerId . " and use_status = 0 and end_date >= " . date('Ymd', time()) ,
        "columns" => 'coupon_id, start_date, end_date',
    ])->toArray();

    if (empty($coupons)) {
        return [];
    }

    $ret = [];
    $products = json_decode($products, true);
    foreach($coupons as $item) {
        $info = CustomerCoupon::findFirst($item['coupon_id']);
        if ($info && $info->status == 0) {
            $diff = (strtotime($item['end_date']) - time()) / 86400;
            $item['name'] = $info->name;
            $item['desc'] = $info->desc;
            $item['money'] = $info->money;
            $item['type'] = $info->type;
            $item['recent_flag'] = $diff < 7 ? 1 : 0;
            $item['start_date'] = date('Y.m.d', strtotime($item['start_date']));
            $item['end_date'] = date('Y.m.d', strtotime($item['end_date']));

            $item['status'] = $app->datahelper->checkCouponStatus($app, $item['coupon_id'], $products);
            $ret[] = $item;
        }
    }

    return $ret;
});


$app->post('/v1/h5/coupon/result', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);
    $coupons = $app->request->getPost("coupons");

    if (!empty($coupons)) {
        $coupons = CustomerCouponUse::find([
            'conditions' => "customer_id=" . $customerId . " and use_status = 0 and coupon_id in (" . $coupons . ")and end_date >= " . date('Ymd', time()) ,
            "columns" => 'coupon_id',
        ])->toArray();

        if (empty($coupons)) {
            return 0;
        } else {
            $total = 0;
            foreach($coupons as $item) {
                $total += CustomerCoupon::findFirst($item['coupon_id'])->money;
            }

            return $total;
        }
    } else {
        return 0;
    }
});


