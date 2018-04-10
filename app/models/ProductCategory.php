<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class Product extends Model
{
    public function getSource() {
        return "product_category";
    }
}
