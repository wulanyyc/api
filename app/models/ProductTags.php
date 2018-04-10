<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class ProductTags extends Model
{
    public function getSource() {
        return "product_tags";
    }
}
