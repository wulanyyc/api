<?php
/**
 * 优惠券
 */

use Biaoye\Model\CustomerCoupon;
use Biaoye\Model\CustomerCouponUse;

$app->get('/v1/h5/coupon/list', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $coupons = CustomerCouponUse::find([
        'conditions' => "customer_id=" . $customerId . " and use_status = 0 and end_date >= " . date('Ymd', time()) ,
        "columns" => 'coupon_id, start_date, end_date',
    ])->toArray();

    return $coupons;

    if (empty($coupons)) {
        return [];
    }

    $ret = [];
    foreach($coupons as $item) {
        $info = CustomerCoupon::findFirst($item['coupon_id']);
        if ($info && $info->status == 0) {
            $item['name'] = $info->name;
            $item['desc'] = $info->desc;
            $item['money'] = $info->money;
            $item['type'] = $info->type;

            $ret[] = $item;
        }
    }

    return $ret;
});


