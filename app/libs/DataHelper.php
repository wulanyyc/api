<?php
use Biaoye\Model\School;
use Biaoye\Model\Room;
use Biaoye\Model\Product;
use Biaoye\Model\ProductListSchool;
use Biaoye\Model\ProductCategory;
use Biaoye\Model\CustomerCoupon;
use Biaoye\Model\CustomerCouponUse;

class DataHelper
{
    public function getSchoolName($app, $id) {
        $key = "school_" . $id;

        $cache = $app->redis->get($key);

        if ($cache) return $cache;

        $info = School::findFirst($id);

        if ($info) {
            $app->redis->setex($key, 604800, $info->name);
            return $info->name;
        }

        return '';
    }

    public function getRoomName($app, $id) {
        $key = "room_" . $id;

        $cache = $app->redis->get($key);

        if ($cache) return $cache;

        $info = Room::findFirst($id);

        if ($info) {
            $app->redis->setex($key, 604800, $info->name);
            return $info->name;
        }

        return '';
    }

    public function checkCouponStatus($app, $couponId, $products) {
        $couponInfo = CustomerCoupon::findFirst($couponId);
        $config = json_decode($couponInfo->config, true);

        if ($couponInfo->type == 1) {
            if (isset($config['limit_money'])) {
                $total = 0;
                foreach($products as $product) {
                    $money = $product['num'] * $app->product->getProductPrice($product['id']);
                    $total += $money;
                }

                if ($total > $config['limit_money']) {
                    return 1;
                } else {
                    return 0;
                }
            }

            return 1;
        }

        if ($couponInfo->type == 2) {
            $data = [];
            $total = 0;

            foreach($products as $product) {
                $info = Product::findFirst($product['id']);

                $money = $product['num'] * $app->product->getProductPrice($product['id']);

                if (!isset($data['category'][$info->category]['total'])) {
                    $data['category'][$info->category]['total'] = $money;
                } else {
                    $data['category'][$info->category]['total'] += $money;
                }
                
                if (!isset($data['category'][$info->sub_category]['total'])) {
                    $data['category'][$info->sub_category]['total'] = $money;
                } else {
                    $data['category'][$info->sub_category]['total'] += $money;
                }

                $total += $money;
            }

            if (empty($config['category'])) {
                if ($total >= $config['limit_money']) {
                    return 1;
                }
            } else {
                $categoryTotal = 0;
                foreach($config['category'] as $item) {
                    if (isset($data['category'][$item])) {
                        $categoryTotal += $data['category'][$item]['total'];
                    }
                }

                if ($categoryTotal >= $config['limit_money']) {
                    return 1;
                }
            }
        }

        if ($couponInfo->type == 3) {
            $data = [];
            $total = 0;

            foreach($products as $product) {
                $info = Product::findFirst($product['id']);

                $money = $product['num'] * $app->product->getProductPrice($product['id']);

                if (!isset($data['factory'][$info->factory]['total'])) {
                    $data['factory'][$info->factory]['total'] = $money;
                } else {
                    $data['factory'][$info->factory]['total'] += $money;
                }
                
                $total += $money;
            }

            $factoryTotal = 0;
            foreach($config['factory'] as $item) {
                if (isset($data['factory'][$item])) {
                    $factoryTotal += $data['factory'][$item]['total'];
                }
            }

            if ($factoryTotal >= $config['limit_money']) {
                return 1;
            }
        }

        return 0;
    }

    public function checkInventory($app, $products) {
        $ret = [
            'status' => true,
        ];

        foreach($products as $product) {
            $info = ProductListSchool::findFirst($product['id']);
            $inventoryNum = $info->num;

            if ($inventoryNum < $product['num']) {
                $ret['product'] = $info->name;
                $ret['num'] = $info->num;
                $ret['status'] = false;
                break;
            }
        }

        return $ret;
    }

    public function calculateOrderPrice($app, $data) {
        $products = json_decode($data['products'], true);
        $coupons = explode(',', $data['coupon_ids']);
        $productPrice = 0;

        foreach($products as $product) {
            $productPrice += $product['num'] * $app->product->getProductPrice;
        }

        $couponFee = 0;
        if (!empty($coupons)) {
            $customerId = $app->util->getCustomerId($app);
            foreach($coupons as $coupon) {
                $cnt = CustomerCouponUse::count("customer_id=" . $customerId . " and coupon_id=" . $coupon . " and use_status=0");

                if ($cnt > 0) {
                    $status = $app->data->checkCouponStatus($app, $coupon, $products);
                    if ($status == 1) {
                        $couponFee += CustomerCoupon::findFirst($coupon)->money;
                    }
                }
            }
        }

        $expressFee = $app->config->params->express_fee;

        $payMoney = $productPrice + $expressFee - $couponFee;
        if ($payMoney < 0) {
            $payMoney = 0;
        }

        $deliverFee = round($expressFee * $app->config->params->deliver_fee_rate, 2);
        $productSalary = round(($productPrice - $couponFee) * $app->config->params->order_salary_rate, 2);
        $totalSalary = $productSalary + $deliverFee;

        return [
            'product_price' => $productPrice,
            'express_fee' => $expressFee,
            'pay_money' => $payMoney,
            'deliver_fee' => $deliverFee,
            'product_salary' => $productSalary,
            'total_salary' => $totalSalary,
            'coupon_fee' => $couponFee,
        ];
    }
}
