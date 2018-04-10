<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class Customer extends Model
{
    public function getSource() {
        return "customer";
    }
}
