<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;
use Biaoye\Model\ProductTagRelation;

class ProductListSchool extends Model
{
    public function getSource() {
        return "product_list_school";
    }
}
