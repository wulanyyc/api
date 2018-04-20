<?php
use Biaoye\Model\School;
use Biaoye\Model\Room;
use Biaoye\Model\Product;
use Biaoye\Model\ProductCategory;
use Biaoye\Model\CustomerCoupon;

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
            return 1;
        }

        if ($couponInfo->type == 2) {
            $data = [];
            $total = 0;

            foreach($products as $product) {
                $info = Product::findFirst($product['id']);

                $money = $product['num'] * $app->producthelper->getProductPrice($product['id']);

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
                    $categoryTotal += $data['category'][$item]['total'];
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

                $money = $product['num'] * $app->producthelper->getProductPrice($product['id']);

                if (!isset($data['factory'][$info->factory]['total'])) {
                    $data['factory'][$info->factory]['total'] = $money;
                } else {
                    $data['factory'][$info->factory]['total'] += $money;
                }
                
                $total += $money;
            }

            $factoryTotal = 0;
            foreach($config['factory'] as $item) {
                $factoryTotal += $data['factory'][$item]['total'];
            }

            if ($factoryTotal >= $config['limit_money']) {
                return 1;
            }
        }

        return 0;
    }
}
