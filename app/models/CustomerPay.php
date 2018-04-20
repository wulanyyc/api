<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class CustomerPay extends Model
{
    public function getSource() {
        return "customer_pay";
    }
}
