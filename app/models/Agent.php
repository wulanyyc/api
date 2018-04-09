<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class Agent extends Model
{
    public function getSource() {
        return "agent";
    }
}
