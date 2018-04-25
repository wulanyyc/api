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
         throw new BusinessException(1000, '非法访问');
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

            $item['status'] = $app->data->checkCouponStatus($app, $item['coupon_id'], $products);
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


$app->get('/v1/h5/coupon/customer/list', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $allCoupons = CustomerCoupon::find("status=0")->toArray();

    $coupons = CustomerCouponUse::find([
        'conditions' => "customer_id=" . $customerId,
        "columns" => 'coupon_id, end_date, start_date',
    ])->toArray();

    $ret = [];
    foreach($coupons as $item) {
        $info = CustomerCoupon::findFirst($item['coupon_id']);
        $diff = (strtotime($item['end_date']) - time()) / 86400;

        $item['name'] = $info->name;
        $item['desc'] = $info->desc;
        $item['money'] = $info->money;
        $item['type'] = $info->type;
        $item['recent_flag'] = $diff < 7 ? 1 : 0;
        $item['start_date'] = date('Y.m.d', strtotime($item['start_date']));
        $item['end_date'] = date('Y.m.d', strtotime($item['end_date']));
        $ret[$item['coupon_id']] = $item;
    }

    $valid = [];
    foreach($allCoupons as $coupon) {
        if (!isset($ret[$coupon['id']])) {
            $valid[$coupon['id']] = [
                'name' => $coupon['name'],
                'desc' => $coupon['desc'],
                'money' => $coupon['money'],
                'type' => $coupon['type'],
                'coupon_id' => $coupon['id'],
            ];
        }
    }

    return [
        'get' => sort($ret),
        'unget' => sort($valid),
    ];
});

$app->get('/v1/h5/coupon/get/{id:\d+}', function ($id) use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $exsit = CustomerCouponUse::count([
        'conditions' => "customer_id=" . $customerId . " and coupon_id = " . $id,
    ]);

    if ($exsit) {
        throw new BusinessException(1000, '已领取过');
    }

    $info = CustomerCoupon::findFirst($id);

    if ($info->type == 1) {
        $config = json_decode($info->config, true);
        $startDate = date('Ymd', time());
        $endDate = date('Ymd', time() + $config['days'] * 86400);
    }

    if ($info->type == 2 || $info->type == 3) {
        $config = json_decode($info->config, true);

        $startDate = $config['start_date'];
        $endDate   = $config['end_date'];
    }

    $ar = new CustomerCouponUse();
    $ar->customer_id = $customerId;
    $ar->coupon_id = $id;
    $ar->start_date = $startDate;
    $ar->end_date = $endDate;

    if (!$ar->save()) {
        throw new BusinessException(1000, '领取失败');
    }

    return 1;
});


