<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class AgentInventoryRecords extends Model
{
    public function getSource() {
        return "agent_inventory_records";
    }
}
