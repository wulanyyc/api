<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class Room extends Model
{
    public function getSource() {
        return "location_school_room";
    }
}
