<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class ProductCategory extends Model
{
    public function getSource() {
        return "product_category";
    }
}
