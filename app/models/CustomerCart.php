<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class CustomerCart extends Model
{
    public function getSource() {
        return "customer_cart";
    }
}
