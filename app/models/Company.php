<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class Company extends Model
{
    public function getSource() {
        return "company";
    }
}
