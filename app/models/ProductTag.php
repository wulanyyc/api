<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class ProductTag extends Model
{
    public function getSource() {
        return "product_tag";
    }
}
