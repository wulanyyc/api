<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class Users extends Model
{
    public function getSource() {
        return "users";
    }
}
