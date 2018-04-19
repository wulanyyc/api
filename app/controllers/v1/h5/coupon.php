<?php
/**
 * 优惠券
 */

use Biaoye\Model\CustomerCoupon;
use Biaoye\Model\CustomerCouponUse;

$app->post('/v1/h5/coupon/list', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);
    $money = $app->request->getPost("money");
    $products = $app->request->getPost("products");

    $coupons = CustomerCouponUse::find([
        'conditions' => "customer_id=" . $customerId . " and use_status = 0 and end_date >= " . date('Ymd', time()) ,
        "columns" => 'coupon_id, start_date, end_date',
    ])->toArray();

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
            $item['start_date'] = date('Y.m.d', strtotime($item['start_date']));
            $item['end_date'] = date('Y.m.d', strtotime($item['end_date']));

            if ($info->type == 2) {
                $config = json_decode($info->config, true);
                if ($money)
            }

            $ret[] = $item;
        }
    }

    return $ret;
});


