<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class UserCards extends Model
{
    public function getSource() {
        return "user_cards";
    }
}
