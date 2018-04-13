<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class AgentOrderList extends Model
{
    public function getSource() {
        return "agent_order_list";
    }

    public static function addRecord($agentId, $orderId, $status = 0) {
        $ar = new self();
        $ar->agent_id = $agentId;
        $ar->order_id = $orderId;
        $ar->status = $status;
        $ar->save();
    }
}
