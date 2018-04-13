<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class AgentOrderList extends Model
{
    public function getSource() {
        return "agent_order_list";
    }
}
