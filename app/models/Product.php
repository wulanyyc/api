<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;
use Biaoye\Model\ProductTagRelation;

class Product extends Model
{
    public function getSource() {
        return "product_list";
    }

    public static function getProductByTag($tagId) {
        $pids = ProductTagRelation::find('status=0 and tag_id=' . $tagId)->toArray();
        return $pids;
    }
}
