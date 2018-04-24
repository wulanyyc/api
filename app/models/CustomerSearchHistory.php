<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class CustomerSearchHistory extends Model
{
    public function getSource() {
        return "customer_search_history";
    }
}
