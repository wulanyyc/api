<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class CustomerAddress extends Model
{
    public function getSource() {
        return "customer_address";
    }
}
