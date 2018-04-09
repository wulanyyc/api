<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class School extends Model
{
    public function getSource() {
        return "location_school";
    }
}
