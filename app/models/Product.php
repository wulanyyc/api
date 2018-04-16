<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;
use Biaoye\Model\ProductTagRelation;
use Biaoye\Model\Customer;

class Product extends Model
{
    public function getSource() {
        return "product_list";
    }

    /**
     * need 是否补充不足商品
     */
    public static function getProductByTag($customerId, $tagId, $num, $need = false) {
        $school = Customer::findFirst($customerId)->school_id;
        $pids = ProductTagRelation::find([
            'conditions' => 'status=0 and tag_id=' . $tagId,
            'columns' => 'product_id',
            'limit' => $num,
            'order' => 'id desc'
        ])->toArray();

        $currentNum = count($pids);

        $pidArr = [];
        if ($currentNum > 0) {
            foreach($pids as $value) {
                $pidArr[] = $value['product_id'];
            }
        }

        if ($currentNum < $num && $need) {
            $need = $num - $currentNum;

            if (empty($pidArr)) {
                $conditions = "status = 1";
            } else {
                $conditions = "status = 1 and id not in (" . implode(',', $pidArr) . ")";
            }

            $needPids = self::find([
                "conditions" => $conditions,
                "order" => 'sale_num desc',
                "limit" => $need,
                'columns' => 'id',
            ])->toArray();

            if (!empty($needPids)) {
                foreach($needPids as $value) {
                    $pidArr[] = $value['id'];
                }
            }
        }

        if (empty($pidArr)) {
            return [];
        }

        return self::find([
            "conditions" => "id in (" . implode(',', $pidArr) . ")",
            'columns' => "id, name, price, title, slogan, img",
        ])->toArray();
    }
}
