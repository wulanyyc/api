<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class CustomerCoupon extends Model
{
    public function getSource() {
        return "customer_coupon";
    }
}
