<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class CustomerCouponUse extends Model
{
    public function getSource() {
        return "customer_coupon_use";
    }
}
