<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class AgentOrder extends Model
{
    public function getSource() {
        return "agent_order";
    }
}
