<?php
use Biaoye\Model\School;
use Biaoye\Model\Room;
use Biaoye\Model\Product;
use Biaoye\Model\ProductCategory;

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

    public function checkCouponStatus($app, $config, $products) {
        $data = [];
        $total = 0;
        foreach($products as $product) {
            $info = Product::findFirst($product['id']);
            $data[$product['category']]['total'] = $product['num'] * $app->producthelper->getProductPrice($product['id']);

            $total += $data[$product['category']]['total'];
        }

        
    }
}
