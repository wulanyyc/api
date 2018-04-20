<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class CustomerFeedback extends Model
{
    public function getSource() {
        return "customer_feedback";
    }
}
