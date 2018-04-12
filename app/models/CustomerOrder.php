<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class CustomerOrder extends Model
{
    public function getSource() {
        return "customer_order";
    }
}
