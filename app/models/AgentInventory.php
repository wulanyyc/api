<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class AgentInventory extends Model
{
    public function getSource() {
        return "agent_inventory";
    }
}
