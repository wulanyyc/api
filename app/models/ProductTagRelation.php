<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class ProductTagRelation extends Model
{
    public function getSource() {
        return "product_tag_relation";
    }
}
